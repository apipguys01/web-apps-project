<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class StoreController extends Controller
{
    // ── Store Settings ─────────────────────────────────────────
    public function settings()
    {
        $store = Auth::user()->store;
        return view('store.settings', compact('store'));
    }

    public function updateSettings(Request $request)
    {
        $store = Auth::user()->store;

        $request->validate([
            'name'    => ['required', 'string', 'max:150'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'email'   => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string'],
            'logo'    => ['nullable', 'image', 'max:2048'],
        ]);

        $data = $request->except('logo');

        if ($request->hasFile('logo')) {
            if ($store->logo) Storage::disk('public')->delete($store->logo);
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $store->update($data);

        return back()->with('success', 'Pengaturan toko berhasil disimpan.');
    }

    // ── Kasir Management ───────────────────────────────────────
    public function kasir()
    {
        $kasirList = User::where('store_id', Auth::user()->store_id)
            ->where('role', 'kasir')
            ->latest()
            ->get();

        return view('store.kasir', compact('kasirList'));
    }

    public function storeKasir(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::min(8)],
            'phone'    => ['nullable', 'string', 'max:20'],
        ], [
            'email.unique' => 'Email sudah digunakan.',
        ]);

        User::create([
            'store_id' => Auth::user()->store_id,
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'phone'    => $request->phone,
            'role'     => 'kasir',
        ]);

        return back()->with('success', 'Kasir ' . $request->name . ' berhasil ditambahkan.');
    }

    public function toggleKasir(User $user)
    {
        // Only owner can toggle their own store's kasir
        if ($user->store_id !== Auth::user()->store_id || $user->isOwner()) {
            abort(403);
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Kasir {$user->name} berhasil {$status}.");
    }

    public function destroyKasir(User $user)
    {
        if ($user->store_id !== Auth::user()->store_id || $user->isOwner()) {
            abort(403);
        }

        $user->delete();
        return back()->with('success', 'Kasir berhasil dihapus.');
    }

    // ── Profile (own account) ──────────────────────────────────
    public function profile()
    {
        return view('store.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'current_password' => ['nullable', 'current_password'],
            'password'     => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $data = $request->only('name', 'phone');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
