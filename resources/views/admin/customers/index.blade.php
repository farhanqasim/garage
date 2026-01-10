@extends('layouts.app')
@section('title','All Customers')
@section('content')
<style>
    /* Customer Form Styling - User Friendly & Responsive */
    .profile-upload-box:hover, .multiple-upload-box:hover {
        border-color: #0d6efd !important;
        background-color: #f8f9fa !important;
        transition: all 0.3s ease;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .profile-upload-box, .multiple-upload-box {
        transition: all 0.3s ease;
    }
    .form-label.fw-bold {
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }
    .card.border-primary, .card.border-warning {
        border-width: 2px !important;
    }
    .btn-group .btn-check:checked + .btn {
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        font-weight: 600;
    }
    .input-group-text {
        border-right: none;
        min-width: 45px;
        justify-content: center;
        flex-shrink: 0;
        display: flex;
        align-items: center;
    }
    .form-control:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
    .input-group-lg .form-control,
    .input-group-lg .form-select,
    .input-group-lg .input-group-text {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .modal-dialog {
            margin: 0.5rem;
        }
        .modal-body {
            max-height: calc(100vh - 160px);
            padding: 1rem !important;
        }
        .col-12, .col-md-6 {
            margin-bottom: 0.75rem;
        }
        /* Ensure input-group stays in one row on mobile */
        .input-group {
            display: flex;
            flex-wrap: nowrap;
            width: 100%;
            align-items: stretch;
        }
        .input-group-text {
            flex-shrink: 0;
            min-width: 44px;
            width: auto;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
            display: flex !important;
            align-items: center;
            justify-content: center;
            border-right: 1px solid #ced4da;
        }
        .input-group-text i {
            font-size: 1rem !important;
        }
        .input-group .form-control,
        .input-group .form-select {
            flex: 1 1 auto;
            min-width: 0;
            width: 1%;
        }
        .input-group .btn {
            flex-shrink: 0;
            min-width: 44px;
            width: auto;
            padding: 0.375rem 0.75rem;
            display: flex !important;
            align-items: center;
            justify-content: center;
            border-left: 1px solid #ced4da;
        }
        .input-group .btn i {
            font-size: 1rem;
        }
        /* Ensure remove-row button is visible when needed */
        .input-group .remove-row:not(.d-none) {
            display: flex !important;
        }
        .btn-group {
            flex-direction: row;
        }
        .btn-group .btn {
            flex: 1;
            padding: 0.5rem;
            font-size: 0.875rem;
        }
        .form-label {
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        .form-control, .form-select {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
        }
        .remove-row, .mic-btn {
            display: flex !important;
            align-items: center;
            justify-content: center;
            min-width: 44px;
            min-height: 44px;
            flex-shrink: 0;
        }
        .remove-row.d-none,
        .mic-btn.d-none {
            display: none !important;
        }
        /* Ensure remove-row button shows when needed on mobile */
        .name-phone-row:not(:first-child) .remove-row:not(.d-none) {
            display: flex !important;
            flex-shrink: 0;
            min-width: 44px;
        }
        /* Ensure input-group elements are properly aligned on mobile */
        .input-group {
            overflow: hidden;
        }
        .input-group .form-control,
        .input-group .form-select {
            overflow: hidden;
            text-overflow: ellipsis;
        }
        #multiple_images_preview {
            gap: 8px !important;
        }
        #multiple_images_preview > div {
            width: 80px !important;
            height: 100px !important;
        }
        #multiple_images_preview img {
            height: 60px !important;
        }
        .profile-preview-container {
            margin-top: 0.5rem;
        }
        .profile-preview-container .btn {
            min-width: 36px;
            min-height: 36px;
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        .form-control[type="file"] {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
    }
    
    @media (max-width: 576px) {
        .modal-lg {
            max-width: 100%;
            margin: 0.25rem;
        }
        .modal-header h5 {
            font-size: 1rem;
        }
        .modal-body {
            max-height: calc(100vh - 120px);
            padding: 0.75rem !important;
        }
        .form-control, .form-select {
            font-size: 16px; /* Prevents zoom on iOS */
            padding: 0.5rem 0.75rem;
        }
        .btn {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            min-height: 44px;
        }
        /* Ensure input-group stays in one row on very small screens */
        .input-group {
            display: flex;
            flex-wrap: nowrap !important;
            width: 100%;
        }
        .input-group-text {
            flex-shrink: 0;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
            min-width: 44px;
            width: auto;
            display: flex !important;
            align-items: center;
            justify-content: center;
        }
        .input-group-text i {
            font-size: 1.1rem !important;
        }
        .input-group .form-control,
        .input-group .form-select {
            flex: 1 1 auto;
            min-width: 0;
            width: 1%;
        }
        .input-group .btn {
            flex-shrink: 0;
            min-width: 44px;
            min-height: 44px;
            width: auto;
            padding: 0.5rem 0.75rem;
            display: flex !important;
            align-items: center;
            justify-content: center;
        }
        .input-group .btn i {
            font-size: 1rem;
        }
        .form-label {
            font-size: 0.875rem;
        }
        #multiple_images_preview > div {
            width: 70px !important;
            height: 90px !important;
        }
        #multiple_images_preview img {
            height: 55px !important;
        }
    }
    
    /* Touch-friendly buttons on mobile */
    @media (hover: none) and (pointer: coarse) {
        .btn, .form-control, .form-select {
            min-height: 44px; /* iOS recommended touch target */
        }
        .mic-btn, .remove-row {
            min-width: 44px;
            min-height: 44px;
        }
        /* Ensure input-group stays in one row on touch devices */
        .input-group {
            flex-wrap: nowrap !important;
        }
        .input-group-text {
            flex-shrink: 0;
            display: flex !important;
        }
        .input-group .form-control,
        .input-group .form-select {
            flex: 1 1 auto;
            min-width: 0;
        }
        .input-group .btn {
            flex-shrink: 0;
            display: flex !important;
        }
    }
    
    /* General input-group fix - ensure all elements stay in one row */
    .input-group {
        display: flex;
        flex-wrap: nowrap !important;
        align-items: stretch;
        width: 100%;
    }
    .input-group-text {
        flex-shrink: 0;
        display: flex !important;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }
    .input-group .form-control,
    .input-group .form-select {
        flex: 1 1 auto;
        min-width: 0;
        width: 1%;
    }
    .input-group .btn:not(.d-none) {
        flex-shrink: 0;
        display: flex !important;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }
    /* Ensure input-group elements don't wrap on any screen */
    .input-group > * {
        flex-wrap: nowrap;
    }
    
    /* Better spacing for sections */
    .mb-4 {
        margin-bottom: 2rem !important;
    }
    
    /* Modal without scroll - content fits */
    .modal-body {
        overflow-y: auto;
        max-height: calc(100vh - 180px);
    }
    
    .modal-lg {
        max-width: 900px;
    }
    
    /* Better focus states */
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        outline: none;
    }
    
    /* Icon sizing */
    .input-group-text i {
        font-size: 1.1rem;
    }
    
    /* Card hover effect */
    .card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: box-shadow 0.3s ease;
    }
    
    /* Image Preview Styles */
    #multiple_images_preview {
        gap: 10px;
        padding: 10px;
    }
    
    #multiple_images_preview > div {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    #multiple_images_preview > div:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
    }
    
    .remove-image-preview {
        border-radius: 50% !important;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    
    .remove-image-preview:hover {
        transform: scale(1.1);
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .remove-profile-image, .crop-profile-image {
        transition: all 0.2s ease;
    }
    
    .remove-profile-image:hover, .crop-profile-image:hover {
        transform: scale(1.1);
        box-shadow: 0 3px 6px rgba(0,0,0,0.4) !important;
    }
    
    /* Ensure preview buttons are visible when preview is shown */
    .profile-preview-container[style*="block"] .remove-profile-image,
    .profile-preview-container[style*="block"] .crop-profile-image {
        display: flex !important;
    }
    
    .profile-preview-container[style*="block"] #profile_preview {
        display: block !important;
    }
    
    /* Image preview styling */
    #profile_preview, #visiting_img {
        transition: opacity 0.3s ease;
    }
    
    #multiple_images_preview {
        min-height: 100px;
    }
    
    /* Ensure remove buttons are always visible on previews */
    #visiting_preview[style*="block"] .remove-visiting-doc {
        display: flex !important;
    }
    
    #multiple_images_preview:not(.d-none) .remove-image-preview {
        display: flex !important;
    }
    
    /* Cropper Modal Styles */
    #imageCropModal .modal-body {
        max-height: 70vh;
        overflow: auto;
    }
    
    #cropImage {
        display: block;
        max-width: 100%;
    }
    
    /* Profile Preview Container */
    .profile-upload-box .preview-container {
        position: relative;
        min-height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Recording Indicator Animation */
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .recording-indicator {
        animation: pulse 1s infinite;
    }
    
    .recording-indicator i.ti-record {
        color: #dc3545;
        animation: pulse 1s infinite;
        font-size: 1.1rem;
    }
    
    /* Mic button - only show on first row */
    .name-phone-row:not(:first-child) .mic-btn {
        display: none !important;
    }
</style>
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h2 class="fw-bold">All Customers</h2>
            </div>
        </div>
        <ul class="table-top-head">
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{ asset('assets/img/icons/pdf.svg') }}" alt="img"></a></li>
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{ asset('assets/img/icons/excel.svg') }}" alt="img"></a></li>
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a></li>
            <li><a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a></li>
        </ul>
        <div class="page-btn">
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                <i class="ti ti-user-plus me-1"></i>Add Customer
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
            <div class="d-flex justify-content-end mb-3">
                <input type="text" id="tableSearch" class="form-control w-100" placeholder="Search...">
            </div>
            <div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                <div class="dropdown">
                    <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">Status</a>
                    <ul class="dropdown-menu dropdown-menu-end p-3">
                        <li><a href="javascript:void(0);" class="dropdown-item rounded-1">Active</a></li>
                        <li><a href="javascript:void(0);" class="dropdown-item rounded-1">Inactive</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="searchableTable" class="table table-hover table-center" id="branchTable">
                    <thead class="thead-primary">
                        <tr>
                            <th>#</th>
                            <th>Customer Name</th>
                            <th>Profile Image</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $key => $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $item->names[0] ?? 'N/A' }}</td>
                            <td>
                                @if ($item->profile_img)
                                <a href="{{ asset($item->profile_img) }}" target="_blank">
                                    <img src="{{ asset($item->profile_img) }}" class="rounded" width='50px' height="50px" alt="">
                                </a>
                                @else
                                <img src="{{ asset('assets/img/profiles/avator1.jpg') }}" class="rounded" width='50px' height="50px" alt="">
                                @endif
                            </td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->phones[0] ?? 'N/A' }}</td>
                            <td>
                                <div class="edit-delete-action">
                                    <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#editCustomerModal{{ $item->id }}">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    <a href="javascript:void(0)"
                                        onclick="confirmDelete('delete-form-{{ $item->id }}')"
                                        class="p-2 text-danger">
                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                    </a>
                                    <!-- Hidden delete form -->
                                    <form id="delete-form-{{ $item->id }}"
                                        action="{{ route('customers.delete', $item->id) }}"
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
                            <td colspan="6" class="text-center text-muted">No customers found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $customers->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

{{-- Add Modal (Static) --}}
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold d-flex align-items-center mb-0">
                    <i class="ti ti-user-plus me-2"></i>Add New Customer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @include('admin.customers.modals.create-customer-form')
        </div>
    </div>
</div>

@forelse ($customers as $item)
<div class="modal fade" id="editCustomerModal{{ $item->id }}">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Customer</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @include('admin.customers.modals.edit-customer-form', ['customer' => $item])
        </div>
    </div>
</div>
@empty
@endforelse


@endsection
@push('scripts')
<!-- Cropper.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
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
            rows.forEach((row) => {
                const removeBtn = row.querySelector('.remove-row');
                if (removeBtn) {
                    if (rows.length > 1) {
                        removeBtn.style.display = 'flex';
                        removeBtn.classList.remove('d-none');
                        removeBtn.style.flexShrink = '0';
                    } else {
                        removeBtn.style.display = 'none';
                        removeBtn.classList.add('d-none');
                    }
                }
            });
        }
        
        function updateMicButtons(containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;
            const rows = container.querySelectorAll('.name-phone-row');
            rows.forEach((row, index) => {
                const micBtn = row.querySelector('.mic-btn');
                if (micBtn) {
                    // Only show mic button on first row (index 0)
                    if (index === 0) {
                        micBtn.style.display = 'flex';
                        micBtn.classList.remove('d-none');
                    } else {
                        micBtn.style.display = 'none';
                        micBtn.classList.add('d-none');
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
            inputField.style.removeProperty('color');
            inputField.style.removeProperty('textShadow');
            inputField.style.removeProperty('backgroundColor');
            inputField.placeholder = 'Enter customer full name';
            controlBtn.innerHTML = '<i class="ti ti-microphone"></i>';
            controlBtn.style.removeProperty('animation');
            controlBtn.classList.add('mic-btn');
            controlBtn.classList.remove('play-pause-btn');
            const audioContainer = nameCol.querySelector('.audio-player-container');
            if (audioContainer) audioContainer.remove();
            const recordingIndicator = nameCol.querySelector('.recording-indicator');
            if (recordingIndicator) recordingIndicator.remove();
            const hiddenInput = document.querySelector('input[name="voice_note"]');
            if (hiddenInput) hiddenInput.remove();
        }

        // Event Delegation for All Modals (click events)
        document.addEventListener('click', function(e) {
            // Add Name & Phone
            if (e.target.closest('#addNamePhone')) {
                e.preventDefault();
                e.stopPropagation();
                const btn = e.target.closest('#addNamePhone');
                const container = document.getElementById('namePhoneContainer');
                if (!container) return;
                
                const newRow = document.createElement('div');
                newRow.className = 'row g-2 mb-2 align-items-end name-phone-row';
                newRow.innerHTML = `
                    <div class="col-12 col-md-6">
                        <label class="form-label small mb-1">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-user"></i></span>
                            <input type="text" name="names[]" class="form-control" placeholder="Enter name" required>
                            <button type="button" class="btn btn-danger remove-row" title="Remove">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small mb-1">WhatsApp Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-phone"></i></span>
                            <input type="text" name="phones[]" class="form-control" placeholder="03XX-XXXXXXX">
                        </div>
                    </div>
                `;
                container.appendChild(newRow);
                updateRemoveButtons('namePhoneContainer');
                // Hide mic button on new rows (only first row should have it)
                updateMicButtons('namePhoneContainer');
            }

            // Remove Row
            if (e.target.closest('.remove-row')) {
                e.preventDefault();
                e.stopPropagation();
                const row = e.target.closest('.name-phone-row');
                if (row) {
                    row.remove();
                    updateRemoveButtons('namePhoneContainer');
                    updateMicButtons('namePhoneContainer');
                }
            }

            // Cancel Audio
            if (e.target.classList.contains('cancel-audio')) {
                const audioContainer = e.target.closest('.audio-player-container');
                if (audioContainer) {
                    const nameCol = audioContainer.parentElement;
                    const inputGroup = nameCol.querySelector('.input-group');
                    const inputField = inputGroup ? inputGroup.querySelector('input[type="text"]') : null;
                    const controlBtn = inputGroup ? inputGroup.querySelector('.mic-btn, .play-pause-btn') : null;
                    if (inputField && controlBtn && nameCol) resetRecordingUI(inputField, controlBtn, nameCol);
                }
            }

            // Delete Buttons
            if (e.target.classList.contains('delete-btn')) {
                const onclickAttr = e.target.getAttribute('onclick');
                const match = onclickAttr ? onclickAttr.match(/'([^']+)'/) : null;
                const fieldName = match ? match[1] : '';
                toggleDelete(e.target, fieldName);
            }


            // Credit Limit Toggle
            if (e.target.id === 'showCreditLimitOptions' || e.target.closest('#showCreditLimitOptions')) {
                e.preventDefault();
                e.stopPropagation();
                const btn = e.target.id === 'showCreditLimitOptions' ? e.target : e.target.closest('#showCreditLimitOptions');
                const defaultDiv = document.getElementById('creditLimitDefault');
                const optionsDiv = document.getElementById('creditLimitOptions');
                const noLimitRadio = document.getElementById('no_limit');
                const customRadio = document.getElementById('custom_limit');
                const inputDiv = document.getElementById('custom_limit_input');
                
                if (defaultDiv) defaultDiv.style.display = 'none';
                if (optionsDiv) {
                    optionsDiv.style.display = 'block';
                }
                // Ensure No Limit is selected by default
                if (noLimitRadio) {
                    noLimitRadio.checked = true;
                }
                if (customRadio) {
                    customRadio.checked = false;
                }
                if (inputDiv) {
                    inputDiv.style.display = 'none';
                }
            }
            
            if (e.target.id === 'hideCreditLimitOptions' || e.target.closest('#hideCreditLimitOptions')) {
                e.preventDefault();
                e.stopPropagation();
                const optionsDiv = document.getElementById('creditLimitOptions');
                const defaultDiv = document.getElementById('creditLimitDefault');
                const inputDiv = document.getElementById('custom_limit_input');
                const noLimitRadio = document.getElementById('no_limit');
                
                if (optionsDiv) optionsDiv.style.display = 'none';
                if (defaultDiv) defaultDiv.style.display = 'block';
                if (inputDiv) inputDiv.style.display = 'none';
                if (noLimitRadio) noLimitRadio.checked = true;
                const limitInput = document.querySelector('input[name="credit_limit"]');
                if (limitInput) limitInput.value = '';
            }
            
            // Credit Limit Type Radio Change
            if (e.target.name === 'credit_limit_type') {
                const inputDiv = document.getElementById('custom_limit_input');
                const noLimitRadio = document.getElementById('no_limit');
                if (e.target.value === 'custom' && inputDiv) {
                    inputDiv.style.display = 'block';
                } else {
                    if (inputDiv) {
                        inputDiv.style.display = 'none';
                    }
                    const limitInput = document.querySelector('input[name="credit_limit"]');
                    if (limitInput) limitInput.value = '';
                    // Ensure No Limit is checked if custom is unchecked
                    if (e.target.value !== 'custom' && noLimitRadio) {
                        noLimitRadio.checked = true;
                    }
                }
            }
        });

        // Microphone Logic (delegated)
        document.addEventListener('click', async function(e) {
            // Check if clicked element or its parent is a mic button
            const micBtn = e.target.closest('.mic-btn') || (e.target.classList.contains('ti-microphone') || e.target.closest('.ti-microphone') ? e.target.closest('button') : null);
            const playPauseBtn = e.target.closest('.play-pause-btn');
            const controlBtn = micBtn || playPauseBtn;
            
            if (!controlBtn) return;
            
            // Prevent default behavior
            e.preventDefault();
            e.stopPropagation();
            
            const inputGroup = controlBtn.closest('.input-group');
            if (!inputGroup) return;
            
            const inputField = inputGroup.querySelector('input[type="text"].speech-input, input[type="text"][name*="names"]');
            const nameCol = inputGroup.closest('.col-12, .col-md-6, .col-md-5');
            
            if (!inputField || !nameCol) return;

            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRecognition || !navigator.mediaDevices) {
                alert('Speech Recognition or Microphone not supported.');
                return;
            }

            let recognition = new SpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.lang = 'en-US';

            let mediaRecorder = null;
            let audioChunks = [];
            let transcript = '';

            if (playPauseBtn) {
                const audio = inputGroup.querySelector('audio');
                if (audio) {
                    if (audio.paused) {
                        audio.play();
                        controlBtn.innerHTML = '<i class="ti ti-pause"></i>';
                    } else {
                        audio.pause();
                        controlBtn.innerHTML = '<i class="ti ti-play"></i>';
                    }
                }
                return;
            }

            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                inputField.value = '';
                inputField.style.color = 'transparent';
                inputField.style.textShadow = '0 0 8px rgba(0,0,0,0.5)';
                inputField.placeholder = 'Listening... Speak now';
                
                // Add recording indicator
                const recordingIndicator = document.createElement('div');
                recordingIndicator.className = 'recording-indicator mt-1';
                recordingIndicator.innerHTML = `
                    <small class="text-danger fw-bold">
                        <i class="ti ti-record me-1" style="animation: pulse 1s infinite;"></i>Recording...
                    </small>
                `;
                nameCol.appendChild(recordingIndicator);
                
                const existingAudio = nameCol.querySelector('.audio-player-container');
                if (existingAudio) existingAudio.remove();
                const existingHiddenInput = document.querySelector('input[name="voice_note"]');
                if (existingHiddenInput) existingHiddenInput.remove();
                audioChunks = [];
                mediaRecorder = new MediaRecorder(stream);
                mediaRecorder.ondataavailable = (event) => audioChunks.push(event.data);
                mediaRecorder.onstop = () => {
                    // Remove recording indicator
                    const indicator = nameCol.querySelector('.recording-indicator');
                    if (indicator) indicator.remove();
                    
                    const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                    const audioURL = URL.createObjectURL(audioBlob);
                    const audioContainer = document.createElement('div');
                    audioContainer.className = 'audio-player-container mt-2';
                    audioContainer.innerHTML = `
                        <audio controls class="w-100">
                            <source src="${audioURL}" type="audio/webm">
                        </audio>
                        <button type="button" class="btn btn-sm btn-danger cancel-audio mt-1 float-end">
                            <i class="ti ti-x me-1"></i>Remove
                        </button>
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
                    const form = document.getElementById('customerForm');
                    if (form) form.appendChild(fileInput);
                    inputField.style.removeProperty('textShadow');
                    inputField.style.color = '';
                    inputField.style.backgroundColor = 'lightgreen';
                    inputField.placeholder = 'Voice transcribed';
                    if (transcript.trim()) inputField.value = transcript.trim();
                    controlBtn.innerHTML = '<i class="ti ti-microphone"></i>';
                    controlBtn.classList.add('mic-btn');
                    controlBtn.classList.remove('play-pause-btn');
                    stream.getTracks().forEach(track => track.stop());
                };
                mediaRecorder.start();
                recognition.start();
                controlBtn.innerHTML = '<i class="ti ti-stop text-danger"></i>';
                controlBtn.style.animation = 'pulse 1s infinite';
                recognition.onresult = (event) => { 
                    transcript = event.results[0][0].transcript;
                    if (transcript.trim()) {
                        inputField.value = transcript.trim();
                        inputField.style.color = '';
                        inputField.style.textShadow = '';
                        inputField.style.backgroundColor = '';
                    }
                };
                recognition.onerror = (event) => {
                    const indicator = nameCol.querySelector('.recording-indicator');
                    if (indicator) indicator.remove();
                    alert('Speech error: ' + event.error);
                    resetRecordingUI(inputField, controlBtn, nameCol);
                    if (mediaRecorder && mediaRecorder.state === 'recording') mediaRecorder.stop();
                    stream.getTracks().forEach(track => track.stop());
                };
                recognition.onend = () => {
                    if (mediaRecorder && mediaRecorder.state === 'recording') {
                        mediaRecorder.stop();
                    }
                    stream.getTracks().forEach(track => track.stop());
                };
            } catch (err) {
                alert('Microphone access denied: ' + err.message);
                resetRecordingUI(inputField, controlBtn, nameCol);
            }
        });

        // Store selected files for multiple images
        let selectedMultipleImages = [];
        let profileImageFile = null;
        let cropper = null;

        // File Input Previews (delegated)
        document.addEventListener('change', function(e) {
            if (e.target.id === 'profile_img') {
                const file = e.target.files[0];
                const preview = document.getElementById('profile_preview');
                const previewContainer = document.querySelector('.profile-preview-container');
                const removeBtn = document.querySelector('.remove-profile-image');
                const cropBtn = document.querySelector('.crop-profile-image');
                
                if (file && file.type.startsWith('image/')) {
                    profileImageFile = file;
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        if (preview) {
                            preview.src = ev.target.result;
                            preview.style.display = 'block';
                        }
                        if (previewContainer) {
                            previewContainer.style.display = 'block';
                        }
                        // Buttons are always visible when preview is shown (via CSS)
                    };
                    reader.readAsDataURL(file);
                } else {
                    if (previewContainer) {
                        previewContainer.style.display = 'none';
                    }
                    if (preview) {
                        preview.src = '';
                        preview.style.display = 'none';
                    }
                    profileImageFile = null;
                }
            }

            if (e.target.id === 'visiting_doc') {
                const file = e.target.files[0];
                const preview = document.getElementById('visiting_preview');
                const imgContainer = document.getElementById('visiting_img_container');
                const fileInfo = document.getElementById('visiting_file_info');
                const filename = document.getElementById('visiting_filename');
                const visitingImg = document.getElementById('visiting_img');
                const removeBtns = document.querySelectorAll('.remove-visiting-doc');
                
                if (file) {
                    if (preview) preview.style.display = 'block';
                    if (filename) filename.textContent = file.name;
                    
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(ev) {
                            if (visitingImg) {
                                visitingImg.src = ev.target.result;
                                visitingImg.style.display = 'block';
                            }
                        };
                        reader.readAsDataURL(file);
                        if (imgContainer) imgContainer.style.display = 'block';
                        if (fileInfo) fileInfo.style.display = 'none';
                    } else {
                        if (imgContainer) imgContainer.style.display = 'none';
                        if (fileInfo) fileInfo.style.display = 'block';
                    }
                    // Show remove buttons
                    removeBtns.forEach(btn => {
                        btn.style.display = 'flex';
                    });
                } else {
                    if (preview) preview.style.display = 'none';
                    if (imgContainer) imgContainer.style.display = 'none';
                    if (fileInfo) fileInfo.style.display = 'none';
                }
            }

            if (e.target.id === 'multiple_images') {
                const files = e.target.files;
                const previewContainer = document.getElementById('multiple_images_preview');
                
                if (files && files.length > 0) {
                    // Add new files to selected images array
                    Array.from(files).forEach((file) => {
                        if (file.type.startsWith('image/')) {
                            // Check if file already exists by name and size
                            const exists = selectedMultipleImages.find(f => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified);
                            if (!exists) {
                                selectedMultipleImages.push(file);
                            }
                        }
                    });
                    
                    // Update preview immediately
                    updateMultipleImagesPreview();
                }
            }
        });

        // Update multiple images preview
        function updateMultipleImagesPreview() {
            const previewContainer = document.getElementById('multiple_images_preview');
            
            if (!previewContainer) return;
            
            if (selectedMultipleImages.length > 0) {
                previewContainer.classList.remove('d-none');
                previewContainer.innerHTML = '';
                
                selectedMultipleImages.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        const div = document.createElement('div');
                        div.className = 'position-relative border rounded p-2 bg-white shadow-sm';
                        div.style.width = '130px';
                        div.style.height = '150px';
                        div.style.flexShrink = '0';
                        div.setAttribute('data-index', index);
                        div.innerHTML = `
                            <img src="${ev.target.result}" alt="${file.name}" class="img-fluid rounded mb-1" style="max-height: 110px; max-width: 100%; object-fit: cover; width: 100%; height: 110px; display: block;">
                            <small class="d-block text-muted text-truncate text-center" style="font-size: 0.7rem;" title="${file.name}">${file.name.length > 18 ? file.name.substring(0, 18) + '...' : file.name}</small>
                            <button type="button" class="btn btn-danger position-absolute top-0 end-0 m-1 remove-image-preview" data-index="${index}" style="width: 32px; height: 32px; padding: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.3);" title="Remove Image">
                                <i class="ti ti-x" style="font-size: 16px; font-weight: bold;"></i>
                            </button>
                        `;
                        previewContainer.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            } else {
                previewContainer.classList.add('d-none');
                previewContainer.innerHTML = '';
            }
            
            // Update file input
            updateMultipleImagesInput();
        }

        // Update multiple images file input
        function updateMultipleImagesInput() {
            const input = document.getElementById('multiple_images');
            if (!input) return;
            
            const dataTransfer = new DataTransfer();
            selectedMultipleImages.forEach(file => {
                dataTransfer.items.add(file);
            });
            input.files = dataTransfer.files;
        }

        // Remove profile image
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-profile-image')) {
                const profileInput = document.getElementById('profile_img');
                const preview = document.getElementById('profile_preview');
                const placeholder = document.querySelector('.profile-upload-box .upload-placeholder');
                const removeBtn = document.querySelector('.remove-profile-image');
                const cropBtn = document.querySelector('.crop-profile-image');
                const croppedInput = document.getElementById('profile_img_cropped');
                
                if (profileInput) profileInput.value = '';
                if (preview) {
                    preview.src = '';
                    preview.style.display = 'none';
                }
                if (placeholder) placeholder.classList.remove('d-none');
                if (removeBtn) removeBtn.style.display = 'none';
                if (cropBtn) cropBtn.style.display = 'none';
                if (croppedInput) croppedInput.value = '';
                profileImageFile = null;
                
                // Destroy cropper if exists
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
            }
            
            // Remove multiple image
            if (e.target.closest('.remove-image-preview')) {
                e.preventDefault();
                e.stopPropagation();
                const btn = e.target.closest('.remove-image-preview');
                const index = parseInt(btn.getAttribute('data-index'));
                if (!isNaN(index) && selectedMultipleImages[index] !== undefined) {
                    selectedMultipleImages.splice(index, 1);
                    updateMultipleImagesPreview();
                }
            }
            
            // Remove profile image
            if (e.target.closest('.remove-profile-image')) {
                e.preventDefault();
                e.stopPropagation();
                const profileInput = document.getElementById('profile_img');
                const preview = document.getElementById('profile_preview');
                const previewContainer = document.querySelector('.profile-preview-container');
                const croppedInput = document.getElementById('profile_img_cropped');
                
                if (profileInput) profileInput.value = '';
                if (preview) preview.src = '';
                if (previewContainer) previewContainer.style.display = 'none';
                if (croppedInput) croppedInput.value = '';
                profileImageFile = null;
                
                // Destroy cropper if exists
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
            }
            
            // Remove visiting document
            if (e.target.closest('.remove-visiting-doc')) {
                e.preventDefault();
                e.stopPropagation();
                const visitingInput = document.getElementById('visiting_doc');
                const visitingPreview = document.getElementById('visiting_preview');
                if (visitingInput) visitingInput.value = '';
                if (visitingPreview) visitingPreview.style.display = 'none';
            }
            
            // Crop profile image
            if (e.target.closest('.crop-profile-image')) {
                e.preventDefault();
                e.stopPropagation();
                
                const preview = document.getElementById('profile_preview');
                const cropModalElement = document.getElementById('imageCropModal');
                if (!cropModalElement) return;
                
                // Check if we have preview or file
                if (!preview || !preview.src) {
                    if (!profileImageFile) {
                        alert('Please select an image first');
                        return;
                    }
                }
                
                const cropModal = new bootstrap.Modal(cropModalElement);
                const cropImage = document.getElementById('cropImage');
                
                if (cropImage) {
                    // Use current preview if available, otherwise load from file
                    if (preview && preview.src) {
                        cropImage.src = preview.src;
                    } else if (profileImageFile) {
                        const reader = new FileReader();
                        reader.onload = function(ev) {
                            cropImage.src = ev.target.result;
                        };
                        reader.readAsDataURL(profileImageFile);
                    }
                    
                    cropModal.show();
                    
                    // Initialize cropper when modal is shown
                    const initCropper = function() {
                        if (cropper) {
                            cropper.destroy();
                            cropper = null;
                        }
                        
                        // Wait a bit for image to load
                        setTimeout(function() {
                            cropper = new Cropper(cropImage, {
                                aspectRatio: 1,
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
                            });
                        }, 100);
                    };
                    
                    cropModalElement.addEventListener('shown.bs.modal', initCropper, { once: true });
                }
            }
            
            // Crop and save button
            if (e.target.id === 'cropImageBtn' || e.target.closest('#cropImageBtn')) {
                e.preventDefault();
                e.stopPropagation();
                if (!cropper) {
                    alert('Cropper not initialized');
                    return;
                }
                
                const canvas = cropper.getCroppedCanvas({
                    width: 800,
                    height: 800,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });
                
                if (canvas) {
                    // Store cropped image as base64 first
                    canvas.toBlob(function(blob) {
                        const preview = document.getElementById('profile_preview');
                        const croppedInput = document.getElementById('profile_img_cropped');
                        const profileInput = document.getElementById('profile_img');
                        const previewContainer = document.querySelector('.profile-preview-container');
                        const removeBtn = document.querySelector('.remove-profile-image');
                        const cropBtn = document.querySelector('.crop-profile-image');
                        
                        // Create cropped image URL for preview
                        const url = URL.createObjectURL(blob);
                        
                        // Update preview with cropped image immediately
                        if (preview) {
                            preview.src = url;
                            preview.style.display = 'block';
                        }
                        if (previewContainer) {
                            previewContainer.style.display = 'block';
                        }
                        // Buttons are always visible when preview is shown
                        
                        // Create new file from blob and update file input
                        const originalFileName = profileImageFile ? profileImageFile.name : 'cropped_image.jpg';
                        const file = new File([blob], originalFileName, { type: blob.type || 'image/jpeg' });
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        if (profileInput) profileInput.files = dataTransfer.files;
                        // Update the stored file reference with cropped version
                        profileImageFile = file;
                        
                        // Store cropped image as base64 in hidden input
                        const reader = new FileReader();
                        reader.onload = function() {
                            if (croppedInput) croppedInput.value = reader.result;
                        };
                        reader.readAsDataURL(blob);
                        
                        // Close modal
                        const cropModalElement = document.getElementById('imageCropModal');
                        if (cropModalElement) {
                            const cropModal = bootstrap.Modal.getInstance(cropModalElement);
                            if (cropModal) cropModal.hide();
                        }
                        
                        // Destroy cropper after a short delay to allow modal to close
                        setTimeout(function() {
                            if (cropper) {
                                cropper.destroy();
                                cropper = null;
                            }
                        }, 300);
                        
                    }, 'image/jpeg', 0.9);
                }
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
            if (e.target.id === 'customerForm') {
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
                const isAdd = modalId === 'addCustomerModal';
                const form = this.querySelector('#customerForm');
                if (!form) return;

                if (isAdd) {
                    // Reset for add
                    const genBtn = form.querySelector('#generatePassword');
                    if (genBtn) genBtn.click();
                    const asOfDate = form.querySelector('#as_of_date');
                    if (asOfDate) asOfDate.value = new Date().toLocaleDateString('en-GB');
                    
                    // Reset fields
                    const profileInput = form.querySelector('#profile_img');
                    const multipleInput = form.querySelector('#multiple_images');
                    const visitingInput = form.querySelector('#visiting_doc');
                    if (profileInput) profileInput.value = '';
                    if (multipleInput) multipleInput.value = '';
                    if (visitingInput) visitingInput.value = '';
                    
                    const preview = form.querySelector('#profile_preview');
                    const previewContainer = form.querySelector('.profile-preview-container');
                    if (preview) preview.style.display = 'none';
                    if (previewContainer) previewContainer.style.display = 'none';
                    
                    // Reset name/phone rows
                    updateRemoveButtons('namePhoneContainer');
                    updateMicButtons('namePhoneContainer');
                    
                    // Reset credit limit - ensure No Limit is selected by default
                    const optionsDiv = form.querySelector('#creditLimitOptions');
                    const defaultDiv = form.querySelector('#creditLimitDefault');
                    const noLimitRadio = form.querySelector('#no_limit');
                    const customLimitRadio = form.querySelector('#custom_limit');
                    const customLimitInput = form.querySelector('#custom_limit_input');
                    const limitInput = form.querySelector('input[name="credit_limit"]');
                    
                    if (optionsDiv) optionsDiv.style.display = 'none';
                    if (defaultDiv) defaultDiv.style.display = 'block';
                    if (noLimitRadio) noLimitRadio.checked = true;
                    if (customLimitRadio) customLimitRadio.checked = false;
                    if (customLimitInput) customLimitInput.style.display = 'none';
                    if (limitInput) limitInput.value = '';
                    
                    // Reset multiple images
                    selectedMultipleImages = [];
                    updateMultipleImagesPreview();
                }
            });
        });
        
        // Reset form when modal is closed
        const addCustomerModal = document.getElementById('addCustomerModal');
        if (addCustomerModal) {
            addCustomerModal.addEventListener('hidden.bs.modal', function() {
                // Reset profile image
                const profileInput = document.getElementById('profile_img');
                const profilePreview = document.getElementById('profile_preview');
                const profilePlaceholder = document.querySelector('.profile-upload-box .upload-placeholder');
                const removeProfileBtn = document.querySelector('.remove-profile-image');
                const cropProfileBtn = document.querySelector('.crop-profile-image');
                const croppedInput = document.getElementById('profile_img_cropped');
                
                if (profileInput) profileInput.value = '';
                if (profilePreview) {
                    profilePreview.src = '';
                    profilePreview.style.display = 'none';
                }
                if (profilePlaceholder) profilePlaceholder.classList.remove('d-none');
                if (removeProfileBtn) removeProfileBtn.style.display = 'none';
                if (cropProfileBtn) cropProfileBtn.style.display = 'none';
                if (croppedInput) croppedInput.value = '';
                profileImageFile = null;
                
                // Reset multiple images
                selectedMultipleImages = [];
                updateMultipleImagesPreview();
                
                // Destroy cropper if exists
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
                
                // Reset visiting doc
                const visitingInput = document.getElementById('visiting_doc');
                const visitingPreview = document.getElementById('visiting_preview');
                if (visitingInput) visitingInput.value = '';
                if (visitingPreview) visitingPreview.style.display = 'none';
                
                // Reset mic buttons - ensure only first row has mic button
                updateMicButtons('namePhoneContainer');
                
                // Reset credit limit - ensure No Limit is default
                const noLimitRadio = document.getElementById('no_limit');
                const customLimitRadio = document.getElementById('custom_limit');
                const customLimitInput = document.getElementById('custom_limit_input');
                const limitInput = document.querySelector('input[name="credit_limit"]');
                if (noLimitRadio) noLimitRadio.checked = true;
                if (customLimitRadio) customLimitRadio.checked = false;
                if (customLimitInput) customLimitInput.style.display = 'none';
                if (limitInput) limitInput.value = '';
            });
            
            // Initialize mic buttons when modal opens
            addCustomerModal.addEventListener('shown.bs.modal', function() {
                updateMicButtons('namePhoneContainer');
                // Ensure No Limit is selected by default
                const noLimitRadio = document.getElementById('no_limit');
                if (noLimitRadio) noLimitRadio.checked = true;
            });
        }
    })();
</script>

    <script>
    document.getElementById('addMoreBtn').addEventListener('click', function () {

        let wrapper = document.getElementById('yearFields');
        let inputs  = wrapper.querySelectorAll('.yearpicker');

        let nextYear = '';

        // check last input value
        if (inputs.length > 0) {
            let lastValue = inputs[inputs.length - 1].value;

            if (lastValue !== '' && !isNaN(lastValue)) {
                nextYear = parseInt(lastValue) + 1;
            }
        }

        let div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `
            <input
                type="text"
                class="form-control yearpicker"
                name="carmanufactured_year[]"
                placeholder="Select Year"
                value="${nextYear}"
            >
            <button type="button" class="btn btn-danger btn-sm removeField">X</button>
        `;

        wrapper.appendChild(div);

        // re-init datepicker for new input
        initYearPicker();
    });

    // remove field
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('removeField')) {
            e.target.parentElement.remove();
        }
    });
    </script>
    <script>
        let currentTargetSelect = null;
        $(document).on('click', '.open-universal-modal', function() {
            const title = $(this).data('title');
            const route = $(this).data('route');
            currentTargetSelect = $(this).data('target-select');
            $('#universal-modal-title').text(title);
            $('#universal-form').attr('action', route);
            $('#universal-name').val('');
            $('#universal-image').val('');
            $('#image-field').toggle(title === 'Add Category');
            $('#universal-add-modal').modal('show');
        });
        $('#universal-form').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (!res || !res.success) {
                        // handle server error / validation returned by modal create endpoint
                        console.error('Modal save failed', res);
                        return;
                    }
                    // Parse id as integer to avoid string/NaN issues
                    const parsedId = parseInt(res.id, 10);
                    if (isNaN(parsedId)) {
                        console.error('Returned id is not numeric', res.id);
                        return;
                    }
                    const val = parsedId;
                    const text = res.name || res.title || 'New Item';
                    // For each target select (might be a selector string like ".category-select")
                    $(currentTargetSelect).each(function() {
                        // Make sure we're working with a real <select>
                        if (!$(this).is('select')) return;
                        // Create a fresh Option for THIS select (do NOT reuse the same node)
                        const option = new Option(text, val, true, true);
                        // Remove any duplicate option with same value (if any)
                        $(this).find(`option[value="${val}"]`).remove();
                        // Append the new option and set it as selected on the DOM element
                        $(this)[0].add(option);
                        // Set value and trigger change so plain <select> picks it up
                        $(this).val(val).trigger('change');
                        // --- Plugin-specific fixes ---
                        // Select2 (common class: select2-hidden-accessible)
                        if ($(this).hasClass('select2-hidden-accessible') && $(this).data(
                                'select2')) {
                            // Append via Select2-friendly way then trigger change
                            // (some setups require re-init, but this pattern is widely compatible)
                            const $sel = $(this);
                            // Ensure the option is present in DOM then tell select2 to update its value
                            $sel.append(new Option(text, val, true, true)).val(val).trigger(
                                'change.select2');
                        }
                        // Bootstrap selectpicker
                        if ($(this).hasClass('selectpicker') && typeof $(this).selectpicker ===
                            'function') {
                            // Append option, refresh plugin, then set value
                            $(this).append(new Option(text, val));
                            $(this).selectpicker('refresh');
                            $(this).selectpicker('val', val);
                        }
                        // Choices.js or other plugins: if you use a plugin with an instance, update via its API.
                        // e.g. if you keep an instance in window.choices_category: window.choices_category.setChoices([{ value: val, label: text, selected: true }], 'value', 'label', false);
                    });
                    // close modal
                    $('#universal-add-modal').modal('hide');
                },
                error: function(xhr) {
                    console.error('AJAX error', xhr.responseText || xhr);
                    // Optionally show server validation messages inside modal
                }
            });
        });

    </script>

@endpush
