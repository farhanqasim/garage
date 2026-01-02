{{--  resources/views/admin/items/duplicate.blade.php  --}}
@extends('layouts.app')

@section('title', 'Duplicate Product')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h2 class="fw-bold">Duplicate Product</h2>
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

            <form method="POST"
                  action="{{ route('all.items.store') }}"
                  enctype="multipart/form-data"
                  id="mainItemForm">
                @csrf
                <input type="hidden" name="is_duplicate" value="1">

                <div class="container">
                    <div class="row" id="itemFormsContainer">
                        <!-- Default Form -->
                        <div class="col-md-12 item-form">
                            <div class="row">
                                <div class="col-md-12">
                                    <h4>Item Info:</h4>
                                    <div class="row mt-4">
                                        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">

                                        {{-- ==== BARCODE ==== --}}
                                        <div class="row mt-1 border p-2">
                                            <div class="col-md-4">
                                                <label for="itemBarCode">Product Bar Code:</label>
                                                <div class="input-group">
                                                    <input type="text"
                                                           class="form-control @error('bar_code') is-invalid @enderror"
                                                           name="bar_code"
                                                           id="itemBarCode"
                                                           value="{{ old('bar_code', $item->bar_code) }}" required />
                                                    <button type="button" class="btn btn-primary generate-code-btn">
                                                        <i data-feather="refresh-cw"></i>
                                                    </button>
                                                    @error('bar_code')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- ==== NAME ==== --}}
                                            <div class="col-sm-4">
                                                <label for="itemname">Product Name:</label>
                                                <input type="text"
                                                       class="form-control @error('name') is-invalid @enderror"
                                                       name="name"
                                                       id="itemname"
                                                       value="{{ old('name', $item->name) }}" required />
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            {{-- ==== MILEAGE ==== --}}
                                            <div class="col-sm-4">
                                                <label for="mileage">Mileage:</label>
                                                <div class="input-group">
                                                    <select class="form-control mileage-select @error('mileage') is-invalid @enderror"
                                                            id="mileage" name="mileage">
                                                        <option value="">Select Mileage</option>
                                                        @foreach ($milleages as $milleage)
                                                            <option value="{{ $milleage->name }}"
                                                                    {{ old('mileage', $item->mileage) == $milleage->name ? 'selected' : '' }}>
                                                                {{ $milleage->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" class="btn btn-primary open-universal-modal"
                                                            data-title="Add Mileage"
                                                            data-route="{{ route('post.item.mileage') }}"
                                                            data-target-select=".mileage-select">
                                                        <i data-feather="plus"></i>
                                                    </button>
                                                    @error('mileage')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- ==== ITEM TYPE ==== --}}
                                            <div class="col-md-4 mt-3">
                                                <label for="type">Item Type:</label>
                                                <div class="input-group">
                                                    <select class="form-control product-type @error('type') is-invalid @enderror"
                                                            name="type" id="type">
                                                        <option value="">Select Item Type</option>
                                                        @foreach ($item_types as $type)
                                                            <option value="{{ $type->name }}"
                                                                    {{ old('type', $item->type) == $type->name ? 'selected' : '' }}>
                                                                {{ strtoupper($type->name) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" class="btn btn-primary open-universal-modal"
                                                            data-title="Add Product Type"
                                                            data-route="{{ route('post.producttype') }}"
                                                            data-target-select=".product-type">
                                                        <i data-feather="plus"></i>
                                                    </button>
                                                    @error('type')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- ==== PLATO ==== --}}
                                            <div class="col-md-4 mt-3">
                                                <label for="platos">Plat:</label>
                                                <div class="input-group">
                                                    <select class="form-control plat-select @error('plato') is-invalid @enderror"
                                                            name="plato" id="platos">
                                                        <option value="">Select Plat</option>
                                                        @foreach ($platos as $plato)
                                                            <option value="{{ $plato->name }}"
                                                                    {{ old('plato', $item->plato) == $plato->name ? 'selected' : '' }}>
                                                                {{ $plato->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" class="btn btn-primary open-universal-modal"
                                                            data-title="Add Plat"
                                                            data-route="{{ route('post.platos') }}"
                                                            data-target-select=".plat-select">
                                                        <i data-feather="plus"></i>
                                                    </button>
                                                    @error('plato')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- ==== AMPHORS ==== --}}
                                            <div class="col-sm-4 mt-3">
                                                <label for="amphors">Amphors:</label>
                                                <div class="input-group">
                                                    <select class="form-control amphor-select @error('amphors') is-invalid @enderror"
                                                            name="amphors" id="amphors">
                                                        <option value="">Select Amphors</option>
                                                        @foreach ($amphors as $amphor)
                                                            <option value="{{ $amphor->name }}"
                                                                    {{ old('amphors', $item->amphors) == $amphor->name ? 'selected' : '' }}>
                                                                {{ $amphor->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" class="btn btn-primary open-universal-modal"
                                                            data-title="Add Amphor"
                                                            data-route="{{ route('post.amphors') }}"
                                                            data-target-select=".amphor-select">
                                                        <i data-feather="plus"></i>
                                                    </button>
                                                    @error('amphors')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        {{-- ==== LINEITEMS / COMPANY / CATEGORY / SUBCATEGORY / VEHICLE ==== --}}
                                        <div class="row border p-2">
                                            <div class="col-sm-12 mt-2">
                                                <div class="row">

                                                    {{-- LineItems --}}
                                                    <div class="col-md-4 mt-2">
                                                        <label for="lineitems">LineItems:</label>
                                                        <div class="input-group">
                                                            <select class="form-control lineitem-select @error('lineitems') is-invalid @enderror"
                                                                    name="lineitems" id="lineitems">
                                                                <option value="">Select Lineitems</option>
                                                                @foreach ($lineitems as $line)
                                                                    <option value="{{ $line->name }}"
                                                                            {{ old('lineitems', $item->lineitems) == $line->name ? 'selected' : '' }}>
                                                                        {{ $line->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <button type="button" class="btn btn-primary open-universal-modal"
                                                                    data-title="Add Lineitem"
                                                                    data-route="{{ route('post.lineitems') }}"
                                                                    data-target-select=".lineitem-select">
                                                                <i data-feather="plus"></i>
                                                            </button>
                                                            @error('lineitems')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    {{-- Company --}}
                                                    <div class="col-md-4 mt-2">
                                                        <label for="company">Company:</label>
                                                        <div class="input-group">
                                                            <select class="form-control company-select @error('company') is-invalid @enderror"
                                                                    name="company" id="company">
                                                                <option value="">Select Company</option>
                                                                @foreach ($Companies as $company)
                                                                    <option value="{{ $company->name }}"
                                                                            {{ old('company', $item->company) == $company->name ? 'selected' : '' }}>
                                                                        {{ $company->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <button type="button" class="btn btn-primary open-universal-modal"
                                                                    data-title="Add Company"
                                                                    data-route="{{ route('post.companies') }}"
                                                                    data-target-select=".company-select">
                                                                <i data-feather="plus"></i>
                                                            </button>
                                                            @error('company')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    {{-- Category --}}
                                                    <div class="col-md-4 mt-2">
                                                        <label for="category">Category:</label>
                                                        <div class="input-group">
                                                            <select class="form-control category-select @error('category_id') is-invalid @enderror"
                                                                    name="category_id" id="category" required>
                                                                <option value="">Select Category</option>
                                                                @foreach ($Categories as $category)
                                                                    <option value="{{ $category->id }}"
                                                                            {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
                                                                        {{ $category->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <button type="button" class="btn btn-primary open-universal-modal"
                                                                    data-title="Add Category"
                                                                    data-route="{{ route('post.item.category') }}"
                                                                    data-target-select=".category-select">
                                                                <i data-feather="plus"></i>
                                                            </button>
                                                            @error('category_id')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    {{-- Sub Category (AJAX) --}}
                                                    <div class="col-md-4 mt-2">
                                                        <label for="subcategory">Sub Category:</label>
                                                        <div class="input-group">
                                                            <select class="form-control subcategory-select @error('subcategory_id') is-invalid @enderror"
                                                                    name="subcategory_id" id="subcategory"
                                                                    data-old-subcat="{{ old('subcategory_id', $item->subcategory_id) }}">
                                                                <option value="">Select Subcategory</option>
                                                            </select>
                                                            <button type="button" class="btn btn-primary open-universal-modal"
                                                                    data-title="Add SubCategory"
                                                                    data-route="{{ route('post.item.category') }}"
                                                                    data-target-select=".subcategory-select">
                                                                <i data-feather="plus"></i>
                                                            </button>
                                                            @error('subcategory_id')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    {{-- Vehicle Type --}}
                                                    <div class="col-md-4 mt-2">
                                                        <label for="veh_type">Veh Type:</label>
                                                        <div class="input-group">
                                                            <select class="form-control vehical-select @error('veh_type') is-invalid @enderror"
                                                                    name="veh_type" id="veh_type">
                                                                <option value="">Select Vehicle Type</option>
                                                                @foreach ($Vehicals as $Vehical)
                                                                    <option value="{{ $Vehical->name }}"
                                                                            {{ old('veh_type', $item->veh_type) == $Vehical->name ? 'selected' : '' }}>
                                                                        {{ $Vehical->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <button type="button" class="btn btn-primary open-universal-modal"
                                                                    data-title="Add Vehicle"
                                                                    data-route="{{ route('post.vehical') }}"
                                                                    data-target-select=".vehical-select">
                                                                <i data-feather="plus"></i>
                                                            </button>
                                                            @error('veh_type')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- ==== THUMBNAIL & IMAGES & CHECKBOXES ==== --}}
                                            <div class="col-md-12 mt-4 mb-3">
                                                <div class="row">
                                                    <div class="col-md-4 mt-3">
                                                        <label for="p_brochure">Product brochure:</label>
                                                        <input type="text"
                                                               class="form-control @error('p_brochure') is-invalid @enderror"
                                                               id="p_brochure" name="p_brochure"
                                                               value="{{ old('p_brochure', $item->p_brochure) }}" />
                                                        @error('p_brochure')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Thumbnail --}}
                                                    <div class="col-md-4 mt-3">
                                                        <label for="imageInput" class="form-label fw-bold">Thumbnail</label>
                                                        <div class="d-flex gap-2 input-group">
                                                            <input type="file" id="imageInput" accept="image/*"
                                                                   name="image" class="form-control @error('image') is-invalid @enderror">
                                                            <button type="button" id="uploadBtn" class="btn btn-primary btn-sm">
                                                                <i data-feather="upload"></i>
                                                            </button>
                                                        </div>
                                                        <div id="previewContainer" class="text-center mt-2" style="display:none;">
                                                            <div class="d-flex">
                                                                <img id="imagePreview" src="https://pdis.co.kr/img/image.jpg"
                                                                     alt="Preview" class="img-fluid rounded border"
                                                                     style="max-height:150px; object-fit:cover;">
                                                                <div>
                                                                    <button type="button" id="removeBtn"
                                                                            class="btn btn-danger btn-sm">
                                                                        <i data-feather="x"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @error('image')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Product Images --}}
                                                    <div class="col-md-4 mt-3">
                                                        <label for="imagesInput" class="form-label fw-bold">Product Images</label>
                                                        <div class="d-flex gap-2 input-group">
                                                            <input type="file" id="imagesInput" accept="image/*"
                                                                   name="images[]" class="form-control @error('images.*') is-invalid @enderror"
                                                                   multiple>
                                                            <button type="button" id="uploadImagesBtn"
                                                                    class="btn btn-primary btn-sm">
                                                                <i data-feather="upload"></i>
                                                            </button>
                                                        </div>
                                                        <div id="imagesPreviewContainer" class="mt-2" style="display: none;">
                                                            <div class="d-flex flex-wrap gap-2" id="imagesPreview"></div>
                                                        </div>
                                                        @error('images.*')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Checkboxes --}}
                                                    <div class="col-md-4">
                                                        <div class="d-flex justify-content-between align-items-center pt-4">
                                                            <div class="section-box">
                                                                <div class="form-check">
                                                                    <input type="hidden" name="is_active" value="0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                           name="is_active" id="isActive" value="1"
                                                                           style="width:20px;height:20px"
                                                                           {{ old('is_active', $item->is_active) ? 'checked' : '' }} />
                                                                    <label class="form-check-label ms-1 mt-1" for="isActive">Is Active</label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input type="hidden" name="auto_deactive" value="0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                           name="auto_deactive" id="autoDeactive" value="1"
                                                                           style="width:20px;height:20px"
                                                                           {{ old('auto_deactive', $item->auto_deactive) ? 'checked' : '' }} />
                                                                    <label class="form-check-label ms-1 mt-1" for="autoDeactive">Auto De-Active</label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input type="hidden" name="is_dead" value="0">
                                                                    <input class="form-check-input" type="checkbox"
                                                                           name="is_dead" id="isDead" value="1"
                                                                           style="width:20px;height:20px"
                                                                           {{ old('is_dead', $item->is_dead) ? 'checked' : '' }} />
                                                                    <label class="form-check-label ms-1 mt-1" for="isDead">Is Dead Item</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- ==== CAR DETAILS ==== --}}
                                        <div class="row mb-3 border">
                                            @foreach ([
                                                'car_company' => 'Car Company',
                                                'car_name' => 'Car Name',
                                                'car_model_name' => 'Car Model Name',
                                                'car_manufactur_country' => 'Car Manufacture Country',
                                                'car_manufacture_year' => 'Car Manufacture Year',
                                                'volt' => 'Volt',
                                                'cca' => 'CCA',
                                                'minus_pool_direction' => 'Minus Pool Direction',
                                                'tecnology' => 'Tecnology',
                                                'grade' => 'Grade',
                                                'farmula' => 'Farmulas',
                                                'serial_number' => 'Serial Number',
                                                'battery_size' => 'Battery Size',
                                            ] as $field => $label)
                                                <div class="col-md-3 mt-3">
                                                    <label for="{{ $field }}">{{ $label }}:</label>
                                                    @if($field === 'car_manufacture_year')
                                                        <input type="date"
                                                               class="form-control @error($field) is-invalid @enderror"
                                                               id="{{ $field }}" name="{{ $field }}"
                                                               value="{{ old($field, $item->{$field}) }}" />
                                                    @else
                                                        <input type="text"
                                                               class="form-control @error($field) is-invalid @enderror"
                                                               id="{{ $field }}" name="{{ $field }}"
                                                               value="{{ old($field, $item->{$field}) }}" />
                                                    @endif
                                                    @error($field)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- ==== PACKING & OTHER INFO ==== --}}
                                        <div class="row mt-3 mb-4">
                                            <div class="col-md-6">
                                                <div class="row mb-3">
                                                    @foreach ([
                                                        'bussiness_location' => 'Bussiness Location',
                                                        'p_quality' => 'Quality',
                                                        'pro_part_number' => 'Part Number',
                                                        'l_stock' => 'Low Stock',
                                                        'm_stock' => 'Maintain Stock',
                                                    ] as $field => $label)
                                                        <div class="col-6 mt-3">
                                                            <label for="{{ $field }}">{{ $label }}:</label>
                                                            <input type="text"
                                                                   class="form-control @error($field) is-invalid @enderror"
                                                                   id="{{ $field }}" name="{{ $field }}"
                                                                   value="{{ old($field, $item->{$field}) }}" />
                                                            @error($field)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    @endforeach

                                                    {{-- Product Unit --}}
                                                    <div class="col-6 mt-3">
                                                        <label for="product_unit">Product Unit:</label>
                                                        <div class="input-group">
                                                            <select class="form-control pro_unit-select @error('product_unit') is-invalid @enderror"
                                                                    name="product_unit" id="product_unit">
                                                                <option value="">Select Unit</option>
                                                                @foreach ($units as $unit)
                                                                    <option value="{{ $unit->name }}"
                                                                            {{ old('product_unit', $item->product_unit) == $unit->name ? 'selected' : '' }}>
                                                                        {{ $unit->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <button type="button" class="btn btn-primary open-universal-modal"
                                                                    data-title="Add Product Unit"
                                                                    data-route="{{ route('post.units') }}"
                                                                    data-target-select=".pro_unit-select">
                                                                <i data-feather="plus"></i>
                                                            </button>
                                                            @error('product_unit')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <h3>Packing Info</h3>
                                                <div class="row border mt-3 p-3">
                                                    @foreach ([
                                                        'packing' => ['label' => 'Packing', 'options' => $packings],
                                                        'scale'   => ['label' => 'Scale',   'options' => $scales],
                                                    ] as $field => $info)
                                                        <div class="col-6">
                                                            <label for="{{ $field }}">{{ $info['label'] }}:</label>
                                                            <div class="input-group">
                                                                <select class="form-control {{ $field }}-select @error($field) is-invalid @enderror"
                                                                        name="{{ $field }}" id="{{ $field }}">
                                                                    <option value="">Select {{ $info['label'] }}</option>
                                                                    @foreach ($info['options'] as $opt)
                                                                        <option value="{{ $opt->name }}"
                                                                                {{ old($field, $item->{$field}) == $opt->name ? 'selected' : '' }}>
                                                                            {{ $opt->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <button type="button" class="btn btn-primary open-universal-modal"
                                                                        data-title="Add {{ $info['label'] }}"
                                                                        data-route="{{ route('post.' . ($field === 'packing' ? 'packings' : 'scales')) }}"
                                                                        data-target-select=".{{ $field }}-select">
                                                                    <i data-feather="plus"></i>
                                                                </button>
                                                                @error($field)
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    @endforeach

                                                    <div class="col-6 mt-3">
                                                        <label for="filling">Filling:</label>
                                                        <input type="number" step="0.01"
                                                               class="form-control @error('filling') is-invalid @enderror"
                                                               id="filling" name="filling"
                                                               value="{{ old('filling', $item->filling) }}" />
                                                        @error('filling')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="col-6 mt-3">
                                                        <label for="weight_for_delivery">Weight</label>
                                                        <input type="number" step="0.01"
                                                               class="form-control @error('weight_for_delivery') is-invalid @enderror"
                                                               id="weight_for_delivery" name="weight_for_delivery"
                                                               value="{{ old('weight_for_delivery', $item->weight_for_delivery) }}" />
                                                        @error('weight_for_delivery')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- ==== RATE & OTHER INFO ==== --}}
                                            <div class="col-md-6">
                                                <h3 class="mb-1">Rate & Dis Info</h3>
                                                <div class="row mt-3 border p-3">
                                                    <div class="col-12">
                                                        <label for="packing_purchase_rate">Packing Purchase Rate:</label>
                                                        <input type="number" step="0.01"
                                                               class="form-control form-control-sm @error('packing_purchase_rate') is-invalid @enderror"
                                                               name="packing_purchase_rate" id="packing_purchase_rate"
                                                               value="{{ old('packing_purchase_rate', $item->packing_purchase_rate ?? 0.0) }}" />
                                                        @error('packing_purchase_rate')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    {{-- Add other rate fields here exactly as in your create view --}}
                                                </div>

                                                <h3 class="mt-3">Other Info</h3>
                                                <div class="row border mt-2 p-3">
                                                    @foreach ([
                                                        'min_qty' => 'Min Qty',
                                                        'max_qty' => 'Max Qty',
                                                        'update_date' => 'Update Date',
                                                        'rack' => 'Rack',
                                                        'supplier' => 'Supplier',
                                                    ] as $field => $label)
                                                        <div class="col-6 mt-3">
                                                            <label for="{{ $field }}">{{ $label }}:</label>
                                                            @if($field === 'update_date')
                                                                <input type="date"
                                                                       class="form-control @error($field) is-invalid @enderror"
                                                                       id="{{ $field }}" name="{{ $field }}"
                                                                       value="{{ old($field, $item->{$field}) }}" />
                                                            @else
                                                                <input type="text"
                                                                       class="form-control @error($field) is-invalid @enderror"
                                                                       id="{{ $field }}" name="{{ $field }}"
                                                                       value="{{ old($field, $item->{$field}) }}" />
                                                            @endif
                                                            @error($field)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            {{-- ==== DESCRIPTION ==== --}}
                                            <div class="mb-3 mt-4">
                                                <label for="content" class="form-label fw-bold">Product Description</label>
                                                <textarea id="summernote" name="pro_dis" class="form-control">
                                                    {!! old('pro_dis', $item->pro_dis ?? '') !!}
                                                </textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="page-btn d-flex justify-content-end">
                    <a href="{{ route('all.items') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary me-2">Save Duplicate</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ==== UNIVERSAL MODAL ==== --}}
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
$(document).ready(function() {
    feather.replace();

    // ---------- BARCODE ----------
    function generateRandomItemCode() {
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < 10; i++) {
            result += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        return result;
    }

    if (!$('#itemBarCode').val()) {
        $('#itemBarCode').val(generateRandomItemCode());
    }

    $(document).on('click', '.generate-code-btn', function() {
        $('#itemBarCode').val(generateRandomItemCode());
    });

    // ---------- THUMBNAIL ----------
    const imageInput = $('#imageInput')[0];
    const preview = $('#imagePreview')[0];
    const container = $('#previewContainer')[0];
    const uploadBtn = $('#uploadBtn')[0];
    const removeBtn = $('#removeBtn')[0];
    const defaultImg = "https://pdis.co.kr/img/image.jpg";

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
        container.style.display = 'none';
    };

    // ---------- MULTIPLE IMAGES ----------
    const imagesInput = $('#imagesInput')[0];
    const imagesPreview = $('#imagesPreview')[0];
    const imagesContainer = $('#imagesPreviewContainer')[0];
    const uploadImagesBtn = $('#uploadImagesBtn')[0];

    uploadImagesBtn.onclick = () => imagesInput.click();
    imagesInput.onchange = function() {
        imagesPreview.innerHTML = '';
        const files = this.files;
        if (files.length > 0) {
            imagesContainer.style.display = 'block';
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
                        Array.from(files).filter((_, idx) => idx !== i).forEach(f => dt.items.add(f));
                        imagesInput.files = dt.files;
                        if (imagesPreview.children.length === 0) imagesContainer.style.display = 'none';
                        feather.replace();
                    };
                    div.appendChild(img);
                    div.appendChild(remove);
                    imagesPreview.appendChild(div);
                    feather.replace();
                };
                reader.readAsDataURL(file);
            });
        } else {
            imagesContainer.style.display = 'none';
        }
    };

    // ---------- UNIVERSAL MODAL ----------
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
                if (res.success) {
                    const val = res.id || res.name;
                    const opt = `<option value="${val}" selected>${res.name}</option>`;
                    $(currentTargetSelect).each(function() {
                        if (!$(this).find(`option[value="${val}"]`).length) {
                            $(this).append(opt);
                        }
                    });
                    $('#universal-add-modal').modal('hide');
                }
            }
        });
    });

    // ---------- SUBCATEGORY AJAX ----------
    $(document).on('change', '#category', function() {
        const subSelect = $('#subcategory');
        const catId = $(this).val();
        subSelect.empty().append('<option value="">Select Subcategory</option>');
        if (catId) {
            $.getJSON(`/admin/categories/${catId}/subcategories`, function(data) {
                data.forEach(item => {
                    subSelect.append(`<option value="${item.id}">${item.name}</option>`);
                });
                const oldSub = subSelect.data('old-subcat');
                if (oldSub) subSelect.val(oldSub);
            });
        }
    });

    // Trigger on page load (for duplicate)
    if ($('#category').val()) {
        $('#category').trigger('change');
    }
});
</script>
@endpush
