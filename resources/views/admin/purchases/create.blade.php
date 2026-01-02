@extends('layouts.app')
@section('title', 'Add Purchase')
@section('content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h2 class="fw-bold">Add Purchase</h2>
            </div>
        </div>
        <ul class="table-top-head">
            <li><a href="{{ route('all_purchases') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Back"><i class="ti ti-arrow-left"></i></a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
                @csrf
                
                <!-- Invoice Header -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <h5 class="mb-0 me-3">INVOICE NO.</h5>
                            <span class="text-primary fw-bold fs-16" id="invoice-number">-</span>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="d-flex align-items-center justify-content-end mb-3">
                            <h5 class="mb-0 me-3">DATE</h5>
                            <input type="text" name="purchase_date" id="purchase_date" class="form-control" style="width: 150px;" value="{{ date('d/m/Y') }}" required>
                        </div>
                    </div>
                </div>

                <!-- Supplier Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">FIRM:</label>
                        <select name="supplier_id" id="supplier_id" class="form-control" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" data-company="{{ $supplier->company ?? '' }}" data-phone="{{ $supplier->phones[0] ?? '' }}">
                                    {{ $supplier->names[0] ?? 'N/A' }} @if($supplier->company) - {{ $supplier->company }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">REFERENCE:</label>
                        <input type="text" name="reference" id="reference" class="form-control" placeholder="Enter reference number">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">PARTY NAME</label>
                        <input type="text" id="party_name" class="form-control" placeholder="Search Name" readonly>
                        <small class="text-muted">Select supplier from dropdown above</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">PARTY NUMBER</label>
                        <input type="text" id="party_number" class="form-control" placeholder="03xxxxxxxxx" readonly>
                        <small class="text-muted" style="font-size: 11px;">Double tap to edit</small>
                    </div>
                </div>

                <!-- Items List Section -->
                <div class="mb-4">
                    <h5 class="fw-bold mb-3">ITEMS LIST</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="items-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                    <th>Rate</th>
                                    <th>Discount</th>
                                    <th>Tax %</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="items-tbody">
                                <tr class="empty-row">
                                    <td colspan="8" class="text-center text-muted py-4">No items added. Click "Add Purchase Item" to add items.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#add-item-modal">
                        <i class="ti ti-plus me-2"></i>ADD PURCHASE ITEM
                    </button>
                </div>

                <!-- Totals Section -->
                <div class="row mb-4">
                    <div class="col-md-6 offset-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">PURCHASE ITEM TOTAL</td>
                                <td class="text-end" id="item-total">0.00</td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="form-label mb-0">Order Tax:</label>
                                    <input type="number" name="order_tax" id="order_tax" class="form-control form-control-sm" value="0" step="0.01" min="0">
                                </td>
                                <td class="text-end" id="tax-display">0.00</td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="form-label mb-0">Discount:</label>
                                    <input type="number" name="discount" id="discount" class="form-control form-control-sm" value="0" step="0.01" min="0">
                                </td>
                                <td class="text-end" id="discount-display">0.00</td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="form-label mb-0">Shipping:</label>
                                    <input type="number" name="shipping" id="shipping" class="form-control form-control-sm" value="0" step="0.01" min="0">
                                </td>
                                <td class="text-end" id="shipping-display">0.00</td>
                            </tr>
                            <tr class="border-top">
                                <td class="fw-bold fs-16">GROSS TOTAL</td>
                                <td class="text-end fw-bold fs-16" id="gross-total">0.00</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="bg-primary text-white p-3 rounded">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold fs-18">NET PAYABLE</span>
                                            <span class="fw-bold fs-20" id="net-payable">Rs 0.00</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Status and Description -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="ordered">Ordered</option>
                            <option value="received">Received</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3" placeholder="Optional description"></textarea>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('all_purchases') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="add-item-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-shopping-cart me-2"></i>PURCHASE ITEM ENTRY
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Stock Display -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-success bg-opacity-10 border-success">
                            <div class="card-body text-center">
                                <h6 class="text-success fw-bold mb-1">WAREHOUSE</h6>
                                <h4 class="mb-0" id="warehouse-stock">0 Units</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-warning bg-opacity-10 border-warning">
                            <div class="card-body text-center">
                                <h6 class="text-warning fw-bold mb-1">SHOP</h6>
                                <h4 class="mb-0" id="shop-stock">0 Units</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search Item -->
                <div class="mb-3">
                    <label class="form-label fw-bold">SEARCH ITEM</label>
                    <div class="input-group">
                        <input type="text" id="item-search" class="form-control" placeholder="Start typing...">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                    </div>
                    <div id="search-results" class="mt-2" style="max-height: 200px; overflow-y: auto; display: none;">
                        <ul class="list-group" id="search-results-list"></ul>
                    </div>
                </div>

                <!-- Selected Item Display -->
                <div id="selected-item-display" class="mb-3" style="display: none;">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 id="selected-item-name"></h6>
                            <small class="text-muted">Selected Item</small>
                        </div>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="mb-3">
                    <label class="form-label fw-bold">QUANTITY</label>
                    <div class="input-group" style="max-width: 200px;">
                        <button type="button" class="btn btn-outline-secondary" id="decrease-qty">-</button>
                        <input type="number" id="item-quantity" class="form-control text-center" value="1" min="0.01" step="0.01">
                        <button type="button" class="btn btn-outline-secondary" id="increase-qty">+</button>
                    </div>
                </div>

                <!-- Unit -->
                <div class="mb-3">
                    <label class="form-label fw-bold">UNIT</label>
                    <select id="item-unit" class="form-control">
                        <option value="Unit">Unit</option>
                        <option value="Can">Can</option>
                        <option value="Box">Box</option>
                        <option value="Piece">Piece</option>
                        <option value="Kg">Kg</option>
                        <option value="Liter">Liter</option>
                    </select>
                </div>

                <!-- Rate -->
                <div class="mb-3">
                    <label class="form-label fw-bold">RATE</label>
                    <div class="input-group">
                        <span class="input-group-text">Rs</span>
                        <input type="number" id="item-rate" class="form-control" value="0.00" step="0.01" min="0">
                        <button type="button" class="btn btn-outline-primary" id="suggest-rate">
                            <i class="ti ti-sparkles me-1"></i>SUGGEST RATE
                        </button>
                    </div>
                </div>

                <!-- Discount -->
                <div class="mb-3">
                    <label class="form-label fw-bold">DISCOUNT</label>
                    <div class="input-group">
                        <input type="number" id="item-discount" class="form-control" value="0" step="0.01" min="0">
                        <select id="discount-type" class="form-control" style="max-width: 100px;">
                            <option value="amount">Rs</option>
                            <option value="percent">%</option>
                        </select>
                    </div>
                </div>

                <!-- Tax -->
                <div class="mb-3">
                    <label class="form-label fw-bold">TAX %</label>
                    <input type="number" id="item-tax" class="form-control" value="0" step="0.01" min="0" max="100">
                </div>

                <input type="hidden" id="selected-item-id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-entry">Confirm Entry</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let purchaseItems = [];
    let itemCounter = 0;

    // Generate invoice number on page load
    generateInvoiceNumber();

    // Supplier change handler
    $('#supplier_id').on('change', function() {
        const selected = $(this).find('option:selected');
        const company = selected.data('company') || '';
        const phone = selected.data('phone') || '';
        
        $('#party_name').val(selected.text().split(' - ')[0]);
        $('#party_number').val(phone);
    });

    // Item search
    let searchTimeout;
    $('#item-search').on('input', function() {
        const search = $(this).val();
        
        clearTimeout(searchTimeout);
        if (search.length < 2) {
            $('#search-results').hide();
            return;
        }

        searchTimeout = setTimeout(function() {
            $.ajax({
                url: '{{ route("purchases.items.search") }}',
                method: 'GET',
                data: { search: search },
                success: function(response) {
                    displaySearchResults(response);
                }
            });
        }, 300);
    });

    function displaySearchResults(items) {
        const resultsList = $('#search-results-list');
        resultsList.empty();

        if (items.length === 0) {
            resultsList.append('<li class="list-group-item">No items found</li>');
        } else {
            items.forEach(function(item) {
                const name = item.short_disc || item.pro_dis || item.bar_code || 'N/A';
                resultsList.append(
                    '<li class="list-group-item list-group-item-action" style="cursor: pointer;" data-id="' + item.id + '">' +
                    '<strong>' + name + '</strong><br>' +
                    '<small class="text-muted">Stock: ' + (item.on_hand || 0) + ' | Rate: Rs ' + (item.packing_purchase_rate || 0).toFixed(2) + '</small>' +
                    '</li>'
                );
            });
        }

        $('#search-results').show();
    }

    // Select item from search results
    $(document).on('click', '#search-results-list li', function() {
        const itemId = $(this).data('id');
        loadItemDetails(itemId);
    });

    // Load item details
    function loadItemDetails(itemId) {
        $.ajax({
            url: '{{ route("purchases.items.details", ":id") }}'.replace(':id', itemId),
            method: 'GET',
            success: function(response) {
                $('#selected-item-id').val(response.id);
                $('#selected-item-name').text(response.name);
                $('#item-rate').val(parseFloat(response.rate).toFixed(2));
                $('#item-unit').val(response.unit || 'Unit');
                $('#warehouse-stock').text(response.warehouse_stock + ' Units');
                $('#shop-stock').text(response.shop_stock + ' Units');
                $('#selected-item-display').show();
                $('#search-results').hide();
                $('#item-search').val('');
            }
        });
    }

    // Suggest rate button
    $('#suggest-rate').on('click', function() {
        const itemId = $('#selected-item-id').val();
        if (itemId) {
            loadItemDetails(itemId);
        }
    });

    // Quantity controls
    $('#increase-qty').on('click', function() {
        const current = parseFloat($('#item-quantity').val()) || 0;
        $('#item-quantity').val((current + 1).toFixed(2));
    });

    $('#decrease-qty').on('click', function() {
        const current = parseFloat($('#item-quantity').val()) || 0;
        if (current > 0.01) {
            $('#item-quantity').val((current - 1).toFixed(2));
        }
    });

    // Confirm entry
    $('#confirm-entry').on('click', function() {
        const itemId = $('#selected-item-id').val();
        const quantity = parseFloat($('#item-quantity').val()) || 0;
        const unit = $('#item-unit').val();
        const rate = parseFloat($('#item-rate').val()) || 0;
        const discount = parseFloat($('#item-discount').val()) || 0;
        const discountType = $('#discount-type').val();
        const taxPercentage = parseFloat($('#item-tax').val()) || 0;
        const itemName = $('#selected-item-name').text();

        if (!itemId || quantity <= 0 || rate <= 0) {
            alert('Please select an item and enter valid quantity and rate');
            return;
        }

        // Calculate discount amount
        let discountAmount = discount;
        if (discountType === 'percent') {
            discountAmount = (quantity * rate * discount) / 100;
        }

        // Calculate totals
        const subtotal = (quantity * rate) - discountAmount;
        const taxAmount = (subtotal * taxPercentage) / 100;
        const total = subtotal + taxAmount;

        // Add to items array
        const item = {
            id: itemCounter++,
            item_id: itemId,
            name: itemName,
            quantity: quantity,
            unit: unit,
            rate: rate,
            discount: discountAmount,
            tax_percentage: taxPercentage,
            tax_amount: taxAmount,
            total: total
        };

        purchaseItems.push(item);
        addItemToTable(item);
        resetItemModal();
        $('#add-item-modal').modal('hide');
        calculateTotals();
    });

    function addItemToTable(item) {
        $('.empty-row').remove();
        
        const row = `
            <tr data-item-id="${item.item_id}" data-row-id="${item.id}">
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>${item.unit}</td>
                <td>Rs ${parseFloat(item.rate).toFixed(2)}</td>
                <td>Rs ${parseFloat(item.discount).toFixed(2)}</td>
                <td>${parseFloat(item.tax_percentage).toFixed(2)}%</td>
                <td>Rs ${parseFloat(item.total).toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-item" data-row-id="${item.id}">
                        <i class="ti ti-x"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#items-tbody').append(row);
    }

    // Remove item
    $(document).on('click', '.remove-item', function() {
        const rowId = $(this).data('row-id');
        purchaseItems = purchaseItems.filter(item => item.id !== rowId);
        $(this).closest('tr').remove();
        
        if ($('#items-tbody tr').length === 0) {
            $('#items-tbody').html('<tr class="empty-row"><td colspan="8" class="text-center text-muted py-4">No items added. Click "Add Purchase Item" to add items.</td></tr>');
        }
        
        calculateTotals();
    });

    function resetItemModal() {
        $('#selected-item-id').val('');
        $('#selected-item-name').text('');
        $('#item-quantity').val('1');
        $('#item-unit').val('Unit');
        $('#item-rate').val('0.00');
        $('#item-discount').val('0');
        $('#discount-type').val('amount');
        $('#item-tax').val('0');
        $('#warehouse-stock').text('0 Units');
        $('#shop-stock').text('0 Units');
        $('#selected-item-display').hide();
        $('#search-results').hide();
        $('#item-search').val('');
    }

    function calculateTotals() {
        let itemTotal = 0;
        purchaseItems.forEach(function(item) {
            itemTotal += parseFloat(item.total);
        });

        const orderTax = parseFloat($('#order_tax').val()) || 0;
        const discount = parseFloat($('#discount').val()) || 0;
        const shipping = parseFloat($('#shipping').val()) || 0;

        const grossTotal = itemTotal;
        const netPayable = itemTotal + orderTax - discount + shipping;

        $('#item-total').text('Rs ' + parseFloat(itemTotal).toFixed(2));
        $('#tax-display').text('Rs ' + parseFloat(orderTax).toFixed(2));
        $('#discount-display').text('Rs ' + parseFloat(discount).toFixed(2));
        $('#shipping-display').text('Rs ' + parseFloat(shipping).toFixed(2));
        $('#gross-total').text('Rs ' + parseFloat(grossTotal).toFixed(2));
        $('#net-payable').text('Rs ' + parseFloat(netPayable).toFixed(2));
    }

    // Recalculate on tax, discount, shipping change
    $('#order_tax, #discount, #shipping').on('input', function() {
        calculateTotals();
    });

    // Form submission
    $('#purchaseForm').on('submit', function(e) {
        e.preventDefault();

        if (purchaseItems.length === 0) {
            alert('Please add at least one item');
            return;
        }

        // Prepare items data
        const itemsData = purchaseItems.map(function(item) {
            return {
                item_id: item.item_id,
                quantity: item.quantity,
                unit: item.unit,
                rate: item.rate,
                discount: item.discount,
                tax_percentage: item.tax_percentage
            };
        });

        // Add items to form
        const formData = new FormData(this);
        itemsData.forEach(function(item, index) {
            Object.keys(item).forEach(function(key) {
                formData.append(`items[${index}][${key}]`, item[key]);
            });
        });

        // Submit form
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                window.location.href = '{{ route("all_purchases") }}';
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = '';
                    Object.values(xhr.responseJSON.errors).forEach(function(error) {
                        errors += error[0] + '\n';
                    });
                    alert(errors);
                } else {
                    alert('Error saving purchase. Please try again.');
                }
            }
        });
    });

    function generateInvoiceNumber() {
        // This will be generated server-side, but we can show a placeholder
        $('#invoice-number').text('Will be generated');
    }

    // Initialize date picker
    if ($('#purchase_date').length) {
        $('#purchase_date').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            todayHighlight: true
        });
    }
});
</script>
@endpush
@endsection

