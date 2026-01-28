<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate List - A4 Print</title>
    <style>
        body { font-family: Segoe UI, Arial, sans-serif; margin: 20px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 8px 10px; text-align: left; }
        th { background: #1e293b; color: #fff; font-weight: 700; }
        tr:nth-child(even) { background: #f1f5f9; }
        tbody td { font-weight: bold; }
        h1 { text-align: center; margin-bottom: 16px; }
        .print-hint { text-align: center; margin-top: 20px; font-size: 10px; color: #64748b; }
        .close-btn { position: fixed; top: 15px; right: 15px; width: 40px; height: 40px; background: #ef4444; color: white; border: none; border-radius: 50%; font-size: 24px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px rgba(0,0,0,0.3); z-index: 1000; transition: background 0.2s; }
        .close-btn:hover { background: #dc2626; }
        @media print {
            @page { size: A4; margin: 15mm; }
            body { margin: 0; }
            table { font-size: 11px; }
            .print-hint { display: none; }
            .close-btn { display: none; }
        }
    </style>
</head>
<body>
    <button class="close-btn" id="rateListCloseBtn" title="Close">×</button>
    <h1>Elite Car Wash Rate List</h1>
    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th>Base Rate</th>
                <th>Premium Service Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $index => $service)
            <tr>
                <td>{{ $service->label ?? 'N/A' }}</td>
                <td>{{ number_format((float) ($service->base_price ?? $service->basePrice ?? 0), 0) }}</td>
                <td>
                    @if(!empty($service->additional_prices) && is_array($service->additional_prices))
                        @php
                            $premiumText = collect($service->additional_prices)->map(fn($p) => ($p['label'] ?? '') . ' ( ' . (int) ($p['amount'] ?? 0) . ' )')->join(', ') ?: '-';
                        @endphp
                        {{ ucwords($premiumText) }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p class="print-hint">Print (Ctrl+P) – Select "Save as PDF" for A4</p>
    <script>
        (function() {
            var returnUrl = {!! json_encode(request()->get('return_url', '')) !!};
            document.getElementById('rateListCloseBtn').onclick = function() {
                window.close();
                setTimeout(function() {
                    if (returnUrl) { window.location.href = returnUrl; }
                }, 200);
            };
        })();
        window.onload = function() {
            setTimeout(function() { window.print(); }, 300);
        };
    </script>
</body>
</html>
