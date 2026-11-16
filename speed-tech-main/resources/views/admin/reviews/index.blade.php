@extends('admin.layout', ['title' => 'Customer reviews'])

@section('actions')
  <a class="btn" href="{{ route('reviews') }}" target="_blank" rel="noopener">View on site ↗</a>
  <a class="btn primary" href="{{ route('admin.reviews.create') }}"><svg class="icon"><use href="#icon-plus"/></svg>Add review</a>
@endsection

@section('content')
  <section class="card">
    <h2>Import from Google <small>free, brings in all your Google reviews</small></h2>
    <ol class="help" style="padding-left:18px;margin-bottom:12px">
      <li>Open <a class="link" href="https://takeout.google.com" target="_blank" rel="noopener">takeout.google.com</a> while signed in to the Google account that manages your Business Profile.</li>
      <li>Click <b>Deselect all</b>, tick only <b>Google Business Profile</b>, then <b>Next step → Create export</b>.</li>
      <li>When Google emails you, download the .zip and upload it here. Run it again any time to add new reviews: existing ones are updated, never duplicated.</li>
    </ol>
    <form class="inline-form" method="post" action="{{ route('admin.reviews.import') }}" enctype="multipart/form-data">
      @csrf
      <input type="file" name="file" accept=".zip,.json" required>
      <button class="btn primary" type="submit">Import reviews</button>
    </form>
  </section>

  <form class="filters" method="get">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Name or review text…">
    <select name="show" data-autosubmit>
      <option value="">All reviews</option>
      <option value="shown" @selected(request('show') === 'shown')>Shown on site</option>
      <option value="hidden" @selected(request('show') === 'hidden')>Hidden</option>
    </select>
    <button class="btn" type="submit">Filter</button>
    <span class="help" style="margin:0 0 0 auto;align-self:center">Shown on site: <b>{{ $stats->total }}</b> @if ($stats->total) · average <b>{{ number_format((float) $stats->average, 1) }}★</b>@endif</span>
  </form>

  <div class="table-wrap"><table class="data">
    <thead><tr><th>Customer</th><th>Stars</th><th>Review</th><th>On site</th><th></th></tr></thead>
    <tbody>
      @forelse ($reviews as $review)
        <tr>
          <td>{{ $review->author_name }}<small>{{ $review->reviewed_at?->format('d M Y') ?? 'No date' }} · {{ $review->source === 'google' ? 'Google' : 'Added here' }}</small></td>
          <td style="white-space:nowrap;color:#d99a00">{{ str_repeat('★', $review->rating) }}<span style="color:#d6dbe3">{{ str_repeat('★', 5 - $review->rating) }}</span></td>
          <td>{{ Str::limit($review->text ?? '(rating only, no text)', 110) }}</td>
          <td>
            <form method="post" action="{{ route('admin.reviews.toggle', $review) }}" style="display:inline">@csrf @method('PATCH')<input type="hidden" name="field" value="is_visible">
              <button class="badge {{ $review->is_visible ? 'completed' : 'muted' }}" type="submit" style="border:0;cursor:pointer" title="Click to {{ $review->is_visible ? 'hide' : 'show' }}">{{ $review->is_visible ? 'Shown' : 'Hidden' }}</button></form>
            <form method="post" action="{{ route('admin.reviews.toggle', $review) }}" style="display:inline">@csrf @method('PATCH')<input type="hidden" name="field" value="is_featured">
              <button class="badge {{ $review->is_featured ? 'pending' : 'muted' }}" type="submit" style="border:0;cursor:pointer" title="Featured reviews show first on the home page">{{ $review->is_featured ? '★ Featured' : 'Feature' }}</button></form>
          </td>
          <td style="white-space:nowrap">
            <a class="btn sm" href="{{ route('admin.reviews.edit', $review) }}">Edit</a>
            <form method="post" action="{{ route('admin.reviews.destroy', $review) }}" style="display:inline" data-confirm="Delete this review from the website? (It stays on Google.)">@csrf @method('DELETE')<button class="btn sm danger" type="submit">Delete</button></form>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="empty-row">No reviews yet. Import your Google reviews above, or add one by hand.</td></tr>
      @endforelse
    </tbody>
  </table></div>
  {{ $reviews->links() }}
@endsection
