<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ $store->name ?? 'UMKM Manager' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --bg: #080A0F; --bg2: #0E1118; --bg3: #141820;
            --card: #181D27; --border: #232938; --border2: #2D3548;
            --text: #E8EDF5; --muted: #5A6480; --muted2: #8892AA;
            --green: #00D68F; --green-bg: rgba(0,214,143,0.08);
            --orange: #FF7240; --blue: #4D9FFF; --red: #FF4D6A;
            --sidebar-w: 230px;
        }
        * { box-sizing: border-box; }
        body { background: var(--bg); color: var(--text); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }

        /* Sidebar */
        .sidebar { width: var(--sidebar-w); background: var(--bg2); border-right: 1px solid var(--border); height: 100vh; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; padding: 20px 12px; z-index: 100; }
        .main-wrapper { margin-left: var(--sidebar-w); min-height: 100vh; }
        .main-content { padding: 28px 32px; }

        .brand-name { font-size: 1.2rem; font-weight: 800; color: var(--text); }
        .brand-name span { color: var(--green); }
        .brand-store { font-size: 0.72rem; color: var(--muted); margin-top: 2px; }
        .sidebar-divider { border-color: var(--border); margin: 14px 0; }
        .nav-section { font-size: 0.65rem; font-weight: 700; color: var(--muted); letter-spacing: 0.1em; text-transform: uppercase; padding: 0 8px; margin: 8px 0 4px; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 8px; cursor: pointer; font-size: 0.82rem; font-weight: 500; color: var(--muted2); transition: all 0.15s; margin-bottom: 2px; text-decoration: none; }
        .nav-item:hover { background: var(--bg3); color: var(--text); }
        .nav-item.active { background: var(--green-bg); color: var(--green); font-weight: 600; }
        .nav-icon { font-size: 1rem; width: 20px; text-align: center; }
        .nav-badge { margin-left: auto; background: var(--red); color: #fff; font-size: 0.62rem; font-weight: 700; border-radius: 10px; padding: 1px 6px; }
        .sidebar-footer { margin-top: auto; padding-top: 14px; border-top: 1px solid var(--border); }
        .user-chip { display: flex; align-items: center; gap: 10px; padding: 10px 8px; border-radius: 10px; background: var(--bg3); }
        .user-avatar { width: 34px; height: 34px; border-radius: 8px; overflow: hidden; flex-shrink: 0; }
        .user-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .user-name { font-size: 0.78rem; font-weight: 600; }
        .user-role { font-size: 0.68rem; color: var(--muted); }

        /* Cards */
        .card-dark { background: var(--card); border: 1px solid var(--border); border-radius: 14px; }

        /* Form controls */
        .form-control-dark { background: var(--bg2); border: 1px solid var(--border2); border-radius: 10px; padding: 10px 14px; color: var(--text); font-size: 0.88rem; width: 100%; font-family: inherit; transition: border-color 0.2s; }
        .form-control-dark:focus { outline: none; border-color: var(--green); }
        .form-control-dark::placeholder { color: var(--muted); }

        /* Buttons */
        .btn-green { background: var(--green); color: #000; border: none; border-radius: 10px; padding: 10px 20px; font-weight: 700; cursor: pointer; font-family: inherit; transition: all 0.2s; }
        .btn-green:hover { background: #00f0a0; }
        .btn-outline { background: transparent; border: 1px solid var(--border2); color: var(--muted2); border-radius: 9px; padding: 8px 16px; font-size: 0.82rem; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.15s; }
        .btn-outline:hover { background: var(--bg3); color: var(--text); }

        /* Alerts */
        .alert-success-dark { background: var(--green-bg); border: 1px solid rgba(0,214,143,0.2); color: var(--green); border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; font-size: 0.85rem; }
        .alert-error-dark { background: rgba(255,77,106,0.08); border: 1px solid rgba(255,77,106,0.2); color: var(--red); border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; font-size: 0.85rem; }

        /* Badges */
        .badge-green { background: var(--green-bg); color: var(--green); border: 1px solid rgba(0,214,143,0.2); }
        .badge-orange { background: rgba(255,114,64,0.08); color: var(--orange); border: 1px solid rgba(255,114,64,0.2); }
        .badge-red { background: rgba(255,77,106,0.08); color: var(--red); border: 1px solid rgba(255,77,106,0.2); }
        .badge-blue { background: rgba(77,159,255,0.08); color: var(--blue); border: 1px solid rgba(77,159,255,0.2); }
        .status-badge { display: inline-flex; align-items: center; gap: 4px; font-size: 0.68rem; font-weight: 700; padding: 3px 9px; border-radius: 20px; }

        /* Table */
        .table-dark-custom { color: var(--text); }
        .table-dark-custom th { font-size: 0.68rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.07em; padding: 12px 16px; background: var(--bg2); border-bottom: 1px solid var(--border); border-top: none; }
        .table-dark-custom td { padding: 12px 16px; font-size: 0.82rem; border-top: 1px solid var(--border); border-bottom: none; vertical-align: middle; }
        .table-dark-custom tr:hover td { background: rgba(255,255,255,0.015); }

        /* Topbar */
        .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
        .page-title { font-size: 1.4rem; font-weight: 800; letter-spacing: -0.02em; }
        .page-title span { color: var(--green); }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 2px; }

        @yield('styles')
    </style>
    @stack('styles')
</head>
<body>

{{-- Sidebar --}}
<div class="sidebar">
    <div style="padding: 4px 8px 16px; border-bottom: 1px solid var(--border); margin-bottom: 12px;">
        <div class="brand-name">{{ $store->name ?? 'UMKM' }} <span>.</span></div>
        <div class="brand-store">📍 {{ Str::limit($store->address ?? '', 30) }}</div>
    </div>

    <div class="nav-section">Menu Utama</div>
    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <span class="nav-icon">📊</span> Dashboard
    </a>
    <a href="{{ route('pos') }}" class="nav-item {{ request()->routeIs('pos*') ? 'active' : '' }}">
        <span class="nav-icon">🛒</span> Kasir / POS
    </a>
    <a href="{{ route('transactions.index') }}" class="nav-item {{ request()->routeIs('transactions*') ? 'active' : '' }}">
        <span class="nav-icon">🧾</span> Transaksi
    </a>

    @if(auth()->user()->isOwner())
    <a href="{{ route('products.index') }}" class="nav-item {{ request()->routeIs('products*') ? 'active' : '' }}">
        <span class="nav-icon">📦</span> Produk
        @php $lowStock = \App\Models\Product::forStore(auth()->user()->store_id)->lowStock()->count() @endphp
        @if($lowStock > 0)
            <span class="nav-badge">{{ $lowStock }}</span>
        @endif
    </a>
    <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports*') ? 'active' : '' }}">
        <span class="nav-icon">📈</span> Laporan
    </a>
    @endif

    <div class="nav-section">Pengaturan</div>
    @if(auth()->user()->isOwner())
    <a href="{{ route('store.settings') }}" class="nav-item {{ request()->routeIs('store.*') || request()->routeIs('categories.*') ? 'active' : '' }}">
        <span class="nav-icon">⚙️</span> Pengaturan Toko
    </a>
    @endif
    <a href="{{ route('profile') }}" class="nav-item {{ request()->routeIs('profile') ? 'active' : '' }}">
        <span class="nav-icon">👤</span> Profil Saya
    </a>

    <div class="sidebar-footer">
        <div class="user-chip">
            <div class="user-avatar">
                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}">
            </div>
            <div>
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ auth()->user()->role_label }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin-left:auto">
                @csrf
                <button type="submit" style="background:none;border:none;cursor:pointer;font-size:1rem;color:var(--muted);" title="Logout">🚪</button>
            </form>
        </div>
    </div>
</div>

{{-- Main --}}
<div class="main-wrapper">
    <div class="main-content">
        @if(session('success'))
            <div class="alert-success-dark">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert-error-dark">❌ {{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
