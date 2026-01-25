<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Report - Elite Car Wash</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">
    <div id="root" class="min-h-screen">
        <!-- Header -->
        <header class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white p-6 shadow-2xl">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-black uppercase tracking-tighter mb-1">Daily Jobs Report</h1>
                        <p class="text-sm opacity-90">{{ $branchName }} • {{ $userName }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('car.wash') }}" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-black uppercase transition-colors backdrop-blur-sm">← Car Wash</a>
                        <a href="{{ route('car.wash.completed-jobs') }}" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-black uppercase transition-colors backdrop-blur-sm">Completed Jobs</a>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto p-6">
            <!-- Date, Filters & Actions -->
            <div class="bg-white rounded-2xl shadow-xl border-2 border-slate-200 p-6 mb-6">
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-sm font-black text-slate-700 uppercase mb-2">Report Date</label>
                        <input type="date" id="reportDate" value="{{ $selectedDate }}"
                            class="px-4 py-3 border-2 border-slate-300 rounded-xl text-slate-900 font-bold focus:border-indigo-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-700 uppercase mb-2">Customer (Vehicle)</label>
                        <select id="filterCustomer" class="px-4 py-3 border-2 border-slate-300 rounded-xl text-slate-900 font-bold focus:border-indigo-500 focus:outline-none min-w-[180px]">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-black text-slate-700 uppercase mb-2">Worker</label>
                        <select id="filterWorker" class="px-4 py-3 border-2 border-slate-300 rounded-xl text-slate-900 font-bold focus:border-indigo-500 focus:outline-none min-w-[160px]">
                            <option value="">All</option>
                        </select>
                    </div>
                    <button type="button" id="btnLoadReport"
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-black uppercase transition-colors">
                        Load Report
                    </button>
                    <button type="button" id="btnDownloadPdf" disabled
                        class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-black uppercase transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download PDF
                    </button>
                </div>
            </div>

            <!-- Summary (like image: total vehicles, total debit, total credit, cash on hand, total workers, total commission) -->
            <div id="totalsSection" class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6 hidden">
                <div class="bg-white rounded-xl border-2 border-slate-200 p-4 shadow">
                    <p class="text-xs font-black text-slate-500 uppercase">Total Vehicles</p>
                    <p id="totVehicles" class="text-xl font-black text-slate-900">0</p>
                </div>
                <div class="bg-amber-50 rounded-xl border-2 border-amber-200 p-4 shadow">
                    <p class="text-xs font-black text-amber-800 uppercase">Total Refreshment Expenses</p>
                    <p id="totDebit" class="text-xl font-black text-amber-800">Rs.0.00</p>
                </div>
                <div class="bg-blue-50 rounded-xl border-2 border-blue-200 p-4 shadow">
                    <p class="text-xs font-black text-blue-700 uppercase">Total Credit</p>
                    <p id="totCredit" class="text-xl font-black text-blue-700">Rs.0.00</p>
                </div>
                <div class="bg-indigo-100 rounded-xl border-2 border-indigo-300 p-4 shadow">
                    <p class="text-xs font-black text-indigo-900 uppercase">Cash on Hand</p>
                    <p id="totCashOnHand" class="text-xl font-black text-indigo-900">Rs.0.00</p>
                </div>
                <div class="bg-white rounded-xl border-2 border-slate-200 p-4 shadow">
                    <p class="text-xs font-black text-slate-500 uppercase">Total Workers</p>
                    <p id="totWorkers" class="text-xl font-black text-slate-900">0</p>
                </div>
                <div class="bg-emerald-50 rounded-xl border-2 border-emerald-200 p-4 shadow">
                    <p class="text-xs font-black text-emerald-800 uppercase">Total Commission</p>
                    <p id="totCommission" class="text-xl font-black text-emerald-800">Rs.0.00</p>
                </div>
            </div>

            <!-- Ledger Table: Date & Time | Vehicle | Debit | Credit | Total | Worker | Commission -->
            <div id="tableSection" class="bg-white rounded-2xl shadow-xl border-2 border-slate-200 overflow-hidden hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-black uppercase">Date & Time</th>
                                <th class="px-4 py-3 text-left text-xs font-black uppercase">Vehicle</th>
                                <th class="px-4 py-3 text-right text-xs font-black uppercase">Credit</th>
                                <th class="px-4 py-3 text-right text-xs font-black uppercase">Refreshment Expenses</th>
                                <th class="px-4 py-3 text-right text-xs font-black uppercase">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-black uppercase">Worker</th>
                                <th class="px-4 py-3 text-right text-xs font-black uppercase">Commission</th>
                                <th class="px-4 py-3 text-right text-xs font-black uppercase">G.total</th>
                            </tr>
                        </thead>
                        <tbody id="reportTableBody" class="divide-y divide-slate-200">
                        </tbody>
                        <tfoot>
                            <tr class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white font-black">
                                <td class="px-4 py-3">Total</td>
                                <td class="px-4 py-3">-</td>
                                <td id="ftCredit" class="px-4 py-3 text-right">Rs.0.00</td>
                                <td id="ftExpenses" class="px-4 py-3 text-right">Rs.0.00</td>
                                <td id="ftTotal" class="px-4 py-3 text-right">Rs.0.00</td>
                                <td class="px-4 py-3">-</td>
                                <td id="ftCommission" class="px-4 py-3 text-right">Rs.0.00</td>
                                <td id="ftGtotal" class="px-4 py-3 text-right">Rs.0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Empty state (no jobs, only opening) -->
            <div id="emptyState" class="bg-white rounded-2xl shadow-xl border-2 border-slate-200 p-16 text-center hidden">
                <p class="text-xl font-black text-slate-600">No completed jobs for this date.</p>
                <p class="text-slate-500 mt-2">Select another date or apply different filters.</p>
            </div>

            <!-- Loading -->
            <div id="loadingState" class="bg-white rounded-2xl shadow-xl border-2 border-slate-200 p-16 text-center">
                <p class="text-slate-500">Select a date and click <strong>Load Report</strong>.</p>
            </div>
        </main>
    </div>

    <script>
        (function() {
            const reportDate = document.getElementById('reportDate');
            const filterCustomer = document.getElementById('filterCustomer');
            const filterWorker = document.getElementById('filterWorker');
            const btnLoad = document.getElementById('btnLoadReport');
            const btnPdf = document.getElementById('btnDownloadPdf');
            const totalsSection = document.getElementById('totalsSection');
            const tableSection = document.getElementById('tableSection');
            const tableBody = document.getElementById('reportTableBody');
            const emptyState = document.getElementById('emptyState');
            const loadingState = document.getElementById('loadingState');

            const routes = {
                data: '{{ route("car-wash.jobs.daily-report-data") }}',
                pdf: '{{ route("car-wash.jobs.daily-report-pdf") }}'
            };

            function showLoading() {
                loadingState.classList.remove('hidden');
                totalsSection.classList.add('hidden');
                tableSection.classList.add('hidden');
                emptyState.classList.add('hidden');
            }

            function renderReport(data) {
                loadingState.classList.add('hidden');

                // Populate filters (customers & workers) - keep current selection if still in list
                const custVal = filterCustomer.value;
                const workVal = filterWorker.value;
                filterCustomer.innerHTML = '<option value="">All</option>';
                (data.customers || []).forEach(function(c) {
                    const o = document.createElement('option');
                    o.value = c.value;
                    o.textContent = c.label;
                    if (c.value === custVal) o.selected = true;
                    filterCustomer.appendChild(o);
                });
                filterWorker.innerHTML = '<option value="">All</option>';
                (data.workers || []).forEach(function(w) {
                    const o = document.createElement('option');
                    o.value = w.value;
                    o.textContent = w.label;
                    if (w.value === workVal) o.selected = true;
                    filterWorker.appendChild(o);
                });

                const rows = data.rows || [];
                const hasJobs = rows.some(function(r) { return !r.isOpening; });

                if (rows.length === 0) {
                    emptyState.classList.remove('hidden');
                    tableSection.classList.add('hidden');
                    totalsSection.classList.add('hidden');
                    btnPdf.disabled = true;
                    return;
                }

                emptyState.classList.add('hidden');
                tableSection.classList.remove('hidden');
                totalsSection.classList.remove('hidden');
                btnPdf.disabled = false;

                const t = data.totals || {};
                document.getElementById('totVehicles').textContent = t.totalVehicles || 0;
                document.getElementById('totDebit').textContent = 'Rs.' + (t.totalDebit || 0).toFixed(2);
                document.getElementById('totCredit').textContent = 'Rs.' + (t.totalCredit || 0).toFixed(2);
                document.getElementById('totCashOnHand').textContent = 'Rs.' + (t.cashOnHand || 0).toFixed(2);
                document.getElementById('totWorkers').textContent = t.totalWorkers || 0;
                document.getElementById('totCommission').textContent = 'Rs.' + (t.totalCommission || 0).toFixed(2);
                // Footer row (har column ky nechy sum). Total = no commission. G.total = commission subtract.
                document.getElementById('ftExpenses').textContent = 'Rs.' + (t.totalDebit || 0).toFixed(2);
                document.getElementById('ftCredit').textContent = 'Rs.' + (t.totalCredit || 0).toFixed(2);
                document.getElementById('ftTotal').textContent = 'Rs.' + (t.cashOnHand || 0).toFixed(2);
                document.getElementById('ftCommission').textContent = 'Rs.' + (t.totalCommission || 0).toFixed(2);
                document.getElementById('ftGtotal').textContent = 'Rs.' + (t.sumGtotal != null ? t.sumGtotal : ((t.cashOnHand || 0) - (t.totalCommission || 0))).toFixed(2);

                function fmtNum(n) {
                    if (n === 0 || n === '-') return n === 0 ? '0' : '-';
                    return 'Rs.' + (Number(n) || 0).toFixed(2);
                }

                let html = '';
                rows.forEach(function(r) {
                    const creditStr = (r.credit || 0) > 0 ? fmtNum(r.credit) : '-';
                    const gTotalStr = r.gTotal != null ? fmtNum(r.gTotal) : '-';
                    const totalStr = r.total != null ? fmtNum(r.total) : '-';
                    const expenseStr = (r.debit || 0) > 0 ? fmtNum(r.debit) : '-';
                    const commStr = (r.commission !== '-' && (r.commission || 0) > 0) ? fmtNum(r.commission) : (r.commission || '-');
                    const rowClass = r.isOpening ? 'bg-slate-100 font-semibold' : 'hover:bg-slate-50';
                    // Main row: Refreshment Expenses aur Total khali; next row mein aayenge
                    html += '<tr class="' + rowClass + '">' +
                        '<td class="px-4 py-3 text-sm text-slate-700">' + (r.dateTime || '-') + '</td>' +
                        '<td class="px-4 py-3 text-sm font-semibold text-slate-900">' + (r.vehicle || '-') + '</td>' +
                        '<td class="px-4 py-3 text-sm text-right ' + ((r.credit || 0) > 0 ? 'font-bold text-blue-600' : 'text-slate-500') + '">' + creditStr + '</td>' +
                        '<td class="px-4 py-3"></td>' +
                        '<td class="px-4 py-3"></td>' +
                        '<td class="px-4 py-3 text-sm text-slate-700">' + (r.worker || '-') + '</td>' +
                        '<td class="px-4 py-3 text-sm text-right ' + ((r.commission !== '-' && (r.commission || 0) > 0) ? 'font-bold text-emerald-600' : 'text-slate-500') + '">' + commStr + '</td>' +
                        '<td class="px-4 py-3 text-sm text-right font-bold text-indigo-600">' + gTotalStr + '</td>' +
                        '</tr>';
                    // Next row: Refreshment Expenses | Total (image ke mutabiq)
                    html += '<tr class="bg-slate-50 border-b border-slate-100">' +
                        '<td class="px-4 py-2"></td>' +
                        '<td class="px-4 py-2"></td>' +
                        '<td class="px-4 py-2"></td>' +
                        '<td class="px-4 py-2 text-sm text-right ' + ((r.debit || 0) > 0 ? 'font-bold text-amber-700' : 'text-slate-400') + '">' + expenseStr + '</td>' +
                        '<td class="px-4 py-2 text-sm text-right font-bold text-slate-800">' + totalStr + '</td>' +
                        '<td class="px-4 py-2"></td>' +
                        '<td class="px-4 py-2"></td>' +
                        '<td class="px-4 py-2"></td>' +
                        '</tr>';
                });
                tableBody.innerHTML = html;
            }

            function buildUrl(base, params) {
                const p = new URLSearchParams();
                Object.keys(params).forEach(function(k) {
                    if (params[k] != null && params[k] !== '') p.set(k, params[k]);
                });
                const q = p.toString();
                return base + (q ? '?' + q : '');
            }

            function loadReport() {
                const date = reportDate.value;
                if (!date) return;
                showLoading();
                const url = buildUrl(routes.data, {
                    date: date,
                    customer: filterCustomer.value || '',
                    worker: filterWorker.value || ''
                });
                fetch(url)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) renderReport(data);
                        else renderReport({ rows: [], totals: {}, customers: [], workers: [] });
                    })
                    .catch(function() { renderReport({ rows: [], totals: {}, customers: [], workers: [] }); });
            }

            btnLoad.addEventListener('click', loadReport);

            filterCustomer.addEventListener('change', loadReport);
            filterWorker.addEventListener('change', loadReport);

            btnPdf.addEventListener('click', function() {
                if (btnPdf.disabled) return;
                const date = reportDate.value;
                if (!date) return;
                const url = buildUrl(routes.pdf, {
                    date: date,
                    customer: filterCustomer.value || '',
                    worker: filterWorker.value || ''
                });
                window.location.href = url;
            });

            reportDate.addEventListener('change', loadReport);

            if (reportDate.value) loadReport();
        })();
    </script>
</body>
</html>
