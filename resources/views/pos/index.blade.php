@extends('layouts.app')

@section('page_title', 'Point of Sale')
@section('content')
<div class="row">
    <div class="col-xl-8">
        <div class="card shadow-sm mb-3">
            <div class="card-body pb-2">
                <form class="form-row align-items-end" method="GET">
                    <div class="col-md-5 mb-2">
                        <label class="small text-muted mb-1">Product Search</label>
                        <input id="productSearch" class="form-control form-control-lg" name="search" placeholder="Search name, SKU, or barcode" value="{{ request('search') }}" autocomplete="off">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small text-muted mb-1">Category</label>
                        <select class="form-control form-control-lg" name="category_id">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 d-flex">
                        <button class="btn btn-outline-primary btn-lg btn-block mr-2">Apply</button>
                        <a href="{{ route('pos.index') }}" class="btn btn-light btn-lg border">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Tap To Add Products</strong>
                <span class="badge badge-info" id="visibleProductCount">{{ $products->count() }} items</span>
            </div>
            <div class="card-body">
                <div class="row" id="productGrid">
                    @foreach($products as $product)
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 col-12 mb-3 product-tile" data-search="{{ strtolower($product->product_name.' '.$product->sku.' '.$product->barcode) }}">
                            <button type="button" class="btn btn-light border btn-block text-left touch-product-btn product-btn"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->product_name }}"
                                data-price="{{ $product->selling_price }}"
                                data-imei-required="{{ $product->is_imei_required ? 1 : 0 }}">
                                <div class="font-weight-bold mb-1 text-truncate">{{ $product->product_name }}</div>
                                <div class="small text-muted mb-2">{{ $product->sku ?: 'No SKU' }}</div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-primary font-weight-bold">PHP {{ number_format($product->selling_price, 2) }}</span>
                                    <span class="badge badge-light border">Tap</span>
                                </div>
                            </button>
                        </div>
                    @endforeach
                </div>
                <div id="noProductsState" class="text-center text-muted py-4 d-none">No products matched your search.</div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <form method="POST" action="{{ route('pos.checkout') }}" id="checkoutForm" class="sticky-cart">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $active_branch_id }}">

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Current Cart</strong>
                    <span class="badge badge-primary" id="cartCountBadge">0 item(s)</span>
                </div>
                <div class="card-body">
                    <div id="cartItems" class="mb-2"></div>

                    <div class="border rounded p-2 bg-light">
                        <div class="d-flex justify-content-between mb-1"><span>Subtotal</span><strong id="subtotalText">PHP 0.00</strong></div>
                        <div class="d-flex justify-content-between mb-1"><span>Discount</span><strong id="discountText">PHP 0.00</strong></div>
                        <div class="d-flex justify-content-between"><span class="font-weight-bold">Total</span><strong class="text-success" id="totalText">PHP 0.00</strong></div>
                    </div>

                    <hr>
                    <div class="form-group mb-2">
                        <label class="mb-1">Discount Type</label>
                        <select class="form-control form-control-lg" name="discount[type]" id="discountType">
                            <option value="">No Discount</option>
                            <option value="fixed">Fixed</option>
                            <option value="percentage">Percentage</option>
                            <option value="manual">Manual</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="mb-1">Discount Value</label>
                        <input class="form-control form-control-lg" type="number" step="0.01" name="discount[value]" id="discountValue" value="0">
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-1">Payment Method</label>
                        <select class="form-control form-control-lg" name="payments[0][payment_method_id]" required>
                            @foreach($payment_methods as $method)
                                <option value="{{ $method->id }}">{{ $method->payment_method_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label class="mb-1">Payment Reference</label>
                        <input class="form-control form-control-lg" name="payments[0][payment_reference]" placeholder="Optional reference">
                    </div>
                    <div class="form-group mb-2">
                        <label class="mb-1">Amount Paid</label>
                        <div class="input-group input-group-lg">
                            <input class="form-control" type="text" inputmode="decimal" name="payments[0][amount]" id="amountPaidInput" placeholder="0.00" required readonly>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="amountPaidKeypadBtn">Keypad</button>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap mt-2" id="quickPayButtons">
                            <button type="button" class="btn btn-outline-secondary btn-sm mr-2 mb-2 quick-pay" data-mode="exact">Exact</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm mr-2 mb-2 quick-pay" data-add="20">+20</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm mr-2 mb-2 quick-pay" data-add="50">+50</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm mr-2 mb-2 quick-pay" data-add="100">+100</button>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="mb-1">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-6 mb-2"><button type="button" class="btn btn-warning btn-block btn-lg" id="holdBtn">Hold</button></div>
                        <div class="col-6 mb-2"><button type="button" class="btn btn-secondary btn-block btn-lg" id="clearBtn">Clear</button></div>
                        <div class="col-12"><button class="btn btn-success btn-block btn-lg" id="checkoutBtn">Checkout</button></div>
                    </div>
                </div>
            </div>
        </form>

        <div class="card shadow-sm mt-3">
            <div class="card-header">Held Transactions</div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>No</th><th>Total</th><th></th></tr></thead>
                    <tbody>
                        @forelse($held_transactions as $held)
                            <tr>
                                <td>{{ $held->hold_number }}</td>
                                <td>{{ number_format($held->total_amount, 2) }}</td>
                                <td>
                                    <form method="POST" action="{{ route('sales.held.resume', $held) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-primary">Resume</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No held transactions.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('sales.held.store') }}" id="holdForm">
    @csrf
    <input type="hidden" name="branch_id" value="{{ $active_branch_id }}">
    <div id="holdItems"></div>
</form>

<div id="touchKeypadBackdrop" class="touch-keypad-backdrop d-none">
    <div class="touch-keypad-panel">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong id="touchKeypadTitle">Numeric Keypad</strong>
            <button type="button" class="btn btn-sm btn-light border" id="touchKeypadClose">Close</button>
        </div>
        <input id="touchKeypadDisplay" class="form-control form-control-lg text-right mb-2" readonly>
        <div class="touch-keypad-grid mb-2">
            <button type="button" class="btn btn-light border" data-key="7">7</button>
            <button type="button" class="btn btn-light border" data-key="8">8</button>
            <button type="button" class="btn btn-light border" data-key="9">9</button>
            <button type="button" class="btn btn-light border" data-key="4">4</button>
            <button type="button" class="btn btn-light border" data-key="5">5</button>
            <button type="button" class="btn btn-light border" data-key="6">6</button>
            <button type="button" class="btn btn-light border" data-key="1">1</button>
            <button type="button" class="btn btn-light border" data-key="2">2</button>
            <button type="button" class="btn btn-light border" data-key="3">3</button>
            <button type="button" class="btn btn-light border" data-key="0">0</button>
            <button type="button" class="btn btn-light border" data-key="00">00</button>
            <button type="button" class="btn btn-light border" data-key=".">.</button>
        </div>
        <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-outline-secondary" id="touchKeypadBackspace">Backspace</button>
            <button type="button" class="btn btn-outline-danger" id="touchKeypadClear">Clear</button>
            <button type="button" class="btn btn-success" id="touchKeypadApply">Apply</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.touch-product-btn {
    min-height: 132px;
    border-radius: 12px;
}

.sticky-cart {
    position: sticky;
    top: 1rem;
}

.cart-line {
    border: 1px solid #dee2e6;
    border-radius: 10px;
    padding: 0.65rem;
    margin-bottom: 0.6rem;
}

.cart-line .qty-btn {
    width: 40px;
    height: 40px;
}

.touch-keypad-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
    z-index: 1060;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.touch-keypad-panel {
    width: 100%;
    max-width: 420px;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.3);
    padding: 1rem;
}

.touch-keypad-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
}

.touch-keypad-grid .btn {
    min-height: 56px;
    font-size: 1.1rem;
}

@media (max-width: 991.98px) {
    .sticky-cart {
        position: static;
    }

    .touch-product-btn {
        min-height: 116px;
    }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    const cart = [];
    let computedTotal = 0;
    let keypadContext = null;
    const cartItems = document.getElementById('cartItems');
    const subtotalText = document.getElementById('subtotalText');
    const discountText = document.getElementById('discountText');
    const totalText = document.getElementById('totalText');
    const amountPaidInput = document.getElementById('amountPaidInput');
    const cartCountBadge = document.getElementById('cartCountBadge');
    const keypadBackdrop = document.getElementById('touchKeypadBackdrop');
    const keypadDisplay = document.getElementById('touchKeypadDisplay');
    const keypadTitle = document.getElementById('touchKeypadTitle');

    function formatMoney(value) {
        return 'PHP ' + value.toFixed(2);
    }

    function recalc() {
        let subtotal = 0;
        cart.forEach(item => subtotal += item.qty * item.price);

        const discountType = document.getElementById('discountType').value;
        const discountValue = parseFloat(document.getElementById('discountValue').value || '0');
        let discountAmount = 0;

        if (discountType === 'fixed' || discountType === 'manual') discountAmount = discountValue;
        if (discountType === 'percentage') discountAmount = subtotal * (discountValue / 100);
        if (discountAmount > subtotal) discountAmount = subtotal;

        const total = subtotal - discountAmount;
        computedTotal = total;

        subtotalText.textContent = formatMoney(subtotal);
        discountText.textContent = formatMoney(discountAmount);
        totalText.textContent = formatMoney(total);
        cartCountBadge.textContent = cart.reduce((sum, item) => sum + item.qty, 0) + ' item(s)';

        renderHiddenInputs();
    }

    function render() {
        cartItems.innerHTML = '';

        if (cart.length === 0) {
            cartItems.innerHTML = '<div class="text-muted text-center py-3 border rounded">Tap a product to start a sale.</div>';
            return;
        }

        cart.forEach((item, i) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'cart-line';
            wrapper.innerHTML = `<div class="d-flex justify-content-between align-items-center">
                    <strong class="text-truncate mr-2">${item.name}</strong>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-remove="${i}">Remove</button>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <div class="btn-group" role="group" aria-label="Quantity controls">
                        <button type="button" class="btn btn-outline-secondary qty-btn" data-decrease="${i}">-</button>
                        <input class="form-control text-center" style="max-width:72px;" type="text" inputmode="numeric" value="${item.qty}" data-qty="${i}" readonly>
                        <button type="button" class="btn btn-outline-secondary qty-btn" data-increase="${i}">+</button>
                    </div>
                    <input class="form-control text-right" style="max-width:120px;" type="text" inputmode="decimal" value="${item.price}" data-price="${i}" readonly>
                </div>`;
            cartItems.appendChild(wrapper);
        });

        cartItems.querySelectorAll('[data-remove]').forEach(btn => btn.addEventListener('click', e => {
            cart.splice(parseInt(e.target.dataset.remove, 10), 1);
            render();
            recalc();
        }));
        cartItems.querySelectorAll('[data-qty]').forEach(input => input.addEventListener('input', e => {
            cart[parseInt(e.target.dataset.qty, 10)].qty = Math.max(1, parseInt(e.target.value || '1', 10));
            recalc();
        }));
        cartItems.querySelectorAll('[data-qty]').forEach(input => input.addEventListener('click', e => {
            const index = parseInt(e.target.dataset.qty, 10);
            openKeypad({
                mode: 'qty',
                index,
                value: String(cart[index].qty),
                title: 'Edit Quantity'
            });
        }));
        cartItems.querySelectorAll('[data-increase]').forEach(btn => btn.addEventListener('click', e => {
            const index = parseInt(e.currentTarget.dataset.increase, 10);
            cart[index].qty += 1;
            render();
            recalc();
        }));
        cartItems.querySelectorAll('[data-decrease]').forEach(btn => btn.addEventListener('click', e => {
            const index = parseInt(e.currentTarget.dataset.decrease, 10);
            cart[index].qty = Math.max(1, cart[index].qty - 1);
            render();
            recalc();
        }));
        cartItems.querySelectorAll('[data-price]').forEach(input => input.addEventListener('input', e => {
            cart[parseInt(e.target.dataset.price, 10)].price = Math.max(0, parseFloat(e.target.value || '0'));
            recalc();
        }));
        cartItems.querySelectorAll('[data-price]').forEach(input => input.addEventListener('click', e => {
            const index = parseInt(e.target.dataset.price, 10);
            openKeypad({
                mode: 'price',
                index,
                value: String(cart[index].price),
                title: 'Edit Item Price'
            });
        }));
    }

    function renderHiddenInputs() {
        const checkoutForm = document.getElementById('checkoutForm');
        checkoutForm.querySelectorAll('.generated-item').forEach(el => el.remove());

        cart.forEach((item, i) => {
            ['product_id', 'quantity', 'selling_price'].forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `items[${i}][${key}]`;
                input.value = key === 'product_id' ? item.id : (key === 'quantity' ? item.qty : item.price);
                input.className = 'generated-item';
                checkoutForm.appendChild(input);
            });
        });
    }

    document.querySelectorAll('.product-btn').forEach(btn => btn.addEventListener('click', () => {
        cart.push({ id: parseInt(btn.dataset.id, 10), name: btn.dataset.name, price: parseFloat(btn.dataset.price), qty: 1 });
        render();
        recalc();
    }));

    document.getElementById('productSearch').addEventListener('input', (e) => {
        const keyword = (e.target.value || '').toLowerCase().trim();
        let visible = 0;
        document.querySelectorAll('.product-tile').forEach(tile => {
            const match = keyword === '' || tile.dataset.search.includes(keyword);
            tile.classList.toggle('d-none', !match);
            if (match) visible += 1;
        });
        document.getElementById('visibleProductCount').textContent = visible + ' items';
        document.getElementById('noProductsState').classList.toggle('d-none', visible > 0);
    });

    document.getElementById('discountType').addEventListener('change', recalc);
    document.getElementById('discountValue').addEventListener('input', recalc);

    document.querySelectorAll('.quick-pay').forEach((btn) => {
        btn.addEventListener('click', () => {
            const mode = btn.dataset.mode;
            const add = parseFloat(btn.dataset.add || '0');
            const current = parseFloat(amountPaidInput.value || '0');

            if (mode === 'exact') {
                amountPaidInput.value = computedTotal > 0 ? computedTotal.toFixed(2) : '';
                return;
            }

            const base = current > 0 ? current : computedTotal;
            amountPaidInput.value = (base + add).toFixed(2);
        });
    });

    document.getElementById('amountPaidKeypadBtn').addEventListener('click', () => {
        openKeypad({
            mode: 'amount',
            index: null,
            value: amountPaidInput.value || '',
            title: 'Amount Paid'
        });
    });

    function openKeypad(context) {
        keypadContext = context;
        keypadTitle.textContent = context.title || 'Numeric Keypad';
        keypadDisplay.value = context.value || '';
        keypadBackdrop.classList.remove('d-none');
    }

    function closeKeypad() {
        keypadContext = null;
        keypadDisplay.value = '';
        keypadBackdrop.classList.add('d-none');
    }

    function sanitizeKeypadValue(value, mode) {
        let output = (value || '').replace(/[^0-9.]/g, '');

        if (mode === 'qty') {
            output = output.replace(/\./g, '');
            output = output.replace(/^0+/, '') || '0';
            return output;
        }

        const pieces = output.split('.');
        if (pieces.length > 2) {
            output = pieces[0] + '.' + pieces.slice(1).join('');
        }

        return output;
    }

    keypadBackdrop.querySelectorAll('[data-key]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (!keypadContext) {
                return;
            }

            const mode = keypadContext.mode;
            const key = btn.dataset.key;
            if (mode === 'qty' && key === '.') {
                return;
            }

            keypadDisplay.value = sanitizeKeypadValue((keypadDisplay.value || '') + key, mode);
        });
    });

    document.getElementById('touchKeypadBackspace').addEventListener('click', () => {
        keypadDisplay.value = (keypadDisplay.value || '').slice(0, -1);
    });

    document.getElementById('touchKeypadClear').addEventListener('click', () => {
        keypadDisplay.value = '';
    });

    document.getElementById('touchKeypadClose').addEventListener('click', closeKeypad);
    keypadBackdrop.addEventListener('click', (e) => {
        if (e.target === keypadBackdrop) {
            closeKeypad();
        }
    });

    document.getElementById('touchKeypadApply').addEventListener('click', () => {
        if (!keypadContext) {
            return;
        }

        const mode = keypadContext.mode;
        const rawValue = sanitizeKeypadValue(keypadDisplay.value || '', mode);

        if (mode === 'qty') {
            const parsed = Math.max(1, parseInt(rawValue || '1', 10));
            cart[keypadContext.index].qty = parsed;
            render();
            recalc();
            closeKeypad();
            return;
        }

        if (mode === 'price') {
            const parsed = Math.max(0, parseFloat(rawValue || '0'));
            cart[keypadContext.index].price = parsed;
            render();
            recalc();
            closeKeypad();
            return;
        }

        if (mode === 'amount') {
            if (rawValue === '') {
                amountPaidInput.value = '';
            } else {
                amountPaidInput.value = Math.max(0, parseFloat(rawValue)).toFixed(2);
            }
        }

        closeKeypad();
    });

    document.getElementById('clearBtn').addEventListener('click', () => {
        cart.length = 0;
        render();
        recalc();
    });

    document.getElementById('holdBtn').addEventListener('click', () => {
        const holdForm = document.getElementById('holdForm');
        holdForm.querySelectorAll('.generated-item').forEach(el => el.remove());
        cart.forEach((item, i) => {
            const fields = {
                product_id: item.id,
                quantity: item.qty,
                selling_price: item.price,
            };
            Object.keys(fields).forEach((key) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `items[${i}][${key}]`;
                input.value = fields[key];
                input.className = 'generated-item';
                holdForm.appendChild(input);
            });
        });
        holdForm.submit();
    });

    document.getElementById('checkoutForm').addEventListener('submit', (e) => {
        if (cart.length === 0) {
            e.preventDefault();
            alert('Add at least one product before checkout.');
            return;
        }

        const paid = parseFloat(amountPaidInput.value || '0');
        if (paid <= 0) {
            e.preventDefault();
            alert('Enter amount paid before checkout.');
        }
    });

    render();
    recalc();
})();
</script>
@endpush
