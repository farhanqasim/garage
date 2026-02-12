@extends('layouts.app')
@section('title', 'Sales')
@section('content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Sales</h4>
            </div>
        </div>
        <ul class="table-top-head">
            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf">
                    <img src="{{ asset('assets/img/icons/pdf.svg') }}" alt="img">
                </a>
            </li>
            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel">
                    <img src="{{ asset('assets/img/icons/excel.svg') }}" alt="img">
                </a>
            </li>
            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh">
                    <i class="ti ti-refresh"></i>
                </a>
            </li>
            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header">
                    <i class="ti ti-chevron-up"></i>
                </a>
            </li>
        </ul>
        <div class="page-btn">
            @can('add_sales')
            <a href="{{ route('create.sale') }}" class="btn btn-primary" ><i
                    class="ti ti-circle-plus me-1"></i>Add Sales</a>
            @endcan
        </div>
    </div>

    <!-- /product list -->
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
            <div class="search-set">
                <div class="d-flex justify-content-end mb-3">
                    <input type="text" id="tableSearch" class="form-control w-100" placeholder="Search...">
                </div>
            </div>
            <div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                <div class="dropdown me-2">
                    <a href="javascript:void(0);"
                        class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center"
                        data-bs-toggle="dropdown">
                        Customer
                    </a>
                    <ul class="dropdown-menu  dropdown-menu-end p-3">
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Carl Evans</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Minerva Rameriz</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Robert Lamon</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Patricia Lewis</a>
                        </li>
                    </ul>
                </div>
                <div class="dropdown me-2">
                    <a href="javascript:void(0);"
                        class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center"
                        data-bs-toggle="dropdown">
                        Staus
                    </a>
                    <ul class="dropdown-menu  dropdown-menu-end p-3">
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Completed</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Pending</a>
                        </li>
                    </ul>
                </div>
                <div class="dropdown me-2">
                    <a href="javascript:void(0);"
                        class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center"
                        data-bs-toggle="dropdown">
                        Payment Status
                    </a>
                    <ul class="dropdown-menu  dropdown-menu-end p-3">
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Paid</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Unpaid</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Overdue</a>
                        </li>
                    </ul>
                </div>
                <div class="dropdown">
                    <a href="javascript:void(0);"
                        class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center"
                        data-bs-toggle="dropdown">
                        Sort By : Last 7 Days
                    </a>
                    <ul class="dropdown-menu  dropdown-menu-end p-3">
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Recently Added</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Ascending</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Desending</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Last Month</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Last 7 Days</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="searchableTable" class="table table-hover table-center">
                    <thead class="thead-light">
                        <tr>
                            <th>Customer</th>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Grand Total (Discount)</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Payment Status</th>
                            <th>Biller</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="sales-list">
                        @forelse($sales as $sale)
                        @php
                            $totalPaid = $sale->total_paid ?? 0;
                            $grandTotal = $sale->grand_total ?? 0;
                            $discount = $sale->discount ?? 0;
                            
                            // Due calculation: 
                            // Grand total already has discount subtracted (grand_total = subtotal + tax - discount + shipping)
                            // So due = grand_total - total_paid
                            // But if discount is given and no payment, treat discount as credit/payment
                            // If discount covers the entire grand_total, due should be 0
                            if ($discount > 0 && $totalPaid == 0) {
                                // Discount is like payment, so due = grand_total - discount
                                // But since grand_total already has discount, we need to add it back to original amount
                                // Original amount = grand_total + discount (before discount was applied)
                                // Due = (grand_total + discount) - discount - total_paid = grand_total - total_paid
                                // Actually, if discount is given, it means customer doesn't need to pay the discounted amount
                                // So due should be 0 if discount >= grand_total, otherwise grand_total - discount
                                $due = max(0, $grandTotal - $discount);
                            } else {
                                // Normal calculation: due = grand_total - total_paid
                                $due = max(0, $grandTotal - $totalPaid);
                            }
                            
                            // Payment status logic: 
                            // Agar discount hai aur payment nahi, to discount ko payment ki tarah treat karo
                            // Agar discount + payment >= grand_total ho to Paid
                            if ($discount > 0 && $totalPaid == 0) {
                                if ($discount >= $grandTotal) {
                                    $paymentStatus = 'Paid';
                                    $statusClass = 'success';
                                } else {
                                    $paymentStatus = 'Pending';
                                    $statusClass = 'warning';
                                }
                            } elseif ($totalPaid >= $grandTotal) {
                                $paymentStatus = 'Paid';
                                $statusClass = 'success';
                            } elseif ($totalPaid > 0) {
                                $paymentStatus = 'Partial';
                                $statusClass = 'info';
                            } else {
                                $paymentStatus = 'Pending';
                                $statusClass = 'warning';
                            }
                            
                            $statusBadge = $sale->status == 'completed' ? 'success' : ($sale->status == 'pending' ? 'warning' : 'secondary');
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                        <img src="{{ asset('assets/img/users/user-27.jpg') }}" alt="customer">
                                    </a>
                                    <a href="javascript:void(0);">{{ $sale->customer->names[0] ?? 'N/A' }}</a>
                                </div>
                            </td>
                            <td>{{ $sale->reference ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }}</td>
                            <td><span class="badge badge-{{ $statusBadge }}">{{ ucfirst($sale->status) }}</span></td>
                            <td>
                                <div>Rs {{ number_format($grandTotal, 2) }}</div>
                                @if($discount > 0)
                                    <small class="text-success">Discount: Rs {{ number_format($discount, 2) }}</small>
                                @endif
                            </td>
                            <td>Rs {{ number_format($totalPaid, 2) }}</td>
                            <td>Rs {{ number_format($due, 2) }}</td>
                            <td><span class="badge badge-soft-{{ $statusClass }} shadow-none badge-xs"><i
                                        class="ti ti-point-filled me-1"></i>{{ $paymentStatus }}</span></td>
                            <td>{{ $sale->user->name ?? 'Admin' }}</td>
                            <td class="text-center no-highlight">
                                <a class="action-set" href="javascript:void(0);" data-bs-toggle="dropdown"
                                    aria-expanded="true">
                                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    @can('view_sales')
                                    <li>
                                        <a href="{{ route('sales.show', $sale->id) }}" class="dropdown-item"><i data-feather="eye"
                                                class="info-img"></i>Sale Detail</a>
                                    </li>
                                    @endcan
                                    @can('update_sales')
                                    <li>
                                        <a href="{{ route('sales.edit', $sale->id) }}" class="dropdown-item"><i data-feather="edit"
                                                class="info-img"></i>Edit Sale</a>
                                    </li>
                                    @endcan
                                    @can('view_sales')
                                    <li>
                                        <a href="{{ route('sales.payments', $sale->id) }}" class="dropdown-item"><i data-feather="dollar-sign"
                                                class="info-img"></i>Show Payments</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('sales.payments.create', $sale->id) }}" class="dropdown-item"><i data-feather="plus-circle"
                                                class="info-img"></i>Create Payment</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('sales.download.pdf', $sale->id) }}" class="dropdown-item" target="_blank"><i data-feather="download"
                                                class="info-img"></i>Download pdf</a>
                                    </li>
                                    @endcan
                                    @can('delete_sales')
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item mb-0 delete-sale" 
                                            data-sale-id="{{ $sale->id }}" data-bs-toggle="modal"
                                            data-bs-target="#delete"><i data-feather="trash-2"
                                                class="info-img"></i>Delete Sale</a>
                                    </li>
                                    @endcan
                                </ul>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <p class="text-muted mb-0">No sales found. <a href="{{ route('create.sale.new') }}">Create your first sale</a></p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Add Customer -->
<div class="modal fade" id="add_customer">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <div class="page-title">
                    <h4>Add Customer</h4>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="customerForm" enctype="multipart/form-data">
                <div class="modal-body">

                    <!-- Phone -->
                    <div>
                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="number" id="phone" class="form-control">
                    </div>

                    <!-- Customer Name + Mic -->
                    <div class="mt-3">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>

                        <div class="input-group">
                            <input type="text" id="customerName" class="form-control">

                            <button type="button" id="startRecord" class="btn btn-outline-primary">
                                <i class="fa fa-microphone"></i>
                            </button>

                            <button type="button" id="stopRecord" class="btn btn-outline-danger d-none">
                                <i class="fa fa-stop"></i>
                            </button>
                        </div>

                        <small id="recordingStatus" class="text-primary d-none">Recording… speak now</small>

                        <div id="audioPreview" class="mt-2 d-none">
                            <audio controls id="recordedAudio" class="w-100"></audio>
                            <button type="button" id="deleteAudio" class="btn btn-sm btn-danger mt-2">Delete
                                Recording</button>
                        </div>

                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn me-2 btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Customer</button>
                </div>
            </form>

        </div>
    </div>
</div>
<script>
    let mediaRecorder;
    let audioChunks = [];
    let finalAudioBlob = null;
    let recognition = new webkitSpeechRecognition();
    recognition.lang = "en-US";
    recognition.interimResults = true;
    recognition.onresult = (e) => {
        const text = e.results[0][0].transcript;
        document.getElementById("customerName").value = text;
    };
    document.getElementById("startRecord").onclick = async () => {
        document.getElementById("recordingStatus").classList.remove("d-none");
        document.getElementById("stopRecord").classList.remove("d-none");
        document.getElementById("startRecord").classList.add("d-none");
        const stream = await navigator.mediaDevices.getUserMedia({
            audio: true
        });
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];
        mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
        mediaRecorder.onstop = () => {
            finalAudioBlob = new Blob(audioChunks, {
                type: "audio/webm"
            });
            const audioURL = URL.createObjectURL(finalAudioBlob);
            document.getElementById("recordedAudio").src = audioURL;
            document.getElementById("audioPreview").classList.remove("d-none");
            document.getElementById("recordingStatus").classList.add("d-none");
        };
        mediaRecorder.start();
        recognition.start();
    };
    document.getElementById("stopRecord").onclick = () => {
        mediaRecorder.stop();
        recognition.stop();
        document.getElementById("stopRecord").classList.add("d-none");
        document.getElementById("startRecord").classList.remove("d-none");
    };
    // Delete recording
    document.getElementById("deleteAudio").onclick = () => {
        finalAudioBlob = null;
        document.getElementById("audioPreview").classList.add("d-none");
        document.getElementById("customerName").value = "";
    };
</script>

<!-- Sale Detail Modal -->
<div class="modal fade" id="sales-details-new" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sale Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="sale-detail-content">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Sale Modal -->
<div class="modal fade" id="edit-sales-new" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Sale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="edit-sale-form">
                <div class="modal-body" id="edit-sale-content">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Sale</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Show Payments Modal -->
<div class="modal fade" id="showpayment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Show Payments</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="show-payments-content">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Payment Modal -->
<div class="modal fade" id="createpayment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="create-payment-form">
                <div class="modal-body">
                    <input type="hidden" id="payment-sale-id" name="sale_id">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method_id" id="payment_method_id_modal" class="form-select" required>
                                <option value="">Select Payment Method</option>
                                @php
                                    $cashMethod = \App\Models\PaymentMethod::where('code', 'cash')->where('is_active', true)->first();
                                    $bankMethod = \App\Models\PaymentMethod::where('code', 'bank_transfer')->where('is_active', true)->first();
                                    if (!$bankMethod) {
                                        $bankMethod = \App\Models\PaymentMethod::where('requires_bank_account', true)->where('is_active', true)->first();
                                    }
                                @endphp
                                @if($cashMethod)
                                    <option value="{{ $cashMethod->id }}" data-requires-bank="0" data-method-code="cash">Cash</option>
                                @endif
                                @if($bankMethod)
                                    <option value="{{ $bankMethod->id }}" data-requires-bank="1" data-method-code="bank">Bank</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Amount <span class="text-danger">*</span></label>
                            <input type="number" name="payment_amount" id="payment_amount_modal" class="form-control" step="0.01" min="0.01" required>
                            <small class="text-muted">Remaining: <span id="remaining-amount-modal">Rs 0</span></small>
                        </div>
                    </div>
                    <div class="row mb-3" id="bank-account-row-modal" style="display: none;">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Bank Account <span class="text-danger">*</span></label>
                            <select name="bank_account_id" id="bank_account_id_modal" class="form-select">
                                <option value="">Select Bank Account</option>
                                @php
                                    $bankAccounts = \App\Models\BankAccount::where('status', true)->with('bank')->get();
                                @endphp
                                @foreach($bankAccounts as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->bank->name ?? 'N/A' }} - {{ $account->account_title }} ({{ $account->account_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Transaction ID <span class="text-danger">*</span></label>
                            <input type="text" name="payment_transaction_id" id="payment_transaction_id_modal" class="form-control" placeholder="Enter transaction reference">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" id="payment_date_modal" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea name="payment_notes" id="payment_notes_modal" class="form-control" rows="2" placeholder="Additional notes (optional)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Sale Modal -->
<div class="modal fade" id="delete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="form-content">
                    <h4 class="modal-title mb-3">Are you sure?</h4>
                    <p class="mb-0">Do you really want to delete this sale? This process cannot be undone.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-sale">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let currentSaleId = null;
    
    // View Sale Detail
    $(document).on('click', '.view-sale-detail', function() {
        const saleId = $(this).data('sale-id');
        currentSaleId = saleId;
        
        $('#sale-detail-content').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        
        $.ajax({
            url: "{{ route('sales.show', ':id') }}".replace(':id', saleId),
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const sale = response.sale;
                    let itemsHtml = '';
                    response.items.forEach(function(item) {
                        itemsHtml += `
                            <tr>
                                <td>${item.name}</td>
                                <td>${item.quantity}</td>
                                <td>${item.unit}</td>
                                <td>Rs ${parseFloat(item.rate).toFixed(2)}</td>
                                <td>Rs ${parseFloat(item.discount).toFixed(2)}</td>
                                <td>${parseFloat(item.tax_percentage).toFixed(2)}%</td>
                                <td>Rs ${parseFloat(item.total).toFixed(2)}</td>
                            </tr>
                        `;
                    });
                    
                    let paymentsHtml = '';
                    if (response.payments.length > 0) {
                        response.payments.forEach(function(payment) {
                            paymentsHtml += `
                                <tr>
                                    <td>${payment.method}</td>
                                    <td>Rs ${parseFloat(payment.amount).toFixed(2)}</td>
                                    <td>${payment.date}</td>
                                    <td>${payment.transaction_id || 'N/A'}</td>
                                    <td>${payment.bank_account || 'N/A'}</td>
                                </tr>
                            `;
                        });
                    } else {
                        paymentsHtml = '<tr><td colspan="5" class="text-center text-muted">No payments found</td></tr>';
                    }
                    
                    const html = `
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold">Customer Information</h6>
                                <p class="mb-1"><strong>Name:</strong> ${sale.customer ? (sale.customer.names ? sale.customer.names[0] : 'N/A') : 'N/A'}</p>
                                <p class="mb-1"><strong>Phone:</strong> ${sale.customer ? (sale.customer.phones ? sale.customer.phones[0] : 'N/A') : 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold">Sale Information</h6>
                                <p class="mb-1"><strong>Reference:</strong> ${sale.reference || 'N/A'}</p>
                                <p class="mb-1"><strong>Date:</strong> ${new Date(sale.sale_date).toLocaleDateString()}</p>
                                <p class="mb-1"><strong>Status:</strong> <span class="badge badge-${sale.status === 'completed' ? 'success' : 'warning'}">${sale.status}</span></p>
                            </div>
                        </div>
                        <div class="table-responsive mb-4">
                            <h6 class="fw-bold mb-3">Items</h6>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>Rate</th>
                                        <th>Discount</th>
                                        <th>Tax %</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemsHtml}
                                </tbody>
                            </table>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Subtotal:</strong> Rs ${parseFloat(sale.subtotal).toFixed(2)}</p>
                                <p><strong>Order Tax:</strong> Rs ${parseFloat(sale.order_tax || 0).toFixed(2)}</p>
                                <p><strong>Discount:</strong> Rs ${parseFloat(sale.discount || 0).toFixed(2)}</p>
                                <p><strong>Shipping:</strong> Rs ${parseFloat(sale.shipping || 0).toFixed(2)}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="h5"><strong>Grand Total:</strong> Rs ${parseFloat(sale.grand_total).toFixed(2)}</p>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <h6 class="fw-bold mb-3">Payments</h6>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Method</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Transaction ID</th>
                                        <th>Bank Account</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${paymentsHtml}
                                </tbody>
                            </table>
                        </div>
                    `;
                    $('#sale-detail-content').html(html);
                }
            },
            error: function(xhr) {
                $('#sale-detail-content').html('<div class="alert alert-danger">Error loading sale details. Please try again.</div>');
            }
        });
    });
    
    // Edit Sale
    $(document).on('click', '.edit-sale', function() {
        const saleId = $(this).data('sale-id');
        currentSaleId = saleId;
        
        $('#edit-sale-content').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        
        $.ajax({
            url: "{{ route('sales.edit', ':id') }}".replace(':id', saleId),
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const sale = response.sale;
                    const html = `
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Customer <span class="text-danger">*</span></label>
                                <select name="customer_id" id="edit_customer_id" class="form-select" required>
                                    <option value="">Select Customer</option>
                                    ${response.customers.map(c => `<option value="${c.id}" ${c.id == sale.customer_id ? 'selected' : ''}>${c.names ? c.names[0] : 'N/A'}</option>`).join('')}
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Branch <span class="text-danger">*</span></label>
                                <select name="branch_id" id="edit_branch_id" class="form-select" required>
                                    <option value="">Select Branch</option>
                                    ${response.branches.map(b => `<option value="${b.id}" ${b.id == sale.branch_id ? 'selected' : ''}>${b.branch_name}</option>`).join('')}
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Sale Date <span class="text-danger">*</span></label>
                                <input type="date" name="sale_date" id="edit_sale_date" class="form-control" value="${sale.sale_date}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Reference</label>
                                <input type="text" name="reference" id="edit_reference" class="form-control" value="${sale.reference || ''}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status</label>
                                <select name="status" id="edit_status" class="form-select">
                                    <option value="pending" ${sale.status === 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="completed" ${sale.status === 'completed' ? 'selected' : ''}>Completed</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Discount</label>
                                <input type="number" name="discount" id="edit_discount" class="form-control" value="${sale.discount || 0}" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Order Tax</label>
                                <input type="number" name="order_tax" id="edit_order_tax" class="form-control" value="${sale.order_tax || 0}" step="0.01" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Shipping</label>
                                <input type="number" name="shipping" id="edit_shipping" class="form-control" value="${sale.shipping || 0}" step="0.01" min="0">
                            </div>
                        </div>
                    `;
                    $('#edit-sale-content').html(html);
                }
            },
            error: function(xhr) {
                $('#edit-sale-content').html('<div class="alert alert-danger">Error loading sale data. Please try again.</div>');
            }
        });
    });
    
    // Submit Edit Sale Form
    $('#edit-sale-form').on('submit', function(e) {
        e.preventDefault();
        if (!currentSaleId) return;
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: "{{ route('sales.update', ':id') }}".replace(':id', currentSaleId),
            method: 'PUT',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Sale updated successfully!'
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Error updating sale. Please try again.'
                });
            }
        });
    });
    
    // Show Payments
    $(document).on('click', '.show-payments', function() {
        const saleId = $(this).data('sale-id');
        
        $('#show-payments-content').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        
        $.ajax({
            url: "{{ route('sales.payments', ':id') }}".replace(':id', saleId),
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    let paymentsHtml = '';
                    if (response.payments.length > 0) {
                        response.payments.forEach(function(payment) {
                            paymentsHtml += `
                                <tr>
                                    <td>${payment.method}</td>
                                    <td>Rs ${parseFloat(payment.amount).toFixed(2)}</td>
                                    <td>${payment.date}</td>
                                    <td>${payment.transaction_id || 'N/A'}</td>
                                    <td>${payment.bank_account || 'N/A'}</td>
                                    <td>${payment.notes || 'N/A'}</td>
                                </tr>
                            `;
                        });
                    } else {
                        paymentsHtml = '<tr><td colspan="6" class="text-center text-muted">No payments found</td></tr>';
                    }
                    
                    const html = `
                        <div class="mb-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>Grand Total:</strong> Rs ${parseFloat(response.grand_total).toFixed(2)}</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Total Paid:</strong> Rs ${parseFloat(response.total_paid).toFixed(2)}</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Due:</strong> Rs ${parseFloat(response.due).toFixed(2)}</p>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Method</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Transaction ID</th>
                                        <th>Bank Account</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${paymentsHtml}
                                </tbody>
                            </table>
                        </div>
                    `;
                    $('#show-payments-content').html(html);
                }
            },
            error: function(xhr) {
                $('#show-payments-content').html('<div class="alert alert-danger">Error loading payments. Please try again.</div>');
            }
        });
    });
    
    // Create Payment
    $(document).on('click', '.create-payment', function() {
        const saleId = $(this).data('sale-id');
        currentSaleId = saleId;
        $('#payment-sale-id').val(saleId);
        
        // Load sale data to show remaining amount
        $.ajax({
            url: "{{ route('sales.show', ':id') }}".replace(':id', saleId),
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const sale = response.sale;
                    const totalPaid = sale.total_paid || 0;
                    const remaining = sale.grand_total - totalPaid;
                    $('#remaining-amount-modal').text('Rs ' + parseFloat(remaining).toFixed(2));
                    $('#payment_amount_modal').attr('max', remaining);
                }
            }
        });
    });
    
    // Payment method change handler for modal
    $('#payment_method_id_modal').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const requiresBank = selectedOption.data('requires-bank') == '1';
        const methodCode = selectedOption.data('method-code') || '';
        const isBank = methodCode.toLowerCase() === 'bank' || requiresBank;
        
        if (isBank && $(this).val()) {
            $('#bank-account-row-modal').show();
            $('#bank_account_id_modal').prop('required', true);
            $('#payment_transaction_id_modal').prop('required', true);
        } else {
            $('#bank-account-row-modal').hide();
            $('#bank_account_id_modal').prop('required', false);
            $('#payment_transaction_id_modal').prop('required', false);
        }
    });
    
    // Submit Create Payment Form
    $('#create-payment-form').on('submit', function(e) {
        e.preventDefault();
        if (!currentSaleId) return;
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: "{{ route('sales.payments.create', ':id') }}".replace(':id', currentSaleId),
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Payment created successfully!'
                    }).then(() => {
                        $('#createpayment').modal('hide');
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Error creating payment. Please try again.'
                });
            }
        });
    });
    
    // Delete Sale
    $(document).on('click', '.delete-sale', function() {
        currentSaleId = $(this).data('sale-id');
    });
    
    $('#confirm-delete-sale').on('click', function() {
        if (!currentSaleId) return;
        
        $.ajax({
            url: "{{ route('sales.destroy', ':id') }}".replace(':id', currentSaleId),
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Sale deleted successfully!'
                    }).then(() => {
                        $('#delete').modal('hide');
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Error deleting sale. Please try again.'
                });
            }
        });
    });
});
</script>

@endsection


