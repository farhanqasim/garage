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
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-category"><i
                    class="ti ti-circle-plus me-1"></i>Add</a>
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
                                    <a class="me-2 p-2" href="#" data-bs-toggle="modal"
                                        data-bs-target="#edit-category{{ $item->id }}">
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

                        <!-- Edit Modal for each unit -->
                        <div class="modal fade" id="edit-category{{ $item->id }}">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <div class="page-title">
                                            <h4>Edit Unit</h4>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('update.units', $item->id) }}" method="post">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="form-group col-12">
                                                    <label for="edit-name-{{ $item->id }}" class="col-form-label">Name
                                                        <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="name"
                                                        id="edit-name-{{ $item->id }}"
                                                        value="{{ old('name', $item->name) }}" required>
                                                </div>
                                                <div class="form-group col-12">
                                                    <label for="edit-short-name-{{ $item->id }}"
                                                        class="col-form-label">Short Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="short_name"
                                                        id="edit-short-name-{{ $item->id }}"
                                                        value="{{ old('short_name', $item->short_name) }}" required>
                                                </div>
                                                <div class="form-group col-12">
                                                    <label for="edit-allow-decimal-{{ $item->id }}"
                                                        class="col-form-label">Allow Decimal <span
                                                            class="text-danger">*</span></label>
                                                    <select name="allow_decimal" class="form-control"
                                                        id="edit-allow-decimal-{{ $item->id }}" required>
                                                        <option value="1" {{ $item->allow_decimal == 1 ? 'selected' : ''
                                                            }}>Yes</option>
                                                        <option value="0" {{ $item->allow_decimal == 0 ? 'selected' : ''
                                                            }}>No</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-12">
                                                    <label class="col-form-label">
                                                        <input type="checkbox" name="is_base_unit" value="1"
                                                            id="edit-toggle-base-{{ $item->id }}" {{ $item->is_base_unit
                                                        ? 'checked' : '' }}>
                                                        Add as multiple of another Unit
                                                    </label>
                                                </div>
                                                <div class="col-12" id="edit-base-details-{{ $item->id }}"
                                                    style="display: {{ ($item->base_unit_id || $item->baseUnits->count() > 0) ? 'block' : 'none' }};">
                                                    <label class="fw-bold mb-2">Base Unit Options:</label>
                                                    <div id="edit-baseUnitsContainer-{{ $item->id }}">
                                                        @php
                                                            $baseUnitsList = $item->baseUnits;
                                                            $hasOldBaseUnit = $item->base_unit_id && $baseUnitsList->count() == 0;
                                                        @endphp
                                                        @if($hasOldBaseUnit)
                                                            <div class="base-unit-item mb-3 p-3 border rounded">
                                                                <div class="row g-2">
                                                                    <div class="col-5">
                                                                        <label class="small">Multiplier</label>
                                                                        <input type="number" step="0.0001" name="base_units[0][multiplier]" class="form-control form-control-sm" value="{{ $item->base_unit_multiplier ?? 1 }}">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="small">Base Unit</label>
                                                                        <select name="base_units[0][base_unit_id]" class="form-control form-control-sm">
                                                                            <option value="">Select Base Unit</option>
                                                                            @foreach($units as $baseUnit)
                                                                            <option value="{{ $baseUnit->id }}" {{ $item->base_unit_id == $baseUnit->id ? 'selected' : '' }}>
                                                                                {{ $baseUnit->name }} ({{ $baseUnit->short_name }})
                                                                            </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-1 d-flex align-items-end">
                                                                        <button type="button" class="btn btn-danger btn-sm removeBaseUnit" style="display:none;">
                                                                            <i class="ti ti-x"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @else
                                                            @foreach($baseUnitsList as $index => $baseUnit)
                                                            <div class="base-unit-item mb-3 p-3 border rounded">
                                                                <div class="row g-2">
                                                                    <div class="col-5">
                                                                        <label class="small">Multiplier</label>
                                                                        <input type="number" step="0.0001" name="base_units[{{ $index }}][multiplier]" class="form-control form-control-sm" value="{{ $baseUnit->pivot->multiplier }}">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="small">Base Unit</label>
                                                                        <select name="base_units[{{ $index }}][base_unit_id]" class="form-control form-control-sm">
                                                                            <option value="">Select Base Unit</option>
                                                                            @foreach($units as $baseUnitOption)
                                                                            <option value="{{ $baseUnitOption->id }}" {{ $baseUnit->id == $baseUnitOption->id ? 'selected' : '' }}>
                                                                                {{ $baseUnitOption->name }} ({{ $baseUnitOption->short_name }})
                                                                            </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-1 d-flex align-items-end">
                                                                        <button type="button" class="btn btn-danger btn-sm removeBaseUnit">
                                                                            <i class="ti ti-x"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endforeach
                                                            @if($baseUnitsList->count() == 0)
                                                            <div class="base-unit-item mb-3 p-3 border rounded">
                                                                <div class="row g-2">
                                                                    <div class="col-5">
                                                                        <label class="small">Multiplier</label>
                                                                        <input type="number" step="0.0001" name="base_units[0][multiplier]" class="form-control form-control-sm" placeholder="e.g., 1, 2, 3">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="small">Base Unit</label>
                                                                        <select name="base_units[0][base_unit_id]" class="form-control form-control-sm">
                                                                            <option value="">Select Base Unit</option>
                                                                            @foreach($units as $baseUnit)
                                                                            <option value="{{ $baseUnit->id }}">{{ $baseUnit->name }} ({{ $baseUnit->short_name }})</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-1 d-flex align-items-end">
                                                                        <button type="button" class="btn btn-danger btn-sm removeBaseUnit" style="display:none;">
                                                                            <i class="ti ti-x"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif
                                                        @endif
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-primary mt-2 addBaseUnitBtn" data-unit-id="{{ $item->id }}">
                                                        <i class="ti ti-plus"></i> Add Another Base Unit
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Update Unit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
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
                <form action="{{ route('post.units.detail') }}" method="post">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">Unit Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control form-control-sm text-uppercase" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">Short Name <span class="text-danger">*</span></label>
                                <input type="text" name="short_name" class="form-control form-control-sm text-uppercase" required>
                            </div>
                        </div>
                        <div class="row g-3 mt-2 bg-light p-3 rounded">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">Allow Decimal? <span class="text-danger">*</span></label>
                                <select name="allow_decimal" class="form-control form-control-sm" required onchange="toggleDecimalPrecisionUnitPage()">
                                    <option value="0">NO</option>
                                    <option value="1">YES</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="unit-precision-container-page" style="display: none;">
                                <label class="form-label small fw-bold text-uppercase text-muted mb-1">Decimals</label>
                                <select name="decimal_precision" class="form-control form-control-sm">
                                    <option value="1">1</option>
                                    <option value="2" selected>2</option>
                                    <option value="3">3</option>
                                </select>
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
                                        <button type="button" class="btn btn-danger btn-sm remove-base-row" style="display: none;">
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning text-white fw-bold">SAVE UNIT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle decimal precision visibility for unit page
    function toggleDecimalPrecisionUnitPage() {
        const allowDecimal = document.querySelector('#add-category select[name="allow_decimal"]');
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
    let baseUnitRowIndexPage = 1;
    function addBaseRowUnitPage() {
        const container = document.getElementById('unit-base-rows-page');
        if (!container) return;
        
        const newRow = document.createElement('div');
        newRow.className = 'row g-2 mb-2 base-unit-row';
        newRow.innerHTML = `
            <div class="col-5">
                <label class="form-label small fw-bold text-uppercase text-muted mb-1">QTY</label>
                <input type="number" step="any" name="base_units[${baseUnitRowIndexPage}][multiplier]" class="form-control form-control-sm multiplier-input" placeholder="e.g., 1, 2, 3">
            </div>
            <div class="col-6">
                <label class="form-label small fw-bold text-uppercase text-muted mb-1">BASE UNIT</label>
                <select name="base_units[${baseUnitRowIndexPage}][base_unit_id]" class="form-control form-control-sm base-unit-select">
                    <option value="">Select Base Unit</option>
                    @foreach($units as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->short_name }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm remove-base-row">
                    <i class="ti ti-x"></i>
                </button>
            </div>
        `;
        container.appendChild(newRow);
        baseUnitRowIndexPage++;
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
    
    // Show/hide base unit details for each edit modal
    @foreach($units as $item)
    const editToggleBase{{ $item->id }} = document.getElementById('edit-toggle-base-{{ $item->id }}');
    if (editToggleBase{{ $item->id }}) {
        editToggleBase{{ $item->id }}.addEventListener("change", function() {
            const baseDetailsDiv = document.getElementById('edit-base-details-{{ $item->id }}');
            if (baseDetailsDiv) {
                baseDetailsDiv.style.display = this.checked ? "block" : "none";
            }
        });
    }
    @endforeach

    // Add Base Unit functionality for Edit Modals
    @foreach($units as $item)
    let editBaseUnitIndex{{ $item->id }} = {{ $item->baseUnits->count() > 0 ? $item->baseUnits->count() : 1 }};
    document.querySelector('.addBaseUnitBtn[data-unit-id="{{ $item->id }}"]')?.addEventListener('click', function() {
        const container = document.getElementById('edit-baseUnitsContainer-{{ $item->id }}');
        const newItem = document.createElement('div');
        newItem.className = 'base-unit-item mb-3 p-3 border rounded';
        newItem.innerHTML = `
            <div class="row g-2">
                <div class="col-5">
                    <label class="small">Multiplier</label>
                    <input type="number" step="0.0001" name="base_units[${editBaseUnitIndex{{ $item->id }}}][multiplier]" class="form-control form-control-sm" placeholder="e.g., 1, 2, 3">
                </div>
                <div class="col-6">
                    <label class="small">Base Unit</label>
                    <select name="base_units[${editBaseUnitIndex{{ $item->id }}}][base_unit_id]" class="form-control form-control-sm">
                        <option value="">Select Base Unit</option>
                        @foreach($units as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->short_name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm removeBaseUnit">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(newItem);
        editBaseUnitIndex{{ $item->id }}++;
        updateRemoveButtons();
    });
    @endforeach

    // Remove Base Unit functionality
    function updateRemoveButtons() {
        document.querySelectorAll('.base-unit-item').forEach(function(item, index) {
            const removeBtn = item.querySelector('.removeBaseUnit');
            const allItems = item.closest('#baseUnitsContainer, [id^="edit-baseUnitsContainer"]')?.querySelectorAll('.base-unit-item');
            if (allItems && allItems.length > 1) {
                removeBtn.style.display = 'block';
            } else {
                removeBtn.style.display = 'none';
            }
        });
    }

    // Event delegation for remove buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.removeBaseUnit')) {
            e.target.closest('.base-unit-item').remove();
            updateRemoveButtons();
        }
    });

    // Initialize remove buttons on page load
    updateRemoveButtons();
</script>
@endsection
