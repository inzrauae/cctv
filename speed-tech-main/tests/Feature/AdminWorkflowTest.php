<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CatalogStructureSeeder;
use Database\Seeders\ProductCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/** End-to-end check of every admin function against the real product catalogue. */
class AdminWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CatalogStructureSeeder::class, ProductCatalogSeeder::class]);
        $this->admin = User::factory()->create(['password' => Hash::make('old-password-123')]);
        $this->admin->forceFill(['is_admin' => true])->save();
    }

    public function test_all_admin_screens_render_with_real_catalogue(): void
    {
        $this->actingAs($this->admin);
        $package = Product::where('price', 42500)->firstOrFail();

        foreach (['/admin', '/admin/products', '/admin/products/create', "/admin/products/{$package->slug}/edit",
            '/admin/inventory', '/admin/inventory/movements', '/admin/orders', '/admin/enquiries',
            '/admin/catalog/categories', '/admin/settings', '/admin/account'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/admin/products?q=DL05')->assertOk()->assertSee('EZVIZ DL05 Smart Fingerprint Lock');
    }

    public function test_settings_save_and_payment_rules(): void
    {
        $this->actingAs($this->admin);

        $this->put('/admin/settings', ['delivery_fee' => 500])->assertSessionHasErrors('payment');
        $this->put('/admin/settings', ['delivery_fee' => 500, 'payment_bank_transfer_enabled' => 1])->assertSessionHasErrors('bank_account_number');

        $this->put('/admin/settings', [
            'delivery_fee' => 450, 'free_delivery_over' => 100000, 'cod_max_order' => 200000,
            'payment_cod_enabled' => 1, 'payment_bank_transfer_enabled' => 1, 'payment_pay_at_store_enabled' => 1,
            'bank_name' => 'Test Bank', 'bank_account_name' => 'Last Solution', 'bank_account_number' => '123456789',
        ])->assertSessionHas('status');
        $this->assertEqualsCanonicalizing(['cod', 'bank_transfer', 'pay_at_store'], Order::enabledPaymentMethods());
    }

    public function test_product_edit_offer_and_stock_flow(): void
    {
        $this->actingAs($this->admin);
        $package = Product::where('price', 69500)->firstOrFail();

        // Set a regular price + offer price: it must show in the Offers section.
        $this->put("/admin/products/{$package->slug}", [
            'name' => $package->name, 'category_id' => $package->category_id, 'brand' => 'Hikvision',
            'price' => 75000, 'offer_price' => 69500, 'stock_qty' => 4, 'is_active' => 1,
        ])->assertRedirect();
        $package->refresh();
        $this->assertTrue($package->hasOffer());
        $this->assertSame(4, $package->stock_qty);
        $this->assertSame('assets/products/hikvision-8-camera-ir-kit.jpg', $package->image, 'editing must keep the catalogue image');
        $this->get('/offers')->assertOk()->assertSee($package->name);

        // Offer price must be lower than the price.
        $this->put("/admin/products/{$package->slug}", ['name' => $package->name, 'category_id' => $package->category_id, 'price' => 1000, 'offer_price' => 2000, 'stock_qty' => 4])
            ->assertSessionHasErrors('offer_price');

        // Inventory: receive, remove, count; export.
        $this->post("/admin/inventory/{$package->slug}/adjust", ['action' => 'receive', 'quantity' => 6, 'unit_cost' => 50000])->assertRedirect();
        $this->post("/admin/inventory/{$package->slug}/adjust", ['action' => 'remove', 'quantity' => 1, 'reason' => 'damaged'])->assertRedirect();
        $this->assertSame(9, $package->fresh()->stock_qty);
        $this->post("/admin/inventory/{$package->slug}/adjust", ['action' => 'count', 'quantity' => 7])->assertRedirect();
        $this->assertSame(7, $package->fresh()->stock_qty);
        $this->get('/admin/inventory/movements')->assertOk()->assertSee($package->name);
        $csv = $this->get('/admin/inventory/export')->assertOk()->streamedContent();
        $this->assertStringContainsString($package->sku, $csv);

        // Now purchasable on the storefront.
        $this->get('/products/'.$package->slug)->assertOk()->assertSee('Add to cart');
    }

    public function test_new_product_with_image_then_delete(): void
    {
        $this->actingAs($this->admin);
        $lockCat = Product::where('name', 'EZVIZ DL05 Smart Fingerprint Lock')->value('category_id');

        $this->post('/admin/products', [
            'name' => 'Imou Ranger 2 Wi-Fi camera', 'category_id' => $lockCat, 'brand' => 'Imou', 'price' => 9500,
            'stock_qty' => 3, 'is_active' => 1, 'image' => UploadedFile::fake()->image('cam.jpg', 800, 600),
        ])->assertRedirect();
        $product = Product::where('name', 'Imou Ranger 2 Wi-Fi camera')->firstOrFail();
        $this->assertFileExists(public_path($product->image));

        $this->delete("/admin/products/{$product->slug}")->assertRedirect('/admin/products');
        $this->assertFileDoesNotExist(public_path($product->image));
        $this->assertNull(Product::find($product->id));
    }

    public function test_order_lifecycle_payment_and_slip(): void
    {
        $package = Product::where('price', 42500)->firstOrFail();
        $package->update(['stock_qty' => 5]);
        \App\Models\Setting::write(['payment_bank_transfer_enabled' => true, 'bank_account_number' => '123456789']);

        // Customer orders with bank transfer and uploads a slip.
        $this->post('/cart/items', ['product_id' => $package->id, 'quantity' => 2]);
        $this->post('/checkout', ['customer_name' => 'Kamal', 'phone' => '0771234567', 'fulfilment' => 'pickup', 'payment_method' => 'bank_transfer'])->assertRedirect();
        $order = Order::firstOrFail();
        $this->assertSame(3, $package->fresh()->stock_qty);

        $this->post(URL::signedRoute('orders.slip', $order), ['slip' => UploadedFile::fake()->image('slip.jpg', 600, 800)])->assertRedirect();
        $this->assertNotNull($order->fresh()->payment_slip);

        // Admin views, downloads the slip, marks paid, moves the status on.
        $this->actingAs($this->admin);
        $this->get("/admin/orders/{$order->id}")->assertOk()->assertSee('Kamal')->assertSee($package->name);
        $this->get("/admin/orders/{$order->id}/slip")->assertOk();
        $this->patch("/admin/orders/{$order->id}/payment", ['payment_status' => 'paid', 'payment_reference' => 'TXN-1'])->assertRedirect();
        $this->assertTrue($order->fresh()->isPaid());
        foreach (['confirmed', 'ready', 'completed'] as $status) {
            $this->patch("/admin/orders/{$order->id}", ['status' => $status])->assertRedirect();
        }
        $this->assertSame('completed', $order->fresh()->status);
        $this->get('/admin')->assertOk();
    }

    public function test_enquiry_handling(): void
    {
        $this->postJson('/enquiries', [
            'type' => 'repair', 'name' => 'Nimal', 'phone' => '0771234567', 'device' => 'House, Kandy',
            'service' => 'CCTV installation', 'message' => 'Need the 4CH package installed',
        ])->assertCreated();
        $enquiry = Enquiry::firstOrFail();

        $this->actingAs($this->admin);
        $this->get('/admin/enquiries')->assertOk()->assertSee('Nimal');
        $this->get("/admin/enquiries/{$enquiry->id}")->assertOk()->assertSee('Need the 4CH package installed');
        $this->patch("/admin/enquiries/{$enquiry->id}", ['status' => 'contacted', 'admin_notes' => 'Called, site visit Monday'])->assertRedirect();
        $this->assertSame('contacted', $enquiry->fresh()->status);
    }

    public function test_password_change_and_logout(): void
    {
        $this->actingAs($this->admin);
        $this->put('/admin/account', ['current_password' => 'wrong', 'password' => 'new-password-456', 'password_confirmation' => 'new-password-456'])
            ->assertSessionHasErrors('current_password');
        $this->put('/admin/account', ['current_password' => 'old-password-123', 'password' => 'new-password-456', 'password_confirmation' => 'new-password-456'])
            ->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('new-password-456', $this->admin->fresh()->password));

        $this->post('/admin/logout')->assertRedirect();
        $this->get('/admin')->assertRedirect();
        $this->post('/admin/login', ['email' => $this->admin->email, 'password' => 'new-password-456'])->assertRedirect('/admin');
    }

    protected function tearDown(): void
    {
        // Remove any images uploaded by these tests.
        foreach (File::glob(public_path('uploads/products/imou-ranger-2-wi-fi-camera-*')) as $file) {
            File::delete($file);
        }
        parent::tearDown();
    }
}
