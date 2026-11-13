<?php

use App\Models\Enquiry;
use App\Models\Order;
use App\Models\Product;
use App\Services\ImageVariants;
use App\Services\SkuGenerator;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('demo:clear', function () {
    $orders = Order::where('number', 'like', 'DEMO-%')->count();
    $enquiries = Enquiry::where('reference', 'like', 'DEMO-%')->count();

    if (! $this->confirm("Delete {$orders} sample orders and {$enquiries} sample enquiries?", true)) {
        return;
    }

    Order::where('number', 'like', 'DEMO-%')->delete();        // order items cascade
    Enquiry::where('reference', 'like', 'DEMO-%')->delete();
    $this->info('Sample orders and enquiries removed. Real orders were not touched.');
})->purpose('Remove the sample (DEMO-) orders and enquiries');

Artisan::command('products:codes {--all : Replace every existing code, not just fill missing ones}', function (SkuGenerator $generator) {
    $query = Product::orderBy('id');
    if (! $this->option('all')) {
        $query->where(fn ($q) => $q->whereNull('sku')->orWhere('sku', ''));
    }
    $products = $query->get();

    if ($products->isEmpty()) {
        $this->info('Every product already has a code.');

        return;
    }
    if ($this->option('all') && ! $this->confirm("Replace the codes of all {$products->count()} products? Codes on past orders are kept as they were.", true)) {
        return;
    }

    if ($this->option('all')) {
        Product::whereKey($products->modelKeys())->update(['sku' => null]); // free the old codes first
    }

    $rows = [];
    foreach ($products as $product) {
        $product->sku = $generator->for($product);
        $product->saveQuietly();
        $rows[] = [$product->sku, $product->oem_part_number ?: '-', Str::limit($product->name, 50)];
    }
    $this->table(['Code', 'OEM P/N', 'Product'], $rows);
    $this->info(count($rows).' product codes generated.');
})->purpose('Generate product codes (SKUs) like LCD-00012 for products that have none');

Artisan::command('images:variants {--force : Rebuild variants that already exist}', function () {
    // Hero photos: built from the .webp (the .jpg is only a fallback src).
    // Products, uploads and logos: built from whatever format the file is in.
    $files = collect()
        ->merge(File::glob(public_path('assets/hero/*.webp')))
        ->merge(File::glob(public_path('assets/products/*.{jpg,jpeg,png,webp}'), GLOB_BRACE))
        ->merge(File::glob(public_path('uploads/products/*.{jpg,jpeg,png,webp}'), GLOB_BRACE))
        ->merge([public_path('assets/last-solution-logo.png'), public_path('assets/last-solution-logo-light.png'), public_path('assets/last-solution-facebook.jpg')])
        ->reject(fn ($path) => ImageVariants::isVariant($path));

    $written = 0;
    foreach ($files as $path) {
        $written += ImageVariants::generate(ltrim(str_replace(DIRECTORY_SEPARATOR, '/', Str::after($path, public_path())), '/'), (bool) $this->option('force'));
    }
    $this->info("Checked {$files->count()} images, wrote {$written} smaller copies.");
})->purpose('Create small WebP copies of hero, product and logo images for phones');
