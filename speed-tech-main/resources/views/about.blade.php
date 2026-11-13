@extends('layouts.app', [
  'title' => 'About Last Solution',
  'description' => 'Last Solution (Pvt) Ltd in Nugawela, Kandy: CCTV, alarm systems, smart locks, intercoms, networking and smart home, plus A/C and appliance repair, solar power and battery backup.',
])

@section('content')
  <section class="page-hero photo-page-hero">
    @include('partials.page-hero-media', ['images' => ['cctv-dome-hikvision' => 'center', 'smart-lock' => 'center', 'solar-roof' => 'center', 'smart-home' => 'center']])
    <div class="wrap page-hero-inner">
      <nav class="breadcrumbs light" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><b>About us</b></nav>
      <div class="eyebrow hero-anim"><span class="status-dot"></span>LAST SOLUTION (PVT) LTD</div>
      <h1 class="hero-anim">A safer view of<br><em>what matters most.</em></h1>
      <p class="hero-anim">Security, smart home, networking, appliance repair and solar power solutions from one local team in Kandy.</p>
    </div>
  </section>

  <section class="wrap about-story">
    <div>
      <div class="eyebrow brand-text">BASED IN NUGAWELA · KANDY</div>
      <h2>Solutions for your home and business.</h2>
      <p>Last Solution (Pvt) Ltd helps homes, shops and workplaces with CCTV camera installation, repairs and servicing, alarm systems, smart door locks, intercoms, networking, Wi-Fi and smart home devices.</p>
      <p>We work with trusted brands including {{ implode(', ', array_slice(config('shop.brands'), 0, -1)) }} and {{ last(config('shop.brands')) }}. Our social pages feature our camera installations, including colour night-vision systems.</p>
      <p>We also install and service air-conditioning, repair refrigerators and washing machines, and install solar power and battery backup systems.</p>
      <div class="buttons">
        <a class="button red" href="{{ route('repairs') }}">Explore our services<svg class="icon"><use href="#icon-arrow"/></svg></a>
        <a class="button button-outline" href="{{ route('contact') }}">Contact our team</a>
      </div>
    </div>
    <img src="{{ asset('assets/last-solution-facebook.jpg') }}" {{ responsive_img('assets/last-solution-facebook.jpg', '(max-width:700px) 100vw, 400px') }} alt="Last Solution logo: Leader in Video Surveillance" loading="lazy" width="720" height="720">
  </section>

  <section class="wrap about-offer" aria-labelledby="offer-title">
    <div class="eyebrow brand-text">HOW WE CAN HELP</div>
    <h2 id="offer-title">Services from one local team</h2>
    <div class="offer-grid">
      @foreach (config('shop.service_groups') as $key => [$groupTitle, $icon, $text])
        <a class="offer-card" href="{{ route('repairs') }}#group-{{ $key }}"><span class="service-icon"><svg class="icon"><use href="#icon-{{ $icon }}"/></svg></span><h3>{{ $groupTitle }}</h3><p>{{ collect(config('shop.repair_services'))->where('group', $key)->pluck('name')->implode(', ') }}.</p></a>
      @endforeach
    </div>
  </section>

  <section class="about-values" aria-labelledby="values-title">
    <div class="wrap">
      <div class="eyebrow">A GOOD PLACE TO START</div>
      <h2 id="values-title">A simple way to get the conversation started</h2>
      <div class="values-grid">
        <div><svg class="icon"><use href="#icon-search"/></svg><h3>Share your needs</h3><p>Tell us about your property and what you want to protect.</p></div>
        <div><svg class="icon"><use href="#icon-pin"/></svg><h3>Discuss your location</h3><p>We are based at Ebulpuraya, Nugawela, Kandy.</p></div>
        <div><svg class="icon"><use href="#icon-camera"/></svg><h3>Plan the solution</h3><p>Talk through the equipment, brands and options that suit you.</p></div>
        <div><svg class="icon"><use href="#icon-phone"/></svg><h3>Stay in touch</h3><p>Call or WhatsApp our team to arrange the next step.</p></div>
      </div>
    </div>
  </section>

  <section class="wrap"><div class="repair-cta">
    <div>
      <h2>Let’s talk about your project.</h2>
      <p>Call, WhatsApp or visit us in Nugawela, Kandy.</p>
    </div>
    <div class="buttons">
      <a class="button red" href="{{ route('contact') }}">Contact us<svg class="icon"><use href="#icon-arrow"/></svg></a>
      <a class="button white" href="{{ whatsapp_url('Hi Last Solution, I would like to discuss your services.') }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-whatsapp"/></svg>WhatsApp</a>
    </div>
  </div></section>
@endsection
