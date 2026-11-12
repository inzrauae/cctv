@extends('layouts.app')

@section('content')
  @php($shop = config('shop'))
  @php($services = collect(config('shop.repair_services'))->keyBy('slug'))

  {{-- Hero: one slide per service family (photos: Pexels, free licence). Edit in config/shop.php → hero_slides. --}}
  @php($slides = config('shop.hero_slides'))
  <section class="security-hero photo-hero" data-hero aria-roledescription="carousel" aria-label="Our services">
    <div class="hero-media" aria-hidden="true">
      @foreach ($slides as $i => $slide)
        <picture class="hero-slide {{ $i === 0 ? 'is-active' : '' }}" data-slide>
          <source srcset="{{ asset('assets/hero/'.$slide[0].'.webp') }}" type="image/webp">
          <img src="{{ asset('assets/hero/'.$slide[0].'.jpg') }}" alt="" width="1600" height="1067" style="object-position:{{ $slide[1] }}" @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif>
        </picture>
      @endforeach
    </div>
    <div class="hero-fade" aria-hidden="true"></div>
    <h1 class="visually-hidden">Last Solution: CCTV, security, smart home, networking, A/C and appliance repair, solar and battery backup in Kandy</h1>

    <div class="wrap security-hero-inner">
      <div class="hero-copy-stack">
        @foreach ($slides as $i => [$file, $focus, $kicker, $headline, $highlight, $text, $slug, $tab])
          <div @class(['security-copy', 'hero-copy', 'is-active' => $i === 0]) data-copy @if ($i > 0) inert aria-hidden="true" @endif>
            <div class="eyebrow"><span class="status-dot"></span>{{ $kicker }}</div>
            <p class="hero-title">{{ $headline }}<br><em>{{ $highlight }}</em></p>
            <p>{{ $text }}</p>
            <div class="buttons">
              <a class="button red" href="{{ whatsapp_url('Hi Last Solution, I would like a quote for '.Str::lower($tab).'.') }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-whatsapp"/></svg>Get a quote<svg class="icon"><use href="#icon-arrow"/></svg></a>
              <a class="button security-button-outline" href="{{ route('repairs.show', $slug) }}">Learn more</a>
            </div>
          </div>
        @endforeach
        <ul class="hero-points">
          <li><svg class="icon"><use href="#icon-check"/></svg>Local team in Nugawela</li>
          <li><svg class="icon"><use href="#icon-check"/></svg>Trusted brands</li>
          <li><svg class="icon"><use href="#icon-check"/></svg>Installation, repair &amp; service</li>
        </ul>
      </div>

      <div class="hero-overlay" aria-hidden="true">
        <a class="hero-call" href="tel:{{ $shop['phone'] }}" tabindex="-1"><svg class="icon"><use href="#icon-phone"/></svg><span><small>Call or WhatsApp</small><b>{{ $shop['phone_display'] }}</b></span></a>
        <div class="hero-caption">
          @foreach ($slides as $i => $slide)
            <div class="hero-caption-item {{ $i === 0 ? 'is-active' : '' }}" data-caption><b>{{ $slide[8] }}</b><small>{{ $slide[9] }}</small></div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="wrap hero-tabs">
      <div class="tabs-bar" role="tablist" aria-label="Choose a service">
        @foreach ($slides as $i => $slide)
          <button type="button" role="tab" @class(['is-active' => $i === 0]) data-dot="{{ $i }}" aria-selected="{{ $i === 0 ? 'true' : 'false' }}"><em>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</em><span>{{ $slide[7] }}</span><i class="tab-progress"></i></button>
        @endforeach
      </div>
    </div>
    <div class="hero-ribbon" aria-hidden="true"><span>Secure your life</span><b>Anytime &amp; anywhere</b></div>
  </section>
  <script>
    (() => {
      const hero = document.querySelector('[data-hero]');
      if (!hero) return;
      const slides = hero.querySelectorAll('[data-slide]');
      const copies = hero.querySelectorAll('[data-copy]');
      const captions = hero.querySelectorAll('[data-caption]');
      const tabs = hero.querySelectorAll('[data-dot]');
      const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      const delay = 7000;
      let current = 0, timer = null, paused = false;
      const show = i => {
        current = (i + slides.length) % slides.length;
        [slides, captions].forEach(list => list.forEach((el, n) => el.classList.toggle('is-active', n === current)));
        copies.forEach((el, n) => {
          const on = n === current;
          el.classList.toggle('is-active', on);
          el.toggleAttribute('inert', !on);
          on ? el.removeAttribute('aria-hidden') : el.setAttribute('aria-hidden', 'true');
        });
        tabs.forEach((tab, n) => { tab.classList.toggle('is-active', n === current); tab.setAttribute('aria-selected', n === current ? 'true' : 'false'); });
        const bar = hero.querySelector('.hero-tabs');
        if (bar.scrollWidth > bar.clientWidth) bar.scrollTo({ left: tabs[current].offsetLeft - 24, behavior: reduce ? 'auto' : 'smooth' });
        hero.style.setProperty('--slide-time', delay + 'ms');
        tabs[current].querySelector('.tab-progress').style.animation = 'none';
        void tabs[current].offsetWidth;
        tabs[current].querySelector('.tab-progress').style.animation = '';
      };
      const start = () => { clearInterval(timer); if (!reduce && !paused) timer = setInterval(() => show(current + 1), delay); };
      tabs.forEach(tab => tab.addEventListener('click', () => { show(+tab.dataset.dot); start(); }));
      hero.addEventListener('mouseenter', () => { paused = true; hero.classList.add('is-paused'); clearInterval(timer); });
      hero.addEventListener('mouseleave', () => { paused = false; hero.classList.remove('is-paused'); start(); });
      hero.addEventListener('focusin', () => { paused = true; hero.classList.add('is-paused'); clearInterval(timer); });
      hero.addEventListener('focusout', e => { if (!hero.contains(e.relatedTarget)) { paused = false; hero.classList.remove('is-paused'); start(); } });
      show(0); start();
    })();
  </script>

  {{-- Shop by category --}}
  @php($strip = collect(config('shop.home_strip'))->map(fn ($slug) => $categories->firstWhere('slug', $slug))->filter())
  @php($stripItems = ($strip->isNotEmpty() ? $strip : $categories)->take(5))
  <div class="wrap"><nav class="cat-strip" aria-labelledby="cat-strip-title">
    <div class="cat-strip-head"><h2 id="cat-strip-title"><svg class="icon"><use href="#icon-cart"/></svg>Shop by category</h2><a class="text-link" href="{{ route('shop') }}">View all products<svg class="icon"><use href="#icon-arrow"/></svg></a></div>
    @forelse ($stripItems as $n => $item)
      <a class="cat-tile tone-{{ $loop->index % 5 }}" href="{{ route('shop', ['category' => $item->slug]) }}">
        <span class="cat-icon"><svg class="icon"><use href="#icon-{{ $item->icon ?: 'box' }}"/></svg></span>
        <span class="cat-text"><b>{!! str_replace('-', '&#8209;', e($item->name)) !!}</b><small>{{ $item->products_count ? $item->products_count.' '.Str::plural('product', $item->products_count) : 'Ask for options' }}</small></span>
        <svg class="icon cat-go" aria-hidden="true"><use href="#icon-arrow"/></svg>
      </a>
    @empty
      @foreach ($services->take(5) as $service)
        <a class="cat-tile tone-{{ $loop->index % 5 }}" href="{{ route('repairs.show', $service['slug']) }}">
          <span class="cat-icon"><svg class="icon"><use href="#icon-{{ $service['icon'] }}"/></svg></span>
          <span class="cat-text"><b>{{ $service['name'] }}</b><small>View service</small></span>
          <svg class="icon cat-go" aria-hidden="true"><use href="#icon-arrow"/></svg>
        </a>
      @endforeach
    @endforelse
    <a class="cat-tile cat-all" href="{{ route('shop') }}">
      <span class="cat-icon"><svg class="icon"><use href="#icon-cart"/></svg></span>
      <span class="cat-text"><b>All products</b><small>Visit the shop</small></span>
      <svg class="icon cat-go" aria-hidden="true"><use href="#icon-arrow"/></svg>
    </a>
  </nav></div>

  {{-- Get a quote finder (like the parts finder): opens the booking form pre-filled --}}
  @php($propertyTypes = collect(config('shop.property_solutions', []))->pluck(1))
  <section class="wrap quote-finder" aria-labelledby="finder-title">
    <div class="finder-card">
      <div class="finder-head">
        <div><div class="eyebrow brand-text">THE RIGHT SOLUTION, FIRST TIME</div><h2 id="finder-title">Get a quote for your project.</h2></div>
        <p>Service <span>→</span> Property <span>→</span> Area<br>Our team will reply with options and a quote.</p>
      </div>
      <form class="finder-form" action="{{ route('repairs') }}" method="get" data-quote-finder>
        <label><span>01 <b>Service</b></span>
          <select name="service" required>
            <option value="">Select a service</option>
            @foreach (config('shop.service_groups') as $key => [$groupTitle])
              <optgroup label="{{ $groupTitle }}">
                @foreach ($services->where('group', $key) as $service)<option value="{{ $service['name'] }}">{{ $service['name'] }}</option>@endforeach
              </optgroup>
            @endforeach
          </select>
        </label>
        <label><span>02 <b>Property</b></span>
          <select name="property">
            <option value="">Select property type</option>
            @foreach ($propertyTypes as $type)<option>{{ $type }}</option>@endforeach
            <option>Other</option>
          </select>
        </label>
        <label><span>03 <b>Area / town</b></span><input name="area" maxlength="60" placeholder="e.g. Nugawela, Kandy" autocomplete="address-level2"></label>
        <button class="button red" type="submit">Get a quote<svg class="icon"><use href="#icon-arrow"/></svg></button>
      </form>
      <div class="finder-chips"><span>Popular:</span>
        @foreach (config('shop.finder_popular', []) as $name)<button type="button" data-pick="{{ $name }}">{{ $name }}</button>@endforeach
      </div>
    </div>
  </section>
  <script>
    (() => {
      const form = document.querySelector('[data-quote-finder]');
      if (!form) return;
      document.querySelectorAll('[data-pick]').forEach(chip => chip.addEventListener('click', () => {
        form.elements.service.value = chip.dataset.pick;
        form.elements.service.focus();
      }));
      form.addEventListener('submit', event => {
        if (typeof openRepair !== 'function') return; // fall back to the services page
        event.preventDefault();
        const { service, property, area } = form.elements;
        openRepair(service.value);
        const dialog = document.getElementById('repairDialog');
        const where = [property.value, area.value.trim()].filter(Boolean).join(', ');
        if (where && dialog) dialog.querySelector('[name="device"]').value = where;
      });
    })();
  </script>

  {{-- Our services sidebar + featured products --}}
  <section class="wrap home-split" aria-labelledby="featured-title">
    <aside class="services-panel">
      <h2><svg class="icon"><use href="#icon-wrench"/></svg>Our services</h2>
      <p class="panel-lead">Safe, connected, cool and powered. One local team.</p>
      <ul>
        @foreach ($services->take(8) as $service)
          <li><a href="{{ route('repairs.show', $service['slug']) }}"><svg class="icon"><use href="#icon-check"/></svg>{{ $service['name'] }}</a></li>
        @endforeach
      </ul>
      <button class="panel-button" type="button" data-repair>Book a service<svg class="icon"><use href="#icon-arrow"/></svg></button>
      <a class="panel-more" href="{{ route('repairs') }}">See all {{ $services->count() }} services →</a>
      <a class="panel-shop" href="{{ route('shop') }}"><svg class="icon"><use href="#icon-cart"/></svg><span><small>Looking for products?</small>Visit the shop →</span></a>
    </aside>

    <div class="solutions">
      <div class="solutions-head">
        <div><div class="eyebrow brand-text">QUALITY BRANDS, LOCAL SUPPORT</div><h2 id="featured-title">Featured products</h2></div>
        <a class="text-link" href="{{ route('shop') }}">Shop all products<svg class="icon"><use href="#icon-arrow"/></svg></a>
      </div>
      @if ($featured->isNotEmpty())
        <div class="products home-products">@each('partials.product-card', $featured, 'product')</div>
      @else
        <p class="empty-note">Products are coming soon. <a class="text-link" href="{{ whatsapp_url('Hi Last Solution, I am looking for a product.') }}" target="_blank" rel="noopener">Ask us on WhatsApp</a>.</p>
      @endif
    </div>
  </section>

  {{-- Offers (only when products are on offer) --}}
  @if ($offers->isNotEmpty())
    <section class="home-offers" aria-labelledby="offers-title">
      <div class="wrap offers-layout">
        <div class="offers-promo">
          <div class="offers-kicker"><svg class="icon"><use href="#icon-tag"/></svg>LIMITED-TIME DEALS</div>
          <h2 id="offers-title">Special offers</h2>
          <div class="offers-save"><small>Save up to</small><b>{{ $offerStats['max'] }}<span>%</span></b></div>
          <ul class="offers-facts">
            <li><svg class="icon"><use href="#icon-box"/></svg>{{ $offerStats['count'] }} {{ Str::plural('product', $offerStats['count']) }} on offer</li>
            @if ($offerStats['ends'])<li><svg class="icon"><use href="#icon-clock"/></svg>Some offers end {{ $offerStats['ends']->format('j M') }}</li>@endif
            <li><svg class="icon"><use href="#icon-wrench"/></svg>Installation available</li>
          </ul>
          <div class="offers-actions">
            <a class="button offers-all" href="{{ route('offers') }}">View all offers<svg class="icon"><use href="#icon-arrow"/></svg></a>
            <a class="button offers-wa" href="{{ whatsapp_url('Hi Last Solution, I would like to ask about your current offers.') }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-whatsapp"/></svg>Ask about a deal</a>
          </div>
          <svg class="offers-bg-icon icon" aria-hidden="true"><use href="#icon-tag"/></svg>
        </div>
        <div class="offers-cards">
          <div class="offers-cards-head">
            <h3>Top deals right now</h3>
            <a class="text-link" href="{{ route('offers') }}">See all {{ $offerStats['count'] }}<svg class="icon"><use href="#icon-arrow"/></svg></a>
          </div>
          <div class="products offers-row">@each('partials.product-card', $offers->take(3), 'product')</div>
        </div>
      </div>
    </section>
  @endif

  {{-- Our services: image carousel with family filters (swipe / arrows; all slides reachable without JS by scrolling) --}}
  @php($groupInfo = collect(config('shop.service_groups')))
  <section class="services-showcase" id="services" aria-labelledby="services-title" data-showcase>
    <div class="wrap">
      <div class="section-intro split light">
        <div><div class="eyebrow">WHAT WE DO</div><h2 id="services-title">Our services</h2></div>
        <div class="intro-side">
          <p>We install, repair and service the systems that keep your home or business safe, connected, cool and powered.</p>
          <div class="intro-actions">
            <button class="button red" type="button" data-repair><svg class="icon"><use href="#icon-wrench"/></svg>Book a service</button>
            <a class="button intro-ghost" href="{{ route('repairs') }}">All services<svg class="icon"><use href="#icon-arrow"/></svg></a>
          </div>
        </div>
      </div>

      <div class="showcase-bar">
        <div class="showcase-filters" role="group" aria-label="Filter services">
          <button type="button" class="is-active" data-filter="all" aria-pressed="true"><svg class="icon"><use href="#icon-check"/></svg>All<span>{{ $services->count() }}</span></button>
          @foreach ($groupInfo as $key => [$groupTitle, $groupIcon])
            @php($count = $services->where('group', $key)->count())
            @if ($count)
              <button type="button" data-filter="{{ $key }}" aria-pressed="false"><svg class="icon"><use href="#icon-{{ $groupIcon }}"/></svg>{{ $groupTitle }}<span>{{ $count }}</span></button>
            @endif
          @endforeach
        </div>
        <div class="carousel-arrows">
          <button type="button" class="carousel-arrow" data-prev aria-label="Previous services"><svg class="icon"><use href="#icon-arrow"/></svg></button>
          <button type="button" class="carousel-arrow" data-next aria-label="Next services"><svg class="icon"><use href="#icon-arrow"/></svg></button>
        </div>
      </div>
    </div>

    <div class="carousel-viewport">
      <div class="carousel-track" data-track tabindex="0" aria-label="Services carousel">
        @foreach ($services as $service)
          @php($group = $groupInfo[$service['group'] ?? ''] ?? null)
          <article class="service-slide tone-{{ $service['group'] ?? 'security' }}" data-group="{{ $service['group'] ?? '' }}" aria-label="{{ $service['name'] }}">
            @if (! empty($service['image']))
              <picture class="slide-photo" aria-hidden="true"><source srcset="{{ asset('assets/hero/'.$service['image'].'.webp') }}" type="image/webp"><img src="{{ asset('assets/hero/'.$service['image'].'.jpg') }}" alt="" loading="lazy" width="1600" height="1067"></picture>
            @endif
            <div class="slide-shade" aria-hidden="true"></div>
            @if ($group)<span class="slide-tag">{{ $group[0] }}</span>@endif
            <div class="slide-content">
              <span class="slide-icon"><svg class="icon"><use href="#icon-{{ $service['icon'] }}"/></svg></span>
              <h3><a href="{{ route('repairs.show', $service['slug']) }}">{{ $service['name'] }}</a></h3>
              <p>{{ $service['summary'] }}</p>
              <div class="slide-actions">
                <a class="slide-details" href="{{ route('repairs.show', $service['slug']) }}">Details<svg class="icon"><use href="#icon-arrow"/></svg></a>
                <a class="slide-quote" href="{{ whatsapp_url('Hi Last Solution, I would like a quote for '.Str::lower($service['name']).'.') }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-whatsapp"/></svg>Get a quote</a>
              </div>
            </div>
          </article>
        @endforeach
      </div>
    </div>

    <div class="wrap">
      <div class="carousel-progress" aria-hidden="true"><i data-progress></i></div>
      <ol class="showcase-steps">
        @foreach (config('shop.repair_process') as $i => [$step, $text])
          <li><span>{{ $i + 1 }}</span><b>{{ $step }}</b></li>
        @endforeach
      </ol>
    </div>
  </section>
  <script>
    (() => {
      const root = document.querySelector('[data-showcase]');
      if (!root) return;
      const track = root.querySelector('[data-track]');
      const slides = [...track.querySelectorAll('[data-group]')];
      const prev = root.querySelector('[data-prev]');
      const next = root.querySelector('[data-next]');
      const bar = root.querySelector('[data-progress]');
      const buttons = root.querySelectorAll('[data-filter]');
      const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      const step = () => { const s = slides.find(el => !el.hidden); return s ? s.getBoundingClientRect().width + parseFloat(getComputedStyle(track).columnGap || 16) : 300; };
      const update = () => {
        const max = track.scrollWidth - track.clientWidth;
        prev.disabled = track.scrollLeft <= 4;
        next.disabled = track.scrollLeft >= max - 4;
        const visible = max > 0 ? track.clientWidth / track.scrollWidth : 1;
        bar.style.width = Math.max(visible * 100, 8) + '%';
        bar.style.marginLeft = (max > 0 ? (track.scrollLeft / track.scrollWidth) * 100 : 0) + '%';
      };
      const go = dir => track.scrollBy({ left: dir * step(), behavior: reduce ? 'auto' : 'smooth' });
      prev.addEventListener('click', () => go(-1));
      next.addEventListener('click', () => go(1));
      track.addEventListener('keydown', e => { if (e.key === 'ArrowRight') { e.preventDefault(); go(1); } if (e.key === 'ArrowLeft') { e.preventDefault(); go(-1); } });
      track.addEventListener('scroll', () => requestAnimationFrame(update), { passive: true });
      window.addEventListener('resize', update);
      buttons.forEach(button => button.addEventListener('click', () => {
        const filter = button.dataset.filter;
        buttons.forEach(b => { const on = b === button; b.classList.toggle('is-active', on); b.setAttribute('aria-pressed', on ? 'true' : 'false'); });
        slides.forEach(slide => { slide.hidden = filter !== 'all' && slide.dataset.group !== filter; });
        track.scrollTo({ left: 0 });
        update();
        restart();
      }));
      update();

      // Autoplay: one card every 4 s, loops; pauses on hover, touch, keyboard focus,
      // when off-screen or the tab is hidden; off for reduced-motion users.
      const DELAY = 4000;
      let timer = null, hovering = false, focused = false, inView = false, touchedAt = 0;
      const canPlay = () => !reduce && !hovering && !focused && inView && !document.hidden && Date.now() - touchedAt > 8000;
      const tick = () => {
        if (!canPlay()) return;
        const max = track.scrollWidth - track.clientWidth;
        if (max <= 4) return;
        if (track.scrollLeft >= max - 4) track.scrollTo({ left: 0, behavior: 'smooth' });
        else go(1);
      };
      function restart() { clearInterval(timer); if (!reduce) timer = setInterval(tick, DELAY); }
      root.addEventListener('mouseenter', () => { hovering = true; });
      root.addEventListener('mouseleave', () => { hovering = false; });
      root.addEventListener('focusin', () => { focused = true; });
      root.addEventListener('focusout', e => { if (!root.contains(e.relatedTarget)) focused = false; });
      track.addEventListener('touchstart', () => { touchedAt = Date.now(); }, { passive: true });
      [prev, next].forEach(b => b.addEventListener('click', restart));
      if ('IntersectionObserver' in window) {
        new IntersectionObserver(entries => { inView = entries[0].isIntersecting; }, { threshold: 0.3 }).observe(track);
      } else {
        inView = true;
      }
      restart();
    })();
  </script>

  {{-- Shop by category: icon tiles --}}
  @if ($categories->isNotEmpty())
    <section class="wrap home-section type-tiles" aria-labelledby="types-title">
      <div class="section-head">
        <div><div class="eyebrow brand-text">SECURITY · SMART HOME · POWER</div><h2 id="types-title">Shop products by type</h2></div>
        <a class="text-link" href="{{ route('shop') }}">All products<svg class="icon"><use href="#icon-arrow"/></svg></a>
      </div>
      <div class="tile-grid">
        @foreach ($categories as $item)
          <a class="type-tile" href="{{ route('shop', ['category' => $item->slug]) }}">
            <svg class="icon"><use href="#icon-{{ $item->icon ?: 'box' }}"/></svg>
            <b>{{ $item->name }}</b>
            <small>{{ $item->products_count ? $item->products_count.' '.Str::plural('product', $item->products_count) : 'Ask for options' }}</small>
          </a>
        @endforeach
      </div>
    </section>
  @endif

  {{-- Promo · why choose · not sure --}}
  <section class="wrap ref-trio">
    <div class="ref-promo">
      <picture aria-hidden="true"><source srcset="{{ asset('assets/hero/network-switch.webp') }}" type="image/webp"><img src="{{ asset('assets/hero/network-switch.jpg') }}" alt="" loading="lazy" width="1600" height="1067"></picture>
      <div class="ref-promo-body">
        <div class="eyebrow">INSTALL · REPAIR · SERVICE</div>
        <h2>System not working?<br>Let's get it sorted.</h2>
        <p>From a camera that went offline to an A/C that stopped cooling, we'll help you find the next step.</p>
        <div class="ref-promo-actions">
          <button class="button red" type="button" data-repair>Book a repair<svg class="icon"><use href="#icon-arrow"/></svg></button>
          <a class="button ref-ghost" href="{{ route('repairs') }}">Our services</a>
        </div>
      </div>
    </div>
    <div class="ref-why">
      <h2>Why choose Last Solution?</h2>
      <p>A local team that does the job properly.</p>
      <ul>
        <li><svg class="icon"><use href="#icon-pin"/></svg>Local<br>team</li>
        <li><svg class="icon"><use href="#icon-shield"/></svg>Trusted<br>brands</li>
        <li><svg class="icon"><use href="#icon-wrench"/></svg>Install &amp;<br>repair</li>
        <li><svg class="icon"><use href="#icon-whatsapp"/></svg>Direct<br>support</li>
      </ul>
    </div>
    <div class="ref-help">
      <span class="ref-help-icon"><svg class="icon"><use href="#icon-whatsapp"/></svg></span>
      <h2>Not sure what you need?</h2>
      <p>Tell us about your property or the problem. We'll help you choose the right service or product.</p>
      <a class="text-link" href="{{ whatsapp_url('Hi Last Solution, I need some advice.') }}" target="_blank" rel="noopener">Talk to our team<svg class="icon"><use href="#icon-arrow"/></svg></a>
    </div>
  </section>

  {{-- Shop by brand --}}
  @if ($brandList = config('shop.brands'))
    <section class="wrap home-section maker-section" aria-labelledby="brands-title">
      <div class="section-head">
        <div><div class="eyebrow brand-text">TRUSTED BRANDS</div><h2 id="brands-title">Shop by brand</h2></div>
        <a class="text-link" href="{{ route('shop') }}">Open the shop<svg class="icon"><use href="#icon-arrow"/></svg></a>
      </div>
      <div class="maker-grid">
        @foreach ($brandList as $name)
          @php($items = $brandProducts->get($name, collect()))
          <a class="maker-card" href="{{ route('shop', ['brand' => $name]) }}">
            <div class="maker-head"><b>{{ $name }}</b><small>{{ $items->count() ? $items->count().' '.Str::plural('product', $items->count()) : 'Ask for models' }}</small></div>
            <ul>
              @forelse ($items->take(3) as $item)<li>{{ $item->name }}</li>@empty<li class="muted">Contact us for available models</li>@endforelse
            </ul>
            <span class="maker-link">All {{ $name }} products<svg class="icon"><use href="#icon-arrow"/></svg></span>
          </a>
        @endforeach
        <div class="maker-card maker-other">
          <b>Other brand?</b>
          <p>Already have equipment from another brand? Tell us the model and we'll check how we can help.</p>
          <a class="button red" href="{{ whatsapp_url('Hi Last Solution, I have equipment from another brand.') }}" target="_blank" rel="noopener">Ask us<svg class="icon"><use href="#icon-arrow"/></svg></a>
        </div>
      </div>
    </section>
  @endif

  {{-- Tips --}}
  @if ($tips = config('shop.home_tips'))
    <section class="wrap home-section home-tips" aria-labelledby="tips-title">
      <div class="section-head">
        <div><div class="eyebrow brand-text">GOOD TO KNOW</div><h2 id="tips-title">Simple tips that save you trouble</h2></div>
        <p>Small habits that prevent the most common problems we see.</p>
      </div>
      <div class="hint-grid">
        @foreach ($tips as [$icon, $tipTitle, $tipText, $tipLink, $tipSlug])
          <article class="hint-card">
            <span class="hint-icon"><svg class="icon"><use href="#icon-{{ $icon }}"/></svg></span>
            <h3>{{ $tipTitle }}</h3>
            <p>{{ $tipText }}</p>
            <a class="text-link" href="{{ route('repairs.show', $tipSlug) }}">{{ $tipLink }}<svg class="icon"><use href="#icon-arrow"/></svg></a>
          </article>
        @endforeach
      </div>
    </section>
  @endif

  {{-- FAQ + can't find what you need --}}
  <section class="home-faq" id="contact-help" aria-labelledby="home-faq-title">
    <div class="wrap home-faq-inner">
      <div>
        <div class="eyebrow brand-text">QUESTIONS BEFORE YOU BOOK</div>
        <h2 id="home-faq-title">Frequently asked questions</h2>
        <div class="faq-list">
          @foreach (config('shop.home_faqs', []) as $i => [$question, $answer])
            <details @if ($i === 0) open @endif><summary>{{ $question }}<svg class="icon"><use href="#icon-plus"/></svg></summary><p>{{ $answer }}</p></details>
          @endforeach
        </div>
      </div>
      <aside class="find-card" aria-label="Can't find what you need?">
        <span class="find-icon"><svg class="icon"><use href="#icon-search"/></svg></span>
        <h3>Can't find what you need?</h3>
        <p>Send us the model, a photo of your existing equipment or a short description. We'll check what's available and get back to you.</p>
        <div class="find-actions">
          <a class="button red" href="{{ route('contact') }}">Send a request<svg class="icon"><use href="#icon-arrow"/></svg></a>
          <a class="button whatsapp-link" href="{{ whatsapp_url('Hi Last Solution, I am sending a photo of what I need.') }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-whatsapp"/></svg>Send a photo</a>
        </div>
        <a class="find-visit" href="{{ $shop['maps_url'] }}" target="_blank" rel="noopener"><svg class="icon"><use href="#icon-pin"/></svg>{{ $shop['address'] }}<svg class="icon"><use href="#icon-arrow"/></svg></a>
      </aside>
    </div>
  </section>

  {{-- Benefits strip --}}
  <section class="benefit-strip" aria-label="Why buy from Last Solution">
    <div class="wrap benefit-grid">
      <div><span><svg class="icon"><use href="#icon-check"/></svg></span><p><b>Right solution, first time</b>We discuss your needs before quoting</p></div>
      <div><span><svg class="icon"><use href="#icon-truck"/></svg></span><p><b>Delivery or collection</b>Or collect from Nugawela, Kandy</p></div>
      <div><span><svg class="icon"><use href="#icon-shield"/></svg></span><p><b>Trusted brands</b>{{ implode(', ', array_slice(config('shop.brands'), 0, 3)) }} and more</p></div>
      <div><span><svg class="icon"><use href="#icon-wrench"/></svg></span><p><b>Expert installation</b>We can install it for you</p></div>
    </div>
  </section>
@endsection
