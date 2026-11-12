<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    private Product $battery;
    private Product $display;
    private Product $onRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $cameras = Category::create(['name' => 'CCTV Cameras', 'icon' => 'camera']);
        $recorders = Category::create(['name' => 'DVR & NVR Recorders', 'icon' => 'storage']);

        $this->battery = Product::create(['category_id' => $cameras->id, 'name' => '2MP HD bullet camera', 'oem_part_number' => 'BULLET-2MP', 'price' => 12500, 'stock_qty' => 3]);
        $this->display = Product::create(['category_id' => $recorders->id, 'name' => '4-channel DVR recorder', 'price' => 15000, 'stock_qty' => 1]);
        $this->onRequest = Product::create(['category_id' => $recorders->id, 'name' => '16-channel NVR recorder', 'price' => null, 'stock_qty' => 0]);
    }

    public function test_pages_render(): void
    {
        $this->get('/')->assertOk()->assertSee('Your trusted');
        $this->get('/shop')->assertOk();
        $this->get('/spare-parts')->assertRedirect('/shop');
        $this->get('/products/'.$this->battery->slug)->assertOk()->assertSee('BULLET-2MP')->assertSee('CCTV Cameras');
        $this->get('/cart')->assertOk()->assertSee('Your cart is empty');
        $this->get('/repairs')->assertOk()->assertSee('CCTV installation')->assertSee('Smart door locks')->assertSee('Battery backup systems')->assertSee('Appliance repair &amp; A/C', false)
            ->assertSee('What areas do you cover?')->assertSee('data-service="CCTV installation"', false);
    }

    public function test_home_content_sections_and_benefits_strip(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Featured products')
            ->assertSee('Shop by category')
            ->assertSee('Why choose Last Solution?')
            ->assertSee('CCTV installation')
            ->assertDontSee('Find a part for your laptop.')
            ->assertSee('id="contact"', false);

        // Main navigation: services first, then the shop (labelled "Shop").
        $this->get('/')->assertSeeInOrder(['>Our services</a>', '>Shop</a>'], false)->assertDontSee('Shop CCTV');

        // Footer service links are sitewide.
        $this->get('/shop')->assertSee('CCTV installation');
    }

    public function test_home_content_sections(): void
    {
        $this->get('/')->assertOk()
            ->assertSeeInOrder(['Shop by category', 'Get a quote for your project.', 'Featured products', 'Our services', 'Shop products by type', 'Why choose Last Solution?', 'Shop by brand', 'Simple tips that save you trouble', 'Frequently asked questions'])
            ->assertSee('data-filter="appliance"', false)->assertSee('Not sure what you need?')
            ->assertSee('class="service-slide', false)->assertSee('data-track', false)->assertSee('aria-label="Next services"', false);
        $this->assertSame(count(config('shop.repair_services')), substr_count($this->get('/')->getContent(), 'class="service-slide'));
        $this->get('/')
            ->assertSee(route('repairs.show', 'solar-services'), false)
            ->assertSee('How many cameras do I need?')
            ->assertSee("Can't find what you need?", false)->assertSee('Expert installation')
            ->assertSee('System not working?')->assertSee('Not sure what you need?')
            ->assertSee('Keep recording in power cuts')->assertSee(route('repairs.show', 'battery-backup'), false)
            ->assertSee('https://www.tiktok.com/@last_solution_pvt_ltd', false);

        // CCTV deep-dive sections live on the CCTV installation page.
        $this->get('/repairs/cctv-installation')->assertOk()
            ->assertSee('Security solutions for every property')->assertSee('Construction sites')
            ->assertSee('Which camera is right for you?')->assertSee(route('shop', ['q' => 'bullet']), false)
            ->assertSee('CCTV packages, fully installed')->assertSee('Recommended for shops')
            ->assertSee('in your pocket.')->assertSee('4 cameras online')->assertSee('Motion alerts');
        $this->get('/repairs/solar-services')->assertOk()->assertDontSee('Which camera is right for you?');
    }

    public function test_brand_filter_and_service_families(): void
    {
        $this->battery->update(['brand' => 'Hikvision']);
        $this->display->update(['brand' => 'Dahua']);

        $this->get('/shop?brand=Hikvision')->assertOk()->assertSee('2MP HD bullet camera')->assertDontSee('4-channel DVR recorder')
            ->assertSee('Brands');
        $this->get('/shop?brand=NotABrand')->assertOk()->assertSee('4-channel DVR recorder');
        $this->get('/shop?q=dahua')->assertOk()->assertSee('4-channel DVR recorder')->assertDontSee('2MP HD bullet camera');

        $this->get('/')->assertOk()->assertSee('Shop by brand')->assertSee('Other brand?')->assertSee(route('shop', ['brand' => 'TP-Link']), false)
            ->assertSee('Our services')->assertSee('Washing machine repair')->assertSee('Battery backup systems');

        $this->get('/repairs/led-sign-boards')->assertRedirect('/repairs');
        foreach (['alarm-systems', 'smart-door-locks', 'intercom-systems', 'networking', 'wifi-solutions', 'smart-home', 'refrigerator-repair', 'washing-machine-repair', 'battery-backup'] as $slug) {
            $this->get('/repairs/'.$slug)->assertOk();
        }
    }

    public function test_about_and_contact_pages(): void
    {
        $this->get('/about')->assertOk()->assertSee('Solutions for your home and business.')
            ->assertSee('air-conditioning')->assertSee('Solar &amp; power backup', false)->assertSee('TP-Link');
        $this->get('/contact')->assertOk()->assertSee('Send us a message')->assertSee('output=embed', false)
            ->assertSee('class="mobile-actions"', false)->assertSee('href="tel:', false)
            ->assertSee('https://www.facebook.com/LASTSOLUTION1/', false)
            ->assertSee('https://www.tiktok.com/@last_solution_pvt_ltd', false)
            ->assertDontSee('href="https://share.google/', false);
    }

    public function test_contact_form_creates_general_enquiry(): void
    {
        $this->postJson('/enquiries', ['type' => 'general', 'name' => 'Sunil', 'phone' => '0771234567'])
            ->assertStatus(422)->assertJsonValidationErrors('message');

        $this->postJson('/enquiries', ['type' => 'general', 'name' => 'Sunil', 'phone' => '0771234567', 'message' => 'Do you sell solar panels?'])
            ->assertCreated();
        $this->assertSame('general', Enquiry::latest('id')->first()->type);
    }

    public function test_repair_service_pages(): void
    {
        $this->get('/')->assertOk()->assertSee('Featured products')
            ->assertSee(route('repairs.show', 'cctv-installation'), false);

        foreach (config('shop.repair_services') as $service) {
            $this->get('/repairs/'.$service['slug'])->assertOk()
                ->assertSee($service['name'])->assertSee($service['signs'][0])
                ->assertSee('data-service="'.e($service['name']).'"', false);
        }

        $this->get('/repairs/not-a-service')->assertNotFound();
    }

    public function test_shop_filters_by_category_and_searches_by_model_number(): void
    {
        $recorders = Category::where('name', 'DVR & NVR Recorders')->first();

        $this->get('/shop')->assertOk()->assertSee('2MP HD bullet camera')->assertSee('4-channel DVR recorder');
        $this->get('/shop?category='.$recorders->slug)->assertOk()
            ->assertSee('4-channel DVR recorder')->assertSee('16-channel NVR recorder')->assertDontSee('2MP HD bullet camera');
        $this->get('/shop?q=BULLET-2MP')->assertOk()->assertSee('2MP HD bullet camera')->assertDontSee('4-channel DVR recorder');
        $this->get('/shop?in_stock=1')->assertOk()->assertDontSee('16-channel NVR recorder');
    }

    public function test_home_shows_featured_products_and_categories(): void
    {
        $this->battery->update(['is_featured' => true]);

        $this->get('/')->assertOk()->assertSee('2MP HD bullet camera')
            ->assertSee(route('shop', ['category' => 'cctv-cameras']), false);
    }

    public function test_cart_caps_quantity_at_stock_and_rejects_items_without_price(): void
    {
        $this->postJson('/cart/items', ['product_id' => $this->battery->id, 'quantity' => 2])
            ->assertOk()->assertJsonPath('cart.count', 2);
        $this->postJson('/cart/items', ['product_id' => $this->battery->id, 'quantity' => 5])
            ->assertOk()->assertJsonPath('cart.count', 3);

        $this->postJson('/cart/items', ['product_id' => $this->onRequest->id])->assertStatus(422);
    }

    public function test_checkout_places_order_and_reserves_stock(): void
    {
        $this->post('/cart/items', ['product_id' => $this->battery->id, 'quantity' => 2]);

        $response = $this->post('/checkout', [
            'customer_name' => 'Nimal Perera', 'phone' => '077 123 4567',
            'fulfilment' => 'delivery', 'address' => '12 Main St', 'city' => 'Malabe',
            'payment_method' => 'cod',
        ]);

        $order = Order::with('items')->firstOrFail();
        $response->assertRedirect();
        $this->assertStringContainsString('/orders/'.$order->number, $response->headers->get('Location'));
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertEquals(25000, (float) $order->subtotal);
        $this->assertEquals(25000 + (float) $order->delivery_fee, (float) $order->total);
        $this->assertSame(1, $this->battery->fresh()->stock_qty);

        $this->get($response->headers->get('Location'))->assertOk()->assertSee($order->number);
        $this->get('/orders/'.$order->number)->assertForbidden(); // unsigned link
        $this->get('/cart')->assertSee('Your cart is empty');
    }

    public function test_checkout_rejects_when_stock_ran_out(): void
    {
        $this->post('/cart/items', ['product_id' => $this->display->id]);
        $this->display->update(['stock_qty' => 0]);

        $this->post('/checkout', [
            'customer_name' => 'A', 'phone' => '0771234567', 'fulfilment' => 'pickup', 'payment_method' => 'pay_at_store',
        ]);

        $this->assertSame(0, Order::count());
    }

    public function test_delivery_requires_address(): void
    {
        $this->post('/cart/items', ['product_id' => $this->battery->id]);
        $this->post('/checkout', ['customer_name' => 'A', 'phone' => '0771234567', 'fulfilment' => 'delivery', 'payment_method' => 'cod'])
            ->assertSessionHasErrors(['address', 'city']);
    }

    public function test_repair_and_part_enquiries_are_stored(): void
    {
        $this->postJson('/enquiries', [
            'type' => 'repair', 'name' => 'Kamal', 'phone' => '0771234567',
            'device' => 'Retail shop, Nugawela', 'service' => 'CCTV installation',
            'message' => 'Need cameras at the entrance and cashier area',
        ])->assertCreated()->assertJsonStructure(['reference', 'whatsapp']);

        $this->postJson('/enquiries', [
            'type' => 'part', 'name' => 'Kamal', 'phone' => '0771234567',
            'details' => ['part_number' => '16-channel NVR'], 'message' => 'For an office',
        ])->assertCreated();

        $this->assertSame(2, Enquiry::count());
        $this->assertSame('16-channel NVR', Enquiry::where('type', 'part')->first()->details['part_number']);
    }

    public function test_enquiry_validation_and_honeypot(): void
    {
        $this->postJson('/enquiries', ['type' => 'repair', 'name' => 'X', 'phone' => 'abc'])
            ->assertStatus(422)->assertJsonValidationErrors(['phone', 'device', 'message']);

        $this->postJson('/enquiries', ['type' => 'general', 'name' => 'Bot', 'phone' => '0771234567', 'website' => 'spam'])
            ->assertStatus(422);
    }
}
