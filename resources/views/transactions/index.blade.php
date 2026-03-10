@extends('layouts.app')
@section('title', 'Riwayat Transaksi')

@section('content')
<div class="topbar">
    <div>
        <div class="page-title">Riwayat <span>Transaksi</span></div>
        <div style="font-size:0.75rem;color:var(--muted);margin-top:2px">{{ $transactions->total() }} total transaksi</div>
    </div>
    <a href="{{ route('pos') }}" class="btn-green" style="text-decoration:none;display:flex;align-items:center;gap:6px;font-size:0.85rem">
        🛒 Buka Kasir
    </a>
</div>

{{-- Filter Bar --}}
<form method="GET" style="display:flex;gap:10px;align-items:center;margin-bottom:20px;flex-wrap:wrap">
    <input type="date" name="date" value="{{ request('date', today()->format('Y-m-d')) }}"
        style="background:var(--card);border:1px solid var(--border);border-radius:9px;padding:8px 12px;color:var(--text);font-size:0.82rem;font-family:inherit">

    <select name="payment" style="background:var(--card);border:1px solid var(--border);border-radius:9px;padding:8px 12px;color:var(--text);font-size:0.82rem;font-family:inherit">
        <option value="">Semua Metode</option>
        <option value="cash" {{ request('payment')==='cash'?'selected':'' }}>💵 Tunai</option>
        <option value="qris" {{ request('payment')==='qris'?'selected':'' }}>📱 QRIS</option>
        <option value="transfer" {{ request('payment')==='transfer'?'selected':'' }}>🏦 Transfer</option>
    </select>

    <button type="submit" class="btn-green" style="padding:8px 18px;font-size:0.82rem">Filter</button>
    <a href="{{ route('transactions.index') }}" class="btn-outline" style="padding:8px 16px;font-size:0.82rem;text-decoration:none">Reset</a>

    @php
        $todaySummary = $transactions->getCollection();
        $todayTotal = $todaySummary->sum('total_amount');
    @endphp
    <div style="margin-left:auto;display:flex;gap:16px;align-items:center">
        <div style="text-align:right">
            <div style="font-size:0.68rem;color:var(--muted2);text-transform:uppercase;letter-spacing:0.07em">Total Ditampilkan</div>
            <div style="font-size:1rem;font-weight:800;color:var(--green)">Rp {{ number_format($todayTotal,0,',','.') }}</div>
        </div>
    </div>
</form>

<div class="card-dark" style="overflow:hidden">
    <table class="table-dark-custom" style="width:100%;border-collapse:collapse">
        <thead><tr>
            <th>Invoice</th>
            <th>Waktu</th>
            <th>Kasir</th>
            <th>Items</th>
            <th>Total</th>
            <th>Bayar</th>
            <th>Kembalian</th>
            <th>Metode</th>
        </tr></thead>
        <tbody>
            @forelse($transactions as $trx)
            <tr>
                <td style="font-weight:700;font-size:0.8rem;color:var(--green)">{{ $trx->invoice_no }}</td>
                <td style="color:var(--muted2);font-size:0.75rem">
                    <div>{{ $trx->created_at->format('d/m/Y') }}</div>
                    <div>{{ $trx->created_at->format('H:i') }}</div>
                </td>
                <td style="font-size:0.82rem">{{ $trx->user->name }}</td>
                <td style="color:var(--muted2);font-size:0.78rem">{{ $trx->items->count() }} item</td>
                <td style="font-weight:700">Rp {{ number_format($trx->total_amount,0,',','.') }}</td>
                <td style="color:var(--muted2);font-size:0.78rem">Rp {{ number_format($trx->paid_amount,0,',','.') }}</td>
                <td style="font-size:0.78rem">
                    @if($trx->change_amount > 0)
                        <span style="color:var(--orange)">Rp {{ number_format($trx->change_amount,0,',','.') }}</span>
                    @else
                        <span style="color:var(--muted)">—</span>
                    @endif
                </td>
                <td>
                    <span class="status-badge badge-{{ $trx->payment_method==='cash'?'orange':($trx->payment_method==='qris'?'blue':'green') }}">
                        {{ $trx->payment_label }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center;padding:48px;color:var(--muted)">
                    <div style="font-size:2.5rem;margin-bottom:10px">🧾</div>
                    <div>Belum ada transaksi pada periode ini</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($transactions->hasPages())
    <div style="padding:14px 20px;border-top:1px solid var(--border)">
        {{ $transactions->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
