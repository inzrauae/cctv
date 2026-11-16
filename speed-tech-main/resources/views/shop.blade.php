@extends('layouts.app', [
  'title' => $search !== '' ? "Search: {$search}" : trim(($brand ? $brand.' ' : '').($subcategory?->name ?? $category?->name ?? ($brand ? 'products' : 'Shop'))),
  'description' => 'Buy CCTV cameras, recorders, alarm systems, smart door locks, intercoms, network and Wi-Fi equipment from Hikvision, HiLook, EZVIZ, Dahua, Imou, TP-Link and UNV at Last Solution, Nugawela, Kandy.',
])

@section('content')
  <div class="wrap page-head">
    <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span>@if ($category)<a href="{{ route('shop') }}">Shop</a><span>/</span>@if ($subcategory)<a href="{{ route('shop', ['category' => $category->slug]) }}">{{ $category->name }}</a><span>/</span><b>{{ $subcategory->name }}</b>@else<b>{{ $category->name }}</b>@endif @else<b>{{ $search !== '' ? 'Search' : 'Shop' }}</b>@endif</nav>
    <div class="eyebrow brand-text">CCTV · SECURITY · SMART HOME · NETWORK</div>
    <h1 class="page-title">
      @if ($search !== '') Results for “{{ $search }}” @else {{ trim(($brand ? $brand.' ' : '').($subcategory?->name ?? $category?->name ?? ($brand ? 'products' : 'Shop all products'))) }} @endif
    </h1>
    @if ($search !== '')<p class="page-sub">Searching all products by name, product code and model number.</p>@endif
  </div>

  <section class="wrap shop-page">
    <aside class="filter-panel" aria-label="Filter products">
      <h2>Categories</h2>
      <ul class="filter-list">
        <li><a href="{{ route('shop', array_filter(['q' => $search ?: null, 'brand' => $brand])) }}" @class(['active' => ! $category])>All products</a></li>
        @foreach ($categories as $item)
          <li><a href="{{ route('shop', array_filter(['category' => $item->slug, 'q' => $search ?: null, 'brand' => $brand])) }}" @class(['active' => $category?->id === $item->id && ! $subcategory])>{{ $item->name }} <small>{{ $item->products_count }}</small></a>
            @if ($category?->id === $item->id && $subcategories->isNotEmpty())
              <ul class="filter-sublist">
                @foreach ($subcategories as $s)
                  <li><a href="{{ route('shop', array_filter(['category' => $item->slug, 'sub' => $s->slug, 'q' => $search ?: null, 'brand' => $brand])) }}" @class(['active' => $subcategory?->id === $s->id])>{{ $s->name }} <small>{{ $s->products_count }}</small></a></li>
                @endforeach
              </ul>
            @endif
          </li>
        @endforeach
      </ul>
      @if ($brands->isNotEmpty())
        <h2 class="filter-heading">Brands</h2>
        <ul class="filter-list brand-filter">
          <li><a href="{{ route('shop', array_filter(['category' => $category?->slug, 'sub' => $subcategory?->slug, 'q' => $search ?: null])) }}" @class(['active' => ! $brand])>All brands</a></li>
          @foreach (config('shop.brands') as $name)
            @if ($brands->has($name))
              <li><a href="{{ route('shop', array_filter(['brand' => $name, 'category' => $category?->slug, 'sub' => $subcategory?->slug, 'q' => $search ?: null])) }}" @class(['active' => $brand === $name])>{{ $name }} <small>{{ $brands[$name] }}</small></a></li>
            @endif
          @endforeach
        </ul>
      @endif
      <button class="filter-cta" type="button" data-service="CCTV installation"><svg class="icon"><use href="#icon-camera"/></svg><span>Need it installed?<b>Book a CCTV installation →</b></span></button>
    </aside>

    <div>
      @if ($subcategories->isNotEmpty())
        <nav class="sub-chips" aria-label="{{ $category->name }} types">
          <a href="{{ route('shop', array_filter(['category' => $category->slug, 'q' => $search ?: null, 'brand' => $brand])) }}" @class(['active' => ! $subcategory])>All {{ $category->name }}</a>
          @foreach ($subcategories as $s)
            <a href="{{ route('shop', array_filter(['category' => $category->slug, 'sub' => $s->slug, 'q' => $search ?: null, 'brand' => $brand])) }}" @class(['active' => $subcategory?->id === $s->id])>{{ $s->name }} <small>{{ $s->products_count }}</small></a>
          @endforeach
        </nav>
      @endif
      <form class="toolbar" method="get" action="{{ route('shop') }}">
        @if ($search !== '')<input type="hidden" name="q" value="{{ $search }}">@endif
        @if ($category)<input type="hidden" name="category" value="{{ $category->slug }}">@endif
        @if ($subcategory)<input type="hidden" name="sub" value="{{ $subcategory->slug }}">@endif
        @if ($brand)<input type="hidden" name="brand" value="{{ $brand }}">@endif
        <span class="result-count">{{ $products->total() }} {{ Str::plural('product', $products->total()) }}</span>
        <label class="check"><input type="checkbox" name="in_stock" value="1" @checked($inStock) data-autosubmit> In stock only</label>
        <label class="sort">Sort
          <select name="sort" data-autosubmit>@foreach ($sorts as $key => $label)<option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>@endforeach</select>
        </label>
        <noscript><button class="button button-outline" type="submit">Apply</button></noscript>
      </form>

      @if ($products->isEmpty())
        <div class="empty"><svg class="icon"><use href="#icon-search"/></svg><h3>No products found</h3><p>Try another search, or <a class="text-link" href="{{ whatsapp_url('Hi Last Solution, I am looking for: '.($search ?: ($category?->name ?? 'CCTV equipment'))) }}" target="_blank" rel="noopener">ask us on WhatsApp</a>. We can often source it.</p></div>
      @else
        <div class="products">@each('partials.product-card', $products, 'product')</div>
        {{ $products->links() }}
      @endif
    </div>
  </section>
@endsection
