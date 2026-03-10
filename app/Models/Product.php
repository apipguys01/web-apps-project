<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id', 'category_id', 'name', 'sku', 'description',
        'price', 'cost_price', 'stock', 'min_stock', 'image', 'unit', 'is_active',
    ];

    protected $casts = [
        'price'      => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    // ── Scopes ─────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    public function scopeForStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    // ── Accessors ──────────────────────────────────────────────
    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : asset('images/default-product.png');
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->price <= 0) return 0;
        return round((($this->price - $this->cost_price) / $this->price) * 100, 1);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0)           return 'empty';
        if ($this->stock <= $this->min_stock) return 'low';
        return 'ok';
    }

    // ── Helpers ────────────────────────────────────────────────
    public function decrementStock(int $qty): void
    {
        $this->decrement('stock', $qty);
    }
}
