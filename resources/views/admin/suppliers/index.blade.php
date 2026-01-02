@extends('layouts.app')
@section('title','All Suppliers')
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
                <table id="searchableTable" class="table table-hover table-center" id="supplierTable">
                    <thead class="thead-primary">
                        <tr>
                            <th>#</th>
                            <th>Supplier Name</th>
                            <th>Profile Image</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suppliers as $key => $item)
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
                                    <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#editSupplierModal{{ $item->id }}">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    <a class="p-2 text-danger" href="{{ route('suppliers.delete', $item->id) }}" onclick="return confirm('Are you sure?')">
                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                    </a>
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


@endsection
@push('scripts')

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
                    const form = document.getElementById('supplierForm');
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

        // File Input Previews (delegated)
        document.addEventListener('change', function(e) {
            if (e.target.id === 'profile_img') {
                const file = e.target.files[0];
                const preview = document.getElementById('profile_preview');
                const placeholder = e.target.closest('.profile-upload-box').querySelector('.upload-placeholder');
                const uploadBtn = e.target.closest('.profile-upload-box').querySelector('.upload-btn');
                const existing = document.querySelector('.existing-image');
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        if (preview) {
                            preview.src = ev.target.result;
                            preview.style.display = 'block';
                        }
                        if (placeholder) placeholder.classList.add('d-none');
                        if (uploadBtn) uploadBtn.classList.add('d-none');
                        if (existing) existing.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
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
</script>

@endpush
