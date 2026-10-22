@extends('layouts.shop')

@section('title', 'POS Counter')
@section('page-title', 'Point of Sale (POS) Counter')

@section('content')
<style>
    .pos-layout {
        display: grid;
        grid-template-columns: 1.3fr 1fr;
        gap: 1.5rem;
        height: calc(100vh - 140px);
    }

    .pos-catalog {
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border: 1px solid var(--surface-border);
        border-radius: 12px;
        overflow: hidden;
    }

    .pos-catalog-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--surface-border);
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .pos-tabs {
        display: flex;
        border-bottom: 1px solid var(--surface-border);
        background: #f8fafc;
    }

    .pos-tab {
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        color: var(--text-muted);
    }

    .pos-tab.active {
        color: var(--brand-indigo-600);
        border-bottom-color: var(--brand-indigo-600);
        background: #ffffff;
    }

    .pos-items-grid {
        padding: 1rem;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        gap: 0.75rem;
        overflow-y: auto;
        flex: 1;
    }

    .pos-item-card {
        border: 1px solid var(--surface-border);
        border-radius: 8px;
        padding: 0.875rem;
        cursor: pointer;
        background: #ffffff;
        transition: all 0.15s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .pos-item-card:hover {
        border-color: var(--brand-indigo-600);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
    }

    .pos-cart {
        background: #ffffff;
        border: 1px solid var(--surface-border);
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .pos-cart-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--surface-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
    }

    .pos-cart-items {
        flex: 1;
        overflow-y: auto;
        padding: 0.75rem 1.25rem;
    }

    .pos-cart-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.65rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .pos-cart-summary {
        padding: 1.25rem;
        border-top: 1px solid var(--surface-border);
        background: #f8fafc;
    }

    .split-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    @media (max-width: 1024px) {
        .pos-layout {
            grid-template-columns: 1fr;
            height: auto;
        }
    }
</style>

<div class="pos-layout" id="posApp">
    <!-- Left: Catalog & Search -->
    <div class="pos-catalog">
        <div class="pos-catalog-header">
            <input type="text" id="searchInput" class="form-control" placeholder="Scan Barcode or Search by Name, IMEI, Brand..." onkeyup="filterItems()">
            <select id="categoryFilter" class="form-control" style="max-width: 180px;" onchange="filterItems()">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->name }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="pos-tabs">
            <div class="pos-tab active" onclick="switchTab('phones', this)">Mobile Phones (IMEI)</div>
            <div class="pos-tab" onclick="switchTab('products', this)">Accessories & Items</div>
        </div>

        <!-- Phone Items Tab -->
        <div id="tabPhones" class="pos-items-grid">
            @forelse($phones as $phone)
                <div class="pos-item-card item-card" 
                     data-type="phone" 
                     data-id="{{ $phone->id }}" 
                     data-name="{{ $phone->brand }} {{ $phone->model }}" 
                     data-price="{{ $phone->selling_price }}" 
                     data-imei="{{ $phone->imei_1 }}"
                     data-category="Phones"
                     onclick="addPhoneToCart({{ $phone->id }}, '{{ addslashes($phone->brand.' '.$phone->model) }}', {{ $phone->selling_price }}, '{{ $phone->imei_1 }}')">
                    <div>
                        <div style="font-size: 0.75rem; color: #0284c7; font-weight: 700; text-transform: uppercase;">{{ $phone->brand }}</div>
                        <div style="font-weight: 700; font-size: 0.875rem; margin-top: 0.2rem;">{{ $phone->model }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem;">
                            {{ $phone->color }} • {{ $phone->storage }}
                        </div>
                        <div style="font-family: monospace; font-size: 0.7rem; color: #64748b; margin-top: 0.35rem; background: #f1f5f9; padding: 2px 4px; border-radius: 4px;">
                            IMEI: {{ $phone->imei_1 }}
                        </div>
                    </div>
                    <div style="margin-top: 0.75rem; font-weight: 800; color: #166534; font-size: 0.9375rem;">
                        LKR {{ number_format($phone->selling_price, 2) }}
                    </div>
                </div>
            @empty
                <div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 3rem;">
                    No phones currently in stock. Register phones under Phone Inventory.
                </div>
            @endforelse
        </div>

        <!-- Products / Accessories Tab -->
        <div id="tabProducts" class="pos-items-grid" style="display: none;">
            @forelse($products as $product)
                <div class="pos-item-card item-card" 
                     data-type="product" 
                     data-id="{{ $product->id }}" 
                     data-name="{{ $product->name }}" 
                     data-price="{{ $product->selling_price }}" 
                     data-barcode="{{ $product->barcode }}"
                     data-category="{{ $product->category->name ?? 'General' }}"
                     onclick="addProductToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->selling_price }}, {{ $product->stock_quantity }})">
                    <div>
                        <div style="font-size: 0.75rem; color: #4f46e5; font-weight: 700; text-transform: uppercase;">{{ $product->category->name ?? 'Accessory' }}</div>
                        <div style="font-weight: 700; font-size: 0.875rem; margin-top: 0.2rem;">{{ $product->name }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem;">
                            Stock: {{ $product->stock_quantity }} units
                        </div>
                    </div>
                    <div style="margin-top: 0.75rem; font-weight: 800; color: #166534; font-size: 0.9375rem;">
                        LKR {{ number_format($product->selling_price, 2) }}
                    </div>
                </div>
            @empty
                <div style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 3rem;">
                    No products found in stock.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Right: Cart & Split Tender Checkout -->
    <div class="pos-cart">
        <div class="pos-cart-header">
            <span style="font-weight: 700;">Active Register Cart</span>
            <button class="btn btn-sm btn-secondary" onclick="clearCart()">Clear Cart</button>
        </div>

        <!-- Customer selection -->
        <div style="padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--surface-border); background: #fdfdfd;">
            <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Customer (Optional for Credit)</label>
            <select id="customerSelect" class="form-control">
                <option value="">Walk-in Customer (Cash)</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                @endforeach
            </select>
        </div>

        <!-- Cart Items List -->
        <div class="pos-cart-items" id="cartContainer">
            <div id="emptyCartNotice" style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                Scan a barcode or click items on the left to add them to this sale.
            </div>
            <div id="cartItemsList"></div>
        </div>

        <!-- Checkout Form & Split Payment -->
        <div class="pos-cart-summary">
            <div style="display: flex; justify-content: space-between; font-size: 0.875rem; margin-bottom: 0.35rem;">
                <span style="color: var(--text-muted);">Subtotal</span>
                <span id="lblSubtotal" style="font-weight: 600;">LKR 0.00</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem; margin-bottom: 0.5rem;">
                <span style="color: var(--text-muted);">Discount (LKR)</span>
                <input type="number" id="inpDiscount" class="form-control" style="width: 100px; text-align: right; padding: 0.25rem 0.5rem;" value="0" min="0" oninput="recalc()">
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 800; color: var(--text-heading); border-top: 1px solid #e2e8f0; padding-top: 0.5rem; margin-bottom: 1rem;">
                <span>Total Payable</span>
                <span id="lblGrandTotal" style="color: #4f46e5;">LKR 0.00</span>
            </div>

            <!-- Split Payments Entry -->
            <label style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); display: block;">Payment Tender (Split Allowed)</label>
            <div class="split-row">
                <div>
                    <label style="font-size: 0.7rem; color: #166534; font-weight: 600;">Cash</label>
                    <input type="number" id="payCash" class="form-control" placeholder="0.00" oninput="updatePaymentRemaining()">
                </div>
                <div>
                    <label style="font-size: 0.7rem; color: #1e40af; font-weight: 600;">Card Terminal</label>
                    <input type="number" id="payCard" class="form-control" placeholder="0.00" oninput="updatePaymentRemaining()">
                </div>
                <div>
                    <label style="font-size: 0.7rem; color: #6b21a8; font-weight: 600;">Bank / QR</label>
                    <input type="number" id="payBank" class="form-control" placeholder="0.00" oninput="updatePaymentRemaining()">
                </div>
            </div>

            <div id="paymentStatusNotice" style="font-size: 0.75rem; font-weight: 600; margin-top: 0.5rem; color: #b45309;">
                Remaining to settle: LKR 0.00
            </div>

            <button type="button" id="btnSubmitSale" class="btn btn-primary" style="width: 100%; margin-top: 0.75rem; padding: 0.75rem;" onclick="processCheckout()">
                Complete Sale & Generate Invoice
            </button>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal-backdrop" id="receiptModal">
    <div class="modal-box" style="max-width: 420px; font-family: monospace;">
        <div class="modal-header">
            <h3>Invoice Receipt</h3>
            <button class="modal-close" onclick="closeModal('receiptModal')">&times;</button>
        </div>
        <div class="modal-body" id="receiptContent" style="font-size: 0.8125rem;">
            <!-- Dynamic Receipt populated by JS -->
        </div>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--surface-border); display: flex; gap: 0.5rem;">
            <button class="btn btn-primary" style="flex: 1;" onclick="window.print()">Print Receipt</button>
            <button class="btn btn-secondary" onclick="closeModal('receiptModal')">Done</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let cart = [];

    function switchTab(tab, el) {
        document.querySelectorAll('.pos-tab').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
        if (tab === 'phones') {
            document.getElementById('tabPhones').style.display = 'grid';
            document.getElementById('tabProducts').style.display = 'none';
        } else {
            document.getElementById('tabPhones').style.display = 'none';
            document.getElementById('tabProducts').style.display = 'grid';
        }
    }

    function filterItems() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        const cat = document.getElementById('categoryFilter').value.toLowerCase();

        document.querySelectorAll('.item-card').forEach(card => {
            const name = card.dataset.name.toLowerCase();
            const imei = (card.dataset.imei || '').toLowerCase();
            const barcode = (card.dataset.barcode || '').toLowerCase();
            const category = (card.dataset.category || '').toLowerCase();

            const matchesQuery = name.includes(query) || imei.includes(query) || barcode.includes(query);
            const matchesCat = !cat || category.includes(cat);

            card.style.display = (matchesQuery && matchesCat) ? 'flex' : 'none';
        });
    }

    function addPhoneToCart(id, name, price, imei) {
        if (cart.some(item => item.phone_device_id === id)) {
            alert('This specific serialized phone (IMEI ' + imei + ') is already in your cart.');
            return;
        }
        cart.push({
            type: 'phone',
            phone_device_id: id,
            product_id: null,
            name: name,
            imei: imei,
            quantity: 1,
            unit_price: parseFloat(price)
        });
        renderCart();
    }

    function addProductToCart(id, name, price, maxStock) {
        const existing = cart.find(item => item.product_id === id);
        if (existing) {
            if (existing.quantity >= maxStock) {
                alert('Maximum available stock reached for this product (' + maxStock + ').');
                return;
            }
            existing.quantity++;
        } else {
            cart.push({
                type: 'product',
                product_id: id,
                phone_device_id: null,
                name: name,
                imei: null,
                quantity: 1,
                unit_price: parseFloat(price)
            });
        }
        renderCart();
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function clearCart() {
        cart = [];
        renderCart();
    }

    function renderCart() {
        const list = document.getElementById('cartItemsList');
        const emptyNotice = document.getElementById('emptyCartNotice');

        if (cart.length === 0) {
            list.innerHTML = '';
            emptyNotice.style.display = 'block';
        } else {
            emptyNotice.style.display = 'none';
            list.innerHTML = cart.map((item, idx) => `
                <div class="pos-cart-row">
                    <div style="flex: 1; padding-right: 0.5rem;">
                        <div style="font-weight: 600; font-size: 0.8125rem;">${item.name}</div>
                        ${item.imei ? `<div style="font-size: 0.7rem; color: #0284c7; font-family: monospace;">IMEI: ${item.imei}</div>` : ''}
                        <div style="font-size: 0.75rem; color: var(--text-muted);">${item.quantity} x LKR ${item.unit_price.toFixed(2)}</div>
                    </div>
                    <div style="font-weight: 700; font-size: 0.875rem; margin-right: 0.75rem;">
                        LKR ${(item.quantity * item.unit_price).toFixed(2)}
                    </div>
                    <button type="button" onclick="removeFromCart(${idx})" style="background:none; border:none; color: #ef4444; font-size: 1.1rem; cursor:pointer;">&times;</button>
                </div>
            `).join('');
        }
        recalc();
    }

    function recalc() {
        const subtotal = cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
        const discount = parseFloat(document.getElementById('inpDiscount').value || 0);
        const total = Math.max(0, subtotal - discount);

        document.getElementById('lblSubtotal').innerText = 'LKR ' + subtotal.toFixed(2);
        document.getElementById('lblGrandTotal').innerText = 'LKR ' + total.toFixed(2);

        // Default cash payment to grand total if not manually split
        const cashInput = document.getElementById('payCash');
        const cardInput = document.getElementById('payCard');
        const bankInput = document.getElementById('payBank');

        if (!cardInput.value && !bankInput.value) {
            cashInput.value = total > 0 ? total.toFixed(2) : '';
        }

        updatePaymentRemaining();
    }

    function updatePaymentRemaining() {
        const subtotal = cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
        const discount = parseFloat(document.getElementById('inpDiscount').value || 0);
        const total = Math.max(0, subtotal - discount);

        const cash = parseFloat(document.getElementById('payCash').value || 0);
        const card = parseFloat(document.getElementById('payCard').value || 0);
        const bank = parseFloat(document.getElementById('payBank').value || 0);
        const tendered = cash + card + bank;
        const diff = total - tendered;

        const notice = document.getElementById('paymentStatusNotice');
        if (Math.abs(diff) < 0.01) {
            notice.innerText = '✓ Exact payment tendered (LKR ' + tendered.toFixed(2) + ')';
            notice.style.color = '#166534';
        } else if (diff < 0) {
            notice.innerText = 'Change due to customer: LKR ' + Math.abs(diff).toFixed(2);
            notice.style.color = '#0284c7';
        } else {
            notice.innerText = 'Remaining to settle: LKR ' + diff.toFixed(2);
            notice.style.color = '#b45309';
        }
    }

    async function processCheckout() {
        if (cart.length === 0) {
            alert('Your cart is empty. Add items before checking out.');
            return;
        }

        const subtotal = cart.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
        const discount = parseFloat(document.getElementById('inpDiscount').value || 0);
        const total = Math.max(0, subtotal - discount);

        const cash = parseFloat(document.getElementById('payCash').value || 0);
        const card = parseFloat(document.getElementById('payCard').value || 0);
        const bank = parseFloat(document.getElementById('payBank').value || 0);

        const payments = [];
        if (cash > 0) payments.push({ payment_method: 'cash', amount: cash });
        if (card > 0) payments.push({ payment_method: 'card', amount: card });
        if (bank > 0) payments.push({ payment_method: 'bank_transfer', amount: bank });

        if (payments.length === 0) {
            alert('Please enter a payment amount in Cash, Card, or Bank Transfer.');
            return;
        }

        const btn = document.getElementById('btnSubmitSale');
        btn.disabled = true;
        btn.innerText = 'Processing Transaction...';

        const payload = {
            customer_id: document.getElementById('customerSelect').value || null,
            discount_amount: discount,
            items: cart.map(i => ({
                product_id: i.product_id,
                phone_device_id: i.phone_device_id,
                quantity: i.quantity,
                unit_price: i.unit_price
            })),
            payments: payments
        };

        try {
            const res = await fetch("{{ route('shop.pos.checkout') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || 'Error processing transaction.');
            }

            // Show Receipt Modal
            showReceipt(data.sale);
            clearCart();
        } catch (err) {
            alert(err.message);
        } finally {
            btn.disabled = false;
            btn.innerText = 'Complete Sale & Generate Invoice';
        }
    }

    function showReceipt(sale) {
        const rc = document.getElementById('receiptContent');
        rc.innerHTML = `
            <div style="text-align: center; margin-bottom: 1rem; border-bottom: 1px dashed #cbd5e1; padding-bottom: 0.75rem;">
                <h4 style="margin: 0; font-size: 1.1rem; font-weight: 700;">{{ auth()->user()->shop->name ?? 'Phone Shop' }}</h4>
                <div style="font-size: 0.75rem; color: #64748b;">Official POS Tax Invoice</div>
                <div style="font-size: 0.75rem; margin-top: 0.25rem;">Invoice: <strong>${sale.invoice_number}</strong></div>
                <div style="font-size: 0.75rem;">Date: ${sale.sale_date}</div>
            </div>
            <div style="margin-bottom: 1rem;">
                ${sale.items.map(item => `
                    <div style="display:flex; justify-content:space-between; margin-bottom: 0.25rem;">
                        <span>${item.product_name} x${item.quantity}</span>
                        <span>LKR ${parseFloat(item.subtotal).toFixed(2)}</span>
                    </div>
                    ${item.imei ? `<div style="font-size: 0.7rem; color: #64748b;">IMEI: ${item.imei}</div>` : ''}
                `).join('')}
            </div>
            <div style="border-top: 1px dashed #cbd5e1; padding-top: 0.5rem; margin-bottom: 0.75rem;">
                <div style="display:flex; justify-content:space-between; font-weight:700;">
                    <span>Grand Total:</span>
                    <span>LKR ${parseFloat(sale.total_amount).toFixed(2)}</span>
                </div>
            </div>
            <div style="font-size: 0.75rem; color: #64748b; text-align: center; margin-top: 1rem;">
                Thank you for your purchase!<br>
                Items covered under official warranty terms where applicable.
            </div>
        `;
        openModal('receiptModal');
    }
</script>
@endpush
@endsection

