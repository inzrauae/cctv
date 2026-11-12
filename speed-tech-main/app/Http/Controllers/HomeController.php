<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featured = Product::active()->with('category')
            ->orderByDesc('is_featured')->latest('updated_at')->take(4)->get();

        $offers = Product::active()->onOffer()->with('category')
            ->orderByRaw('(price - offer_price) * 1.0 / price desc')->take(4)->get();

        $stats = Product::active()->onOffer()
            ->selectRaw('count(*) as total, max((price - offer_price) * 1.0 / price) as best, min(offer_ends_at) as ends')->first();
        $offerStats = [
            'count' => (int) $stats->total,
            'max' => (int) round(((float) $stats->best) * 100),
            'ends' => $stats->ends ? \Illuminate\Support\Carbon::parse($stats->ends) : null,
        ];

        // Shop by brand: product count and a few product names per configured brand.
        $brandProducts = Product::active()->whereIn('brand', config('shop.brands'))
            ->orderByDesc('is_featured')->orderBy('name')->get(['name', 'brand', 'slug'])->groupBy('brand');

        return view('home', [
            'offerStats' => $offerStats,
            'brandProducts' => $brandProducts,
            'featured' => $featured,
            'offers' => $offers,
            'categories' => Category::active()->withCount(['products' => fn ($q) => $q->active()])->get(),
        ]);
    }
}
