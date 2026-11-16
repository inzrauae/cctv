<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Services\GoogleReviews;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(GoogleReviews $google): View
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
            'reviews' => $this->reviews($google),
            'categories' => Category::active()->withCount(['products' => fn ($q) => $q->active()])->get(),
        ]);
    }

    /**
     * Reviews for the home page: the ones saved on this site (imported from Google Takeout or
     * added in the admin) first; otherwise live ones from the Google Places API when a key is set.
     */
    private function reviews(GoogleReviews $google): ?array
    {
        $stats = Review::visible()->selectRaw('count(*) as total, avg(rating) as average')->first();
        if ((int) $stats->total === 0) {
            return $google->get();
        }

        return [
            'rating' => round((float) $stats->average, 1),
            'count' => (int) $stats->total,
            'url' => route('reviews'),
            'reviews' => Review::forDisplay()->whereNotNull('text')->take(6)->get()->map(fn (Review $review) => [
                'author' => $review->author_name,
                'author_url' => null,
                'photo' => $review->author_photo_url,
                'rating' => $review->rating,
                'text' => $review->text,
                'when' => $review->when(),
                'url' => null,
            ])->all(),
        ];
    }
}
