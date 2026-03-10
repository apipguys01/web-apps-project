<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1f2e; background: #fff; }

.header { background: #1a1f2e; color: #fff; padding: 20px 24px; margin-bottom: 20px; }
.header h1 { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
.header .sub { font-size: 11px; color: #8892aa; }
.header .period { font-size: 12px; color: #00d68f; font-weight: 600; margin-top: 6px; }

.section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em;
    color: #5a6480; margin-bottom: 10px; margin-top: 20px; padding-bottom: 4px;
    border-bottom: 1px solid #e8ecf0; }

.summary-grid { display: table; width: 100%; margin-bottom: 16px; }
.summary-item { display: table-cell; width: 25%; padding: 12px 14px; text-align: center;
    border: 1px solid #e8ecf0; border-radius: 6px; }
.summary-item .val { font-size: 15px; font-weight: 800; color: #1a1f2e; margin-bottom: 2px; }
.summary-item .val.green { color: #00a86b; }
.summary-item .val.blue { color: #2d7dd2; }
.summary-item .label { font-size: 9px; color: #8892aa; text-transform: uppercase; letter-spacing: 0.05em; }
.summary-spacer { display: table-cell; width: 2%; }

table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 10px; }
thead tr { background: #1a1f2e; color: #fff; }
thead th { padding: 8px 10px; text-align: left; font-weight: 600; }
tbody tr:nth-child(even) { background: #f8faff; }
tbody td { padding: 7px 10px; border-bottom: 1px solid #e8ecf0; }
.td-right { text-align: right; }
.td-center { text-align: center; }
.badge { padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; }
.badge-green { background: #e6f9f3; color: #00a86b; }
.badge-orange { background: #fff3ec; color: #e05a00; }
.badge-blue { background: #eef4ff; color: #2d7dd2; }

.two-col { display: table; width: 100%; }
.col-left { display: table-cell; width: 48%; vertical-align: top; }
.col-right { display: table-cell; width: 48%; vertical-align: top; padding-left: 16px; }

.footer { margin-top: 28px; padding-top: 10px; border-top: 1px solid #e8ecf0;
    font-size: 9px; color: #8892aa; text-align: center; }
</style>
</head>
<body>

<div class="header">
    <h1>📊 Laporan Penjualan &mdash; {{ $store->name }}</h1>
    <div class="sub">{{ $store->address ?? '' }}</div>
    <div class="period">Periode: {{ $startDate->format('d M Y') }} &ndash; {{ $endDate->format('d M Y') }}</div>
</div>

<div style="padding: 0 24px">

{{-- Summary --}}
<div class="section-title">Ringkasan</div>
@php
    $margin = ($summary->total_revenue ?? 0) > 0
        ? round(($grossProfit / $summary->total_revenue) * 100, 1) : 0;
    $avgTrx = ($summary->total_transactions ?? 0) > 0
        ? $summary->total_revenue / $summary->total_transactions : 0;
@endphp
<table style="margin-bottom:20px">
    <tr>
        <td style="width:50%;padding:12px;border:1px solid #e8ecf0;text-align:center;border-radius:4px">
            <div style="font-size:15px;font-weight:800;color:#00a86b">Rp {{ number_format($summary->total_revenue ?? 0, 0, ',', '.') }}</div>
            <div style="font-size:9px;color:#8892aa;text-transform:uppercase;letter-spacing:0.05em;margin-top:2px">Total Omzet</div>
        </td>
        <td style="width:4%"></td>
        <td style="width:20%;padding:12px;border:1px solid #e8ecf0;text-align:center;border-radius:4px">
            <div style="font-size:15px;font-weight:800">{{ $summary->total_transactions ?? 0 }}</div>
            <div style="font-size:9px;color:#8892aa;text-transform:uppercase;margin-top:2px">Transaksi</div>
        </td>
        <td style="width:4%"></td>
        <td style="width:22%;padding:12px;border:1px solid #e8ecf0;text-align:center;border-radius:4px">
            <div style="font-size:15px;font-weight:800;color:#2d7dd2">Rp {{ number_format($grossProfit, 0, ',', '.') }}</div>
            <div style="font-size:9px;color:#8892aa;text-transform:uppercase;margin-top:2px">Laba Kotor ({{ $margin }}%)</div>
        </td>
    </tr>
</table>

<div class="two-col">
    <div class="col-left">
        {{-- Top Products --}}
        <div class="section-title">Top 10 Produk</div>
        <table>
            <thead>
                <tr><th>#</th><th>Produk</th><th class="td-right">Qty</th><th class="td-right">Omzet</th></tr>
            </thead>
            <tbody>
                @forelse($topProducts as $i => $prod)
                <tr>
                    <td class="td-center">{{ $i+1 }}</td>
                    <td>{{ $prod->product_name }}</td>
                    <td class="td-right" style="font-weight:700">{{ $prod->total_qty }}</td>
                    <td class="td-right">Rp {{ number_format($prod->total_revenue,0,',','.') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="td-center" style="padding:16px;color:#8892aa">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="col-right">
        {{-- Payment Methods --}}
        <div class="section-title">Metode Pembayaran</div>
        <table>
            <thead>
                <tr><th>Metode</th><th class="td-right">Transaksi</th><th class="td-right">Total</th></tr>
            </thead>
            <tbody>
                @php $pmMap = ['cash'=>'Tunai','qris'=>'QRIS','transfer'=>'Transfer']; @endphp
                @forelse($paymentMethods as $pm)
                <tr>
                    <td>{{ $pmMap[$pm->payment_method] ?? $pm->payment_method }}</td>
                    <td class="td-right">{{ $pm->count }}</td>
                    <td class="td-right">Rp {{ number_format($pm->total,0,',','.') }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="td-center" style="padding:16px;color:#8892aa">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- Penjualan per hari --}}
        <div class="section-title">Penjualan Per Hari</div>
        <table>
            <thead><tr><th>Tanggal</th><th class="td-right">Transaksi</th><th class="td-right">Omzet</th></tr></thead>
            <tbody>
                @forelse($salesByDay as $day)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</td>
                    <td class="td-right">{{ $day->count }}</td>
                    <td class="td-right">Rp {{ number_format($day->total,0,',','.') }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="td-center" style="padding:16px;color:#8892aa">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Transactions --}}
<div class="section-title">Riwayat Transaksi</div>
<table>
    <thead>
        <tr>
            <th>Invoice</th><th>Tanggal</th><th>Kasir</th>
            <th class="td-right">Total</th><th class="td-right">Laba</th><th>Metode</th>
        </tr>
    </thead>
    <tbody>
        @forelse($allTransactions->take(100) as $trx)
        <tr>
            <td style="font-weight:700;color:#00a86b">{{ $trx->invoice_no }}</td>
            <td>{{ $trx->created_at->format('d/m H:i') }}</td>
            <td>{{ $trx->user->name }}</td>
            <td class="td-right" style="font-weight:700">Rp {{ number_format($trx->total_amount,0,',','.') }}</td>
            <td class="td-right" style="color:#2d7dd2">Rp {{ number_format($trx->total_profit ?? 0,0,',','.') }}</td>
            <td>
                <span class="badge badge-{{ $trx->payment_method==='cash'?'orange':($trx->payment_method==='qris'?'blue':'green') }}">
                    {{ $pmMap[$trx->payment_method] ?? $trx->payment_method }}
                </span>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="td-center" style="padding:20px;color:#8892aa">Belum ada transaksi</td></tr>
        @endforelse
    </tbody>
</table>
@if($allTransactions->count() > 100)
<div style="text-align:center;font-size:9px;color:#8892aa;margin-top:-10px;margin-bottom:16px">
    Menampilkan 100 dari {{ $allTransactions->count() }} transaksi. Export Excel untuk data lengkap.
</div>
@endif

<div class="footer">
    Laporan dibuat otomatis oleh Sistem UMKM Manager · {{ now()->format('d M Y H:i') }} · {{ $store->name }}
</div>

</div>
</body>
</html>
