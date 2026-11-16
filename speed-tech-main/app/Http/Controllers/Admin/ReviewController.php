<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\ReviewImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = Review::query()
            ->when($request->query('show') === 'hidden', fn ($q) => $q->where('is_visible', false))
            ->when($request->query('show') === 'shown', fn ($q) => $q->where('is_visible', true))
            ->when($request->query('q'), fn ($q, $s) => $q->where(fn ($w) => $w->where('author_name', 'like', "%{$s}%")->orWhere('text', 'like', "%{$s}%")))
            ->orderByDesc('is_featured')->orderByDesc('reviewed_at')->orderByDesc('id')
            ->paginate(30)->withQueryString();

        $stats = Review::visible()->selectRaw('count(*) as total, avg(rating) as average')->first();

        return view('admin.reviews.index', ['reviews' => $reviews, 'stats' => $stats]);
    }

    public function create(): View
    {
        return view('admin.reviews.form', ['review' => new Review(['rating' => 5, 'is_visible' => true, 'reviewed_at' => now()])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Review::create($this->validated($request) + ['source' => 'manual']);

        return redirect()->route('admin.reviews.index')->with('status', 'Review added.');
    }

    public function edit(Review $review): View
    {
        return view('admin.reviews.form', ['review' => $review]);
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $review->update($this->validated($request));

        return redirect()->route('admin.reviews.index')->with('status', 'Review saved.');
    }

    /** Quick show/hide and feature toggles from the list. */
    public function toggle(Request $request, Review $review): RedirectResponse
    {
        $field = $request->validate(['field' => ['required', 'in:is_visible,is_featured']])['field'];
        $review->update([$field => ! $review->{$field}]);

        return back()->with('status', 'Review updated.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return redirect()->route('admin.reviews.index')->with('status', 'Review deleted.');
    }

    public function import(Request $request, ReviewImporter $importer): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:51200', function ($attribute, $file, $fail) {
                if (! in_array(strtolower($file->getClientOriginalExtension()), ['zip', 'json'], true)) {
                    $fail('Upload the Google Takeout .zip file, or the reviews .json file from inside it.');
                }
            }],
        ]);

        $file = $request->file('file');
        $result = $importer->importFile($file->getRealPath(), $file->getClientOriginalName());

        return redirect()->route('admin.reviews.index')->with('status', "Import finished: {$result['added']} new, {$result['updated']} updated".($result['skipped'] ? ", {$result['skipped']} skipped (no star rating)" : '').'.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'author_name' => ['required', 'string', 'max:120'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'text' => ['nullable', 'string', 'max:5000'],
            'reply' => ['nullable', 'string', 'max:5000'],
            'reviewed_at' => ['nullable', 'date'],
            'is_visible' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);
        $data['is_visible'] = $request->boolean('is_visible');
        $data['is_featured'] = $request->boolean('is_featured');

        return $data;
    }
}
