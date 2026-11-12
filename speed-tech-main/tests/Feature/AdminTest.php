<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->admin->forceFill(['is_admin' => true])->save();
    }

    public function test_guests_and_non_admins_are_kept_out(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');

        $customer = User::factory()->create();
        $this->actingAs($customer)->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_can_log_in(): void
    {
        $this->post('/admin/login', ['email' => $this->admin->email, 'password' => 'password'])
            ->assertRedirect('/admin');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_non_admin_cannot_log_in(): void
    {
        $customer = User::factory()->create();
        $this->post('/admin/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_pages_render(): void
    {
        $this->actingAs($this->admin);
        foreach (['/admin', '/admin/products', '/admin/products/create', '/admin/catalog/categories',
            '/admin/orders', '/admin/enquiries', '/admin/inventory', '/admin/settings', '/admin/account'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('AI product writer')->assertDontSee('Spare-part finder');
        }
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.ai.create'));
        foreach (['/admin/catalog/unknown', '/admin/catalog/brands', '/admin/catalog/part-categories', '/admin/models'] as $url) {
            $this->get($url)->assertNotFound();
        }
    }

    public function test_admin_creates_product_with_image(): void
    {
        $cameras = Category::create(['name' => 'CCTV Cameras']);

        $this->actingAs($this->admin)->post('/admin/products', [
            'name' => '2MP dome camera', 'category_id' => $cameras->id, 'specification' => '1080p, IR 20 m',
            'oem_part_number' => 'DOME-2MP', 'price' => '5900', 'stock_qty' => 4, 'is_active' => 1,
            'image' => UploadedFile::fake()->image('cam.jpg', 400, 300),
        ])->assertRedirect();

        $product = Product::where('name', '2MP dome camera')->firstOrFail();
        $this->assertSame($cameras->id, $product->category_id);
        $this->assertSame(4, $product->stock_qty);
        $this->assertStringStartsWith('uploads/products/', $product->image);
        $this->assertFileExists(public_path($product->image));

        $this->delete('/admin/products/'.$product->slug)->assertRedirect('/admin/products');
        $this->assertFileDoesNotExist(public_path($product->image));
        File::deleteDirectory(public_path('uploads/products'), preserve: true);
    }

    public function test_product_requires_category(): void
    {
        $this->actingAs($this->admin)->post('/admin/products', [
            'name' => 'Junction box', 'stock_qty' => 1,
        ])->assertSessionHasErrors('category_id');

        $cat = Category::create(['name' => 'Accessories']);
        $this->post('/admin/products', ['name' => 'Junction box', 'stock_qty' => 1, 'category_id' => $cat->id])
            ->assertSessionDoesntHaveErrors();
    }

    public function test_taxonomy_crud_and_in_use_protection(): void
    {
        $this->actingAs($this->admin)->post('/admin/catalog/categories', ['name' => 'Power Supplies', 'sort_order' => 1, 'is_active' => 1]);
        $power = Category::where('name', 'Power Supplies')->firstOrFail();
        $this->assertSame('power-supplies', $power->slug);

        Product::create(['name' => '12V adapter', 'category_id' => $power->id]);
        $this->delete("/admin/catalog/categories/{$power->id}")->assertSessionHas('error');
        $this->assertModelExists($power);
    }

    public function test_cancelling_an_order_returns_stock(): void
    {
        $product = Product::create(['name' => '1TB surveillance hard drive', 'price' => 10000, 'stock_qty' => 3]);
        $this->post('/cart/items', ['product_id' => $product->id, 'quantity' => 2]);
        $this->post('/checkout', ['customer_name' => 'A', 'phone' => '0771234567', 'fulfilment' => 'pickup', 'payment_method' => 'pay_at_store']);
        $order = Order::firstOrFail();
        $this->assertSame(1, $product->fresh()->stock_qty);

        $this->actingAs($this->admin)->patch("/admin/orders/{$order->id}", ['status' => 'cancelled'])->assertSessionHas('status');
        $this->assertSame(3, $product->fresh()->stock_qty);

        $this->patch("/admin/orders/{$order->id}", ['status' => 'confirmed']);
        $this->assertSame(1, $product->fresh()->stock_qty);
    }
}
