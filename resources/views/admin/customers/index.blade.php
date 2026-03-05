@extends('layouts.app')
@section('title','All Customers')
@push('styles')
<style>
    /* Add Vehicle modal (from Add Customer): show above Add Customer modal */
    #add-vehicle-row-modal.modal { z-index: 1065; }
    #add-vehicle-row-modal.modal .modal-backdrop { z-index: 1060; }
    #add-vehicle-row-modal .modal-dialog { max-width: 360px; }

    /* Edit Customer modal: fit in viewport, card form, simple & compact */
    [id^="editCustomerModal"] .modal-dialog { max-width: 720px; }
    [id^="editCustomerModal"] .modal-content { max-height: 90vh; display: flex; flex-direction: column; }
    [id^="editCustomerModal"] .modal-body { overflow-y: auto; max-height: calc(90vh - 120px); padding: 0.75rem 1rem; }
    [id^="editCustomerModal"] .modal-body .card { margin-bottom: 0.75rem; }
    [id^="editCustomerModal"] .modal-body .card:last-child { margin-bottom: 0; }
    [id^="editCustomerModal"] .modal-body .card-body { padding: 0.75rem 1rem; }
    [id^="editCustomerModal"] .modal-footer { flex-shrink: 0; padding: 0.5rem 1rem; }
</style>
@endpush
@section('content')
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
            @can('add_customer')
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
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
                            <th>Branch</th>
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
                            <td>{{ (is_array($item->phones ?? null) && count($item->phones) > 0) ? $item->phones[0] : 'N/A' }}</td>
                            <td>{{ $item->branch ? $item->branch->branch_name . ($item->branch->branch_code ? ' (' . $item->branch->branch_code . ')' : '') : '—' }}</td>
                            <td>
                                <div class="edit-delete-action">
                                    @can('update_customer')
                                    <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#editCustomerModal{{ $item->id }}">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    @endcan
                                    @can('view_customer')
                                    <a class="me-2 p-2 text-success" href="javascript:void(0)" 
                                       onclick="showCustomerLedger({{ $item->id }})" 
                                       title="Ledger Report">
                                        <i data-feather="file-text" class="feather-file-text"></i>
                                    </a>
                                    @endcan
                                    @can('delete_customer')
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
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No customers found</td>
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
@can('add_customer')
<div class="modal fade" id="addCustomerModal">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Customer</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @include('admin.customers.modals.create-customer-form')
        </div>
    </div>
</div>
@endcan

{{-- Modal: Add vehicle (for Add Customer form – opens when "Add another vehicle" is clicked) --}}
<div class="modal fade" id="add-vehicle-row-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Add Vehicle</h6>
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div class="mb-2">
                    <label class="form-label small mb-0">Plate # <span class="text-danger">*</span></label>
                    <input type="text" id="av-modal-plate" class="form-control form-control-sm" placeholder="e.g. ABC-123" maxlength="50">
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-0">Make <span class="text-danger">*</span></label>
                    <input type="text" id="av-modal-make" class="form-control form-control-sm" placeholder="Make">
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-0">Model <span class="text-danger">*</span></label>
                    <input type="text" id="av-modal-model" class="form-control form-control-sm" placeholder="Model">
                </div>
                <div class="mb-3">
                    <label class="form-label small mb-0">Year <span class="text-danger">*</span></label>
                    <input type="text" id="av-modal-year" class="form-control form-control-sm" placeholder="Year" maxlength="4">
                </div>
                <button type="button" id="add-vehicle-row-modal-submit" class="btn btn-primary btn-sm w-100">
                    <i class="ti ti-plus me-1"></i>Add vehicle
                </button>
            </div>
        </div>
    </div>
</div>

@forelse ($customers as $item)
@can('update_customer')
<div class="modal fade" id="editCustomerModal{{ $item->id }}">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @include('admin.customers.modals.edit-customer-form', ['customer' => $item])
        </div>
    </div>
</div>
@endcan
@empty
@endforelse

{{-- Add Vehicle for Customer (when editing a customer) --}}
<div class="modal fade" id="addVehicleForCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Add Vehicle for this Customer</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addVehicleForCustomerForm">
                @csrf
                <input type="hidden" name="customer_id" id="addVehicleCustomerId" value="">
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small mb-0">Plate Number <span class="text-danger">*</span></label>
                        <input type="text" name="plate_number" id="addVehiclePlate" class="form-control form-control-sm" placeholder="e.g. ABC-1234" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-0">Make <span class="text-danger">*</span></label>
                        <input type="text" name="make" id="addVehicleMake" class="form-control form-control-sm" placeholder="e.g. Toyota" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-0">Model <span class="text-danger">*</span></label>
                        <input type="text" name="model" id="addVehicleModel" class="form-control form-control-sm" placeholder="e.g. Corolla" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-0">Year <span class="text-danger">*</span></label>
                        <input type="text" name="year" id="addVehicleYear" class="form-control form-control-sm" placeholder="e.g. 2023" required maxlength="4">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Vehicle for Customer (when editing a customer) --}}
<div class="modal fade" id="editVehicleForCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Edit Vehicle</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editVehicleForCustomerForm">
                @csrf
                <input type="hidden" id="editVehicleId" value="">
                <input type="hidden" id="editVehicleCustomerId" value="">
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small mb-0">Plate Number <span class="text-danger">*</span></label>
                        <input type="text" name="plate_number" id="editVehiclePlate" class="form-control form-control-sm" placeholder="e.g. ABC-1234" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-0">Make <span class="text-danger">*</span></label>
                        <input type="text" name="make" id="editVehicleMake" class="form-control form-control-sm" placeholder="e.g. Toyota" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-0">Model <span class="text-danger">*</span></label>
                        <input type="text" name="model" id="editVehicleModel" class="form-control form-control-sm" placeholder="e.g. Corolla" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-0">Year <span class="text-danger">*</span></label>
                        <input type="text" name="year" id="editVehicleYear" class="form-control form-control-sm" placeholder="e.g. 2023" required maxlength="4">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>

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

            // Add another vehicle: open modal so user can add vehicle details there
            if (e.target.closest('#add-another-vehicle-btn')) {
                e.preventDefault();
                const plateEl = document.getElementById('av-modal-plate');
                const makeEl = document.getElementById('av-modal-make');
                const modelEl = document.getElementById('av-modal-model');
                const yearEl = document.getElementById('av-modal-year');
                if (plateEl) plateEl.value = '';
                if (makeEl) makeEl.value = '';
                if (modelEl) modelEl.value = '';
                if (yearEl) yearEl.value = '';
                const modalEl = document.getElementById('add-vehicle-row-modal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
                setTimeout(function() { if (plateEl) plateEl.focus(); }, 300);
            }

            // Modal "Add vehicle" submit: append card (display only + hidden inputs) to Add Customer form and close modal
            if (e.target.closest('#add-vehicle-row-modal-submit')) {
                e.preventDefault();
                const plate = (document.getElementById('av-modal-plate') && document.getElementById('av-modal-plate').value || '').trim();
                const make = (document.getElementById('av-modal-make') && document.getElementById('av-modal-make').value || '').trim();
                const model = (document.getElementById('av-modal-model') && document.getElementById('av-modal-model').value || '').trim();
                const year = (document.getElementById('av-modal-year') && document.getElementById('av-modal-year').value || '').trim();
                if (!plate || !make || !model || !year) {
                    alert('Please fill Plate #, Make, Model and Year.');
                    return;
                }
                const container = document.getElementById('add-customer-vehicles-container');
                if (!container) return;
                const emptyEl = document.getElementById('add-customer-vehicles-empty');
                const rows = container.querySelectorAll('.add-customer-vehicle-row');
                const index = rows.length;
                const esc = function(s) { return (s || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); };
                const card = document.createElement('div');
                card.className = 'add-customer-vehicle-row card border shadow-none mb-0';
                card.style.cssText = 'border-radius: 12px; background: #f8f9fa;';
                card.innerHTML = `
                    <div class="card-body p-2 position-relative">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-add-customer-vehicle position-absolute top-0 end-0" style="padding: 2px 6px; font-size: 0.75rem;"><i class="ti ti-x"></i></button>
                        <p class="mb-0 fw-bold text-uppercase" style="color: #4a90e2; font-size: 10px; letter-spacing: 0.5px;">Active Vehicle</p>
                        <h6 class="mb-0 fw-bold mt-1" style="color: #1e3a8a; font-size: 14px;">${esc(plate)}</h6>
                        <p class="mb-0 fw-semibold small" style="color: #1e3a8a;">${esc(make)} ${esc(model)}</p>
                        <p class="mb-0 small text-muted">Year: ${esc(year)}</p>
                        <input type="hidden" name="vehicles[${index}][plate_number]" value="${esc(plate)}">
                        <input type="hidden" name="vehicles[${index}][make]" value="${esc(make)}">
                        <input type="hidden" name="vehicles[${index}][model]" value="${esc(model)}">
                        <input type="hidden" name="vehicles[${index}][year]" value="${esc(year)}">
                    </div>
                `;
                container.appendChild(card);
                if (emptyEl) emptyEl.style.display = 'none';
                bootstrap.Modal.getInstance(document.getElementById('add-vehicle-row-modal')).hide();
                document.getElementById('av-modal-plate').value = '';
                document.getElementById('av-modal-make').value = '';
                document.getElementById('av-modal-model').value = '';
                document.getElementById('av-modal-year').value = '';
            }

            // Remove vehicle card (Add Customer modal)
            if (e.target.closest('.remove-add-customer-vehicle')) {
                const row = e.target.closest('.add-customer-vehicle-row');
                const container = document.getElementById('add-customer-vehicles-container');
                const emptyEl = document.getElementById('add-customer-vehicles-empty');
                if (!row || !container) return;
                row.remove();
                container.querySelectorAll('.add-customer-vehicle-row').forEach(function(r, i) {
                    r.querySelectorAll('input').forEach(function(inp) {
                        const name = inp.getAttribute('name');
                        if (name && name.startsWith('vehicles[')) {
                            inp.setAttribute('name', name.replace(/vehicles\[\d+\]/, 'vehicles[' + i + ']'));
                        }
                    });
                });
                if (container.querySelectorAll('.add-customer-vehicle-row').length === 0 && emptyEl) {
                    emptyEl.style.display = 'block';
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
    
    <!-- Customer Ledger Modal -->
    <div class="modal fade" id="customerLedgerModal" tabindex="-1" aria-labelledby="customerLedgerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Customer Ledger Report</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="customerLedgerModalBody">
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
    
    <script>
        // Customer Ledger Function
        function showCustomerLedger(customerId) {
            // Show loading
            const modalBody = document.getElementById('customerLedgerModalBody');
            if (modalBody) {
                modalBody.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            }
            
            // Open modal
            const modalElement = document.getElementById('customerLedgerModal');
            if (!modalElement) return;
            
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            
            // Fetch ledger data
            fetch(`/customers/${customerId}/ledger`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = '<div class="ledger-report">';
                    
                    // Customer Info
                    html += '<div class="row mb-4">';
                    html += '<div class="col-md-6">';
                    html += '<h5 class="mb-3">Customer Information</h5>';
                    html += '<table class="table table-bordered">';
                    html += `<tr><th width="40%">Name:</th><td>${data.customer.name}</td></tr>`;
                    html += `<tr><th>Email:</th><td>${data.customer.email}</td></tr>`;
                    html += `<tr><th>Phone:</th><td>${data.customer.phone}</td></tr>`;
                    html += '</table>';
                    html += '</div>';
                    html += '<div class="col-md-6">';
                    html += '<h5 class="mb-3">Balance Summary</h5>';
                    html += '<table class="table table-bordered">';
                    html += `<tr><th width="40%">Opening Balance:</th><td class="fw-bold">${data.opening_balance}</td></tr>`;
                    html += `<tr><th>Total Debit:</th><td class="text-danger">${data.total_debit}</td></tr>`;
                    html += `<tr><th>Total Credit:</th><td class="text-success">${data.total_credit}</td></tr>`;
                    html += `<tr><th>Ending Balance:</th><td class="fw-bold text-primary fs-5">${data.ending_balance}</td></tr>`;
                    html += `<tr><th>Balance Type:</th><td>${data.balance_type == 'receive' ? 'To Receive (Customer Owes)' : 'To Pay (We Owe)'}</td></tr>`;
                    html += '</table>';
                    html += '</div>';
                    html += '</div>';
                    
                    // Transactions Table
                    html += '<h5 class="mb-3">Transaction Details</h5>';
                    html += '<div class="table-responsive">';
                    html += '<table class="table table-bordered table-hover table-striped">';
                    html += '<thead class="table-light">';
                    html += '<tr>';
                    html += '<th>Date</th>';
                    html += '<th>Time</th>';
                    html += '<th>Type</th>';
                    html += '<th>Reference</th>';
                    html += '<th>Description</th>';
                    html += '<th>Branch</th>';
                    html += '<th>User</th>';
                    html += '<th class="text-end">Debit</th>';
                    html += '<th class="text-end">Credit</th>';
                    html += '<th class="text-end">Balance</th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';
                    
                    if (data.transactions.length > 0) {
                        data.transactions.forEach(function(trans) {
                            html += '<tr>';
                            html += `<td>${trans.date}</td>`;
                            html += `<td>${trans.time}</td>`;
                            html += `<td><span class="badge bg-info">${trans.type}</span></td>`;
                            html += `<td>${trans.reference}</td>`;
                            html += `<td>${trans.description}</td>`;
                            html += `<td>${trans.branch}</td>`;
                            html += `<td>${trans.user}</td>`;
                            html += `<td class="text-end text-danger">${parseFloat(trans.debit).toFixed(2)}</td>`;
                            html += `<td class="text-end text-success">${parseFloat(trans.credit).toFixed(2)}</td>`;
                            html += `<td class="text-end fw-bold">${parseFloat(trans.balance).toFixed(2)}</td>`;
                            html += '</tr>';
                        });
                    } else {
                        html += '<tr><td colspan="10" class="text-center text-muted">No transactions found</td></tr>';
                    }
                    
                    html += '</tbody>';
                    html += '<tfoot class="table-light">';
                    html += '<tr>';
                    html += '<th colspan="7" class="text-end">Totals:</th>';
                    html += `<th class="text-end text-danger">${data.total_debit}</th>`;
                    html += `<th class="text-end text-success">${data.total_credit}</th>`;
                    html += `<th class="text-end fw-bold">${data.ending_balance}</th>`;
                    html += '</tr>';
                    html += '</tfoot>';
                    html += '</table>';
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
    </script>

    {{-- Add Vehicle for Customer (from Edit Customer modal) --}}
    <script>
        $(document).on('click', '.btn-add-customer-vehicle', function() {
            var customerId = $(this).data('customer-id');
            if (!customerId) return;
            $('#addVehicleCustomerId').val(customerId);
            $('#addVehiclePlate').val('');
            $('#addVehicleMake').val('');
            $('#addVehicleModel').val('');
            $('#addVehicleYear').val('');
            $('#addVehicleForCustomerModal').modal('show');
        });

        $('#addVehicleForCustomerForm').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var customerId = $('#addVehicleCustomerId').val();
            if (!customerId) {
                alert('Customer not set');
                return;
            }
            var payload = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                customer_id: customerId,
                plate_number: ($('#addVehiclePlate').val() || '').trim().toUpperCase(),
                make: ($('#addVehicleMake').val() || '').trim(),
                model: ($('#addVehicleModel').val() || '').trim(),
                year: ($('#addVehicleYear').val() || '').trim()
            };
            if (!payload.plate_number || !payload.make || !payload.model || !payload.year) {
                alert('Please fill all fields');
                return;
            }
            var $submitBtn = $form.find('button[type="submit"]');
            $submitBtn.prop('disabled', true).text('Saving...');
            $.ajax({
                url: '{{ route("customer.vehicles.store") }}',
                type: 'POST',
                data: payload,
                success: function(res) {
                    $submitBtn.prop('disabled', false).text('Save Vehicle');
                    $('#addVehicleForCustomerModal').modal('hide');
                    if (res && res.success) {
                        var vehiclesUrl = '{{ url("/customers") }}/' + customerId + '/vehicles';
                        $.get(vehiclesUrl).done(function(data) {
                            if (!data.success || !data.vehicles) return;
                            var $editModal = $('#editCustomerModal' + customerId);
                            if (!$editModal.length) return;
                            var $grid = $editModal.find('[id^="edit-customer-vehicles-grid-"]');
                            var $select = $editModal.find('select#carnumber');
                            if ($grid.length) {
                                var html = '';
                                if (data.vehicles.length > 0) {
                                    data.vehicles.forEach(function(v) {
                                        var plate = (v.plateNumber || '—').toString();
                                        var make = (v.make || '').toString();
                                        var model = (v.model || '').toString();
                                        var year = (v.year || '—').toString();
                                        html += '<div class="card border shadow-none mb-0 vehicle-tile position-relative" style="border-radius: 10px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-color: #e2e8f0 !important;" data-car-id="' + (v.id || '') + '" data-customer-id="' + customerId + '" data-plate="' + plate + '" data-make="' + make + '" data-model="' + model + '" data-year="' + year + '"><div class="card-body p-2">';
                                        html += '<button type="button" class="btn btn-outline-primary btn-sm btn-edit-customer-vehicle position-absolute top-0 end-0" style="padding: 2px 6px; font-size: 0.7rem;" title="Edit vehicle"><i data-feather="edit-2" style="width: 12px; height: 12px;"></i></button>';
                                        html += '<p class="mb-0 fw-bold text-uppercase" style="color: #4a90e2; font-size: 10px; letter-spacing: 0.5px;">Active Vehicle</p>';
                                        html += '<h6 class="mb-0 fw-bold mt-1 vehicle-tile-plate" style="color: #1e3a8a; font-size: 14px;">' + plate + '</h6>';
                                        html += '<p class="mb-0 fw-semibold small vehicle-tile-make-model" style="color: #1e3a8a;">' + (make && model ? make.toUpperCase() + ' ' + model.toUpperCase() : make || model || '—') + '</p>';
                                        html += '<p class="mb-0 small text-muted vehicle-tile-year">Year: ' + year + '</p></div></div>';
                                    });
                                    if (typeof feather !== 'undefined') feather.replace();
                                } else {
                                    html = '<div class="vehicles-empty-state rounded-2 border bg-light d-flex align-items-center justify-content-center text-center py-3 px-3" style="grid-column: 1 / -1; min-height: 72px; border-style: dashed; border-color: #dee2e6;"><div><i class="fas fa-car-side text-muted mb-1" style="font-size: 1.25rem; opacity: 0.6;"></i><p class="text-muted mb-0 small">No vehicles yet. Add one below.</p></div></div>';
                                }
                                $grid.html(html);
                            }
                            if ($select.length) {
                                var opts = '<option value="">Select vehicle</option>';
                                data.vehicles.forEach(function(v) {
                                    var label = (v.plateNumber || '—') + ' - ' + (v.make || '').toUpperCase() + ' ' + (v.model || '').toUpperCase();
                                    if (v.year) label += ' (' + v.year + ')';
                                    opts += '<option value="' + (v.id || '') + '">' + label + '</option>';
                                });
                                $select.html(opts);
                            }
                        });
                    }
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false).text('Save Vehicle');
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || (xhr.responseJSON && xhr.responseJSON.errors) ? JSON.stringify(xhr.responseJSON.errors) : (xhr.statusText || 'Failed to save');
                    alert('Could not save vehicle: ' + msg);
                }
            });
        });

        // Edit vehicle: open modal with vehicle data
        $(document).on('click', '.btn-edit-customer-vehicle', function() {
            var $tile = $(this).closest('.vehicle-tile');
            if (!$tile.length) return;
            var carId = $tile.data('car-id');
            var customerId = $tile.data('customer-id');
            $('#editVehicleId').val(carId);
            $('#editVehicleCustomerId').val(customerId);
            $('#editVehiclePlate').val($tile.data('plate') || '');
            $('#editVehicleMake').val($tile.data('make') || '');
            $('#editVehicleModel').val($tile.data('model') || '');
            $('#editVehicleYear').val($tile.data('year') || '');
            $('#editVehicleForCustomerModal').modal('show');
        });

        $('#editVehicleForCustomerForm').on('submit', function(e) {
            e.preventDefault();
            var vehicleId = $('#editVehicleId').val();
            var customerId = $('#editVehicleCustomerId').val();
            if (!vehicleId) {
                alert('Vehicle not set');
                return;
            }
            var payload = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                plate_number: ($('#editVehiclePlate').val() || '').trim().toUpperCase(),
                make: ($('#editVehicleMake').val() || '').trim(),
                model: ($('#editVehicleModel').val() || '').trim(),
                year: ($('#editVehicleYear').val() || '').trim()
            };
            if (!payload.plate_number || !payload.make || !payload.model || !payload.year) {
                alert('Please fill all fields');
                return;
            }
            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            $submitBtn.prop('disabled', true).text('Updating...');
            var updateUrl = '{{ url("/customer-vehicles") }}/' + vehicleId;
            $.ajax({
                url: updateUrl,
                type: 'PUT',
                data: payload,
                success: function(res) {
                    $submitBtn.prop('disabled', false).text('Update Vehicle');
                    $('#editVehicleForCustomerModal').modal('hide');
                    if (res && res.success && customerId) {
                        var $editModal = $('#editCustomerModal' + customerId);
                        var $tile = $editModal.find('.vehicle-tile[data-car-id="' + vehicleId + '"]');
                        if ($tile.length) {
                            $tile.data('plate', payload.plate_number).data('make', payload.make).data('model', payload.model).data('year', payload.year);
                            $tile.find('.vehicle-tile-plate').text(payload.plate_number);
                            $tile.find('.vehicle-tile-make-model').text((payload.make + ' ' + payload.model).toUpperCase().trim() || '—');
                            $tile.find('.vehicle-tile-year').text('Year: ' + (payload.year || '—'));
                        }
                        var $select = $editModal.find('select#carnumber');
                        if ($select.length) {
                            var label = payload.plate_number + ' - ' + (payload.make + ' ' + payload.model).toUpperCase().trim();
                            if (payload.year) label += ' (' + payload.year + ')';
                            $select.find('option[value="' + vehicleId + '"]').text(label);
                        }
                    }
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false).text('Update Vehicle');
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || (xhr.responseJSON && xhr.responseJSON.errors) ? JSON.stringify(xhr.responseJSON.errors) : (xhr.statusText || 'Failed to update');
                    alert('Could not update vehicle: ' + msg);
                }
            });
        });
    </script>

@endpush
