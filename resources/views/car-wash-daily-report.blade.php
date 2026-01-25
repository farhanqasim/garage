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
            <!-- Tabs: Cash | Bank -->
            <div class="flex gap-2 mb-4">
                <button type="button" id="tabCash" class="tab-payment px-5 py-2.5 rounded-xl text-sm font-black uppercase transition-all border-2 bg-indigo-600 text-white border-indigo-600">Cash</button>
                <button type="button" id="tabBank" class="tab-payment px-5 py-2.5 rounded-xl text-sm font-black uppercase transition-all border-2 bg-white text-slate-600 border-slate-300 hover:border-indigo-400">Bank</button>
            </div>

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
                    <p id="totDebit" class="text-xl font-black text-amber-800">Rs.0</p>
                </div>
                <div class="bg-blue-50 rounded-xl border-2 border-blue-200 p-4 shadow">
                    <p class="text-xs font-black text-blue-700 uppercase">Total Credit</p>
                    <p id="totCredit" class="text-xl font-black text-blue-700">Rs.0</p>
                </div>
                <div id="cashOnHandCard" class="bg-indigo-100 rounded-xl border-2 border-indigo-300 p-4 shadow cursor-pointer hover:bg-indigo-200 hover:border-indigo-400 transition-colors" title="Click for breakdown: kahan say kitna aya">
                    <p class="text-xs font-black text-indigo-900 uppercase">Cash on Hand</p>
                    <p id="totCashOnHand" class="text-xl font-black text-indigo-900">Rs.0</p>
                </div>
                <div class="bg-white rounded-xl border-2 border-slate-200 p-4 shadow">
                    <p class="text-xs font-black text-slate-500 uppercase">Total Workers</p>
                    <p id="totWorkers" class="text-xl font-black text-slate-900">0</p>
                </div>
                <div class="bg-emerald-50 rounded-xl border-2 border-emerald-200 p-4 shadow">
                    <p class="text-xs font-black text-emerald-800 uppercase">Total Commission</p>
                    <p id="totCommission" class="text-xl font-black text-emerald-800">Rs.0</p>
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
                                <th class="px-4 py-3 text-right text-xs font-black uppercase">Refreshment Expenses</th>
                                <th class="px-4 py-3 text-right text-xs font-black uppercase">Credit</th>
                                <th class="px-4 py-3 text-right text-xs font-black uppercase">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-black uppercase">Worker</th>
                                <th class="px-4 py-3 text-left text-xs font-black uppercase">Bank</th>
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
                                <td id="ftExpenses" class="px-4 py-3 text-right">Rs.0.00</td>
                                <td id="ftCredit" class="px-4 py-3 text-right">Rs.0.00</td>
                                <td id="ftTotal" class="px-4 py-3 text-right">Rs.0.00</td>
                                <td class="px-4 py-3">-</td>
                                <td class="col-bank px-4 py-3">-</td>
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

        <!-- Cash on Hand Detail Modal -->
        <div id="cashOnHandModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-2xl shadow-2xl border-2 border-indigo-200 w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
                <div class="p-4 sm:p-5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white flex justify-between items-center flex-shrink-0">
                    <h3 class="text-lg sm:text-xl font-black uppercase">Cash on Hand – Breakdown<br><span class="text-sm font-normal opacity-90">Har cheez kahan say kitna aaya</span></h3>
                    <button type="button" id="cashOnHandModalClose" class="w-9 h-9 rounded-lg bg-white/20 hover:bg-white/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div id="cashOnHandModalBody" class="p-4 sm:p-5 overflow-y-auto flex-1 text-sm">
                    <p class="text-slate-500">Load a report first, then click Cash on Hand for details.</p>
                </div>
            </div>
        </div>
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
            const tabCash = document.getElementById('tabCash');
            const tabBank = document.getElementById('tabBank');
            const cashOnHandCard = document.getElementById('cashOnHandCard');
            const cashOnHandModal = document.getElementById('cashOnHandModal');
            const cashOnHandModalBody = document.getElementById('cashOnHandModalBody');
            const cashOnHandModalClose = document.getElementById('cashOnHandModalClose');

            let paymentTab = 'cash';
            let lastReportRows = [];
            let lastReportTotals = {};

            function getPaymentTab() { return paymentTab; }
            function setPaymentTab(v) {
                paymentTab = v;
                tabCash.className = 'tab-payment px-5 py-2.5 rounded-xl text-sm font-black uppercase transition-all border-2 ' + (v === 'cash' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-300 hover:border-indigo-400');
                tabBank.className = 'tab-payment px-5 py-2.5 rounded-xl text-sm font-black uppercase transition-all border-2 ' + (v === 'bank' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-300 hover:border-indigo-400');
            }
            function toggleBankColumn() {
                document.querySelectorAll('.col-bank').forEach(function(el) { el.classList.toggle('hidden', getPaymentTab() !== 'bank'); });
            }

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

                lastReportRows = rows;
                lastReportTotals = data.totals || {};

                if (rows.length === 0) {
                    lastReportRows = [];
                    lastReportTotals = {};
                    emptyState.classList.remove('hidden');
                    tableSection.classList.add('hidden');
                    totalsSection.classList.add('hidden');
                    btnPdf.disabled = true;
                    toggleBankColumn();
                    return;
                }

                emptyState.classList.add('hidden');
                tableSection.classList.remove('hidden');
                totalsSection.classList.remove('hidden');
                btnPdf.disabled = false;

                const t = data.totals || {};
                document.getElementById('totVehicles').textContent = t.totalVehicles || 0;
                document.getElementById('totDebit').textContent = 'Rs.' + Math.round(t.totalDebit || 0);
                document.getElementById('totCredit').textContent = 'Rs.' + Math.round(t.totalCredit || 0);
                document.getElementById('totCashOnHand').textContent = 'Rs.' + Math.round(t.cashOnHand || 0);
                document.getElementById('totWorkers').textContent = t.totalWorkers || 0;
                document.getElementById('totCommission').textContent = 'Rs.' + Math.round(t.totalCommission || 0);
                var cohLabel = (getPaymentTab() === 'bank') ? 'Bank Total' : 'Cash on Hand';
                cashOnHandCard.querySelector('p:first-of-type').textContent = cohLabel;
                // Footer row (har column ky nechy sum). Total = no commission. G.total = commission subtract.
                document.getElementById('ftExpenses').textContent = 'Rs.' + Math.round(t.totalDebit || 0);
                document.getElementById('ftCredit').textContent = 'Rs.' + Math.round(t.totalCredit || 0);
                document.getElementById('ftTotal').textContent = 'Rs.' + Math.round(t.cashOnHand || 0);
                document.getElementById('ftCommission').textContent = 'Rs.' + Math.round(t.totalCommission || 0);
                document.getElementById('ftGtotal').textContent = 'Rs.' + Math.round(t.sumGtotal != null ? t.sumGtotal : ((t.cashOnHand || 0) - (t.totalCommission || 0)));

                function fmtNum(n) {
                    if (n === 0 || n === '-') return n === 0 ? '0' : '-';
                    return 'Rs.' + Math.round(Number(n) || 0);
                }

                // Image jaisa: Credit row (debit empty); Debit row (credit, worker, commission empty). Total running.
                let html = '';
                rows.forEach(function(r) {
                    const creditStr = (r.credit || 0) > 0 ? fmtNum(r.credit) : '-';
                    const gTotalStr = (r.gTotal != null && r.gTotal !== '') ? fmtNum(r.gTotal) : '-';
                    const totalStr = r.total != null ? fmtNum(r.total) : '-';
                    const expenseStr = (r.debit || 0) > 0 ? fmtNum(r.debit) : '-';
                    const commStr = (r.commission !== '-' && r.commission != null && (r.commission || 0) > 0) ? fmtNum(r.commission) : '-';
                    const rowClass = r.isOpening ? 'bg-slate-100 font-semibold' : 'hover:bg-slate-50';
                    html += '<tr class="' + rowClass + '">' +
                        '<td class="px-4 py-3 text-sm text-slate-700">' + (r.dateTime || '-') + '</td>' +
                        '<td class="px-4 py-3 text-sm font-semibold text-slate-900">' + (r.vehicle || '-') + '</td>' +
                        '<td class="px-4 py-3 text-sm text-right ' + ((r.debit || 0) > 0 ? 'font-bold text-amber-700' : 'text-slate-500') + '">' + expenseStr + '</td>' +
                        '<td class="px-4 py-3 text-sm text-right ' + ((r.credit || 0) > 0 ? 'font-bold text-blue-600' : 'text-slate-500') + '">' + creditStr + '</td>' +
                        '<td class="px-4 py-3 text-sm text-right font-bold text-slate-900">' + totalStr + '</td>' +
                        '<td class="px-4 py-3 text-sm text-slate-700">' + (r.worker || '-') + '</td>' +
                        '<td class="col-bank px-4 py-3 text-sm text-slate-700">' + (r.bankName || '-') + '</td>' +
                        '<td class="px-4 py-3 text-sm text-right ' + ((r.commission !== '-' && (r.commission || 0) > 0) ? 'font-bold text-emerald-600' : 'text-slate-500') + '">' + commStr + '</td>' +
                        '<td class="px-4 py-3 text-sm text-right font-bold text-indigo-600">' + gTotalStr + '</td>' +
                        '</tr>';
                });
                tableBody.innerHTML = html;
                toggleBankColumn();
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
                    worker: filterWorker.value || '',
                    payment: getPaymentTab()
                });
                fetch(url)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) renderReport(data);
                        else { lastReportRows = []; lastReportTotals = {}; renderReport({ rows: [], totals: {}, customers: [], workers: [] }); }
                    })
                    .catch(function() { lastReportRows = []; lastReportTotals = {}; renderReport({ rows: [], totals: {}, customers: [], workers: [] }); });
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
                    worker: filterWorker.value || '',
                    payment: getPaymentTab()
                });
                window.location.href = url;
            });

            tabCash.addEventListener('click', function() { setPaymentTab('cash'); loadReport(); });
            tabBank.addEventListener('click', function() { setPaymentTab('bank'); loadReport(); });

            reportDate.addEventListener('change', loadReport);

            function openCashOnHandModal() {
                var isBank = (getPaymentTab() === 'bank');
                var titleMain = isBank ? 'Bank Total – Breakdown' : 'Cash on Hand – Breakdown';
                var titleSub = isBank ? 'Bank ledger' : 'Har cheez kahan say kitna aaya';
                document.querySelector('#cashOnHandModal h3').innerHTML = titleMain + '<br><span class="text-sm font-normal opacity-90">' + titleSub + '</span>';
                if (lastReportRows.length === 0) {
                    cashOnHandModalBody.innerHTML = '<p class="text-slate-500">Load a report first, then click ' + (isBank ? 'Bank Total' : 'Cash on Hand') + ' for details.</p>';
                } else {
                    var rows = lastReportRows;
                    var totals = lastReportTotals;
                    var html = '<div class="overflow-x-auto"><table class="w-full text-left border-collapse"><thead><tr class="bg-indigo-50 border-b-2 border-indigo-200"><th class="px-3 py-2.5 text-xs font-black uppercase text-indigo-900">#</th><th class="px-3 py-2.5 text-xs font-black uppercase text-indigo-900">Kahan say (Source)</th><th class="px-3 py-2.5 text-xs font-black uppercase text-indigo-900 text-right">Type</th><th class="px-3 py-2.5 text-xs font-black uppercase text-indigo-900 text-right">Kitna (Amount)</th><th class="px-3 py-2.5 text-xs font-black uppercase text-indigo-900 text-right">Running Total</th></tr></thead><tbody>';
                    var sr = 0;
                    for (var i = 0; i < rows.length; i++) {
                        var r = rows[i];
                        if (r.isOpening) {
                            sr++;
                            html += '<tr class="border-b border-slate-200 bg-slate-50"><td class="px-3 py-2.5 text-slate-600 font-bold">' + sr + '</td><td class="px-3 py-2.5 font-semibold text-slate-800">Opening</td><td class="px-3 py-2.5 text-right text-slate-500">—</td><td class="px-3 py-2.5 text-right font-mono">Rs.0</td><td class="px-3 py-2.5 text-right font-bold font-mono">Rs.0</td></tr>';
                        } else if ((r.credit || 0) > 0) {
                            sr++;
                            var amt = Math.round(Number(r.credit) || 0);
                            var run = Math.round(r.total != null ? (Number(r.total) || 0) : 0);
                            var src = (r.vehicle || '-') + ' • ' + (r.dateTime || '-') + (r.worker ? ' • ' + r.worker : '');
                            html += '<tr class="border-b border-slate-200 hover:bg-green-50/50"><td class="px-3 py-2.5 text-slate-600 font-bold">' + sr + '</td><td class="px-3 py-2.5 text-slate-800">' + src + '</td><td class="px-3 py-2.5 text-right font-semibold text-green-600">+ Credit</td><td class="px-3 py-2.5 text-right font-mono font-bold text-green-700">+ Rs.' + amt + '</td><td class="px-3 py-2.5 text-right font-bold font-mono">Rs.' + run + '</td></tr>';
                        } else if ((r.debit || 0) > 0) {
                            sr++;
                            var amt = Math.round(Number(r.debit) || 0);
                            var run = Math.round(r.total != null ? (Number(r.total) || 0) : 0);
                            var src = (r.vehicle || '-') + ' • ' + (r.dateTime || '-') + ' • Refreshment';
                            html += '<tr class="border-b border-slate-200 hover:bg-amber-50/50"><td class="px-3 py-2.5 text-slate-600 font-bold">' + sr + '</td><td class="px-3 py-2.5 text-slate-800">' + src + '</td><td class="px-3 py-2.5 text-right font-semibold text-amber-600">− Expense</td><td class="px-3 py-2.5 text-right font-mono font-bold text-amber-700">− Rs.' + amt + '</td><td class="px-3 py-2.5 text-right font-bold font-mono">Rs.' + run + '</td></tr>';
                        }
                    }
                    var coh = Math.round(totals.cashOnHand != null ? (Number(totals.cashOnHand) || 0) : 0);
                    var footLabel = isBank ? 'Bank Total' : 'Cash on Hand (Total)';
                    html += '</tbody><tfoot><tr class="bg-indigo-100 border-t-2 border-indigo-300"><td class="px-3 py-3 font-black text-indigo-900" colspan="3">' + footLabel + '</td><td class="px-3 py-3 text-right font-black text-indigo-900 font-mono" colspan="2">Rs.' + coh + '</td></tr></tfoot></table></div>';
                    cashOnHandModalBody.innerHTML = html;
                }
                cashOnHandModal.style.display = 'flex';
            }

            function closeCashOnHandModal() {
                cashOnHandModal.style.display = 'none';
            }

            cashOnHandCard.addEventListener('click', openCashOnHandModal);
            cashOnHandModalClose.addEventListener('click', closeCashOnHandModal);
            cashOnHandModal.addEventListener('click', function(e) {
                if (e.target === cashOnHandModal) closeCashOnHandModal();
            });

            if (reportDate.value) loadReport();
        })();
    </script>
</body>
</html>
