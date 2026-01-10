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
            padding: 1rem !important;
        }
        .col-12, .col-md-6 {
            margin-bottom: 1rem;
        }
        .input-group-lg {
            flex-wrap: wrap;
        }
        .input-group-lg .btn {
            margin-top: 0.5rem;
            width: 100%;
        }
        .btn-group {
            flex-direction: column;
        }
        .btn-group .btn {
            border-radius: 0.375rem !important;
            margin-bottom: 0.5rem;
        }
        .card-body {
            padding: 1rem !important;
        }
        h6 {
            font-size: 0.85rem;
        }
        .form-label {
            font-size: 0.9rem;
        }
        .input-group-text {
            min-width: 40px;
            padding: 0.5rem 0.75rem;
        }
    }
    
    @media (max-width: 576px) {
        .modal-xl {
            max-width: 100%;
        }
        .modal-header h4 {
            font-size: 1.1rem;
        }
        .input-group-lg .form-control {
            font-size: 16px; /* Prevents zoom on iOS */
        }
        .btn {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
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
    }
    
    /* Better spacing for sections */
    .mb-4 {
        margin-bottom: 2rem !important;
    }
    
    /* Smooth scrolling */
    .modal-dialog-scrollable .modal-body {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
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
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 sticky-top" style="z-index: 1050;">
                <h4 class="modal-title fw-bold d-flex align-items-center">
                    <i class="ti ti-user-plus me-2"></i>
                    <span class="d-none d-sm-inline">Add New Customer</span>
                    <span class="d-sm-none">Add Customer</span>
                </h4>
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
                if (removeBtn) removeBtn.style.display = rows.length > 1 ? 'block' : 'none';
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
            inputField.placeholder = 'Enter name or use mic';
            inputField.value = '';
            controlBtn.innerHTML = '<i class="fas fa-microphone"></i>';
            controlBtn.classList.add('mic-btn');
            controlBtn.classList.remove('play-pause-btn');
            const audioContainer = nameCol.querySelector('.audio-player-container');
            if (audioContainer) audioContainer.remove();
            const hiddenInput = document.querySelector('input[name="voice_note"]');
            if (hiddenInput) hiddenInput.remove();
        }

        // Event Delegation for All Modals (click events)
        document.addEventListener('click', function(e) {
            // Add Name & Phone
            if (e.target.closest('#addNamePhone')) {
                const btn = e.target.closest('#addNamePhone');
                const container = btn.closest('.col-12').querySelector('#namePhoneContainer');
                if (!container) return;
                const newRow = document.createElement('div');
                newRow.className = 'row g-3 mb-3 align-items-end name-phone-row';
                newRow.innerHTML = `
                    <div class="col-md-5">
                        <label class="form-label">Name</label>
                        <div class="input-group">
                            <input type="text" name="names[]" class="form-control speech-input" placeholder="Enter name or use mic">
                            <button type="button" class="btn btn-outline-secondary mic-btn d-none"><i class="fas fa-microphone"></i></button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-row"><i class="fas fa-trash"></i></button>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phones[]" class="form-control" placeholder="Enter phone number">
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
        });

        // Microphone Logic (delegated)
        document.addEventListener('click', async function(e) {
            const micBtn = e.target.closest('.mic-btn');
            const playPauseBtn = e.target.closest('.play-pause-btn');
            const controlBtn = micBtn || playPauseBtn;
            if (!controlBtn) return;
            const inputGroup = controlBtn.closest('.input-group');
            if (!inputGroup) return;
            const inputField = inputGroup.querySelector('input[type="text"]');
            const nameCol = inputGroup.closest('.col-md-6, .col-md-5');
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
                inputField.placeholder = 'Listening... Speak now';
                const existingAudio = nameCol.querySelector('.audio-player-container');
                if (existingAudio) existingAudio.remove();
                const existingHiddenInput = document.querySelector('input[name="voice_note"]');
                if (existingHiddenInput) existingHiddenInput.remove();
                audioChunks = [];
                mediaRecorder = new MediaRecorder(stream);
                mediaRecorder.ondataavailable = (event) => audioChunks.push(event.data);
                mediaRecorder.onstop = () => {
                    const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                    const audioURL = URL.createObjectURL(audioBlob);
                    const audioContainer = document.createElement('div');
                    audioContainer.className = 'audio-player-container mt-2';
                    audioContainer.innerHTML = `
                        <audio controls class="w-100">
                            <source src="${audioURL}" type="audio/webm">
                        </audio>
                        <button type="button" class="btn btn-sm btn-danger cancel-audio mt-1 float-end"><i class="fas fa-trash"></i></button>
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
                    inputField.style.color = 'transparent';
                    inputField.style.backgroundColor = 'lightgreen';
                    inputField.placeholder = 'Voice transcribed (mic used)';
                    if (transcript.trim()) inputField.value = transcript.trim();
                    controlBtn.innerHTML = '<i class="fas fa-play"></i>';
                    controlBtn.classList.remove('mic-btn');
                    controlBtn.classList.add('play-pause-btn');
                    stream.getTracks().forEach(track => track.stop());
                };
                mediaRecorder.start();
                recognition.start();
                controlBtn.innerHTML = '<i class="fas fa-stop text-danger"></i>';
                recognition.onresult = (event) => { transcript = event.results[0][0].transcript; };
                recognition.onerror = (event) => {
                    alert('Speech error: ' + event.error);
                    resetRecordingUI(inputField, controlBtn, nameCol);
                    if (mediaRecorder && mediaRecorder.state === 'recording') mediaRecorder.stop();
                };
                recognition.onend = () => {
                    if (mediaRecorder && mediaRecorder.state === 'recording') mediaRecorder.stop();
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
                const placeholder = e.target.closest('.profile-upload-box').querySelector('.upload-placeholder');
                const removeBtn = document.querySelector('.remove-profile-image');
                const cropBtn = document.querySelector('.crop-profile-image');
                const existing = document.querySelector('.existing-image');
                
                if (file && file.type.startsWith('image/')) {
                    profileImageFile = file;
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        if (preview) {
                            preview.src = ev.target.result;
                            preview.style.display = 'block';
                        }
                        if (placeholder) placeholder.classList.add('d-none');
                        if (removeBtn) removeBtn.style.display = 'block';
                        if (cropBtn) cropBtn.style.display = 'block';
                        if (existing) existing.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                } else {
                    if (preview) {
                        preview.src = '';
                        preview.style.display = 'none';
                    }
                    if (placeholder) placeholder.classList.remove('d-none');
                    if (removeBtn) removeBtn.style.display = 'none';
                    if (cropBtn) cropBtn.style.display = 'none';
                    if (existing) existing.style.display = 'block';
                    profileImageFile = null;
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
                    if (preview) preview.style.display = 'block';
                    if (filename) filename.textContent = file.name;
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(ev) {
                            const img = document.getElementById('visiting_img');
                            if (img) img.src = ev.target.result;
                        };
                        reader.readAsDataURL(file);
                        if (imgContainer) imgContainer.style.display = 'block';
                        if (fileInfo) fileInfo.style.display = 'none';
                    } else {
                        if (imgContainer) imgContainer.style.display = 'none';
                        if (fileInfo) fileInfo.style.display = 'block';
                    }
                    if (existing) existing.style.display = 'none';
                } else {
                    if (preview) preview.style.display = 'none';
                    if (existing) existing.style.display = 'block';
                }
            }

            if (e.target.id === 'multiple_images') {
                const files = e.target.files;
                const previewContainer = document.getElementById('multiple_images_preview');
                const placeholder = e.target.closest('.multiple-upload-box').querySelector('.upload-placeholder');
                const existing = e.target.closest('.multiple-upload-box').querySelector('.existing-images');
                
                if (files.length > 0) {
                    // Add new files to selected images array
                    Array.from(files).forEach((file) => {
                        if (file.type.startsWith('image/') && !selectedMultipleImages.find(f => f.name === file.name && f.size === file.size)) {
                            selectedMultipleImages.push(file);
                        }
                    });
                    
                    // Update preview
                    updateMultipleImagesPreview();
                    
                    if (placeholder) placeholder.style.display = 'none';
                    if (existing) existing.style.display = 'none';
                }
            }
        });

        // Update multiple images preview
        function updateMultipleImagesPreview() {
            const previewContainer = document.getElementById('multiple_images_preview');
            const placeholder = document.querySelector('.multiple-upload-box .upload-placeholder');
            
            if (!previewContainer) return;
            
            if (selectedMultipleImages.length > 0) {
                previewContainer.classList.remove('d-none');
                previewContainer.innerHTML = '';
                
                selectedMultipleImages.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        const div = document.createElement('div');
                        div.className = 'position-relative border rounded p-2 bg-white shadow-sm';
                        div.style.width = '120px';
                        div.style.height = '140px';
                        div.style.flexShrink = '0';
                        div.setAttribute('data-index', index);
                        div.innerHTML = `
                            <img src="${ev.target.result}" alt="${file.name}" class="img-fluid rounded" style="max-height: 100px; max-width: 100%; object-fit: cover; width: 100%; height: 100px;">
                            <small class="d-block text-muted mt-1 text-truncate" style="font-size: 0.7rem;" title="${file.name}">${file.name.length > 15 ? file.name.substring(0, 15) + '...' : file.name}</small>
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-image-preview" data-index="${index}" style="width: 28px; height: 28px; padding: 0; z-index: 10;" title="Remove">
                                <i class="ti ti-x" style="font-size: 14px;"></i>
                            </button>
                        `;
                        previewContainer.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
                
                if (placeholder) placeholder.style.display = 'none';
            } else {
                previewContainer.classList.add('d-none');
                previewContainer.innerHTML = '';
                if (placeholder) placeholder.style.display = 'block';
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
                const btn = e.target.closest('.remove-image-preview');
                const index = parseInt(btn.getAttribute('data-index'));
                if (!isNaN(index) && selectedMultipleImages[index]) {
                    selectedMultipleImages.splice(index, 1);
                    updateMultipleImagesPreview();
                }
            }
            
            // Crop profile image
            if (e.target.closest('.crop-profile-image')) {
                if (!profileImageFile) {
                    alert('Please select an image first');
                    return;
                }
                
                const preview = document.getElementById('profile_preview');
                const cropModal = new bootstrap.Modal(document.getElementById('imageCropModal'));
                const cropImage = document.getElementById('cropImage');
                
                if (preview && cropImage) {
                    cropImage.src = preview.src;
                    cropModal.show();
                    
                    // Initialize cropper when modal is shown
                    document.getElementById('imageCropModal').addEventListener('shown.bs.modal', function() {
                        if (cropper) {
                            cropper.destroy();
                        }
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
                    }, { once: true });
                }
            }
            
            // Crop and save button
            if (e.target.id === 'cropImageBtn') {
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
                    canvas.toBlob(function(blob) {
                        const preview = document.getElementById('profile_preview');
                        const croppedInput = document.getElementById('profile_img_cropped');
                        const profileInput = document.getElementById('profile_img');
                        
                        // Create new file from blob
                        const file = new File([blob], profileImageFile.name, { type: blob.type });
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        if (profileInput) profileInput.files = dataTransfer.files;
                        
                        // Update preview
                        const url = URL.createObjectURL(blob);
                        if (preview) {
                            preview.src = url;
                            preview.style.display = 'block';
                        }
                        
                        // Store cropped image as base64
                        canvas.toBlob(function(blob) {
                            const reader = new FileReader();
                            reader.onload = function() {
                                if (croppedInput) croppedInput.value = reader.result;
                            };
                            reader.readAsDataURL(blob);
                        }, 'image/jpeg', 0.9);
                        
                        // Close modal
                        const cropModal = bootstrap.Modal.getInstance(document.getElementById('imageCropModal'));
                        if (cropModal) cropModal.hide();
                        
                        // Destroy cropper
                        if (cropper) {
                            cropper.destroy();
                            cropper = null;
                        }
                    }, 'image/jpeg', 0.9);
                }
            }
        });
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
