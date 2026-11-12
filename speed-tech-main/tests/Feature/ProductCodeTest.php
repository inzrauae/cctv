<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\SkuGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_get_sequential_codes_per_category(): void
    {
        $cameras = Category::create(['name' => 'CCTV Cameras', 'sku_prefix' => 'CAM']);
        $drives = Category::create(['name' => 'Hard Drives', 'sku_prefix' => 'HDD']);

        $a = Product::create(['category_id' => $cameras->id, 'name' => 'Bullet camera']);
        $b = Product::create(['category_id' => $cameras->id, 'name' => 'Dome camera']);
        $c = Product::create(['category_id' => $drives->id, 'name' => '1TB drive']);

        $this->assertSame('CAM-00001', $a->sku);
        $this->assertSame('CAM-00002', $b->sku);
        $this->assertSame('HDD-00001', $c->sku);
    }

    public function test_numbers_are_not_reused_and_codes_do_not_change_on_edit(): void
    {
        $cameras = Category::create(['name' => 'CCTV Cameras', 'sku_prefix' => 'CAM']);
        $a = Product::create(['category_id' => $cameras->id, 'name' => 'Camera A']);
        $b = Product::create(['category_id' => $cameras->id, 'name' => 'Camera B']);

        $a->update(['name' => 'Camera A (renamed)']);
        $this->assertSame('CAM-00001', $a->fresh()->sku);

        $a->delete();
        $c = Product::create(['category_id' => $cameras->id, 'name' => 'Camera C']);
        $this->assertSame('CAM-00003', $c->sku); // continues after the highest code
        $this->assertSame('CAM-00002', $b->fresh()->sku);
    }

    public function test_a_typed_code_is_kept_and_uncategorised_products_get_a_general_code(): void
    {
        $typed = Product::create(['name' => 'Sign board', 'sku' => ' my-code-1 ']);
        $general = Product::create(['name' => 'Misc item']);

        $this->assertSame('MY-CODE-1', $typed->sku);
        $this->assertSame('GEN-00001', $general->sku);
    }

    public function test_new_categories_get_a_unique_prefix(): void
    {
        $speaker = Category::create(['name' => 'Speaker']);
        $other = Category::create(['name' => 'Speakers']);

        $this->assertSame('SPK', $speaker->sku_prefix);
        $this->assertNotSame('SPK', $other->sku_prefix);
        $this->assertSame('MNT', SkuGenerator::derivePrefix('Monitor'));
    }

    public function test_admin_form_generates_code_when_left_blank(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $recorders = Category::create(['name' => 'Recorders', 'sku_prefix' => 'REC']);

        $this->actingAs($admin)->post('/admin/products', [
            'name' => '4-channel DVR', 'category_id' => $recorders->id,
            'oem_part_number' => 'DVR-4CH', 'sku' => '', 'stock_qty' => 0, 'is_active' => 1,
        ])->assertRedirect();

        $this->assertSame('REC-00001', Product::where('name', '4-channel DVR')->value('sku'));
    }

    public function test_admin_cannot_reuse_a_category_code(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        Category::create(['name' => 'CCTV Cameras', 'sku_prefix' => 'CAM']);

        $this->actingAs($admin)->post('/admin/catalog/categories', ['name' => 'Webcams', 'sku_prefix' => 'cam'])
            ->assertSessionHasErrors('sku_prefix');
    }

    public function test_command_fills_missing_codes(): void
    {
        $cameras = Category::create(['name' => 'CCTV Cameras', 'sku_prefix' => 'CAM']);
        $p = Product::create(['category_id' => $cameras->id, 'name' => 'Camera']);
        Product::whereKey($p->id)->update(['sku' => null]);

        $this->artisan('products:codes')->assertSuccessful();

        $this->assertSame('CAM-00001', $p->fresh()->sku);
    }
}
