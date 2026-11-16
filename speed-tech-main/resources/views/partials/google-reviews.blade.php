{{-- Google reviews. $reviews comes from App\Services\GoogleReviews (null when no API key is set). --}}
@php($google = app(\App\Services\GoogleReviews::class))
@php($writeUrl = $google->writeReviewUrl())
<section class="wrap home-section google-reviews" aria-labelledby="reviews-title">
  <div class="section-head">
    <div><div class="eyebrow brand-text">REVIEWS FROM GOOGLE</div><h2 id="reviews-title">What our customers say</h2></div>
    @if ($reviews && count($reviews['reviews']) > 1)
      <div class="gr-arrows">
        <button type="button" class="gr-arrow" data-review-prev aria-label="Previous reviews"><svg class="icon"><use href="#icon-arrow"/></svg></button>
        <button type="button" class="gr-arrow" data-review-next aria-label="Next reviews"><svg class="icon"><use href="#icon-arrow"/></svg></button>
      </div>
    @endif
  </div>

  <div class="gr-layout">
    <div class="gr-summary">
      <span class="gr-google" aria-hidden="true"><b style="color:#4285f4">G</b><b style="color:#ea4335">o</b><b style="color:#fbbc05">o</b><b style="color:#4285f4">g</b><b style="color:#34a853">l</b><b style="color:#ea4335">e</b></span>
      @if ($reviews && $reviews['count'] > 0)
        <div class="gr-score"><b>{{ number_format($reviews['rating'], 1) }}</b>
          <span class="gr-stars" style="--rating:{{ $reviews['rating'] }}" role="img" aria-label="Rated {{ number_format($reviews['rating'], 1) }} out of 5"></span>
        </div>
        <p>Based on {{ $reviews['count'] }} {{ Str::plural('review', $reviews['count']) }} from our customers on Google</p>
      @else
        <p class="gr-intro">See what customers in Kandy say about our CCTV installation, repairs and service.</p>
      @endif
      <div class="gr-actions">
        <a class="button red" href="{{ $reviews['url'] ?? $google->listingUrl() }}" target="_blank" rel="noopener">Read all reviews<svg class="icon"><use href="#icon-arrow"/></svg></a>
        <a class="button button-outline" href="{{ $writeUrl ?? $google->listingUrl() }}" target="_blank" rel="noopener">Write a review</a>
      </div>
    </div>

    @if ($reviews && $reviews['reviews'])
      <div class="gr-carousel" data-review-carousel>
      <div class="gr-list" data-review-track tabindex="0" aria-label="Customer reviews, scroll sideways for more">
        @foreach ($reviews['reviews'] as $review)
          <figure class="gr-card">
            <figcaption>
              @if ($review['photo'])<img src="{{ $review['photo'] }}" alt="" width="40" height="40" loading="lazy" referrerpolicy="no-referrer">@else<span class="gr-avatar" aria-hidden="true">{{ Str::upper(Str::substr($review['author'], 0, 1)) }}</span>@endif
              <span><b>@if ($review['author_url'])<a href="{{ $review['author_url'] }}" target="_blank" rel="noopener nofollow">{{ $review['author'] }}</a>@else{{ $review['author'] }}@endif</b><small>{{ $review['when'] }}</small></span>
            </figcaption>
            <span class="gr-stars" style="--rating:{{ $review['rating'] }}" role="img" aria-label="{{ $review['rating'] }} out of 5 stars"></span>
            @if ($review['text'])<blockquote>{{ $review['text'] }}</blockquote>@endif
            @if ($review['url'])<a class="gr-more" href="{{ $review['url'] }}" target="_blank" rel="noopener">Read on Google</a>@endif
          </figure>
        @endforeach
      </div>
      <div class="gr-progress" aria-hidden="true"><i data-review-progress></i></div>
      </div>
    @endif
  </div>
</section>
