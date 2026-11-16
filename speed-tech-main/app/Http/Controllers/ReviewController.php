<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\GoogleReviews;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /** Public "All reviews" page. */
    public function __invoke(GoogleReviews $google): View
    {
        $stats = Review::visible()->selectRaw('count(*) as total, avg(rating) as average')->first();

        return view('reviews', [
            'reviews' => Review::forDisplay()->paginate(24),
            'total' => (int) $stats->total,
            'average' => round((float) $stats->average, 1),
            'google' => $google,
        ]);
    }
}
