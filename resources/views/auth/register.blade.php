@extends('layouts.auth')
@section('title', 'Daftar Toko Baru')

@section('content')
<div style="position:relative;z-index:2;background:#181D27;border:1px solid #2D3548;border-radius:20px;padding:44px 40px;width:480px;box-shadow:0 40px 80px rgba(0,0,0,0.5)">

    <div style="font-family:'Instrument Serif',serif;font-size:1.8rem;line-height:1.2;margin-bottom:4px">
        Daftar <span style="color:#00D68F;font-style:italic">Toko Baru</span>
    </div>
    <div style="color:#8892AA;font-size:0.83rem;margin-bottom:28px">Isi data toko dan akun owner Anda</div>

    @if($errors->any())
        <div class="error-msg">❌ {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div style="font-size:0.75rem;font-weight:700;color:var(--muted2,#8892AA);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #232938">
            🏪 Data Toko
        </div>

        <div style="margin-bottom:14px">
            <label class="form-label">Nama Toko *</label>
            <input type="text" name="store_name" class="form-input" placeholder="Warung Budi, Toko ABC, ..." value="{{ old('store_name') }}" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
            <div>
                <label class="form-label">No. Telepon</label>
                <input type="text" name="store_phone" class="form-input" placeholder="0812xxxx" value="{{ old('store_phone') }}">
            </div>
            <div>
                <label class="form-label">Alamat</label>
                <input type="text" name="store_address" class="form-input" placeholder="Jl. Merdeka No. 1" value="{{ old('store_address') }}">
            </div>
        </div>

        <div style="font-size:0.75rem;font-weight:700;color:#8892AA;text-transform:uppercase;letter-spacing:0.08em;margin:20px 0 12px;padding-bottom:8px;border-bottom:1px solid #232938">
            👤 Akun Owner
        </div>

        <div style="margin-bottom:14px">
            <label class="form-label">Nama Lengkap *</label>
            <input type="text" name="name" class="form-input" placeholder="Budi Wijaya" value="{{ old('name') }}" required>
        </div>
        <div style="margin-bottom:14px">
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-input" placeholder="owner@toko.com" value="{{ old('email') }}" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:22px">
            <div>
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-input" placeholder="Min. 8 karakter" required>
            </div>
            <div>
                <label class="form-label">Konfirmasi Password *</label>
                <input type="password" name="password_confirmation" class="form-input" placeholder="Ulangi password" required>
            </div>
        </div>

        <button type="submit" class="btn-primary">Daftarkan Toko Saya →</button>
    </form>

    <div style="text-align:center;margin-top:16px;font-size:0.78rem;color:#8892AA">
        Sudah punya akun? <a href="{{ route('login') }}" style="color:#00D68F;text-decoration:none;font-weight:600">Masuk</a>
    </div>
</div>
@endsection
