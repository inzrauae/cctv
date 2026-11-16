{{--
  Search engine and social tags for every page (included in the <head> of layouts/app).
  Pages can pass: $title, $description, $ogImage (path in public/ or full URL), $ogType, $noindex.
  Page-specific structured data goes in @push('schema') as plain PHP arrays via @include('partials.schema', [...]).
--}}
@php
  $shop = config('shop');
  $pageTitle = isset($title) ? $title.' | '.$shop['name'] : $shop['name'].' | CCTV & Security Solutions in Kandy';
  $pageDescription = $description ?? 'CCTV, alarm systems, smart door locks, intercoms, networking, Wi-Fi and smart home devices, plus A/C and appliance repair, solar power and battery backup. Last Solution (Pvt) Ltd, Kandy, Sri Lanka.';

  // Canonical URL: the page without sorting/stock filters, keeping only what changes the content.
  $keep = array_filter(request()->only(['category', 'sub', 'brand']), 'filled');
  if ((int) request('page') > 1) { $keep['page'] = (int) request('page'); }
  $canonical = request()->url().($keep ? '?'.http_build_query($keep) : '');

  // Pages that should not appear in search results.
  $noindex = ($noindex ?? false)
      || request()->routeIs('cart', 'checkout', 'orders.*', 'install')
      || (request()->routeIs('shop') && filled(request('q')));

  $image = $ogImage ?? 'assets/hero/cctv-dome-hikvision.jpg';
  $image = str_contains($image, '://') ? $image : asset($image);
@endphp
  <link rel="canonical" href="{{ $canonical }}">
  <meta name="robots" content="{{ $noindex ? 'noindex, follow' : 'index, follow, max-image-preview:large, max-snippet:-1' }}">
  <meta property="og:site_name" content="{{ $shop['name'] }}">
  <meta property="og:type" content="{{ $ogType ?? 'website' }}">
  <meta property="og:locale" content="en_LK">
  <meta property="og:title" content="{{ $pageTitle }}">
  <meta property="og:description" content="{{ $pageDescription }}">
  <meta property="og:url" content="{{ $canonical }}">
  <meta property="og:image" content="{{ $image }}">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="{{ $pageTitle }}">
  <meta name="twitter:description" content="{{ $pageDescription }}">
  <meta name="twitter:image" content="{{ $image }}">
  <link rel="alternate" type="text/plain" title="About Last Solution for AI assistants" href="{{ url('/llms.txt') }}">

@php
  $business = array_filter([
      '@type' => 'LocalBusiness',
      '@id' => url('/').'#business',
      'name' => $shop['legal_name'],
      'alternateName' => 'Last Solution',
      'description' => 'CCTV installation, repair and service, alarm systems, smart door locks, intercoms, networking and Wi-Fi, smart home devices, A/C and appliance repair, solar power and battery backup in Kandy, Sri Lanka.',
      'url' => url('/'),
      'logo' => asset('assets/last-solution-logo.png'),
      'image' => asset('assets/hero/cctv-dome-hikvision.jpg'),
      'telephone' => $shop['phone'],
      'email' => $shop['email'] ?: null,
      'address' => [
          '@type' => 'PostalAddress',
          'streetAddress' => $shop['street'],
          'addressLocality' => $shop['locality'],
          'addressRegion' => $shop['region'],
          'addressCountry' => 'LK',
      ],
      'geo' => ['@type' => 'GeoCoordinates', 'latitude' => $shop['geo'][0], 'longitude' => $shop['geo'][1]],
      'hasMap' => $shop['google_reviews']['listing_url'],
      'areaServed' => [['@type' => 'City', 'name' => 'Kandy'], ['@type' => 'Country', 'name' => 'Sri Lanka']],
      'knowsAbout' => ['CCTV installation', 'CCTV repair', 'IP cameras', 'Analog HD cameras', 'DVR and NVR recorders', 'Alarm systems', 'Smart door locks', 'Intercoms', 'Network installation', 'Wi-Fi', 'Smart home', 'A/C repair', 'Solar power', 'Battery backup'],
      'brand' => array_map(fn ($b) => ['@type' => 'Brand', 'name' => $b], $shop['brands']),
      'sameAs' => array_values(array_filter([$shop['facebook_url'], $shop['tiktok_url'], $shop['google_reviews']['listing_url']])),
  ]);
  $site = [
      '@type' => 'WebSite',
      '@id' => url('/').'#website',
      'url' => url('/'),
      'name' => $shop['name'],
      'publisher' => ['@id' => url('/').'#business'],
      'potentialAction' => [
          '@type' => 'SearchAction',
          'target' => ['@type' => 'EntryPoint', 'urlTemplate' => route('shop').'?q={search_term_string}'],
          'query-input' => 'required name=search_term_string',
      ],
  ];
@endphp
  <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@graph' => [$business, $site]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
  @stack('schema')
