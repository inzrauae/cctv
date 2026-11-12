@extends('layouts.app', [
  'title' => 'CCTV, security, smart home, A/C and solar services in Kandy',
  'description' => 'CCTV, alarm systems, smart door locks, intercoms, networking, Wi-Fi and smart home devices, plus A/C, refrigerator and washing machine repair, solar power and battery backup from Last Solution (Pvt) Ltd, Nugawela, Kandy.',
])

@php($services = config('shop.repair_services'))

@section('content')
  <section class="repair-hero">
    <div class="wrap repair-hero-inner">
      <div>
        <nav class="breadcrumbs light" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><b>Our services</b></nav>
        <div class="eyebrow hero-anim"><span class="status-dot"></span>LAST SOLUTION · NUGAWELA, KANDY</div>
        <h1 class="hero-anim">Our <em>services.</em></h1>
        <p class="hero-anim">Security, smart home and networking solutions, appliance repair and A/C, and solar power with battery backup, all from one local team.</p>
        <div class="buttons hero-anim">
          <button class="button red" type="button" data-repair><svg class="icon"><use href="#icon-camera"/></svg>Request a quote<svg class="icon"><use href="#icon-arrow"/></svg></button>
          <a class="button whatsapp-link" href="{{ whatsapp_url('Hi Last Solution, I would like to ask about your services.') }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-whatsapp"/></svg>Ask on WhatsApp</a>
        </div>
      </div>
      <ul class="repair-promises hero-anim">
        <li><svg class="icon"><use href="#icon-pin"/></svg><span><b>Local team</b>Based in Nugawela</span></li>
        <li><svg class="icon"><use href="#icon-shield"/></svg><span><b>Trusted brands</b>{{ implode(', ', array_slice(config('shop.brands'), 0, 3)) }} and more</span></li>
        <li><svg class="icon"><use href="#icon-wrench"/></svg><span><b>{{ count($services) }} services</b>For homes and businesses</span></li>
        <li><svg class="icon"><use href="#icon-phone"/></svg><span><b>Direct contact</b>Call or WhatsApp us</span></li>
      </ul>
    </div>
  </section>

  @php($groups = config('shop.service_groups'))
  <nav class="wrap service-jump" aria-label="Jump to a service group">
    @foreach ($groups as $key => [$title])<a href="#group-{{ $key }}">{{ $title }}</a>@endforeach
  </nav>

  <section class="wrap repair-services" aria-labelledby="services-heading">
    <div class="section-heading"><div><div class="eyebrow brand-text">HOW WE CAN HELP</div><h2 id="services-heading">Our services</h2></div><p>Contact us with your location and requirements.<br>Our team will discuss the next steps with you.</p></div>
    @foreach ($groups as $key => [$groupTitle, $groupIcon, $groupText])
    @php($groupServices = collect($services)->where('group', $key))
    @continue($groupServices->isEmpty())
    <div class="service-group-head" id="group-{{ $key }}"><span class="service-icon"><svg class="icon"><use href="#icon-{{ $groupIcon }}"/></svg></span><div><h3>{{ $groupTitle }}</h3><p>{{ $groupText }}</p></div></div>
    <div class="service-grid">
      @foreach ($groupServices as $service)
        <article class="service-card" id="{{ $service['slug'] }}">
          @if (! empty($service['image']))
            <a class="service-card-photo" href="{{ route('repairs.show', $service['slug']) }}" tabindex="-1" aria-hidden="true"><picture><source srcset="{{ asset('assets/hero/'.$service['image'].'.webp') }}" type="image/webp"><img src="{{ asset('assets/hero/'.$service['image'].'.jpg') }}" alt="" loading="lazy" width="1600" height="1067"></picture></a>
          @endif
          <div class="service-card-head">
            <span class="service-icon"><svg class="icon"><use href="#icon-{{ $service['icon'] }}"/></svg></span>
            <h3><a href="{{ route('repairs.show', $service['slug']) }}">{{ $service['name'] }}</a></h3>
          </div>
          <p>{{ $service['summary'] }}</p>
          <ul class="service-includes">
            @foreach ($service['includes'] as $item)<li><svg class="icon"><use href="#icon-check"/></svg>{{ $item }}</li>@endforeach
          </ul>
          <div class="service-foot">
            <span class="turnaround"><svg class="icon"><use href="#icon-clock"/></svg>{{ $service['turnaround'] }}</span>
            <div class="service-actions"><a class="button button-outline" href="{{ route('repairs.show', $service['slug']) }}">View details</a><button class="button red" type="button" data-service="{{ $service['name'] }}">Ask us</button></div>
          </div>
        </article>
      @endforeach
    </div>
    @endforeach
    <p class="finder-note">Service availability and scheduling are discussed with our team.</p>
  </section>

  <section class="wrap" aria-labelledby="process-heading"><div class="repair-process">
    <div class="eyebrow brand-text">HOW IT WORKS</div>
    <h2 id="process-heading">A clear, practical process</h2>
    <ol class="process-steps">
      @foreach (config('shop.repair_process') as $i => [$step, $text])
        <li><span class="step-number">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $step }}</h3><p>{{ $text }}</p></li>
      @endforeach
    </ol>
  </div></section>

  <section class="wrap" aria-labelledby="brands-heading"><div class="repair-brands">
    <div>
      <h2 id="brands-heading">CCTV systems and more</h2>
      <p>Our social pages feature Hikvision camera systems, colour night vision and CCTV installations in Kandy. Contact us to discuss your property and requirements.</p>
    </div>
    <div class="chip-row">
      @foreach (['CCTV installation', 'Hikvision systems', 'Night vision', 'Remote viewing', 'CCTV service'] as $brand)<span class="chip">{{ $brand }}</span>@endforeach
    </div>
  </div></section>

  <section class="wrap repair-faq" aria-labelledby="faq-heading">
    <div class="faq-intro">
      <div class="eyebrow brand-text">GOOD TO KNOW</div>
      <h2 id="faq-heading">Common questions</h2>
      <p>Can't find your answer? Call us on <a class="text-link" href="tel:{{ config('shop.phone') }}">{{ config('shop.phone_display') }}</a> or send a WhatsApp message.</p>
    </div>
    <div class="faq-list">
      @foreach (config('shop.repair_faqs') as [$question, $answer])
        <details><summary>{{ $question }}<svg class="icon"><use href="#icon-plus"/></svg></summary><p>{{ $answer }}</p></details>
      @endforeach
    </div>
  </section>

  <section class="wrap"><div class="repair-cta">
    <div>
      <h2>Need help with CCTV or another service?</h2>
      <p>Call, WhatsApp or visit us at {{ config('shop.address') }}.</p>
    </div>
    <div class="buttons">
      <button class="button red" type="button" data-repair>Request a quote<svg class="icon"><use href="#icon-arrow"/></svg></button>
      <a class="button white" href="{{ config('shop.maps_url') }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-pin"/></svg>Get directions</a>
    </div>
  </div></section>
@endsection
