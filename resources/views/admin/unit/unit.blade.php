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
                                                        <input type="checkbox" name="define_base_unit" value="1"
                                                            id="edit-toggle-base-{{ $item->id }}" {{ $item->base_unit_id
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

    <!-- Add Unit Modal -->
    <div class="modal fade" id="add-category">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="page-title">
                        <h4>Add Unit</h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('post.units.detail') }}" method="post">
                    @csrf
                    <div class="modal-body row">
                        <div class="form-group col-12 mt-2">
                            <label>Name <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="name" required>
                        </div>
                        <div class="form-group col-12 mt-3">
                            <label>Short Name <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="short_name" required>
                        </div>
                        <div class="form-group col-12 mt-4">
                            <label>Allow Decimal <span class="text-danger">*</span></label>
                            <select name="allow_decimal" class="form-control" required>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="form-group col-12 mt-3">
                            <label>
                                <input type="checkbox" name="define_base_unit" value="1" id="toggleBaseUnit">
                                Add as multiple of another Unit
                            </label>
                        </div>
                        <div class="col-12 mt-4" id="baseDetails" style="display:none;">
                            <label class="fw-bold mb-2">Base Unit Options:</label>
                            <div id="baseUnitsContainer">
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
                                                @foreach($units as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->short_name }})</option>
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
                            </div>
                            <button type="button" class="btn btn-sm btn-primary mt-2" id="addBaseUnitBtn">
                                <i class="ti ti-plus"></i> Add Another Base Unit
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Show/hide base unit details for add modal
    document.getElementById('toggleBaseUnit').addEventListener("change", function() {
        document.getElementById('baseDetails').style.display = this.checked ? "block" : "none";
    });
    
    // Show/hide base unit details for each edit modal
    @foreach($units as $item)
    document.getElementById('edit-toggle-base-{{ $item->id }}').addEventListener("change", function() {
        const baseDetailsDiv = document.getElementById('edit-base-details-{{ $item->id }}');
        baseDetailsDiv.style.display = this.checked ? "block" : "none";
    });
    @endforeach

    // Add Base Unit functionality for Add Modal
    let baseUnitIndex = 1;
    document.getElementById('addBaseUnitBtn').addEventListener('click', function() {
        const container = document.getElementById('baseUnitsContainer');
        const newItem = document.createElement('div');
        newItem.className = 'base-unit-item mb-3 p-3 border rounded';
        newItem.innerHTML = `
            <div class="row g-2">
                <div class="col-5">
                    <label class="small">Multiplier</label>
                    <input type="number" step="0.0001" name="base_units[${baseUnitIndex}][multiplier]" class="form-control form-control-sm" placeholder="e.g., 1, 2, 3">
                </div>
                <div class="col-6">
                    <label class="small">Base Unit</label>
                    <select name="base_units[${baseUnitIndex}][base_unit_id]" class="form-control form-control-sm">
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
        baseUnitIndex++;
        updateRemoveButtons();
    });

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
