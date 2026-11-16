<?php

namespace Tests\Feature;

use App\Services\GoogleReviews;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleReviewsTest extends TestCase
{
    use RefreshDatabase;

    private function place(): array
    {
        return [
            'id' => 'ChIJtest',
            'rating' => 4.86,
            'userRatingCount' => 37,
            'googleMapsUri' => 'https://maps.google.com/?cid=1',
            'reviews' => [[
                'rating' => 5,
                'relativePublishTimeDescription' => '2 weeks ago',
                'originalText' => ['text' => 'Great CCTV installation, very neat work.'],
                'authorAttribution' => ['displayName' => 'Kasun P', 'uri' => 'https://maps.google.com/u/1', 'photoUri' => 'https://lh3.googleusercontent.com/a/x'],
                'googleMapsUri' => 'https://maps.google.com/review/1',
            ]],
        ];
    }

    public function test_without_api_key_home_links_to_the_google_listing(): void
    {
        config(['shop.google_reviews.api_key' => null]);
        Http::fake();

        $this->get('/')->assertOk()
            ->assertSee('What our customers say')
            ->assertSee('Read all reviews')
            ->assertSee('https://www.google.com/maps?cid=12639943915634735336', false);
        Http::assertNothingSent();
    }

    public function test_shows_rating_and_reviews_and_caches_them(): void
    {
        config(['shop.google_reviews.api_key' => 'test-key', 'shop.google_reviews.place_id' => 'ChIJtest', 'cache.default' => 'array']);
        Http::fake(['places.googleapis.com/*' => Http::response($this->place())]);

        $this->get('/')->assertOk()
            ->assertSee('4.9')
            ->assertSee('Based on 37 reviews from our customers on Google')
            ->assertSee('Kasun P')
            ->assertSee('Great CCTV installation, very neat work.')
            ->assertSee('placeid=ChIJtest', false);

        $this->get('/')->assertOk();
        Http::assertSentCount(1); // second view served from cache
    }

    public function test_finds_the_place_by_name_when_no_place_id_is_set(): void
    {
        config(['shop.google_reviews.api_key' => 'test-key', 'shop.google_reviews.place_id' => null, 'cache.default' => 'array']);
        Http::fake([
            'places.googleapis.com/v1/places:searchText' => Http::response(['places' => [['id' => 'ChIJfound']]]),
            'places.googleapis.com/v1/places/ChIJfound*' => Http::response($this->place()),
        ]);

        $this->assertSame(37, app(GoogleReviews::class)->get()['count']);
        $this->assertStringContainsString('ChIJfound', app(GoogleReviews::class)->writeReviewUrl());
    }

    public function test_google_errors_keep_the_page_working(): void
    {
        config(['shop.google_reviews.api_key' => 'bad-key', 'shop.google_reviews.place_id' => 'ChIJtest', 'cache.default' => 'array']);
        Http::fake(['places.googleapis.com/*' => Http::response(['error' => 'denied'], 403)]);

        $this->get('/')->assertOk()->assertSee('Read all reviews');
    }
}
