@extends('layouts.app', [
  'title' => 'Contact us',
  'description' => 'Contact Last Solution (Pvt) Ltd in Nugawela, Kandy for CCTV installation, repair and service. Call or WhatsApp '.config('shop.phone_display').'.',
])

@php($shop = config('shop'))

@section('content')
  <section class="page-hero photo-page-hero">
    @include('partials.page-hero-media', ['images' => ['intercom' => 'center', 'cctv-bullet-cameras' => 'center', 'network-switch' => 'center']])
    <div class="wrap page-hero-inner">
      <nav class="breadcrumbs light" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><b>Contact us</b></nav>
      <div class="eyebrow hero-anim"><span class="status-dot"></span>WE’RE HERE TO HELP</div>
      <h1 class="hero-anim">Let’s talk.<br><em>We’re listening.</em></h1>
      <p class="hero-anim">Ask about CCTV installation, camera repairs, servicing or any of our other solutions. Call or message our Kandy team.</p>
    </div>
  </section>

  <section class="wrap contact-methods" aria-label="Ways to contact us">
    <a class="method-card" href="tel:{{ $shop['phone'] }}">
      <span class="service-icon"><svg class="icon"><use href="#icon-phone"/></svg></span>
      <b>Hotline</b><span>{{ $shop['phone_display'] }}</span>
    </a>
    <a class="method-card method-whatsapp" href="{{ whatsapp_url('Hi Last Solution, I have a question about your services.') }}" target="_blank" rel="noopener">
      <span class="service-icon"><svg class="icon"><use href="#icon-whatsapp"/></svg></span>
      <b>WhatsApp</b><span>Send a message or a photo</span>
    </a>
    <a class="method-card" href="{{ $shop['maps_url'] }}" target="_blank" rel="noopener">
      <span class="service-icon"><svg class="icon"><use href="#icon-pin"/></svg></span>
      <b>Find us</b><span>{{ $shop['address'] }}</span>
    </a>
  </section>

  <section class="wrap contact-main">
    <div class="form-card contact-form" data-enquiry-wrap>
      <h2>Send us a message</h2>
      <p class="help-text">Tell us a little about your property and the service you need. We’ll reply by phone or WhatsApp.</p>
      <form class="js-enquiry" action="{{ route('enquiries.store') }}" method="post">
        @csrf
        <input type="hidden" name="type" value="general">
        @include('partials.honeypot')
        <div class="form-grid">
          <label>Your name<input name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name"></label>
          <label>Phone number<input name="phone" type="tel" value="{{ old('phone') }}" required maxlength="20" autocomplete="tel" placeholder="076 722 3260"></label>
          <label class="span-2">E-mail <small>(optional)</small><input name="email" type="email" value="{{ old('email') }}" maxlength="150" autocomplete="email"></label>
          <label class="span-2">How can we help?<textarea name="message" required maxlength="2000" rows="5" placeholder="CCTV installation, camera issue, service request or another question…">{{ old('message') }}</textarea></label>
        </div>
        @if ($errors->any())<div class="form-error">{{ $errors->first() }}</div>@else<div class="form-error" hidden></div>@endif
        <button class="button red" type="submit">Send message<svg class="icon"><use href="#icon-arrow"/></svg></button>
      </form>
      <div class="enquiry-done" hidden></div>
    </div>

    <div class="contact-side">
      <div class="map-card">
        <iframe src="{{ $shop['maps_embed'] }}" title="Map showing Last Solution in Nugawela, Kandy" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
      </div>
      <div class="form-card visit-card">
        <h2>Visit Last Solution</h2>
        <p>{{ $shop['address'] }}</p>
        <div class="phone-list">
          <h3>Call us</h3>
          <a class="phone-hotline" href="tel:{{ $shop['phone'] }}"><svg class="icon"><use href="#icon-phone"/></svg><span><small>Hotline</small>{{ $shop['phone_display'] }}</span></a>
          @foreach ($shop['other_phones'] as [$display, $dial])<a href="tel:{{ $dial }}"><svg class="icon"><use href="#icon-phone"/></svg><span>{{ $display }}</span></a>@endforeach
        </div>
        <div class="help-actions">
          <a class="button red" href="{{ $shop['maps_url'] }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-pin"/></svg>Get directions</a>
        </div>
        <div class="follow-us"><span>Follow us</span>@include('partials.social', ['class' => 'social-brand', 'labels' => true])</div>
        <p class="detail-note">Please call or WhatsApp before visiting so we can discuss your service request.</p>
      </div>
    </div>
  </section>
@endsection
