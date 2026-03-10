@extends('layouts.app')
@section('title', 'Kasir / POS')

@push('styles')
<style>
.pos-wrap { display:flex; gap:0; margin:-28px -32px; height:calc(100vh - 48px); }
.pos-left { flex:1; padding:24px; overflow-y:auto; background:var(--bg); }
.pos-right { width:340px; background:var(--bg2); border-left:1px solid var(--border); display:flex; flex-direction:column; flex-shrink:0; }
.product-tile { background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px 14px; cursor:pointer; transition:all 0.2s; }
.product-tile:hover { border-color:var(--green); transform:translateY(-2px); box-shadow:0 8px 20px rgba(0,0,0,0.3); }
.product-tile:active { transform:scale(0.97); }
.cart-item { display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid var(--border); }
.cart-item:last-child { border-bottom:none; }
.qty-btn { width:26px; height:26px; border-radius:6px; background:var(--bg3); border:1px solid var(--border2); color:var(--text); font-size:0.9rem; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.15s; }
.qty-btn:hover { background:var(--border2); }
.pay-method { padding:8px 4px; border-radius:8px; text-align:center; border:1px solid var(--border2); cursor:pointer; font-size:0.7rem; font-weight:600; color:var(--muted2); transition:all 0.15s; background:transparent; font-family:inherit; }
.pay-method.active { background:var(--green-bg); color:var(--green); border-color:rgba(0,214,143,0.3); }
</style>
@endpush

@section('content')
<div class="pos-wrap">

    {{-- LEFT: Products --}}
    <div class="pos-left">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <div class="page-title" style="font-size:1.1rem">Kasir <span>/ POS</span></div>
            <div style="font-size:0.75rem;color:var(--muted)">{{ auth()->user()->name }} · {{ now()->format('H:i') }}</div>
        </div>

        <input type="text" id="searchInput" placeholder="🔍  Cari produk atau ketik nama..."
            style="width:100%;background:var(--card);border:1px solid var(--border);border-radius:12px;padding:11px 16px;color:var(--text);font-size:0.88rem;font-family:inherit;margin-bottom:14px;transition:border-color 0.2s"
            oninput="filterProducts(this.value)">

        {{-- Category tabs --}}
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:18px">
            <button class="cat-btn active" onclick="filterCat(this,'all')"
                style="padding:5px 14px;border-radius:20px;font-size:0.75rem;font-weight:600;border:1px solid rgba(0,214,143,0.3);background:var(--green-bg);color:var(--green);cursor:pointer;font-family:inherit">
                Semua
            </button>
            @foreach($categories as $cat)
            <button class="cat-btn" onclick="filterCat(this,'{{ $cat->id }}')"
                style="padding:5px 14px;border-radius:20px;font-size:0.75rem;font-weight:600;border:1px solid var(--border2);background:transparent;color:var(--muted2);cursor:pointer;font-family:inherit">
                {{ $cat->icon }} {{ $cat->name }}
            </button>
            @endforeach
        </div>

        {{-- Product Grid --}}
        <div id="productGrid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
            @foreach($products as $product)
            <div class="product-tile"
                data-id="{{ $product->id }}"
                data-name="{{ $product->name }}"
                data-price="{{ $product->price }}"
                data-stock="{{ $product->stock }}"
                data-cat="{{ $product->category_id }}"
                data-emoji="{{ $product->category->icon ?? '📦' }}"
                onclick="addToCart(this)">
                <div style="font-size:2rem;margin-bottom:10px">{{ $product->category->icon ?? '📦' }}</div>
                <div style="font-size:0.8rem;font-weight:600;margin-bottom:4px;line-height:1.3">{{ $product->name }}</div>
                <div style="font-size:0.82rem;color:var(--green);font-weight:700">Rp {{ number_format($product->price,0,',','.') }}</div>
                <div style="font-size:0.68rem;margin-top:3px;color:{{ $product->stock <= $product->min_stock ? 'var(--red)' : 'var(--muted)' }}">
                    {{ $product->stock <= $product->min_stock ? '⚠️' : '' }} Stok: {{ $product->stock }}
                </div>
            </div>
            @endforeach
        </div>

        @if($products->isEmpty())
        <div style="text-align:center;padding:60px 20px;color:var(--muted)">
            <div style="font-size:3rem;margin-bottom:12px">📦</div>
            <div>Belum ada produk aktif.<br><a href="{{ route('products.create') }}" style="color:var(--green)">Tambah produk</a></div>
        </div>
        @endif
    </div>

    {{-- RIGHT: Cart --}}
    <div class="pos-right">
        <div style="padding:16px 18px;border-bottom:1px solid var(--border)">
            <div style="font-size:0.9rem;font-weight:700;display:flex;align-items:center;gap:8px">
                🛒 Keranjang
                <span id="cartCount" style="background:var(--green);color:#000;font-size:0.68rem;font-weight:800;border-radius:10px;padding:1px 7px">0</span>
            </div>
        </div>

        <div id="cartItems" style="flex:1;overflow-y:auto;padding:12px 16px">
            <div id="emptyCart" style="text-align:center;padding:40px 20px;color:var(--muted)">
                <div style="font-size:2rem;margin-bottom:10px">🛒</div>
                <div style="font-size:0.82rem">Pilih produk untuk mulai</div>
            </div>
        </div>

        <div style="padding:16px;border-top:1px solid var(--border)">
            <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                <span style="font-size:0.75rem;color:var(--muted2)">Subtotal</span>
                <span id="subtotalVal" style="font-size:0.88rem;font-weight:700">Rp 0</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                <span style="font-size:0.75rem;color:var(--muted2)">Diskon</span>
                <div style="display:flex;align-items:center;gap:4px">
                    <span style="font-size:0.78rem;color:var(--muted)">Rp</span>
                    <input type="number" id="discountInput" value="0" min="0" oninput="renderTotals()"
                        style="width:70px;background:var(--bg3);border:1px solid var(--border2);border-radius:6px;padding:3px 6px;color:var(--text);font-size:0.78rem;text-align:right;font-family:inherit">
                </div>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding-top:8px;border-top:1px solid var(--border);margin-bottom:14px">
                <span style="font-size:0.88rem;font-weight:700">Total</span>
                <span id="totalVal" style="font-size:1.3rem;font-weight:800;color:var(--green)">Rp 0</span>
            </div>

            {{-- Payment method --}}
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:14px">
                <button class="pay-method active" onclick="setPayment(this,'cash')">💵<br>Tunai</button>
                <button class="pay-method" onclick="setPayment(this,'qris')">📱<br>QRIS</button>
                <button class="pay-method" onclick="setPayment(this,'transfer')">🏦<br>Transfer</button>
            </div>

            {{-- Bayar input (for cash) --}}
            <div id="cashInput" style="margin-bottom:10px">
                <label style="font-size:0.68rem;color:var(--muted2);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;display:block;margin-bottom:4px">Uang Bayar</label>
                <div style="position:relative">
                    <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:0.78rem;color:var(--muted)">Rp</span>
                    <input type="number" id="paidInput" oninput="renderChange()"
                        style="width:100%;background:var(--bg3);border:1px solid var(--border2);border-radius:8px;padding:8px 10px 8px 28px;color:var(--text);font-size:0.88rem;font-family:inherit">
                </div>
                <div id="changeRow" style="display:none;margin-top:6px;display:flex;justify-content:space-between">
                    <span style="font-size:0.75rem;color:var(--muted2)">Kembalian</span>
                    <span id="changeVal" style="font-size:0.88rem;font-weight:700;color:var(--green)">Rp 0</span>
                </div>
            </div>

            <button id="checkoutBtn" onclick="checkout()"
                style="width:100%;background:var(--green);color:#000;border:none;border-radius:10px;padding:13px;font-size:0.9rem;font-weight:800;cursor:pointer;font-family:inherit;transition:all 0.2s;opacity:0.5"
                disabled>
                Proses Pembayaran →
            </button>
        </div>
    </div>
</div>

{{-- Success Modal --}}
<div id="successModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:999;align-items:center;justify-content:center">
    <div style="background:var(--card);border:1px solid var(--border2);border-radius:20px;padding:40px;max-width:360px;width:90%;text-align:center">
        <div style="font-size:3rem;margin-bottom:12px">✅</div>
        <div style="font-size:1.2rem;font-weight:800;margin-bottom:8px">Transaksi Berhasil!</div>
        <div id="modalInvoice" style="font-size:0.82rem;color:var(--muted2);margin-bottom:4px"></div>
        <div id="modalChange" style="font-size:1rem;font-weight:700;color:var(--green);margin-bottom:24px"></div>
        <button onclick="closeModal()" class="btn-green" style="width:100%;padding:12px;font-size:0.9rem">
            Transaksi Baru
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
let cart = {};
let paymentMethod = 'cash';

// ── Cart ───────────────────────────────────────────────────────
function addToCart(tile) {
    const id    = tile.dataset.id;
    const name  = tile.dataset.name;
    const price = parseFloat(tile.dataset.price);
    const stock = parseInt(tile.dataset.stock);
    const emoji = tile.dataset.emoji;

    if (cart[id] && cart[id].qty >= stock) {
        tile.style.borderColor = 'var(--red)';
        setTimeout(() => tile.style.borderColor = '', 500);
        return;
    }

    tile.style.borderColor = 'var(--green)';
    tile.style.boxShadow = '0 0 16px rgba(0,214,143,0.3)';
    setTimeout(() => { tile.style.borderColor = ''; tile.style.boxShadow = ''; }, 300);

    if (cart[id]) { cart[id].qty++; }
    else { cart[id] = { name, price, stock, emoji, qty: 1 }; }

    renderCart();
}

function changeQty(id, delta) {
    if (!cart[id]) return;
    cart[id].qty += delta;
    if (cart[id].qty <= 0) delete cart[id];
    renderCart();
}

function renderCart() {
    const entries = Object.entries(cart);
    const container = document.getElementById('cartItems');
    const empty = document.getElementById('emptyCart');
    const btn = document.getElementById('checkoutBtn');

    document.getElementById('cartCount').textContent = entries.reduce((s,[,v])=>s+v.qty,0);

    if (!entries.length) {
        container.innerHTML = '';
        container.appendChild(empty);
        empty.style.display = 'block';
        btn.disabled = true; btn.style.opacity = '0.5';
        renderTotals(); return;
    }

    empty.style.display = 'none';
    container.innerHTML = entries.map(([id, item]) => `
        <div class="cart-item">
            <div style="width:36px;height:36px;background:var(--bg3);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0">${item.emoji}</div>
            <div style="flex:1;min-width:0">
                <div style="font-size:0.78rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${item.name}</div>
                <div style="font-size:0.7rem;color:var(--muted2)">Rp ${(item.price*item.qty).toLocaleString('id')}</div>
            </div>
            <div style="display:flex;align-items:center;gap:6px">
                <button class="qty-btn" onclick="changeQty('${id}',-1)">−</button>
                <span style="font-size:0.82rem;font-weight:700;min-width:18px;text-align:center">${item.qty}</span>
                <button class="qty-btn" onclick="changeQty('${id}',1)">+</button>
            </div>
        </div>
    `).join('');

    btn.disabled = false; btn.style.opacity = '1';
    renderTotals();
}

function renderTotals() {
    const subtotal = Object.values(cart).reduce((s,v)=>s+(v.price*v.qty),0);
    const discount = parseFloat(document.getElementById('discountInput').value)||0;
    const total = Math.max(0, subtotal - discount);

    document.getElementById('subtotalVal').textContent = 'Rp '+subtotal.toLocaleString('id');
    document.getElementById('totalVal').textContent = 'Rp '+total.toLocaleString('id');

    document.getElementById('paidInput').min = total;
    renderChange();
}

function renderChange() {
    const total = parseFloat(document.getElementById('totalVal').textContent.replace(/[^\d]/g,''))||0;
    const paid  = parseFloat(document.getElementById('paidInput').value)||0;
    const change = paid - total;
    document.getElementById('changeVal').textContent = 'Rp '+(Math.max(0,change)).toLocaleString('id');
    document.getElementById('changeRow').style.display = 'flex';
}

function setPayment(btn, method) {
    paymentMethod = method;
    document.querySelectorAll('.pay-method').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('cashInput').style.display = method === 'cash' ? 'block' : 'none';
}

// ── Checkout ───────────────────────────────────────────────────
function checkout() {
    const total = parseFloat(document.getElementById('totalVal').textContent.replace(/[^\d]/g,''))||0;
    const paid  = paymentMethod === 'cash' ? parseFloat(document.getElementById('paidInput').value)||0 : total;
    const discount = parseFloat(document.getElementById('discountInput').value)||0;

    if (paymentMethod === 'cash' && paid < total) {
        alert('Uang bayar kurang dari total!'); return;
    }

    const items = Object.entries(cart).map(([id, item]) => ({
        product_id: id, qty: item.qty
    }));

    document.getElementById('checkoutBtn').textContent = 'Memproses...';
    document.getElementById('checkoutBtn').disabled = true;

    fetch('{{ route("pos.checkout") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
        },
        body: JSON.stringify({
            items,
            paid_amount: paid,
            payment_method: paymentMethod,
            discount_amount: discount,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('modalInvoice').textContent = 'Invoice: ' + data.invoice_no;
            document.getElementById('modalChange').textContent = paymentMethod === 'cash'
                ? 'Kembalian: Rp ' + data.change_amount.toLocaleString('id')
                : 'Pembayaran ' + paymentMethod.toUpperCase() + ' berhasil';
            document.getElementById('successModal').style.display = 'flex';
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(() => alert('Terjadi kesalahan, coba lagi.'))
    .finally(() => {
        document.getElementById('checkoutBtn').textContent = 'Proses Pembayaran →';
        document.getElementById('checkoutBtn').disabled = false;
    });
}

function closeModal() {
    document.getElementById('successModal').style.display = 'none';
    cart = {};
    document.getElementById('discountInput').value = 0;
    document.getElementById('paidInput').value = '';
    renderCart();
    // Reload page to refresh stock counts
    location.reload();
}

// ── Filter ─────────────────────────────────────────────────────
function filterProducts(q) {
    document.querySelectorAll('.product-tile').forEach(t => {
        const match = t.dataset.name.toLowerCase().includes(q.toLowerCase());
        t.style.display = match ? '' : 'none';
    });
}

function filterCat(btn, catId) {
    document.querySelectorAll('.cat-btn').forEach(b => {
        b.style.background = 'transparent';
        b.style.color = 'var(--muted2)';
        b.style.borderColor = 'var(--border2)';
    });
    btn.style.background = 'var(--green-bg)';
    btn.style.color = 'var(--green)';
    btn.style.borderColor = 'rgba(0,214,143,0.3)';

    document.querySelectorAll('.product-tile').forEach(t => {
        t.style.display = catId === 'all' || t.dataset.cat === catId ? '' : 'none';
    });
}
</script>
@endpush
