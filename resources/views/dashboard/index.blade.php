@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="topbar">
    <div>
        <div class="page-title">Dashboard <span>Overview</span></div>
        <div style="font-size:0.75rem;color:var(--muted);margin-top:2px">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</div>
    </div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('pos') }}" class="btn-green" style="text-decoration:none;display:flex;align-items:center;gap:6px;font-size:0.85rem">
            🛒 Buka Kasir
        </a>
    </div>
</div>

{{-- Stat Cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px">
    @php
        $stats = [
            ['icon'=>'💰','label'=>'Omzet Hari Ini','value'=>'Rp '.number_format($todayRevenue,0,',','.'),'change'=>$revenueChange,'color'=>'green','suffix'=>'% vs kemarin'],
            ['icon'=>'🧾','label'=>'Transaksi','value'=>$todayTransactions,'change'=>null,'color'=>'orange','suffix'=>'transaksi hari ini'],
            ['icon'=>'📦','label'=>'Total Produk','value'=>$totalProducts,'change'=>null,'color'=>'blue','suffix'=>'produk aktif'],
            ['icon'=>'⚠️','label'=>'Stok Menipis','value'=>$lowStockCount,'change'=>null,'color'=>'red','suffix'=>'perlu restock'],
        ];
        $colors = ['green'=>'#00D68F','orange'=>'#FF7240','blue'=>'#4D9FFF','red'=>'#FF4D6A'];
    @endphp

    @foreach($stats as $stat)
    <div class="card-dark" style="padding:20px;border-top:2px solid {{ $colors[$stat['color']] }};transition:transform 0.2s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
        <div style="width:38px;height:38px;border-radius:10px;background:rgba({{ $stat['color']=='green'?'0,214,143':($stat['color']=='orange'?'255,114,64':($stat['color']=='blue'?'77,159,255':'255,77,106')) }},0.1);display:flex;align-items:center;justify-content:center;font-size:1.1rem;margin-bottom:14px">{{ $stat['icon'] }}</div>
        <div style="font-size:0.72rem;color:var(--muted2);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px">{{ $stat['label'] }}</div>
        <div style="font-size:1.5rem;font-weight:800;letter-spacing:-0.03em">{{ $stat['value'] }}</div>
        <div style="font-size:0.72rem;margin-top:6px;color:{{ $stat['color']=='red'?'var(--red)':'var(--muted2)' }}">
            @if($stat['change'] !== null)
                <span style="color:{{ $stat['change'] >= 0 ? 'var(--green)' : 'var(--red)' }}">
                    {{ $stat['change'] >= 0 ? '↑' : '↓' }} {{ abs($stat['change']) }}%
                </span> vs kemarin
            @else
                {{ $stat['suffix'] }}
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- Charts Row --}}
<div style="display:grid;grid-template-columns:1.6fr 1fr;gap:16px;margin-bottom:24px">
    {{-- Bar Chart --}}
    <div class="card-dark" style="padding:22px">
        <div style="font-size:0.88rem;font-weight:700;margin-bottom:4px">Penjualan 7 Hari</div>
        <div style="font-size:0.72rem;color:var(--muted2);margin-bottom:20px">Total omzet per hari minggu ini</div>
        <canvas id="salesChart" height="100"></canvas>
    </div>
    {{-- Donut --}}
    <div class="card-dark" style="padding:22px">
        <div style="font-size:0.88rem;font-weight:700;margin-bottom:4px">Kategori Terlaris</div>
        <div style="font-size:0.72rem;color:var(--muted2);margin-bottom:20px">Distribusi omzet bulan ini</div>
        @if($categoryRevenue->count())
            <canvas id="categoryChart" height="130"></canvas>
        @else
            <div style="text-align:center;padding:30px;color:var(--muted)">Belum ada data</div>
        @endif
    </div>
</div>

{{-- Bottom Row --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
    {{-- Recent Transactions --}}
    <div class="card-dark" style="overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
            <div style="font-size:0.88rem;font-weight:700">Transaksi Terbaru</div>
            <a href="{{ route('transactions.index') }}" style="font-size:0.72rem;color:var(--muted2);text-decoration:none">Lihat Semua →</a>
        </div>
        <table class="table-dark-custom" style="width:100%;border-collapse:collapse">
            <thead><tr>
                <th>Invoice</th><th>Kasir</th><th>Total</th><th>Metode</th>
            </tr></thead>
            <tbody>
                @forelse($recentTransactions as $trx)
                <tr>
                    <td style="font-weight:700;font-size:0.78rem">{{ $trx->invoice_no }}</td>
                    <td style="color:var(--muted2)">{{ $trx->user->name }}</td>
                    <td style="color:var(--green);font-weight:700">Rp {{ number_format($trx->total_amount,0,',','.') }}</td>
                    <td><span class="status-badge badge-{{ $trx->payment_method === 'cash' ? 'orange' : 'blue' }}">{{ $trx->payment_label }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px">Belum ada transaksi hari ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Low Stock Alert --}}
    <div class="card-dark" style="overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
            <div style="font-size:0.88rem;font-weight:700">⚠️ Stok Menipis</div>
            <a href="{{ route('products.index', ['low_stock' => 1]) }}" style="font-size:0.72rem;color:var(--muted2);text-decoration:none">Lihat Semua →</a>
        </div>
        @forelse($lowStockProducts as $product)
        <div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--border)">
            <div style="font-size:1.4rem">{{ $product->category->icon ?? '📦' }}</div>
            <div style="flex:1">
                <div style="font-size:0.82rem;font-weight:600">{{ $product->name }}</div>
                <div style="font-size:0.7rem;color:var(--muted)">Min. stok: {{ $product->min_stock }}</div>
            </div>
            <span class="status-badge badge-red">{{ $product->stock }} tersisa</span>
        </div>
        @empty
        <div style="text-align:center;padding:40px 20px;color:var(--muted)">
            <div style="font-size:2rem;margin-bottom:8px">✅</div>
            <div style="font-size:0.82rem">Semua stok aman</div>
        </div>
        @endforelse
    </div>
</div>

@endsection

@push('scripts')
<script>
// Sales Chart
const salesData = @json($salesChart);
new Chart(document.getElementById('salesChart'), {
    type: 'bar',
    data: {
        labels: salesData.map(d => d.label),
        datasets: [{
            data: salesData.map(d => d.value),
            backgroundColor: salesData.map((d, i) => i === salesData.length - 1 ? '#00D68F' : 'rgba(0,214,143,0.4)'),
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: '#232938' }, ticks: { color: '#5A6480' } },
            y: { grid: { color: '#232938' }, ticks: { color: '#5A6480', callback: v => 'Rp ' + (v/1000).toFixed(0) + 'rb' } }
        }
    }
});

// Category Chart
@if($categoryRevenue->count())
const catData = @json($categoryRevenue);
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: catData.map(d => d.name),
        datasets: [{
            data: catData.map(d => d.total),
            backgroundColor: ['#00D68F','#FF7240','#4D9FFF','#FFB930','#FF4D6A'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: {
            legend: { position: 'right', labels: { color: '#8892AA', font: { size: 11 }, padding: 12 } }
        }
    }
});
@endif
</script>
@endpush
