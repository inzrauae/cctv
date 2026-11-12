@extends('admin.layout', ['title' => 'Products'])

@section('actions')
  <a class="btn primary" href="{{ route('admin.products.create') }}"><svg class="icon"><use href="#icon-plus"/></svg>Add product</a>
@endsection

@section('content')
  <form class="filters" method="get">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name, product code, model number…">
    <select name="category" data-autosubmit><option value="">All categories</option>@foreach ($categories as $item)<option value="{{ $item->id }}" @selected((string) request('category') === (string) $item->id)>{{ $item->name }}</option>@endforeach</select>
    <select name="stock" data-autosubmit><option value="">Any stock</option><option value="low" @selected(request('stock') === 'low')>Low stock</option><option value="out" @selected(request('stock') === 'out')>Out of stock</option></select>
    <select name="status" data-autosubmit><option value="">Any visibility</option><option value="hidden" @selected(request('status') === 'hidden')>Hidden only</option><option value="offer" @selected(request('status') === 'offer')>On offer</option></select>
    <button class="btn" type="submit">Filter</button>
  </form>

  <div class="table-wrap"><table class="data">
    <thead><tr><th></th><th>Product</th><th>Category</th><th class="num">Price</th><th class="num">Stock</th><th>Status</th></tr></thead>
    <tbody>
      @forelse ($products as $product)
        <tr>
          <td><img class="thumb" src="{{ $product->imageUrl() }}" alt=""></td>
          <td><a class="link" href="{{ route('admin.products.edit', $product) }}">{{ $product->name }}</a><small>{{ collect([$product->brand, $product->oem_part_number ? 'Model '.$product->oem_part_number : null, $product->sku])->filter()->implode(' · ') }}</small></td>
          <td>{{ $product->groupLabel() }}</td>
          <td class="num">{{ $product->priceLabel() }}@if ($product->hasOffer())<small><s>{{ money($product->price) }}</s></small>@endif</td>
          <td class="num"><span class="badge {{ $product->stockState() }}">{{ $product->stock_qty }}</span></td>
          <td>@if ($product->is_active)<span class="badge in">Visible</span>@else<span class="badge muted">Hidden</span>@endif @if ($product->hasOffer())<span class="badge offer">Offer -{{ $product->discountPercent() }}%</span>@endif @if ($product->is_featured)<span class="badge">Featured</span>@endif</td>
        </tr>
      @empty
        <tr><td colspan="6" class="empty-row">No products match.</td></tr>
      @endforelse
    </tbody>
  </table></div>
  {{ $products->links() }}
@endsection
