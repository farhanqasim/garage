@extends('layouts.app')
@section('title', 'Edit Product')
@section('content')
@php $editingItem = $item; @endphp
@push('styles')
<style>
    input[type="text"],
    input[type="password"],
    input[type="email"],
    input[type="url"],
    input[type="tel"],
    input[type="number"],
    input[type="date"],
    input[type="datetime-local"],
    input[type="month"],
    input[type="week"],
    input[type="time"],
    select,
    textarea {
        font-weight: bold;
        font-size: 15px;
        text-transform: uppercase;
    }
    .type-box {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #ddd;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        background: #f8f9fa;
        font-weight: bold;
        font-size: 18px;
    }
    .type-box:hover {
        border-color: #fe9f43;
        background: #fe9f43;
        color: white;
    }
    .type-box.selected {
        border-color: #fe9f43;
        background: #fe9f43;
        color: white;
        box-shadow: 0 4px 12px #fe962e;
    }
    /* Add Vehicle modal: keep edit button inside, medium modal */
    #itemVehicleAddModalEdit .input-group,
    #itemVehicleAddModalEdit .modal-body .input-group {
        display: flex;
        max-width: 100%;
        min-width: 0;
    }
    #itemVehicleAddModalEdit .input-group .select2-container,
    #itemVehicleAddModalEdit .input-group .select2-container--default {
        flex: 1;
        min-width: 0;
        max-width: 100%;
    }
    #itemVehicleAddModalEdit .input-group .select2-container .selection .select2-selection {
        max-width: 100%;
    }
    #itemVehicleAddModalEdit .input-group .btn {
        flex-shrink: 0;
    }
    .field-group {
        display: none;
    }
    .field-group.active {
        display: block;
    }
    .border {
        border: 1px solid #ddd !important;
        border-radius: 8px;
    }
    .input-group .btn i {
        pointer-events: none;
    }
    /* Responsive adjustments for edit.blade.php */
    @media (max-width: 768px) {
        .type-box {
            padding: 15px !important;
            font-size: 14px !important;
        }
        .type-box .fs-1 {
            font-size: 2rem !important;
        }
        .inputswidth {
            width: 100% !important;
        }
        /* Vehicle table - better mobile display */
        #vehicleTable {
            font-size: 11px !important;
        }
        #vehicleTable th,
        #vehicleTable td {
            padding: 0.5rem 0.25rem !important;
            font-size: 11px !important;
        }
        /* Year badges - smaller on mobile */
        .badge {
            font-size: 0.65rem !important;
            padding: 4px 8px !important;
        }
        /* Form columns stack properly */
        .row .col-md-4,
        .row .col-md-6 {
            margin-bottom: 1rem;
        }
        /* Modal vehicle form - better mobile layout */
        #vehical-add-modal .modal-body {
            padding: 1rem !important;
        }
        #vehical-add-modal .row {
            margin-left: -0.5rem;
            margin-right: -0.5rem;
        }
        #vehical-add-modal .col-md-6 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        /* Year range inputs - better mobile layout */
        .year-range-item .col-5 {
            flex: 0 0 48%;
            max-width: 48%;
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }
        .year-range-item .col-2 {
            flex: 0 0 4%;
            max-width: 4%;
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }
        /* Image previews - responsive */
        #imagePreview, #imagesPreview img {
            max-width: 100% !important;
            height: auto !important;
        }
        /* Unit info display - stack on mobile */
        #unit-info, #sale-price-info {
            flex-direction: column !important;
            gap: 0.5rem !important;
        }
        #unit-info .form-control,
        #sale-price-info .form-control {
            width: 100% !important;
        }
    }
    @media (max-width: 576px) {
        /* Very small screens */
        .type-box {
            padding: 10px !important;
            font-size: 12px !important;
        }
        .type-box .fs-1 {
            font-size: 1.5rem !important;
        }
        /* Hide table columns on very small screens if needed */
        #vehicleTable th:nth-child(4),
        #vehicleTable td:nth-child(4),
        #vehicleTable th:nth-child(5),
        #vehicleTable td:nth-child(5) {
            display: none;
        }
    }
</style>
@endpush
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h2 class="fw-bold">Edit Product</h2>
            </div>
        </div>
        <ul class="table-top-head">
            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header">
                    <i class="ti ti-chevron-up"></i>
                </a>
            </li>
        </ul>
    </div>
    <div class="card">
        <div class="card-body">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <form method="POST" action="{{ route('item.update', $item->id) }}" enctype="multipart/form-data" id="mainItemForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                <div class="container" x-data="productForm({{ json_encode($item->type ?? old('type') ?? '') }})" x-init="if(selectedType) { setTimeout(() => loadItemsByType(selectedType, false), 300); }">
                    <!-- 4 Clickable Type Boxes -->
                    <div class="row mb-5 g-3">
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" :class="{ 'selected': selectedType === 'parts' }"
                                @click="selectType('parts')">
                                <i class="ti ti-tool fs-1 d-block mb-2"></i>
                                Parts
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" :class="{ 'selected': selectedType === 'filters' }"
                                @click="selectType('filters')">
                                <i class="ti ti-filter fs-1 d-block mb-2"></i>
                                Filters
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" :class="{ 'selected': selectedType === 'breakpad' }"
                                @click="selectType('breakpad')">
                                <i class="ti ti-disc fs-1 d-block mb-2"></i>
                                Break Pad
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" :class="{ 'selected': selectedType === 'oil' }"
                                @click="selectType('oil')">
                                <i class="ti ti-droplet fs-1 d-block mb-2"></i>
                                Oil
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" :class="{ 'selected': selectedType === 'battery' }"
                                @click="selectType('battery')">
                                <i class="ti ti-battery fs-1 d-block mb-2"></i>
                                Battery
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" :class="{ 'selected': selectedType === 'scrap' }"
                                @click="selectType('scrap')">
                                <i class="ti ti-trash fs-1 d-block mb-2"></i>
                                Scrap
                            </div>
                        </div>

                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" :class="{ 'selected': selectedType === 'services' }"
                                @click="selectType('services')">
                                <i class="ti ti-tools fs-1 d-block mb-2"></i>
                                Services
                            </div>
                        </div>
                      
                    </div>
                    <!-- Hidden field to store selected type -->
                    <input type="hidden" name="type" x-model="selectedType">
                    <!-- Form Fields Container -->
                    <div class="row" id="itemFormsContainer">
                        <!-- COMMON FIELDS (Visible after type selection) -->
                        <div class="col-md-12 field-group common-fields" :class="{ 'active': selectedType }">
                            <h4 class="mt-3">Item Info:</h4>
                            <div class="row mt-4">
                                  <!-- Barcode -->
                                <div class="col-md-4">
                                    <label for="itemBarCode">Product Bar Code:</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('bar_code') is-invalid @enderror"
                                            name="bar_code" id="itemBarCode" value="{{ $item->bar_code ?? old('bar_code') }}"  />
                                        <button type="button" class="btn btn-primary generate-code-btn"
                                            id="generateCodeBtn">
                                            <i data-feather="refresh-cw"></i>
                                        </button>
                                    </div>
                                    @error('bar_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    {{-- @if($item->barcode_image)
                                        <img src="{{ asset($item->barcode_image) }}" width="180">
                                    @endif --}}
                                </div>
                                <div class="col-md-4" x-show="selectedType === 'parts' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <label for="part_number_id">Part Number:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control part_number-select searchable-select @error('part_number_id') is-invalid @enderror"
                                            name="part_number_id" id="part_number_id">
                                            <option value="">Select Part Number</option>
                                            @foreach ($partnumbers as $partnumber)
                                            <option value="{{ $partnumber->id??'' }}" {{ old('part_number_id', $item->part_number_id) == $partnumber->id ? 'selected' : '' }}>
                                                {{ $partnumber->name ?? '-' }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Part Number"
                                            data-fetch-route="{{ route('show.partnumber', ':id') }}"
                                            data-update-route="{{ route('update.partnumber', ':id') }}"
                                            data-delete-route="{{ route('destory.partnumber', ':id') }}"
                                            data-target-select=".part_number-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('part_number_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <!-- Product Name -->
                                <div class="col-md-4" x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil' || selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <label for="itemname">Product Name:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control name-select searchable-select @error('p_id') is-invalid @enderror"
                                            name="p_id" id="name">
                                            <option value="">Select Product Name</option>
                                            @foreach ($product as $prod)
                                            <option value="{{ $prod->id }}"
                                              data-type="{{ $prod->type ?? '' }}"
                                              {{ old('p_id', $item->p_id) == $prod->id ? 'selected' : '' }}>
                                                {{ $prod->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Product"
                                            data-fetch-route="{{ route('show.product', ':id') }}"
                                            data-update-route="{{ route('update.product', ':id') }}"
                                            data-delete-route="{{ route('destory.product', ':id') }}"
                                            data-target-select=".name-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('p_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4 mt-3" x-show="selectedType === 'parts' || selectedType === 'oil' || selectedType === 'scrap' || selectedType === 'services' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <label for="category_parts">Category:</label>
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="flex-grow-1">
                                            <div class="input-group inputswidth">
                                                <select
                                                    class="form-control category-select searchable-select @error('category_id') is-invalid @enderror"
                                                    name="category_id" id="category_parts">
                                                    <option value="">Select Category</option>
                                                    @foreach ($Categories as $category)
                                                    <option value="{{ $category->id }}"
                                                        data-image="{{ $category->image ? asset($category->image) : '' }}"
                                                        {{ ($item->category_id ?? old('category_id')) == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-secondary open-universal-modal"
                                                    data-mode="edit" data-title="Edit Category"
                                                    data-fetch-route="{{ route('show.category', ':id') }}"
                                                    data-update-route="{{ route('update.category', ':id') }}"
                                                    data-delete-route="{{ route('destory.category', ':id') }}"
                                                    data-target-select=".category-select" data-has-image="1">
                                                    <i data-feather="edit"></i>
                                                </button>
                                            </div>
                                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div id="categoryImageDisplay" class="flex-shrink-0" style="display:none;">
                                            <img id="categoryImageImg" src="" alt="Category" class="rounded border" style="width:60px;height:60px;object-fit:cover;">
                                        </div>
                                    </div>
                                </div>
                                <!-- Business Location -->
                                <div class="col-md-4 d-none">
                                    <label for="business_location">Business Location:</label>
                                    <input type="text"
                                        class="form-control @error('business_location') is-invalid @enderror"
                                        name="business_location" id="business_location"
                                        value="{{ $item->business_location ?? old('business_location') }}" />
                                    @error('business_location') <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4" x-show="selectedType === 'battery'">
                                    <label for="group_select">Group Name:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control group-select searchable-select @error('group') is-invalid @enderror"
                                            name="group" id="group_select">
                                            <option value="">Select Group Name</option>
                                            @foreach ($groups as $group)
                                            <option value="{{ $group->id }}" {{ old('group', $item->gorup) == $group->id ? 'selected' : '' }}>
                                                {{ $group->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit group"
                                            data-fetch-route="{{ route('show.groups', ':id') }}"
                                            data-update-route="{{ route('post.groups.update', ':id') }}"
                                            data-delete-route="{{ route('post.groups.destroy', ':id') }}"
                                            data-target-select=".group-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('group')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                    <div class="col-md-4 mt-3" x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <label for="company_parts">Company:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control company-select searchable-select @error('company_id') is-invalid @enderror"
                                            name="company_id" id="company_parts">
                                            <option value="">Select Company</option>
                                            @foreach ($Companies as $company)
                                            <option value="{{ $company->id }}"
                                              {{ old('company_id', $item->company_id) == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Company"
                                            data-fetch-route="{{ route('show.company', ':id') }}"
                                            data-update-route="{{ route('update.company', ':id') }}"
                                            data-delete-route="{{ route('destory.company', ':id') }}"
                                            data-target-select=".company-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <!-- Quality -->
                                <div class="col-md-4 mt-3" x-show="selectedType === 'parts' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <label for="quality_common">Quality:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control quality-select searchable-select @error('quality_id') is-invalid @enderror"
                                            name="quality_id" id="quality_common">
                                            <option value="">Select Quality</option>
                                            @foreach ($qualities as $qaul)
                                            <option value="{{ $qaul->id }}"
                                              {{ old('quality_id', $item->quality_id) == $qaul->id ? 'selected' : '' }}>
                                                {{ $qaul->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Quality"
                                            data-fetch-route="{{ route('show.quality', ':id') }}"
                                            data-update-route="{{ route('update.quality', ':id') }}"
                                            data-delete-route="{{ route('destory.quality', ':id') }}"
                                            data-target-select=".quality-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('quality_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                

                            </div>
                        </div>
                        <!-- PARTS FIELDS -->
                        <div class="field-group parts-fields" :class="{ 'active': selectedType === 'parts' }">
                            <div class="row p-3 mt-4">
                                <!-- Quality field moved to common fields section above -->
                            </div>
                        </div>
                        <!-- BATTERY FIELDS -->
                        <div class="field-group battery-fields" :class="{ 'active': selectedType === 'battery' }">
                            <div class="row  p-3 mt-4">
                                    <div class="col-md-4">
                                <label for="plates_scrap">Plates:</label>
                                <div class="input-group inputswidth">
                                    <select
                                        class="form-control plates-select searchable-select @error('plato') is-invalid @enderror"
                                        name="plato" id="plates_scrap">
                                        <option value="">Select Plate</option>
                                        @foreach ($platos as $plate)
                                        <option value="{{ $plate->id }}"
                                              {{ old('plato', $item->plat_id) == $plate->id ? 'selected' : '' }}>
                                                {{ $plate->name }}PL
                                        </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-secondary open-universal-modal"
                                        data-mode="edit" data-title="Edit Plates"
                                        data-fetch-route="{{ route('show.plate', ':id') }}"
                                        data-update-route="{{ route('update.plate', ':id') }}"
                                        data-delete-route="{{ route('destory.plate', ':id') }}"
                                        data-target-select=".plates-select">
                                        <i data-feather="edit"></i>
                                    </button>
                                </div>
                                @error('plato')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                                <div class="col-md-4">
                                    <label for="amperes_select">Amperes:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control amperes-select searchable-select @error('amphors') is-invalid @enderror"
                                            name="amphors" id="amperes_select">
                                            <option value="">Select Amperes</option>
                                            @foreach ($amphors as $ampere)
                                            <option value="{{ $ampere->id }}"
                                              {{ old('amphors', $item->amphors) == $ampere->id ? 'selected' : '' }}>
                                                {{ $ampere->name }}AH
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Amperes"
                                            data-fetch-route="{{ route('show.ampere', ':id') }}"
                                            data-update-route="{{ route('update.ampere', ':id') }}"
                                            data-delete-route="{{ route('destory.ampere', ':id') }}"
                                            data-target-select=".amperes-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('amphors')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="volt_select">Volt:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                                class="form-control volt-select searchable-select @error('volt') is-invalid @enderror"
                                                name="volt" id="volt_select">
                                                <option value="">Select Volt</option>
                                                @foreach ($volts as $volt)
                                                    <option value="{{ $volt->id }}"
                                                        {{
                                                            old(
                                                                'volt',
                                                                $item->volt ?? ($volt->name == 12 ? $volt->id : null)
                                                            ) == $volt->id ? 'selected' : ''
                                                        }}>
                                                        {{ $volt->name }}V
                                                    </option>
                                                @endforeach
                                            </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Volt"
                                            data-fetch-route="{{ route('show.volt', ':id') }}"
                                            data-update-route="{{ route('update.volt', ':id') }}"
                                            data-delete-route="{{ route('destory.volt', ':id') }}"
                                            data-target-select=".volt-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('volt')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label for="cca_select">CCA:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control cca-select searchable-select @error('cca') is-invalid @enderror"
                                            name="cca" id="cca_select">
                                            <option value="">Select CCA</option>
                                            @foreach ($ccas as $cca)
                                            <option value="{{ $cca->id }}"
                                                {{ old('cca', $item->cca) == $cca->id ? 'selected' : '' }}>
                                                {{ $cca->name }}CCA
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit CCA"
                                            data-fetch-route="{{ route('show.cca', ':id') }}"
                                            data-update-route="{{ route('update.cca', ':id') }}"
                                            data-delete-route="{{ route('destory.cca', ':id') }}"
                                            data-target-select=".cca-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('cca')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            <div class="col-md-4 mt-3">
                                <label for="minus_pole_direction_select">Minus Pole Direction:</label>
                                <div class="input-group inputswidth">
                                    <select
                                        class="form-control minus-pole-direction-select searchable-select @error('minus_pole_direction') is-invalid @enderror"
                                        name="minus_pole_direction" id="minus_pole_direction_select">
                                        <option value="">Select Minus Pole Direction</option>
                                        @foreach ($minspols as $mpd)
                                        <option value="{{ $mpd->id }}"
                                             {{  old(
                                                'minus_pole_direction',
                                                $item->minus_pole_direction ?? ($mpd->name == 'L' ? $mpd->id : null)
                                                ) == $mpd->id ? 'selected' : ''}}>
                                                {{ $mpd->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-secondary open-universal-modal"
                                        data-mode="edit" data-title="Edit Minus Pole Direction"
                                        data-fetch-route="{{ route('show.minuspool', ':id') }}"
                                        data-update-route="{{ route('update.minuspool', ':id') }}"
                                        data-delete-route="{{ route('destory.minuspool', ':id') }}"
                                        data-target-select=".minus-pole-direction-select">
                                        <i data-feather="edit"></i>
                                    </button>
                                </div>
                                @error('minus_pole_direction')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                                <div class="col-md-4 mt-3">
                                    <label for="Warrenty_select">Warrenty:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control Warrenty-select searchable-select @error('warrenty') is-invalid @enderror"
                                            name="warrenty" id="Warrenty_select">
                                            <option value="">Select Warrenty</option>
                                            @foreach ($warrenties as $warrenty)
                                            <option value="{{ $warrenty->id }}" {{ old('warrenty', $item->warrenty) == $warrenty->id ? 'selected' : '' }}>
                                                {{ \Illuminate\Support\Str::title(strtolower($warrenty->name)) }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Warrenty"
                                            data-fetch-route="{{ route('show.warrenty', ':id') }}"
                                            data-update-route="{{ route('update.warrenty', ':id') }}"
                                            data-delete-route="{{ route('destory.warrenty', ':id') }}"
                                            data-target-select=".Warrenty-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('warrenty')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label for="made_in_select">Made In:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control made_in-select searchable-select @error('made_in') is-invalid @enderror"
                                            name="made_in" id="made_in_select">
                                            <option value="">Select Made In</option>
                                            @foreach ($made_ins as $made)
                                            <option value="{{ $made->id }}" {{  old(
                                                'made_in',
                                                $item->made_in ?? ($made->name == 'Pakistan' ? $made->id : null)
                                                ) == $made->id ? 'selected' : ''}} >
                                                {{ $made->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Made In"
                                            data-fetch-route="{{ route('show.madeins', ':id') }}"
                                            data-update-route="{{ route('update.madeins', ':id') }}"
                                            data-delete-route="{{ route('destory.madeins', ':id') }}"
                                            data-target-select=".made_in-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('made_in')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mt-3 d-none">
                                    <label for="battery_code">Battery Code:</label>
                                    <input type="text" class="form-control @error('battery_code') is-invalid @enderror"
                                        name="battery_code" id="battery_code" value="{{ $item->battery_code ?? old('battery_code') }}" />
                                    @error('battery_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mt-3 d-none">
                                    <label for="serial_number_battery">Serial Number:</label>
                                    <input type="text" class="form-control @error('serial_number') is-invalid @enderror"
                                        name="serial_number" id="serial_number_battery"
                                        value="{{ $item->serial_number ?? old('serial_number') }}" />
                                    @error('serial_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                            </div>
                        </div>
                        <!-- OIL FIELDS -->
                        <div class="field-group oil-fields" :class="{ 'active': selectedType === 'oil' }">
                            <div class="row p-3 mt-4">
                            <div class="col-md-4">
                                    <label for="grade_select">Grade:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control grade-select searchable-select @error('grade') is-invalid @enderror"
                                            name="grade" id="grade_select">
                                            <option value="">Select Grade</option>
                                            @foreach ($grades as $grade)
                                            <option
                                                value="{{ $grade->id }}"
                                                {{ old('grade', $item->grade) == $grade->id ? 'selected' : '' }}>
                                                {{ $grade->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Grade"
                                            data-fetch-route="{{ route('show.grade', ':id') }}"
                                            data-update-route="{{ route('update.grade', ':id') }}"
                                            data-delete-route="{{ route('destory.grade', ':id') }}"
                                            data-target-select=".grade-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('grade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                <div class="col-md-4 mt-3 d-none">
                                    <label for="brand_select">Brand:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control brand-select searchable-select @error('brand') is-invalid @enderror"
                                            name="brand" id="brand_select">
                                            <option value="">Select Brand</option>
                                            @foreach ($brands as $brand)
                                            <option
                                                value="{{ $brand->id }}"
                                                {{ old('brand', $item->brand) == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-title="Add Brand"
                                            data-route="{{ route('post.item.brand') }}"
                                            data-target-select=".brand-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                    </div>
                                    @error('brand')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label for="mileage_oil">Mileage:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control mileage-select searchable-select @error('mileage') is-invalid @enderror"
                                            name="mileage" id="mileage_oil">
                                            <option value="">Select Mileage</option>
                                            @foreach ($milleages as $milleage)
                                            <option
                                                value="{{ $milleage->id }}"
                                                {{ old('mileage', $item->mileage) == $milleage->id ? 'selected' : '' }}>
                                                {{ $milleage->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Mileage"
                                            data-fetch-route="{{ route('show.mileage', ':id') }}"
                                            data-update-route="{{ route('update.mileage', ':id') }}"
                                            data-delete-route="{{ route('destory.mileage', ':id') }}"
                                            data-target-select=".mileage-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('mileage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mt-3 ">
                                    <label for="Level_select">Level:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control level-select searchable-select @error('level') is-invalid @enderror"
                                            name="level" id="Level_select">
                                            <option value="">Select Level</option>
                                            @foreach ($levels as $level)
                                            <option value="{{ $level->id }}" {{ old('level', $item->level) == $level->id ? 'selected' : '' }}>
                                                {{ $level->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Level"
                                            data-fetch-route="{{ route('show.level', ':id') }}"
                                            data-update-route="{{ route('update.level', ':id') }}"
                                            data-delete-route="{{ route('destory.level', ':id') }}"
                                            data-target-select=".level-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                              <div class="col-md-4 mt-3 d-none">
                                    <label for="formulas_select">Formulas:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control formulas-select searchable-select @error('formulas') is-invalid @enderror"
                                            name="formulas" id="formulas_select">
                                            <option value="">Select Formula</option>
                                            @foreach ($formulas as $formula)
                                            <option value="{{ $formula->id }}"
                                                {{ old('formulas', $item->formulas) == $formula->id ? 'selected' : '' }}>
                                                {{ $formula->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('formulas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mt-3 d-none">
                                    <label for="serial_number_oil">Serial Number:</label>
                                    <input type="text" class="form-control @error('serial_number') is-invalid @enderror"
                                        name="serial_number" id="serial_number_oil"
                                        value="{{ $item->serial_number ?? old('serial_number') }}" />
                                    @error('serial_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <!-- SCRAP FIELDS -->
                        <div class="field-group scrap-fields" :class="{ 'active': selectedType === 'scrap' }">
                            <div class="row p-3 mt-4">
                           <div class="col-md-4">
                            <label for="battery_size">Battery Size:</label>
                            <input type="text" class="form-control @error('battery_size') is-invalid @enderror"
                                name="battery_size" id="battery_size" value="{{ $item->battery_size ?? old('battery_size') }}" />
                            @error('battery_size') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                            </div>
                        </div>

                        <div class="field-group" :class="{ 'active': selectedType === 'services' }">
                            <div class="row p-3 mt-4">
                                <div class="col-md-4">
                                    <label for="Services_scrap">Services:</label>
                                    <div class="input-group inputswidth">
                                        <select class="form-control Services-select searchable-select @error('services') is-invalid @enderror"
                                            name="services" id="Services_scrap">
                                            <option value="">Select Services</option>
                                            @foreach ($services as $service)
                                            <option value="{{ $service->id }}"
                                                 {{ old('services', $item->services) == $service->id ? 'selected' : '' }} >
                                                    {{ $service->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Services"
                                            data-fetch-route="{{ route('show.service', ':id') }}"
                                            data-update-route="{{ route('update.service', ':id') }}"
                                            data-delete-route="{{ route('destory.service', ':id') }}"
                                            data-target-select=".Services-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('services')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <button type="button" class="btn btn-outline-primary btn-sm mt-2 open-service-details-modal" id="addServiceDetailsBtn" disabled title="Select a service first">
                                        <i data-feather="plus" class="me-1" style="width:14px;height:14px;"></i> Add Detail
                                    </button>
                                    <div class="mt-3" id="serviceDetailsDisplay" style="display:none;">
                                        <label class="form-label small text-muted mb-1">Service details:</label>
                                        <div class="small border rounded p-2 bg-light" id="serviceDetailsDisplayList"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- FILTERS FIELDS -->
                        <div class="field-group filters-fields" :class="{ 'active': selectedType === 'filters' }">
                            <div class="row p-3 mt-4">
                                <!-- Quality field moved to common fields section above -->
                            </div>
                        </div>

                        <!-- BREAK PAD FIELDS -->
                        <div class="field-group breakpad-fields" :class="{ 'active': selectedType === 'breakpad' }">
                            <div class="row p-3 mt-4">
                                <!-- Quality field moved to common fields section above -->
                            </div>
                        </div>

                        <!-- COMMON MEDIA & DESCRIPTION -->
                        <div class="field-group media-fields" :class="{ 'active': selectedType }">
                            <div class="row mt-4">
                                <!-- Unit Management & Price Calculation Section -->
                                <div class="col-12 mt-4"
                                    x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil'|| selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0 fw-bold">Unit Management & Price Calculation</h5>
                                        </div>
                                        <div class="card-body">
                                            <!-- Unit Selection -->
                                            <div class="mb-4">
                                                <label class="form-label fw-bold mb-2">Select Unit & Conversion:</label>
                                                <div class="input-group">
                                                    <select class="form-control form-control-lg searchable-select @error('unit') is-invalid @enderror"
                                                        name="unit" id="unit_parts" 
                                                        data-saved-unit-id="{{ $itemUnitIdForSelect ?? $item->unit ?? ($item->unit_item->id ?? '') }}"
                                                        data-saved-unit-option="{{ $itemUnitOptionForSelect ?? '' }}"
                                                        style="width: 100%;">
                                                        <option value="">-- PLEASE SELECT --</option>
                                                    </select>
                                                    <button type="button" class="btn btn-secondary" id="editUnitBtn">
                                                        <i data-feather="edit"></i> Edit
                                                    </button>
                                                </div>
                                                @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>

                                            <!-- Warning Message -->
                                            <div id="priceWarning" class="alert alert-danger mb-3" style="display: none;">
                                                <i class="ti ti-alert-circle me-2"></i>
                                                <strong>WARNING:</strong> SALE PRICE IS LESS THAN COST PRICE (LOSS)
                                            </div>

                                            <!-- Cost Price Management -->
                                            <div id="costPriceManagement" class="mb-4" style="display: none;">
                                                <h6 class="text-success fw-bold mb-3 text-uppercase">Cost Price Management</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-6" id="costUnitContainer">
                                                        <div class="card border-success bg-light">
                                                            <div class="card-body">
                                                                <label class="form-label small fw-bold text-success text-uppercase mb-1" id="costUnitLabel">Unit Cost:</label>
                                                                <div class="input-group input-group-lg" style="display: flex; align-items: stretch;">
                                                                    <span class="input-group-text bg-success text-white fw-bold" style="display: flex; align-items: center; justify-content: center; height: auto; min-height: 100%; padding: 0.5rem 0.75rem;">Rs.</span>
                                                                    <input type="number" step="0.01" id="costPrice" name="total_price"
                                                                        class="form-control form-control-lg fw-bold" placeholder="0"
                                                                        value="{{ old('total_price', $item->total_price ?? '') }}"
                                                                        oninput="calculateFromUnit('cost')" style="flex: 1; height: 100%;">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6" id="baseCostPriceContainer" style="display: none;">
                                                        <div class="card border-success bg-light">
                                                            <div class="card-body">
                                                                <label class="form-label small fw-bold text-success text-uppercase mb-1" id="costBaseLabel">Per Base Cost:</label>
                                                                <div class="input-group input-group-lg" style="display: flex; align-items: stretch;">
                                                                    <span class="input-group-text bg-success text-white fw-bold" style="display: flex; align-items: center; justify-content: center; height: auto; min-height: 100%; padding: 0.5rem 0.75rem;">Rs.</span>
                                                                    <input type="number" step="0.01" id="baseCostPrice" name="price_per_unit"
                                                                        class="form-control form-control-lg fw-bold" placeholder="0"
                                                                        value="{{ old('price_per_unit', $item->price_per_unit ?? '') }}"
                                                                        oninput="calculateFromBase('cost')" style="flex: 1; height: 100%;">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Sale Price Management -->
                                            <div id="salePriceManagement" class="mb-4" style="display: none;">
                                                <h6 class="text-warning fw-bold mb-3 text-uppercase">Sale Price Management</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-6" id="saleUnitContainer">
                                                        <div class="card border-warning bg-light">
                                                            <div class="card-body">
                                                                <label class="form-label small fw-bold text-warning text-uppercase mb-1" id="saleUnitLabel">Unit Sale:</label>
                                                                <div class="input-group input-group-lg" style="display: flex; align-items: stretch;">
                                                                    <span class="input-group-text bg-warning text-white fw-bold" style="display: flex; align-items: center; justify-content: center; height: auto; min-height: 100%; padding: 0.5rem 0.75rem;">Rs.</span>
                                                                    <input type="number" step="0.01" id="salePrice" name="sale_price"
                                                                        class="form-control form-control-lg fw-bold" placeholder="0"
                                                                        value="{{ old('sale_price', $item->sale_price ?? '') }}"
                                                                        oninput="calculateFromUnit('sale')" style="flex: 1; height: 100%;">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6" id="baseSalePriceContainer" style="display: none;">
                                                        <div class="card border-warning bg-light">
                                                            <div class="card-body">
                                                                <label class="form-label small fw-bold text-warning text-uppercase mb-1" id="saleBaseLabel">Per Base Sale:</label>
                                                                <div class="input-group input-group-lg" style="display: flex; align-items: stretch;">
                                                                    <span class="input-group-text bg-warning text-white fw-bold" style="display: flex; align-items: center; justify-content: center; height: auto; min-height: 100%; padding: 0.5rem 0.75rem;">Rs.</span>
                                                                    <input type="number" step="0.01" id="baseSalePrice" name="sale_price_per_base"
                                                                        class="form-control form-control-lg fw-bold" placeholder="0"
                                                                        value="{{ old('sale_price_per_base', $item->sale_price_per_base ?? '') }}"
                                                                        oninput="calculateFromBase('sale')" style="flex: 1; height: 100%;">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Price Analysis -->
                                            <div id="derivedPricesList" class="mt-4">
                                                <!-- Analysis cards will be dynamically inserted here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Single Product Image (Thumbnail) -->
                                <div class="col-md-6 mt-3">
                                    <label for="imageInput" class="form-label fw-bold">Product Image (Thumbnail)</label>

                                    <div class="d-flex gap-2">
                                        <!-- Hidden file input -->
                                        <input type="file" id="imageInput" name="image" accept="image/*"
                                            class="d-none @error('image') is-invalid @enderror">

                                        <!-- Custom Upload Button -->
                                        <button type="button" id="uploadBtn" class="btn btn-primary flex-fill">
                                            <i data-feather="camera"></i> Take or Choose Photo
                                        </button>
                                    </div>

                                    <!-- Preview -->
                                    <div id="previewContainer" class="text-center mt-3" style="display: {{ $item->image ? 'block' : 'none' }};">
                                        <div class="position-relative d-inline-block">
                                            <img id="imagePreview"
                                                src="{{ $item->image ? asset($item->image) : 'https://pdis.co.kr/img/image.jpg' }}"
                                                alt="Preview"
                                                class="img-fluid rounded border shadow-sm"
                                                style="max-height: 220px; object-fit: cover;">
                                            <button type="button" id="removeBtn" class="btn btn-danger btn-sm position-absolute"
                                                    style="top: 8px; right: 8px;" data-id="{{ $item->id }}">
                                                <i data-feather="x"></i>
                                            </button>
                                        </div>
                                    </div>

                                    @error('image')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Multiple Product Images -->
                                <div class="col-md-6 mt-3">
                                    <label class="form-label fw-bold">Product Images (Multiple)</label>

                                    <div class="d-flex gap-2">
                                        <!-- Hidden file input -->
                                        <input type="file" id="imagesInput" name="images[]" accept="image/*" multiple
                                            class="d-none @error('images.*') is-invalid @enderror">

                                        <!-- Custom Upload Button -->
                                        <button type="button" id="uploadImagesBtn" class="btn btn-outline-primary flex-fill">
                                            <i data-feather="image"></i> Add More Photos
                                        </button>
                                    </div>

                                    <!-- Multiple Images Preview -->
                                    <div id="imagesPreviewContainer" class="mt-3" style="display: {{ !empty($item->images) ? 'block' : 'none' }};">
                                        <div class="d-flex flex-wrap gap-3" id="imagesPreview">
                                            @if(!empty($item->images))
                                                @foreach($item->images as $img)
                                                    <div class="position-relative" id="img-box-{{ md5($img) }}">
                                                        <img src="{{ $img }}" class="img-fluid rounded border"
                                                            style="width:120px; height:120px; object-fit:cover;">

                                                        <button type="button"
                                                                class="btn btn-danger btn-sm position-absolute top-0 end-0"
                                                                onclick="removeExistingImage('{{ $img }}', {{ $item->id }})">
                                                            X
                                                        </button>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>

                                    @error('images.*')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!-- Brochure -->
                                <div class="col-md-4 mt-3">
                                    <label for="p_brochure">Product Brochure (URL):</label>
                                    <input type="url" class="form-control @error('p_brochure') is-invalid @enderror"
                                        id="p_brochure" name="p_brochure" value="{{ $item->p_brochure ?? old('p_brochure') }}" />
                                    @error('p_brochure') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4 mt-3" x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil' || selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <label for="low_stock_parts" class="text-center fw-bold d-block" style="font-weight: 900 !important;">LOW STOCK:</label>
                                    <select class="form-control searchable-select @error('l_stock') is-invalid @enderror"
                                        name="l_stock" id="low_stock_parts">
                                        <option value="">Select Low Stock</option>
                                        @for($i = 1; $i <= 1000; $i++)
                                            <option value="{{ $i }}" {{ old('l_stock', $item->l_stock ?? '') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('l_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4 mt-3" x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil' || selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <label for="maintain_stock_parts" class="text-center fw-bold d-block" style="font-weight: 900 !important;">MAINTAIN STOCK:</label>
                                    <select class="form-control searchable-select @error('m_stock') is-invalid @enderror"
                                        name="m_stock" id="maintain_stock_parts">
                                        <option value="">Select Maintain Stock</option>
                                        @for($i = 1; $i <= 1000; $i++)
                                            <option value="{{ $i }}" {{ old('m_stock', $item->m_stock ?? '') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('m_stock') <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mt-3" x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil' || selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <label for="on_hand" class="text-center fw-bold d-block" style="font-weight: 900 !important;">OPENING STOCK:</label>
                                    <select class="form-control searchable-select @error('on_hand') is-invalid @enderror"
                                        name="on_hand" id="on_hand">
                                        <option value="">Select Opening Stock</option>
                                        @for($i = 1; $i <= 1000; $i++)
                                            <option value="{{ $i }}" {{ old('on_hand', $item->on_hand ?? '') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                    @error('on_hand') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Weight -->
                                    <div class="col-md-4 mt-3" x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil' || selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                                        <label for="weight" class="text-center fw-bold d-block" style="font-weight: 900 !important;">WEIGHT:</label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('weight_for_delivery') is-invalid @enderror" name="weight_for_delivery"
                                            id="weight" value="{{ $item->weight_for_delivery ?? old('weight_for_delivery') }}" placeholder="Enter weight" />
                                        @error('weight_for_delivery') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    
                                    <!-- Weight Unit -->
                                    <div class="col-md-4 mt-3" x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil' || selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                                        <label for="weight_unit" class="text-center fw-bold d-block" style="font-weight: 900 !important;">WEIGHT UNIT:</label>
                                        <select class="form-control searchable-select @error('weight_unit') is-invalid @enderror"
                                            name="weight_unit" id="weight_unit">
                                            <option value="">Select Weight Unit</option>
                                            <optgroup label="Metric System (most used worldwide)">
                                                <option value="ml" {{ old('weight_unit', $item->weight_unit ?? '') == 'ml' ? 'selected' : '' }}>MilliLiter (Ml)</option>
                                                <option value="mg" {{ old('weight_unit', $item->weight_unit ?? '') == 'mg' ? 'selected' : '' }}>Milligram (mg)</option>
                                                <option value="g" {{ old('weight_unit', $item->weight_unit ?? '') == 'g' ? 'selected' : '' }}>Gram (g)</option>
                                                <option value="kg" {{ old('weight_unit', $item->weight_unit ?? '') == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                                <option value="quintal" {{ old('weight_unit', $item->weight_unit ?? '') == 'quintal' ? 'selected' : '' }}>Quintal (100 kg)</option>
                                                <option value="tonne" {{ old('weight_unit', $item->weight_unit ?? '') == 'tonne' ? 'selected' : '' }}>Metric Ton / Tonne (t) = 1000 kg</option>
                                            </optgroup>
                                            <optgroup label="Imperial / Other Systems">
                                                <option value="oz" {{ old('weight_unit', $item->weight_unit ?? '') == 'oz' ? 'selected' : '' }}>Ounce (oz)</option>
                                                <option value="lb" {{ old('weight_unit', $item->weight_unit ?? '') == 'lb' ? 'selected' : '' }}>Pound (lb)</option>
                                                <option value="stone" {{ old('weight_unit', $item->weight_unit ?? '') == 'stone' ? 'selected' : '' }}>Stone (UK-specific)</option>
                                                <option value="ton_us" {{ old('weight_unit', $item->weight_unit ?? '') == 'ton_us' ? 'selected' : '' }}>Ton (US)</option>
                                                <option value="ton_uk" {{ old('weight_unit', $item->weight_unit ?? '') == 'ton_uk' ? 'selected' : '' }}>Ton (UK)</option>
                                            </optgroup>
                                        </select>
                                        @error('weight_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                <div class="col-md-4 mt-3 d-none">
                                    <label for="vehical_id">Vehicle Type:</label>
                                    <div class="input-group inputswidth">
                                        <select class="form-control searchable-select" name="vehical_id" id="vehical_id">
                                            <option value="">Select Vehicle Type</option>
                                            @foreach ($Vehicals as $vehicle)
                                            <option value="{{ $vehicle->id }}">
                                                {{ $vehicle->manutacturer_vehical->name??'-' }}
                                                {{ $vehicle->model_vehical->name??'-' }}
                                                {{ $vehicle->engine_vehical->name??'-' }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                {{-- PART NUMBER SELECT --}}

                                {{-- ASSOCIATED VEHICLES - Same as create/new: list + Add Vehicle modal --}}
                                <div class="col-md-12 mt-4" x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <div class="card border">
                                        <div class="card-header py-2">
                                            <h6 class="mb-0"><i class="ti ti-car me-1"></i> Associated Vehicles</h6>
                                            <small class="text-muted">Add vehicles this item fits. Part Number must be selected first.</small>
                                        </div>
                                        <div class="card-body py-3">
                                            <button type="button" class="btn btn-primary btn-sm" id="openItemVehicleModalEdit" data-bs-toggle="modal" data-bs-target="#itemVehicleAddModalEdit">
                                                <i class="ti ti-plus me-1"></i> Add Vehicle
                                            </button>
                                            <div id="itemVehiclesListEdit" class="mt-3"></div>
                                            <div id="itemVehiclesHiddenInputsEdit"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- VEHICLE TABLE - Hidden: Edit uses Associated Vehicles (create/new style) only --}}
                                <div class="col-md-12 d-none" x-show="false" x-data>
                                    <div class="table-responsive mt-4" style="max-height:250px;overflow-x:auto;overflow-y:auto;background-color:#ffffff;">
                                        <table class="table table-bordered" id="vehicleTable" style="background-color:#ffffff;">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th style="width: 50px; text-align: center;">
                                                        <div class="d-flex flex-column">
                                                            <div class="mb-2 fw-bold">Select</div>
                                                            <div class="text-center">
                                                                <input type="checkbox" id="selectAllVehicles" style="cursor: pointer;" title="Select All">
                                                            </div>
                                                        </div>
                                                    </th>
                                                    <th>
                                                        <div class="d-flex flex-column">
                                                            <div class="mb-2 fw-bold">Manufacturer</div>
                                                            <div class="input-group inputswidth">
                                                                <select id="vehicle_manufacturer_select" class="form-control car-manufacturer-select searchable-select" style="font-size: 12px; padding: 4px 8px;">
                                                                    <option value="">Select</option>
                                                                    @foreach ($carManufacturers as $manufacturer)
                                                                    <option value="{{ $manufacturer->id }}">{{ $manufacturer->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <button type="button" class="btn btn-sm btn-secondary open-universal-modal"
                                                                    data-mode="edit" data-title="Edit Manufacturer"
                                                                    data-fetch-route="{{ route('show.car.manufacturer', ':id') }}"
                                                                    data-update-route="{{ route('update.car.manufacturer', ':id') }}"
                                                                    data-delete-route="{{ route('destory.car.manufacturer', ':id') }}"
                                                                    data-target-select=".car-manufacturer-select">
                                                                    <i data-feather="edit" style="width: 14px; height: 14px;"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </th>
                                                    <th>
                                                        <div class="d-flex flex-column">
                                                            <div class="mb-2 fw-bold">Model</div>
                                                            <div class="input-group inputswidth">
                                                                <select id="vehicle_model_select" class="form-control car-model-select searchable-select" style="font-size: 12px; padding: 4px 8px;">
                                                                    <option value="">Select</option>
                                                                    @foreach ($carModels as $carModel)
                                                                    <option value="{{ $carModel->id }}">{{ $carModel->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <button type="button" class="btn btn-sm btn-secondary open-universal-modal"
                                                                    data-mode="edit" data-title="Edit Car Model"
                                                                    data-fetch-route="{{ route('show.car.model', ':id') }}"
                                                                    data-update-route="{{ route('update.car.model', ':id') }}"
                                                                    data-delete-route="{{ route('destory.car.model', ':id') }}"
                                                                    data-target-select=".car-model-select">
                                                                    <i data-feather="edit" style="width: 14px; height: 14px;"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </th>
                                                    <th>
                                                        <div class="d-flex flex-column">
                                                            <div class="mb-2 fw-bold">Country</div>
                                                            <div class="input-group inputswidth">
                                                                <select id="vehicle_country_select" class="form-control car-country-select searchable-select" style="font-size: 12px; padding: 4px 8px;">
                                                                    <option value="">Select</option>
                                                                    @foreach ($carCountries as $carCountry)
                                                                    <option value="{{ $carCountry->id }}">{{ $carCountry->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <button type="button" class="btn btn-sm btn-secondary open-universal-modal"
                                                                    data-mode="edit" data-title="Edit Country"
                                                                    data-fetch-route="{{ route('show.car.country', ':id') }}"
                                                                    data-update-route="{{ route('update.car.country', ':id') }}"
                                                                    data-delete-route="{{ route('destory.car.country', ':id') }}"
                                                                    data-target-select=".car-country-select">
                                                                    <i data-feather="edit" style="width: 14px; height: 14px;"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </th>
                                                    <th>
                                                        <div class="d-flex flex-column">
                                                            <button type="button" class="btn btn-sm btn-primary open-year-range-modal mb-2" style="font-size: 11px; padding: 0.25rem 0.5rem; width: 100%;">
                                                                <i data-feather="calendar" style="width: 12px; height: 12px;"></i>
                                                                <span style="margin-left: 4px;">Year Range</span>
                                                            </button>
                                                            <div id="selectedYearRangesDisplay" class="border rounded p-2" style="min-height: 40px; max-height: 80px; overflow-y: auto; background-color: #f8f9fa; font-size: 10px; cursor: pointer;" data-show-all="false">
                                                                <div class="text-muted text-center" style="font-size: 10px;">No ranges selected</div>
                                                            </div>
                                                        </div>
                                                    </th>
                                                    <th>
                                                        <div class="d-flex flex-column">
                                                            <div class="mb-2 fw-bold">Engine</div>
                                                            <div class="input-group inputswidth">
                                                                <select id="vehicle_engine_select" class="form-control car-engine-select searchable-select" style="font-size: 12px; padding: 4px 8px;">
                                                                    <option value="">Select</option>
                                                                    @foreach ($engineccs as $engineCc)
                                                                    <option value="{{ $engineCc->id }}">{{ $engineCc->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <button type="button" class="btn btn-sm btn-secondary open-universal-modal"
                                                                    data-mode="edit" data-title="Edit Engine CC"
                                                                    data-fetch-route="{{ route('show.engine_cc', ':id') }}"
                                                                    data-update-route="{{ route('update.engine_cc', ':id') }}"
                                                                    data-delete-route="{{ route('destory.engine_cc', ':id') }}"
                                                                    data-target-select=".car-engine-select">
                                                                    <i data-feather="edit" style="width: 14px; height: 14px;"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </th>
                                                  
                                                    <th>
                                                        <div class="d-flex flex-column">
                                                            <button type="button" class="btn btn-sm btn-warning clearVehicleFiltersBtn mb-2" style="font-size: 11px; padding: 0.25rem 0.5rem; width: 100%;">
                                                                <i data-feather="x-circle" style="width: 12px; height: 12px;"></i>
                                                                <span style="margin-left: 4px;">Empty</span>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-primary loadVehicleBtn" style="font-size: 11px; padding: 0.25rem 0.5rem; width: 100%;">
                                                                <i data-feather="refresh-cw" style="width: 12px; height: 12px;"></i>
                                                                <span style="margin-left: 4px;">Load</span>
                                                            </button>
                                                        </div>
                                                    </th>
                                                    <th>
                                                        <div class="d-flex flex-column">
                                                            <div class="mb-2 fw-bold">Part Number</div>
                                                        </div>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $itemVehicleIds = $item->vehical_ids ?? []; @endphp
                                                @foreach ($Vehis as $car)
                                                <tr data-part="{{ $car->v_part_number_id??'' }}">
                                                    <td style="text-align: center;">
                                                        <input type="checkbox" class="vehicle-checkbox" data-vehicle-id="{{ $car->id ?? '' }}"
                                                            {{ in_array($car->id ?? 0, $itemVehicleIds) ? 'checked' : '' }}
                                                            data-part="{{ $car->v_part_number_id??'' }}"
                                                            data-manufacturer="{{ $car->car_manufacturer }}"
                                                            data-model="{{ $car->car_model_name }}"
                                                            data-engine="{{ $car->engine_cc }}"
                                                            data-country="{{ $car->car_manufactured_country }}"
                                                            style="cursor: pointer;">
                                                    </td>
                                                    <td>{{ $car->manutacturer_vehical->name ?? '-' }}</td>

                                                    <td>{{ $car->model_vehical->name ?? '-' }}</td>
                                                    <td>{{ $car->country_vehical->name ?? '-' }}</td>
                                                    <td>
                                                        @if($car->year_ranges && count($car->year_ranges) > 0)
                                                            <div style="display: inline-flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                                                                @foreach($car->year_ranges as $yearRange)
                                                                    <span class="badge" style="background-color: #7DD3FC; color: #0C4A6E; padding: 6px 12px; border-radius: 6px; font-weight: 500; font-size: 13px; white-space: nowrap;">{{ $yearRange }}</span>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <span class="badge bg-secondary">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $car->engine_vehical->name ?? '-' }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-primary editVehicleBtn"
                                                            data-part="{{ $car->v_part_number_id }}"
                                                            data-manufacturer="{{ $car->car_manufacturer }}"
                                                            data-model="{{ $car->car_model_name }}"
                                                            data-engine="{{ $car->engine_cc }}"
                                                            data-country="{{ $car->car_manufactured_country }}"
                                                            data-year-ranges="{{ json_encode($car->year_ranges ?? []) }}"
                                                            data-year-from="{{ $car->year_from ?? '' }}"
                                                            data-year-to="{{ $car->year_to ?? '' }}">
                                                            <i class="ti ti-pencil"></i>
                                                        </button>
                                                    </td>
                                                    <td>{{ $car->vehical_part_number->name ?? '-' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                    {{-- <div class="col-md-12">
                                            <div class="d-flex justify-content-end">
                                                <a href="{{ route('all.vehical') }}" class="btn btn-primary">See All</a>
                                            </div>
                                            <div class="table-responsive mt-4" style="max-height: 250px; overflow-y:auto;">
                                                <table class="table table-bordered table-striped" id="vehicleTable">
                                                    <thead class="bg-dark text-white">
                                                        <tr>
                                                            <th>Manufacturer</th>
                                                            <th>Model</th>
                                                            <th>Year</th>
                                                            <th>Engine</th>
                                                            <th>Country</th>
                                                            <th>Status</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div> --}}
                                        </div>

                                    <div class="col-md-12 mt-4 field-group checkbox-fields" :class="{ 'active': selectedType }">
                                    <div class="d-none justify-content-between align-items-center">
                                        <div class="section-box">
                                            <div class="form-check">
                                                <input type="hidden" name="is_active" value="0">
                                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive"
                                                    value="1" style="width: 20px; height:20px"
                                                    {{ ($item->is_active ?? old('is_active', 1)) ? 'checked' : '' }} />
                                                <label class="form-check-label ms-1 mt-1" for="isActive">Is Active</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="hidden" name="auto_deactive" value="0">
                                                <input class="form-check-input" type="checkbox" name="auto_deactive"
                                                    id="autoDeactive" value="1" style="width: 20px; height:20px"
                                                    {{ ($item->auto_deactive ?? old('auto_deactive', 0)) ? 'checked' : '' }} />
                                                <label class="form-check-label ms-1 mt-1" for="autoDeactive">Auto
                                                    De-Active</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="hidden" name="is_dead" value="0">
                                                <input class="form-check-input" type="checkbox" name="is_dead" id="isDead"
                                                    value="1" style="width: 20px; height:20px"
                                                    {{ ($item->is_dead ?? old('is_dead', 0)) ? 'checked' : '' }} />
                                                <label class="form-check-label ms-1 mt-1" for="isDead">Is Dead Item</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <label for="content" class="form-label fw-bold">Short Description</label>
                                    <input name="short_disc" class="form-control"
                                        value="{{ old('short_disc', $item->short_disc ?? '') }}" />
                                </div>
                                <!-- Description -->
                                <div class="col-md-12 mt-3">
                                    <label for="content" class="form-label fw-bold">Product Description</label>
                                    <textarea id="summernote" name="pro_dis" class="form-control">{!! old('pro_dis', $item->pro_dis ?? '') !!}</textarea>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <!-- Submit Buttons -->
                    <div class="page-btn d-flex justify-content-end mt-4">
                        <a href="{{ route('all.items') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary me-2">Update</button>
                    </div>
                </div>
            </form>
            <h4 id="itemsTableTitle" class="mt-4">Last 5 Created Items</h4>
            <div class="table-responsive mt-3">
                <table id="searchableTable" class="table table-hover table-center">
                <thead class="thead-primary">
                    <tr>
                        <th>Product Image</th>
                        <th>Item Details</th>
                        <th>User & Branch</th>
                        <th>View</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="latestItemsTableBody">
                    @forelse ($latestItems as $item)
                    <tr>

                        <td>
                            <img src="{{ asset($item->image ?? 'assets/img/media/default.png') }}" width="70"
                                height="70" class="rounded item-image" style="cursor:pointer;" data-bs-toggle="modal"
                                data-bs-target="#imageModal"
                                data-src="{{ asset($item->image ?? 'assets/img/media/default.png') }}">
                        </td>
                        <td>  
                            <div class="small">
                                <div class="fw-semibold">{{ $item->product_item->name ?? $item->partnumber_item->name ?? '-' }}</div>
                                @if($item->product_item)
                                <div class="text-muted">{{ $item->partnumber_item->name ?? '-' }}</div>
                                @endif
                                <div> {{ $item->category->name ?? '-' }}</div>
                                <div> {{ $item->company_item->name ?? '-' }}</div>
                                <div> {{ $item->quality_item->name ?? '-' }}</div>
                            </div>
                        </td>
                        <td>
                            <div>{{ $item->item_user->name ?? '-' }}</div>
                            <div class="small text-muted">{{ $item->item_user && $item->item_user->branch ? $item->item_user->branch->branch_name : '-' }}</div>
                        </td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('item.show',$item->id) }}">
                                <i data-feather="eye" class="me-1"></i> View
                            </a>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-primary  dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('item.edit',$item->id) }}">
                                            <i data-feather="edit" class="me-1"></i> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)"
                                            onclick="confirmDelete('delete-form-{{ $item->id }}')" class="dropdown-item">
                                            <i data-feather="trash-2" class="feather-trash-2"></i> Delete
                                        </a>
                                        <!-- Hidden delete form -->
                                        <form id="delete-form-{{ $item->id }}"
                                            action="{{ route('item.delete', $item->id) }}" method="POST"
                                            style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </li>

                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No items found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div class="text-center mt-3" id="allItemsButtonContainer" style="display: none;">
                <button type="button" class="btn btn-primary" id="loadAllItemsBtn">
                    <i data-feather="list"></i> All Items
                </button>
            </div>
        </div>
    </div>
</div>

    <div class="modal fade" id="Unit-add-modal">
        <div class="modal-dialog modal-dialog-centered ">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="Unit-modal-title">Add Unit</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="Unit-form" method="POST" enctype="multipart/form-data">
                    @csrf
                        <div class="modal-body">
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
                        <button type="button" class="btn btn-danger d-none" id="deleteUnitBtn"><i class="ti ti-trash"></i> Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="vehical-add-modal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="vehical-modal-title">Add Vehical</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="vehical-form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-3">
                    <div class="container">
                        <div class="row">
                            {{-- 1. Car Company ---------------------------------------------------- --}}
                            <div class="col-md-6 mt-3">
                                <label for="car_manufacturer">Car Manufactured:</label>
                                <div class="input-group">
                                    <select
                                        class="form-control car-manufacturer-select searchable-select @error('car_manufacturer') is-invalid @enderror"
                                        name="car_manufacturer" id="car_manufacturer">
                                        <option value="">Select Manufactured</option>
                                        @foreach ($carManufacturers as $manufacturer)
                                        <option value="{{ $manufacturer->id }}" {{
                                            old('car_manufacturer')==$manufacturer->id ?
                                            'selected' : '' }}>
                                            {{ $manufacturer->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-secondary open-universal-modal"
                                        data-title="Add Manufacturerd" data-mode="add"
                                        data-route="{{ route('post.car.manufacturer') }}"
                                        data-target-select=".car-manufacturer-select">
                                        <i data-feather="plus" class="feather-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-secondary open-universal-modal"
                                        data-mode="edit" data-title="Edit Manufacturer"
                                        data-fetch-route="{{ route('show.car.manufacturer', ':id') }}"
                                        data-update-route="{{ route('update.car.manufacturer', ':id') }}"
                                        data-delete-route="{{ route('destory.car.manufacturer', ':id') }}"
                                        data-target-select=".car-manufacturer-select">
                                        <i data-feather="edit"></i>
                                    </button>
                                </div>
                                @error('car_manufacturer') <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- 2. Car Name ------------------------------------------------------- --}}
                            <div class="col-md-6 mt-3">
                                <label for="car_name">Car Model:</label>
                                <div class="input-group">
                                    <select
                                        class="form-control car-model-select searchable-select @error('car_model_name') is-invalid @enderror"
                                        name="car_model_name" id="car_model_name">
                                        <option value="">Select Car Model</option>
                                        @foreach ($carModels as $item)
                                        <option value="{{ $item->id }}" {{ old('car_model_name')==$item->id ?
                                            'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-secondary open-universal-modal"
                                        data-title="Add Car Model" data-mode="add"
                                        data-route="{{ route('post.car.model') }}"
                                        data-target-select=".car-model-select">
                                        <i data-feather="plus" class="feather-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-secondary open-universal-modal"
                                        data-mode="edit" data-title="Edit Car Model"
                                        data-fetch-route="{{ route('show.car.model', ':id') }}"
                                        data-update-route="{{ route('update.car.model', ':id') }}"
                                        data-delete-route="{{ route('destory.car.model', ':id') }}"
                                        data-target-select=".car-model-select">
                                        <i data-feather="edit"></i>
                                    </button>
                                </div>
                                @error('car_model_name') <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- 3. Car Model Name ------------------------------------------------- --}}
                            <div class="col-md-6 mt-3">
                                <label for="engine_cc">Engin CC:</label>
                                <div class="input-group">
                                    <select
                                        class="form-control car-engine-select searchable-select @error('engine_cc') is-invalid @enderror"
                                        name="engine_cc" id="engine_cc">
                                        <option value="">Select Engine CC</option>
                                        @foreach ($engineccs as $item)
                                        <option value="{{ $item->id }}" {{ old('engine_cc')==$item->id ?
                                            'selected' : '' }}>
                                            {{ $item->name }} CC
                                        </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-secondary open-universal-modal"
                                        data-title="Add Engine CC" data-mode="add"
                                        data-route="{{ route('post.engine.cc') }}"
                                        data-target-select=".car-engine-select">
                                        <i data-feather="plus" class="feather-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-secondary open-universal-modal"
                                        data-mode="edit" data-title="Edit Engine CC"
                                        data-fetch-route="{{ route('show.engine_cc', ':id') }}"
                                        data-update-route="{{ route('update.engine_cc', ':id') }}"
                                        data-delete-route="{{ route('destory.engine_cc', ':id') }}"
                                        data-target-select=".car-engine-select">
                                        <i data-feather="edit"></i>
                                    </button>
                                </div>
                                @error('engine_cc') <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- 4. Car Manufactured Country --------------------------------------- --}}
                            <div class="col-md-6 mt-3">
                                <label for="car_manufactured_country">Car Manufactured Country:</label>
                                <div class="input-group">
                                    <select
                                        class="form-control car-country-select searchable-select @error('car_manufactured_country') is-invalid @enderror"
                                        name="car_manufactured_country" id="car_manufactured_country">
                                        <option value="">Select Country</option>
                                        @foreach ($carCountries as $item)
                                        <option value="{{ $item->id }}" {{ old('car_manufactured_country')==$item->id ?
                                            'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                        @endforeach
                                    </select>

                                    <button type="button" class="btn btn-secondary open-universal-modal"
                                        data-title="Add Country" data-mode="add"
                                        data-route="{{ route('post.car.country') }}"
                                        data-target-select=".car-country-select">
                                        <i data-feather="plus" class="feather-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-secondary open-universal-modal"
                                        data-mode="edit" data-title="Edit Country"
                                        data-fetch-route="{{ route('show.car.country', ':id') }}"
                                        data-update-route="{{ route('update.car.country', ':id') }}"
                                        data-delete-route="{{ route('destory.car.country', ':id') }}"
                                        data-target-select=".car-country-select">
                                        <i data-feather="edit"></i>
                                    </button>
                                </div>
                                @error('car_manufactured_country') <div class="invalid-feedback">{{ $message }}
                                </div> @enderror
                            </div>
                              <div class="col-md-6 mt-3" >
                                    <label for="part_number">Part Number:</label>
                                    <div class="input-group">
                                        <select
                                            class="form-control part_number-select searchable-select @error('v_part_number_id') is-invalid @enderror"
                                            name="v_part_number_id" id="part_number">
                                            <option value="">Select Part Number</option>
                                            @foreach ($partnumbers as $item)
                                            <option value="{{ $item->id }}" {{ old('v_part_number_id')==$item->id ?
                                                'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-title="Add Part Number" data-mode="add"
                                            data-route="{{ route('post.partnumber') }}"
                                            data-target-select=".part_number-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Part Number"
                                            data-fetch-route="{{ route('show.partnumber', ':id') }}"
                                            data-update-route="{{ route('update.partnumber', ':id') }}"
                                            data-delete-route="{{ route('destory.partnumber', ':id') }}"
                                            data-target-select=".part_number-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('v_part_number_id') <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mt-3">
                                    <label><strong>YEAR RANGES</strong></label>
                                    <div id="yearRangesContainer">
                                        <div class="year-range-item mb-2">
                                            <div class="row g-2">
                                                <div class="col-5">
                                                    <select class="form-control year-from-select" name="year_from[]">
                                                        <option value="">From Year</option>
                                                        @for($year = 1900; $year <= 2100; $year++)
                                                            <option value="{{ $year }}">{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div class="col-5">
                                                    <select class="form-control year-to-select" name="year_to[]">
                                                        <option value="">To Year</option>
                                                        @for($year = 1900; $year <= 2100; $year++)
                                                            <option value="{{ $year }}">{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div class="col-2">
                                                    <button type="button" class="btn btn-danger btn-sm removeYearRange" style="display: none;">X</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" id="addYearRangeBtn" class="btn btn-primary btn-sm mt-2">
                                        + ADD ANOTHER YEAR RANGE
                                    </button>
                                    @error('carmanufactured_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                        </div>
                    </div>
                    {{-- <div class="table-responsive mt-5" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-bordered table-striped" id="vehicleTable">
                            <thead class="bg-primary fixed">
                                <tr>
                                    <th>Vehical Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($Vehicals as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div> --}}
                </div>
                     <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>

                        <button type="submit" class="btn btn-primary" data-action="save">
                            Save
                        </button>

                        <button type="submit" class="btn btn-success" data-action="save_new">
                            Save & New
                        </button>
                    </div>
                <input type="hidden" id="submit_type" name="submit_type" value="save">
            </form>
        </div>
    </div>
</div>

{{-- Add Vehicle Modal for Edit - Same as create/new (AJAX save, no redirect/refresh) --}}
<div class="modal fade" id="itemVehicleAddModalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-car me-2"></i>Add Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="itemVehicleAddFormEdit" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="v_part_number_id" id="itemVehiclePartNumberEdit" value="">
                    <div class="mb-3">
                        <label class="form-label">Make <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select id="itemVehicleMakeEdit" class="form-control form-select item-vehicle-manufacturer-edit searchable-select" name="car_manufacturer" required>
                                <option value="">— Select —</option>
                                @foreach ($carManufacturers as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-secondary open-universal-modal text-white" data-mode="edit" data-title="Edit Make" data-fetch-route="{{ route('show.car.manufacturer', ':id') }}" data-update-route="{{ route('update.car.manufacturer', ':id') }}" data-delete-route="{{ route('destory.car.manufacturer', ':id') }}" data-target-select=".item-vehicle-manufacturer-edit" title="Edit Make"><i data-feather="edit" class="feather-16"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Model <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select id="itemVehicleModelEdit" class="form-control form-select item-vehicle-model-edit searchable-select" name="car_model_name" required>
                                <option value="">— Select —</option>
                                @foreach ($carModels as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-secondary open-universal-modal text-white" data-mode="edit" data-title="Edit Model" data-fetch-route="{{ route('show.car.model', ':id') }}" data-update-route="{{ route('update.car.model', ':id') }}" data-delete-route="{{ route('destory.car.model', ':id') }}" data-target-select=".item-vehicle-model-edit" title="Edit Model"><i data-feather="edit" class="feather-16"></i></button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Year From <span class="text-danger">*</span></label>
                            <select id="itemVehicleYearFromEdit" class="form-control form-select item-vehicle-year-from-edit searchable-select" name="year_from[]" required>
                                <option value="">From</option>
                                @for($y = 1900; $y <= 2100; $y++)
                                <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Year To <span class="text-danger">*</span></label>
                            <select id="itemVehicleYearToEdit" class="form-control form-select item-vehicle-year-to-edit searchable-select" name="year_to[]" required>
                                <option value="">To</option>
                                @for($y = 1900; $y <= 2100; $y++)
                                <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Engine CC</label>
                        <div class="input-group">
                            <select id="itemVehicleEngineEdit" class="form-control form-select item-vehicle-engine-edit searchable-select" name="engine_cc">
                                <option value="">— Select —</option>
                                @foreach ($engineccs as $e)
                                <option value="{{ $e->id }}">{{ $e->name }} CC</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-secondary open-universal-modal text-white" data-mode="edit" data-title="Edit Engine CC" data-fetch-route="{{ route('show.engine_cc', ':id') }}" data-update-route="{{ route('update.engine_cc', ':id') }}" data-delete-route="{{ route('destory.engine_cc', ':id') }}" data-target-select=".item-vehicle-engine-edit" title="Edit Engine CC"><i data-feather="edit" class="feather-16"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Country</label>
                        <div class="input-group">
                            <select id="itemVehicleCountryEdit" class="form-control form-select item-vehicle-country-edit searchable-select" name="car_manufactured_country">
                                <option value="">— Select —</option>
                                @foreach ($carCountries as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-secondary open-universal-modal text-white" data-mode="edit" data-title="Edit Country" data-fetch-route="{{ route('show.car.country', ':id') }}" data-update-route="{{ route('update.car.country', ':id') }}" data-delete-route="{{ route('destory.car.country', ':id') }}" data-target-select=".item-vehicle-country-edit" title="Edit Country"><i data-feather="edit" class="feather-16"></i></button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="itemVehicleAddFormEdit" class="btn btn-primary" id="itemVehicleAddBtnEdit">Add Vehicle</button>
            </div>
        </div>
    </div>
</div>

<!-- Year Range Selector Modal (same as Create Item) -->
<div class="modal fade" id="yearRangeModal" tabindex="-1" aria-labelledby="yearRangeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="yearRangeModalLabel">Select Year Range</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="filterYearRangesContainer">
                    <div class="filter-year-range-item mb-2">
                        <div class="row g-2">
                            <div class="col-5">
                                <label class="form-label small">From Year</label>
                                <select class="form-control filter-year-from">
                                    <option value="">Select Year</option>
                                    @php
                                        $currentYear = date('Y');
                                        for($year = $currentYear; $year >= 1980; $year--) {
                                            echo '<option value="' . $year . '">' . $year . '</option>';
                                        }
                                    @endphp
                                </select>
                                <div class="invalid-feedback" style="display: none; font-size: 10px;">From Year cannot be greater than To Year</div>
                            </div>
                            <div class="col-5">
                                <label class="form-label small">To Year</label>
                                <select class="form-control filter-year-to">
                                    <option value="">Select Year</option>
                                    @php
                                        $currentYear = date('Y');
                                        for($year = $currentYear; $year >= 1980; $year--) {
                                            echo '<option value="' . $year . '">' . $year . '</option>';
                                        }
                                    @endphp
                                </select>
                                <div class="invalid-feedback" style="display: none; font-size: 10px;">To Year cannot be less than From Year</div>
                            </div>
                            <div class="col-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-sm removeFilterYearRange" style="display: none;">X</button>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-sm mt-2" id="addMoreFilterYearRange">
                    <i data-feather="plus" style="width: 14px; height: 14px;"></i>
                    Add More Year Range
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="applyYearRangeFilter">Apply Filter</button>
            </div>
        </div>
    </div>
</div>

    <!-- Service Details Modal - Add kia kia kaam shamil hai -->
    <div class="modal fade" id="serviceDetailsModal" tabindex="-1" aria-labelledby="serviceDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="serviceDetailsModalLabel">Service Details - <span id="serviceDetailsServiceName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="service_details_modal_id" value="">
                    <p class="text-muted small mb-3">Is service mein kia kia kaam shamil hai - Add/Edit details below</p>
                    <div class="mb-3">
                        <label class="form-label">Add new detail</label>
                        <div class="row g-2 align-items-end">
                            <div class="col">
                                <input type="text" class="form-control" id="service_detail_new_input" placeholder="e.g. Oil Change, Filter Replacement">
                            </div>
                            <div class="col-auto" style="min-width: 120px;">
                                <label class="form-label small mb-0">Price</label>
                                <input type="number" class="form-control" id="service_detail_new_price" placeholder="0" min="0" step="0.01">
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-primary" id="serviceDetailAddBtn">Add</button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Details list</label>
                        <ul class="list-group" id="serviceDetailsList"></ul>
                        <p class="text-muted small mt-2 mb-0" id="serviceDetailsEmpty" style="display:none;">No details added yet. Add above.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="serviceDetailsModalSave">Save</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Universal Modal -->
    <div class="modal fade" id="universal-add-modal" tabindex="-1" aria-labelledby="universal-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0; padding: 20px 25px; border-bottom: none;">
                    <div class="d-flex align-items-center w-100">
                        <div class="me-3" style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="ti ti-plus" style="font-size: 20px;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-0 fw-bold" id="universal-modal-title" style="color: white; font-size: 20px;">Add Item</h4>
                            <small class="text-white-50" id="universal-modal-subtitle" style="font-size: 12px;">Fill in the details below</small>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.8; font-size: 18px;"></button>
                    </div>
                </div>
                <form id="universal-form" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" id="universal-type" value="">
                    <div class="modal-body" style="padding: 30px;">
                        <div class="form-group mb-4">
                            <label class="form-label fw-semibold mb-2" style="color: #495057; font-size: 14px;">
                                <i class="ti ti-tag me-2 text-primary"></i>Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   name="name" 
                                   id="universal-name" 
                                   required
                                   placeholder="Enter name"
                                   style="border-radius: 8px; border: 2px solid #e9ecef; padding: 12px 15px; font-size: 15px; transition: all 0.3s;"
                                   onfocus="this.style.borderColor='#667eea'; this.style.boxShadow='0 0 0 0.2rem rgba(102, 126, 234, 0.25)'"
                                   onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'">
                            <div class="invalid-feedback" id="universal-name-error"></div>
                        </div>
                        <div class="form-group mt-4" id="image-field" style="display: none;">
                            <label class="form-label fw-semibold mb-2" style="color: #495057; font-size: 14px;">
                                <i class="ti ti-photo me-2 text-primary"></i>Image
                            </label>
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-grow-1">
                                    <input type="file" 
                                           class="form-control form-control-lg" 
                                           name="image" 
                                           id="universal-image" 
                                           accept="image/*"
                                           style="border-radius: 8px; border: 2px solid #e9ecef; padding: 12px 15px; font-size: 14px;">
                                    <small class="text-muted d-block mt-2">
                                        <i class="ti ti-info-circle me-1"></i>Supported formats: JPG, PNG, GIF (Max 2MB)
                                    </small>
                                </div>
                                <div class="text-center">
                                    <img id="universal-image-preview" 
                                         src="" 
                                         alt="Preview"
                                         style="width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 2px solid #e9ecef; display:none; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                    <div id="universal-image-placeholder" style="width: 120px; height: 120px; background: #f8f9fa; border-radius: 10px; border: 2px dashed #dee2e6; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                        <i class="ti ti-photo" style="font-size: 32px;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="padding: 20px 30px; background: #f8f9fa; border-radius: 0 0 12px 12px; border-top: 1px solid #e9ecef;">
                        <button type="button" class="btn btn-light btn-lg" data-bs-dismiss="modal" style="border-radius: 8px; padding: 10px 20px; font-weight: 500;">
                            <i class="ti ti-x me-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-danger btn-lg d-none me-2" id="universal-delete-btn" style="border-radius: 8px; padding: 10px 20px; font-weight: 500;">
                            <i class="ti ti-trash me-2"></i>Delete
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg" id="universal-save-btn" style="border-radius: 8px; padding: 10px 25px; font-weight: 500; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                            <i class="ti ti-check me-2"></i><span>Save</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
<script>
$(document).ready(function () {
    $("#vehicleTable tbody tr").hide();
});
</script>

<script>
$("#part_number_id").on("change", function () {

    let selectedPart = $(this).val();

    $("#vehicleTable tbody tr").each(function () {
        let rowPart = $(this).data("part");

        if (!selectedPart) {
            // No selection → hide everything
            $(this).hide();
        }
        else if (rowPart == selectedPart) {
            $(this).show();
        }
        else {
            $(this).hide();
        }
    });
});
</script>

    <script>
    // Prevent modal from opening if part number is not selected
    $(document).on('click', '[data-bs-target="#vehical-add-modal"]', function(e) {
        let outsidePart = $('#part_number_id').val();
        if (!outsidePart || outsidePart === '' || outsidePart === null) {
            e.preventDefault();
            e.stopImmediatePropagation();
            e.stopPropagation();

            // Highlight the part number field
            $('#part_number_id').addClass('is-invalid');
            // Add visual feedback
            $('#part_number_id').focus();
            // Scroll to the part number field if needed
            $('html, body').animate({
                scrollTop: $('#part_number_id').offset().top - 100
            }, 300);
            return false;
        }
    });

    // When modal opens, pre-fill Part Number from the outside select field
    // Also validate that part number is selected before allowing modal to show
    $('#vehical-add-modal').on('show.bs.modal', function(e) {
        let outsidePart = $('#part_number_id').val();
        if (!outsidePart || outsidePart === '' || outsidePart === null) {
            e.preventDefault();
            toastr.error('Please select part number first.');
            $('#part_number_id').addClass('is-invalid').focus();
            $('html, body').animate({
                scrollTop: $('#part_number_id').offset().top - 100
            }, 300);
            return false;
        }
    });

    $('#vehical-add-modal').on('shown.bs.modal', function() {
        let outsidePart = $('#part_number_id').val();
        if (outsidePart) {
            $('#part_number').val(outsidePart).trigger('change');
            $('#part_number').removeClass('is-invalid');
        } else {
            // If somehow modal opened without part number, close it
            $('#vehical-add-modal').modal('hide');
            toastr.error('Please select part number first.');
            $('#part_number_id').addClass('is-invalid').focus();
        }
    });

    // Remove error styling when part number is selected (both fields)
    $(document).on('change', '#part_number, #part_number_id', function() {
        if ($(this).val()) {
            $(this).removeClass('is-invalid');
            // Also remove error from the other field
            $('#part_number, #part_number_id').removeClass('is-invalid');
        }
    });
    </script>
    <script>
    // Set hidden input based on which submit button was clicked
    // Global AJAX setup for CSRF token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        error: function(xhr, status, error) {
            if (xhr.status === 419) {
                // CSRF token expired - show user-friendly error message
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Session Expired',
                        text: 'Your session has expired. Please refresh the page and try again.',
                        confirmButtonText: 'Refresh Page',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        window.location.reload();
                    });
                } else {
                    alert('Your session has expired. Please refresh the page and try again.');
                    window.location.reload();
                }
                return false;
            }
        }
    });

    // Handle 419 errors globally for all AJAX requests
    $(document).ajaxError(function(event, xhr, settings) {
        if (xhr.status === 419) {
            // Prevent multiple error dialogs
            if ($('body').data('showing-419-error')) {
                return;
            }
            $('body').data('showing-419-error', true);
            
            // Show user-friendly error message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Session Expired',
                    text: 'Your session has expired. Please refresh the page and try again.',
                    confirmButtonText: 'Refresh Page',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    $('body').data('showing-419-error', false);
                    window.location.reload();
                });
            } else {
                alert('Your session has expired. Please refresh the page and try again.');
                $('body').data('showing-419-error', false);
                window.location.reload();
            }
        }
    });

    $("#vehical-form button[type=submit]").on("click", function() {
        $("#submit_type").val($(this).data("action"));
    });
    </script>
    <script>
    $("#vehical-form").off("submit").on("submit", function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Check if modal is actually visible
        if (!$('#vehical-add-modal').hasClass('show')) {
            console.log('Vehicle modal not visible, ignoring submit');
            return false;
        }
        
        let form = this;

        // Validate part number before form submission
        let partNumber = $('#part_number').val();
        let outsidePartNumber = $('#part_number_id').val();

        // Check both fields (modal field and outside field)
        if ((!partNumber || partNumber === '' || partNumber === null) &&
            (!outsidePartNumber || outsidePartNumber === '' || outsidePartNumber === null)) {
            toastr.error('Please select part number first.');
            // Add error styling to part number fields
            $('#part_number').addClass('is-invalid');
            $('#part_number_id').addClass('is-invalid');
            // Focus on modal part number field
            $('#part_number').focus();
            // Scroll to the field if needed
            $('html, body').animate({
                scrollTop: $('#part_number').closest('.modal-content').offset().top - 50
            }, 300);
            return false;
        }

        // Use outside part number if modal part number is not set
        if (!partNumber || partNumber === '') {
            partNumber = outsidePartNumber;
            $('#part_number').val(partNumber).trigger('change');
        }

        // Remove error styling if part number is selected
        $('#part_number').removeClass('is-invalid');
        $('#part_number_id').removeClass('is-invalid');

        let formData = new FormData(form);
        let submitType = $("#submit_type").val();
        let outsidePart = $('#part_number_id').val();
        $.ajax({
            url: "{{ route('post.product_vehical') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                // Only show success message if there's a valid response with vehicles
                if (!res) {
                    console.error('No response from server');
                    toastr.error('No response from server');
                    return;
                }
                
                if (res.errors && res.errors.length > 0) {
                    // Display overlap errors
                    res.errors.forEach(function(error) {
                        toastr.error(error);
                    });
                    return;
                }
                
                if (res.duplicate_years?.length) {
                    toastr.warning("Already exists for year(s): " + res.duplicate_years.join(', '));
                } else if (res.vehicles && res.vehicles.length > 0) {
                    // Only show success if vehicles were actually saved and modal is visible
                    if ($('#vehical-add-modal').hasClass('show')) {
                        toastr.success(res.message || "Vehicle saved successfully!");
                        // 🔊 Play save sound when vehicle is saved
                        if (typeof playSaveSound === 'function') {
                            playSaveSound();
                        }
                    }
                } else if (res.message && $('#vehical-add-modal').hasClass('show')) {
                    // If there's a message but no vehicles, show it (might be a warning)
                    toastr.info(res.message);
                }

                // Add/update vehicles in table without page reload
                if (res.vehicles && res.vehicles.length > 0) {
                    // Group vehicles by config (part, manufacturer, model, engine, country)
                    let vehicleGroups = {};
                    res.vehicles.forEach(function(v) {
                        let key = `${v.v_part_number_id}-${v.car_manufacturer}-${v.car_model_name}-${v.engine_cc}-${v.car_manufactured_country}`;
                        if (!vehicleGroups[key]) {
                            vehicleGroups[key] = {
                                v_part_number_id: v.v_part_number_id,
                                car_manufacturer: v.car_manufacturer,
                                car_model_name: v.car_model_name,
                                engine_cc: v.engine_cc,
                                car_manufactured_country: v.car_manufactured_country,
                                manutacturer_vehical: v.manutacturer_vehical,
                                model_vehical: v.model_vehical,
                                engine_vehical: v.engine_vehical,
                                country_vehical: v.country_vehical,
                                vehical_part_number: v.vehical_part_number,
                                yearRanges: []
                            };
                        }
                        // Add year range
                        if (v.year_from && v.year_to) {
                            let yearStr = v.year_from == v.year_to ? v.year_from : v.year_from + '-' + v.year_to;
                            if (vehicleGroups[key].yearRanges.indexOf(yearStr) === -1) {
                                vehicleGroups[key].yearRanges.push(yearStr);
                            }
                        }
                    });

                    // Check if vehicle group already exists in table, if yes update it, else add new
                    Object.keys(vehicleGroups).forEach(function(key) {
                        let group = vehicleGroups[key];

                        // Find existing row by matching all config fields
                        let existingRow = null;
                        $("#vehicleTable tbody tr").each(function() {
                            let $row = $(this);
                            if ($row.data('part') == group.v_part_number_id &&
                                $row.find('.editVehicleBtn').data('manufacturer') == group.car_manufacturer &&
                                $row.find('.editVehicleBtn').data('model') == group.car_model_name &&
                                $row.find('.editVehicleBtn').data('engine') == group.engine_cc &&
                                $row.find('.editVehicleBtn').data('country') == group.car_manufactured_country) {
                                existingRow = $row;
                                return false; // break loop
                            }
                        });

                        // Build year ranges display with light blue badges (sorted by year)
                        let yearRangesHtml = '';
                        if (group.yearRanges.length > 0) {
                            // Sort year ranges by 'from' year
                            group.yearRanges.sort(function(a, b) {
                                let aFrom = parseInt(a.split('-')[0]);
                                let bFrom = parseInt(b.split('-')[0]);
                                return aFrom - bFrom;
                            });

                            yearRangesHtml = '<div style="display: inline-flex; flex-wrap: wrap; gap: 6px; align-items: center;">';
                            group.yearRanges.forEach(function(range) {
                                yearRangesHtml += `<span class="badge" style="background-color: #7DD3FC; color: #0C4A6E; padding: 6px 12px; border-radius: 6px; font-weight: 500; font-size: 13px; white-space: nowrap;">${range}</span>`;
                            });
                            yearRangesHtml += '</div>';
                        } else {
                            yearRangesHtml = '<span class="badge bg-secondary">-</span>';
                        }

                        if (existingRow && existingRow.length > 0) {
                            // Update existing row
                            existingRow.find('td:eq(0)').text(group.manutacturer_vehical?.name || '-');
                            existingRow.find('td:eq(1)').text(group.model_vehical?.name || '-');
                            existingRow.find('td:eq(2)').html(yearRangesHtml);
                            existingRow.find('td:eq(3)').text(group.engine_vehical?.name || '-');
                            existingRow.find('td:eq(4)').text(group.country_vehical?.name || '-');
                            existingRow.find('td:eq(5)').text(group.vehical_part_number?.name || '-');

                            // Update edit button data attributes - store first year range for backward compatibility
                            let editBtn = existingRow.find('.editVehicleBtn');
                            editBtn.attr('data-part', group.v_part_number_id);
                            editBtn.attr('data-manufacturer', group.car_manufacturer);
                            editBtn.attr('data-model', group.car_model_name);
                            editBtn.attr('data-engine', group.engine_cc);
                            editBtn.attr('data-country', group.car_manufactured_country);
                            // Store all year ranges as JSON string
                            editBtn.attr('data-year-ranges', JSON.stringify(group.yearRanges));
                            // For backward compatibility, also store first range
                            if (group.yearRanges.length > 0) {
                                let firstRange = group.yearRanges[0];
                                let rangeParts = firstRange.split('-');
                                editBtn.attr('data-year-from', rangeParts[0]);
                                editBtn.attr('data-year-to', rangeParts.length > 1 ? rangeParts[1] : rangeParts[0]);
                            }
                        } else {
                            // Add new row
                            let firstYearFrom = '';
                            let firstYearTo = '';
                            if (group.yearRanges.length > 0) {
                                let firstRange = group.yearRanges[0];
                                let rangeParts = firstRange.split('-');
                                firstYearFrom = rangeParts[0];
                                firstYearTo = rangeParts.length > 1 ? rangeParts[1] : rangeParts[0];
                            }

                            $("#vehicleTable tbody").append(`
                                <tr data-part="${group.v_part_number_id}">
                                    <td>${group.manutacturer_vehical?.name || '-'}</td>
                                    <td>${group.model_vehical?.name || '-'}</td>
                                    <td>${yearRangesHtml}</td>
                                    <td>${group.engine_vehical?.name || '-'}</td>
                                    <td>${group.country_vehical?.name || '-'}</td>
                                    <td>${group.vehical_part_number?.name || '-'}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary editVehicleBtn"
                                            data-part="${group.v_part_number_id}"
                                            data-manufacturer="${group.car_manufacturer}"
                                            data-model="${group.car_model_name}"
                                            data-engine="${group.engine_cc}"
                                            data-country="${group.car_manufactured_country}"
                                            data-year-ranges="${JSON.stringify(group.yearRanges)}"
                                            data-year-from="${firstYearFrom}"
                                            data-year-to="${firstYearTo}">
                                            <i class="ti ti-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                        }
                    });
                }

                // Reset the form
                form.reset();
                // Clear year ranges
                let yearOptions = '';
                for (let year = 1900; year <= 2100; year++) {
                    yearOptions += `<option value="${year}">${year}</option>`;
                }
                $("#yearRangesContainer").html(`
                    <div class="year-range-item mb-2">
                        <div class="row g-2">
                            <div class="col-5">
                                <select class="form-control year-from-select" name="year_from[]">
                                    <option value="">From Year</option>
                                    ${yearOptions}
                                </select>
                            </div>
                            <div class="col-5">
                                <select class="form-control year-to-select" name="year_to[]">
                                    <option value="">To Year</option>
                                    ${yearOptions}
                                </select>
                            </div>
                            <div class="col-2">
                                <button type="button" class="btn btn-danger btn-sm removeYearRange" style="display: none;">X</button>
                            </div>
                        </div>
                    </div>
                `);
                updateRemoveButtons();

                // Close modal or keep open based on submit type
                if (submitType === 'save') {
                    if (outsidePart) {
                        $("#part_number").val(outsidePart).trigger('change');
                    } else {
                        $("#part_number").val('').trigger('change');
                    }
                    $("#vehical-add-modal").modal('hide');
                } else if (submitType === 'save_new') {
                    $("#part_number").val('').trigger('change');
                }
            },
            error: function(xhr) {
                let response = xhr.responseJSON;
                if (response && response.errors && Array.isArray(response.errors)) {
                    response.errors.forEach(function(error) {
                        toastr.error(error);
                    });
                } else {
                    let msg = response?.message || 'Something went wrong!';
                    toastr.error(msg);
                }
            }
        });
    });
    </script>
    <script>
    $(document).on('click', '.editVehicleBtn', function() {
        let yearRangesData = $(this).data('year-ranges');
        let yearRanges = [];

        // Parse year ranges from JSON data attribute
        if (yearRangesData) {
            try {
                yearRanges = typeof yearRangesData === 'string' ? JSON.parse(yearRangesData) : yearRangesData;
            } catch(e) {
                console.error('Error parsing year ranges:', e);
                // Fallback to year-from and year-to
                let yearFrom = $(this).data('year-from');
                let yearTo = $(this).data('year-to');
                if (yearFrom && yearTo) {
                    yearRanges = [yearFrom == yearTo ? yearFrom : yearFrom + '-' + yearTo];
                }
            }
        } else {
            // Fallback to year-from and year-to if data-year-ranges not available
            let yearFrom = $(this).data('year-from');
            let yearTo = $(this).data('year-to');
            if (yearFrom && yearTo) {
                yearRanges = [yearFrom == yearTo ? yearFrom : yearFrom + '-' + yearTo];
            }
        }

        $('#car_manufacturer').val($(this).data('manufacturer')).trigger('change');
        $('#car_model_name').val($(this).data('model')).trigger('change');
        $('#engine_cc').val($(this).data('engine')).trigger('change');
        $('#part_number').val($(this).data('part')).trigger('change');
        $('#car_manufactured_country').val($(this).data('country')).trigger('change');

        // Build year range HTML
        let yearRangeHtml = '';

        // Generate year options
        let yearOptions = '';
        for (let year = 1900; year <= 2100; year++) {
            yearOptions += `<option value="${year}">${year}</option>`;
        }

        if (yearRanges.length > 0) {
            // Populate with existing year ranges
            yearRanges.forEach(function(rangeStr) {
                let fromYear = '';
                let toYear = '';

                // Parse range string (e.g., "2014-2021" or "2015")
                if (rangeStr.includes('-')) {
                    let parts = rangeStr.split('-');
                    fromYear = parts[0].trim();
                    toYear = parts[1].trim();
                } else {
                    fromYear = rangeStr.trim();
                    toYear = rangeStr.trim();
                }

                // Build options with selected year
                let fromOptions = '<option value="">From Year</option>';
                let toOptions = '<option value="">To Year</option>';
                for (let year = 1900; year <= 2100; year++) {
                    fromOptions += `<option value="${year}" ${fromYear == year ? 'selected' : ''}>${year}</option>`;
                    toOptions += `<option value="${year}" ${toYear == year ? 'selected' : ''}>${year}</option>`;
                }

                yearRangeHtml += `
                    <div class="year-range-item mb-2">
                        <div class="row g-2">
                            <div class="col-5">
                                <select class="form-control year-from-select" name="year_from[]">
                                    ${fromOptions}
                                </select>
                            </div>
                            <div class="col-5">
                                <select class="form-control year-to-select" name="year_to[]">
                                    ${toOptions}
                                </select>
                            </div>
                            <div class="col-2">
                                <button type="button" class="btn btn-danger btn-sm removeYearRange">X</button>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            // No ranges, show empty range input
            yearRangeHtml = `
                <div class="year-range-item mb-2">
                    <div class="row g-2">
                        <div class="col-5">
                            <select class="form-control year-from-select" name="year_from[]">
                                <option value="">From Year</option>
                                ${yearOptions}
                            </select>
                        </div>
                        <div class="col-5">
                            <select class="form-control year-to-select" name="year_to[]">
                                <option value="">To Year</option>
                                ${yearOptions}
                            </select>
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-danger btn-sm removeYearRange" style="display: none;">X</button>
                        </div>
                    </div>
                </div>
            `;
        }

        $('#yearRangesContainer').html(yearRangeHtml);
        updateRemoveButtons();
        $('#vehical-add-modal').modal('show');
    });
    </script>
    <script>
    // Function to update remove buttons visibility
    function updateRemoveButtons() {
        let ranges = document.querySelectorAll('.year-range-item');
        ranges.forEach((range, index) => {
            let removeBtn = range.querySelector('.removeYearRange');
            if (ranges.length > 1) {
                removeBtn.style.display = 'block';
            } else {
                removeBtn.style.display = 'none';
            }
        });
    }

    // Add year range
    document.getElementById('addYearRangeBtn').addEventListener('click', function() {
        let container = document.getElementById('yearRangesContainer');
        let newRange = document.createElement('div');
        newRange.className = 'year-range-item mb-2';

        // Build year options
        let yearOptions = '';
        for (let year = 1900; year <= 2100; year++) {
            yearOptions += `<option value="${year}">${year}</option>`;
        }

        newRange.innerHTML = `
            <div class="row g-2">
                <div class="col-5">
                    <select class="form-control year-from-select" name="year_from[]">
                        <option value="">From Year</option>
                        ${yearOptions}
                    </select>
                </div>
                <div class="col-5">
                    <select class="form-control year-to-select" name="year_to[]">
                        <option value="">To Year</option>
                        ${yearOptions}
                    </select>
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-danger btn-sm removeYearRange">X</button>
                </div>
            </div>
        `;
        container.appendChild(newRange);
        updateRemoveButtons();
    });

    // Remove year range
    $(document).on('click', '.removeYearRange', function() {
        $(this).closest('.year-range-item').remove();
        updateRemoveButtons();
    });

    // Initialize remove buttons on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateRemoveButtons();
    });
    </script>

<script>
$('.delete-single-image').on('click', function (e) {
    e.preventDefault();

    let id = $(this).data('id');
    let box = $("#img-box-" + id);   // wrapper to remove

    $.ajax({
        url: `/item/delete-image/${id}`,
        type: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (res) {
            if (res.status === true) {
                // 🔥 Remove image and button instantly (no reload)
                box.fadeOut(300, function() { $(this).remove(); });
            } else {
                alert(res.message);
            }
        },
        error: function (xhr) {
            console.error(xhr.responseText);
        }
    });
});

function removeExistingImage(imgPath, itemId) {
    $.ajax({
        url: '/item/delete-single-from-array',  // new route
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            item_id: itemId,
            image: imgPath
        },
        success: function(res) {
            if(res.status === true){
                // Remove image from page
                $("#img-box-" + md5(imgPath)).fadeOut(300, function(){ $(this).remove(); });
            } else {
                alert(res.message);
            }
        },
        error: function(xhr) {
            console.log(xhr.responseText);
        }
    });
}

// Simple JS function to mimic PHP md5
function md5(string) {
    return CryptoJS.MD5(string).toString(); // make sure you include CryptoJS
}




    document.getElementById('toggleBaseUnit').addEventListener("change", function() {
        document.getElementById('baseDetails').style.display = this.checked ? "block" : "none";
    });
    
    // Add Base Unit functionality for Unit Modal in edit.blade.php
    let baseUnitIndex = 1;
    let currentUnitId = null;
    document.getElementById('addBaseUnitBtn')?.addEventListener('click', function() {
        const container = document.getElementById('baseUnitsContainer');
        if (!container) return;
        
        // Auto-select: copy base unit from last existing row so new row has same base unit pre-selected
        const lastItem = container.querySelector('.base-unit-item:last-child');
        let clonedBaseUnitId = '';
        if (lastItem) {
            const lastBaseUnitSelect = lastItem.querySelector('select[name*="[base_unit_id]"]');
            if (lastBaseUnitSelect && lastBaseUnitSelect.value) {
                clonedBaseUnitId = lastBaseUnitSelect.value;
            }
        }
        
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
        
        if (clonedBaseUnitId) {
            const newSelect = newItem.querySelector('select[name*="[base_unit_id]"]');
            if (newSelect) newSelect.value = clonedBaseUnitId;
        }
        
        baseUnitIndex++;
        updateUnitRemoveButtons();
    });
    
    // Remove Base Unit functionality for Unit Modal
    function updateUnitRemoveButtons() {
        const container = document.getElementById('baseUnitsContainer');
        if (!container) return;
        
        const allItems = container.querySelectorAll('.base-unit-item');
        allItems.forEach(function(item) {
            const removeBtn = item.querySelector('.removeBaseUnit');
            if (allItems.length > 1) {
                removeBtn.style.display = 'block';
            } else {
                removeBtn.style.display = 'none';
            }
        });
    }
    
    // Event delegation for remove buttons in Unit Modal
    document.addEventListener('click', function(e) {
        if (e.target.closest('.removeBaseUnit') && e.target.closest('#baseUnitsContainer')) {
            e.target.closest('.base-unit-item').remove();
            updateUnitRemoveButtons();
        }
    });
    
    // Initialize remove buttons on page load
    updateUnitRemoveButtons();

    $("#Unit-form").off("submit").on("submit", function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    // Check if modal is actually visible
    if (!$('#Unit-add-modal').hasClass('show')) {
        console.log('Unit modal not visible, ignoring submit');
        return false;
    }

    let formData = new FormData(this);
    var url = currentUnitId ? "{{ url('units') }}/" + currentUnitId : "{{ route('post.units') }}";
    var method = currentUnitId ? 'POST' : 'POST';
    if (currentUnitId) {
        formData.append('_method', 'PUT');
    }

    $.ajax({
        url: url,
        type: method,
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            if (!res || !res.success) {
                console.error('Unit save failed', res);
                if (res && res.message) {
                    toastr.error(res.message);
                }
                return;
            }
            if(res.success){
                var wasEdit = !!currentUnitId;
                loadUnitsForDropdown();
                $("#Unit-add-modal").modal("hide");
                $("#Unit-form")[0].reset();
                currentUnitId = null;
                $('#deleteUnitBtn').addClass('d-none');
                $('#Unit-modal-title').text('Add Unit');
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: wasEdit ? 'Unit updated successfully' : 'Unit added successfully'
                });
            }
        },
        error: function(xhr) {
            console.error('Unit save error', xhr);
            if (xhr.responseJSON && xhr.responseJSON.message) {
                toastr.error(xhr.responseJSON.message);
            } else {
                toastr.error('Failed to save unit. Please try again.');
            }
        }
    });
    });

    // Initialize unit dropdown
    $(document).ready(function() {
        // Hide price management sections initially
        $('#costPriceManagement').hide();
        $('#salePriceManagement').hide();
        
        // Load units from API
        loadUnitsForDropdown();
        
        // Handle unit change
        $('#unit_parts').on('change', handleUnitChange);
        
        // Initialize searchable-select if the class exists
        if ($.fn.select2 && $('#unit_parts').hasClass('searchable-select')) {
            $('#unit_parts').select2({
                placeholder: '-- PLEASE SELECT --',
                allowClear: true,
                width: '100%'
            });
        }
        
        // Check if unit is already selected (from old value) and trigger change
        setTimeout(function() {
            const selectedValue = $('#unit_parts').val();
            if (selectedValue && selectedValue !== '') {
                handleUnitChange();
            }
        }, 500);

        /* =========================
           EDIT UNIT (Edit Unit button next to Select Unit & Conversion)
        ==========================*/
        $('#editUnitBtn').on('click', function() {
            var selected = $('#unit_parts option:selected');
            if (!selected.length || !selected.val()) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Select Unit', 'Please select a unit first', 'warning');
                } else {
                    alert('Please select a unit first');
                }
                return;
            }
            var selectedValue = selected.val();
            var unitIdFromOption = selected.attr('data-unit-id') || (selectedValue + '').split('_')[0];
            currentUnitId = unitIdFromOption;
            $('#Unit-modal-title').text('Edit Unit');
            $('#deleteUnitBtn').removeClass('d-none');

            $.ajax({
                url: "{{ url('units') }}/" + currentUnitId,
                type: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                success: function(res) {
                    if (res && res.unit) {
                        var unit = res.unit;
                        $('#Unit-form [name="name"]').val(unit.name);
                        $('#Unit-form [name="short_name"]').val(unit.short_name);
                        var allowDecimalValue = (unit.allow_decimal === true || unit.allow_decimal === 1 || unit.allow_decimal === '1') ? '1' : '0';
                        $('#Unit-form [name="allow_decimal"]').val(allowDecimalValue);

                        $('#baseUnitsContainer').empty();
                        baseUnitIndex = 0;
                        if (unit.base_units && unit.base_units.length > 0) {
                            $('#Unit-form [name="define_base_unit"]').prop('checked', true);
                            $('#baseDetails').show();
                            unit.base_units.forEach(function(baseUnit) {
                                var mult = (baseUnit.pivot && baseUnit.pivot.multiplier) || baseUnit.multiplier || 1;
                                addBaseUnitRowForEdit(baseUnit.id, mult);
                            });
                        } else {
                            $('#Unit-form [name="define_base_unit"]').prop('checked', false);
                            $('#baseDetails').hide();
                            addBaseUnitRowForEdit('', '');
                        }
                        updateUnitRemoveButtons();
                        $('#Unit-add-modal').modal('show');
                    }
                },
                error: function(xhr) {
                    var unitName = selected.attr('data-unit-name') || selected.text().split('(')[0].trim();
                    var shortName = selected.attr('data-unit-short') || '';
                    $('#Unit-form [name="name"]').val(unitName);
                    $('#Unit-form [name="short_name"]').val(shortName);
                    $('#Unit-form [name="allow_decimal"]').val(selected.attr('data-allow-decimal') || '0');
                    var baseUnitId = selected.attr('data-base-unit-id');
                    if (baseUnitId) {
                        $('#Unit-form [name="define_base_unit"]').prop('checked', true);
                        $('#baseDetails').show();
                        $('#baseUnitsContainer').empty();
                        baseUnitIndex = 0;
                        addBaseUnitRowForEdit(baseUnitId, selected.attr('data-multiplier') || 1);
                    } else {
                        $('#Unit-form [name="define_base_unit"]').prop('checked', false);
                        $('#baseDetails').hide();
                        $('#baseUnitsContainer').empty();
                        baseUnitIndex = 0;
                        addBaseUnitRowForEdit('', '');
                    }
                    updateUnitRemoveButtons();
                    $('#Unit-add-modal').modal('show');
                }
            });
        });

        $('#Unit-add-modal').on('hidden.bs.modal', function() {
            $('#Unit-form')[0].reset();
            $('#Unit-form [name="allow_decimal"]').val('0');
            $('#deleteUnitBtn').addClass('d-none');
            $('#Unit-modal-title').text('Add Unit');
            currentUnitId = null;
        });

        $('#deleteUnitBtn').on('click', function() {
            if (!currentUnitId) return;
            if (typeof Swal === 'undefined') {
                if (confirm('Delete this unit?')) { /* TODO: AJAX delete */ }
                return;
            }
            Swal.fire({
                title: 'Are you sure?',
                text: 'This unit will be deleted',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('units') }}/" + currentUnitId,
                        type: 'POST',
                        data: { _method: 'DELETE', _token: $('input[name=_token]').val() },
                        success: function(res) {
                            if (res && res.success) {
                                loadUnitsForDropdown();
                                $('#Unit-add-modal').modal('hide');
                                $('#Unit-form')[0].reset();
                                currentUnitId = null;
                                $('#deleteUnitBtn').addClass('d-none');
                                Swal.fire('Deleted!', 'Unit deleted successfully', 'success');
                            }
                        }
                    });
                }
            });
        });
    });

    function addBaseUnitRowForEdit(baseUnitId, multiplier) {
        var container = $('#baseUnitsContainer');
        var html = '<div class="base-unit-item mb-3 p-3 border rounded"><div class="row g-2"><div class="col-5"><label class="small">Multiplier</label><input type="number" step="0.0001" name="base_units[' + baseUnitIndex + '][multiplier]" class="form-control form-control-sm" placeholder="e.g., 1, 2, 3" value="' + (multiplier || '') + '"></div><div class="col-6"><label class="small">Base Unit</label><select name="base_units[' + baseUnitIndex + '][base_unit_id]" class="form-control form-control-sm"><option value="">Select Base Unit</option>@foreach($units as $u)<option value="{{ $u->id }}">{{ $u->name }} ({{ $u->short_name }})</option>@endforeach</select></div><div class="col-1 d-flex align-items-end"><button type="button" class="btn btn-danger btn-sm removeBaseUnit"><i class="ti ti-x"></i></button></div></div></div>';
        container.append(html);
        if (baseUnitId) {
            container.find('.base-unit-item:last select').val(baseUnitId);
        }
        baseUnitIndex++;
    }
    
    // Load units with conversions from API
    function loadUnitsForDropdown() {
        $.ajax({
            url: '{{ route("api.units.search") }}',
            data: { search: '' },
            success: function(data) {
                // Store current value before clearing
                const currentValue = $('#unit_parts').val();
                // Get unit value from database - directly from $item->unit column
                @php
                    // Get unit value - try multiple sources
                    $unitValue = null;
                    
                    // First try: Direct from unit column
                    if (isset($item->unit) && $item->unit !== null && $item->unit !== '') {
                        $unitValue = (string) trim($item->unit);
                    }
                    // Second try: From unit_item relationship
                    elseif (isset($item->unit_item) && $item->unit_item && isset($item->unit_item->id)) {
                        $unitValue = (string) $item->unit_item->id;
                    }
                    // Third try: Get original value from database
                    elseif (method_exists($item, 'getOriginal')) {
                        $originalUnit = $item->getOriginal('unit');
                        if ($originalUnit !== null && $originalUnit !== '') {
                            $unitValue = (string) trim($originalUnit);
                        }
                    }
                    
                    // Clean the value - remove underscore if present (format: unit_id_base_unit_id)
                    if ($unitValue !== null && $unitValue !== '') {
                        if (strpos($unitValue, '_') !== false) {
                            $parts = explode('_', $unitValue);
                            $unitValue = $parts[0]; // Get only unit_id
                        }
                    }
                @endphp
                
                // Pass unit value to JavaScript - ensure it's a valid number or null
                @php
                    // Ensure unitValue is properly formatted
                    $jsUnitValue = null;
                    if ($unitValue !== null && $unitValue !== '') {
                        $jsUnitValue = (string) trim($unitValue);
                        // Remove underscore if present
                        if (strpos($jsUnitValue, '_') !== false) {
                            $jsUnitValue = explode('_', $jsUnitValue)[0];
                        }
                        // Ensure it's a valid number
                        if (is_numeric($jsUnitValue)) {
                            $jsUnitValue = (int) $jsUnitValue;
                        } else {
                            $jsUnitValue = null;
                        }
                    }
                @endphp
                var itemUnitId = @json($jsUnitValue);
                
                // Clear and rebuild options
                $('#unit_parts').empty().append('<option value="">-- PLEASE SELECT --</option>');
                
                // Keep track of unique unit IDs to avoid duplicates
                const uniqueUnits = {};
                
                data.forEach(function(item) {
                    // Unique value per conversion: unit_id_base_unit_id_multiplier (e.g. 12_8_1, 12_8_2) so Can 1L vs Can 2L save correctly
                    const basePart = (item.base_unit_id || 'main');
                    const multPart = (item.multiplier != null && item.multiplier !== '') ? '_' + item.multiplier : '';
                    const optionId = item.id + '_' + basePart + multPart;
                    const option = new Option(item.display_text, optionId, false, false);
                    option.setAttribute('data-unit-id', item.id);
                    option.setAttribute('data-unit-name', item.name);
                    option.setAttribute('data-unit-short', item.short_name);
                    option.setAttribute('data-base-unit-id', item.base_unit_id || '');
                    option.setAttribute('data-base-unit-name', item.base_unit_name || '');
                    option.setAttribute('data-multiplier', item.multiplier || '');
                    option.setAttribute('data-decimal-places', item.decimal_places || 0);
                    option.setAttribute('data-allow-decimal', item.allow_decimal || 0);
                    $('#unit_parts').append(option);
                    
                    // Store unique units for Base Unit dropdown
                    if (!uniqueUnits[item.id]) {
                        uniqueUnits[item.id] = {
                            id: item.id,
                            name: item.name,
                            short_name: item.short_name,
                            display_text: item.name + ' (' + item.short_name + ')'
                        };
                    }
                });
                
                // Update Base Unit dropdowns with unique units
                updateBaseUnitDropdowns(uniqueUnits);
                
                // Initialize Select2 if needed before selecting unit
                let select2Initialized = false;
                if ($.fn.select2 && $('#unit_parts').hasClass('searchable-select')) {
                    if (!$('#unit_parts').hasClass('select2-hidden-accessible')) {
                        $('#unit_parts').select2({
                            placeholder: '-- PLEASE SELECT --',
                            allowClear: true,
                            width: '100%'
                        });
                    }
                    select2Initialized = true;
                } else if ($.fn.select2) {
                    // Initialize Select2 even if searchable-select class is not present
                    if (!$('#unit_parts').hasClass('select2-hidden-accessible')) {
                        $('#unit_parts').select2({
                            placeholder: '-- PLEASE SELECT --',
                            allowClear: true,
                            width: '100%'
                        });
                    }
                    select2Initialized = true;
                }
                
                // Set the selected unit from item data (after options are loaded and Select2 initialized)
                var savedItemUnitId = itemUnitId;
                var savedUnitOption = $('#unit_parts').attr('data-saved-unit-option') || '';
                if (savedUnitOption) savedUnitOption = String(savedUnitOption).trim();
                
                if (!savedItemUnitId || savedItemUnitId === null || savedItemUnitId === undefined || savedItemUnitId === '') {
                    var dataUnitId = $('#unit_parts').attr('data-saved-unit-id');
                    if (dataUnitId && dataUnitId !== '' && dataUnitId !== 'null') {
                        savedItemUnitId = dataUnitId;
                    }
                }
                
                function setSelectedUnit() {
                    let foundOption = null;
                    if (savedUnitOption && savedUnitOption !== '' && savedUnitOption !== 'null') {
                        var $opt = $('#unit_parts').find('option[value="' + savedUnitOption.replace(/"/g, '\\"') + '"]');
                        if ($opt.length) {
                            foundOption = $opt.val();
                        }
                        // Backward compat: old unit_option may be "12_8" (no multiplier); match first option like "12_8_1"
                        if (!foundOption && savedUnitOption.indexOf('_') !== -1) {
                            var prefix = savedUnitOption + '_';
                            $('#unit_parts option').each(function() {
                                if ($(this).val() && $(this).val().indexOf(prefix) === 0) {
                                    foundOption = $(this).val();
                                    return false;
                                }
                            });
                        }
                    }
                    if (!foundOption && savedItemUnitId && savedItemUnitId !== 'null') {
                        let savedUnitId = String(savedItemUnitId).trim();
                        if (savedUnitId.includes('_')) savedUnitId = savedUnitId.split('_')[0];
                        savedUnitId = parseInt(savedUnitId);
                        if (isNaN(savedUnitId)) savedUnitId = null;
                        if (savedUnitId != null) {
                            $('#unit_parts option').each(function() {
                                if ($(this).val() === '' || $(this).val() === null) return true;
                                const optionUnitId = $(this).attr('data-unit-id');
                                const optionBaseUnitId = $(this).attr('data-base-unit-id') || '';
                                if (optionUnitId && parseInt(optionUnitId) === savedUnitId) {
                                    if (!foundOption || optionBaseUnitId === 'main' || optionBaseUnitId === '') {
                                        foundOption = $(this).val();
                                        return false;
                                    }
                                }
                            });
                            if (!foundOption) {
                                $('#unit_parts option').each(function() {
                                    if ($(this).val() === '' || $(this).val() === null) return true;
                                    if (parseInt($(this).attr('data-unit-id')) === savedUnitId) {
                                        foundOption = $(this).val();
                                        return false;
                                    }
                                });
                            }
                        }
                    }
                    
                    // Set the found option
                    if (foundOption) {
                        // Set the value
                        $('#unit_parts').val(foundOption);
                        
                        // Update Select2 if it's initialized
                        if (select2Initialized) {
                            if ($('#unit_parts').hasClass('select2-hidden-accessible')) {
                                $('#unit_parts').trigger('change.select2');
                            } else {
                                // Initialize Select2 if not already initialized
                                $('#unit_parts').select2({
                                    placeholder: '-- PLEASE SELECT --',
                                    allowClear: true,
                                    width: '100%'
                                });
                                $('#unit_parts').trigger('change.select2');
                            }
                        }
                        
                        // Verify and trigger handlers after a delay
                        setTimeout(function() {
                            const currentVal = $('#unit_parts').val();
                            
                            if (currentVal === foundOption) {
                                if (typeof handleUnitChange === 'function') {
                                    handleUnitChange();
                                }
                                if (typeof syncPrices === 'function') {
                                    syncPrices();
                                }
                            } else {
                                // Retry setting the value
                                $('#unit_parts').val(foundOption);
                                if (select2Initialized && $('#unit_parts').hasClass('select2-hidden-accessible')) {
                                    $('#unit_parts').trigger('change.select2');
                                }
                                setTimeout(function() {
                                    const retryVal = $('#unit_parts').val();
                                    if (retryVal === foundOption) {
                                        if (typeof handleUnitChange === 'function') {
                                            handleUnitChange();
                                        }
                                        if (typeof syncPrices === 'function') {
                                            syncPrices();
                                        }
                                    }
                                }, 300);
                            }
                        }, 500);
                    }
                }
                
                // Call the function after a delay to ensure options are loaded and Select2 is ready
                setTimeout(function() {
                    setSelectedUnit();
                }, 500);
                
                // Trigger Select2 update to refresh the dropdown
                if ($.fn.select2 && $('#unit_parts').hasClass('select2-hidden-accessible')) {
                    $('#unit_parts').trigger('change.select2');
                }
            }
        });
    }
    
    // Function to update all Base Unit dropdowns in the Unit modal
    function updateBaseUnitDropdowns(unitsMap) {
        // Convert unitsMap object to array
        const unitsArray = Object.values(unitsMap);
        
        // Update all Base Unit dropdowns in the modal
        $('#baseUnitsContainer select[name*="[base_unit_id]"]').each(function() {
            const $select = $(this);
            const currentValue = $select.val(); // Store current selection
            
            // Clear existing options except the first one
            $select.find('option:not(:first)').remove();
            
            // Add all units
            unitsArray.forEach(function(unit) {
                const option = new Option(unit.display_text, unit.id, false, false);
                $select.append(option);
            });
            
            // Restore previous selection if it still exists
            if (currentValue) {
                $select.val(currentValue);
            }
        });
    }
    
    // Handle unit selection change
    function handleUnitChange() {
        const selectedOption = $('#unit_parts option:selected');
        if (!selectedOption.val() || selectedOption.val() === '') {
            $('#costPriceManagement').hide();
            $('#salePriceManagement').hide();
            resetPriceFields();
            return;
        }
        
        $('#costPriceManagement').show();
        $('#salePriceManagement').show();
        
        const unitName = selectedOption.attr('data-unit-name');
        const baseUnitName = selectedOption.attr('data-base-unit-name');
        const multiplier = parseFloat(selectedOption.attr('data-multiplier')) || 1;
        const decimalPlaces = parseInt(selectedOption.attr('data-decimal-places')) || 2;
        
        $('#costUnitLabel').text(`${unitName} COST:`);
        $('#saleUnitLabel').text(`${unitName} SALE:`);
        
        const hasBaseUnit = baseUnitName && baseUnitName !== '' && multiplier && multiplier > 0;
        
        if (hasBaseUnit) {
            $('#costBaseLabel').text(`PER ${baseUnitName} COST:`);
            $('#saleBaseLabel').text(`PER ${baseUnitName} SALE:`);
            $('#baseCostPriceContainer').show();
            $('#baseSalePriceContainer').show();
            $('#costUnitContainer').removeClass('col-12').addClass('col-md-6');
            $('#saleUnitContainer').removeClass('col-12').addClass('col-md-6');
            var baseCost = parseFloat($('#baseCostPrice').val()) || 0;
            var baseSale = parseFloat($('#baseSalePrice').val()) || 0;
            if (baseCost > 0 || baseSale > 0) {
                $('#costPrice').val((baseCost * multiplier).toFixed(decimalPlaces));
                $('#salePrice').val((baseSale * multiplier).toFixed(decimalPlaces));
            }
        } else {
            $('#costBaseLabel').text(`PER ${unitName} COST:`);
            $('#saleBaseLabel').text(`PER ${unitName} SALE:`);
            $('#baseCostPriceContainer').hide();
            $('#baseSalePriceContainer').hide();
            $('#costUnitContainer').removeClass('col-md-6').addClass('col-12');
            $('#saleUnitContainer').removeClass('col-md-6').addClass('col-12');
        }
        
        syncPrices();
    }
    
    // Calculate from unit price
    function calculateFromUnit(type) {
        const selectedOption = $('#unit_parts option:selected');
        if (!selectedOption.val()) return;
        
        const multiplier = parseFloat(selectedOption.attr('data-multiplier')) || 1;
        const baseUnitName = selectedOption.attr('data-base-unit-name');
        const decimalPlaces = parseInt(selectedOption.attr('data-decimal-places')) || 2;
        
        const unitInput = type === 'cost' ? $('#costPrice') : $('#salePrice');
        const baseInput = type === 'cost' ? $('#baseCostPrice') : $('#baseSalePrice');
        const unitVal = parseFloat(unitInput.val()) || 0;
        
        if (baseUnitName && multiplier > 0) {
            const baseVal = (unitVal / multiplier).toFixed(decimalPlaces);
            baseInput.val(baseVal);
        } else {
            baseInput.val(unitVal.toFixed(decimalPlaces));
        }
        
        syncPrices();
    }
    
    // Calculate from base price
    function calculateFromBase(type) {
        const selectedOption = $('#unit_parts option:selected');
        if (!selectedOption.val()) return;
        
        const multiplier = parseFloat(selectedOption.attr('data-multiplier')) || 1;
        const baseUnitName = selectedOption.attr('data-base-unit-name');
        const decimalPlaces = parseInt(selectedOption.attr('data-decimal-places')) || 2;
        
        const unitInput = type === 'cost' ? $('#costPrice') : $('#salePrice');
        const baseInput = type === 'cost' ? $('#baseCostPrice') : $('#baseSalePrice');
        const baseVal = parseFloat(baseInput.val()) || 0;
        
        if (baseUnitName && multiplier > 0) {
            const unitVal = (baseVal * multiplier).toFixed(decimalPlaces);
            unitInput.val(unitVal);
        } else {
            unitInput.val(baseVal.toFixed(decimalPlaces));
        }
        
        syncPrices();
    }
    
    // Sync prices and show analysis
    function syncPrices() {
        const selectedOption = $('#unit_parts option:selected');
        const salePrice = parseFloat($('#salePrice').val()) || 0;
        const costPrice = parseFloat($('#costPrice').val()) || 0;
        const list = $('#derivedPricesList');
        const warning = $('#priceWarning');
        
        list.empty();
        
        // Show warning if sale < cost
        if (salePrice > 0 && costPrice > 0 && salePrice < costPrice) {
            warning.show();
            $('#costPrice, #salePrice, #baseCostPrice, #baseSalePrice').closest('.card').addClass('border-danger');
        } else {
            warning.hide();
            $('#costPrice, #salePrice, #baseCostPrice, #baseSalePrice').closest('.card').removeClass('border-danger');
        }
        
        if (!selectedOption.val() || (salePrice === 0 && costPrice === 0)) return;
        
        const unitName = selectedOption.attr('data-unit-name');
        const baseUnitName = selectedOption.attr('data-base-unit-name');
        const multiplier = parseFloat(selectedOption.attr('data-multiplier')) || 1;
        const decimalPlaces = parseInt(selectedOption.attr('data-decimal-places')) || 2;
        
        const totalProfit = salePrice - costPrice;
        const totalMargin = salePrice > 0 ? ((totalProfit / salePrice) * 100).toFixed(1) : 0;
        const isTotalLoss = totalProfit < 0;
        
        // Full Unit Analysis Card
        list.append(`
            <div class="card border-primary mb-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold">FULL UNIT ANALYSIS: ${unitName}</span>
                    <span class="badge ${isTotalLoss ? 'bg-danger' : 'bg-success'}">${isTotalLoss ? 'LOSS' : 'Margin'}: ${totalMargin}%</span>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <p class="small text-muted mb-1">TOTAL COST</p>
                            <p class="fw-bold text-success h5">Rs. ${costPrice.toFixed(decimalPlaces)}</p>
                        </div>
                        <div class="col-4">
                            <p class="small text-muted mb-1">TOTAL SALE</p>
                            <p class="fw-bold text-warning h5">Rs. ${salePrice.toFixed(decimalPlaces)}</p>
                        </div>
                        <div class="col-4">
                            <p class="small text-muted mb-1">TOTAL PROFIT</p>
                            <p class="fw-bold h5 ${totalProfit >= 0 ? 'text-primary' : 'text-danger'}">Rs. ${totalProfit.toFixed(decimalPlaces)}</p>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        // Base Unit Analysis Card (if base unit exists)
        if (baseUnitName && multiplier > 0) {
            const baseSale = (salePrice / multiplier);
            const baseCost = (costPrice / multiplier);
            const profit = baseSale - baseCost;
            const margin = baseSale > 0 ? ((profit / baseSale) * 100).toFixed(1) : 0;
            const isLoss = profit < 0;
            
            list.append(`
                <div class="card border-secondary mb-3 ${isLoss ? 'border-danger' : ''}">
                    <div class="card-header ${isLoss ? 'bg-danger text-white' : 'bg-secondary text-white'} d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Analysis per ${baseUnitName}</span>
                        <span class="badge ${isLoss ? 'bg-danger' : 'bg-warning text-dark'}">${isLoss ? 'LOSS' : 'Margin'}: ${margin}%</span>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <p class="small text-muted mb-1">COST/${baseUnitName}</p>
                                <p class="fw-bold text-success h5">Rs. ${baseCost.toFixed(decimalPlaces)}</p>
                            </div>
                            <div class="col-4">
                                <p class="small text-muted mb-1">SALE/${baseUnitName}</p>
                                <p class="fw-bold text-warning h5">Rs. ${baseSale.toFixed(decimalPlaces)}</p>
                            </div>
                            <div class="col-4">
                                <p class="small text-muted mb-1">PROFIT/${baseUnitName}</p>
                                <p class="fw-bold h5 ${profit >= 0 ? 'text-primary' : 'text-danger'}">Rs. ${profit.toFixed(decimalPlaces)}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }
    }
    
    function resetPriceFields() {
        $('#costPrice, #baseCostPrice, #salePrice, #baseSalePrice').val('');
        $('#derivedPricesList').empty();
        $('#priceWarning').hide();
    }




</script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productForm', (initialType) => ({
            selectedType: initialType || '',
            init() {
                // Clear localStorage on edit form to use actual item type
                if (initialType) {
                    localStorage.removeItem('selectedType');
                }
                // Keep hidden type input in sync so form always has correct type on submit
                var typeInput = document.querySelector('input[name="type"]');
                if (typeInput) typeInput.value = this.selectedType || '';
                this.$watch('selectedType', (value) => {
                    if (typeInput) typeInput.value = value || '';
                    this.updateFieldsVisibility(value);
                    this.filterProductNameDropdown(value);
                });
                // Trigger initial update
                this.updateFieldsVisibility(this.selectedType);
                // Filter product dropdown on init
                setTimeout(() => {
                    this.filterProductNameDropdown(this.selectedType);
                }, 100);
            },
            selectType(type) {
                this.selectedType = type;
                var typeInput = document.querySelector('input[name="type"]');
                if (typeInput) typeInput.value = type || '';
                this.$nextTick(() => {
                    this.updateFieldsVisibility(type);
                    this.filterProductNameDropdown(type);
                });
            },
            filterProductNameDropdown(type) {
                const productSelect = document.getElementById('name');
                if (!productSelect) return;
                
                const $select = $(productSelect);
                const options = productSelect.querySelectorAll('option');
                const selectedValue = productSelect.value;
                
                options.forEach(option => {
                    if (option.value === '') {
                        // Always show the "Select Product Name" option
                        option.style.display = '';
                        $(option).removeClass('filtered-out').prop('disabled', false);
                        return;
                    }
                    
                    const productType = option.getAttribute('data-type') || '';
                    const $option = $(option);
                    
                    // Show products that match the selected type or have no type
                    // For battery, show products with type 'battery' or empty type
                    let shouldShow = false;
                    if (type === 'battery') {
                        shouldShow = (productType === 'battery' || productType === '');
                    } else if (type === 'parts' || type === 'filters' || type === 'breakpad') {
                        // For parts, filters, breakpad - show matching types or empty
                        shouldShow = (productType === type || productType === '');
                    } else if (type === 'oil' || type === 'scrap') {
                        // For oil and scrap - show matching types or empty
                        shouldShow = (productType === type || productType === '');
                    } else {
                        // For other types, show all
                        shouldShow = true;
                    }
                    
                    if (shouldShow) {
                        option.style.display = '';
                        $option.removeClass('filtered-out').prop('disabled', false);
                    } else {
                        option.style.display = 'none';
                        $option.addClass('filtered-out').prop('disabled', true);
                    }
                });
                
                // If the currently selected option is now hidden, reset to empty
                if (selectedValue && productSelect.querySelector(`option[value="${selectedValue}"]`)?.style.display === 'none') {
                    productSelect.value = '';
                }
                
                // Reinitialize Select2 with templateResult to hide filtered options
                if ($select.hasClass('select2-hidden-accessible')) {
                    try {
                        $select.select2('destroy');
                        $select.removeClass('select2-hidden-accessible');
                        $select.next('.select2-container').remove();
                        
                        setTimeout(() => {
                            $select.select2({
                                placeholder: 'Please Select',
                                allowClear: true,
                                width: '100%',
                                templateResult: function(data) {
                                    // Hide disabled/filtered options in Select2 dropdown
                                    if (data.element && (data.element.disabled || $(data.element).hasClass('filtered-out'))) {
                                        return null;
                                    }
                                    return data.text;
                                },
                                templateSelection: function(data) {
                                    return data.text;
                                }
                            });
                            
                            // Restore selection if it's still valid
                            if (selectedValue && $select.find(`option[value="${selectedValue}"]:not(:hidden):not([disabled]):not(.filtered-out)`).length) {
                                $select.val(selectedValue).trigger('change');
                            }
                        }, 50);
                    } catch(e) {
                        console.error('Error reinitializing Select2:', e);
                    }
                }
            },
            updateFieldsVisibility(type) {
                // Hide all type-specific fields first
                this.$el.querySelectorAll('.field-group').forEach(group => {
                    group.classList.remove('active');
                });
                
                // Common fields always show if type is set
                const commonFields = this.$el.querySelector('.common-fields');
                if (commonFields && type) {
                    commonFields.classList.add('active');
                }
                
                // Type-specific fields
                const typeFields = this.$el.querySelector(`.${type}-fields`);
                if (typeFields) {
                    typeFields.classList.add('active');
                }
                
                // Media and checkboxes
                const mediaFields = this.$el.querySelector('.media-fields');
                const checkboxFields = this.$el.querySelector('.checkbox-fields');
                if (mediaFields && type) {
                    mediaFields.classList.add('active');
                }
                if (checkboxFields && type) {
                    checkboxFields.classList.add('active');
                }
            }
        }));
    });
    $(document).ready(function() {
        feather.replace();
        // Generate random barcode only if empty
        if (!$('#itemBarCode').val().trim()) {
            generateRandomItemCode();
        }
        function generateRandomItemCode() {
            const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let result = '';
            for (let i = 0; i < 10; i++) {
                result += characters.charAt(Math.floor(Math.random() * characters.length));
            }
            $('#itemBarCode').val(result);
        }
        $(document).on('click', '.generate-code-btn', function() {
            generateRandomItemCode();
        });
        // Thumbnail handler
        function initializeThumbnailHandler() {
            const imageInput = $('#imageInput')[0];
            const preview = $('#imagePreview')[0];
            const container = $('#previewContainer')[0];
            const uploadBtn = $('#uploadBtn')[0];
            const removeBtn = $('#removeBtn')[0];
            const defaultImg = "https://pdis.co.kr/img/image.jpg";
            if (!imageInput || !preview || !container || !uploadBtn || !removeBtn) return;
            uploadBtn.onclick = () => imageInput.click();
            imageInput.onchange = function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        preview.src = e.target.result;
                        container.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            };
            removeBtn.onclick = () => {
                preview.src = defaultImg;
                imageInput.value = '';
                let removeInput = $('#mainItemForm input[name="remove_image"]');
                if (removeInput.length === 0) {
                    $('<input>').attr({ type: 'hidden', name: 'remove_image', value: '1' }).appendTo('#mainItemForm');
                } else {
                    removeInput.val('1');
                }
                container.style.display = 'none';
            };
            // For edit: handle existing image removal
            if (container.style.display === 'block' && preview.src !== defaultImg) {
                const originalRemove = removeBtn.onclick;
                removeBtn.onclick = () => {
                    preview.src = defaultImg;
                    let removeInput = $('#mainItemForm input[name="remove_image"]');
                    if (removeInput.length === 0) {
                        $('<input>').attr({ type: 'hidden', name: 'remove_image', value: '1' }).appendTo('#mainItemForm');
                    } else {
                        removeInput.val('1');
                    }
                    container.style.display = 'none';
                };
            }
        }
        // Multiple images handler
        function initializeImagesHandler() {
            const input = $('#imagesInput')[0];
            const preview = $('#imagesPreview')[0];
            const container = $('#imagesPreviewContainer')[0];
            const btn = $('#uploadImagesBtn')[0];
            if (!input || !preview || !container || !btn) return;
            btn.onclick = () => input.click();
            input.onchange = function() {
                preview.innerHTML = '';
                const files = this.files;
                if (files.length > 0) {
                    container.style.display = 'block';
                    Array.from(files).forEach((file, i) => {
                        const reader = new FileReader();
                        reader.onload = e => {
                            const div = document.createElement('div');
                            div.className = 'position-relative';
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'img-fluid rounded border';
                            img.style.maxHeight = '150px';
                            const remove = document.createElement('button');
                            remove.type = 'button';
                            remove.className = 'btn btn-danger btn-sm position-absolute';
                            remove.style.top = '5px';
                            remove.style.right = '5px';
                            remove.innerHTML = '<i data-feather="x"></i>';
                            remove.onclick = () => {
                                div.remove();
                                const dt = new DataTransfer();
                                Array.from(files).filter((_, idx) => idx !== i).forEach(f =>
                                    dt.items.add(f));
                                input.files = dt.files;
                                if (preview.children.length === 0) container.style.display =
                                    'none';
                                feather.replace();
                            };
                            div.appendChild(img);
                            div.appendChild(remove);
                            preview.appendChild(div);
                            feather.replace();
                        };
                        reader.readAsDataURL(file);
                    });
                } else {
                    container.style.display = 'none';
                }
            };
        }
        initializeThumbnailHandler();
        initializeImagesHandler();
        // Universal Modal - Complete implementation matching create.blade.php
        let currentTargetSelect = null;
        let currentEditId = null;
        let deleteRoute = null;
        let lastSearchTerm = {};
        let activeSelectSearch = { selectId: null, searchTerm: '' };

        $(document).on('click', '.open-universal-modal', function () {
            const mode = $(this).data('mode') || 'add';
            const title = $(this).data('title');
            const route = $(this).data('route');
            const hasImage = $(this).data('has-image') === 1;

            currentTargetSelect = $(this).data('target-select');
            
            // =========================
            // CAPTURE SELECTED TYPE FROM ALPINE.JS
            // =========================
            let selectedType = '';
            try {
                // Try to get selectedType from Alpine.js component
                const alpineComponent = Alpine.$data(document.querySelector('[x-data*="productForm"]'));
                if (alpineComponent && alpineComponent.selectedType) {
                    selectedType = alpineComponent.selectedType;
                } else {
                    // Fallback: try to get from hidden input
                    const typeInput = document.querySelector('input[name="type"]');
                    if (typeInput) {
                        selectedType = typeInput.value;
                    }
                }
            } catch (e) {
                console.log('Could not get selectedType from Alpine:', e);
                // Fallback: try to get from hidden input
                const typeInput = document.querySelector('input[name="type"]');
                if (typeInput) {
                    selectedType = typeInput.value;
                }
            }
            
            // Set type in universal form
            if (selectedType && mode === 'add') {
                $('#universal-type').val(selectedType);
            } else {
                $('#universal-type').val('');
            }
            
            // Reset edit state
            currentEditId = null;
            deleteRoute = null;

            // =========================
            // ADD MODE
            // =========================
            if (mode === 'add') {
                $('#universal-modal-title').text(title);
                $('#universal-modal-subtitle').text('Fill in the details below');
                $('#universal-form').attr('action', route);
                $('#universal-form').attr('method', 'POST');
                $('#universal-name').val('').removeClass('is-invalid');
                $('#universal-name-error').text('');
                $('#universal-image').val('');
                $('#universal-image-preview').hide();
                $('#universal-image-placeholder').show();
                $('#universal-delete-btn').addClass('d-none');
                $('#universal-save-btn').text('Save');
                
                // Show/hide image field
                if (hasImage || title === 'Add Category' || title === 'Add Brand') {
                    $('#image-field').removeClass('d-none').show();
                } else {
                    $('#image-field').hide();
                }
                
                // Auto-fill name from dropdown "Add" search term: read directly from clicked button first
                let finalSearchTerm = '';
                const $clickedBtn = $(this).closest('.add-new-dropdown-btn').length ? $(this).closest('.add-new-dropdown-btn') : ($(this).hasClass('add-new-dropdown-btn') ? $(this) : null);
                if ($clickedBtn && $clickedBtn.length) {
                    const $termSpan = $clickedBtn.find('.dropdown-search-term');
                    if ($termSpan.length) {
                        finalSearchTerm = $termSpan.text().trim();
                    }
                    const btnSelectId = $clickedBtn.data('select-id');
                    if (btnSelectId && $('#' + btnSelectId).length) {
                        try { $('#' + btnSelectId).select2('close'); } catch (e) {}
                    }
                }
                if (!finalSearchTerm) {
                    const activeSearch = window.activeSelectSearch || activeSelectSearch;
                    const storedTerms = window.lastSearchTerm || lastSearchTerm;
                    const isFromAddDropdownBtn = $(this).hasClass('add-new-dropdown-btn');
                    const btnSelectId = isFromAddDropdownBtn ? ($(this).data('select-id') || '') : '';
                    let resolvedSelectId = '';
                    if (currentTargetSelect) {
                        const $targetEl = $(currentTargetSelect);
                        resolvedSelectId = ($targetEl.length && $targetEl.attr('id')) ? $targetEl.attr('id') : '';
                    }
                    const selectIdForPrefill = btnSelectId || resolvedSelectId;
                    if (selectIdForPrefill && activeSearch && activeSearch.selectId === selectIdForPrefill && activeSearch.searchTerm) {
                        finalSearchTerm = String(activeSearch.searchTerm).trim();
                    }
                    if (!finalSearchTerm && storedTerms && selectIdForPrefill && storedTerms[selectIdForPrefill]) {
                        finalSearchTerm = String(storedTerms[selectIdForPrefill]).trim();
                    }
                }
                if (finalSearchTerm) {
                    $('#universal-name').val(finalSearchTerm);
                }
                
                // Open modal and focus input
                $('#universal-add-modal').modal('show');
                setTimeout(function() {
                    $('#universal-name').focus();
                }, 300);
            }
            
            // =========================
            // EDIT MODE
            // =========================
            if (mode === 'edit') {
                const $select = $(currentTargetSelect);
                const selectedId = $select.val();
                
                if (!selectedId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Select Item',
                        text: 'Please select an item to edit'
                    });
                    return;
                }
                
                const fetchRoute = $(this).data('fetch-route').replace(':id', selectedId);
                const updateRoute = $(this).data('update-route').replace(':id', selectedId);
                deleteRoute = $(this).data('delete-route').replace(':id', selectedId);
                currentEditId = selectedId;
                
                $('#universal-modal-title').text(title);
                $('#universal-form').attr('action', updateRoute);
                $('#universal-form').attr('method', 'POST');
                $('#universal-save-btn').text('Update');
                $('#universal-delete-btn').removeClass('d-none');
                
                // Show image field if it has image
                if (hasImage || title.includes('Category') || title.includes('Brand')) {
                    $('#image-field').removeClass('d-none').show();
                }
                
                // Fetch existing data
                $.get(fetchRoute, function(res) {
                    $('#universal-name').val(res.name).removeClass('is-invalid');
                    $('#universal-name-error').text('');
                    
                    // Image preview (edit mode)
                    if (hasImage && res.image) {
                        $('#image-field').removeClass('d-none').show();
                        $('#universal-image-preview')
                            .attr('src', '/' + res.image)
                            .show();
                        $('#universal-image-placeholder').hide();
                    } else {
                        $('#universal-image-preview').hide();
                        $('#universal-image-placeholder').show();
                    }
                    
                    $('#universal-add-modal').modal('show');
                    setTimeout(function() {
                        $('#universal-name').focus();
                    }, 300);
                }).fail(function(xhr) {
                    console.error('Error fetching data:', xhr);
                    toastr.error('Failed to load item data. Please try again.');
                });
            }
        });

        // =========================
        // IMAGE LIVE PREVIEW
        // =========================
        $('#universal-image').on('change', function() {
            const file = this.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    toastr.error('Image size should be less than 2MB');
                    $(this).val('');
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#universal-image-preview')
                        .attr('src', e.target.result)
                        .show();
                    $('#universal-image-placeholder').hide();
                };
                reader.readAsDataURL(file);
            } else {
                $('#universal-image-preview').hide();
                $('#universal-image-placeholder').show();
            }
        });
        
        $('#universal-form').off('submit').on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Check if modal is actually visible
            if (!$('#universal-add-modal').hasClass('show')) {
                console.log('Universal modal not visible, ignoring submit');
                return false;
            }
            
            // Check if form has action attribute (should be set when modal opens)
            const formAction = $(this).attr('action');
            if (!formAction || formAction === '' || formAction === '#') {
                console.error('Form action not set');
                return false;
            }
            
            const formData = new FormData(this);
            if (currentEditId) {
                formData.append('_method', 'PUT');
            }
            
            $.ajax({
                url: formAction,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (!res || !res.id) {
                        console.error('Invalid response', res);
                        if (res && res.message) {
                            toastr.error(res.message);
                        }
                        return;
                    }
                    
                    // Check if this is for plates-select, amperes-select, or warranty-select and format accordingly
                    let displayName = res.name;
                    const $select = $(currentTargetSelect);
                    if ($select.hasClass('plates-select') || $select.attr('id') === 'plates_scrap') {
                        // Append "PL" to the name for plates (no space)
                        displayName = res.name + 'PL';
                    } else if ($select.hasClass('amperes-select') || $select.attr('id') === 'amperes_select') {
                        // Append "AH" to the name for amperes (no space)
                        displayName = res.name + 'AH';
                    } else if ($select.hasClass('Warrenty-select') || $select.attr('id') === 'Warrenty_select') {
                        // Format warranty name: "1 YEAR" -> "1 Year" (first word stays, rest words capitalize first letter)
                        displayName = res.name.toLowerCase().replace(/\b\w/g, function(char) {
                            return char.toUpperCase();
                        });
                    }
                    
                    const option = new Option(displayName, res.id, true, true);
                    $select.find(`option[value="${res.id}"]`).remove();
                    $select.append(option).val(res.id).trigger('change');
                    $('#universal-add-modal').modal('hide');
                    $('#universal-form')[0].reset();
                    $('#universal-image-preview').hide();
                    currentEditId = null;
                    deleteRoute = null;
                    // 🔊 Play save sound when item is saved via universal modal
                    if (typeof playSaveSound === 'function') {
                        playSaveSound();
                    }
                },
                error: function(xhr) {
                    console.error('AJAX error', xhr);
                    const response = xhr.responseJSON;
                    if (response && response.errors) {
                        if (response.errors.name) {
                            $('#universal-name').addClass('is-invalid');
                            $('#universal-name-error').text(response.errors.name[0]);
                        }
                        if (response.message) {
                            toastr.error(response.message);
                        }
                    } else if (response && response.message) {
                        toastr.error(response.message);
                    } else {
                        toastr.error('An error occurred. Please try again.');
                    }
                }
            });
        });
        
        // =========================
        // DELETE FUNCTIONALITY WITH AUDIO
        // =========================
        $('#universal-delete-btn').off('click').on('click', function() {
            if (!deleteRoute || !currentEditId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Item Selected',
                    text: 'Please select an item to delete'
                });
                return;
            }
            
            Swal.fire({
                title: 'Are you sure?',
                text: 'This item will be deleted',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteRoute,
                        method: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: $('input[name=_token]').val()
                        },
                        success: function() {
                            // 🔊 Play delete sound ONE time
                            const audio = document.getElementById('deleteSound');
                            if (audio) {
                                audio.currentTime = 0; // reset if previously played
                                audio.play().catch(function(error) {
                                    console.log('Audio play failed:', error);
                                });
                            }
                            
                            // Remove option from select
                            $(currentTargetSelect)
                                .find(`option[value="${currentEditId}"]`)
                                .remove()
                                .trigger('change');
                            
                            // Close modal
                            $('#universal-add-modal').modal('hide');
                            $('#universal-form')[0].reset();
                            $('#universal-image-preview').hide();
                            currentEditId = null;
                            deleteRoute = null;
                            
                            Swal.fire('Deleted!', 'Item deleted successfully', 'success');
                        },
                        error: function(xhr) {
                            console.error('Delete error', xhr);
                            const response = xhr.responseJSON;
                            if (response && response.message) {
                                toastr.error(response.message);
                            } else {
                                toastr.error('Failed to delete item. Please try again.');
                            }
                        }
                    });
                }
            });
        });
        
        // Reset modal on close
        $('#universal-add-modal').on('hidden.bs.modal', function() {
            $('#universal-form')[0].reset();
            $('#universal-name').removeClass('is-invalid');
            $('#universal-name-error').text('');
            $('#universal-image-preview').hide();
            $('#universal-image-placeholder').show();
            $('#universal-delete-btn').addClass('d-none');
            $('#universal-save-btn').text('Save');
            currentEditId = null;
            deleteRoute = null;
        });

        // =========================
        // SERVICE DETAILS MODAL - Add kia kia kaam shamil hai
        // =========================
        function updateServiceDetailsBtnStateEdit() {
            const val = $('#Services_scrap').val();
            const $btn = $('#addServiceDetailsBtn');
            if (val) {
                $btn.prop('disabled', false).attr('title', 'Add details for selected service');
                loadServiceDetailsDisplayEdit(val);
            } else {
                $btn.prop('disabled', true).attr('title', 'Select a service first');
                $('#serviceDetailsDisplay').hide();
            }
        }
        function loadServiceDetailsDisplayEdit(serviceId) {
            if (!serviceId) return;
            $.get('{{ url("show/service") }}/' + serviceId, function(res) {
                const raw = Array.isArray(res.details) ? res.details : [];
                const $box = $('#serviceDetailsDisplay');
                const $list = $('#serviceDetailsDisplayList');
                if (raw.length === 0) {
                    $list.html('<span class="text-muted">No details added yet. Click Add Detail to add.</span>');
                } else {
                    const html = raw.map(function(d, i) {
                        const t = (d.text || d.label || d || '');
                        const p = (d.price !== undefined && d.price !== null && String(d.price).trim() !== '') ? String(d.price).trim() : '';
                        const item = p ? t + ' <span class="badge bg-success ms-1">' + p + '</span>' : t;
                        return '<div class="py-1">' + (i + 1) + '. ' + item + '</div>';
                    }).join('');
                    $list.html(html);
                }
                $box.show();
            }).fail(function() {
                $('#serviceDetailsDisplay').hide();
            });
        }
        updateServiceDetailsBtnStateEdit();
        $(document).on('change select2:select', '#Services_scrap', updateServiceDetailsBtnStateEdit);
        let serviceDetailsTempEdit = [];
        $(document).on('click', '.open-service-details-modal', function() {
            const id = $('#Services_scrap').val();
            const name = $('#Services_scrap option:selected').text();
            if (!id || !name) {
                toastr.warning('Please select a service first');
                return;
            }
            $('#service_details_modal_id').val(id);
            $('#serviceDetailsServiceName').text(name);
            $('#service_detail_new_input').val('');
            $('#service_detail_new_price').val('');
            $.get('{{ url("show/service") }}/' + id, function(res) {
                const raw = Array.isArray(res.details) ? res.details : [];
                serviceDetailsTempEdit = raw.map(function(d) {
                    return typeof d === 'object' && d !== null ? { text: (d.text || d.label || ''), price: (d.price ?? '') } : { text: String(d), price: '' };
                });
                renderServiceDetailsListEdit();
                $('#serviceDetailsModal').modal('show');
                setTimeout(function() { $('#service_detail_new_input').focus(); }, 300);
            });
        });
        function renderServiceDetailsListEdit() {
            const $list = $('#serviceDetailsList');
            const $empty = $('#serviceDetailsEmpty');
            $list.empty();
            if (serviceDetailsTempEdit.length === 0) {
                $empty.show();
            } else {
                $empty.hide();
                serviceDetailsTempEdit.forEach(function(d, i) {
                    const text = (d.text || d.label || d || '');
                    const priceVal = (d.price !== undefined && d.price !== null && String(d.price).trim() !== '') ? String(d.price).trim() : '';
                    const priceHtml = priceVal ? ' <span class="badge bg-success ms-2">' + priceVal + '</span>' : '';
                    $list.append('<li class="list-group-item d-flex justify-content-between align-items-center"><span>' + text + priceHtml + '</span><button type="button" class="btn btn-sm btn-outline-danger remove-service-detail-edit" data-idx="' + i + '"><i data-feather="trash-2" style="width:14px;height:14px;"></i></button></li>');
                });
                if (typeof feather !== 'undefined') feather.replace();
            }
        }
        $(document).on('click', '#serviceDetailAddBtn', function() {
            const v = $('#service_detail_new_input').val().trim();
            if (!v) return;
            const priceVal = $('#service_detail_new_price').val().trim();
            serviceDetailsTempEdit.push({ text: v, price: priceVal ? priceVal : '' });
            $('#service_detail_new_input').val('');
            $('#service_detail_new_price').val('');
            $('#service_detail_new_input').focus();
            renderServiceDetailsListEdit();
        });
        $(document).on('keypress', '#service_detail_new_input', function(e) {
            if (e.which === 13) { e.preventDefault(); $('#serviceDetailAddBtn').click(); }
        });
        $(document).on('click', '.remove-service-detail-edit', function() {
            const i = parseInt($(this).data('idx'), 10);
            serviceDetailsTempEdit.splice(i, 1);
            renderServiceDetailsListEdit();
        });
        $('#serviceDetailsModalSave').on('click', function() {
            const id = $('#service_details_modal_id').val();
            if (!id) return;
            $.ajax({
                url: '{{ url("update/service") }}/' + id,
                method: 'POST',
                data: {
                    _token: $('input[name="_token"]').val(),
                    _method: 'PUT',
                    name: $('#Services_scrap option:selected').text(),
                    details: serviceDetailsTempEdit
                },
                success: function(res) {
                    $('#serviceDetailsModal').modal('hide');
                    loadServiceDetailsDisplayEdit(id);
                    if (typeof playSaveSound === 'function') playSaveSound();
                    toastr.success('Service details saved successfully');
                },
                error: function(xhr) {
                    const res = xhr.responseJSON;
                    toastr.error(res && res.message ? res.message : 'Failed to save');
                }
            });
        });

        // Category image display
        function updateCategoryImageDisplayEdit() {
            const $catSelect = $('#category_parts');
            const catId = $catSelect.val();
            const $opt = $catSelect.find('option:selected');
            const imgSrc = $opt.length ? ($opt.data('image') || $opt.attr('data-image') || '') : '';
            const $box = $('#categoryImageDisplay');
            const $img = $('#categoryImageImg');
            if (imgSrc) {
                $img.attr('src', imgSrc).attr('alt', $opt.text());
                $box.show();
            } else if (catId) {
                $.get('{{ url("show/category") }}/' + catId, function(res) {
                    const raw = res.image ? String(res.image) : '';
                    const src = raw ? (raw.startsWith('http') ? raw : '{{ url("/") }}/' + raw.replace(/^\/+/, '')) : '';
                    if (src) {
                        $opt.attr('data-image', src);
                        $img.attr('src', src).attr('alt', res.name);
                        $box.show();
                    } else {
                        $box.hide();
                    }
                }).fail(function() { $box.hide(); });
            } else {
                $box.hide();
            }
        }
        $(document).on('change', '#category_parts', updateCategoryImageDisplayEdit);
        updateCategoryImageDisplayEdit();

        // Edit button color: blue when select has value, gray when empty
        function updateEditButtonColors() {
            $('.input-group').each(function() {
                var $grp = $(this);
                var $btn = $grp.find('.open-universal-modal[data-target-select]');
                if (!$btn.length) return;
                var targetClass = ($btn.attr('data-target-select') || '').trim();
                if (!targetClass) return;
                var $sel = $grp.find('select' + targetClass);
                if (!$sel.length) return;
                var val = $sel.val();
                if (val === '' || val == null) {
                    $btn.removeClass('btn-primary').addClass('btn-secondary');
                } else {
                    $btn.removeClass('btn-secondary').addClass('btn-primary');
                }
            });
        }
        $(document).on('change', '#mainItemForm select', function() {
            var $sel = $(this);
            var $grp = $sel.closest('.input-group');
            var $btn = $grp.find('.open-universal-modal[data-target-select]');
            if (!$btn.length) return;
            var targetClass = ($btn.attr('data-target-select') || '').trim();
            if (!targetClass) return;
            var $targetSel = $grp.find('select' + targetClass);
            if (!$targetSel.length || $targetSel[0] !== $sel[0]) return;
            var val = $sel.val();
            if (val === '' || val == null) {
                $btn.removeClass('btn-primary').addClass('btn-secondary');
            } else {
                $btn.removeClass('btn-secondary').addClass('btn-primary');
            }
        });
        setTimeout(updateEditButtonColors, 100);
        $(document).ready(updateEditButtonColors);

        // Dynamic Subcategory Load (if applicable)
        $(document).on('change', 'select.category-select', function() {
            const subSelect = $('#subcategory');
            if (subSelect.length === 0) return;
            const catId = $(this).val();
            subSelect.empty().append('<option value="">Select Subcategory</option>');
            if (catId) {
                $.getJSON(`{{ url('admin/categories') }}/${catId}/subcategories`, function(data) {
                    data.forEach(item => {
                        subSelect.append(
                            `<option value="${item.id}">${item.name}</option>`);
                    });
                });
            }
        });
        // Restore subcategory on validation error (if applicable)
        setTimeout(() => {
            const categorySelect = $('#category');
            if (categorySelect.val()) {
                categorySelect.trigger('change');
            }
            setTimeout(() => {
                const subSelect = $('#subcategory');
                const oldVal = subSelect.data('old-subcat');
                if (oldVal && subSelect.find(`option[value="${oldVal}"]`).length) {
                    subSelect.val(oldVal);
                }
            }, 500);
        }, 100);
        // Don't use jQuery fallback - let Alpine.js handle the selection
        // Alpine.js will properly set selectedType from the initial value
        
        // =========================
        // GENERIC "ADD NEW" FUNCTIONALITY FOR ALL DROPDOWNS
        // =========================
        
        // Generic function to check and show "Add New" button for any dropdown
        function checkAndShowAddNewButtonForDropdown(selectId, buttonConfig) {
            const $openSelect2 = $('.select2-container--open');
            if ($openSelect2.length) {
                const $select = $('#' + selectId);
                if (!$select.length) return;
                
                const $selectContainer = $select.next('.select2-container');
                if (!$selectContainer.is($openSelect2)) {
                    return;
                }
                
                const $noResultsMsg = $openSelect2.find('.select2-results__message');
                const $results = $openSelect2.find('.select2-results__option--selectable:not(.select2-results__option--loading)');
                const $searchInput = $openSelect2.find('.select2-search__field');
                const $resultsContainer = $openSelect2.find('.select2-results');
                
                if ($searchInput.length && $searchInput.val() && buttonConfig) {
                    const searchVal = $searchInput.val().trim();
                    const hasNoResults = ($noResultsMsg.length && $noResultsMsg.is(':visible')) || 
                                        ($results.length === 0 && searchVal.length > 0);
                    
                    if (hasNoResults && searchVal.length > 0) {
                        if ($noResultsMsg.length) {
                            $noResultsMsg.hide();
                        }
                        
                        let $addNewBtnInDropdown = $resultsContainer.find('.add-new-dropdown-btn[data-select-id="' + selectId + '"]');
                        
                        if (!$addNewBtnInDropdown.length && $resultsContainer.length) {
                            const buttonHtml = `
                                <div class="select2-results__option select2-results__option--add-new" style="padding: 10px; text-align: center; border-top: 1px solid #ddd;">
                                    <button type="button" class="btn btn-success btn-sm w-100 add-new-dropdown-btn open-universal-modal" 
                                            data-select-id="${selectId}"
                                            data-title="${buttonConfig.title}" 
                                            data-mode="add"
                                            data-route="${buttonConfig.route}"
                                            data-target-select="${buttonConfig.targetSelect}"
                                            ${buttonConfig.hasImage ? 'data-has-image="1"' : ''}
                                            style="background: #f97316; border: none; box-shadow: 0 4px 14px 0 rgba(249, 115, 22, 0.2);">
                                        <i data-feather="plus" class="feather-plus me-1"></i>
                                        Add "<span class="dropdown-search-term fw-bold">${searchVal}</span>"
                                    </button>
                                </div>
                            `;
                            $resultsContainer.append(buttonHtml);
                            
                            if (typeof feather !== 'undefined') {
                                feather.replace();
                            }
                            
                            $resultsContainer.find('.dropdown-search-term').text(searchVal);
                        } else if ($addNewBtnInDropdown.length) {
                            $resultsContainer.find('.dropdown-search-term').text(searchVal);
                        }
                    } else if ($results.length > 0) {
                        $resultsContainer.find('.add-new-dropdown-btn[data-select-id="' + selectId + '"]').closest('.select2-results__option--add-new').remove();
                        if ($noResultsMsg.length) {
                            $noResultsMsg.show();
                        }
                    }
                } else {
                    $openSelect2.find('.select2-results').find('.add-new-dropdown-btn[data-select-id="' + selectId + '"]').closest('.select2-results__option--add-new').remove();
                    if ($noResultsMsg.length) {
                        $noResultsMsg.show();
                    }
                }
            }
        }
        
        // Auto focus and Add New button for dropdowns (includes vehicle modal dropdowns)
        $(document).on('select2:opening', '#name, #category, #company_parts, #quality, #quality_filters, #quality_breakpad, #technology_select, #grade_select, #mileage_oil, #Level_select, #plates_scrap, #amperes_select, #volt_select, #cca_select, #minus_pole_direction_select, #Warrenty_select, #made_in_select, #Services_scrap, #itemVehicleMakeEdit, #itemVehicleModelEdit, #itemVehicleYearFromEdit, #itemVehicleYearToEdit, #itemVehicleEngineEdit, #itemVehicleCountryEdit', function(e) {
            const selectId = $(this).attr('id');
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    const $searchInput = $('#' + selectId).next('.select2-container').find('.select2-search__field');
                    if ($searchInput.length) {
                        $searchInput[0].focus();
                        $searchInput[0].select();
                    }
                });
            });
        });
        
        $(document).on('select2:open', '#name, #part_number_id, #category, #company_parts, #quality, #quality_filters, #quality_breakpad, #technology_select, #grade_select, #mileage_oil, #Level_select, #plates_scrap, #amperes_select, #volt_select, #cca_select, #minus_pole_direction_select, #Warrenty_select, #made_in_select, #Services_scrap, #itemVehicleMakeEdit, #itemVehicleModelEdit, #itemVehicleYearFromEdit, #itemVehicleYearToEdit, #itemVehicleEngineEdit, #itemVehicleCountryEdit', function(e) {
            const $select = $(this);
            const selectId = $select.attr('id');
            if (!selectId) return;
            
            function focusSearchInput() {
                // Prefer the currently open dropdown's search field (works for modal/body-rendered dropdowns)
                var $searchInput = $('.select2-container--open .select2-search__field');
                if (!$searchInput.length) {
                    var $container = $select.next('.select2-container');
                    if ($container.length) $searchInput = $container.find('.select2-search__field');
                }
                if ($searchInput.length && $searchInput[0]) {
                    $searchInput[0].focus();
                    try { $searchInput[0].select(); } catch (e) {}
                    return true;
                }
                return false;
            }
            
            // Auto focus for all dropdowns (including vehicle modal)
            const focusSelectIds = 'name,part_number_id,category,company_parts,quality,quality_filters,quality_breakpad,technology_select,grade_select,mileage_oil,Level_select,plates_scrap,amperes_select,volt_select,cca_select,minus_pole_direction_select,Warrenty_select,made_in_select,Services_scrap,itemVehicleMakeEdit,itemVehicleModelEdit,itemVehicleYearFromEdit,itemVehicleYearToEdit,itemVehicleEngineEdit,itemVehicleCountryEdit';
            if (focusSelectIds.split(',').indexOf(selectId) !== -1) {
                requestAnimationFrame(focusSearchInput);
                setTimeout(focusSearchInput, 0);
                setTimeout(focusSearchInput, 30);
                setTimeout(focusSearchInput, 80);
                setTimeout(focusSearchInput, 150);
            }
            
            let buttonConfig = null;
            const $addButton = $select.closest('.input-group').find('.open-universal-modal[data-mode="add"]');
            if ($addButton.length) {
                buttonConfig = {
                    title: $addButton.data('title') || 'Add New',
                    route: $addButton.data('route') || '',
                    targetSelect: $addButton.data('target-select') || '',
                    hasImage: $addButton.data('has-image') == 1
                };
            } else {
                const configMap = {
                    'name': {
                        title: 'Add Product',
                        route: '{{ route("post.product") }}',
                        targetSelect: '.name-select'
                    },
                    'part_number_id': {
                        title: 'Add Part Number',
                        route: '{{ route("post.partnumber") }}',
                        targetSelect: '.part_number-select'
                    },
                    'category': {
                        title: 'Add Category',
                        route: '{{ route("post.item.category") }}',
                        targetSelect: '.category-select',
                        hasImage: true
                    },
                    'company_parts': {
                        title: 'Add Company',
                        route: '{{ route("post.companies") }}',
                        targetSelect: '.company-select'
                    },
                    'quality': {
                        title: 'Add Quality',
                        route: '{{ route("post.qualities") }}',
                        targetSelect: '.quality-select'
                    },
                    'quality_filters': {
                        title: 'Add Quality',
                        route: '{{ route("post.qualities") }}',
                        targetSelect: '.quality-select'
                    },
                    'quality_breakpad': {
                        title: 'Add Quality',
                        route: '{{ route("post.qualities") }}',
                        targetSelect: '.quality-select'
                    },
                    'technology_select': {
                        title: 'Add Technology',
                        route: '{{ route("post.technology") }}',
                        targetSelect: '.technology-select'
                    },
                    'grade_select': {
                        title: 'Add Grade',
                        route: '{{ route("post.grade") }}',
                        targetSelect: '.grade-select'
                    },
                    'mileage_oil': {
                        title: 'Add Mileage',
                        route: '{{ route("post.item.mileage") }}',
                        targetSelect: '.mileage-select'
                    },
                    'Level_select': {
                        title: 'Add Level',
                        route: '{{ route("post.levels") }}',
                        targetSelect: '.level-select'
                    },
                    'plates_scrap': {
                        title: 'Add Plate',
                        route: '{{ route("post.platos") }}',
                        targetSelect: '.plates-select'
                    },
                    'amperes_select': {
                        title: 'Add Amperes',
                        route: '{{ route("post.amphors") }}',
                        targetSelect: '.amperes-select'
                    },
                    'volt_select': {
                        title: 'Add Volt',
                        route: '{{ route("post.volts") }}',
                        targetSelect: '.volt-select'
                    },
                    'cca_select': {
                        title: 'Add CCA',
                        route: '{{ route("post.cca") }}',
                        targetSelect: '.cca-select'
                    },
                    'minus_pole_direction_select': {
                        title: 'Add Minus Pole Direction',
                        route: '{{ route("post.minuspool") }}',
                        targetSelect: '.minus-pole-direction-select'
                    },
                    'Warrenty_select': {
                        title: 'Add Warrenty',
                        route: '{{ route("post.warrenty") }}',
                        targetSelect: '.Warrenty-select'
                    },
                    'made_in_select': {
                        title: 'Add Made In',
                        route: '{{ route("post.made_ins") }}',
                        targetSelect: '.made_in-select'
                    },
                    'Services_scrap': {
                        title: 'Add Services',
                        route: '{{ route("post.services") }}',
                        targetSelect: '.Services-select'
                    },
                    'itemVehicleMakeEdit': {
                        title: 'Add Make',
                        route: '{{ route("post.car.manufacturer") }}',
                        targetSelect: '.item-vehicle-manufacturer-edit'
                    },
                    'itemVehicleModelEdit': {
                        title: 'Add Model',
                        route: '{{ route("post.car.model") }}',
                        targetSelect: '.item-vehicle-model-edit'
                    },
                    'itemVehicleEngineEdit': {
                        title: 'Add Engine CC',
                        route: '{{ route("post.engine.cc") }}',
                        targetSelect: '.item-vehicle-engine-edit'
                    },
                    'itemVehicleCountryEdit': {
                        title: 'Add Country',
                        route: '{{ route("post.car.country") }}',
                        targetSelect: '.item-vehicle-country-edit'
                    }
                };
                
                if (configMap[selectId]) {
                    buttonConfig = configMap[selectId];
                }
            }
            
            if (!buttonConfig) return;
            
            setTimeout(function() {
                const $searchInput = $('.select2-container--open .select2-search__field');
                if ($searchInput.length) {
                    $searchInput.off('input.dropdownSearch').on('input.dropdownSearch', function() {
                        setTimeout(function() {
                            checkAndShowAddNewButtonForDropdown(selectId, buttonConfig);
                        }, 300);
                    });
                    
                    $searchInput.off('keydown.dropdownEnter').on('keydown.dropdownEnter', function(e) {
                        if (e.key === 'Enter' || e.keyCode === 13) {
                            e.preventDefault();
                            e.stopPropagation();
                            const $addNewBtn = $('.select2-container--open .add-new-dropdown-btn[data-select-id="' + selectId + '"]');
                            if ($addNewBtn.length) {
                                $addNewBtn.trigger('mousedown');
                                $addNewBtn.trigger('click');
                                return false;
                            }
                        }
                    });
                    
                    let checkNoResultsInterval = setInterval(function() {
                        const $openSelect2 = $('.select2-container--open');
                        if ($openSelect2.length) {
                            checkAndShowAddNewButtonForDropdown(selectId, buttonConfig);
                        } else {
                            clearInterval(checkNoResultsInterval);
                        }
                    }, 200);
                    
                    $(document).one('select2:close', '#' + selectId, function() {
                        clearInterval(checkNoResultsInterval);
                        $searchInput.off('keydown.dropdownEnter');
                        const $selectContainer = $('#' + selectId).next('.select2-container');
                        if ($selectContainer.length) {
                            $selectContainer.find('.select2-results').find('.add-new-dropdown-btn[data-select-id="' + selectId + '"]').closest('.select2-results__option--add-new').remove();
                            const $noResultsMsg = $selectContainer.find('.select2-results__message');
                            if ($noResultsMsg.length) {
                                $noResultsMsg.show();
                            }
                        }
                    });
                }
            }, 100);
        });
        
        $(document).on('mousedown touchstart', '.add-new-dropdown-btn', function(e) {
            const $button = $(this);
            const $searchTermSpan = $button.find('.dropdown-search-term');
            const selectId = $button.data('select-id');
            
            if ($searchTermSpan.length && selectId) {
                const searchText = $searchTermSpan.text().trim();
                if (searchText) {
                    if (!window.lastSearchTerm) {
                        window.lastSearchTerm = {};
                    }
                    window.lastSearchTerm[selectId] = searchText;
                    window.activeSelectSearch = {
                        selectId: selectId,
                        searchTerm: searchText,
                        hasNoResults: true
                    };
                }
            }
            // Do NOT close dropdown here - otherwise click may not fire and modal won't open. Close in modal handler.
        });
    });
</script>
<script>
    // Update Alpine.js productForm to include type-based filtering
    document.addEventListener('alpine:init', () => {
        // Check if productForm already exists
        if (typeof Alpine !== 'undefined' && Alpine.data('productForm')) {
            // Extend existing productForm
            const originalForm = Alpine.data('productForm');
            Alpine.data('productForm', (initialType) => {
                const instance = originalForm(initialType);
                // Override selectType to ensure only one type is selected
                const originalSelectType = instance.selectType;
                instance.selectType = function(type) {
                    // Ensure only one type is selected at a time
                    this.selectedType = type;
                    // Call original function for filtering
                    if (originalSelectType) {
                    originalSelectType.call(this, type);
                    }
                    // Load items by type when type changes
                    if (type) {
                        loadItemsByType(type, false);
                    } else {
                        loadAllItems();
                    }
                };
                return instance;
            });
        } else {
            // Create new productForm if it doesn't exist (should not happen in edit form)
            Alpine.data('productForm', (initialType) => ({
                selectedType: initialType || '', // Don't use localStorage in edit form
                selectType(type) {
                    this.selectedType = type;
                    // Load items by type when type changes
                    if (type) {
                        loadItemsByType(type, false);
                    } else {
                        loadAllItems();
                    }
                }
            }));
        }
    });

    // Function to load items by type (with limit option)
    function loadItemsByType(type, loadAll = false) {
        const routeBase = '{{ route("items.by.type", ":type") }}';
        let routeUrl = routeBase.replace(':type', type);
        
        // Add 'all' parameter if loading all items
        if (loadAll) {
            routeUrl += '?all=true';
        }
        
        $.ajax({
            url: routeUrl,
            type: 'GET',
            success: function(response) {
                if (response.success && response.items) {
                    updateItemsTable(response.items);
                    // Update table title and button visibility
                    const typeNames = {
                        'parts': 'Parts',
                        'battery': 'Battery',
                        'oil': 'Oil',
                        'scrap': 'Scrap',
                        'services': 'Services',
                        'filters': 'Filters',
                        'breakpad': 'Break Pad'
                    };
                    
                    if (loadAll) {
                        // Show all items
                        $('#itemsTableTitle').text(`All ${typeNames[type] || type.toUpperCase()} Items (${response.total || response.items.length})`);
                        $('#allItemsButtonContainer').hide();
                    } else {
                        // Show last 5 items
                        $('#itemsTableTitle').text(`Last 5 ${typeNames[type] || type.toUpperCase()} Items`);
                        // Show "All Items" button if there are 5 items (might be more)
                        if (response.items.length >= 5) {
                            $('#allItemsButtonContainer').show();
                            // Re-initialize feather icons for the button
                            if (typeof feather !== 'undefined') {
                                feather.replace();
                            }
                        } else {
                            $('#allItemsButtonContainer').hide();
                        }
                    }
                }
            },
            error: function(xhr) {
                console.error('Error loading items:', xhr);
                // Fallback: show all items
                loadAllItems();
            }
        });
    }

    // Function to load all items of selected type
    function loadAllItemsByType() {
        const savedType = localStorage.getItem('selectedType') || '{{ $item->type ?? old("type") ?? "" }}';
        if (savedType) {
            loadItemsByType(savedType, true);
        }
    }

    // Function to load all items (initial load)
    function loadAllItems() {
        $('#itemsTableTitle').text('Last 5 Created Items');
        $('#allItemsButtonContainer').hide();
        // Table is already populated by server, no need to reload
    }

    // Handle "All Items" button click
    $(document).on('click', '#loadAllItemsBtn', function() {
        loadAllItemsByType();
    });

    // Function to update the items table
    function updateItemsTable(items) {
        const tbody = $('#latestItemsTableBody');
        tbody.empty();

        if (items.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="5" class="text-center">No items found for this type.</td>
                </tr>
            `);
            return;
        }

        items.forEach(function(item) {
            const csrfToken = $('input[name="_token"]').val();
            
            // Build item details HTML - Product Name first, then Part Number
            const productName = item.product_name && item.product_name !== '-' ? item.product_name : (item.part_number || '-');
            const partNumberLine = (item.product_name && item.product_name !== '-') ? `<div class="text-muted">${item.part_number || '-'}</div>` : '';
            const itemDetails = `
                <div class="small">
                    <div class="fw-semibold">${productName}</div>
                    ${partNumberLine}
                    <div>${item.category_name || '-'}</div>
                    <div>${item.company_name || '-'}</div>
                    <div>${item.quality_name || '-'}</div>
                </div>
            `;

            const row = `
                <tr>
                    <td>
                        <img src="${item.image}" width="70" height="70"
                            class="rounded item-image" style="cursor:pointer;" data-bs-toggle="modal"
                            data-bs-target="#imageModal"
                            data-src="${item.image}">
                    </td>
                    <td>
                        ${itemDetails}
                    </td>
                    <td>
                        <div>${item.user_name}</div>
                        <div class="small text-muted">${item.branch_name || '-'}</div>
                    </td>
                    <td>
                        <a class="btn btn-sm btn-outline-primary" href="${item.show_url}">
                            <i data-feather="eye" class="me-1"></i> View
                        </a>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="${item.edit_url}">
                                        <i data-feather="edit" class="me-1"></i> Edit
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" onclick="confirmDelete('delete-form-${item.id}')"
                                        class="dropdown-item">
                                        <i data-feather="trash-2" class="feather-trash-2"></i> Delete
                                    </a>
                                    <form id="delete-form-${item.id}"
                                        action="${item.delete_url}" method="POST"
                                        style="display: none;">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <input type="hidden" name="_method" value="DELETE">
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        // Re-initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }

    // Load items by type on page load if type is already selected
    $(document).ready(function() {
        const savedType = localStorage.getItem('selectedType') || '{{ $item->type ?? old("type") ?? "" }}';
        if (savedType) {
            // Wait a bit for Alpine.js to initialize
            setTimeout(function() {
                loadItemsByType(savedType, false);
            }, 300);
        } else {
            // Hide button if no type is selected
            $('#allItemsButtonContainer').hide();
        }
    });

    // Unlock save audio on Update button click (same as item create - so audio can play after AJAX success)
    $('#mainItemForm').on('click', 'button[type=submit]', function() {
        var s = document.getElementById('saveSound');
        if (s && typeof s.play === 'function') {
            var v = s.volume;
            s.volume = 0;
            s.currentTime = 0;
            s.play().then(function() { s.pause(); s.currentTime = 0; s.volume = v; }).catch(function(){ s.volume = v; });
        }
    });

    // =========================
    // SUBMIT VIA AJAX - show success toast + save audio (same as item create)
    // =========================
    $('#mainItemForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);

        // ========== SYNC hidden fields (same as before) ==========
        var shortDiscVal = ($form.find('input[name="short_disc"]').val() || '').trim();
        var proDisVal = '';
        var $summernote = $('#summernote');
        if ($summernote.length && $summernote.data('summernote')) {
            try { proDisVal = $summernote.summernote('code') || ''; $summernote.val(proDisVal); } catch (err) { console.warn('Summernote sync:', err); }
        } else {
            proDisVal = $summernote.val() || '';
        }
        $form.find('input[name="short_disc"]').remove();
        $form.find('textarea[name="pro_dis"]').remove();
        $form.append($('<input type="hidden" name="short_disc">').val(shortDiscVal));
        $form.append($('<input type="hidden" name="pro_dis">').val(proDisVal));

        var $unitSelect = $('#unit_parts');
        if ($unitSelect.length && $unitSelect.hasClass('select2-hidden-accessible') && typeof $unitSelect.select2 === 'function') {
            try { $unitSelect.select2('close'); } catch (err) {}
        }
        var runSubmit = function() {
            var rawVal = null;
            if ($unitSelect.length) {
                if ($unitSelect.hasClass('select2-hidden-accessible') && typeof $unitSelect.select2 === 'function') {
                    try {
                        var sel2Data = $unitSelect.select2('data');
                        if (sel2Data && sel2Data[0] && sel2Data[0].id !== undefined && sel2Data[0].id !== null) {
                            rawVal = String(sel2Data[0].id);
                        }
                    } catch (err) {}
                }
                if (rawVal === undefined || rawVal === null || rawVal === '') {
                    rawVal = $unitSelect.val();
                    if (rawVal !== undefined && rawVal !== null) rawVal = String(rawVal);
                }
            }
            var unitValueToSend = (rawVal !== undefined && rawVal !== null && rawVal !== '') ? String(rawVal).trim() : '';
            $form.find('input[type="hidden"][name="unit"]').remove();
            $unitSelect.removeAttr('name');
            if (unitValueToSend) {
                var $hu = $('<input type="hidden" name="unit">').attr('value', unitValueToSend);
                $form.append($hu);
            }

        var qualityVal = $('#quality').val() || $('#quality_filters').val() || $('#quality_breakpad').val() || '';
        $form.find('input[type="hidden"][name="quality_id"]').remove();
        if (qualityVal) $form.append('<input type="hidden" name="quality_id" value="' + qualityVal + '">');

        var techVal = $('#technology_select').val() || $('#technology_oil_select').val() || '';
        $form.find('input[type="hidden"][name="technology"]').remove();
        if (techVal) $form.append('<input type="hidden" name="technology" value="' + techVal + '">');

        var formData = new FormData($form[0]);
        formData.set('_method', 'PUT');
        if (unitValueToSend) {
            formData.set('unit', unitValueToSend);
            if (unitValueToSend.indexOf('_') !== -1) {
                formData.set('unit_option', unitValueToSend);
            }
        }
        // Ensure Unit Management & Price Calculation fields are always sent (so edit saves unit + prices)
        var totalPrice = $form.find('input[name="total_price"]').val();
        var pricePerUnit = $form.find('input[name="price_per_unit"]').val();
        var salePrice = $form.find('input[name="sale_price"]').val();
        var salePricePerBase = $form.find('input[name="sale_price_per_base"]').val();
        formData.set('total_price', totalPrice !== undefined && totalPrice !== null && totalPrice !== '' ? totalPrice : '');
        formData.set('price_per_unit', pricePerUnit !== undefined && pricePerUnit !== null && pricePerUnit !== '' ? pricePerUnit : '');
        formData.set('sale_price', salePrice !== undefined && salePrice !== null && salePrice !== '' ? salePrice : '');
        formData.set('sale_price_per_base', salePricePerBase !== undefined && salePricePerBase !== null && salePricePerBase !== '' ? salePricePerBase : '');
        // Sync common Item Info and all Select2 fields into FormData so changes are saved
        var barCode = $('#itemBarCode').val();
        if (barCode !== undefined && barCode !== null) formData.set('bar_code', barCode);
        var pId = $('#name').val();
        if (pId !== undefined && pId !== null) formData.set('p_id', pId);
        var partNumberId = $('#part_number_id').val();
        if (partNumberId !== undefined && partNumberId !== null) formData.set('part_number_id', partNumberId);
        var categoryId = $('#category_parts').val();
        if (categoryId !== undefined && categoryId !== null) formData.set('category_id', categoryId);
        var companyId = $('#company_parts').length ? $('#company_parts').val() : ($('#company_filters').length ? $('#company_filters').val() : ($('#company_breakpad').length ? $('#company_breakpad').val() : ''));
        if (companyId !== undefined && companyId !== null && companyId !== '') formData.set('company_id', companyId);
        $form.find('select.select2-hidden-accessible').each(function() {
            var $sel = $(this);
            var name = $sel.attr('name');
            if (name && name !== 'unit') {
                var v = $sel.val();
                if (v !== undefined && v !== null) formData.set(name, v);
            }
        });
        var $submitBtn = $form.find('button[type=submit]');
        var origHtml = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            success: function(data) {
                if (data.success && data.message) {
                    if (typeof toastr !== 'undefined') toastr.success(data.message, '', { timeOut: 3500 });
                    if (typeof playSaveSound === 'function') playSaveSound();
                }
                // If inside iframe (e.g. add-new-item-modal), tell parent to show add-item-modal
                if (window.self !== window.top) {
                    var itemId = (data && (data.item_id || data.id)) || (function() {
                        var m = $form.attr('action').match(/\/update\/(\d+)/);
                        return m ? m[1] : null;
                    })();
                    if (itemId) {
                        try { window.parent.postMessage({ type: 'ITEM_UPDATED', itemId: String(itemId) }, '*'); } catch (e) {}
                    }
                    setTimeout(function() { window.location.reload(); }, 450);
                } else {
                    // If opened with return_to (e.g. from purchases create Edit button), redirect there and open add-item-modal
                    var params = new URLSearchParams(window.location.search);
                    var returnTo = params.get('return_to');
                    if (returnTo) {
                        returnTo += (returnTo.indexOf('?') !== -1 ? '&' : '?') + 'open_add_item=1';
                        window.location.href = returnTo;
                    } else {
                        setTimeout(function() { window.location.reload(); }, 450);
                    }
                }
            },
            error: function(xhr) {
                $form.find('button[type=submit]').prop('disabled', false).html(origHtml);
                var res = xhr.responseJSON;
                var msg = (res && res.message) ? res.message : 'Failed to update item. Please try again.';
                if (res && res.errors && typeof res.errors === 'object') {
                    msg = Object.values(res.errors).flat().join(' ') || msg;
                }
                if (typeof toastr !== 'undefined') toastr.error(msg);
            }
        });
        };
        setTimeout(runSubmit, 0);
    });
</script>
<script>
    // Common function to filter vehicle table
    function filterVehicleTable() {
        // Get filter values from table headers
        let selectedManufacturer = $('#vehicle_manufacturer_select').val() || $('.car-manufacturer-select').val();
        let selectedModel = $('#vehicle_model_select').val() || $('.car-model-select').val();
        let selectedEngine = $('#vehicle_engine_select').val() || $('.car-engine-select').val();
        let selectedCountry = $('#vehicle_country_select').val() || $('.car-country-select').val();
        let selectedPartNumber = $('#part_number_id').val() || $('#part_number').val();
        
        // Get selected year ranges
        let selectedYearRanges = $('#selectedYearRangesDisplay').data('all-ranges') || [];
        
        // Check if any filter is selected
        const hasFilters = selectedManufacturer || selectedModel || selectedEngine || selectedCountry || selectedPartNumber || selectedYearRanges.length > 0;
        
        // Hide all rows first only if filters are applied
        if (hasFilters) {
            $("#vehicleTable tbody tr").hide();
        } else {
            // If no filters, show all rows
            $("#vehicleTable tbody tr").show();
            return;
        }
        
        // Filter rows based on selected criteria
        $("#vehicleTable tbody tr").each(function() {
            let $row = $(this);
            let shouldShow = true;
            
            // Check Manufacturer
            if (selectedManufacturer) {
                let rowManufacturer = $row.find('.editVehicleBtn').data('manufacturer');
                if (rowManufacturer != selectedManufacturer) {
                    shouldShow = false;
                }
            }
            
            // Check Model
            if (shouldShow && selectedModel) {
                let rowModel = $row.find('.editVehicleBtn').data('model');
                if (rowModel != selectedModel) {
                    shouldShow = false;
                }
            }
            
            // Check Engine
            if (shouldShow && selectedEngine) {
                let rowEngine = $row.find('.editVehicleBtn').data('engine');
                if (rowEngine != selectedEngine) {
                    shouldShow = false;
                }
            }
            
            // Check Country
            if (shouldShow && selectedCountry) {
                let rowCountry = $row.find('.editVehicleBtn').data('country');
                if (rowCountry != selectedCountry) {
                    shouldShow = false;
                }
            }
            
            // Check Part Number
            if (shouldShow && selectedPartNumber) {
                let rowPartNumber = $row.data('part') || $row.find('.editVehicleBtn').data('part');
                if (rowPartNumber != selectedPartNumber) {
                    shouldShow = false;
                }
            }
            
            // Check Year Ranges
            if (shouldShow && selectedYearRanges.length > 0) {
                let yearCell = $row.find('td:nth-child(5)');
                let yearText = yearCell.text();
                let yearMatch = false;
                
                // Extract year ranges from table cell
                let tableRanges = [];
                if (yearText.includes('-')) {
                    let rangeMatches = yearText.match(/\d{4}(-\d{4})?/g) || [];
                    rangeMatches.forEach(function(range) {
                        let rangeParts = range.split('-');
                        let rangeFrom = parseInt(rangeParts[0]);
                        let rangeTo = rangeParts[1] ? parseInt(rangeParts[1]) : rangeFrom;
                        tableRanges.push({ from: rangeFrom, to: rangeTo });
                    });
                } else {
                    let yearMatch = yearText.match(/\d{4}/);
                    if (yearMatch) {
                        let year = parseInt(yearMatch[0]);
                        tableRanges.push({ from: year, to: year });
                    }
                }
                
                // Check if any selected range overlaps with table ranges
                selectedYearRanges.forEach(function(selectedRange) {
                    let rangeParts = selectedRange.split('-');
                    let selectedFrom = parseInt(rangeParts[0]);
                    let selectedTo = rangeParts.length > 1 ? parseInt(rangeParts[1]) : selectedFrom;
                    
                    tableRanges.forEach(function(tableRange) {
                        if (tableRange.from <= selectedTo && tableRange.to >= selectedFrom) {
                            yearMatch = true;
                        }
                    });
                });
                
                if (!yearMatch) {
                    shouldShow = false;
                }
            }
            
            // Show row if it matches all criteria
            if (shouldShow) {
                $row.show();
            }
        });
    }

    // Clear All Vehicle Filters Button
    $(document).on('click', '.clearVehicleFiltersBtn', function() {
        // Clear all Select2 dropdowns
        $('#vehicle_manufacturer_select').val('').trigger('change');
        $('#vehicle_model_select').val('').trigger('change');
        $('#vehicle_country_select').val('').trigger('change');
        $('#vehicle_engine_select').val('').trigger('change');
        
        // Clear part number if exists
        if ($('#part_number_id').length) {
            $('#part_number_id').val('').trigger('change');
        }
        if ($('#part_number').length) {
            $('#part_number').val('').trigger('change');
        }
        
        // Clear year ranges
        $('#selectedYearRangesDisplay').data('all-ranges', []);
        $('#selectedYearRangesDisplay').attr('data-show-all', 'false');
        $('#selectedYearRangesDisplay').html('<div class="text-muted text-center" style="font-size: 10px;">No ranges selected</div>');
        
        // Show all rows in table
        filterVehicleTable();
        
        toastr.success('All filters cleared successfully.');
    });

    // Year Range Modal - open same as Create Item
    $(document).on('click', '.open-year-range-modal', function() {
        $('#yearRangeModal').modal('show');
    });

    function updateYearRangeDisplay() {
        let displayBox = $('#selectedYearRangesDisplay');
        let allRanges = displayBox.data('all-ranges') || [];
        let showAll = displayBox.attr('data-show-all') === 'true';
        if (allRanges.length === 0) {
            displayBox.html('<div class="text-muted text-center" style="font-size: 10px;">No ranges selected</div>');
            return;
        }
        let rangesToShow = showAll ? allRanges : allRanges.slice(0, 2);
        let html = '<div style="display: flex; flex-wrap: wrap; gap: 4px; align-items: center;">';
        rangesToShow.forEach(function(range) {
            html += '<span class="badge" style="background-color: #7DD3FC; color: #0C4A6E; padding: 4px 8px; border-radius: 4px; font-size: 10px;">' + range + '</span>';
        });
        if (!showAll && allRanges.length > 2) {
            html += '<span class="badge bg-secondary" style="padding: 4px 8px; border-radius: 4px; font-size: 10px; cursor: pointer;">+' + (allRanges.length - 2) + ' more</span>';
        }
        html += '</div>';
        displayBox.html(html);
    }

    $(document).on('click', '#selectedYearRangesDisplay', function(e) {
        let displayBox = $(this);
        let allRanges = displayBox.data('all-ranges') || [];
        if (allRanges.length > 2) {
            let showAll = displayBox.attr('data-show-all') === 'true';
            displayBox.attr('data-show-all', showAll ? 'false' : 'true');
            updateYearRangeDisplay();
        }
    });

    function updateFilterYearRangeRemoveButtons() {
        let ranges = $('#filterYearRangesContainer .filter-year-range-item');
        ranges.each(function(index) {
            let removeBtn = $(this).find('.removeFilterYearRange');
            if (ranges.length > 1) removeBtn.show(); else removeBtn.hide();
        });
    }

    $(document).on('click', '#addMoreFilterYearRange', function() {
        let container = $('#filterYearRangesContainer');
        let newRange = $('<div class="filter-year-range-item mb-2"></div>');
        let yearOptions = '';
        let currentYear = new Date().getFullYear();
        for (let year = currentYear; year >= 1980; year--) yearOptions += '<option value="' + year + '">' + year + '</option>';
        newRange.html('<div class="row g-2"><div class="col-5"><label class="form-label small">From Year</label><select class="form-control filter-year-from"><option value="">Select Year</option>' + yearOptions + '</select><div class="invalid-feedback" style="display:none;font-size:10px;">From Year cannot be greater than To Year</div></div><div class="col-5"><label class="form-label small">To Year</label><select class="form-control filter-year-to"><option value="">Select Year</option>' + yearOptions + '</select><div class="invalid-feedback" style="display:none;font-size:10px;">To Year cannot be less than From Year</div></div><div class="col-2 d-flex align-items-end"><button type="button" class="btn btn-danger btn-sm removeFilterYearRange">X</button></div></div></div>');
        container.append(newRange);
        updateFilterYearRangeRemoveButtons();
        attachYearRangeValidation(newRange);
        if (typeof feather !== 'undefined') feather.replace();
    });

    function attachYearRangeValidation($container) {
        var $fromSelect = $container.find('.filter-year-from');
        var $toSelect = $container.find('.filter-year-to');
        var $fromError = $fromSelect.next('.invalid-feedback');
        var $toError = $toSelect.next('.invalid-feedback');
        $fromSelect.off('change').on('change', function() {
            var fromYear = $(this).val(), toYear = $toSelect.val();
            if (fromYear && toYear && parseInt(fromYear) > parseInt(toYear)) {
                $(this).addClass('is-invalid'); $fromError.show(); $toSelect.addClass('is-invalid'); $toError.show();
            } else {
                $(this).removeClass('is-invalid'); $fromError.hide();
                if (!toYear || parseInt(fromYear) <= parseInt(toYear)) { $toSelect.removeClass('is-invalid'); $toError.hide(); }
            }
        });
        $toSelect.off('change').on('change', function() {
            var fromYear = $fromSelect.val(), toYear = $(this).val();
            if (fromYear && toYear && parseInt(fromYear) > parseInt(toYear)) {
                $(this).addClass('is-invalid'); $toError.show(); $fromSelect.addClass('is-invalid'); $fromError.show();
            } else {
                $(this).removeClass('is-invalid'); $toError.hide();
                if (!fromYear || parseInt(fromYear) <= parseInt(toYear)) { $fromSelect.removeClass('is-invalid'); $fromError.hide(); }
            }
        });
    }

    $(document).on('click', '.removeFilterYearRange', function() {
        $(this).closest('.filter-year-range-item').remove();
        updateFilterYearRangeRemoveButtons();
    });

    $(document).on('click', '#applyYearRangeFilter', function() {
        $('#filterYearRangesContainer .filter-year-range-item').each(function() {
            var $fromSelect = $(this).find('.filter-year-from');
            var $toSelect = $(this).find('.filter-year-to');
            var fromYear = $fromSelect.val(), toYear = $toSelect.val();
            if (fromYear && toYear && parseInt(fromYear) > parseInt(toYear)) {
                $fromSelect.val(toYear); $toSelect.val(fromYear);
                $fromSelect.removeClass('is-invalid'); $toSelect.removeClass('is-invalid');
                $fromSelect.next('.invalid-feedback').hide(); $toSelect.next('.invalid-feedback').hide();
            }
        });
        var filterRanges = [], displayRanges = [];
        $('#filterYearRangesContainer .filter-year-range-item').each(function() {
            var fromYear = $(this).find('.filter-year-from').val();
            var toYear = $(this).find('.filter-year-to').val();
            if (fromYear || toYear) {
                var from = fromYear ? parseInt(fromYear) : 1980;
                var to = toYear ? parseInt(toYear) : new Date().getFullYear();
                filterRanges.push({ from: from, to: to });
                displayRanges.push(from === to ? from.toString() : from + '-' + to);
            }
        });
        var displayBox = $('#selectedYearRangesDisplay');
        displayBox.data('all-ranges', displayRanges);
        displayBox.attr('data-show-all', 'false');
        if (displayRanges.length > 0) updateYearRangeDisplay(); else displayBox.html('<div class="text-muted text-center" style="font-size: 10px;">No ranges selected</div>');
        $('#yearRangeModal').modal('hide');
        filterVehicleTable();
    });

    $('#yearRangeModal').on('hidden.bs.modal', function() {
        var yearOptions = '';
        for (var y = new Date().getFullYear(); y >= 1980; y--) yearOptions += '<option value="' + y + '">' + y + '</option>';
        var $newRange = $('<div class="filter-year-range-item mb-2"><div class="row g-2"><div class="col-5"><label class="form-label small">From Year</label><select class="form-control filter-year-from"><option value="">Select Year</option>' + yearOptions + '</select><div class="invalid-feedback" style="display:none;font-size:10px;">From Year cannot be greater than To Year</div></div><div class="col-5"><label class="form-label small">To Year</label><select class="form-control filter-year-to"><option value="">Select Year</option>' + yearOptions + '</select><div class="invalid-feedback" style="display:none;font-size:10px;">To Year cannot be less than From Year</div></div><div class="col-2 d-flex align-items-end"><button type="button" class="btn btn-danger btn-sm removeFilterYearRange" style="display:none;">X</button></div></div></div></div>');
        $('#filterYearRangesContainer').html($newRange);
        attachYearRangeValidation($newRange);
    });

    $('#yearRangeModal').on('shown.bs.modal', function() {
        $('#filterYearRangesContainer .filter-year-range-item').each(function() { attachYearRangeValidation($(this)); });
    });

    // Select All Vehicles Checkbox
    $(document).on('change', '#selectAllVehicles', function() {
        const isChecked = $(this).is(':checked');
        $('.vehicle-checkbox').prop('checked', isChecked);
    });
    
    // Individual checkbox change - update select all checkbox
    $(document).on('change', '.vehicle-checkbox', function() {
        const totalCheckboxes = $('.vehicle-checkbox').length;
        const checkedCheckboxes = $('.vehicle-checkbox:checked').length;
        $('#selectAllVehicles').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
    
    // Function to collect checked vehicle IDs
    function getCheckedVehicleIds() {
        const checkedVehicles = [];
        $('.vehicle-checkbox:checked').each(function() {
            const vehicleId = $(this).data('vehicle-id');
            if (vehicleId) {
                checkedVehicles.push(vehicleId);
            }
        });
        return checkedVehicles;
    }
    

    // Load Vehicle Button - Filter table and save if no match found
    $(document).on('click', '.loadVehicleBtn', function() {
        // Check if part number is selected first
        let selectedPartNumber = $('#part_number_id').val() || $('#part_number').val();
        if (!selectedPartNumber || selectedPartNumber.trim() === '') {
            toastr.warning('Please select Part Number first.');
            return;
        }
        
        // First filter the table
        filterVehicleTable();
        
        // Count visible rows
        let visibleRows = $("#vehicleTable tbody tr:visible").length;
        
        // If no vehicles match, save the vehicle
        if (visibleRows === 0) {
            // Get all filter values
            let selectedManufacturer = $('.car-manufacturer-select').val();
            let selectedModel = $('.car-model-select').val();
            let selectedEngine = $('.car-engine-select').val();
            let selectedCountry = $('.car-country-select').val();
            let selectedYearRanges = $('#selectedYearRangesDisplay').data('all-ranges') || [];
            
            // Check if all required fields are filled
            if (!selectedManufacturer || !selectedModel || !selectedEngine || !selectedCountry || selectedYearRanges.length === 0) {
                toastr.warning('Please fill all fields (Manufacturer, Model, Engine, Country, and Year Ranges) before loading.');
                return;
            }
            
            // Prepare form data
            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('car_manufacturer', selectedManufacturer);
            formData.append('car_model_name', selectedModel);
            formData.append('engine_cc', selectedEngine);
            formData.append('car_manufactured_country', selectedCountry);
            formData.append('v_part_number_id', selectedPartNumber);
            
            // Add year ranges
            selectedYearRanges.forEach(function(range) {
                let rangeParts = range.split('-');
                let fromYear = rangeParts[0];
                let toYear = rangeParts.length > 1 ? rangeParts[1] : rangeParts[0];
                formData.append('year_from[]', fromYear);
                formData.append('year_to[]', toYear);
            });
            
            // Save vehicle via AJAX
            $.ajax({
                url: "{{ route('post.product_vehical') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.errors && res.errors.length > 0) {
                        res.errors.forEach(function(error) {
                            toastr.error(error);
                        });
                        return;
                    }
                    
                    if (res.vehicles && res.vehicles.length > 0) {
                        toastr.success(res.message || "Vehicle saved and loaded successfully!");
                        
                        // Add vehicles to table
                        let vehicleGroups = {};
                        res.vehicles.forEach(function(v) {
                            let key = `${v.v_part_number_id}-${v.car_manufacturer}-${v.car_model_name}-${v.engine_cc}-${v.car_manufactured_country}`;
                            if (!vehicleGroups[key]) {
                                vehicleGroups[key] = {
                                    v_part_number_id: v.v_part_number_id,
                                    car_manufacturer: v.car_manufacturer,
                                    car_model_name: v.car_model_name,
                                    engine_cc: v.engine_cc,
                                    car_manufactured_country: v.car_manufactured_country,
                                    manutacturer_vehical: v.manutacturer_vehical,
                                    model_vehical: v.model_vehical,
                                    engine_vehical: v.engine_vehical,
                                    country_vehical: v.country_vehical,
                                    vehical_part_number: v.vehical_part_number,
                                    yearRanges: []
                                };
                            }
                            if (v.year_from && v.year_to) {
                                let yearStr = v.year_from == v.year_to ? v.year_from : v.year_from + '-' + v.year_to;
                                if (vehicleGroups[key].yearRanges.indexOf(yearStr) === -1) {
                                    vehicleGroups[key].yearRanges.push(yearStr);
                                }
                            }
                        });
                        
                        // Add new vehicles to table
                        Object.keys(vehicleGroups).forEach(function(key) {
                            let group = vehicleGroups[key];
                            
                            // Check if row already exists
                            let exists = false;
                            $("#vehicleTable tbody tr").each(function() {
                                let $row = $(this);
                                if ($row.data('part') == group.v_part_number_id &&
                                    $row.find('.editVehicleBtn').data('manufacturer') == group.car_manufacturer &&
                                    $row.find('.editVehicleBtn').data('model') == group.car_model_name &&
                                    $row.find('.editVehicleBtn').data('engine') == group.engine_cc &&
                                    $row.find('.editVehicleBtn').data('country') == group.car_manufactured_country) {
                                    exists = true;
                                    return false;
                                }
                            });
                            
                            if (!exists) {
                                // Build year ranges HTML
                                let yearRangesHtml = '';
                                if (group.yearRanges.length > 0) {
                                    group.yearRanges.sort(function(a, b) {
                                        let aFrom = parseInt(a.split('-')[0]);
                                        let bFrom = parseInt(b.split('-')[0]);
                                        return aFrom - bFrom;
                                    });
                                    yearRangesHtml = '<div style="display: inline-flex; flex-wrap: wrap; gap: 6px; align-items: center;">';
                                    group.yearRanges.forEach(function(range) {
                                        yearRangesHtml += `<span class="badge" style="background-color: #7DD3FC; color: #0C4A6E; padding: 6px 12px; border-radius: 6px; font-weight: 500; font-size: 13px; white-space: nowrap;">${range}</span>`;
                                    });
                                    yearRangesHtml += '</div>';
                                } else {
                                    yearRangesHtml = '<span class="badge bg-secondary">-</span>';
                                }
                                
                                // Add new row
                                let newRow = `
                                    <tr data-part="${group.v_part_number_id}">
                                        <td style="text-align: center;">
                                            <input type="checkbox" class="vehicle-checkbox" name="vehical_id[]" value="${group.id || ''}"
                                                data-vehicle-id="${group.id || ''}"
                                                data-part="${group.v_part_number_id}"
                                                data-manufacturer="${group.car_manufacturer}"
                                                data-model="${group.car_model_name}"
                                                data-engine="${group.engine_cc}"
                                                data-country="${group.car_manufactured_country}"
                                                style="cursor: pointer;">
                                        </td>
                                        <td>${group.manutacturer_vehical?.name || '-'}</td>
                                        <td>${group.model_vehical?.name || '-'}</td>
                                        <td>${group.country_vehical?.name || '-'}</td>
                                        <td>${yearRangesHtml}</td>
                                        <td>${group.engine_vehical?.name || '-'}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary editVehicleBtn"
                                                data-part="${group.v_part_number_id}"
                                                data-manufacturer="${group.car_manufacturer}"
                                                data-model="${group.car_model_name}"
                                                data-engine="${group.engine_cc}"
                                                data-country="${group.car_manufactured_country}"
                                                data-year-ranges='${JSON.stringify(group.yearRanges)}'
                                                data-year-from="${group.yearRanges.length > 0 ? group.yearRanges[0].split('-')[0] : ''}"
                                                data-year-to="${group.yearRanges.length > 0 ? (group.yearRanges[0].includes('-') ? group.yearRanges[0].split('-')[1] : group.yearRanges[0]) : ''}">
                                                <i class="ti ti-pencil"></i>
                                            </button>
                                        </td>
                                        <td>${group.vehical_part_number?.name || '-'}</td>
                                    </tr>
                                `;
                                $("#vehicleTable tbody").prepend(newRow);
                            }
                        });
                        
                        // Filter again to show the new vehicle
                        filterVehicleTable();
                    }
                },
                error: function(xhr) {
                    let response = xhr.responseJSON;
                    if (response && response.errors && Array.isArray(response.errors)) {
                        response.errors.forEach(function(error) {
                            toastr.error(error);
                        });
                    } else {
                        toastr.error(response?.message || 'Error saving vehicle');
                    }
                }
            });
        }
    });

    // Auto-filter on dropdown change - Manufacturer
    $(document).on('change', '.car-manufacturer-select', function() {
        filterVehicleTable();
    });

    // Auto-filter on dropdown change - Model
    $(document).on('change', '.car-model-select', function() {
        filterVehicleTable();
    });

    // Auto-filter on dropdown change - Engine
    $(document).on('change', '.car-engine-select', function() {
        filterVehicleTable();
    });

    // Auto-filter on dropdown change - Country
    $(document).on('change', '.car-country-select', function() {
        filterVehicleTable();
    });

    // Auto-filter on Part Number change
    $(document).on('change', '#part_number_id, #part_number', function() {
        filterVehicleTable();
    });

    // Show all vehicles on page load (if no filters are applied)
    $(document).ready(function() {
        // Show all vehicles initially
        $("#vehicleTable tbody tr").show();
        
        // If part number is selected, filter by it
        const selectedPartNumber = $('#part_number_id').val() || $('#part_number').val();
        if (selectedPartNumber) {
            filterVehicleTable();
        }
    });

    // Associated Vehicles (same as create/new) - Initialize from item's vehical_ids
    if ($('#openItemVehicleModalEdit').length && $('#itemVehicleAddModalEdit').length) {
        let itemVehicleIdsEdit = @json($editingItem->vehical_ids ?? []);
        let itemVehicleDetailsEdit = @json(($editingItem->vehical_items() ?? collect())->mapWithKeys(function($v) {
            $make = optional($v->manutacturer_vehical)->name ?? '-';
            $model = optional($v->model_vehical)->name ?? '-';
            $yr = ($v->year_from && $v->year_to) ? ($v->year_from == $v->year_to ? $v->year_from : $v->year_from . '-' . $v->year_to) : '-';
            return [$v->id => $make . ' / ' . $model . ' / ' . $yr];
        })->toArray());
        @php
            $itemVehicleFullDetailsEditData = ($editingItem->vehical_items() ?? collect())->keyBy('id')->map(function($v) {
                return [
                    'car_manufacturer' => $v->car_manufacturer,
                    'car_model_name' => $v->car_model_name,
                    'engine_cc' => $v->engine_cc,
                    'car_manufactured_country' => $v->car_manufactured_country,
                    'year_from' => $v->year_from,
                    'year_to' => $v->year_to,
                    'v_part_number_id' => $v->v_part_number_id
                ];
            })->toArray();
        @endphp
        let itemVehicleFullDetailsEdit = @json($itemVehicleFullDetailsEditData);
        let editingVehicleId = null;

        $(document).on('click', '[data-bs-target="#itemVehicleAddModalEdit"]', function(e) {
            if (editingVehicleId) return;
            let outsidePart = $('#part_number_id').val();
            if (!outsidePart || outsidePart === '' || outsidePart === null) {
                e.preventDefault();
                e.stopPropagation();
                if (typeof toastr !== 'undefined') toastr.warning('Please select Part Number first.');
                $('#part_number_id').addClass('is-invalid').focus();
                return false;
            }
        });

        $('#itemVehicleAddModalEdit').on('show.bs.modal', function(e) {
            if (!editingVehicleId) {
                let outsidePart = $('#part_number_id').val();
                if (!outsidePart || outsidePart === '') {
                    e.preventDefault();
                    if (typeof toastr !== 'undefined') toastr.error('Please select Part Number first.');
                    $('#part_number_id').addClass('is-invalid').focus();
                    return;
                }
                $('#itemVehicleAddModalEdit .modal-title').html('<i class="ti ti-car me-2"></i>Add Vehicle');
                $('#itemVehicleAddBtnEdit').text('Add Vehicle');
                document.getElementById('itemVehicleAddFormEdit')?.reset();
                $('#itemVehiclePartNumberEdit').val(outsidePart);
            }
            // Init Select2 search on vehicle modal dropdowns (in case they weren’t in DOM or need refresh)
            var $modal = $('#itemVehicleAddModalEdit');
            var vehicleSelectors = '.item-vehicle-manufacturer-edit, .item-vehicle-model-edit, .item-vehicle-year-from-edit, .item-vehicle-year-to-edit, .item-vehicle-engine-edit, .item-vehicle-country-edit';
            $modal.find(vehicleSelectors).each(function() {
                var $sel = $(this);
                if ($.fn.select2) {
                    if ($sel.hasClass('select2-hidden-accessible')) {
                        try { $sel.select2('destroy'); } catch (err) {}
                    }
                    $sel.select2({
                        placeholder: $sel.find('option:first').text(),
                        allowClear: true,
                        width: '100%',
                        minimumResultsForSearch: 0,
                        dropdownParent: $modal
                    });
                }
            });
        });
        $('#itemVehicleAddModalEdit').on('shown.bs.modal', function() {
            var $modal = $('#itemVehicleAddModalEdit');
            if (editingVehicleId && itemVehicleFullDetailsEdit[editingVehicleId]) {
                var data = itemVehicleFullDetailsEdit[editingVehicleId];
                $modal.find('#itemVehicleMakeEdit').val(data.car_manufacturer || '').trigger('change');
                $modal.find('#itemVehicleModelEdit').val(data.car_model_name || '').trigger('change');
                $modal.find('#itemVehicleYearFromEdit').val(data.year_from || '').trigger('change');
                $modal.find('#itemVehicleYearToEdit').val(data.year_to || '').trigger('change');
                $modal.find('#itemVehicleEngineEdit').val(data.engine_cc || '').trigger('change');
                $modal.find('#itemVehicleCountryEdit').val(data.car_manufactured_country || '').trigger('change');
            }
            $modal.find('.item-vehicle-manufacturer-edit, .item-vehicle-model-edit, .item-vehicle-year-from-edit, .item-vehicle-year-to-edit, .item-vehicle-engine-edit, .item-vehicle-country-edit').off('select2:open.vehicleFocus').on('select2:open.vehicleFocus', function() {
                var $sel = $(this);
                setTimeout(function() {
                    var $search = $modal.find('.select2-search__field');
                    if ($search.length && $search[0]) {
                        $search[0].focus();
                    }
                }, 50);
                setTimeout(function() {
                    var $search = $modal.find('.select2-search__field');
                    if ($search.length && $search[0]) {
                        $search[0].focus();
                    }
                }, 150);
            });
        });
        $('#itemVehicleAddModalEdit').on('hidden.bs.modal', function() {
            editingVehicleId = null;
            $('#itemVehicleAddModalEdit').find('.item-vehicle-manufacturer-edit, .item-vehicle-model-edit, .item-vehicle-year-from-edit, .item-vehicle-year-to-edit, .item-vehicle-engine-edit, .item-vehicle-country-edit').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    try { $(this).select2('destroy'); } catch (err) {}
                }
            });
        });

        $('#itemVehicleAddFormEdit').off('submit').on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (!$('#itemVehicleAddModalEdit').hasClass('show')) return false;
            let outsidePart = $('#itemVehiclePartNumberEdit').val() || $('#part_number_id').val();
            if (!outsidePart || outsidePart === '') {
                if (typeof toastr !== 'undefined') toastr.error('Please select Part Number first.');
                $('#part_number_id').addClass('is-invalid').focus();
                return false;
            }
            $('#part_number_id').removeClass('is-invalid');
            let form = this;
            let $btn = $('#itemVehicleAddBtnEdit');
            let isEdit = !!editingVehicleId;
            let formData = new FormData(form);
            formData.set('v_part_number_id', outsidePart);

            if (isEdit && itemVehicleFullDetailsEdit[editingVehicleId]) {
                var old = itemVehicleFullDetailsEdit[editingVehicleId];
                formData.set('old_v_part_number_id', old.v_part_number_id);
                formData.set('old_car_manufacturer', old.car_manufacturer);
                formData.set('old_car_model_name', old.car_model_name);
                formData.set('old_engine_cc', old.engine_cc || '');
                formData.set('old_car_manufactured_country', old.car_manufactured_country || '');
                $btn.prop('disabled', true).html('<i class="ti ti-loader me-1"></i> Updating...');
                $.ajax({
                    url: '{{ route("update.product_vehical") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        if (!res) { if (typeof toastr !== 'undefined') toastr.error('No response'); return; }
                        if (res.success) {
                            var deletedIds = res.deleted_ids || [];
                            deletedIds.forEach(function(id) {
                                itemVehicleIdsEdit = itemVehicleIdsEdit.filter(function(x) { return x !== id; });
                                delete itemVehicleDetailsEdit[id];
                                delete itemVehicleFullDetailsEdit[id];
                            });
                            if (res.vehicles && res.vehicles.length > 0) {
                                res.vehicles.forEach(function(v) {
                                    if (itemVehicleIdsEdit.indexOf(v.id) === -1) itemVehicleIdsEdit.push(v.id);
                                    var make = v.manutacturer_vehical?.name || '-';
                                    var model = v.model_vehical?.name || '-';
                                    var yr = (v.year_from && v.year_to) ? (v.year_from === v.year_to ? v.year_from : v.year_from + '-' + v.year_to) : '-';
                                    itemVehicleDetailsEdit[v.id] = make + ' / ' + model + ' / ' + yr;
                                    itemVehicleFullDetailsEdit[v.id] = {
                                        car_manufacturer: v.car_manufacturer,
                                        car_model_name: v.car_model_name,
                                        engine_cc: v.engine_cc,
                                        car_manufactured_country: v.car_manufactured_country,
                                        year_from: v.year_from,
                                        year_to: v.year_to,
                                        v_part_number_id: v.v_part_number_id
                                    };
                                });
                            }
                            updateItemVehiclesDisplayEdit();
                            bootstrap.Modal.getInstance(document.getElementById('itemVehicleAddModalEdit'))?.hide();
                            if (typeof toastr !== 'undefined') toastr.success(res.message || 'Vehicle updated!');
                            if (typeof playSaveSound === 'function') playSaveSound();
                        } else {
                            if (typeof toastr !== 'undefined') toastr.warning(res.message || 'Update failed');
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON?.message || 'Failed to update vehicle.';
                        var errors = xhr.responseJSON?.errors;
                        if (errors && Array.isArray(errors) && errors.length) {
                            errors.forEach(function(err) { if (typeof toastr !== 'undefined') toastr.error(err); });
                        } else {
                            if (typeof toastr !== 'undefined') toastr.error(msg);
                        }
                    },
                    complete: function() { $btn.prop('disabled', false).html('Update Vehicle'); }
                });
            } else {
                $btn.prop('disabled', true).html('<i class="ti ti-loader me-1"></i> Adding...');
                $.ajax({
                    url: '{{ route("post.product_vehical") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        if (!res) { if (typeof toastr !== 'undefined') toastr.error('No response from server'); return; }
                        if (res.errors && res.errors.length > 0) {
                            res.errors.forEach(function(err) { if (typeof toastr !== 'undefined') toastr.error(err); });
                            return;
                        }
                        if (res.duplicate_years && res.duplicate_years.length > 0) {
                            if (typeof toastr !== 'undefined') toastr.warning('Already exists for year(s): ' + res.duplicate_years.join(', '));
                            return;
                        }
                        if (res.success && res.vehicles && res.vehicles.length > 0) {
                            res.vehicles.forEach(function(v) {
                                if (itemVehicleIdsEdit.indexOf(v.id) === -1) {
                                    itemVehicleIdsEdit.push(v.id);
                                    var make = v.manutacturer_vehical?.name || '-';
                                    var model = v.model_vehical?.name || '-';
                                    var yr = (v.year_from && v.year_to) ? (v.year_from === v.year_to ? v.year_from : v.year_from + '-' + v.year_to) : '-';
                                    itemVehicleDetailsEdit[v.id] = make + ' / ' + model + ' / ' + yr;
                                    itemVehicleFullDetailsEdit[v.id] = {
                                        car_manufacturer: v.car_manufacturer,
                                        car_model_name: v.car_model_name,
                                        engine_cc: v.engine_cc,
                                        car_manufactured_country: v.car_manufactured_country,
                                        year_from: v.year_from,
                                        year_to: v.year_to,
                                        v_part_number_id: v.v_part_number_id
                                    };
                                }
                            });
                            updateItemVehiclesDisplayEdit();
                            bootstrap.Modal.getInstance(document.getElementById('itemVehicleAddModalEdit'))?.hide();
                            if (typeof toastr !== 'undefined') toastr.success(res.message || 'Vehicle saved successfully!');
                            if (typeof playSaveSound === 'function') playSaveSound();
                        } else if (res.message && $('#itemVehicleAddModalEdit').hasClass('show')) {
                            if (typeof toastr !== 'undefined') toastr.info(res.message);
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON?.message || 'Failed to add vehicle.';
                        var errors = xhr.responseJSON?.errors;
                        if (errors && Array.isArray(errors) && errors.length) {
                            errors.forEach(function(err) { if (typeof toastr !== 'undefined') toastr.error(err); else alert(err); });
                        } else {
                            if (typeof toastr !== 'undefined') toastr.error(msg); else alert(msg);
                        }
                    },
                    complete: function() { $btn.prop('disabled', false).html('Add Vehicle'); }
                });
            }
            return false;
        });

        function updateItemVehiclesDisplayEdit() {
            var $list = $('#itemVehiclesListEdit');
            var $hidden = $('#itemVehiclesHiddenInputsEdit');
            $hidden.empty();
            if (itemVehicleIdsEdit.length === 0) {
                $list.html('<p class="text-muted small mb-0">No vehicles added yet.</p>');
                return;
            }
            var html = '<div class="list-group list-group-flush">';
            itemVehicleIdsEdit.forEach(function(id, idx) {
                $hidden.append('<input type="hidden" name="vehical_id[]" value="' + id + '">');
                var label = itemVehicleDetailsEdit[id] || 'Vehicle #' + (idx + 1);
                html += '<div class="list-group-item d-flex justify-content-between align-items-center py-2" data-vehicle-id="' + id + '"><span class="small">' + label + '</span><div class="btn-group btn-group-sm"><button type="button" class="btn btn-sm btn-outline-primary edit-item-vehicle-edit" data-id="' + id + '" title="Edit vehicle"><i class="ti ti-pencil"></i></button><button type="button" class="btn btn-sm btn-outline-danger remove-item-vehicle-edit" data-id="' + id + '"><i class="ti ti-trash"></i></button></div></div>';
            });
            html += '</div>';
            $list.html(html);
            $('.edit-item-vehicle-edit').off('click').on('click', function() {
                var id = parseInt($(this).data('id'), 10);
                var data = itemVehicleFullDetailsEdit[id];
                if (!data) return;
                editingVehicleId = id;
                $('#itemVehicleAddModalEdit .modal-title').html('<i class="ti ti-car me-2"></i>Edit Vehicle');
                $('#itemVehicleAddBtnEdit').text('Update Vehicle');
                $('#itemVehiclePartNumberEdit').val(data.v_part_number_id);
                $('#part_number_id').val(data.v_part_number_id).removeClass('is-invalid');
                $('#itemVehicleAddModalEdit').modal('show');
            });
            $('.remove-item-vehicle-edit').off('click').on('click', function() {
                var id = parseInt($(this).data('id'));
                itemVehicleIdsEdit = itemVehicleIdsEdit.filter(function(x) { return x !== id; });
                delete itemVehicleDetailsEdit[id];
                if (itemVehicleFullDetailsEdit[id]) delete itemVehicleFullDetailsEdit[id];
                updateItemVehiclesDisplayEdit();
            });
        }
        updateItemVehiclesDisplayEdit();
    }
</script>
@endpush
