<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Report - Elite Car Wash</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body class="bg-slate-50 min-h-screen">
    <div id="root" class="min-h-screen">
        <!-- Header -->
        <header class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white p-6 shadow-2xl">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-black uppercase tracking-tighter mb-1">Daily Report</h1>
                        <p class="text-sm opacity-90">{{ $branchName }} • {{ $userName }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('car.wash') }}" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-black uppercase transition-colors backdrop-blur-sm">← Car Wash</a>
                        <a href="{{ route('car.wash.completed-jobs') }}" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-black uppercase transition-colors backdrop-blur-sm">Completed Jobs</a>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto p-6 min-h-[605px]" style="min-height: 605px;">
            <!-- Date, Filters & Actions -->
            <div class="bg-white rounded-2xl shadow-xl border-2 border-slate-200 p-6 mb-6 min-h-[227px]" style="min-height: 227px;">
                <div class="flex flex-wrap items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-black text-slate-700 uppercase mb-2 text-center">Select Range</label>
                        <div class="flex items-center gap-1.5">
                            <input type="date" id="reportDateFrom" value="{{ $selectedDate ?? now()->format('Y-m-d') }}"
                                class="flex-1 px-2.5 py-2 border-2 border-slate-300 rounded-lg text-slate-900 font-bold focus:border-indigo-500 focus:outline-none text-xs" />
                            <span class="text-[10px] font-bold text-slate-600 whitespace-nowrap">To</span>
                            <input type="date" id="reportDateTo" value="{{ $selectedDate ?? now()->format('Y-m-d') }}"
                                class="flex-1 px-2.5 py-2 border-2 border-slate-300 rounded-lg text-slate-900 font-bold focus:border-indigo-500 focus:outline-none text-xs" />
                        </div>
                    </div>
                    <div class="flex items-end gap-4 flex-1">
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-slate-700 uppercase mb-2" style="font-family: ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';">Customer (Vehicle)</label>
                            <select id="filterCustomer" class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl text-slate-900 font-bold focus:border-indigo-500 focus:outline-none">
                                <option value="">All</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-black text-slate-700 uppercase mb-2 text-center" style="font-family: 'Segoe UI Emoji';">Worker</label>
                            <select id="filterWorker" class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl text-slate-900 font-bold focus:border-indigo-500 focus:outline-none">
                                <option value="">All</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-black text-slate-700 uppercase mb-2 text-center">User</label>
                            <select id="filterUser" class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl text-slate-900 font-bold focus:border-indigo-500 focus:outline-none">
                                <option value="">All</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="button" id="btnDownloadPng" disabled
                            class="px-5 py-2.5 bg-gradient-to-br from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg text-xs sm:text-sm font-bold uppercase transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shadow-md hover:shadow-lg">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="hidden sm:inline">Download PNG</span>
                            <span class="sm:hidden">PNG</span>
                        </button>
                        <button type="button" id="btnDownloadPdf" disabled
                            class="px-5 py-2.5 bg-gradient-to-br from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white rounded-lg text-xs sm:text-sm font-bold uppercase transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shadow-md hover:shadow-lg">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="hidden sm:inline">Download PDF</span>
                            <span class="sm:hidden">PDF</span>
                        </button>
                        <button type="button" id="btnSendWhatsApp" disabled
                            class="px-5 py-2.5 bg-gradient-to-br from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg text-xs sm:text-sm font-bold uppercase transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 shadow-md hover:shadow-lg">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                            <span class="hidden sm:inline">Send WhatsApp</span>
                            <span class="sm:hidden">WA</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary (like image: total vehicles, total debit, total credit, cash on hand, bank balance, total workers, total commission) - visible from start to avoid layout shift on refresh -->
            <div id="totalsSection" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2 sm:gap-3 mb-6">
                <div class="bg-gradient-to-br from-white to-slate-50 rounded-lg border border-slate-300 p-2.5 sm:p-3 shadow-sm hover:shadow transition-shadow">
                    <p class="text-[9px] sm:text-[10px] font-bold text-slate-600 uppercase mb-1">Total Vehicles</p>
                    <p id="totVehicles" class="text-base sm:text-lg font-black text-slate-900">0</p>
                </div>
                <div class="bg-gradient-to-br from-white to-slate-50 rounded-lg border border-slate-300 p-2.5 sm:p-3 shadow-sm hover:shadow transition-shadow">
                    <p class="text-[9px] sm:text-[10px] font-bold text-slate-600 uppercase mb-1">Total Workers</p>
                    <p id="totWorkers" class="text-base sm:text-lg font-black text-slate-900">0</p>
                </div>
                <div id="cashOnHandCard" class="bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-lg border-2 border-indigo-400 p-2.5 sm:p-3 shadow-sm cursor-pointer hover:bg-indigo-200 hover:border-indigo-500 hover:shadow transition-all" title="Click for breakdown: kahan say kitna aya">
                    <p class="text-[9px] sm:text-[10px] font-bold text-indigo-900 uppercase mb-1">Cash on Hand</p>
                    <p id="totCashOnHand" class="text-base sm:text-lg font-black text-indigo-900">Rs.0</p>
                </div>
                <div id="bankBalanceCard" class="bg-gradient-to-br from-purple-100 to-purple-200 rounded-lg border-2 border-purple-400 p-2.5 sm:p-3 shadow-sm cursor-pointer hover:bg-purple-200 hover:border-purple-500 hover:shadow transition-all" title="Bank Account Balance">
                    <p class="text-[9px] sm:text-[10px] font-bold text-purple-900 uppercase mb-1">Bank Balance</p>
                    <p id="totBankBalance" class="text-base sm:text-lg font-black text-purple-900">-</p>
                </div>
                <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg border border-amber-300 p-2.5 sm:p-3 shadow-sm hover:shadow transition-shadow">
                    <p class="text-[9px] sm:text-[10px] font-bold text-amber-800 uppercase mb-1">Job Expense</p>
                    <p id="totDebit" class="text-base sm:text-lg font-black text-amber-800">Rs.0</p>
                </div>
                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg border border-emerald-300 p-2.5 sm:p-3 shadow-sm hover:shadow transition-shadow">
                    <p class="text-[9px] sm:text-[10px] font-bold text-emerald-800 uppercase mb-1">Commission</p>
                    <p id="totCommission" class="text-base sm:text-lg font-black text-emerald-800">Rs.0</p>
                </div>
            </div>

            <!-- Ledger Table: Date & Time | Vehicle | Debit | Credit | Total | Worker | Commission -->
            <div id="tableSection" class="bg-white rounded-2xl shadow-xl border-2 border-slate-200 overflow-hidden hidden min-h-[120px]" style="min-height: 120px;">
                <div class="overflow-x-auto min-h-[100px]">
                    <table class="w-full min-w-[1200px] sm:min-w-[1400px]">
                        <thead class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white">
                            <tr>
                                <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-left text-[9px] sm:text-[10px] md:text-xs font-black uppercase whitespace-nowrap">Date & Time</th>
                                <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-left text-[9px] sm:text-[10px] md:text-xs font-black uppercase whitespace-nowrap">Vehicle</th>
                                <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-xs font-black uppercase whitespace-nowrap bg-indigo-500/50">Cash Receipt</th>
                                <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-xs font-black uppercase whitespace-nowrap">CASH TRANSFER</th>
                                <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-xs font-black uppercase whitespace-nowrap">SHOP EXPENSE</th>
                                <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-xs font-black uppercase whitespace-nowrap">JOB EXPENSE</th>
                                <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-xs font-black uppercase whitespace-nowrap bg-indigo-500/50">Cash Total</th>
                                <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-xs font-black uppercase whitespace-nowrap bg-purple-500/50">Bank Credit</th>
                                <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-xs font-black uppercase whitespace-nowrap bg-purple-500/50">Bank Total</th>
                            </tr>
                        </thead>
                        <tbody id="reportTableBody" class="divide-y divide-slate-200">
                        </tbody>
                        <tfoot>
                            <tr class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white font-black">
                                <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-[9px] sm:text-[10px] md:text-sm whitespace-nowrap font-bold">TOTAL</td>
                                <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-[9px] sm:text-[10px] md:text-sm whitespace-nowrap">-</td>
                                <td id="ftCashCredit" class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-sm whitespace-nowrap bg-indigo-500/30">Rs.0.00</td>
                                <td id="ftCashTransfer" class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-sm whitespace-nowrap">Rs.0.00</td>
                                <td id="ftShopExpense" class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-sm whitespace-nowrap">Rs.0.00</td>
                                <td id="ftExpenses" class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-sm whitespace-nowrap">Rs.0.00</td>
                                <td id="ftCashTotal" class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-sm whitespace-nowrap bg-indigo-500/30">Rs.0.00</td>
                                <td id="ftBankCredit" class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-sm whitespace-nowrap bg-purple-500/30">Rs.0.00</td>
                                <td id="ftBankTotal" class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-sm whitespace-nowrap bg-purple-500/30">Rs.0.00</td>
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

        <!-- PNG Preview Modal -->
        <div id="pngPreviewModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-2xl shadow-2xl border-2 border-slate-200 w-full max-w-6xl max-h-[95vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
                <div class="p-4 sm:p-5 bg-gradient-to-r from-blue-500 to-blue-600 text-white flex justify-between items-center flex-shrink-0">
                    <div>
                        <h3 class="text-lg sm:text-xl font-black uppercase">PNG Preview</h3>
                        <p class="text-xs sm:text-sm opacity-90 mt-1">Review before downloading</p>
                    </div>
                    <button type="button" id="pngPreviewModalClose" class="w-9 h-9 rounded-lg bg-white/20 hover:bg-white/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-4 sm:p-5 overflow-auto flex-1 bg-slate-50 flex items-center justify-center">
                    <img id="pngPreviewImage" src="" alt="PNG Preview" class="max-w-full max-h-full object-contain shadow-lg rounded-lg" style="display: none;">
                    <div id="pngPreviewLoading" class="text-center">
                        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
                        <p class="mt-4 text-slate-600 font-bold">Generating preview...</p>
                    </div>
                </div>
                <div class="p-4 sm:p-5 bg-slate-100 border-t-2 border-slate-200 flex justify-end gap-3 flex-shrink-0">
                    <button type="button" id="pngPreviewDownload" class="px-6 py-3 bg-gradient-to-br from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white rounded-xl text-sm font-black uppercase transition-all duration-200 flex items-center gap-2 shadow-md hover:shadow-lg" style="display: none;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download PNG
                    </button>
                    <button type="button" id="pngPreviewCancel" class="px-6 py-3 bg-slate-500 hover:bg-slate-600 text-white rounded-xl text-sm font-black uppercase transition-all duration-200">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Cash Transfer Modal -->
        <div id="cashTransferModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-2xl shadow-2xl border-2 border-yellow-200 w-full max-w-md max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
                <div class="p-4 sm:p-5 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white flex justify-between items-center flex-shrink-0">
                    <div>
                        <h3 class="text-lg sm:text-xl font-black uppercase">Cash Transfer</h3>
                        <p class="text-xs sm:text-sm opacity-90 mt-1">Transfer money to different accounts</p>
                    </div>
                    <button type="button" id="cashTransferModalClose" class="w-9 h-9 rounded-lg bg-white/20 hover:bg-white/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-4 sm:p-5 overflow-y-auto flex-1">
                    <!-- Available Cash -->
                    <div class="mb-5 bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 rounded-xl border-2 border-yellow-200">
                        <div class="text-xs sm:text-sm font-bold text-yellow-700 uppercase mb-1">Available Cash</div>
                        <div class="text-2xl sm:text-3xl font-black text-yellow-600 font-mono" id="modalCashBalance">Rs.0</div>
                    </div>
                    
                    <!-- Transfer Amount -->
                    <div class="mb-5">
                        <label class="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-2">Transfer Amount</label>
                        <input type="number" id="transferAmount" placeholder="Enter amount" 
                            class="w-full px-4 py-3 text-sm text-slate-900 border-2 border-slate-300 rounded-xl bg-white focus:border-yellow-500 focus:outline-none font-mono" 
                            min="0" step="0.01" max="" />
                    </div>
                    
                    <!-- Transfer To User -->
                    <div class="mb-5">
                        <label class="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-2">Transfer To User</label>
                        <select id="transferToUser" class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl text-slate-900 font-bold focus:border-yellow-500 focus:outline-none">
                            <option value="">Select User</option>
                        </select>
                        <p id="transferUserWarning" class="mt-1.5 text-sm font-bold text-amber-600 hidden" role="alert">Pehle user select kero</p>
                    </div>
                    
                    <!-- Transfer Note -->
                    <div class="mb-5">
                        <label class="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-2">Note (Optional)</label>
                        <textarea id="transferNote" rows="2" placeholder="Add a note about this transfer"
                            class="w-full px-4 py-3 text-sm text-slate-900 border-2 border-slate-300 rounded-xl bg-white focus:border-yellow-500 focus:outline-none resize-none"></textarea>
                    </div>
                    
                    <!-- Transfer Button -->
                    <button type="button" id="btnTransferCash" 
                        class="w-full px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl text-sm font-black uppercase transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Transfer Cash
                    </button>
                </div>
            </div>
        </div>

        <!-- Cash On Hand Breakdown Modal -->
        <div id="cashOnHandBreakdownModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-2xl shadow-2xl border-2 border-indigo-200 w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
                <div class="p-4 sm:p-5 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white flex justify-between items-center flex-shrink-0">
                    <div>
                        <h3 class="text-lg sm:text-xl font-black uppercase">Cash On Hand Breakdown</h3>
                        <p class="text-xs sm:text-sm opacity-90 mt-1" id="breakdownDateRange">Transaction Details</p>
                    </div>
                    <button type="button" id="cashOnHandBreakdownModalClose" class="w-9 h-9 rounded-lg bg-white/20 hover:bg-white/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-4 sm:p-5 overflow-y-auto flex-1">
                    <div id="breakdownContent" class="space-y-3">
                        <div class="text-center text-slate-500 py-8">
                            <p>Loading transaction breakdown...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank Balance Breakdown Modal -->
        <div id="bankBalanceBreakdownModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-2xl shadow-2xl border-2 border-purple-200 w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
                <div class="p-4 sm:p-5 bg-gradient-to-r from-purple-500 to-purple-600 text-white flex justify-between items-center flex-shrink-0">
                    <div>
                        <h3 class="text-lg sm:text-xl font-black uppercase">Bank Balance Breakdown</h3>
                        <p class="text-xs sm:text-sm opacity-90 mt-1" id="bankBreakdownDateRange">Transaction Details</p>
                    </div>
                    <button type="button" id="bankBalanceBreakdownModalClose" class="w-9 h-9 rounded-lg bg-white/20 hover:bg-white/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-4 sm:p-5 overflow-y-auto flex-1">
                    <div id="bankBreakdownContent" class="space-y-3">
                        <div class="text-center text-slate-500 py-8">
                            <p>Loading transaction breakdown...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank Transfer Modal -->
        <div id="bankTransferModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-2xl shadow-2xl border-2 border-purple-200 w-full max-w-md max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
                <div class="p-4 sm:p-5 bg-gradient-to-r from-purple-500 to-purple-600 text-white flex justify-between items-center flex-shrink-0">
                    <div>
                        <h3 class="text-lg sm:text-xl font-black uppercase">Bank Transfer</h3>
                        <p class="text-xs sm:text-sm opacity-90 mt-1">Transfer cash to bank account</p>
                    </div>
                    <button type="button" id="bankTransferModalClose" class="w-9 h-9 rounded-lg bg-white/20 hover:bg-white/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-4 sm:p-5 overflow-y-auto flex-1">
                    <!-- Bank Account Balance from Footer -->
                    <div class="mb-5 bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-xl border-2 border-purple-200">
                        <div class="text-xs sm:text-sm font-bold text-purple-700 uppercase mb-1">Bank Account Balance</div>
                        <div class="text-2xl sm:text-3xl font-black text-purple-600 font-mono" id="modalBankBalance">Rs.0</div>
                    </div>
                    
                    <!-- Transfer Amount -->
                    <div class="mb-5">
                        <label class="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-2">Transfer Amount</label>
                        <input type="number" id="bankTransferAmount" placeholder="Enter amount" 
                            class="w-full px-4 py-3 text-sm text-slate-900 border-2 border-slate-300 rounded-xl bg-white focus:border-purple-500 focus:outline-none font-mono" 
                            min="0" step="0.01" />
                    </div>
                    
                    <!-- Transfer To Bank Account (custom dropdown: each account in 3 lines) -->
                    <div class="mb-5 relative">
                        <label class="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-2">Transfer To Bank Account</label>
                        <input type="hidden" id="transferToBankAccount" value="" />
                        <button type="button" id="transferToBankAccountTrigger" class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl text-slate-900 font-bold focus:border-purple-500 focus:outline-none text-left bg-white flex items-center justify-between">
                            <span id="transferToBankAccountLabel" class="text-sm leading-relaxed">
                                <span id="triggerLine1" class="block font-bold text-slate-800">Select Bank Account</span>
                                <span id="triggerLine2" class="block text-slate-700"></span>
                                <span id="triggerLine3" class="block text-slate-600"></span>
                            </span>
                            <svg class="w-5 h-5 text-slate-500 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="transferToBankAccountDropdown" class="absolute left-0 right-0 top-full mt-1 bg-white border-2 border-slate-300 rounded-xl shadow-xl z-50 max-h-60 overflow-y-auto hidden">
                            <div id="transferToBankAccountList"></div>
                        </div>
                    </div>
                    
                    <!-- Transfer Note -->
                    <div class="mb-5">
                        <label class="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-2">Note (Optional)</label>
                        <textarea id="bankTransferNote" rows="2" placeholder="Add a note about this transfer"
                            class="w-full px-4 py-3 text-sm text-slate-900 border-2 border-slate-300 rounded-xl bg-white focus:border-purple-500 focus:outline-none resize-none"></textarea>
                    </div>
                    
                    <!-- Transfer Button -->
                    <button type="button" id="btnTransferBank" 
                        class="w-full px-6 py-3 bg-purple-500 hover:bg-purple-600 text-white rounded-xl text-sm font-black uppercase transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Transfer to Bank
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const reportDateFrom = document.getElementById('reportDateFrom');
            const reportDateTo = document.getElementById('reportDateTo');
            function getTodayLocal() {
                const d = new Date();
                return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            }
            if (reportDateFrom && reportDateTo) {
                reportDateFrom.value = getTodayLocal();
                reportDateTo.value = getTodayLocal();
            }
            const filterCustomer = document.getElementById('filterCustomer');
            const filterWorker = document.getElementById('filterWorker');
            const filterUser = document.getElementById('filterUser');
            const btnPng = document.getElementById('btnDownloadPng');
            const btnPdf = document.getElementById('btnDownloadPdf');
            const btnSendWhatsApp = document.getElementById('btnSendWhatsApp');
            const totalsSection = document.getElementById('totalsSection');
            const tableSection = document.getElementById('tableSection');
            const tableBody = document.getElementById('reportTableBody');
            const emptyState = document.getElementById('emptyState');
            const loadingState = document.getElementById('loadingState');
            const cashOnHandCard = document.getElementById('cashOnHandCard');
            const cashOnHandBreakdownModal = document.getElementById('cashOnHandBreakdownModal');
            const cashOnHandBreakdownModalClose = document.getElementById('cashOnHandBreakdownModalClose');
            const bankBalanceCard = document.getElementById('bankBalanceCard');
            const cashTransferModal = document.getElementById('cashTransferModal');
            const cashTransferModalClose = document.getElementById('cashTransferModalClose');
            const modalCashBalance = document.getElementById('modalCashBalance');
            const transferAmount = document.getElementById('transferAmount');
            const transferToUser = document.getElementById('transferToUser');
            const transferUserWarning = document.getElementById('transferUserWarning');
            const transferNote = document.getElementById('transferNote');
            const btnTransferCash = document.getElementById('btnTransferCash');
            
            const bankTransferModal = document.getElementById('bankTransferModal');
            const bankTransferModalClose = document.getElementById('bankTransferModalClose');
            const modalBankBalance = document.getElementById('modalBankBalance');
            const bankTransferAmount = document.getElementById('bankTransferAmount');
            const transferToBankAccount = document.getElementById('transferToBankAccount');
            const transferToBankAccountTrigger = document.getElementById('transferToBankAccountTrigger');
            const triggerLine1 = document.getElementById('triggerLine1');
            const triggerLine2 = document.getElementById('triggerLine2');
            const triggerLine3 = document.getElementById('triggerLine3');
            const transferToBankAccountDropdown = document.getElementById('transferToBankAccountDropdown');
            const transferToBankAccountList = document.getElementById('transferToBankAccountList');
            const bankTransferNote = document.getElementById('bankTransferNote');
            const btnTransferBank = document.getElementById('btnTransferBank');
            
            const pngPreviewModal = document.getElementById('pngPreviewModal');
            const pngPreviewModalClose = document.getElementById('pngPreviewModalClose');
            const pngPreviewImage = document.getElementById('pngPreviewImage');
            const pngPreviewLoading = document.getElementById('pngPreviewLoading');
            const pngPreviewDownload = document.getElementById('pngPreviewDownload');
            const pngPreviewCancel = document.getElementById('pngPreviewCancel');
            let currentPngBlob = null;
            let currentPngFilename = '';

            let lastReportRows = [];
            let lastReportTotals = {};
            let lastReportCashOnHand = 0;
            let lastReportBankBalance = 0;
            let bankBalance = 0;

            const routes = {
                data: '{{ route("car-wash.jobs.daily-report-data") }}',
                pdf: '{{ route("car-wash.jobs.daily-report-pdf") }}',
                bankAccounts: '{{ route("car-wash.bank-accounts.index") }}',
                bankAccountsForTransfer: '{{ route("car-wash.bank-accounts.for-transfer") }}',
                cashAccountBalance: '{{ route("car-wash.payments.cash-account-balance") }}',
                branchUsers: '{{ route("car-wash.payments.branch-users") }}',
                transferToUser: '{{ route("car-wash.payments.transfer-to-user") }}',
                deleteCashTransfer: '{{ route("car-wash.payments.delete-cash-transfer", ["id" => ":id"]) }}',
                cashTransfers: '{{ route("car-wash.cash-transfers.store") }}'
            };

            // Load bank account balance (already filtered by branch in API)
            let loggedInUserBankAccounts = [];
            function loadBankBalance() {
                fetch(routes.bankAccounts)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success && data.bankAccounts) {
                            // Store logged-in user's branch bank accounts
                            loggedInUserBankAccounts = data.bankAccounts;
                            bankBalance = data.bankAccounts.reduce(function(sum, acc) {
                                return sum + (parseFloat(acc.balance) || 0);
                            }, 0);
                            // Don't update totBankBalance (user requirement - show only in footer)
                            // Card will show '-' and footer will show final running total from table
                        } else {
                            bankBalance = 0;
                            loggedInUserBankAccounts = [];
                            // Don't update card
                        }
                    })
                    .catch(function() {
                        bankBalance = 0;
                        loggedInUserBankAccounts = [];
                        // Don't update card
                    });
            }

            // Cash on Hand card shows logged-in user's cash account balance
            // Also update footer Cash Total with actual cash account balance
            function loadCashAccountBalance() {
                if (!routes.cashAccountBalance) {
                    console.warn('Cash account balance route not available');
                    return;
                }
                fetch(routes.cashAccountBalance)
                    .then(function(res) {
                        return res.json();
                    })
                    .then(function(data) {
                        if (data.success && data.balance != null) {
                            const userBalance = parseFloat(data.balance) || 0;
                            const roundedBalance = Math.round(userBalance);
                            // Don't update Cash on Hand card (user requirement - show only in footer)
                            // Card will show '-' and footer will show final running total from table
                            lastReportCashOnHand = roundedBalance;
                        } else {
                            // Don't update card, just set lastReportCashOnHand for modal
                            lastReportCashOnHand = 0;
                        }
                    })
                    .catch(function(err) {
                        console.error('Error loading cash account balance:', err);
                        // Don't update card, just set lastReportCashOnHand for modal
                        lastReportCashOnHand = 0;
                    });
            }

            function showLoading() {
                loadingState.classList.remove('hidden');
                tableSection.classList.add('hidden');
                emptyState.classList.add('hidden');
                // Keep totalsSection visible so layout stays fixed on refresh
            }

            function renderReport(data) {
                loadingState.classList.add('hidden');

                // Populate filters (customers & workers) - keep current selection if still in list
                const custVal = filterCustomer.value;
                const workVal = filterWorker.value;
                const userVal = filterUser.value;
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
                // User filter: keep all Barki Express users (loaded once via loadBranchUsers); only preserve selection
                if (filterUser.options.length <= 1) {
                    filterUser.innerHTML = '<option value="">All</option>';
                    (data.users || []).forEach(function(u) {
                        const o = document.createElement('option');
                        o.value = u.value;
                        o.textContent = u.label;
                        if (u.value === userVal) o.selected = true;
                        filterUser.appendChild(o);
                    });
                } else {
                    var opt = filterUser.querySelector('option[value="' + userVal + '"]');
                    filterUser.value = (opt ? userVal : '');
                }

                const rows = data.rows || [];
                const hasJobs = rows.some(function(r) { return !r.isOpening; });

                lastReportRows = rows;
                lastReportTotals = data.totals || {};

                if (rows.length === 0) {
                    lastReportRows = [];
                    lastReportTotals = {};
                    emptyState.classList.remove('hidden');
                    tableSection.classList.add('hidden');
                    document.getElementById('totCashOnHand').textContent = 'Rs.0';
                    document.getElementById('totBankBalance').textContent = '-';
                    document.getElementById('totCommission').textContent = 'Rs.0';
                    btnPng.disabled = true;
                    btnPdf.disabled = true;
                    btnSendWhatsApp.disabled = true;
                    return;
                }

                emptyState.classList.add('hidden');
                tableSection.classList.remove('hidden');
                btnPng.disabled = false;
                btnPdf.disabled = false;
                btnSendWhatsApp.disabled = false;

                const t = data.totals || {};
                const cashT = data.cashTotals || {};
                const bankT = data.bankTotals || {};
                
                document.getElementById('totVehicles').textContent = t.totalVehicles || 0;
                document.getElementById('totDebit').textContent = 'Rs.' + Math.round(t.totalDebit || 0);
                // Total Credit card removed - data still available in t.totalCredit if needed
                // Cash on Hand card will show actual cash account balance (loaded separately)
                document.getElementById('totWorkers').textContent = t.totalWorkers || 0;
                const commissionVal = Math.round(t.totalCommission || 0);
                document.getElementById('totCommission').textContent = 'Rs.' + commissionVal;
                
                // All totals in single footer row (with + for credits, - for debits)
                const jobExpenseVal = Math.round(t.totalDebit || 0);
                const cashTransferVal = Math.round(t.totalCashTransfer || 0);
                const shopExpenseVal = Math.round(t.totalShopExpense || 0);
                const cashReceiptVal = Math.round(
                    (cashT.totalCashReceipt || 0) ||
                    rows.filter(function(r) { return r.paymentType === 'cash' && (r.credit || 0) > 0; }).reduce(function(s, r) { return s + (parseFloat(r.credit) || 0); }, 0)
                );
                document.getElementById('ftExpenses').textContent = jobExpenseVal > 0 ? '-Rs.' + jobExpenseVal : 'Rs.0';
                document.getElementById('ftCashTransfer').textContent = cashTransferVal > 0 ? '-Rs.' + cashTransferVal : 'Rs.0';
                document.getElementById('ftShopExpense').textContent = shopExpenseVal > 0 ? '-Rs.' + shopExpenseVal : 'Rs.0';
                document.getElementById('ftCashCredit').textContent = cashReceiptVal > 0 ? '+Rs.' + cashReceiptVal : 'Rs.0';
                // Cash Total: Will be updated by loadCashAccountBalance() to show actual cash account balance
                // Set temporary value, will be replaced by actual balance from API
                document.getElementById('ftCashTotal').textContent = 'Rs.0';
                // Cash on Hand card shows logged-in user's cash account balance (loaded separately)
                // Footer Cash Total also shows actual cash account balance (not calculated total)
                // Note: loadCashAccountBalance() is called after table is rendered to ensure proper update
                const bankTotalVal = Math.round(bankT.cashOnHand || 0);
                lastReportBankBalance = bankTotalVal;
                const bankCreditVal = Math.round(bankT.totalCredit || 0);
                document.getElementById('ftBankCredit').textContent = bankCreditVal > 0 ? '+Rs.' + bankCreditVal : 'Rs.0';
                document.getElementById('ftBankTotal').textContent = 'Rs.' + bankTotalVal;
                // Don't update totBankBalance (user requirement - show only in footer)
                // Card will show '-' and footer will show final running total from table

                function fmtNum(n) {
                    if (n === 0 || n === '-') return n === 0 ? '0' : '-';
                    return 'Rs.' + Math.round(Number(n) || 0);
                }
                function fmtNumPlus(n) {
                    if (!n && n !== 0) return '-';
                    const val = Math.round(Number(n) || 0);
                    return val > 0 ? '+Rs.' + val : (val === 0 ? '0' : '-');
                }
                function fmtNumMinus(n) {
                    if (!n && n !== 0) return '-';
                    const val = Math.round(Number(n) || 0);
                    return val > 0 ? '-Rs.' + val : (val === 0 ? '0' : '-');
                }

                // Image jaisa: Credit row (debit empty); Debit row (credit, worker, commission empty). Total running.
                // Track running cash total as we process rows (exactly like image)
                let runningCashTotal = 0;
                let runningBankTotal = 0;
                let html = '';
                // Store final running total for footer (will be updated after rows are processed)
                let finalRunningCashTotal = 0;
                let finalRunningBankTotal = 0;
                rows.forEach(function(r) {
                    const isCash = r.paymentType === 'cash';
                    const isBank = r.paymentType === 'bank';
                    const isOpening = r.isOpening;
                    
                    const creditStr = (r.credit || 0) > 0 ? fmtNum(r.credit) : '-';
                    const totalStr = r.total != null ? fmtNum(r.total) : '-';
                    const expenseStr = (r.debit || 0) > 0 ? fmtNumMinus(r.debit) : '-';
                    const isShopExpense = r.isShopExpense === true;
                    const shopExpenseStr = isShopExpense && (r.shopExpense || 0) > 0 ? fmtNumMinus(r.shopExpense) : '-';
                    const isCashTransfer = r.isCashTransfer === true;
                    const cashTransferStr = isCashTransfer && (r.cashTransfer || 0) > 0 ? fmtNumMinus(r.cashTransfer) : '-';
                    const rowClass = isOpening ? 'bg-slate-100 font-semibold' : (isCashTransfer ? 'bg-blue-50 hover:bg-blue-100' : (isShopExpense ? 'bg-amber-50 hover:bg-amber-100' : 'hover:bg-slate-50'));
                    
                    // Cash columns with user name (+ for receipt = money in)
                    let cashCreditStr = (isCash && (r.credit || 0) > 0) ? fmtNumPlus(r.credit) : '-';
                    if (isCash && (r.credit || 0) > 0 && r.userName) {
                        cashCreditStr = '<div class="flex flex-col items-end">' +
                            '<span>' + fmtNumPlus(r.credit) + '</span>' +
                            '<span class="text-[8px] sm:text-[9px] text-slate-500">(' + r.userName + ')</span>' +
                            '</div>';
                    }
                    // Calculate running cash total for each row (exactly like image: step by step)
                    // Image logic:
                    // - Cash Received: Add cash receipt → show new total
                    // - Cash Transfer: Subtract cash transfer → show new total
                    // - Shop Expense: Subtract shop expense → show new total
                    // - Job Expense: Subtract job expense → show new total (for both cash and bank payments)
                    // Cash Total column always shows running balance after each transaction
                    let cashTotalStr = '-';
                    if (isOpening && (r.cashOpeningBalance != null || r.cashOpeningBalance === 0)) {
                        // Opening row: Set initial cash balance
                        runningCashTotal = parseFloat(r.cashOpeningBalance) || 0;
                        cashTotalStr = fmtNum(runningCashTotal);
                    } else {
                        // Process transactions step by step (like image)
                        let rowAffectsCash = false;
                        
                        // Step 1: Add cash receipt if present (cash payments only)
                        if (isCash && (r.credit || 0) > 0) {
                            const cashReceipt = parseFloat(r.credit) || 0;
                            runningCashTotal = runningCashTotal + cashReceipt;
                            rowAffectsCash = true;
                        }
                        
                        // Step 2: Subtract cash transfer if present
                        if (isCashTransfer && (r.cashTransfer || 0) > 0) {
                            const cashTransfer = parseFloat(r.cashTransfer) || 0;
                            runningCashTotal = runningCashTotal - cashTransfer;
                            rowAffectsCash = true;
                        }
                        
                        // Step 3: Subtract shop expense if present
                        if (isShopExpense && (r.shopExpense || 0) > 0) {
                            const shopExpense = parseFloat(r.shopExpense) || 0;
                            runningCashTotal = runningCashTotal - shopExpense;
                            rowAffectsCash = true;
                        }
                        
                        // Step 4: Subtract job expense if present (for both cash and bank payments)
                        // Job expenses are always paid in cash, so they reduce cash total
                        if ((r.debit || 0) > 0) {
                            const jobExpense = parseFloat(r.debit) || 0;
                            runningCashTotal = runningCashTotal - jobExpense;
                            rowAffectsCash = true;
                        }
                        
                        // Show cash total if this row affects cash balance
                        if (rowAffectsCash) {
                            cashTotalStr = fmtNum(runningCashTotal);
                        } else {
                            // Bank payment without any expenses - no change to cash total
                            cashTotalStr = '-';
                        }
                    }
                    
                    // Bank columns: + for credit (money in), calculate running bank total in merged order
                    let bankCreditStr = (isBank && (r.credit || 0) > 0) ? fmtNumPlus(r.credit) : '-';
                    // Check if this is a bank transfer - check for transferId or isBankTransfer flag
                    const isBankTransfer = (r.isBankTransfer === true || r.isBankTransfer === 1 || r.isBankTransfer === '1') ||
                                           (r.vehicle && r.vehicle.toString().toLowerCase().includes('bank transfer')) ||
                                           (isBank && r.transferId);
                    
                    // Debug: Check if this row should have delete button
                    if (isBank && (r.credit || 0) > 0) {
                        const shouldShowDelete = isBank && r.transferId && (r.credit || 0) > 0;
                        console.log('Bank Credit Row:', {
                            vehicle: r.vehicle,
                            isBankTransfer: r.isBankTransfer,
                            transferId: r.transferId,
                            credit: r.credit,
                            hasTransferId: !!r.transferId,
                            isBankTransferFlag: isBankTransfer,
                            isBank: isBank,
                            shouldShowDelete: shouldShowDelete,
                            conditionCheck: {
                                'isBank': isBank,
                                'hasTransferId': !!r.transferId,
                                'hasCredit': (r.credit || 0) > 0
                            }
                        });
                        if (!r.transferId) {
                            console.warn('⚠️ Bank credit row missing transferId:', r);
                        }
                    }
                    
                    // Format bank credit with delete button for bank transfers
                    // Show delete button if: is bank payment AND has transferId AND has credit
                    // This will show for bank transfers (which have transferId)
                    if (isBank && r.transferId && (r.credit || 0) > 0) {
                        const deleteBtn = '<button onclick="deleteBankTransfer(' + r.transferId + ')" ' +
                            'class="ml-1 px-1.5 py-0.5 text-[8px] sm:text-[9px] bg-red-500 hover:bg-red-600 text-white rounded transition-colors" ' +
                            'title="Delete Bank Transfer">🗑️</button>';
                        bankCreditStr = '<div class="flex flex-col items-end">' +
                            '<div class="flex items-center gap-1">' +
                            '<span>' + fmtNumPlus(r.credit) + '</span>' +
                            deleteBtn +
                            '</div>';
                        if (r.bankNameOnly) {
                            bankCreditStr += '<span class="text-[8px] sm:text-[9px] text-slate-600">' + r.bankNameOnly + '</span>';
                        }
                        if (r.bankAccountTitle) {
                            bankCreditStr += '<span class="text-[8px] sm:text-[9px] text-slate-500">' + r.bankAccountTitle + '</span>';
                        }
                        if (r.bankAccountNumber) {
                            bankCreditStr += '<span class="text-[8px] sm:text-[9px] text-slate-500">(' + r.bankAccountNumber + ')</span>';
                        }
                        if (r.userName) {
                            bankCreditStr += '<span class="text-[8px] sm:text-[9px] text-slate-400 italic">(' + r.userName + ')</span>';
                        }
                        bankCreditStr += '</div>';
                    } else if (isBank && (r.credit || 0) > 0 && (r.bankNameOnly || r.bankAccountTitle || r.bankAccountNumber)) {
                        // Add edit and delete buttons for bank payment jobs
                        const editBtn = r.jobId ? 
                            '<button onclick="editBankJob(' + r.jobId + ', ' + r.credit + ')" ' +
                            'class="ml-1 px-1.5 py-0.5 text-[8px] sm:text-[9px] bg-blue-500 hover:bg-blue-600 text-white rounded transition-colors" ' +
                            'title="Edit Bank Payment">✏️</button>' : '';
                        const deleteBtn = r.jobId ? 
                            '<button onclick="deleteBankJob(' + r.jobId + ')" ' +
                            'class="ml-1 px-1.5 py-0.5 text-[8px] sm:text-[9px] bg-red-500 hover:bg-red-600 text-white rounded transition-colors" ' +
                            'title="Delete Bank Payment">🗑️</button>' : '';
                        bankCreditStr = '<div class="flex flex-col items-end">' +
                            '<div class="flex items-center gap-1">' +
                            '<span>' + fmtNumPlus(r.credit) + '</span>' +
                            (editBtn || deleteBtn ? editBtn + deleteBtn : '') +
                            '</div>';
                        if (r.bankNameOnly) {
                            bankCreditStr += '<span class="text-[8px] sm:text-[9px] text-slate-600">' + r.bankNameOnly + '</span>';
                        }
                        if (r.bankAccountTitle) {
                            bankCreditStr += '<span class="text-[8px] sm:text-[9px] text-slate-500">' + r.bankAccountTitle + '</span>';
                        }
                        if (r.bankAccountNumber) {
                            bankCreditStr += '<span class="text-[8px] sm:text-[9px] text-slate-500">(' + r.bankAccountNumber + ')</span>';
                        }
                        if (r.userName) {
                            bankCreditStr += '<span class="text-[8px] sm:text-[9px] text-slate-400 italic">(' + r.userName + ')</span>';
                        }
                        bankCreditStr += '</div>';
                    } else if (isBank && (r.credit || 0) > 0 && r.userName) {
                        // Add edit and delete buttons for bank payment jobs without bank details
                        const editBtn = r.jobId ? 
                            '<button onclick="editBankJob(' + r.jobId + ', ' + r.credit + ')" ' +
                            'class="ml-1 px-1.5 py-0.5 text-[8px] sm:text-[9px] bg-blue-500 hover:bg-blue-600 text-white rounded transition-colors" ' +
                            'title="Edit Bank Payment">✏️</button>' : '';
                        const deleteBtn = r.jobId ? 
                            '<button onclick="deleteBankJob(' + r.jobId + ')" ' +
                            'class="ml-1 px-1.5 py-0.5 text-[8px] sm:text-[9px] bg-red-500 hover:bg-red-600 text-white rounded transition-colors" ' +
                            'title="Delete Bank Payment">🗑️</button>' : '';
                        bankCreditStr = '<div class="flex flex-col items-end">' +
                            '<div class="flex items-center gap-1">' +
                            '<span>' + fmtNumPlus(r.credit) + '</span>' +
                            (editBtn || deleteBtn ? editBtn + deleteBtn : '') +
                            '</div>' +
                            '<span class="text-[8px] sm:text-[9px] text-slate-500">(' + r.userName + ')</span>' +
                            '</div>';
                    } else if (isBank && (r.credit || 0) > 0 && r.jobId) {
                        // Add edit and delete buttons for bank payment jobs without user name or bank details
                        const editBtn = '<button onclick="editBankJob(' + r.jobId + ', ' + r.credit + ')" ' +
                            'class="ml-1 px-1.5 py-0.5 text-[8px] sm:text-[9px] bg-blue-500 hover:bg-blue-600 text-white rounded transition-colors" ' +
                            'title="Edit Bank Payment">✏️</button>';
                        const deleteBtn = '<button onclick="deleteBankJob(' + r.jobId + ')" ' +
                            'class="ml-1 px-1.5 py-0.5 text-[8px] sm:text-[9px] bg-red-500 hover:bg-red-600 text-white rounded transition-colors" ' +
                            'title="Delete Bank Payment">🗑️</button>';
                        bankCreditStr = '<div class="flex flex-col items-end">' +
                            '<div class="flex items-center gap-1">' +
                            '<span>' + fmtNumPlus(r.credit) + '</span>' +
                            editBtn + deleteBtn +
                            '</div>' +
                            '</div>';
                    }
                    // Bank total: calculate running total in merged/sorted order (like cash)
                    let bankTotalStr = '-';
                    if (r.isOpening && (r.bankOpeningBalance != null || r.bankOpeningBalance === 0)) {
                        runningBankTotal = parseFloat(r.bankOpeningBalance) || 0;
                        bankTotalStr = fmtNum(runningBankTotal);
                    } else if (isBank && (r.credit || 0) > 0) {
                        runningBankTotal = runningBankTotal + (parseFloat(r.credit) || 0);
                        bankTotalStr = fmtNum(runningBankTotal);
                    }
                    
                    // Format date & time: Date first, then time on same line or below
                    // Helper: show "Today" if date matches today
                    const formatDatePart = function(datePart) {
                        if (!datePart || datePart === '-') return datePart;
                        const today = new Date();
                        const todayStr = String(today.getDate()).padStart(2, '0') + '/' + String(today.getMonth() + 1).padStart(2, '0') + '/' + String(today.getFullYear()).slice(-2);
                        return (datePart === todayStr) ? 'Today' : datePart;
                    };
                    let dateTimeStr = '-';
                    if (r.isOpening) {
                        // Extract date and time from dateTime field (format: "d/m/y Time h:i A")
                        const datePart = r.date || (r.dateTime ? r.dateTime.split(' Time ')[0] : '-');
                        let timePart = '12:00AM'; // Default fallback
                        if (r.dateTime && r.dateTime.includes(' Time ')) {
                            timePart = r.dateTime.split(' Time ')[1] || '12:00AM';
                        }
                        dateTimeStr = '<span class="font-bold">' + formatDatePart(datePart) + '</span> <span class="text-[8px] sm:text-[9px]">Time ' + timePart + '</span>';
                    } else if (r.startTime && r.endTime && r.totalTime && r.startTime !== '-' && r.endTime !== '-' && r.totalTime !== '-') {
                        const datePart = r.date || (r.dateTime ? r.dateTime.split(' time ')[0] : '-');
                        dateTimeStr = '<span class="font-bold">' + formatDatePart(datePart) + '</span><br>' + 
                                     '<span class="text-[8px] sm:text-[9px]">' + r.startTime + ' - ' + r.endTime + '</span><br>' +
                                     '<span class="text-[8px] sm:text-[9px] font-semibold inline-flex items-center gap-0.5"><svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' + r.totalTime + '</span>';
                        // Add worker name below total time in parentheses
                        if (r.worker && r.worker !== '-') {
                            dateTimeStr += '<br><span class="text-[8px] sm:text-[9px] text-slate-600">(' + r.worker + ')</span>';
                        }
                    } else {
                        const datePart = r.date || (r.dateTime ? r.dateTime.split(' time ')[0] : '-');
                        const timePart = r.dateTime && r.dateTime.includes(' time ') ? r.dateTime.split(' time ')[1] : '';
                        dateTimeStr = '<span class="font-bold">' + formatDatePart(datePart) + '</span>' + (timePart ? ' <span class="text-[8px] sm:text-[9px]">Time ' + timePart + '</span>' : '');
                        // Add worker name if available in parentheses
                        if (r.worker && r.worker !== '-') {
                            dateTimeStr += '<br><span class="text-[8px] sm:text-[9px] text-slate-600">(' + r.worker + ')</span>';
                        }
                    }
                    
                    // Format vehicle with customer name, mobile, and user name
                    let vehicleStr = (r.vehicle || '-');
                    if (isCashTransfer) {
                        // Cash transfer row: show transfer info and user name
                        vehicleStr = '<div class="flex flex-col">' +
                            '<span class="font-semibold text-blue-700">' + (r.vehicle || '-') + '</span>';
                        if (r.userName) {
                            vehicleStr += '<span class="text-[8px] sm:text-[9px] text-slate-500">(From: ' + r.userName + ')</span>';
                        }
                        if (r.notes) {
                            vehicleStr += '<span class="text-[8px] sm:text-[9px] text-slate-400 italic">' + r.notes + '</span>';
                        }
                        vehicleStr += '</div>';
                    } else if (isShopExpense) {
                        // Shop expense row: show category and user name
                        vehicleStr = '<div class="flex flex-col">' +
                            '<span class="font-semibold text-red-700">' + (r.vehicle || '-') + '</span>';
                        if (r.userName) {
                            vehicleStr += '<span class="text-[8px] sm:text-[9px] text-slate-500">(' + r.userName + ')</span>';
                        }
                        if (r.notes) {
                            vehicleStr += '<span class="text-[8px] sm:text-[9px] text-slate-400 italic">' + r.notes + '</span>';
                        }
                        vehicleStr += '</div>';
                    } else if (!r.isOpening && (r.customerName || r.mobile || r.userName)) {
                        vehicleStr = '<div class="flex flex-col">' +
                            '<span class="font-semibold">' + (r.vehicle || '-') + '</span>';
                        if (r.customerName) {
                            vehicleStr += '<span class="text-[8px] sm:text-[9px] text-slate-600">' + r.customerName + '</span>';
                        }
                        if (r.mobile) {
                            vehicleStr += '<span class="text-[8px] sm:text-[9px] text-slate-500">' + r.mobile;
                            // Add user name in parentheses below mobile
                            if (r.userName) {
                                vehicleStr += '<br><span class="text-[8px] sm:text-[9px] text-slate-400 italic">(' + r.userName + ')</span>';
                            }
                            vehicleStr += '</span>';
                        } else if (r.userName) {
                            // If no mobile but has user name
                            vehicleStr += '<span class="text-[8px] sm:text-[9px] text-slate-400 italic">(' + r.userName + ')</span>';
                        }
                        vehicleStr += '</div>';
                    }
                    
                    // Format cash transfer with user name and delete button
                    let cashTransferDisplayStr = cashTransferStr;
                    if (isCashTransfer && (r.cashTransfer || 0) > 0 && r.toUserName) {
                        const deleteBtn = r.transferId ? 
                            '<button onclick="deleteCashTransfer(' + r.transferId + ')" ' +
                            'class="ml-1 px-1.5 py-0.5 text-[8px] sm:text-[9px] bg-red-500 hover:bg-red-600 text-white rounded transition-colors" ' +
                            'title="Delete Cash Transfer">🗑️</button>' : '';
                        cashTransferDisplayStr = '<div class="flex flex-col items-end">' +
                            '<div class="flex items-center gap-1">' +
                            '<span>' + fmtNumMinus(r.cashTransfer) + '</span>' +
                            deleteBtn +
                            '</div>' +
                            '<span class="text-[8px] sm:text-[9px] text-slate-500">(To: ' + r.toUserName + ')</span>' +
                            '</div>';
                    }
                    
                    html += '<tr class="' + rowClass + '">' +
                        '<td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-[9px] sm:text-[10px] md:text-sm text-slate-700 whitespace-normal leading-tight">' + dateTimeStr + '</td>' +
                        '<td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-[9px] sm:text-[10px] md:text-sm text-slate-900 whitespace-normal leading-tight">' + vehicleStr + '</td>' +
                        '<td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-[9px] sm:text-[10px] md:text-sm text-right ' + (isCash && (r.credit || 0) > 0 ? 'font-bold text-indigo-600' : 'text-slate-500') + ' whitespace-normal leading-tight">' + cashCreditStr + '</td>' +
                        '<td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-[9px] sm:text-[10px] md:text-sm text-right ' + (isCashTransfer && (r.cashTransfer || 0) > 0 ? 'font-bold text-blue-600' : 'text-slate-500') + ' whitespace-normal leading-tight">' + cashTransferDisplayStr + '</td>' +
                        '<td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-[9px] sm:text-[10px] md:text-sm text-right ' + (isShopExpense && (r.shopExpense || 0) > 0 ? 'font-bold text-red-600' : 'text-slate-500') + ' whitespace-nowrap">' + shopExpenseStr + '</td>' +
                        '<td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-[9px] sm:text-[10px] md:text-sm text-right ' + ((r.debit || 0) > 0 ? 'font-bold text-amber-700' : 'text-slate-500') + ' whitespace-nowrap">' + expenseStr + '</td>' +
                        '<td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-[9px] sm:text-[10px] md:text-sm text-right ' + (cashTotalStr !== '-' ? 'font-bold text-indigo-700' : 'text-slate-500') + ' whitespace-nowrap">' + cashTotalStr + '</td>' +
                        '<td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-[9px] sm:text-[10px] md:text-sm text-right ' + (isBank && (r.credit || 0) > 0 ? 'font-bold text-purple-600' : 'text-slate-500') + ' whitespace-normal leading-tight">' + bankCreditStr + '</td>' +
                        '<td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-[9px] sm:text-[10px] md:text-sm text-right ' + (bankTotalStr !== '-' ? 'font-bold text-purple-700' : 'text-slate-500') + ' whitespace-nowrap">' + bankTotalStr + '</td>' +
                        '</tr>';
                });
                // Store final running total after all rows are processed
                finalRunningCashTotal = runningCashTotal;
                finalRunningBankTotal = runningBankTotal;
                tableBody.innerHTML = html;
                
                // Update footer Cash Total with final running cash total from table
                const ftCashTotalEl = document.getElementById('ftCashTotal');
                const finalCashTotal = Math.round(finalRunningCashTotal);
                if (ftCashTotalEl) {
                    ftCashTotalEl.textContent = 'Rs.' + finalCashTotal;
                }
                
                // Update footer Bank Total with final running bank total from table
                const ftBankTotalEl = document.getElementById('ftBankTotal');
                const finalBankTotal = Math.round(finalRunningBankTotal);
                if (ftBankTotalEl) {
                    ftBankTotalEl.textContent = 'Rs.' + finalBankTotal;
                }
                
                // Update Bank Balance card with same balance as footer
                const totBankBalanceEl = document.getElementById('totBankBalance');
                if (totBankBalanceEl) {
                    totBankBalanceEl.textContent = 'Rs.' + finalBankTotal;
                }
                
                // Update header bank balance if it exists (for car-wash.blade.php header)
                const headerBankBalance = document.querySelector('[aria-label="Bank account balance"]');
                if (headerBankBalance) {
                    headerBankBalance.textContent = 'Rs.' + finalBankTotal;
                }
                
                // Store bank balance in localStorage for car-wash.blade.php header to use
                localStorage.setItem('reportBankBalance', finalBankTotal.toString());
                
                // Update Cash on Hand card with same balance as footer
                const totCashOnHandEl = document.getElementById('totCashOnHand');
                if (totCashOnHandEl) {
                    totCashOnHandEl.textContent = 'Rs.' + finalCashTotal;
                }
                
                // Also update lastReportCashOnHand for modal calculations
                lastReportCashOnHand = finalRunningCashTotal;
            }

            function buildUrl(base, params) {
                const p = new URLSearchParams();
                Object.keys(params).forEach(function(k) {
                    if (params[k] != null && params[k] !== '') p.set(k, params[k]);
                });
                const q = p.toString();
                return base + (q ? '?' + q : '');
            }

            var loadReportTimeout = null;
            function doLoadReport() {
                const dateFrom = reportDateFrom.value;
                const dateTo = reportDateTo.value;
                if (!dateFrom || !dateTo) return;
                showLoading();
                
                // Fetch both cash and bank data, then merge
                const baseParams = {
                    date_from: dateFrom,
                    date_to: dateTo,
                    customer: filterCustomer.value || '',
                    worker: filterWorker.value || '',
                    user: filterUser.value || ''
                };
                
                Promise.all([
                    fetch(buildUrl(routes.data, {...baseParams, payment: 'cash'})).then(r => r.json()),
                    fetch(buildUrl(routes.data, {...baseParams, payment: 'bank'})).then(r => r.json())
                ])
                    .then(function([cashData, bankData]) {
                        if (cashData.success || bankData.success) {
                            const cashOpening = (cashData.rows || []).find(r => r.isOpening);
                            const bankOpening = (bankData.rows || []).find(r => r.isOpening);
                            // Single opening row with both cash and bank opening balances (pichli date ka closing)
                            const openingRow = cashOpening || bankOpening;
                            const mergedOpening = openingRow ? {
                                ...openingRow,
                                cashOpeningBalance: cashOpening && (cashOpening.openingBalance != null || cashOpening.openingBalance === 0) ? cashOpening.openingBalance : null,
                                bankOpeningBalance: bankOpening && (bankOpening.openingBalance != null || bankOpening.openingBalance === 0) ? bankOpening.openingBalance : null
                            } : null;
                            
                            // Merge all non-opening rows from both, mark payment type
                            const cashRows = (cashData.rows || []).filter(r => !r.isOpening).map(r => ({...r, paymentType: 'cash'}));
                            const bankRows = (bankData.rows || []).filter(r => !r.isOpening).map(r => ({...r, paymentType: 'bank'}));
                            
                            // Debug: Check for bank transfers in bank rows
                            const bankTransferRows = bankRows.filter(r => r.isBankTransfer || r.transferId || (r.vehicle && r.vehicle.toString().toLowerCase().includes('bank transfer')));
                            if (bankTransferRows.length > 0) {
                                console.log('✅ Found bank transfer rows:', bankTransferRows);
                            } else {
                                console.log('⚠️ No bank transfer rows found in bank data. Total bank rows:', bankRows.length);
                            }
                            
                            // Sort all rows by date and time (opening row stays first)
                            const allRows = [...cashRows, ...bankRows];
                            allRows.sort(function(a, b) {
                                // Parse dateTime string to compare
                                const parseDateTime = function(dtStr) {
                                    if (!dtStr) return null;
                                    // Format: "d/m/y time h:i A" or "d/m/y Time h:i A"
                                    const match = dtStr.match(/(\d{2})\/(\d{2})\/(\d{2})\s+(?:time|Time)\s+(\d{1,2}):(\d{2})\s+(AM|PM)/i);
                                    if (!match) return null;
                                    const [, d, m, y, hour, minute, ampm] = match;
                                    let h = parseInt(hour);
                                    if (ampm.toUpperCase() === 'PM' && h !== 12) h += 12;
                                    if (ampm.toUpperCase() === 'AM' && h === 12) h = 0;
                                    const year = 2000 + parseInt(y);
                                    return new Date(year, parseInt(m) - 1, parseInt(d), h, parseInt(minute));
                                };
                                
                                const dtA = parseDateTime(a.dateTime);
                                const dtB = parseDateTime(b.dateTime);
                                
                                if (!dtA && !dtB) return 0;
                                if (!dtA) return 1;
                                if (!dtB) return -1;
                                
                                return dtA - dtB;
                            });
                            
                            const mergedRows = mergedOpening ? [mergedOpening, ...allRows] : allRows;
                            
                            // Merge totals
                            const cashTotals = cashData.totals || {};
                            const bankTotals = bankData.totals || {};
                            const mergedTotals = {
                                totalVehicles: Math.max(cashTotals.totalVehicles || 0, bankTotals.totalVehicles || 0),
                                totalDebit: (cashTotals.totalDebit || 0) + (bankTotals.totalDebit || 0),
                                totalCredit: (cashTotals.totalCredit || 0) + (bankTotals.totalCredit || 0),
                                cashOnHand: (cashTotals.cashOnHand || 0) + (bankTotals.cashOnHand || 0),
                                totalWorkers: Math.max(cashTotals.totalWorkers || 0, bankTotals.totalWorkers || 0),
                                totalCommission: (cashTotals.totalCommission || 0) + (bankTotals.totalCommission || 0),
                                pendingCommission: (cashTotals.pendingCommission || 0) + (bankTotals.pendingCommission || 0),
                                sumGtotal: ((cashTotals.cashOnHand || 0) + (bankTotals.cashOnHand || 0)) - ((cashTotals.totalCommission || 0) + (bankTotals.totalCommission || 0)),
                                totalShopExpense: (cashTotals.totalShopExpense || 0) + (bankTotals.totalShopExpense || 0),
                                totalCashTransfer: (cashTotals.totalCashTransfer || 0) + (bankTotals.totalCashTransfer || 0)
                            };
                            
                            // Merge customers, workers and users (unique)
                            const allCustomers = [...(cashData.customers || []), ...(bankData.customers || [])];
                            const uniqueCustomers = Array.from(new Map(allCustomers.map(c => [c.value, c])).values());
                            
                            const allWorkers = [...(cashData.workers || []), ...(bankData.workers || [])];
                            const uniqueWorkers = Array.from(new Map(allWorkers.map(w => [w.value, w])).values());
                            
                            const allUsers = [...(cashData.users || []), ...(bankData.users || [])];
                            const uniqueUsers = Array.from(new Map(allUsers.map(u => [u.value, u])).values());
                            
                            renderReport({
                                success: true,
                                rows: mergedRows,
                                totals: mergedTotals,
                                cashTotals: cashTotals,
                                bankTotals: bankTotals,
                                customers: uniqueCustomers,
                                workers: uniqueWorkers,
                                users: uniqueUsers
                            });
                            // Reload cash account balance after report is rendered
                            loadCashAccountBalance();
                        } else {
                            lastReportRows = [];
                            lastReportTotals = {};
                            renderReport({ rows: [], totals: {}, customers: [], workers: [], users: [] });
                            loadCashAccountBalance();
                        }
                    })
                    .catch(function() {
                        lastReportRows = [];
                        lastReportTotals = {};
                        renderReport({ rows: [], totals: {}, customers: [], workers: [], users: [] });
                        loadCashAccountBalance();
                    });
            }
            function loadReport() {
                if (loadReportTimeout) clearTimeout(loadReportTimeout);
                loadReportTimeout = setTimeout(doLoadReport, 280);
            }

            filterCustomer.addEventListener('change', loadReport);
            filterWorker.addEventListener('change', loadReport);
            filterUser.addEventListener('change', loadReport);

            btnPng.addEventListener('click', function() {
                if (btnPng.disabled) return;
                const tableSection = document.getElementById('tableSection');
                if (!tableSection) {
                    alert('No data to download');
                    return;
                }
                
                // Open preview modal immediately with loading state
                pngPreviewModal.style.display = 'flex';
                pngPreviewImage.style.display = 'none';
                pngPreviewLoading.style.display = 'block';
                pngPreviewDownload.style.display = 'none';
                
                // Show loading state on button
                const originalText = btnPng.innerHTML;
                btnPng.disabled = true;
                btnPng.innerHTML = '<span>Generating...</span>';
                
                // A4 Landscape dimensions in pixels (at 96 DPI)
                // A4 Landscape: 297mm x 210mm = 1123px x 794px
                const a4LandscapeWidth = 1123;
                const a4LandscapeHeight = 794;
                
                // Get the actual scrollable width and height of the table
                const table = tableSection.querySelector('table');
                const tableContainer = tableSection.querySelector('.overflow-x-auto');
                
                // Temporarily remove overflow to get full table width including all columns
                const originalTableSectionOverflow = tableSection.style.overflow;
                const originalTableSectionWidth = tableSection.style.width;
                const originalContainerOverflow = tableContainer ? tableContainer.style.overflow : '';
                const originalContainerWidth = tableContainer ? tableContainer.style.width : '';
                
                // Make container and table section show full width
                tableSection.style.overflow = 'visible';
                tableSection.style.width = 'auto';
                if (tableContainer) {
                    tableContainer.style.overflow = 'visible';
                    tableContainer.style.width = 'auto';
                }
                
                // Force browser to recalculate layout
                void tableSection.offsetWidth;
                
                // Get the actual full width including all columns (Date & Time to G.TOTAL)
                let actualWidth = table ? table.scrollWidth : tableSection.scrollWidth;
                let actualHeight = table ? table.scrollHeight : tableSection.scrollHeight;
                
                // Ensure we capture at least the minimum table width
                if (table) {
                    actualWidth = Math.max(actualWidth, table.offsetWidth, 1400); // min-w-[1400px] from table class
                }
                
                // Use html2canvas to capture the table section with optimized settings for faster generation
                html2canvas(tableSection, {
                    scale: 1, // Reduced to 1 for fastest generation (will be scaled up later if needed)
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    logging: false,
                    width: actualWidth,
                    height: actualHeight,
                    windowWidth: actualWidth,
                    windowHeight: actualHeight,
                    scrollX: 0,
                    scrollY: 0,
                    x: 0,
                    y: 0,
                    removeContainer: true,
                    allowTaint: false,
                    foreignObjectRendering: false, // Disable for faster rendering
                    imageTimeout: 0, // No timeout for images
                    ignoreElements: function(element) {
                        // Ignore hidden elements and non-essential elements
                        if (element.classList && element.classList.contains('hidden')) return true;
                        // Ignore elements outside viewport if possible
                        return false;
                    }
                }).then(function(canvas) {
                    // Restore original styles
                    tableSection.style.overflow = originalTableSectionOverflow;
                    tableSection.style.width = originalTableSectionWidth;
                    if (tableContainer) {
                        tableContainer.style.overflow = originalContainerOverflow;
                        tableContainer.style.width = originalContainerWidth;
                    }
                    // Calculate scale to fit A4 landscape
                    const originalWidth = canvas.width;
                    const originalHeight = canvas.height;
                    
                    // Calculate scale to fit within A4 landscape dimensions
                    // Use both width and height to ensure everything fits
                    const scaleX = a4LandscapeWidth / originalWidth;
                    const scaleY = a4LandscapeHeight / originalHeight;
                    const scale = Math.min(scaleX, scaleY); // Use the smaller scale to fit both dimensions
                    
                    // Create new canvas with A4 landscape dimensions
                    const a4Canvas = document.createElement('canvas');
                    a4Canvas.width = a4LandscapeWidth;
                    a4Canvas.height = a4LandscapeHeight;
                    const ctx = a4Canvas.getContext('2d');
                    
                    // Fill with white background
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, a4LandscapeWidth, a4LandscapeHeight);
                    
                    // Calculate scaled dimensions
                    const scaledWidth = originalWidth * scale;
                    const scaledHeight = originalHeight * scale;
                    
                    // Center the image on A4 canvas
                    const x = (a4LandscapeWidth - scaledWidth) / 2;
                    const y = (a4LandscapeHeight - scaledHeight) / 2;
                    
                    // Draw the scaled canvas onto A4 canvas with optimized quality
                    ctx.imageSmoothingEnabled = true;
                    ctx.imageSmoothingQuality = 'low'; // Set to 'low' for fastest processing
                    ctx.drawImage(canvas, x, y, scaledWidth, scaledHeight);
                    
                    // Convert canvas to blob
                    a4Canvas.toBlob(function(blob) {
                        // Generate filename with date range and time
                        const dateFrom = reportDateFrom.value;
                        const dateTo = reportDateTo.value;
                        const now = new Date();
                        const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }).replace(':', '-');
                        const dateStr = dateFrom === dateTo ? dateFrom : dateFrom + '_to_' + dateTo;
                        currentPngFilename = 'elite-car-wash-daily-Report-' + dateStr + '-' + timeStr + '.png';
                        
                        // Store blob for download
                        currentPngBlob = blob;
                        
                        // Show preview in modal
                        const previewUrl = URL.createObjectURL(blob);
                        pngPreviewImage.src = previewUrl;
                        pngPreviewImage.style.display = 'block';
                        pngPreviewLoading.style.display = 'none';
                        pngPreviewDownload.style.display = 'flex';
                        
                        // Restore button state
                        btnPng.disabled = false;
                        btnPng.innerHTML = originalText;
                    }, 'image/png', 1.0); // Maximum quality
                }).catch(function(error) {
                    console.error('Error generating PNG:', error);
                    alert('Error generating PNG. Please try again.');
                    
                    // Restore original styles in case of error
                    tableSection.style.overflow = originalTableSectionOverflow;
                    tableSection.style.width = originalTableSectionWidth;
                    if (tableContainer) {
                        tableContainer.style.overflow = originalContainerOverflow;
                        tableContainer.style.width = originalContainerWidth;
                    }
                    
                    btnPng.disabled = false;
                    btnPng.innerHTML = originalText;
                });
            });

            btnPdf.addEventListener('click', function() {
                if (btnPdf.disabled) return;
                const dateFrom = reportDateFrom.value;
                const dateTo = reportDateTo.value;
                if (!dateFrom || !dateTo) return;
                const url = buildUrl(routes.pdf, {
                    date_from: dateFrom,
                    date_to: dateTo,
                    customer: filterCustomer.value || '',
                    worker: filterWorker.value || '',
                    user: filterUser.value || ''
                });
                window.location.href = url;
            });

            btnSendWhatsApp.addEventListener('click', function() {
                if (btnSendWhatsApp.disabled) return;
                const dateFrom = reportDateFrom.value;
                const dateTo = reportDateTo.value;
                if (!dateFrom || !dateTo) return;
                
                // Build PDF URL with inline parameter for browser viewing
                const pdfUrl = buildUrl(routes.pdf, {
                    date_from: dateFrom,
                    date_to: dateTo,
                            customer: filterCustomer.value || '',
                            worker: filterWorker.value || '',
                            user: filterUser.value || '',
                            inline: '1' // Add inline parameter to open PDF in browser instead of download
                });
                
                // Format dates for message
                const formatDate = function(dateStr) {
                    if (!dateStr) return '';
                    const date = new Date(dateStr);
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    return day + '/' + month + '/' + year;
                };
                
                const dateFromFormatted = formatDate(dateFrom);
                const dateToFormatted = formatDate(dateTo);
                const dateRange = dateFromFormatted === dateToFormatted ? dateFromFormatted : dateFromFormatted + ' to ' + dateToFormatted;
                
                // Create full PDF URL - ensure it's a complete URL
                let fullPdfUrl = pdfUrl;
                if (!fullPdfUrl.startsWith('http://') && !fullPdfUrl.startsWith('https://')) {
                    // If it starts with /, it's a relative URL
                    if (fullPdfUrl.startsWith('/')) {
                        fullPdfUrl = window.location.origin + fullPdfUrl;
                    } else {
                        // Otherwise prepend origin
                        fullPdfUrl = window.location.origin + '/' + fullPdfUrl;
                    }
                }
                
                // Create WhatsApp message with the PDF link (don't encode the entire message, encode separately)
                const messageText = '📊 *Daily Jobs Report*\n\n' +
                    '📅 Date Range: ' + dateRange + '\n\n' +
                    '📄 PDF Report:\n' + fullPdfUrl + '\n\n' +
                    'Please click the link above to view the PDF report in your browser.';
                
                // Open WhatsApp with pre-filled message
                const whatsappNumber = '923350899908';
                const whatsappUrl = 'https://wa.me/' + whatsappNumber + '?text=' + encodeURIComponent(messageText);
                window.open(whatsappUrl, '_blank');
            });

            reportDateFrom.addEventListener('change', loadReport);  // debounced
            reportDateTo.addEventListener('change', loadReport);    // debounced


            // Load cash balance
            function loadCashBalance() {
                fetch(routes.cashAccountBalance)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            var balance = Math.round(parseFloat(data.balance) || 0);
                            modalCashBalance.textContent = 'Rs.' + balance;
                            transferAmount.max = balance;
                        } else {
                            modalCashBalance.textContent = 'Rs.0';
                            transferAmount.max = 0;
                        }
                    })
                    .catch(function() {
                        modalCashBalance.textContent = 'Rs.0';
                        transferAmount.max = 0;
                    });
            }

            // Load branch users (for transfer modal and for User filter = Barki Express all users)
            function loadBranchUsers() {
                return fetch(routes.branchUsers)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        transferToUser.innerHTML = '<option value="">Select User</option>';
                        filterUser.innerHTML = '<option value="">All</option>';
                        if (data.success && data.users) {
                            data.users.forEach(function(user) {
                                var roleLabel = user.role ? ' - ' + (user.role + '').replace(/_/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); }) : '';
                                var label = user.name + (user.email ? ' (' + user.email + ')' : '') + roleLabel;
                                var optionTransfer = document.createElement('option');
                                optionTransfer.value = user.id;
                                optionTransfer.textContent = label;
                                transferToUser.appendChild(optionTransfer);
                                var optionFilter = document.createElement('option');
                                optionFilter.value = user.id;
                                optionFilter.textContent = label;
                                filterUser.appendChild(optionFilter);
                            });
                        }
                        return data;
                    })
                    .catch(function() {
                        transferToUser.innerHTML = '<option value="">Select User</option>';
                        filterUser.innerHTML = '<option value="">All</option>';
                        return { success: false };
                    });
            }

            function updateModalBalanceDisplay() {
                var transferVal = parseFloat(transferAmount.value) || 0;
                var remaining = Math.max(0, lastReportCashOnHand - transferVal);
                modalCashBalance.textContent = 'Rs.' + Math.round(remaining);
            }

            // Open cash on hand breakdown modal
            function openCashOnHandBreakdown() {
                if (!lastReportRows || lastReportRows.length === 0) {
                    alert('No report data available. Please load a report first.');
                    return;
                }
                
                var dateFrom = reportDateFrom.value;
                var dateTo = reportDateTo.value;
                var dateRange = dateFrom && dateTo ? 
                    (dateFrom === dateTo ? dateFrom : dateFrom + ' to ' + dateTo) : 
                    'Selected Date Range';
                
                document.getElementById('breakdownDateRange').textContent = dateRange;
                
                // Build breakdown table with Debit, Credit, and Total columns
                var breakdownHtml = '<div class="overflow-x-auto">' +
                    '<table class="w-full min-w-[600px] border-collapse">' +
                    '<thead class="bg-indigo-100">' +
                    '<tr>' +
                    '<th class="px-3 py-2 text-left text-xs font-black text-indigo-900 uppercase border-b-2 border-indigo-300">Date & Time</th>' +
                    '<th class="px-3 py-2 text-left text-xs font-black text-indigo-900 uppercase border-b-2 border-indigo-300">Description</th>' +
                    '<th class="px-3 py-2 text-right text-xs font-black text-indigo-900 uppercase border-b-2 border-indigo-300">Debit</th>' +
                    '<th class="px-3 py-2 text-right text-xs font-black text-indigo-900 uppercase border-b-2 border-indigo-300">Credit</th>' +
                    '<th class="px-3 py-2 text-right text-xs font-black text-indigo-900 uppercase border-b-2 border-indigo-300">Total</th>' +
                    '</tr>' +
                    '</thead>' +
                    '<tbody class="divide-y divide-slate-200">';
                
                var totalCashReceipt = 0;
                var totalCashTransfer = 0;
                var totalShopExpense = 0;
                var totalJobExpense = 0;
                var openingBalance = 0;
                var runningTotal = 0;
                
                lastReportRows.forEach(function(r) {
                    var debit = 0;
                    var credit = 0;
                    var description = '';
                    var rowClass = '';
                    
                    if (r.isOpening) {
                        openingBalance = parseFloat(r.cashOpeningBalance) || 0;
                        runningTotal = openingBalance;
                        description = 'Opening Balance';
                        rowClass = 'bg-slate-100 font-semibold';
                        breakdownHtml += '<tr class="' + rowClass + '">' +
                            '<td class="px-3 py-2 text-xs text-slate-700">' + (r.date || '-') + '</td>' +
                            '<td class="px-3 py-2 text-xs font-bold text-slate-900">' + description + '</td>' +
                            '<td class="px-3 py-2 text-xs text-right text-slate-500">-</td>' +
                            '<td class="px-3 py-2 text-xs text-right text-slate-500">-</td>' +
                            '<td class="px-3 py-2 text-xs text-right font-black text-slate-900">Rs.' + Math.round(runningTotal) + '</td>' +
                            '</tr>';
                    } else if (r.paymentType === 'cash' && (r.credit || 0) > 0) {
                        credit = parseFloat(r.credit) || 0;
                        totalCashReceipt += credit;
                        runningTotal += credit;
                        description = 'Cash Receipt: ' + (r.vehicle || '-');
                        rowClass = 'bg-indigo-50';
                        breakdownHtml += '<tr class="' + rowClass + '">' +
                            '<td class="px-3 py-2 text-xs text-slate-700">' + (r.date || '-') + '</td>' +
                            '<td class="px-3 py-2 text-xs text-indigo-700">' + description + '</td>' +
                            '<td class="px-3 py-2 text-xs text-right text-slate-500">-</td>' +
                            '<td class="px-3 py-2 text-xs text-right font-bold text-indigo-700">+Rs.' + Math.round(credit) + '</td>' +
                            '<td class="px-3 py-2 text-xs text-right font-black text-indigo-900">Rs.' + Math.round(runningTotal) + '</td>' +
                            '</tr>';
                    } else if (r.isCashTransfer && (r.cashTransfer || 0) > 0) {
                        debit = parseFloat(r.cashTransfer) || 0;
                        totalCashTransfer += debit;
                        runningTotal -= debit;
                        description = 'Cash Transfer: To ' + (r.toUserName || 'Admin');
                        rowClass = 'bg-blue-50';
                        breakdownHtml += '<tr class="' + rowClass + '">' +
                            '<td class="px-3 py-2 text-xs text-slate-700">' + (r.date || '-') + '</td>' +
                            '<td class="px-3 py-2 text-xs text-blue-700">' + description + '</td>' +
                            '<td class="px-3 py-2 text-xs text-right font-bold text-blue-700">Rs.' + Math.round(debit) + '</td>' +
                            '<td class="px-3 py-2 text-xs text-right text-slate-500">-</td>' +
                            '<td class="px-3 py-2 text-xs text-right font-black text-blue-900">Rs.' + Math.round(runningTotal) + '</td>' +
                            '</tr>';
                    } else if (r.isShopExpense && (r.shopExpense || 0) > 0) {
                        debit = parseFloat(r.shopExpense) || 0;
                        totalShopExpense += debit;
                        runningTotal -= debit;
                        description = 'Shop Expense: ' + (r.vehicle || '-');
                        rowClass = 'bg-amber-50';
                        breakdownHtml += '<tr class="' + rowClass + '">' +
                            '<td class="px-3 py-2 text-xs text-slate-700">' + (r.date || '-') + '</td>' +
                            '<td class="px-3 py-2 text-xs text-amber-700">' + description + '</td>' +
                            '<td class="px-3 py-2 text-xs text-right font-bold text-amber-700">Rs.' + Math.round(debit) + '</td>' +
                            '<td class="px-3 py-2 text-xs text-right text-slate-500">-</td>' +
                            '<td class="px-3 py-2 text-xs text-right font-black text-amber-900">Rs.' + Math.round(runningTotal) + '</td>' +
                            '</tr>';
                    } else if ((r.debit || 0) > 0) {
                        debit = parseFloat(r.debit) || 0;
                        totalJobExpense += debit;
                        runningTotal -= debit;
                        description = 'Job Expense: ' + (r.vehicle || '-');
                        rowClass = 'bg-red-50';
                        breakdownHtml += '<tr class="' + rowClass + '">' +
                            '<td class="px-3 py-2 text-xs text-slate-700">' + (r.date || '-') + '</td>' +
                            '<td class="px-3 py-2 text-xs text-red-700">' + description + '</td>' +
                            '<td class="px-3 py-2 text-xs text-right font-bold text-red-700">Rs.' + Math.round(debit) + '</td>' +
                            '<td class="px-3 py-2 text-xs text-right text-slate-500">-</td>' +
                            '<td class="px-3 py-2 text-xs text-right font-black text-red-900">Rs.' + Math.round(runningTotal) + '</td>' +
                            '</tr>';
                    }
                });
                
                // Calculate final total
                var finalTotal = openingBalance + totalCashReceipt - totalCashTransfer - totalShopExpense - totalJobExpense;
                var totalDebit = totalCashTransfer + totalShopExpense + totalJobExpense;
                var totalCredit = totalCashReceipt;
                
                // Footer row with totals
                breakdownHtml += '</tbody>' +
                    '<tfoot class="bg-indigo-100 font-black">' +
                    '<tr class="border-t-2 border-indigo-300">' +
                    '<td class="px-3 py-2 text-xs text-indigo-900" colspan="2">TOTAL</td>' +
                    '<td class="px-3 py-2 text-xs text-right text-indigo-900">Rs.' + Math.round(totalDebit) + '</td>' +
                    '<td class="px-3 py-2 text-xs text-right text-indigo-900">Rs.' + Math.round(totalCredit) + '</td>' +
                    '<td class="px-3 py-2 text-xs text-right text-indigo-900">Rs.' + Math.round(finalTotal) + '</td>' +
                    '</tr>' +
                    '</tfoot>' +
                    '</table>' +
                    '</div>';
                
                document.getElementById('breakdownContent').innerHTML = breakdownHtml;
                cashOnHandBreakdownModal.style.display = 'flex';
            }
            
            function closeCashOnHandBreakdown() {
                cashOnHandBreakdownModal.style.display = 'none';
            }
            
            // Open bank balance breakdown modal
            function openBankBalanceBreakdown() {
                if (!lastReportRows || lastReportRows.length === 0) {
                    alert('No report data available. Please load a report first.');
                    return;
                }
                
                var dateFrom = reportDateFrom.value;
                var dateTo = reportDateTo.value;
                var dateRange = dateFrom && dateTo ? 
                    (dateFrom === dateTo ? dateFrom : dateFrom + ' to ' + dateTo) : 
                    'Selected Date Range';
                
                document.getElementById('bankBreakdownDateRange').textContent = dateRange;
                
                // Build breakdown table with Debit, Credit, and Total columns for bank transactions
                var breakdownHtml = '<div class="overflow-x-auto">' +
                    '<table class="w-full min-w-[600px] border-collapse">' +
                    '<thead class="bg-purple-100">' +
                    '<tr>' +
                    '<th class="px-3 py-2 text-left text-xs font-black text-purple-900 uppercase border-b-2 border-purple-300">Date & Time</th>' +
                    '<th class="px-3 py-2 text-left text-xs font-black text-purple-900 uppercase border-b-2 border-purple-300">Description</th>' +
                    '<th class="px-3 py-2 text-right text-xs font-black text-purple-900 uppercase border-b-2 border-purple-300">Debit</th>' +
                    '<th class="px-3 py-2 text-right text-xs font-black text-purple-900 uppercase border-b-2 border-purple-300">Credit</th>' +
                    '<th class="px-3 py-2 text-right text-xs font-black text-purple-900 uppercase border-b-2 border-purple-300">Total</th>' +
                    '</tr>' +
                    '</thead>' +
                    '<tbody class="divide-y divide-slate-200">';
                
                var totalBankCredit = 0;
                var openingBankBalance = 0;
                var runningBankTotal = 0;
                
                lastReportRows.forEach(function(r) {
                    var debit = 0;
                    var credit = 0;
                    var description = '';
                    var rowClass = '';
                    
                    var dateTimeStr = r.dateTime || (r.date || '-');
                    // Extract just date if dateTime contains time
                    if (dateTimeStr.includes(' time ') || dateTimeStr.includes(' Time ')) {
                        dateTimeStr = dateTimeStr.split(' time ')[0].split(' Time ')[0];
                    }
                    
                    if (r.isOpening) {
                        openingBankBalance = parseFloat(r.bankOpeningBalance) || 0;
                        runningBankTotal = openingBankBalance;
                        description = 'Opening Balance';
                        rowClass = 'bg-slate-100 font-semibold';
                        breakdownHtml += '<tr class="' + rowClass + '">' +
                            '<td class="px-3 py-2 text-xs text-slate-700">' + dateTimeStr + '</td>' +
                            '<td class="px-3 py-2 text-xs font-bold text-slate-900">' + description + '</td>' +
                            '<td class="px-3 py-2 text-xs text-right text-slate-500">-</td>' +
                            '<td class="px-3 py-2 text-xs text-right text-slate-500">-</td>' +
                            '<td class="px-3 py-2 text-xs text-right font-black text-slate-900">Rs.' + Math.round(runningBankTotal) + '</td>' +
                            '</tr>';
                    } else if (r.paymentType === 'bank' && (r.credit || 0) > 0) {
                        credit = parseFloat(r.credit) || 0;
                        totalBankCredit += credit;
                        runningBankTotal += credit;
                        var bankInfo = '';
                        if (r.bankNameOnly) {
                            bankInfo = r.bankNameOnly;
                            if (r.bankAccountTitle) bankInfo += ' - ' + r.bankAccountTitle;
                            if (r.bankAccountNumber) bankInfo += ' (' + r.bankAccountNumber + ')';
                        }
                        description = 'Bank Credit: ' + (r.vehicle || '-') + (bankInfo ? ' - ' + bankInfo : '');
                        rowClass = 'bg-purple-50';
                        breakdownHtml += '<tr class="' + rowClass + '">' +
                            '<td class="px-3 py-2 text-xs text-slate-700">' + dateTimeStr + '</td>' +
                            '<td class="px-3 py-2 text-xs text-purple-700">' + description + '</td>' +
                            '<td class="px-3 py-2 text-xs text-right text-slate-500">-</td>' +
                            '<td class="px-3 py-2 text-xs text-right font-bold text-purple-700">+Rs.' + Math.round(credit) + '</td>' +
                            '<td class="px-3 py-2 text-xs text-right font-black text-purple-900">Rs.' + Math.round(runningBankTotal) + '</td>' +
                            '</tr>';
                    }
                });
                
                // Calculate final total
                var finalBankTotal = openingBankBalance + totalBankCredit;
                
                // Footer row with totals
                breakdownHtml += '</tbody>' +
                    '<tfoot class="bg-purple-100 font-black">' +
                    '<tr class="border-t-2 border-purple-300">' +
                    '<td class="px-3 py-2 text-xs text-purple-900" colspan="2">TOTAL</td>' +
                    '<td class="px-3 py-2 text-xs text-right text-purple-900">Rs.0</td>' +
                    '<td class="px-3 py-2 text-xs text-right text-purple-900">Rs.' + Math.round(totalBankCredit) + '</td>' +
                    '<td class="px-3 py-2 text-xs text-right text-purple-900">Rs.' + Math.round(finalBankTotal) + '</td>' +
                    '</tr>' +
                    '</tfoot>' +
                    '</table>' +
                    '</div>';
                
                document.getElementById('bankBreakdownContent').innerHTML = breakdownHtml;
                bankBalanceBreakdownModal.style.display = 'flex';
            }
            
            function closeBankBalanceBreakdown() {
                bankBalanceBreakdownModal.style.display = 'none';
            }
            
            function openCashTransferModal() {
                var balance = lastReportCashOnHand;
                transferAmount.setAttribute('max', balance);
                transferAmount.max = balance;
                transferAmount.value = balance;
                transferAmount.disabled = false; // Ensure enabled for new transfers
                loadBranchUsers();
                transferToUser.value = '';
                transferToUser.disabled = false; // Ensure enabled for new transfers
                transferNote.value = '';
                // Reset edit mode
                if (cashTransferModal.dataset.editMode) {
                    delete cashTransferModal.dataset.editMode;
                    delete cashTransferModal.dataset.transferId;
                    delete cashTransferModal.dataset.originalAmount;
                }
                // Reset modal title
                var modalTitle = cashTransferModal.querySelector('h3');
                if (modalTitle) {
                    modalTitle.textContent = 'Cash Transfer';
                }
                if (transferUserWarning) {
                    transferUserWarning.classList.add('hidden');
                }
                cashTransferModal.style.display = 'flex';
                updateModalBalanceDisplay();
            }
            
            // Edit cash transfer function
            window.editCashTransfer = function(transferId, amount, toUserId, notes) {
                var balance = lastReportCashOnHand;
                // In edit mode, allow amount change - remove max restriction or set it very high
                // This allows user to increase/decrease the transfer amount freely
                var originalAmount = parseFloat(amount) || 0;
                // Set a very high max value or remove it to allow free editing
                var newMax = Math.max(1000000, balance + originalAmount * 10); // Very high limit
                transferAmount.setAttribute('max', newMax);
                transferAmount.max = newMax;
                transferAmount.removeAttribute('readonly');
                transferAmount.disabled = false; // Ensure amount field is enabled
                // Clear and set value to allow editing
                transferAmount.value = '';
                setTimeout(function() {
                    transferAmount.value = amount || '';
                    transferAmount.focus();
                    // Select all text so user can easily replace it
                    transferAmount.select();
                }, 100);
                loadBranchUsers().then(function() {
                    if (toUserId) {
                        transferToUser.value = toUserId;
                    } else {
                        transferToUser.value = '';
                    }
                    transferToUser.disabled = false; // Allow user change too
                });
                transferNote.value = notes || '';
                // Set edit mode
                cashTransferModal.dataset.editMode = 'true';
                cashTransferModal.dataset.transferId = transferId;
                cashTransferModal.dataset.originalAmount = originalAmount;
                if (transferUserWarning) {
                    transferUserWarning.classList.add('hidden');
                }
                // Update modal title to show edit mode
                var modalTitle = cashTransferModal.querySelector('h3');
                if (modalTitle) {
                    modalTitle.textContent = 'Edit Cash Transfer';
                }
                cashTransferModal.style.display = 'flex';
                updateModalBalanceDisplay();
            };
            
            // Delete cash transfer function
            window.deleteCashTransfer = function(transferId) {
                if (!transferId) {
                    alert('Transfer ID not found');
                    return;
                }
                
                if (!confirm('Are you sure you want to delete this cash transfer? This action cannot be undone.')) {
                    return;
                }
                
                // Call delete endpoint
                var deleteUrl = routes.deleteCashTransfer.replace(':id', transferId);
                fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        alert('Cash transfer deleted successfully!');
                        loadCashAccountBalance(); // Update the card balance
                        // Reload report if it's loaded
                        if (reportDateFrom.value && reportDateTo.value) {
                            doLoadReport();
                        }
                    } else {
                        alert('Failed to delete cash transfer: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(function(err) {
                    console.error('Error deleting cash transfer:', err);
                    alert('Error deleting cash transfer. Please try again.');
                });
            };
            
            // Edit bank job function
            window.editBankJob = function(jobId, currentPrice) {
                if (!jobId) {
                    alert('Job ID not found');
                    return;
                }
                
                const newPrice = prompt('Enter new bank payment amount:', currentPrice);
                if (newPrice === null || newPrice === '') {
                    return; // User cancelled
                }
                
                const price = parseFloat(newPrice);
                if (isNaN(price) || price < 0) {
                    alert('Please enter a valid amount');
                    return;
                }
                
                // Call update endpoint
                fetch('/car-wash/jobs/' + jobId, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        price: price
                    })
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        alert('Bank payment updated successfully!');
                        // Reload report if it's loaded
                        if (reportDateFrom.value && reportDateTo.value) {
                            doLoadReport();
                        }
                    } else {
                        alert('Error: ' + (data.message || 'Failed to update bank payment'));
                    }
                })
                .catch(function(error) {
                    console.error('Error updating bank payment:', error);
                    alert('Error updating bank payment. Please try again.');
                });
            };
            
            // Delete bank job function
            window.deleteBankJob = function(jobId) {
                if (!jobId) {
                    alert('Job ID not found');
                    return;
                }
                
                if (!confirm('Are you sure you want to delete this bank payment? This action cannot be undone.')) {
                    return;
                }
                
                // Call delete endpoint
                fetch('/car-wash/jobs/' + jobId, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        alert('Bank payment deleted successfully!');
                        // Reload report if it's loaded
                        if (reportDateFrom.value && reportDateTo.value) {
                            doLoadReport();
                        }
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete bank payment'));
                    }
                })
                .catch(function(error) {
                    console.error('Error deleting bank payment:', error);
                    alert('Error deleting bank payment. Please try again.');
                });
            };
            
            // Delete bank transfer function
            window.deleteBankTransfer = function(transferId) {
                if (!transferId) {
                    alert('Transfer ID not found');
                    return;
                }
                
                if (!confirm('Are you sure you want to delete this bank transfer? This action cannot be undone.')) {
                    return;
                }
                
                // Call delete endpoint
                fetch('/car-wash/payments/bank-transfer/' + transferId + '/delete', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        alert('Bank transfer deleted successfully!');
                        // Reload report if it's loaded
                        if (reportDateFrom.value && reportDateTo.value) {
                            doLoadReport();
                        }
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete bank transfer'));
                    }
                })
                .catch(function(error) {
                    console.error('Error deleting bank transfer:', error);
                    alert('Error deleting bank transfer. Please try again.');
                });
            };

            function closeCashTransferModal() {
                cashTransferModal.style.display = 'none';
                transferAmount.value = '';
                transferToUser.value = '';
                transferNote.value = '';
                // Reset edit mode
                if (cashTransferModal.dataset.editMode) {
                    delete cashTransferModal.dataset.editMode;
                    delete cashTransferModal.dataset.transferId;
                }
            }

            // Transfer cash
            btnTransferCash.addEventListener('click', function() {
                var userId = (transferToUser.value || '').trim();
                var amount = parseFloat(transferAmount.value);
                var note = transferNote.value.trim();

                if (!userId) {
                    if (transferUserWarning) {
                        transferUserWarning.classList.remove('hidden');
                    }
                    alert('Please select a user first');
                    transferToUser.focus();
                    return;
                }
                if (transferUserWarning) {
                    transferUserWarning.classList.add('hidden');
                }

                if (!amount || amount <= 0) {
                    alert('Please enter a valid amount');
                    return;
                }

                var maxAmount = parseFloat(transferAmount.max) || 0;
                if (amount > maxAmount) {
                    alert('Amount cannot exceed available cash balance');
                    return;
                }

                btnTransferCash.disabled = true;
                var isEditMode = cashTransferModal.dataset.editMode === 'true';
                var transferId = cashTransferModal.dataset.transferId;
                btnTransferCash.textContent = isEditMode ? 'Updating...' : 'Transferring...';

                // Check if edit mode
                if (isEditMode && transferId) {
                    // For now, show message that edit is not yet implemented
                    // In future, you can add an update endpoint
                    alert('Edit functionality is not yet implemented. Please delete and create a new transfer.');
                    btnTransferCash.disabled = false;
                    btnTransferCash.textContent = 'Transfer Cash';
                    return;
                }

                fetch(routes.transferToUser, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        amount: amount,
                        note: note || null
                    })
                })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            alert('Transfer successful! Rs.' + amount + ' has been transferred.');
                            closeCashTransferModal();
                            loadCashBalance();
                            loadCashAccountBalance(); // Update the card balance
                            // Reload report if it's loaded
                            if (reportDateFrom.value && reportDateTo.value) {
                                doLoadReport();
                            }
                        } else {
                            alert('Error: ' + (data.message || 'Failed to transfer cash'));
                        }
                    })
                    .catch(function() {
                        alert('Error transferring cash. Please try again.');
                    })
                    .finally(function() {
                        btnTransferCash.disabled = false;
                        btnTransferCash.textContent = 'Transfer Cash';
                    });
            });

            // Load bank accounts for transfer (custom dropdown: each account in 3 lines)
            function loadBankAccountsForTransfer() {
                fetch(routes.bankAccountsForTransfer)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (!transferToBankAccountList) return;
                        transferToBankAccountList.innerHTML = '';
                        if (data.success && data.bankAccounts) {
                            data.bankAccounts.forEach(function(account) {
                                var item = document.createElement('button');
                                item.type = 'button';
                                item.className = 'w-full px-4 py-3 text-left border-b border-slate-100 hover:bg-purple-50 focus:bg-purple-50 focus:outline-none';
                                item.dataset.id = account.id;
                                item.dataset.bankName = account.bankName || '';
                                item.dataset.accountTitle = account.accountTitle || '';
                                item.dataset.accountNumber = account.accountNumber || '';
                                item.innerHTML = '<span class="block font-bold text-slate-800 text-sm">' + (account.bankName || '') + '</span>' +
                                    '<span class="block text-slate-700 text-sm">' + (account.accountTitle || '') + '</span>' +
                                    '<span class="block text-slate-600 text-sm">' + (account.accountNumber || '') + '</span>';
                                item.addEventListener('click', function() {
                                    transferToBankAccount.value = this.dataset.id;
                                    triggerLine1.textContent = this.dataset.bankName || 'Bank Account';
                                    triggerLine2.textContent = this.dataset.accountTitle || '';
                                    triggerLine3.textContent = this.dataset.accountNumber || '';
                                    transferToBankAccountDropdown.classList.add('hidden');
                                });
                                transferToBankAccountList.appendChild(item);
                            });
                        }
                    })
                    .catch(function() {
                        transferToBankAccountList.innerHTML = '';
                    });
            }

            function setBankAccountTriggerPlaceholder() {
                if (triggerLine1) triggerLine1.textContent = 'Select Bank Account';
                if (triggerLine2) triggerLine2.textContent = '';
                if (triggerLine3) triggerLine3.textContent = '';
            }

            function updateModalBankBalanceDisplay() {
                // Get balance from footer ftBankTotal instead of calculating
                const ftBankTotalEl = document.getElementById('ftBankTotal');
                if (ftBankTotalEl && modalBankBalance) {
                    const footerBalance = ftBankTotalEl.textContent.trim();
                    modalBankBalance.textContent = footerBalance || 'Rs.0';
                    // Auto-fill amount input with parsed balance (e.g. "Rs.2650" or "+Rs.2650" -> 2650)
                    const numMatch = footerBalance.match(/[\d,]+(?:\.\d+)?/);
                    const balanceNum = numMatch ? parseFloat(numMatch[0].replace(/,/g, '')) : 0;
                    if (bankTransferAmount) {
                        bankTransferAmount.value = balanceNum > 0 ? balanceNum : '';
                    }
                }
            }

            function openBankTransferModal() {
                // Load bank balance and auto-fill amount
                loadBankBalanceForModal().then(function(balance) {
                    bankTransferAmount.setAttribute('max', balance);
                    bankTransferAmount.max = balance;
                    // Amount is already set in loadBankBalanceForModal
                });
                loadBankAccountsForTransfer();
                transferToBankAccount.value = '';
                setBankAccountTriggerPlaceholder();
                bankTransferNote.value = '';
                bankTransferModal.style.display = 'flex';
                updateModalBankBalanceDisplay();
                if (transferToBankAccountDropdown) transferToBankAccountDropdown.classList.add('hidden');
            }

            function closeBankTransferModal() {
                bankTransferModal.style.display = 'none';
                bankTransferAmount.value = '';
                transferToBankAccount.value = '';
                setBankAccountTriggerPlaceholder();
                bankTransferNote.value = '';
                if (transferToBankAccountDropdown) transferToBankAccountDropdown.classList.add('hidden');
            }

            // Load bank account balance for modal
            function loadBankBalanceForModal() {
                return fetch(routes.bankAccounts)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success && data.bankAccounts) {
                            var totalBalance = data.bankAccounts.reduce(function(sum, acc) {
                                return sum + (parseFloat(acc.balance) || 0);
                            }, 0);
                            var balance = Math.round(totalBalance);
                            // Bank transfer: max = bank balance (not cash)
                            bankTransferAmount.setAttribute('max', balance);
                            bankTransferAmount.max = balance;
                            setTimeout(function() {
                                updateModalBankBalanceDisplay();
                            }, 100);
                            return balance;
                        } else {
                            bankTransferAmount.max = 0;
                            setTimeout(function() {
                                updateModalBankBalanceDisplay();
                            }, 100);
                            return 0;
                        }
                    })
                    .catch(function() {
                        bankTransferAmount.max = 0;
                        setTimeout(function() {
                            updateModalBankBalanceDisplay();
                        }, 100);
                        return 0;
                    });
            }

            // Load cash balance for bank transfer validation (hidden, used for max amount)
            function loadCashBalanceForBankTransfer() {
                fetch(routes.cashAccountBalance)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            var balance = parseFloat(data.balance) || 0;
                            bankTransferAmount.max = balance;
                        } else {
                            bankTransferAmount.max = 0;
                        }
                    })
                    .catch(function() {
                        bankTransferAmount.max = 0;
                    });
            }

            // Transfer to bank
            btnTransferBank.addEventListener('click', function() {
                var amount = parseFloat(bankTransferAmount.value);
                var bankAccountId = transferToBankAccount.value;
                var note = bankTransferNote.value.trim();

                if (!amount || amount <= 0) {
                    alert('Please enter a valid amount');
                    return;
                }

                if (!bankAccountId) {
                    alert('Please select a bank account to transfer to');
                    return;
                }

                var maxAmount = parseFloat(bankTransferAmount.max) || 0;
                if (amount > maxAmount) {
                    alert('Amount cannot exceed available bank balance (Rs.' + maxAmount + ')');
                    return;
                }

                btnTransferBank.disabled = true;
                btnTransferBank.textContent = 'Transferring...';

                fetch(routes.cashTransfers, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        bank_account_id: bankAccountId,
                        amount: amount,
                        notes: note || null
                    })
                })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            var accountName = (triggerLine1 && triggerLine1.textContent) ? (triggerLine1.textContent + (triggerLine2 && triggerLine2.textContent ? ' - ' + triggerLine2.textContent : '') + (triggerLine3 && triggerLine3.textContent ? ' (' + triggerLine3.textContent + ')' : '')) : 'Bank Account';
                            alert('Rs.' + amount + ' transferred to ' + accountName + ' successfully!');
                            closeBankTransferModal();
                            loadBankBalanceForModal();
                            loadBankBalance();
                            loadCashAccountBalance(); // Update the card balance after bank transfer
                            // Reload report if it's loaded
                            if (reportDateFrom.value && reportDateTo.value) {
                                doLoadReport();
                            }
                        } else {
                            alert('Error: ' + (data.message || 'Failed to transfer to bank'));
                        }
                    })
                    .catch(function() {
                        alert('Error transferring to bank. Please try again.');
                    })
                    .finally(function() {
                        btnTransferBank.disabled = false;
                        btnTransferBank.textContent = 'Transfer to Bank';
                    });
            });

            // Long press on cash on hand card to show breakdown
            let longPressTimer = null;
            let isLongPress = false;
            
            cashOnHandCard.addEventListener('mousedown', function(e) {
                isLongPress = false;
                longPressTimer = setTimeout(function() {
                    isLongPress = true;
                    openCashOnHandBreakdown();
                }, 500); // 500ms for long press
            });
            
            cashOnHandCard.addEventListener('mouseup', function(e) {
                if (longPressTimer) {
                    clearTimeout(longPressTimer);
                    longPressTimer = null;
                }
                // If it was a long press, prevent click event
                if (isLongPress) {
                    e.preventDefault();
                    e.stopPropagation();
                    isLongPress = false;
                    return false;
                }
            });
            
            cashOnHandCard.addEventListener('mouseleave', function(e) {
                if (longPressTimer) {
                    clearTimeout(longPressTimer);
                    longPressTimer = null;
                }
                isLongPress = false;
            });
            
            // Touch events for mobile
            cashOnHandCard.addEventListener('touchstart', function(e) {
                isLongPress = false;
                longPressTimer = setTimeout(function() {
                    isLongPress = true;
                    openCashOnHandBreakdown();
                }, 500);
            });
            
            cashOnHandCard.addEventListener('touchend', function(e) {
                if (longPressTimer) {
                    clearTimeout(longPressTimer);
                    longPressTimer = null;
                }
                if (isLongPress) {
                    e.preventDefault();
                    e.stopPropagation();
                    isLongPress = false;
                    return false;
                }
            });
            
            // Click event for cash transfer modal (only if not long press)
            cashOnHandCard.addEventListener('click', function(e) {
                if (!isLongPress) {
                    openCashTransferModal();
                }
            });
            
            // Close breakdown modal
            cashOnHandBreakdownModalClose.addEventListener('click', closeCashOnHandBreakdown);
            cashOnHandBreakdownModal.addEventListener('click', function(e) {
                if (e.target === cashOnHandBreakdownModal) closeCashOnHandBreakdown();
            });
            // Long press on bank balance card to show breakdown
            let bankLongPressTimer = null;
            let isBankLongPress = false;
            
            bankBalanceCard.addEventListener('mousedown', function(e) {
                isBankLongPress = false;
                bankLongPressTimer = setTimeout(function() {
                    isBankLongPress = true;
                    openBankBalanceBreakdown();
                }, 500); // 500ms for long press
            });
            
            bankBalanceCard.addEventListener('mouseup', function(e) {
                if (bankLongPressTimer) {
                    clearTimeout(bankLongPressTimer);
                    bankLongPressTimer = null;
                }
                // If it was a long press, prevent click event
                if (isBankLongPress) {
                    e.preventDefault();
                    e.stopPropagation();
                    isBankLongPress = false;
                    return false;
                }
            });
            
            bankBalanceCard.addEventListener('mouseleave', function(e) {
                if (bankLongPressTimer) {
                    clearTimeout(bankLongPressTimer);
                    bankLongPressTimer = null;
                }
                isBankLongPress = false;
            });
            
            // Touch events for mobile
            bankBalanceCard.addEventListener('touchstart', function(e) {
                isBankLongPress = false;
                bankLongPressTimer = setTimeout(function() {
                    isBankLongPress = true;
                    openBankBalanceBreakdown();
                }, 500);
            });
            
            bankBalanceCard.addEventListener('touchend', function(e) {
                if (bankLongPressTimer) {
                    clearTimeout(bankLongPressTimer);
                    bankLongPressTimer = null;
                }
                if (isBankLongPress) {
                    e.preventDefault();
                    e.stopPropagation();
                    isBankLongPress = false;
                    return false;
                }
            });
            
            // Click event for bank transfer modal (only if not long press)
            bankBalanceCard.addEventListener('click', function(e) {
                if (!isBankLongPress) {
                    openBankTransferModal();
                }
            });
            
            // Close bank breakdown modal
            bankBalanceBreakdownModalClose.addEventListener('click', closeBankBalanceBreakdown);
            bankBalanceBreakdownModal.addEventListener('click', function(e) {
                if (e.target === bankBalanceBreakdownModal) closeBankBalanceBreakdown();
            });
            cashTransferModalClose.addEventListener('click', closeCashTransferModal);
            cashTransferModal.addEventListener('click', function(e) {
                if (e.target === cashTransferModal) closeCashTransferModal();
            });
            transferToUser.addEventListener('change', function() {
                if (transferUserWarning && transferToUser.value) {
                    transferUserWarning.classList.add('hidden');
                }
            });
            // Transfer amount cannot exceed available cash; balance = available minus transfer amount
            // But in edit mode, allow free editing
            transferAmount.addEventListener('input', function() {
                // Skip validation in edit mode
                var isEditMode = cashTransferModal.dataset.editMode === 'true';
                if (!isEditMode) {
                    var maxAllowed = lastReportCashOnHand;
                    var val = parseFloat(transferAmount.value);
                    if (!isNaN(val) && val > maxAllowed) {
                        transferAmount.value = maxAllowed;
                    }
                }
                updateModalBalanceDisplay();
            });
            bankTransferModalClose.addEventListener('click', closeBankTransferModal);
            bankTransferModal.addEventListener('click', function(e) {
                if (e.target === bankTransferModal) closeBankTransferModal();
            });
            bankTransferAmount.addEventListener('input', function() {
                var maxAllowed = parseFloat(bankTransferAmount.max) || lastReportBankBalance;
                var val = parseFloat(bankTransferAmount.value);
                if (!isNaN(val) && val > maxAllowed) {
                    bankTransferAmount.value = maxAllowed;
                }
                // Don't call updateModalBankBalanceDisplay here - it overwrites user's typing
            });
            transferToBankAccountTrigger.addEventListener('click', function(e) {
                e.preventDefault();
                transferToBankAccountDropdown.classList.toggle('hidden');
            });
            document.addEventListener('click', function(e) {
                if (transferToBankAccountDropdown && !transferToBankAccountTrigger.contains(e.target) && !transferToBankAccountDropdown.contains(e.target)) {
                    transferToBankAccountDropdown.classList.add('hidden');
                }
            });
            
            // PNG Preview Modal handlers
            function closePngPreviewModal() {
                pngPreviewModal.style.display = 'none';
                if (pngPreviewImage.src) {
                    URL.revokeObjectURL(pngPreviewImage.src);
                    pngPreviewImage.src = '';
                    pngPreviewImage.style.display = 'none';
                }
                pngPreviewLoading.style.display = 'block';
                pngPreviewDownload.style.display = 'none';
                currentPngBlob = null;
                currentPngFilename = '';
            }
            
            pngPreviewModalClose.addEventListener('click', closePngPreviewModal);
            pngPreviewCancel.addEventListener('click', closePngPreviewModal);
            pngPreviewModal.addEventListener('click', function(e) {
                if (e.target === pngPreviewModal) closePngPreviewModal();
            });
            
            pngPreviewDownload.addEventListener('click', function() {
                if (currentPngBlob) {
                    const url = URL.createObjectURL(currentPngBlob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = currentPngFilename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                    closePngPreviewModal();
                }
            });

            // Load on page load: branch users (User filter = all Barki Express users), bank balance, then report
            loadBranchUsers();
            loadBankBalance();
            if (reportDateFrom.value && reportDateTo.value) {
                doLoadReport();
            } else {
                loadCashAccountBalance();
            }
        })();
    </script>
</body>
</html>
