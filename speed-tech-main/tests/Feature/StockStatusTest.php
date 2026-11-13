<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockStatusTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes): Product
    {
        $category = Category::firstOrCreate(['name' => 'Accessories']);

        return Product::create($attributes + ['category_id' => $category->id, 'price' => 5000, 'is_active' => true]);
    }

    public function test_labels_for_each_stock_state(): void
    {
        $in = $this->product(['name' => 'Camera', 'stock_qty' => 10]);
        $low = $this->product(['name' => 'Bracket', 'stock_qty' => 2]);
        $soon = $this->product(['name' => 'Junction box', 'stock_qty' => 0, 'restock_soon' => true]);
        $available = $this->product(['name' => 'Old adapter', 'stock_qty' => 0]);
        $noPrice = $this->product(['name' => 'Custom cable', 'stock_qty' => 0, 'price' => null]);

        $this->assertSame(['in', 'In stock'], [$in->stockState(), $in->stockLabel()]);
        $this->assertSame(['low', 'Only 2 left'], [$low->stockState(), $low->stockLabel()]);
        $this->assertSame(['soon', 'Stock coming soon'], [$soon->stockState(), $soon->stockLabel()]);
        $this->assertSame(['available', 'Available'], [$available->stockState(), $available->stockLabel()]);
        $this->assertSame(['out', null], [$noPrice->stockState(), $noPrice->stockLabel()]);
        $this->assertSame('Ask when it arrives', $soon->enquiryLabel());
        $this->assertSame('Check availability', $available->enquiryLabel());
    }

    public function test_shop_shows_green_and_coming_soon_but_hides_status_without_price(): void
    {
        $this->product(['name' => 'Junction box', 'stock_qty' => 0, 'restock_soon' => true]);
        $this->product(['name' => 'Old adapter', 'stock_qty' => 0]);
        $this->product(['name' => 'Custom cable', 'stock_qty' => 0, 'price' => null]);

        $this->get(route('shop'))->assertOk()
            ->assertSee('stock stock-available', false)
            ->assertSee('>Available<', false)
            ->assertSee('Stock coming soon')
            ->assertSee('Ask when it arrives')
            ->assertDontSee('stock stock-out', false)
            ->assertDontSee('Available to order');
    }

    public function test_restocked_product_drops_coming_soon_automatically(): void
    {
        $product = $this->product(['name' => 'Junction box', 'stock_qty' => 0, 'restock_soon' => true]);
        $product->update(['stock_qty' => 8]);

        $this->assertSame('In stock', $product->fresh()->stockLabel());
    }
}
