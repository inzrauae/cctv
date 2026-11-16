<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReviewsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->admin->forceFill(['is_admin' => true])->save();
        config(['shop.google_reviews.api_key' => null]);
        Http::fake();
    }

    /** Same shape as the reviews file in a Google Takeout "Google Business Profile" export. */
    private function takeoutJson(): string
    {
        return json_encode(['reviews' => [
            [
                'name' => 'accounts/1/locations/2/reviews/AbC1',
                'reviewer' => ['displayName' => 'Nimal Perera', 'profilePhotoUrl' => 'https://lh3.googleusercontent.com/a/photo'],
                'starRating' => 'FIVE',
                'comment' => 'Very neat CCTV installation at our shop.',
                'createTime' => '2026-09-01T10:00:00Z',
                'reviewReply' => ['comment' => 'Thank you Nimal!'],
            ],
            [
                'name' => 'accounts/1/locations/2/reviews/XyZ2',
                'reviewer' => ['displayName' => 'Kamala'],
                'starRating' => 'FOUR',
                'comment' => "(Translated by Google) Good service\n\n(Original)\nහොඳ සේවාවක්",
                'createTime' => '2026-08-01T10:00:00Z',
            ],
            [
                'name' => 'accounts/1/locations/2/reviews/NoText3',
                'reviewer' => ['displayName' => 'Sunil'],
                'starRating' => 'FIVE',
                'createTime' => '2026-07-01T10:00:00Z',
            ],
        ]]);
    }

    private function zipWith(string $json): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'takeout').'.zip';
        $zip = new \ZipArchive;
        $zip->open($path, \ZipArchive::CREATE);
        $zip->addFromString('Takeout/Google Business Profile/account-1/location-2/reviews.json', $json);
        $zip->addFromString('Takeout/Google Business Profile/account-1/location-2/profile.json', '{"title":"Last Solution"}');
        $zip->close();

        return new UploadedFile($path, 'takeout-20261116.zip', 'application/zip', null, true);
    }

    public function test_import_takeout_zip_then_reimport_without_duplicates(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/reviews/import', ['file' => $this->zipWith($this->takeoutJson())])
            ->assertRedirect('/admin/reviews')
            ->assertSessionHas('status', 'Import finished: 3 new, 0 updated.');

        $nimal = Review::where('author_name', 'Nimal Perera')->sole();
        $this->assertSame(5, $nimal->rating);
        $this->assertSame('Thank you Nimal!', $nimal->reply);
        $this->assertSame('google', $nimal->source);
        $this->assertSame('හොඳ සේවාවක්', Review::where('author_name', 'Kamala')->value('text')); // customer's own words
        $this->assertNull(Review::where('author_name', 'Sunil')->value('text'));

        // Admin hides one, then imports a newer export: no duplicates, choice kept.
        $nimal->update(['is_visible' => false]);
        $json = UploadedFile::fake()->createWithContent('reviews.json', $this->takeoutJson());
        $this->actingAs($this->admin)->post('/admin/reviews/import', ['file' => $json])
            ->assertSessionHas('status', 'Import finished: 0 new, 3 updated.');

        $this->assertSame(3, Review::count());
        $this->assertFalse($nimal->fresh()->is_visible);
    }

    public function test_import_reviews_list_json_with_relative_dates_and_photos(): void
    {
        $json = json_encode(['place' => '', 'count' => 2, 'reviews' => [
            [
                'id' => 'Ci9abc', 'name' => 'Ishara J', 'rating' => 5, 'date' => '2 years ago',
                'text' => 'Neat wiring, highly recommended!', 'owner_reply' => 'Thank you!',
                'avatar' => 'images/avatar_1.jpg', 'avatar_original' => 'https://lh3.googleusercontent.com/a-/x=s120',
                'photos' => ['images/photo_1.jpg'], 'photos_original' => ['https://lh3.googleusercontent.com/grass-cs/p=s1200', 'javascript:alert(1)'],
            ],
            [
                'id' => 'Ci9ghi', 'name' => 'Stars Only', 'rating' => 5, 'date' => 'a week ago',
                'text' => 'Thanking you!', 'owner_reply' => 'Thanking you!', 'avatar_original' => '', 'photos_original' => [],
            ],
            [
                'id' => 'Ci9def', 'name' => 'Daysinn', 'rating' => 1, 'date' => 'Edited a month ago',
                'text' => '', 'owner_reply' => '', 'avatar_original' => '', 'photos_original' => [],
            ],
        ]]);

        $this->actingAs($this->admin)
            ->post('/admin/reviews/import', ['file' => UploadedFile::fake()->createWithContent('reviews.json', $json)])
            ->assertSessionHas('status', 'Import finished: 3 new, 0 updated.');

        $ishara = Review::where('author_name', 'Ishara J')->sole();
        $this->assertSame('https://lh3.googleusercontent.com/a-/x=s120', $ishara->author_photo_url);
        $this->assertSame(['https://lh3.googleusercontent.com/grass-cs/p=s1200'], $ishara->photos); // unsafe link dropped
        $this->assertSame('Thank you!', $ishara->reply);
        $starsOnly = Review::where('author_name', 'Stars Only')->sole();
        $this->assertNull($starsOnly->text); // the copied reply is not shown as the customer's words
        $this->assertSame('Thanking you!', $starsOnly->reply);
        $this->assertTrue($ishara->reviewed_at->between(now()->subYears(2)->subDay(), now()->subYears(2)->addDay()));
        $this->assertTrue(Review::where('author_name', 'Daysinn')->sole()->reviewed_at->between(now()->subMonth()->subDay(), now()->subMonth()->addDay()));

        // Re-import a week later: no duplicates, and the first (approximate) date is kept.
        $firstDate = $ishara->reviewed_at;
        $this->travel(7)->days();
        $this->actingAs($this->admin)
            ->post('/admin/reviews/import', ['file' => UploadedFile::fake()->createWithContent('reviews.json', $json)])
            ->assertSessionHas('status', 'Import finished: 0 new, 3 updated.');
        $this->assertTrue($ishara->fresh()->reviewed_at->equalTo($firstDate));

        $this->get('/reviews')->assertOk()->assertSee('grass-cs/p=s1200', false);
    }

    public function test_import_rejects_files_without_reviews(): void
    {
        $file = UploadedFile::fake()->createWithContent('reviews.json', '{"title":"nothing here"}');

        $this->actingAs($this->admin)->post('/admin/reviews/import', ['file' => $file])->assertSessionHasErrors('file');
        $this->assertSame(0, Review::count());
    }

    public function test_admin_can_add_edit_toggle_and_delete(): void
    {
        $this->actingAs($this->admin)->get('/admin/reviews')->assertOk()->assertSee('Import from Google');

        $this->actingAs($this->admin)->post('/admin/reviews', [
            'author_name' => 'Ruwan', 'rating' => 5, 'text' => 'Fixed our DVR the same day.', 'is_visible' => 1,
        ])->assertRedirect('/admin/reviews');
        $review = Review::sole();
        $this->assertSame('manual', $review->source);

        $this->actingAs($this->admin)->patch("/admin/reviews/{$review->id}/toggle", ['field' => 'is_featured']);
        $this->assertTrue($review->fresh()->is_featured);

        $this->actingAs($this->admin)->put("/admin/reviews/{$review->id}", ['author_name' => 'Ruwan K', 'rating' => 4, 'is_visible' => 1]);
        $this->assertSame(['Ruwan K', 4], [$review->fresh()->author_name, $review->fresh()->rating]);

        $this->actingAs($this->admin)->delete("/admin/reviews/{$review->id}");
        $this->assertSame(0, Review::count());
    }

    public function test_reviews_page_paginates_many_reviews(): void
    {
        foreach (range(1, 30) as $i) {
            Review::create(['author_name' => "Customer {$i}", 'rating' => 5, 'text' => "Review {$i}", 'reviewed_at' => now()->subDays($i)]);
        }

        $this->get('/reviews')->assertOk()->assertSee('Customer 1')->assertDontSee('Customer 30');
        $this->get('/reviews?page=2')->assertOk()->assertSee('Customer 30');
    }

    public function test_guests_cannot_manage_reviews(): void
    {
        $this->post('/admin/reviews', ['author_name' => 'X', 'rating' => 5])->assertRedirect('/admin/login');
        $this->assertSame(0, Review::count());
    }

    public function test_home_and_reviews_page_show_only_visible_reviews(): void
    {
        Review::create(['author_name' => 'Shown Person', 'rating' => 5, 'text' => 'Excellent camera setup.', 'reviewed_at' => now()->subWeek(), 'reply' => 'Thanks!']);
        Review::create(['author_name' => 'Also Shown', 'rating' => 4, 'text' => 'Good.', 'reviewed_at' => now()->subMonth()]);
        Review::create(['author_name' => 'Hidden Person', 'rating' => 1, 'text' => 'Spam', 'is_visible' => false]);

        $this->get('/')->assertOk()
            ->assertSee('4.5')
            ->assertSee('Based on 2 reviews from our customers on Google')
            ->assertSee('Shown Person')
            ->assertDontSee('Hidden Person')
            ->assertSee(route('reviews'), false);

        $this->get('/reviews')->assertOk()
            ->assertSee('Excellent camera setup.')
            ->assertSee('Reply from Last Solution')
            ->assertDontSee('Hidden Person');
        Http::assertNothingSent(); // site reviews don't need the Google API
    }
}
