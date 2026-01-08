@extends('layouts.app')
@section('title', 'Create Product')
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
        font-weight: bold !important;
        font-size: 15px !important;
        text-transform: uppercase !important;
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
    /* Responsive adjustments for create.blade.php */
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
                <h2 class="fw-bold">Create Product  </h2>
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
            <form method="POST" action="{{ route('all.items.store') }}" enctype="multipart/form-data" id="mainItemForm">
                @csrf
                <input type="hidden" name="user_id" value="{{ auth()->check() ? auth()->user()->id : '' }}">
                <div class="container" x-data="productForm()">
                    <!-- 4 Clickable Type Boxes -->
                    <div class="row mb-5 g-3">
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" 
                                 :class="{ 'selected': selectedType === 'parts' }"
                                 data-type="parts"
                                @click="selectType('parts')">
                                <i class="ti ti-tool fs-1 d-block mb-2"></i>
                                Parts
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" 
                                 :class="{ 'selected': selectedType === 'filters' }"
                                 data-type="filters"
                                 @click="selectType('filters')"
                                 onclick="selectItemType('filters'); return false;">
                                <i class="ti ti-filter fs-1 d-block mb-2"></i>
                                Filters
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" 
                                 :class="{ 'selected': selectedType === 'breakpad' }"
                                 data-type="breakpad"
                                 @click="selectType('breakpad')"
                                 onclick="selectItemType('breakpad'); return false;">
                                <i class="ti ti-disc fs-1 d-block mb-2"></i>
                                Break Pad
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" 
                                 :class="{ 'selected': selectedType === 'oil' }"
                                 data-type="oil"
                                 @click="selectType('oil')"
                                 onclick="selectItemType('oil'); return false;">
                                <i class="ti ti-droplet fs-1 d-block mb-2"></i>
                                Oil
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" 
                                 :class="{ 'selected': selectedType === 'battery' }"
                                 data-type="battery"
                                 @click="selectType('battery')"
                                 onclick="selectItemType('battery'); return false;">
                                <i class="ti ti-battery fs-1 d-block mb-2"></i>
                                Battery
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" 
                                 :class="{ 'selected': selectedType === 'scrap' }"
                                 data-type="scrap"
                                 @click="selectType('scrap')"
                                 onclick="selectItemType('scrap'); return false;">
                                <i class="ti ti-trash fs-1 d-block mb-2"></i>
                                Scrap
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" 
                                 :class="{ 'selected': selectedType === 'services' }"
                                 data-type="services"
                                 @click="selectType('services')"
                                 onclick="selectItemType('services'); return false;">
                                <i class="ti ti-tools fs-1 d-block mb-2"></i>
                                Services
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="type" x-model="selectedType">
                    <!-- Hidden inputs to ensure quality_id and technology are submitted even when fields are hidden -->
                    <input type="hidden" name="quality_id" id="hidden_quality_id" value="">
                    <input type="hidden" name="technology" id="hidden_technology" value="">
                    <div class="row" id="itemFormsContainer">
                        <!-- COMMON FIELDS (Visible after type selection) -->
                        <div class="col-md-12 field-group" :class="{ 'active': selectedType }">
                            <h4 class="mt-3">Item Info:</h4>
                            <div class="row mt-4">
                                <!-- Barcode -->
                                <div class="col-md-4">
                                    <label for="itemBarCode">Product Bar Code:</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('bar_code') is-invalid @enderror"
                                            name="bar_code" id="itemBarCode" value="{{ old('bar_code') }}" required />
                                        <button type="button" class="btn btn-primary generate-code-btn"
                                            id="generateCodeBtn">
                                            <i data-feather="refresh-cw"></i>
                                        </button>
                                    </div>

                                    @error('bar_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                         <div class="col-md-4 mt-3" x-show="selectedType === 'parts' || selectedType === 'filters' || selectedType === 'breakpad' ">
                            <label for="part_number_id">Part Number:</label>
                            <div class="input-group inputswidth">
                                <select
                                    class="form-control part_number-select searchable-select @error('part_number_id') is-invalid @enderror"
                                    name="part_number_id" id="part_number_id">
                                    <option value="">Select Part Number</option>
                                    @foreach ($partnumbers as $partnumber)
                                    <option value="{{ $partnumber->id??'' }}" 
                                        data-type="{{ $partnumber->type ?? '' }}">
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
                                    <button type="button" class="btn btn-secondary open-universal-modal"
                                        data-mode="edit" data-title="Edit Part Number"
                                        data-fetch-route="{{ route('show.partnumber', ':id') }}"
                                        data-update-route="{{ route('update.partnumber', ':id') }}"
                                        data-delete-route="{{ route('destory.partnumber', ':id') }}"
                                        data-target-select=".part_number-select">
                                        <i data-feather="edit"></i>
                                    </button>
                            </div>
                            <!-- Part Number Items Count -->
                            <div class="mt-2" id="partNumberCountContainer" style="display: none;">
                                <small class="text-muted">
                                    Items using this part number: 
                                    <span id="partNumberCount" class="fw-bold text-primary" style="cursor: pointer; text-decoration: underline;" title="Double-click to view items">0</span>
                                </small>
                            </div>
                            @error('part_number_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                         <!-- Product Name -->
                         <div class="col-md-4"
                                    x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil'|| selectedType === 'scrap'">
                                    <label for="itemname">Product Name:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control name-select searchable-select @error('p_id') is-invalid @enderror"
                                            name="p_id" id="product_name_item">
                                            <option value="">Select Product Name</option>
                                            @foreach ($product as $item)
                                            <option value="{{ $item->id }}" 
                                                data-type="{{ $item->type ?? '' }}"
                                                {{ old('p_id')==$item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Product" data-mode="add"
                                            data-route="{{ route('post.product') }}" data-target-select=".name-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
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
                                <div class="col-md-4 " x-show="selectedType === 'parts' || selectedType === 'oil' || selectedType === 'scrap' || selectedType === 'services' || selectedType === 'filters' || selectedType === 'breakpad' ">
                                    <label for="category">Category:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control category-select searchable-select @error('category_id') is-invalid @enderror"
                                            name="category_id" id="category">
                                            <option value="">Select Category</option>
                                            @foreach ($Categories as $category)
                                            <option value="{{ $category->id }}" 
                                                data-type="{{ $category->type ?? '' }}"
                                                {{ old('category_id')==$category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Category" data-mode="add"
                                            data-route="{{ route('post.item.category') }}"
                                            data-target-select=".category-select" data-has-image="1">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
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
                                <!-- Business Location -->
                                <div class="col-md-4 d-none">
                                    <label for="business_location">Business Location:</label>
                                    <input type="text"
                                        class="form-control @error('business_location') is-invalid @enderror"
                                        name="business_location" id="business_location"
                                        value="{{ old('business_location') }}" />

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
                                            <option value="{{ $group->id }}" {{ old('group')==$group->id ?
                                                'selected' : '' }}>
                                                {{ $group->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-mode="add" data-title="Add group"
                                            data-route="{{ route('post.groups') }}" data-target-select=".group-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>

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
                                <div class="col-md-4"
                                    x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <label for="company_parts">Company:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control company-select searchable-select @error('company_id') is-invalid @enderror"
                                            name="company_id" id="company_parts">
                                            <option value="">Select Company</option>
                                            @foreach ($Companies as $company)
                                            <option value="{{ $company->id }}" 
                                                data-type="{{ $company->type ?? '' }}"
                                                {{ old('company_id')==$company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-mode="add" data-title="Add Company"
                                            data-route="{{ route('post.companies') }}"
                                            data-target-select=".company-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
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
                                <!-- Series/Technology -->
                                <div class="col-md-4 mt-3"  x-show=" selectedType === 'battery' || selectedType === 'oil'">
                                    <label for="technology_select">
                                        <b x-show="selectedType === 'oil'">Technology:</b>
                                        <b x-show="selectedType == 'battery'">Series:</b>
                                    </label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control technology-select searchable-select @error('technology') is-invalid @enderror"
                                            name="technology" id="technology_select">
                                            <option value="">Select</option>
                                            @foreach ($technologies as $tech)
                                            <option value="{{ $tech->id }}" 
                                                data-type="{{ $tech->type ?? '' }}"
                                                {{ old('technology')==$tech->id ? 'selected' : '' }}>
                                                {{ $tech->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            x-bind:data-title="selectedType === 'parts' ? 'Add Technology' : 'Add Series'"
                                            data-mode="add"
                                            data-route="{{ route('post.technology') }}"
                                            data-target-select=".technology-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit"
                                            x-bind:data-title="selectedType === 'parts' ? 'Edit Technology' : 'Edit Series'"
                                            data-fetch-route="{{ route('show.technology', ':id') }}"
                                            data-update-route="{{ route('update.technology', ':id') }}"
                                            data-delete-route="{{ route('destory.technology', ':id') }}"
                                            data-target-select=".technology-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>

                                    @error('technology')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <!-- PARTS FIELDS -->

                        <div class="field-group" :class="{ 'active': selectedType === 'parts' }">
                            <div class="row  p-3 mt-4">

                                <div class="col-md-4">
                                    <label for="quality">Quality:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control quality-select searchable-select @error('quality_id') is-invalid @enderror"
                                            name="quality_id" id="quality">
                                            <option value="">Select Quality</option>
                                            @foreach ($qualities as $item)
                                            <option value="{{ $item->id }}" 
                                                data-type="{{ $item->type ?? '' }}"
                                                {{ old('quality_id')==$item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Quality" data-mode="add"
                                            data-route="{{ route('post.qualities') }}"
                                            data-target-select=".quality-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
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

                        <!-- FILTERS FIELDS -->
                        <div class="field-group" :class="{ 'active': selectedType === 'filters' }">
                            <div class="row  p-3 mt-4">

                                <div class="col-md-4">
                                    <label for="quality_filters">Quality:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control quality-select searchable-select @error('quality_id') is-invalid @enderror"
                                            name="quality_id" id="quality_filters">
                                            <option value="">Select Quality</option>
                                            @foreach ($qualities as $item)
                                            <option value="{{ $item->id }}" 
                                                data-type="{{ $item->type ?? '' }}"
                                                x-show="!selectedType || '{{ $item->type ?? '' }}' === selectedType || '{{ $item->type ?? '' }}' === ''"
                                                {{ old('quality_id')==$item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Quality" data-mode="add"
                                            data-route="{{ route('post.qualities') }}"
                                            data-target-select=".quality-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
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

                        <!-- BREAK PAD FIELDS -->
                        <div class="field-group" :class="{ 'active': selectedType === 'breakpad' }">
                            <div class="row  p-3 mt-4">

                                <div class="col-md-4">
                                    <label for="quality_breakpad">Quality:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control quality-select searchable-select @error('quality_id') is-invalid @enderror"
                                            name="quality_id" id="quality_breakpad">
                                            <option value="">Select Quality</option>
                                            @foreach ($qualities as $item)
                                            <option value="{{ $item->id }}" {{ old('quality_id')==$item->id ?
                                                'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Quality" data-mode="add"
                                            data-route="{{ route('post.qualities') }}"
                                            data-target-select=".quality-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
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

                        <!-- BATTERY FIELDS -->
                        <div class="field-group" :class="{ 'active': selectedType === 'battery' }">
                            <div class="row p-3 mt-4">
                                <div class="col-md-4">
                                    <label for="plates_scrap">Plates:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control plates-select searchable-select @error('plat_id') is-invalid @enderror"
                                            name="plat_id" id="plates_scrap">
                                            <option value="">Select Plate</option>
                                            @foreach ($platos as $plate)
                                            <option value="{{ $plate->id }}" {{ old('plat_id')==$plate->id ?
                                                'selected' : '' }}>
                                                {{ $plate->name }}PL
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Plate" data-mode="add"
                                            data-route="{{ route('post.platos') }}" data-target-select=".plates-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Plates"
                                            data-fetch-route="{{ route('show.plate', ':id') }}"
                                            data-update-route="{{ route('update.plate', ':id') }}"
                                            data-delete-route="{{ route('destory.plate', ':id') }}"
                                            data-target-select=".plates-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('plat_id')
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
                                            <option value="{{ $ampere->id }}" {{ old('amphors')==$ampere->id ?
                                                'selected' : '' }}>
                                                {{ $ampere->name }}AH
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Amperes" data-mode="add"
                                            data-route="{{ route('post.amphors') }}"
                                            data-target-select=".amperes-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
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
                                            <option value="{{ $volt->id }}" {{ old('volt', $volt->name == 12 ? $volt->id
                                                : '') == $volt->id ? 'selected' : '' }}>
                                                {{ $volt->name }} V
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Volt" data-mode="add" data-route="{{ route('post.volts') }}"
                                            data-target-select=".volt-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
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
                                            <option value="{{ $cca->id }}" {{ old('cca')==$cca->id ? 'selected' : ''
                                                }}>
                                                {{ $cca->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add CCA" data-mode="add" data-route="{{ route('post.cca') }}"
                                            data-target-select=".cca-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>

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
                                            <option value="{{ $mpd->id }}" {{ old('minus_pole_direction', $mpd->name
                                                ==='L' ? $mpd->id : '') == $mpd->id ? 'selected' : '' }}>
                                                {{ $mpd->name }}
                                            </option>
                                            @endforeach
                                        </select>

                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Minus Pole Direction" data-mode="add"
                                            data-route="{{ route('post.minuspool') }}"
                                            data-target-select=".minus-pole-direction-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
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
                                            <option value="{{ $warrenty->id }}" {{ old('warrenty')==$warrenty->id ?
                                                'selected' : '' }}>
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
                                            <option value="{{ $made->id }}" {{ old('made_in', $made->name ==='Pakistan'
                                                ? $made->id : '') == $made->id ? 'selected' : '' }}>
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
                                        name="battery_code" id="battery_code" value="{{ old('battery_code') }}" />
                                    @error('battery_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 d-none">
                                    <label for="serial_number_battery">Serial Number:</label>
                                    <input type="text" class="form-control @error('serial_number') is-invalid @enderror"
                                        name="serial_number" id="serial_number_battery"
                                        value="{{ old('serial_number') }}" />
                                    @error('serial_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                            </div>
                        </div>

                        <!-- OIL FIELDS -->
                        <div class="field-group" :class="{ 'active': selectedType === 'oil' }">
                            <div class="row p-3 mt-4">
                                <div class="col-md-4">
                                    <label for="grade_select">Grade:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control grade-select searchable-select @error('grade') is-invalid @enderror"
                                            name="grade" id="grade_select">
                                            <option value="">Select Grade</option>
                                            @foreach ($grades as $grade)
                                            <option value="{{ $grade->id }}" {{ old('grade')==$grade->id ?
                                                'selected' : '' }}>
                                                {{ $grade->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Grade" data-mode="add"
                                            data-route="{{ route('post.grade') }}" data-target-select=".grade-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
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
                                            <option value="{{ $brand->id }}" {{ old('brand')==$brand->id ?
                                                'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Brand" data-mode="add"
                                            data-route="{{ route('post.item.brand') }}"
                                            data-target-select=".brand-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Brand"
                                            data-fetch-route="{{ route('show.grade', ':id') }}"
                                            data-update-route="{{ route('update.grade', ':id') }}"
                                            data-delete-route="{{ route('destory.grade', ':id') }}" data-has-image="1"
                                            data-target-select=".grade-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('brand')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="mileage_oil">Mileage:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control mileage-select searchable-select @error('mileage') is-invalid @enderror"
                                            name="mileage" id="mileage_oil">
                                            <option value="">Select Mileage</option>
                                            @foreach ($milleages as $milleage)
                                            <option value="{{ $milleage->id }}" {{ old('mileage')==$milleage->id ?
                                                'selected' : '' }}>
                                                {{ $milleage->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Mileage" data-mode="add"
                                            data-route="{{ route('post.item.mileage') }}"
                                            data-target-select=".mileage-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
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
                                            <option value="{{ $level->id }}" {{ old('level')==$level->id ?
                                                'selected' : '' }}>
                                                {{ $level->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Level" data-mode="add"
                                            data-route="{{ route('post.levels') }}" data-target-select=".level-select">
                                            <i data-feather="plus"></i>
                                        </button>
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
                                            <option value="{{ $formula->id }}" {{ old('formulas')==$formula->id ?
                                                'selected' : '' }}>
                                                {{ $formula->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Formula" data-mode="add"
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
                                        value="{{ old('serial_number') }}" />
                                    @error('serial_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- SCRAP FIELDS -->
                        <div class="field-group" :class="{ 'active': selectedType === 'scrap' }">
                            <div class="row p-3 mt-4">
                                {{-- <div class="col-md-4">
                                    <label for="plates_scrap">Plates:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control plates-select searchable-select @error('plato') is-invalid @enderror"
                                            name="plato" id="plates_scrap">
                                            <option value="">Select Plate</option>
                                            @foreach ($platos as $plate)
                                            <option value="{{ $plate->id }}" {{ old('plato')==$plate->id ? 'selected' :
                                                '' }}>
                                {{ $plate->name }}
                                </option>
                                @endforeach
                                </select>
                                <button type="button" class="btn btn-primary open-universal-modal"
                                    data-title="Add Plate" data-mode="add" data-route="{{ route('post.platos') }}"
                                    data-target-select=".plates-select">
                                    <i data-feather="plus" class="feather-plus"></i>
                                </button>
                            </div>
                            @error('plato')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div> --}}
                        <div class="col-md-4">
                            <label for="battery_size">Battery Size:</label>
                            <input type="text" class="form-control @error('battery_size') is-invalid @enderror"
                                name="battery_size" id="battery_size" value="{{ old('battery_size') }}" />
                            @error('battery_size') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="field-group" :class="{ 'active': selectedType === 'services' }">
                    <div class="row p-3 mt-4">
                        <div class="col-md-4">
                            <label for="Services_scrap">Services:</label>
                            <div class="input-group inputswidth">
                                <select
                                    class="form-control Services-select searchable-select @error('services') is-invalid @enderror"
                                    name="services" id="Services_scrap">
                                    <option value="">Select Services</option>
                                    @foreach ($services as $service)
                                    <option value="{{ $service->id }}" {{ old('services')==$service->id ?
                                                'selected' :
                                                '' }}>
                                        {{ $service->name }}
                                    </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-primary open-universal-modal"
                                    data-title="Add Services" data-mode="add" data-route="{{ route('post.services') }}"
                                    data-target-select=".Services-select">
                                    <i data-feather="plus" class="feather-plus"></i>
                                </button>
                                <button type="button" class="btn btn-secondary open-universal-modal" data-mode="edit"
                                    data-title="Edit Services" data-fetch-route="{{ route('show.service', ':id') }}"
                                    data-update-route="{{ route('update.service', ':id') }}"
                                    data-delete-route="{{ route('destory.service', ':id') }}"
                                    data-target-select=".Services-select">
                                    <i data-feather="edit"></i>
                                </button>
                            </div>
                            @error('services')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <!-- COMMON MEDIA & DESCRIPTION -->
                <div class="field-group" :class="{ 'active': selectedType }">
                    <div class="row mt-4">
                        <div class="col-md-6"
                            x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil'|| selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                            <label for="unit_parts">Unit:</label>
                            <div class="input-group inputswidth">
                                <select
                                    class="form-control pro_unit-select searchable-select @error('unit') is-invalid @enderror"
                                    name="unit" id="unit_parts">
                                    <option value="">Select Unit</option>
                                    @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" data-name="{{ $unit->name }}"
                                        data-baseunit="{{ $unit->baseUnit->name ?? '' }}"
                                        data-multiplier="{{ $unit->base_unit_multiplier ?? '' }}">
                                        {{ $unit->name }}
                                        @if ($unit->base_unit_id)
                                        ( {{ $unit->base_unit_multiplier }}-{{ $unit->baseUnit->name }} )
                                        @endif
                                    </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-mode="add"
                                    data-bs-target="#Unit-add-modal">
                                    <i data-feather="plus" class="feather-plus"></i>
                                </button>
                                <button type="button" class="btn btn-secondary" id="editUnitBtn">
                                    <i data-feather="edit"></i>
                                </button>

                            </div>
                            <div class="input-group align-items-center gap-2" id="unit-info"
                                style="display:none; margin-top:10px;">
                                <span id="unit-name" class="fw-bold"></span>
                                <span class="equal-sign">=</span>
                                <input type="text" id="total_can_price" name="total_price"
                                    class="form-control form-control-sm" style="width:120px;" placeholder="Cost Price">
                                {{-- <span class="equal-sign">=</span> --}}
                                <span id="multiplier-text" class="fw-bold"></span>
                                {{-- <span class="multiply-sign">×</span> --}}
                                <input type="number" id="base_price" name="price_per_unit"
                                    class="form-control form-control-sm" placeholder="Price per Unit"
                                    style="width:100px;">
                            </div>
                            @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 "
                            x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil'|| selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                            <label for="sale_price_parts">Sale Price:</label>
                            <input type="number" class="form-control @error('sale_price') is-invalid @enderror"
                                name="sale_price" id="sale_price_parts" value="{{ old('sale_price') }}" hidden />
                            @error('sale_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="input-group align-items-center gap-2" id="sale-price-info"
                                style="display:none; margin-top:10px;">
                                <span id="sale-unit-name" class="fw-bold"></span>
                                <span class="sale-equal-sign">=</span>
                                <input type="text" id="total_sale_price" name="total_sale_price"
                                    class="form-control form-control-sm" style="width:120px;" placeholder=" Sale Price">

                                {{-- <span class="sale-equal-sign">=</span> --}}
                                <span id="sale-multiplier-text" class="fw-bold"></span>
                                {{-- <span class="sale-multiply-sign">×</span> --}}

                                <input type="number" id="sale_base_price" name="sale_price_per_base"
                                    class="form-control form-control-sm" placeholder="Sale per  Unit"
                                    style="width:100px;">
                            </div>
                        </div>
                        <!-- Thumbnail -->
                        <!-- Thumbnail (Single Image) -->
                        <div class="col-md-6 mt-3">
                            <label for="imageInput" class="form-label fw-bold">Product Image (Thumbnail)</label>

                            <div class="d-flex gap-2">
                                <!-- Using label to trigger file input - works on all mobile browsers (Chrome, Firefox, etc.) -->
                                <label for="imageInput" class="btn btn-primary flex-fill mb-0" style="cursor: pointer;">
                                    <i data-feather="camera"></i> Take or Choose Photo
                                </label>
                                <input type="file" id="imageInput" name="image" accept="image/*"
                                    class="d-none @error('image') is-invalid @enderror">
                            </div>

                            <!-- Preview -->
                            <div id="previewContainer" class="text-center mt-3" style="display: none;">
                                <div class="position-relative d-inline-block">
                                    <img id="imagePreview" src="https://pdis.co.kr/img/image.jpg" alt="Preview"
                                        class="img-fluid rounded border shadow-sm"
                                        style="max-height: 220px; object-fit: cover;">
                                    <button type="button" id="removeBtn" class="btn btn-danger btn-sm position-absolute"
                                        style="top: 8px; right: 8px;">
                                        <i data-feather="x"></i>
                                    </button>
                                </div>
                            </div>

                            @error('image')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Multiple Images -->
                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold">Product Images (Multiple)</label>
                            <div class="d-flex gap-2">
                                <!-- File input for multiple images - using label for better mobile support -->
                                <label for="imagesInput" class="btn btn-outline-primary flex-fill mb-0" style="cursor: pointer;">
                                    <i data-feather="image"></i> Add More Photos
                                </label>
                                <input type="file" id="imagesInput" name="images[]" accept="image/*" multiple
                                    class="d-none @error('images.*') is-invalid @enderror">
                            </div>

                            <!-- Multiple Images Preview -->
                            <div id="imagesPreviewContainer" class="mt-3" style="display: none;">
                                <div class="d-flex flex-wrap gap-3" id="imagesPreview"></div>
                            </div>

                            @error('images.*')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mt-3"
                            x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil'|| selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                            <label for="low_stock_parts">Low Stock:</label>
                            <select class="form-control searchable-select @error('l_stock') is-invalid @enderror"
                                name="l_stock" id="low_stock_parts">
                                <option value="">Select Low Stock</option>
                                @for($i = 1; $i <= 1000; $i++)
                                    <option value="{{ $i }}" {{ old('l_stock') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('l_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mt-3"
                            x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil'|| selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                            <label for="maintain_stock_parts">Maintain Stock:</label>
                            <select class="form-control searchable-select @error('m_stock') is-invalid @enderror"
                                name="m_stock" id="maintain_stock_parts">
                                <option value="">Select Maintain Stock</option>
                                @for($i = 1; $i <= 1000; $i++)
                                    <option value="{{ $i }}" {{ old('m_stock') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('m_stock') <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mt-3"
                            x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil'|| selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                            <label for="on_hand">Opening Stock:</label>
                            <select class="form-control searchable-select @error('on_hand') is-invalid @enderror"
                                name="on_hand" id="on_hand">
                                <option value="">Select Opening Stock</option>
                                @for($i = 1; $i <= 1000; $i++)
                                    <option value="{{ $i }}" {{ old('on_hand') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('on_hand') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <!-- Brochure -->
                        <div class="col-md-4 mt-3">
                            <label for="p_brochure">Product Brochure (URL):</label>
                            <input type="url" class="form-control @error('p_brochure') is-invalid @enderror"
                                id="p_brochure" name="p_brochure" value="{{ old('p_brochure') }}" />
                            @error('p_brochure') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <!-- Weight -->
                        <div class="col-md-4 mt-3"
                            x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil'|| selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                            <label for="weight">Weight:</label>
                            <input type="number" step="0.01" class="form-control @error('weight_for_delivery') is-invalid @enderror"
                                name="weight_for_delivery" id="weight" value="{{ old('weight_for_delivery') }}" placeholder="Enter weight" />
                            @error('weight_for_delivery') <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Weight Unit -->
                        <div class="col-md-4 mt-3"
                            x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil'|| selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                            <label for="weight_unit">Weight Unit:</label>
                            <select class="form-control searchable-select @error('weight_unit') is-invalid @enderror"
                                name="weight_unit" id="weight_unit">
                                <option value="">Select Weight Unit</option>
                                <optgroup label="Metric System (most used worldwide)">
                                    <option value="ml" {{ old('weight_unit') == 'ml' ? 'selected' : '' }}>MilliLiter (Ml)</option>
                                    <option value="mg" {{ old('weight_unit') == 'mg' ? 'selected' : '' }}>Milligram (mg)</option>
                                    <option value="g" {{ old('weight_unit') == 'g' ? 'selected' : '' }}>Gram (g)</option>
                                    <option value="kg" {{ old('weight_unit') == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                    <option value="quintal" {{ old('weight_unit') == 'quintal' ? 'selected' : '' }}>Quintal (100 kg)</option>
                                    <option value="tonne" {{ old('weight_unit') == 'tonne' ? 'selected' : '' }}>Metric Ton / Tonne (t) = 1000 kg</option>
                                </optgroup>
                                <optgroup label="Imperial / Other Systems">
                                    <option value="oz" {{ old('weight_unit') == 'oz' ? 'selected' : '' }}>Ounce (oz)</option>
                                    <option value="lb" {{ old('weight_unit') == 'lb' ? 'selected' : '' }}>Pound (lb)</option>
                                    <option value="stone" {{ old('weight_unit') == 'stone' ? 'selected' : '' }}>Stone (UK-specific)</option>
                                    <option value="ton_us" {{ old('weight_unit') == 'ton_us' ? 'selected' : '' }}>Ton (US)</option>
                                    <option value="ton_uk" {{ old('weight_unit') == 'ton_uk' ? 'selected' : '' }}>Ton (UK)</option>
                                </optgroup>
                            </select>
                            @error('weight_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mt-3">
                            <label for="vehical_id">Vehicle Type:</label>
                            <div class="input-group inputswidth">
                                <select class="form-control searchable-select @error('vehical_id') is-invalid @enderror"
                                    name="vehical_id" id="vehical_id">
                                    <option value="">Select Vehicle Type</option>
                                    @foreach ($Vehicals as $vehicle)
                                    <option value="{{ $vehicle->id }}">
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
                        {{-- PART NUMBER SELECT --}}

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
                                                <button type="button" class="btn btn-sm btn-primary editVehicleBtn"
                                                    data-part="{{ $car->v_part_number_id }}"
                                                    data-manufacturer="{{ $car->car_manufacturer }}"
                                                    data-model="{{ $car->car_model_name }}"
                                                    data-engine="{{ $car->engine_cc }}"
                                                    data-country="{{ $car->car_manufactured_country }}"
                                                    data-year-from="{{ $car->year_from }}"
                                                    data-year-to="{{ $car->year_to }}">
                                                    <i class="ti ti-pencil"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Checkboxes
                        <div class="col-md-12 mt-4 field-group" :class="{ 'active': selectedType }">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="section-box">
                                    <div class="form-check">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive"
                                            value="1" style="width: 20px; height:20px" {{
                                                    old('is_active', 1) ? 'checked' : '' }} />
                                        <label class="form-check-label ms-1 mt-1" for="isActive">Is
                                            Active</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="hidden" name="auto_deactive" value="0">
                                        <input class="form-check-input" type="checkbox" name="auto_deactive"
                                            id="autoDeactive" value="1" style="width: 20px; height:20px" {{
                                                    old('auto_deactive', 0) ? 'checked' : '' }} />
                                        <label class="form-check-label ms-1 mt-1" for="autoDeactive">Auto
                                            De-Active</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="hidden" name="is_dead" value="0">
                                        <input class="form-check-input" type="checkbox" name="is_dead" id="isDead"
                                            value="1" style="width: 20px; height:20px" {{
                                                    old('is_dead', 0) ? 'checked' : '' }} />
                                        <label class="form-check-label ms-1 mt-1" for="isDead">Is Dead
                                            Item</label>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        <div class="col-md-12 mt-3">
                            <label for="content" class="form-label fw-bold">Short Description</label>
                            <input name="short_disc" class="form-control" value="{{ old('short_disc') }}" />
                        </div>
                        <!-- Description -->
                        <div class="col-md-12 mt-3">
                            <label for="content" class="form-label fw-bold">Long Description</label>
                            <textarea id="summernote" name="pro_dis"
                                class="form-control">{{ old('pro_dis') }}</textarea>
                        </div>
                    </div>
                </div>
        </div>

        <!-- Submit Buttons -->
        <div class="page-btn d-flex justify-content-end mt-4">
            <a href="{{ route('all.items') }}" class="btn btn-secondary me-2">Cancel</a>
            {{-- <button type="submit" name="action" value="save" class="btn btn-primary">
                            Save
                        </button> --}}
            <button type="submit" name="action" value="save_new" class="btn btn-success">
                Save & New
            </button>
        </div>
    </div>
    </form>
    <h4 id="itemsTableTitle">Last 5 Created Items</h4>
    <div class="table-responsive">
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
                        <img src="{{ asset($item->image ?? 'assets/img/media/default.png') }}" width="70" height="70"
                            class="rounded item-image" style="cursor:pointer;" data-bs-toggle="modal"
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
                            <button class="btn btn-primary  dropdown-toggle" type="button" data-bs-toggle="dropdown">
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
                                    <a href="javascript:void(0)" onclick="confirmDelete('delete-form-{{ $item->id }}')"
                                        class="p-2">
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
                    <button type="button" class="btn btn-danger d-none" id="deleteUnitBtn">
                        Delete
                    </button>

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
                                <div class="input-group inputswidth">
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
                                        data-title="Add Manufacturerd" data-mode="add"
                                        data-route="{{ route('post.car.manufacturer') }}"
                                        data-target-select=".car-manufacturer-select">
                                        <i data-feather="plus" class="feather-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-secondary open-universal-modal"
                                        data-mode="edit" data-title="Edit Manufacturerd"
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
                                <div class="input-group inputswidth">
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
                                <div class="input-group inputswidth">
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
                                <div class="input-group inputswidth">
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
                            <div class="col-md-6 mt-3 d-none">
                                <label for="part_number">Part Number: <span class="text-danger">*</span></label>
                                <div class="input-group inputswidth">
                                    <select
                                        class="form-control part_number-select searchable-select @error('v_part_number_id') is-invalid @enderror"
                                        name="v_part_number_id" id="part_number" required>
                                        <option value="">Select Part Number</option>
                                        @foreach ($partnumbers as $item)
                                        <option value="{{ $item->id }}" {{ old('v_part_number_id')==$item->id ?
                                            'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                        @endforeach
                                    </select>

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
<div class="modal fade" id="universal-add-modal" tabindex="-1" aria-labelledby="universal-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0; padding: 20px 25px; border-bottom: none;">
                <div class="d-flex align-items-center w-100">
                    <div class="me-3" style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="ti ti-plus" style="font-size: 20px;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-0 fw-bold" id="universal-modal-title" style="color: white; font-size: 20px;">ADD NEW ENTRY</h4>
                        <small class="text-white-50" id="universal-modal-subtitle" style="font-size: 12px;">SMART ASSET REGISTRY</small>
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
                               placeholder="Search or Enter Name..."
                               style="border-radius: 8px; border: 2px solid #e9ecef; padding: 12px 15px; font-size: 15px; transition: all 0.3s;"
                               onfocus="this.style.borderColor='#ff6b35'; this.style.boxShadow='0 0 0 0.2rem rgba(255, 107, 53, 0.25)'"
                               onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'">
                        <div class="invalid-feedback" id="universal-name-error"></div>
                    </div>
                    <div class="form-group mb-4" id="universal-type-selection" style="display: none;">
                        <label class="form-label fw-semibold mb-3" style="color: #495057; font-size: 14px;">
                            <i class="ti ti-category me-2 text-primary"></i>Select Categories
                        </label>
                        <div class="row g-2" style="max-height: 200px; overflow-y: auto; padding: 10px; background: #f8f9fa; border-radius: 8px;">
                            <div class="col-md-6 col-6">
                                <label class="type-checkbox-label" style="display: flex; align-items: center; padding: 10px; background: white; border-radius: 6px; cursor: pointer; margin-bottom: 8px; transition: all 0.3s; border: 2px solid #e9ecef;" onmouseover="this.style.borderColor='#ff6b35'" onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#e9ecef'">
                                    <input type="checkbox" name="type_checkbox[]" value="parts" class="universal-type-checkbox" style="margin-right: 8px; width: 18px; height: 18px; cursor: pointer;">
                                    <span style="font-weight: 500; color: #495057;">PARTS</span>
                                </label>
                        </div>
                            <div class="col-md-6 col-6">
                                <label class="type-checkbox-label" style="display: flex; align-items: center; padding: 10px; background: white; border-radius: 6px; cursor: pointer; margin-bottom: 8px; transition: all 0.3s; border: 2px solid #e9ecef;" onmouseover="this.style.borderColor='#ff6b35'" onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#e9ecef'">
                                    <input type="checkbox" name="type_checkbox[]" value="filters" class="universal-type-checkbox" style="margin-right: 8px; width: 18px; height: 18px; cursor: pointer;">
                                    <span style="font-weight: 500; color: #495057;">FILTERS</span>
                                </label>
                    </div>
                            <div class="col-md-6 col-6">
                                <label class="type-checkbox-label" style="display: flex; align-items: center; padding: 10px; background: white; border-radius: 6px; cursor: pointer; margin-bottom: 8px; transition: all 0.3s; border: 2px solid #e9ecef;" onmouseover="this.style.borderColor='#ff6b35'" onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#e9ecef'">
                                    <input type="checkbox" name="type_checkbox[]" value="breakpad" class="universal-type-checkbox" style="margin-right: 8px; width: 18px; height: 18px; cursor: pointer;">
                                    <span style="font-weight: 500; color: #495057;">BREAK PAD</span>
                                </label>
                </div>
                            <div class="col-md-6 col-6">
                                <label class="type-checkbox-label" style="display: flex; align-items: center; padding: 10px; background: white; border-radius: 6px; cursor: pointer; margin-bottom: 8px; transition: all 0.3s; border: 2px solid #e9ecef;" onmouseover="this.style.borderColor='#ff6b35'" onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#e9ecef'">
                                    <input type="checkbox" name="type_checkbox[]" value="oil" class="universal-type-checkbox" style="margin-right: 8px; width: 18px; height: 18px; cursor: pointer;">
                                    <span style="font-weight: 500; color: #495057;">OIL</span>
                                </label>
                            </div>
                            <div class="col-md-6 col-6">
                                <label class="type-checkbox-label" style="display: flex; align-items: center; padding: 10px; background: white; border-radius: 6px; cursor: pointer; margin-bottom: 8px; transition: all 0.3s; border: 2px solid #e9ecef;" onmouseover="this.style.borderColor='#ff6b35'" onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#e9ecef'">
                                    <input type="checkbox" name="type_checkbox[]" value="battery" class="universal-type-checkbox" style="margin-right: 8px; width: 18px; height: 18px; cursor: pointer;">
                                    <span style="font-weight: 500; color: #495057;">BATTERY</span>
                                </label>
                            </div>
                            <div class="col-md-6 col-6">
                                <label class="type-checkbox-label" style="display: flex; align-items: center; padding: 10px; background: white; border-radius: 6px; cursor: pointer; margin-bottom: 8px; transition: all 0.3s; border: 2px solid #e9ecef;" onmouseover="this.style.borderColor='#ff6b35'" onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#e9ecef'">
                                    <input type="checkbox" name="type_checkbox[]" value="scrap" class="universal-type-checkbox" style="margin-right: 8px; width: 18px; height: 18px; cursor: pointer;">
                                    <span style="font-weight: 500; color: #495057;">SCRAP</span>
                                </label>
                            </div>
                            <div class="col-md-6 col-6">
                                <label class="type-checkbox-label" style="display: flex; align-items: center; padding: 10px; background: white; border-radius: 6px; cursor: pointer; margin-bottom: 8px; transition: all 0.3s; border: 2px solid #e9ecef;" onmouseover="this.style.borderColor='#ff6b35'" onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#e9ecef'">
                                    <input type="checkbox" name="type_checkbox[]" value="services" class="universal-type-checkbox" style="margin-right: 8px; width: 18px; height: 18px; cursor: pointer;">
                                    <span style="font-weight: 500; color: #495057;">SERVICES</span>
                                </label>
                            </div>
                        </div>
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
                    <button type="submit" class="btn btn-primary btn-lg" id="universal-save-btn" style="border-radius: 8px; padding: 12px 30px; font-weight: 600; background: #ff6b35; border: none; box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4); color: white; font-size: 16px;">
                        <i class="ti ti-check me-2"></i><span>SAVE ENTRY</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<audio id="deleteSound" src="{{ asset('deleteaudio_ubWu5Ok3.mp3') }}" preload="auto"></audio>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        let currentUnitId = null;
        /* =========================
           ADD / UPDATE UNIT
        ==========================*/
        $("#Unit-form").off("submit").on("submit", function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Check if modal is actually visible
            if (!$('#Unit-add-modal').hasClass('show')) {
                console.log('Unit modal not visible, ignoring submit');
                return false;
            }
            
            let formData = new FormData(this);
            let url = currentUnitId ?
                `/units/${currentUnitId}` :
                `{{ route('post.units') }}`;
            if (currentUnitId) {
                formData.append('_method', 'PUT');
            }
            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        if (currentUnitId) {
                            // 🔄 Update option
                            let option = $(`#unit_parts option[value="${currentUnitId}"]`);
                            option.text(res.unit.name);
                            option.attr('data-name', res.unit.name);
                        } else {
                            // ➕ Add new option
                            $("#unit_parts").append(`
                            <option value="${res.unit.id}"
                                data-name="${res.unit.name}"
                                data-baseunit="${res.unit.base_unit_name ?? ''}"
                                data-multiplier="${res.unit.base_unit_multiplier ?? ''}"
                                selected>
                                ${res.unit.name}
                            </option>
                        `);
                        }
                        $('#Unit-add-modal').modal('hide');
                        $('#Unit-form')[0].reset();
                        currentUnitId = null;
                        
                        // Only show success message if modal was actually open
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Unit saved successfully'
                        });
                        // 🔊 Play save sound when unit is saved
                        if (typeof playSaveSound === 'function') {
                            playSaveSound();
                        }
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
        /* =========================
           EDIT UNIT
        ==========================*/
        $('#editUnitBtn').on('click', function() {
            let selected = $('#unit_parts option:selected');
            if (!selected.val()) {
                Swal.fire('Select Unit', 'Please select a unit first', 'warning');
                return;
            }
            currentUnitId = selected.val();
            $('#Unit-modal-title').text('Edit Unit');
            $('#deleteUnitBtn').removeClass('d-none');
            
            // Fetch unit data including base units
            $.get(`/all/units`, function(units) {
                // Find the unit in the list (we need a better way - add a route to fetch single unit)
                // For now, use the selected option data
                const unitName = selected.data('name') || selected.text().split('(')[0].trim();
                $('#Unit-form [name="name"]').val(unitName);
            });
            
            // Fetch unit details via AJAX - we need to add a route for this
            // For now, use a workaround - fetch from all units
            $.ajax({
                url: `/units/${currentUnitId}`,
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(res) {
                    if (res && res.unit) {
                        const unit = res.unit;
                        $('#Unit-form [name="name"]').val(unit.name);
                        $('#Unit-form [name="short_name"]').val(unit.short_name);
                        $('#Unit-form [name="allow_decimal"]').val(unit.allow_decimal);
                        $('#Unit-form [name="define_base_unit"]').prop('checked', unit.define_base_unit == 1);
                        
                        // Show/hide base details
                        if (unit.define_base_unit == 1) {
                            $('#baseDetails').show();
                        } else {
                            $('#baseDetails').hide();
                        }
                        
                        // Clear existing base units
                        $('#baseUnitsContainer').empty();
                        baseUnitIndex = 0;
                        
                        // Load base units if available
                        if (unit.base_units && unit.base_units.length > 0) {
                            unit.base_units.forEach(function(baseUnit) {
                                addBaseUnitRow(baseUnit.base_unit_id, baseUnit.pivot?.multiplier || baseUnit.multiplier);
                            });
                        } else if (unit.base_unit_id) {
                            // Legacy single base unit
                            addBaseUnitRow(unit.base_unit_id, unit.base_unit_multiplier || 1);
                        } else {
                            // Add one empty row
                            addBaseUnitRow('', '');
                        }
                        
                        updateUnitRemoveButtons();
                        $('#Unit-add-modal').modal('show');
                    }
                },
                error: function() {
                    // Fallback to using option data
                    $('#Unit-form [name="name"]').val(selected.data('name') || selected.text().split('(')[0].trim());
                    $('#Unit-add-modal').modal('show');
                }
            });
        });
        
        // Helper function to add base unit row
        function addBaseUnitRow(baseUnitId, multiplier) {
            const container = $('#baseUnitsContainer');
            const newItem = $(`
                <div class="base-unit-item mb-3 p-3 border rounded">
                    <div class="row g-2">
                        <div class="col-5">
                            <label class="small">Multiplier</label>
                            <input type="number" step="0.0001" name="base_units[${baseUnitIndex}][multiplier]" class="form-control form-control-sm" value="${multiplier || ''}" placeholder="e.g., 1, 2, 3">
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
                            <button type="button" class="btn btn-danger btn-sm removeBaseUnit" style="display:none;">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `);
            container.append(newItem);
            
            // Set selected value
            if (baseUnitId) {
                newItem.find('select[name="base_units[' + baseUnitIndex + '][base_unit_id]"]').val(baseUnitId);
            }
            
            baseUnitIndex++;
        }
        /* =========================
           DELETE UNIT
        ==========================*/
        $('#deleteUnitBtn').on('click', function() {
            if (!currentUnitId) return;
            Swal.fire({
                title: 'Are you sure?',
                text: 'This unit will be deleted',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/units/${currentUnitId}`,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: $('input[name=_token]').val()
                        },
                        success: function(res) {
                            if (res.success) {
                                // 🔊 Play delete sound
                                const audio = document.getElementById(
                                'deleteSound');
                                audio.currentTime = 0;
                                audio.play();
                                // Remove option
                                $(`#unit_parts option[value="${currentUnitId}"]`)
                                    .remove();
                                $('#Unit-add-modal').modal('hide');
                                $('#Unit-form')[0].reset();
                                currentUnitId = null;
                                Swal.fire('Deleted!', 'Unit deleted successfully',
                                    'success');
                            }
                        }
                    });
                }
            });
        });
        /* =========================
           RESET MODAL
        ==========================*/
        $('#Unit-add-modal').on('hidden.bs.modal', function() {
            $('#Unit-form')[0].reset();
            $('#deleteUnitBtn').addClass('d-none');
            $('#Unit-modal-title').text('Add Unit');
            currentUnitId = null;
            
            // Reset base units container
            $('#baseUnitsContainer').empty();
            baseUnitIndex = 1;
            // Add one empty base unit row
            const container = $('#baseUnitsContainer');
            container.html(`
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
            `);
            $('#baseDetails').hide();
            $('#toggleBaseUnit').prop('checked', false);
        });
    });
</script>
<script>
    document.getElementById('imageInput').addEventListener('change', function(evt) {
        const file = evt.target.files[0];
        const imgURL = URL.createObjectURL(file);
        const imgPreview = document.getElementById('imagePreview');
        imgPreview.src = imgURL;
        imgPreview.style.display = 'block';
    });
    document.getElementById('toggleBaseUnit').addEventListener("change", function() {
        document.getElementById('baseDetails').style.display = this.checked ? "block" : "none";
    });
    
    // Add Base Unit functionality for Unit Modal
    let baseUnitIndex = 1;
    document.getElementById('addBaseUnitBtn')?.addEventListener('click', function() {
        const container = document.getElementById('baseUnitsContainer');
        if (!container) return;
        
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
    $('#unit_parts').on('change', function() {
        let selected = $(this).find(':selected');
        let unitName = selected.data('name');
        let baseUnit = selected.data('baseunit');
        let multiplier = parseFloat(selected.data('multiplier'));
        // Reset values
        $('#base_price, #total_can_price, #sale_base_price, #total_sale_price').val('');
        // Always show containers
        $('#unit-info, #sale-price-info').show();
        // Set unit names
        $('#unit-name, #sale-unit-name').text(unitName);
        // Remove previous listeners
        $('#base_price, #total_can_price, #sale_base_price, #total_sale_price').off('input');
        if (baseUnit && multiplier) {
            // 🔥 BASE UNIT CASE
            $('#multiplier-text, #sale-multiplier-text')
                .text(`${multiplier} ${baseUnit}`)
                .show();
            $('.equal-sign, .sale-equal-sign').show();
            // Show base price inputs
            $('#base_price, #sale_base_price').show();
            // Placeholders
            $('#base_price').attr('placeholder', `Price per ${baseUnit}`);
            $('#sale_base_price').attr('placeholder', `Sale per ${baseUnit}`);
            $('#total_can_price').attr('placeholder', `${unitName} Cost Price`);
            $('#total_sale_price').attr('placeholder', `${unitName} Sale Price`);
            // Bidirectional calculation
            $('#base_price').on('input', function() {
                $('#total_can_price').val($(this).val() * multiplier || '');
            });
            $('#total_can_price').on('input', function() {
                $('#base_price').val($(this).val() / multiplier || '');
            });
            $('#sale_base_price').on('input', function() {
                $('#total_sale_price').val($(this).val() * multiplier || '');
            });
            $('#total_sale_price').on('input', function() {
                $('#sale_base_price').val($(this).val() / multiplier || '');
            });
        } else {
            // 🔥 SIMPLE UNIT CASE
            // Hide base unit related fields
            $('#base_price, #sale_base_price').hide();
            $('#multiplier-text, #sale-multiplier-text').hide();
            $('.equal-sign, .sale-equal-sign').hide();
            // Placeholders
            $('#total_can_price').attr('placeholder', `${unitName} Cost Price`);
            $('#total_sale_price').attr('placeholder', `${unitName} Sale Price`);
        }
    });
</script>
<script>
    $(document).ready(function() {
        $("#vehicleTable tbody tr").hide();
    });
</script>
<script>
    $("#part_number_id").on("change", function() {
        let selectedPart = $(this).val();
        $("#vehicleTable tbody tr").each(function() {
            let rowPart = $(this).data("part");
            if (selectedPart === "") {
                $(this).show();
            } else if (rowPart == selectedPart) {
                $(this).show();
            } else {
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
    document.getElementById("part_number").addEventListener("keydown", function(e) {
        if (e.key === " ") {
            e.preventDefault();
            this.value += "-";
        }
    });
    document.addEventListener("DOMContentLoaded", function() {
        const checkboxes = document.querySelectorAll('.form-check-input');
        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    checkboxes.forEach((cb) => {
                        if (cb !== this) {
                            cb.checked = false;
                        }
                    });
                }
            });
        });
    });
</script>
<script>

    // Global function for type selection (works from inline onclick)
    window.selectItemType = function(type) {
        console.log('🎯 selectItemType called with:', type);
        
        // Update visual selection immediately
        $('.type-box').removeClass('selected');
        $(`.type-box[data-type="${type}"]`).addClass('selected');
        
        // Update localStorage
        localStorage.setItem('selectedType', type);
        
        // Try to update Alpine.js component if available
        setTimeout(() => {
            try {
                if (typeof Alpine !== 'undefined' && Alpine.$data) {
                    const alpineElement = document.querySelector('[x-data*="productForm"]');
                    if (alpineElement) {
                        const alpineComponent = Alpine.$data(alpineElement);
                        if (alpineComponent) {
                            alpineComponent.selectedType = type;
                        }
                    }
                }
            } catch (e) {
                console.log('Alpine.js update failed:', e);
            }
        }, 50);
        
        // Filter dropdowns
        if (typeof filterDropdownsByType === 'function') {
            setTimeout(() => {
                try {
                    filterDropdownsByType(type);
                } catch (e) {
                    console.error('Error filtering dropdowns:', e);
                }
            }, 300);
        }
        
        // Load items by type
        if (typeof loadItemsByType === 'function') {
            try {
                loadItemsByType(type);
            } catch (e) {
                console.error('Error loading items:', e);
            }
        }
    };

    // Initialize Alpine.js component
    document.addEventListener('alpine:init', () => {
        console.log('🚀 Alpine.js initialized, setting up productForm');
        
        Alpine.data('productForm', () => ({
            selectedType: localStorage.getItem('selectedType') || '{{ old("type") }}' || '',
            init() {
                console.log('📦 productForm init, selectedType:', this.selectedType);
                
                // Update visual selection based on stored type
                const initialType = this.selectedType;
                if (initialType) {
                    $('.type-box').removeClass('selected');
                    $(`.type-box[data-type="${initialType}"]`).addClass('selected');
                    
                    // Wait for Select2 to initialize first
                    setTimeout(() => {
                        if (typeof filterDropdownsByType === 'function') {
                            filterDropdownsByType(initialType);
                        }
                    }, 1500);
                }
                
                // Watch for selectedType changes and filter dropdown options
                this.$watch('selectedType', (newType) => {
                    console.log('👀 selectedType changed to:', newType);
                    // Filter all dropdowns based on selected type with delay
                    setTimeout(() => {
                        if (typeof filterDropdownsByType === 'function') {
                            filterDropdownsByType(newType);
                        }
                    }, 300);
                });
            },
            selectType(type) {
                console.log('🎯 Alpine selectType called with:', type);
                // Call the global function instead
                if (typeof window.selectItemType === 'function') {
                    window.selectItemType(type);
                } else {
                    // Fallback if global function not available
                    this.selectedType = type;
                    localStorage.setItem('selectedType', type);
                    $('.type-box').removeClass('selected');
                    $(`.type-box[data-type="${type}"]`).addClass('selected');
                }
            }
        }));
        
        console.log('✅ productForm component registered');
    });
    
    // Fallback: If Alpine.js doesn't load, still make type selection work
    $(document).ready(function() {
        setTimeout(() => {
            if (typeof Alpine === 'undefined') {
                console.warn('⚠️ Alpine.js not loaded, using jQuery fallback only');
            }
        }, 2000);
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
        const savedType = localStorage.getItem('selectedType');
        if (savedType) {
            loadItemsByType(savedType, true);
        }
    }

    // Global function for type selection (works from inline onclick)
    window.selectItemType = function(type) {
        console.log('🎯 selectItemType called with:', type);
        
        // Update visual selection immediately
        $('.type-box').removeClass('selected');
        $(`.type-box[data-type="${type}"]`).addClass('selected');
        
        // Update localStorage
        localStorage.setItem('selectedType', type);
        
        // Try to update Alpine.js component if available
        setTimeout(() => {
            try {
                if (typeof Alpine !== 'undefined' && Alpine.$data) {
                    const alpineElement = document.querySelector('[x-data*="productForm"]');
                    if (alpineElement) {
                        const alpineComponent = Alpine.$data(alpineElement);
                        if (alpineComponent) {
                            alpineComponent.selectedType = type;
                            if (typeof alpineComponent.selectType === 'function') {
                                alpineComponent.selectType(type);
                            }
                        }
                    }
                }
            } catch (e) {
                console.log('Alpine.js update failed:', e);
            }
        }, 50);
        
        // Filter dropdowns
        if (typeof filterDropdownsByType === 'function') {
            setTimeout(() => {
                try {
                    filterDropdownsByType(type);
                } catch (e) {
                    console.error('Error filtering dropdowns:', e);
                }
            }, 300);
        }
        
        // Load items by type
        if (typeof loadItemsByType === 'function') {
            try {
                loadItemsByType(type);
            } catch (e) {
                console.error('Error loading items:', e);
            }
        }
    };

    // Primary jQuery click handler for type boxes (works immediately, doesn't wait for Alpine.js)
    $(document).ready(function() {
        // Remove any existing handlers to avoid duplicates
        $(document).off('click', '.type-box');
        
        // Add click handler
        $(document).on('click', '.type-box', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $box = $(this);
            const type = $box.data('type') || $box.attr('data-type') || $box.closest('[data-type]').data('type');
            
            if (!type) {
                console.error('No type found for clicked box');
                return;
            }
            
            console.log('🖱️ Type box clicked:', type);
            
            // Update visual selection immediately
            $('.type-box').removeClass('selected');
            $box.addClass('selected');
            
            // Update localStorage
            localStorage.setItem('selectedType', type);
            
            // Try to update Alpine.js component if available
            try {
                // Wait a bit for Alpine.js to be ready
                setTimeout(() => {
                    try {
                        const alpineElement = document.querySelector('[x-data*="productForm"]');
                        if (alpineElement && window.Alpine) {
                            const alpineComponent = Alpine.$data(alpineElement);
                            if (alpineComponent) {
                                alpineComponent.selectedType = type;
                                console.log('✅ Updated Alpine.js component');
                            }
                        }
                    } catch (alpineError) {
                        console.log('Alpine.js not available or not ready:', alpineError);
                    }
                }, 100);
            } catch (e) {
                console.log('Alpine.js check failed:', e);
            }
            
            // Filter dropdowns
            if (typeof filterDropdownsByType === 'function') {
                setTimeout(() => {
                    try {
                        filterDropdownsByType(type);
                        console.log('✅ Filtered dropdowns for type:', type);
                    } catch (filterError) {
                        console.error('Error filtering dropdowns:', filterError);
                    }
                }, 300);
            } else {
                console.warn('filterDropdownsByType function not found');
            }
            
            // Load items by type
            if (typeof loadItemsByType === 'function') {
                try {
                    loadItemsByType(type);
                } catch (loadError) {
                    console.error('Error loading items:', loadError);
                }
            }
        });
        
        console.log('✅ Type box click handlers initialized');
    });

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

    // Function to filter dropdowns by selected type
    function filterDropdownsByType(selectedType) {
        console.log('🔍 Filtering dropdowns by type:', selectedType);
        
        // List of all dropdowns that need filtering
        const dropdowns = [
            '.company-select',
            '.technology-select',
            '.quality-select',
            '.name-select',
            '.part_number-select',
            '.category-select'
        ];

        dropdowns.forEach(selector => {
            $(selector).each(function() {
                const $select = $(this);
                const currentValue = $select.val();
                const isSelect2 = $select.hasClass('select2-hidden-accessible');
                
                // Store current value before filtering
                let selectedValue = currentValue;
                
                // Show/hide options based on type
                let visibleCount = 0;
                let hiddenCount = 0;
                
                // Store all options with their data before filtering
                const allOptions = [];
                $select.find('option').each(function() {
                    const $option = $(this);
                    allOptions.push({
                        element: $option[0],
                        value: $option.val(),
                        text: $option.text(),
                        type: $option.data('type') || '',
                        selected: $option.prop('selected')
                    });
                });
                
                $select.find('option').each(function() {
                    const $option = $(this);
                    const optionType = $option.data('type') || '';
                    const optionValue = $option.val();
                    
                    // Show option if:
                    // 1. It's the empty/default option
                    // 2. No type is selected (show all)
                    // 3. Option type matches selected type
                    // 4. Option has no type (backward compatibility)
                    const shouldShow = optionValue === '' || !selectedType || optionType === selectedType || optionType === '';
                    
                    if (shouldShow) {
                        $option.show().prop('disabled', false).removeAttr('data-hidden-by-filter');
                        if (optionValue !== '') visibleCount++;
                    } else {
                        $option.hide().prop('disabled', true).attr('data-hidden-by-filter', 'true');
                        if (optionValue !== '') hiddenCount++;
                    }
                });
                
                console.log(`✅ Filtered ${selector}: ${visibleCount} visible, ${hiddenCount} hidden for type "${selectedType}"`);

                // If current selected value is hidden or disabled, clear selection
                const $selectedOption = $select.find('option:selected');
                if ($selectedOption.length && ($selectedOption.is(':hidden') || $selectedOption.prop('disabled'))) {
                    console.log(`⚠️ Clearing invalid selection in ${selector}`);
                    selectedValue = '';
                    $select.val('').trigger('change');
                }

                // For Select2, we need to destroy and reinitialize to properly reflect changes
                if (isSelect2) {
                    try {
                        // Destroy Select2 completely
                        if ($.fn.select2) {
                            $select.select2('destroy');
                        }
                        // Remove Select2 classes and container
                        $select.removeClass('select2-hidden-accessible');
                        const $container = $select.next('.select2-container');
                        if ($container.length) {
                            $container.remove();
                        }
                        
                        // Reinitialize Select2 after a short delay with templateResult to filter
                        setTimeout(() => {
                            if ($.fn.select2) {
                                // Check if Select2 is not already initialized
                                if (!$select.hasClass('select2-hidden-accessible')) {
                                    $select.select2({
                                        placeholder: 'Please Select',
                                        allowClear: true,
                                        width: '100%',
                                        matcher: function(params, data) {
                                            // If no search term, show all matching type options
                                            if (!params.term) {
                                                const $option = $select.find(`option[value="${data.id}"]`);
                                                const optionType = $option.data('type') || '';
                                                
                                                // Show if matches type or has no type
                                                if (!selectedType || optionType === selectedType || optionType === '') {
                                                    return data;
                                                }
                                                return null;
                                            }
                                            
                                            // If search term exists, use default matcher but also check type
                                            const $option = $select.find(`option[value="${data.id}"]`);
                                            const optionType = $option.data('type') || '';
                                            
                                            // Check type match first
                                            if (selectedType && optionType && optionType !== selectedType) {
                                                return null;
                                            }
                                            
                                            // Then check text match
                                            if (data.text && data.text.toUpperCase().indexOf(params.term.toUpperCase()) >= 0) {
                                                return data;
                                            }
                                            
                                            return null;
                                        }
                                    });
                                } else {
                                    // If already initialized, trigger update
                                    $select.trigger('change.select2');
                                }
                                
                                // Restore selection if it's still valid
                                if (selectedValue) {
                                    const $validOption = $select.find(`option[value="${selectedValue}"]:not(:hidden):not([disabled])`);
                                    if ($validOption.length) {
                                        setTimeout(() => {
                                            $select.val(selectedValue).trigger('change');
                                            console.log(`✅ Restored selection in ${selector}: ${selectedValue}`);
                                        }, 50);
                                    } else {
                                        console.log(`⚠️ Could not restore selection in ${selector}: ${selectedValue} (option hidden/disabled)`);
                                    }
                                }
                            }
                        }, 150);
                    } catch(e) {
                        console.error('❌ Error refreshing Select2 for ' + selector + ':', e);
                        // Fallback: just trigger change
                        $select.trigger('change');
                    }
                } else {
                    // For regular selects, just trigger change
                    $select.trigger('change');
                }
            });
        });
        
        console.log('✅ Filtering complete for type:', selectedType);
    }

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

        items.forEach(function(item) {
            const activeBadge = item.is_active 
                ? '<span class="badge bg-success">Active</span>' 
                : '<span class="badge bg-danger">Inactive</span>';

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
                                    <a class="dropdown-item text-primary" href="${item.duplicate_url}">
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
        const savedType = localStorage.getItem('selectedType');
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
    $(document).ready(function() {
        feather.replace();
        // Generate random barcode
        function generateRandomItemCode() {
            const digits = '0123456789';
            let result = '';
            for (let i = 0; i < 10; i++) {
                result += digits.charAt(Math.floor(Math.random() * digits.length));
            }
            return result;
        }
        if (!$('#itemBarCode').val()) {
            $('#itemBarCode').val(generateRandomItemCode());
        }
        $(document).on('click', '.generate-code-btn', function() {
            $('#itemBarCode').val(generateRandomItemCode());
        });
        // Thumbnail handler
        function initializeThumbnailHandler() {
            const imageInput = $('#imageInput')[0];
            const preview = $('#imagePreview')[0];
            const container = $('#previewContainer')[0];
            const removeBtn = $('#removeBtn')[0];
            const defaultImg = "https://pdis.co.kr/img/image.jpg";
            if (!imageInput || !preview || !container || !removeBtn) return;

            // Using label for file input - works on all mobile browsers (Chrome, Firefox, etc.)
            // Label automatically triggers the input when clicked, preserving camera/gallery options
            // No need for JavaScript click handler when using label

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
                container.style.display = 'none';
            };
        }
        // Multiple images handler
        function initializeImagesHandler() {
            const input = $('#imagesInput')[0];
            const preview = $('#imagesPreview')[0];
            const container = $('#imagesPreviewContainer')[0];
            let allFiles = [];
            if (!input || !preview || !container) return;

            // Using label for file input - works on all mobile browsers (Chrome, Firefox, etc.)
            // Label automatically triggers the input when clicked, preserving camera/gallery options
            // No need for JavaScript click handler when using label
            input.onchange = function() {
                const newFiles = Array.from(this.files);
                // add newly selected files to previous files
                allFiles = [...allFiles, ...newFiles];
                // update input.files
                const dt = new DataTransfer();
                allFiles.forEach(f => dt.items.add(f));
                input.files = dt.files;
                // show preview
                preview.innerHTML = "";
                container.style.display = "block";
                allFiles.forEach((file, index) => {
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
                            allFiles.splice(index, 1);
                            const dt2 = new DataTransfer();
                            allFiles.forEach(f => dt2.items.add(f));
                            input.files = dt2.files;
                            div.remove();
                            if (allFiles.length === 0) container.style.display = 'none';
                        };
                        div.appendChild(img);
                        div.appendChild(remove);
                        preview.appendChild(div);
                        feather.replace();
                    };
                    reader.readAsDataURL(file);
                });
            };
        }
        initializeThumbnailHandler();
        initializeImagesHandler();
        // Universal Modal
        let currentTargetSelect = null;
        let currentEditId = null;
        let deleteRoute = null;
        let lastSearchTerm = {}; // Store last search term for each select
        
        // =========================
        // Universal "Add New" Modal Behavior for Searchable Dropdowns
        // NEW APPROACH: Direct and Simple
        // =========================
        
        // Global tracker for active select and its search term
        let activeSelectSearch = {
            select: null,
            searchTerm: '',
            selectId: null
        };
        
        // Method 1: Direct capture from Select2 search input (most reliable)
        $(document).on('input', '.select2-search__field', function(e) {
            const searchValue = $(this).val().trim();
            if (searchValue) {
                // Find the associated select
                const $select2Container = $(this).closest('.select2-container');
                const $select = $select2Container.prev('select.searchable-select');
                
                if ($select.length) {
                    const selectId = $select.attr('id') || $select.attr('name') || 'default';
                    activeSelectSearch = {
                        select: $select,
                        searchTerm: searchValue,
                        selectId: selectId
                    };
                    lastSearchTerm[selectId] = searchValue;
                }
            }
        });
        
        // Method 2: Capture when Select2 opens (store reference)
        $(document).on('select2:open', '.searchable-select', function() {
            const $select = $(this);
            const selectId = $select.attr('id') || $select.attr('name') || 'default';
            
            // Wait for search input to be available
            setTimeout(function() {
                const $container = $select.next('.select2-container');
                const $searchInput = $container.find('.select2-search__field');
                if ($searchInput.length) {
                    // Store reference
                    activeSelectSearch.select = $select;
                    activeSelectSearch.selectId = selectId;
                    
                    // Capture current value
                    const currentVal = $searchInput.val().trim();
                    if (currentVal) {
                        activeSelectSearch.searchTerm = currentVal;
                        lastSearchTerm[selectId] = currentVal;
                    }
                }
            }, 50);
        });
        
        // Method 3: Capture when "No results found" message appears and add "ADD NEW" button
        $(document).on('select2:results:message', '.searchable-select', function(e) {
            const $select = $(this);
            const selectId = $select.attr('id') || $select.attr('name') || 'default';
            const message = (e.params && e.params.message) ? e.params.message.toLowerCase() : '';
            
            if (message.includes('no results') || message.includes('not found')) {
                // Immediate capture
                const $openSelect2 = $('.select2-container--open');
                let searchVal = '';
                
                if ($openSelect2.length) {
                    const $searchInput = $openSelect2.find('.select2-search__field');
                    if ($searchInput.length && $searchInput.val()) {
                        searchVal = $searchInput.val().trim();
                        activeSelectSearch = {
                            select: $select,
                            searchTerm: searchVal,
                            selectId: selectId
                        };
                        lastSearchTerm[selectId] = searchVal;
                    }
                }
                
                // Also try after a short delay
                setTimeout(function() {
                    const $container = $select.next('.select2-container');
                    const $searchInput = $container.find('.select2-search__field');
                    if ($searchInput.length && $searchInput.val()) {
                        searchVal = $searchInput.val().trim();
                        activeSelectSearch = {
                            select: $select,
                            searchTerm: searchVal,
                            selectId: selectId
                        };
                        lastSearchTerm[selectId] = searchVal;
                    }
                    
                    // Add "ADD NEW" button below "No results found" message
                    if (searchVal) {
                        addNewButtonToSelect2($select, searchVal);
                    }
                }, 100);
            } else {
                // Remove button if results are found
                removeAddNewButtonFromSelect2($select);
            }
        });
        
        // Also listen for when Select2 results are updated
        $(document).on('select2:open select2:select select2:unselect', '.searchable-select', function() {
            const $select = $(this);
            const $openSelect2 = $('.select2-container--open');
            
            setTimeout(() => {
                const $noResultsMsg = $openSelect2.find('.select2-results__message');
                if ($noResultsMsg.length && $noResultsMsg.is(':visible')) {
                    const msgText = $noResultsMsg.text().toUpperCase();
                    if (msgText.includes('NO RESULTS') || msgText.includes('NOT FOUND')) {
                        const $searchInput = $openSelect2.find('.select2-search__field');
                        if ($searchInput.length && $searchInput.val()) {
                            const searchVal = $searchInput.val().trim();
                            if (searchVal) {
                                addNewButtonToSelect2($select, searchVal);
                            }
                        }
                    } else {
                        removeAddNewButtonFromSelect2($select);
                    }
                } else {
                    removeAddNewButtonFromSelect2($select);
                }
            }, 150);
        });
        
        // Function to add "ADD NEW" button to Select2 dropdown
        function addNewButtonToSelect2($select, searchTerm) {
            const $container = $select.next('.select2-container');
            if (!$container.length) return;
            
            const $dropdown = $container.find('.select2-dropdown');
            const $results = $dropdown.find('.select2-results__options');
            
            if (!$results.length) return;
            
            // Remove existing button if any
            $results.find('.select2-add-new-button').remove();
            
            // Get the button configuration from the select
            const $addButton = $select.closest('.input-group').find('.open-universal-modal[data-mode="add"]').first();
            if (!$addButton.length) return;
            
            const title = $addButton.data('title') || 'Add New';
            const route = $addButton.data('route');
            const targetSelect = $addButton.data('target-select') || $select.attr('class');
            
            // Get selected type from Alpine.js
            let selectedType = '';
            try {
                const alpineComponent = Alpine.$data(document.querySelector('[x-data*="productForm"]'));
                if (alpineComponent && alpineComponent.selectedType) {
                    selectedType = alpineComponent.selectedType;
                } else {
                    const typeInput = document.querySelector('input[name="type"]');
                    if (typeInput) {
                        selectedType = typeInput.value;
                    }
                }
            } catch (e) {
                console.log('Could not get selectedType:', e);
            }
            
            // Get type name for display
            const typeNames = {
                'parts': 'parts',
                'filters': 'filters',
                'breakpad': 'break pad',
                'oil': 'oil',
                'battery': 'battery',
                'scrap': 'scrap',
                'services': 'services'
            };
            const typeDisplayName = typeNames[selectedType] || selectedType || 'selected type';
            
            // Create button HTML matching the image style
            const buttonHtml = `
                <li class="select2-add-new-button" style="padding: 15px; border-top: 1px solid #e9ecef; background: #fff; list-style: none;">
                    <div style="text-align: center;">
                        <div style="color: #6c757d; font-size: 13px; margin-bottom: 12px; font-weight: 500;">
                            No matches in ${typeDisplayName}
                        </div>
                        <button type="button" 
                                class="btn btn-primary select2-add-new-btn" 
                                style="background: #ff6b35; border: none; border-radius: 8px; padding: 10px 24px; font-weight: 600; width: 100%; color: white; font-size: 14px; box-shadow: 0 2px 8px rgba(255, 107, 53, 0.3);"
                                data-search-term="${searchTerm}"
                                data-title="${title}"
                                data-route="${route}"
                                data-target-select="${targetSelect}"
                                data-type="${selectedType}">
                            ADD NEW "${searchTerm.toUpperCase()}"
                        </button>
                    </div>
                </li>
            `;
            
            // Append button to results
            $results.append(buttonHtml);
            console.log('✅ Added "ADD NEW" button for search term:', searchTerm);
        }
        
        // Function to remove "ADD NEW" button from Select2 dropdown
        function removeAddNewButtonFromSelect2($select) {
            const $container = $select.next('.select2-container');
            if ($container.length) {
                $container.find('.select2-add-new-button').remove();
            }
        }
        
        // Handle click on "ADD NEW" button in Select2 dropdown
        $(document).on('click', '.select2-add-new-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const searchTerm = $(this).data('search-term');
            const title = $(this).data('title');
            const route = $(this).data('route');
            const targetSelect = $(this).data('target-select');
            const selectedType = $(this).data('type');
            
            // Close Select2 dropdown
            const $select = $(this).closest('.select2-container').prev('select.searchable-select');
            if ($select.length && $select.hasClass('select2-hidden-accessible')) {
                $select.select2('close');
            }
            
            // Open universal modal with pre-filled name
            setTimeout(() => {
                // Set type in universal form
                if (selectedType) {
                    $('#universal-type').val(selectedType);
                }
                
                // Set title and route
                $('#universal-modal-title').text(title);
                $('#universal-modal-subtitle').text('SMART ASSET REGISTRY');
                $('#universal-form').attr('action', route).attr('method', 'POST');
                
                // Pre-fill name with search term
                $('#universal-name').val(searchTerm);
                
                // Show type selection if in add mode
                $('#universal-type-selection').show();
                
                // Check the selected type checkbox
                if (selectedType) {
                    $('.universal-type-checkbox').prop('checked', false).closest('label').css({
                        'background': 'white',
                        'border-color': '#e9ecef'
                    });
                    $(`.universal-type-checkbox[value="${selectedType}"]`).prop('checked', true);
                    $(`.universal-type-checkbox[value="${selectedType}"]`).closest('label').css({
                        'background': '#fff4f0',
                        'border-color': '#ff6b35'
                    });
                }
                
                // Set target select
                currentTargetSelect = targetSelect;
                
                // Show delete button as hidden
                $('#universal-delete-btn').addClass('d-none');
                $('#universal-save-btn').html('<i class="ti ti-check me-2"></i><span>SAVE ENTRY</span>');
                
                // Open modal
                $('#universal-add-modal').modal('show');
                
                // Focus on name input
                setTimeout(() => {
                    $('#universal-name').focus();
                }, 300);
            }, 100);
        });
        
        // Method 3b: Also listen for when results are loaded (to catch "No results" even if event doesn't fire)
        $(document).on('select2:select select2:unselect', '.searchable-select', function() {
            // This fires after results are shown, check for "No results found"
            setTimeout(function() {
                const $select = $(this);
                const $openSelect2 = $('.select2-container--open');
                if ($openSelect2.length) {
                    const $noResultsMsg = $openSelect2.find('.select2-results__message');
                    if ($noResultsMsg.length && $noResultsMsg.is(':visible')) {
                        const selectId = $select.attr('id') || $select.attr('name') || 'default';
                        const $searchInput = $openSelect2.find('.select2-search__field');
                        if ($searchInput.length && $searchInput.val()) {
                            const searchVal = $searchInput.val().trim();
                            activeSelectSearch = {
                                select: $select,
                                searchTerm: searchVal,
                                selectId: selectId
                            };
                            lastSearchTerm[selectId] = searchVal;
                        }
                    }
                }
            }.bind(this), 100);
        });
        
        // Method 4: Try to get from Select2 internal data (if available)
        function getSearchTermFromSelect2($select) {
            try {
                const select2Data = $select.data('select2');
                if (select2Data && select2Data.dropdown) {
                    const $dropdown = select2Data.dropdown.$dropdown || select2Data.dropdown.$results;
                    if ($dropdown && $dropdown.length) {
                        const $searchInput = $dropdown.find('.select2-search__field');
                        if ($searchInput.length && $searchInput.val()) {
                            return $searchInput.val().trim();
                        }
                    }
                }
            } catch(e) {
                // Select2 API not available or different version
            }
            return null;
        }
        
        // Helper function: Get search term when "No results found" is visible
        function getSearchTermFromNoResults($select) {
            // Check open Select2 dropdown
            const $openSelect2 = $('.select2-container--open');
            if ($openSelect2.length) {
                const $noResultsMsg = $openSelect2.find('.select2-results__message');
                if ($noResultsMsg.length && $noResultsMsg.is(':visible')) {
                    const msgText = $noResultsMsg.text().toUpperCase();
                    if (msgText.includes('NO RESULTS') || msgText.includes('NOT FOUND')) {
                        const $searchInput = $openSelect2.find('.select2-search__field');
                        if ($searchInput.length && $searchInput.val()) {
                            return $searchInput.val().trim();
                        }
                    }
                }
            }
            
            // Check Select2 container (even if dropdown is closed)
            const $container = $select.next('.select2-container');
            if ($container.length) {
                const $noResultsMsg = $container.find('.select2-results__message');
                if ($noResultsMsg.length) {
                    const msgText = $noResultsMsg.text().toUpperCase();
                    if (msgText.includes('NO RESULTS') || msgText.includes('NOT FOUND')) {
                        const $searchInput = $container.find('.select2-search__field');
                        if ($searchInput.length && $searchInput.val()) {
                            return $searchInput.val().trim();
                        }
                    }
                }
            }
            
            return null;
        }
        
        // Method 5: MutationObserver to watch for "No results found" messages and add button
        if (typeof MutationObserver !== 'undefined') {
            // Watch for changes in Select2 dropdowns
            $('.searchable-select').each(function() {
                const $select = $(this);
                const $container = $select.next('.select2-container');
                
                if ($container.length) {
                    const $dropdown = $container.find('.select2-dropdown, .select2-results');
                    
                    if ($dropdown.length) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        $(mutation.addedNodes).each(function() {
                            const $node = $(this);
                            // Check if this is a "No results found" message
                            const text = $node.text().toLowerCase();
                            if (text.includes('no results') || text.includes('not found')) {
                                // Find the associated select
                                const $select2Container = $node.closest('.select2-container, .select2-dropdown');
                                if ($select2Container.length) {
                                    const $select = $select2Container.prev('select.searchable-select');
                                    if ($select.length) {
                                        const $searchInput = $select2Container.find('.select2-search__field');
                                        if ($searchInput.length && $searchInput.val()) {
                                            const selectId = $select.attr('id') || $select.attr('name') || 'default';
                                            const searchVal = $searchInput.val().trim();
                                            activeSelectSearch = {
                                                select: $select,
                                                searchTerm: searchVal,
                                                selectId: selectId
                                            };
                                            lastSearchTerm[selectId] = searchVal;
                                                        
                                                        // Add "ADD NEW" button
                                                        setTimeout(() => {
                                                            addNewButtonToSelect2($select, searchVal);
                                                        }, 100);
                                        }
                                    }
                                }
                                        });
                            }
                        });
                    }
                            
                            // Observe the dropdown for changes
                            observer.observe($dropdown[0], {
                                childList: true,
                                subtree: true,
                                characterData: true
                            });
                        }
                    }
                });
            }
            
            // Also observe document body for dynamically created Select2 dropdowns
            const globalObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        $(mutation.addedNodes).each(function() {
                            const $node = $(this);
                            // Check if this is a "No results found" message
                            const text = $node.text().toLowerCase();
                            if (text.includes('no results') || text.includes('not found')) {
                                // Find the associated select
                                const $select2Container = $node.closest('.select2-container, .select2-dropdown');
                                if ($select2Container.length) {
                                    const $select = $select2Container.prev('select.searchable-select');
                                    if ($select.length) {
                                        const $searchInput = $select2Container.find('.select2-search__field');
                                        if ($searchInput.length && $searchInput.val()) {
                                            const searchVal = $searchInput.val().trim();
                                            setTimeout(() => {
                                                addNewButtonToSelect2($select, searchVal);
                                            }, 100);
                                        }
                                    }
                                }
                            }
                        });
                    }
                });
            });
            
            // Start observing when DOM is ready
            $(document).ready(function() {
                // Observe the document body for changes
                globalObserver.observe(document.body, {
                    childList: true,
                    subtree: true,
                    characterData: true
                });
            });
        }
        
        // =========================
        // INTERCEPT PLUS BUTTON CLICK EARLY
        // Capture search term at the exact moment of click
        // =========================
        $(document).on('mousedown touchstart', '.open-universal-modal, .add-btn', function(e) {
            // Capture search term BEFORE the click event fires
            const $openSelect2 = $('.select2-container--open');
            if ($openSelect2.length) {
                // Check if "No results found" is visible
                const $noResultsMsg = $openSelect2.find('.select2-results__message');
                const hasNoResults = $noResultsMsg.length && $noResultsMsg.is(':visible');
                
                const $searchInput = $openSelect2.find('.select2-search__field');
                if ($searchInput.length && $searchInput.val()) {
                    const searchVal = $searchInput.val().trim();
                    
                    // Find the select - try multiple methods
                    let $select = $openSelect2.prev('select.searchable-select');
                    
                    // If not found, try to find by checking which select has this container
                    if (!$select.length) {
                        $('.searchable-select').each(function() {
                            const $s = $(this);
                            const $container = $s.next('.select2-container');
                            if ($container.length && $container.is($openSelect2)) {
                                $select = $s;
                                return false; // break
                            }
                        });
                    }
                    
                    // If still not found, try to find select near the button
                    if (!$select.length) {
                        const $button = $(this);
                        $select = $button.closest('.input-group').find('select.searchable-select').first();
                    }
                    
                    if ($select.length) {
                        const selectId = $select.attr('id') || $select.attr('name') || 'default';
                        activeSelectSearch = {
                            select: $select,
                            searchTerm: searchVal,
                            selectId: selectId
                        };
                        lastSearchTerm[selectId] = searchVal;
                        
                        // If "No results found" is visible, this is high priority
                        if (hasNoResults) {
                            // Store with higher priority flag
                            activeSelectSearch.hasNoResults = true;
                        }
                    }
                }
            }
        });
        
        // =========================
        // OPEN ADD / EDIT MODAL
        // Universal support for both .open-universal-modal and .add-btn
        // =========================
        $(document).on('click', '.open-universal-modal, .add-btn', function() {
            const mode = $(this).data('mode'); // add | edit
            const title = $(this).data('title');
            const hasImage = $(this).data('has-image') == 1;
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
            
            // Set type in universal form and show type selection
            if (mode === 'add') {
                $('#universal-type-selection').show();
                // Uncheck all checkboxes first
                $('.universal-type-checkbox').prop('checked', false).closest('label').css({
                    'background': 'white',
                    'border-color': '#e9ecef'
                });
                
                // Check the selected type checkbox
                if (selectedType) {
                    $(`.universal-type-checkbox[value="${selectedType}"]`).prop('checked', true);
                    $(`.universal-type-checkbox[value="${selectedType}"]`).closest('label').css({
                        'background': '#fff4f0',
                        'border-color': '#ff6b35'
                    });
                    $('#universal-type').val(selectedType);
                } else {
                    $('#universal-type').val('');
                }
            } else {
                $('#universal-type-selection').hide();
                $('#universal-type').val('');
            }
            
            // =========================
            // CAPTURE SEARCH TERM FIRST (before form reset)
            // NEW SIMPLE APPROACH
            // =========================
            let searchTerm = '';
            if (mode === 'add') {
                // Find the associated select element
                let $select = null;
                if (currentTargetSelect) {
                    $select = $(currentTargetSelect);
                } else {
                    // Fallback: find select near the button
                    const $button = $(this);
                    $select = $button.closest('.input-group').find('select.searchable-select').first();
                }
                
                if ($select && $select.length) {
                    const selectId = $select.attr('id') || $select.attr('name') || 'default';
                    
                    // PRIORITY 1: Check if "No results found" is visible and get search term directly
                    const noResultsTerm = getSearchTermFromNoResults($select);
                    if (noResultsTerm) {
                        searchTerm = noResultsTerm;
                    }
                    
                    // PRIORITY 2: Get from currently open Select2 dropdown (even if no "No results" message)
                    if (!searchTerm) {
                        const $openSelect2 = $('.select2-container--open');
                        if ($openSelect2.length) {
                            const $searchInput = $openSelect2.find('.select2-search__field');
                            if ($searchInput.length && $searchInput.val()) {
                                searchTerm = $searchInput.val().trim();
                            }
                        }
                    }
                    
                    // PRIORITY 3: Get from activeSelectSearch (most recent, especially if hasNoResults flag)
                    if (!searchTerm) {
                        if (activeSelectSearch.select && activeSelectSearch.selectId === selectId && activeSelectSearch.searchTerm) {
                            searchTerm = activeSelectSearch.searchTerm;
                        }
                    }
                    
                    // PRIORITY 4: Get from stored search terms
                    if (!searchTerm && lastSearchTerm[selectId]) {
                        searchTerm = lastSearchTerm[selectId].trim();
                    }
                    
                    // PRIORITY 5: Try Select2 internal API
                    if (!searchTerm) {
                        const internalTerm = getSearchTermFromSelect2($select);
                        if (internalTerm) {
                            searchTerm = internalTerm;
                        }
                    }
                }
            }
            
            // Reset form
            $('#universal-modal-title').text(mode === 'add' ? 'ADD NEW ENTRY' : title);
            $('#universal-modal-subtitle').text(mode === 'add' ? 'SMART ASSET REGISTRY' : 'Update the details below');
            $('#universal-name').val('').removeClass('is-invalid');
            $('#universal-name-error').text('');
            $('#universal-image').val('');
            $('#universal-image-preview').hide().attr('src', '');
            $('#universal-image-placeholder').show();
            currentEditId = null;
            // Image field toggle
            if (hasImage) {
                $('#image-field').removeClass('d-none').show();
            } else {
                $('#image-field').addClass('d-none').hide();
            }
            
            // Handle type checkboxes change
            $('.universal-type-checkbox').off('change').on('change', function() {
                const $label = $(this).closest('label');
                if ($(this).is(':checked')) {
                    $label.css({
                        'background': '#fff4f0',
                        'border-color': '#ff6b35'
                    });
                    // Set the first checked type as the main type
                    const checkedTypes = $('.universal-type-checkbox:checked').map(function() {
                        return $(this).val();
                    }).get();
                    if (checkedTypes.length > 0) {
                        $('#universal-type').val(checkedTypes[0]); // Use first checked type
                    }
                } else {
                    $label.css({
                        'background': 'white',
                        'border-color': '#e9ecef'
                    });
                    // Update type if no checkboxes are checked
                    const checkedTypes = $('.universal-type-checkbox:checked').map(function() {
                        return $(this).val();
                    }).get();
                    if (checkedTypes.length === 0) {
                        $('#universal-type').val('');
                    } else {
                        $('#universal-type').val(checkedTypes[0]);
                    }
                }
            });
            
            // =========================
            // ADD MODE
            // =========================
            if (mode === 'add') {
                $('#universal-form')
                    .attr('action', $(this).data('route'))
                    .attr('method', 'POST');
                $('#universal-delete-btn').addClass('d-none');
                $('#universal-save-btn').html('<i class="ti ti-check me-2"></i><span>SAVE ENTRY</span>');
                
                // Pre-fill the modal input with the captured search term
                if (searchTerm) {
                    $('#universal-name').val(searchTerm);
                    // Clear the stored term after using it
                    let $select = null;
                    if (currentTargetSelect) {
                        $select = $(currentTargetSelect);
                    } else {
                        const $button = $(this);
                        $select = $button.closest('.input-group').find('select.searchable-select').first();
                    }
                    if ($select && $select.length) {
                        const selectId = $select.attr('id') || $select.attr('name') || 'default';
                        delete lastSearchTerm[selectId];
                    }
                } else {
                    // Last resort: Try one more time after a short delay
                    setTimeout(function() {
                        let finalSearchTerm = '';
                        let $select = null;
                        if (currentTargetSelect) {
                            $select = $(currentTargetSelect);
                        } else {
                            const $button = $(this);
                            $select = $button.closest('.input-group').find('select.searchable-select').first();
                        }
                        
                        if ($select && $select.length) {
                            const selectId = $select.attr('id') || $select.attr('name') || 'default';
                            
                            // Check activeSelectSearch one more time
                            if (activeSelectSearch.selectId === selectId && activeSelectSearch.searchTerm) {
                                finalSearchTerm = activeSelectSearch.searchTerm;
                            }
                            
                            // Check stored terms
                            if (!finalSearchTerm && lastSearchTerm[selectId]) {
                                finalSearchTerm = lastSearchTerm[selectId].trim();
                            }
                            
                            // Set if found
                            if (finalSearchTerm) {
                                $('#universal-name').val(finalSearchTerm);
                                delete lastSearchTerm[selectId];
                            }
                        }
                    }.bind(this), 100);
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
                    alert('Please select an item to edit');
                    return;
                }
                const fetchRoute = $(this).data('fetch-route').replace(':id', selectedId);
                const updateRoute = $(this).data('update-route').replace(':id', selectedId);
                deleteRoute = $(this).data('delete-route').replace(':id', selectedId);
                currentEditId = selectedId;
                $('#universal-form')
                    .attr('action', updateRoute)
                    .attr('method', 'POST');
                $('#universal-save-btn').text('Update');
                $('#universal-delete-btn').removeClass('d-none');
                // Fetch existing data
                $.get(fetchRoute, function(res) {
                    $('#universal-name').val(res.name);
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
                });
            }
        });
        // =========================
        // IMAGE LIVE PREVIEW
        // =========================
        $('#universal-image').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#universal-image-preview')
                        .attr('src', e.target.result)
                        .show();
                };
                reader.readAsDataURL(file);
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
            
            // Get the checked type from checkboxes (use first checked, or the hidden field value)
            const checkedType = $('.universal-type-checkbox:checked').first().val();
            if (checkedType) {
                formData.set('type', checkedType);
            } else if ($('#universal-type').val()) {
                formData.set('type', $('#universal-type').val());
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
                    const option = new Option(res.name, res.id, true, true);
                    const $select = $(currentTargetSelect);
                    $select.find(`option[value="${res.id}"]`).remove();
                    $select.append(option).val(res.id).trigger('change');
                    $('#universal-add-modal').modal('hide');
                    $('#universal-form')[0].reset();
                    // 🔊 Play save sound when dropdown option is saved
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
        $('#universal-delete-btn').on('click', function() {
            if (!confirm('Are you sure?')) return;
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
                        audio.play();
                    }
                    // Remove option from select
                    $(currentTargetSelect)
                        .find(`option[value="${currentEditId}"]`)
                        .remove()
                        .trigger('change');
                    // Close modal
                    $('#universal-add-modal').modal('hide');
                }
            });
        });
        // Dynamic Subcategory Load
        $(document).on('change', 'select.category-select', function() {
            const subSelect = $('#subcategory');
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
        // Restore subcategory on validation error
        setTimeout(() => {
            if ($('#category').val()) {
                $('#category').trigger('change');
            }
            setTimeout(() => {
                const $select = $('#subcategory');
                const oldVal = $select.data('old-subcat');
                if (oldVal && $select.find(`option[value="${oldVal}"]`).length) {
                    $select.val(oldVal);
                }
            }, 500);
                        }, 150);

        // =========================
        // PART NUMBER COUNT & ITEMS VIEW
        // =========================
        
        // Handle part number selection change - fetch count
        $('#part_number_id').on('change', function() {
            const partNumberId = $(this).val();
            const countContainer = $('#partNumberCountContainer');
            const countSpan = $('#partNumberCount');
            
            if (partNumberId && partNumberId !== '') {
                // Fetch count
                $.ajax({
                    url: '{{ route("items.count.by.part.number", ":id") }}'.replace(':id', partNumberId),
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            countSpan.text(response.count);
                            countContainer.show();
                        }
                    },
                    error: function() {
                        countContainer.hide();
                    }
                });
            } else {
                countContainer.hide();
            }
        });

        // Double-click handler to show items in modal
        $(document).on('dblclick', '#partNumberCount', function() {
            const partNumberId = $('#part_number_id').val();
            if (!partNumberId || partNumberId === '') {
                toastr.warning('Please select a part number first');
                return;
            }

            // Show loading
            Swal.fire({
                title: 'Loading...',
                text: 'Fetching items...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Fetch items
            $.ajax({
                url: '{{ route("items.by.part.number", ":id") }}'.replace(':id', partNumberId),
                type: 'GET',
                success: function(response) {
                    Swal.close();
                    if (response.success && response.items && response.items.length > 0) {
                        showPartNumberItemsModal(response.items, partNumberId);
                    } else {
                        toastr.info('No items found for this part number');
                    }
                },
                error: function() {
                    Swal.close();
                    toastr.error('Error loading items');
                }
            });
        });

        // Function to show items in SweetAlert modal
        function showPartNumberItemsModal(items, partNumberId) {
            const csrfToken = $('input[name="_token"]').val();
            let itemsHtml = '';
            
            if (items.length === 0) {
                itemsHtml = '<tr><td colspan="7" class="text-center">No items found</td></tr>';
            } else {
                items.forEach(function(item) {
                    const activeBadge = item.is_active 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-danger">Inactive</span>';

                    itemsHtml += `
                        <tr>
                            <td>
                                <img src="${item.image}" width="50" height="50"
                                    class="rounded item-image-modal" style="cursor:pointer;"
                                    data-src="${item.image}">
                            </td>
                            <td>${item.bar_code || '-'}</td>
                            <td>${item.product_name || '-'}</td>
                            <td><span class="badge bg-info">${item.type || '-'}</span></td>
                            <td>${item.category_name || '-'}</td>
                            <td>${item.company_name || '-'}</td>
                            <td>${activeBadge}</td>
                            <td>
                                <a href="${item.show_url}" class="btn btn-sm btn-info" target="_blank" title="View">
                                    <i data-feather="eye"></i>
                                </a>
                                <a href="${item.edit_url}" class="btn btn-sm btn-primary" target="_blank" title="Edit">
                                    <i data-feather="edit"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                });
            }

            const modalHtml = `
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th>Image</th>
                                <th>Bar Code</th>
                                <th>Product Name</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Company</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                        </tbody>
                    </table>
                </div>
            `;

            Swal.fire({
                title: `Items Using This Part Number (${items.length})`,
                html: modalHtml,
                width: '95%',
                customClass: {
                    popup: 'swal-wide'
                },
                showCloseButton: true,
                showConfirmButton: true,
                confirmButtonText: 'Close',
                confirmButtonColor: '#3085d6',
                didOpen: () => {
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                    // Handle image click to show larger view
                    $('.item-image-modal').on('click', function() {
                        const imgSrc = $(this).data('src');
                        Swal.fire({
                            imageUrl: imgSrc,
                            imageWidth: '80%',
                            imageAlt: 'Item Image',
                            showCloseButton: true,
                            showConfirmButton: false
                        });
                    });
                }
            });
        }

        // =========================
        // ENSURE quality_id AND technology ARE SUBMITTED ON FORM SUBMIT
        // This ensures they are sent even if fields are hidden by Alpine.js
        // =========================
        $('#mainItemForm').on('submit', function(e) {
            // Get quality_id from any visible quality field
            const qualityVal = $('#quality').val() || $('#quality_filters').val() || $('#quality_breakpad').val() || '';
            
            // Remove any existing hidden quality_id inputs to avoid duplicates
            $(this).find('input[type="hidden"][name="quality_id"]').remove();
            
            // Add hidden input if quality_id has a value
            if (qualityVal) {
                $(this).append('<input type="hidden" name="quality_id" value="' + qualityVal + '">');
            }
            
            // Get technology from any visible technology field
            const techVal = $('#technology_select').val() || $('#technology_oil_select').val() || '';
            
            // Remove any existing hidden technology inputs to avoid duplicates
            $(this).find('input[type="hidden"][name="technology"]').remove();
            
            // Add hidden input if technology has a value
            if (techVal) {
                $(this).append('<input type="hidden" name="technology" value="' + techVal + '">');
            }
        });
    });
</script>
<style>
    .swal-wide {
        max-width: 1400px !important;
    }
    #partNumberCount:hover {
        color: #0056b3 !important;
        text-decoration: underline !important;
    }
</style>
@endpush
