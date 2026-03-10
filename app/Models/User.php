<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'store_id', 'name', 'email', 'password',
        'role', 'phone', 'avatar', 'is_active', 'last_login_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
    ];

    // ── Relationships ──────────────────────────────────────────
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // ── Role Helpers ───────────────────────────────────────────
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    // ── Scopes ─────────────────────────────────────────────────
    public function scopeOwners($query)
    {
        return $query->where('role', 'owner');
    }

    public function scopeKasir($query)
    {
        return $query->where('role', 'kasir');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Accessors ──────────────────────────────────────────────
    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=00D68F&color=000';
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->role === 'owner' ? '👑 Owner' : '💼 Kasir';
    }
}
