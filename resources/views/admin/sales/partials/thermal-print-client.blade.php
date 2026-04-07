{{-- Shared thermal print (Bluetooth BLE / Bluetooth Serial COM / USB / Wired) — used by create sale + sales list --}}
<script>
window.__SALE_PRINT_PAYLOAD_TEMPLATE__ = @json(route('sales.print.payload', ['id' => '__SALE_ID__']));
window.__SALE_PRINT_VIEW_TEMPLATE__ = @json(route('sales.print', ['id' => '__SALE_ID__', 'return' => 'list']));
window.__THERMAL_PRINT_DEFAULTS__ = {
    type: 'serial',
    serialBaudRate: 9600,
    paperSize: '80',
    autoCut: true,
    duplicateCount: 1,
    openInvoiceAfterSave: true
};

function getThermalPrintSettings() {
    try {
        var stored = JSON.parse(localStorage.getItem('thermal_print_settings') || '{}');
        var merged = Object.assign({}, window.__THERMAL_PRINT_DEFAULTS__, stored || {});
        // Old installs defaulted to BLE; typical thermal printers use COM (serial). Migrate unless user explicitly saved "Bluetooth BLE".
        if (merged.type === 'bluetooth' && stored.thermal_ble_explicit !== true) {
            merged.type = 'serial';
            stored.type = 'serial';
            localStorage.setItem('thermal_print_settings', JSON.stringify(stored));
        }
        return merged;
    } catch (e) {
        return Object.assign({}, window.__THERMAL_PRINT_DEFAULTS__);
    }
}

function thermalPersistPreferSerial() {
    try {
        var stored = JSON.parse(localStorage.getItem('thermal_print_settings') || '{}');
        stored.type = 'serial';
        localStorage.setItem('thermal_print_settings', JSON.stringify(stored));
    } catch (e) {}
}

function thermalUserCancelledDevice(err) {
    return err && (err.name === 'NotFoundError' || err.name === 'AbortError');
}

function saveThermalPrintSettings(settings) {
    localStorage.setItem('thermal_print_settings', JSON.stringify(settings));
}

function escPosTextEncoder(text) {
    return new TextEncoder().encode(String(text || ''));
}

function replaceSaleId(template, saleId) {
    return String(template || '').replace('__SALE_ID__', String(saleId || ''));
}

async function loadSalePrintPayload(saleId) {
    var url = replaceSaleId(window.__SALE_PRINT_PAYLOAD_TEMPLATE__, saleId);
    var response = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if (!response.ok) {
        throw new Error('Could not load invoice print payload');
    }
    return response.json();
}

function thermalLine(text, maxLen) {
    var raw = String(text == null ? '' : text);
    if (!maxLen || raw.length <= maxLen) return [raw];
    var words = raw.split(/\s+/);
    var out = [];
    var line = '';
    for (var i = 0; i < words.length; i++) {
        var next = line ? (line + ' ' + words[i]) : words[i];
        if (next.length <= maxLen) {
            line = next;
        } else {
            if (line) out.push(line);
            line = words[i];
        }
    }
    if (line) out.push(line);
    return out;
}

function toMoney(value) {
    return Number(value || 0).toFixed(2);
}

function moneyPretty(value) {
    var x = toMoney(value);
    var parts = x.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    return parts.join('.');
}

function thermalPadLeft(str, len) {
    var s = String(str == null ? '' : str);
    if (s.length >= len) {
        return s.slice(-len);
    }
    return ' '.repeat(len - s.length) + s;
}

function thermalTwoCol(label, value, w) {
    var L = String(label || '');
    var R = String(value || '');
    if (L.length + R.length <= w) {
        return L + ' '.repeat(w - L.length - R.length) + R;
    }
    var maxL = Math.max(6, w - R.length - 1);
    if (L.length > maxL) {
        L = L.slice(0, Math.max(0, maxL - 1)) + '..';
    }
    var gap = w - L.length - R.length;
    if (gap < 1) {
        return (L + ' ' + R).slice(0, w);
    }
    return L + ' '.repeat(gap) + R;
}

function thermalIsCrossOriginUrl(url) {
    try {
        var u = new URL(url, window.location.href);
        return u.origin !== window.location.origin;
    } catch (e) {
        return false;
    }
}

function escPosGsV0RasterFromRgba(rgbaData, widthPx, heightPx) {
    var bytesPerRow = Math.ceil(widthPx / 8);
    var xL = bytesPerRow & 0xff;
    var xH = (bytesPerRow >> 8) & 0xff;
    var yL = heightPx & 0xff;
    var yH = (heightPx >> 8) & 0xff;
    var out = [];
    out.push(0x1D, 0x76, 0x30, 0x00, xL, xH, yL, yH);
    for (var y = 0; y < heightPx; y++) {
        for (var bx = 0; bx < bytesPerRow; bx++) {
            var b = 0;
            for (var bit = 0; bit < 8; bit++) {
                var x = bx * 8 + bit;
                if (x < widthPx) {
                    var idx = (y * widthPx + x) * 4;
                    var gray = (rgbaData[idx] + rgbaData[idx + 1] + rgbaData[idx + 2]) / 3;
                    if (gray < 135) {
                        b |= (0x80 >> bit);
                    }
                }
            }
            out.push(b);
        }
    }
    return new Uint8Array(out);
}

async function escPosRasterFromImageUrl(imageUrl, maxWidthPx) {
    if (!imageUrl) {
        return null;
    }
    return new Promise(function (resolve) {
        var img = new Image();
        if (thermalIsCrossOriginUrl(imageUrl)) {
            img.crossOrigin = 'anonymous';
        }
        img.onload = function () {
            try {
                var nw = img.naturalWidth || img.width;
                var nh = img.naturalHeight || img.height;
                if (!nw || !nh) {
                    resolve(null);
                    return;
                }
                var scale = Math.min(1, maxWidthPx / nw);
                var w = Math.max(1, Math.round(nw * scale));
                var h = Math.max(1, Math.round(nh * scale));
                var maxH = 100;
                if (h > maxH) {
                    h = maxH;
                    w = Math.max(1, Math.round((nw * maxH) / nh));
                }
                var canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                var ctx = canvas.getContext('2d');
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, w, h);
                ctx.drawImage(img, 0, 0, w, h);
                var id = ctx.getImageData(0, 0, w, h);
                resolve(escPosGsV0RasterFromRgba(id.data, w, h));
            } catch (e) {
                resolve(null);
            }
        };
        img.onerror = function () {
            resolve(null);
        };
        img.src = imageUrl;
    });
}

function escPosBarcodeTextForCode39(raw) {
    var s = String(raw || '').toUpperCase().replace(/[^A-Z0-9\-\.\s\$\/\+\%]/g, '');
    if (s.length < 1) {
        return 'SALE0';
    }
    return s.length > 40 ? s.slice(0, 40) : s;
}

function escPosAppendCode39Barcode(pushArray, nl, centerOn, leftOn, barcodeText) {
    var clean = escPosBarcodeTextForCode39(barcodeText);
    var data = escPosTextEncoder(clean);
    var n = data.length;
    if (n < 1) {
        return;
    }
    centerOn();
    pushArray([0x1D, 0x48, 0x02]);
    pushArray([0x1D, 0x68, 0x48]);
    pushArray([0x1D, 0x77, 0x02]);
    pushArray([0x1D, 0x6B, 0x04, n]);
    pushArray(Array.from(data));
    nl();
    leftOn();
}

/**
 * ESC/POS receipt (thermal printer bytes).
 */
async function buildEscPosReceipt(payload, settings) {
    var paper = String(settings.paperSize) === '58' ? '58' : '80';
    var width = paper === '58' ? 32 : 42;
    var qtyW = 5;
    var priceW = paper === '58' ? 7 : 8;
    var totalW = paper === '58' ? 8 : 9;
    var numsBlock = qtyW + 1 + priceW + 1 + totalW;
    var itemColW = width - numsBlock;

    var bytes = [];
    function pushArray(arr) { for (var i = 0; i < arr.length; i++) bytes.push(arr[i]); }
    function pushText(t) { pushArray(Array.from(escPosTextEncoder(t))); }
    function nl() { pushText('\n'); }
    function centerOn() { pushArray([0x1B, 0x61, 0x01]); }
    function leftOn() { pushArray([0x1B, 0x61, 0x00]); }
    function bold(on) { pushArray([0x1B, 0x45, on ? 0x01 : 0x00]); }
    function hr() { pushText('-'.repeat(width)); nl(); }

    function tableHeaderLine() {
        var itemPart = ('Item' + ' '.repeat(itemColW)).slice(0, itemColW);
        var q = thermalPadLeft('Qty', qtyW);
        var p = thermalPadLeft('Price', priceW);
        var t = thermalPadLeft('Total', totalW);
        return itemPart + q + ' ' + p + ' ' + t;
    }

    function itemNumbersLine(qty, rate, total) {
        var q = thermalPadLeft(toMoney(qty), qtyW);
        var p = thermalPadLeft(toMoney(rate), priceW);
        var t = thermalPadLeft(toMoney(total), totalW);
        var block = q + ' ' + p + ' ' + t;
        return block.padStart(width);
    }

    var shop = payload.shop || {};

    pushArray([0x1B, 0x40]);
    centerOn();
    var maxLogoW = paper === '58' ? 256 : 384;
    if (payload.logo_url) {
        try {
            var logoRaster = await escPosRasterFromImageUrl(payload.logo_url, maxLogoW);
            if (logoRaster && logoRaster.length) {
                pushArray(Array.from(logoRaster));
                nl();
            }
        } catch (e) {}
    }
    if (payload.barcode_text) {
        escPosAppendCode39Barcode(pushArray, nl, centerOn, leftOn, payload.barcode_text);
        nl();
    }
    centerOn();
    bold(true);
    pushText(shop.name || 'SHOP');
    bold(false);
    nl();
    if (shop.address) {
        thermalLine(shop.address, width).forEach(function (l) {
            pushText(l);
            nl();
        });
    }
    var emailLine = '';
    if (shop.email) {
        emailLine = String(shop.email);
    }
    if (shop.phone) {
        emailLine = emailLine ? (emailLine + ' · ' + shop.phone) : String(shop.phone);
    }
    if (emailLine) {
        thermalLine(emailLine, width).forEach(function (l) {
            pushText(l);
            nl();
        });
    }
    hr();
    leftOn();
    var invLabel = 'Invoice';
    var invVal = '#' + String(payload.invoice_no || payload.id || '');
    pushText(thermalTwoCol(invLabel, invVal, width));
    nl();
    pushText(thermalTwoCol('Date', String((payload.sale_date || '') + ' ' + (payload.sale_time || '')).trim(), width));
    nl();
    if (payload.branch_name) {
        pushText(thermalTwoCol('Branch', payload.branch_name, width));
        nl();
    }
    if (payload.customer_name) {
        pushText(thermalTwoCol('Customer', payload.customer_name, width));
        nl();
    }
    hr();
    pushText(tableHeaderLine());
    nl();
    (payload.items || []).forEach(function (item) {
        thermalLine(item.name || '', width).forEach(function (l) {
            pushText(l);
            nl();
        });
        pushText(itemNumbersLine(item.quantity, item.rate, item.total));
        nl();
    });
    hr();
    pushText(thermalTwoCol('Subtotal', 'Rs ' + moneyPretty(payload.subtotal), width));
    nl();
    if (Number(payload.discount || 0) > 0) {
        pushText(thermalTwoCol('Discount', '- Rs ' + moneyPretty(payload.discount), width));
        nl();
    }
    if (Number(payload.tax || 0) > 0) {
        pushText(thermalTwoCol('Tax', 'Rs ' + moneyPretty(payload.tax), width));
        nl();
    }
    if (Number(payload.shipping || 0) > 0) {
        pushText(thermalTwoCol('Shipping', 'Rs ' + moneyPretty(payload.shipping), width));
        nl();
    }
    bold(true);
    pushText(thermalTwoCol('Grand Total', 'Rs ' + moneyPretty(payload.grand_total), width));
    bold(false);
    nl();
    hr();
    centerOn();
    pushText('Thank you for your business!');
    nl();
    nl();
    nl();
    nl();
    nl();
    if (settings.autoCut) {
        pushArray([0x1D, 0x56, 0x42, 0x00]);
    }
    return new Uint8Array(bytes);
}

window.__THERMAL_PRINTER_STATE__ = {
    bluetooth: { device: null, characteristic: null },
    serial: { port: null },
    usb: { device: null, endpointOut: null, interfaceNumber: null }
};

var THERMAL_BLE_OPTIONAL_SERVICES = [
    0xFFE0,
    0xFFF0,
    '6e400001-b5a3-f393-e0a9-e50e24dcca9e',
    '49535343-fe7d-4ae5-8fa9-9fafd2050e6e',
    '0000fff0-0000-1000-8000-00805f9b34fb',
    '0000ff00-0000-1000-8000-00805f9b34fb',
    '0000ae00-0000-1000-8000-00805f9b34fb',
    '0000ae30-0000-1000-8000-00805f9b34fb'
];

function thermalBleTryGetService(server, uuid) {
    return server.getPrimaryService(uuid).catch(function () { return null; });
}

function thermalBleTryGetCharacteristic(service, uuid) {
    return service.getCharacteristic(uuid).catch(function () { return null; });
}

async function thermalBleDiscoverWriteCharacteristic(server) {
    function pickWritable(chars) {
        var j, c;
        for (j = 0; j < chars.length; j++) {
            c = chars[j];
            if (c.properties.writeWithoutResponse || c.properties.write) return c;
        }
        return null;
    }

    var si, svc, chars, ch;
    for (si = 0; si < THERMAL_BLE_OPTIONAL_SERVICES.length; si++) {
        svc = await thermalBleTryGetService(server, THERMAL_BLE_OPTIONAL_SERVICES[si]);
        if (!svc) continue;
        try {
            chars = await svc.getCharacteristics();
        } catch (e) {
            continue;
        }
        ch = pickWritable(chars);
        if (ch) return ch;
    }

    var knownPairs = [
        { service: 0xFFE0, characteristic: 0xFFE1 },
        { service: 0xFFE0, characteristic: 0xFFE2 },
        { service: 0xFFE0, characteristic: 0xFFE3 },
        { service: '0000ffe0-0000-1000-8000-00805f9b34fb', characteristic: '0000ffe1-0000-1000-8000-00805f9b34fb' },
        { service: '0000ffe0-0000-1000-8000-00805f9b34fb', characteristic: '0000ffe2-0000-1000-8000-00805f9b34fb' },
        { service: '6e400001-b5a3-f393-e0a9-e50e24dcca9e', characteristic: '6e400002-b5a3-f393-e0a9-e50e24dcca9e' },
        { service: 0xFFF0, characteristic: 0xFFF1 },
        { service: 0xFFF0, characteristic: 0xFFF2 },
        { service: '0000fff0-0000-1000-8000-00805f9b34fb', characteristic: '0000fff1-0000-1000-8000-00805f9b34fb' },
        { service: '0000fff0-0000-1000-8000-00805f9b34fb', characteristic: '0000fff2-0000-1000-8000-00805f9b34fb' },
        { service: '0000ff00-0000-1000-8000-00805f9b34fb', characteristic: '0000ff01-0000-1000-8000-00805f9b34fb' }
    ];
    var k, pair;
    for (k = 0; k < knownPairs.length; k++) {
        pair = knownPairs[k];
        svc = await thermalBleTryGetService(server, pair.service);
        if (!svc) continue;
        ch = await thermalBleTryGetCharacteristic(svc, pair.characteristic);
        if (ch && (ch.properties.write || ch.properties.writeWithoutResponse)) return ch;
    }
    var services = [];
    try {
        services = await server.getPrimaryServices();
    } catch (e) {
        services = [];
    }
    for (var i = 0; i < services.length; i++) {
        try {
            chars = await services[i].getCharacteristics();
        } catch (e2) {
            continue;
        }
        ch = pickWritable(chars);
        if (ch) return ch;
    }
    throw new Error(
        'No writable BLE characteristic found. Re-pair printer and try again. If your printer is Classic Bluetooth (SPP), use Wired/USB mode because browser Bluetooth supports BLE only.'
    );
}

function thermalResetBluetoothState() {
    var st = window.__THERMAL_PRINTER_STATE__.bluetooth || {};
    try {
        if (st.device && st.device.gatt && st.device.gatt.connected) {
            st.device.gatt.disconnect();
        }
    } catch (e) {}
    window.__THERMAL_PRINTER_STATE__.bluetooth = { device: null, characteristic: null };
}

async function connectBluetoothPrinter(forceRequestDevice) {
    var state = window.__THERMAL_PRINTER_STATE__.bluetooth || {};
    var device = state.device || null;

    if (device && !forceRequestDevice) {
        try {
            var existingServer = device.gatt.connected ? device.gatt : await device.gatt.connect();
            var existingCharacteristic = await thermalBleDiscoverWriteCharacteristic(existingServer);
            window.__THERMAL_PRINTER_STATE__.bluetooth = { device: device, characteristic: existingCharacteristic };
            return device;
        } catch (reuseErr) {
            thermalResetBluetoothState();
            device = null;
        }
    }

    device = await navigator.bluetooth.requestDevice({
        acceptAllDevices: true,
        optionalServices: THERMAL_BLE_OPTIONAL_SERVICES
    });
    var server = await device.gatt.connect();
    var characteristic = await thermalBleDiscoverWriteCharacteristic(server);
    window.__THERMAL_PRINTER_STATE__.bluetooth = { device: device, characteristic: characteristic };
    return device;
}

async function sendToBluetooth(data, alreadyRetried) {
    var state = window.__THERMAL_PRINTER_STATE__.bluetooth;
    if (!state.characteristic) {
        await connectBluetoothPrinter();
        state = window.__THERMAL_PRINTER_STATE__.bluetooth;
    }
    try {
        var chunkSize = 180;
        for (var i = 0; i < data.length; i += chunkSize) {
            var part = data.slice(i, i + chunkSize);
            if (state.characteristic.properties.writeWithoutResponse) {
                try {
                    await state.characteristic.writeValueWithoutResponse(part);
                } catch (e) {
                    await state.characteristic.writeValue(part);
                }
            } else {
                await state.characteristic.writeValue(part);
            }
            await new Promise(function(resolve){ setTimeout(resolve, 20); });
        }
    } catch (err) {
        if (alreadyRetried) {
            throw err;
        }
        thermalResetBluetoothState();
        await connectBluetoothPrinter(true);
        await sendToBluetooth(data, true);
    }
}

async function connectUsbPrinter() {
    var device = await navigator.usb.requestDevice({ filters: [] });
    await device.open();
    if (device.configuration === null) {
        await device.selectConfiguration(1);
    }
    var iface = (device.configuration.interfaces || [])[0];
    var alt = iface.alternates[0];
    var endpointOut = (alt.endpoints || []).find(function(ep){ return ep.direction === 'out'; });
    await device.claimInterface(iface.interfaceNumber);
    window.__THERMAL_PRINTER_STATE__.usb = {
        device: device,
        interfaceNumber: iface.interfaceNumber,
        endpointOut: endpointOut ? endpointOut.endpointNumber : 1
    };
    return device;
}

async function sendToUsb(data) {
    var state = window.__THERMAL_PRINTER_STATE__.usb;
    if (!state.device) {
        await connectUsbPrinter();
        state = window.__THERMAL_PRINTER_STATE__.usb;
    }
    await state.device.transferOut(state.endpointOut || 1, data);
}

function thermalResetSerialState() {
    var st = window.__THERMAL_PRINTER_STATE__.serial || {};
    try {
        if (st.port) {
            st.port.close();
        }
    } catch (e) {}
    window.__THERMAL_PRINTER_STATE__.serial = { port: null };
}

/**
 * Classic Bluetooth (SPP) thermal printers on Windows usually appear as a COM port after pairing.
 * Web Serial sends raw ESC/POS bytes — works where Web Bluetooth (BLE-only) cannot.
 */
async function connectSerialPrinter() {
    if (!navigator.serial) {
        throw new Error('Web Serial is not available. Use Chrome or Edge, and HTTPS or localhost.');
    }
    var baudRate = parseInt(getThermalPrintSettings().serialBaudRate, 10);
    if (!baudRate || baudRate < 1200) {
        baudRate = 9600;
    }
    var port;
    var granted = await navigator.serial.getPorts();
    if (granted.length === 1) {
        port = granted[0];
    } else {
        port = await navigator.serial.requestPort();
    }
    try {
        await port.open({
            baudRate: baudRate,
            dataBits: 8,
            stopBits: 1,
            parity: 'none',
            flowControl: 'none'
        });
    } catch (openErr) {
        if (openErr && openErr.name === 'InvalidStateError') {
            /* already open */
        } else {
            throw openErr;
        }
    }
    window.__THERMAL_PRINTER_STATE__.serial = { port: port };
    return port;
}

async function sendToSerial(data, alreadyRetried) {
    var state = window.__THERMAL_PRINTER_STATE__.serial || {};
    if (!state.port) {
        await connectSerialPrinter();
        state = window.__THERMAL_PRINTER_STATE__.serial;
    }
    var port = state.port;
    if (!port || !port.writable) {
        throw new Error('Serial port is not open.');
    }
    var writer = port.writable.getWriter();
    try {
        var chunkSize = 512;
        var i;
        for (i = 0; i < data.length; i += chunkSize) {
            var part = data.slice(i, i + chunkSize);
            await writer.write(part);
        }
    } catch (err) {
        try {
            writer.releaseLock();
        } catch (e0) {}
        if (alreadyRetried) {
            throw err;
        }
        thermalResetSerialState();
        await connectSerialPrinter();
        await sendToSerial(data, true);
        return;
    }
    try {
        writer.releaseLock();
    } catch (e2) {}
}

function thermalOpenBrowserPrintWindows(saleId, copies, settings) {
    settings = settings || (typeof getThermalPrintSettings === 'function' ? getThermalPrintSettings() : {});
    var paper = String(settings.paperSize || '80');
    if (paper !== '58' && paper !== '80') {
        paper = '80';
    }
    var autocut = (settings.autoCut === false || settings.autoCut === '0' || settings.autoCut === 0) ? '0' : '1';
    var base = replaceSaleId(window.__SALE_PRINT_VIEW_TEMPLATE__, saleId);
    var join = base.indexOf('?') >= 0 ? '&' : '?';
    var printUrl = base + join + 'paper=' + encodeURIComponent(paper) + '&autocut=' + encodeURIComponent(autocut);
    var w;
    for (w = 0; w < copies; w++) {
        window.open(printUrl, '_blank', 'noopener');
    }
}

async function runThermalPrintBySettings(saleId, settings) {
    var copies = Math.max(1, parseInt(settings.duplicateCount, 10) || 1);
    var payload;
    var data;

    // WebUSB / WebBluetooth permission prompts must run before other awaited work (fetch), or the browser may block them.
    if (settings.type === 'bluetooth') {
        try {
            if (!window.__THERMAL_PRINTER_STATE__.bluetooth.characteristic) {
                await connectBluetoothPrinter();
            }
            payload = await loadSalePrintPayload(saleId);
            data = await buildEscPosReceipt(payload, settings);
            for (var c = 0; c < copies; c++) { await sendToBluetooth(data); }
            return { mode: 'bluetooth' };
        } catch (e) {
            thermalResetBluetoothState();
            // Most "Bluetooth" thermal printers are Classic SPP (COM), not BLE — try Web Serial in the same click gesture.
            if (navigator.serial) {
                try {
                    thermalResetSerialState();
                    await connectSerialPrinter();
                    payload = await loadSalePrintPayload(saleId);
                    data = await buildEscPosReceipt(payload, settings);
                    for (var cBle = 0; cBle < copies; cBle++) { await sendToSerial(data); }
                    thermalPersistPreferSerial();
                    return { mode: 'serial', upgradedFromBle: true };
                } catch (eSerial) {
                    if (thermalUserCancelledDevice(eSerial)) {
                        throw eSerial;
                    }
                }
            }
            if (thermalUserCancelledDevice(e)) {
                throw e;
            }
            thermalOpenBrowserPrintWindows(saleId, copies, settings);
            return { mode: 'wired', fallback: true, reason: (e && e.message) ? e.message : 'ble' };
        }
    }

    if (settings.type === 'usb') {
        try {
            if (!window.__THERMAL_PRINTER_STATE__.usb.device) {
                await connectUsbPrinter();
            }
            payload = await loadSalePrintPayload(saleId);
            data = await buildEscPosReceipt(payload, settings);
            for (var c2 = 0; c2 < copies; c2++) { await sendToUsb(data); }
            return { mode: 'usb' };
        } catch (e) {
            thermalOpenBrowserPrintWindows(saleId, copies, settings);
            return { mode: 'wired', fallback: true, reason: (e && e.message) ? e.message : 'usb' };
        }
    }

    if (settings.type === 'serial') {
        try {
            if (!window.__THERMAL_PRINTER_STATE__.serial.port) {
                await connectSerialPrinter();
            }
            payload = await loadSalePrintPayload(saleId);
            data = await buildEscPosReceipt(payload, settings);
            for (var c3 = 0; c3 < copies; c3++) { await sendToSerial(data); }
            return { mode: 'serial' };
        } catch (e) {
            if (thermalUserCancelledDevice(e)) {
                throw e;
            }
            thermalOpenBrowserPrintWindows(saleId, copies, settings);
            return { mode: 'wired', fallback: true, reason: (e && e.message) ? e.message : 'serial' };
        }
    }

    thermalOpenBrowserPrintWindows(saleId, copies, settings);
    return { mode: 'wired' };
}

window.getThermalPrintSettings = getThermalPrintSettings;
window.runThermalPrintBySettings = runThermalPrintBySettings;
window.replaceSaleId = replaceSaleId;
window.loadSalePrintPayload = loadSalePrintPayload;
</script>
