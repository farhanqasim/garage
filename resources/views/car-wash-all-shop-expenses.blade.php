<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Shop Expenses - Elite Car Wash</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">
    <div id="root" class="min-h-screen">
        <!-- Header -->
        <header class="bg-gradient-to-r from-red-600 via-pink-600 to-rose-600 text-white p-4 sm:p-6 shadow-2xl">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4">
                    <div>
                        <h1 class="text-xl sm:text-2xl md:text-3xl font-black uppercase tracking-tighter mb-1">All Shop Expenses</h1>
                        <p class="text-xs sm:text-sm opacity-90">{{ $branchName }} • {{ $userName }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <button type="button" id="btnAddExpense" class="px-3 sm:px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-xs sm:text-sm font-black uppercase transition-colors backdrop-blur-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="hidden sm:inline">Add Expense</span>
                            <span class="sm:hidden">Add</span>
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
                                class="flex-1 px-2 sm:px-3 py-2 border-2 border-slate-300 rounded-lg text-slate-900 font-bold focus:border-red-500 focus:outline-none text-xs sm:text-sm" />
                            <span class="text-xs sm:text-sm font-bold text-slate-600 whitespace-nowrap">To</span>
                            <input type="date" id="filterDateTo" value="{{ now()->format('Y-m-d') }}"
                                class="flex-1 px-2 sm:px-3 py-2 border-2 border-slate-300 rounded-lg text-slate-900 font-bold focus:border-red-500 focus:outline-none text-xs sm:text-sm" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-black text-slate-700 uppercase mb-2">User</label>
                        <select id="filterUser" class="w-full px-2 sm:px-3 py-2 border-2 border-slate-300 rounded-lg text-slate-900 font-bold focus:border-red-500 focus:outline-none text-xs sm:text-sm">
                            <option value="">All Users</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-black text-slate-700 uppercase mb-2">Category</label>
                        <select id="filterCategory" class="w-full px-2 sm:px-3 py-2 border-2 border-slate-300 rounded-lg text-slate-900 font-bold focus:border-red-500 focus:outline-none text-xs sm:text-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 mt-4">
                    <button type="button" id="btnApplyFilters"
                        class="flex-1 sm:flex-none px-4 sm:px-5 py-2 sm:py-2.5 bg-gradient-to-br from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg text-xs sm:text-sm font-bold uppercase transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg">
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
            <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-xl sm:rounded-2xl shadow-xl border-2 border-red-200 p-4 sm:p-6 mb-4 sm:mb-6">
                <div class="grid grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <p class="text-xs sm:text-sm font-bold text-red-700 uppercase mb-1">Total Expenses</p>
                        <p id="totalExpenses" class="text-2xl sm:text-3xl font-black text-red-900">Rs.0</p>
                    </div>
                    <div>
                        <p class="text-xs sm:text-sm font-bold text-red-700 uppercase mb-1">Total Records</p>
                        <p id="totalRecords" class="text-2xl sm:text-3xl font-black text-red-900">0</p>
                    </div>
                </div>
            </div>

            <!-- Expenses Table -->
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl border-2 border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-red-600 to-pink-600 text-white">
                            <tr>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-black uppercase tracking-wider">Date</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-black uppercase tracking-wider">User</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-black uppercase tracking-wider">Category</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-black uppercase tracking-wider">Amount</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-black uppercase tracking-wider hidden md:table-cell">Notes</th>
                                <th class="px-2 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-black uppercase tracking-wider hidden lg:table-cell">Created At</th>
                            </tr>
                        </thead>
                        <tbody id="expensesTableBody" class="bg-white divide-y divide-slate-200">
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-xs sm:text-sm font-bold">No expenses found. Apply filters to load expenses.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Add Expense Modal -->
        <div id="addExpenseModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-gradient-to-r from-red-600 to-pink-600 text-white p-4 sm:p-6 rounded-t-xl sm:rounded-t-2xl flex items-center justify-between">
                    <h2 class="text-lg sm:text-xl font-black uppercase">Add Shop Expense</h2>
                    <button type="button" id="btnCloseModal" class="text-white hover:text-red-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form id="addExpenseForm" class="p-4 sm:p-6 space-y-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-black text-slate-700 uppercase mb-2">Expense Date *</label>
                        <input type="date" id="expenseDate" required
                            value="{{ now()->format('Y-m-d') }}"
                            class="w-full px-3 py-2 border-2 border-slate-300 rounded-lg text-slate-900 font-bold focus:border-red-500 focus:outline-none text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-black text-slate-700 uppercase mb-2">Category *</label>
                        <input type="text" id="expenseCategory" required placeholder="e.g., Tea, Food, Supplies"
                            class="w-full px-3 py-2 border-2 border-slate-300 rounded-lg text-slate-900 font-bold focus:border-red-500 focus:outline-none text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-black text-slate-700 uppercase mb-2">Amount (Rs.) *</label>
                        <input type="number" id="expenseAmount" required min="0" step="0.01" placeholder="0.00"
                            class="w-full px-3 py-2 border-2 border-slate-300 rounded-lg text-slate-900 font-bold focus:border-red-500 focus:outline-none text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-black text-slate-700 uppercase mb-2">Notes (Optional)</label>
                        <textarea id="expenseNotes" rows="3" placeholder="Optional notes about this expense"
                            class="w-full px-3 py-2 border-2 border-slate-300 rounded-lg text-slate-900 font-bold focus:border-red-500 focus:outline-none text-sm resize-none"></textarea>
                    </div>
                    <div class="flex flex-wrap gap-3 pt-4">
                        <button type="submit" id="btnSubmitExpense"
                            class="flex-1 px-4 py-2.5 bg-gradient-to-br from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg text-sm font-bold uppercase transition-all duration-200 shadow-md hover:shadow-lg">
                            Add Expense
                        </button>
                        <button type="button" id="btnCancelExpense"
                            class="flex-1 px-4 py-2.5 bg-gradient-to-br from-slate-500 to-slate-600 hover:from-slate-600 hover:to-slate-700 text-white rounded-lg text-sm font-bold uppercase transition-all duration-200 shadow-md hover:shadow-lg">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const API_ROUTES = {
            shopExpenses: '{{ url("/car-wash/shop-expenses") }}',
            shopExpensesStore: '{{ url("/car-wash/shop-expenses") }}',
        };

        let currentFilters = {
            from: document.getElementById('filterDateFrom').value,
            to: document.getElementById('filterDateTo').value,
            user_id: '',
            category: '',
        };

        // Load expenses on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadExpenses();
        });

        // Apply filters button
        document.getElementById('btnApplyFilters').addEventListener('click', function() {
            currentFilters.from = document.getElementById('filterDateFrom').value;
            currentFilters.to = document.getElementById('filterDateTo').value;
            currentFilters.user_id = document.getElementById('filterUser').value;
            currentFilters.category = document.getElementById('filterCategory').value;
            loadExpenses();
        });

        // Reset filters button
        document.getElementById('btnResetFilters').addEventListener('click', function() {
            document.getElementById('filterDateFrom').value = '{{ now()->subDays(30)->format("Y-m-d") }}';
            document.getElementById('filterDateTo').value = '{{ now()->format("Y-m-d") }}';
            document.getElementById('filterUser').value = '';
            document.getElementById('filterCategory').value = '';
            currentFilters = {
                from: document.getElementById('filterDateFrom').value,
                to: document.getElementById('filterDateTo').value,
                user_id: '',
                category: '',
            };
            loadExpenses();
        });

        function loadExpenses() {
            const tbody = document.getElementById('expensesTableBody');
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center"><div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div></div></td></tr>';

            const params = new URLSearchParams();
            params.append('from', currentFilters.from);
            params.append('to', currentFilters.to);
            if (currentFilters.user_id) {
                params.append('user_id', currentFilters.user_id);
            }
            if (currentFilters.category) {
                params.append('category', currentFilters.category);
            }

            fetch(`${API_ROUTES.shopExpenses}?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.expenses) {
                    displayExpenses(data.expenses, data.total);
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-red-500 font-bold">Error loading expenses</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-red-500 font-bold">Error loading expenses</td></tr>';
            });
        }

        function displayExpenses(expenses, total) {
            const tbody = document.getElementById('expensesTableBody');
            
            if (expenses.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-slate-500 font-bold">No expenses found for the selected filters.</td></tr>';
                document.getElementById('totalExpenses').textContent = 'Rs.0';
                document.getElementById('totalRecords').textContent = '0';
                return;
            }

            tbody.innerHTML = expenses.map(expense => {
                const date = new Date(expense.expense_date).toLocaleDateString('en-GB');
                const created = expense.created_at ? new Date(expense.created_at).toLocaleString('en-GB') : '-';
                const userName = expense.user_name || 'Unknown';
                const amount = parseFloat(expense.amount).toFixed(2);
                const notes = expense.notes || '-';

                return `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm font-bold text-slate-900">${date}</td>
                        <td class="px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm font-bold text-slate-900">${userName}</td>
                        <td class="px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm font-bold text-slate-900">${expense.category}</td>
                        <td class="px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm font-black text-red-600">Rs.${amount}</td>
                        <td class="px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm text-slate-600 hidden md:table-cell">${notes}</td>
                        <td class="px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm text-slate-500 hidden lg:table-cell">${created}</td>
                    </tr>
                `;
            }).join('');

            document.getElementById('totalExpenses').textContent = `Rs.${parseFloat(total).toFixed(2)}`;
            document.getElementById('totalRecords').textContent = expenses.length;
        }

        // Add Expense Modal
        const addExpenseModal = document.getElementById('addExpenseModal');
        const addExpenseForm = document.getElementById('addExpenseForm');
        const btnAddExpense = document.getElementById('btnAddExpense');
        const btnCloseModal = document.getElementById('btnCloseModal');
        const btnCancelExpense = document.getElementById('btnCancelExpense');

        btnAddExpense.addEventListener('click', function() {
            addExpenseModal.classList.remove('hidden');
        });

        btnCloseModal.addEventListener('click', function() {
            addExpenseModal.classList.add('hidden');
            addExpenseForm.reset();
        });

        btnCancelExpense.addEventListener('click', function() {
            addExpenseModal.classList.add('hidden');
            addExpenseForm.reset();
        });

        // Close modal on backdrop click
        addExpenseModal.addEventListener('click', function(e) {
            if (e.target === addExpenseModal) {
                addExpenseModal.classList.add('hidden');
                addExpenseForm.reset();
            }
        });

        // Submit expense form
        addExpenseForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const expenseDate = document.getElementById('expenseDate').value;
            const expenseCategory = document.getElementById('expenseCategory').value.trim();
            const expenseAmount = document.getElementById('expenseAmount').value;
            const expenseNotes = document.getElementById('expenseNotes').value.trim();

            if (!expenseDate || !expenseCategory || !expenseAmount || parseFloat(expenseAmount) <= 0) {
                alert('Please fill in all required fields with valid values.');
                return;
            }

            const submitBtn = document.getElementById('btnSubmitExpense');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Adding...';

            try {
                const response = await fetch(API_ROUTES.shopExpensesStore, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        expense_date: expenseDate,
                        category: expenseCategory,
                        amount: parseFloat(expenseAmount),
                        notes: expenseNotes || '',
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    alert('Expense added successfully!');
                    addExpenseModal.classList.add('hidden');
                    addExpenseForm.reset();
                    loadExpenses(); // Reload expenses list
                } else {
                    alert(data.message || 'Error adding expense. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error adding expense. Please try again.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Add Expense';
            }
        });
    </script>
</body>
</html>
