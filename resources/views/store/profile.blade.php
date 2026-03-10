@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
<div class="topbar">
    <div>
        <div class="page-title">Profil <span>Saya</span></div>
        <div style="font-size:0.75rem;color:var(--muted);margin-top:2px">Kelola akun Anda</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;max-width:900px">

    {{-- Profile Info --}}
    <div class="card-dark" style="padding:28px">
        <div style="text-align:center;margin-bottom:24px">
            <img src="{{ $user->avatar_url }}" style="width:80px;height:80px;border-radius:16px;object-fit:cover;margin-bottom:12px">
            <div style="font-size:1rem;font-weight:700">{{ $user->name }}</div>
            <div style="font-size:0.78rem;color:var(--muted2);margin-top:2px">{{ $user->role_label }}</div>
            <div style="font-size:0.72rem;color:var(--muted);margin-top:2px">{{ $user->store->name }}</div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}">
        @csrf

        <div style="margin-bottom:14px">
            <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Nama Lengkap *</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-control-dark">
            @error('name')<div style="color:var(--red);font-size:0.72rem;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:14px">
            <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Email</label>
            <input type="email" value="{{ $user->email }}" disabled class="form-control-dark" style="opacity:0.6;cursor:not-allowed">
            <div style="font-size:0.68rem;color:var(--muted);margin-top:4px">Email tidak bisa diubah</div>
        </div>

        <div style="margin-bottom:20px">
            <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">No. Telepon</label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control-dark" placeholder="0812xxxx">
        </div>

        <button type="submit" class="btn-green" style="width:100%;padding:11px">💾 Simpan Profil</button>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="card-dark" style="padding:28px">
        <div style="font-size:0.8rem;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:20px">🔒 Ubah Password</div>

        <form method="POST" action="{{ route('profile.update') }}">
        @csrf

        <div style="margin-bottom:14px">
            <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Password Saat Ini *</label>
            <input type="password" name="current_password" class="form-control-dark" placeholder="••••••••">
            @error('current_password')<div style="color:var(--red);font-size:0.72rem;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:14px">
            <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Password Baru *</label>
            <input type="password" name="password" class="form-control-dark" placeholder="Min. 8 karakter">
            @error('password')<div style="color:var(--red);font-size:0.72rem;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:24px">
            <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Konfirmasi Password Baru *</label>
            <input type="password" name="password_confirmation" class="form-control-dark" placeholder="Ulangi password baru">
        </div>

        {{-- Hidden name so form submit doesn't fail --}}
        <input type="hidden" name="name" value="{{ $user->name }}">

        <button type="submit" class="btn-green" style="width:100%;padding:11px">🔒 Ubah Password</button>
        </form>

        <div style="margin-top:20px;padding:14px;background:var(--bg3);border-radius:10px">
            <div style="font-size:0.72rem;color:var(--muted2);font-weight:600;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.06em">Info Akun</div>
            <div style="font-size:0.78rem;color:var(--muted2)">Terdaftar: <span style="color:var(--text)">{{ $user->created_at->format('d M Y') }}</span></div>
            @if($user->last_login_at)
            <div style="font-size:0.78rem;color:var(--muted2);margin-top:4px">Login terakhir: <span style="color:var(--text)">{{ $user->last_login_at->diffForHumans() }}</span></div>
            @endif
        </div>
    </div>

</div>
@endsection
