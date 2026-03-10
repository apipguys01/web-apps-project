@extends('layouts.app')
@section('title', 'Detail Transaksi')

@section('content')
<div class="topbar">
    <div style="display:flex;align-items:center;gap:12px">
        <a href="{{ route('transactions.index') }}" style="color:var(--muted2);text-decoration:none;font-size:1.2rem">←</a>
        <div>
            <div class="page-title">{{ $transaction->invoice_no }}</div>
            <div style="font-size:0.75rem;color:var(--muted);margin-top:2px">{{ $transaction->created_at->format('d M Y, H:i') }}</div>
        </div>
    </div>
    @if(auth()->user()->isOwner())
    <div style="display:flex;gap:8px">
        <a href="{{ route('transactions.edit', $transaction) }}"
           style="display:flex;align-items:center;gap:6px;padding:9px 16px;border-radius:9px;border:1px solid rgba(77,159,255,0.3);background:rgba(77,159,255,0.08);color:#4D9FFF;text-decoration:none;font-size:0.82rem;font-weight:600">
            ✏️ Edit
        </a>
        <form method="POST" action="{{ route('transactions.destroy', $transaction) }}"
              onsubmit="return confirm('Hapus transaksi ini? Stok produk akan dikembalikan.')">
            @csrf @method('DELETE')
            <button type="submit"
                style="display:flex;align-items:center;gap:6px;padding:9px 16px;border-radius:9px;border:1px solid rgba(255,77,106,0.3);background:rgba(255,77,106,0.08);color:#FF4D6A;font-size:0.82rem;font-weight:600;cursor:pointer;font-family:inherit">
                🗑️ Hapus
            </button>
        </form>
    </div>
    @endif
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px">

    {{-- Items --}}
    <div>
        <div class="card-dark" style="overflow:hidden;margin-bottom:16px">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
                <div style="font-size:0.88rem;font-weight:700">🧾 Item Transaksi</div>
            </div>
            <table class="table-dark-custom" style="width:100%;border-collapse:collapse">
                <thead><tr>
                    <th>Produk</th><th>Harga</th><th>Qty</th><th>Subtotal</th><th>Laba</th>
                </tr></thead>
                <tbody>
                    @foreach($transaction->items as $item)
                    <tr>
                        <td style="font-weight:600">{{ $item->product_name }}</td>
                        <td style="color:var(--muted2)">Rp {{ number_format($item->price,0,',','.') }}</td>
                        <td>
                            <span style="background:var(--bg3);padding:2px 10px;border-radius:6px;font-size:0.8rem;font-weight:700">
                                {{ $item->qty }}
                            </span>
                        </td>
                        <td style="font-weight:700;color:var(--green)">Rp {{ number_format($item->subtotal,0,',','.') }}</td>
                        <td style="color:var(--blue);font-size:0.78rem">Rp {{ number_format($item->profit,0,',','.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($transaction->note)
        <div class="card-dark" style="padding:18px 20px">
            <div style="font-size:0.75rem;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:8px">📝 Catatan</div>
            <div style="font-size:0.85rem;color:var(--text)">{{ $transaction->note }}</div>
        </div>
        @endif
    </div>

    {{-- Summary --}}
    <div style="display:flex;flex-direction:column;gap:14px">
        <div class="card-dark" style="padding:22px">
            <div style="font-size:0.8rem;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:16px">💰 Ringkasan</div>

            @php $rows = [
                ['Subtotal', 'Rp '.number_format($transaction->total_amount + $transaction->discount_amount,0,',','.'), 'var(--text)'],
                ['Diskon', '- Rp '.number_format($transaction->discount_amount,0,',','.'), 'var(--red)'],
                ['Total', 'Rp '.number_format($transaction->total_amount,0,',','.'), 'var(--green)'],
                ['Bayar', 'Rp '.number_format($transaction->paid_amount,0,',','.'), 'var(--text)'],
                ['Kembalian', 'Rp '.number_format($transaction->change_amount,0,',','.'), 'var(--orange)'],
                ['Laba Kotor', 'Rp '.number_format($transaction->total_profit,0,',','.'), 'var(--blue)'],
            ] @endphp

            @foreach($rows as [$label, $val, $color])
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--border)' : '' }}">
                <span style="font-size:0.78rem;color:var(--muted2)">{{ $label }}</span>
                <span style="font-weight:700;color:{{ $color }};font-size:{{ $loop->index === 2 ? '1.1rem' : '0.88rem' }}">{{ $val }}</span>
            </div>
            @endforeach
        </div>

        <div class="card-dark" style="padding:22px">
            <div style="font-size:0.8rem;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:14px">📋 Info</div>
            @foreach([
                ['Invoice', $transaction->invoice_no],
                ['Kasir', $transaction->user->name],
                ['Metode', $transaction->payment_label],
                ['Waktu', $transaction->created_at->format('d/m/Y H:i')],
            ] as [$label, $val])
            <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border)">
                <span style="font-size:0.75rem;color:var(--muted2)">{{ $label }}</span>
                <span style="font-size:0.78rem;font-weight:600">{{ $val }}</span>
            </div>
            @endforeach
        </div>

        <a href="{{ route('transactions.index') }}" class="btn-outline" style="text-align:center;text-decoration:none;padding:11px;font-size:0.85rem">
            ← Kembali ke Riwayat
        </a>
    </div>

</div>
@endsection