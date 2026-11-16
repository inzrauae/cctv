<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>{{ $title ?? 'Dashboard' }} | Last Solution Admin</title>
  <link rel="icon" href="{{ asset('assets/favicon.png') }}" type="image/png">
  <link rel="stylesheet" href="{{ asset('admin.css') }}?v={{ filemtime(public_path('admin.css')) }}">
</head>
<body>
  @include('partials.icons')
  <div class="admin">
    <aside class="sidebar">
      <a class="side-brand" href="{{ route('admin.dashboard') }}"><img src="{{ asset('assets/last-solution-logo-light.png') }}" alt="Last Solution" width="538" height="314"><span>Admin</span></a>
      <nav aria-label="Admin">
        @php($nav = [
          ['admin.dashboard', 'Dashboard', 'home', 'admin.dashboard', null],
          ['admin.orders.index', 'Orders', 'cart', 'admin.orders.*', null],
          ['admin.enquiries.index', 'Enquiries & bookings', 'mail', 'admin.enquiries.*', null],
          ['admin.products.index', 'Products', 'box', 'admin.products.*', null],
          ['admin.inventory.index', 'Inventory', 'storage', 'admin.inventory.*', null],
          ['admin.catalog.index', 'Shop categories', 'tag', 'admin.catalog.*', 'categories'],
          ['admin.reviews.index', 'Customer reviews', 'users', 'admin.reviews.*', null],
          ['admin.settings', 'Payments & delivery', 'truck', 'admin.settings*', null],
        ])
        @foreach ($nav as [$route, $label, $icon, $pattern, $param])
          <a href="{{ route($route, $param) }}" @class(['active' => request()->routeIs($pattern)])><svg class="icon"><use href="#icon-{{ $icon }}"/></svg>{{ $label }}</a>
        @endforeach
      </nav>
      <div class="side-foot">
        <a href="{{ route('home') }}" target="_blank" rel="noopener">View store ↗</a>
        <a href="{{ route('admin.account') }}">{{ auth()->user()->email }}</a>
        <form action="{{ route('admin.logout') }}" method="post">@csrf<button type="submit">Sign out</button></form>
      </div>
    </aside>

    <main class="content">
      <header class="content-head">
        <div>
          @isset($breadcrumb)<div class="crumb">{!! $breadcrumb !!}</div>@endisset
          <h1>{{ $title ?? 'Dashboard' }}</h1>
        </div>
        <div class="head-actions">@yield('actions')</div>
      </header>

      @if (session('status'))<div class="alert ok" role="status">{{ session('status') }}</div>@endif
      @if (session('error'))<div class="alert err" role="alert">{{ session('error') }}</div>@endif
      @if ($errors->any())
        <div class="alert err" role="alert"><b>Please fix the following:</b><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
      @endif

      @yield('content')
    </main>
  </div>
  <script>
    document.addEventListener('submit', e => {
      const msg = e.target.dataset.confirm;
      if (msg && !confirm(msg)) e.preventDefault();
    });
    document.querySelectorAll('[data-autosubmit]').forEach(el => el.addEventListener('change', () => el.form.submit()));
  </script>
  @stack('scripts')
</body>
</html>
