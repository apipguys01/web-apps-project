<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id', 'user_id', 'invoice_no', 'total_amount',
        'paid_amount', 'change_amount', 'discount_amount',
        'payment_method', 'note',
    ];

    protected $casts = [
        'total_amount'    => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'change_amount'   => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function getTotalProfitAttribute(): float
    {
        return $this->items->sum(fn($item) =>
            ($item->price - $item->cost_price) * $item->qty
        );
    }

    public function getPaymentLabelAttribute(): string
    {
        return match($this->payment_method) {
            'cash'     => '💵 Tunai',
            'qris'     => '📱 QRIS',
            'transfer' => '🏦 Transfer',
            default    => $this->payment_method,
        };
    }

    // Auto-generate invoice number
    public static function generateInvoiceNo(int $storeId): string
    {
        $prefix = 'TRX-' . date('Ymd');
        $last = static::where('store_id', $storeId)
            ->whereDate('created_at', today())
            ->count();
        return $prefix . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }
}
