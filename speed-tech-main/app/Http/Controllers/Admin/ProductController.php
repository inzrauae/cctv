<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ImageVariants;
use App\Services\Inventory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    private const UPLOAD_DIR = 'uploads/products';

    public function index(Request $request): View
    {
        $products = Product::with('category')
            ->search($request->query('q'))
            ->when($request->query('category'), fn ($q, $category) => $q->where('category_id', $category))
            ->when($request->query('stock') === 'out', fn ($q) => $q->where('stock_qty', 0))
            ->when($request->query('stock') === 'low', fn ($q) => $q->whereBetween('stock_qty', [1, Product::LOW_STOCK]))
            ->when($request->query('status') === 'hidden', fn ($q) => $q->where('is_active', false))
            ->when($request->query('status') === 'offer', fn ($q) => $q->onOffer())
            ->latest('updated_at')
            ->paginate(25)->withQueryString();

        $categories = Category::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        return view('admin.products.form', $this->formData(new Product([
            'category_id' => request('category'),
            'is_active' => true,
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        $product = new Product;
        $this->save($request, $product);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', $this->formData($product));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->save($request, $product);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product saved.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->deleteImage($product->image);
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', "“{$product->name}” deleted.");
    }

    private function formData(Product $product): array
    {
        return [
            'product' => $product,
            'categories' => Category::with('subcategories')->orderBy('sort_order')->orderBy('name')->get(),
        ];
    }

    private function save(Request $request, Product $product): void
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'alpha_dash', 'max:200', Rule::unique('products')->ignore($product->id)],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products')->ignore($product->id)],
            'oem_part_number' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', Rule::in(config('shop.brands'))],
            'category_id' => ['required', 'exists:categories,id'],
            // Must belong to the chosen category.
            'subcategory_id' => ['nullable', Rule::exists('subcategories', 'id')->where('category_id', $request->input('category_id'))],
            'tag' => ['nullable', 'string', 'max:40'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'],
            'meta_title' => ['nullable', 'string', 'max:120'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'specification' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'offer_price' => ['nullable', 'numeric', 'min:0', 'max:99999999', 'required_with:offer_ends_at', 'lt:price'],
            'offer_ends_at' => ['nullable', 'date'],
            'stock_qty' => ['required', 'integer', 'min:0', 'max:1000000'],
            'reorder_level' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'cost_price' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'warranty' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'restock_soon' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'category_id.required' => 'Choose a shop category.',
            'subcategory_id.exists' => 'That sub-category is not part of the chosen shop category.',
            'offer_price.lt' => 'The offer price must be lower than the regular price.',
            'offer_price.required_with' => 'Add an offer price, or clear the offer end date.',
        ]);

        // Stock is changed through the inventory log, not set directly.
        $newStock = (int) $data['stock_qty'];
        $product->fill(collect($data)->except(['image', 'remove_image', 'stock_qty'])->all());
        $product->reorder_level = (int) ($data['reorder_level'] ?? 3);
        $isNew = ! $product->exists;
        $product->is_featured = $request->boolean('is_featured');
        $product->restock_soon = $request->boolean('restock_soon');
        $product->is_active = $request->boolean('is_active');
        if (blank($data['slug'] ?? null) && ! $product->exists) {
            $product->slug = null; // HasSlug fills it from the name
        } elseif (blank($data['slug'] ?? null)) {
            $product->slug = $product->getOriginal('slug');
        }

        if ($request->boolean('remove_image') && $product->image) {
            $this->deleteImage($product->image);
            $product->image = null;
        }
        if ($request->hasFile('image')) {
            $this->deleteImage($product->image);
            $file = $request->file('image');
            $name = Str::slug(Str::limit($data['name'], 60, '')).'-'.Str::lower(Str::random(6)).'.'.$file->extension();
            File::ensureDirectoryExists(public_path(self::UPLOAD_DIR));
            $file->move(public_path(self::UPLOAD_DIR), $name);
            $product->image = self::UPLOAD_DIR.'/'.$name;
            ImageVariants::generate($product->image); // small copies for phones
        }

        $product->save();

        if ($isNew && $newStock > 0) {
            Inventory::move($product, $newStock, 'opening', ['note' => 'Opening stock', 'unit_cost' => $data['cost_price'] ?? null]);
        } elseif (! $isNew && $newStock !== (int) $product->stock_qty) {
            Inventory::setCount($product, $newStock, ['note' => 'Changed on the product form']);
        }
    }

    private function deleteImage(?string $path): void
    {
        self::deleteUploadedImage($path);
    }

    /** Only removes files this admin uploaded, never the bundled theme assets. */
    public static function deleteUploadedImage(?string $path): void
    {
        if ($path && str_starts_with($path, self::UPLOAD_DIR.'/')) {
            File::delete(public_path($path));
            ImageVariants::delete($path);
        }
    }
}
