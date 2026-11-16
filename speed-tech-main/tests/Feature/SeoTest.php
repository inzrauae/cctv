<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        config(['shop.google_reviews.api_key' => null]);
        Http::fake();
        $cameras = Category::create(['name' => 'CCTV Cameras']);
        $ip = Subcategory::create(['category_id' => $cameras->id, 'name' => 'IP Cameras']);
        $this->product = Product::create([
            'category_id' => $cameras->id, 'subcategory_id' => $ip->id, 'name' => 'Hikvision 4MP IP Dome',
            'brand' => 'Hikvision', 'oem_part_number' => 'DS-2CD1143G0-I', 'price' => 18500, 'is_active' => true,
        ]);
        Product::create(['category_id' => $cameras->id, 'name' => 'Hidden old model', 'price' => 1000, 'is_active' => false]);
    }

    private function jsonLd(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

        return array_map(fn ($json) => json_decode($json, true, flags: JSON_THROW_ON_ERROR), $m[1]);
    }

    public function test_robots_blocks_private_pages_and_points_to_sitemap(): void
    {
        $this->get('/robots.txt')->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /admin')->assertSee('Disallow: /checkout')
            ->assertSee('User-agent: GPTBot')->assertSee('User-agent: ClaudeBot')->assertSee('User-agent: PerplexityBot')
            ->assertSee('Sitemap: '.url('/sitemap.xml'));
    }

    public function test_sitemap_lists_pages_categories_and_active_products_only(): void
    {
        $xml = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->getContent();

        $this->assertNotFalse(simplexml_load_string($xml));
        $this->assertStringContainsString('<loc>'.e($this->product->url()).'</loc>', $xml);
        $this->assertStringContainsString(e(route('shop', ['category' => 'cctv-cameras', 'sub' => 'ip-cameras'])), $xml);
        $this->assertStringContainsString(route('repairs.show', 'cctv-installation'), $xml);
        $this->assertStringNotContainsString('hidden-old-model', $xml);
    }

    public function test_llms_txt_summarises_the_business(): void
    {
        $this->get('/llms.txt')->assertOk()
            ->assertSee('# Last Solution (Pvt) Ltd')
            ->assertSee('info@lastsolution.lk')
            ->assertSee('CCTV Cameras')
            ->assertSee('IP Cameras');
    }

    public function test_every_page_has_business_schema_and_canonical(): void
    {
        $html = $this->get('/')->assertOk()->assertSee('<link rel="canonical" href="'.url('/').'">', false)->getContent();
        $graph = $this->jsonLd($html)[0]['@graph'];

        $this->assertSame('LocalBusiness', $graph[0]['@type']);
        $this->assertSame('info@lastsolution.lk', $graph[0]['email']);
        $this->assertSame('LK', $graph[0]['address']['addressCountry']);
        $this->assertSame('WebSite', $graph[1]['@type']);
        $this->assertSame('FAQPage', $this->jsonLd($html)[1]['@type']);
    }

    public function test_product_page_has_product_offer_and_breadcrumbs(): void
    {
        $html = $this->get($this->product->url())->assertOk()->getContent();
        $graph = collect($this->jsonLd($html))->pluck('@graph')->filter()->last();

        $product = collect($graph)->firstWhere('@type', 'Product');
        $this->assertSame('Hikvision 4MP IP Dome', $product['name']);
        $this->assertSame('DS-2CD1143G0-I', $product['mpn']);
        $this->assertSame('18500.00', $product['offers']['price']);
        $this->assertSame('LKR', $product['offers']['priceCurrency']);
        $this->assertSame('https://schema.org/InStock', $product['offers']['availability']);
        $crumbs = collect($graph)->firstWhere('@type', 'BreadcrumbList')['itemListElement'];
        $this->assertSame(['Home', 'Shop', 'CCTV Cameras', 'IP Cameras', 'Hikvision 4MP IP Dome'], array_column($crumbs, 'name'));
    }

    public function test_private_and_search_pages_are_noindex_and_canonical_drops_sorting(): void
    {
        $this->get('/cart')->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->get('/shop?q=dome')->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->get('/shop?category=cctv-cameras&sort=newest&in_stock=1')
            ->assertSee('<link rel="canonical" href="'.url('/shop').'?category=cctv-cameras">', false)
            ->assertDontSee('noindex');
    }

    public function test_email_shows_on_contact_page(): void
    {
        $this->get('/contact')->assertOk()->assertSee('mailto:info@lastsolution.lk', false);
    }
}
