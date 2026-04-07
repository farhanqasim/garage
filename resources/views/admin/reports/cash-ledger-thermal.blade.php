<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cash Ledger Thermal Print</title>
    @php
        $paperMm = ($paper === '58') ? '58' : '80';
        $lineWidth = $paperMm === '58' ? 32 : 42;
        $companyName = setting_value('logo_text', config('app.name', 'Business'));
    @endphp
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #fff; color: #000; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
        .thermal-wrap { width: {{ $paperMm }}mm; max-width: {{ $paperMm }}mm; margin: 0 auto; padding: 3mm 2.5mm 8mm; font-size: 10px; line-height: 1.35; }
        .center { text-align: center; }
        .title { font-weight: 700; font-size: 12px; margin: 1mm 0; }
        .muted { color: #000; opacity: .85; }
        .hr { border: none; border-top: 1px dashed #000; margin: 2mm 0; }
        .line { display: flex; justify-content: space-between; gap: 6px; }
        .line .k { font-weight: 600; flex-shrink: 0; }
        .line .v { text-align: right; word-break: break-word; }
        .blk { margin-bottom: 2mm; }
        .entry { margin: 0 0 2.5mm; }
        .neg { font-weight: 700; }
        .small { font-size: 9px; }
        .feed { height: 8mm; }
        .thermal-actions {
            width: {{ $paperMm }}mm;
            max-width: {{ $paperMm }}mm;
            margin: 10px auto 6px;
            display: flex;
            justify-content: center;
        }
        .thermal-print-btn {
            border: 1px solid #000;
            background: #fff;
            color: #000;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
            line-height: 1.1;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .thermal-print-btn[disabled] {
            opacity: .6;
            cursor: not-allowed;
        }
        @media screen { body { padding: 10px 0; background: #f3f3f3; } .thermal-wrap { background: #fff; box-shadow: 0 1px 5px rgba(0,0,0,.15); } }
        @media print {
            @page { size: {{ $paperMm }}mm auto; margin: 0; }
            body { background: #fff; }
            .thermal-wrap { width: {{ $paperMm }}mm; max-width: {{ $paperMm }}mm; margin: 0 auto; padding: 2mm 2mm 6mm; box-shadow: none; }
            .no-print, .thermal-actions { display: none !important; }
        }
    </style>
</head>
<body>
<div class="no-print thermal-actions">
    <button type="button" id="cash-ledger-bluetooth-print-btn" class="thermal-print-btn" onclick="return window.cashLedgerThermalPrintNow ? window.cashLedgerThermalPrintNow() : false;">
        <i class="ti ti-printer me-1" aria-hidden="true"></i>
        Bluetooth Print
    </button>
</div>
<div class="thermal-wrap">
    <div class="center">
        <div class="title">{{ $companyName }}</div>
        <div class="small muted">{{ $branchName }}</div>
        <div class="small">Cash Ledger Report</div>
    </div>

    <hr class="hr">
    <div class="blk">
        <div class="line"><span class="k">Range</span><span class="v">{{ $from->format('d M Y') }} - {{ $to->format('d M Y') }}</span></div>
        <div class="line"><span class="k">Printed</span><span class="v">{{ $generatedAt->format('d M Y h:i A') }}</span></div>
        <div class="line"><span class="k">User</span><span class="v">{{ $userName }}</span></div>
        @if(!empty($filters['type']))
            <div class="line"><span class="k">Type</span><span class="v">{{ $typeLabels[$filters['type']] ?? $filters['type'] }}</span></div>
        @endif
        @if(!empty($filters['q']))
            <div class="line"><span class="k">Search</span><span class="v">{{ $filters['q'] }}</span></div>
        @endif
        @if(!empty($filters['party']))
            <div class="line"><span class="k">Party</span><span class="v">{{ $filters['party'] }}</span></div>
        @endif
    </div>

    <hr class="hr">
    <div class="blk">
        <div class="line"><span class="k">Opening Cash Balance</span><span class="v">Rs {{ number_format($summary['opening_balance'], 2) }}</span></div>
        <div class="line"><span class="k">Total Cash In</span><span class="v">Rs {{ number_format($summary['total_cash_in'], 2) }}</span></div>
        <div class="line"><span class="k">Total Cash Out</span><span class="v">Rs {{ number_format($summary['total_cash_out'], 2) }}</span></div>
        <div class="line"><span class="k">Net Cash Flow</span><span class="v">Rs {{ number_format($summary['net_cash_flow'], 2) }}</span></div>
        <div class="line"><span class="k">Closing Cash Balance</span><span class="v {{ $summary['closing_balance'] < 0 ? 'neg' : '' }}">Rs {{ number_format($summary['closing_balance'], 2) }}</span></div>
    </div>

    <hr class="hr">
    @forelse($rows as $row)
        <div class="entry">
            @php
                $entryDt = \Carbon\Carbon::parse(($row['date'] ?? now()->toDateString()) . ' ' . ($row['time'] ?? '00:00:00'));
            @endphp
            <div class="line"><span class="k">Date</span><span class="v">{{ $entryDt->format('d-M-Y') }}</span></div>
            <div class="line"><span class="k">Time</span><span class="v">{{ $entryDt->format('h:i A') }}</span></div>
            <div class="line"><span class="k">Ref</span><span class="v">{{ $row['voucher_ref'] }}</span></div>
            <div class="line"><span class="k">Type</span><span class="v">{{ $row['transaction_type_label'] }}</span></div>
            <div class="line"><span class="k">Party</span><span class="v">{{ $row['party'] ?: '—' }}</span></div>
            <div class="line"><span class="k">By</span><span class="v">{{ $row['created_by'] ?: '—' }}</span></div>
            <div class="line"><span class="k">Dr</span><span class="v">{{ (float)$row['debit'] > 0 ? ('Rs '.number_format((float)$row['debit'], 2)) : 'Rs 0.00' }}</span></div>
            <div class="line"><span class="k">Cr</span><span class="v">{{ (float)$row['credit'] > 0 ? ('Rs '.number_format((float)$row['credit'], 2)) : 'Rs 0.00' }}</span></div>
            <div class="line"><span class="k">Bal</span><span class="v {{ (float)$row['running_balance'] < 0 ? 'neg' : '' }}">Rs {{ number_format((float)$row['running_balance'], 2) }}</span></div>
            @if(!empty($row['description']))
                <div class="line"><span class="k">Note</span><span class="v">{{ $row['description'] }}</span></div>
            @endif
            <div class="line"><span class="k">Branch</span><span class="v">{{ $row['branch'] ?: '—' }}</span></div>
            <hr class="hr">
        </div>
    @empty
        <div class="center small muted">No record found for selected range.</div>
    @endforelse

    <div class="feed"></div>
</div>

@php
    $ledgerRowsForJs = [];
    foreach ($rows as $r) {
        $ledgerRowsForJs[] = [
            'date' => $r['date'] ?? '',
            'time' => $r['time'] ?? '',
            'ref' => $r['voucher_ref'] ?? '',
            'type' => $r['transaction_type_label'] ?? '',
            'party' => $r['party'] ?? '',
            'by' => $r['created_by'] ?? '',
            'debit' => (float) ($r['debit'] ?? 0),
            'credit' => (float) ($r['credit'] ?? 0),
            'balance' => (float) ($r['running_balance'] ?? 0),
            'note' => $r['description'] ?? '',
            'branch' => $r['branch'] ?? '',
        ];
    }

    $ledgerDataForJs = [
        'company_name' => $companyName,
        'branch_name' => $branchName,
        'from' => $from->format('d M Y'),
        'to' => $to->format('d M Y'),
        'printed_at' => $generatedAt->format('d M Y h:i A'),
        'user_name' => $userName,
        'summary' => $summary,
        'rows' => $ledgerRowsForJs,
    ];
@endphp

@include('admin.sales.partials.thermal-print-client')
<script>
(function () {
    var printBtn = document.getElementById('cash-ledger-bluetooth-print-btn');
    var isPrintRunning = false;
    var unlockTimer = null;
    var ledgerData = @json($ledgerDataForJs);

    function toMoney(v) {
        var n = Number(v || 0);
        return n.toFixed(2);
    }
    function moneyPretty(v) {
        var x = toMoney(v).split('.');
        x[0] = x[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return x.join('.');
    }
    function lineWrap(text, width) {
        var raw = String(text == null ? '' : text);
        if (!raw || raw.length <= width) return [raw];
        var words = raw.split(/\s+/);
        var out = [];
        var line = '';
        for (var i = 0; i < words.length; i++) {
            var next = line ? (line + ' ' + words[i]) : words[i];
            if (next.length <= width) line = next;
            else {
                if (line) out.push(line);
                line = words[i];
            }
        }
        if (line) out.push(line);
        return out;
    }
    function twoCol(k, v, width) {
        var L = String(k || '');
        var R = String(v || '');
        if (L.length + R.length <= width) return L + ' '.repeat(width - L.length - R.length) + R;
        var maxL = Math.max(6, width - R.length - 1);
        if (L.length > maxL) L = L.slice(0, maxL - 2) + '..';
        var gap = Math.max(1, width - L.length - R.length);
        return (L + ' '.repeat(gap) + R).slice(0, width);
    }
    function buildCashLedgerEscPos(data, settings) {
        var paper = String(settings.paperSize) === '58' ? '58' : '80';
        var width = paper === '58' ? 32 : 42;
        var bytes = [];
        function pushArray(arr) { for (var i = 0; i < arr.length; i++) bytes.push(arr[i]); }
        function pushText(t) { pushArray(Array.from(escPosTextEncoder(String(t || '')))); }
        function nl() { pushText('\n'); }
        function centerOn() { pushArray([0x1B, 0x61, 0x01]); }
        function leftOn() { pushArray([0x1B, 0x61, 0x00]); }
        function bold(on) { pushArray([0x1B, 0x45, on ? 0x01 : 0x00]); }
        function hr() { pushText('-'.repeat(width)); nl(); }

        pushArray([0x1B, 0x40]);
        centerOn(); bold(true); pushText(data.company_name || 'Business'); bold(false); nl();
        pushText(data.branch_name || 'Branch'); nl();
        pushText('Cash Ledger Report'); nl();
        leftOn(); hr();
        pushText(twoCol('Range', (data.from || '') + ' - ' + (data.to || ''), width)); nl();
        pushText(twoCol('Printed', data.printed_at || '', width)); nl();
        pushText(twoCol('User', data.user_name || '', width)); nl();
        hr();
        pushText(twoCol('Opening', 'Rs ' + moneyPretty(data.summary.opening_balance), width)); nl();
        pushText(twoCol('Cash In', 'Rs ' + moneyPretty(data.summary.total_cash_in), width)); nl();
        pushText(twoCol('Cash Out', 'Rs ' + moneyPretty(data.summary.total_cash_out), width)); nl();
        pushText(twoCol('Net Flow', 'Rs ' + moneyPretty(data.summary.net_cash_flow), width)); nl();
        pushText(twoCol('Closing', 'Rs ' + moneyPretty(data.summary.closing_balance), width)); nl();
        hr();

        var rows = Array.isArray(data.rows) ? data.rows : [];
        for (var r = 0; r < rows.length; r++) {
            var row = rows[r] || {};
            pushText(twoCol('Date', row.date || '', width)); nl();
            pushText(twoCol('Time', row.time || '', width)); nl();
            pushText(twoCol('Ref', row.ref || '', width)); nl();
            pushText(twoCol('Type', row.type || '', width)); nl();
            pushText(twoCol('Party', row.party || '-', width)); nl();
            pushText(twoCol('By', row.by || '-', width)); nl();
            pushText(twoCol('Dr', 'Rs ' + moneyPretty(row.debit || 0), width)); nl();
            pushText(twoCol('Cr', 'Rs ' + moneyPretty(row.credit || 0), width)); nl();
            pushText(twoCol('Bal', 'Rs ' + moneyPretty(row.balance || 0), width)); nl();
            if (row.note) {
                var noteLines = lineWrap('Note: ' + row.note, width);
                for (var n = 0; n < noteLines.length; n++) { pushText(noteLines[n]); nl(); }
            }
            pushText(twoCol('Branch', row.branch || '-', width)); nl();
            hr();
        }
        nl(); nl(); nl();
        if (settings.autoCut) pushArray([0x1D, 0x56, 0x42, 0x00]);
        return new Uint8Array(bytes);
    }

    function setBusyState() {
        if (!printBtn) return;
        printBtn.disabled = true;
        printBtn.innerHTML = '<i class="ti ti-loader ti-spin me-1" aria-hidden="true"></i>Printing...';
    }

    function setIdleState() {
        if (!printBtn) return;
        printBtn.disabled = false;
        printBtn.innerHTML = '<i class="ti ti-printer me-1" aria-hidden="true"></i>Bluetooth Print';
    }

    function unlockPrint() {
        if (!isPrintRunning) return;
        isPrintRunning = false;
        if (unlockTimer) {
            clearTimeout(unlockTimer);
            unlockTimer = null;
        }
        setIdleState();
    }

    async function triggerThermalPrint() {
        if (isPrintRunning) return;
        isPrintRunning = true;
        setBusyState();
        try {
            if (typeof getThermalPrintSettings !== 'function') {
                throw new Error('Thermal printer client is not available.');
            }
            var settings = getThermalPrintSettings();
            settings.paperSize = @json($paperMm);
            var copies = Math.max(1, parseInt(settings.duplicateCount, 10) || 1);
            var data = buildCashLedgerEscPos(ledgerData, settings);

            if (settings.type === 'bluetooth') {
                if (!navigator.bluetooth) throw new Error('Bluetooth printing not supported in this browser.');
                if (!window.__THERMAL_PRINTER_STATE__ || !window.__THERMAL_PRINTER_STATE__.bluetooth || !window.__THERMAL_PRINTER_STATE__.bluetooth.characteristic) {
                    await connectBluetoothPrinter();
                }
                for (var i = 0; i < copies; i++) await sendToBluetooth(data);
            } else if (settings.type === 'usb') {
                if (!navigator.usb) throw new Error('USB printing not supported in this browser.');
                if (!window.__THERMAL_PRINTER_STATE__ || !window.__THERMAL_PRINTER_STATE__.usb || !window.__THERMAL_PRINTER_STATE__.usb.device) {
                    await connectUsbPrinter();
                }
                for (var u = 0; u < copies; u++) await sendToUsb(data);
            } else if (settings.type === 'serial') {
                if (!navigator.serial) throw new Error('Serial (COM) printing not supported in this browser.');
                if (!window.__THERMAL_PRINTER_STATE__ || !window.__THERMAL_PRINTER_STATE__.serial || !window.__THERMAL_PRINTER_STATE__.serial.port) {
                    await connectSerialPrinter();
                }
                for (var s = 0; s < copies; s++) await sendToSerial(data);
            } else {
                throw new Error('Bluetooth Print requires Bluetooth/USB/Serial mode in thermal settings.');
            }
            unlockTimer = setTimeout(unlockPrint, 500);
        } catch (e) {
            var msg = (e && e.message) ? e.message : 'Bluetooth print failed.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Print Failed', text: msg, confirmButtonText: 'OK' });
            } else {
                alert(msg);
            }
            unlockPrint();
        }
    }
    window.cashLedgerThermalPrintNow = function () {
        triggerThermalPrint();
        return false;
    };

    if (printBtn) {
        printBtn.addEventListener('click', function (e) {
            e.preventDefault();
            triggerThermalPrint();
        });
    }

    var params = new URLSearchParams(window.location.search || '');
    if (params.get('auto_print') === '1') {
        window.addEventListener('load', function () {
            setTimeout(triggerThermalPrint, 150);
        });
    }
})();
</script>
</body>
</html>
