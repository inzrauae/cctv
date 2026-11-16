<?php

namespace App\Services;

use App\Models\Review;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Imports Google reviews from either
 *  - a Google Takeout "Google Business Profile" export (the whole .zip, or the reviews .json inside it), or
 *  - a reviews.json list like {"reviews": [{"id", "name", "rating": 5, "date": "2 years ago", "text",
 *    "owner_reply", "avatar_original", "photos_original": [...]}]} (relative dates are turned into real ones).
 *
 * The export stores reviews in the same shape as Google's Business Profile API:
 *   {"reviews": [{"reviewId", "reviewer": {"displayName", "profilePhotoUrl"},
 *                 "starRating": "FIVE", "comment", "createTime", "reviewReply": {"comment"}}]}
 * The parser looks for that shape anywhere in the JSON, so small layout differences are fine.
 * Re-importing updates existing reviews (matched on the Google review id) and never duplicates them.
 */
class ReviewImporter
{
    private const STARS = ['ONE' => 1, 'TWO' => 2, 'THREE' => 3, 'FOUR' => 4, 'FIVE' => 5];

    /** @return array{added: int, updated: int, skipped: int} */
    public function importFile(string $path, string $originalName): array
    {
        $jsonFiles = str_ends_with(strtolower($originalName), '.zip')
            ? $this->jsonFromZip($path)
            : [file_get_contents($path)];

        $reviews = [];
        foreach ($jsonFiles as $json) {
            $data = json_decode((string) $json, true);
            if (is_array($data)) {
                $this->collect($data, $reviews);
            }
        }

        if (! $reviews) {
            throw ValidationException::withMessages([
                'file' => 'No reviews were found in that file. Upload the Google Takeout .zip (Google Business Profile), or the reviews .json file inside it.',
            ]);
        }

        return $this->save($reviews);
    }

    /** @return array<int, string> */
    private function jsonFromZip(string $path): array
    {
        if (! class_exists(\ZipArchive::class)) {
            throw ValidationException::withMessages(['file' => 'This server cannot open .zip files. Unzip the export and upload the reviews .json file instead.']);
        }

        $zip = new \ZipArchive;
        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages(['file' => 'That .zip file could not be opened.']);
        }

        $files = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = strtolower((string) $zip->getNameIndex($i));
            if (str_ends_with($name, '.json') && str_contains($name, 'review')) {
                $files[] = (string) $zip->getFromIndex($i);
            }
        }
        $zip->close();

        return $files;
    }

    /** Walks the JSON and collects every object that looks like a Google review. */
    private function collect(array $node, array &$out): void
    {
        if (isset($node['starRating']) && (isset($node['reviewer']) || isset($node['reviewId']) || isset($node['name']))) {
            $out[] = $node;

            return;
        }
        if (isset($node['rating'], $node['name']) && is_numeric($node['rating']) && (array_key_exists('text', $node) || array_key_exists('owner_reply', $node))) {
            $out[] = $this->fromList($node);

            return;
        }
        foreach ($node as $child) {
            if (is_array($child)) {
                $this->collect($child, $out);
            }
        }
    }

    /** @return array{added: int, updated: int, skipped: int} */
    private function save(array $reviews): array
    {
        $result = ['added' => 0, 'updated' => 0, 'skipped' => 0];

        foreach ($reviews as $raw) {
            $stars = $raw['starRating'];
            $rating = is_numeric($stars) ? (int) $stars : (self::STARS[strtoupper((string) $stars)] ?? 0);
            if ($rating < 1 || $rating > 5) {
                $result['skipped']++;

                continue;
            }

            $id = $raw['reviewId'] ?? (isset($raw['name']) ? last(explode('/', (string) $raw['name'])) : null);
            $attributes = [
                'author_name' => mb_substr(trim((string) Arr::get($raw, 'reviewer.displayName', '')) ?: 'Google user', 0, 120),
                'author_photo_url' => $this->photo(Arr::get($raw, 'reviewer.profilePhotoUrl')),
                'rating' => $rating,
                'text' => $this->original(Arr::get($raw, 'comment')),
                'reply' => $this->original(Arr::get($raw, 'reviewReply.comment')),
                'reviewed_at' => $this->date($raw['createTime'] ?? $raw['updateTime'] ?? null),
                'photos' => ($raw['photos'] ?? []) ?: null,
                'source' => 'google',
            ];
            if ($attributes['text'] !== null && $attributes['text'] === $attributes['reply']) {
                $attributes['text'] = null; // star-only review: some exports copy the owner's reply into the text
            }

            $existing = $id ? Review::where('external_id', 'google:'.$id)->first() : null;
            if ($existing && ! empty($raw['relativeDate']) && $existing->reviewed_at) {
                unset($attributes['reviewed_at']); // "2 years ago" is approximate; keep the first date
            }
            if ($existing) {
                $existing->update($attributes); // keeps the admin's shown/hidden and featured choices
                $result['updated']++;
            } else {
                Review::create($attributes + ['external_id' => $id ? 'google:'.$id : null]);
                $result['added']++;
            }
        }

        return $result;
    }

    /** Maps the simple list format onto the Takeout shape used by save(). */
    private function fromList(array $node): array
    {
        $photos = collect($node['photos_original'] ?? [])->filter(fn ($url) => $this->photo($url))->take(6)->values()->all();

        return [
            'reviewId' => $node['id'] ?? null,
            'reviewer' => ['displayName' => $node['name'], 'profilePhotoUrl' => $node['avatar_original'] ?? null],
            'starRating' => (int) $node['rating'],
            'comment' => $node['text'] ?? null,
            'reviewReply' => ['comment' => $node['owner_reply'] ?? null],
            'createTime' => $this->relativeDate((string) ($node['date'] ?? '')),
            'relativeDate' => true,
            'photos' => $photos,
        ];
    }

    /** "2 years ago", "a month ago", "Edited 3 weeks ago" → an ISO date (approximate, as Google shows it). */
    private function relativeDate(string $text): ?string
    {
        if (! preg_match('/(a|an|one|\d+)\s+(minute|hour|day|week|month|year)s?\s+ago/i', $text, $m)) {
            return null;
        }
        $amount = is_numeric($m[1]) ? (int) $m[1] : 1;

        return now()->sub(strtolower($m[2]), $amount)->toIso8601String();
    }

    /** Google adds a "(Translated by Google)" version; keep only the customer's own words. */
    private function original(mixed $text): ?string
    {
        $text = trim((string) $text);
        if ($text === '') {
            return null;
        }
        if (str_contains($text, '(Original)')) {
            $text = trim((string) last(explode('(Original)', $text)));
        } elseif (str_starts_with($text, '(Translated by Google)')) {
            $text = trim((string) preg_replace('/^\(Translated by Google\)\s*/', '', $text));
        }

        return mb_substr($text, 0, 5000);
    }

    private function photo(mixed $url): ?string
    {
        return is_string($url) && str_starts_with($url, 'https://') && strlen($url) <= 500 ? $url : null;
    }

    private function date(mixed $value): ?Carbon
    {
        try {
            return $value ? Carbon::parse((string) $value) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
