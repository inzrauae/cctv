<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferTest extends TestCase
{
    use RefreshDatabase;

    private Category $cameras;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cameras = Category::create(['name' => 'CCTV Cameras']);
    }

    private function product(array $attributes): Product
    {
        return Product::create($attributes + ['category_id' => $this->cameras->id, 'stock_qty' => 5, 'is_active' => true]);
    }

    public function test_offer_rules(): void
    {
        $live = $this->product(['name' => 'Bullet', 'price' => 10000, 'offer_price' => 8500]);
        $endsToday = $this->product(['name' => 'Dome', 'price' => 10000, 'offer_price' => 9000, 'offer_ends_at' => today()]);
        $expired = $this->product(['name' => 'Old deal', 'price' => 10000, 'offer_price' => 9000, 'offer_ends_at' => today()->subDay()]);
        $notLower = $this->product(['name' => 'Odd', 'price' => 10000, 'offer_price' => 12000]);
        $plain = $this->product(['name' => 'Plain', 'price' => 10000]);

        $this->assertTrue($live->hasOffer());
        $this->assertSame(8500.0, $live->sellingPrice());
        $this->assertSame(15, $live->discountPercent());
        $this->assertSame(1500.0, $live->savings());
        $this->assertTrue($endsToday->hasOffer());
        $this->assertFalse($expired->hasOffer());
        $this->assertSame(10000.0, $expired->sellingPrice());
        $this->assertFalse($notLower->hasOffer());
        $this->assertFalse($plain->hasOffer());

        $this->assertEqualsCanonicalizing(['Bullet', 'Dome'], Product::onOffer()->pluck('name')->all());
    }

    public function test_offers_section_tab_and_page_appear_only_with_offers(): void
    {
        $this->product(['name' => 'Plain camera', 'price' => 10000]);

        $this->get('/')->assertOk()->assertDontSee('id="offers-title"', false)->assertDontSee('class="nav-offers"', false);
        $this->get('/offers')->assertOk()->assertSee('No offers right now');

        $this->product(['name' => 'Deal camera', 'price' => 10000, 'offer_price' => 7500]);

        $this->get('/')->assertOk()->assertSee('id="offers-title"', false)->assertSee('class="nav-offers"', false)
            ->assertSeeInOrder(['class="security-hero photo-hero"', 'class="cat-strip"', 'data-quote-finder', 'class="home-offers"'], false)
            ->assertSee('-25%')->assertSee('Rs. 7,500')->assertSee('Rs. 10,000');
        $this->get('/offers')->assertOk()->assertSee('Deal camera')->assertDontSee('Plain camera');
        $this->get('/')->assertSee('Save up to')->assertSee('25<span>%</span>', false);

        $this->product(['name' => 'Bigger deal', 'price' => 10000, 'offer_price' => 6000]);
        $this->get('/offers')->assertSeeInOrder(['Bigger deal', 'Deal camera']);
        $this->get('/')->assertSee('40<span>%</span>', false);
        $this->get('/products/deal-camera')->assertOk()->assertSee('You save Rs. 2,500');
    }

    public function test_cart_and_checkout_charge_the_offer_price(): void
    {
        $deal = $this->product(['name' => 'Deal camera', 'price' => 10000, 'offer_price' => 7500]);

        $this->postJson('/cart/items', ['product_id' => $deal->id, 'quantity' => 2])->assertOk();
        $this->post('/checkout', ['customer_name' => 'A', 'phone' => '0771234567', 'fulfilment' => 'pickup', 'payment_method' => 'pay_at_store'])
            ->assertRedirect();

        $order = Order::with('items')->firstOrFail();
        $this->assertEquals(15000, (float) $order->subtotal);
        $this->assertEquals(7500, (float) $order->items->first()->unit_price);
    }

    public function test_shop_sorts_by_the_price_customers_pay(): void
    {
        $this->product(['name' => 'Regular 9000', 'price' => 9000]);
        $this->product(['name' => 'Offer 7000', 'price' => 12000, 'offer_price' => 7000]);

        $this->get('/shop?sort=price_asc')->assertOk()->assertSeeInOrder(['Offer 7000', 'Regular 9000']);
    }

    public function test_admin_sets_and_validates_offer_price(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin);

        $this->post('/admin/products', ['name' => 'Bad offer', 'category_id' => $this->cameras->id, 'price' => 5000, 'offer_price' => 6000, 'stock_qty' => 1])
            ->assertSessionHasErrors('offer_price');

        $this->post('/admin/products', ['name' => 'Good offer', 'category_id' => $this->cameras->id, 'price' => 5000, 'offer_price' => 4500,
            'offer_ends_at' => today()->addWeek()->toDateString(), 'stock_qty' => 1, 'is_active' => 1])
            ->assertSessionDoesntHaveErrors();

        $product = Product::where('name', 'Good offer')->firstOrFail();
        $this->assertTrue($product->hasOffer());
        $this->get('/admin/products?status=offer')->assertOk()->assertSee('Good offer')->assertSee('Offer -10%');
        $this->get('/admin/products/'.$product->slug.'/edit')->assertOk()->assertSee('Offer is live: -10%.');
    }
}
