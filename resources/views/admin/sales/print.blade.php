<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #{{ $sale->reference ?? $sale->id }}</title>
    @php
        $thermalPaperMm = $thermalPaperMm ?? '80';
        if (! in_array((string) $thermalPaperMm, ['58', '80'], true)) {
            $thermalPaperMm = '80';
        }
        $thermalAutoCut = $thermalAutoCut ?? true;
        $feedBottomMm = $thermalAutoCut ? 16 : 8;
    @endphp
    <style>
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            background: #f0f0f0;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .wrap {
            width: {{ $thermalPaperMm }}mm;
            max-width: {{ $thermalPaperMm }}mm;
            margin: 0 auto;
            padding: 8mm 4mm 24mm;
            background: #fff;
            @if($thermalPaperMm === '58')
            font-size: 10px;
            @endif
        }
        @media screen {
            body { padding: 12px 0; }
            .wrap { box-shadow: 0 1px 6px rgba(0,0,0,.12); }
        }
        @page {
            size: {{ $thermalPaperMm }}mm auto;
            margin: 0;
        }
        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            max-width: 420px;
            margin: 0 auto 12px;
            padding: 10px 12px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 6px rgba(0,0,0,.12);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            justify-content: center;
        }
        .print-toolbar button,
        .print-toolbar a.btn-link {
            font: inherit;
            font-size: 13px;
            padding: 8px 14px;
            border-radius: 6px;
            border: 1px solid #ccc;
            background: #f8f9fa;
            cursor: pointer;
            text-decoration: none;
            color: #212529;
        }
        .print-toolbar button.primary {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }
        .print-toolbar button.secondary {
            background: #6f42c1;
            border-color: #6f42c1;
            color: #fff;
        }
        @media print {
            html, body { background: #fff; }
            .wrap {
                width: {{ $thermalPaperMm }}mm;
                max-width: {{ $thermalPaperMm }}mm;
                margin: 0 auto;
                padding: 4mm 3mm {{ $feedBottomMm }}mm;
                box-shadow: none;
            }
            .no-print { display: none !important; }
        }
        .center { text-align: center; }
        .logo { max-width: 48mm; max-height: 18mm; object-fit: contain; display: inline-block; margin-bottom: 2mm; }
        .shop-name { font-size: 13px; font-weight: 700; margin: 0 0 1mm; line-height: 1.2; }
        .muted { font-size: 9px; color: #333; line-height: 1.35; margin: 0; }
        .hr { border: none; border-top: 1px dashed #999; margin: 3mm 0; }
        .row { display: flex; justify-content: space-between; font-size: 10px; margin-bottom: 1mm; gap: 2mm; }
        .row strong { flex-shrink: 0; }
        .row span { text-align: right; word-break: break-word; }
        table { width: 100%; border-collapse: collapse; font-size: 9px; margin: 2mm 0; }
        th, td { padding: 1mm 0; vertical-align: top; }
        th { border-bottom: 1px solid #000; font-weight: 600; text-align: left; }
        th:nth-child(2), th:nth-child(3), th:nth-child(4),
        td:nth-child(2), td:nth-child(3), td:nth-child(4) { text-align: right; white-space: nowrap; }
        .item-name { font-weight: 600; word-break: break-word; }
        .item-sub { font-size: 8px; color: #444; font-weight: 400; display: block; margin-top: 0.5mm; }
        .totals { font-size: 10px; margin-top: 2mm; }
        .totals .row { margin-bottom: 1.2mm; }
        .grand { font-size: 12px; font-weight: 700; margin-top: 1mm; padding-top: 1mm; border-top: 1px solid #000; }
        .thanks { text-align: center; font-size: 10px; margin-top: 4mm; padding-top: 2mm; }
        .feed { height: 12mm; }
        .feed-print { display: none; }
        @media print {
            .feed-print { display: block; height: {{ $feedBottomMm }}mm; }
        }
    </style>
</head>
<body>
@php
    $logoUrl = setting_value('logo');
    if (!$logoUrl) {
        $logoUrl = asset('assets/img/logo.svg');
    }
    $companyName = setting_value('logo_text', 'MUBARAK TRADERS');
    $helpline = setting_value('helpline', '+92-335-08-999-08');
    $address = setting_value('address', '');
    $city = setting_value('city', '');
    $state = setting_value('state', '');
    $zip = setting_value('zip', '');
    $country = setting_value('country', '');
    $invoiceLabel = $sale->reference ? $sale->reference : ('SALE-' . $sale->id);
    $when = $sale->sale_date ? $sale->sale_date->copy() : null;
    $timeStr = $sale->created_at ? $sale->created_at->format('H:i') : '';
@endphp
<div class="no-print print-toolbar" role="toolbar" aria-label="Print options">
    <a href="#" class="btn-link" id="print-toolbar-back">{{ $returnTo === 'list' ? 'Back to list' : 'Back to sale' }}</a>
    <button type="button" class="primary" id="print-toolbar-browser-print">Print</button>
    <button type="button" class="secondary" id="print-toolbar-thermal-print" title="Bluetooth BLE, USB, or COM (saved in Settings)">Thermal / Bluetooth</button>
</div>
<div class="wrap">
    <div class="center">
        <img src="{{ $logoUrl }}" alt="" class="logo" width="120" height="48">
        <p class="shop-name">{{ $companyName }}</p>
        @if($address || $city || $state || $zip || $country)
            <p class="muted">
                @if($address){{ $address }}, @endif
                @if($city){{ $city }}, @endif
                @if($state){{ $state }}, @endif
                @if($zip){{ $zip }} @endif
                @if($country){{ $country }}@endif
            </p>
        @endif
        @php $emailLine = setting_value('email', ''); @endphp
        @if($emailLine || $helpline)
            <p class="muted">@if($emailLine){{ $emailLine }}@endif @if($emailLine && $helpline) · @endif @if($helpline){{ $helpline }}@endif</p>
        @endif
    </div>
    <div class="hr"></div>
    <div class="row"><strong>Invoice</strong><span>#{{ $invoiceLabel }}</span></div>
    <div class="row"><strong>Date</strong><span>{{ $when ? $when->format('d M Y') : '—' }}@if($timeStr) {{ $timeStr }}@endif</span></div>
    @if($sale->branch)
        <div class="row"><strong>Branch</strong><span>{{ $sale->branch->branch_name }}</span></div>
    @endif
    @if($sale->customer)
        <div class="row"><strong>Customer</strong><span>{{ $sale->customer->names[0] ?? 'Walk-in' }}</span></div>
    @endif
    <div class="hr"></div>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $saleItem)
                @php
                    $item = $saleItem->item;
                    $entryType = (string) ($saleItem->entry_type ?? '');
                    $isTemporary = $entryType === 'temporary';
                    $isPlaceholder = $entryType === 'placeholder';
                    $itemName = $item
                        ? (($item->short_disc ?? $item->pro_dis ?? $item->bar_code ?? 'N/A'))
                        : 'N/A';
                    if ($item && $item->partnumber_item) {
                        $itemName = $item->partnumber_item->name ?? $itemName;
                    }
                    if ($item && $item->category) {
                        $itemName .= ' - ' . $item->category->name;
                    }
                    if ($isTemporary) {
                        $itemName = $saleItem->temporary_item_name ?: ($saleItem->voice_transcript ?: 'Temporary item');
                    } elseif ($isPlaceholder) {
                        $itemName = $saleItem->line_note ?: 'Placeholder line';
                    }
                @endphp
                <tr>
                    <td>
                        <span class="item-name">{{ $itemName }}</span>
                        @if($isTemporary && $saleItem->temporary_quality)
                            <span class="item-sub">Q: {{ $saleItem->temporary_quality }}</span>
                        @endif
                    </td>
                    <td>{{ number_format((float) $saleItem->quantity, 2) }}</td>
                    <td>{{ number_format((float) $saleItem->rate, 2) }}</td>
                    <td>{{ number_format((float) $saleItem->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="hr"></div>
    @php
        $subtotalPrint = $sale->subtotal;
        if ($subtotalPrint === null || $subtotalPrint === '') {
            $subtotalPrint = $sale->saleItems->sum('total');
        }
    @endphp
    <div class="totals">
        <div class="row"><strong>Subtotal</strong><span>Rs {{ number_format((float) $subtotalPrint, 2) }}</span></div>
        @if((float) ($sale->discount ?? 0) > 0)
            <div class="row"><strong>Discount</strong><span>- Rs {{ number_format((float) $sale->discount, 2) }}</span></div>
        @endif
        @if((float) ($sale->order_tax ?? 0) > 0)
            <div class="row"><strong>Tax</strong><span>Rs {{ number_format((float) $sale->order_tax, 2) }}</span></div>
        @endif
        @if((float) ($sale->shipping ?? 0) > 0)
            <div class="row"><strong>Shipping</strong><span>Rs {{ number_format((float) $sale->shipping, 2) }}</span></div>
        @endif
        <div class="row grand"><strong>Grand Total</strong><span>Rs {{ number_format((float) $sale->grand_total, 2) }}</span></div>
    </div>
    <p class="thanks">Thank you for your business!</p>
    <div class="feed no-print"></div>
    <div class="feed-print" aria-hidden="true"></div>
</div>
@include('admin.sales.partials.thermal-print-client')
<script>
(function () {
    var returnTo = @json($returnTo);
    var showUrl = @json(route('sales.show', $sale->id));
    var listUrl = @json(route('all_sales'));
    var saleId = @json((int) $sale->id);
    var done = false;

    function navigateAfterPrint() {
        if (done) return;
        done = true;
        if (window.opener && !window.opener.closed) {
            try { window.close(); return; } catch (e) {}
        }
        if (returnTo === 'list') {
            window.location.href = listUrl;
            return;
        }
        window.location.href = showUrl;
    }

    window.addEventListener('afterprint', function () {
        setTimeout(navigateAfterPrint, 150);
    });

    var backEl = document.getElementById('print-toolbar-back');
    if (backEl) {
        backEl.addEventListener('click', function (e) {
            e.preventDefault();
            done = false;
            navigateAfterPrint();
        });
    }

    var browserBtn = document.getElementById('print-toolbar-browser-print');
    if (browserBtn) {
        browserBtn.addEventListener('click', function () {
            window.print();
        });
    }

    var thermalBtn = document.getElementById('print-toolbar-thermal-print');
    if (thermalBtn) {
        thermalBtn.addEventListener('click', function () {
            if (typeof runThermalPrintBySettings !== 'function' || typeof getThermalPrintSettings !== 'function') {
                alert('Thermal print script failed to load.');
                return;
            }
            thermalBtn.disabled = true;
            runThermalPrintBySettings(saleId, getThermalPrintSettings())
                .catch(function (err) {
                    if (err && (err.name === 'NotFoundError' || err.name === 'AbortError')) {
                        return;
                    }
                    alert(err && err.message ? err.message : 'Thermal print failed.');
                })
                .finally(function () {
                    thermalBtn.disabled = false;
                });
        });
    }
})();
</script>
</body>
</html>
