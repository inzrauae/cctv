@extends('admin.layout', ['title' => $config['title']])

@section('content')
  <p class="help">{{ $config['help'] }} Each category can have sub-categories (e.g. CCTV Cameras → IP Cameras, Analog Cameras); customers can filter by them in the shop. Lower sort numbers appear first. Hidden items stay in the database but don't show in the store.</p>

  <section class="card">
    <h2>Add {{ $config['singular'] }}</h2>
    <form class="inline-form" method="post" action="{{ route('admin.catalog.store', $type) }}">
      @csrf
      <input class="w-lg" name="name" placeholder="Name" required maxlength="100">
      <input class="w-md" name="slug" placeholder="slug (optional)" maxlength="100">
      <input class="w-sm" name="sku_prefix" placeholder="Code" maxlength="4" title="Product code prefix, e.g. CAM. Leave blank to create one from the name.">
      <input class="w-sm" type="number" name="sort_order" placeholder="Sort" min="0" value="{{ $items->max('sort_order') + 1 }}">
      <input type="hidden" name="is_active" value="1">
      <button class="btn primary" type="submit"><svg class="icon"><use href="#icon-plus"/></svg>Add</button>
    </form>
  </section>

  <div class="table-wrap"><table class="data">
    <thead><tr><th>Name / slug / code / sort / visible</th><th class="num">Products</th><th></th></tr></thead>
    <tbody>
      @forelse ($items as $item)
        <tr>
          <td>
            <form class="inline-form" method="post" action="{{ route('admin.catalog.update', [$type, $item->id]) }}" id="row-{{ $item->id }}">
              @csrf @method('PUT')
              <input class="w-lg" name="name" value="{{ $item->name }}" required maxlength="100" aria-label="Name">
              <input class="w-md" name="slug" value="{{ $item->slug }}" maxlength="100" aria-label="Slug">
              <input class="w-sm" name="sku_prefix" value="{{ $item->sku_prefix }}" maxlength="4" aria-label="Product code prefix" title="Product code prefix. New products in this group are numbered {{ $item->sku_prefix }}-00001, {{ $item->sku_prefix }}-00002…">
              <input class="w-sm" type="number" name="sort_order" value="{{ $item->sort_order }}" min="0" aria-label="Sort order">
              <label class="check"><input type="checkbox" name="is_active" value="1" @checked($item->is_active)> Visible</label>
              <button class="btn sm" type="submit">Save</button>
            </form>
          </td>
          <td class="num">
            {{ $item->products_count }}
          </td>
          <td class="num">
            <form method="post" action="{{ route('admin.catalog.destroy', [$type, $item->id]) }}" data-confirm="Delete “{{ $item->name }}”?">
              @csrf @method('DELETE')
              <button class="btn sm danger" type="submit" aria-label="Delete {{ $item->name }}"><svg class="icon"><use href="#icon-trash"/></svg></button>
            </form>
          </td>
        </tr>
        <tr class="sub-row">
          <td colspan="3">
            <details @if ($item->subcategories->isNotEmpty()) open @endif>
              <summary>Sub-categories in {{ $item->name }} <small>({{ $item->subcategories->count() }})</small></summary>
              <div class="sub-list">
                @foreach ($item->subcategories as $sub)
                  <div class="sub-item">
                    <form class="inline-form" method="post" action="{{ route('admin.subcategories.update', $sub) }}">
                      @csrf @method('PUT')
                      <span class="sub-arrow" aria-hidden="true">↳</span>
                      <input class="w-lg" name="name" value="{{ $sub->name }}" required maxlength="100" aria-label="Sub-category name">
                      <input class="w-md" name="slug" value="{{ $sub->slug }}" maxlength="100" aria-label="Slug">
                      <input class="w-sm" type="number" name="sort_order" value="{{ $sub->sort_order }}" min="0" aria-label="Sort order">
                      <label class="check"><input type="checkbox" name="is_active" value="1" @checked($sub->is_active)> Visible</label>
                      <button class="btn sm" type="submit">Save</button>
                      <span class="help" style="margin:0">{{ $sub->products_count }} {{ Str::plural('product', $sub->products_count) }}</span>
                    </form>
                    <form method="post" action="{{ route('admin.subcategories.destroy', $sub) }}" data-confirm="Delete “{{ $sub->name }}”? Its products stay in {{ $item->name }}.">
                      @csrf @method('DELETE')
                      <button class="btn sm danger" type="submit" aria-label="Delete {{ $sub->name }}"><svg class="icon"><use href="#icon-trash"/></svg></button>
                    </form>
                  </div>
                @endforeach
                <form class="inline-form sub-add" method="post" action="{{ route('admin.subcategories.store', $item) }}">
                  @csrf
                  <span class="sub-arrow" aria-hidden="true">+</span>
                  <input class="w-lg" name="name" placeholder="New sub-category, e.g. IP Cameras" required maxlength="100">
                  <input class="w-sm" type="number" name="sort_order" placeholder="Sort" min="0" value="{{ $item->subcategories->max('sort_order') + 1 }}">
                  <input type="hidden" name="is_active" value="1">
                  <button class="btn sm" type="submit">Add sub-category</button>
                </form>
              </div>
            </details>
          </td>
        </tr>
      @empty
        <tr><td colspan="3" class="empty-row">Nothing here yet.</td></tr>
      @endforelse
    </tbody>
  </table></div>
@endsection
