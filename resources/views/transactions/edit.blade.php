@extends('layouts.app')
@section('title', 'Edit Transaksi')

@section('content')
<div class="topbar">
    <div style="display:flex;align-items:center;gap:12px">
        <a href="{{ route('transactions.show', $transaction) }}" style="color:var(--muted2);text-decoration:none;font-size:1.2rem">←</a>
        <div>
            <div class="page-title">Edit <span>Transaksi</span></div>
            <div style="font-size:0.75rem;color:var(--muted);margin-top:2px">{{ $transaction->invoice_no }} · {{ $transaction->created_at->format('d M Y, H:i') }}</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px">

    {{-- Items (read-only) --}}
    <div class="card-dark" style="overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
            <div style="font-size:0.88rem;font-weight:700">🧾 Item Transaksi</div>
            <span style="font-size:0.7rem;color:var(--muted2);background:var(--bg3);padding:3px 9px;border-radius:6px">Read-only</span>
        </div>
        <table class="table-dark-custom" style="width:100%;border-collapse:collapse">
            <thead><tr>
                <th>Produk</th><th>Harga</th><th>Qty</th><th>Subtotal</th>
            </tr></thead>
            <tbody>
                @foreach($transaction->items as $item)
                <tr>
                    <td style="font-weight:600">{{ $item->product_name }}</td>
                    <td style="color:var(--muted2)">Rp {{ number_format($item->price,0,',','.') }}</td>
                    <td><span style="background:var(--bg3);padding:2px 10px;border-radius:6px;font-size:0.8rem;font-weight:700">{{ $item->qty }}</span></td>
                    <td style="font-weight:700;color:var(--green)">Rp {{ number_format($item->subtotal,0,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
            <span style="font-size:0.82rem;color:var(--muted2)">Total</span>
            <span style="font-size:1.1rem;font-weight:800;color:var(--green)">Rp {{ number_format($transaction->total_amount,0,',','.') }}</span>
        </div>
    </div>

    {{-- Edit Form --}}
    <div>
        <form method="POST" action="{{ route('transactions.update', $transaction) }}">
        @csrf @method('PUT')

        <div class="card-dark" style="padding:24px;margin-bottom:16px">
            <div style="font-size:0.8rem;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:18px">✏️ Yang Bisa Diedit</div>

            <div style="margin-bottom:16px">
                <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Metode Pembayaran</label>
                <select name="payment_method" class="form-control-dark">
                    @foreach(['cash'=>'💵 Tunai','qris'=>'📱 QRIS','transfer'=>'🏦 Transfer'] as $val=>$label)
                    <option value="{{ $val }}" {{ old('payment_method',$transaction->payment_method)===$val?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('payment_method')<div style="color:var(--red);font-size:0.72rem;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:16px">
                <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Uang Bayar</label>
                <div style="position:relative">
                    <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:0.82rem">Rp</span>
                    <input type="number" name="paid_amount" value="{{ old('paid_amount',$transaction->paid_amount) }}"
                        min="{{ $transaction->total_amount }}" required class="form-control-dark" style="padding-left:34px">
                </div>
                @error('paid_amount')<div style="color:var(--red);font-size:0.72rem;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:20px">
                <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Catatan</label>
                <textarea name="note" rows="3" class="form-control-dark" placeholder="Tambahkan catatan...">{{ old('note',$transaction->note) }}</textarea>
            </div>

            <div style="padding:12px;background:var(--bg3);border-radius:10px;margin-bottom:20px">
                <div style="font-size:0.68rem;color:var(--muted2);text-transform:uppercase;letter-spacing:0.07em;margin-bottom:8px;font-weight:700">ℹ️ Tidak Bisa Diubah</div>
                @foreach([
                    ['Invoice', $transaction->invoice_no],
                    ['Kasir', $transaction->user->name],
                    ['Total', 'Rp '.number_format($transaction->total_amount,0,',','.')],
                    ['Waktu', $transaction->created_at->format('d/m/Y H:i')],
                ] as [$label,$val])
                <div style="display:flex;justify-content:space-between;font-size:0.75rem;margin-bottom:4px">
                    <span style="color:var(--muted2)">{{ $label }}</span>
                    <span style="font-weight:600">{{ $val }}</span>
                </div>
                @endforeach
            </div>

            <button type="submit" class="btn-green" style="width:100%;padding:12px">💾 Simpan Perubahan</button>
            <a href="{{ route('transactions.show', $transaction) }}"
               style="display:block;text-align:center;margin-top:8px;padding:10px;border-radius:9px;border:1px solid var(--border2);color:var(--muted2);text-decoration:none;font-size:0.85rem;font-weight:600">
               Batal
            </a>
        </div>

        </form>

        {{-- Danger zone --}}
        <div class="card-dark" style="padding:20px;border-color:rgba(255,77,106,0.2)">
            <div style="font-size:0.78rem;font-weight:700;color:var(--red);margin-bottom:10px">⚠️ Danger Zone</div>
            <div style="font-size:0.75rem;color:var(--muted2);margin-bottom:14px">Hapus transaksi ini secara permanen. Stok semua produk akan dikembalikan otomatis.</div>
            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}"
                  onsubmit="return confirm('Yakin hapus transaksi {{ $transaction->invoice_no }}?\nStok produk akan dikembalikan.')">
                @csrf @method('DELETE')
                <button type="submit"
                    style="width:100%;padding:10px;background:rgba(255,77,106,0.08);border:1px solid rgba(255,77,106,0.3);border-radius:9px;color:var(--red);font-size:0.82rem;font-weight:700;cursor:pointer;font-family:inherit;transition:all 0.15s"
                    onmouseover="this.style.background='rgba(255,77,106,0.15)'" onmouseout="this.style.background='rgba(255,77,106,0.08)'">
                    🗑️ Hapus Transaksi
                </button>
            </form>
        </div>
    </div>

</div>
@endsection