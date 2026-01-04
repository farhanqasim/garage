<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Product Catalog - WhatsApp Share</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        @page {
            margin: 15mm;
        }
        body {
            font-family: 'Arial', 'Helvetica', 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.5;
            color: #1a1a1a;
            background: #ffffff;
        }
        
        /* Header Styles */
        .header {
            background: linear-gradient(135deg, #25D366 0%, #075E54 100%);
            color: white;
            padding: 30px 25px;
            margin: -15mm -15mm 20px -15mm;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-bottom: 5px solid #128C7E;
        }
        .header h1 {
            margin: 0 0 15px 0;
            font-size: 32px;
            font-weight: bold;
            text-shadow: 2px 2px 6px rgba(0,0,0,0.3);
            letter-spacing: 1px;
        }
        .header-info {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .header-info-badge {
            background: rgba(255,255,255,0.25);
            padding: 8px 18px;
            border-radius: 25px;
            font-size: 11px;
            font-weight: 600;
            border: 2px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
        }
        
        /* Item Card Styles */
        .item {
            background: white;
            margin-bottom: 30px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.12);
            overflow: hidden;
            page-break-inside: avoid;
            border: 2px solid #e8e8e8;
        }
        .item-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 18px 25px;
            font-weight: bold;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .item-header-title {
            flex: 1;
            font-size: 18px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }
        .item-number {
            background: rgba(255,255,255,0.35);
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            border: 2px solid rgba(255,255,255,0.5);
        }
        
        /* Item Content */
        .item-content {
            padding: 25px;
            background: linear-gradient(to bottom, #ffffff 0%, #fafafa 100%);
        }
        
        /* Image Section */
        .image-section {
            text-align: center;
            margin-bottom: 25px;
            padding: 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 10px;
            border: 2px dashed #25D366;
        }
        .item-image {
            max-width: 220px;
            max-height: 220px;
            border: 4px solid white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        /* Details Grid */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            margin-top: 20px;
        }
        
        /* Detail Box Styles */
        .detail-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 12px 15px;
            border-radius: 8px;
            border-left: 5px solid #25D366;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .detail-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .detail-label {
            font-weight: bold;
            color: #495057;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
            display: block;
        }
        .detail-value {
            color: #212529;
            font-size: 13px;
            font-weight: 600;
            word-break: break-word;
        }
        
        /* Special Box Styles */
        .price-box {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-left: 5px solid #d63384;
            box-shadow: 0 3px 8px rgba(245, 87, 108, 0.3);
        }
        .price-box .detail-label,
        .price-box .detail-value {
            color: white;
        }
        
        .stock-box {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border-left: 5px solid #0d6efd;
            box-shadow: 0 3px 8px rgba(79, 172, 254, 0.3);
        }
        .stock-box .detail-label,
        .stock-box .detail-value {
            color: white;
        }
        
        .highlight-box {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            border-left: 5px solid #ff6b35;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        }
        .status-active {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        .status-inactive {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        
        /* Section Title */
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin: 25px 0 15px 0;
            padding: 12px 0 8px 0;
            border-bottom: 3px solid #25D366;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding: 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            border-radius: 12px;
            margin-left: -15mm;
            margin-right: -15mm;
            margin-bottom: -15mm;
            box-shadow: 0 -4px 12px rgba(0,0,0,0.15);
        }
        .footer-icon {
            font-size: 32px;
            margin-bottom: 12px;
        }
        .footer p {
            margin: 6px 0;
            font-size: 11px;
            opacity: 0.95;
        }
        .footer p strong {
            font-size: 13px;
            font-weight: bold;
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .item {
                page-break-inside: avoid;
                margin-bottom: 20px;
            }
            .header {
                margin: 0 0 20px 0;
                padding: 25px 20px;
            }
            .footer {
                margin: 40px 0 0 0;
                padding: 20px;
            }
        }
        
        /* Full Width Detail Box */
        .detail-box-full {
            grid-column: 1 / -1;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>📦 PRODUCT CATALOG</h1>
        <div class="header-info">
            <span class="header-info-badge">📅 {{ date('d M Y') }}</span>
            <span class="header-info-badge">⏰ {{ date('h:i A') }}</span>
            <span class="header-info-badge">📊 {{ $items->count() }} Item(s)</span>
            <span class="header-info-badge">📱 WhatsApp Share</span>
        </div>
    </div>

    <!-- Items List -->
    @foreach($items as $index => $item)
    <div class="item">
        <!-- Item Header -->
        <div class="item-header">
            <span class="item-header-title">{{ $item->product_item->name ?? 'N/A' }}</span>
            <span class="item-number">#{{ $index + 1 }}</span>
        </div>
        
        <div class="item-content">
            <!-- Image -->
            @if($item->image)
            <div class="image-section">
                @php
                    $imagePath = str_replace(url('/'), '', $item->image);
                    $imagePath = ltrim($imagePath, '/');
                    $fullImagePath = public_path($imagePath);
                @endphp
                @if(file_exists($fullImagePath))
                <img src="{{ $fullImagePath }}" class="item-image" alt="Item Image">
                @endif
            </div>
            @endif
            
            <!-- Details Grid -->
            <div class="details-grid">
                <!-- Basic Information -->
                <div class="detail-box highlight-box">
                    <span class="detail-label">Product Name</span>
                    <div class="detail-value">{{ $item->product_item->name ?? '-' }}</div>
                </div>
                
                <div class="detail-box">
                    <span class="detail-label">Product Type</span>
                    <div class="detail-value">{{ ucfirst($item->type ?? '-') }}</div>
                </div>
                
                <div class="detail-box">
                    <span class="detail-label">Barcode</span>
                    <div class="detail-value">{{ $item->bar_code ?? '-' }}</div>
                </div>
                
                <div class="detail-box">
                    <span class="detail-label">Category</span>
                    <div class="detail-value">{{ $item->category->name ?? 'N/A' }}</div>
                </div>
                
                @if($item->subcategory)
                <div class="detail-box">
                    <span class="detail-label">Subcategory</span>
                    <div class="detail-value">{{ $item->subcategory->name ?? '-' }}</div>
                </div>
                @endif
                
                @if($item->partnumber_item)
                <div class="detail-box">
                    <span class="detail-label">Part Number</span>
                    <div class="detail-value">{{ $item->partnumber_item->name ?? '-' }}</div>
                </div>
                @endif
                
                @if($item->company_item)
                <div class="detail-box highlight-box">
                    <span class="detail-label">Company / Brand</span>
                    <div class="detail-value">{{ $item->company_item->name ?? '-' }}</div>
                </div>
                @endif
                
                @if($item->quality_item)
                <div class="detail-box">
                    <span class="detail-label">Quality</span>
                    <div class="detail-value">{{ $item->quality_item->name ?? '-' }}</div>
                </div>
                @endif
                
                @if($item->technology_item)
                <div class="detail-box">
                    <span class="detail-label">Series</span>
                    <div class="detail-value">{{ $item->technology_item->name ?? '-' }}</div>
                </div>
                @endif
                
                @if($item->group_item)
                <div class="detail-box">
                    <span class="detail-label">Group Name</span>
                    <div class="detail-value">{{ $item->group_item->name ?? '-' }}</div>
                </div>
                @endif
                
                <div class="detail-box">
                    <span class="detail-label">Unit of Measurement</span>
                    <div class="detail-value">{{ $item->unit_item->name ?? 'Piece' }}</div>
                </div>
                
                <!-- Stock Information -->
                <div class="stock-box">
                    <span class="detail-label">Stock Available</span>
                    <div class="detail-value">{{ $item->on_hand ?? 0 }} Units</div>
                </div>
                
                @if($item->min_qty)
                <div class="detail-box">
                    <span class="detail-label">Min Qty Alert</span>
                    <div class="detail-value">{{ $item->min_qty ?? 0 }}</div>
                </div>
                @endif
                
                <!-- Price Information -->
                @if($item->price_per_unit)
                <div class="price-box">
                    <span class="detail-label">Price per Unit</span>
                    <div class="detail-value">Rs. {{ number_format($item->price_per_unit ?? 0, 2) }}</div>
                </div>
                @endif
                
                @if($item->total_price)
                <div class="price-box">
                    <span class="detail-label">Total Value</span>
                    <div class="detail-value">Rs. {{ number_format($item->total_price ?? 0, 2) }}</div>
                </div>
                @endif
                
                @if($item->sale_price)
                <div class="price-box">
                    <span class="detail-label">Sale Price per Unit</span>
                    <div class="detail-value">Rs. {{ number_format($item->sale_price ?? 0, 2) }}</div>
                </div>
                @endif
                
                @if($item->total_sale_price)
                <div class="price-box">
                    <span class="detail-label">Total Sale Value</span>
                    <div class="detail-value">Rs. {{ number_format($item->total_sale_price ?? 0, 2) }}</div>
                </div>
                @endif
                
                <!-- Vehicle Information -->
                @if($item->vehical_item)
                <div class="detail-box detail-box-full">
                    <span class="detail-label" style="font-size: 11px;">Vehicle Information</span>
                    <div class="detail-value" style="font-size: 11px;">
                        @if($item->vehical_item->model_vehical && $item->vehical_item->model_vehical->name)
                            Model: {{ $item->vehical_item->model_vehical->name }}
                        @endif
                        @if($item->vehical_item->manutacturer_vehical && $item->vehical_item->manutacturer_vehical->name)
                            | Manufacturer: {{ $item->vehical_item->manutacturer_vehical->name }}
                        @endif
                        @if($item->vehical_item->engine_vehical && $item->vehical_item->engine_vehical->name)
                            | Engine: {{ $item->vehical_item->engine_vehical->name }} CC
                        @endif
                        @if($item->vehical_item->country_vehical && $item->vehical_item->country_vehical->name)
                            | Country: {{ $item->vehical_item->country_vehical->name }}
                        @endif
                    </div>
                </div>
                @endif
                
                <!-- Battery Specific -->
                @if($item->type == 'battery')
                    @if($item->plate_item)
                    <div class="detail-box">
                        <span class="detail-label">Plates</span>
                        <div class="detail-value">{{ $item->plate_item->name ?? '-' }}</div>
                    </div>
                    @endif
                    @if($item->amphors_item)
                    <div class="detail-box">
                        <span class="detail-label">Amperes</span>
                        <div class="detail-value">{{ $item->amphors_item->name ?? '-' }}</div>
                    </div>
                    @endif
                    @if($item->volt_item)
                    <div class="detail-box">
                        <span class="detail-label">Volt</span>
                        <div class="detail-value">{{ $item->volt_item->name ?? '-' }}</div>
                    </div>
                    @endif
                    @if($item->cca_item)
                    <div class="detail-box">
                        <span class="detail-label">CCA</span>
                        <div class="detail-value">{{ $item->cca_item->name ?? '-' }}</div>
                    </div>
                    @endif
                    @if($item->minus_pool_item)
                    <div class="detail-box">
                        <span class="detail-label">Minus Pole Direction</span>
                        <div class="detail-value">{{ $item->minus_pool_item->name ?? '-' }}</div>
                    </div>
                    @endif
                    @if($item->warrenty_item)
                    <div class="detail-box">
                        <span class="detail-label">Warranty</span>
                        <div class="detail-value">{{ $item->warrenty_item->name ?? '-' }}</div>
                    </div>
                    @endif
                @endif
                
                <!-- Oil Specific -->
                @if($item->type == 'oil')
                    @if($item->grade_item)
                    <div class="detail-box">
                        <span class="detail-label">Grade</span>
                        <div class="detail-value">{{ $item->grade_item->name ?? '-' }}</div>
                    </div>
                    @endif
                    @if($item->mileage_item)
                    <div class="detail-box">
                        <span class="detail-label">Mileage</span>
                        <div class="detail-value">{{ $item->mileage_item->name ?? '-' }}</div>
                    </div>
                    @endif
                @endif
                
                <!-- Scrap Specific -->
                @if($item->type == 'scrap')
                    @if($item->level_item)
                    <div class="detail-box">
                        <span class="detail-label">Level</span>
                        <div class="detail-value">{{ $item->level_item->name ?? '-' }}</div>
                    </div>
                    @endif
                @endif
                
                <!-- Services Specific -->
                @if($item->type == 'services')
                    @if($item->services_item)
                    <div class="detail-box">
                        <span class="detail-label">Services</span>
                        <div class="detail-value">{{ $item->services_item->name ?? '-' }}</div>
                    </div>
                    @endif
                @endif
                
                @if($item->made_in_item)
                <div class="detail-box">
                    <span class="detail-label">Made In</span>
                    <div class="detail-value">{{ $item->made_in_item->name ?? '-' }}</div>
                </div>
                @endif
                
                <!-- Status -->
                <div class="detail-box">
                    <span class="detail-label">Status</span>
                    <div class="detail-value">
                        <span class="status-badge {{ $item->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                
                @if($item->updated_by_user)
                <div class="detail-box detail-box-full">
                    <span class="detail-label">Last Updated</span>
                    <div class="detail-value">
                        {{ $item->updated_by_user->name ?? 'Unknown' }} - 
                        {{ $item->last_updated_at ? \Carbon\Carbon::parse($item->last_updated_at)->format('d M Y, h:i A') : '-' }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach

    <!-- Footer -->
    <div class="footer">
        <div class="footer-icon">📱</div>
        <p><strong>This document was generated for WhatsApp sharing</strong></p>
        <p>Phone: +{{ $phoneNumber }}</p>
        <p>Generated on {{ date('d M Y, h:i A') }}</p>
        <p style="margin-top: 10px; font-size: 9px; opacity: 0.8;">© {{ date('Y') }} Product Catalog System</p>
    </div>
</body>
</html>
