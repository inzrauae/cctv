<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Sub-categories inside a shop category (managed on the Shop categories page). */
class SubcategoryController extends Controller
{
    public function store(Request $request, Category $category): RedirectResponse
    {
        $sub = new Subcategory(['category_id' => $category->id]);
        $sub->fill($this->validated($request, $sub))->save();

        return back()->with('status', "Sub-category “{$sub->name}” added to {$category->name}.");
    }

    public function update(Request $request, Subcategory $subcategory): RedirectResponse
    {
        $subcategory->fill($this->validated($request, $subcategory))->save();

        return back()->with('status', "“{$subcategory->name}” saved.");
    }

    /** Products in it keep their category; they just lose the sub-category. */
    public function destroy(Subcategory $subcategory): RedirectResponse
    {
        $count = $subcategory->products()->count();
        $subcategory->delete();

        return back()->with('status', "“{$subcategory->name}” deleted.".($count ? " {$count} ".str('product')->plural($count).' kept in '.$subcategory->category->name.' without a sub-category.' : ''));
    }

    private function validated(Request $request, Subcategory $sub): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'alpha_dash', 'max:100',
                Rule::unique('subcategories')->where('category_id', $sub->category_id)->ignore($sub->id)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');
        if (blank($data['slug'] ?? null)) {
            $data['slug'] = $sub->slug; // keep existing; HasSlug fills new ones
        }

        return $data;
    }
}
