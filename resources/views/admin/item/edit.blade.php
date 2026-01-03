@extends('layouts.app')
@section('title', 'Edit Product')
@section('content')
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
                            <div class="type-box text-center p-4" :class="{ 'selected': selectedType === 'battery' }"
                                @click="selectType('battery')">
                                <i class="ti ti-battery fs-1 d-block mb-2"></i>
                                Battery
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
                                <div class="col-md-4 mt-3" x-show="selectedType === 'parts' || selectedType === 'filters' || selectedType === 'breakpad'">
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
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                                data-title="Add Part Number" data-mode="add"
                                                data-route="{{ route('post.partnumber') }}"
                                                data-target-select=".part_number-select">
                                                <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary open-universal-modal d-none"
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
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-mode="add" data-title="Add group"
                                            data-route="{{ route('post.groups') }}" data-target-select=".group-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>

                                        {{-- <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit group"
                                            data-fetch-route="{{ route('show.groups', ':id') }}"
                                            data-update-route="{{ route('post.groups.update', ':id') }}"
                                            data-delete-route="{{ route('post.groups.destroy', ':id') }}"
                                            data-target-select=".group-select">
                                            <i data-feather="edit"></i>
                                        </button> --}}
                                    </div>
                                    @error('group')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                    <div class="col-md-4" x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil' || selectedType === 'filters' || selectedType === 'breakpad'">
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
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Company" data-route="{{ route('post.companies') }}"
                                            data-target-select=".company-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                    </div>
                                    @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <!-- Series/Technology -->
                                <div class="col-md-4 mt-3" x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil'">
                                    <label for="technology_select">
                                        <span x-show="selectedType === 'parts'">Technology:</span>
                                        <span x-show="selectedType !== 'parts'">Series:</span>
                                    </label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control technology-select searchable-select @error('technology') is-invalid @enderror"
                                            name="technology" id="technology_select">
                                            <option value="">Select</option>
                                            @foreach ($technologies as $tech)
                                            <option
                                                value="{{ $tech->id }}"
                                                {{ old('technology', $item->technology) == $tech->id ? 'selected' : '' }}>
                                                {{ $tech->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            x-bind:data-title="selectedType === 'parts' ? 'Add Technology' : 'Add Series'"
                                            data-route="{{ route('post.technology') }}"
                                            data-target-select=".technology-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                    </div>
                                    @error('technology')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!-- Product Name -->
                                 <div class="col-md-4" x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil' || selectedType === 'scrap'">
                                    <label for="itemname">Product Name:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control name-select searchable-select @error('p_id') is-invalid @enderror"
                                            name="p_id" id="name">
                                            <option value="">Select Product Name</option>
                                            @foreach ($product as $prod)
                                            <option value="{{ $prod->id }}"
                                              {{ old('p_id', $item->p_id) == $prod->id ? 'selected' : '' }}>
                                                {{ $prod->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Product" data-route="{{ route('post.product') }}"
                                            data-target-select=".name-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                    </div>
                                    @error('p_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4 mt-3" x-show="selectedType === 'parts' || selectedType === 'oil' || selectedType === 'scrap' || selectedType === 'services' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <label for="category_parts">Category:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control category-select searchable-select @error('category_id') is-invalid @enderror"
                                            name="category_id" id="category_parts">
                                            <option value="">Select Category</option>
                                            @foreach ($Categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ ($item->category_id ?? old('category_id')) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Category" data-route="{{ route('post.item.category') }}"
                                            data-target-select=".category-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                    </div>
                                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                            </div>
                        </div>
                        <!-- PARTS FIELDS -->
                        <div class="field-group parts-fields" :class="{ 'active': selectedType === 'parts' }">
                            <div class="row p-3 mt-4">
                                <div class="col-md-4">
                                    <label for="quality">Quality:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control quality-select searchable-select @error('quality_id') is-invalid @enderror"
                                            name="quality_id" id="quality">
                                            <option value="">Select Quality</option>
                                            @foreach ($qualities as $qaul)
                                            <option value="{{ $qaul->id }}"
                                              {{ old('quality_id', $item->quality_id) == $qaul->id ? 'selected' : '' }}>
                                                {{ $qaul->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Quality" data-route="{{ route('post.qualities') }}"
                                            data-target-select=".quality-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                    </div>
                                    @error('quality_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
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
                                                {{ $plate->name }} P
                                        </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-primary open-universal-modal"
                                        data-title="Add Plate"
                                        data-route="{{ route('post.platos') }}"
                                        data-target-select=".plates-select">
                                        <i data-feather="plus" class="feather-plus"></i>
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
                                                {{ $ampere->name }}A
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Amperes"
                                            data-route="{{ route('post.amphors') }}"
                                            data-target-select=".amperes-select">
                                            <i data-feather="plus" class="feather-plus"></i>
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
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Volt"
                                            data-route="{{ route('post.volts') }}"
                                            data-target-select=".volt-select">
                                            <i data-feather="plus" class="feather-plus"></i>
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
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add CCA"
                                            data-route="{{ route('post.cca') }}"
                                            data-target-select=".cca-select">
                                            <i data-feather="plus" class="feather-plus"></i>
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
                                    <button type="button" class="btn btn-primary open-universal-modal"
                                        data-title="Add Minus Pole Direction"
                                        data-route="{{ route('post.minuspool') }}"
                                        data-target-select=".minus-pole-direction-select">
                                        <i data-feather="plus" class="feather-plus"></i>
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
                                                {{ $warrenty->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Warrenty" data-mode="add"
                                            data-route="{{ route('post.warrenty') }}"
                                            data-target-select=".Warrenty-select">
                                            <i data-feather="plus" class="feather-plus"></i>
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
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Made In" data-mode="add"
                                            data-route="{{ route('post.made_ins') }}"
                                            data-target-select=".made_in-select">
                                            <i data-feather="plus" class="feather-plus"></i>
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
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Grade"
                                            data-route="{{ route('post.grade') }}"
                                            data-target-select=".grade-select">
                                            <i data-feather="plus" class="feather-plus"></i>
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
                                        <button type="button" class="btn btn-primary open-universal-modal"
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
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Mileage" data-route="{{ route('post.item.mileage') }}"
                                            data-target-select=".mileage-select">
                                            <i data-feather="plus" class="feather-plus"></i>
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
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Level" data-mode="add"
                                            data-route="{{ route('post.levels') }}" data-target-select=".level-select">
                                            <i data-feather="plus"></i>
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
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Formula"
                                            data-route="{{ route('post.formulas') }}"
                                            data-target-select=".formulas-select">
                                            <i data-feather="plus"></i>
                                        </button>
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
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Services" data-route="{{ route('post.services') }}"
                                            data-target-select=".Services-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>

                                    </div>
                                    @error('services')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- FILTERS FIELDS -->
                        <div class="field-group filters-fields" :class="{ 'active': selectedType === 'filters' }">
                            <div class="row p-3 mt-4">
                                <div class="col-md-4">
                                    <label for="quality_filters">Quality:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control quality-select searchable-select @error('quality_id') is-invalid @enderror"
                                            name="quality_id" id="quality_filters">
                                            <option value="">Select Quality</option>
                                            @foreach ($qualities as $qaul)
                                            <option value="{{ $qaul->id }}"
                                              {{ old('quality_id', $item->quality_id) == $qaul->id ? 'selected' : '' }}>
                                                {{ $qaul->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Quality" data-route="{{ route('post.qualities') }}"
                                            data-target-select=".quality-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                    </div>
                                    @error('quality_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- BREAK PAD FIELDS -->
                        <div class="field-group breakpad-fields" :class="{ 'active': selectedType === 'breakpad' }">
                            <div class="row p-3 mt-4">
                                <div class="col-md-4">
                                    <label for="quality_breakpad">Quality:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control quality-select searchable-select @error('quality_id') is-invalid @enderror"
                                            name="quality_id" id="quality_breakpad">
                                            <option value="">Select Quality</option>
                                            @foreach ($qualities as $qaul)
                                            <option value="{{ $qaul->id }}"
                                              {{ old('quality_id', $item->quality_id) == $qaul->id ? 'selected' : '' }}>
                                                {{ $qaul->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Quality" data-route="{{ route('post.qualities') }}"
                                            data-target-select=".quality-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                    </div>
                                    @error('quality_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- COMMON MEDIA & DESCRIPTION -->
                        <div class="field-group media-fields" :class="{ 'active': selectedType }">
                            <div class="row mt-4">
                                <div class="col-md-6" x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil' || selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <label for="unit_parts">Unit:</label>
                                    <div class="input-group">
                                        <select
                                           class="form-control pro_unit-select searchable-select @error('unit') is-invalid @enderror"
                                            name="unit" id="unit_parts">
                                            <option value="">Select Unit</option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }}"
                                                    data-name="{{ $unit->name }}"
                                                    data-baseunit="{{ $unit->baseUnit->name ?? '' }}"
                                                    data-multiplier="{{ $unit->base_unit_multiplier ?? '' }}"
                                                    {{ ($item->unit ?? old('unit')) == $unit->id ? 'selected' : '' }}>
                                                    {{ $unit->name }}
                                                    @if ($unit->base_unit_id)
                                                        ({{ $unit->base_unit_multiplier }} - {{ $unit->baseUnit->name }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#Unit-add-modal">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                    </div>
                                    <!-- Purchase Section -->
                                    <div class="input-group align-items-center gap-2" id="unit-info" style="display:none; margin-top:10px;">
                                        <span id="unit-name" class="fw-bold"></span>
                                        <span class="equal-sign">=</span>
                                        <input type="text" id="total_price_input" name="total_price"
                                            class="form-control form-control-sm"
                                            value="{{ old('total_price', $item->total_price ?? '') }}"
                                            style="width:120px;" placeholder="Cost Price">

                                        <span id="multiplier-text" class="fw-bold"></span>

                                        <input type="number" id="base_price_input" name="price_per_unit"
                                            class="form-control form-control-sm"
                                            value="{{ old('price_per_unit', $item->price_per_unit ?? '') }}"
                                            style="width:100px;" placeholder="Price per Unit">
                                    </div>
                                    @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <!-- Sale Price Section -->
                                <div class="col-md-6" x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil' || selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <label for="sale_price_parts">Sale Price:</label>
                                    <input type="number" class="form-control @error('sale_price') is-invalid @enderror"
                                        name="sale_price" id="sale_price_parts" value="{{ old('sale_price') }}" hidden />

                                    <div class="input-group align-items-center gap-2" id="sale-price-info" style="display:none; margin-top:10px;">
                                    <span id="sale-unit-name" class="fw-bold"></span>
                                    <span class="sale-equal-sign">=</span>

                                    <input type="text" id="total_sale_price" name="total_sale_price"
                                        class="form-control form-control-sm"
                                        value="{{ old('total_sale_price', $item->total_sale_price ?? '') }}"
                                        style="width:120px;" placeholder="Sale Price">

                                    <span id="sale-multiplier-text" class="fw-bold"></span>

                                    <input type="number" id="sale_base_price" name="sale_price_per_base"
                                        class="form-control form-control-sm"
                                        value="{{ old('sale_price_per_base', $item->sale_price_per_base ?? '') }}"
                                        style="width:100px;" placeholder="Sale per Unit">
                                </div>

                                    @error('sale_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                    <label for="low_stock_parts">Low Stock:</label>
                                    <input type="number" class="form-control @error('l_stock') is-invalid @enderror"
                                        name="l_stock" id="low_stock_parts" value="{{ $item->l_stock ?? old('l_stock') }}" />
                                    @error('l_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4 mt-3" x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil' || selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <label for="maintain_stock_parts">Maintain Stock:</label>
                                    <input type="number"
                                        class="form-control @error('m_stock') is-invalid @enderror"
                                        name="m_stock" id="maintain_stock_parts"
                                        value="{{ $item->m_stock ?? old('m_stock') }}" />
                                    @error('m_stock') <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mt-3" x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil' || selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <label for="on_hand">Opening Stock:</label>
                                    <input type="number"
                                        class="form-control @error('on_hand') is-invalid @enderror" name="on_hand"
                                        id="on_hand" value="{{ $item->on_hand ?? old('on_hand') }}" />
                                    @error('on_hand') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Weight -->
                                    <div class="col-md-4 mt-3" x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil' || selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                                        <label for="weight">Weight (kg):</label>
                                        <input type="number"
                                            class="form-control @error('weight_for_delivery') is-invalid @enderror" name="weight_for_delivery"
                                            id="weight" value="{{ $item->weight_for_delivery ?? old('weight_for_delivery') }}" />
                                        @error('weight_for_delivery') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>



                                <div class="col-md-12 mt-4" x-show="selectedType === 'battery' || selectedType === 'parts' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <div class="col-md-4 mt-3 d-none">
                                        <label for="vehical_id">Vehicle Type:</label>
                                        <div class="input-group">
                                            <select
                                                class="form-control searchable-select @error('vehical_id') is-invalid @enderror"
                                                name="vehical_id" id="vehical_id">
                                                <option value="">Select Vehicle Type</option>
                                                @foreach ($Vehicals as $vehicle)
                                                    <option value="{{ $vehicle->id }}"
                                                    {{ old('vehical_id', $item->vehical_id) == $vehicle->id ? 'selected' : '' }}>
                                                        {{ $vehicle->manutacturer_vehical->name??'-' }}
                                                        {{ $vehicle->model_vehical->name??'-' }}
                                                        {{ $vehicle->engine_vehical->name??'-' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-primary " data-bs-toggle="modal"
                                                data-bs-target="#vehical-add-modal">
                                                <i data-feather="plus" class="feather-plus"></i>
                                            </button>
                                        </div>
                                        @error('vehical_id') <div class="invalid-feedback">{{ $message }}</div> @enderror

                                    </div>
                                    {{-- VEHICLE TABLE --}}
                                <div class="col-md-12" x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <div class="table-responsive mt-4" style="max-height:250px;overflow-y:auto;">
                                        <table class="table table-bordered" id="vehicleTable">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Manufacturer</th>
                                                    <th>Model</th>
                                                    <th>Year</th>
                                                    <th>Engine</th>
                                                    <th>Country</th>
                                                    <th>Part Number</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                        <tbody>
                                            @foreach ($Vehis as $car)
                                            <tr data-part="{{ $car->v_part_number_id??'' }}">

                                                <td>{{ $car->manutacturer_vehical->name ?? '-' }}</td>

                                                <td>{{ $car->model_vehical->name ?? '-' }}</td>
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

                                                <td>{{ $car->country_vehical->name ?? '-' }}</td>

                                                <td>{{ $car->vehical_part_number->name ?? '-' }}</td>

                                                <td>
                                                    <button
                                                    type="button"
                                                    class="btn btn-sm btn-primary editVehicleBtn"
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
                                    <input  name="short_disc"
                                        class="form-control" value="{{ $item->short_disc ?? old('short_disc') }}" />
                                </div>
                                <!-- Description -->
                                <div class="col-md-12 mt-3">
                                    <label for="content" class="form-label fw-bold">Product Description</label>
                                    <textarea id="summernote" name="pro_dis"
                                        class="form-control">{{ $item->pro_dis ?? old('pro_dis') }}</textarea>
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
                        <th>User Name</th>
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
                                <div> {{ $item->partnumber_item->name ?? '-' }}</div>
                                <div> {{ $item->category->name ?? '-' }}</div>
                                <div> {{ $item->company_item->name ?? '-' }}</div>
                                <div> {{ $item->quality_item->name ?? '-' }}</div>
                            </div>
                        </td>
                        <td>{{ $item->item_user->name??'-' }}</td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-primary  dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu">
                                    {{-- <li>
                                        <a class="dropdown-item" href="">
                                            <i data-feather="tag" class="me-1"></i> Lable
                                        </a>
                                    </li> --}}
                                    <li>
                                        <a class="dropdown-item" href="{{ route('item.show',$item->id) }}">
                                            <i data-feather="eye" class="me-1"></i> View
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('item.edit',$item->id) }}">
                                            <i data-feather="edit" class="me-1"></i> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)"
                                            onclick="confirmDelete('delete-form-{{ $item->id }}')" class="p-2">
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
                                    <hr>
                                    {{-- <li>
                                        <a class="dropdown-item" href="#">
                                            <i data-feather="package" class="me-1"></i> Add or Edit Open Stock
                                        </a>
                                    </li> --}}

                                    {{-- <li>
                                        <a class="dropdown-item" href="#">
                                            <i data-feather="clock" class="me-1"></i> Product Stock History
                                        </a>
                                    </li> --}}


                                    <li>
                                        <a class="dropdown-item text-primary"
                                            href="{{ route('item.duplicate', $item->id) }}">
                                            <i data-feather="copy" class="me-1"></i> Duplicate
                                        </a>
                                    </li>

                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">No items found.</td>
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
                            <div class="row col-12 mt-4" id="baseDetails" style="display:none;">
                                <div class="col-6">
                                    <label>Multiplier</label>
                                    <input type="number" name="base_unit_multiplier" class="form-control"
                                        placeholder="Enter Time base Unit">
                                </div>
                                <div class="col-6">
                                    <label>Base Unit</label>
                                    <select name="base_unit_id" class="form-control">
                                        <option value="">Select Unit</option>
                                        @foreach($units as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->short_name }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
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
                                    <button type="button" class="btn btn-primary open-universal-modal"
                                        data-title="Add Manufacturerd" data-route="{{ route('post.car.manufacturer') }}"
                                        data-target-select=".car-manufacturer-select">
                                        <i data-feather="plus" class="feather-plus"></i>
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
                                    <button type="button" class="btn btn-primary open-universal-modal"
                                        data-title="Add Car Model" data-route="{{ route('post.car.model') }}"
                                        data-target-select=".car-model-select">
                                        <i data-feather="plus" class="feather-plus"></i>
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
                                    <button type="button" class="btn btn-primary open-universal-modal"
                                        data-title="Add Engine CC" data-route="{{ route('post.engine.cc') }}"
                                        data-target-select=".car-engine-select">
                                        <i data-feather="plus" class="feather-plus"></i>
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

                                    <button type="button" class="btn btn-primary open-universal-modal"
                                        data-title="Add Country" data-route="{{ route('post.car.country') }}"
                                        data-target-select=".car-country-select">
                                        <i data-feather="plus" class="feather-plus"></i>
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
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Part Number" data-route="{{ route('post.partnumber') }}"
                                            data-target-select=".part_number-select">
                                            <i data-feather="plus" class="feather-plus"></i>
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

    <!-- Universal Modal -->
    <div class="modal fade" id="universal-add-modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="universal-modal-title">Add Item</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="universal-form" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name:</label>
                            <input type="text" class="form-control" name="name" id="universal-name" required>
                        </div>
                        <div class="form-group" id="image-field" style="display: none;">
                            <label>Image:</label>
                            <input type="file" class="form-control" name="image" id="universal-image" accept="image/*">
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
            _token: '{{ csrf_token() }}'
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
            _token: '{{ csrf_token() }}',
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
    document.getElementById('baseDetails').style.display = this.checked ? "flex" : "none";
    });

    $("#Unit-form").off("submit").on("submit", function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    // Check if modal is actually visible
    if (!$('#Unit-add-modal').hasClass('show')) {
        console.log('Unit modal not visible, ignoring submit');
        return false;
    }

    let formData = new FormData(this);

    $.ajax({
        url: "{{ route('post.units') }}",
        type: "POST",
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
                // Dropdown me new option add kare
                $("#unit_parts").append(
                    `<option value="${res.unit.name}" selected>
                        ${res.unit.name}
                    </option>`
                );
                // Modal close kare
                $("#Unit-add-modal").modal("hide");

                // Form clear kare
                $("#Unit-form")[0].reset();

                // Success alert - only if modal was actually open
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: 'Unit added successfully'
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

$(document).ready(function () {

    function handleUnitChange(loadFromDB = false) {
        let selected = $('#unit_parts').find(':selected');
        let unitName = selected.data('name') || '';
        let baseUnit = selected.data('baseunit') || '';
        let multiplier = parseFloat(selected.data('multiplier')) || 0;

        let dbBasePrice   = $('#base_price_input').val();
        let dbTotalPrice  = $('#total_price_input').val();
        let dbSaleBase    = $('#sale_base_price').val();
        let dbSaleTotal   = $('#total_sale_price').val();

        if (unitName === '') {
            // No unit selected
            $('#unit-info, #sale-price-info').hide();
            return;
        }

        // Show sections
        $('#unit-info, #sale-price-info').show();
        $('#unit-name').text(unitName);
        $('#sale-unit-name').text(unitName);

        if (baseUnit && multiplier > 0) {
            // Show multiplier info
            $('#multiplier-text, #sale-multiplier-text').show().text(`${multiplier} ${baseUnit}`);

            // Show base price inputs
            $('#base_price_input, #sale_base_price').show();
            $('#total_price_input, #total_sale_price').show();

            // Restore DB values only on page load
            if (loadFromDB) {
                $('#base_price_input').val(dbBasePrice);
                $('#total_price_input').val(dbTotalPrice);
                $('#sale_base_price').val(dbSaleBase);
                $('#total_sale_price').val(dbSaleTotal);
            }

            // Bind calculations
            $('#base_price_input').off('input').on('input', function () {
                let val = parseFloat($(this).val()) || 0;
                $('#total_price_input').val(val * multiplier);
            });
            $('#total_price_input').off('input').on('input', function () {
                let val = parseFloat($(this).val()) || 0;
                $('#base_price_input').val(val / multiplier);
            });

            $('#sale_base_price').off('input').on('input', function () {
                let val = parseFloat($(this).val()) || 0;
                $('#total_sale_price').val(val * multiplier);
            });
            $('#total_sale_price').off('input').on('input', function () {
                let val = parseFloat($(this).val()) || 0;
                $('#sale_base_price').val(val / multiplier);
            });
        } else {
            // No base unit, hide base inputs, show only total
            $('#base_price_input, #sale_base_price, #multiplier-text, #sale-multiplier-text').hide();
            $('#total_price_input, #total_sale_price').show();
            $('#total_price_input').attr('placeholder', `${unitName} Price`);
            $('#total_sale_price').attr('placeholder', `${unitName} Sale Price`);

            // Unbind base price events
            $('#base_price_input, #sale_base_price').off('input');
        }
    }

    // On dropdown change
    $('#unit_parts').on('change', function () {
        handleUnitChange(false);
    });

    // Trigger on page load for edit form
    if ($('#unit_parts').val()) {
        handleUnitChange(true);
    }

});




</script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productForm', (initialType) => ({
            selectedType: initialType || '',
            init() {
                this.$watch('selectedType', (value) => {
                    this.updateFieldsVisibility(value);
                });
                // Trigger initial update
                this.updateFieldsVisibility(this.selectedType);
            },
            selectType(type) {
                this.selectedType = type;
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
        // Universal Modal
     let currentTargetSelect = null;

                $(document).on('click', '.open-universal-modal', function () {
                    const title = $(this).data('title');
                    const route = $(this).data('route');

                    currentTargetSelect = $(this).data('target-select');

                    $('#universal-modal-title').text(title);
                    $('#universal-form').attr('action', route);

                    $('#universal-name').val('');
                    $('#universal-image').val('');
                    $('#image-field').toggle(title === 'Add Category' || title === 'Add Brand');

                    $('#universal-add-modal').modal('show');
                });

                $('#universal-form').off('submit').on('submit', function (e) {
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

                    $.ajax({
                        url: formAction,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (res) {
                            if (!res || !res.success) {
                                // handle server error / validation returned by modal create endpoint
                                console.error('Modal save failed', res);
                                if (res && res.message) {
                                    toastr.error(res.message);
                                }
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
                            $(currentTargetSelect).each(function () {

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
                                if ($(this).hasClass('select2-hidden-accessible') && $(this).data('select2')) {
                                    // Append via Select2-friendly way then trigger change
                                    // (some setups require re-init, but this pattern is widely compatible)
                                    const $sel = $(this);
                                    // Ensure the option is present in DOM then tell select2 to update its value
                                    $sel.append(new Option(text, val, true, true)).val(val).trigger('change.select2');
                                }
                                // Bootstrap selectpicker
                                if ($(this).hasClass('selectpicker') && typeof $(this).selectpicker === 'function') {
                                    // Append option, refresh plugin, then set value
                                    $(this).append(new Option(text, val));
                                    $(this).selectpicker('refresh');
                                    $(this).selectpicker('val', val);
                                }

                            });

                            // close modal
                            $('#universal-add-modal').modal('hide');
                        },
                        error: function (xhr) {
                            console.error('AJAX error', xhr.responseText || xhr);
                            // Optionally show server validation messages inside modal
                        }
                    });
                });

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
        // Fallback for initial type selection if Alpine doesn't trigger
        const initialType = '{{ $item->type ?? old("type") ?? "" }}';
        if (initialType) {
            setTimeout(() => {
                const typeBox = $(`.type-box:contains("${initialType.charAt(0).toUpperCase() + initialType.slice(1)}")`);
                if (typeBox.length) {
                    typeBox.addClass('selected');
                }
                // Force show fields
                $('.common-fields, .media-fields, .checkbox-fields').addClass('active');
                // Show type-specific fields
                const typeFieldClass = `.${initialType}-fields`;
                if ($(typeFieldClass).length) {
                    $(typeFieldClass).addClass('active');
                }
            }, 100);
        }
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
                // Override selectType to include filtering
                const originalSelectType = instance.selectType;
                instance.selectType = function(type) {
                    originalSelectType.call(this, type);
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
            // Create new productForm if it doesn't exist
            Alpine.data('productForm', (initialType) => ({
                selectedType: localStorage.getItem('selectedType') || initialType || '',
                selectType(type) {
                    this.selectedType = type;
                    localStorage.setItem('selectedType', type);
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
                    <td colspan="4" class="text-center">No items found for this type.</td>
                </tr>
            `);
            return;
        }

        const duplicateRouteBase = '{{ route("item.duplicate", ":id") }}';
        
        items.forEach(function(item) {
            const csrfToken = $('input[name="_token"]').val();
            
            // Build item details HTML
            const itemDetails = `
                <div class="small">
                    <div> ${item.part_number || '-'}</div>
                    <div> ${item.category_name || '-'}</div>
                    <div> ${item.company_name || '-'}</div>
                    <div> ${item.quality_name || '-'}</div>
                </div>
            `;

            const duplicateUrl = duplicateRouteBase.replace(':id', item.id);

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
                    <td>${item.user_name}</td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="${item.show_url}">
                                        <i data-feather="eye" class="me-1"></i> View
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="${item.edit_url}">
                                        <i data-feather="edit" class="me-1"></i> Edit
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" onclick="confirmDelete('delete-form-${item.id}')"
                                        class="p-2">
                                        <i data-feather="trash-2" class="feather-trash-2"></i> Delete
                                    </a>
                                    <form id="delete-form-${item.id}"
                                        action="${item.delete_url}" method="POST"
                                        style="display: none;">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <input type="hidden" name="_method" value="DELETE">
                                    </form>
                                </li>
                                <hr>
                                <li>
                                    <a class="dropdown-item text-primary" href="${duplicateUrl}">
                                        <i data-feather="copy" class="me-1"></i> Duplicate
                                    </a>
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
</script>
@endpush
