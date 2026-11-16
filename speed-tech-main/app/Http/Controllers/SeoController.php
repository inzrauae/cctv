<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Response;

/** robots.txt, sitemap.xml and llms.txt, built from the live catalogue so they never go stale. */
class SeoController extends Controller
{
    /** AI and answer-engine crawlers we welcome by name (they are also covered by "*"). */
    private const AI_CRAWLERS = [
        'GPTBot', 'OAI-SearchBot', 'ChatGPT-User', 'ClaudeBot', 'Claude-SearchBot', 'Claude-User',
        'PerplexityBot', 'Perplexity-User', 'Google-Extended', 'Applebot-Extended', 'Bingbot', 'CCBot',
    ];

    public function robots(): Response
    {
        $private = ['/admin', '/cart', '/checkout', '/orders/', '/install/'];
        $lines = ['User-agent: *'];
        foreach ($private as $path) {
            $lines[] = "Disallow: {$path}";
        }
        // Search result pages are not blocked here: they carry a noindex tag, which crawlers must be able to read.
        $lines[] = 'Allow: /';
        $lines[] = '';
        foreach (self::AI_CRAWLERS as $bot) {
            $lines[] = "User-agent: {$bot}";
            foreach ($private as $path) {
                $lines[] = "Disallow: {$path}";
            }
            $lines[] = 'Allow: /';
            $lines[] = '';
        }
        $lines[] = 'Sitemap: '.url('/sitemap.xml');

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(): Response
    {
        $urls = [
            [route('home'), null, '1.0'],
            [route('shop'), null, '0.9'],
            [route('repairs'), null, '0.9'],
            [route('about'), null, '0.6'],
            [route('contact'), null, '0.7'],
            [route('reviews'), Review::visible()->max('updated_at'), '0.6'],
        ];
        if (Product::active()->onOffer()->exists()) {
            $urls[] = [route('offers'), null, '0.7'];
        }
        foreach (config('shop.repair_services') as $service) {
            $urls[] = [route('repairs.show', $service['slug']), null, '0.8'];
        }
        foreach (Category::active()->with(['subcategories' => fn ($q) => $q->where('is_active', true)])->get() as $category) {
            $urls[] = [route('shop', ['category' => $category->slug]), $category->updated_at, '0.8'];
            foreach ($category->subcategories as $sub) {
                $urls[] = [route('shop', ['category' => $category->slug, 'sub' => $sub->slug]), $sub->updated_at, '0.7'];
            }
        }
        foreach (Product::active()->orderBy('id')->get(['id', 'slug', 'updated_at']) as $product) {
            $urls[] = [$product->url(), $product->updated_at, '0.7'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as [$loc, $modified, $priority]) {
            $xml .= '  <url><loc>'.e($loc).'</loc>'
                .($modified ? '<lastmod>'.\Illuminate\Support\Carbon::parse($modified)->toAtomString().'</lastmod>' : '')
                ."<priority>{$priority}</priority></url>\n";
        }
        $xml .= "</urlset>\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /** Plain-language summary for AI assistants and answer engines (https://llmstxt.org). */
    public function llms(): Response
    {
        $shop = config('shop');
        $stats = Review::visible()->selectRaw('count(*) as total, avg(rating) as average')->first();
        $categories = Category::active()->with(['subcategories' => fn ($q) => $q->where('is_active', true)])
            ->withCount(['products' => fn ($q) => $q->active()])->get();

        $out = "# {$shop['legal_name']}\n\n";
        $out .= "> CCTV and security company in Kandy, Sri Lanka. Installs, repairs and services CCTV camera systems "
            ."(IP and analog HD), alarm systems, smart door locks, intercoms, networks and Wi-Fi and smart home devices; "
            ."also A/C, refrigerator and washing machine repair, solar power and battery backup. Sells CCTV equipment online "
            .'from '.implode(', ', $shop['brands']).".\n\n";

        $out .= "## Contact\n\n";
        $out .= "- Phone / WhatsApp: {$shop['phone_display']} ({$shop['phone']})\n";
        if ($shop['email']) {
            $out .= "- Email: {$shop['email']}\n";
        }
        $out .= "- Address: {$shop['address']}\n";
        $out .= "- Area served: Kandy and island-wide in Sri Lanka (delivery island-wide; installation quoted by distance from the Kandy office)\n";
        $out .= "- Website: ".url('/')."\n";
        if ((int) $stats->total > 0) {
            $out .= '- Google reviews: '.number_format((float) $stats->average, 1)." out of 5 from {$stats->total} reviews (".route('reviews').")\n";
        }
        $out .= "\n## Services\n\n";
        foreach ($shop['repair_services'] as $service) {
            $out .= "- [{$service['name']}](".route('repairs.show', $service['slug'])."): {$service['summary']}\n";
        }
        $out .= "\n## Shop categories\n\n";
        foreach ($categories as $category) {
            $subs = $category->subcategories->pluck('name')->implode(', ');
            $out .= "- [{$category->name}](".route('shop', ['category' => $category->slug]).')'.($subs ? ": {$subs}" : '')."\n";
        }
        $out .= "\n## Buying\n\n";
        $out .= "- Prices are in Sri Lankan rupees (LKR). Products with a price can be ordered online; others are quoted on request.\n";
        $out .= "- Pickup from the Kandy office, or island-wide delivery. Payment: cash on delivery, bank transfer or pay at the office.\n";
        $out .= "- Installation is optional and quoted separately based on distance.\n";
        $out .= "\n## FAQ\n\n";
        foreach ($shop['home_faqs'] as [$question, $answer]) {
            $out .= "- {$question} {$answer}\n";
        }
        $out .= "\n## Key pages\n\n";
        foreach ([['Shop', route('shop')], ['Our services', route('repairs')], ['Customer reviews', route('reviews')], ['About us', route('about')], ['Contact', route('contact')], ['Sitemap', url('/sitemap.xml')]] as [$label, $link]) {
            $out .= "- [{$label}]({$link})\n";
        }

        return response($out, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
