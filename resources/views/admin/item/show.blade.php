@extends('layouts.app')

@section('title', $item->name . ' - Product Details')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Product Details</h4>
            <h6>Full details of a product</h6>
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



                            <li>
                                <h4>Type</h4>
                                <h6><span class="badge {{ $item->type == 'parts' ? 'bg-success' : 'bg-info' }}">{{ ucfirst($item->type ?? 'N/A') }}</span></h6>
                            </li>

                            <!-- Show Vehicle Info ONLY when type == 'parts' -->
                            @if($item->type == 'parts' && $item->vehical_item)
                                <li>
                                    <h4>Vehicle Model</h4>
                                    <h6>{{ $item->vehical_item->car_model_name }} ({{ $item->vehical_item->carmanufactured_year }})</h6>
                                </li>
                                <li>
                                    <h4>Manufacturer</h4>
                                    <h6>{{ $item->vehical_item->car_manufacturer }}</h6>
                                </li>
                                <li>
                                    <h4>Engine CC</h4>
                                    <h6>{{ $item->vehical_item->engine_cc }} cc</h6>
                                </li>
                                <li>
                                    <h4>Country</h4>
                                    <h6>{{ $item->vehical_item->car_manufactured_country }}</h6>
                                </li>
                            @endif

                            <li>
                                <h4>Unit</h4>
                                <h6>{{ $item->unit_item->unit ?? 'Piece' }}</h6>
                            </li>

                            <li>
                                <h4>On Hand (Stock)</h4>
                                <h6>
                                    <span class="badge {{ $item->on_hand > 10 ? 'bg-success' : ($item->on_hand > 0 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ $item->on_hand ?? 0 }}
                                    </span>
                                </h6>
                            </li>

                            <li>
                                <h4>Price per Unit</h4>
                                <h6>Rs. {{ number_format($item->price_per_unit ?? 0, 2) }}</h6>
                            </li>

                            <li>
                                <h4>Total Value</h4>
                                <h6>Rs. {{ number_format($item->total_price ?? 0, 2) }}</h6>
                            </li>

                            <li>
                                <h4>Min Qty Alert</h4>
                                <h6>{{ $item->min_qty ?? 0 }}</h6>
                            </li>

                            <li>
                                <h4>Status</h4>
                                <h6>
                                    <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </h6>
                            </li>



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

<!-- Initialize Owl Carousel -->
@push('scripts')
<script>
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
    });
</script>
@endpush
@endsection
