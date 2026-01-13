@extends('layouts.app')
@section('title','All Suppliers')
@push('styles')
<!-- Cropper.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<style>
    /* Fix cursor crosshair issue on body and hide DuckDuckGo header */
    body {
        cursor: default !important;
    }
    
    /* Hide DuckDuckGo header wrapper */
    #header_wrapper,
    div#header_wrapper,
    .header-wrap.js-header-wrap,
    div[data-testid="header"],
    div[class*="header-wrap"][id*="header"],
    div[id*="header_wrapper"] {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        height: 0 !important;
        width: 0 !important;
        overflow: hidden !important;
        position: absolute !important;
        left: -9999px !important;
        pointer-events: none !important;
    }
    
    body * {
        cursor: inherit !important;
    }
    
    /* Restore pointer cursor for clickable elements */
    a, button, .btn, [onclick], [role="button"], .clickable,
    input[type="submit"], input[type="button"], input[type="file"],
    .profile-upload-box, .multiple-upload-box, .cursor-pointer {
        cursor: pointer !important;
    }
    
    input, textarea, select, .form-control, .form-select {
        cursor: text !important;
    }
    
    /* Prevent inline cursor: crosshair style on body - more aggressive */
    body[style*="cursor: crosshair"],
    body[style*="cursor:crosshair"],
    body[style*="cursor:crosshair"],
    body[style*="cursor: crosshair"] {
        cursor: default !important;
    }
    
    /* Override any inline style */
    body {
        cursor: default !important;
    }
    
    /* Overall Modal Form Alignment */
    #addSupplierModal .modal-body .row {
        margin-left: 0;
        margin-right: 0;
    }
    
    #addSupplierModal .modal-body .row > [class*="col-"] {
        padding-left: 15px;
        padding-right: 15px;
        margin-bottom: 1rem;
    }
    
    /* Form Labels Alignment */
    #addSupplierModal .form-label {
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #495057;
        display: block;
    }
    
    /* Form Controls Alignment */
    #addSupplierModal .form-control,
    #addSupplierModal .form-select {
        width: 100%;
        margin-bottom: 0;
    }
    
    /* Input Groups Alignment */
    #addSupplierModal .input-group {
        width: 100%;
        display: flex;
        align-items: stretch;
    }
    
    #addSupplierModal .input-group .form-control {
        flex: 1 1 auto;
    }
    
    #addSupplierModal .input-group .btn {
        flex: 0 0 auto;
    }
    
    /* Name Phone Row Alignment */
    .name-phone-row {
        align-items: flex-start !important;
        margin-bottom: 1rem !important;
    }
    
    .name-phone-row .col-md-6 {
        display: flex;
        flex-direction: column;
        margin-bottom: 0;
    }
    
    .name-phone-row .form-label {
        margin-bottom: 0.5rem;
    }
    
    .name-phone-row .input-group {
        width: 100%;
    }
    
    .name-phone-row .remove-row {
        flex-shrink: 0;
        width: auto;
        min-width: 40px;
    }
    
    .name-phone-row .d-flex.gap-2 {
        gap: 0.5rem !important;
    }
    
    /* Upload Boxes Alignment */
    #addSupplierModal .profile-upload-box,
    #addSupplierModal .multiple-upload-box {
        min-height: 150px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    /* Form Check Alignment */
    #addSupplierModal .form-check {
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
    }
    
    #addSupplierModal .form-check-input {
        margin-right: 0.5rem;
        margin-top: 0;
    }
    
    /* Credit Limit Section */
    #addSupplierModal #creditLimitOptions {
        margin-top: 1rem;
    }
    
    /* Button Alignment */
    #addSupplierModal #addNamePhone {
        margin-top: 0.5rem;
    }
    
    /* Small Text Alignment */
    #addSupplierModal .form-text {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.875rem;
    }
    
    /* Preview Containers */
    #addSupplierModal #visiting_preview,
    #addSupplierModal .preview-container {
        margin-top: 0.5rem;
    }
    
    /* Cancel Buttons */
    #addSupplierModal #cancelVisitingDoc,
    #addSupplierModal #cancelProfileImg {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        border: 2px solid #fff;
    }
    
    #addSupplierModal #cancelVisitingDoc:hover,
    #addSupplierModal #cancelProfileImg:hover {
        transform: scale(1.1);
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);
    }
    
    #addSupplierModal #visiting_preview {
        position: relative;
    }
    
    /* Error Messages Alignment */
    #addSupplierModal .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: #dc3545;
    }
    
    #addSupplierModal .is-invalid {
        border-color: #dc3545;
    }
    
    #addSupplierModal .is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }
    
    /* Consistent Spacing */
    #addSupplierModal .col-md-6,
    #addSupplierModal .col-md-12,
    #addSupplierModal .col-12 {
        margin-bottom: 1rem;
    }
    
    /* Group field specific styling */
    #addSupplierModal .col-md-6:has(select[name="group_id"]) {
        width: auto;
        min-width: 100px;
        flex: 1 1 auto;
        text-align: left;
    }
    
    /* Remove extra padding from row */
    #addSupplierModal .row.g-3 {
        --bs-gutter-x: 1rem;
        --bs-gutter-y: 1rem;
    }
    
    /* Ensure proper height for upload boxes */
    #addSupplierModal .profile-upload-box,
    #addSupplierModal .multiple-upload-box {
        position: relative;
        overflow: hidden;
    }
    
    /* Button spacing */
    #addSupplierModal .btn {
        white-space: nowrap;
    }
    
    /* Select dropdown alignment */
    #addSupplierModal .form-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
    }
    
    /* Supplier Modal Background Styling - Consistent White Background */
    #addSupplierModal .modal-content {
        background-color: #ffffff !important;
        border-radius: 12px !important;
        border: none !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
    }
    
    #addSupplierModal .modal-header {
        background-color: #ffffff !important;
        border-bottom: 1px solid #e9ecef !important;
        border-radius: 12px 12px 0 0 !important;
        padding: 20px 25px !important;
    }
    
    #addSupplierModal .modal-body {
        background-color: #ffffff !important;
        padding: 25px !important;
    }
    
    #addSupplierModal .modal-footer {
        background-color: #ffffff !important;
        border-top: 1px solid #e9ecef !important;
        border-radius: 0 0 12px 12px !important;
        padding: 15px 25px !important;
    }
    
    /* Edit Supplier Modal Background Styling - Consistent White Background */
    [id^="editSupplierModal"] .modal-content {
        background-color: #ffffff !important;
        border-radius: 12px !important;
        border: none !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
    }
    
    [id^="editSupplierModal"] .modal-header {
        background-color: #ffffff !important;
        border-bottom: 1px solid #e9ecef !important;
        border-radius: 12px 12px 0 0 !important;
        padding: 20px 25px !important;
    }
    
    [id^="editSupplierModal"] .modal-body {
        background-color: #ffffff !important;
        padding: 25px !important;
    }
    
    [id^="editSupplierModal"] .modal-footer {
        background-color: #ffffff !important;
        border-top: 1px solid #e9ecef !important;
        border-radius: 0 0 12px 12px !important;
        padding: 15px 25px !important;
    }
    
    /* Form controls should have white background */
    #addSupplierModal .form-control,
    #addSupplierModal .form-select,
    [id^="editSupplierModal"] .form-control,
    [id^="editSupplierModal"] .form-select {
        background-color: #ffffff !important;
    }
    
    /* Upload boxes should have light background */
    #addSupplierModal .profile-upload-box,
    #addSupplierModal .multiple-upload-box,
    #addSupplierModal .bg-light,
    [id^="editSupplierModal"] .profile-upload-box,
    [id^="editSupplierModal"] .multiple-upload-box,
    [id^="editSupplierModal"] .bg-light {
        background-color: #f8f9fa !important;
    }
    
    /* Ensure row and column backgrounds are transparent */
    #addSupplierModal .row,
    #addSupplierModal .col-md-6,
    #addSupplierModal .col-md-12,
    #addSupplierModal .col-12,
    [id^="editSupplierModal"] .row,
    [id^="editSupplierModal"] .col-md-6,
    [id^="editSupplierModal"] .col-md-12,
    [id^="editSupplierModal"] .col-12 {
        background-color: transparent !important;
    }
    
    /* Image Crop Modal Styling */
    #imageCropModal .modal-content {
        background-color: #ffffff !important;
        border-radius: 12px !important;
    }
    
    /* Supplier Ledger Modal Background Styling - Consistent White Background */
    #supplierLedgerModal .modal-content {
        background-color: #ffffff !important;
        border-radius: 12px !important;
        border: none !important;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    #supplierLedgerModal .modal-header {
        background-color: #ffffff !important;
        border-bottom: 1px solid #e9ecef !important;
        border-radius: 12px 12px 0 0 !important;
        padding: 1rem 1.5rem !important;
    }
    
    #supplierLedgerModal .modal-body {
        background-color: #ffffff !important;
        padding: 25px !important;
        min-height: 200px;
        max-height: 70vh;
        overflow-y: auto;
    }
    
    #supplierLedgerModal .modal-footer {
        background-color: #ffffff !important;
        border-top: 1px solid #e9ecef !important;
        border-radius: 0 0 12px 12px !important;
        padding: 1rem 1.5rem !important;
    }
    
    /* Ledger Report Styling */
    .ledger-report {
        background-color: #ffffff !important;
        width: 100%;
    }
    
    .ledger-report .row {
        margin-left: 0;
        margin-right: 0;
    }
    
    .ledger-report .row > [class*="col-"] {
        padding-left: 15px;
        padding-right: 15px;
        margin-bottom: 1rem;
    }
    
    .ledger-report table {
        width: 100%;
        margin-bottom: 1rem;
    }
    
    .ledger-report table th,
    .ledger-report table td {
        padding: 0.75rem;
        vertical-align: middle;
    }
    
    .ledger-report .table-bordered {
        border: 1px solid #dee2e6;
    }
    
    .ledger-report .table-bordered th,
    .ledger-report .table-bordered td {
        border: 1px solid #dee2e6;
    }
    
    /* Modal Backdrop */
    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.5) !important;
    }
    
    /* Ensure modal is properly centered */
    #supplierLedgerModal.modal {
        z-index: 1055;
    }
    
    #supplierLedgerModal.modal.show {
        display: block !important;
    }
    
    #imageCropModal .img-container {
        text-align: center;
        max-height: 500px;
        overflow: hidden;
    }
    
    #imageCropModal .img-container img {
        max-width: 100%;
        max-height: 500px;
    }
    
    /* Cropper.js container styling */
    #imageCropModal .cropper-container {
        direction: ltr;
    }
    
    /* Table Column Alignment - Phone (3rd column) */
    #searchableTable thead th:nth-child(3),
    #searchableTable tbody td:nth-child(3) {
        text-align: left;
        vertical-align: middle;
        padding-left: 15px;
        padding-right: 15px;
    }
    
    /* Table Column Alignment - Supplier Name (4th column) */
    #searchableTable thead th:nth-child(4),
    #searchableTable tbody td:nth-child(4) {
        text-align: left;
        vertical-align: middle;
        padding-left: 15px;
        padding-right: 15px;
    }
    
    /* Ensure Supplier Name column has proper alignment */
    #searchableTable tbody tr td:nth-child(4) {
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 250px;
    }
    
    /* Table Column Alignment - Created Date/Time (6th column) */
    #searchableTable thead th:nth-child(6),
    #searchableTable tbody td:nth-child(6) {
        text-align: left;
        vertical-align: middle;
        padding-left: 15px;
        padding-right: 15px;
        min-width: 150px;
    }
    
    /* Table Column Alignment - Created By (7th column) */
    #searchableTable thead th:nth-child(7),
    #searchableTable tbody td:nth-child(7) {
        text-align: left;
        vertical-align: middle;
        padding-left: 15px;
        padding-right: 15px;
        min-width: 180px;
    }
    
    /* Style for created date/time and created by cells */
    #searchableTable tbody td:nth-child(6) small,
    #searchableTable tbody td:nth-child(7) small {
        display: block;
        line-height: 1.4;
    }
</style>
@endpush
@section('content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h2 class="fw-bold">All Suppliers</h2>
            </div>
        </div>
        <ul class="table-top-head">
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{ asset('assets/img/icons/pdf.svg') }}" alt="img"></a></li>
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{ asset('assets/img/icons/excel.svg') }}" alt="img"></a></li>
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
        <div class="page-btn">
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                <i class="ti ti-circle-plus me-1"></i>Add
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
            <div class="d-flex justify-content-end mb-3">
                <input type="text" id="tableSearch" class="form-control w-100" placeholder="Search...">
            </div>
            <div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                <!-- Start Date Filter -->
                <div class="me-2">
                    <label for="startDateFilter" class="form-label mb-0 me-2" style="font-size: 12px;">Start Date:</label>
                    <input type="date" id="startDateFilter" class="form-control form-control-sm" style="width: 150px;" value="{{ date('Y-m-d') }}">
                </div>
                
                <!-- End Date Filter -->
                <div class="me-2">
                    <label for="endDateFilter" class="form-label mb-0 me-2" style="font-size: 12px;">End Date:</label>
                    <input type="date" id="endDateFilter" class="form-control form-control-sm" style="width: 150px;" value="{{ date('Y-m-d') }}">
                </div>
                
                <!-- Status Switch -->
                <div class="status-toggle d-flex align-items-center">
                    <span class="me-2">Status:</span>
                    <input type="checkbox" id="status-filter" class="check status-filter-checkbox">
                    <label for="status-filter" class="checktoggle"></label>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="searchableTable" class="table table-hover table-center" id="supplierTable">
                    <thead class="thead-primary">
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Phone</th>
                            <th>Supplier Name</th>
                            <th>Email</th>
                            <th>Created Date/Time</th>
                            <th>Created By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suppliers as $key => $item)
                        <tr data-status="{{ strtolower($item->status ?? 'active') }}">
                            <td>{{ $key + 1 }}</td>
                            <td>
                                @if ($item->profile_img)
                                <a href="{{ asset($item->profile_img) }}" target="_blank">
                                    <img src="{{ asset($item->profile_img) }}" class="rounded" width='50px' height="50px" alt="">
                                </a>
                                @else
                                <img src="{{ asset('assets/img/profiles/avator1.jpg') }}" class="rounded" width='50px' height="50px" alt="">
                                @endif
                            </td>
                            <td>{{ $item->phones[0] ?? 'N/A' }}</td>
                            <td>{{ $item->names[0] ?? 'N/A' }}</td>
                            <td>{{ $item->email }}</td>
                            <td>
                                @if($item->created_at)
                                    <small>
                                        <strong>Date:</strong> {{ $item->created_at->format('d/m/Y') }}<br>
                                        <strong>Time:</strong> {{ $item->created_at->format('h:i A') }}
                                    </small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $createdByUser = $item->createdBy ?? null;
                                    $createdByBranch = $item->createdByBranch ?? null;
                                @endphp
                                @if($createdByUser || $createdByBranch)
                                    <small>
                                        @if($createdByUser)
                                            <strong>User:</strong> {{ $createdByUser->name ?? 'N/A' }}<br>
                                        @else
                                            <span class="text-muted">User: N/A</span><br>
                                        @endif
                                        @if($createdByBranch)
                                            <strong>Branch:</strong> {{ $createdByBranch->branch_name ?? 'N/A' }}
                                        @else
                                            <span class="text-muted">Branch: N/A</span>
                                        @endif
                                    </small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="edit-delete-action">
                                    <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#editSupplierModal{{ $item->id }}">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    <a class="me-2 p-2 text-success" href="javascript:void(0)" 
                                       onclick="showSupplierLedger({{ $item->id }})" 
                                       title="Ledger Report">
                                        <i data-feather="file-text" class="feather-file-text"></i>
                                    </a>
                                    <a class="me-2 p-2 text-info" href="javascript:void(0)" 
                                       onclick="showEditHistory({{ $item->id }})" 
                                       title="Edit History">
                                        <i data-feather="clock" class="feather-clock"></i>
                                    </a>
                                    <a href="javascript:void(0)"
                                        onclick="confirmDelete('delete-form-{{ $item->id }}')"
                                        class="p-2 text-danger">
                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                    </a>
                                    <!-- Hidden delete form -->
                                    <form id="delete-form-{{ $item->id }}"
                                        action="{{ route('suppliers.delete', $item->id) }}"
                                        method="POST"
                                        style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No suppliers found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $suppliers->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

{{-- Add Modal (Static) --}}
<div class="modal fade" id="addSupplierModal">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Supplier</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @include('admin.suppliers.modals.create-supplier-form')
        </div>
    </div>
</div>

@forelse ($suppliers as $item)
<div class="modal fade" id="editSupplierModal{{ $item->id }}">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Supplier</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @include('admin.suppliers.modals.edit-supplier-form', ['supplier' => $item])
        </div>
    </div>
</div>
@empty
@endforelse

<!-- Edit History Modal -->
<div class="modal fade" id="editHistoryModal" tabindex="-1" aria-labelledby="editHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit History</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editHistoryModalBody">
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Supplier Ledger Modal -->
<div class="modal fade" id="supplierLedgerModal" tabindex="-1" aria-labelledby="supplierLedgerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Supplier Ledger Report</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="supplierLedgerModalBody">
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="whatsappLedgerBtn" onclick="sendLedgerViaWhatsApp()" style="display: none;">
                    <i class="fab fa-whatsapp me-1"></i> Send via WhatsApp
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Image Crop Modal -->
<div class="modal fade" id="imageCropModal" tabindex="-1" aria-labelledby="imageCropModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageCropModalLabel">Crop Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="img-container">
                    <img id="cropImage" src="" alt="Crop Image" style="max-width: 100%; max-height: 500px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="cropImageBtn">Crop & Save</button>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<!-- Cropper.js JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
    // IIFE to avoid global pollution
    (function() {
        // Common functions (shared across modals)
        function updateRemoveButtons(containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;
            const rows = container.querySelectorAll('.name-phone-row');
            rows.forEach((row, index) => {
                const removeBtn = row.querySelector('.remove-row');
                if (removeBtn) {
                    // First row (index 0) should never show remove button
                    // Only show remove button for rows added after the first one
                    if (index === 0) {
                        removeBtn.style.display = 'none';
                    } else {
                        removeBtn.style.display = rows.length > 1 ? 'block' : 'none';
                    }
                }
            });
        }

        function toggleDelete(btn, fieldName) {
            const hiddenInput = btn.closest('.col-md-6').querySelector(`input[name="${fieldName}"]`);
            if (hiddenInput) {
                hiddenInput.value = hiddenInput.value === '0' ? '1' : '0';
                btn.textContent = hiddenInput.value === '1' ? 'Undo Delete' : 'Delete';
                btn.classList.toggle('btn-success', hiddenInput.value === '1');
                btn.classList.toggle('btn-danger', hiddenInput.value !== '1');
            }
            const existingDiv = btn.closest('.existing-file, .existing-image');
            if (existingDiv) existingDiv.style.opacity = hiddenInput && hiddenInput.value === '1' ? '0.5' : '1';
        }

        function resetRecordingUI(inputField, controlBtn, nameCol) {
            // Reset input field
            if (inputField) {
                inputField.style.removeProperty('color');
                inputField.style.removeProperty('textShadow');
                inputField.style.removeProperty('backgroundColor');
                inputField.placeholder = 'Enter name or use mic';
                inputField.value = '';
                // Ensure speech-input class is present
                if (!inputField.classList.contains('speech-input')) {
                    inputField.classList.add('speech-input');
                }
            }
            
            // Reset the Record Voice button to its original state
            if (controlBtn && controlBtn.parentNode) {
                // Create a completely new button to remove all event listeners
                const newBtn = document.createElement('button');
                newBtn.type = 'button';
                newBtn.className = 'btn btn-outline-secondary mic-btn w-100'; // Match original class order
                newBtn.innerHTML = '<i class="fas fa-microphone me-2"></i>Record Voice';
                // Ensure button is enabled
                newBtn.disabled = false;
                // Replace the old button with the new one
                controlBtn.parentNode.replaceChild(newBtn, controlBtn);
            } else if (controlBtn) {
                // Fallback: reset the existing button and remove event listeners
                controlBtn.classList.remove('play-pause-btn');
                controlBtn.classList.add('mic-btn');
                controlBtn.innerHTML = '<i class="fas fa-microphone me-2"></i>Record Voice';
                controlBtn.removeAttribute('style');
                controlBtn.disabled = false;
                controlBtn.onclick = null;
            }
            
            // Remove audio container if it exists
            const audioContainer = nameCol.querySelector('.audio-player-container');
            if (audioContainer) audioContainer.remove();
            
            // Remove hidden voice note input
            const form = document.getElementById('supplierForm');
            if (form) {
                const voiceNoteInput = form.querySelector('input[name="voice_note"]');
                if (voiceNoteInput) voiceNoteInput.remove();
            }
        }

        // Event Delegation for All Modals (click events)
        document.addEventListener('click', function(e) {
            // Add Name & Phone
            if (e.target.closest('#addNamePhone')) {
                const btn = e.target.closest('#addNamePhone');
                const container = btn.closest('.col-12').querySelector('#namePhoneContainer');
                if (!container) return;
                const newRow = document.createElement('div');
                newRow.className = 'row g-3 mb-3 name-phone-row';
                newRow.innerHTML = `
                    <div class="col-md-6">
                        <div class="mb-2">
                            <button type="button" class="btn btn-danger remove-row w-100">
                                <i class="fas fa-trash me-2"></i>Remove
                            </button>
                        </div>
                        <label class="form-label">Name</label>
                        <div class="input-group">
                            <input type="text" name="names[]" class="form-control speech-input" placeholder="Enter name or use mic">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">WhatsApp Number</label>
                        <div class="input-group">
                            <select name="country_codes[]" class="form-select phone-country-code" style="max-width: 150px;">
                                <option value="1">🇺🇸 +1 (US/CA)</option>
                                <option value="44">🇬🇧 +44 (UK)</option>
                                <option value="91">🇮🇳 +91 (India)</option>
                                <option value="92" selected>🇵🇰 +92 (Pakistan)</option>
                                <option value="971">🇦🇪 +971 (UAE)</option>
                                <option value="966">🇸🇦 +966 (Saudi)</option>
                                <option value="974">🇶🇦 +974 (Qatar)</option>
                                <option value="965">🇰🇼 +965 (Kuwait)</option>
                                <option value="973">🇧🇭 +973 (Bahrain)</option>
                                <option value="968">🇴🇲 +968 (Oman)</option>
                                <option value="961">🇱🇧 +961 (Lebanon)</option>
                                <option value="20">🇪🇬 +20 (Egypt)</option>
                                <option value="27">🇿🇦 +27 (South Africa)</option>
                                <option value="49">🇩🇪 +49 (Germany)</option>
                                <option value="33">🇫🇷 +33 (France)</option>
                                <option value="39">🇮🇹 +39 (Italy)</option>
                                <option value="34">🇪🇸 +34 (Spain)</option>
                                <option value="31">🇳🇱 +31 (Netherlands)</option>
                                <option value="32">🇧🇪 +32 (Belgium)</option>
                                <option value="41">🇨🇭 +41 (Switzerland)</option>
                                <option value="43">🇦🇹 +43 (Austria)</option>
                                <option value="86">🇨🇳 +86 (China)</option>
                                <option value="81">🇯🇵 +81 (Japan)</option>
                                <option value="82">🇰🇷 +82 (South Korea)</option>
                                <option value="65">🇸🇬 +65 (Singapore)</option>
                                <option value="60">🇲🇾 +60 (Malaysia)</option>
                                <option value="62">🇮🇩 +62 (Indonesia)</option>
                                <option value="66">🇹🇭 +66 (Thailand)</option>
                                <option value="84">🇻🇳 +84 (Vietnam)</option>
                                <option value="63">🇵🇭 +63 (Philippines)</option>
                                <option value="880">🇧🇩 +880 (Bangladesh)</option>
                                <option value="94">🇱🇰 +94 (Sri Lanka)</option>
                                <option value="95">🇲🇲 +95 (Myanmar)</option>
                                <option value="977">🇳🇵 +977 (Nepal)</option>
                            </select>
                            <input type="text" name="phones[]" class="form-control phone-number-input" placeholder="Enter phone number">
                            <button type="button" class="btn btn-success phone-whatsapp-btn" title="Open WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </button>
                            <button type="button" class="btn btn-primary phone-call-btn" title="Call">
                                <i class="fas fa-phone"></i>
                            </button>
                        </div>
                    </div>
                `;
                container.appendChild(newRow);
                updateRemoveButtons(container.id);
            }

            // Remove Row
            if (e.target.closest('.remove-row')) {
                e.target.closest('.name-phone-row').remove();
                const container = e.target.closest('.col-12').querySelector('#namePhoneContainer');
                if (container) updateRemoveButtons(container.id);
            }

            // Cancel Audio
            const cancelAudioBtn = e.target.closest('.cancel-audio');
            if (cancelAudioBtn) {
                const audioContainer = cancelAudioBtn.closest('.audio-player-container');
                if (audioContainer) {
                    const nameCol = audioContainer.parentElement;
                    const inputGroup = nameCol.querySelector('.input-group');
                    const inputField = inputGroup ? inputGroup.querySelector('input[type="text"]') : null;
                    
                    // Find the Record Voice button in the mb-2 div (above the input-group)
                    const mb2Div = nameCol.querySelector('.mb-2');
                    let controlBtn = mb2Div ? mb2Div.querySelector('.mic-btn') : null;
                    if (!controlBtn && mb2Div) {
                        controlBtn = mb2Div.querySelector('.play-pause-btn');
                    }
                    if (!controlBtn && mb2Div) {
                        // Fallback: find any button in mb-2
                        controlBtn = mb2Div.querySelector('button');
                    }
                    
                    // Remove the audio container first
                    audioContainer.remove();
                    
                    // Remove the hidden file input
                    const form = document.getElementById('supplierForm');
                    if (form) {
                        const voiceNoteInput = form.querySelector('input[name="voice_note"]');
                        if (voiceNoteInput) voiceNoteInput.remove();
                    }
                    
                    // Reset the UI immediately
                    if (inputField && controlBtn && nameCol) {
                        resetRecordingUI(inputField, controlBtn, nameCol);
                    }
                }
            }

            // Delete Buttons
            if (e.target.classList.contains('delete-btn')) {
                const onclickAttr = e.target.getAttribute('onclick');
                const match = onclickAttr ? onclickAttr.match(/'([^']+)'/) : null;
                const fieldName = match ? match[1] : '';
                toggleDelete(e.target, fieldName);
            }

            // Remove Preview Image
            if (e.target.closest('.remove-image-preview')) {
                e.target.closest('div').remove();
                const previewContainer = e.target.closest('#multiple_images_preview');
                if (previewContainer && previewContainer.children.length === 0) {
                    const uploadBox = e.target.closest('.multiple-upload-box');
                    const placeholder = uploadBox.querySelector('.upload-placeholder');
                    const uploadBtn = uploadBox.querySelector('.upload-btn');
                    const existing = uploadBox.querySelector('.existing-images');
                    if (placeholder) placeholder.style.display = 'block';
                    if (uploadBtn) uploadBtn.style.display = 'block';
                    if (previewContainer) previewContainer.classList.add('d-none');
                    if (existing) existing.style.display = 'block';
                }
            }

            // Credit Limit Toggle
            if (e.target.id === 'showCreditLimitOptions') {
                e.preventDefault();
                const defaultDiv = e.target.closest('#creditLimitDefault');
                const optionsDiv = document.getElementById('creditLimitOptions');
                const customRadio = document.getElementById('custom');
                const inputDiv = document.getElementById('custom_limit_input');
                if (defaultDiv) defaultDiv.style.display = 'none';
                if (optionsDiv) optionsDiv.style.display = 'block';
                if (customRadio) customRadio.checked = true;
                if (inputDiv) inputDiv.style.display = 'block';
            }
            if (e.target.id === 'hideCreditLimitOptions') {
                e.preventDefault();
                const optionsDiv = document.getElementById('creditLimitOptions');
                const defaultDiv = document.getElementById('creditLimitDefault');
                const inputDiv = document.getElementById('custom_limit_input');
                if (optionsDiv) optionsDiv.style.display = 'none';
                if (defaultDiv) defaultDiv.style.display = 'block';
                document.querySelectorAll('input[name="credit_limit_type"]').forEach(r => r.checked = false);
                const limitInput = document.querySelector('input[name="credit_limit"]');
                if (limitInput) limitInput.value = '';
                if (inputDiv) inputDiv.style.display = 'none';
            }

            // Description Toggle
            if (e.target.id === 'showDescriptionOptions') {
                e.preventDefault();
                const descriptionOptions = document.getElementById('descriptionOptions');
                if (descriptionOptions) {
                    descriptionOptions.style.display = 'block';
                    const textarea = document.getElementById('description');
                    if (textarea) textarea.focus();
                }
            }
            if (e.target.id === 'hideDescriptionOptions') {
                e.preventDefault();
                const descriptionOptions = document.getElementById('descriptionOptions');
                if (descriptionOptions) {
                    descriptionOptions.style.display = 'none';
                }
            }

            // Get Current Location
            if (e.target.id === 'getCurrentLocation' || e.target.closest('#getCurrentLocation')) {
                e.preventDefault();
                const btn = e.target.closest('#getCurrentLocation') || e.target;
                const addressInput = document.getElementById('location_address');
                const latInput = document.getElementById('location_latitude');
                const lngInput = document.getElementById('location_longitude');
                const linkContainer = document.getElementById('locationLinkContainer');
                const googleMapLink = document.getElementById('locationGoogleMapLink');

                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by your browser.');
                    return;
                }

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        // Store coordinates
                        if (latInput) latInput.value = lat;
                        if (lngInput) lngInput.value = lng;

                        // Update address input with coordinates
                        if (addressInput) {
                            addressInput.value = `${lat}, ${lng}`;
                        }

                        // Update Google Maps link
                        if (googleMapLink) {
                            googleMapLink.href = `https://www.google.com/maps?q=${lat},${lng}`;
                        }
                        if (linkContainer) {
                            linkContainer.style.display = 'block';
                        }

                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-map-marker-alt"></i>';

                        // Try reverse geocoding if Google Maps API is available
                        if (typeof google !== 'undefined' && google.maps && google.maps.Geocoder) {
                            const geocoder = new google.maps.Geocoder();
                            const latlng = { lat: lat, lng: lng };
                            
                            geocoder.geocode({ location: latlng }, function(results, status) {
                                if (status === 'OK' && results[0] && addressInput) {
                                    addressInput.value = results[0].formatted_address;
                                }
                            });
                        } else {
                            // Fallback: Use a free geocoding service
                            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                                .then(response => response.json())
                                .then(data => {
                                    if (data && data.display_name && addressInput) {
                                        addressInput.value = data.display_name;
                                    }
                                })
                                .catch(err => {
                                    console.log('Geocoding error:', err);
                                });
                        }
                    },
                    function(error) {
                        alert('Error getting location: ' + error.message);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-map-marker-alt"></i>';
                    }
                );
            }

            // Clear Location
            if (e.target.id === 'clearLocation') {
                e.preventDefault();
                const addressInput = document.getElementById('location_address');
                const latInput = document.getElementById('location_latitude');
                const lngInput = document.getElementById('location_longitude');
                const linkContainer = document.getElementById('locationLinkContainer');

                if (addressInput) addressInput.value = '';
                if (latInput) latInput.value = '';
                if (lngInput) lngInput.value = '';
                if (linkContainer) linkContainer.style.display = 'none';
            }

            // WhatsApp Button Click
            if (e.target.closest('.phone-whatsapp-btn')) {
                e.preventDefault();
                const btn = e.target.closest('.phone-whatsapp-btn');
                const inputGroup = btn.closest('.input-group');
                const countryCodeSelect = inputGroup ? inputGroup.querySelector('.phone-country-code') : null;
                const phoneInput = inputGroup ? inputGroup.querySelector('.phone-number-input') : null;
                
                if (!phoneInput) return;
                
                const phoneNumber = phoneInput.value.trim();
                if (!phoneNumber) {
                    alert('Please enter a phone number');
                    phoneInput.focus();
                    return;
                }
                
                // Get country code
                let countryCode = '92'; // Default to Pakistan
                if (countryCodeSelect) {
                    countryCode = countryCodeSelect.value || '92';
                }
                
                // Remove any non-digit characters from phone number
                const cleanNumber = phoneNumber.replace(/\D/g, '');
                
                // Create WhatsApp URL
                const whatsappNumber = countryCode + cleanNumber;
                const whatsappUrl = `https://wa.me/${whatsappNumber}`;
                
                // Open WhatsApp
                window.open(whatsappUrl, '_blank');
            }

            // Call Button Click
            if (e.target.closest('.phone-call-btn')) {
                e.preventDefault();
                const btn = e.target.closest('.phone-call-btn');
                const inputGroup = btn.closest('.input-group');
                const countryCodeSelect = inputGroup ? inputGroup.querySelector('.phone-country-code') : null;
                const phoneInput = inputGroup ? inputGroup.querySelector('.phone-number-input') : null;
                
                if (!phoneInput) return;
                
                const phoneNumber = phoneInput.value.trim();
                if (!phoneNumber) {
                    alert('Please enter a phone number');
                    phoneInput.focus();
                    return;
                }
                
                // Get country code
                let countryCode = '92'; // Default to Pakistan
                if (countryCodeSelect) {
                    countryCode = countryCodeSelect.value || '92';
                }
                
                // Remove any non-digit characters from phone number
                const cleanNumber = phoneNumber.replace(/\D/g, '');
                
                // Create tel: URL with country code
                const fullNumber = '+' + countryCode + cleanNumber;
                const telUrl = `tel:${fullNumber}`;
                
                // Initiate call (works on mobile devices)
                window.location.href = telUrl;
            }
        });

        // Auto-detect country code from phone number input
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('phone-number-input')) {
                const phoneInput = e.target;
                const inputGroup = phoneInput.closest('.input-group');
                const countryCodeSelect = inputGroup ? inputGroup.querySelector('.phone-country-code') : null;
                
                if (!countryCodeSelect) return;
                
                let phoneNumber = phoneInput.value.trim();
                
                // Remove spaces, dashes, and other formatting
                const cleanNumber = phoneNumber.replace(/[\s\-\(\)]/g, '');
                
                // Check if number starts with + or 00 (international format)
                if (cleanNumber.startsWith('+')) {
                    // Number starts with +, extract country code
                    const numberWithoutPlus = cleanNumber.substring(1);
                    detectAndSetCountryCode(numberWithoutPlus, countryCodeSelect);
                } else if (cleanNumber.startsWith('00')) {
                    // Number starts with 00, extract country code
                    const numberWithoutDoubleZero = cleanNumber.substring(2);
                    detectAndSetCountryCode(numberWithoutDoubleZero, countryCodeSelect);
                } else if (cleanNumber.length > 0 && /^\d+$/.test(cleanNumber)) {
                    // Check if number starts with a known country code
                    detectAndSetCountryCode(cleanNumber, countryCodeSelect);
                }
            }
        });

        // Function to detect and set country code
        function detectAndSetCountryCode(number, countryCodeSelect) {
            // Country code mapping - common codes and their lengths
            const countryCodes = {
                '1': '1',      // US/Canada
                '44': '44',    // UK
                '91': '91',    // India
                '92': '92',    // Pakistan
                '971': '971',  // UAE
                '966': '966',  // Saudi
                '974': '974',  // Qatar
                '965': '965',  // Kuwait
                '973': '973',  // Bahrain
                '968': '968',  // Oman
                '961': '961',  // Lebanon
                '20': '20',    // Egypt
                '27': '27',    // South Africa
                '49': '49',    // Germany
                '33': '33',    // France
                '39': '39',    // Italy
                '34': '34',    // Spain
                '31': '31',    // Netherlands
                '32': '32',    // Belgium
                '41': '41',    // Switzerland
                '43': '43',    // Austria
                '86': '86',    // China
                '81': '81',    // Japan
                '82': '82',    // South Korea
                '65': '65',    // Singapore
                '60': '60',    // Malaysia
                '62': '62',    // Indonesia
                '66': '66',    // Thailand
                '84': '84',    // Vietnam
                '63': '63',    // Philippines
                '880': '880',  // Bangladesh
                '94': '94',    // Sri Lanka
                '95': '95',    // Myanmar
                '977': '977'   // Nepal
            };

            // Try to match country codes (check longer codes first)
            const sortedCodes = Object.keys(countryCodes).sort((a, b) => b.length - a.length);
            
            for (const code of sortedCodes) {
                if (number.startsWith(code)) {
                    // Check if this is likely a country code (not part of local number)
                    // For most countries, if number starts with country code and is long enough, it's likely international
                    const remainingDigits = number.substring(code.length);
                    
                    // If remaining digits are reasonable for a phone number (7-15 digits)
                    if (remainingDigits.length >= 7 && remainingDigits.length <= 15) {
                        countryCodeSelect.value = code;
                        countryCodeSelect.dispatchEvent(new Event('change'));
                        
                        // Remove country code from input if it was detected
                        const phoneInput = countryCodeSelect.closest('.input-group').querySelector('.phone-number-input');
                        if (phoneInput && (phoneInput.value.startsWith('+') || phoneInput.value.startsWith('00'))) {
                            // Keep the + or 00, but this helps user see the detected code
                        }
                        return;
                    }
                }
            }
        }

        // Microphone Logic (delegated)
        document.addEventListener('click', async function(e) {
            const micBtn = e.target.closest('.mic-btn');
            const playPauseBtn = e.target.closest('.play-pause-btn');
            const controlBtn = micBtn || playPauseBtn;
            if (!controlBtn) return;
            
            // Prevent if button is disabled
            if (controlBtn.disabled) return;
            
            // Find the name input field - it's in the same row as the mic button
            const namePhoneRow = controlBtn.closest('.name-phone-row');
            if (!namePhoneRow) return;
            
            const nameCol = namePhoneRow.querySelector('.col-md-6:first-child, .col-md-5:first-child');
            if (!nameCol) return;
            
            const inputGroup = nameCol.querySelector('.input-group');
            let inputField = inputGroup ? inputGroup.querySelector('input[type="text"].speech-input') : null;
            
            // Fallback: if speech-input class is missing, find any text input and add the class
            if (!inputField && inputGroup) {
                inputField = inputGroup.querySelector('input[type="text"]');
                if (inputField && !inputField.classList.contains('speech-input')) {
                    inputField.classList.add('speech-input');
                }
            }
            
            if (!inputField || !nameCol) return;

            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRecognition || !navigator.mediaDevices) {
                alert('Speech Recognition or Microphone not supported.');
                return;
            }

            let recognition = new SpeechRecognition();
            recognition.continuous = true; // Changed to true for continuous recording
            recognition.interimResults = false;
            recognition.lang = 'en-US';

            let mediaRecorder = null;
            let audioChunks = [];
            let transcript = '';
            let recordingTimer = null;
            let recordingStartTime = null;
            let actualRecordingDuration = 0; // Track actual recording duration
            let timeRemaining = 30; // 30 seconds
            let isRecording = false;

            if (playPauseBtn) {
                const audio = inputGroup.querySelector('audio');
                if (audio) {
                    if (audio.paused) {
                        audio.play();
                        controlBtn.innerHTML = '<i class="fas fa-pause"></i>';
                    } else {
                        audio.pause();
                        controlBtn.innerHTML = '<i class="fas fa-play"></i>';
                    }
                }
                return;
            }

            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                inputField.value = '';
                inputField.style.color = 'transparent';
                inputField.style.textShadow = '0 0 8px rgba(0,0,0,0.5)';
                inputField.placeholder = 'Listening... Speak now (0:30 remaining)';
                const existingAudio = nameCol.querySelector('.audio-player-container');
                if (existingAudio) existingAudio.remove();
                const existingHiddenInput = document.querySelector('input[name="voice_note"]');
                if (existingHiddenInput) existingHiddenInput.remove();
                audioChunks = [];
                mediaRecorder = new MediaRecorder(stream, {
                    mimeType: 'audio/webm;codecs=opus'
                });
                
                // Collect data chunks continuously
                mediaRecorder.ondataavailable = (event) => {
                    if (event.data && event.data.size > 0) {
                        audioChunks.push(event.data);
                    }
                };
                
                // Handle errors in media recorder
                mediaRecorder.onerror = (event) => {
                    console.log('MediaRecorder error:', event.error);
                    // Continue recording if possible
                };
                
                // Function to stop recording
                const stopRecording = () => {
                    if (!isRecording) return;
                    isRecording = false;
                    
                    // Calculate actual recording duration
                    if (recordingStartTime) {
                        actualRecordingDuration = Math.round((Date.now() - recordingStartTime) / 1000);
                    }
                    
                    if (recordingTimer) {
                        clearInterval(recordingTimer);
                        recordingTimer = null;
                    }
                    
                    if (recognition) {
                        try {
                            if (recognition.state === 'listening' || recognition.state === 'running') {
                                recognition.stop();
                            }
                        } catch (err) {
                            console.log('Recognition stop error:', err);
                        }
                    }
                    
                    if (mediaRecorder && mediaRecorder.state === 'recording') {
                        mediaRecorder.stop();
                    }
                    
                    if (stream) {
                        stream.getTracks().forEach(track => track.stop());
                    }
                };
                
                mediaRecorder.onstop = () => {
                    const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                    const audioURL = URL.createObjectURL(audioBlob);
                    
                    // Format actual recording duration
                    const minutes = Math.floor(actualRecordingDuration / 60);
                    const seconds = actualRecordingDuration % 60;
                    const durationText = minutes > 0 
                        ? `${minutes}m ${seconds}s` 
                        : `${seconds}s`;
                    
                    const audioContainer = document.createElement('div');
                    audioContainer.className = 'audio-player-container mt-2';
                    audioContainer.innerHTML = `
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>Recording Duration: <strong>${durationText}</strong>
                            </small>
                            <button type="button" class="btn btn-sm btn-danger cancel-audio">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                        <audio controls class="w-100" preload="metadata">
                            <source src="${audioURL}" type="audio/webm">
                        </audio>
                    `;
                    nameCol.appendChild(audioContainer);
                    
                    const fileInput = document.createElement('input');
                    fileInput.type = 'file';
                    fileInput.name = 'voice_note';
                    fileInput.hidden = true;
                    const file = new File([audioBlob], "voice_note.webm", { type: 'audio/webm' });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                    const form = document.getElementById('supplierForm');
                    if (form) form.appendChild(fileInput);
                    
                    inputField.style.removeProperty('textShadow');
                    inputField.style.color = 'transparent';
                    inputField.style.backgroundColor = 'lightgreen';
                    inputField.placeholder = `Voice transcribed (${durationText} recorded)`;
                    if (transcript.trim()) inputField.value = transcript.trim();
                    
                    controlBtn.innerHTML = '<i class="fas fa-play"></i>';
                    controlBtn.classList.remove('mic-btn');
                    controlBtn.classList.add('play-pause-btn');
                    
                    stream.getTracks().forEach(track => track.stop());
                };
                
                isRecording = true;
                recordingStartTime = Date.now();
                actualRecordingDuration = 0;
                timeRemaining = 30;
                
                // Start media recorder first - this will record for the full duration
                mediaRecorder.start(1000); // Request data every 1 second
                
                // Start speech recognition
                recognition.start();
                
                controlBtn.innerHTML = '<i class="fas fa-stop text-danger"></i>';
                controlBtn.style.backgroundColor = '#dc3545';
                controlBtn.style.color = 'white';
                
                // Update timer every second and ensure recording continues
                recordingTimer = setInterval(() => {
                    if (!isRecording) {
                        clearInterval(recordingTimer);
                        return;
                    }
                    
                    // Update actual recording duration
                    actualRecordingDuration = Math.round((Date.now() - recordingStartTime) / 1000);
                    
                    timeRemaining--;
                    const minutes = Math.floor(timeRemaining / 60);
                    const seconds = timeRemaining % 60;
                    const timeString = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                    
                    // Show both remaining time and elapsed time
                    const elapsedMinutes = Math.floor(actualRecordingDuration / 60);
                    const elapsedSeconds = actualRecordingDuration % 60;
                    const elapsedString = elapsedMinutes > 0 
                        ? `${elapsedMinutes}:${elapsedSeconds.toString().padStart(2, '0')}` 
                        : `0:${elapsedSeconds.toString().padStart(2, '0')}`;
                    
                    inputField.placeholder = `🔴 Recording... (${elapsedString} / ${timeString} remaining)`;
                    
                    // Visual feedback - change background color based on remaining time
                    if (timeRemaining <= 5) {
                        inputField.style.backgroundColor = '#ffebee';
                        inputField.style.textShadow = '0 0 8px rgba(255,0,0,0.8)';
                    } else if (timeRemaining <= 10) {
                        inputField.style.backgroundColor = '#fff3e0';
                        inputField.style.textShadow = '0 0 8px rgba(255,152,0,0.6)';
                    } else {
                        inputField.style.backgroundColor = '#e3f2fd';
                        inputField.style.textShadow = '0 0 8px rgba(33,150,243,0.5)';
                    }
                    
                    // Ensure mediaRecorder is still recording
                    if (mediaRecorder && mediaRecorder.state !== 'recording' && isRecording && timeRemaining > 0) {
                        try {
                            if (mediaRecorder.state === 'inactive') {
                                mediaRecorder.start(1000);
                            }
                        } catch (err) {
                            console.log('MediaRecorder restart error:', err);
                        }
                    }
                    
                    // Restart recognition if it stopped but time remains
                    if (recognition && recognition.state === 'stopped' && isRecording && timeRemaining > 0) {
                        try {
                            recognition.start();
                        } catch (err) {
                            console.log('Recognition auto-restart error:', err);
                        }
                    }
                    
                    // Stop recording after exactly 30 seconds
                    if (timeRemaining <= 0) {
                        clearInterval(recordingTimer);
                        stopRecording();
                        const finalDuration = Math.round((Date.now() - recordingStartTime) / 1000);
                        const finalMinutes = Math.floor(finalDuration / 60);
                        const finalSeconds = finalDuration % 60;
                        const finalDurationText = finalMinutes > 0 
                            ? `${finalMinutes}:${finalSeconds.toString().padStart(2, '0')}` 
                            : `0:${finalSeconds.toString().padStart(2, '0')}`;
                        inputField.placeholder = `✅ Recording completed (${finalDurationText} recorded)`;
                        inputField.style.backgroundColor = 'lightgreen';
                        inputField.style.textShadow = 'none';
                        controlBtn.style.backgroundColor = '';
                        controlBtn.style.color = '';
                    }
                }, 1000);
                
                recognition.onresult = (event) => { 
                    // Accumulate all results for continuous recognition
                    let interimTranscript = '';
                    for (let i = event.resultIndex; i < event.results.length; i++) {
                        const result = event.results[i];
                        if (result.isFinal) {
                            transcript += result[0].transcript + ' ';
                        } else {
                            interimTranscript += result[0].transcript;
                        }
                    }
                };
                
                recognition.onerror = (event) => {
                    // Don't stop on all errors - only stop on critical errors
                    if (event.error === 'no-speech' || event.error === 'audio-capture') {
                        // These are recoverable - try to continue
                        if (isRecording && timeRemaining > 0) {
                            try {
                                setTimeout(() => {
                                    if (isRecording && timeRemaining > 0) {
                                        recognition.start();
                                    }
                                }, 100);
                            } catch (err) {
                                console.log('Recognition restart after error failed:', err);
                            }
                        }
                    } else if (event.error === 'not-allowed' || event.error === 'aborted') {
                        // Critical errors - stop recording
                        alert('Speech error: ' + event.error);
                        stopRecording();
                        resetRecordingUI(inputField, controlBtn, nameCol);
                    }
                    // For other errors, continue recording
                };
                
                recognition.onend = () => {
                    // If recording is still active and time hasn't reached 0, restart recognition
                    if (isRecording && timeRemaining > 0) {
                        try {
                            recognition.start();
                        } catch (err) {
                            console.log('Recognition restart error:', err);
                            // If restart fails, continue with media recorder only
                        }
                    } else {
                        stopRecording();
                    }
                };
                
                // Handle stop button click - prevent event bubbling
                const originalOnClick = controlBtn.onclick;
                controlBtn.onclick = function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    if (isRecording) {
                        stopRecording();
                    }
                    return false;
                };
                
                // Also handle click event on the button to stop recording
                controlBtn.addEventListener('click', function(e) {
                    if (isRecording && e.target.closest('.mic-btn, .play-pause-btn')) {
                        e.stopPropagation();
                        e.preventDefault();
                        stopRecording();
                        return false;
                    }
                }, true);
            } catch (err) {
                alert('Microphone access denied: ' + err.message);
                resetRecordingUI(inputField, controlBtn, nameCol);
            }
        });

        // File Input Previews (delegated)
        document.addEventListener('change', function(e) {
            if (e.target.id === 'profile_img') {
                const file = e.target.files[0];
                const preview = document.getElementById('profile_preview');
                const placeholder = e.target.closest('.profile-upload-box').querySelector('.upload-placeholder');
                const uploadBtn = e.target.closest('.profile-upload-box').querySelector('.upload-btn');
                const existing = document.querySelector('.existing-image');
                
                if (file) {
                    if (file.type.startsWith('image/')) {
                        // For images, open crop modal
                        const reader = new FileReader();
                        reader.onload = function(ev) {
                            // Store original file and input ID
                            window.profileImgOriginalFile = file;
                            window.currentCropInputId = 'profile_img';
                            
                            // Get crop modal elements
                            const cropModalEl = document.getElementById('imageCropModal');
                            const cropImg = document.getElementById('cropImage');
                            
                            if (!cropModalEl || !cropImg) {
                                console.error('Crop modal elements not found');
                                return;
                            }
                            
                            // Set image source
                            cropImg.src = ev.target.result;
                            
                            // Destroy existing cropper if any
                            if (window.profileImgCropper) {
                                window.profileImgCropper.destroy();
                                window.profileImgCropper = null;
                            }
                            
                            // Open crop modal
                            const cropModal = new bootstrap.Modal(cropModalEl);
                            
                            // Initialize cropper when modal is shown
                            const initCropper = function() {
                                if (window.profileImgCropper) {
                                    window.profileImgCropper.destroy();
                                }
                                
                                window.profileImgCropper = new Cropper(cropImg, {
                                    aspectRatio: NaN, // Free aspect ratio
                                    viewMode: 1,
                                    dragMode: 'move',
                                    autoCropArea: 0.8,
                                    restore: false,
                                    guides: true,
                                    center: true,
                                    highlight: false,
                                    cropBoxMovable: true,
                                    cropBoxResizable: true,
                                    toggleDragModeOnDblclick: false,
                                    ready: function() {
                                        // Cropper is ready
                                    }
                                });
                            };
                            
                            // Remove existing event listeners
                            cropModalEl.removeEventListener('shown.bs.modal', initCropper);
                            cropModalEl.removeEventListener('hidden.bs.modal', cleanupCropper);
                            
                            // Add event listeners
                            function cleanupCropper() {
                                if (window.profileImgCropper) {
                                    window.profileImgCropper.destroy();
                                    window.profileImgCropper = null;
                                }
                            }
                            
                            cropModalEl.addEventListener('shown.bs.modal', initCropper, { once: true });
                            cropModalEl.addEventListener('hidden.bs.modal', cleanupCropper, { once: true });
                            
                            // Show modal
                            cropModal.show();
                        };
                        reader.readAsDataURL(file);
                    }
                } else {
                    if (preview) {
                        preview.src = '';
                        preview.style.display = 'none';
                    }
                    if (placeholder) placeholder.classList.remove('d-none');
                    if (uploadBtn) uploadBtn.classList.remove('d-none');
                    if (existing) existing.style.display = 'block';
                }
            }

            if (e.target.id === 'visiting_doc') {
                const file = e.target.files[0];
                const preview = document.getElementById('visiting_preview');
                const imgContainer = document.getElementById('visiting_img_container');
                const fileInfo = document.getElementById('visiting_file_info');
                const filename = document.getElementById('visiting_filename');
                const existing = document.querySelector('.existing-file');
                
                if (file) {
                    if (file.type.startsWith('image/')) {
                        // For images, open crop modal
                        const reader = new FileReader();
                        reader.onload = function(ev) {
                            // Store original file and input ID
                            window.visitingDocOriginalFile = file;
                            window.currentCropInputId = 'visiting_doc';
                            
                            // Get crop modal elements
                            const cropModalEl = document.getElementById('imageCropModal');
                            const cropImg = document.getElementById('cropImage');
                            
                            if (!cropModalEl || !cropImg) {
                                console.error('Crop modal elements not found');
                                return;
                            }
                            
                            // Set image source
                            cropImg.src = ev.target.result;
                            
                            // Destroy existing cropper if any
                            if (window.visitingDocCropper) {
                                window.visitingDocCropper.destroy();
                                window.visitingDocCropper = null;
                            }
                            
                            // Open crop modal
                            const cropModal = new bootstrap.Modal(cropModalEl);
                            
                            // Initialize cropper when modal is shown
                            const initCropper = function() {
                                if (window.visitingDocCropper) {
                                    window.visitingDocCropper.destroy();
                                }
                                
                                window.visitingDocCropper = new Cropper(cropImg, {
                                    aspectRatio: NaN, // Free aspect ratio
                                    viewMode: 1,
                                    dragMode: 'move',
                                    autoCropArea: 0.8,
                                    restore: false,
                                    guides: true,
                                    center: true,
                                    highlight: false,
                                    cropBoxMovable: true,
                                    cropBoxResizable: true,
                                    toggleDragModeOnDblclick: false,
                                    ready: function() {
                                        // Cropper is ready
                                    }
                                });
                            };
                            
                            // Remove existing event listeners
                            cropModalEl.removeEventListener('shown.bs.modal', initCropper);
                            cropModalEl.removeEventListener('hidden.bs.modal', cleanupCropper);
                            
                            // Add event listeners
                            function cleanupCropper() {
                                if (window.visitingDocCropper) {
                                    window.visitingDocCropper.destroy();
                                    window.visitingDocCropper = null;
                                }
                            }
                            
                            cropModalEl.addEventListener('shown.bs.modal', initCropper, { once: true });
                            cropModalEl.addEventListener('hidden.bs.modal', cleanupCropper, { once: true });
                            
                            // Show modal
                            cropModal.show();
                        };
                        reader.readAsDataURL(file);
                    } else {
                        // For non-image files (PDF, DOC, etc.), show file info
                        if (preview) preview.style.display = 'block';
                        if (filename) filename.textContent = file.name;
                        if (imgContainer) imgContainer.style.display = 'none';
                        if (fileInfo) fileInfo.style.display = 'block';
                        if (existing) existing.style.display = 'none';
                        // Show cancel button
                        const cancelBtn = document.getElementById('cancelVisitingDoc');
                        if (cancelBtn) cancelBtn.style.display = 'block';
                    }
                } else {
                    if (preview) preview.style.display = 'none';
                    if (existing) existing.style.display = 'block';
                }
            }

            if (e.target.id === 'multiple_images') {
                const files = e.target.files;
                const previewContainer = document.getElementById('multiple_images_preview');
                const placeholder = e.target.closest('.multiple-upload-box').querySelector('.upload-placeholder');
                const uploadBtn = e.target.closest('.multiple-upload-box').querySelector('.upload-btn');
                const existing = e.target.closest('.multiple-upload-box').querySelector('.existing-images');
                if (files.length > 0) {
                    if (placeholder) placeholder.style.display = 'none';
                    if (uploadBtn) uploadBtn.style.display = 'none';
                    if (existing) existing.style.display = 'none';
                    if (previewContainer) {
                        previewContainer.classList.remove('d-none');
                        previewContainer.innerHTML = '';
                        Array.from(files).forEach((file) => {
                            if (file.type.startsWith('image/')) {
                                const reader = new FileReader();
                                reader.onload = function(ev) {
                                    const div = document.createElement('div');
                                    div.className = 'text-center border rounded p-2 bg-light position-relative';
                                    div.style.width = '150px';
                                    div.style.height = '150px';
                                    div.style.cursor = 'pointer';
                                    div.innerHTML = `
                                        <img src="${ev.target.result}" alt="${file.name}" class="img-fluid rounded" style="max-height: 100px; max-width: 100px; display: block; margin: 0 auto;">
                                        <small class="d-block text-muted mt-1">${file.name}</small>
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-image-preview"><i class="fas fa-trash"></i></button>
                                    `;
                                    previewContainer.appendChild(div);
                                };
                                reader.readAsDataURL(file);
                            }
                        });
                    }
                } else {
                    if (placeholder) placeholder.style.display = 'block';
                    if (uploadBtn) uploadBtn.style.display = 'block';
                    if (existing) existing.style.display = 'block';
                    if (previewContainer) {
                        previewContainer.classList.add('d-none');
                        previewContainer.innerHTML = '';
                    }
                }
            }
        });

        // Credit Limit Radio Toggle
        document.addEventListener('change', function(e) {
            if (e.target.name === 'credit_limit_type') {
                const inputDiv = document.getElementById('custom_limit_input');
                if (inputDiv) inputDiv.style.display = e.target.value === 'custom' ? 'block' : 'none';
            }
        });

        // Password Generation (delegated for add modal)
        document.addEventListener('click', function(e) {
            if (e.target.id === 'generatePassword') {
                const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
                let password = "";
                for (let i = 0; i < 14; i++) {
                    password += charset.charAt(Math.floor(Math.random() * charset.length));
                }
                const passInput = document.getElementById('password');
                if (passInput) passInput.value = password;
            }
        });


        // Form Submission Spinner (delegated for all forms)
        document.addEventListener('submit', function(e) {
            if (e.target.id === 'supplierForm') {
                const submitBtn = e.target.querySelector('button[type="submit"]');
                const spinner = submitBtn ? submitBtn.querySelector('.spinner-border') : null;
                if (spinner) spinner.classList.remove('d-none');
                if (submitBtn) submitBtn.disabled = true;
            }
        });

        // Modal Shown Event (for resets, delegated)
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('shown.bs.modal', function() {
                const modalId = this.id;
                const isAdd = modalId === 'addSupplierModal';
                const form = this.querySelector('#supplierForm');
                if (!form) return;

                if (isAdd) {
                    // Reset for add
                    const genBtn = form.querySelector('#generatePassword');
                    if (genBtn) genBtn.click();
                    const asOfDate = form.querySelector('#as_of_date');
                    if (asOfDate) asOfDate.value = new Date().toLocaleDateString('en-GB');
                    // Reset fields (simplified – full reset as before)
                    form.querySelector('#profile_img').value = '';
                    form.querySelector('#multiple_images').value = '';
                    form.querySelector('#visiting_doc').value = '';
                    const preview = form.querySelector('#profile_preview');
                    if (preview) preview.style.display = 'none';
                    // ... (add other resets as in previous script)
                    updateRemoveButtons('namePhoneContainer');
                    // Reset credit limit
                    const optionsDiv = form.querySelector('#creditLimitOptions');
                    const defaultDiv = form.querySelector('#creditLimitDefault');
                    if (optionsDiv) optionsDiv.style.display = 'none';
                    if (defaultDiv) defaultDiv.style.display = 'block';
                    form.querySelectorAll('input[name="credit_limit_type"]').forEach(r => r.checked = false);
                    form.querySelector('input[name="credit_limit"]').value = '';
                }
            });
        });
    })();

    // Fix cursor crosshair issue - continuously monitor and remove it (AGGRESSIVE)
    (function() {
        function removeCrosshairCursor() {
            try {
                const body = document.body;
                if (!body) return;
                
                // Check and remove cursor: crosshair from style property
                if (body.style && (body.style.cursor === 'crosshair' || body.style.cursor === 'crosshair')) {
                    body.style.cursor = '';
                    body.style.removeProperty('cursor');
                }
                
                // Check and remove from inline style attribute
                const styleAttr = body.getAttribute('style');
                if (styleAttr) {
                    if (styleAttr.includes('cursor: crosshair') || styleAttr.includes('cursor:crosshair')) {
                        let newStyle = styleAttr
                            .replace(/cursor\s*:\s*crosshair\s*;?/gi, '')
                            .replace(/cursor\s*:\s*crosshair/gi, '')
                            .replace(/;\s*;/g, ';')
                            .trim();
                        if (newStyle.endsWith(';')) {
                            newStyle = newStyle.slice(0, -1);
                        }
                        if (newStyle) {
                            body.setAttribute('style', newStyle);
                        } else {
                            body.removeAttribute('style');
                        }
                        body.style.cursor = '';
                    }
                }
                
                // Also remove data-cursor-element-id if it exists (might be from extension)
                if (body.hasAttribute('data-cursor-element-id')) {
                    // Don't remove it, but ensure cursor is still default
                    body.style.cursor = 'default';
                }
            } catch(e) {
                console.error('Error fixing cursor:', e);
            }
        }
        
        // Remove immediately
        removeCrosshairCursor();
        
        // Remove on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', removeCrosshairCursor);
        } else {
            removeCrosshairCursor();
        }
        
        // Use MutationObserver to watch for style changes
        let observer = null;
        function startObserver() {
            if (observer) return; // Already started
            
            observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes') {
                        if (mutation.attributeName === 'style' || mutation.attributeName === 'data-cursor-element-id') {
                            removeCrosshairCursor();
                        }
                    }
                });
            });
            
            if (document.body) {
                observer.observe(document.body, {
                    attributes: true,
                    attributeFilter: ['style', 'data-cursor-element-id'],
                    attributeOldValue: false
                });
            }
        }
        
        // Start observing
        if (document.body) {
            startObserver();
        } else {
            document.addEventListener('DOMContentLoaded', startObserver);
        }
        
        // Function to remove DuckDuckGo header
        function removeDuckDuckGoHeader() {
            try {
                const headerWrapper = document.getElementById('header_wrapper');
                if (headerWrapper) {
                    headerWrapper.remove();
                    headerWrapper.style.display = 'none';
                    headerWrapper.style.visibility = 'hidden';
                }
                
                const headerWraps = document.querySelectorAll('.header-wrap, .js-header-wrap, [data-testid="header"]');
                headerWraps.forEach(function(el) {
                    if (el && (el.id === 'header_wrapper' || el.textContent.includes('DuckDuckGo'))) {
                        el.remove();
                        el.style.display = 'none';
                    }
                });
            } catch(e) {}
        }
        
        // Check periodically as a fallback (every 500ms to reduce CPU usage)
        setInterval(function() {
            removeCrosshairCursor();
            removeDuckDuckGoHeader();
        }, 500);
        
        // Remove DuckDuckGo header immediately
        removeDuckDuckGoHeader();
        
        // Also intercept style.setProperty if possible
        if (document.body && document.body.style) {
            const originalSetProperty = CSSStyleDeclaration.prototype.setProperty;
            CSSStyleDeclaration.prototype.setProperty = function(property, value, priority) {
                if (property === 'cursor' && value === 'crosshair') {
                    return; // Block setting cursor to crosshair
                }
                return originalSetProperty.call(this, property, value, priority);
            };
        }
    })();

    // Crop Image Button Handler (outside IIFE for global access)
    document.addEventListener('DOMContentLoaded', function() {
        const cropImageBtn = document.getElementById('cropImageBtn');
        if (cropImageBtn) {
            cropImageBtn.addEventListener('click', function() {
                const currentInputId = window.currentCropInputId;
                let cropper = null;
                let originalFile = null;
                
                // Determine which cropper to use based on current input
                if (currentInputId === 'profile_img') {
                    cropper = window.profileImgCropper;
                    originalFile = window.profileImgOriginalFile;
                } else if (currentInputId === 'visiting_doc') {
                    cropper = window.visitingDocCropper;
                    originalFile = window.visitingDocOriginalFile;
                }
                
                if (cropper) {
                    // Get cropped canvas
                    const canvas = cropper.getCroppedCanvas({
                        width: 800,
                        height: 600,
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });
                    
                    // Convert canvas to blob
                    canvas.toBlob(function(blob) {
                        if (!blob) {
                            console.error('Failed to create blob from canvas');
                            return;
                        }
                        
                        // Create a new File object from the blob
                        const fileName = originalFile ? originalFile.name : 'cropped_image.jpg';
                        const file = new File([blob], fileName, { type: 'image/jpeg' });
                        
                        // Create a new FileList with the cropped file
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        
                        // Handle based on input type
                        if (currentInputId === 'profile_img') {
                            const profileInput = document.getElementById('profile_img');
                            const preview = document.getElementById('profile_preview');
                            const placeholder = document.querySelector('.profile-upload-box .upload-placeholder');
                            const uploadBtn = document.querySelector('.profile-upload-box .upload-btn');
                            
                            if (profileInput) {
                                profileInput.files = dataTransfer.files;
                            }
                            
                            // Update preview
                            const reader = new FileReader();
                            reader.onload = function(ev) {
                                if (preview) {
                                    preview.src = ev.target.result;
                                    preview.style.display = 'block';
                                }
                                // Show cancel button
                                const cancelBtn = document.getElementById('cancelProfileImg');
                                if (cancelBtn) cancelBtn.style.display = 'block';
                                
                                if (placeholder) placeholder.style.display = 'none';
                                if (uploadBtn) uploadBtn.style.display = 'none';
                            };
                            reader.readAsDataURL(file);
                            
                            // Clean up
                            if (cropper) {
                                cropper.destroy();
                                window.profileImgCropper = null;
                            }
                            window.profileImgOriginalFile = null;
                            
                        } else if (currentInputId === 'visiting_doc') {
                            const visitingDocInput = document.getElementById('visiting_doc');
                            const preview = document.getElementById('visiting_preview');
                            const imgContainer = document.getElementById('visiting_img_container');
                            const fileInfo = document.getElementById('visiting_file_info');
                            const visitingImg = document.getElementById('visiting_img');
                            
                            if (visitingDocInput) {
                                visitingDocInput.files = dataTransfer.files;
                            }
                            
                            // Update preview
                            const reader = new FileReader();
                            reader.onload = function(ev) {
                                if (visitingImg) visitingImg.src = ev.target.result;
                                if (imgContainer) imgContainer.style.display = 'block';
                                if (fileInfo) fileInfo.style.display = 'none';
                                if (preview) preview.style.display = 'block';
                                // Show cancel button
                                const cancelBtn = document.getElementById('cancelVisitingDoc');
                                if (cancelBtn) cancelBtn.style.display = 'block';
                            };
                            reader.readAsDataURL(file);
                            
                            // Clean up
                            if (cropper) {
                                cropper.destroy();
                                window.visitingDocCropper = null;
                            }
                            window.visitingDocOriginalFile = null;
                        }
                        
                        // Close modal
                        const cropModalEl = document.getElementById('imageCropModal');
                        if (cropModalEl) {
                            const cropModal = bootstrap.Modal.getInstance(cropModalEl);
                            if (cropModal) {
                                cropModal.hide();
                            }
                        }
                        
                        // Clear current input ID
                        window.currentCropInputId = null;
                    }, 'image/jpeg', 0.9);
                } else {
                    console.error('Cropper instance not found');
                }
            });
        }
    });

    // Initialize Select2 for country code dropdowns to make them searchable
    function initializeCountryCodeSelect2() {
        // Initialize existing dropdowns
        document.querySelectorAll('.phone-country-code').forEach(function(select) {
            if (!$(select).hasClass('select2-hidden-accessible')) {
                $(select).select2({
                    placeholder: 'Select Country Code',
                    allowClear: false,
                    width: '100%',
                    minimumResultsForSearch: 0, // Always show search box
                    matcher: function(params, data) {
                        // If no search term, show all options
                        if (!params.term || params.term === '') {
                            return data;
                        }
                        
                        // Search in country code (value) and text
                        const searchTerm = params.term.toUpperCase();
                        const optionText = data.text.toUpperCase();
                        const optionValue = data.id.toString();
                        
                        // Check if search term matches country code or country name
                        if (optionValue.includes(searchTerm) || optionText.includes(searchTerm)) {
                            return data;
                        }
                        
                        return null;
                    }
                });
                
                // Auto-focus search input when dropdown opens
                $(select).on('select2:open', function() {
                    setTimeout(function() {
                        const searchInput = $('.select2-container--open .select2-search__field');
                        if (searchInput.length) {
                            searchInput.focus();
                        }
                    }, 100);
                });
            }
        });
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeCountryCodeSelect2();
        
        // Date Filter Functionality
        const startDateFilter = document.getElementById('startDateFilter');
        const endDateFilter = document.getElementById('endDateFilter');
        const statusFilters = document.querySelectorAll('.status-filter');
        let currentStatus = 'all';
        
        // Function to filter table by date and status
        function filterTable() {
            const startDate = startDateFilter ? startDateFilter.value : '';
            const endDate = endDateFilter ? endDateFilter.value : '';
            const table = document.getElementById('searchableTable');
            const rows = table ? table.querySelectorAll('tbody tr') : [];
            
            rows.forEach(function(row) {
                let showRow = true;
                
                // Filter by date (check created date column - 6th column)
                const dateCell = row.querySelector('td:nth-child(6)');
                if (dateCell && (startDate || endDate)) {
                    const dateText = dateCell.textContent.trim();
                    // Extract date from format "Date: DD/MM/YYYY"
                    const dateMatch = dateText.match(/(\d{2})\/(\d{2})\/(\d{4})/);
                    if (dateMatch) {
                        const rowDate = new Date(dateMatch[3], dateMatch[2] - 1, dateMatch[1]);
                        
                        if (startDate) {
                            const start = new Date(startDate);
                            start.setHours(0, 0, 0, 0);
                            if (rowDate < start) {
                                showRow = false;
                            }
                        }
                        
                        if (endDate && showRow) {
                            const end = new Date(endDate);
                            end.setHours(23, 59, 59, 999);
                            if (rowDate > end) {
                                showRow = false;
                            }
                        }
                    } else {
                        // If date format not found, hide row if date filter is active
                        if (startDate || endDate) {
                            showRow = false;
                        }
                    }
                }
                
                // Filter by status (if status filter is active)
                if (showRow && currentStatus !== 'all') {
                    // Check if row has status indicator (you may need to add data-status attribute to rows)
                    // For now, we'll check if the row should be shown based on status
                    // This assumes status is stored somewhere in the row
                    const rowStatus = row.getAttribute('data-status') || 'active';
                    if (rowStatus !== currentStatus) {
                        showRow = false;
                    }
                }
                
                // Show or hide row
                row.style.display = showRow ? '' : 'none';
            });
        }
        
        // Add event listeners for date filters
        if (startDateFilter) {
            startDateFilter.addEventListener('change', filterTable);
            // Open calendar on click/focus
            startDateFilter.addEventListener('click', function() {
                this.showPicker();
            });
            startDateFilter.addEventListener('focus', function() {
                this.showPicker();
            });
        }
        
        if (endDateFilter) {
            endDateFilter.addEventListener('change', filterTable);
            // Open calendar on click/focus
            endDateFilter.addEventListener('click', function() {
                this.showPicker();
            });
            endDateFilter.addEventListener('focus', function() {
                this.showPicker();
            });
        }
        
        // Add event listeners for status filters (old dropdown - keeping for compatibility)
        statusFilters.forEach(function(filter) {
            filter.addEventListener('click', function(e) {
                e.preventDefault();
                currentStatus = this.getAttribute('data-status') || 'all';
                
                // Update dropdown text
                const dropdownToggle = document.querySelector('.dropdown-toggle');
                if (dropdownToggle) {
                    const statusText = currentStatus === 'all' ? 'Status' : currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1);
                    dropdownToggle.textContent = statusText;
                }
                
                // Close dropdown
                const dropdown = this.closest('.dropdown-menu');
                if (dropdown) {
                    const bsDropdown = bootstrap.Dropdown.getInstance(dropdown.previousElementSibling);
                    if (bsDropdown) {
                        bsDropdown.hide();
                    }
                }
                
                filterTable();
            });
        });
        
        // Status Switch Button Functionality
        const statusFilterCheckbox = document.getElementById('status-filter');
        if (statusFilterCheckbox) {
            statusFilterCheckbox.addEventListener('change', function() {
                // When checked (ON) - show only active suppliers
                // When unchecked (OFF) - show all suppliers
                if (this.checked) {
                    currentStatus = 'active';
                } else {
                    currentStatus = 'all';
                }
                filterTable();
            });
        }
        
        // Also filter when search input changes (combine with date/status filters)
        const tableSearch = document.getElementById('tableSearch');
        if (tableSearch) {
            tableSearch.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                const table = document.getElementById('searchableTable');
                const rows = table ? table.querySelectorAll('tbody tr') : [];
                
                rows.forEach(function(row) {
                    // First check date and status filters
                    let showRow = true;
                    
                    const startDate = startDateFilter ? startDateFilter.value : '';
                    const endDate = endDateFilter ? endDateFilter.value : '';
                    
                    // Filter by date
                    const dateCell = row.querySelector('td:nth-child(6)');
                    if (dateCell && (startDate || endDate)) {
                        const dateText = dateCell.textContent.trim();
                        const dateMatch = dateText.match(/(\d{2})\/(\d{2})\/(\d{4})/);
                        if (dateMatch) {
                            const rowDate = new Date(dateMatch[3], dateMatch[2] - 1, dateMatch[1]);
                            
                            if (startDate) {
                                const start = new Date(startDate);
                                start.setHours(0, 0, 0, 0);
                                if (rowDate < start) {
                                    showRow = false;
                                }
                            }
                            
                            if (endDate && showRow) {
                                const end = new Date(endDate);
                                end.setHours(23, 59, 59, 999);
                                if (rowDate > end) {
                                    showRow = false;
                                }
                            }
                        } else {
                            if (startDate || endDate) {
                                showRow = false;
                            }
                        }
                    }
                    
                    // Filter by status
                    if (showRow && currentStatus !== 'all') {
                        const rowStatus = row.getAttribute('data-status') || 'active';
                        if (rowStatus !== currentStatus) {
                            showRow = false;
                        }
                    }
                    
                    // Then check search filter
                    if (showRow && filter) {
                        const cells = row.getElementsByTagName('td');
                        let match = false;
                        for (let j = 0; j < cells.length; j++) {
                            const cellText = cells[j].textContent.toLowerCase();
                            if (cellText.includes(filter)) {
                                match = true;
                                break;
                            }
                        }
                        showRow = match;
                    }
                    
                    row.style.display = showRow ? '' : 'none';
                });
            });
        }
    });

    // Re-initialize when modal opens (for dynamically added rows)
    const addSupplierModal = document.getElementById('addSupplierModal');
    if (addSupplierModal) {
        addSupplierModal.addEventListener('shown.bs.modal', function() {
            setTimeout(function() {
                initializeCountryCodeSelect2();
            }, 100);
        });
    }

    // Auto-focus for all select elements when clicked/opened
    document.addEventListener('click', function(e) {
        // Handle select element click - auto focus (prevent default to avoid unwanted actions)
        if (e.target.tagName === 'SELECT' || e.target.closest('select')) {
            const select = e.target.tagName === 'SELECT' ? e.target : e.target.closest('select');
            if (select && !select.classList.contains('phone-country-code')) {
                // Stop propagation to prevent triggering other events
                e.stopPropagation();
                // For regular select elements, focus them
                setTimeout(function() {
                    select.focus();
                }, 50);
            }
        }
        
        // Re-initialize when new phone row is added
        if (e.target.closest('#addNamePhone')) {
            setTimeout(function() {
                initializeCountryCodeSelect2();
            }, 100);
        }
        
        // Cancel Visiting Document
        if (e.target.closest('#cancelVisitingDoc')) {
            e.preventDefault();
            e.stopPropagation();
            
            const visitingInput = document.getElementById('visiting_doc');
            const preview = document.getElementById('visiting_preview');
            const imgContainer = document.getElementById('visiting_img_container');
            const fileInfo = document.getElementById('visiting_file_info');
            const cancelBtn = document.getElementById('cancelVisitingDoc');
            
            // Clear file input
            if (visitingInput) {
                visitingInput.value = '';
                visitingInput.files = null;
            }
            
            // Hide preview
            if (preview) preview.style.display = 'none';
            if (imgContainer) imgContainer.style.display = 'none';
            if (fileInfo) fileInfo.style.display = 'none';
            if (cancelBtn) cancelBtn.style.display = 'none';
            
            // Clear cropper if exists
            if (window.visitingDocCropper) {
                window.visitingDocCropper.destroy();
                window.visitingDocCropper = null;
            }
            window.visitingDocOriginalFile = null;
        }
        
        // Cancel Profile Image
        if (e.target.closest('#cancelProfileImg')) {
            e.preventDefault();
            e.stopPropagation();
            
            const profileInput = document.getElementById('profile_img');
            const preview = document.getElementById('profile_preview');
            const placeholder = document.querySelector('.profile-upload-box .upload-placeholder');
            const uploadBtn = document.querySelector('.profile-upload-box .upload-btn');
            const cancelBtn = document.getElementById('cancelProfileImg');
            
            // Clear file input
            if (profileInput) {
                profileInput.value = '';
                profileInput.files = null;
            }
            
            // Hide preview and show placeholder
            if (preview) preview.style.display = 'none';
            if (placeholder) placeholder.style.display = 'block';
            if (uploadBtn) uploadBtn.style.display = 'block';
            if (cancelBtn) cancelBtn.style.display = 'none';
            
            // Clear cropper if exists
            if (window.profileImgCropper) {
                window.profileImgCropper.destroy();
                window.profileImgCropper = null;
            }
            window.profileImgOriginalFile = null;
        }
    });
    
    // Supplier Ledger Function
    function showSupplierLedger(supplierId) {
        // Show loading
        const modalBody = document.getElementById('supplierLedgerModalBody');
        if (modalBody) {
            modalBody.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        }
        
        // Open modal
        const modalElement = document.getElementById('supplierLedgerModal');
        if (!modalElement) return;
        
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        
        // Fetch ledger data
        fetch(`/suppliers/${supplierId}/ledger`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (response.status === 401) {
                // Don't redirect, just show error
                if (modalBody) modalBody.innerHTML = '<div class="alert alert-danger text-center">Session expired. Please refresh the page.</div>';
                return;
            }
            return response.json();
        })
        .then(data => {
            if (!data) return; // Exit if 401 was handled
            if (data.success) {
                // Store supplierId and phone in modal body for later use
                if (modalBody) {
                    modalBody.setAttribute('data-supplier-id', supplierId);
                    modalBody.setAttribute('data-supplier-phone', data.supplier.phone);
                    modalBody.setAttribute('data-supplier-name', data.supplier.name);
                }
                
                // Show WhatsApp button
                const whatsappBtn = document.getElementById('whatsappLedgerBtn');
                if (whatsappBtn && data.supplier.phone && data.supplier.phone !== 'N/A') {
                    whatsappBtn.style.display = 'inline-block';
                }
                
                let html = '<div class="ledger-report">';
                
                // Supplier Info
                html += '<div class="row mb-4">';
                html += '<div class="col-md-6">';
                html += '<h5 class="mb-3">Supplier Information</h5>';
                html += '<table class="table table-bordered">';
                html += `<tr><th width="40%">Name:</th><td>${data.supplier.name}</td></tr>`;
                html += `<tr><th>Email:</th><td>${data.supplier.email}</td></tr>`;
                html += `<tr><th>Phone:</th><td>${data.supplier.phone}</td></tr>`;
                html += '</table>';
                html += '</div>';
                html += '<div class="col-md-6">';
                html += '<h5 class="mb-3">Balance Summary</h5>';
                html += '<table class="table table-bordered">';
                html += `<tr><th width="40%">Opening Balance:</th><td class="fw-bold">${data.opening_balance}</td></tr>`;
                html += `<tr><th>Total Debit:</th><td class="text-danger">${data.total_debit}</td></tr>`;
                html += `<tr><th>Total Credit:</th><td class="text-success">${data.total_credit}</td></tr>`;
                html += `<tr><th>Ending Balance:</th><td class="fw-bold text-primary fs-5">${data.ending_balance}</td></tr>`;
                html += `<tr><th>Balance Type:</th><td>${data.balance_type == 'pay' ? 'To Pay (We Owe Supplier)' : 'To Receive (Supplier Owes)'}</td></tr>`;
                html += '</table>';
                html += '</div>';
                html += '</div>';
                
                // Transactions Table
                html += '<div class="d-flex justify-content-between align-items-center mb-3">';
                html += '<h5 class="mb-0">Transaction Details</h5>';
                html += `<button type="button" class="btn btn-primary btn-sm" onclick="toggleTransactionDetails(${supplierId})" id="toggleDetailsBtn">`;
                html += '<i class="fas fa-list-alt me-1"></i> Show Detail History';
                html += '</button>';
                html += '</div>';
                html += '<div class="table-responsive">';
                html += '<table class="table table-bordered table-hover table-striped">';
                html += '<thead class="table-light">';
                html += '<tr>';
                html += '<th>Date</th>';
                html += '<th>Time</th>';
                html += '<th>Type</th>';
                html += '<th>Reference/Bill</th>';
                html += '<th>History Purchase Bill</th>';
                html += '<th>Branch</th>';
                html += '<th class="text-end">Debit</th>';
                html += '<th class="text-end">Credit</th>';
                html += '<th class="text-end">Balance</th>';
                html += '</tr>';
                html += '</thead>';
                html += '<tbody>';
                
                // Opening Balance Row
                html += '<tr class="table-info">';
                html += '<td colspan="6" class="fw-bold"><strong>Opening Balance</strong></td>';
                html += '<td class="text-end">-</td>';
                html += '<td class="text-end">-</td>';
                html += `<td class="text-end fw-bold">${data.opening_balance}</td>`;
                html += '</tr>';
                
                if (data.transactions.length > 0) {
                    data.transactions.forEach(function(trans) {
                        html += '<tr>';
                        html += `<td>${trans.date}</td>`;
                        html += `<td>${trans.time}</td>`;
                        html += `<td><span class="badge bg-warning">${trans.type}</span></td>`;
                        if (trans.purchase_id) {
                            html += `<td><a href="javascript:void(0)" class="text-primary text-decoration-underline" onclick="showPurchaseDetail(${trans.purchase_id})" title="Click to view purchase details">${trans.reference}</a></td>`;
                        } else {
                            html += `<td>${trans.reference}</td>`;
                        }
                        html += `<td>${trans.description}</td>`;
                        html += `<td>${trans.branch}</td>`;
                        html += `<td class="text-end text-danger">${parseFloat(trans.debit).toFixed(2)}</td>`;
                        html += `<td class="text-end text-success">${parseFloat(trans.credit).toFixed(2)}</td>`;
                        html += `<td class="text-end fw-bold">${parseFloat(trans.balance).toFixed(2)}</td>`;
                        html += '</tr>';
                    });
                } else {
                    html += '<tr><td colspan="9" class="text-center text-muted">No transactions found</td></tr>';
                }
                
                html += '</tbody>';
                html += '<tfoot class="table-light">';
                html += '<tr>';
                html += '<th colspan="6" class="text-end">Totals:</th>';
                html += `<th class="text-end text-danger">${data.total_debit}</th>`;
                html += `<th class="text-end text-success">${data.total_credit}</th>`;
                html += `<th class="text-end fw-bold">${data.ending_balance}</th>`;
                html += '</tr>';
                html += '</tfoot>';
                html += '</table>';
                html += '</div>';
                
                // Transaction Details Section (initially hidden)
                html += '<div id="transactionDetailsSection" style="display: none; margin-top: 20px;">';
                html += '<h5 class="mb-3">Purchase Detail History</h5>';
                html += '<div id="transactionDetailsContent">';
                html += '<div class="text-center p-4">';
                html += '<div class="spinner-border text-primary" role="status">';
                html += '<span class="visually-hidden">Loading...</span>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                
                html += '</div>';
                
                if (modalBody) modalBody.innerHTML = html;
            } else {
                if (modalBody) modalBody.innerHTML = '<div class="alert alert-danger text-center">Error loading ledger data. Please try again.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (modalBody) modalBody.innerHTML = '<div class="alert alert-danger text-center">Error loading ledger data. Please try again.</div>';
        });
    }
    
    // Send Ledger via WhatsApp Function
    function sendLedgerViaWhatsApp() {
        const modalBody = document.getElementById('supplierLedgerModalBody');
        if (!modalBody) {
            alert('Modal not found. Please refresh and try again.');
            return;
        }
        
        const supplierId = modalBody.getAttribute('data-supplier-id');
        let phone = modalBody.getAttribute('data-supplier-phone');
        const supplierName = modalBody.getAttribute('data-supplier-name');
        
        // If phone not found in attribute, try to get from the table
        if (!phone || phone === 'N/A' || phone.trim() === '') {
            // Try multiple selectors to find phone number
            const phoneSelectors = [
                'table.table-bordered tbody tr:nth-child(3) td',
                '.ledger-report table.table-bordered tbody tr:has(th:contains("Phone")) td',
                'table tbody tr td:contains("03")',
            ];
            
            for (let selector of phoneSelectors) {
                const phoneCell = modalBody.querySelector(selector);
                if (phoneCell) {
                    phone = phoneCell.textContent.trim();
                    if (phone && phone !== 'N/A' && phone.length > 5) {
                        break;
                    }
                }
            }
            
            // If still not found, search all table cells for phone pattern
            if (!phone || phone === 'N/A') {
                const allCells = modalBody.querySelectorAll('table.table-bordered tbody tr td');
                for (let cell of allCells) {
                    const text = cell.textContent.trim();
                    // Match phone numbers starting with 0 and having 11 digits (03001234569)
                    if (text && /^0\d{10}$/.test(text)) {
                        phone = text;
                        break;
                    }
                }
            }
        }
        
        if (!phone || phone === 'N/A' || phone.trim() === '') {
            alert('Phone number not available for this supplier.');
            return;
        }
        
        // Clean phone number (remove spaces, dashes, brackets, etc.)
        let cleanPhone = phone.replace(/[\s\-\(\)]/g, '').trim();
        
        // Remove leading 0 and add country code if needed
        if (cleanPhone.startsWith('0')) {
            cleanPhone = '92' + cleanPhone.substring(1); // Pakistan country code
        } else if (!cleanPhone.startsWith('92') && !cleanPhone.startsWith('+')) {
            cleanPhone = '92' + cleanPhone;
        }
        
        // Remove + if present
        cleanPhone = cleanPhone.replace(/^\+/, '');
        
        // Validate phone number (should be digits only and at least 10 digits)
        if (!/^\d+$/.test(cleanPhone) || cleanPhone.length < 10) {
            alert('Invalid phone number format: ' + phone + '\nCleaned: ' + cleanPhone);
            return;
        }
        
        // Generate PDF URL
        const pdfUrl = `/suppliers/${supplierId}/ledger-pdf`;
        const message = encodeURIComponent(`Hello ${supplierName || 'Supplier'},\n\nPlease find your Supplier Ledger Report.\n\nThank you!`);
        
        // Create WhatsApp URL
        const whatsappUrl = `https://wa.me/${cleanPhone}?text=${message}`;
        
        // Debug: Show WhatsApp URL in console
        console.log('Opening WhatsApp:', whatsappUrl);
        console.log('Phone:', phone, '-> Cleaned:', cleanPhone);
        
        // Open PDF in new tab first (non-blocking)
        if (supplierId) {
            setTimeout(() => {
                window.open(pdfUrl, '_blank');
            }, 100);
        }
        
        // Open WhatsApp - use window.open in new tab to avoid redirecting away from the page
        window.open(whatsappUrl, '_blank');
    }
    
    // Toggle Transaction Details Function
    function toggleTransactionDetails(supplierId) {
        const detailsSection = document.getElementById('transactionDetailsSection');
        const toggleBtn = document.getElementById('toggleDetailsBtn');
        const detailsContent = document.getElementById('transactionDetailsContent');
        
        if (!detailsSection) {
            console.error('transactionDetailsSection not found');
            return;
        }
        
        if (!toggleBtn) {
            console.error('toggleDetailsBtn not found');
            return;
        }
        
        // Get current display state
        const isHidden = detailsSection.style.display === 'none' || detailsSection.style.display === '' || window.getComputedStyle(detailsSection).display === 'none';
        
        // Toggle visibility
        if (isHidden) {
            // Show details
            detailsSection.style.display = 'block';
            toggleBtn.innerHTML = '<i class="fas fa-eye-slash me-1"></i> Hide Detail History';
            
            // Load details if not already loaded
            if (detailsContent && (detailsContent.innerHTML.includes('spinner-border') || detailsContent.innerHTML.trim() === '' || detailsContent.innerHTML.includes('Loading'))) {
                loadTransactionDetails(supplierId);
            }
        } else {
            // Hide details
            detailsSection.style.display = 'none';
            toggleBtn.innerHTML = '<i class="fas fa-list-alt me-1"></i> Show Detail History';
        }
    }
    
    // Load Transaction Details Function
    function loadTransactionDetails(supplierId) {
        const detailsContent = document.getElementById('transactionDetailsContent');
        if (!detailsContent) return;
        
        // Fetch detail history
        fetch(`/suppliers/${supplierId}/purchase-detail-history`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (response.status === 401) {
                // Don't redirect, just show error
                if (modalBody) modalBody.innerHTML = '<div class="alert alert-danger text-center">Session expired. Please refresh the page.</div>';
                return;
            }
            return response.json();
        })
        .then(data => {
            if (!data) return; // Exit if 401 was handled
            if (data.success) {
                let html = '';
                
                if (data.detail_history.length > 0) {
                    data.detail_history.forEach(function(purchase, index) {
                        html += '<div class="card mb-4">';
                        html += '<div class="card-header bg-light">';
                        html += '<div class="row">';
                        html += '<div class="col-md-6">';
                        html += `<h6 class="mb-0"><strong>Invoice:</strong> ${purchase.invoice_no}</h6>`;
                        html += `<small class="text-muted"><strong>Reference:</strong> ${purchase.reference}</small>`;
                        html += '</div>';
                        html += '<div class="col-md-6 text-end">';
                        html += `<small><strong>Date:</strong> ${purchase.date} | <strong>Time:</strong> ${purchase.time}</small><br>`;
                        html += `<small><strong>Branch:</strong> ${purchase.branch} | <strong>Status:</strong> <span class="badge bg-info">${purchase.status}</span></small>`;
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        
                        html += '<div class="card-body">';
                        html += '<h6 class="mb-3">Items:</h6>';
                        html += '<div class="table-responsive">';
                        html += '<table class="table table-bordered table-sm">';
                        html += '<thead class="table-light">';
                        html += '<tr>';
                        html += '<th>#</th>';
                        html += '<th>Item Name</th>';
                        html += '<th>Barcode</th>';
                        html += '<th class="text-end">Quantity</th>';
                        html += '<th class="text-end">Rate</th>';
                        html += '<th class="text-end">Discount</th>';
                        html += '<th class="text-end">Tax %</th>';
                        html += '<th class="text-end">Tax Amount</th>';
                        html += '<th class="text-end">Unit Cost</th>';
                        html += '<th class="text-end">Total Cost</th>';
                        html += '</tr>';
                        html += '</thead>';
                        html += '<tbody>';
                        
                        purchase.items.forEach(function(item, itemIndex) {
                            html += '<tr>';
                            html += `<td>${itemIndex + 1}</td>`;
                            html += `<td>${item.item_name}</td>`;
                            html += `<td><small class="text-muted">${item.barcode}</small></td>`;
                            html += `<td class="text-end">${item.quantity} ${item.unit}</td>`;
                            html += `<td class="text-end">${item.rate}</td>`;
                            html += `<td class="text-end">${item.discount}</td>`;
                            html += `<td class="text-end">${item.tax_percentage}%</td>`;
                            html += `<td class="text-end">${item.tax_amount}</td>`;
                            html += `<td class="text-end">${item.unit_cost}</td>`;
                            html += `<td class="text-end fw-bold">${item.total_cost}</td>`;
                            html += '</tr>';
                        });
                        
                        html += '</tbody>';
                        html += '</table>';
                        html += '</div>';
                        
                        html += '<div class="row mt-3">';
                        html += '<div class="col-md-8"></div>';
                        html += '<div class="col-md-4">';
                        html += '<table class="table table-bordered table-sm">';
                        html += `<tr><th>Subtotal:</th><td class="text-end">${purchase.subtotal}</td></tr>`;
                        html += `<tr><th>Discount:</th><td class="text-end text-danger">-${purchase.discount}</td></tr>`;
                        html += `<tr><th>Tax:</th><td class="text-end">${purchase.order_tax}</td></tr>`;
                        html += `<tr><th>Shipping:</th><td class="text-end">${purchase.shipping}</td></tr>`;
                        html += `<tr class="table-primary"><th>Grand Total:</th><td class="text-end fw-bold">${purchase.grand_total}</td></tr>`;
                        html += '</table>';
                        html += '</div>';
                        html += '</div>';
                        
                        if (purchase.description && purchase.description !== 'N/A') {
                            html += `<div class="mt-2"><small><strong>Description:</strong> ${purchase.description}</small></div>`;
                        }
                        
                        html += '</div>';
                        html += '</div>';
                    });
                } else {
                    html += '<div class="alert alert-info text-center">No purchase detail history found.</div>';
                }
                
                if (detailsContent) detailsContent.innerHTML = html;
            } else {
                if (detailsContent) detailsContent.innerHTML = '<div class="alert alert-danger text-center">Error loading detail history. Please try again.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (detailsContent) detailsContent.innerHTML = '<div class="alert alert-danger text-center">Error loading detail history. Please try again.</div>';
        });
    }
    
    // Show Purchase Detail History Function
    function showPurchaseDetailHistory(supplierId) {
        // Show loading
        const modalBody = document.getElementById('purchaseDetailHistoryModalBody');
        if (modalBody) {
            modalBody.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        }
        
        // Open modal
        const modalElement = document.getElementById('purchaseDetailHistoryModal');
        if (!modalElement) return;
        
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        
        // Fetch detail history
        fetch(`/suppliers/${supplierId}/purchase-detail-history`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (response.status === 401) {
                // Don't redirect, just show error
                if (modalBody) modalBody.innerHTML = '<div class="alert alert-danger text-center">Session expired. Please refresh the page.</div>';
                return;
            }
            return response.json();
        })
        .then(data => {
            if (!data) return; // Exit if 401 was handled
            if (data.success) {
                let html = '<div class="detail-history-report">';
                html += `<h5 class="mb-3">Supplier: ${data.supplier.name}</h5>`;
                
                if (data.detail_history.length > 0) {
                    data.detail_history.forEach(function(purchase, index) {
                        html += `<div class="card mb-4" data-purchase-id="${purchase.purchase_id}">`;
                        html += '<div class="card-header bg-light">';
                        html += '<div class="row">';
                        html += '<div class="col-md-6">';
                        html += `<h6 class="mb-0"><strong>Invoice:</strong> ${purchase.invoice_no}</h6>`;
                        html += `<small class="text-muted"><strong>Reference:</strong> ${purchase.reference}</small>`;
                        html += '</div>';
                        html += '<div class="col-md-6 text-end">';
                        html += `<small><strong>Date:</strong> ${purchase.date} | <strong>Time:</strong> ${purchase.time}</small><br>`;
                        html += `<small><strong>Branch:</strong> ${purchase.branch} | <strong>Status:</strong> <span class="badge bg-info">${purchase.status}</span></small>`;
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                        
                        html += '<div class="card-body">';
                        html += '<h6 class="mb-3">Items:</h6>';
                        html += '<div class="table-responsive">';
                        html += '<table class="table table-bordered table-sm">';
                        html += '<thead class="table-light">';
                        html += '<tr>';
                        html += '<th>#</th>';
                        html += '<th>Item Name</th>';
                        html += '<th>Barcode</th>';
                        html += '<th class="text-end">Quantity</th>';
                        html += '<th class="text-end">Rate</th>';
                        html += '<th class="text-end">Discount</th>';
                        html += '<th class="text-end">Tax %</th>';
                        html += '<th class="text-end">Tax Amount</th>';
                        html += '<th class="text-end">Unit Cost</th>';
                        html += '<th class="text-end">Total Cost</th>';
                        html += '</tr>';
                        html += '</thead>';
                        html += '<tbody>';
                        
                        purchase.items.forEach(function(item, itemIndex) {
                            html += '<tr>';
                            html += `<td>${itemIndex + 1}</td>`;
                            html += `<td>${item.item_name}</td>`;
                            html += `<td><small class="text-muted">${item.barcode}</small></td>`;
                            html += `<td class="text-end">${item.quantity} ${item.unit}</td>`;
                            html += `<td class="text-end">${item.rate}</td>`;
                            html += `<td class="text-end">${item.discount}</td>`;
                            html += `<td class="text-end">${item.tax_percentage}%</td>`;
                            html += `<td class="text-end">${item.tax_amount}</td>`;
                            html += `<td class="text-end">${item.unit_cost}</td>`;
                            html += `<td class="text-end fw-bold">${item.total_cost}</td>`;
                            html += '</tr>';
                        });
                        
                        html += '</tbody>';
                        html += '</table>';
                        html += '</div>';
                        
                        html += '<div class="row mt-3">';
                        html += '<div class="col-md-8"></div>';
                        html += '<div class="col-md-4">';
                        html += '<table class="table table-bordered table-sm">';
                        html += `<tr><th>Subtotal:</th><td class="text-end">${purchase.subtotal}</td></tr>`;
                        html += `<tr><th>Discount:</th><td class="text-end text-danger">-${purchase.discount}</td></tr>`;
                        html += `<tr><th>Tax:</th><td class="text-end">${purchase.order_tax}</td></tr>`;
                        html += `<tr><th>Shipping:</th><td class="text-end">${purchase.shipping}</td></tr>`;
                        html += `<tr class="table-primary"><th>Grand Total:</th><td class="text-end fw-bold">${purchase.grand_total}</td></tr>`;
                        html += '</table>';
                        html += '</div>';
                        html += '</div>';
                        
                        if (purchase.description && purchase.description !== 'N/A') {
                            html += `<div class="mt-2"><small><strong>Description:</strong> ${purchase.description}</small></div>`;
                        }
                        
                        html += '</div>';
                        html += '</div>';
                    });
                } else {
                    html += '<div class="alert alert-info text-center">No purchase detail history found.</div>';
                }
                
                html += '</div>';
                
                if (modalBody) modalBody.innerHTML = html;
            } else {
                if (modalBody) modalBody.innerHTML = '<div class="alert alert-danger text-center">Error loading detail history. Please try again.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (modalBody) modalBody.innerHTML = '<div class="alert alert-danger text-center">Error loading detail history. Please try again.</div>';
        });
    }
    
    // Show Single Purchase Detail Function
    function showPurchaseDetail(purchaseId) {
        // Get supplierId from the ledger modal body
        const ledgerModalBody = document.getElementById('supplierLedgerModalBody');
        const supplierId = ledgerModalBody?.dataset?.supplierId;
        
        if (supplierId) {
            // Show detail history and scroll to the specific purchase
            showPurchaseDetailHistory(supplierId);
            
            // After modal loads, scroll to the specific purchase
            setTimeout(() => {
                const purchaseCard = document.querySelector(`[data-purchase-id="${purchaseId}"]`);
                if (purchaseCard) {
                    purchaseCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    purchaseCard.classList.add('border-primary', 'border-2');
                }
            }, 500);
        }
    }
    
    // Edit History Function
    function showEditHistory(supplierId) {
        // Show loading
        const modalBody = document.getElementById('editHistoryModalBody');
        if (modalBody) {
            modalBody.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        }
        
        // Open modal
        const modalElement = document.getElementById('editHistoryModal');
        if (!modalElement) return;
        
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        
        // Fetch edit history
        fetch(`/suppliers/${supplierId}/edit-history`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (response.status === 401) {
                throw new Error('Unauthorized');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.history.length > 0) {
                let html = '<div class="table-responsive"><table class="table table-bordered table-hover">';
                html += '<thead class="table-light"><tr><th>Date/Time</th><th>Changes</th><th>Edited By</th><th>Branch</th></tr></thead>';
                html += '<tbody>';
                
                data.history.forEach(function(item) {
                    html += '<tr>';
                    html += `<td><strong>${item.date}</strong><br><small class="text-muted">${item.time}</small></td>`;
                    html += '<td><ul class="list-unstyled mb-0">';
                    
                    // Display changes
                    Object.keys(item.changes).forEach(function(field) {
                        const change = item.changes[field];
                        const label = change.label || field;
                        html += `<li class="mb-2 p-2 bg-light rounded">`;
                        html += `<strong>${label}:</strong><br>`;
                        html += `<span class="text-danger"><i class="fas fa-arrow-left"></i> Old:</span> <code>${change.old || 'N/A'}</code><br>`;
                        html += `<span class="text-success"><i class="fas fa-arrow-right"></i> New:</span> <code>${change.new || 'N/A'}</code>`;
                        html += `</li>`;
                    });
                    
                    html += '</ul></td>';
                    html += `<td>${item.edited_by}</td>`;
                    html += `<td>${item.branch}</td>`;
                    html += '</tr>';
                });
                
                html += '</tbody></table></div>';
                if (modalBody) modalBody.innerHTML = html;
            } else {
                if (modalBody) modalBody.innerHTML = '<div class="alert alert-info text-center">No edit history found for this supplier.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (modalBody) modalBody.innerHTML = '<div class="alert alert-danger text-center">Error loading edit history. Please try again.</div>';
        });
    }
</script>

@endpush
