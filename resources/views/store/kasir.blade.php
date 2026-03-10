@extends('layouts.app')
@section('title', 'Kelola Kasir')

@section('content')
<div class="topbar">
    <div>
        <div class="page-title">Kelola <span>Kasir</span></div>
        <div style="font-size:0.75rem;color:var(--muted);margin-top:2px">{{ $kasirList->count() }} akun kasir terdaftar</div>
    </div>
    <a href="{{ route('store.settings') }}" style="color:var(--muted2);text-decoration:none;font-size:0.82rem">← Kembali ke Pengaturan</a>
</div>

<div style="display:grid;grid-template-columns:1fr 1.4fr;gap:20px">

    {{-- Add Kasir Form --}}
    <div class="card-dark" style="padding:26px">
        <div style="font-size:0.8rem;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:20px">➕ Tambah Kasir Baru</div>

        <form method="POST" action="{{ route('store.kasir.store') }}">
        @csrf

        <div style="margin-bottom:14px">
            <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Nama Lengkap *</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="form-control-dark" placeholder="Siti Rahayu">
            @error('name')<div style="color:var(--red);font-size:0.72rem;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:14px">
            <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">No. Telepon</label>
            <input type="text" name="phone" value="{{ old('phone') }}" class="form-control-dark" placeholder="0812xxxx">
        </div>

        <div style="margin-bottom:14px">
            <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Email *</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="form-control-dark" placeholder="kasir@toko.com">
            @error('email')<div style="color:var(--red);font-size:0.72rem;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px">
            <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Password *</label>
            <input type="password" name="password" required class="form-control-dark" placeholder="Min. 8 karakter">
            @error('password')<div style="color:var(--red);font-size:0.72rem;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn-green" style="width:100%;padding:11px">Tambah Kasir</button>
        </form>
    </div>

    {{-- Kasir List --}}
    <div class="card-dark" style="overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
            <div style="font-size:0.88rem;font-weight:700">Daftar Kasir</div>
        </div>

        @forelse($kasirList as $kasir)
        <div style="display:flex;align-items:center;gap:14px;padding:14px 20px;border-bottom:1px solid var(--border)">
            <img src="{{ $kasir->avatar_url }}" style="width:40px;height:40px;border-radius:10px;object-fit:cover;flex-shrink:0">
            <div style="flex:1">
                <div style="font-size:0.85rem;font-weight:600">{{ $kasir->name }}</div>
                <div style="font-size:0.72rem;color:var(--muted2)">{{ $kasir->email }}</div>
                @if($kasir->phone)
                <div style="font-size:0.7rem;color:var(--muted)">📞 {{ $kasir->phone }}</div>
                @endif
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                @if($kasir->last_login_at)
                <div style="font-size:0.68rem;color:var(--muted);text-align:right">
                    <div>Login terakhir</div>
                    <div>{{ $kasir->last_login_at->diffForHumans() }}</div>
                </div>
                @endif
                <span class="status-badge {{ $kasir->is_active ? 'badge-green' : 'badge-red' }}">
                    {{ $kasir->is_active ? '● Aktif' : '● Nonaktif' }}
                </span>
                <form method="POST" action="{{ route('store.kasir.toggle', $kasir) }}" style="display:inline">
                    @csrf @method('PATCH')
                    <button type="submit" title="{{ $kasir->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                        style="width:30px;height:30px;border-radius:7px;border:1px solid var(--border2);background:transparent;color:var(--muted2);cursor:pointer;font-size:0.85rem;display:flex;align-items:center;justify-content:center;transition:all 0.15s"
                        onmouseover="this.style.background='var(--bg3)'" onmouseout="this.style.background='transparent'">
                        {{ $kasir->is_active ? '🔒' : '🔓' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('store.kasir.destroy', $kasir) }}" onsubmit="return confirm('Hapus kasir {{ $kasir->name }}?')" style="display:inline">
                    @csrf @method('DELETE')
                    <button type="submit"
                        style="width:30px;height:30px;border-radius:7px;border:1px solid var(--border2);background:transparent;color:var(--muted2);cursor:pointer;font-size:0.85rem;display:flex;align-items:center;justify-content:center;transition:all 0.15s"
                        onmouseover="this.style.background='rgba(255,77,106,0.08)';this.style.color='var(--red)'" onmouseout="this.style.background='transparent';this.style.color='var(--muted2)'">
                        🗑️
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:48px 20px;color:var(--muted)">
            <div style="font-size:2.5rem;margin-bottom:10px">👥</div>
            <div style="font-size:0.88rem">Belum ada kasir</div>
            <div style="font-size:0.78rem;margin-top:4px">Tambah kasir dari form di sebelah kiri</div>
        </div>
        @endforelse
    </div>

</div>
@endsection
