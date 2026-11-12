<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\CatalogStructureSeeder;
use Database\Seeders\ProductCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_real_catalogue_seeds_with_images_and_is_idempotent(): void
    {
        $this->seed([CatalogStructureSeeder::class, ProductCatalogSeeder::class]);
        $this->seed(ProductCatalogSeeder::class);

        $this->assertSame(18, Product::count());
        $this->assertSame(10, Product::whereHas('category', fn ($q) => $q->where('slug', 'cctv-packages'))->count());
        $this->assertNull(Product::whereNull('category_id')->first());

        foreach (Product::all() as $product) {
            $this->assertFileExists(public_path($product->image), $product->name);
        }

        $package = Product::where('price', 42500)->firstOrFail();
        $this->get('/products/'.$package->slug)->assertOk()->assertSee('DS-2CE16D0T-EXIPF')->assertSee('500GB WD Purple');
        $this->get('/shop?brand=EZVIZ')->assertOk()->assertSee('EZVIZ DL05 Smart Fingerprint Lock')->assertDontSee('Dahua Smart Full Colour');
    }
}
