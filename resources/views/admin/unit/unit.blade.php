@extends('layouts.app')
@section('title', __('Units List'))
@section('content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h2 class="fw-bold">All Units</h2>
            </div>
        </div>
        <!-- table top header -->
    <ul class="table-top-head">
        <li>
                <a href="#" class="export-pdf" data-bs-toggle="tooltip" data-bs-placement="top" title="PDF">
                    <img src="{{ asset('assets/img/icons/pdf.svg') }}" alt="PDF Export">
                </a>
            </li>
            <li>
                <a href="#" class="export-excel" data-bs-toggle="tooltip" data-bs-placement="top" title="Excel">
                    <img src="{{ asset('assets/img/icons/excel.svg') }}" alt="Excel Export">
                </a>
            </li>
        <li>
            <a href="javascript:void(0)" class="table-refresh" data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh">
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
            <a href="javascript:void(0)" class="btn btn-primary" onclick="openUnitModal('add')"><i
                    class="ti ti-circle-plus me-1"></i>Add Unit</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-end flex-wrap row-gap-3">
            <div class="table-dropdown ">
                <div class="">
                    <a href="javascript:void(0);"
                        class="dropdown-toggle btn btn-white btn-md d-inline-flex"
                        data-bs-toggle="dropdown">
                        Status
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a href="javascript:void(0);" class="dropdown-item rounded-1">Active</a></li>
                        <li><a href="javascript:void(0);" class="dropdown-item rounded-1">Inactive</a></li>
                    </ul>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <input type="text" id="tableSearch" class="form-control w-100" placeholder="Search...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="searchableTable" class="table table-hover table-center">
                    <thead class="thead-primary">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Short Name</th>
                            <th>Allow Decimal</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($units as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->name }}
                                @php
                                    $baseUnits = $item->baseUnits;
                                    $hasOldBaseUnit = $item->base_unit_id && $baseUnits->count() == 0;
                                @endphp
                                @if($hasOldBaseUnit)
                                    <span class="text-muted small">( {{ $item->base_unit_multiplier??'' }} - {{ $item->baseUnit->name??'' }} )</span>
                                @elseif($baseUnits->count() > 0)
                                    <div class="small text-muted">
                                        @foreach($baseUnits as $baseUnit)
                                            <span class="badge bg-info me-1">{{ $baseUnit->pivot->multiplier }} - {{ $baseUnit->name }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>{{ $item->short_name }}</td>
                            <td>
                                @if ($item->allow_decimal === 1)
                                <span>Yes</span>
                                @else
                                <span>No</span>
                                @endif
                            </td>
                            <td>
                                @if ($item->status === 'active')
                                <span class="badge badge-success">Active</span>
                                @else
                                <span class="badge badge-danger">Inactive</span>
                                @endif
                                <div class="form-group mt-2">
                                    <form action="{{ route('update.units.status', $item->id) }}" method="POST"
                                        class="status-form">
                                        @csrf
                                        @method('PATCH')
                                        <div
                                            class="status-toggle modal-status d-flex justify-content-between align-items-center">
                                            <input type="checkbox" id="status-{{ $item->id }}"
                                                class="check status-checkbox" {{ $item->status == 'active' ? 'checked' :
                                            '' }}>
                                            <label for="status-{{ $item->id }}" class="checktoggle"></label>
                                        </div>
                                    </form>
                                </div>
                            </td>
                            <td class="action-table-data">
                                <div class="edit-delete-action">
                                    <a class="me-2 p-2" href="javascript:void(0)" onclick="openUnitModal('edit', {{ $item->id }})">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    <!-- Delete button with confirmation -->
                                        <a href="javascript:void(0)"
                                        onclick="confirmDelete('delete-form-{{ $item->id }}')"
                                        class="p-2">
                                            <i data-feather="trash-2" class="feather-trash-2"></i>
                                        </a>
                                        <!-- Hidden delete form -->
                                        <form id="delete-form-{{ $item->id }}"
                                            action="{{ route('delete.units', $item->id) }}"
                                            method="POST"
                                            style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="">
                {{-- {{ $units->links('pagination::bootstrap-5') }} --}}
            </div>
        </div>
    </div>

    <!-- Add Unit Modal -->
    <div class="modal fade" id="add-unit-modal" tabindex="-1" aria-labelledby="addUnitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="addUnitModalLabel">Add New Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="Unit-add-form-page" method="POST" action="{{ route('post.units') }}">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">Unit Name <span class="text-danger">*</span></label>
                                <input type="text" id="unit-add-name-input-page" name="name" class="form-control form-control-sm text-uppercase" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">Short Name <span class="text-danger">*</span></label>
                                <input type="text" id="unit-add-short-input-page" name="short_name" class="form-control form-control-sm text-uppercase" required>
                            </div>
                        </div>
                        <div class="row g-3 mt-2 bg-light p-3 rounded">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">Allow Decimal? <span class="text-danger">*</span></label>
                                <select id="unit-add-allow-decimal-page" name="allow_decimal" class="form-control form-control-sm" required onchange="toggleAddDecimalPrecisionUnitPage()">
                                    <option value="0">NO</option>
                                    <option value="1">YES</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="unit-add-precision-container-page" style="display: none;">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">Decimals</label>
                                <input type="number" id="unit-add-decimal-precision-page" name="decimal_after_point_digit" class="form-control form-control-sm" min="0" max="10" value="2">
                            </div>
                        </div>
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="unit-add-has-base-page" name="is_base_unit" value="1" onchange="toggleAddBaseSettingsUnitPage()">
                            <label class="form-check-label small fw-bold text-uppercase" for="unit-add-has-base-page">
                                Multiple of other units
                            </label>
                        </div>
                        <div id="unit-add-base-settings-page" class="mt-3 border-start border-4 border-warning ps-3" style="display: none;">
                            <div id="unit-add-base-rows-page" class="space-y-3">
                                <div class="row g-2 mb-2 base-unit-row">
                                    <div class="col-5">
                                        <label class="form-label small fw-bold text-uppercase text-muted mb-1">QTY</label>
                                        <input type="number" step="any" name="base_units[0][multiplier]" class="form-control form-control-sm multiplier-input" placeholder="e.g., 1, 2, 3">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-uppercase text-muted mb-1">BASE UNIT</label>
                                        <select name="base_units[0][base_unit_id]" class="form-control form-control-sm base-unit-select">
                                            <option value="">Select Base Unit</option>
                                            @foreach($units as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->short_name }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger btn-sm remove-base-row" style="display: none;" onclick="removeAddRowUnitPage(this)">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-warning text-white w-100 mt-2" onclick="addAddBaseRowUnitPage()">
                                <i class="ti ti-plus"></i> ADD ANOTHER
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" onclick="saveNewUnitPage()" class="btn btn-warning text-white fw-bold">SAVE UNIT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Unit Modal -->
    <div class="modal fade" id="edit-unit-modal" tabindex="-1" aria-labelledby="editUnitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="editUnitModalLabel">Edit Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="Unit-edit-form-page" method="POST">
                    @csrf
                    <input type="hidden" id="unit-edit-id-page" name="unit_id" value="">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">Unit Name <span class="text-danger">*</span></label>
                                <input type="text" id="unit-edit-name-input-page" name="name" class="form-control form-control-sm text-uppercase" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">Short Name <span class="text-danger">*</span></label>
                                <input type="text" id="unit-edit-short-input-page" name="short_name" class="form-control form-control-sm text-uppercase" required>
                            </div>
                        </div>
                        <div class="row g-3 mt-2 bg-light p-3 rounded">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">Allow Decimal? <span class="text-danger">*</span></label>
                                <select id="unit-edit-allow-decimal-page" name="allow_decimal" class="form-control form-control-sm" required onchange="toggleEditDecimalPrecisionUnitPage()">
                                    <option value="0">NO</option>
                                    <option value="1">YES</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="unit-edit-precision-container-page" style="display: none;">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">Decimals</label>
                                <input type="number" id="unit-edit-decimal-precision-page" name="decimal_after_point_digit" class="form-control form-control-sm" min="0" max="10" value="2">
                            </div>
                        </div>
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="unit-edit-has-base-page" name="is_base_unit" value="1" onchange="toggleEditBaseSettingsUnitPage()">
                            <label class="form-check-label small fw-bold text-uppercase" for="unit-edit-has-base-page">
                                Multiple of other units
                            </label>
                        </div>
                        <div id="unit-edit-base-settings-page" class="mt-3 border-start border-4 border-warning ps-3" style="display: none;">
                            <div id="unit-edit-base-rows-page" class="space-y-3">
                                <div class="row g-2 mb-2 base-unit-row">
                                    <div class="col-5">
                                        <label class="form-label small fw-bold text-uppercase text-muted mb-1">QTY</label>
                                        <input type="number" step="any" name="base_units[0][multiplier]" class="form-control form-control-sm multiplier-input" placeholder="e.g., 1, 2, 3">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-uppercase text-muted mb-1">BASE UNIT</label>
                                        <select name="base_units[0][base_unit_id]" class="form-control form-control-sm base-unit-select">
                                            <option value="">Select Base Unit</option>
                                            @foreach($units as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->short_name }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger btn-sm remove-base-row" style="display: none;" onclick="removeEditRowUnitPage(this)">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-warning text-white w-100 mt-2" onclick="addEditBaseRowUnitPage()">
                                <i class="ti ti-plus"></i> ADD ANOTHER
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-danger" id="unit-edit-delete-btn-page" onclick="deleteUnitPage()">Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" onclick="updateUnitPage()" class="btn btn-warning text-white fw-bold">UPDATE UNIT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // ========== ADD MODAL FUNCTIONS ==========
    function toggleAddDecimalPrecisionUnitPage() {
        const allowDecimal = document.getElementById('unit-add-allow-decimal-page');
        if (!allowDecimal) return;
        const precisionContainer = document.getElementById('unit-add-precision-container-page');
        if (!precisionContainer) return;
        precisionContainer.style.display = allowDecimal.value == '1' ? 'block' : 'none';
    }
    
    function toggleAddBaseSettingsUnitPage() {
        const hasBase = document.getElementById('unit-add-has-base-page');
        if (!hasBase) return;
        const baseSettings = document.getElementById('unit-add-base-settings-page');
        if (!baseSettings) return;
        baseSettings.style.display = hasBase.checked ? 'block' : 'none';
    }
    
    function addAddBaseRowUnitPage() {
        const rows = document.getElementById('unit-add-base-rows-page');
        if (!rows || rows.children.length === 0) return;
        
        // Clone the first row (template)
        const newRow = rows.children[0].cloneNode(true);
        const index = rows.children.length;
        
        // Clear all values - ensure new row is always empty
        const multiplierInput = newRow.querySelector('.multiplier-input');
        const baseUnitSelect = newRow.querySelector('.base-unit-select');
        
        if (multiplierInput) {
            multiplierInput.value = '';
            multiplierInput.name = `base_units[${index}][multiplier]`;
        }
        
        if (baseUnitSelect) {
            baseUnitSelect.value = ''; // Always empty for new row
            baseUnitSelect.name = `base_units[${index}][base_unit_id]`;
        }
        
        // Setup remove button
        const removeBtn = newRow.querySelector('.remove-base-row');
        if (removeBtn) {
            removeBtn.style.display = 'block';
            removeBtn.onclick = function() { removeAddRowUnitPage(this); };
        }
        
        rows.appendChild(newRow);
        updateAddRemoveButtonsUnitPage();
    }
    
    function removeAddRowUnitPage(btn) {
        btn.closest('.base-unit-row').remove();
        updateAddRemoveButtonsUnitPage();
    }
    
    function updateAddRemoveButtonsUnitPage() {
        const container = document.getElementById('unit-add-base-rows-page');
        if (!container) return;
        const allRows = container.querySelectorAll('.base-unit-row');
        allRows.forEach(function(row) {
            const removeBtn = row.querySelector('.remove-base-row');
            if (removeBtn) {
                removeBtn.style.display = allRows.length > 1 ? 'block' : 'none';
            }
        });
    }
    
    function resetAddFormUnitPage() {
        const nameInput = document.getElementById('unit-add-name-input-page');
        const shortInput = document.getElementById('unit-add-short-input-page');
        const allowDecimal = document.getElementById('unit-add-allow-decimal-page');
        const decimalPrecision = document.getElementById('unit-add-decimal-precision-page');
        const hasBase = document.getElementById('unit-add-has-base-page');
        
        if (nameInput) nameInput.value = '';
        if (shortInput) shortInput.value = '';
        if (allowDecimal) allowDecimal.value = "0";
        if (decimalPrecision) decimalPrecision.value = "2";
        if (hasBase) hasBase.checked = false;
        
        toggleAddDecimalPrecisionUnitPage();
        toggleAddBaseSettingsUnitPage();
        
        const rows = document.getElementById('unit-add-base-rows-page');
        if (rows) {
            while (rows.children.length > 1) {
                rows.removeChild(rows.lastChild);
            }
            if (rows.children[0]) {
                rows.children[0].querySelector('.multiplier-input').value = '';
                rows.children[0].querySelector('.base-unit-select').value = '';
                const removeBtn = rows.children[0].querySelector('.remove-base-row');
                if (removeBtn) removeBtn.style.display = 'none';
            }
        }
        updateAddRemoveButtonsUnitPage();
    }
    
    function saveNewUnitPage() {
        const nameInput = document.getElementById('unit-add-name-input-page');
        const shortInput = document.getElementById('unit-add-short-input-page');
        const name = nameInput ? nameInput.value.trim().toUpperCase() : '';
        const short = shortInput ? shortInput.value.trim().toUpperCase() : '';
        
        if (!name || !short) {
            alert("Please fill name and short name");
            return;
        }
        
        let baseUnits = [];
        const hasBaseCheckbox = document.getElementById('unit-add-has-base-page');
        if (hasBaseCheckbox && hasBaseCheckbox.checked) {
            document.querySelectorAll('#unit-add-base-rows-page .base-unit-row').forEach(row => {
                const m = row.querySelector('.multiplier-input').value;
                const b = row.querySelector('.base-unit-select').value;
                if (m && b && !isNaN(m) && parseFloat(m) > 0) {
                    baseUnits.push({ multiplier: parseFloat(m), base_unit_id: parseInt(b) });
                }
            });
        }
        
        const formData = new FormData();
        formData.append('name', name);
        formData.append('short_name', short);
        const allowDecimal = document.getElementById('unit-add-allow-decimal-page');
        formData.append('allow_decimal', allowDecimal ? allowDecimal.value : '0');
        const decimalPrecision = document.getElementById('unit-add-decimal-precision-page');
        formData.append('decimal_after_point_digit', decimalPrecision ? decimalPrecision.value : '2');
        
        if (hasBaseCheckbox && hasBaseCheckbox.checked && baseUnits.length > 0) {
            baseUnits.forEach((bu, index) => {
                formData.append(`base_units[${index}][multiplier]`, bu.multiplier);
                formData.append(`base_units[${index}][base_unit_id]`, bu.base_unit_id);
            });
        }
        
        fetch('{{ route("post.units") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw { status: response.status, errors: err };
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(data.message || 'Unit created successfully!');
                window.location.reload();
            } else {
                alert(data.message || 'Error creating unit. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error creating unit:', error);
            if (error.errors) {
                let errorMessages = [];
                if (error.errors.name) errorMessages.push('Name: ' + error.errors.name.join(', '));
                if (error.errors.short_name) errorMessages.push('Short Name: ' + error.errors.short_name.join(', '));
                alert('Validation Error:\n' + errorMessages.join('\n'));
            } else {
                alert('Error creating unit. Please try again.');
            }
        });
    }
    
    // ========== EDIT MODAL FUNCTIONS ==========
    function toggleEditDecimalPrecisionUnitPage() {
        const allowDecimal = document.getElementById('unit-edit-allow-decimal-page');
        if (!allowDecimal) return;
        const precisionContainer = document.getElementById('unit-edit-precision-container-page');
        if (!precisionContainer) return;
        precisionContainer.style.display = allowDecimal.value == '1' ? 'block' : 'none';
    }
    
    function toggleEditBaseSettingsUnitPage() {
        const hasBase = document.getElementById('unit-edit-has-base-page');
        if (!hasBase) return;
        const baseSettings = document.getElementById('unit-edit-base-settings-page');
        if (!baseSettings) return;
        baseSettings.style.display = hasBase.checked ? 'block' : 'none';
    }
    
    function addEditBaseRowUnitPage() {
        const rows = document.getElementById('unit-edit-base-rows-page');
        if (!rows || rows.children.length === 0) return;
        
        // Clone the first row (template)
        const newRow = rows.children[0].cloneNode(true);
        const index = rows.children.length;
        
        // Clear all values - ensure new row is always empty
        const multiplierInput = newRow.querySelector('.multiplier-input');
        const baseUnitSelect = newRow.querySelector('.base-unit-select');
        
        if (multiplierInput) {
            multiplierInput.value = '';
            multiplierInput.name = `base_units[${index}][multiplier]`;
        }
        
        if (baseUnitSelect) {
            baseUnitSelect.value = ''; // Always empty for new row
            baseUnitSelect.name = `base_units[${index}][base_unit_id]`;
        }
        
        // Setup remove button
        const removeBtn = newRow.querySelector('.remove-base-row');
        if (removeBtn) {
            removeBtn.style.display = 'block';
            removeBtn.onclick = function() { removeEditRowUnitPage(this); };
        }
        
        rows.appendChild(newRow);
        updateEditRemoveButtonsUnitPage();
    }
    
    function removeEditRowUnitPage(btn) {
        btn.closest('.base-unit-row').remove();
        updateEditRemoveButtonsUnitPage();
    }
    
    function updateEditRemoveButtonsUnitPage() {
        const container = document.getElementById('unit-edit-base-rows-page');
        if (!container) return;
        const allRows = container.querySelectorAll('.base-unit-row');
        allRows.forEach(function(row) {
            const removeBtn = row.querySelector('.remove-base-row');
            if (removeBtn) {
                removeBtn.style.display = allRows.length > 1 ? 'block' : 'none';
            }
        });
    }
    
    function resetEditFormUnitPage() {
        const nameInput = document.getElementById('unit-edit-name-input-page');
        const shortInput = document.getElementById('unit-edit-short-input-page');
        const editId = document.getElementById('unit-edit-id-page');
        const allowDecimal = document.getElementById('unit-edit-allow-decimal-page');
        const decimalPrecision = document.getElementById('unit-edit-decimal-precision-page');
        const hasBase = document.getElementById('unit-edit-has-base-page');
        
        if (nameInput) nameInput.value = '';
        if (shortInput) shortInput.value = '';
        if (editId) editId.value = '';
        if (allowDecimal) allowDecimal.value = "0";
        if (decimalPrecision) decimalPrecision.value = "2";
        if (hasBase) hasBase.checked = false;
        
        toggleEditDecimalPrecisionUnitPage();
        toggleEditBaseSettingsUnitPage();
        
        const rows = document.getElementById('unit-edit-base-rows-page');
        if (rows) {
            while (rows.children.length > 1) {
                rows.removeChild(rows.lastChild);
            }
            if (rows.children[0]) {
                rows.children[0].querySelector('.multiplier-input').value = '';
                rows.children[0].querySelector('.base-unit-select').value = '';
                const removeBtn = rows.children[0].querySelector('.remove-base-row');
                if (removeBtn) removeBtn.style.display = 'none';
            }
        }
        updateEditRemoveButtonsUnitPage();
    }
    
    function updateUnitPage() {
        const editId = document.getElementById('unit-edit-id-page');
        if (!editId || !editId.value) {
            alert('Unit ID is missing');
            return;
        }
        
        const nameInput = document.getElementById('unit-edit-name-input-page');
        const shortInput = document.getElementById('unit-edit-short-input-page');
        const name = nameInput ? nameInput.value.trim().toUpperCase() : '';
        const short = shortInput ? shortInput.value.trim().toUpperCase() : '';
        
        if (!name || !short) {
            alert("Please fill name and short name");
            return;
        }
        
        let baseUnits = [];
        const hasBaseCheckbox = document.getElementById('unit-edit-has-base-page');
        if (hasBaseCheckbox && hasBaseCheckbox.checked) {
            document.querySelectorAll('#unit-edit-base-rows-page .base-unit-row').forEach(row => {
                const m = row.querySelector('.multiplier-input').value;
                const b = row.querySelector('.base-unit-select').value;
                if (m && b && !isNaN(m) && parseFloat(m) > 0) {
                    baseUnits.push({ multiplier: parseFloat(m), base_unit_id: parseInt(b) });
                }
            });
        }
        
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('name', name);
        formData.append('short_name', short);
        const allowDecimal = document.getElementById('unit-edit-allow-decimal-page');
        formData.append('allow_decimal', allowDecimal ? allowDecimal.value : '0');
        const decimalPrecision = document.getElementById('unit-edit-decimal-precision-page');
        formData.append('decimal_after_point_digit', decimalPrecision ? decimalPrecision.value : '2');
        
        if (hasBaseCheckbox && hasBaseCheckbox.checked && baseUnits.length > 0) {
            baseUnits.forEach((bu, index) => {
                formData.append(`base_units[${index}][multiplier]`, bu.multiplier);
                formData.append(`base_units[${index}][base_unit_id]`, bu.base_unit_id);
            });
        }
        
        fetch(`/units/${editId.value}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw { status: response.status, errors: err };
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(data.message || 'Unit updated successfully!');
                window.location.reload();
            } else {
                alert(data.message || 'Error updating unit. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error updating unit:', error);
            if (error.errors) {
                let errorMessages = [];
                if (error.errors.name) errorMessages.push('Name: ' + error.errors.name.join(', '));
                if (error.errors.short_name) errorMessages.push('Short Name: ' + error.errors.short_name.join(', '));
                alert('Validation Error:\n' + errorMessages.join('\n'));
            } else {
                alert('Error updating unit. Please try again.');
            }
        });
    }
    
    function deleteUnitPage() {
        const editId = document.getElementById('unit-edit-id-page');
        const id = editId ? editId.value : '';
        if (id && confirm("Delete this unit?")) {
            fetch(`/units/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Unit deleted successfully!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Error deleting unit. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error deleting unit:', error);
                alert('Error deleting unit. Please try again.');
            });
        }
    }
    
    // ========== OPEN MODAL FUNCTION ==========
    function openUnitModal(mode, unitId = null) {
        if (mode === 'add') {
            resetAddFormUnitPage();
            const modal = new bootstrap.Modal(document.getElementById('add-unit-modal'));
            modal.show();
        } else if (mode === 'edit' && unitId) {
            resetEditFormUnitPage();
            document.getElementById('unit-edit-id-page').value = unitId;
            
            // Fetch unit data from API
            fetch(`/units/${unitId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.unit) {
                    const unit = data.unit;
                    document.getElementById('unit-edit-id-page').value = unit.id;
                    document.getElementById('unit-edit-name-input-page').value = unit.name || '';
                    document.getElementById('unit-edit-short-input-page').value = unit.short_name || '';
                    document.getElementById('unit-edit-allow-decimal-page').value = unit.allow_decimal == '1' || unit.allow_decimal == 1 ? "1" : "0";
                    document.getElementById('unit-edit-decimal-precision-page').value = unit.decimal_after_point_digit || "2";
                    toggleEditDecimalPrecisionUnitPage();
                    
                    // Load base units if they exist
                    if (unit.base_units && unit.base_units.length > 0) {
                        document.getElementById('unit-edit-has-base-page').checked = true;
                        toggleEditBaseSettingsUnitPage();
                        const rowsContainer = document.getElementById('unit-edit-base-rows-page');
                        while (rowsContainer.children.length > 1) {
                            rowsContainer.removeChild(rowsContainer.lastChild);
                        }
                        
                        unit.base_units.forEach((baseUnit, i) => {
                            if (i > 0) addEditBaseRowUnitPage();
                            const row = rowsContainer.children[i];
                            row.querySelector('.multiplier-input').value = baseUnit.multiplier || '';
                            const baseSelect = row.querySelector('.base-unit-select');
                            baseSelect.value = baseUnit.id || '';
                            if (i > 0) {
                                const removeBtn = row.querySelector('.remove-base-row');
                                if (removeBtn) removeBtn.style.display = 'block';
                            }
                        });
                    } else {
                        document.getElementById('unit-edit-has-base-page').checked = false;
                        toggleEditBaseSettingsUnitPage();
                    }
                    
                    // Open edit modal
                    const modal = new bootstrap.Modal(document.getElementById('edit-unit-modal'));
                    modal.show();
                }
            })
            .catch(error => {
                console.error('Error fetching unit:', error);
                alert('Error loading unit data. Please try again.');
            });
        }
    }
    
</script>
@endsection
