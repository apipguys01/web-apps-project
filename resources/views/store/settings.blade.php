@extends('layouts.app')
@section('title', 'Pengaturan Toko')

@section('content')
<div class="topbar">
    <div>
        <div class="page-title">Pengaturan <span>Toko</span></div>
        <div style="font-size:0.75rem;color:var(--muted);margin-top:2px">Kelola info dan preferensi toko Anda</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

    {{-- Store Info --}}
    <div class="card-dark" style="padding:28px">
        <div style="font-size:0.8rem;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:20px">🏪 Info Toko</div>

        <form method="POST" action="{{ route('store.settings.update') }}" enctype="multipart/form-data">
        @csrf

        {{-- Logo --}}
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px">
            <div id="logoPreview" style="width:72px;height:72px;border-radius:14px;background:var(--bg3);border:2px dashed var(--border2);overflow:hidden;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:2rem;flex-shrink:0"
                onclick="document.getElementById('logoInput').click()">
                @if($store->logo)
                    <img src="{{ $store->logo_url }}" style="width:100%;height:100%;object-fit:cover">
                @else
                    🏪
                @endif
            </div>
            <div>
                <div style="font-size:0.82rem;font-weight:600;margin-bottom:4px">Logo Toko</div>
                <div style="font-size:0.72rem;color:var(--muted)">Klik untuk upload · Max 2MB</div>
                <input type="file" name="logo" id="logoInput" accept="image/*" style="display:none" onchange="previewLogo(this)">
            </div>
        </div>

        <div style="margin-bottom:14px">
            <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Nama Toko *</label>
            <input type="text" name="name" value="{{ old('name', $store->name) }}" required class="form-control-dark">
            @error('name')<div style="color:var(--red);font-size:0.72rem;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
            <div>
                <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">No. Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $store->phone) }}" class="form-control-dark" placeholder="081234567890">
            </div>
            <div>
                <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Email Toko</label>
                <input type="email" name="email" value="{{ old('email', $store->email) }}" class="form-control-dark" placeholder="toko@email.com">
            </div>
        </div>

        <div style="margin-bottom:20px">
            <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Alamat</label>
            <textarea name="address" rows="3" class="form-control-dark" placeholder="Jl. Merdeka No. 12...">{{ old('address', $store->address) }}</textarea>
        </div>

        <button type="submit" class="btn-green" style="width:100%;padding:11px">💾 Simpan Perubahan</button>
        </form>
    </div>

    {{-- Quick Stats --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        <div class="card-dark" style="padding:24px">
            <div style="font-size:0.8rem;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px">📊 Statistik Toko</div>
            @php
                $storeId = auth()->user()->store_id;
                $totalProducts = \App\Models\Product::forStore($storeId)->count();
                $totalTrx = \App\Models\Transaction::where('store_id',$storeId)->count();
                $totalRevenue = \App\Models\Transaction::where('store_id',$storeId)->sum('total_amount');
                $totalKasir = \App\Models\User::where('store_id',$storeId)->where('role','kasir')->count();
            @endphp
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                @foreach([
                    ['📦','Total Produk',$totalProducts,'produk'],
                    ['🧾','Total Transaksi',$totalTrx,'transaksi'],
                    ['💰','Total Omzet','Rp '.number_format($totalRevenue,0,',','.'),'semua waktu'],
                    ['👥','Jumlah Kasir',$totalKasir,'akun kasir'],
                ] as [$icon,$label,$val,$sub])
                <div style="background:var(--bg3);border-radius:10px;padding:14px">
                    <div style="font-size:1.3rem;margin-bottom:6px">{{ $icon }}</div>
                    <div style="font-size:0.68rem;color:var(--muted2);text-transform:uppercase;letter-spacing:0.06em">{{ $label }}</div>
                    <div style="font-size:1.1rem;font-weight:800;margin-top:2px">{{ $val }}</div>
                    <div style="font-size:0.68rem;color:var(--muted);margin-top:1px">{{ $sub }}</div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card-dark" style="padding:24px">
            <div style="font-size:0.8rem;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px">⚡ Menu Cepat</div>
            <div style="display:flex;flex-direction:column;gap:8px">
                <a href="{{ route('store.kasir') }}" style="display:flex;align-items:center;gap:12px;padding:12px;background:var(--bg3);border-radius:10px;text-decoration:none;color:var(--text);transition:background 0.15s" onmouseover="this.style.background='var(--border)'" onmouseout="this.style.background='var(--bg3)'">
                    <span style="font-size:1.3rem">👥</span>
                    <div><div style="font-size:0.82rem;font-weight:600">Kelola Kasir</div><div style="font-size:0.7rem;color:var(--muted)">Tambah & atur akun kasir</div></div>
                    <span style="margin-left:auto;color:var(--muted)">→</span>
                </a>
                <a href="{{ route('categories.index') }}" style="display:flex;align-items:center;gap:12px;padding:12px;background:var(--bg3);border-radius:10px;text-decoration:none;color:var(--text);transition:background 0.15s" onmouseover="this.style.background='var(--border)'" onmouseout="this.style.background='var(--bg3)'">
                    <span style="font-size:1.3rem">🏷️</span>
                    <div><div style="font-size:0.82rem;font-weight:600">Kategori Produk</div><div style="font-size:0.7rem;color:var(--muted)">Atur kategori toko</div></div>
                    <span style="margin-left:auto;color:var(--muted)">→</span>
                </a>
                <a href="{{ route('profile') }}" style="display:flex;align-items:center;gap:12px;padding:12px;background:var(--bg3);border-radius:10px;text-decoration:none;color:var(--text);transition:background 0.15s" onmouseover="this.style.background='var(--border)'" onmouseout="this.style.background='var(--bg3)'">
                    <span style="font-size:1.3rem">👤</span>
                    <div><div style="font-size:0.82rem;font-weight:600">Profil Saya</div><div style="font-size:0.7rem;color:var(--muted)">Ubah nama & password</div></div>
                    <span style="margin-left:auto;color:var(--muted)">→</span>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('logoPreview').innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
