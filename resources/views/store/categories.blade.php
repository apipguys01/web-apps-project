@extends('layouts.app')
@section('title', 'Kategori Produk')

@section('content')
<div class="topbar">
    <div>
        <div class="page-title">Kategori <span>Produk</span></div>
        <div style="font-size:0.75rem;color:var(--muted);margin-top:2px">{{ $categories->count() }} kategori</div>
    </div>
    <a href="{{ route('store.settings') }}" style="color:var(--muted2);text-decoration:none;font-size:0.82rem">← Kembali</a>
</div>

<div style="display:grid;grid-template-columns:340px 1fr;gap:20px">

    {{-- Add Form --}}
    <div class="card-dark" style="padding:26px">
        <div style="font-size:0.8rem;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:20px">➕ Tambah Kategori</div>
        <form method="POST" action="{{ route('categories.store') }}">
        @csrf
        <div style="margin-bottom:14px">
            <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Nama Kategori *</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="form-control-dark" placeholder="Minuman, Makanan, ...">
            @error('name')<div style="color:var(--red);font-size:0.72rem;margin-top:4px">{{ $message }}</div>@enderror
        </div>
        <div style="margin-bottom:20px">
            <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Icon (Emoji)</label>
            <input type="text" name="icon" value="{{ old('icon','📦') }}" class="form-control-dark" placeholder="📦" maxlength="4" style="font-size:1.4rem;text-align:center;letter-spacing:0.1em">
            <div style="font-size:0.68rem;color:var(--muted);margin-top:4px">Salin emoji dari keyboard atau copy-paste</div>
        </div>
        <button type="submit" class="btn-green" style="width:100%;padding:11px">Tambah Kategori</button>
        </form>
    </div>

    {{-- Categories Grid --}}
    <div>
        @if($categories->isEmpty())
        <div class="card-dark" style="padding:48px;text-align:center;color:var(--muted)">
            <div style="font-size:3rem;margin-bottom:10px">🏷️</div>
            <div>Belum ada kategori</div>
        </div>
        @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px">
            @foreach($categories as $cat)
            <div class="card-dark" style="padding:18px">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
                    <div style="width:44px;height:44px;background:var(--bg3);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0">{{ $cat->icon }}</div>
                    <div>
                        <div style="font-size:0.9rem;font-weight:700">{{ $cat->name }}</div>
                        <div style="font-size:0.72rem;color:var(--muted2)">{{ $cat->products_count }} produk</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('categories.update', $cat) }}" style="display:flex;gap:6px;margin-bottom:8px">
                    @csrf @method('PUT')
                    <input type="text" name="icon" value="{{ $cat->icon }}" style="width:44px;background:var(--bg3);border:1px solid var(--border2);border-radius:7px;padding:5px;color:var(--text);font-size:1rem;text-align:center;font-family:inherit">
                    <input type="text" name="name" value="{{ $cat->name }}" class="form-control-dark" style="flex:1;padding:6px 10px;font-size:0.8rem">
                    <button type="submit" style="padding:0 10px;background:var(--green-bg);border:1px solid rgba(0,214,143,0.2);border-radius:7px;color:var(--green);cursor:pointer;font-size:0.8rem;font-family:inherit">✓</button>
                </form>
                @if($cat->products_count === 0)
                <form method="POST" action="{{ route('categories.destroy', $cat) }}" onsubmit="return confirm('Hapus kategori {{ $cat->name }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="width:100%;padding:5px;background:transparent;border:1px solid var(--border2);border-radius:7px;color:var(--muted);cursor:pointer;font-size:0.72rem;font-family:inherit;transition:all 0.15s"
                        onmouseover="this.style.background='rgba(255,77,106,0.08)';this.style.color='var(--red)'" onmouseout="this.style.background='transparent';this.style.color='var(--muted)'">
                        🗑️ Hapus
                    </button>
                </form>
                @else
                <div style="font-size:0.68rem;color:var(--muted);text-align:center;padding:4px">Tidak bisa dihapus (ada produk)</div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
