<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderPlacer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderWithoutStockCountTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes): Product
    {
        $category = Category::firstOrCreate(['name' => 'CCTV Packages']);

        return Product::create($attributes + ['category_id' => $category->id, 'name' => 'Package', 'price' => 42500, 'is_active' => true]);
    }

    private function checkout(): void
    {
        $this->post('/checkout', [
            'customer_name' => 'Nimal', 'phone' => '0771234567', 'fulfilment' => 'pickup', 'payment_method' => 'pay_at_store',
        ]);
    }

    public function test_priced_product_without_stock_count_shows_add_to_cart_and_install_note(): void
    {
        $this->product(['stock_qty' => 0]);

        $this->get(route('shop'))->assertOk()
            ->assertSee('Add to cart')
            ->assertSee('Installation cost depends on distance')
            ->assertDontSee('Check availability');
    }

    public function test_coming_soon_and_unpriced_products_stay_enquiry_only(): void
    {
        $soon = $this->product(['name' => 'Soon', 'stock_qty' => 0, 'restock_soon' => true]);
        $unpriced = $this->product(['name' => 'Quote', 'stock_qty' => 0, 'price' => null]);

        $this->assertFalse($soon->isPurchasable());
        $this->assertFalse($unpriced->isPurchasable());
        $this->postJson('/cart/items', ['product_id' => $soon->id])->assertStatus(422);
    }

    public function test_ordering_without_stock_count_takes_no_stock_and_cancel_returns_none(): void
    {
        $product = $this->product(['stock_qty' => 0]);

        $this->post('/cart/items', ['product_id' => $product->id, 'quantity' => 3]);
        $this->checkout();

        $order = Order::with('items')->sole();
        $this->assertSame(3, $order->items->first()->quantity);
        $this->assertFalse($order->items->first()->stock_reserved);
        $this->assertSame(0, $product->fresh()->stock_qty);

        OrderPlacer::changeStatus($order, 'cancelled');
        $this->assertSame(0, $product->fresh()->stock_qty);
    }

    public function test_counted_stock_is_still_reserved_and_returned(): void
    {
        $product = $this->product(['stock_qty' => 5]);

        $this->post('/cart/items', ['product_id' => $product->id, 'quantity' => 2]);
        $this->checkout();
        $this->assertSame(3, $product->fresh()->stock_qty);

        OrderPlacer::changeStatus(Order::sole(), 'cancelled');
        $this->assertSame(5, $product->fresh()->stock_qty);
    }
}
