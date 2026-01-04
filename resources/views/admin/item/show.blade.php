@extends('layouts.app')

@section('title', $item->name . ' - Product Details')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Product Details</h4>
            <h6>Full details of a product</h6>
        </div>
        <div class="page-btn">
            <button type="button" id="shareWhatsAppBtn" class="btn btn-success">
                <i class="ti ti-brand-whatsapp me-1"></i> Share on WhatsApp
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Left Side - Details -->
        <div class="col-lg-8 col-sm-12">
            <div class="card">
                <div class="card-body">

                    <!-- Barcode -->
                    <div class="bar-code-view text-center mb-4">
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

                            <li>
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

                            <li>
                                <h4>Type</h4>
                                <h6><span class="badge {{ $item->type == 'parts' ? 'bg-success' : ($item->type == 'battery' ? 'bg-warning' : 'bg-info') }}">{{ ucfirst($item->type ?? 'N/A') }}</span></h6>
                            </li>

                            <!-- COMMON FIELDS FOR ALL TYPES -->
                            @if(in_array($item->type, ['parts', 'battery', 'oil', 'scrap', 'filters', 'breakpad']))
                                <li>
                                    <h4>Unit</h4>
                                    <h6>{{ $item->unit_item->name ?? ($item->unit_item->unit ?? 'Piece') }}</h6>
                                </li>
                            @endif

                            <!-- PARTS SPECIFIC FIELDS -->
                            @if($item->type == 'parts')
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
                                
                                @if($item->group_item)
                                <li>
                                    <h4>Group Name</h4>
                                    <h6>{{ $item->group_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->technology_item)
                                <li>
                                    <h4>Series</h4>
                                    <h6>{{ $item->technology_item->name ?? '-' }}</h6>
                                </li>
                                @endif
                                
                                @if($item->vehical_item)
                                <li>
                                    <h4>Vehicle Model</h4>
                                    <h6>{{ ($item->vehical_item->model_vehical && $item->vehical_item->model_vehical->name) ? $item->vehical_item->model_vehical->name : '-' }}</h6>
                                </li>
                                <li>
                                    <h4>Manufacturer</h4>
                                    <h6>{{ ($item->vehical_item->manutacturer_vehical && $item->vehical_item->manutacturer_vehical->name) ? $item->vehical_item->manutacturer_vehical->name : '-' }}</h6>
                                </li>
                                <li>
                                    <h4>Engine CC</h4>
                                    <h6>{{ ($item->vehical_item->engine_vehical && $item->vehical_item->engine_vehical->name) ? $item->vehical_item->engine_vehical->name . ' CC' : '-' }}</h6>
                                </li>
                                <li>
                                    <h4>Country</h4>
                                    <h6>{{ ($item->vehical_item->country_vehical && $item->vehical_item->country_vehical->name) ? $item->vehical_item->country_vehical->name : '-' }}</h6>
                                </li>
                                @if($item->vehical_item->vehical_part_number && $item->vehical_item->vehical_part_number->name)
                                <li>
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
                            <li>
                                <h4>On Hand (Stock)</h4>
                                <h6>
                                    <span class="badge {{ $item->on_hand > 10 ? 'bg-success' : ($item->on_hand > 0 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ $item->on_hand ?? 0 }}
                                    </span>
                                </h6>
                            </li>

                            @if($item->price_per_unit)
                            <li>
                                <h4>Price per Unit</h4>
                                <h6>Rs. {{ number_format($item->price_per_unit ?? 0, 2) }}</h6>
                            </li>
                            @endif

                            @if($item->total_price)
                            <li>
                                <h4>Total Value</h4>
                                <h6>Rs. {{ number_format($item->total_price ?? 0, 2) }}</h6>
                            </li>
                            @endif

                            @if($item->sale_price)
                            <li>
                                <h4>Sale Price</h4>
                                <h6>Rs. {{ number_format($item->sale_price ?? 0, 2) }}</h6>
                            </li>
                            @endif

                            @if($item->weight_for_delivery)
                            <li>
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

                            @if($item->made_in_item)
                            <li>
                                <h4>Made In</h4>
                                <h6>{{ $item->made_in_item->name ?? '-' }}</h6>
                            </li>
                            @endif

                            @if($item->min_qty)
                            <li>
                                <h4>Min Qty Alert</h4>
                                <h6>{{ $item->min_qty ?? 0 }}</h6>
                            </li>
                            @endif

                            <li>
                                <h4>Status</h4>
                                <h6>
                                    <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </h6>
                            </li>

                            @if($item->updated_by_user)
                            <li>
                                <h4>Last Updated By</h4>
                                <h6>{{ $item->updated_by_user->name ?? 'Unknown' }} ({{ \Carbon\Carbon::parse($item->last_updated_at)->format('d M Y, h:i A') }})</h6>
                            </li>
                            @endif

                            <li>
                                <h4>Added By</h4>
                                <h6>{{ $item->item_user?->name ?? 'Unknown' }} ({{ $item->item_user?->email ?? '' }})</h6>
                            </li>

                            <li>
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

<!-- WhatsApp Share Modal -->
<div class="modal fade" id="whatsappShareModal" tabindex="-1" aria-labelledby="whatsappShareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="whatsappShareModalLabel">
                    <i class="ti ti-brand-whatsapp me-2"></i>Share on WhatsApp
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="whatsappShareForm">
                    <div class="mb-3">
                        <label for="phoneNumber" class="form-label">Phone Number (with Country Code) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">+</span>
                            <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" 
                                placeholder="923001234567" required pattern="[0-9]{10,15}">
                        </div>
                        <small class="text-muted">Example: 923001234567 (without + sign)</small>
                    </div>
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Item:</strong> {{ $item->product_item->name ?? 'N/A' }} will be shared
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="generateAndShareBtn">
                    <i class="ti ti-brand-whatsapp me-1"></i> Generate PDF & Share
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Initialize Owl Carousel -->
@push('scripts')
<script>
    $(document).ready(function(){
        $('.product-slide').owlCarousel({
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
        
        // WhatsApp Share Button Click - Use event delegation
        $(document).on('click', '#shareWhatsAppBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const modalElement = document.getElementById('whatsappShareModal');
            if (!modalElement) {
                console.error('WhatsApp modal not found');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Modal not found. Please refresh the page.'
                });
                return;
            }
            
            try {
                // Check if Bootstrap 5 is available
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                } else if (typeof $('#whatsappShareModal').modal === 'function') {
                    // Fallback for Bootstrap 4
                    $('#whatsappShareModal').modal('show');
                } else {
                    $(modalElement).modal('show');
                }
                
                // Focus on phone input after modal shows
                setTimeout(function() {
                    const phoneInput = document.getElementById('phoneNumber');
                    if (phoneInput) {
                        phoneInput.focus();
                    }
                }, 400);
            } catch (error) {
                console.error('Error showing modal:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Could not open modal. Please try again.'
                });
            }
        });
        
        // Generate PDF and Share on WhatsApp - Use event delegation
        $(document).on('click', '#generateAndShareBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Get phone number
            const phoneNumberInput = document.getElementById('phoneNumber');
            const phoneNumber = phoneNumberInput ? phoneNumberInput.value.trim() : '';
            
            if (!phoneNumber) {
                Swal.fire({
                    icon: 'error',
                    title: 'Phone Number Required',
                    text: 'Please enter a phone number with country code.'
                });
                return;
            }
            
            // Validate phone number format
            if (!/^[0-9]{10,15}$/.test(phoneNumber)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Phone Number',
                    text: 'Please enter a valid phone number (10-15 digits).'
                });
                return;
            }
            
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
                    phone_number: phoneNumber
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Open WhatsApp with PDF
                    const whatsappUrl = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(data.message)}`;
                    window.open(whatsappUrl, '_blank');
                    
                    // Close modal - Try multiple methods
                    const modalElement = document.getElementById('whatsappShareModal');
                    if (modalElement) {
                        try {
                            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                const modal = bootstrap.Modal.getInstance(modalElement);
                                if (modal) {
                                    modal.hide();
                                } else {
                                    const newModal = new bootstrap.Modal(modalElement);
                                    newModal.hide();
                                }
                            } else if (typeof $('#whatsappShareModal').modal === 'function') {
                                $('#whatsappShareModal').modal('hide');
                            } else {
                                $(modalElement).modal('hide');
                            }
                        } catch (error) {
                            console.error('Error closing modal:', error);
                            $(modalElement).hide();
                        }
                    }
                    
                    // Reset form
                    const form = document.getElementById('whatsappShareForm');
                    if (form) {
                        form.reset();
                    }
                    
                    // Play save sound
                    if (typeof playSaveSound === 'function') {
                        playSaveSound();
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Shared!',
                        text: 'PDF has been shared on WhatsApp.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to generate PDF. Please try again.'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.'
                });
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });
    });
</script>
@endpush
@endsection
