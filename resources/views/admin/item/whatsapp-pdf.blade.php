<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Items Details - WhatsApp Share</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 11px;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
            padding: 15px;
        }
        .header {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white;
            padding: 25px 20px;
            margin: -15px -15px 25px -15px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .header-info {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .header-info p {
            margin: 5px;
            font-size: 12px;
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 20px;
        }
        .item {
            background: white;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            page-break-inside: avoid;
            border: 1px solid #e0e0e0;
        }
        .item-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            font-weight: bold;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .item-number {
            background: rgba(255,255,255,0.3);
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
        }
        .item-content {
            padding: 20px;
        }
        .image-section {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .item-image {
            max-width: 200px;
            max-height: 200px;
            border: 3px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 15px;
        }
        .detail-box {
            background: #f8f9fa;
            padding: 10px 12px;
            border-radius: 6px;
            border-left: 4px solid #25D366;
            transition: all 0.3s ease;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .detail-value {
            color: #333;
            font-size: 12px;
            font-weight: 600;
        }
        .price-box {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-left: 4px solid #d63384;
        }
        .price-box .detail-label,
        .price-box .detail-value {
            color: white;
        }
        .stock-box {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border-left: 4px solid #0d6efd;
        }
        .stock-box .detail-label,
        .stock-box .detail-value {
            color: white;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active {
            background: #28a745;
            color: white;
        }
        .status-inactive {
            background: #dc3545;
            color: white;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin: 20px 0 12px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #25D366;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .footer {
            margin-top: 40px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            border-radius: 10px;
            margin-left: -15px;
            margin-right: -15px;
            margin-bottom: -15px;
        }
        .footer p {
            margin: 5px 0;
            font-size: 11px;
        }
        .footer-icon {
            font-size: 24px;
            margin-bottom: 10px;
        }
        @media print {
            body {
                background: white;
            }
            .item {
                page-break-inside: avoid;
                margin-bottom: 20px;
            }
        }
        .highlight {
            background: #fff3cd;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📦 Product Catalog</h1>
        <div class="header-info">
            <p><strong>Date:</strong> {{ date('d M Y, h:i A') }}</p>
            <p><strong>Total Items:</strong> {{ $items->count() }}</p>
            <p><strong>Share Via:</strong> WhatsApp</p>
        </div>
    </div>

    @foreach($items as $index => $item)
    <div class="item">
        <div class="item-header">
            <span>{{ $item->product_item->name ?? 'N/A' }}</span>
            <span class="item-number">Item #{{ $index + 1 }}</span>
        </div>
        
        <div class="item-content">
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
            
            <div class="details-grid">
                <div class="detail-box">
                    <div class="detail-label">Product Name</div>
                    <div class="detail-value">{{ $item->product_item->name ?? '-' }}</div>
                </div>
                
                <div class="detail-box">
                    <div class="detail-label">Product Type</div>
                    <div class="detail-value">{{ ucfirst($item->type ?? '-') }}</div>
                </div>
                
                <div class="detail-box">
                    <div class="detail-label">Barcode</div>
                    <div class="detail-value">{{ $item->bar_code ?? '-' }}</div>
                </div>
                
                <div class="detail-box">
                    <div class="detail-label">Category</div>
                    <div class="detail-value">{{ $item->category->name ?? 'N/A' }}</div>
                </div>
                
                @if($item->partnumber_item)
                <div class="detail-box">
                    <div class="detail-label">Part Number</div>
                    <div class="detail-value">{{ $item->partnumber_item->name ?? '-' }}</div>
                </div>
                @endif
                
                @if($item->company_item)
                <div class="detail-box">
                    <div class="detail-label">Company / Brand</div>
                    <div class="detail-value">{{ $item->company_item->name ?? '-' }}</div>
                </div>
                @endif
                
                @if($item->quality_item)
                <div class="detail-box">
                    <div class="detail-label">Quality</div>
                    <div class="detail-value">{{ $item->quality_item->name ?? '-' }}</div>
                </div>
                @endif
                
                @if($item->technology_item)
                <div class="detail-box">
                    <div class="detail-label">Series</div>
                    <div class="detail-value">{{ $item->technology_item->name ?? '-' }}</div>
                </div>
                @endif
                
                <div class="detail-box">
                    <div class="detail-label">Unit of Measurement</div>
                    <div class="detail-value">{{ $item->unit_item->name ?? 'Piece' }}</div>
                </div>
                
                <div class="stock-box">
                    <div class="detail-label">Stock Available</div>
                    <div class="detail-value">{{ $item->on_hand ?? 0 }} Units</div>
                </div>
                
                @if($item->price_per_unit)
                <div class="price-box">
                    <div class="detail-label">Price per Unit</div>
                    <div class="detail-value">Rs. {{ number_format($item->price_per_unit ?? 0, 2) }}</div>
                </div>
                @endif
                
                @if($item->total_price)
                <div class="price-box">
                    <div class="detail-label">Total Value</div>
                    <div class="detail-value">Rs. {{ number_format($item->total_price ?? 0, 2) }}</div>
                </div>
                @endif
                
                @if($item->sale_price)
                <div class="price-box">
                    <div class="detail-label">Sale Price per Unit</div>
                    <div class="detail-value">Rs. {{ number_format($item->sale_price ?? 0, 2) }}</div>
                </div>
                @endif
                
                @if($item->vehical_item)
                <div class="detail-box">
                    <div class="detail-label">Vehicle Model</div>
                    <div class="detail-value">
                        {{ ($item->vehical_item->model_vehical && $item->vehical_item->model_vehical->name) ? $item->vehical_item->model_vehical->name : '-' }}
                    </div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Manufacturer</div>
                    <div class="detail-value">
                        {{ ($item->vehical_item->manutacturer_vehical && $item->vehical_item->manutacturer_vehical->name) ? $item->vehical_item->manutacturer_vehical->name : '-' }}
                    </div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Engine CC</div>
                    <div class="detail-value">
                        {{ ($item->vehical_item->engine_vehical && $item->vehical_item->engine_vehical->name) ? $item->vehical_item->engine_vehical->name . ' CC' : '-' }}
                    </div>
                </div>
                @endif
                
                @if($item->made_in_item)
                <div class="detail-box">
                    <div class="detail-label">Made In</div>
                    <div class="detail-value">{{ $item->made_in_item->name ?? '-' }}</div>
                </div>
                @endif
                
                <div class="detail-box">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">
                        <span class="status-badge {{ $item->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <div class="footer">
        <div class="footer-icon">📱</div>
        <p><strong>This document was generated for WhatsApp sharing</strong></p>
        <p>Phone: +{{ $phoneNumber }}</p>
        <p>Generated on {{ date('d M Y, h:i A') }}</p>
    </div>
</body>
</html>
