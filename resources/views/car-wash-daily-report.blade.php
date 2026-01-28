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

        <main class="max-w-7xl mx-auto p-6">
            <!-- Date, Filters & Actions -->
            <div class="bg-white rounded-2xl shadow-xl border-2 border-slate-200 p-6 mb-6">
                <div class="flex flex-wrap items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-black text-slate-700 uppercase mb-2 text-center">Select Range</label>
                        <div class="flex items-center gap-1.5">
                            <input type="date" id="reportDateFrom" value="{{ $selectedDate }}"
                                class="flex-1 px-2.5 py-2 border-2 border-slate-300 rounded-lg text-slate-900 font-bold focus:border-indigo-500 focus:outline-none text-xs" />
                            <span class="text-[10px] font-bold text-slate-600 whitespace-nowrap">To</span>
                            <input type="date" id="reportDateTo" value="{{ $selectedDate }}"
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

            <!-- Summary (like image: total vehicles, total debit, total credit, cash on hand, bank balance, total workers, total commission) -->
            <div id="totalsSection" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2 sm:gap-3 mb-6 hidden">
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
                    <p id="totBankBalance" class="text-base sm:text-lg font-black text-purple-900">Rs.0</p>
                </div>
                <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-lg border border-amber-300 p-2.5 sm:p-3 shadow-sm hover:shadow transition-shadow">
                    <p class="text-[9px] sm:text-[10px] font-bold text-amber-800 uppercase mb-1">Ref Expence</p>
                    <p id="totDebit" class="text-base sm:text-lg font-black text-amber-800">Rs.0</p>
                </div>
                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg border border-emerald-300 p-2.5 sm:p-3 shadow-sm hover:shadow transition-shadow">
                    <p class="text-[9px] sm:text-[10px] font-bold text-emerald-800 uppercase mb-1">Commission</p>
                    <p id="totCommission" class="text-base sm:text-lg font-black text-emerald-800">Rs.0</p>
                </div>
            </div>

            <!-- Ledger Table: Date & Time | Vehicle | Debit | Credit | Total | Worker | Commission -->
            <div id="tableSection" class="bg-white rounded-2xl shadow-xl border-2 border-slate-200 overflow-hidden hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1200px] sm:min-w-[1400px]">
                        <thead class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white">
                            <tr>
                                <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-left text-[9px] sm:text-[10px] md:text-xs font-black uppercase whitespace-nowrap">Date & Time</th>
                                <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-left text-[9px] sm:text-[10px] md:text-xs font-black uppercase whitespace-nowrap">Vehicle</th>
                                <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-xs font-black uppercase whitespace-nowrap">REF EXPENCE</th>
                                <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-xs font-black uppercase whitespace-nowrap bg-indigo-500/50">Cash Credit</th>
                                <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-xs font-black uppercase whitespace-nowrap bg-indigo-500/50">Cash Total</th>
                                <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-xs font-black uppercase whitespace-nowrap bg-purple-500/50">Bank Credit</th>
                                <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-xs font-black uppercase whitespace-nowrap bg-purple-500/50">Bank Total</th>
                                <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-xs font-black uppercase whitespace-nowrap">Commission</th>
                                <th class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-xs font-black uppercase whitespace-nowrap">G.total</th>
                            </tr>
                        </thead>
                        <tbody id="reportTableBody" class="divide-y divide-slate-200">
                        </tbody>
                        <tfoot>
                            <tr class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white font-black">
                                <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-[9px] sm:text-[10px] md:text-sm whitespace-nowrap font-bold">TOTAL</td>
                                <td class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-[9px] sm:text-[10px] md:text-sm whitespace-nowrap">-</td>
                                <td id="ftExpenses" class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-sm whitespace-nowrap">Rs.0.00</td>
                                <td id="ftCashCredit" class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-sm whitespace-nowrap bg-indigo-500/30">Rs.0.00</td>
                                <td id="ftCashTotal" class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-sm whitespace-nowrap bg-indigo-500/30">Rs.0.00</td>
                                <td id="ftBankCredit" class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-sm whitespace-nowrap bg-purple-500/30">Rs.0.00</td>
                                <td id="ftBankTotal" class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-sm whitespace-nowrap bg-purple-500/30">Rs.0.00</td>
                                <td id="ftCommission" class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-sm whitespace-nowrap">Rs.0.00</td>
                                <td id="ftGtotal" class="px-2 sm:px-3 md:px-4 py-2 sm:py-2.5 md:py-3 text-right text-[9px] sm:text-[10px] md:text-sm whitespace-nowrap">Rs.0.00</td>
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
                        <h3 class="text-lg sm:text-xl font-black uppercase">Money Transfer</h3>
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
                            min="0" step="0.01" />
                    </div>
                    
                    <!-- Transfer To User -->
                    <div class="mb-5">
                        <label class="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-2">Transfer To User</label>
                        <select id="transferToUser" class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl text-slate-900 font-bold focus:border-yellow-500 focus:outline-none">
                            <option value="">Select User</option>
                        </select>
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
                    <!-- Bank Account Balance -->
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
                    
                    <!-- Transfer To Bank Account -->
                    <div class="mb-5">
                        <label class="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-2">Transfer To Bank Account</label>
                        <select id="transferToBankAccount" class="w-full px-4 py-3 border-2 border-slate-300 rounded-xl text-slate-900 font-bold focus:border-purple-500 focus:outline-none">
                            <option value="">Select Bank Account</option>
                        </select>
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
            const filterCustomer = document.getElementById('filterCustomer');
            const filterWorker = document.getElementById('filterWorker');
            const btnPng = document.getElementById('btnDownloadPng');
            const btnPdf = document.getElementById('btnDownloadPdf');
            const btnSendWhatsApp = document.getElementById('btnSendWhatsApp');
            const totalsSection = document.getElementById('totalsSection');
            const tableSection = document.getElementById('tableSection');
            const tableBody = document.getElementById('reportTableBody');
            const emptyState = document.getElementById('emptyState');
            const loadingState = document.getElementById('loadingState');
            const cashOnHandCard = document.getElementById('cashOnHandCard');
            const bankBalanceCard = document.getElementById('bankBalanceCard');
            const cashTransferModal = document.getElementById('cashTransferModal');
            const cashTransferModalClose = document.getElementById('cashTransferModalClose');
            const modalCashBalance = document.getElementById('modalCashBalance');
            const transferAmount = document.getElementById('transferAmount');
            const transferToUser = document.getElementById('transferToUser');
            const transferNote = document.getElementById('transferNote');
            const btnTransferCash = document.getElementById('btnTransferCash');
            
            const bankTransferModal = document.getElementById('bankTransferModal');
            const bankTransferModalClose = document.getElementById('bankTransferModalClose');
            const modalBankBalance = document.getElementById('modalBankBalance');
            const bankTransferAmount = document.getElementById('bankTransferAmount');
            const transferToBankAccount = document.getElementById('transferToBankAccount');
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
            let bankBalance = 0;

            const routes = {
                data: '{{ route("car-wash.jobs.daily-report-data") }}',
                pdf: '{{ route("car-wash.jobs.daily-report-pdf") }}',
                bankAccounts: '{{ route("car-wash.bank-accounts.index") }}',
                cashAccountBalance: '{{ route("car-wash.payments.cash-account-balance") }}',
                branchUsers: '{{ route("car-wash.payments.branch-users") }}',
                transferToUser: '{{ route("car-wash.payments.transfer-to-user") }}',
                cashTransfers: '{{ route("car-wash.cash-transfers.store") }}'
            };

            // Load bank account balance
            function loadBankBalance() {
                fetch(routes.bankAccounts)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success && data.bankAccounts) {
                            bankBalance = data.bankAccounts.reduce(function(sum, acc) {
                                return sum + (parseFloat(acc.balance) || 0);
                            }, 0);
                            document.getElementById('totBankBalance').textContent = 'Rs.' + Math.round(bankBalance);
                        } else {
                            bankBalance = 0;
                            document.getElementById('totBankBalance').textContent = 'Rs.0';
                        }
                    })
                    .catch(function() {
                        bankBalance = 0;
                        document.getElementById('totBankBalance').textContent = 'Rs.0';
                    });
            }

            // Load actual cash account balance
            function loadCashAccountBalance() {
                fetch(routes.cashAccountBalance)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            var balance = Math.round(parseFloat(data.balance) || 0);
                            document.getElementById('totCashOnHand').textContent = 'Rs.' + balance;
                        } else {
                            document.getElementById('totCashOnHand').textContent = 'Rs.0';
                        }
                    })
                    .catch(function() {
                        document.getElementById('totCashOnHand').textContent = 'Rs.0';
                    });
            }

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
                    btnPng.disabled = true;
                    btnPdf.disabled = true;
                    btnSendWhatsApp.disabled = true;
                    return;
                }

                emptyState.classList.add('hidden');
                tableSection.classList.remove('hidden');
                totalsSection.classList.remove('hidden');
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
                document.getElementById('totCommission').textContent = 'Rs.' + Math.round(t.totalCommission || 0);
                
                // All totals in single footer row
                document.getElementById('ftExpenses').textContent = 'Rs.' + Math.round(t.totalDebit || 0);
                document.getElementById('ftCashCredit').textContent = 'Rs.' + Math.round(cashT.totalCredit || 0);
                document.getElementById('ftCashTotal').textContent = 'Rs.' + Math.round(cashT.cashOnHand || 0);
                document.getElementById('ftBankCredit').textContent = 'Rs.' + Math.round(bankT.totalCredit || 0);
                document.getElementById('ftBankTotal').textContent = 'Rs.' + Math.round(bankT.cashOnHand || 0);
                document.getElementById('ftCommission').textContent = 'Rs.' + Math.round(t.totalCommission || 0);
                document.getElementById('ftGtotal').textContent = 'Rs.' + Math.round(t.sumGtotal != null ? t.sumGtotal : ((t.cashOnHand || 0) - (t.totalCommission || 0)));

                function fmtNum(n) {
                    if (n === 0 || n === '-') return n === 0 ? '0' : '-';
                    return 'Rs.' + Math.round(Number(n) || 0);
                }

                // Image jaisa: Credit row (debit empty); Debit row (credit, worker, commission empty). Total running.
                let html = '';
                rows.forEach(function(r) {
                    const isCash = r.paymentType === 'cash';
                    const isBank = r.paymentType === 'bank';
                    const isOpening = r.isOpening;
                    
                    const creditStr = (r.credit || 0) > 0 ? fmtNum(r.credit) : '-';
                    const gTotalStr = (r.gTotal != null && r.gTotal !== '') ? fmtNum(r.gTotal) : '-';
                    const totalStr = r.total != null ? fmtNum(r.total) : '-';
                    const expenseStr = (r.debit || 0) > 0 ? fmtNum(r.debit) : '-';
                    let commStr = (r.commission !== '-' && r.commission != null && (r.commission || 0) > 0) ? fmtNum(r.commission) : '-';
                    // Add worker name below commission if available and not opening row
                    if (!isOpening && (r.commission !== '-' && r.commission != null && (r.commission || 0) > 0) && r.worker && r.worker !== '-') {
                        commStr = '<div class="flex flex-col items-end">' +
                            '<span>' + fmtNum(r.commission) + '</span>' +
                            '<span class="text-[8px] sm:text-[9px] text-slate-500">(' + r.worker + ')</span>' +
                            '</div>';
                    }
                    const rowClass = isOpening ? 'bg-slate-100 font-semibold' : 'hover:bg-slate-50';
                    
                    // Cash columns with user name
                    let cashCreditStr = (isCash && (r.credit || 0) > 0) ? fmtNum(r.credit) : '-';
                    if (isCash && (r.credit || 0) > 0 && r.userName) {
                        cashCreditStr = '<div class="flex flex-col items-end">' +
                            '<span>' + fmtNum(r.credit) + '</span>' +
                            '<span class="text-[8px] sm:text-[9px] text-slate-500">(' + r.userName + ')</span>' +
                            '</div>';
                    }
                    const cashTotalStr = (isCash && r.total != null) ? fmtNum(r.total) : '-';
                    
                    // Bank columns with bank details and user name
                    let bankCreditStr = (isBank && (r.credit || 0) > 0) ? fmtNum(r.credit) : '-';
                    if (isBank && (r.credit || 0) > 0 && (r.bankNameOnly || r.bankAccountTitle || r.bankAccountNumber)) {
                        bankCreditStr = '<div class="flex flex-col items-end">' +
                            '<span>' + fmtNum(r.credit) + '</span>';
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
                        bankCreditStr = '<div class="flex flex-col items-end">' +
                            '<span>' + fmtNum(r.credit) + '</span>' +
                            '<span class="text-[8px] sm:text-[9px] text-slate-500">(' + r.userName + ')</span>' +
                            '</div>';
                    }
                    const bankTotalStr = (isBank && r.total != null) ? fmtNum(r.total) : '-';
                    
                    // Format date & time: Date first, then time on same line or below
                    let dateTimeStr = '-';
                    if (r.isOpening) {
                        // Extract date and time from dateTime field (format: "d/m/y Time h:i A")
                        const datePart = r.date || (r.dateTime ? r.dateTime.split(' Time ')[0] : '-');
                        let timePart = '12:00AM'; // Default fallback
                        if (r.dateTime && r.dateTime.includes(' Time ')) {
                            timePart = r.dateTime.split(' Time ')[1] || '12:00AM';
                        }
                        dateTimeStr = '<span class="font-bold">' + datePart + '</span> <span class="text-[8px] sm:text-[9px]">Time ' + timePart + '</span>';
                    } else if (r.startTime && r.endTime && r.totalTime && r.startTime !== '-' && r.endTime !== '-' && r.totalTime !== '-') {
                        dateTimeStr = '<span class="font-bold">' + (r.date || (r.dateTime ? r.dateTime.split(' time ')[0] : '-')) + '</span><br>' + 
                                     '<span class="text-[8px] sm:text-[9px]">' + r.startTime + ' - ' + r.endTime + '</span><br>' +
                                     '<span class="text-[8px] sm:text-[9px] font-semibold inline-flex items-center gap-0.5"><svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' + r.totalTime + '</span>';
                        // Add worker name below total time in parentheses
                        if (r.worker && r.worker !== '-') {
                            dateTimeStr += '<br><span class="text-[8px] sm:text-[9px] text-slate-600">(' + r.worker + ')</span>';
                        }
                    } else {
                        const datePart = r.date || (r.dateTime ? r.dateTime.split(' time ')[0] : '-');
                        const timePart = r.dateTime && r.dateTime.includes(' time ') ? r.dateTime.split(' time ')[1] : '';
                        dateTimeStr = '<span class="font-bold">' + datePart + '</span>' + (timePart ? ' <span class="text-[8px] sm:text-[9px]">Time ' + timePart + '</span>' : '');
                        // Add worker name if available in parentheses
                        if (r.worker && r.worker !== '-') {
                            dateTimeStr += '<br><span class="text-[8px] sm:text-[9px] text-slate-600">(' + r.worker + ')</span>';
                        }
                    }
                    
                    // Format vehicle with customer name, mobile, and user name
                    let vehicleStr = (r.vehicle || '-');
                    if (!r.isOpening && (r.customerName || r.mobile || r.userName)) {
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
                    
                    html += '<tr class="' + rowClass + '">' +
                        '<td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-[9px] sm:text-[10px] md:text-sm text-slate-700 whitespace-normal leading-tight">' + dateTimeStr + '</td>' +
                        '<td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-[9px] sm:text-[10px] md:text-sm text-slate-900 whitespace-normal leading-tight">' + vehicleStr + '</td>' +
                        '<td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-[9px] sm:text-[10px] md:text-sm text-right ' + ((r.debit || 0) > 0 ? 'font-bold text-amber-700' : 'text-slate-500') + ' whitespace-nowrap">' + expenseStr + '</td>' +
                        '<td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-[9px] sm:text-[10px] md:text-sm text-right ' + (isCash && (r.credit || 0) > 0 ? 'font-bold text-indigo-600' : 'text-slate-500') + ' whitespace-normal leading-tight">' + cashCreditStr + '</td>' +
                        '<td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-[9px] sm:text-[10px] md:text-sm text-right ' + (isCash && r.total != null ? 'font-bold text-indigo-700' : 'text-slate-500') + ' whitespace-nowrap">' + cashTotalStr + '</td>' +
                        '<td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-[9px] sm:text-[10px] md:text-sm text-right ' + (isBank && (r.credit || 0) > 0 ? 'font-bold text-purple-600' : 'text-slate-500') + ' whitespace-normal leading-tight">' + bankCreditStr + '</td>' +
                        '<td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-[9px] sm:text-[10px] md:text-sm text-right ' + (isBank && r.total != null ? 'font-bold text-purple-700' : 'text-slate-500') + ' whitespace-nowrap">' + bankTotalStr + '</td>' +
                        '<td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-[9px] sm:text-[10px] md:text-sm text-right ' + ((r.commission !== '-' && (r.commission || 0) > 0) ? 'font-bold text-emerald-600' : 'text-slate-500') + ' whitespace-normal leading-tight">' + commStr + '</td>' +
                        '<td class="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 md:py-3 text-[9px] sm:text-[10px] md:text-sm text-right font-bold text-indigo-600 whitespace-nowrap">' + gTotalStr + '</td>' +
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
                const dateFrom = reportDateFrom.value;
                const dateTo = reportDateTo.value;
                if (!dateFrom || !dateTo) return;
                showLoading();
                
                // Fetch both cash and bank data, then merge
                const baseParams = {
                    date_from: dateFrom,
                    date_to: dateTo,
                    customer: filterCustomer.value || '',
                    worker: filterWorker.value || ''
                };
                
                Promise.all([
                    fetch(buildUrl(routes.data, {...baseParams, payment: 'cash'})).then(r => r.json()),
                    fetch(buildUrl(routes.data, {...baseParams, payment: 'bank'})).then(r => r.json())
                ])
                    .then(function([cashData, bankData]) {
                        if (cashData.success || bankData.success) {
                            // Get opening row from cash data (or bank if cash has no data)
                            const openingRow = (cashData.rows || []).find(r => r.isOpening) || (bankData.rows || []).find(r => r.isOpening);
                            
                            // Merge all non-opening rows from both, mark payment type
                            const cashRows = (cashData.rows || []).filter(r => !r.isOpening).map(r => ({...r, paymentType: 'cash'}));
                            const bankRows = (bankData.rows || []).filter(r => !r.isOpening).map(r => ({...r, paymentType: 'bank'}));
                            const mergedRows = openingRow ? [openingRow, ...cashRows, ...bankRows] : [...cashRows, ...bankRows];
                            
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
                                sumGtotal: ((cashTotals.cashOnHand || 0) + (bankTotals.cashOnHand || 0)) - ((cashTotals.totalCommission || 0) + (bankTotals.totalCommission || 0))
                            };
                            
                            // Merge customers and workers (unique)
                            const allCustomers = [...(cashData.customers || []), ...(bankData.customers || [])];
                            const uniqueCustomers = Array.from(new Map(allCustomers.map(c => [c.value, c])).values());
                            
                            const allWorkers = [...(cashData.workers || []), ...(bankData.workers || [])];
                            const uniqueWorkers = Array.from(new Map(allWorkers.map(w => [w.value, w])).values());
                            
                            renderReport({
                                success: true,
                                rows: mergedRows,
                                totals: mergedTotals,
                                cashTotals: cashTotals,
                                bankTotals: bankTotals,
                                customers: uniqueCustomers,
                                workers: uniqueWorkers
                            });
                            // Reload cash account balance after report is rendered
                            loadCashAccountBalance();
                        } else {
                            lastReportRows = [];
                            lastReportTotals = {};
                            renderReport({ rows: [], totals: {}, customers: [], workers: [] });
                            loadCashAccountBalance();
                        }
                    })
                    .catch(function() {
                        lastReportRows = [];
                        lastReportTotals = {};
                        renderReport({ rows: [], totals: {}, customers: [], workers: [] });
                        loadCashAccountBalance();
                    });
            }

            filterCustomer.addEventListener('change', loadReport);
            filterWorker.addEventListener('change', loadReport);

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
                    worker: filterWorker.value || ''
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

            reportDateFrom.addEventListener('change', loadReport);
            reportDateTo.addEventListener('change', loadReport);


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

            // Load branch users
            function loadBranchUsers() {
                fetch(routes.branchUsers)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        transferToUser.innerHTML = '<option value="">Select User</option>';
                        if (data.success && data.users) {
                            data.users.forEach(function(user) {
                                var option = document.createElement('option');
                                option.value = user.id;
                                option.textContent = user.name + (user.email ? ' (' + user.email + ')' : '');
                                transferToUser.appendChild(option);
                            });
                        }
                    })
                    .catch(function() {
                        transferToUser.innerHTML = '<option value="">Select User</option>';
                    });
            }

            function openCashTransferModal() {
                loadCashBalance();
                loadBranchUsers();
                transferAmount.value = '';
                transferToUser.value = '';
                transferNote.value = '';
                cashTransferModal.style.display = 'flex';
            }

            function closeCashTransferModal() {
                cashTransferModal.style.display = 'none';
                transferAmount.value = '';
                transferToUser.value = '';
                transferNote.value = '';
            }

            // Transfer cash
            btnTransferCash.addEventListener('click', function() {
                var amount = parseFloat(transferAmount.value);
                var userId = transferToUser.value;
                var note = transferNote.value.trim();

                if (!amount || amount <= 0) {
                    alert('Please enter a valid amount');
                    return;
                }

                if (!userId) {
                    alert('Please select a user to transfer to');
                    return;
                }

                var maxAmount = parseFloat(transferAmount.max) || 0;
                if (amount > maxAmount) {
                    alert('Amount cannot exceed available cash balance');
                    return;
                }

                btnTransferCash.disabled = true;
                btnTransferCash.textContent = 'Transferring...';

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
                            alert('Rs.' + amount + ' transferred successfully!');
                            closeCashTransferModal();
                            loadCashBalance();
                            loadCashAccountBalance(); // Update the card balance
                            // Reload report if it's loaded
                            if (reportDate.value) {
                                loadReport();
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

            // Load bank accounts for transfer
            function loadBankAccountsForTransfer() {
                fetch(routes.bankAccounts)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        transferToBankAccount.innerHTML = '<option value="">Select Bank Account</option>';
                        if (data.success && data.bankAccounts) {
                            data.bankAccounts.forEach(function(account) {
                                var option = document.createElement('option');
                                option.value = account.id;
                                option.textContent = account.displayLabel || (account.bankName + ' - ' + (account.accountTitle || account.accountNumber || ''));
                                transferToBankAccount.appendChild(option);
                            });
                        }
                    })
                    .catch(function() {
                        transferToBankAccount.innerHTML = '<option value="">Select Bank Account</option>';
                    });
            }

            function openBankTransferModal() {
                loadBankBalanceForModal();
                loadBankAccountsForTransfer();
                bankTransferAmount.value = '';
                transferToBankAccount.value = '';
                bankTransferNote.value = '';
                bankTransferModal.style.display = 'flex';
            }

            function closeBankTransferModal() {
                bankTransferModal.style.display = 'none';
                bankTransferAmount.value = '';
                transferToBankAccount.value = '';
                bankTransferNote.value = '';
            }

            // Load bank account balance for modal
            function loadBankBalanceForModal() {
                fetch(routes.bankAccounts)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success && data.bankAccounts) {
                            var totalBalance = data.bankAccounts.reduce(function(sum, acc) {
                                return sum + (parseFloat(acc.balance) || 0);
                            }, 0);
                            var balance = Math.round(totalBalance);
                            modalBankBalance.textContent = 'Rs.' + balance;
                            // For bank transfer, we still need cash balance to validate transfer amount
                            loadCashBalanceForBankTransfer();
                        } else {
                            modalBankBalance.textContent = 'Rs.0';
                            bankTransferAmount.max = 0;
                        }
                    })
                    .catch(function() {
                        modalBankBalance.textContent = 'Rs.0';
                        bankTransferAmount.max = 0;
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
                    alert('Amount cannot exceed available cash balance');
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
                            var selectedAccount = Array.from(transferToBankAccount.options).find(opt => opt.value === bankAccountId);
                            var accountName = selectedAccount ? selectedAccount.textContent : 'Bank Account';
                            alert('Rs.' + amount + ' transferred to ' + accountName + ' successfully!');
                            closeBankTransferModal();
                            loadBankBalanceForModal();
                            loadBankBalance();
                            loadCashAccountBalance(); // Update the card balance after bank transfer
                            // Reload report if it's loaded
                            if (reportDate.value) {
                                loadReport();
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

            cashOnHandCard.addEventListener('click', openCashTransferModal);
            bankBalanceCard.addEventListener('click', openBankTransferModal);
            cashTransferModalClose.addEventListener('click', closeCashTransferModal);
            cashTransferModal.addEventListener('click', function(e) {
                if (e.target === cashTransferModal) closeCashTransferModal();
            });
            bankTransferModalClose.addEventListener('click', closeBankTransferModal);
            bankTransferModal.addEventListener('click', function(e) {
                if (e.target === bankTransferModal) closeBankTransferModal();
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

            // Load bank balance and cash account balance on page load
            loadBankBalance();
            loadCashAccountBalance();
            
            if (reportDateFrom.value && reportDateTo.value) {
                loadReport();
            } else {
                // Load balances even if no report date
                loadBankBalance();
                loadCashAccountBalance();
            }
        })();
    </script>
</body>
</html>
