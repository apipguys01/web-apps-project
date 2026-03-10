@extends('layouts.app')
@section('title', 'Tambah Produk')

@section('content')
<div class="topbar">
    <div style="display:flex;align-items:center;gap:12px">
        <a href="{{ route('products.index') }}" style="color:var(--muted2);text-decoration:none;font-size:1.2rem">←</a>
        <div>
            <div class="page-title">Tambah <span>Produk</span></div>
            <div style="font-size:0.75rem;color:var(--muted);margin-top:2px">Isi detail produk baru</div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
@csrf

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">

    {{-- Left: Main Info --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        <div class="card-dark" style="padding:24px">
            <div style="font-size:0.8rem;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:18px">📝 Info Produk</div>

            <div style="margin-bottom:16px">
                <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Nama Produk *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="form-control-dark" placeholder="Contoh: Es Teh Manis">
                @error('name')<div style="color:var(--red);font-size:0.72rem;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
                <div>
                    <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">SKU *</label>
                    <div style="display:flex;gap:8px">
                        <input type="text" name="sku" id="sku" value="{{ old('sku') }}" required
                            class="form-control-dark" placeholder="BVR-001" style="flex:1">
                        <button type="button" onclick="generateSku()"
                            style="padding:0 12px;background:var(--bg3);border:1px solid var(--border2);border-radius:9px;color:var(--muted2);cursor:pointer;font-size:0.75rem;white-space:nowrap">
                            Auto
                        </button>
                    </div>
                    @error('sku')<div style="color:var(--red);font-size:0.72rem;margin-top:4px">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Satuan *</label>
                    <select name="unit" class="form-control-dark">
                        @foreach(['pcs','kg','gram','liter','ml','lusin','pak','box','botol','porsi'] as $unit)
                        <option value="{{ $unit }}" {{ old('unit','pcs')===$unit?'selected':'' }}>{{ $unit }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Kategori</label>
                <select name="category_id" class="form-control-dark">
                    <option value="">— Tanpa Kategori —</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id')==$cat->id?'selected':'' }}>
                        {{ $cat->icon }} {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="card-dark" style="padding:24px">
            <div style="font-size:0.8rem;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:18px">💰 Harga</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Harga Jual *</label>
                    <div style="position:relative">
                        <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:0.82rem">Rp</span>
                        <input type="number" name="price" value="{{ old('price',0) }}" min="0" required
                            class="form-control-dark" style="padding-left:34px" oninput="calcMargin()">
                    </div>
                    @error('price')<div style="color:var(--red);font-size:0.72rem;margin-top:4px">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Harga Modal *</label>
                    <div style="position:relative">
                        <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:0.82rem">Rp</span>
                        <input type="number" name="cost_price" value="{{ old('cost_price',0) }}" min="0" required
                            class="form-control-dark" style="padding-left:34px" oninput="calcMargin()">
                    </div>
                </div>
            </div>
            <div id="marginInfo" style="margin-top:12px;padding:10px 14px;background:var(--bg3);border-radius:8px;font-size:0.78rem;color:var(--muted2)">
                Margin: <span id="marginVal" style="font-weight:700;color:var(--green)">0%</span>
                &nbsp;·&nbsp; Laba per unit: <span id="profitVal" style="font-weight:700">Rp 0</span>
            </div>
        </div>

        <div class="card-dark" style="padding:24px">
            <div style="font-size:0.8rem;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:18px">📦 Stok</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Stok Awal *</label>
                    <input type="number" name="stock" value="{{ old('stock',0) }}" min="0" required class="form-control-dark">
                </div>
                <div>
                    <label style="font-size:0.72rem;font-weight:600;color:var(--muted2);display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em">Min. Stok Alert *</label>
                    <input type="number" name="min_stock" value="{{ old('min_stock',5) }}" min="0" required class="form-control-dark">
                    <div style="font-size:0.68rem;color:var(--muted);margin-top:4px">Notif saat stok ≤ nilai ini</div>
                </div>
            </div>
        </div>

    </div>

    {{-- Right: Image + Status --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        <div class="card-dark" style="padding:24px">
            <div style="font-size:0.8rem;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:18px">🖼️ Foto Produk</div>
            <div id="imagePreview" style="width:100%;aspect-ratio:1;background:var(--bg3);border:2px dashed var(--border2);border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;margin-bottom:12px;overflow:hidden"
                onclick="document.getElementById('imageInput').click()">
                <div style="font-size:2.5rem;margin-bottom:8px">📷</div>
                <div style="font-size:0.75rem;color:var(--muted)">Klik untuk upload foto</div>
                <div style="font-size:0.68rem;color:var(--muted);margin-top:2px">Max 2MB · JPG/PNG</div>
            </div>
            <input type="file" name="image" id="imageInput" accept="image/*" style="display:none" onchange="previewImage(this)">
        </div>

        <div class="card-dark" style="padding:24px">
            <div style="font-size:0.8rem;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:18px">⚙️ Status</div>
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active',1)?'checked':'' }}
                    style="width:18px;height:18px;accent-color:var(--green)">
                <div>
                    <div style="font-size:0.82rem;font-weight:600">Produk Aktif</div>
                    <div style="font-size:0.72rem;color:var(--muted)">Tampil di kasir jika aktif</div>
                </div>
            </label>
        </div>

        <div style="display:flex;flex-direction:column;gap:8px">
            <button type="submit" class="btn-green" style="padding:12px;font-size:0.88rem">
                💾 Simpan Produk
            </button>
            <a href="{{ route('products.index') }}" class="btn-outline" style="padding:11px;font-size:0.88rem;text-align:center;text-decoration:none">
                Batal
            </a>
        </div>

    </div>
</div>

</form>
@endsection

@push('scripts')
<script>
function generateSku() {
    fetch('{{ route("products.generate-sku") }}')
        .then(r => r.json())
        .then(d => document.getElementById('sku').value = d.sku);
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function calcMargin() {
    const price = parseFloat(document.querySelector('[name=price]').value) || 0;
    const cost  = parseFloat(document.querySelector('[name=cost_price]').value) || 0;
    const profit = price - cost;
    const margin = price > 0 ? ((profit / price) * 100).toFixed(1) : 0;
    document.getElementById('marginVal').textContent = margin + '%';
    document.getElementById('marginVal').style.color = margin >= 30 ? 'var(--green)' : margin >= 10 ? '#FFB930' : 'var(--red)';
    document.getElementById('profitVal').textContent = 'Rp ' + profit.toLocaleString('id');
}
</script>
@endpush
