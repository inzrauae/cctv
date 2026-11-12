<?php

namespace Database\Seeders;

use App\Models\Enquiry;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Sample orders and enquiries so the admin panel can be demonstrated.
 * Every record is marked as a sample (numbers start with DEMO-).
 * Remove them all with:  php artisan demo:clear
 * Does not change product stock. Safe to re-run (skips if samples exist).
 */
class DemoActivitySeeder extends Seeder
{
    public function run(): void
    {
        if (Order::where('number', 'like', 'DEMO-%')->exists()) {
            $this->command?->info('Sample orders already exist; skipped.');

            return;
        }

        $products = Product::whereNotNull('price')->get()->keyBy('name');
        $line = function (string $name, int $qty) use ($products): ?array {
            $p = $products->get($name);

            return $p ? [
                'product_id' => $p->id, 'product_name' => $p->name, 'sku' => $p->sku,
                'oem_part_number' => $p->oem_part_number, 'unit_price' => $p->sellingPrice(),
                'quantity' => $qty, 'line_total' => $p->sellingPrice() * $qty,
            ] : null;
        };

        $orders = [
            ['Nimal Perera (sample)', '0771234501', 'delivery', 'cod', 'pending', 'Katugastota', 0, [['2MP HD bullet camera', 2]], 'Please call before delivery.'],
            ['Kasuni Fernando (sample)', '0712345602', 'pickup', 'pay_at_store', 'confirmed', null, 1, [['4-channel DVR recorder', 1], ['1TB surveillance hard drive', 1]], 'Need installation as well.'],
            ['Ruwan Silva (sample)', '0763456703', 'delivery', 'bank_transfer', 'shipped', 'Peradeniya', 2, [['Wi-Fi pan & tilt camera', 2]], null],
            ['Dilani Jayasinghe (sample)', '0754567804', 'pickup', 'pay_at_store', 'ready', null, 3, [['12V 10A CCTV power supply box', 1]], null],
            ['Chamara Bandara (sample)', '0705678905', 'delivery', 'cod', 'completed', 'Kandy', 6, [['2MP HD dome camera', 1], ['CCTV coaxial cable with power (100 m)', 1]], null],
            ['Ishara Wickramasinghe (sample)', '0786789006', 'pickup', 'pay_at_store', 'cancelled', null, 9, [['CCTV warning sign board', 1]], 'Customer changed mind.'],
        ];

        foreach ($orders as $i => [$name, $phone, $fulfilment, $payment, $status, $city, $daysAgo, $items, $notes]) {
            $lines = array_values(array_filter(array_map(fn ($item) => $line(...$item), $items)));
            if (! $lines) {
                continue;
            }
            $subtotal = array_sum(array_column($lines, 'line_total'));
            $fee = $fulfilment === 'delivery' && $subtotal < (float) config('shop.free_delivery_over') ? (float) config('shop.delivery_fee') : 0;
            $when = now()->subDays($daysAgo)->subHours($i * 2);

            $order = new Order([
                'number' => 'DEMO-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'customer_name' => $name, 'phone' => $phone, 'email' => null,
                'fulfilment' => $fulfilment, 'payment_method' => $payment, 'status' => $status,
                'address' => $fulfilment === 'delivery' ? 'No. '.(10 + $i).', Sample Road' : null,
                'city' => $city, 'subtotal' => $subtotal, 'delivery_fee' => $fee, 'total' => $subtotal + $fee,
                'notes' => $notes, 'admin_notes' => 'Sample order for demonstration. Remove with: php artisan demo:clear',
            ]);
            $order->created_at = $order->updated_at = $when;
            $order->save();
            $order->items()->createMany($lines);
        }

        $enquiries = [
            ['repair', 'Saman Kumara (sample)', '0771112201', 'House in Nugawela', 'CCTV installation', null, 'Need 4 cameras covering the gate, garage and back garden.', 'new', 0],
            ['repair', 'Tharushi Peiris (sample)', '0712223302', 'Shop in Kandy town', 'CCTV repair & service', null, 'Two cameras show no picture at night.', 'contacted', 1],
            ['part', 'Asanka Rathnayake (sample)', '0763334403', 'Office, Katugastota', null, ['part_number' => '16-channel NVR'], 'Looking for a 16-channel NVR for an office.', 'new', 0],
            ['product', 'Malsha Dissanayake (sample)', '0754445504', null, null, null, 'Do you have this in stock this week?', 'new', 2],
            ['general', 'Pradeep Gunawardena (sample)', '0705556605', null, null, null, 'Can you service an A/C unit at our office?', 'closed', 5],
        ];
        $enquiryProduct = Product::where('name', '8-channel PoE NVR recorder')->first();

        foreach ($enquiries as $i => [$type, $name, $phone, $device, $service, $details, $message, $status, $daysAgo]) {
            $enquiry = new Enquiry([
                'reference' => 'DEMO-E'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'type' => $type, 'name' => $name, 'phone' => $phone, 'device' => $device,
                'service' => $service, 'details' => $details, 'message' => $message, 'status' => $status,
                'product_id' => $type === 'product' ? $enquiryProduct?->id : null,
                'admin_notes' => 'Sample enquiry for demonstration.',
            ]);
            $enquiry->created_at = $enquiry->updated_at = now()->subDays($daysAgo)->subHours($i * 3);
            $enquiry->save();
        }

        $this->command?->info('Added '.count($orders).' sample orders and '.count($enquiries).' sample enquiries.');
    }
}
