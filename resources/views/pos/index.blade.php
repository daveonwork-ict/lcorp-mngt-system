@extends('layouts.app')

@section('page_title', 'Point of Sale')
@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form class="form-row mb-3 align-items-end" method="GET">
                    <div class="col-md-4 col-sm-6 mb-2">
                        <input class="form-control form-control-lg" name="search" placeholder="Search / barcode" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4 col-sm-6 mb-2">
                        <select class="form-control form-control-lg" name="category_id">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-2 text-right">
                        <button class="btn btn-outline-primary btn-lg touch-btn">Apply Filter</button>
                    </div>
                </form>

                <div class="row">
                    @foreach($products as $product)
                        <div class="col-xl-4 col-md-6 col-sm-6 col-12 mb-3">
                            <button type="button" class="btn btn-light border btn-block p-3 text-left touch-btn product-btn"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->product_name }}"
                                data-price="{{ $product->selling_price }}"
                                data-imei-required="{{ $product->is_imei_required ? 1 : 0 }}">
                                <strong>{{ $product->product_name }}</strong><br>
                                <small>{{ $product->sku }}</small><br>
                                <span class="text-primary">PHP {{ number_format($product->selling_price, 2) }}</span>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <form method="POST" action="{{ route('pos.checkout') }}" id="checkoutForm">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $active_branch_id }}">

            <div class="card">
                <div class="card-header"><strong>Cart</strong></div>
                <div class="card-body">
                    <div id="cartItems"></div>
                    <p class="mb-1">Subtotal: <strong id="subtotalText">PHP 0.00</strong></p>
                    <p class="mb-1">Discount: <strong id="discountText">PHP 0.00</strong></p>
                    <p class="mb-1">Total: <strong id="totalText">PHP 0.00</strong></p>

                    <hr>
                    <div class="form-group">
                        <label>Discount Type</label>
                        <select class="form-control" name="discount[type]" id="discountType">
                            <option value="">No Discount</option>
                            <option value="fixed">Fixed</option>
                            <option value="percentage">Percentage</option>
                            <option value="manual">Manual</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Discount Value</label>
                        <input class="form-control" type="number" step="0.01" name="discount[value]" id="discountValue" value="0">
                    </div>

                    <div class="form-group">
                        <label>Payment Method</label>
                        <select class="form-control" name="payments[0][payment_method_id]" required>
                            @foreach($payment_methods as $method)
                                <option value="{{ $method->id }}">{{ $method->payment_method_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Payment Reference</label>
                        <input class="form-control" name="payments[0][payment_reference]" placeholder="Optional reference">
                    </div>
                    <div class="form-group">
                        <label>Amount Paid</label>
                        <input class="form-control" type="number" step="0.01" min="0.01" name="payments[0][amount]" required>
                    </div>
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea class="form-control" name="remarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-6 mb-2"><button type="button" class="btn btn-warning btn-block touch-btn" id="holdBtn">Hold Transaction</button></div>
                        <div class="col-6 mb-2"><button type="button" class="btn btn-secondary btn-block touch-btn" id="clearBtn">Clear Cart</button></div>
                        <div class="col-12"><button class="btn btn-success btn-block btn-lg touch-btn">Checkout and Confirm Payment</button></div>
                    </div>
                </div>
            </div>
        </form>

        <div class="card">
            <div class="card-header">Held Transactions</div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>No</th><th>Total</th><th></th></tr></thead>
                    <tbody>
                        @foreach($held_transactions as $held)
                            <tr>
                                <td>{{ $held->hold_number }}</td>
                                <td>{{ number_format($held->total_amount, 2) }}</td>
                                <td>
                                    <form method="POST" action="{{ route('sales.held.resume', $held) }}">@csrf<button class="btn btn-xs btn-primary">Resume</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('sales.held.store') }}" id="holdForm">@csrf<input type="hidden" name="branch_id" value="{{ $active_branch_id }}"><div id="holdItems"></div></form>
@endsection

@push('scripts')
<script>
(function () {
    const cart = [];
    const cartItems = document.getElementById('cartItems');
    const subtotalText = document.getElementById('subtotalText');
    const discountText = document.getElementById('discountText');
    const totalText = document.getElementById('totalText');

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

        subtotalText.textContent = 'PHP ' + subtotal.toFixed(2);
        discountText.textContent = 'PHP ' + discountAmount.toFixed(2);
        totalText.textContent = 'PHP ' + total.toFixed(2);

        renderHiddenInputs();
    }

    function render() {
        cartItems.innerHTML = '';
        cart.forEach((item, i) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'border rounded p-2 mb-2';
            wrapper.innerHTML = `<div class="d-flex justify-content-between"><strong>${item.name}</strong><button type="button" class="btn btn-xs btn-danger" data-remove="${i}">x</button></div>
                <div class="form-row mt-2">
                    <div class="col-4"><input class="form-control form-control-sm" type="number" min="1" value="${item.qty}" data-qty="${i}"></div>
                    <div class="col-8"><input class="form-control form-control-sm" type="number" step="0.01" min="0" value="${item.price}" data-price="${i}"></div>
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
        cartItems.querySelectorAll('[data-price]').forEach(input => input.addEventListener('input', e => {
            cart[parseInt(e.target.dataset.price, 10)].price = Math.max(0, parseFloat(e.target.value || '0'));
            recalc();
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

    document.getElementById('discountType').addEventListener('change', recalc);
    document.getElementById('discountValue').addEventListener('input', recalc);

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
})();
</script>
@endpush
