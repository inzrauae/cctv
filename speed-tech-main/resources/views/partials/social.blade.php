{{-- Social media links. Optional: $class (extra CSS class), $labels (show text labels). --}}
@php
  $links = array_filter([
    'facebook' => config('shop.facebook_url') ? ['Facebook', config('shop.facebook_url')] : null,
    'tiktok' => config('shop.tiktok_url') ? ['TikTok', config('shop.tiktok_url')] : null,
    'google' => config('shop.reviews_url') ? ['Google', config('shop.reviews_url')] : null,
  ]);
@endphp
@if ($links)
  <div class="social {{ $class ?? '' }}">
    @foreach ($links as $network => [$label, $url])
      <a class="social-{{ $network }}" href="{{ $url }}" target="_blank" rel="noopener" aria-label="Last Solution on {{ $label }}" title="Last Solution on {{ $label }}">
        <svg class="social-icon" aria-hidden="true"><use href="#social-{{ $network }}"/></svg>
        @if (! empty($labels))<span>{{ $label === 'Google' ? 'Google reviews' : $label }}</span>@endif
      </a>
    @endforeach
  </div>
@endif
