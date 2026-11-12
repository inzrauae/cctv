{{-- Photo slideshow behind a page hero, same look as the home hero. $images = ['file-name' => 'object-position', ...] in public/assets/hero --}}
<div class="hero-media" aria-hidden="true" data-page-hero>
  @foreach ($images as $image => $position)
    <picture class="hero-slide {{ $loop->first ? 'is-active' : '' }}" data-slide>
      <source srcset="{{ asset('assets/hero/'.$image.'.webp') }}" type="image/webp">
      <img src="{{ asset('assets/hero/'.$image.'.jpg') }}" alt="" width="1600" height="1067" style="object-position:{{ $position }}" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
    </picture>
  @endforeach
</div>
<div class="hero-fade" aria-hidden="true"></div>
