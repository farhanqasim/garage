@extends('layouts.app')

@section('title', 'Create Purchase')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Create Purchase</h4>
            <h6>Add new purchase order</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('all_purchases') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
                        @csrf
                        
                        <!-- Active Branch Section -->
                        <div class="mb-4 d-flex align-items-center">
                            <i class="ti ti-user me-2 fs-18"></i>
                            <span class="fw-bold me-2">ACTIVE BRANCH:</span>
                            <div class="dropdown">
                                <button class="btn btn-link text-primary p-0 text-decoration-none dropdown-toggle" type="button" id="branchDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ session('selected_branch_name', 'Select Branch') }}
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="branchDropdown">
                                    @php
                                        $branches = \App\Models\Branch::where('status', 'active')->get();
                                        $currentBranchId = session('selected_branch_id');
                                    @endphp
                                    @foreach($branches as $branch)
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)" onclick="selectBranch({{ $branch->id }}, '{{ $branch->branch_name }}')">
                                            {{ $branch->branch_name }} @if($branch->branch_code) ({{ $branch->branch_code }}) @endif
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <!-- Business Info and Purchase Number -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-primary text-white rounded p-2 me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ti ti-file-invoice fs-20"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0 fw-bold">{{ setting_value('logo_text', 'MUBARAK TRADERS') }}</h4>
                                        <p class="mb-0 text-muted">{{ setting_value('company_tagline', 'PREMIUM OIL & LUBRICANTS') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="mb-2">
                                    <span class="text-primary fw-bold fs-18" id="purchase-number">PUR #{{ str_pad(\App\Models\Purchase::max('id') + 1 ?? 1, 5, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <div>
                                    <input type="text" name="purchase_date" id="purchase_date" class="form-control" style="width: 150px; display: inline-block;" value="{{ date('d/m/Y') }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- Supplier Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold mb-2">SUPPLIER NAME</label>
                                <select name="supplier_id" id="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror" required>
                                    <option value="">Party Name</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" 
                                                data-name="{{ $supplier->names[0] ?? '' }}" 
                                                data-phone="{{ $supplier->phones[0] ?? '' }}"
                                                data-company="{{ $supplier->company ?? '' }}">
                                            {{ $supplier->names[0] ?? 'N/A' }} @if($supplier->company) - {{ $supplier->company }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold mb-2">MOBILE NUMBER</label>
                                <input type="text" id="supplier_mobile" class="form-control" placeholder="03xx..." readonly>
                                <small class="text-muted" style="font-size: 11px;">Double tap to edit</small>
                            </div>
                        </div>

                        <!-- Reference (Optional) -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold mb-2">REFERENCE</label>
                                <input type="text" name="reference" id="reference" class="form-control" placeholder="Enter reference number">
                            </div>
                        </div>

                        <!-- Items Summary Section -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">ITEMS SUMMARY</h5>
                            <div id="items-summary-container" class="text-center py-5" style="background: #f8f9fa; border-radius: 8px; min-height: 200px;">
                                <div id="empty-items-state">
                                    <i class="ti ti-package fs-48 text-muted mb-3" style="display: block;"></i>
                                    <p class="text-muted mb-0">ABHI KOI ITEM NAHI HAI</p>
                                </div>
                                <div id="items-list" style="display: none;">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
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
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Amount Summary -->
                        <div class="row mb-4">
                            <div class="col-md-6 offset-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold">GROSS AMOUNT</span>
                                    <span class="fw-bold" id="gross-amount">Rs 0</span>
                                </div>
                                <div class="bg-primary text-white p-3 rounded mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold fs-16">GRAND TOTAL</div>
                                            <div class="small">Total Payable Amount</div>
                                        </div>
                                        <div class="fw-bold fs-24" id="grand-total">Rs 0</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden fields for order tax, discount, shipping -->
                        <input type="hidden" name="order_tax" id="order_tax" value="0">
                        <input type="hidden" name="discount" id="discount" value="0">
                        <input type="hidden" name="shipping" id="shipping" value="0">
                        <input type="hidden" name="status" value="pending">

                        <!-- Add Item Button -->
                        <div class="text-center mb-4">
                            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#add-item-modal">
                                <i class="ti ti-plus me-2"></i>NAYA ITEM ADD KARAIN
                            </button>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('all_purchases') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-check me-1"></i> Save Purchase
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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

    // Supplier change handler
    $('#supplier_id').on('change', function() {
        const selected = $(this).find('option:selected');
        const name = selected.data('name') || '';
        const phone = selected.data('phone') || '';
        
        $('#supplier_mobile').val(phone);
        
        // Make mobile editable on double click
        $('#supplier_mobile').off('dblclick').on('dblclick', function() {
            $(this).prop('readonly', false).focus();
        });
    });

    // Branch selection
    function selectBranch(branchId, branchName) {
        // Update session via AJAX
        $.ajax({
            url: '{{ route("branch.select.complete") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                branch_id: branchId
            },
            success: function() {
                $('#branchDropdown').text(branchName);
                location.reload();
            }
        });
    }
    window.selectBranch = selectBranch;

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
                $('#item-rate').val(parseFloat(response.rate || 0).toFixed(2));
                $('#item-unit').val(response.unit || 'Unit');
                $('#warehouse-stock').text((response.warehouse_stock || 0) + ' Units');
                $('#shop-stock').text((response.shop_stock || 0) + ' Units');
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
        $('#empty-items-state').hide();
        $('#items-list').show();
        
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
            $('#empty-items-state').show();
            $('#items-list').hide();
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
        const grandTotal = itemTotal + orderTax - discount + shipping;

        $('#gross-amount').text('Rs ' + parseFloat(grossTotal).toFixed(2));
        $('#grand-total').text('Rs ' + parseFloat(grandTotal).toFixed(2));
    }

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
