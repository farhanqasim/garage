@extends('layouts.app')

@push('styles')
<style>
    .product-bar h6 { text-transform: uppercase; }
    .view-toggle-switch { display: flex; align-items: center; gap: 10px; }
    .view-toggle-switch .form-check-input { width: 3rem; height: 1.5rem; cursor: pointer; }
    .view-toggle-switch .form-check-label { cursor: pointer; user-select: none; }
    [data-view="internal"] { transition: opacity 0.2s; }
</style>
@endpush

@section('title', $item->name . ' - Product Details')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Product Details</h4>
            <h6>Full details of a product</h6>
        </div>
        <div class="page-btn d-flex align-items-center gap-3">
            <div class="view-toggle-switch">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="viewModeSwitch">
                    <label class="form-check-label" for="viewModeSwitch">
                        <span id="viewModeLabel">Internal View</span>
                    </label>
                </div>
            </div>
            <a href="{{ url()->current() }}" target="_blank" class="btn btn-outline-primary">
                <i class="ti ti-external-link me-1"></i> View in New Tab
            </a>
            <button type="button" id="shareWhatsAppBtn" class="btn btn-success" onclick="toggleWhatsAppSection()">
                <i class="ti ti-brand-whatsapp me-1"></i> Share on WhatsApp
            </button>
        </div>
    </div>

    <!-- WhatsApp Share Input Section (Hidden by default) -->
    <div id="whatsappShareSection" class="card mb-3" style="display: none;">
        <div class="card-body">
            <h6 class="mb-3">
                <i class="ti ti-brand-whatsapp me-2"></i>Enter Phone Number to Share on WhatsApp
            </h6>
            <form id="whatsappShareForm">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label for="countryCode" class="form-label small">Country Code <span class="text-danger">*</span></label>
                        <select class="form-select" id="countryCode" name="countryCode">
                            <option value="1">🇺🇸 +1 (US/CA)</option>
                            <option value="44">🇬🇧 +44 (UK)</option>
                            <option value="91">🇮🇳 +91 (India)</option>
                            <option value="92" selected>🇵🇰 +92 (Pakistan)</option>
                            <option value="971">🇦🇪 +971 (UAE)</option>
                            <option value="966">🇸🇦 +966 (Saudi)</option>
                            <option value="974">🇶🇦 +974 (Qatar)</option>
                            <option value="965">🇰🇼 +965 (Kuwait)</option>
                            <option value="973">🇧🇭 +973 (Bahrain)</option>
                            <option value="968">🇴🇲 +968 (Oman)</option>
                            <option value="961">🇱🇧 +961 (Lebanon)</option>
                            <option value="20">🇪🇬 +20 (Egypt)</option>
                            <option value="27">🇿🇦 +27 (South Africa)</option>
                            <option value="49">🇩🇪 +49 (Germany)</option>
                            <option value="33">🇫🇷 +33 (France)</option>
                            <option value="39">🇮🇹 +39 (Italy)</option>
                            <option value="34">🇪🇸 +34 (Spain)</option>
                            <option value="31">🇳🇱 +31 (Netherlands)</option>
                            <option value="32">🇧🇪 +32 (Belgium)</option>
                            <option value="41">🇨🇭 +41 (Switzerland)</option>
                            <option value="43">🇦🇹 +43 (Austria)</option>
                            <option value="46">🇸🇪 +46 (Sweden)</option>
                            <option value="47">🇳🇴 +47 (Norway)</option>
                            <option value="45">🇩🇰 +45 (Denmark)</option>
                            <option value="358">🇫🇮 +358 (Finland)</option>
                            <option value="353">🇮🇪 +353 (Ireland)</option>
                            <option value="351">🇵🇹 +351 (Portugal)</option>
                            <option value="30">🇬🇷 +30 (Greece)</option>
                            <option value="48">🇵🇱 +48 (Poland)</option>
                            <option value="420">🇨🇿 +420 (Czech)</option>
                            <option value="36">🇭🇺 +36 (Hungary)</option>
                            <option value="40">🇷🇴 +40 (Romania)</option>
                            <option value="7">🇷🇺 +7 (Russia)</option>
                            <option value="81">🇯🇵 +81 (Japan)</option>
                            <option value="82">🇰🇷 +82 (South Korea)</option>
                            <option value="86">🇨🇳 +86 (China)</option>
                            <option value="852">🇭🇰 +852 (Hong Kong)</option>
                            <option value="65">🇸🇬 +65 (Singapore)</option>
                            <option value="60">🇲🇾 +60 (Malaysia)</option>
                            <option value="62">🇮🇩 +62 (Indonesia)</option>
                            <option value="66">🇹🇭 +66 (Thailand)</option>
                            <option value="84">🇻🇳 +84 (Vietnam)</option>
                            <option value="63">🇵🇭 +63 (Philippines)</option>
                            <option value="61">🇦🇺 +61 (Australia)</option>
                            <option value="64">🇳🇿 +64 (New Zealand)</option>
                            <option value="55">🇧🇷 +55 (Brazil)</option>
                            <option value="52">🇲🇽 +52 (Mexico)</option>
                            <option value="54">🇦🇷 +54 (Argentina)</option>
                            <option value="56">🇨🇱 +56 (Chile)</option>
                            <option value="57">🇨🇴 +57 (Colombia)</option>
                            <option value="51">🇵🇪 +51 (Peru)</option>
                            <option value="90">🇹🇷 +90 (Turkey)</option>
                            <option value="98">🇮🇷 +98 (Iran)</option>
                            <option value="212">🇲🇦 +212 (Morocco)</option>
                            <option value="213">🇩🇿 +213 (Algeria)</option>
                            <option value="216">🇹🇳 +216 (Tunisia)</option>
                            <option value="254">🇰🇪 +254 (Kenya)</option>
                            <option value="234">🇳🇬 +234 (Nigeria)</option>
                            <option value="233">🇬🇭 +233 (Ghana)</option>
                            <option value="256">🇺🇬 +256 (Uganda)</option>
                            <option value="255">🇹🇿 +255 (Tanzania)</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="phoneNumber" class="form-label small">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" 
                            placeholder="3001234567" required pattern="[0-9]{7,12}">
                        <small class="text-muted">Enter phone number without country code</small>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-success w-100" id="generateAndShareBtn">
                            <i class="ti ti-brand-whatsapp me-1"></i> Generate PDF & Share
                        </button>
                    </div>
                </div>
                <div class="alert alert-info mt-3 mb-0">
                    <i class="ti ti-info-circle me-2"></i>
                    <strong>Item:</strong> {{ $item->product_item->name ?? 'N/A' }} will be converted to PDF and shared on WhatsApp
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Left Side - Details -->
        <div class="col-lg-8 col-sm-12">
            <div class="card">
                <div class="card-body">

                    <!-- Barcode (Internal only) -->
                    <div class="bar-code-view text-center mb-4" data-view="internal">
                        @if($item->barcode_image && file_exists(public_path($item->barcode_image)))
                            <div>
                                <img src="{{ asset($item->barcode_image) }}" alt="Barcode" style="max-height:80px;">
                            <h6>{{ $item->bar_code ?? '-' }}</h6>
                            </div>
                        @else
                            <img src="{{ asset('assets/img/barcode/barcode1.png') }}" alt="barcode">
                        @endif
                        <a href="javascript:void(0);" class="printimg ms-3" onclick="window.print()">
                            <img src="{{ asset('assets/img/icons/printer.svg') }}" alt="print">
                        </a>

                    </div>

                    <div class="productdetails">
                        <ul class="product-bar">
                            <li>
                                <h4>Product Name</h4>
                                <h6>{{ $item->product_item->name ?? '-' }}</h6>
                            </li>

                            <li data-view="internal">
                                <h4>Barcode</h4>
                                <h6>{{ $item->bar_code ?? '-' }}</h6>
                            </li>

                            <li>
                                <h4>Category</h4>
                                <h6>{{ $item->category?->name ?? 'N/A' }}</h6>
                            </li>

                            @if($item->subcategory)
                            <li>
                                <h4>Subcategory</h4>
                                <h6>{{ $item->subcategory->name ?? 'N/A' }}</h6>
                            </li>
                            @endif

                            <li data-view="internal">
                                <h4>Type</h4>
                                <h6><span class="badge {{ $item->type == 'parts' ? 'bg-success' : ($item->type == 'battery' ? 'bg-warning' : 'bg-info') }}">{{ ucfirst($item->type ?? 'N/A') }}</span></h6>
                            </li>

                            <!-- COMMON FIELDS FOR ALL TYPES -->
                            @if(in_array($item->type, ['parts', 'battery', 'oil', 'scrap', 'filters', 'breakpad']))
                                <li data-view="internal">
                                    <h4>Unit</h4>
                                    <h6>{{ $item->unit_item->name ?? ($item->unit_item->unit ?? 'Piece') }}</h6>
                                </li>
                            @endif

                            <!-- PARTS SPECIFIC FIELDS -->
                            @if($item->type == 'parts')
                                @if($item->partnumber_item)
                                <li data-view="internal">
                                    <h4>Part Number</h4>
                                    <h6>{{ $item->partnumber_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->company_item)
                                <li>
                                    <h4>Company</h4>
                                    <h6>{{ $item->company_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->quality_item)
                                <li>
                                    <h4>Quality</h4>
                                    <h6>{{ $item->quality_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->group_item)
                                <li data-view="internal">
                                    <h4>Group Name</h4>
                                    <h6>{{ $item->group_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->technology_item)
                                <li data-view="internal">
                                    <h4>Series</h4>
                                    <h6>{{ $item->technology_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->vehical_item)
                                <li data-view="internal">
                                    <h4>Vehicle Model</h4>
                                    <h6>{{ ($item->vehical_item->model_vehical && $item->vehical_item->model_vehical->name) ? $item->vehical_item->model_vehical->name : '-' }}</h6>
                                </li>
                                <li data-view="internal">
                                    <h4>Manufacturer</h4>
                                    <h6>{{ ($item->vehical_item->manutacturer_vehical && $item->vehical_item->manutacturer_vehical->name) ? $item->vehical_item->manutacturer_vehical->name : '-' }}</h6>
                                </li>
                                <li data-view="internal">
                                    <h4>Engine CC</h4>
                                    <h6>{{ ($item->vehical_item->engine_vehical && $item->vehical_item->engine_vehical->name) ? $item->vehical_item->engine_vehical->name . ' CC' : '-' }}</h6>
                                </li>
                                <li data-view="internal">
                                    <h4>Country</h4>
                                    <h6>{{ ($item->vehical_item->country_vehical && $item->vehical_item->country_vehical->name) ? $item->vehical_item->country_vehical->name : '-' }}</h6>
                                </li>
                                @if($item->vehical_item->vehical_part_number && $item->vehical_item->vehical_part_number->name)
                                <li data-view="internal">
                                    <h4>Part Number</h4>
                                    <h6>{{ $item->vehical_item->vehical_part_number->name }}</h6>
                                </li>
                                @endif
                                @endif
                            @endif

                            <!-- BATTERY SPECIFIC FIELDS -->
                            @if($item->type == 'battery')
                                @if($item->company_item)
                                <li>
                                    <h4>Company</h4>
                                    <h6>{{ $item->company_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->technology_item)
                                <li>
                                    <h4>Series</h4>
                                    <h6>{{ $item->technology_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->quality_item)
                                <li>
                                    <h4>Quality</h4>
                                    <h6>{{ $item->quality_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->plate_item)
                                <li>
                                    <h4>Plates</h4>
                                    <h6>{{ $item->plate_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->amphors_item)
                                <li>
                                    <h4>Amperes</h4>
                                    <h6>{{ $item->amphors_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->volt_item)
                                <li>
                                    <h4>Volt</h4>
                                    <h6>{{ $item->volt_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->cca_item)
                                <li>
                                    <h4>CCA</h4>
                                    <h6>{{ $item->cca_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->minus_pool_item)
                                <li>
                                    <h4>Minus Pole Direction</h4>
                                    <h6>{{ $item->minus_pool_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->warrenty_item)
                                <li>
                                    <h4>Warranty</h4>
                                    <h6>{{ $item->warrenty_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->serial_number)
                                <li>
                                    <h4>Serial Number</h4>
                                    <h6>{{ $item->serial_number }}</h6>
                                </li>
                                @endif
                                
                                @if($item->battery_size)
                                <li>
                                    <h4>Battery Size</h4>
                                    <h6>{{ $item->battery_size }}</h6>
                                </li>
                                @endif
                            @endif

                            <!-- OIL SPECIFIC FIELDS -->
                            @if($item->type == 'oil')
                                @if($item->company_item)
                                <li>
                                    <h4>Company</h4>
                                    <h6>{{ $item->company_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->technology_item)
                                <li>
                                    <h4>Series</h4>
                                    <h6>{{ $item->technology_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->quality_item)
                                <li>
                                    <h4>Quality</h4>
                                    <h6>{{ $item->quality_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->grade_item)
                                <li>
                                    <h4>Grade</h4>
                                    <h6>{{ $item->grade_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->mileage_item)
                                <li>
                                    <h4>Mileage</h4>
                                    <h6>{{ $item->mileage_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                            @endif

                            <!-- FILTERS SPECIFIC FIELDS -->
                            @if($item->type == 'filters')
                                @if($item->partnumber_item)
                                <li>
                                    <h4>Part Number</h4>
                                    <h6>{{ $item->partnumber_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->company_item)
                                <li>
                                    <h4>Company</h4>
                                    <h6>{{ $item->company_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->quality_item)
                                <li>
                                    <h4>Quality</h4>
                                    <h6>{{ $item->quality_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                            @endif

                            <!-- BREAKPAD SPECIFIC FIELDS -->
                            @if($item->type == 'breakpad')
                                @if($item->partnumber_item)
                                <li>
                                    <h4>Part Number</h4>
                                    <h6>{{ $item->partnumber_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->company_item)
                                <li>
                                    <h4>Company</h4>
                                    <h6>{{ $item->company_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->quality_item)
                                <li>
                                    <h4>Quality</h4>
                                    <h6>{{ $item->quality_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                            @endif

                            <!-- SCRAP SPECIFIC FIELDS -->
                            @if($item->type == 'scrap')
                                @if($item->company_item)
                            <li>
                                    <h4>Company</h4>
                                    <h6>{{ $item->company_item->name ?? '-' }}</h6>
                            </li>
                                @endif
                                
                                @if($item->level_item)
                                <li>
                                    <h4>Level</h4>
                                    <h6>{{ $item->level_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                            @endif

                            <!-- SERVICES SPECIFIC FIELDS -->
                            @if($item->type == 'services')
                                @if($item->services_item)
                                <li>
                                    <h4>Services</h4>
                                    <h6>{{ $item->services_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                            @endif

                            <!-- COMMON FIELDS FOR ALL TYPES -->
                            <li data-view="internal">
                                <h4>On Hand (Stock)</h4>
                                <h6>
                                    <span class="badge {{ $item->on_hand > 10 ? 'bg-success' : ($item->on_hand > 0 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ $item->on_hand ?? 0 }}
                                    </span>
                                </h6>
                            </li>

                            @if($item->total_price)
                            <li data-view="internal">
                                <h4>Total Value</h4>
                                <h6>Rs. {{ number_format($item->total_price ?? 0, 0) }}</h6>
                            </li>
                            @endif

                            @if($item->sale_price)
                            <li>
                                <h4>Sale Price</h4>
                                <h6>Rs. {{ number_format($item->sale_price ?? 0, 0) }}</h6>
                            </li>
                            @endif

                            @if($item->weight_for_delivery)
                            <li data-view="internal">
                                <h4>Weight</h4>
                                <h6>
                                    {{ number_format($item->weight_for_delivery, 2) }}
                                    @if($item->weight_unit)
                                        @php
                                            $weightUnits = [
                                                'mg' => 'mg',
                                                'g' => 'g',
                                                'kg' => 'kg',
                                                'quintal' => 'Quintal',
                                                'tonne' => 'Ton',
                                                'oz' => 'oz',
                                                'lb' => 'lb',
                                                'stone' => 'Stone',
                                                'ton_us' => 'Ton (US)',
                                                'ton_uk' => 'Ton (UK)'
                                            ];
                                            $unitLabel = $weightUnits[$item->weight_unit] ?? $item->weight_unit;
                                        @endphp
                                        <span class="badge bg-info">{{ $unitLabel }}</span>
                                    @endif
                                </h6>
                            </li>
                            @endif

                            @if($item->type === 'scrap' && ($item->scrap_dim_width || $item->scrap_dim_height || $item->scrap_dim_length || $item->scrap_dim_depth))
                            <li data-view="internal">
                                <h4>Dimensions</h4>
                                @php
                                    $unit = $item->scrap_dim_unit ?: 'inch';
                                    $w = $item->scrap_dim_width !== null ? number_format((float)$item->scrap_dim_width, 2) : '-';
                                    $ht = $item->scrap_dim_height !== null ? number_format((float)$item->scrap_dim_height, 2) : '-';
                                    $l = $item->scrap_dim_length !== null ? number_format((float)$item->scrap_dim_length, 2) : '-';
                                    $d = $item->scrap_dim_depth !== null ? number_format((float)$item->scrap_dim_depth, 2) : '-';
                                @endphp
                                <h6>{{ $w }} x {{ $ht }} x {{ $l }} x {{ $d }} {{ $unit }}</h6>
                            </li>
                            @endif

                            @if($item->made_in_item)
                            <li>
                                <h4>Made In</h4>
                                <h6>{{ $item->made_in_item->name ?? '-' }}</h6>
                            </li>
                            @endif

                            @if($item->min_qty)
                            <li data-view="internal">
                                <h4>Min Qty Alert</h4>
                                <h6>{{ $item->min_qty ?? 0 }}</h6>
                            </li>
                            @endif

                            <li data-view="internal">
                                <h4>Status</h4>
                                <h6>
                                    <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </h6>
                            </li>

                            @if($item->updated_by_user)
                            <li data-view="internal">
                                <h4>Last Updated By</h4>
                                <h6>{{ $item->updated_by_user->name ?? 'Unknown' }} ({{ \Carbon\Carbon::parse($item->last_updated_at)->format('d M Y, h:i A') }})</h6>
                            </li>
                            @endif

                            <li data-view="internal">
                                <h4>Added By</h4>
                                <h6>{{ $item->item_user?->name ?? 'Unknown' }} ({{ $item->item_user?->email ?? '' }})</h6>
                            </li>

                            <li data-view="internal">
                                <h4>Created At</h4>
                                <h6>{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y, h:i A') }}</h6>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Product Description</h4>
                    <p>{!! $item->pro_dis ?? 'No description available for this product.' !!}</p>
                </div>
            </div>
        </div>

        <!-- Right Side - Images Slider -->
        <div class="col-lg-4 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="slider-product-details">
                        <div class="owl-carousel owl-theme product-slide">
                            @php
                                $allImages = collect([$item->image])->merge($item->images ?? [])->filter();
                            @endphp

                            @if($allImages->count() > 0)
                                @foreach($allImages as $img)
                                    <div class="slider-product text-center">
                                        <img src="{{ $img && file_exists(public_path(str_replace(url('/'), '', $img))) ? $img : asset('assets/img/no-image.png') }}"
                                             alt="Product Image" style="max-height:300px; object-fit:contain;">
                                        <h6 class="mt-2 text-muted">Image</h6>
                                    </div>
                                @endforeach
                            @else
                                <div class="slider-product text-center">
                                    <img src="{{ asset('assets/img/no-image.png') }}" alt="No Image" style="max-height:300px;">
                                    <h6 class="mt-2 text-muted">No Image Available</h6>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Brochure Link (if any) -->
            @if($item->p_brochure)
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <a href="{{ $item->p_brochure }}" target="_blank" class="btn btn-outline-primary">
                            View Brochure / Catalog
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>


<!-- Initialize Owl Carousel -->
@push('scripts')
<script>
    // Global function to toggle WhatsApp section (backup method)
    function toggleWhatsAppSection() {
        const shareSection = document.getElementById('whatsappShareSection');
        if (!shareSection) {
            console.error('WhatsApp share section not found');
            alert('Error: WhatsApp share section not found. Please refresh the page.');
            return;
        }
        
        // Toggle visibility
        if (shareSection.style.display === 'none' || shareSection.style.display === '') {
            shareSection.style.display = 'block';
            shareSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            
            // Focus on phone input after showing
            setTimeout(function() {
                const phoneInput = document.getElementById('phoneNumber');
                if (phoneInput) {
                    phoneInput.focus();
                }
            }, 300);
        } else {
            shareSection.style.display = 'none';
        }
    }
    
    // Toggle between Customer View and Internal View
    function updateViewMode() {
        const isInternal = document.getElementById('viewModeSwitch').checked;
        const label = document.getElementById('viewModeLabel');
        const internals = document.querySelectorAll('[data-view="internal"]');
        internals.forEach(function(el) {
            el.style.display = isInternal ? '' : 'none';
        });
        label.textContent = isInternal ? 'Internal View' : 'Customer View';
    }
    document.addEventListener('DOMContentLoaded', function() {
        const viewSwitch = document.getElementById('viewModeSwitch');
        if (viewSwitch) {
            viewSwitch.addEventListener('change', updateViewMode);
            updateViewMode(); // Apply initial state
        }
    });
    
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Owl Carousel if jQuery is available
        if (typeof jQuery !== 'undefined' && jQuery('.product-slide').length) {
            jQuery('.product-slide').owlCarousel({
                loop: true,
                margin: 20,
                nav: true,
                dots: false,
                responsive:{
                    0:{ items:1 },
                    600:{ items:1 },
                    1000:{ items:1 }
                }
            });
        }
        
        // WhatsApp Share Button Click - Toggle input section (additional event listener)
        const shareBtn = document.getElementById('shareWhatsAppBtn');
        if (shareBtn) {
            // Remove inline onclick if event listener works
            shareBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleWhatsAppSection();
            });
        }
        
        
        // Generate PDF and Share on WhatsApp
        const generateBtn = document.getElementById('generateAndShareBtn');
        if (generateBtn) {
            generateBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Get country code and phone number
                const countryCodeSelect = document.getElementById('countryCode');
                const phoneNumberInput = document.getElementById('phoneNumber');
                const countryCode = countryCodeSelect ? countryCodeSelect.value : '';
                const phoneNumber = phoneNumberInput ? phoneNumberInput.value.trim() : '';
                
                if (!phoneNumber) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Phone Number Required',
                            text: 'Please enter a phone number.'
                        });
                    } else {
                        alert('Please enter a phone number.');
                    }
                    if (phoneNumberInput) phoneNumberInput.focus();
                    return;
                }
                
                // Validate phone number format (7-12 digits for local number)
                if (!/^[0-9]{7,12}$/.test(phoneNumber)) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Phone Number',
                            text: 'Please enter a valid phone number (7-12 digits).'
                        });
                    } else {
                        alert('Please enter a valid phone number (7-12 digits).');
                    }
                    if (phoneNumberInput) phoneNumberInput.focus();
                    return;
                }
                
                // Combine country code with phone number
                const fullPhoneNumber = countryCode + phoneNumber;
                
                // Show loading
                const btn = this;
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="ti ti-loader me-1"></i> Generating PDF...';
                
                // Generate PDF and get share URL
                fetch('{{ route("items.generate.whatsapp.pdf") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        item_ids: [{{ $item->id }}],
                        phone_number: fullPhoneNumber
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Open WhatsApp with PDF link
                        const whatsappUrl = `https://wa.me/${fullPhoneNumber}?text=${encodeURIComponent(data.message)}`;
                        window.open(whatsappUrl, '_blank');
                        
                        // Hide the input section
                        const shareSection = document.getElementById('whatsappShareSection');
                        if (shareSection) {
                            shareSection.style.display = 'none';
                        }
                        
                        // Reset form
                        const form = document.getElementById('whatsappShareForm');
                        if (form) {
                            form.reset();
                        }
                        
                        // Play save sound if available
                        if (typeof playSaveSound === 'function') {
                            playSaveSound();
                        }
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Shared!',
                                text: 'PDF has been shared on WhatsApp.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            alert('PDF has been shared on WhatsApp!');
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Failed to generate PDF. Please try again.'
                            });
                        } else {
                            alert('Error: ' + (data.message || 'Failed to generate PDF. Please try again.'));
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred. Please try again.'
                        });
                    } else {
                        alert('An error occurred. Please try again.');
                    }
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
            });
        } else {
            console.error('Generate and share button not found');
        }
    });
</script>
@endpush
@endsection
