@extends('layouts.app')
@section('title', 'Laporan Penjualan')

@section('content')
<div class="topbar">
    <div>
        <div class="page-title">Laporan <span>Penjualan</span></div>
        <div style="font-size:0.75rem;color:var(--muted);margin-top:2px">Analisis performa toko Anda</div>
    </div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('reports.index', array_merge(request()->query(), ['export'=>'pdf'])) }}"
           style="display:flex;align-items:center;gap:6px;padding:9px 16px;border-radius:9px;border:1px solid rgba(255,77,106,0.3);background:rgba(255,77,106,0.08);color:#FF4D6A;text-decoration:none;font-size:0.78rem;font-weight:600">
            📄 Export PDF
        </a>
        <a href="{{ route('reports.index', array_merge(request()->query(), ['export'=>'excel'])) }}"
           style="display:flex;align-items:center;gap:6px;padding:9px 16px;border-radius:9px;border:1px solid rgba(0,214,143,0.3);background:rgba(0,214,143,0.08);color:#00D68F;text-decoration:none;font-size:0.78rem;font-weight:600">
            📊 Export Excel
        </a>
    </div>
</div>

{{-- Period Tabs --}}
<div style="display:flex;gap:6px;margin-bottom:24px">
    @foreach(['today'=>'Hari Ini','week'=>'Minggu Ini','month'=>'Bulan Ini','custom'=>'Custom'] as $key=>$label)
    <a href="{{ route('reports.index', ['period'=>$key]) }}"
       style="padding:8px 18px;border-radius:8px;font-size:0.78rem;font-weight:600;text-decoration:none;transition:all 0.15s;border:1px solid {{ $period===$key ? 'rgba(255,114,64,0.3)' : 'var(--border2)' }};background:{{ $period===$key ? 'rgba(255,114,64,0.08)' : 'transparent' }};color:{{ $period===$key ? '#FF7240' : 'var(--muted2)' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

@if($period === 'custom')
<form method="GET" style="display:flex;gap:10px;align-items:center;margin-bottom:24px">
    <input type="hidden" name="period" value="custom">
    <input type="date" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}"
        style="background:var(--card);border:1px solid var(--border2);border-radius:9px;padding:8px 12px;color:var(--text);font-size:0.82rem;font-family:inherit">
    <span style="color:var(--muted)">—</span>
    <input type="date" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}"
        style="background:var(--card);border:1px solid var(--border2);border-radius:9px;padding:8px 12px;color:var(--text);font-size:0.82rem;font-family:inherit">
    <button type="submit" class="btn-green" style="padding:8px 18px;font-size:0.82rem">Tampilkan</button>
</form>
@endif

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
    @php
        $margin = $summary->total_revenue > 0 ? round(($grossProfit / $summary->total_revenue) * 100, 1) : 0;
        $avgTrx = $summary->total_transactions > 0 ? $summary->total_revenue / $summary->total_transactions : 0;
    @endphp
    <div class="card-dark" style="padding:20px;border-top:2px solid #FF7240">
        <div style="font-size:0.7rem;color:var(--muted2);font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:8px">💰 Total Omzet</div>
        <div style="font-size:1.4rem;font-weight:800">Rp {{ number_format($summary->total_revenue ?? 0, 0, ',', '.') }}</div>
        <div style="font-size:0.72rem;color:var(--muted2);margin-top:4px">{{ $summary->total_transactions ?? 0 }} transaksi</div>
    </div>
    <div class="card-dark" style="padding:20px;border-top:2px solid #00D68F">
        <div style="font-size:0.7rem;color:var(--muted2);font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:8px">📈 Laba Kotor</div>
        <div style="font-size:1.4rem;font-weight:800;color:var(--green)">Rp {{ number_format($grossProfit, 0, ',', '.') }}</div>
        <div style="font-size:0.72rem;color:var(--muted2);margin-top:4px">Margin {{ $margin }}%</div>
    </div>
    <div class="card-dark" style="padding:20px;border-top:2px solid #4D9FFF">
        <div style="font-size:0.7rem;color:var(--muted2);font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:8px">🧾 Avg / Transaksi</div>
        <div style="font-size:1.4rem;font-weight:800">Rp {{ number_format($avgTrx, 0, ',', '.') }}</div>
        <div style="font-size:0.72rem;color:var(--muted2);margin-top:4px">Per transaksi</div>
    </div>
    <div class="card-dark" style="padding:20px;border-top:2px solid #FFB930">
        <div style="font-size:0.7rem;color:var(--muted2);font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:8px">🏆 Produk Terlaris</div>
        <div style="font-size:1rem;font-weight:800;margin-top:4px">{{ $topProducts->first()->product_name ?? '-' }}</div>
        <div style="font-size:0.72rem;color:var(--muted2);margin-top:4px">{{ $topProducts->first()->total_qty ?? 0 }} terjual</div>
    </div>
</div>

{{-- Charts --}}
<div style="display:grid;grid-template-columns:1.6fr 1fr;gap:16px;margin-bottom:24px">
    <div class="card-dark" style="padding:22px">
        <div style="font-size:0.88rem;font-weight:700;margin-bottom:4px">Grafik Penjualan</div>
        <div style="font-size:0.72rem;color:var(--muted2);margin-bottom:20px">
            {{ $startDate->format('d M') }} — {{ $endDate->format('d M Y') }}
        </div>
        <canvas id="salesChart" height="90"></canvas>
    </div>
    <div class="card-dark" style="padding:22px">
        <div style="font-size:0.88rem;font-weight:700;margin-bottom:4px">🏆 Top 5 Produk</div>
        <div style="font-size:0.72rem;color:var(--muted2);margin-bottom:20px">Berdasarkan qty terjual</div>
        @php $maxQty = $topProducts->first()->total_qty ?? 1; @endphp
        @forelse($topProducts->take(5) as $i => $prod)
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
            <div style="font-size:0.72rem;font-weight:800;color:var(--muted);width:16px">#{{ $i+1 }}</div>
            <div style="flex:1">
                <div style="font-size:0.78rem;font-weight:600;margin-bottom:4px">{{ $prod->product_name }}</div>
                <div style="height:5px;background:var(--bg3);border-radius:3px;overflow:hidden">
                    <div style="height:100%;background:#FF7240;border-radius:3px;width:{{ ($prod->total_qty/$maxQty)*100 }}%"></div>
                </div>
            </div>
            <div style="font-size:0.78rem;font-weight:700;color:#FF7240;white-space:nowrap">{{ $prod->total_qty }}</div>
        </div>
        @empty
        <div style="text-align:center;padding:20px;color:var(--muted);font-size:0.82rem">Belum ada data</div>
        @endforelse
    </div>
</div>

{{-- Payment Methods + Transaction Table --}}
<div style="display:grid;grid-template-columns:1fr 2fr;gap:16px;margin-bottom:24px">
    {{-- Payment breakdown --}}
    <div class="card-dark" style="padding:22px">
        <div style="font-size:0.88rem;font-weight:700;margin-bottom:20px">💳 Metode Pembayaran</div>
        @php $totalTrx = $paymentMethods->sum('count'); @endphp
        @forelse($paymentMethods as $pm)
        @php
            $pct = $totalTrx > 0 ? round(($pm->count / $totalTrx) * 100) : 0;
            $pmColors = ['cash'=>'#FFB930','qris'=>'#4D9FFF','transfer'=>'#00D68F'];
            $pmLabels = ['cash'=>'💵 Tunai','qris'=>'📱 QRIS','transfer'=>'🏦 Transfer'];
            $c = $pmColors[$pm->payment_method] ?? '#8892AA';
        @endphp
        <div style="margin-bottom:16px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                <span style="font-size:0.78rem;font-weight:600">{{ $pmLabels[$pm->payment_method] ?? $pm->payment_method }}</span>
                <span style="font-size:0.78rem;color:var(--muted2)">{{ $pm->count }} trx · {{ $pct }}%</span>
            </div>
            <div style="height:6px;background:var(--bg3);border-radius:3px;overflow:hidden">
                <div style="height:100%;background:{{ $c }};width:{{ $pct }}%;border-radius:3px;transition:width 0.5s"></div>
            </div>
            <div style="font-size:0.72rem;color:var(--muted2);margin-top:4px">Rp {{ number_format($pm->total, 0, ',', '.') }}</div>
        </div>
        @empty
        <div style="text-align:center;padding:20px;color:var(--muted);font-size:0.82rem">Belum ada data</div>
        @endforelse
    </div>

    {{-- Transactions table --}}
    <div class="card-dark" style="overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
            <div style="font-size:0.88rem;font-weight:700">Riwayat Transaksi</div>
            <span style="font-size:0.75rem;color:var(--muted2)">{{ $transactions->total() }} transaksi</span>
        </div>
        <table class="table-dark-custom" style="width:100%;border-collapse:collapse">
            <thead><tr>
                <th>Invoice</th><th>Waktu</th><th>Kasir</th><th>Total</th><th>Laba</th><th>Metode</th>
            </tr></thead>
            <tbody>
                @forelse($transactions as $trx)
                <tr>
                    <td style="font-weight:700;font-size:0.78rem">{{ $trx->invoice_no }}</td>
                    <td style="color:var(--muted2);font-size:0.75rem">{{ $trx->created_at->format('d/m H:i') }}</td>
                    <td style="font-size:0.78rem">{{ $trx->user->name }}</td>
                    <td style="color:var(--green);font-weight:700">Rp {{ number_format($trx->total_amount,0,',','.') }}</td>
                    <td style="color:var(--blue);font-size:0.78rem">Rp {{ number_format($trx->total_profit,0,',','.') }}</td>
                    <td><span class="status-badge badge-{{ $trx->payment_method==='cash'?'orange':'blue' }}">{{ $trx->payment_label }}</span></td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:32px">Belum ada transaksi di periode ini</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($transactions->hasPages())
        <div style="padding:12px 20px;border-top:1px solid var(--border)">
            {{ $transactions->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
const salesByDay = @json($salesByDay);
new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: salesByDay.map(d => d.date),
        datasets: [{
            label: 'Omzet',
            data: salesByDay.map(d => d.total),
            borderColor: '#FF7240',
            backgroundColor: 'rgba(255,114,64,0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#FF7240',
            pointRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: '#232938' }, ticks: { color: '#5A6480', font: { size: 11 } } },
            y: { grid: { color: '#232938' }, ticks: { color: '#5A6480', callback: v => 'Rp '+(v/1000).toFixed(0)+'rb' } }
        }
    }
});
</script>
@endpush
