@extends('layouts.auth')
@section('title', 'Login')

@section('content')
<div style="position:relative;z-index:2;background:#181D27;border:1px solid #2D3548;border-radius:20px;padding:44px 40px;width:420px;box-shadow:0 40px 80px rgba(0,0,0,0.5)">

    <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(0,214,143,0.08);color:#00D68F;border:1px solid rgba(0,214,143,0.2);padding:4px 12px;border-radius:20px;font-size:0.72rem;font-weight:700;letter-spacing:0.08em;margin-bottom:20px;text-transform:uppercase">
        🏪 UMKM Manager
    </div>

    <div style="font-family:'Instrument Serif',serif;font-size:2rem;line-height:1.15;margin-bottom:6px">
        Selamat <span style="color:#00D68F;font-style:italic">Datang</span>
    </div>
    <div style="color:#8892AA;font-size:0.83rem;margin-bottom:28px">Masuk ke dashboard toko Anda</div>

    @if(session('success'))
        <div class="success-msg">✅ {{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="error-msg">❌ {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div style="margin-bottom:16px">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-input" placeholder="email@tokosaya.com"
                value="{{ old('email') }}" required autofocus>
        </div>

        <div style="margin-bottom:20px">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-input" placeholder="••••••••" required>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <label style="display:flex;align-items:center;gap:8px;font-size:0.78rem;color:#8892AA;cursor:pointer">
                <input type="checkbox" name="remember" style="accent-color:#00D68F">
                Ingat saya
            </label>
        </div>

        <button type="submit" class="btn-primary">Masuk ke Dashboard →</button>
    </form>

    <div style="text-align:center;margin-top:20px;font-size:0.78rem;color:#8892AA">
        Belum punya toko? <a href="{{ route('register') }}" style="color:#00D68F;text-decoration:none;font-weight:600">Daftar Sekarang</a>
    </div>

 
</div>
@endsection
