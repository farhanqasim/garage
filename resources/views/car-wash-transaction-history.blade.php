<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - Elite Car Wash</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body class="bg-slate-50 min-h-screen">
    <div id="root" class="min-h-screen">
        <!-- Header -->
        <header class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white p-4 sm:p-6 shadow-2xl">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4">
                    <div>
                        <h1 class="text-xl sm:text-2xl md:text-3xl font-black uppercase tracking-tighter mb-1">Transaction History</h1>
                        <p class="text-xs sm:text-sm opacity-90">{{ $branchName }} • {{ $userName }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <button type="button" id="btnDownloadPdf" class="px-3 sm:px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-xs sm:text-sm font-black uppercase transition-colors backdrop-blur-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="hidden sm:inline">Download PDF</span>
                            <span class="sm:hidden">PDF</span>
                        </button>
                        <button type="button" id="btnPrint" class="px-3 sm:px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-xs sm:text-sm font-black uppercase transition-colors backdrop-blur-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span class="hidden sm:inline">Print</span>
                            <span class="sm:hidden">Print</span>
                        </button>
                        <a href="{{ route('car.wash') }}" class="px-3 sm:px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-xs sm:text-sm font-black uppercase transition-colors backdrop-blur-sm">← Car Wash</a>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto p-3 sm:p-4 md:p-6">
            <!-- Filters -->
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl border-2 border-slate-200 p-4 sm:p-6 mb-4 sm:mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs sm:text-sm font-black text-slate-700 uppercase mb-2">Date Range</label>
                        <div class="flex items-center gap-2">
                            <input type="date" id="filterDateFrom" value="{{ now()->subDays(30)->format('Y-m-d') }}"
                                class="flex-1 px-2 sm:px-3 py-2 border-2 border-slate-300 rounded-lg text-slate-900 font-bold focus:border-blue-500 focus:outline-none text-xs sm:text-sm" />
                            <span class="text-xs sm:text-sm font-bold text-slate-600 whitespace-nowrap">To</span>
                            <input type="date" id="filterDateTo" value="{{ now()->format('Y-m-d') }}"
                                class="flex-1 px-2 sm:px-3 py-2 border-2 border-slate-300 rounded-lg text-slate-900 font-bold focus:border-blue-500 focus:outline-none text-xs sm:text-sm" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-black text-slate-700 uppercase mb-2">User</label>
                        <select id="filterUser" class="w-full px-2 sm:px-3 py-2 border-2 border-slate-300 rounded-lg text-slate-900 font-bold focus:border-blue-500 focus:outline-none text-xs sm:text-sm">
                            <option value="">All Users</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-black text-slate-700 uppercase mb-2">Type</label>
                        <select id="filterType" class="w-full px-2 sm:px-3 py-2 border-2 border-slate-300 rounded-lg text-slate-900 font-bold focus:border-blue-500 focus:outline-none text-xs sm:text-sm">
                            <option value="all">All Types</option>
                            <option value="cash_transfer">Cash Transfer</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 mt-4">
                    <button type="button" id="btnApplyFilters"
                        class="flex-1 sm:flex-none px-4 sm:px-5 py-2 sm:py-2.5 bg-gradient-to-br from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg text-xs sm:text-sm font-bold uppercase transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span class="hidden sm:inline">Apply Filters</span>
                        <span class="sm:hidden">Apply</span>
                    </button>
                    <button type="button" id="btnResetFilters"
                        class="flex-1 sm:flex-none px-4 sm:px-5 py-2 sm:py-2.5 bg-gradient-to-br from-slate-500 to-slate-600 hover:from-slate-600 hover:to-slate-700 text-white rounded-lg text-xs sm:text-sm font-bold uppercase transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span class="hidden sm:inline">Reset</span>
                        <span class="sm:hidden">Reset</span>
                    </button>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl sm:rounded-2xl shadow-xl border-2 border-blue-200 p-4 sm:p-6 mb-4 sm:mb-6">
                <div class="grid grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <p class="text-xs sm:text-sm font-bold text-blue-700 uppercase mb-1">Total Transactions</p>
                        <p id="totalTransactions" class="text-2xl sm:text-3xl font-black text-blue-900">0</p>
                    </div>
                    <div>
                        <p class="text-xs sm:text-sm font-bold text-blue-700 uppercase mb-1">Total Amount</p>
                        <p id="totalAmount" class="text-2xl sm:text-3xl font-black text-blue-900">Rs.0</p>
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div id="transactionTableContainer" class="bg-white rounded-xl sm:rounded-2xl shadow-xl border-2 border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full" id="transactionsTable">
                        <thead class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                            <tr>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-black uppercase tracking-wider">Date & Time</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-black uppercase tracking-wider">Type</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-black uppercase tracking-wider">Credit/Debit</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-black uppercase tracking-wider">From</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-black uppercase tracking-wider">To</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-black uppercase tracking-wider">Amount</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-black uppercase tracking-wider hidden md:table-cell">Status/Note</th>
                            </tr>
                        </thead>
                        <tbody id="transactionsTableBody" class="bg-white divide-y divide-slate-200">
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                        </svg>
                                        <p class="text-xs sm:text-sm font-bold">No transactions found. Apply filters to load transactions.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        const API_ROUTES = {
            transactionHistory: '{{ route("car-wash.transaction-history.get") }}',
        };
        
        console.log('API Route:', API_ROUTES.transactionHistory);

        let currentFilters = {
            from: document.getElementById('filterDateFrom').value,
            to: document.getElementById('filterDateTo').value,
            user_id: '',
            type: 'all',
        };

        // Load transactions on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadTransactions();
        });

        // Apply filters button
        document.getElementById('btnApplyFilters').addEventListener('click', function() {
            currentFilters.from = document.getElementById('filterDateFrom').value;
            currentFilters.to = document.getElementById('filterDateTo').value;
            currentFilters.user_id = document.getElementById('filterUser').value;
            currentFilters.type = document.getElementById('filterType').value;
            loadTransactions();
        });

        // Reset filters button
        document.getElementById('btnResetFilters').addEventListener('click', function() {
            document.getElementById('filterDateFrom').value = '{{ now()->subDays(30)->format("Y-m-d") }}';
            document.getElementById('filterDateTo').value = '{{ now()->format("Y-m-d") }}';
            document.getElementById('filterUser').value = '';
            document.getElementById('filterType').value = 'all';
            currentFilters = {
                from: document.getElementById('filterDateFrom').value,
                to: document.getElementById('filterDateTo').value,
                user_id: '',
                type: 'all',
            };
            loadTransactions();
        });

        function loadTransactions() {
            const tbody = document.getElementById('transactionsTableBody');
            tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center"><div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div></td></tr>';

            const params = new URLSearchParams();
            params.append('from', currentFilters.from);
            params.append('to', currentFilters.to);
            if (currentFilters.user_id) {
                params.append('user_id', currentFilters.user_id);
            }
            if (currentFilters.type && currentFilters.type !== 'all') {
                params.append('type', currentFilters.type);
            }

            fetch(`${API_ROUTES.transactionHistory}?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Response is not JSON');
                }
                return response.json();
            })
            .then(data => {
                console.log('Transaction data received:', data);
                if (data.success && data.transactions) {
                    displayTransactions(data.transactions);
                } else {
                    console.error('Invalid response format:', data);
                    tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-red-500 font-bold">Error: Invalid response format</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading transactions:', error);
                tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-red-500 font-bold">Error loading transactions: ' + error.message + '</td></tr>';
            });
        }

        function displayTransactions(transactions) {
            const tbody = document.getElementById('transactionsTableBody');
            
            if (transactions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-slate-500 font-bold">No transactions found for the selected filters.</td></tr>';
                document.getElementById('totalTransactions').textContent = '0';
                document.getElementById('totalAmount').textContent = 'Rs.0';
                return;
            }

            let totalAmount = 0;

            tbody.innerHTML = transactions.map(transaction => {
                const date = new Date(transaction.date).toLocaleString('en-GB');
                const type = transaction.type === 'cash_transfer' ? 'Cash Transfer' : 
                            transaction.type === 'bank_transfer' ? 'Bank Transfer' : 
                            transaction.type === 'bank_to_bank' ? 'Bank to Bank' : 'Unknown';
                const typeColor = transaction.type === 'cash_transfer' ? 'text-green-600' : 
                                 transaction.type === 'bank_transfer' ? 'text-blue-600' : 
                                 'text-purple-600';
                
                // Credit/Debit display
                let creditDebit = '-';
                let creditDebitColor = 'text-slate-600';
                if (transaction.direction) {
                    if (transaction.direction === 'credit') {
                        creditDebit = 'Credit';
                        creditDebitColor = 'text-green-600 font-bold';
                    } else if (transaction.direction === 'debit') {
                        creditDebit = 'Debit';
                        creditDebitColor = 'text-red-600 font-bold';
                    }
                } else if (transaction.type === 'cash_transfer') {
                    // For cash transfers, from_user is debit, to_user is credit
                    creditDebit = 'Transfer';
                    creditDebitColor = 'text-blue-600';
                }
                
                const amount = parseFloat(transaction.amount).toFixed(2);
                totalAmount += parseFloat(transaction.amount);
                
                const statusNote = transaction.status || transaction.note || transaction.description || '-';
                const statusColor = transaction.status === 'completed' ? 'text-green-600' : 
                                   transaction.status === 'pending' ? 'text-yellow-600' : 
                                   transaction.status === 'failed' ? 'text-red-600' : 'text-slate-600';

                return `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm font-bold text-slate-900">${date}</td>
                        <td class="px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm font-bold ${typeColor}">${type}</td>
                        <td class="px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm font-bold ${creditDebitColor}">${creditDebit}</td>
                        <td class="px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm font-bold text-slate-900">${transaction.from_user}</td>
                        <td class="px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm font-bold text-slate-900">${transaction.to_user}</td>
                        <td class="px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm font-black text-blue-600">Rs.${amount}</td>
                        <td class="px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm ${statusColor} hidden md:table-cell">${statusNote}</td>
                    </tr>
                `;
            }).join('');

            document.getElementById('totalTransactions').textContent = transactions.length;
            document.getElementById('totalAmount').textContent = `Rs.${totalAmount.toFixed(2)}`;
        }

        // Print functionality
        document.getElementById('btnPrint').addEventListener('click', function() {
            const printWindow = window.open('', '_blank');
            const table = document.getElementById('transactionsTable');
            
            if (!table || table.rows.length <= 1) {
                alert('No transactions to print. Please load transactions first.');
                return;
            }

            const printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Transaction History - Print</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        h1 { text-align: center; font-size: 24px; margin-bottom: 10px; }
                        .header-info { text-align: center; margin-bottom: 20px; color: #666; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th { background-color: #4f46e5; color: white; padding: 10px; text-align: left; font-weight: bold; }
                        td { padding: 8px; border-bottom: 1px solid #ddd; }
                        tr:nth-child(even) { background-color: #f9fafb; }
                        .summary { margin-top: 20px; padding: 15px; background-color: #f3f4f6; border-radius: 8px; }
                        @media print {
                            body { padding: 10px; }
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <h1>Transaction History</h1>
                    <div class="header-info">
                        <p><strong>{{ $branchName }}</strong> • {{ $userName }}</p>
                        <p>From: ${document.getElementById('filterDateFrom').value} To: ${document.getElementById('filterDateTo').value}</p>
                    </div>
                    ${table.outerHTML}
                    <div class="summary">
                        <p><strong>Total Transactions:</strong> ${document.getElementById('totalTransactions').textContent}</p>
                        <p><strong>Total Amount:</strong> ${document.getElementById('totalAmount').textContent}</p>
                    </div>
                </body>
                </html>
            `;

            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        });

        // PDF Download functionality
        document.getElementById('btnDownloadPdf').addEventListener('click', async function() {
            const btn = document.getElementById('btnDownloadPdf');
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>';

            try {
                if (typeof window.jspdf === 'undefined') {
                    throw new Error('jsPDF library not loaded');
                }

                const { jsPDF } = window.jspdf;
                const table = document.getElementById('transactionsTable');
                
                if (!table || table.rows.length <= 1) {
                    alert('No transactions to download. Please load transactions first.');
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                    return;
                }

                // Create PDF in landscape mode
                const pdf = new jsPDF('landscape', 'mm', 'a4');
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();
                const margin = 10;
                const maxWidth = pageWidth - (margin * 2);

                // Add header
                pdf.setFontSize(18);
                pdf.setFont('helvetica', 'bold');
                pdf.text('Transaction History', margin, 15);
                
                pdf.setFontSize(10);
                pdf.setFont('helvetica', 'normal');
                pdf.text(`{{ $branchName }} • {{ $userName }}`, margin, 22);
                
                pdf.setFontSize(8);
                pdf.text(`From: ${document.getElementById('filterDateFrom').value} To: ${document.getElementById('filterDateTo').value}`, margin, 28);
                pdf.text(`Total Transactions: ${document.getElementById('totalTransactions').textContent} | Total Amount: ${document.getElementById('totalAmount').textContent}`, margin, 33);

                // Table data
                const tableData = [];
                const headers = ['Date', 'Type', 'Credit/Debit', 'From', 'To', 'Amount', 'Status/Note'];
                tableData.push(headers);

                // Get all rows except header
                for (let i = 1; i < table.rows.length; i++) {
                    const row = table.rows[i];
                    const rowData = [];
                    for (let j = 0; j < row.cells.length; j++) {
                        let cellText = row.cells[j].textContent.trim();
                        // Remove currency symbols and format
                        if (j === 4) { // Amount column
                            cellText = cellText.replace('Rs.', '').trim();
                        }
                        rowData.push(cellText);
                    }
                    tableData.push(rowData);
                }

                // Add table to PDF
                let yPos = 40;
                const rowHeight = 7;
                const colWidths = [30, 25, 20, 35, 35, 20, 40]; // Adjusted for landscape with Credit/Debit column

                pdf.setFontSize(7);
                pdf.setFont('helvetica', 'bold');
                
                // Header row
                let xPos = margin;
                headers.forEach((header, index) => {
                    pdf.text(header, xPos, yPos);
                    xPos += colWidths[index];
                });
                yPos += rowHeight;

                // Draw line under header
                pdf.setDrawColor(200, 200, 200);
                pdf.line(margin, yPos - 2, pageWidth - margin, yPos - 2);

                // Data rows
                pdf.setFont('helvetica', 'normal');
                tableData.slice(1).forEach((row, rowIndex) => {
                    if (yPos > pageHeight - 15) {
                        pdf.addPage();
                        yPos = 15;
                    }
                    
                    xPos = margin;
                    row.forEach((cell, colIndex) => {
                        let cellText = cell;
                        // Truncate long text
                        if (cellText.length > 25) {
                            cellText = cellText.substring(0, 22) + '...';
                        }
                        pdf.text(cellText, xPos, yPos);
                        xPos += colWidths[colIndex];
                    });
                    yPos += rowHeight;
                });

                // Save PDF
                const fileName = `Transaction_History_${document.getElementById('filterDateFrom').value}_to_${document.getElementById('filterDateTo').value}.pdf`;
                pdf.save(fileName);

            } catch (error) {
                console.error('Error generating PDF:', error);
                alert('Error generating PDF: ' + error.message + '. Please ensure transactions are loaded first.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        });
    </script>
</body>
</html>
