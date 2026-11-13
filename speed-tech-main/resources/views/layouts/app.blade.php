@php($shop = config('shop'))
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#071a34">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script>document.documentElement.classList.add('js')</script>
  <title>{{ isset($title) ? $title.' | '.$shop['name'] : $shop['name'].' | CCTV & Security Solutions in Kandy' }}</title>
  <meta name="description" content="{{ $description ?? 'CCTV, alarm systems, smart door locks, intercoms, networking, Wi-Fi and smart home devices, plus A/C and appliance repair, solar power and battery backup. Last Solution (Pvt) Ltd, Nugawela, Kandy. Hikvision, HiLook, EZVIZ, Dahua, Imou, TP-Link and UNV.' }}">
  <link rel="icon" href="{{ asset('assets/favicon.png') }}" type="image/png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700;800&family=Barlow:wght@400;500;600;700;800&display=swap">
  <link rel="stylesheet" href="{{ asset('style.css') }}?v={{ filemtime(public_path('style.css')) }}">
  <link rel="stylesheet" href="{{ asset('shop.css') }}?v={{ filemtime(public_path('shop.css')) }}">
  <link rel="stylesheet" href="{{ asset('pages.css') }}?v={{ filemtime(public_path('pages.css')) }}">
  <link rel="stylesheet" href="{{ asset('last-solution.css') }}?v={{ filemtime(public_path('last-solution.css')) }}">
  <link rel="stylesheet" href="{{ asset('home.css') }}?v={{ filemtime(public_path('home.css')) }}">
  @if (request()->routeIs('home', 'repairs.show'))<link rel="stylesheet" href="{{ asset('home-sections.css') }}?v={{ filemtime(public_path('home-sections.css')) }}">@endif
  @if (request()->routeIs('home'))<link rel="stylesheet" href="{{ asset('home-ref.css') }}?v={{ filemtime(public_path('home-ref.css')) }}">@endif
  <link rel="stylesheet" href="{{ asset('polish.css') }}?v={{ filemtime(public_path('polish.css')) }}">
</head>
<body>
  @include('partials.icons')
  <a class="skip-link" href="#main">Skip to content</a>

  <div class="topbar"><div class="wrap topbar-inner"><div class="top-contact">
    <a href="{{ $shop['maps_url'] }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-pin"/></svg>Kandy, Sri Lanka</a>
    <a href="tel:{{ $shop['phone'] }}"><svg class="icon"><use href="#icon-phone"/></svg>Hotline {{ $shop['phone_display'] }}</a>
  </div><div class="top-right"><span class="top-tagline">CCTV · Security · Smart home · A/C · Solar</span>@include('partials.social', ['class' => 'social-top'])</div></div></div>

  <header class="masthead"><div class="wrap header">
    <a class="brand" href="{{ route('home') }}" aria-label="{{ $shop['name'] }} home"><img class="logo" src="{{ asset('assets/last-solution-logo.png') }}" {{ responsive_img('assets/last-solution-logo.png', '(max-width:700px) 100px, 130px') }} width="538" height="314" alt="Last Solution"></a>
    <form class="search" action="{{ route('shop') }}" method="get" role="search">
      <input name="q" type="search" value="{{ request()->routeIs('shop') ? request('q') : '' }}" aria-label="Search products" placeholder="Search cameras, locks, routers and more…" autocomplete="off" maxlength="100">
      <button type="submit" aria-label="Search"><svg class="icon"><use href="#icon-search"/></svg></button>
    </form>
    <a class="header-help" href="tel:{{ $shop['phone'] }}"><svg class="icon"><use href="#icon-phone"/></svg><span><small>Call or WhatsApp</small><b>{{ $shop['phone_display'] }}</b></span></a>
    <a class="bag" href="{{ route('cart') }}" aria-label="Cart, {{ $cartCount }} {{ Str::plural('item', $cartCount) }}" data-cart-link>
      <span class="bag-icon"><svg class="icon"><use href="#icon-cart"/></svg><span id="count">{{ $cartCount }}</span></span>
      <span class="bag-label"><b>My cart</b><small>Review &amp; checkout</small></span>
    </a>
  </div></header>

  <nav class="main-nav" aria-label="Main navigation"><div class="wrap nav">
    <a class="home-link" href="{{ route('home') }}" aria-label="Home"><svg class="icon"><use href="#icon-home"/></svg></a>
    <a href="{{ route('repairs') }}" @class(['active' => request()->routeIs('repairs*')])>Our services</a>
    <a href="{{ route('shop') }}" @class(['active' => request()->routeIs('shop', 'products.*', 'cart', 'checkout')])>Shop</a>
    @if ($offerCount)<a class="nav-offers" href="{{ route('offers') }}" @class(['active' => request()->routeIs('offers')])>Offers<span class="nav-badge">{{ $offerCount }}</span></a>@endif
    <a href="{{ route('about') }}" @class(['active' => request()->routeIs('about')])>About us</a>
    <a href="{{ route('contact') }}" @class(['active' => request()->routeIs('contact')])>Contact</a>
    <a class="whatsapp-link" href="{{ whatsapp_url('Hi Last Solution, I would like to ask about your services.') }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-whatsapp"/></svg>WhatsApp us</a>
  </div></nav>

  <main id="main">
    @if (session('status') || session('error'))
      <div class="wrap"><div @class(['flash', 'flash-error' => session('error')]) role="status">
        <svg class="icon"><use href="#icon-{{ session('error') ? 'info' : 'check' }}"/></svg>
        <span>{{ session('status') ?? session('error') }}</span>
        @if (session('whatsapp'))<a class="text-link" href="{{ session('whatsapp') }}" target="_blank" rel="noopener">Follow up on WhatsApp<svg class="icon"><use href="#icon-arrow"/></svg></a>@endif
      </div></div>
    @endif

    @yield('content')
  </main>

  <footer>
    <div class="wrap footer-cta">
      <div><h2>Let’s talk about your project.</h2><p>CCTV, smart home, A/C, appliance repair or solar? Speak directly with our team.</p></div>
      <div class="footer-cta-actions">
        <a class="button footer-call" href="tel:{{ $shop['phone'] }}"><svg class="icon"><use href="#icon-phone"/></svg>{{ $shop['phone_display'] }}</a>
        <a class="button whatsapp-link" href="{{ whatsapp_url('Hi Last Solution, I would like to discuss CCTV services.') }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-whatsapp"/></svg>Chat on WhatsApp<svg class="icon"><use href="#icon-arrow"/></svg></a>
      </div>
    </div>

    <div class="wrap footer-top">
      <div class="footer-brand">
        <a href="{{ route('home') }}" aria-label="{{ $shop['name'] }} home"><img src="{{ asset('assets/last-solution-logo-light.png') }}" {{ responsive_img('assets/last-solution-logo-light.png', '220px') }} loading="lazy" width="538" height="314" alt="Last Solution"></a>
        <p class="footer-tagline">CCTV <span>•</span> Security <span>•</span> Service</p>
        <p class="footer-about">CCTV, alarms, smart locks, intercoms, networking and smart home, plus A/C and appliance repair, solar power and battery backup. Based in Nugawela, Kandy.</p>
        @include('partials.social', ['class' => 'social-footer'])
      </div>
      <div class="footer-links"><h2>Our services</h2>
        @foreach (array_slice($shop['repair_services'], 0, 5) as $service)<a href="{{ route('repairs.show', $service['slug']) }}">{{ $service['name'] }}</a>@endforeach
        <a class="footer-more" href="{{ route('repairs') }}">All services<svg class="icon"><use href="#icon-arrow"/></svg></a>
      </div>
      <div class="footer-links"><h2>Company</h2>
        <a href="{{ route('shop') }}">Shop</a>
        @if ($offerCount)<a href="{{ route('offers') }}">Special offers</a>@endif
        <a href="{{ route('about') }}">About us</a>
        <a href="{{ route('contact') }}">Contact us</a>
        @if ($shop['facebook_url'])<a href="{{ $shop['facebook_url'] }}" target="_blank" rel="noopener">Facebook page</a>@endif
        @if ($shop['tiktok_url'])<a href="{{ $shop['tiktok_url'] }}" target="_blank" rel="noopener">TikTok</a>@endif
      </div>
      <div class="footer-links footer-contact" id="contact"><h2>Contact us</h2>
        <a class="footer-hotline" href="tel:{{ $shop['phone'] }}"><svg class="icon"><use href="#icon-phone"/></svg><span><small>Hotline</small>{{ $shop['phone_display'] }}</span></a>
        @if ($shop['other_phones'])<div class="footer-phones">@foreach ($shop['other_phones'] as [$display, $dial])<a href="tel:{{ $dial }}">{{ $display }}</a>@endforeach</div>@endif
        <a href="{{ whatsapp_url('Hi Last Solution, I would like to ask about your services.') }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-whatsapp"/></svg>Chat on WhatsApp</a>
        @if ($shop['email'])<a href="mailto:{{ $shop['email'] }}"><svg class="icon"><use href="#icon-mail"/></svg>{{ $shop['email'] }}</a>@endif
        <a href="{{ $shop['maps_url'] }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-pin"/></svg>{{ $shop['address'] }}</a>
      </div>
    </div>

    <div class="wrap footer-bottom"><span>© {{ date('Y') }} {{ $shop['legal_name'] }}. All rights reserved.</span><span>Secure your life, anytime &amp; anywhere.</span></div>
  </footer>

  {{-- Repair booking --}}
  <dialog id="repairDialog" aria-labelledby="repair-title">
    <button class="close" type="button" data-close aria-label="Close repair form"><svg class="icon"><use href="#icon-close"/></svg></button>
    <div class="dialog-icon"><svg class="icon"><use href="#icon-wrench"/></svg></div>
    <div class="eyebrow brand-text">TELL US WHAT YOU NEED</div>
    <h2 id="repair-title">Tell us about your property.</h2>
    <p>Share your location and the service you need. Our team will contact you to discuss the next steps.</p>
    <form class="js-enquiry" action="{{ route('enquiries.store') }}" method="post">
      @csrf
      <input type="hidden" name="type" value="repair">
      @include('partials.honeypot')
      <label>Your name<input name="name" required autocomplete="name" placeholder="Your name" maxlength="100"></label>
      <label>Phone number<input name="phone" type="tel" required autocomplete="tel" placeholder="e.g. 076 722 3260" maxlength="20"></label>
      <label>Property / location<input name="device" required placeholder="Your property and area" maxlength="160"></label>
      <label>Service<select name="service" id="service">@foreach ($shop['services'] as $service)<option>{{ $service }}</option>@endforeach</select></label>
      <label>How can we help?<textarea name="message" required placeholder="Tell us about the installation, repair or service you need…" maxlength="2000"></textarea></label>
      <div class="form-error" hidden></div>
      <button class="button red" type="submit">Send request<svg class="icon"><use href="#icon-arrow"/></svg></button>
    </form>
    <div class="enquiry-done" hidden></div>
  </dialog>

  {{-- Product / part enquiry --}}
  <dialog id="enquiryDialog" aria-labelledby="enquiry-title">
    <button class="close" type="button" data-close aria-label="Close enquiry form"><svg class="icon"><use href="#icon-close"/></svg></button>
    <div class="dialog-icon"><svg class="icon"><use href="#icon-search"/></svg></div>
    <div class="eyebrow brand-text">TALK TO OUR TEAM</div>
    <h2 id="enquiry-title">Ask us about this item.</h2>
    <p class="enquiry-subject"></p>
    <form class="js-enquiry" action="{{ route('enquiries.store') }}" method="post">
      @csrf
      <input type="hidden" name="type" value="product">
      <input type="hidden" name="product_id">
      @include('partials.honeypot')
      <label>Your name<input name="name" required autocomplete="name" placeholder="Your name" maxlength="100"></label>
      <label>Phone number<input name="phone" type="tel" required autocomplete="tel" placeholder="e.g. 076 722 3260" maxlength="20"></label>
      <label>Model number <small>(optional)</small><input name="details[part_number]" maxlength="100" placeholder="Camera or recorder model"></label>
      <label>Anything else? <small>(optional)</small><textarea name="message" maxlength="2000" placeholder="Tell us what you are looking for…"></textarea></label>
      <div class="form-error" hidden></div>
      <button class="button red" type="submit">Send enquiry<svg class="icon"><use href="#icon-arrow"/></svg></button>
    </form>
    <div class="enquiry-done" hidden></div>
  </dialog>

  {{-- Floating WhatsApp button (tablet & desktop; phones use the bar below) --}}
  <a class="wa-float" href="{{ whatsapp_url('Hi Last Solution, I would like to ask about your services.') }}" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp">
    <svg class="icon" aria-hidden="true"><use href="#icon-whatsapp"/></svg><span>Chat with us</span>
  </a>

  {{-- Mobile quick actions --}}
  <div class="mobile-actions" aria-label="Quick contact">
    <a class="ma-call" href="tel:{{ $shop['phone'] }}"><svg class="icon"><use href="#icon-phone"/></svg><span>Call us</span></a>
    <a class="ma-whatsapp" href="{{ whatsapp_url('Hi Last Solution, I would like to ask about your services.') }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-whatsapp"/></svg><span>WhatsApp</span></a>
  </div>

  <div id="toast" role="status" aria-live="polite"></div>
  <script src="{{ asset('app.js') }}?v={{ filemtime(public_path('app.js')) }}" defer></script>
</body>
</html>
