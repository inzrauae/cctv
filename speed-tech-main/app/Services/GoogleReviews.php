<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Rating and latest reviews from the shop's Google Business listing (Google Places API, New).
 *
 * Needs GOOGLE_PLACES_API_KEY in .env. GOOGLE_PLACE_ID is optional: without it the listing is
 * found once by name and remembered. Results are cached for 12 hours (about 2 API calls a day);
 * if Google can't be reached, the last good copy is shown. Without a key, get() returns null
 * and the page shows a "Read our Google reviews" card instead.
 */
class GoogleReviews
{
    private const CACHE_KEY = 'google_reviews';

    private const LAST_GOOD_KEY = 'google_reviews_last_good';

    private const PLACE_ID_KEY = 'google_place_id';

    private const API = 'https://places.googleapis.com/v1';

    /** @return array{rating: float, count: int, url: string, reviews: array<int, array<string, mixed>>}|null */
    public function get(): ?array
    {
        if (! $this->apiKey()) {
            return null;
        }

        $cached = Cache::get(self::CACHE_KEY);
        if ($cached !== null) {
            return $cached ?: Cache::get(self::LAST_GOOD_KEY); // [] marks a recent failure
        }

        try {
            $data = $this->fetch();
            Cache::put(self::CACHE_KEY, $data, now()->addHours(12));
            Cache::forever(self::LAST_GOOD_KEY, $data);

            return $data;
        } catch (\Throwable $e) {
            Log::warning('Google reviews could not be loaded: '.$e->getMessage());
            Cache::put(self::CACHE_KEY, [], now()->addMinutes(30)); // don't retry on every page view

            return Cache::get(self::LAST_GOOD_KEY);
        }
    }

    /** Link that opens Google's "write a review" box for the shop, when the place ID is known. */
    public function writeReviewUrl(): ?string
    {
        $placeId = config('shop.google_reviews.place_id') ?: Cache::get(self::PLACE_ID_KEY);

        return $placeId ? 'https://search.google.com/local/writereview?placeid='.rawurlencode($placeId) : null;
    }

    public function listingUrl(): string
    {
        return config('shop.google_reviews.listing_url');
    }

    private function fetch(): array
    {
        $place = $this->request()
            ->withHeaders(['X-Goog-FieldMask' => 'id,rating,userRatingCount,reviews,googleMapsUri'])
            ->get(self::API.'/places/'.rawurlencode($this->placeId()), ['languageCode' => 'en'])
            ->throw()
            ->json();

        $reviews = collect($place['reviews'] ?? [])->map(fn (array $review) => [
            'author' => $review['authorAttribution']['displayName'] ?? 'Google user',
            'author_url' => $review['authorAttribution']['uri'] ?? null,
            'photo' => $review['authorAttribution']['photoUri'] ?? null,
            'rating' => (int) ($review['rating'] ?? 0),
            'text' => trim($review['originalText']['text'] ?? $review['text']['text'] ?? ''),
            'when' => $review['relativePublishTimeDescription'] ?? '',
            'url' => $review['googleMapsUri'] ?? null,
        ])->filter(fn ($review) => $review['rating'] > 0)->values()->all();

        return [
            'rating' => round((float) ($place['rating'] ?? 0), 1),
            'count' => (int) ($place['userRatingCount'] ?? 0),
            'url' => $place['googleMapsUri'] ?? $this->listingUrl(),
            'reviews' => $reviews,
        ];
    }

    private function placeId(): string
    {
        if ($id = config('shop.google_reviews.place_id')) {
            return $id;
        }

        return Cache::rememberForever(self::PLACE_ID_KEY, function () {
            $id = $this->request()
                ->withHeaders(['X-Goog-FieldMask' => 'places.id'])
                ->post(self::API.'/places:searchText', ['textQuery' => config('shop.google_reviews.search')])
                ->throw()
                ->json('places.0.id');

            if (! $id) {
                throw new \RuntimeException('Google listing not found; set GOOGLE_PLACE_ID in .env.');
            }

            return $id;
        });
    }

    private function request(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout(8)->acceptJson()->withHeaders(['X-Goog-Api-Key' => $this->apiKey()]);
    }

    private function apiKey(): ?string
    {
        return config('shop.google_reviews.api_key') ?: null;
    }
}
