@extends('layouts.app', [
  'title' => 'Special offers on CCTV equipment',
  'description' => 'Current offers on CCTV cameras, recorders, hard drives and accessories from Last Solution, Nugawela, Kandy.',
])

@section('content')
  <section class="offers-hero">
    <div class="wrap offers-hero-inner">
      <div>
        <nav class="breadcrumbs light" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><b>Offers</b></nav>
        <div class="offers-kicker"><svg class="icon"><use href="#icon-tag"/></svg>LIMITED-TIME DEALS</div>
        <h1>Special offers</h1>
        <p>Save on CCTV cameras, recorders and accessories while stocks last. Need it installed? Our team can fit the complete system for you.</p>
      </div>
      @if ($products->total())
        <div class="offers-count"><b>{{ $products->total() }}</b><span>{{ Str::plural('product', $products->total()) }} on offer</span></div>
      @endif
    </div>
  </section>

  <section class="wrap offers-page">
    @if ($products->isEmpty())
      <div class="empty wide">
        <svg class="icon"><use href="#icon-tag"/></svg>
        <h3>No offers right now</h3>
        <p>Check back soon, or ask us on WhatsApp for the best price on a complete CCTV system.</p>
        <div class="buttons center"><a class="button red" href="{{ route('shop') }}">Shop all products</a><a class="button button-outline" href="{{ whatsapp_url('Hi Last Solution, do you have any CCTV offers?') }}" target="_blank" rel="noopener">Ask on WhatsApp</a></div>
      </div>
    @else
      <div class="products offers-grid">@each('partials.product-card', $products, 'product')</div>
      {{ $products->links() }}
    @endif
  </section>
@endsection
