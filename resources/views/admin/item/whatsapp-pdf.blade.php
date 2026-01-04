<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Items Details - WhatsApp Share</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #25D366;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .item {
            margin-bottom: 25px;
            border: 1px solid #ddd;
            padding: 15px;
            page-break-inside: avoid;
        }
        .item-header {
            background-color: #25D366;
            color: white;
            padding: 10px;
            margin: -15px -15px 15px -15px;
            font-weight: bold;
        }
        .item-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 8px;
        }
        .detail-label {
            font-weight: bold;
            width: 150px;
            color: #333;
        }
        .detail-value {
            flex: 1;
            color: #666;
        }
        .image-container {
            text-align: center;
            margin: 10px 0;
        }
        .item-image {
            max-width: 150px;
            max-height: 150px;
            border: 1px solid #ddd;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        @media print {
            .item {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📦 Items Details</h1>
        <p>Generated on: {{ date('d M Y, h:i A') }}</p>
        <p>Total Items: {{ $items->count() }}</p>
    </div>

    @foreach($items as $index => $item)
    <div class="item">
        <div class="item-header">
            Item #{{ $index + 1 }} - {{ $item->product_item->name ?? 'N/A' }}
        </div>
        
        @if($item->image)
        <div class="image-container">
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
        
        <div class="item-details">
            <div class="detail-row">
                <span class="detail-label">Product Name:</span>
                <span class="detail-value">{{ $item->product_item->name ?? '-' }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Type:</span>
                <span class="detail-value">{{ ucfirst($item->type ?? '-') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Barcode:</span>
                <span class="detail-value">{{ $item->bar_code ?? '-' }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Category:</span>
                <span class="detail-value">{{ $item->category->name ?? 'N/A' }}</span>
            </div>
            
            @if($item->partnumber_item)
            <div class="detail-row">
                <span class="detail-label">Part Number:</span>
                <span class="detail-value">{{ $item->partnumber_item->name ?? '-' }}</span>
            </div>
            @endif
            
            @if($item->company_item)
            <div class="detail-row">
                <span class="detail-label">Company:</span>
                <span class="detail-value">{{ $item->company_item->name ?? '-' }}</span>
            </div>
            @endif
            
            @if($item->quality_item)
            <div class="detail-row">
                <span class="detail-label">Quality:</span>
                <span class="detail-value">{{ $item->quality_item->name ?? '-' }}</span>
            </div>
            @endif
            
            @if($item->technology_item)
            <div class="detail-row">
                <span class="detail-label">Series:</span>
                <span class="detail-value">{{ $item->technology_item->name ?? '-' }}</span>
            </div>
            @endif
            
            <div class="detail-row">
                <span class="detail-label">Unit:</span>
                <span class="detail-value">{{ $item->unit_item->name ?? 'Piece' }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Stock (On Hand):</span>
                <span class="detail-value">{{ $item->on_hand ?? 0 }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Price per Unit:</span>
                <span class="detail-value">Rs. {{ number_format($item->price_per_unit ?? 0, 2) }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Total Value:</span>
                <span class="detail-value">Rs. {{ number_format($item->total_price ?? 0, 2) }}</span>
            </div>
            
            @if($item->sale_price)
            <div class="detail-row">
                <span class="detail-label">Sale Price per Unit:</span>
                <span class="detail-value">Rs. {{ number_format($item->sale_price ?? 0, 2) }}</span>
            </div>
            @endif
            
            @if($item->vehical_item)
            <div class="detail-row">
                <span class="detail-label">Vehicle Model:</span>
                <span class="detail-value">
                    {{ ($item->vehical_item->model_vehical && $item->vehical_item->model_vehical->name) ? $item->vehical_item->model_vehical->name : '-' }}
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Manufacturer:</span>
                <span class="detail-value">
                    {{ ($item->vehical_item->manutacturer_vehical && $item->vehical_item->manutacturer_vehical->name) ? $item->vehical_item->manutacturer_vehical->name : '-' }}
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Engine CC:</span>
                <span class="detail-value">
                    {{ ($item->vehical_item->engine_vehical && $item->vehical_item->engine_vehical->name) ? $item->vehical_item->engine_vehical->name . ' CC' : '-' }}
                </span>
            </div>
            @endif
            
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value">{{ $item->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            
            @if($item->made_in_item)
            <div class="detail-row">
                <span class="detail-label">Made In:</span>
                <span class="detail-value">{{ $item->made_in_item->name ?? '-' }}</span>
            </div>
            @endif
        </div>
    </div>
    @endforeach

    <div class="footer">
        <p>This document was generated for WhatsApp sharing</p>
        <p>Phone: +{{ $phoneNumber }}</p>
    </div>
</body>
</html>

