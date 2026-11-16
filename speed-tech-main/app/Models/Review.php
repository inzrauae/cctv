<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'author_name', 'author_photo_url', 'rating', 'text', 'reply', 'reviewed_at',
        'source', 'external_id', 'is_visible', 'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'reviewed_at' => 'datetime',
            'is_visible' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    /** Featured first, then the newest. */
    public function scopeForDisplay(Builder $query): Builder
    {
        return $query->visible()->orderByDesc('is_featured')->orderByDesc('reviewed_at')->orderByDesc('id');
    }

    public function initial(): string
    {
        return mb_strtoupper(mb_substr(trim($this->author_name) ?: '?', 0, 1));
    }

    /** "2 weeks ago" style date, or nothing when the date is unknown. */
    public function when(): string
    {
        return $this->reviewed_at?->diffForHumans() ?? '';
    }
}
