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
    
    /* Add Supplier modal – prevent frozen state (z-index + pointer-events when shown) */
    #addSupplierModal.show { z-index: 10056 !important; }
    #addSupplierModal.show .modal-dialog,
    #addSupplierModal.show .modal-content,
    #addSupplierModal.show .modal-header,
    #addSupplierModal.show .modal-body,
    #addSupplierModal.show .modal-footer { pointer-events: auto !important; }
    /* Modal Dialog Desktop Alignment */
    #addSupplierModal .modal-dialog {
        max-width: 1140px !important;
        width: 90% !important;
        margin: 1.75rem auto !important;
    }
    
    @media (min-width: 1200px) {
        #addSupplierModal .modal-dialog {
            max-width: 1140px !important;
            width: 85% !important;
        }
    }
    
    @media (min-width: 992px) and (max-width: 1199px) {
        #addSupplierModal .modal-dialog {
            max-width: 900px !important;
            width: 90% !important;
        }
    }
    
    @media (min-width: 768px) and (max-width: 991px) {
        #addSupplierModal .modal-dialog {
            max-width: 700px !important;
            width: 90% !important;
        }
    }
    
    /* Overall Modal Form Alignment */
    #addSupplierModal .modal-body .row {
        margin-left: 0;
        margin-right: 0;
        display: flex;
        flex-wrap: wrap;
    }
    
    #addSupplierModal .modal-body .row > [class*="col-"] {
        padding-left: 15px;
        padding-right: 15px;
        margin-bottom: 1rem;
    }
    
    /* Desktop: Ensure proper column spacing */
    @media (min-width: 768px) {
        #addSupplierModal .modal-body .row > [class*="col-md-"] {
            padding-left: 12px;
            padding-right: 12px;
        }
        
        #addSupplierModal .modal-body .row.g-3 > [class*="col-"] {
            padding-left: 12px;
            padding-right: 12px;
        }
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
        display: flex !important;
        align-items: stretch;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    #addSupplierModal .input-group .form-control {
        flex: 1 1 auto;
        display: block !important;
        visibility: visible !important;
        min-width: 100px !important;
    }
    
    #addSupplierModal .input-group .form-select {
        display: block !important;
        visibility: visible !important;
        flex: 0 0 auto;
    }
    
    #addSupplierModal .input-group .btn {
        flex: 0 0 auto;
        display: block !important;
        visibility: visible !important;
        flex-shrink: 0;
    }
    
    /* Country code select2 width control - now in separate div above input-group */
    #addSupplierModal .phone-country-code,
    #addSupplierModal .phone-country-code + .select2-container {
        max-width: 200px !important;
        width: 200px !important;
    }
    
    /* Ensure phone number input has proper width when country code is above */
    #addSupplierModal .name-phone-row .col-md-6:nth-child(2) .input-group .phone-number-input {
        flex: 1 1 auto !important;
        min-width: 200px !important;
        width: auto !important;
    }
    
    /* Ensure phone number input has proper width */
    #addSupplierModal .input-group .phone-number-input {
        flex: 1 1 auto !important;
        min-width: 150px !important;
        width: auto !important;
        display: block !important;
        visibility: visible !important;
    }
    
    /* Ensure WhatsApp number input group is visible */
    #addSupplierModal .name-phone-row .col-md-6:nth-child(2) .input-group {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
        width: 100% !important;
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
        width: 100%;
    }
    
    /* Ensure WhatsApp Number column is visible on desktop */
    @media (min-width: 768px) {
        .name-phone-row .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            width: 50%;
            display: flex !important;
            flex-direction: column;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        .name-phone-row .col-md-6:nth-child(2) {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
    }
    
    .name-phone-row .form-label {
        margin-bottom: 0.5rem;
        display: block !important;
    }
    
    .name-phone-row .input-group {
        width: 100%;
        display: flex !important;
        visibility: visible !important;
    }
    
    /* Ensure phone number input is visible */
    .name-phone-row .phone-number-input {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        width: 100% !important;
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
    
    /* Business Detail Tag Input Styling */
    .business-detail-tag-container {
        position: relative;
    }
    
    .business-detail-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        max-height: 250px;
        overflow-y: auto;
        z-index: 1000;
        margin-top: 2px;
        display: none;
    }
    
    .business-detail-suggestions.show {
        display: block;
    }
    
    .business-detail-suggestion-item {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        gap: 0.75rem;
        transition: background 0.1s;
    }
    
    .business-detail-suggestion-item:hover {
        background-color: #f8f9fa;
    }
    
    .business-detail-suggestion-item:last-child {
        border-bottom: none;
    }
    
    .business-detail-suggestion-item.selected {
        background-color: #e7f3ff;
    }
    
    .business-detail-suggestion-text {
        flex: 1;
        font-size: 0.9rem;
    }
    
    .business-detail-suggestion-text .highlight {
        font-weight: 600;
        color: #0d6efd;
    }
    
    .business-detail-suggestion-loading {
        padding: 0.5rem 0.75rem;
        text-align: center;
        color: #6c757d;
        font-size: 0.875rem;
    }
    
    .business-detail-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        min-height: 40px;
    }
    
    .business-detail-tag {
        display: inline-flex;
        align-items: center;
        padding: 0.375rem 0.75rem;
        background-color: #0d6efd;
        color: white;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        gap: 0.5rem;
    }
    
    .business-detail-tag .tag-remove {
        cursor: pointer;
        font-weight: bold;
        opacity: 0.8;
        font-size: 16px;
        line-height: 1;
        transition: opacity 0.2s;
    }
    
    .business-detail-tag .tag-remove:hover {
        opacity: 1;
    }
    
    /* Consistent Spacing */
    #addSupplierModal .col-md-6,
    #addSupplierModal .col-md-12,
    #addSupplierModal .col-12 {
        margin-bottom: 1rem;
    }
    
    /* Desktop: Better spacing for columns */
    @media (min-width: 768px) {
        #addSupplierModal .col-md-6 {
            margin-bottom: 1.25rem;
        }
        
        #addSupplierModal .col-md-12 {
            margin-bottom: 1.25rem;
        }
        
        #addSupplierModal .col-12 {
            margin-bottom: 1.25rem;
        }
        
        /* Ensure equal column heights for side-by-side fields */
        #addSupplierModal .row > .col-md-6 {
            display: flex;
            flex-direction: column;
        }
    }
    
    /* Group field specific styling */
    #addSupplierModal .col-md-6:has(select[name="group_id"]),
    [id^="editSupplierModal"] .col-md-6:has(select[name="group_id"]) {
        width: auto;
        min-width: 100px;
        flex: 1 1 auto;
        text-align: left;
    }
    /* Ensure Group Select2 dropdown always shows search box */
    .select2-dropdown .select2-search--dropdown,
    .select2-dropdown .select2-search.select2-search--dropdown {
        display: block !important;
        visibility: visible !important;
    }
    .select2-dropdown .select2-search__field {
        display: block !important;
        width: 100% !important;
        visibility: visible !important;
    }
    
    /* Remove extra padding from row */
    #addSupplierModal .row.g-3 {
        --bs-gutter-x: 1rem;
        --bs-gutter-y: 1rem;
    }
    
    /* Desktop: Optimize gutter spacing */
    @media (min-width: 768px) {
        #addSupplierModal .row.g-3 {
            --bs-gutter-x: 1.25rem;
            --bs-gutter-y: 1rem;
        }
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
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }
    
    /* Desktop: Better padding for modal body */
    @media (min-width: 768px) {
        #addSupplierModal .modal-body {
            padding: 30px !important;
        }
    }
    
    @media (min-width: 992px) {
        #addSupplierModal .modal-body {
            padding: 35px !important;
        }
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
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="/assets/img/icons/pdf.svg" alt="img"></a></li>
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="/assets/img/icons/excel.svg" alt="img"></a></li>
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
        <div class="page-btn">
            @can('add_supplier')
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                <i class="ti ti-circle-plus me-1"></i>Add
            </a>
            @endcan
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
                                    @can('update_supplier')
                                    <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#editSupplierModal{{ $item->id }}">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    @endcan
                                    @can('view_supplier')
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
                                    @endcan
                                    @can('delete_supplier')
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
                                    @endcan
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
@can('add_supplier')
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true" style="z-index: 10056;">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" style="pointer-events: auto;">
        <div class="modal-content" style="pointer-events: auto;">
            <div class="modal-header" style="pointer-events: auto;">
                <h4 class="modal-title" id="addSupplierModalLabel">Add Supplier</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @include('admin.suppliers.modals.create-supplier-form')
        </div>
    </div>
</div>
@endcan

@forelse ($suppliers as $item)
@can('update_supplier')
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
@endcan
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
                <div class="me-auto">
                    <button type="button" class="btn btn-outline-secondary" id="rotateLeftBtn" title="Rotate Left 90°">
                        <i class="fas fa-undo"></i> Rotate Left
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="rotateRightBtn" title="Rotate Right 90°">
                        <i class="fas fa-redo"></i> Rotate Right
                    </button>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="cropImageBtn">Crop & Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Universal Edit Group Modal (for Edit Group button next to group dropdown) -->
<div class="modal fade" id="universalEditGroupModal" tabindex="-1" aria-labelledby="universalEditGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="universalEditGroupModalLabel">Edit Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="universalEditGroupHint" class="text-muted small mb-2">Select a group from the dropdown first, then click the edit button.</p>
                <div id="universalEditGroupForm" style="display: none;">
                    <input type="hidden" id="universalEditGroupId" value="">
                    <div class="mb-3">
                        <label for="universalEditGroupName" class="form-label">Group Name</label>
                        <input type="text" class="form-control" id="universalEditGroupName" placeholder="Group name">
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="universalEditGroupFooter">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger me-auto" id="universalEditGroupDeleteBtn" style="display: none;">Delete</button>
                <button type="button" class="btn btn-primary" id="universalEditGroupSaveBtn" style="display: none;">Update</button>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places"></script>
<!-- Tesseract.js for OCR -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
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


        function updateRemoveEmailButtons() {
            const container = document.getElementById('emailContainer');
            if (!container) return;
            const rows = container.querySelectorAll('.email-row');
            // First row (index 0) should never have a remove button
            // Only rows added via "Add More Email" will have remove buttons
            // This function is kept for consistency but first row won't have the button element
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
                inputField.readOnly = false; // Remove readonly to allow editing
                inputField.removeAttribute('readonly');
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
                        <label class="form-label">Record Voice NAME <span class="text-danger">*</span></label>
                        <div class="mb-2">
                            <button type="button" class="btn btn-outline-secondary mic-btn w-100">
                                <i class="fas fa-microphone me-2"></i>Record Voice
                            </button>
                        </div>
                        <div class="input-group">
                            <input type="text" name="names[]" class="form-control speech-input" placeholder="Enter name or use mic">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Other Number</label>
                        <div class="mb-2">
                            <select name="country_codes[]" class="form-select phone-country-code w-100" style="max-width: 200px;">
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
                        </div>
                        <div class="input-group">
                            <input type="text" name="phones[]" class="form-control phone-number-input" placeholder="Enter phone number">
                            <button type="button" class="btn btn-success phone-whatsapp-btn" title="Open WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </button>
                            <button type="button" class="btn btn-primary phone-call-btn" title="Call">
                                <i class="fas fa-phone"></i>
                            </button>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-danger remove-row w-100">
                                <i class="fas fa-trash me-2"></i>Remove
                            </button>
                        </div>
                    </div>
                `;
                container.appendChild(newRow);
                updateRemoveButtons(container.id);
                
                // Initialize Select2 for the new country code dropdown
                const newSelect = newRow.querySelector('.phone-country-code');
                const newPhoneInput = newRow.querySelector('.phone-number-input');
                
                if (newSelect && !$(newSelect).hasClass('select2-hidden-accessible')) {
                    const selectMaxWidth = newSelect.style.maxWidth || '200px';
                    const fixedWidth = selectMaxWidth.replace('px', '') + 'px';
                    
                    $(newSelect).select2({
                        placeholder: 'Select Country Code',
                        allowClear: false,
                        width: fixedWidth,
                        minimumResultsForSearch: 0,
                        matcher: function(params, data) {
                            if (!params.term || params.term === '') {
                                return data;
                            }
                            const searchTerm = params.term.toUpperCase();
                            const optionText = data.text.toUpperCase();
                            const optionValue = data.id.toString();
                            if (optionValue.includes(searchTerm) || optionText.includes(searchTerm)) {
                                return data;
                            }
                            return null;
                        }
                    });
                    
                    // If Pakistan (+92) is selected by default, apply validation to phone input
                    if (newSelect.value === '92' && newPhoneInput) {
                        // Trigger validation for the new phone input
                        setTimeout(function() {
                            const event = new Event('input', { bubbles: true });
                            newPhoneInput.dispatchEvent(event);
                        }, 100);
                    }
                    
                    // Auto-focus search input when dropdown opens
                    $(newSelect).on('select2:open', function() {
                        setTimeout(function() {
                            const searchInput = $('.select2-container--open .select2-search__field');
                            if (searchInput.length) {
                                searchInput.focus();
                            }
                        }, 100);
                    });
                }
            }

            // Add Email
            if (e.target.closest('#addEmail')) {
                e.preventDefault();
                const btn = e.target.closest('#addEmail');
                const container = document.getElementById('emailContainer');
                if (!container) return;
                const newRow = document.createElement('div');
                newRow.className = 'row g-3 mb-3 email-row';
                newRow.innerHTML = `
                    <div class="col-12">
                        <div class="input-group">
                            <input type="email" name="emails[]" class="form-control email-input" placeholder="Enter email address">
                            <button type="button" class="btn btn-danger remove-email-row" style="display: block;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `;
                container.appendChild(newRow);
                // Update remove buttons visibility
                updateRemoveEmailButtons();
            }

            // Remove Email Row
            if (e.target.closest('.remove-email-row')) {
                e.target.closest('.email-row').remove();
                updateRemoveEmailButtons();
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
                    
                    // Make name input editable again when recording is removed
                    if (inputField) {
                        inputField.readOnly = false;
                        inputField.removeAttribute('readonly');
                        inputField.style.backgroundColor = '';
                        inputField.style.cursor = '';
                    }
                    
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

        // Phone number validation and max length (only for Pakistan +92)
        // Allow numbers and letter O in phone input
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('phone-number-input')) {
                const phoneInput = e.target;
                // Remove all non-digit and non-O characters (allow numbers and O)
                let phoneNumber = phoneInput.value.replace(/[^\dOo]/g, '');
                // Convert lowercase 'o' to uppercase 'O'
                phoneNumber = phoneNumber.replace(/o/g, 'O');
                phoneInput.value = phoneNumber;
                
                // Find country code select in the same row (it's in a separate div above input-group)
                const namePhoneRow = phoneInput.closest('.name-phone-row');
                const countryCodeSelect = namePhoneRow ? namePhoneRow.querySelector('.phone-country-code') : null;
                
                // Get country code value (from select2 if initialized, otherwise from select)
                let countryCode = null;
                if (countryCodeSelect) {
                    if ($(countryCodeSelect).hasClass('select2-hidden-accessible')) {
                        countryCode = $(countryCodeSelect).val();
                    } else {
                        countryCode = countryCodeSelect.value;
                    }
                }
                
                // Only apply validation if Pakistan (+92) is selected
                if (countryCode === '92') {
                    // Limit to 11 characters (digits + O)
                    if (phoneNumber.length > 11) {
                        phoneNumber = phoneNumber.substring(0, 11);
                        phoneInput.value = phoneNumber;
                    }
                    
                    // Show red color if less than 11 characters
                    if (phoneNumber.length > 0 && phoneNumber.length < 11) {
                        phoneInput.style.borderColor = '#dc3545';
                        phoneInput.style.color = '#dc3545';
                    } else if (phoneNumber.length === 11) {
                        phoneInput.style.borderColor = '#28a745';
                        phoneInput.style.color = '#28a745';
                    } else {
                        phoneInput.style.borderColor = '';
                        phoneInput.style.color = '';
                    }
                } else {
                    // Reset styling if not Pakistan
                    phoneInput.style.borderColor = '';
                    phoneInput.style.color = '';
                }
            }
        });

        // Search filter for name/phone rows in edit supplier modal
        document.addEventListener('input', function(e) {
            if (e.target.classList && e.target.classList.contains('name-phone-search-input')) {
                const col = e.target.closest('.col-12');
                const container = col ? col.querySelector('#namePhoneContainer') : null;
                if (!container) return;
                const term = (e.target.value || '').trim().toLowerCase();
                container.querySelectorAll('.name-phone-row').forEach(function(row) {
                    const nameInput = row.querySelector('input[name="names[]"]');
                    const phoneInput = row.querySelector('input[name="phones[]"]');
                    const name = (nameInput && nameInput.value) ? nameInput.value.trim().toLowerCase() : '';
                    const phone = (phoneInput && phoneInput.value) ? phoneInput.value.trim().toLowerCase().replace(/\s+/g, '') : '';
                    const show = !term || name.indexOf(term) !== -1 || phone.indexOf(term) !== -1;
                    row.style.display = show ? '' : 'none';
                });
            }
        });

        // Prevent typing non-numeric characters and limit to 11 digits when Pakistan (+92) is selected
        // Allow numbers and letter O
        document.addEventListener('keypress', function(e) {
            if (e.target.classList.contains('phone-number-input')) {
                const phoneInput = e.target;
                
                // Only allow numeric keys (0-9), letter O (both cases), backspace, delete, tab, arrow keys, etc.
                const allowedKeys = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
                const isNumeric = /^\d$/.test(e.key);
                const isLetterO = /^[Oo]$/.test(e.key);
                const isAllowedKey = allowedKeys.includes(e.key) || (e.ctrlKey && ['a', 'c', 'v', 'x'].includes(e.key.toLowerCase()));
                
                // If not a number, not letter O, and not an allowed key, prevent input
                if (!isNumeric && !isLetterO && !isAllowedKey) {
                    e.preventDefault();
                    return false;
                }
                
                // Find country code select in the same row
                const namePhoneRow = phoneInput.closest('.name-phone-row');
                const countryCodeSelect = namePhoneRow ? namePhoneRow.querySelector('.phone-country-code') : null;
                
                if (!countryCodeSelect) return;
                
                // Get country code value
                let countryCode = null;
                if ($(countryCodeSelect).hasClass('select2-hidden-accessible')) {
                    countryCode = $(countryCodeSelect).val();
                } else {
                    countryCode = countryCodeSelect.value;
                }
                
                // Only limit to 11 characters if Pakistan (+92) is selected
                if (countryCode === '92' && (isNumeric || isLetterO)) {
                    const currentValue = phoneInput.value.replace(/[^\dO]/g, '');
                    if (currentValue.length >= 11) {
                        e.preventDefault();
                        return false;
                    }
                }
            }
        });
        
        // Prevent paste of non-numeric and non-O content
        document.addEventListener('paste', function(e) {
            if (e.target.classList.contains('phone-number-input')) {
                e.preventDefault();
                const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                // Allow numeric characters and letter O from pasted text
                const cleanedText = pastedText.replace(/[^\dOo]/g, '').replace(/o/g, 'O');
                e.target.value = cleanedText;
                // Trigger input event to apply validation
                const inputEvent = new Event('input', { bubbles: true });
                e.target.dispatchEvent(inputEvent);
            }
        });
        
        // Handle country code change to validate phone number
        $(document).on('change', '.phone-country-code', function() {
            const countryCodeSelect = this;
            // Find the phone input in the same row (next input-group in the same col-md-6 or in the row)
            const namePhoneRow = $(countryCodeSelect).closest('.name-phone-row');
            const phoneInput = namePhoneRow.find('.phone-number-input')[0];
            
            if (phoneInput) {
                const countryCode = $(countryCodeSelect).val();
                // Allow numbers and letter O, preserve leading zero
                let phoneNumber = phoneInput.value.replace(/[^\dOo]/g, '').replace(/o/g, 'O');
                
                // Limit to 11 characters (digits + O)
                if (phoneNumber.length > 11) {
                    phoneNumber = phoneNumber.substring(0, 11);
                    phoneInput.value = phoneNumber;
                }
                
                // Show red color if less than 11 characters
                if (countryCode && phoneNumber.length > 0 && phoneNumber.length < 11) {
                    phoneInput.style.borderColor = '#dc3545';
                    phoneInput.style.color = '#dc3545';
                } else if (countryCode && phoneNumber.length === 11) {
                    phoneInput.style.borderColor = '#28a745';
                    phoneInput.style.color = '#28a745';
                } else {
                    phoneInput.style.borderColor = '';
                    phoneInput.style.color = '';
                }
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
        
        // Prevent typing more than 11 digits when Pakistan (+92) is selected
        document.addEventListener('keypress', function(e) {
            if (e.target.classList.contains('phone-number-input')) {
                const phoneInput = e.target;
                // Find country code select in the same row
                const namePhoneRow = phoneInput.closest('.name-phone-row');
                const countryCodeSelect = namePhoneRow ? namePhoneRow.querySelector('.phone-country-code') : null;
                
                if (!countryCodeSelect) return;
                
                // Get country code value
                let countryCode = null;
                if ($(countryCodeSelect).hasClass('select2-hidden-accessible')) {
                    countryCode = $(countryCodeSelect).val();
                } else {
                    countryCode = countryCodeSelect.value;
                }
                
                // Only limit to 11 digits if Pakistan (+92) is selected
                if (countryCode === '92') {
                    const currentValue = phoneInput.value.replace(/\D/g, '');
                    if (currentValue.length >= 11 && e.key.match(/\d/)) {
                        e.preventDefault();
                        return false;
                    }
                }
            }
        });
        
        // Handle country code change to validate phone number (only for Pakistan +92)
        $(document).on('change', '.phone-country-code', function() {
            const countryCodeSelect = this;
            // Find the phone input in the same row
            const namePhoneRow = $(countryCodeSelect).closest('.name-phone-row');
            const phoneInput = namePhoneRow.find('.phone-number-input')[0];
            
            if (phoneInput) {
                const countryCode = $(countryCodeSelect).val();
                
                // Only apply validation if Pakistan (+92) is selected
                if (countryCode === '92') {
                    // Allow numbers and letter O, preserve leading zero
                    let phoneNumber = phoneInput.value.replace(/[^\dOo]/g, '').replace(/o/g, 'O');
                    
                    // Limit to 11 characters (digits + O)
                    if (phoneNumber.length > 11) {
                        phoneNumber = phoneNumber.substring(0, 11);
                        phoneInput.value = phoneNumber;
                    }
                    
                    // Show red color if less than 11 characters
                    if (phoneNumber.length > 0 && phoneNumber.length < 11) {
                        phoneInput.style.borderColor = '#dc3545';
                        phoneInput.style.color = '#dc3545';
                    } else if (phoneNumber.length === 11) {
                        phoneInput.style.borderColor = '#28a745';
                        phoneInput.style.color = '#28a745';
                    } else {
                        phoneInput.style.borderColor = '';
                        phoneInput.style.color = '';
                    }
                } else {
                    // Reset styling if not Pakistan
                    phoneInput.style.borderColor = '';
                    phoneInput.style.color = '';
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
            recognition.interimResults = true; // Enable interim results for real-time transcription
            recognition.lang = 'en-US';
            recognition.maxAlternatives = 3; // Get multiple alternatives for better accuracy

            let mediaRecorder = null;
            let audioChunks = [];
            let transcript = '';
            let recordingTimer = null;
            let recordingStartTime = null;
            let actualRecordingDuration = 0; // Track actual recording duration
            let timeRemaining = 7; // 7 seconds
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
                inputField.readOnly = false; // Allow editing during recording
                inputField.removeAttribute('readonly');
                inputField.style.color = ''; // Make text visible for real-time transcription
                inputField.style.textShadow = '';
                inputField.placeholder = 'Listening... Speak now (0:07 remaining)';
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
                    inputField.style.color = '#212529'; // Keep text visible after recording
                    inputField.placeholder = `✅ Voice transcribed (verified spelling) - ${durationText} recorded`;
                    // Ensure final transcript is set with proper formatting (verified and cleaned)
                    if (transcript.trim()) {
                        let finalText = transcript.trim()
                            .replace(/\s+/g, ' ') // Clean multiple spaces
                            .replace(/\b\w/g, (char) => char.toUpperCase()); // Capitalize for names
                        inputField.value = finalText;
                    }
                    // Make input readonly after recording to prevent manual changes (ALWAYS, regardless of transcript)
                    inputField.setAttribute('readonly', 'readonly');
                    inputField.readOnly = true;
                    inputField.style.backgroundColor = '#f8f9fa';
                    inputField.style.cursor = 'not-allowed';
                    // Add event listeners to prevent any changes
                    inputField.addEventListener('keydown', function(e) {
                        e.preventDefault();
                        return false;
                    }, true);
                    inputField.addEventListener('input', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }, true);
                    inputField.addEventListener('paste', function(e) {
                        e.preventDefault();
                        return false;
                    }, true);
                    // Store original value to restore if someone tries to change it
                    const originalValue = inputField.value;
                    // Use setInterval to continuously enforce readonly (as a safety measure)
                    const readonlyEnforcer = setInterval(() => {
                        if (inputField.readOnly !== true) {
                            inputField.readOnly = true;
                            inputField.setAttribute('readonly', 'readonly');
                        }
                        if (inputField.value !== originalValue) {
                            inputField.value = originalValue;
                        }
                    }, 100);
                    // Store the interval ID on the input field so we can clear it later
                    inputField.dataset.readonlyEnforcer = readonlyEnforcer;
                    
                    controlBtn.innerHTML = '<i class="fas fa-play"></i>';
                    controlBtn.classList.remove('mic-btn');
                    controlBtn.classList.add('play-pause-btn');
                    
                    stream.getTracks().forEach(track => track.stop());
                };
                
                isRecording = true;
                recordingStartTime = Date.now();
                actualRecordingDuration = 0;
                timeRemaining = 7;
                
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
                    // Ensure text color remains visible for real-time transcription
                    inputField.style.color = '#212529'; // Dark color for visibility
                    if (timeRemaining <= 5) {
                        inputField.style.backgroundColor = '#ffebee';
                        inputField.style.textShadow = 'none'; // Remove shadow for better readability
                    } else if (timeRemaining <= 10) {
                        inputField.style.backgroundColor = '#fff3e0';
                        inputField.style.textShadow = 'none'; // Remove shadow for better readability
                    } else {
                        inputField.style.backgroundColor = '#e3f2fd';
                        inputField.style.textShadow = 'none'; // Remove shadow for better readability
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
                    
                    // Stop recording after exactly 7 seconds
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
                        inputField.style.color = '#212529'; // Ensure text is visible
                        inputField.style.textShadow = 'none';
                        controlBtn.style.backgroundColor = '';
                        controlBtn.style.color = '';
                    }
                }, 1000);
                
                recognition.onresult = (event) => { 
                    // Accumulate all results for continuous recognition with accuracy verification
                    let interimTranscript = '';
                    let finalTranscript = transcript;
                    
                    for (let i = event.resultIndex; i < event.results.length; i++) {
                        const result = event.results[i];
                        const transcriptObj = result[0];
                        const confidence = transcriptObj.confidence || 0;
                        
                        // Only use results with confidence for better accuracy verification
                        // For final results, use best alternative (highest confidence) if available
                        if (result.isFinal) {
                            let bestTranscript = transcriptObj.transcript;
                            let bestConfidence = confidence;
                            
                            // Check alternatives if available (maxAlternatives is set)
                            // Note: Some browsers may not support alternatives
                            try {
                                if (result.length && result.length > 1) {
                                    for (let alt = 0; alt < Math.min(result.length, 3); alt++) {
                                        if (result[alt] && result[alt].transcript) {
                                            const altConfidence = result[alt].confidence || 0;
                                            if (altConfidence > bestConfidence) {
                                                bestTranscript = result[alt].transcript;
                                                bestConfidence = altConfidence;
                                            }
                                        }
                                    }
                                }
                            } catch (e) {
                                // Fallback if alternatives not supported
                                console.log('Alternatives check:', e);
                            }
                            
                            // Only add if confidence is acceptable (>= 0.3) for verified spelling
                            // Lower threshold for names as they might have lower confidence but still be correct
                            if (bestConfidence >= 0.3) {
                                // Clean and format the transcript for better spelling (verified)
                                let cleanedText = bestTranscript.trim();
                                // Remove extra punctuation and clean
                                cleanedText = cleanedText.replace(/[^\w\s]/g, ' ').replace(/\s+/g, ' ').trim();
                                // Capitalize first letter of each word for proper name formatting
                                cleanedText = cleanedText.replace(/\b\w/g, (char) => char.toUpperCase());
                                finalTranscript += cleanedText + ' ';
                                transcript = finalTranscript;
                            }
                        } else {
                            // For interim results, show only if confidence is decent
                            if (confidence >= 0.5) {
                                interimTranscript += transcriptObj.transcript;
                            }
                        }
                    }
                    
                    // Update input field in real-time with both final and interim results
                    const displayText = (finalTranscript + interimTranscript).trim();
                    if (displayText) {
                        // Format text: capitalize first letter of each word for proper name formatting
                        // Also clean extra spaces and normalize text
                        let formattedText = displayText
                            .replace(/\s+/g, ' ') // Replace multiple spaces with single space
                            .trim();
                        
                        // Capitalize first letter of each word for names (verified spelling)
                        formattedText = formattedText.replace(/\b\w/g, (char) => char.toUpperCase());
                        
                        inputField.value = formattedText;
                        inputField.style.color = '#212529'; // Ensure text is visible
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
                const existing = document.querySelector('.existing-file, .existing-visiting-doc');
                
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


        // Form Submission Spinner and Validation (delegated for all forms)
        document.addEventListener('submit', function(e) {
            if (e.target.id === 'supplierForm') {
                // Check if voice recording is required and validate it exists
                const voiceNoteRequired = e.target.querySelector('#voiceNoteRequired');
                if (voiceNoteRequired && voiceNoteRequired.value === '1') {
                    const voiceNoteInput = e.target.querySelector('input[name="voice_note"]');
                    const firstRow = e.target.querySelector('.name-phone-row');
                    const audioContainer = firstRow ? firstRow.querySelector('.audio-player-container') : null;
                    
                    // Check if voice recording exists (either file input with file or audio container)
                    const hasVoiceNoteFile = voiceNoteInput && voiceNoteInput.files && voiceNoteInput.files.length > 0;
                    const hasAudioContainer = audioContainer !== null;
                    
                    if (!hasVoiceNoteFile && !hasAudioContainer) {
                        e.preventDefault();
                        alert('⚠️ Please record voice for NAME. Voice recording is required!');
                        const firstMicBtn = firstRow ? firstRow.querySelector('.mic-btn') : null;
                        if (firstMicBtn) {
                            firstMicBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            firstMicBtn.focus();
                        }
                        return false;
                    }
                }
                
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
                    // Reset email container - remove all but first email row
                    const emailContainer = form.querySelector('#emailContainer');
                    if (emailContainer) {
                        const emailRows = emailContainer.querySelectorAll('.email-row');
                        emailRows.forEach((row, index) => {
                            if (index > 0) {
                                row.remove();
                            } else {
                                const emailInput = row.querySelector('.email-input');
                                if (emailInput) emailInput.value = '';
                            }
                        });
                        updateRemoveEmailButtons();
                    }
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
    // Use event delegation for rotation buttons
    document.addEventListener('click', function(e) {
        // Rotate Left Button
        if (e.target.closest('#rotateLeftBtn')) {
            e.preventDefault();
            const currentInputId = window.currentCropInputId;
            let cropper = null;
            
            if (currentInputId === 'profile_img') {
                cropper = window.profileImgCropper;
            } else if (currentInputId === 'visiting_doc') {
                cropper = window.visitingDocCropper;
            }
            
            if (cropper) {
                cropper.rotate(-90); // Rotate counter-clockwise by 90 degrees
            }
        }
        
        // Rotate Right Button
        if (e.target.closest('#rotateRightBtn')) {
            e.preventDefault();
            const currentInputId = window.currentCropInputId;
            let cropper = null;
            
            if (currentInputId === 'profile_img') {
                cropper = window.profileImgCropper;
            } else if (currentInputId === 'visiting_doc') {
                cropper = window.visitingDocCropper;
            }
            
            if (cropper) {
                cropper.rotate(90); // Rotate clockwise by 90 degrees
            }
        }
    });
    
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
                    
                    // Get cropped canvas data URL (for preview and OCR)
                    const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.9);
                    
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
                            
                            // Update preview with cropped image
                            if (preview) {
                                preview.src = croppedDataUrl;
                                preview.style.display = 'block';
                            }
                            // Show cancel button
                            const cancelBtn = document.getElementById('cancelProfileImg');
                            if (cancelBtn) cancelBtn.style.display = 'block';
                            
                            if (placeholder) placeholder.style.display = 'none';
                            if (uploadBtn) uploadBtn.style.display = 'none';
                            
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
                            
                            // Update preview with cropped image
                            if (visitingImg) visitingImg.src = croppedDataUrl;
                            if (imgContainer) imgContainer.style.display = 'block';
                            if (fileInfo) fileInfo.style.display = 'none';
                            if (preview) preview.style.display = 'block';
                            // Show cancel button
                            const cancelBtn = document.getElementById('cancelVisitingDoc');
                            if (cancelBtn) cancelBtn.style.display = 'block';
                            
                            // Extract text from visiting card using OCR with cropped image
                            extractTextFromVisitingCard(croppedDataUrl);
                            
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
                // Get max-width from select element or use default
                const selectMaxWidth = select.style.maxWidth || '120px';
                const fixedWidth = selectMaxWidth.replace('px', '') + 'px';
                
                $(select).select2({
                    placeholder: 'Select Country Code',
                    allowClear: false,
                    width: fixedWidth, // Use fixed width based on max-width
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

        // Open edit modal when URL has ?edit=id (e.g. from purchase page Edit vendor button)
        var params = new URLSearchParams(window.location.search);
        var editId = params.get('edit');
        if (editId) {
            var modalEl = document.getElementById('editSupplierModal' + editId);
            if (modalEl && typeof bootstrap !== 'undefined') {
                setTimeout(function() {
                    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                }, 300);
            }
        }

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
                initSupplierGroupSelect2(this);
            }.bind(addSupplierModal), 100);
        });
    }

    // Group select: open on click, search always visible (Add & Edit Supplier modals)
    function initSupplierGroupSelect2(modalEl) {
        const container = modalEl && modalEl.querySelector ? modalEl : document;
        const sel = (container.querySelector || document.querySelector).call(container, 'select.supplier-group-select');
        if (!sel || !window.$ || !$.fn.select2) return;
        const $sel = $(sel);
        const $modal = $sel.closest('.modal');
        if ($sel.hasClass('select2-hidden-accessible')) {
            try { $sel.select2('destroy'); } catch (err) {}
        }
        $sel.select2({
            placeholder: 'Select Group',
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: 0,
            dropdownParent: $modal.length ? $modal : $('body'),
            escapeMarkup: function(m) { return m; },
            language: {
                search: function() { return 'Search…'; },
                noResults: function() {
                    const term = ($('.select2-container--open .select2-search__field').val() || '').trim();
                    const display = term ? ' &quot;' + term.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;') + '&quot;' : '';
                    return '<div class="p-2 text-center"><button type="button" class="btn btn-primary btn-sm select2-add-group-btn" data-term="' + (term.replace(/"/g, '&quot;')) + '"><i class="ti ti-plus me-1"></i>Add' + display + '</button></div>';
                }
            }
        });
        $sel.on('select2:open', function() {
            setTimeout(function() {
                var $search = $('.select2-dropdown .select2-search__field');
                if (!$search.length) $search = $('.select2-container--open .select2-search__field');
                if ($search.length) $search[0].focus();
            }, 100);
        });
        // Delegate from modal so click/mousedown on container or selection always opens dropdown
        var openGroupDropdown = function(e) {
            var $groupSel = $modal.find('select.supplier-group-select');
            if (!$groupSel.length || !$groupSel.data('select2')) return;
            if (!$groupSel.data('select2').isOpen()) {
                e.preventDefault();
                e.stopPropagation();
                $groupSel.select2('open');
            }
        };
        $modal.off('click.groupopen mousedown.groupopen').on('click.groupopen', '.select2-container', openGroupDropdown).on('mousedown.groupopen', '.select2-container', openGroupDropdown);
    }
    document.addEventListener('shown.bs.modal', function(e) {
        if (e.target.id && e.target.id.indexOf('editSupplierModal') === 0) {
            setTimeout(function() { initSupplierGroupSelect2(e.target); }, 100);
        }
    });

    $(document).on('click', '.select2-add-group-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const term = ($(this).data('term') || '').trim();
        const name = term || window.prompt('Enter group name:') || '';
        if (!name) return;
        const $anyGroupSelect = $('select.supplier-group-select').first();
        if ($anyGroupSelect.length) $anyGroupSelect.select2('close');
        $.ajax({
            url: '{{ route("post.groups") }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', name: name },
            success: function(res) {
                if (res.id && res.name) {
                    $('select.supplier-group-select').each(function() {
                        if ($(this).find('option[value="' + res.id + '"]').length === 0) {
                            $(this).append($('<option></option>').val(res.id).text(res.name));
                        }
                        $(this).val(res.id).trigger('change');
                    });
                }
            }
        });
    });

    // Edit Group button (open-universal-modal): open modal, fetch selected group, update/delete
    $(document).on('click', '.open-universal-modal', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var btn = $(this);
        var fetchRoute = (btn.data('fetch-route') || '').toString().replace(/^https?:\/\/[^/]+/, '');
        var updateRoute = (btn.data('update-route') || '').toString().replace(/^https?:\/\/[^/]+/, '');
        var deleteRoute = (btn.data('delete-route') || '').toString().replace(/^https?:\/\/[^/]+/, '');
        var targetSelect = btn.data('target-select') || '.supplier-group-select';
        var $select = btn.closest('.modal').length ? btn.closest('.modal').find(targetSelect) : $(targetSelect).first();
        if (!$select.length) $select = $(targetSelect).first();
        var groupId = $select.length ? ($select.val() || '').trim() : '';
        var $uModal = $('#universalEditGroupModal');
        var $hint = $('#universalEditGroupHint');
        var $form = $('#universalEditGroupForm');
        var $idInput = $('#universalEditGroupId');
        var $nameInput = $('#universalEditGroupName');
        var $saveBtn = $('#universalEditGroupSaveBtn');
        var $deleteBtn = $('#universalEditGroupDeleteBtn');
        if (!groupId) {
            $hint.show().text('Select a group from the dropdown first, then click the edit button.');
            $form.hide();
            $saveBtn.hide();
            $deleteBtn.hide();
            $uModal.modal('show');
            return;
        }
        $hint.hide();
        $form.show();
        $idInput.val(groupId);
        $nameInput.val('');
        $saveBtn.show();
        $deleteBtn.show();
        var url = fetchRoute.replace(':id', groupId);
        $.ajax({
            url: url,
            method: 'GET',
            success: function(data) {
                $nameInput.val(data.name || '');
                $uModal.modal('show');
            },
            error: function() {
                if (typeof toastr !== 'undefined') toastr.error('Could not load group.');
                else alert('Could not load group.');
            }
        });
    });
    $('#universalEditGroupSaveBtn').on('click', function() {
        var id = $('#universalEditGroupId').val();
        var name = ($('#universalEditGroupName').val() || '').trim();
        if (!name) {
            if (typeof toastr !== 'undefined') toastr.warning('Enter a group name.');
            else alert('Enter a group name.');
            return;
        }
        var updateRoute = ($('.open-universal-modal').data('update-route') || '').toString().replace(/^https?:\/\/[^/]+/, '');
        var url = updateRoute.replace(':id', id);
        var $select = $('select.supplier-group-select');
        $.ajax({
            url: url,
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            data: { _token: '{{ csrf_token() }}', name: name },
            success: function(res) {
                $select.find('option[value="' + id + '"]').text(name);
                $('#universalEditGroupModal').modal('hide');
                if (typeof toastr !== 'undefined') toastr.success(res.message || 'Group updated.');
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Update failed.';
                if (typeof toastr !== 'undefined') toastr.error(msg);
                else alert(msg);
            }
        });
    });
    $('#universalEditGroupDeleteBtn').on('click', function() {
        if (!confirm('Delete this group? This may affect suppliers using it.')) return;
        var id = $('#universalEditGroupId').val();
        var deleteRoute = ($('.open-universal-modal').data('delete-route') || '').toString().replace(/^https?:\/\/[^/]+/, '');
        var url = deleteRoute.replace(':id', id);
        var $select = $('select.supplier-group-select');
        $.ajax({
            url: url,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            success: function() {
                $select.find('option[value="' + id + '"]').remove();
                $select.val('').trigger('change');
                $('#universalEditGroupModal').modal('hide');
                if (typeof toastr !== 'undefined') toastr.success('Group deleted.');
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Delete failed.';
                if (typeof toastr !== 'undefined') toastr.error(msg);
                else alert(msg);
            }
        });
    });

    // Auto-focus for all select elements when clicked/opened
    document.addEventListener('click', function(e) {
        // Do not run when interacting with Select2 (container, dropdown, or search)
        if (e.target.closest('.select2-container, .select2-dropdown, .select2-search__field')) return;
        if (e.target.tagName === 'SELECT' || e.target.closest('select')) {
            const select = e.target.tagName === 'SELECT' ? e.target : e.target.closest('select');
            if (select && !select.classList.contains('phone-country-code')) {
                e.stopPropagation();
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
            const existing = document.querySelector('.existing-file, .existing-visiting-doc');
            
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
            
            // Show existing document if it exists
            if (existing) existing.style.display = 'block';
            
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
    
    // Business Detail Tag Input with Spelling Suggestions
    (function() {
        let businessDetailTags = [];
        let suggestionTimeout = null;
        let currentSuggestions = [];
        let selectedSuggestionIndex = -1;
        
        function initializeBusinessDetailInput() {
            const input = document.getElementById('business_detail_input');
            const suggestions = document.getElementById('business_detail_suggestions');
            const tagsContainer = document.getElementById('business_detail_tags');
            const hiddenInput = document.getElementById('business_detail');
            
            if (!input || !tagsContainer || !hiddenInput) return;
            
            // Load existing tags from hidden input
            if (hiddenInput.value) {
                try {
                    const existing = JSON.parse(hiddenInput.value);
                    if (Array.isArray(existing)) {
                        businessDetailTags = existing;
                        renderTags();
                    }
                } catch (e) {
                    // If not JSON, treat as comma-separated
                    const tags = hiddenInput.value.split(',').map(t => t.trim()).filter(t => t);
                    businessDetailTags = tags;
                    renderTags();
                }
            }
            
            // Input handler for suggestions
            input.addEventListener('input', function(e) {
                const query = e.target.value.trim();
                
                if (suggestionTimeout) clearTimeout(suggestionTimeout);
                
                if (query.length < 2) {
                    suggestions.classList.remove('show');
                    selectedSuggestionIndex = -1;
                    return;
                }
                
                suggestionTimeout = setTimeout(() => {
                    fetchSpellingSuggestions(query, suggestions);
                }, 300);
            });
            
            // Keydown handler
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const value = e.target.value.trim();
                    
                    if (selectedSuggestionIndex >= 0 && currentSuggestions[selectedSuggestionIndex]) {
                        // Add selected suggestion
                        addTag(currentSuggestions[selectedSuggestionIndex].name);
                        input.value = '';
                        suggestions.classList.remove('show');
                        selectedSuggestionIndex = -1;
                    } else if (value) {
                        // Add typed value
                        addTag(value);
                        input.value = '';
                        suggestions.classList.remove('show');
                    }
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    navigateSuggestions(1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    navigateSuggestions(-1);
                } else if (e.key === 'Escape') {
                    suggestions.classList.remove('show');
                    selectedSuggestionIndex = -1;
                }
            });
            
            // Click outside to close suggestions
            document.addEventListener('click', function(e) {
                if (!input.contains(e.target) && !suggestions.contains(e.target)) {
                    suggestions.classList.remove('show');
                    selectedSuggestionIndex = -1;
                }
            });
            
            // Click on suggestion
            suggestions.addEventListener('click', function(e) {
                const item = e.target.closest('.business-detail-suggestion-item');
                if (item) {
                    const value = item.dataset.value;
                    if (value) {
                        addTag(value);
                        input.value = '';
                        suggestions.classList.remove('show');
                        selectedSuggestionIndex = -1;
                    }
                }
            });
        }
        
        async function fetchSpellingSuggestions(query, suggestionsEl) {
            suggestionsEl.innerHTML = '<div class="business-detail-suggestion-loading">Searching...</div>';
            suggestionsEl.classList.add('show');
            
            try {
                // Progressive search - as user types, filter suggestions
                const queryLower = query.toLowerCase().trim();
                
                // Comprehensive product list for progressive filtering
                const allProducts = [
                    'Books', 'Book', 'Bookstore', 'Bookshop', 'Bookbinding',
                    'Electronics', 'Electronic Items', 'Electronic Devices', 'Electronic Accessories',
                    'Clothing', 'Clothes', 'Cloth', 'Clothing Store', 'Clothing Accessories',
                    'Groceries', 'Grocery', 'Grocery Store', 'Grocery Items',
                    'Furniture', 'Furniture Store', 'Furniture Items', 'Office Furniture',
                    'Appliances', 'Home Appliances', 'Kitchen Appliances', 'Electronic Appliances',
                    'Toys', 'Toy Store', 'Toy Items', 'Children Toys',
                    'Sports Equipment', 'Sports Items', 'Sports Goods', 'Sports Accessories',
                    'Jewelry', 'Jewellery', 'Jewelry Store', 'Jewelry Items',
                    'Cosmetics', 'Cosmetic Products', 'Cosmetic Items', 'Beauty Products',
                    'Automotive Parts', 'Auto Parts', 'Car Parts', 'Vehicle Parts',
                    'Hardware', 'Hardware Store', 'Hardware Items', 'Hardware Tools',
                    'Software', 'Software Products', 'Software Solutions', 'Computer Software',
                    'Food', 'Food Items', 'Food Products', 'Food Store',
                    'Beverages', 'Beverage', 'Drinks', 'Beverage Store',
                    'Stationery', 'Stationary', 'Stationery Items', 'Office Stationery',
                    'Medicines', 'Medicine', 'Pharmaceuticals', 'Medical Supplies',
                    'Home Decor', 'Home Decoration', 'Decorative Items', 'Home Accessories',
                    'Garden Supplies', 'Garden Tools', 'Garden Items', 'Gardening Supplies',
                    'Pet Supplies', 'Pet Food', 'Pet Accessories', 'Pet Items',
                    'Baby Products', 'Baby Items', 'Baby Care', 'Baby Accessories',
                    'Art Supplies', 'Art Materials', 'Art Items', 'Artistic Supplies',
                    'Mobile Phones', 'Mobile', 'Smartphones', 'Mobile Accessories',
                    'Laptops', 'Laptop', 'Laptop Accessories', 'Computer Laptops',
                    'Tablets', 'Tablet', 'Tablet Accessories', 'Digital Tablets',
                    'Headphones', 'Headphone', 'Earphones', 'Audio Headphones',
                    'Cameras', 'Camera', 'Camera Accessories', 'Digital Cameras',
                    'Watches', 'Watch', 'Wristwatch', 'Watch Accessories',
                    'Shoes', 'Shoe', 'Footwear', 'Shoe Store',
                    'Bags', 'Bag', 'Handbags', 'Bag Accessories',
                    'Wallets', 'Wallet', 'Leather Wallets', 'Wallet Store',
                    'Sunglasses', 'Sunglass', 'Eye Glasses', 'Sunglass Store',
                    'Perfumes', 'Perfume', 'Fragrances', 'Perfume Store',
                    'Skincare Products', 'Skincare', 'Skin Care', 'Beauty Skincare',
                    'Computer Accessories', 'Computer Parts', 'PC Accessories', 'Computer Items',
                    'Office Supplies', 'Office Items', 'Office Products', 'Office Equipment',
                    'Kitchen Items', 'Kitchenware', 'Kitchen Products', 'Kitchen Accessories',
                    'Bedding', 'Bed Sheets', 'Bedding Items', 'Bedroom Accessories',
                    'Towels', 'Towel', 'Bath Towels', 'Towel Store'
                ];
                
                // Progressive filtering - find ALL matching products
                // As user types, filter products that contain the query anywhere
                let filteredProducts = [];
                
                if (queryLower.length === 1) {
                    // Single letter: show all products starting with that letter
                    filteredProducts = allProducts.filter(product => 
                        product.toLowerCase().startsWith(queryLower)
                    );
                } else {
                    // Multiple letters: flexible matching - search in entire product name
                    const queryWords = queryLower.split(/\s+/).filter(w => w.length > 0);
                    
                    filteredProducts = allProducts.filter(product => {
                        const productLower = product.toLowerCase();
                        const productWords = productLower.split(/\s+/);
                        
                        // Method 1: Product starts with query (highest priority)
                        if (productLower.startsWith(queryLower)) {
                            return true;
                        }
                        
                        // Method 2: Any word in product starts with query
                        for (let word of productWords) {
                            if (word.startsWith(queryLower)) {
                                return true;
                            }
                        }
                        
                        // Method 3: Product contains query anywhere (more flexible)
                        if (productLower.includes(queryLower)) {
                            return true;
                        }
                        
                        // Method 4: Check if query words appear in product (for multi-word queries)
                        if (queryWords.length > 1) {
                            // Check if all query words appear in product (in any order)
                            const allWordsFound = queryWords.every(qWord => 
                                productLower.includes(qWord)
                            );
                            if (allWordsFound) {
                                return true;
                            }
                        }
                        
                        // Method 5: Check if any word in query matches any word in product (starts with)
                        for (let qWord of queryWords) {
                            if (productWords.some(pWord => pWord.startsWith(qWord))) {
                                return true;
                            }
                        }
                        
                        return false;
                    });
                }
                
                // Remove already added tags
                filteredProducts = filteredProducts.filter(p => 
                    !businessDetailTags.includes(p) && p.toLowerCase() !== queryLower
                );
                
                // Fetch Google suggestions for additional options
                let googleSuggestions = [];
                try {
                    const googleUrl = `https://www.google.com/complete/search?client=firefox&q=${encodeURIComponent(query)}`;
                    const proxyUrl = `https://api.allorigins.win/get?url=${encodeURIComponent(googleUrl)}`;
                    const response = await fetch(proxyUrl);
                    const data = await response.json();
                    
                    if (data && data.contents) {
                        const content = JSON.parse(data.contents);
                        if (content && content[1] && Array.isArray(content[1])) {
                            googleSuggestions = content[1].slice(0, 10).map(item => item[0])
                                .filter(s => !businessDetailTags.includes(s) && s.toLowerCase() !== queryLower);
                        }
                    }
                } catch (e) {
                    console.log('Error fetching Google suggestions:', e);
                }
                
                // Combine filtered products with Google suggestions
                let allSuggestions = [...new Set([...filteredProducts, ...googleSuggestions])];
                
                // If no suggestions found at all, show "No suggestions found"
                if (allSuggestions.length === 0) {
                    displaySuggestions([], suggestionsEl, query);
                    return;
                }
                
                // Sort by relevance (exact start match first, then contains)
                allSuggestions.sort((a, b) => {
                    const aLower = a.toLowerCase();
                    const bLower = b.toLowerCase();
                    
                    const aStarts = aLower.startsWith(queryLower) ? 1 : 0;
                    const bStarts = bLower.startsWith(queryLower) ? 1 : 0;
                    
                    if (aStarts !== bStarts) return bStarts - aStarts;
                    
                    const aIndex = aLower.indexOf(queryLower);
                    const bIndex = bLower.indexOf(queryLower);
                    
                    return aIndex - bIndex;
                });
                
                // Limit to exactly 5 suggestions
                currentSuggestions = allSuggestions.slice(0, 5).map(name => ({ name }));
                displaySuggestions(currentSuggestions, suggestionsEl, query);
                
            } catch (error) {
                console.error('Error fetching suggestions:', error);
                suggestionsEl.innerHTML = '<div class="business-detail-suggestion-loading">Error loading suggestions</div>';
            }
        }
        
        function displaySuggestions(suggestions, suggestionsEl, query) {
            if (suggestions.length === 0) {
                suggestionsEl.innerHTML = '';
                suggestionsEl.classList.remove('show');
                return;
            }
            
            const highlightText = (text, query) => {
                const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                return text.replace(regex, '<span class="highlight">$1</span>');
            };
            
            suggestionsEl.innerHTML = suggestions.map((suggestion, index) => {
                return `
                    <div class="business-detail-suggestion-item ${index === selectedSuggestionIndex ? 'selected' : ''}" 
                         data-value="${suggestion.name}" data-index="${index}">
                        <div class="business-detail-suggestion-text">${highlightText(suggestion.name, query)}</div>
                    </div>
                `;
            }).join('');
            
            suggestionsEl.classList.add('show');
        }
        
        function navigateSuggestions(direction) {
            const items = document.querySelectorAll('.business-detail-suggestion-item');
            if (items.length === 0) return;
            
            // Remove previous selection
            items.forEach(item => item.classList.remove('selected'));
            
            // Update index
            selectedSuggestionIndex += direction;
            if (selectedSuggestionIndex < 0) {
                selectedSuggestionIndex = items.length - 1;
            } else if (selectedSuggestionIndex >= items.length) {
                selectedSuggestionIndex = 0;
            }
            
            // Add selection
            if (items[selectedSuggestionIndex]) {
                items[selectedSuggestionIndex].classList.add('selected');
                items[selectedSuggestionIndex].scrollIntoView({ block: 'nearest' });
            }
        }
        
        function addTag(tagName) {
            if (tagName && !businessDetailTags.includes(tagName)) {
                businessDetailTags.push(tagName);
                renderTags();
                updateHiddenInput();
            }
        }
        
        function removeTag(tagName) {
            businessDetailTags = businessDetailTags.filter(t => t !== tagName);
            renderTags();
            updateHiddenInput();
        }
        
        function renderTags() {
            const tagsContainer = document.getElementById('business_detail_tags');
            if (!tagsContainer) return;
            
            if (businessDetailTags.length === 0) {
                tagsContainer.innerHTML = '';
                return;
            }
            
            tagsContainer.innerHTML = businessDetailTags.map(tag => `
                <span class="business-detail-tag">
                    ${tag}
                    <span class="tag-remove" data-tag="${tag}" title="Remove">×</span>
                </span>
            `).join('');
            
            // Add remove handlers
            tagsContainer.querySelectorAll('.tag-remove').forEach(btn => {
                btn.addEventListener('click', function() {
                    const tagToRemove = this.dataset.tag;
                    removeTag(tagToRemove);
                });
            });
        }
        
        function updateHiddenInput() {
            const hiddenInput = document.getElementById('business_detail');
            if (hiddenInput) {
                hiddenInput.value = JSON.stringify(businessDetailTags);
            }
        }
        
        // Initialize on modal shown
        const addSupplierModal = document.getElementById('addSupplierModal');
        if (addSupplierModal) {
            addSupplierModal.addEventListener('shown.bs.modal', function() {
                setTimeout(initializeBusinessDetailInput, 100);
            });
        }
        
        // Also initialize on page load
        if (document.getElementById('business_detail_input')) {
            initializeBusinessDetailInput();
        }
    })();
    
    // Function to extract text from visiting card using OCR
    function extractTextFromVisitingCard(imageDataUrl) {
        if (typeof Tesseract === 'undefined') {
            console.log('Tesseract.js not loaded');
            return;
        }
        
        // Show loading indicator
        const companyInput = document.getElementById('company');
        if (companyInput) {
            companyInput.placeholder = 'Extracting information...';
        }
        
        Tesseract.recognize(
            imageDataUrl,
            'eng+urd', // English and Urdu
            {
                logger: m => {
                    if (m.status === 'recognizing text') {
                        // Update progress if needed
                    }
                }
            }
        ).then(({ data: { text } }) => {
            console.log('OCR Recognition completed. Extracted text length:', text ? text.length : 0);
            console.log('OCR Extracted Text:', text);
            // Parse extracted text and fill form fields
            if (text && text.trim()) {
                parseAndFillFormFields(text);
            } else {
                console.log('OCR: No text was extracted from the image');
                alert('No text could be extracted from the image. Please ensure the image is clear and try again.');
            }
            
            // Reset placeholder
            if (companyInput) {
                companyInput.placeholder = '';
            }
        }).catch(err => {
            console.error('OCR Error:', err);
            alert('OCR extraction failed: ' + (err.message || 'Unknown error') + '. Please try again or enter information manually.');
            if (companyInput) {
                companyInput.placeholder = '';
            }
        });
    }
    
    // Function to parse OCR text and fill form fields
    function parseAndFillFormFields(text) {
        if (!text || !text.trim()) {
            console.log('OCR: No text to parse');
            return;
        }
        
        console.log('Parsing OCR text:', text);
        
        const lines = text.split('\n').map(line => line.trim()).filter(line => line.length > 0);
        const fullText = text.replace(/\s+/g, ' '); // Normalize whitespace for better matching
        
        // Extract Company Name (usually first line or contains "STORE", "AUTO", "PARTS", etc.)
        let companyName = '';
        for (let i = 0; i < Math.min(5, lines.length); i++) {
            const line = lines[i].toUpperCase();
            if (line.length > 3 && (line.includes('STORE') || line.includes('AUTO') || line.includes('PARTS') || 
                line.includes('COMPANY') || line.includes('TRADERS') || line.includes('ENTERPRISES') ||
                line.match(/^[A-Z\s]{3,}$/))) {
                companyName = lines[i];
                break;
            }
        }
        if (!companyName && lines.length > 0) {
            // Use first substantial line as company name
            const firstLine = lines[0];
            if (firstLine.length > 3 && !firstLine.match(/^\d+/) && !firstLine.includes('@')) {
                companyName = firstLine;
            }
        }
        if (companyName) {
            const companyInput = document.getElementById('company');
            if (companyInput && !companyInput.value) {
                companyInput.value = companyName;
            }
        }
        
        // Extract Email(s)
        const emailRegex = /[\w\.-]+@[\w\.-]+\.\w+/gi;
        const emailMatches = fullText.match(emailRegex) || [];
        if (emailMatches && emailMatches.length > 0) {
            // Remove duplicates
            const uniqueEmails = [...new Set(emailMatches)];
            const emailContainer = document.getElementById('emailContainer');
            if (emailContainer) {
                const emailRows = emailContainer.querySelectorAll('.email-row');
                uniqueEmails.forEach((email, index) => {
                    if (index === 0) {
                        // Fill first email input
                        const firstEmailInput = emailContainer.querySelector('.email-input');
                        if (firstEmailInput && !firstEmailInput.value) {
                            firstEmailInput.value = email;
                        }
                    } else {
                        // Add new email row for additional emails
                        const addEmailBtn = document.getElementById('addEmail');
                        if (addEmailBtn) {
                            addEmailBtn.click();
                            setTimeout(() => {
                                const emailRows = emailContainer.querySelectorAll('.email-row');
                                const newEmailInput = emailRows[emailRows.length - 1]?.querySelector('.email-input');
                                if (newEmailInput) {
                                    newEmailInput.value = email;
                                }
                            }, 100);
                        }
                    }
                });
            }
        }
        
        // Extract Phone Numbers (Pakistani format: 03XX-XXXXXXX or +92XXXXXXXXXX)
        // Try multiple regex patterns to catch different formats
        const phonePatterns = [
            /0[3-4][0-9]{2}[-\s.]?[0-9]{7}/g,  // 03XX-XXXXXXX or 03XX XXXX XXX
            /\+92[-\s.]?[0-9]{10}/g,           // +92XXXXXXXXXX
            /0092[-\s.]?[0-9]{10}/g,           // 0092XXXXXXXXXX
            /92[-\s.]?[0-9]{10}/g,             // 92XXXXXXXXXX
            /\b[0-9]{11}\b/g,                  // Any 11 digits
            /\b[0-9]{10}\b/g                   // Any 10 digits
        ];
        
        let allPhoneMatches = [];
        phonePatterns.forEach(pattern => {
            const matches = fullText.match(pattern);
            if (matches) {
                allPhoneMatches = allPhoneMatches.concat(matches);
            }
        });
        
        // Also search in individual lines for better detection
        lines.forEach(line => {
            phonePatterns.forEach(pattern => {
                const matches = line.match(pattern);
                if (matches) {
                    allPhoneMatches = allPhoneMatches.concat(matches);
                }
            });
        });
        
        console.log('All phone matches found:', allPhoneMatches);
        
        if (allPhoneMatches && allPhoneMatches.length > 0) {
            // Clean phone numbers - preserve leading zero for Pakistani numbers
            const cleanPhones = allPhoneMatches.map(phone => {
                // Remove non-digit characters
                let cleaned = phone.replace(/[^\d]/g, '');
                // Remove country code 92 if present at the start (only if length > 11)
                if (cleaned.startsWith('92') && cleaned.length > 11) {
                    cleaned = cleaned.substring(2);
                }
                // Remove 0092 prefix if present
                if (cleaned.startsWith('0092') && cleaned.length > 11) {
                    cleaned = cleaned.substring(4);
                }
                // Preserve leading zero (don't remove it)
                // Pakistani numbers often start with 0 (e.g., 03001234567)
                return cleaned;
            })
            .filter(phone => {
                // Filter valid Pakistani phone numbers
                // Must be 10 or 11 digits
                if (phone.length < 10 || phone.length > 11) return false;
                // If 11 digits, must start with 0
                if (phone.length === 11 && !phone.startsWith('0')) return false;
                // If 10 digits, should start with 3 (mobile) or 4 (landline)
                if (phone.length === 10) {
                    return phone.startsWith('3') || phone.startsWith('4');
                }
                // If 11 digits starting with 0, next digit should be 3 or 4
                if (phone.length === 11 && phone.startsWith('0')) {
                    return phone[1] === '3' || phone[1] === '4';
                }
                return true;
            })
            .filter((phone, index, self) => {
                // Remove duplicates
                return self.indexOf(phone) === index;
            });
            
            // Fill first phone number in the first phone input
            if (cleanPhones.length > 0) {
                const firstPhoneInput = document.querySelector('.phone-number-input[data-index="0"]');
                if (firstPhoneInput && !firstPhoneInput.value) {
                    // Mark as programmatic change to preserve leading zero
                    firstPhoneInput.dataset.programmatic = 'true';
                    // Set value directly
                    firstPhoneInput.value = cleanPhones[0];
                    // Manually trigger validation without the input event that might strip the zero
                    const countryCodeSelect = firstPhoneInput.closest('.name-phone-row')?.querySelector('.phone-country-code');
                    if (countryCodeSelect) {
                        const countryCode = $(countryCodeSelect).hasClass('select2-hidden-accessible') 
                            ? $(countryCodeSelect).val() 
                            : countryCodeSelect.value;
                        if (countryCode === '92') {
                            const phoneNumber = cleanPhones[0];
                            if (phoneNumber.length === 11) {
                                firstPhoneInput.style.borderColor = '#28a745';
                                firstPhoneInput.style.color = '#28a745';
                            } else if (phoneNumber.length > 0 && phoneNumber.length < 11) {
                                firstPhoneInput.style.borderColor = '#dc3545';
                                firstPhoneInput.style.color = '#dc3545';
                            }
                        }
                    }
                }
            }
            
            // If multiple phone numbers, add them to additional name-phone rows
            if (cleanPhones.length > 1) {
                for (let i = 1; i < cleanPhones.length; i++) {
                    // Check if we need to add a new row
                    const namePhoneContainer = document.getElementById('namePhoneContainer');
                    if (namePhoneContainer) {
                        const existingRows = namePhoneContainer.querySelectorAll('.name-phone-row');
                        if (existingRows.length <= i) {
                            // Add new row
                            const addBtn = document.getElementById('addNamePhone');
                            if (addBtn) {
                                addBtn.click();
                                // Wait for row to be added
                                setTimeout(() => {
                                    const newPhoneInput = namePhoneContainer.querySelectorAll('.phone-number-input')[i];
                                    if (newPhoneInput && !newPhoneInput.value) {
                                        // Mark as programmatic change to preserve leading zero
                                        newPhoneInput.dataset.programmatic = 'true';
                                        newPhoneInput.value = cleanPhones[i];
                                        // Manually trigger validation
                                        const countryCodeSelect = newPhoneInput.closest('.name-phone-row')?.querySelector('.phone-country-code');
                                        if (countryCodeSelect) {
                                            const countryCode = $(countryCodeSelect).hasClass('select2-hidden-accessible') 
                                                ? $(countryCodeSelect).val() 
                                                : countryCodeSelect.value;
                                            if (countryCode === '92') {
                                                const phoneNumber = cleanPhones[i];
                                                if (phoneNumber.length === 11) {
                                                    newPhoneInput.style.borderColor = '#28a745';
                                                    newPhoneInput.style.color = '#28a745';
                                                } else if (phoneNumber.length > 0 && phoneNumber.length < 11) {
                                                    newPhoneInput.style.borderColor = '#dc3545';
                                                    newPhoneInput.style.color = '#dc3545';
                                                }
                                            }
                                        }
                                    }
                                }, 100);
                            }
                        } else {
                            const phoneInput = namePhoneContainer.querySelectorAll('.phone-number-input')[i];
                            if (phoneInput && !phoneInput.value) {
                                // Mark as programmatic change to preserve leading zero
                                phoneInput.dataset.programmatic = 'true';
                                phoneInput.value = cleanPhones[i];
                                // Manually trigger validation
                                const countryCodeSelect = phoneInput.closest('.name-phone-row')?.querySelector('.phone-country-code');
                                if (countryCodeSelect) {
                                    const countryCode = $(countryCodeSelect).hasClass('select2-hidden-accessible') 
                                        ? $(countryCodeSelect).val() 
                                        : countryCodeSelect.value;
                                    if (countryCode === '92') {
                                        const phoneNumber = cleanPhones[i];
                                        if (phoneNumber.length === 11) {
                                            phoneInput.style.borderColor = '#28a745';
                                            phoneInput.style.color = '#28a745';
                                        } else if (phoneNumber.length > 0 && phoneNumber.length < 11) {
                                            phoneInput.style.borderColor = '#dc3545';
                                            phoneInput.style.color = '#dc3545';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // Extract Address (lines containing "Road", "Street", "Lahore", "Karachi", etc.)
        const addressKeywords = ['ROAD', 'STREET', 'LAHORE', 'KARACHI', 'ISLAMABAD', 'ADDRESS', 'LOCATION', 'AREA', 'TOWN', 'CITY', 'BLOCK', 'SECTOR', 'PHASE', 'COLONY', 'SOCIETY', 'SHOP', 'STORE', 'MARKET', 'PLAZA'];
        let address = '';
        
        console.log('Searching for address in', lines.length, 'lines');
        
        // First, try to find address with keywords
        for (const line of lines) {
            const upperLine = line.toUpperCase();
            if (addressKeywords.some(keyword => upperLine.includes(keyword)) || 
                (line.length > 10 && line.match(/^\d+[A-Z\s,]+/i))) {
                address = line;
                console.log('Address found with keywords:', address);
                break;
            }
        }
        
        // If no address found with keywords, look for longer lines that might be addresses
        if (!address) {
            // Look for lines that contain both numbers and letters (typical address format)
            for (const line of lines) {
                const hasNumbers = /\d/.test(line);
                const hasLetters = /[A-Za-z]/.test(line);
                const hasEmail = /[\w\.-]+@[\w\.-]+\.\w+/gi.test(line);
                const isPhone = /0?[3-4][0-9]{2}[-\s.]?[0-9]{7}/g.test(line);
                
                if (line.length > 15 && hasNumbers && hasLetters && !hasEmail && !isPhone && 
                    !line.match(/^\d{10,}$/) && !line.match(/^[\d\s-]+$/)) {
                    address = line;
                    console.log('Address found (numbers + letters):', address);
                    break;
                }
            }
        }
        
        // If still no address, look for any substantial line that doesn't match other patterns
        if (!address) {
            for (const line of lines) {
                // Skip if it's company name, email, phone, or product
                const isCompany = companyName && line.toUpperCase().includes(companyName.toUpperCase());
                const hasEmail = /[\w\.-]+@[\w\.-]+\.\w+/gi.test(line);
                const hasPhone = /0?[3-4][0-9]{2}[-\s.]?[0-9]{7}/g.test(line);
                const isProduct = productKeywords.some(keyword => line.toUpperCase().includes(keyword));
                
                if (!isCompany && !hasEmail && !hasPhone && !isProduct && 
                    line.length > 10 && line.match(/[A-Za-z\s,]+/) && !line.match(/^\d+$/)) {
                    address = line;
                    console.log('Address found (fallback):', address);
                    break;
                }
            }
        }
        
        console.log('Final extracted address:', address);
        
        // If still no address, look for any substantial line that doesn't match other patterns
        if (!address) {
            for (const line of lines) {
                // Skip if it's company name, email, phone, or product
                const isCompany = companyName && line.toUpperCase().includes(companyName.toUpperCase());
                const hasEmail = /[\w\.-]+@[\w\.-]+\.\w+/gi.test(line);
                const hasPhone = /\b(0?[3-4][0-9]{2}[-\s.]?[0-9]{7})\b/g.test(line);
                const isProduct = productKeywords.some(keyword => line.toUpperCase().includes(keyword));
                
                if (!isCompany && !hasEmail && !hasPhone && !isProduct && 
                    line.length > 10 && line.match(/[A-Za-z\s,]+/) && !line.match(/^\d+$/)) {
                    address = line;
                    break;
                }
            }
        }
        if (address) {
            // Fill the address field (new separate field) - NOT location_address
            const addressInput = document.getElementById('address');
            if (addressInput && !addressInput.value) {
                addressInput.value = address;
            }
            // Do NOT fill location_address - user wants address only in address field
        }
        
        // Extract Product Details (lines containing product-related keywords)
        const productKeywords = ['PARTS', 'SPARE', 'AUTO', 'VEHICLE', 'CAR', 'MOTOR', 'ENGINE', 'TIRE', 'BATTERY', 'OIL'];
        const productLines = [];
        for (const line of lines) {
            const upperLine = line.toUpperCase();
            if (productKeywords.some(keyword => upperLine.includes(keyword)) && line.length > 3) {
                productLines.push(line);
            }
        }
        if (productLines.length > 0) {
            const businessDetailInput = document.getElementById('business_detail_input');
            if (businessDetailInput) {
                // Add product details as tags
                productLines.forEach((product, index) => {
                    setTimeout(() => {
                        businessDetailInput.value = product;
                        businessDetailInput.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', code: 'Enter', keyCode: 13, bubbles: true }));
                        businessDetailInput.value = '';
                    }, index * 200);
                });
            }
        }
        
        // Extract additional information for description (remaining text that doesn't fit other categories)
        const descriptionLines = [];
        const usedLines = new Set();
        
        // Mark used lines
        if (companyName) usedLines.add(companyName);
        if (emailMatches && emailMatches.length > 0) emailMatches.forEach(e => usedLines.add(e));
        if (allPhoneMatches) allPhoneMatches.forEach(p => usedLines.add(p));
        if (address) usedLines.add(address);
        productLines.forEach(p => usedLines.add(p));
        
        // Collect unused substantial lines for description
        for (const line of lines) {
            if (!usedLines.has(line) && line.length > 5 && 
                !line.match(/^\d+$/) && !line.includes('@') && 
                !line.match(/^[\d\s-]+$/)) {
                descriptionLines.push(line);
            }
        }
        
        if (descriptionLines.length > 0) {
            const descriptionTextarea = document.getElementById('description');
            if (descriptionTextarea && !descriptionTextarea.value) {
                // Show description options first
                const showDescBtn = document.getElementById('showDescriptionOptions');
                if (showDescBtn) {
                    showDescBtn.click();
                }
                setTimeout(() => {
                    if (descriptionTextarea) {
                        descriptionTextarea.value = descriptionLines.join('\n');
                    }
                }, 100);
            }
        }
    }
    
    // Global image error handler: prevent console errors for broken images
    document.addEventListener('error', function(e) {
        if (e.target.tagName === 'IMG' && e.target.src) {
            // If image fails to load and doesn't have onerror handler, set a default or hide it
            if (!e.target.hasAttribute('data-error-handled')) {
                e.target.setAttribute('data-error-handled', '1');
                // Try to set a default image if src doesn't already point to default
                if (e.target.src.indexOf('avator1.jpg') === -1 && e.target.src.indexOf('default') === -1) {
                    e.target.onerror = function() {
                        this.onerror = null;
                        if (this.src.indexOf('profile') !== -1 || this.src.indexOf('supplier') !== -1) {
                            this.src = '/assets/img/profiles/avator1.jpg';
                        } else {
                            this.style.display = 'none';
                        }
                    };
                    // Retry with default or hide
                    if (e.target.src.indexOf('profile') !== -1 || e.target.src.indexOf('supplier') !== -1) {
                        e.target.src = '/assets/img/profiles/avator1.jpg';
                    } else {
                        e.target.style.display = 'none';
                    }
                }
            }
        }
    }, true);
</script>

@endpush
