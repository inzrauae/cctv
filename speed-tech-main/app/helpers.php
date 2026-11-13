<?php

if (! function_exists('money')) {
    /** Format an amount in Sri Lankan rupees: "Rs. 15,000" (cents shown only when present). */
    function money(float|int|string|null $amount): string
    {
        $amount = (float) $amount;
        $decimals = fmod($amount, 1.0) == 0.0 ? 0 : 2;

        return config('shop.currency').' '.number_format($amount, $decimals);
    }
}

if (! function_exists('whatsapp_url')) {
    function whatsapp_url(string $message = ''): string
    {
        $url = 'https://wa.me/'.config('shop.whatsapp');

        return $message === '' ? $url : $url.'?text='.rawurlencode($message);
    }
}

if (! function_exists('delivery_text')) {
    /** "Delivery is Rs. 450 and free on orders over Rs. 25,000." from Admin → Payments & delivery. */
    function delivery_text(): string
    {
        $fee = (float) \App\Models\Setting::read('delivery_fee');
        $freeOver = (float) \App\Models\Setting::read('free_delivery_over');

        if ($fee <= 0) {
            return 'Delivery is free.';
        }

        return 'Delivery is '.money($fee).($freeOver > 0 ? ' and free on orders over '.money($freeOver) : '').'.';
    }
}

if (! function_exists('responsive_img')) {
    /**
     * srcset + sizes attributes for an image in public/ that has smaller WebP copies
     * (made by `php artisan images:variants`). Without copies it is just the file itself.
     */
    function responsive_img(?string $path, string $sizes): \Illuminate\Support\HtmlString
    {
        $srcset = \App\Services\ImageVariants::srcset($path);
        if ($srcset === '') {
            // No smaller copies: point srcset at the file itself (needed on <source> tags).
            return new \Illuminate\Support\HtmlString($path ? 'srcset="'.e(str_contains($path, '://') ? $path : asset($path)).'"' : '');
        }

        return new \Illuminate\Support\HtmlString('srcset="'.e($srcset).'" sizes="'.e($sizes).'"');
    }
}

if (! function_exists('highlight_kandy')) {
    /** Escapes $text and wraps the word "Kandy" in a red map-pin pill (see .place-tag in polish.css). */
    function highlight_kandy(string $text): \Illuminate\Support\HtmlString
    {
        $html = preg_replace(
            '/\bkandy\b/i',
            '<span class="place-tag"><svg class="icon" aria-hidden="true"><use href="#icon-pin"/></svg>$0</span>',
            e($text),
        );

        return new \Illuminate\Support\HtmlString($html);
    }
}
