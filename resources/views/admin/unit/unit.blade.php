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

    <!-- Add Unit Modal - Dynamic Unit Manager -->
    <div class="modal fade" id="add-category" tabindex="-1" aria-labelledby="addUnitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="addUnitModalLabel">Unit Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="Unit-form-page" method="POST">
                    @csrf
                    <input type="hidden" id="unit-edit-id-page" name="unit_id" value="">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">Unit Name <span class="text-danger">*</span></label>
                                <input type="text" id="unit-name-input-page" name="name" class="form-control form-control-sm text-uppercase" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">Short Name <span class="text-danger">*</span></label>
                                <input type="text" id="unit-short-input-page" name="short_name" class="form-control form-control-sm text-uppercase" required>
                            </div>
                        </div>
                        <div class="row g-3 mt-2 bg-light p-3 rounded">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">Allow Decimal? <span class="text-danger">*</span></label>
                                <select id="unit-allow-decimal-page" name="allow_decimal" class="form-control form-control-sm" required onchange="toggleDecimalPrecisionUnitPage()">
                                    <option value="0">NO</option>
                                    <option value="1">YES</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="unit-precision-container-page" style="display: none;">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">Decimals</label>
                                <input type="number" id="unit-decimal-precision-page" name="decimal_after_point_digit" class="form-control form-control-sm" min="0" max="10" value="2">
                            </div>
                        </div>
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="unit-has-base-page" name="is_base_unit" value="1" onchange="toggleBaseSettingsUnitPage()">
                            <label class="form-check-label small fw-bold text-uppercase" for="unit-has-base-page">
                                Multiple of other units
                            </label>
                        </div>
                        <div id="unit-base-settings-page" class="mt-3 border-start border-4 border-warning ps-3" style="display: none;">
                            <div id="unit-base-rows-page" class="space-y-3">
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
                                        <button type="button" class="btn btn-danger btn-sm remove-base-row" style="display: none;" onclick="removeRowUnitPage(this)">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-warning text-white w-100 mt-2" onclick="addBaseRowUnitPage()">
                                <i class="ti ti-plus"></i> ADD ANOTHER
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-danger d-none" id="unit-delete-btn-page" onclick="deleteUnitPage()">Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" onclick="saveUnitPage()" class="btn btn-warning text-white fw-bold">SAVE UNIT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle decimal precision visibility for unit page
    function toggleDecimalPrecisionUnitPage() {
        const allowDecimal = document.getElementById('unit-allow-decimal-page');
        if (!allowDecimal) return;
        const precisionContainer = document.getElementById('unit-precision-container-page');
        if (!precisionContainer) return;
        if (allowDecimal.value == '1') {
            precisionContainer.style.display = 'block';
        } else {
            precisionContainer.style.display = 'none';
        }
    }
    
    // Toggle base settings visibility for unit page
    function toggleBaseSettingsUnitPage() {
        const hasBase = document.getElementById('unit-has-base-page');
        if (!hasBase) return;
        const baseSettings = document.getElementById('unit-base-settings-page');
        if (!baseSettings) return;
        if (hasBase.checked) {
            baseSettings.style.display = 'block';
        } else {
            baseSettings.style.display = 'none';
        }
    }
    
    // Add base unit row for unit page
    function addBaseRowUnitPage() {
        const rows = document.getElementById('unit-base-rows-page');
        if (!rows) return;
        const newRow = rows.children[0].cloneNode(true);
        const index = rows.children.length;
        newRow.querySelector('.multiplier-input').value = '';
        newRow.querySelector('.base-unit-select').value = '';
        newRow.querySelector('.multiplier-input').name = `base_units[${index}][multiplier]`;
        newRow.querySelector('.base-unit-select').name = `base_units[${index}][base_unit_id]`;
        const removeBtn = newRow.querySelector('.remove-base-row');
        if (removeBtn) {
            removeBtn.style.display = 'block';
            removeBtn.onclick = function() { removeRowUnitPage(this); };
        }
        rows.appendChild(newRow);
        updateRemoveButtonsUnitPage();
    }
    
    // Remove base unit row for unit page
    function removeRowUnitPage(btn) {
        btn.closest('.base-unit-row').remove();
        updateRemoveButtonsUnitPage();
    }
    
    // Update remove buttons visibility for unit page
    function updateRemoveButtonsUnitPage() {
        const container = document.getElementById('unit-base-rows-page');
        if (!container) return;
        
        const allRows = container.querySelectorAll('.base-unit-row');
        allRows.forEach(function(row) {
            const removeBtn = row.querySelector('.remove-base-row');
            if (allRows.length > 1) {
                removeBtn.style.display = 'block';
            } else {
                removeBtn.style.display = 'none';
            }
        });
    }
    
    // Event delegation for remove buttons on unit page
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-base-row') && e.target.closest('#unit-base-rows-page')) {
            removeRowUnitPage(e.target.closest('.remove-base-row'));
        }
    });
    
    // Initialize remove buttons on page load
    if (document.getElementById('unit-base-rows-page')) {
        updateRemoveButtonsUnitPage();
    }
    
    // ========== UNIT MODAL FUNCTIONS ==========
    function openUnitModal(mode, unitId = null) {
        resetFormUnitPage();
        if (mode === 'add') {
            document.getElementById('addUnitModalLabel').innerText = "Add New Unit";
            document.getElementById('unit-delete-btn-page').classList.add('d-none');
            document.getElementById('unit-edit-id-page').value = '';
            // Open add modal
            const modal = new bootstrap.Modal(document.getElementById('add-category'));
            modal.show();
        } else if (mode === 'edit' && unitId) {
            document.getElementById('addUnitModalLabel').innerText = "Update Unit Settings";
            document.getElementById('unit-delete-btn-page').classList.remove('d-none');
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
                    document.getElementById('unit-name-input-page').value = unit.name || '';
                    document.getElementById('unit-short-input-page').value = unit.short_name || '';
                    document.getElementById('unit-allow-decimal-page').value = unit.allow_decimal == '1' || unit.allow_decimal == 1 ? "1" : "0";
                    document.getElementById('unit-decimal-precision-page').value = unit.decimal_after_point_digit || "2";
                    toggleDecimalPrecisionUnitPage();
                    
                    // Load base units if they exist
                    if (unit.base_units && unit.base_units.length > 0) {
                        document.getElementById('unit-has-base-page').checked = true;
                        toggleBaseSettingsUnitPage();
                        const rowsContainer = document.getElementById('unit-base-rows-page');
                        // Clear existing rows except first one
                        while (rowsContainer.children.length > 1) {
                            rowsContainer.removeChild(rowsContainer.lastChild);
                        }
                        
                        // Populate base units
                        unit.base_units.forEach((baseUnit, i) => {
                            if (i > 0) addBaseRowUnitPage();
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
                        document.getElementById('unit-has-base-page').checked = false;
                        toggleBaseSettingsUnitPage();
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching unit:', error);
                alert('Error loading unit data. Please try again.');
            });
            
            // Open modal
            const modal = new bootstrap.Modal(document.getElementById('add-category'));
            modal.show();
        }
    }
    
    function resetFormUnitPage() {
        document.getElementById('unit-name-input-page').value = '';
        document.getElementById('unit-short-input-page').value = '';
        document.getElementById('unit-edit-id-page').value = '';
        document.getElementById('unit-has-base-page').checked = false;
        document.getElementById('unit-allow-decimal-page').value = "0";
        document.getElementById('unit-decimal-precision-page').value = "2";
        document.getElementById('unit-delete-btn-page').classList.add('d-none');
        toggleDecimalPrecisionUnitPage();
        toggleBaseSettingsUnitPage();
        const rows = document.getElementById('unit-base-rows-page');
        while (rows.children.length > 1) {
            rows.removeChild(rows.lastChild);
        }
        if (rows.children[0]) {
            rows.children[0].querySelector('.multiplier-input').value = '';
            rows.children[0].querySelector('.base-unit-select').value = '';
            const removeBtn = rows.children[0].querySelector('.remove-base-row');
            if (removeBtn) removeBtn.style.display = 'none';
        }
        updateRemoveButtonsUnitPage();
    }
    
    function saveUnitPage() {
        const name = document.getElementById('unit-name-input-page').value.trim().toUpperCase();
        const short = document.getElementById('unit-short-input-page').value.trim().toUpperCase();
        const editId = document.getElementById('unit-edit-id-page').value;
        
        if (!name || !short) {
            alert("Please fill name and short name");
            return;
        }
        
        let baseUnits = [];
        if (document.getElementById('unit-has-base-page').checked) {
            document.querySelectorAll('#unit-base-rows-page .base-unit-row').forEach(row => {
                const m = row.querySelector('.multiplier-input').value;
                const b = row.querySelector('.base-unit-select').value;
                if (m && b) {
                    baseUnits.push({ multiplier: m, base_unit_id: b });
                }
            });
        }
        
        const formData = new FormData();
        formData.append('name', name);
        formData.append('short_name', short);
        formData.append('allow_decimal', document.getElementById('unit-allow-decimal-page').value);
        formData.append('decimal_after_point_digit', document.getElementById('unit-decimal-precision-page').value || 2);
        if (document.getElementById('unit-has-base-page').checked) {
            baseUnits.forEach((bu, index) => {
                formData.append(`base_units[${index}][multiplier]`, bu.multiplier);
                formData.append(`base_units[${index}][base_unit_id]`, bu.base_unit_id);
            });
        }
        
        const url = editId ? `/units/${editId}` : '/post/units';
        const method = editId ? 'PUT' : 'POST';
        
        fetch(url, {
            method: method,
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Unit saved successfully!');
                // Reload page to refresh units list
                window.location.reload();
            } else {
                alert(data.message || 'Error saving unit. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error saving unit:', error);
            alert('Error saving unit. Please try again.');
        });
    }
    
    function deleteUnitPage() {
        const id = document.getElementById('unit-edit-id-page').value;
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
    
</script>
@endsection
