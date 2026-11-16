<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubcategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Category $cameras;

    private Subcategory $ip;

    private Subcategory $analog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->admin->forceFill(['is_admin' => true])->save();
        $this->cameras = Category::create(['name' => 'CCTV Cameras']);
        $this->ip = Subcategory::create(['category_id' => $this->cameras->id, 'name' => 'IP Cameras']);
        $this->analog = Subcategory::create(['category_id' => $this->cameras->id, 'name' => 'Analog Cameras']);
    }

    private function product(string $name, ?Subcategory $sub, string $brand = 'Hikvision'): Product
    {
        return Product::create(['category_id' => $this->cameras->id, 'subcategory_id' => $sub?->id, 'name' => $name, 'brand' => $brand, 'price' => 9000, 'is_active' => true]);
    }

    public function test_shop_filters_by_subcategory_and_brand(): void
    {
        $this->product('Hik IP Dome', $this->ip);
        $this->product('Hik Analog Bullet', $this->analog);
        $this->product('Dahua IP Bullet', $this->ip, 'Dahua');

        $this->get('/shop?category=cctv-cameras')->assertOk()
            ->assertSee('All CCTV Cameras')->assertSee('IP Cameras')->assertSee('Analog Cameras')
            ->assertSee('Hik IP Dome')->assertSee('Hik Analog Bullet');

        $this->get('/shop?category=cctv-cameras&sub=ip-cameras')->assertOk()
            ->assertSee('Hik IP Dome')->assertSee('Dahua IP Bullet')->assertDontSee('Hik Analog Bullet');

        $this->get('/shop?category=cctv-cameras&sub=ip-cameras&brand=Hikvision')->assertOk()
            ->assertSee('Hik IP Dome')->assertDontSee('Dahua IP Bullet')->assertDontSee('Hik Analog Bullet');
    }

    public function test_hidden_subcategory_is_not_offered_or_applied(): void
    {
        $this->analog->update(['is_active' => false]);
        $this->product('Hik Analog Bullet', $this->analog);

        $this->get('/shop?category=cctv-cameras')->assertOk()->assertDontSee('Analog Cameras <small>', false);
        $this->get('/shop?category=cctv-cameras&sub=analog-cameras')->assertOk()->assertSee('Hik Analog Bullet'); // unknown sub = whole category
    }

    public function test_product_page_breadcrumb_shows_subcategory(): void
    {
        $product = $this->product('Hik IP Dome', $this->ip);

        $this->get($product->url())->assertOk()->assertSee('sub=ip-cameras', false);
    }

    public function test_admin_manages_subcategories_and_products_keep_category_on_delete(): void
    {
        $this->actingAs($this->admin)->get('/admin/catalog/categories')->assertOk()->assertSee('Sub-categories in CCTV Cameras');

        $this->actingAs($this->admin)->post("/admin/categories/{$this->cameras->id}/subcategories", ['name' => 'PTZ Cameras', 'is_active' => 1])->assertRedirect();
        $ptz = Subcategory::where('name', 'PTZ Cameras')->sole();
        $this->assertSame('ptz-cameras', $ptz->slug);

        $this->actingAs($this->admin)->put("/admin/subcategories/{$ptz->id}", ['name' => 'PTZ & Speed Dome', 'is_active' => 1])->assertRedirect();
        $this->assertSame('PTZ & Speed Dome', $ptz->fresh()->name);

        $product = $this->product('PTZ cam', $ptz);
        $this->actingAs($this->admin)->delete("/admin/subcategories/{$ptz->id}")->assertRedirect();
        $this->assertNull($product->fresh()->subcategory_id);
        $this->assertSame($this->cameras->id, $product->fresh()->category_id);
    }

    public function test_product_form_rejects_subcategory_from_another_category(): void
    {
        $recorders = Category::create(['name' => 'Recorders']);
        $nvr = Subcategory::create(['category_id' => $recorders->id, 'name' => 'NVR']);

        $this->actingAs($this->admin)->post('/admin/products', [
            'name' => 'Mixed up', 'category_id' => $this->cameras->id, 'subcategory_id' => $nvr->id, 'stock_qty' => 0,
        ])->assertSessionHasErrors('subcategory_id');

        $this->actingAs($this->admin)->post('/admin/products', [
            'name' => 'Hik IP Dome', 'category_id' => $this->cameras->id, 'subcategory_id' => $this->ip->id, 'stock_qty' => 0,
        ])->assertSessionHasNoErrors();
        $this->assertSame($this->ip->id, Product::where('name', 'Hik IP Dome')->value('subcategory_id'));
    }
}
