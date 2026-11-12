<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Shop categories for CCTV, security, smart home, network and power products. Safe to re-run (matches on slug).
 * The code is the start of every product code in that category (e.g. CAM-00001).
 */
class CatalogStructureSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // [name, icon, product-code prefix]
            ['CCTV Packages', 'shield', 'PKG'],
            ['CCTV Cameras', 'camera', 'CAM'],
            ['DVR & NVR Recorders', 'storage', 'REC'],
            ['Surveillance Hard Drives', 'storage', 'HDD'],
            ['Network & Wi-Fi', 'wifi', 'NET'],
            ['Alarm Systems', 'bell', 'ALM'],
            ['Smart Door Locks', 'lock', 'LCK'],
            ['Intercom Systems', 'intercom', 'ICM'],
            ['Smart Home Devices', 'smarthome', 'SMH'],
            ['Solar & Battery Backup', 'battery', 'SOL'],
            ['Cables & Connectors', 'tag', 'CAB'],
            ['Power Supplies', 'bolt', 'PWR'],
            ['Accessories', 'box', 'ACC'],
        ];

        foreach ($categories as $i => [$name, $icon, $prefix]) {
            $category = Category::firstOrNew(['slug' => str($name)->slug()->value()]);
            $category->fill(['name' => $name, 'icon' => $icon, 'sort_order' => $i]);
            if (! $category->exists && ! Category::where('sku_prefix', $prefix)->exists()) {
                $category->sku_prefix = $prefix;
            }
            $category->save();
        }
    }
}
