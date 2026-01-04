<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Product Specification - {{ $item->product_item->name ?? 'N/A' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        @page {
            margin: 20mm;
        }
        body {
            font-family: 'Arial', 'Helvetica', 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.6;
            color: #333333;
            background: #ffffff;
        }
        
        /* Header Section */
        .document-header {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
        }
        .header-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .header-row {
            display: table-row;
            border-bottom: 1px solid #dee2e6;
        }
        .header-row:last-child {
            border-bottom: none;
        }
        .header-label {
            display: table-cell;
            width: 30%;
            padding: 10px 15px;
            font-weight: bold;
            background: #e9ecef;
            border-right: 1px solid #dee2e6;
            vertical-align: middle;
        }
        .header-value {
            display: table-cell;
            padding: 10px 15px;
            vertical-align: middle;
        }
        
        /* Title Section */
        .document-title {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
        }
        .document-title h1 {
            font-size: 28px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .document-title .subtitle {
            font-size: 14px;
            opacity: 0.95;
        }
        
        /* Section Headers */
        .section-header {
            background: #343a40;
            color: white;
            padding: 12px 20px;
            margin: 30px 0 15px 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-left: 5px solid #667eea;
        }
        
        /* Product Overview Table */
        .overview-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            border: 2px solid #dee2e6;
        }
        .overview-table th {
            background: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #dee2e6;
            border-right: 1px solid #dee2e6;
            width: 30%;
        }
        .overview-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
            border-right: 1px solid #dee2e6;
        }
        .overview-table tr:last-child td {
            border-bottom: none;
        }
        .overview-table td:last-child,
        .overview-table th:last-child {
            border-right: none;
        }
        
        /* Specifications Table */
        .specs-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            border: 1px solid #dee2e6;
        }
        .specs-table th,
        .specs-table td {
            padding: 10px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
            border-right: 1px solid #dee2e6;
        }
        .specs-table th {
            background: #f8f9fa;
            font-weight: bold;
            width: 40%;
        }
        .specs-table tr:last-child td {
            border-bottom: none;
        }
        .specs-table td:last-child,
        .specs-table th:last-child {
            border-right: none;
        }
        
        /* Image Section */
        .image-section {
            text-align: center;
            margin: 25px 0;
            padding: 20px;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
        }
        .item-image {
            max-width: 300px;
            max-height: 300px;
            border: 3px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .image-label {
            margin-top: 10px;
            font-weight: bold;
            color: #495057;
        }
        
        /* Content Boxes */
        .content-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px 20px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .content-box h4 {
            margin-bottom: 10px;
            color: #495057;
            font-size: 13px;
            font-weight: bold;
        }
        .content-box p {
            margin: 0;
            color: #6c757d;
            line-height: 1.8;
        }
        
        /* Status Badges */
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-active {
            background: #28a745;
            color: white;
        }
        .badge-inactive {
            background: #dc3545;
            color: white;
        }
        
        /* Two Column Layout */
        .two-column {
            display: table;
            width: 100%;
            margin: 20px 0;
        }
        .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }
        .column:first-child {
            padding-left: 0;
        }
        .column:last-child {
            padding-right: 0;
        }
        
        /* Footer Section */
        .completion-section {
            margin-top: 40px;
            padding: 20px;
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 8px;
        }
        .sign-off {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
        }
        .sign-off-row {
            display: table;
            width: 100%;
            margin-top: 20px;
        }
        .sign-off-cell {
            display: table-cell;
            width: 50%;
            padding: 10px;
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white;
            }
            .section-header {
                page-break-after: avoid;
            }
            .content-box {
                page-break-inside: avoid;
            }
        }
        
        /* N/A Styling */
        .na {
            color: #adb5bd;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Document Header -->
    <div class="document-header">
        <div class="header-grid">
            <div class="header-row">
                <div class="header-label">Company Name</div>
                <div class="header-value">{{ $item->company_item->name ?? 'N/A' }}</div>
            </div>
            <div class="header-row">
                <div class="header-label">Location</div>
                <div class="header-value">{{ $item->bussiness_location ?? 'N/A' }}</div>
            </div>
            <div class="header-row">
                <div class="header-label">Document Version</div>
                <div class="header-value">000001</div>
            </div>
            <div class="header-row">
                <div class="header-label">Date Created</div>
                <div class="header-value">{{ \Carbon\Carbon::parse($item->created_at)->format('d.m.Y H:i') }} {{ date('T') }}</div>
            </div>
        </div>
    </div>

    <!-- Document Title -->
    <div class="document-title">
        <h1>Product Specification</h1>
        <div class="subtitle">Complete Product Details & Specifications</div>
    </div>

    <!-- Product Overview -->
    <div class="section-header">Product Overview</div>
    
    <table class="overview-table">
        <tr>
            <th>Product Name</th>
            <td>{{ $item->product_item->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Product Code</th>
            <td>{{ $item->bar_code ?? ($item->p_id ?? 'N/A') }}</td>
        </tr>
        <tr>
            <th>Product Type</th>
            <td><span class="badge badge-active">{{ ucfirst($item->type ?? 'N/A') }}</span></td>
        </tr>
    </table>

    <!-- Product Description -->
    <div class="content-box">
        <h4>Product Description</h4>
        <p>{{ $item->pro_dis ?? ($item->short_disc ?? 'No description available.') }}</p>
    </div>

    <!-- Purpose -->
    @if($item->category || $item->subcategory)
    <div class="content-box">
        <h4>Purpose</h4>
        <p>
            This product is categorized under 
            <strong>{{ $item->category->name ?? 'N/A' }}</strong>
            @if($item->subcategory)
                - <strong>{{ $item->subcategory->name }}</strong>
            @endif
            @if($item->company_item)
                and is manufactured by <strong>{{ $item->company_item->name }}</strong>.
            @endif
        </p>
    </div>
    @endif

    <!-- Product Images -->
    @if($item->image || ($item->images && count($item->images) > 0))
    <div class="section-header">Product Image(s)</div>
    <div class="image-section">
        @if($item->image)
            @php
                $imagePath = str_replace(url('/'), '', $item->image);
                $imagePath = ltrim($imagePath, '/');
                $fullImagePath = public_path($imagePath);
            @endphp
            @if(file_exists($fullImagePath))
            <img src="{{ $fullImagePath }}" class="item-image" alt="Product Image">
            <div class="image-label">Product Main Image</div>
            @endif
        @endif
    </div>
    @endif

    <!-- Design Specifications -->
    <div class="section-header">Design Specifications</div>
    
    <table class="specs-table">
        @if($item->weight_for_delivery)
        <tr>
            <th>Weight</th>
            <td>
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
                    {{ $unitLabel }}
                @else
                    kg
                @endif
            </td>
        </tr>
        @endif
        
        @if($item->unit_item)
        <tr>
            <th>Unit of Measurement</th>
            <td>{{ $item->unit_item->name ?? 'N/A' }}</td>
        </tr>
        @endif
        
        @if($item->dimensions ?? null)
        <tr>
            <th>Dimensions</th>
            <td>{{ $item->dimensions }}</td>
        </tr>
        @endif
    </table>

    <!-- Materials / Components -->
    <div class="section-header">Materials / Components</div>
    
    <table class="specs-table">
        @if($item->quality_item)
        <tr>
            <th>Quality</th>
            <td>{{ $item->quality_item->name ?? 'N/A' }}</td>
        </tr>
        @endif
        
        @if($item->technology_item)
        <tr>
            <th>Series</th>
            <td>{{ $item->technology_item->name ?? 'N/A' }}</td>
        </tr>
        @endif
        
        @if($item->group_item)
        <tr>
            <th>Group Name</th>
            <td>{{ $item->group_item->name ?? 'N/A' }}</td>
        </tr>
        @endif
        
        @if($item->made_in_item)
        <tr>
            <th>Made In</th>
            <td>{{ $item->made_in_item->name ?? 'N/A' }}</td>
        </tr>
        @endif
    </table>

    <!-- Product Specifications by Type -->
    @if($item->type == 'battery')
    <div class="section-header">Battery Specifications</div>
    <table class="specs-table">
        @if($item->plate_item)
        <tr>
            <th>Plates</th>
            <td>{{ $item->plate_item->name ?? 'N/A' }}</td>
        </tr>
        @endif
        @if($item->amphors_item)
        <tr>
            <th>Amperes</th>
            <td>{{ $item->amphors_item->name ?? 'N/A' }}</td>
        </tr>
        @endif
        @if($item->volt_item)
        <tr>
            <th>Volt</th>
            <td>{{ $item->volt_item->name ?? 'N/A' }}</td>
        </tr>
        @endif
        @if($item->cca_item)
        <tr>
            <th>CCA</th>
            <td>{{ $item->cca_item->name ?? 'N/A' }}</td>
        </tr>
        @endif
        @if($item->minus_pool_item)
        <tr>
            <th>Minus Pole Direction</th>
            <td>{{ $item->minus_pool_item->name ?? 'N/A' }}</td>
        </tr>
        @endif
        @if($item->warrenty_item)
        <tr>
            <th>Warranty</th>
            <td>{{ $item->warrenty_item->name ?? 'N/A' }}</td>
        </tr>
        @endif
        @if($item->serial_number)
        <tr>
            <th>Serial Number</th>
            <td>{{ $item->serial_number }}</td>
        </tr>
        @endif
        @if($item->battery_size)
        <tr>
            <th>Battery Size</th>
            <td>{{ $item->battery_size }}</td>
        </tr>
        @endif
    </table>
    @endif

    @if($item->type == 'oil')
    <div class="section-header">Oil Specifications</div>
    <table class="specs-table">
        @if($item->grade_item)
        <tr>
            <th>Grade</th>
            <td>{{ $item->grade_item->name ?? 'N/A' }}</td>
        </tr>
        @endif
        @if($item->mileage_item)
        <tr>
            <th>Mileage</th>
            <td>{{ $item->mileage_item->name ?? 'N/A' }}</td>
        </tr>
        @endif
    </table>
    @endif

    @if($item->type == 'scrap')
    <div class="section-header">Scrap Specifications</div>
    <table class="specs-table">
        @if($item->level_item)
        <tr>
            <th>Level</th>
            <td>{{ $item->level_item->name ?? 'N/A' }}</td>
        </tr>
        @endif
    </table>
    @endif

    @if($item->type == 'services')
    <div class="section-header">Services Specifications</div>
    <table class="specs-table">
        @if($item->services_item)
        <tr>
            <th>Services</th>
            <td>{{ $item->services_item->name ?? 'N/A' }}</td>
        </tr>
        @endif
    </table>
    @endif

    <!-- Part Number & Identification -->
    @if($item->partnumber_item)
    <div class="section-header">Part Number & Identification</div>
    <table class="specs-table">
        <tr>
            <th>Part Number</th>
            <td>{{ $item->partnumber_item->name ?? 'N/A' }}</td>
        </tr>
        @if($item->bar_code)
        <tr>
            <th>Barcode</th>
            <td>{{ $item->bar_code }}</td>
        </tr>
        @endif
    </table>
    @endif

    <!-- Vehicle Compatibility -->
    @if($item->vehical_item)
    <div class="section-header">Vehicle Compatibility</div>
    <table class="specs-table">
        @if($item->vehical_item->manutacturer_vehical)
        <tr>
            <th>Manufacturer</th>
            <td>{{ $item->vehical_item->manutacturer_vehical->name ?? 'N/A' }}</td>
        </tr>
        @endif
        @if($item->vehical_item->model_vehical)
        <tr>
            <th>Model</th>
            <td>{{ $item->vehical_item->model_vehical->name ?? 'N/A' }}</td>
        </tr>
        @endif
        @if($item->vehical_item->engine_vehical)
        <tr>
            <th>Engine</th>
            <td>{{ $item->vehical_item->engine_vehical->name ?? 'N/A' }} CC</td>
        </tr>
        @endif
        @if($item->vehical_item->country_vehical)
        <tr>
            <th>Country</th>
            <td>{{ $item->vehical_item->country_vehical->name ?? 'N/A' }}</td>
        </tr>
        @endif
        @if($item->vehical_item->vehical_part_number)
        <tr>
            <th>Vehicle Part Number</th>
            <td>{{ $item->vehical_item->vehical_part_number->name ?? 'N/A' }}</td>
        </tr>
        @endif
    </table>
    @endif

    <!-- Packaging Specifications -->
    <div class="section-header">Packaging Specifications</div>
    <table class="specs-table">
        @if($item->packing)
        <tr>
            <th>Packing</th>
            <td>{{ $item->packing }}</td>
        </tr>
        @endif
        @if($item->scale)
        <tr>
            <th>Scale</th>
            <td>{{ $item->scale }}</td>
        </tr>
        @endif
        @if($item->filling)
        <tr>
            <th>Filling</th>
            <td>{{ number_format($item->filling, 2) }}</td>
        </tr>
        @endif
        @if(!$item->packing && !$item->scale && !$item->filling)
        <tr>
            <th>Packaging Information</th>
            <td class="na">N/A</td>
        </tr>
        @endif
    </table>

    <!-- Labeling Specifications -->
    <div class="section-header">Labeling Specifications</div>
    <table class="specs-table">
        @if($item->barcode_image)
        <tr>
            <th>Barcode Image</th>
            <td>Available (Generated)</td>
        </tr>
        @endif
        @if(!$item->barcode_image)
        <tr>
            <th>Labeling Information</th>
            <td class="na">N/A</td>
        </tr>
        @endif
    </table>

    <!-- Additional Information -->
    <div class="section-header">Additional Information</div>
    
    <div class="two-column">
        <div class="column">
            <table class="specs-table">
                <tr>
                    <th>Stock Available</th>
                    <td>{{ $item->on_hand ?? 0 }} Units</td>
                </tr>
                @if($item->min_qty)
                <tr>
                    <th>Minimum Quantity Alert</th>
                    <td>{{ $item->min_qty }}</td>
                </tr>
                @endif
                @if($item->rack)
                <tr>
                    <th>Rack Location</th>
                    <td>{{ $item->rack }}</td>
                </tr>
                @endif
                @if($item->supplier)
                <tr>
                    <th>Supplier</th>
                    <td>{{ $item->supplier }}</td>
                </tr>
                @endif
            </table>
        </div>
        <div class="column">
            <table class="specs-table">
                @if($item->price_per_unit)
                <tr>
                    <th>Price per Unit</th>
                    <td>Rs. {{ number_format($item->price_per_unit, 2) }}</td>
                </tr>
                @endif
                @if($item->total_price)
                <tr>
                    <th>Total Price</th>
                    <td>Rs. {{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endif
                @if($item->sale_price)
                <tr>
                    <th>Sale Price per Unit</th>
                    <td>Rs. {{ number_format($item->sale_price, 2) }}</td>
                </tr>
                @endif
                @if($item->total_sale_price)
                <tr>
                    <th>Total Sale Price</th>
                    <td>Rs. {{ number_format($item->total_sale_price, 2) }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    <!-- Status Information -->
    <div class="section-header">Status & Compliance</div>
    <table class="specs-table">
        <tr>
            <th>Status</th>
            <td>
                <span class="badge {{ $item->is_active ? 'badge-active' : 'badge-inactive' }}">
                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                </span>
            </td>
        </tr>
        @if($item->update_date)
        <tr>
            <th>Update Date</th>
            <td>{{ \Carbon\Carbon::parse($item->update_date)->format('d.m.Y') }}</td>
        </tr>
        @endif
        @if($item->last_updated_at)
        <tr>
            <th>Last Updated</th>
            <td>
                {{ \Carbon\Carbon::parse($item->last_updated_at)->format('d.m.Y H:i') }}
                @if($item->updated_by_user)
                    by {{ $item->updated_by_user->name }}
                @endif
            </td>
        </tr>
        @endif
    </table>

    <!-- Completion Section -->
    <div class="completion-section">
        <div class="section-header" style="margin-top: 0; background: transparent; color: #333; padding: 0; border: none; border-bottom: 2px solid #667eea;">Completion</div>
        
        <div class="content-box" style="margin-top: 20px;">
            <h4>Additional Notes or Observations</h4>
            <p class="na">{{ $item->pro_dis ?? ($item->short_disc ?? 'N/A') }}</p>
        </div>

        <div class="sign-off">
            <div class="sign-off-row">
                <div class="sign-off-cell">
                    <strong>Created By:</strong><br>
                    {{ $item->item_user->name ?? 'Unknown' }}<br>
                    {{ $item->item_user->email ?? '' }}<br>
                    {{ \Carbon\Carbon::parse($item->created_at)->format('d.m.Y H:i') }} {{ date('T') }}
                </div>
                <div class="sign-off-cell" style="text-align: right;">
                    <strong>Document Generated:</strong><br>
                    {{ date('d.m.Y H:i') }} {{ date('T') }}<br><br>
                    <strong>Document Version:</strong><br>
                    000001
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div style="margin-top: 40px; padding: 20px; text-align: center; border-top: 2px solid #dee2e6; color: #6c757d; font-size: 9px;">
        <p>This document was generated automatically by the Product Management System</p>
        <p>&copy; {{ date('Y') }} Product Specification Document - Confidential</p>
    </div>
</body>
</html>


