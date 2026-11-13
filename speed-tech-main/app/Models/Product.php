<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Services\SkuGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasSlug;

    public const LOW_STOCK = 3;

    protected $fillable = [
        'category_id', 'name', 'brand', 'slug', 'sku', 'oem_part_number',
        'tag', 'summary', 'description', 'meta_title', 'meta_description', 'specification', 'connector_type', 'size',
        'price', 'offer_price', 'offer_ends_at', 'cost_price', 'stock_qty', 'restock_soon', 'reorder_level', 'warranty', 'image', 'is_featured', 'is_active',
    ];

    protected static function booted(): void
    {
        // Every product gets a stock code (e.g. CAM-00012) unless one was typed in.
        static::saving(function (Product $product) {
            $product->sku = filled($product->sku) ? Str::upper(trim($product->sku)) : app(SkuGenerator::class)->for($product);
        });
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'offer_price' => 'decimal:2',
            'offer_ends_at' => 'date',
            'cost_price' => 'decimal:2',
            'stock_qty' => 'integer',
            'reorder_level' => 'integer',
            'is_featured' => 'boolean',
            'restock_soon' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest('id');
    }

    /** At or below its reorder level (admin inventory view). */
    public function needsReorder(): bool
    {
        return $this->stock_qty <= $this->reorder_level;
    }

    public function scopeNeedsReorder(Builder $query): Builder
    {
        return $query->whereColumn('stock_qty', '<=', 'reorder_level');
    }

    public function stockValue(): float
    {
        return round((float) $this->cost_price * $this->stock_qty, 2);
    }

    // ----- Scopes -----

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Products with a running offer: an offer price below the regular price, not yet expired. */
    public function scopeOnOffer(Builder $query): Builder
    {
        return $query->whereNotNull('price')->whereNotNull('offer_price')
            ->whereColumn('offer_price', '<', 'price')
            ->where(fn (Builder $q) => $q->whereNull('offer_ends_at')->orWhereDate('offer_ends_at', '>=', today()));
    }

    /** SQL for the price a customer pays now (for sorting). */
    public static function sellingPriceSql(): string
    {
        $today = today()->toDateString();

        return "(case when offer_price is not null and price is not null and offer_price < price and (offer_ends_at is null or offer_ends_at >= '{$today}') then offer_price else price end)";
    }

    /** Matches name, brand, SKU, model number, specification, summary and tag. */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $words = array_slice(preg_split('/\s+/', $term), 0, 6);

        return $query->where(function (Builder $outer) use ($words) {
            foreach ($words as $word) {
                $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $word).'%';
                $outer->where(fn (Builder $q) => $q
                    ->where('name', 'like', $like)
                    ->orWhere('brand', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhere('oem_part_number', 'like', $like)
                    ->orWhere('specification', 'like', $like)
                    ->orWhere('summary', 'like', $like)
                    ->orWhere('tag', 'like', $like));
            }
        });
    }

    // ----- Presentation helpers -----

    public function hasOffer(): bool
    {
        return $this->price !== null && $this->offer_price !== null
            && (float) $this->offer_price < (float) $this->price
            && ($this->offer_ends_at === null || $this->offer_ends_at->endOfDay()->isFuture());
    }

    /** What the customer pays now: the offer price while an offer runs, otherwise the regular price. */
    public function sellingPrice(): ?float
    {
        if ($this->price === null) {
            return null;
        }

        return (float) ($this->hasOffer() ? $this->offer_price : $this->price);
    }

    public function discountPercent(): int
    {
        return $this->hasOffer() ? (int) round((1 - (float) $this->offer_price / (float) $this->price) * 100) : 0;
    }

    public function savings(): float
    {
        return $this->hasOffer() ? round((float) $this->price - (float) $this->offer_price, 2) : 0.0;
    }

    /** Only priced, in-stock items can go into the cart; everything else is enquiry-only. */
    public function isPurchasable(): bool
    {
        return $this->is_active && $this->price !== null && $this->stock_qty > 0;
    }

    public function maxOrderQuantity(): int
    {
        return max(0, min($this->stock_qty, (int) config('shop.max_line_quantity')));
    }

    public function priceLabel(): string
    {
        return $this->price === null ? 'Price on request' : money($this->sellingPrice());
    }

    /**
     * Customer-facing availability, shown next to the price.
     * Null (nothing shown) when the product has no price.
     */
    public function stockLabel(): ?string
    {
        return match ($this->stockState()) {
            'in' => 'In stock',
            'low' => "Only {$this->stock_qty} left",
            'available' => 'In stock',
            'soon' => 'Stock coming soon',
            default => null,
        };
    }

    /** in | low | available (priced, no stock count) | soon (restock on the way) | out (no price: hidden) */
    public function stockState(): string
    {
        return match (true) {
            $this->stock_qty > self::LOW_STOCK => 'in',
            $this->stock_qty > 0 => 'low',
            (bool) $this->restock_soon => 'soon',
            $this->price !== null => 'available',
            default => 'out',
        };
    }

    /** Text for the enquiry button when the product can't be bought online. */
    public function enquiryLabel(): string
    {
        return match (true) {
            $this->price === null => 'Ask for price',
            $this->stockState() === 'soon' => 'Ask when it arrives',
            default => 'Check availability',
        };
    }

    public function imageUrl(): string
    {
        if (! $this->image) {
            return asset('assets/part-placeholder.svg');
        }
        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        return asset($this->image);
    }

    public function groupLabel(): string
    {
        return $this->category?->name ?? 'CCTV equipment';
    }

    public function url(): string
    {
        return route('products.show', $this);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Compact representation for the JSON API. */
    public function toCard(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'group' => $this->groupLabel(),
            'summary' => $this->summary,
            'oem_part_number' => $this->oem_part_number,
            'specification' => $this->specification,
            'price' => $this->sellingPrice(),
            'regular_price' => $this->price === null ? null : (float) $this->price,
            'on_offer' => $this->hasOffer(),
            'discount_percent' => $this->discountPercent(),
            'price_label' => $this->priceLabel(),
            'stock' => $this->stockState(),
            'stock_label' => $this->stockLabel(),
            'warranty' => $this->warranty,
            'image' => $this->imageUrl(),
            'url' => $this->url(),
            'purchasable' => $this->isPurchasable(),
        ];
    }
}
