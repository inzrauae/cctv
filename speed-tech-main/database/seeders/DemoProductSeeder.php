<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;

/**
 * Illustrative products so the shop is not empty on day one.
 * Prices, stock and model numbers are SAMPLE values. Replace them in /admin
 * before going live, or skip this seeder in production.
 */
class DemoProductSeeder extends Seeder
{
    public function run(): void
    {
        $cat = Category::pluck('id', 'slug');

        // [name, category slug, tag, summary, specification, model number, price|null, stock, warranty, featured]
        $products = [
            ['4-camera HD CCTV package', 'cctv-packages', 'HOME & SHOP', 'Four night-vision cameras, recorder, hard drive and installation.', '4 × 2MP cameras, 4-channel DVR, 1TB HDD', null, null, 0, '1 year', true],
            ['8-camera HD CCTV package', 'cctv-packages', 'BUSINESS', 'Eight cameras for larger homes, shops and workplaces.', '8 × 2MP cameras, 8-channel DVR, 2TB HDD', null, null, 0, '1 year', true],
            ['4-camera colour night vision package', 'cctv-packages', 'FULL COLOUR AT NIGHT', 'Colour footage after dark with warm-light cameras.', '4 × colour night vision cameras, DVR, 1TB HDD', null, null, 0, '1 year', false],
            ['2MP HD bullet camera', 'cctv-cameras', 'OUTDOOR', 'Weatherproof bullet camera for gates, entrances and boundaries.', '1080p, IR night vision up to 20 m, IP66', 'BULLET-2MP', 6500, 12, '1 year', true],
            ['2MP HD dome camera', 'cctv-cameras', 'INDOOR', 'Compact dome camera for shops, offices and living areas.', '1080p, IR night vision up to 20 m', 'DOME-2MP', 5900, 10, '1 year', true],
            ['Colour night vision bullet camera', 'cctv-cameras', 'COLOUR AT NIGHT', 'Full-colour images at night using warm supplementary light.', '1080p, colour night vision, IP67', 'COLOR-2MP', 9800, 6, '1 year', false],
            ['4MP IP bullet camera (PoE)', 'cctv-cameras', 'IP CAMERA', 'Sharper 4MP detail for network (NVR) systems.', '4MP, PoE powered, IR up to 30 m', 'IPC-4MP', 14500, 4, '1 year', false],
            ['Wi-Fi pan & tilt camera', 'cctv-cameras', 'WIRELESS', 'Rotating indoor camera with two-way audio and phone viewing.', '1080p, Wi-Fi, two-way audio, microSD', 'WIFI-PT', 8900, 8, '1 year', true],
            ['4-channel DVR recorder', 'dvr-nvr-recorders', 'RECORDER', 'Records up to four HD cameras with mobile viewing.', '4-channel, 1080p, 1 SATA bay', 'DVR-4CH', 14900, 5, '1 year', false],
            ['8-channel DVR recorder', 'dvr-nvr-recorders', 'RECORDER', 'Records up to eight HD cameras with mobile viewing.', '8-channel, 1080p, 1 SATA bay', 'DVR-8CH', 21500, 3, '1 year', false],
            ['8-channel PoE NVR recorder', 'dvr-nvr-recorders', 'IP RECORDER', 'Network recorder with built-in PoE ports for IP cameras.', '8-channel, 8 PoE ports, 4K output', 'NVR-8P', null, 0, '1 year', false],
            ['1TB surveillance hard drive', 'surveillance-hard-drives', '24/7 RECORDING', 'Hard drive designed for continuous CCTV recording.', '1TB, 3.5" SATA, surveillance grade', 'HDD-1TB', 13500, 7, '2 years', false],
            ['2TB surveillance hard drive', 'surveillance-hard-drives', '24/7 RECORDING', 'More recording days for larger systems.', '2TB, 3.5" SATA, surveillance grade', 'HDD-2TB', 19500, 4, '2 years', false],
            ['CCTV coaxial cable with power (100 m)', 'cables-connectors', 'CABLING', 'Combined video and power cable for HD analogue cameras.', 'RG59 + power, 100 m roll', 'RG59-100', 9500, 10, null, false],
            ['CAT6 network cable (305 m)', 'cables-connectors', 'IP CABLING', 'Network cable for PoE IP camera installations.', 'CAT6 UTP, 305 m box', 'CAT6-305', 26500, 2, null, false],
            ['BNC & DC connector set (10 pairs)', 'cables-connectors', 'CONNECTORS', 'Twist-on BNC and DC power connectors.', '10 × BNC, 10 × DC male', 'BNC-DC10', 1500, 25, null, false],
            ['12V 10A CCTV power supply box', 'power-supplies', 'POWER', 'Central power supply for up to eight cameras.', '12V DC, 10A, 9-way output', 'PSU-10A', 6800, 6, '6 months', false],
            ['12V 2A camera adapter', 'power-supplies', 'POWER', 'Plug-in power adapter for a single camera.', '12V DC, 2A', 'ADP-2A', 950, 30, '3 months', false],
            ['Camera junction box', 'accessories', 'MOUNTING', 'Weatherproof base for a neat outdoor camera installation.', 'ABS, fits most bullet and dome cameras', 'JBOX', 1200, 20, null, false],
            ['CCTV warning sign board', 'accessories', 'DETERRENT', '"CCTV in operation" sign for gates and entrances.', 'Weatherproof, 30 × 20 cm', 'SIGN-CCTV', 750, 15, null, false],
            ['Dual-band Wi-Fi 6 router', 'network-wi-fi', 'FASTER WI-FI', 'Fast, stable Wi-Fi for homes and small offices.', 'Wi-Fi 6, dual-band, 4 antennas', 'ROUTER-AX', 14900, 6, '1 year', false],
            ['Whole-home mesh Wi-Fi (2-pack)', 'network-wi-fi', 'NO DEAD ZONES', 'Two mesh units that cover every room and floor.', 'Mesh Wi-Fi, 2 units, app control', 'MESH-2', 28500, 3, '1 year', true],
            ['8-port network switch', 'network-wi-fi', 'NETWORK', 'Connect cameras, computers and devices by cable.', '8 × Gigabit ports, desktop', 'SW-8G', 5200, 9, '1 year', false],
            ['Wireless alarm kit', 'alarm-systems', 'INTRUDER ALARM', 'Hub, siren and sensors with phone alerts.', 'Hub, siren, 2 door sensors, 1 motion sensor', 'ALARM-KIT', null, 0, '1 year', true],
            ['Fingerprint smart door lock', 'smart-door-locks', 'KEYLESS ENTRY', 'Open with fingerprint, PIN, card or key.', 'Fingerprint, PIN, card, backup key', 'LOCK-FP', 38500, 4, '1 year', true],
            ['Video door phone kit', 'intercom-systems', 'SEE WHO IS THERE', 'Door station and indoor screen to see and talk to visitors.', '7" indoor monitor, outdoor door station', 'VDP-7', 24900, 3, '1 year', false],
            ['Indoor smart Wi-Fi camera', 'smart-home-devices', 'SMART HOME', 'Plug-in camera with motion alerts and two-way talk.', '1080p, Wi-Fi, two-way audio, night vision', 'SMART-CAM', 6900, 10, '1 year', false],
            ['Inverter battery backup system', 'solar-battery-backup', 'POWER CUT READY', 'Keeps lights, Wi-Fi and CCTV running during power cuts.', 'Inverter with battery, sized to your load', 'UPS-HOME', null, 0, '1 year', false],
        ];

        // SAMPLE brands: name => brand.
        $brands = [
            '2MP HD bullet camera' => 'Hikvision', '2MP HD dome camera' => 'Dahua', 'Colour night vision bullet camera' => 'Hikvision',
            '4MP IP bullet camera (PoE)' => 'UNV', 'Wi-Fi pan & tilt camera' => 'EZVIZ', '4-channel DVR recorder' => 'Hikvision',
            '8-channel DVR recorder' => 'Dahua', '8-channel PoE NVR recorder' => 'UNV', 'Dual-band Wi-Fi 6 router' => 'TP-Link',
            'Whole-home mesh Wi-Fi (2-pack)' => 'TP-Link', '8-port network switch' => 'TP-Link', 'Wireless alarm kit' => 'EZVIZ',
            'Video door phone kit' => 'Hikvision', 'Indoor smart Wi-Fi camera' => 'Imou',
        ];

        // SAMPLE offers: name => offer price (must be lower than the regular price).
        $offers = [
            '2MP HD bullet camera' => 5490,
            'Wi-Fi pan & tilt camera' => 7490,
            '4-channel DVR recorder' => 12900,
            '1TB surveillance hard drive' => 11900,
            '12V 10A CCTV power supply box' => 5900,
        ];

        foreach ($products as [$name, $slug, $tag, $summary, $spec, $model, $price, $stock, $warranty, $featured]) {
            $before = Product::where('name', $name)->value('stock_qty');
            $product = Product::updateOrCreate(['name' => $name], [
                'category_id' => $cat[$slug] ?? null, 'brand' => $brands[$name] ?? null, 'tag' => $tag, 'summary' => $summary,
                'specification' => $spec, 'oem_part_number' => $model,
                'description' => "{$summary}\n\nNot sure what you need? Tell us about your property and we will recommend a suitable option. Installation by our team is available.",
                'price' => $price, 'offer_price' => $offers[$name] ?? null, 'stock_qty' => $stock, 'warranty' => $warranty, 'is_featured' => $featured,
                'cost_price' => $price ? round($price * 0.72, -1) : null, 'reorder_level' => 3,
            ]);
            $this->logStock($product, $before);
        }
    }

    /** Record the seeded stock level in the stock history (opening stock or a demo reset). */
    private function logStock(Product $product, ?int $before): void
    {
        $diff = $product->stock_qty - (int) $before;
        if ($diff === 0) {
            return;
        }
        StockMovement::create([
            'product_id' => $product->id,
            'type' => $before === null ? 'opening' : 'adjustment',
            'quantity' => $diff,
            'balance_after' => $product->stock_qty,
            'note' => $before === null ? 'Opening stock (sample data)' : 'Sample data reset',
        ]);
    }
}
