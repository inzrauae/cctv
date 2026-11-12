<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

/** Every product with a running offer, biggest discount first. */
class OfferController extends Controller
{
    public function __invoke(): View
    {
        $products = Product::active()->onOffer()->with('category')
            ->orderByRaw('(price - offer_price) * 1.0 / price desc')
            ->orderBy('name')
            ->paginate(16);

        return view('offers', compact('products'));
    }
}
