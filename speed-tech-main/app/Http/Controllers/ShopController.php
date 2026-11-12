<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CCTV equipment shop: browse by category, or search by name, product code or model number.
 */
class ShopController extends Controller
{
    public const SORTS = [
        'featured' => 'Featured',
        'newest' => 'Newest',
        'price_asc' => 'Price: low to high',
        'price_desc' => 'Price: high to low',
        'name' => 'Name A–Z',
    ];

    public function __invoke(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:60'],
            'sort' => ['nullable', 'in:'.implode(',', array_keys(self::SORTS))],
        ]);

        $categories = Category::active()->withCount(['products' => fn ($q) => $q->active()])->get();
        $category = $categories->firstWhere('slug', $request->query('category'));
        $search = trim((string) $request->query('q'));
        $sort = $request->query('sort', 'featured');
        $inStock = $request->boolean('in_stock');
        $brand = in_array($request->query('brand'), config('shop.brands'), true) ? $request->query('brand') : null;
        $brands = Product::active()->whereIn('brand', config('shop.brands'))
            ->selectRaw('brand, count(*) as total')->groupBy('brand')->pluck('total', 'brand');

        $query = Product::active()->with('category')->search($search);

        if ($category) {
            $query->where('category_id', $category->id);
        }
        if ($brand) {
            $query->where('brand', $brand);
        }
        if ($inStock) {
            $query->where('stock_qty', '>', 0);
        }

        match ($sort) {
            'newest' => $query->latest(),
            // Priced items first, then by price; "price on request" items go last.
            'price_asc' => $query->orderByRaw('price is null')->orderByRaw(Product::sellingPriceSql().' asc'),
            'price_desc' => $query->orderByRaw('price is null')->orderByRaw(Product::sellingPriceSql().' desc'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('is_featured')->orderByDesc('stock_qty')->orderBy('name'),
        };

        return view('shop', [
            'products' => $query->paginate(16)->withQueryString(),
            'categories' => $categories,
            'category' => $category,
            'search' => $search,
            'sort' => $sort,
            'inStock' => $inStock,
            'brand' => $brand,
            'brands' => $brands,
            'sorts' => self::SORTS,
        ]);
    }
}
