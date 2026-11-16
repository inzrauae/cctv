@extends('admin.layout', ['title' => $review->exists ? 'Edit review' : 'Add review', 'breadcrumb' => '<a href="'.route('admin.reviews.index').'">Customer reviews</a>'])

@section('content')
  <form method="post" action="{{ $review->exists ? route('admin.reviews.update', $review) : route('admin.reviews.store') }}" class="card" style="max-width:760px">
    @csrf @if ($review->exists) @method('PUT') @endif
    <div class="form-grid">
      <label class="field">Customer name<input name="author_name" value="{{ old('author_name', $review->author_name) }}" maxlength="120" required></label>
      <label class="field">Stars
        <select name="rating">@foreach ([5, 4, 3, 2, 1] as $n)<option value="{{ $n }}" @selected((int) old('rating', $review->rating) === $n)>{{ str_repeat('★', $n) }} ({{ $n }})</option>@endforeach</select>
      </label>
      <label class="field span-2">Review <small>— the customer's own words</small><textarea name="text" rows="5" maxlength="5000">{{ old('text', $review->text) }}</textarea></label>
      <label class="field span-2">Your reply <small>— optional, shown under the review</small><textarea name="reply" rows="3" maxlength="5000">{{ old('reply', $review->reply) }}</textarea></label>
      <label class="field">Date of review<input type="date" name="reviewed_at" value="{{ old('reviewed_at', $review->reviewed_at?->format('Y-m-d')) }}"></label>
      <div class="field" style="display:flex;flex-direction:column;gap:10px;justify-content:flex-end">
        <span><input type="hidden" name="is_visible" value="0"><label class="check"><input type="checkbox" name="is_visible" value="1" @checked(old('is_visible', $review->is_visible))> Show on the website</label></span>
        <span><input type="hidden" name="is_featured" value="0"><label class="check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $review->is_featured))> Featured (shown first on the home page)</label></span>
      </div>
    </div>
    <div class="form-actions" style="margin-top:18px">
      <button class="btn primary" type="submit">{{ $review->exists ? 'Save review' : 'Add review' }}</button>
      <a class="btn" href="{{ route('admin.reviews.index') }}">Cancel</a>
    </div>
  </form>
@endsection
