@extends('layouts.app', ['ogImage' => $product->imageUrl(), 'ogType' => 'product', 'title' => $product->meta_title ? Str::before($product->meta_title, ' | ') : $product->name, 'description' => $product->meta_description ?: ($product->summary ?: Str::limit(strip_tags((string) $product->description), 155))])

@section('content')
  @push('schema')
    @php
      $availability = match ($product->stockState()) {
          'in', 'low', 'available' => 'https://schema.org/InStock',
          'soon' => 'https://schema.org/BackOrder',
          default => null,
      };
      $crumbs = array_values(array_filter([
          ['Home', route('home')],
          ['Shop', route('shop')],
          $product->category ? [$product->category->name, route('shop', ['category' => $product->category->slug])] : null,
          $product->category && $product->subcategory?->is_active ? [$product->subcategory->name, route('shop', ['category' => $product->category->slug, 'sub' => $product->subcategory->slug])] : null,
          [$product->name, $product->url()],
      ]));
    @endphp
    @include('partials.schema', ['data' => ['@graph' => [
      array_filter([
        '@type' => 'Product',
        'name' => $product->name,
        'description' => $product->meta_description ?: ($product->summary ?: Str::limit(strip_tags((string) $product->description), 300)),
        'image' => $product->imageUrl(),
        'sku' => $product->sku,
        'mpn' => $product->oem_part_number ?: null,
        'category' => trim(($product->category?->name ?? '').($product->subcategory ? ' > '.$product->subcategory->name : '')) ?: null,
        'brand' => $product->brand ? ['@type' => 'Brand', 'name' => $product->brand] : null,
        'url' => $product->url(),
        'offers' => $product->price !== null ? array_filter([
          '@type' => 'Offer',
          'price' => number_format($product->sellingPrice(), 2, '.', ''),
          'priceCurrency' => 'LKR',
          'availability' => $availability,
          'url' => $product->url(),
          'itemCondition' => 'https://schema.org/NewCondition',
          'priceValidUntil' => $product->hasOffer() ? $product->offer_ends_at?->toDateString() : null,
          'seller' => ['@id' => url('/').'#business'],
        ]) : null,
      ]),
      ['@type' => 'BreadcrumbList', 'itemListElement' => array_map(fn ($c, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $c[0], 'item' => $c[1]], $crumbs, array_keys($crumbs))],
    ]]])
  @endpush
  <div class="wrap page-head">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
      <a href="{{ route('home') }}">Home</a><span>/</span>
      <a href="{{ route('shop') }}">Shop</a><span>/</span>
      @if ($product->category)<a href="{{ route('shop', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a><span>/</span>@endif
      @if ($product->category && $product->subcategory?->is_active)<a href="{{ route('shop', ['category' => $product->category->slug, 'sub' => $product->subcategory->slug]) }}">{{ $product->subcategory->name }}</a><span>/</span>@endif
      <b>{{ $product->name }}</b>
    </nav>
  </div>

  <section class="wrap product-page">
    <div class="product-gallery"><img src="{{ $product->imageUrl() }}" {{ responsive_img($product->image, '(max-width:1000px) 100vw, 640px') }} alt="{{ $product->name }}" width="640" height="480"></div>

    <div class="product-summary">
      <div class="eyebrow brand-text">{{ strtoupper($product->groupLabel()) }}@if ($product->brand) · {{ strtoupper($product->brand) }}@endif @if ($product->hasOffer()) · <a class="offer-link" href="{{ route('offers') }}">SPECIAL OFFER</a>@endif</div>
      <h1 class="page-title">{{ $product->name }}</h1>
      @if ($product->summary)<p class="lead">{{ $product->summary }}</p>@endif

      <div class="buy-box">
        <div class="price-row">
          <b class="price-big">{{ $product->priceLabel() }}</b>
          @if ($product->hasOffer())<s class="was">{{ money($product->price) }}</s><span class="offer-pill">-{{ $product->discountPercent() }}%</span>@endif
          @if ($product->stockLabel())<span class="stock stock-{{ $product->stockState() }}">{{ $product->stockLabel() }}</span>@endif
        </div>
        @if ($product->hasOffer())
          <p class="offer-note"><svg class="icon"><use href="#icon-tag"/></svg>You save {{ money($product->savings()) }}@if ($product->offer_ends_at) · offer ends {{ $product->offer_ends_at->format('j M Y') }}@endif</p>
        @endif
        @if ($product->isPurchasable())
          <form action="{{ route('cart.add') }}" method="post" class="js-add-to-cart buy-form">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <label class="qty">Qty <input type="number" name="quantity" value="1" min="1" max="{{ $product->maxOrderQuantity() }}"></label>
            <button class="button red" type="submit"><svg class="icon"><use href="#icon-cart"/></svg>Add to cart</button>
          </form>
        @else
          <button class="button red" type="button" data-enquire="{{ $product->id }}" data-enquire-name="{{ $product->name }}">{{ $product->enquiryLabel() }}<svg class="icon"><use href="#icon-arrow"/></svg></button>
        @endif
        <a class="button button-outline" target="_blank" rel="noopener" href="{{ whatsapp_url("Hi Last Solution, I'm interested in {$product->name}".($product->oem_part_number ? " (model {$product->oem_part_number})" : '').'. '.$product->url()) }}"><svg class="icon"><use href="#icon-whatsapp"/></svg>Ask on WhatsApp</a>
        <ul class="assurances">
          <li><svg class="icon"><use href="#icon-shield"/></svg>{{ $product->warranty ? $product->warranty.' warranty' : 'Warranty confirmed on enquiry' }}</li>
          <li><svg class="icon"><use href="#icon-truck"/></svg>Island-wide delivery or collect from our Kandy office</li>
          <li><svg class="icon"><use href="#icon-wrench"/></svg>Installation available. The installation cost depends on the distance to your location and is quoted separately.</li>
        </ul>
      </div>

      <table class="spec-table">
        <caption>Specifications</caption>
        <tbody>
          @if ($product->brand)<tr><th>Brand</th><td><a class="text-link" href="{{ route('shop', ['brand' => $product->brand]) }}">{{ $product->brand }}</a></td></tr>@endif
          @if ($product->oem_part_number)<tr><th>Model number</th><td>{{ $product->oem_part_number }}</td></tr>@endif
          @if ($product->sku)<tr><th>Product code</th><td>{{ $product->sku }}</td></tr>@endif
          @if ($product->specification)<tr><th>Key specifications</th><td>{{ $product->specification }}</td></tr>@endif
          <tr><th>Category</th><td>{{ $product->groupLabel() }}</td></tr>
          <tr><th>Warranty</th><td>{{ $product->warranty ?: 'Ask us' }}</td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <section class="wrap product-details">
    @if ($product->description)
      <div class="detail-block"><h2>Details</h2><p>{!! nl2br(e($product->description)) !!}</p></div>
    @endif
    <div class="detail-block">
      <h2>Need help choosing?</h2>
      <p>Tell us about your property and the areas you want to cover. We will recommend the right cameras, recorder and storage, and can install the complete system for you.</p>
      <p><button class="button red" type="button" data-service="CCTV installation"><svg class="icon"><use href="#icon-camera"/></svg>Book an installation</button></p>
    </div>
  </section>

  @if ($related->isNotEmpty())
    <section class="wrap related" aria-labelledby="related-title">
      <h2 id="related-title">You may also need</h2>
      <div class="products">@each('partials.product-card', $related, 'product')</div>
    </section>
  @endif
@endsection
