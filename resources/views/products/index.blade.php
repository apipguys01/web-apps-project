@extends('layouts.app')
@section('title', 'Manajemen Produk')

@section('content')
<div class="topbar">
    <div>
        <div class="page-title">Manajemen <span>Produk</span></div>
        <div style="font-size:0.75rem;color:var(--muted);margin-top:2px">{{ $products->total() }} produk terdaftar</div>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <form method="GET" style="display:flex;gap:8px">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Cari produk..."
                style="background:var(--card);border:1px solid var(--border);border-radius:10px;padding:9px 14px;color:var(--text);font-size:0.82rem;width:220px;font-family:inherit"
                onchange="this.form.submit()">
        </form>
        <a href="{{ route('products.create') }}" class="btn-green" style="text-decoration:none;display:flex;align-items:center;gap:6px;font-size:0.85rem;padding:10px 18px">
            + Tambah Produk
        </a>
    </div>
</div>

{{-- Filter chips --}}
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <a href="{{ route('products.index') }}"
       style="padding:5px 14px;border-radius:20px;font-size:0.75rem;font-weight:600;text-decoration:none;border:1px solid {{ !request('category') && !request('low_stock') ? 'rgba(77,159,255,0.3)' : 'var(--border2)' }};background:{{ !request('category') && !request('low_stock') ? 'rgba(77,159,255,0.08)' : 'transparent' }};color:{{ !request('category') && !request('low_stock') ? '#4D9FFF' : 'var(--muted2)' }}">
        Semua ({{ $products->total() }})
    </a>
    @foreach($categories as $cat)
    <a href="{{ route('products.index', ['category'=>$cat->id]) }}"
       style="padding:5px 14px;border-radius:20px;font-size:0.75rem;font-weight:600;text-decoration:none;border:1px solid {{ request('category')==$cat->id ? 'rgba(77,159,255,0.3)' : 'var(--border2)' }};background:{{ request('category')==$cat->id ? 'rgba(77,159,255,0.08)' : 'transparent' }};color:{{ request('category')==$cat->id ? '#4D9FFF' : 'var(--muted2)' }}">
        {{ $cat->icon }} {{ $cat->name }}
    </a>
    @endforeach
    <a href="{{ route('products.index', ['low_stock'=>1]) }}"
       style="padding:5px 14px;border-radius:20px;font-size:0.75rem;font-weight:600;text-decoration:none;border:1px solid {{ request('low_stock') ? 'rgba(255,77,106,0.3)' : 'var(--border2)' }};background:{{ request('low_stock') ? 'rgba(255,77,106,0.08)' : 'transparent' }};color:{{ request('low_stock') ? '#FF4D6A' : 'var(--muted2)' }}">
        ⚠️ Stok Menipis ({{ $lowStockCount }})
    </a>
</div>

{{-- Table --}}
<div class="card-dark" style="overflow:hidden">
    <table class="table-dark-custom" style="width:100%;border-collapse:collapse">
        <thead><tr>
            <th>Produk</th>
            <th>Kategori</th>
            <th>Harga Jual</th>
            <th>Harga Modal</th>
            <th>Margin</th>
            <th>Stok</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr></thead>
        <tbody>
            @forelse($products as $product)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:12px">
                        <div style="width:40px;height:40px;border-radius:10px;background:var(--bg3);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;overflow:hidden">
                            @if($product->image)
                                <img src="{{ $product->image_url }}" style="width:100%;height:100%;object-fit:cover">
                            @else
                                {{ $product->category->icon ?? '📦' }}
                            @endif
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:0.85rem">{{ $product->name }}</div>
                            <div style="font-size:0.68rem;color:var(--muted)">SKU: {{ $product->sku }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    @if($product->category)
                    <span class="status-badge badge-blue">{{ $product->category->icon }} {{ $product->category->name }}</span>
                    @else
                    <span style="color:var(--muted);font-size:0.78rem">—</span>
                    @endif
                </td>
                <td style="font-weight:700">Rp {{ number_format($product->price,0,',','.') }}</td>
                <td style="color:var(--muted2)">Rp {{ number_format($product->cost_price,0,',','.') }}</td>
                <td>
                    <span style="color:{{ $product->profit_margin >= 30 ? 'var(--green)' : ($product->profit_margin >= 10 ? 'var(--yellow)' : 'var(--red)') }};font-weight:600;font-size:0.82rem">
                        {{ $product->profit_margin }}%
                    </span>
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="width:60px;height:5px;background:var(--bg3);border-radius:3px;overflow:hidden">
                            @php
                                $pct = $product->min_stock > 0 ? min(100, ($product->stock / ($product->min_stock * 3)) * 100) : 100;
                                $barColor = $product->stock_status === 'ok' ? 'var(--green)' : ($product->stock_status === 'low' ? 'var(--yellow)' : 'var(--red)');
                            @endphp
                            <div style="height:100%;background:{{ $barColor }};width:{{ $pct }}%"></div>
                        </div>
                        <span style="font-size:0.78rem;font-weight:600;color:{{ $product->is_low_stock ? 'var(--red)' : 'var(--text)' }}">
                            {{ $product->stock }} {{ $product->unit }}
                        </span>
                    </div>
                </td>
                <td>
                    @if(!$product->is_active)
                        <span class="status-badge" style="background:rgba(90,100,128,0.15);color:var(--muted);border:1px solid var(--border2)">● Nonaktif</span>
                    @elseif($product->stock_status === 'empty')
                        <span class="status-badge badge-red">● Habis</span>
                    @elseif($product->stock_status === 'low')
                        <span class="status-badge" style="background:rgba(255,185,48,0.1);color:#FFB930;border:1px solid rgba(255,185,48,0.2)">⚠ Menipis</span>
                    @else
                        <span class="status-badge badge-green">● Aktif</span>
                    @endif
                </td>
                <td>
                    <div style="display:flex;gap:6px">
                        <a href="{{ route('products.edit', $product) }}"
                           style="width:30px;height:30px;border-radius:7px;border:1px solid var(--border2);background:transparent;color:var(--muted2);display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:0.85rem;transition:all 0.15s"
                           onmouseover="this.style.background='var(--bg3)';this.style.color='var(--text)'"
                           onmouseout="this.style.background='transparent';this.style.color='var(--muted2)'">✏️</a>
                        <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Hapus produk {{ $product->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                style="width:30px;height:30px;border-radius:7px;border:1px solid var(--border2);background:transparent;color:var(--muted2);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:0.85rem;transition:all 0.15s"
                                onmouseover="this.style.background='rgba(255,77,106,0.08)';this.style.color='var(--red)';this.style.borderColor='rgba(255,77,106,0.3)'"
                                onmouseout="this.style.background='transparent';this.style.color='var(--muted2)';this.style.borderColor='var(--border2)'">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center;padding:48px;color:var(--muted)">
                    <div style="font-size:2.5rem;margin-bottom:12px">📦</div>
                    <div style="font-size:0.88rem;margin-bottom:8px">Belum ada produk</div>
                    <a href="{{ route('products.create') }}" style="color:var(--green);text-decoration:none;font-size:0.82rem;font-weight:600">+ Tambah Produk Pertama</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($products->hasPages())
    <div style="padding:14px 20px;border-top:1px solid var(--border)">
        {{ $products->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
