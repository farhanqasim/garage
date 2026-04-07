<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Price List</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
        th { background: #f0f0f0; font-weight: bold; }
        .text-end { text-align: right; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h1 { margin: 0 0 4px 0; font-size: 14px; }
        .header .sub { font-size: 10px; color: #555; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Item Price List</h1>
        <div class="sub">{{ $currentBranchName ?? 'All branches' }} — {{ now()->format('d M Y H:i') }}</div>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>GST %</th>
                <th>R.TAX %</th>
                <th class="text-end">Retail (Rs.)</th>
                <th class="text-end">Amount at 0%</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                @php
                    $rawName = $item->short_disc ?? $item->pro_dis ?? '';
                    $itemName = trim(strip_tags((string) $rawName));
                    if ($itemName === '' && $item->partnumber_item) {
                        $itemName = $item->partnumber_item->name ?? $item->bar_code ?? '—';
                    }
                    $itemName = $itemName === '' ? ($item->bar_code ?? '—') : $itemName;
                    $isBattery = ($item->type ?? '') === 'battery';
                    if ($isBattery && $item->product_item) {
                        $itemName = $item->product_item->name . ' | ' . ($item->company_item ? $item->company_item->name : '') . ($item->plate_item ? ' ' . trim($item->plate_item->name ?? '') : '') . ($item->amphors_item ? ' ' . trim($item->amphors_item->name ?? '') : '');
                    }
                    $gstPct = (isset($item->tax_percentage) && $item->tax_percentage !== '' && (float)$item->tax_percentage > 0) ? (float)$item->tax_percentage : 18;
                    $rTaxPct = isset($item->r_tax_percentage) && $item->r_tax_percentage !== '' && (float)$item->r_tax_percentage >= 0 ? (float)$item->r_tax_percentage : 0.05;
                    if ($rTaxPct == 0.05) $rTaxPct = 0.5;
                    $retail = $item->retail_price !== null && $item->retail_price !== '' ? (float)$item->retail_price : 0;
                    $priceAfterGst = $retail > 0 ? $retail + round($retail * $gstPct / 100) : 0;
                    $rTaxAmt = $priceAfterGst > 0 ? round($priceAfterGst * $rTaxPct / 100) : 0;
                    $baseAmount = $priceAfterGst + $rTaxAmt;
                    $adjPct = isset($item->amount_adjustment_pct) && $item->amount_adjustment_pct !== '' && $item->amount_adjustment_pct !== null ? (float)$item->amount_adjustment_pct : null;
                    $amountAt0 = $baseAmount > 0 && $adjPct !== null && $adjPct != 0 ? $baseAmount - ($retail * $adjPct/100) : $baseAmount;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $itemName }}</td>
                    <td>{{ number_format($gstPct, 0) }}%</td>
                    <td>{{ number_format($rTaxPct, 2) }}%</td>
                    <td class="text-end">{{ $retail > 0 ? number_format($retail, 2) : '-' }}</td>
                    <td class="text-end">{{ $amountAt0 > 0 ? number_format($amountAt0, 2) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
