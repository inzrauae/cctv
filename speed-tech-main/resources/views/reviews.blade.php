@extends('layouts.app', [
  'title' => 'Customer reviews',
  'description' => 'What customers say about Last Solution (Pvt) Ltd: CCTV installation, repairs and service in Kandy. Reviews from Google.',
])

@section('content')
  @php($writeUrl = $google->writeReviewUrl() ?? $google->listingUrl())
  <section class="offers-hero">
    <div class="wrap offers-hero-inner">
      <div>
        <nav class="breadcrumbs light" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><b>Reviews</b></nav>
        <div class="offers-kicker"><svg class="icon"><use href="#icon-users"/></svg>REVIEWS FROM GOOGLE</div>
        <h1>What our customers say</h1>
        <p>Real reviews from homes and businesses around Kandy that trusted us with their CCTV, security and smart home systems.</p>
      </div>
      @if ($total)
        <div class="offers-count"><b>{{ number_format($average, 1) }}★</b><span>{{ $total }} {{ Str::plural('review', $total) }}</span></div>
      @endif
    </div>
  </section>

  <section class="wrap home-section reviews-page">
    <div class="gr-actions" style="margin-bottom:20px">
      <a class="button red" href="{{ $writeUrl }}" target="_blank" rel="noopener">Write a review on Google<svg class="icon"><use href="#icon-arrow"/></svg></a>
      <a class="button button-outline" href="{{ $google->listingUrl() }}" target="_blank" rel="noopener">See us on Google Maps</a>
    </div>

    @if ($reviews->count())
      <div class="gr-list gr-list-page">
        @foreach ($reviews as $review)
          <figure class="gr-card">
            <figcaption>
              @if ($review->author_photo_url)<img src="{{ $review->author_photo_url }}" alt="" width="40" height="40" loading="lazy" referrerpolicy="no-referrer">@else<span class="gr-avatar" aria-hidden="true">{{ $review->initial() }}</span>@endif
              <span><b>{{ $review->author_name }}</b><small>{{ $review->when() }}</small></span>
            </figcaption>
            <span class="gr-stars" style="--rating:{{ $review->rating }}" role="img" aria-label="{{ $review->rating }} out of 5 stars"></span>
            @if ($review->text)<blockquote class="full">{{ $review->text }}</blockquote>@endif
            @if ($review->reply)<div class="gr-reply"><b>Reply from Last Solution</b>{{ $review->reply }}</div>@endif
          </figure>
        @endforeach
      </div>
      @include('partials.pagination', ['paginator' => $reviews])
    @else
      <p class="gr-intro">Our reviews are on Google. Tap the button above to read them or leave your own.</p>
    @endif
  </section>
@endsection
