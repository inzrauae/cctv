@extends('layouts.app', [
  'ogImage' => ! empty($service['image']) ? 'assets/hero/'.$service['image'].'.jpg' : null,
  'title' => $service['name'].' in Kandy',
  'description' => $service['summary'].' Last Solution (Pvt) Ltd, Nugawela, Kandy.',
])

@section('content')
  @push('schema')
    @include('partials.schema', ['data' => ['@graph' => [
      [
        '@type' => 'Service',
        'name' => $service['name'],
        'serviceType' => $service['name'],
        'description' => $service['summary'],
        'url' => route('repairs.show', $service['slug']),
        'provider' => ['@id' => url('/').'#business'],
        'areaServed' => [['@type' => 'City', 'name' => 'Kandy'], ['@type' => 'Country', 'name' => 'Sri Lanka']],
      ],
      ['@type' => 'BreadcrumbList', 'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Our services', 'item' => route('repairs')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $service['name'], 'item' => route('repairs.show', $service['slug'])],
      ]],
    ]]])
  @endpush
  <section class="repair-hero service-hero">
    @if (! empty($service['image']))
      <div class="service-hero-photo" aria-hidden="true"><picture><source {{ responsive_img('assets/hero/'.$service['image'].'.webp', '100vw') }} type="image/webp"><img src="{{ asset('assets/hero/'.$service['image'].'.jpg') }}" alt="" fetchpriority="high" width="1600" height="1067"></picture></div>
    @endif
    <div class="wrap repair-hero-inner">
      <div>
        <nav class="breadcrumbs light" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><a href="{{ route('repairs') }}">Our services</a><span>/</span><b>{{ $service['name'] }}</b></nav>
        <span class="service-icon large hero-anim"><svg class="icon"><use href="#icon-{{ $service['icon'] }}"/></svg></span>
        <h1 class="hero-anim">{{ $service['name'] }}</h1>
        <p class="hero-anim">{{ $service['summary'] }}</p>
        <div class="buttons hero-anim">
          <button class="button red" type="button" data-service="{{ $service['name'] }}"><svg class="icon"><use href="#icon-wrench"/></svg>Ask about this service<svg class="icon"><use href="#icon-arrow"/></svg></button>
          <a class="button whatsapp-link" href="{{ whatsapp_url("Hi Last Solution, I'd like to ask about {$service['name']}.") }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-whatsapp"/></svg>Ask on WhatsApp</a>
        </div>
      </div>
      <aside class="service-facts hero-anim" aria-label="Service summary">
        <div><svg class="icon"><use href="#icon-clock"/></svg><span><b>Typical turnaround</b>{{ $service['turnaround'] }}</span></div>
        <div><svg class="icon"><use href="#icon-tag"/></svg><span><b>Price</b>Quoted after we discuss your needs</span></div>
        <div><svg class="icon"><use href="#icon-shield"/></svg><span><b>Warranty</b>Confirmed with your quote</span></div>
        <div><svg class="icon"><use href="#icon-pin"/></svg><span><b>Where</b>Nugawela, Kandy</span></div>
      </aside>
    </div>
  </section>

  <section class="wrap service-detail">
    <div class="service-detail-main">
      <div class="detail-block">
        <h2>About this service</h2>
        <p>{{ $service['description'] }}</p>
      </div>
      <div class="service-columns">
        <div class="detail-block">
          <h2>When to contact us</h2>
          <ul class="service-includes">@foreach ($service['signs'] as $sign)<li><svg class="icon"><use href="#icon-info"/></svg>{{ $sign }}</li>@endforeach</ul>
        </div>
        <div class="detail-block">
          <h2>What's included</h2>
          <ul class="service-includes">@foreach ($service['includes'] as $item)<li><svg class="icon"><use href="#icon-check"/></svg>{{ $item }}</li>@endforeach</ul>
        </div>
      </div>
      <div class="detail-block">
        <h2>How it works</h2>
        <ol class="mini-steps">
          @foreach (config('shop.repair_process') as $i => [$step, $text])
            <li><span class="step-number">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span><div><h3>{{ $step }}</h3><p>{{ $text }}</p></div></li>
          @endforeach
        </ol>
      </div>
    </div>

    <aside class="service-book">
      <div class="dialog-icon"><svg class="icon"><use href="#icon-wrench"/></svg></div>
      <h2>Ask about {{ Str::lower($service['name']) }}</h2>
      <p>Tell us about your property and we'll contact you to discuss the next steps.</p>
      <button class="button red full" type="button" data-service="{{ $service['name'] }}">Request a quote<svg class="icon"><use href="#icon-arrow"/></svg></button>
      <a class="button button-outline full" href="tel:{{ config('shop.phone') }}"><svg class="icon"><use href="#icon-phone"/></svg>{{ config('shop.phone_display') }}</a>
      <a class="button whatsapp-link full" href="{{ whatsapp_url("Hi Last Solution, I'd like to ask about {$service['name']}.") }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-whatsapp"/></svg>WhatsApp us</a>
      <p class="detail-note">{{ config('shop.address') }}</p>
    </aside>
  </section>

  @if ($service['slug'] === 'cctv-installation')
    @include('partials.cctv-sections')
  @endif

  <section class="wrap related-services" aria-labelledby="other-services">
    <div class="section-heading"><div><div class="eyebrow brand-text">MORE WAYS WE CAN HELP</div><h2 id="other-services">Other services</h2></div><a class="text-link" href="{{ route('repairs') }}">All services<svg class="icon"><use href="#icon-arrow"/></svg></a></div>
    <div class="service-tiles">@foreach ($others->take(4) as $other)@include('partials.service-card', ['service' => $other])@endforeach</div>
  </section>
@endsection
