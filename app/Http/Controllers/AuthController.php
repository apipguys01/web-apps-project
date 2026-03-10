<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // ── Show Login ─────────────────────────────────────────────
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    // ── Login ──────────────────────────────────────────────────
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Update last login
            Auth::user()->update(['last_login_at' => now()]);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang, ' . Auth::user()->name . '!');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Email atau password salah.']);
    }

    // ── Show Register ──────────────────────────────────────────
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    // ── Register (Owner + new Store) ───────────────────────────
    public function register(Request $request)
    {
        $request->validate([
            'store_name' => ['required', 'string', 'max:150'],
            'store_phone'=> ['nullable', 'string', 'max:20'],
            'store_address' => ['nullable', 'string'],
            'name'       => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'password'   => ['required', 'confirmed', Password::min(8)],
        ], [
            'store_name.required' => 'Nama toko wajib diisi.',
            'name.required'       => 'Nama lengkap wajib diisi.',
            'email.unique'        => 'Email sudah terdaftar.',
            'password.confirmed'  => 'Konfirmasi password tidak cocok.',
        ]);

        // Create store
        $store = Store::create([
            'name'    => $request->store_name,
            'phone'   => $request->store_phone,
            'address' => $request->store_address,
        ]);

        // Create owner user
        $user = User::create([
            'store_id' => $store->id,
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'owner',
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Toko berhasil didaftarkan! Selamat datang, ' . $user->name . '.');
    }

    // ── Logout ─────────────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Berhasil keluar.');
    }
}
