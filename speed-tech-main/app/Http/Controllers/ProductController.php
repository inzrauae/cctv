<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load('category', 'subcategory');

        $related = Product::active()->with('category')
            ->whereKeyNot($product->id)
            ->where('category_id', $product->category_id)
            ->inRandomOrder()->take(4)->get();

        return view('product', compact('product', 'related'));
    }
}
