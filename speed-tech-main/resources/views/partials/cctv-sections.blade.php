{{-- CCTV deep-dive sections (property types, camera guide, packages, mobile viewing). Shown on the CCTV installation page. Content: config/shop.php --}}
  {{-- Solutions by property type --}}
  @if ($propertySolutions = config('shop.property_solutions'))
    <section class="wrap home-section" aria-labelledby="property-title">
      <div class="section-intro">
        <div class="eyebrow brand-text">CCTV FOR EVERY PROPERTY</div>
        <h2 id="property-title">Security solutions for every property</h2>
        <p>Every site is different. We plan camera positions around your entrances, blind spots and the areas that matter most to you.</p>
      </div>
      <div class="property-grid">
        @foreach ($propertySolutions as [$icon, $propertyTitle, $text, $covers])
          <article class="property-card">
            <span class="property-icon"><svg class="icon"><use href="#icon-{{ $icon }}"/></svg></span>
            <h3>{{ $propertyTitle }}</h3>
            <p>{{ $text }}</p>
            <small><svg class="icon"><use href="#icon-camera"/></svg>{{ $covers }}</small>
            <a class="property-link" href="{{ whatsapp_url('Hi Last Solution, I would like a CCTV quote for: '.$propertyTitle) }}" target="_blank" rel="noopener">Get a quote<svg class="icon"><use href="#icon-arrow"/></svg></a>
          </article>
        @endforeach
      </div>
    </section>
  @endif

  {{-- Camera buying guide --}}
  @if ($cameraGuide = config('shop.camera_guide'))
    <section class="camera-guide" aria-labelledby="guide-title">
      <div class="wrap">
        <div class="section-intro split">
          <div><div class="eyebrow brand-text">CAMERA GUIDE</div><h2 id="guide-title">Which camera is right for you?</h2></div>
          <p>Not sure what to choose? Here is a quick guide. We will always recommend the right mix for your property before installing.</p>
        </div>
        <div class="guide-grid">
          @foreach ($cameraGuide as [$icon, $name, $bestFor, $points, $term])
            <article class="guide-card">
              <span class="guide-icon"><svg class="icon"><use href="#icon-{{ $icon }}"/></svg></span>
              <h3>{{ $name }}</h3>
              <p class="guide-best">{{ $bestFor }}</p>
              <ul>@foreach ($points as $point)<li><svg class="icon"><use href="#icon-check"/></svg>{{ $point }}</li>@endforeach</ul>
              <a class="guide-link" href="{{ route('shop', ['q' => $term]) }}">See {{ $name }}<svg class="icon"><use href="#icon-arrow"/></svg></a>
            </article>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  {{-- CCTV packages --}}
  @if ($packages = config('shop.packages'))
    <section class="packages-band" aria-labelledby="packages-title">
      <div class="wrap">
        <div class="section-intro center light">
          <div class="eyebrow">COMPLETE SYSTEMS</div>
          <h2 id="packages-title">CCTV packages, fully installed</h2>
          <p>Cameras, recorder, storage, cabling and mobile setup in one package. Every quote is based on your site and cable runs.</p>
        </div>
        <div class="package-grid">
          @foreach ($packages as [$name, $cameras, $idealFor, $includes, $highlight])
            <article @class(['package-card', 'is-highlight' => $highlight])>
              @if ($highlight)<span class="package-flag">Recommended for shops</span>@endif
              <h3>{{ $name }}</h3>
              <div class="package-cams"><svg class="icon"><use href="#icon-camera"/></svg>{{ $cameras }}</div>
              <p class="package-for">{{ $idealFor }}</p>
              <ul>@foreach ($includes as $item)<li><svg class="icon"><use href="#icon-check"/></svg>{{ $item }}</li>@endforeach</ul>
              <a @class(['button', 'full', 'red' => $highlight, 'package-btn' => ! $highlight]) href="{{ whatsapp_url('Hi Last Solution, I would like a quote for the '.$name.' CCTV package ('.$cameras.').') }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-whatsapp"/></svg>Get a quote</a>
            </article>
          @endforeach
        </div>
        <p class="packages-note"><a href="{{ route('shop', ['category' => 'cctv-packages']) }}">See CCTV packages in the shop<svg class="icon"><use href="#icon-arrow"/></svg></a></p>
      </div>
    </section>
  @endif

  {{-- Mobile viewing --}}
  @if ($mobileFeatures = config('shop.mobile_features'))
    <section class="mobile-band" aria-labelledby="mobile-title">
      <div class="wrap mobile-view">
        <div class="phone-stage" aria-hidden="true">
          <div class="phone-frame">
            <div class="phone-notch"></div>
            <div class="phone-screen">
              <div class="app-top">
                <div><small>My places</small><b>Home · 4 cameras</b></div>
                <span class="phone-live">LIVE</span>
              </div>
              <div class="phone-feed">
                <picture><source {{ responsive_img('assets/hero/cctv-bullet-cameras.webp', '(max-width:700px) 100vw, 50vw') }} type="image/webp"><img src="{{ asset('assets/hero/cctv-bullet-cameras.jpg') }}" alt="" loading="lazy" width="1600" height="1067"></picture>
                <span class="feed-tag"><i></i>CAM 01 · GATE</span>
                <span class="feed-time" data-mv-clock>--:--:--</span>
              </div>
              <div class="phone-grid">
                <div class="mini"><picture><source {{ responsive_img('assets/hero/cctv-dome-hikvision.webp', '(max-width:700px) 33vw, 220px') }} type="image/webp"><img src="{{ asset('assets/hero/cctv-dome-hikvision.jpg') }}" alt="" loading="lazy" width="1600" height="1067"></picture><span>CAM 02</span></div>
                <div class="mini"><picture><source {{ responsive_img('assets/hero/cctv-dome-outdoor.webp', '(max-width:700px) 33vw, 220px') }} type="image/webp"><img src="{{ asset('assets/hero/cctv-dome-outdoor.jpg') }}" alt="" loading="lazy" width="1600" height="1067"></picture><span>CAM 03</span></div>
                <div class="mini night"><picture><source {{ responsive_img('assets/hero/cctv-bullet-cameras.webp', '(max-width:700px) 33vw, 220px') }} type="image/webp"><img src="{{ asset('assets/hero/cctv-bullet-cameras.jpg') }}" alt="" loading="lazy" width="1600" height="1067"></picture><span>CAM 04 · IR</span></div>
                <div class="mini add"><svg class="icon"><use href="#icon-plus"/></svg><span>Add</span></div>
              </div>
              <div class="timeline">
                <div class="timeline-head"><b>Today</b><small>Playback</small></div>
                <div class="timeline-bar"><i style="left:12%;width:6%"></i><i style="left:34%;width:3%"></i><i style="left:58%;width:9%"></i><i style="left:81%;width:4%"></i><span class="timeline-now"></span></div>
                <div class="timeline-scale"><span>00:00</span><span>06:00</span><span>12:00</span><span>18:00</span></div>
              </div>
              <div class="app-tabs">
                <span class="is-on"><svg class="icon"><use href="#icon-play"/></svg>Live</span>
                <span><svg class="icon"><use href="#icon-clock"/></svg>Playback</span>
                <span><svg class="icon"><use href="#icon-bell"/></svg>Alerts</span>
                <span><svg class="icon"><use href="#icon-users"/></svg>Share</span>
              </div>
            </div>
          </div>

          <div class="float-card fc-alert">
            <span class="fc-icon red"><svg class="icon"><use href="#icon-bell"/></svg></span>
            <span><b>Motion detected</b><small>CAM 01 · Gate · just now</small></span>
          </div>
          <div class="float-card fc-playback">
            <span class="fc-icon blue"><svg class="icon"><use href="#icon-clock"/></svg></span>
            <span><b>Playback</b><small>Today · 08:42 AM</small></span>
          </div>
          <div class="float-card fc-online"><i></i>4 cameras online</div>
        </div>

        <div class="mobile-copy">
          <div class="eyebrow brand-text">WATCH FROM ANYWHERE</div>
          <h2 id="mobile-title">Your cameras, <span>in your pocket.</span></h2>
          <p>We set up the mobile app on your phone during installation, so you can check your home or business at any time, from anywhere with an internet connection.</p>
          <div class="feature-grid">
            @foreach ($mobileFeatures as [$icon, $featureTitle, $text])
              <div class="feature"><span><svg class="icon"><use href="#icon-{{ $icon }}"/></svg></span><div><h3>{{ $featureTitle }}</h3><p>{{ $text }}</p></div></div>
            @endforeach
          </div>
          <div class="mobile-actions-row">
            <a class="button red" href="{{ whatsapp_url('Hi Last Solution, I would like CCTV with mobile viewing.') }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-whatsapp"/></svg>Ask about mobile viewing</a>
            <a class="button mobile-secondary" href="{{ route('shop', ['q' => 'wi-fi']) }}"><svg class="icon"><use href="#icon-wifi"/></svg>See Wi-Fi cameras</a>
          </div>
          <p class="mobile-note"><svg class="icon"><use href="#icon-check"/></svg>Works on Android and iPhone. We show you how to use it before we leave.</p>
        </div>
      </div>
    </section>
    <script>
      (() => {
        const el = document.querySelector('[data-mv-clock]');
        if (!el) return;
        const pad = n => String(n).padStart(2, '0');
        const tick = () => { const d = new Date(); el.textContent = `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`; };
        tick(); setInterval(tick, 1000);
      })();
    </script>
  @endif

