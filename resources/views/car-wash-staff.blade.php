<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - Elite Car Wash</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- React and ReactDOM -->
    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
                'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue',
                sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        /* Responsive: prevent horizontal overflow */
        html, body { overflow-x: hidden; }
        /* Touch-friendly tap targets on mobile */
        @media (max-width: 767px) {
            .touch-target { min-height: 44px; min-width: 44px; }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <div id="root"></div>

    <script type="text/babel">
        const { useState, useEffect } = React;

        // Normalize image URL: API may return full URL (asset) or raw path (e.g. workers/id_cards/xxx.jpg)
        const imageUrl = (path, fallback) => {
            if (!path) return fallback || null;
            if (typeof path !== 'string') return fallback || null;
            if (path.startsWith('http://') || path.startsWith('https://')) return path;
            return path.startsWith('/') ? path : '/' + path;
        };
        // Initial data from backend
        const initialWorkers = @json($workers).map(w => ({
            id: w.id,
            name: w.name,
            mobile: w.mobile,
            additionalMobiles: w.additional_mobiles ?? [],
            fatherName: w.father_name,
            fatherMobile: w.father_mobile,
            fatherAdditionalMobiles: w.father_additional_mobiles ?? [],
            location: w.location,
            commission: w.commission,
            idCardFront: imageUrl(w.id_card_front, w.idCardFront),
            idCardBack: imageUrl(w.id_card_back, w.idCardBack),
            fatherCardFront: imageUrl(w.father_card_front, w.fatherCardFront),
            fatherCardBack: imageUrl(w.father_card_back, w.fatherCardBack),
            status: w.status !== undefined ? w.status : true,
            bank_account_id: w.bank_account_id ?? null,
            bankAccount: w.bank_account ?? null,
            has_cash_account: w.has_cash_account ?? false,
            cash_balance: w.cash_balance ?? 0,
            bankName: w.bank_name ?? '',
            bankAccountTitle: w.bank_account_title ?? '',
            bankAccountNumber: w.bank_account_number ?? '',
            bankIban: w.bank_iban ?? '',
            dailyJobsCount: w.daily_jobs_count ?? 0,
            dailyCommission: w.daily_commission ?? 0,
            pendingCommission: w.pending_commission ?? w.pendingCommission ?? 0,
            totalEarned: w.total_earned ?? w.totalEarned ?? 0,
            totalPaid: w.total_paid ?? w.totalPaid ?? 0,
            paymentStatus: w.payment_status ?? w.paymentStatus ?? 'unpaid',
        }));
        const branchName = @json($branchName);
        const userName = @json($userName);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const paymentMethods = @json($paymentMethods ?? []);
        const bankAccounts = @json($bankAccounts ?? []);
        const userCashBalance = Number(@json($userCashBalance ?? 0));

        const API_ROUTES = {
            workers: {
                index: '{{ route("car-wash.workers.index") }}',
                store: '{{ route("car-wash.workers.store") }}',
                update: (id) => `/car-wash/workers/${id}`,
                updateBank: (id) => `/car-wash/workers/${id}/bank`,
                createCashAccount: (id) => `/car-wash/workers/${id}/cash-account`,
                destroy: (id) => `/car-wash/workers/${id}`,
            },
            payments: {
                store: '{{ route("car-wash.payments.store") }}',
            },
        };

        const mapWorkerFromApi = (w) => ({
            id: w.id,
            name: w.name,
            mobile: w.mobile,
            additionalMobiles: w.additional_mobiles ?? w.additionalMobiles ?? [],
            fatherName: w.father_name ?? w.fatherName,
            fatherMobile: w.father_mobile ?? w.fatherMobile,
            fatherAdditionalMobiles: w.father_additional_mobiles ?? w.fatherAdditionalMobiles ?? [],
            location: w.location,
            commission: w.commission,
            idCardFront: imageUrl(w.id_card_front, w.idCardFront),
            idCardBack: imageUrl(w.id_card_back, w.idCardBack),
            fatherCardFront: imageUrl(w.father_card_front, w.fatherCardFront),
            fatherCardBack: imageUrl(w.father_card_back, w.fatherCardBack),
            status: w.status !== undefined ? w.status : true,
            bank_account_id: w.bank_account_id ?? null,
            bankAccount: w.bank_account ?? w.bankAccount ?? null,
            has_cash_account: w.has_cash_account ?? !!w.workerCashAccount ?? false,
            cash_balance: w.cash_balance ?? (w.workerCashAccount && w.workerCashAccount.balance) ?? 0,
            bankName: w.bank_name ?? w.bankName ?? '',
            bankAccountTitle: w.bank_account_title ?? w.bankAccountTitle ?? '',
            bankAccountNumber: w.bank_account_number ?? w.bankAccountNumber ?? '',
            bankIban: w.bank_iban ?? w.bankIban ?? '',
            dailyJobsCount: w.daily_jobs_count ?? w.dailyJobsCount ?? 0,
            dailyCommission: w.daily_commission ?? w.dailyCommission ?? 0,
            pendingCommission: w.pending_commission ?? w.pendingCommission ?? 0,
            totalEarned: w.total_earned ?? w.totalEarned ?? 0,
            totalPaid: w.total_paid ?? w.totalPaid ?? 0,
            paymentStatus: w.payment_status ?? w.paymentStatus ?? 'unpaid',
        });

        function App() {
            const [workers, setWorkers] = useState(initialWorkers);
            const [selectedWorkerForEdit, setSelectedWorkerForEdit] = useState(null);
            const [showAddModal, setShowAddModal] = useState(false);

            // Open worker edit from URL (e.g. from attendance double-click: ?openWorker=123)
            useEffect(() => {
                const params = new URLSearchParams(window.location.search);
                const openWorkerId = params.get('openWorker');
                if (openWorkerId && workers.length > 0) {
                    const worker = workers.find(w => String(w.id) === String(openWorkerId));
                    if (worker) {
                        setSelectedWorkerForEdit(worker);
                        setShowAddModal(true);
                    }
                    // Remove param from URL so refresh doesn't re-open
                    const url = new URL(window.location.href);
                    url.searchParams.delete('openWorker');
                    window.history.replaceState({}, '', url.toString());
                }
            }, []);
            // Pay Commission modal
            const [workerToPay, setWorkerToPay] = useState(null);
            const [payAmount, setPayAmount] = useState('');
            const [payMethodId, setPayMethodId] = useState('');
            const [payBankAccountId, setPayBankAccountId] = useState('');
            const [paySubmitting, setPaySubmitting] = useState(false);
            // Add worker bank account (when paying by bank and worker has no bank)
            const [workerBankAccountId, setWorkerBankAccountId] = useState('');
            const [workerBankName, setWorkerBankName] = useState('');
            const [workerBankTitle, setWorkerBankTitle] = useState('');
            const [workerBankNumber, setWorkerBankNumber] = useState('');
            const [workerBankSaving, setWorkerBankSaving] = useState(false);
            const [workerCashCreating, setWorkerCashCreating] = useState(false);
            // Preview URLs for newly selected files (object URLs); cleared on Cancel or modal close
            const [filePreviews, setFilePreviews] = useState({
                idCardFront: null,
                idCardBack: null,
                fatherCardFront: null,
                fatherCardBack: null,
            });

            const clearFilePreview = (field) => {
                setFilePreviews(prev => {
                    const url = prev[field];
                    if (url) URL.revokeObjectURL(url);
                    return { ...prev, [field]: null };
                });
                const idMap = { idCardFront: 'workerIdCardFront', idCardBack: 'workerIdCardBack', fatherCardFront: 'workerFatherCardFront', fatherCardBack: 'workerFatherCardBack' };
                const el = document.getElementById(idMap[field]);
                if (el) el.value = '';
            };

            const onFileChange = (field, e) => {
                const file = e.target && e.target.files && e.target.files[0];
                setFilePreviews(prev => {
                    const oldUrl = prev[field];
                    if (oldUrl) URL.revokeObjectURL(oldUrl);
                    return { ...prev, [field]: file ? URL.createObjectURL(file) : null };
                });
            };

            const closeModal = () => {
                setShowAddModal(false);
                setSelectedWorkerForEdit(null);
                setFilePreviews(prev => {
                    Object.values(prev).forEach(url => url && URL.revokeObjectURL(url));
                    return { idCardFront: null, idCardBack: null, fatherCardFront: null, fatherCardBack: null };
                });
            };

            const cashMethod = (paymentMethods || []).find(m => (m.code || '').toLowerCase() === 'cash');
            const bankMethod = (paymentMethods || []).find(m => (m.code || '').toLowerCase() === 'bank_transfer');
            const payMethodsList = [cashMethod, bankMethod].filter(Boolean).length ? [cashMethod, bankMethod].filter(Boolean) : (paymentMethods || []);
            const selectedPayMethod = paymentMethods.find(m => m.id == payMethodId);
            const needsBankAccount = selectedPayMethod && selectedPayMethod.requires_bank_account;

            const openPayModal = (worker) => {
                const pending = worker.pendingCommission ?? worker.pending_commission ?? 0;
                setWorkerToPay(worker);
                setPayAmount(String(pending));
                setPayMethodId(cashMethod ? cashMethod.id : (paymentMethods[0]?.id || ''));
                setPayBankAccountId((bankAccounts || [])[0]?.id || '');
                setWorkerBankAccountId(worker.bank_account_id ? String(worker.bank_account_id) : '');
                setWorkerBankName(worker.bankName ?? worker.bank_name ?? '');
                setWorkerBankTitle(worker.bankAccountTitle ?? worker.bank_account_title ?? '');
                setWorkerBankNumber(worker.bankAccountNumber ?? worker.bank_account_number ?? '');
            };
            const workerHasBank = workerToPay && (workerToPay.bank_account_id || workerToPay.bankAccount || workerToPay.bankAccountNumber || workerToPay.bank_account_number || workerBankNumber || workerBankAccountId);
            const saveWorkerBank = async () => {
                if (!workerToPay) return;
                const linkId = workerBankAccountId ? workerBankAccountId : null;
                const title = (workerBankTitle || '').trim();
                const number = (workerBankNumber || '').trim();
                if (!linkId && !number) { alert('Either link to a Bank Account or enter account number.'); return; }
                setWorkerBankSaving(true);
                try {
                    const res = await fetch(API_ROUTES.workers.updateBank(workerToPay.id), {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({
                            bank_account_id: linkId || null,
                            bank_name: (workerBankName || '').trim() || null,
                            bank_account_title: title || null,
                            bank_account_number: number || null,
                        }),
                    });
                    const data = await res.json();
                    if (data.success) {
                        setWorkerToPay(prev => prev ? { ...prev, bank_account_id: data.worker.bank_account_id, bankAccount: data.worker.bank_account, bankName: data.worker.bank_name, bankAccountTitle: data.worker.bank_account_title, bankAccountNumber: data.worker.bank_account_number } : null);
                        setWorkers(prev => prev.map(w => w.id === workerToPay.id ? { ...w, bank_account_id: data.worker.bank_account_id, bankAccount: data.worker.bank_account, bankName: data.worker.bank_name, bankAccountTitle: data.worker.bank_account_title, bankAccountNumber: data.worker.bank_account_number } : w));
                        alert('Worker bank account saved.');
                    } else alert('Error: ' + (data.message || 'Failed to save'));
                } catch (e) { console.error(e); alert('Error saving worker bank.'); }
                setWorkerBankSaving(false);
            };
            const createWorkerCashAccount = async () => {
                if (!workerToPay) return;
                setWorkerCashCreating(true);
                try {
                    const res = await fetch(API_ROUTES.workers.createCashAccount(workerToPay.id), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const data = await res.json();
                    if (data.success) {
                        setWorkerToPay(prev => prev ? { ...prev, has_cash_account: true, cash_balance: data.worker_cash_account.balance } : null);
                        setWorkers(prev => prev.map(w => w.id === workerToPay.id ? { ...w, has_cash_account: true, cash_balance: data.worker_cash_account.balance } : w));
                        alert('Worker cash account created.');
                    } else alert('Error: ' + (data.message || 'Failed to create'));
                } catch (e) { console.error(e); alert('Error creating worker cash account.'); }
                setWorkerCashCreating(false);
            };

            const submitPayCommission = async () => {
                if (!workerToPay) return;
                const amount = parseFloat(payAmount);
                if (isNaN(amount) || amount <= 0) {
                    alert('Enter a valid amount.');
                    return;
                }
                if (needsBankAccount && !payBankAccountId) {
                    alert('Please select a bank account.');
                    return;
                }
                setPaySubmitting(true);
                try {
                    const body = {
                        payment_type: 'commission',
                        worker_id: workerToPay.id,
                        amount: amount,
                        payment_date: new Date().toISOString().slice(0, 10),
                        payment_method_id: payMethodId || null,
                        bank_account_id: needsBankAccount ? payBankAccountId : null,
                    };
                    const response = await fetch(API_ROUTES.payments.store, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify(body),
                    });
                    const result = await response.json();
                    if (result.success) {
                        setWorkerToPay(null);
                        const reloadResponse = await fetch(API_ROUTES.workers.index);
                        const reloadData = await reloadResponse.json();
                        if (reloadData.success && reloadData.workers) {
                            setWorkers(reloadData.workers.map(mapWorkerFromApi));
                        }
                        alert('Commission paid successfully!');
                    } else {
                        alert('Error: ' + (result.message || 'Failed to save payment'));
                    }
                } catch (e) {
                    console.error(e);
                    alert('Error paying commission. Please try again.');
                }
                setPaySubmitting(false);
            };

            // Load workers from backend
            useEffect(() => {
                fetch(API_ROUTES.workers.index)
                    .then(res => res.json())
                    .then(data => {
                                                        if (data.success && data.workers) {
                                                            setWorkers((data.workers || []).map(mapWorkerFromApi));
                                                        }
                    })
                    .catch(err => console.error('Error loading workers:', err));
            }, []);

            return (
                <div className="min-h-screen bg-slate-50">
                    {/* Header - responsive */}
                    <header className="bg-purple-600 text-white shadow-2xl px-3 py-4 sm:p-6">
                        <div className="max-w-7xl mx-auto">
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div className="min-w-0">
                                    <h1 className="text-2xl sm:text-4xl font-black uppercase tracking-tighter mb-1 sm:mb-2 truncate">Staff Management</h1>
                                    <p className="text-xs sm:text-sm opacity-90 truncate">{branchName} • {userName}</p>
                                </div>
                                <div className="flex flex-col sm:flex-row gap-2 sm:gap-3 shrink-0">
                                    <button
                                        onClick={() => setShowAddModal(true)}
                                        className="touch-target px-4 py-3 sm:px-6 sm:py-3 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-black uppercase transition-colors backdrop-blur-sm flex items-center justify-center gap-2"
                                    >
                                        <svg className="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add Staff
                                    </button>
                                    <button
                                        onClick={() => window.location.href = '{{ route("car.wash") }}'}
                                        className="touch-target px-4 py-3 sm:px-6 sm:py-3 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-black uppercase transition-colors backdrop-blur-sm"
                                    >
                                        ← Back to Car Wash
                                    </button>
                                </div>
                            </div>
                        </div>
                    </header>

                    {/* Main Content - responsive padding */}
                    <main className="max-w-7xl mx-auto px-3 py-4 sm:p-6">
                        <div className="bg-white rounded-2xl sm:rounded-3xl shadow-xl border-2 border-slate-200 overflow-hidden">
                            {workers.length === 0 ? (
                                <div className="text-center py-12 sm:py-24 px-4">
                                    <div className="w-24 h-24 sm:w-40 sm:h-40 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-6 sm:mb-10 shadow-lg">
                                        <svg className="w-12 h-12 sm:w-20 sm:h-20 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <p className="text-xl sm:text-3xl font-black text-slate-800 uppercase tracking-tight mb-2 sm:mb-3">No Staff Found</p>
                                    <p className="text-sm sm:text-lg text-slate-500 mt-4 sm:mt-6 font-bold">Add your first staff member to get started</p>
                                </div>
                            ) : (
                                <>
                                    {/* Mobile: card layout */}
                                    <div className="md:hidden divide-y divide-slate-200">
                                        {workers.map((worker, idx) => (
                                            <div key={worker.id || idx} className="p-4 hover:bg-slate-50/50 transition-colors">
                                                <div className="flex items-start justify-between gap-2 mb-3">
                                                    <div className="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center text-white font-black text-sm shrink-0">
                                                        {idx + 1}
                                                    </div>
                                                    <span className={`shrink-0 px-2.5 py-1 rounded-full text-xs font-black uppercase ${(worker.status !== undefined ? worker.status : true) ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'}`}>
                                                        {(worker.status !== undefined ? worker.status : true) ? 'Active' : 'Inactive'}
                                                    </span>
                                                </div>
                                                <p className="text-sm font-black text-slate-900 break-words">{worker.name || 'N/A'}</p>
                                                <p className="text-xs text-slate-600 mt-0.5">{worker.mobile || 'N/A'}</p>
                                                {worker.fatherName && <p className="text-xs text-slate-500 mt-1">Father: {worker.fatherName}</p>}
                                                {worker.location && <p className="text-xs text-slate-500 mt-1 truncate" title={worker.location}>{worker.location}</p>}
                                                <div className="flex flex-wrap gap-2 mt-3">
                                                    <span className="text-xs font-black text-purple-600">{(worker.commission || 0)}%</span>
                                                    <span className="text-xs text-slate-400">•</span>
                                                    <span className="text-xs font-bold text-slate-600">{worker.dailyJobsCount || 0} jobs</span>
                                                    <span className="text-xs font-black text-emerald-600">Rs. {(worker.dailyCommission || 0).toLocaleString('en-PK', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}</span>
                                                </div>
                                                <div className="mt-2 text-xs space-y-0.5">
                                                    <span className="text-slate-600">Earned: Rs {(worker.totalEarned ?? 0).toLocaleString('en-PK', { minimumFractionDigits: 2 })}</span>
                                                    <span className="text-slate-500"> | Paid: Rs {(worker.totalPaid ?? 0).toLocaleString('en-PK', { minimumFractionDigits: 2 })}</span>
                                                    <span className="text-amber-600 font-bold"> | Pending: Rs {(worker.cash_balance ?? worker.pendingCommission ?? 0).toLocaleString('en-PK', { minimumFractionDigits: 2 })}</span>
                                                    <span className={`ml-1 px-1.5 py-0.5 rounded text-xs font-bold ${(worker.paymentStatus || 'unpaid') === 'paid' ? 'bg-emerald-100 text-emerald-700' : (worker.paymentStatus === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-slate-200 text-slate-700')}`}>
                                                        {(worker.paymentStatus || 'unpaid') === 'paid' ? 'Paid' : worker.paymentStatus === 'partial' ? 'Partial' : 'Unpaid'}
                                                    </span>
                                                </div>
                                                <div className="flex flex-wrap gap-2 mt-3">
                                                    {((worker.pendingCommission ?? worker.pending_commission) || 0) > 0 && (
                                                        <button
                                                            onClick={() => openPayModal(worker)}
                                                            className="touch-target flex-1 min-w-[80px] py-2.5 bg-amber-500 text-white rounded-lg text-xs font-black uppercase hover:bg-amber-600 transition-colors"
                                                        >
                                                            Pay (Cash/Bank)
                                                        </button>
                                                    )}
                                                    <button
                                                        onClick={() => setSelectedWorkerForEdit(worker)}
                                                        className="touch-target flex-1 min-w-[80px] py-2.5 bg-emerald-500 text-white rounded-lg text-xs font-black uppercase hover:bg-emerald-600 transition-colors"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        onClick={async () => {
                                                            if (confirm(`Delete ${worker.name}?`)) {
                                                                try {
                                                                    const response = await fetch(API_ROUTES.workers.destroy(worker.id), {
                                                                        method: 'DELETE',
                                                                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                                                                    });
                                                                    const result = await response.json();
                                                                    if (result.success) {
                                                                        setWorkers(prev => prev.filter(w => w.id !== worker.id));
                                                                        alert('Worker deleted!');
                                                                        const reloadResponse = await fetch(API_ROUTES.workers.index);
                                                                        const reloadData = await reloadResponse.json();
                                                                        if (reloadData.success && reloadData.workers) {
                                                                            setWorkers(reloadData.workers.map(mapWorkerFromApi));
                                                                        }
                                                                    } else alert('Error: ' + (result.message || 'Unknown error'));
                                                                } catch (e) { console.error(e); alert('Error deleting worker.'); }
                                                            }
                                                        }}
                                                        className="touch-target flex-1 py-2.5 bg-red-500 text-white rounded-lg text-xs font-black uppercase hover:bg-red-600 transition-colors"
                                                    >
                                                        Delete
                                                    </button>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                    {/* Desktop: table */}
                                    <div className="hidden md:block overflow-x-auto">
                                        <table className="w-full min-w-[1100px]">
                                            <thead className="bg-purple-600 text-white">
                                                <tr>
                                                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-black uppercase tracking-wider">#</th>
                                                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-black uppercase tracking-wider">Name</th>
                                                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-black uppercase tracking-wider">Mobile</th>
                                                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-black uppercase tracking-wider">Father Name</th>
                                                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-black uppercase tracking-wider">Location</th>
                                                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-black uppercase tracking-wider">Commission %</th>
                                                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-black uppercase tracking-wider">Daily Jobs</th>
                                                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-black uppercase tracking-wider">Daily Commission</th>
                                                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-black uppercase tracking-wider">Total Earned</th>
                                                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-black uppercase tracking-wider">Total Paid</th>
                                                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-black uppercase tracking-wider">Balance (Pending)</th>
                                                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-black uppercase tracking-wider">Payment</th>
                                                    <th className="px-4 lg:px-6 py-3 text-left text-xs font-black uppercase tracking-wider">Status</th>
                                                    <th className="px-4 lg:px-6 py-3 text-center text-xs font-black uppercase tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody className="bg-white divide-y divide-slate-200">
                                                {workers.map((worker, idx) => (
                                                    <tr key={worker.id || idx} className="hover:bg-slate-50 transition-colors">
                                                        <td className="px-4 lg:px-6 py-3 whitespace-nowrap">
                                                            <div className="w-9 h-9 lg:w-10 lg:h-10 bg-purple-600 rounded-lg flex items-center justify-center text-white font-black text-sm shadow-lg">
                                                                {idx + 1}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3 whitespace-nowrap">
                                                            <div className="text-sm font-black text-slate-900">{worker.name || 'N/A'}</div>
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3 whitespace-nowrap">
                                                            <div className="text-sm font-black text-slate-900">{worker.mobile || 'N/A'}</div>
                                                            {(worker.additionalMobiles || worker.additional_mobiles || []).length > 0 && (
                                                                <div className="text-xs text-slate-500 mt-0.5">+{(worker.additionalMobiles || worker.additional_mobiles).length} more</div>
                                                            )}
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3 whitespace-nowrap">
                                                            <div className="text-sm font-black text-slate-900">{worker.fatherName || 'N/A'}</div>
                                                            {worker.fatherMobile && <div className="text-xs text-slate-500 mt-0.5">{worker.fatherMobile}</div>}
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3">
                                                            <div className="text-sm text-slate-600 max-w-[180px] truncate">{worker.location || 'N/A'}</div>
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3 whitespace-nowrap">
                                                            <div className="text-sm font-black text-purple-600">{(worker.commission || 0)}%</div>
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3 whitespace-nowrap">
                                                            <div className="flex items-center gap-1.5">
                                                                <div className="w-7 h-7 lg:w-8 lg:h-8 bg-blue-500 rounded-lg flex items-center justify-center text-white font-black text-xs shadow-md">
                                                                    {worker.dailyJobsCount || 0}
                                                                </div>
                                                                <span className="text-xs text-slate-500 font-bold">Jobs</span>
                                                            </div>
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3 whitespace-nowrap">
                                                            <div className="text-sm font-black text-emerald-600">
                                                                Rs. {(worker.dailyCommission || 0).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3 whitespace-nowrap">
                                                            <div className="text-sm font-bold text-slate-700">
                                                                Rs. {(worker.totalEarned ?? 0).toLocaleString('en-PK', { minimumFractionDigits: 2 })}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3 whitespace-nowrap">
                                                            <div className="text-sm font-bold text-slate-600">
                                                                Rs. {(worker.totalPaid ?? 0).toLocaleString('en-PK', { minimumFractionDigits: 2 })}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3 whitespace-nowrap">
                                                            <div className="text-sm font-black text-amber-600">
                                                                Rs. {(worker.cash_balance ?? worker.pendingCommission ?? 0).toLocaleString('en-PK', { minimumFractionDigits: 2 })}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3 whitespace-nowrap">
                                                            <span className={`px-2.5 py-1 rounded-full text-xs font-black uppercase ${(worker.paymentStatus || 'unpaid') === 'paid' ? 'bg-emerald-100 text-emerald-700' : worker.paymentStatus === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-slate-200 text-slate-700'}`}>
                                                                {(worker.paymentStatus || 'unpaid') === 'paid' ? 'Paid' : worker.paymentStatus === 'partial' ? 'Partial' : 'Unpaid'}
                                                            </span>
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3 whitespace-nowrap">
                                                            <span className={`px-2.5 py-1 rounded-full text-xs font-black uppercase ${(worker.status !== undefined ? worker.status : true) ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'}`}>
                                                                {(worker.status !== undefined ? worker.status : true) ? 'Active' : 'Inactive'}
                                                            </span>
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3 whitespace-nowrap text-center">
                                                            <div className="flex items-center justify-center gap-1.5 flex-wrap">
                                                                {((worker.pendingCommission ?? worker.pending_commission) || 0) > 0 && (
                                                                    <button
                                                                        onClick={() => openPayModal(worker)}
                                                                        className="px-2.5 py-1.5 sm:px-3 sm:py-2 bg-amber-500 text-white rounded-lg text-xs font-black uppercase hover:bg-amber-600 transition-colors shadow-md"
                                                                        title="Pay Commission (Cash/Bank)"
                                                                    >
                                                                        Pay
                                                                    </button>
                                                                )}
                                                                <button
                                                                    onClick={() => setSelectedWorkerForEdit(worker)}
                                                                    className="px-2.5 py-1.5 sm:px-3 sm:py-2 bg-emerald-500 text-white rounded-lg text-xs font-black uppercase hover:bg-emerald-600 transition-colors shadow-md"
                                                                    title="Edit Worker"
                                                                >
                                                                    Edit
                                                                </button>
                                                                <button
                                                                    onClick={async () => {
                                                                        if (confirm(`Are you sure you want to delete worker: ${worker.name}?`)) {
                                                                            try {
                                                                                const response = await fetch(API_ROUTES.workers.destroy(worker.id), {
                                                                                    method: 'DELETE',
                                                                                    headers: {
                                                                                        'Content-Type': 'application/json',
                                                                                        'X-CSRF-TOKEN': csrfToken,
                                                                                        'Accept': 'application/json'
                                                                                    }
                                                                                });
                                                                                const result = await response.json();
                                                                                if (result.success) {
                                                                                    setWorkers(prev => prev.filter(w => w.id !== worker.id));
                                                                                    alert('Worker deleted successfully!');
                                                                                    const reloadResponse = await fetch(API_ROUTES.workers.index);
                                                                                    const reloadData = await reloadResponse.json();
                                                                                    if (reloadData.success && reloadData.workers) {
                                                                                        setWorkers(reloadData.workers.map(mapWorkerFromApi));
                                                                                    }
                                                                                } else {
                                                                                    alert('Error deleting worker: ' + (result.message || 'Unknown error'));
                                                                                }
                                                                            } catch (error) {
                                                                                console.error('Error deleting worker:', error);
                                                                                alert('Error deleting worker. Please try again.');
                                                                            }
                                                                        }
                                                                    }}
                                                                    className="px-2.5 py-1.5 sm:px-3 sm:py-2 bg-red-500 text-white rounded-lg text-xs font-black uppercase hover:bg-red-600 transition-colors shadow-md"
                                                                    title="Delete Worker"
                                                                >
                                                                    Delete
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </>
                            )}
                        </div>
                    </main>

                    {/* Add/Edit Staff Modal - responsive */}
                    {(showAddModal || selectedWorkerForEdit) && (
                        <div 
                            className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-y-auto"
                            onClick={closeModal}
                        >
                            <div 
                                className="bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl w-full sm:max-w-2xl max-h-[95vh] sm:max-h-[90vh] overflow-hidden flex flex-col min-h-0"
                                onClick={(e) => e.stopPropagation()}
                            >
                                <div className="p-4 sm:p-6 border-b border-slate-200 bg-purple-600 text-white shrink-0">
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="min-w-0">
                                            <h2 className="text-xl sm:text-2xl font-black uppercase tracking-tighter truncate">
                                                {selectedWorkerForEdit ? 'EDIT WORKER' : 'NEW WORKER'}
                                            </h2>
                                            <p className="text-xs sm:text-sm opacity-90 mt-1">
                                                {selectedWorkerForEdit ? 'Update worker information' : 'ADD A NEW WORKER TO STATION'}
                                            </p>
                                        </div>
                                        <button
                                            onClick={closeModal}
                                            className="touch-target shrink-0 text-white hover:text-slate-200 transition-colors p-2 -m-2"
                                            aria-label="Close"
                                        >
                                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <div className="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
                                    <form 
                                        key={selectedWorkerForEdit ? `edit-${selectedWorkerForEdit.id}` : 'new-worker'}
                                        encType="multipart/form-data"
                                        onSubmit={async (e) => {
                                            e.preventDefault();
                                            
                                            // Get form values directly from inputs to ensure we have all data
                                            const formData = new FormData();
                                            
                                            // Always include all text fields - get current values from inputs
                                            const nameInput = document.getElementById('workerName');
                                            const mobileInput = document.getElementById('workerMobile');
                                            const fatherNameInput = document.getElementById('workerFatherName');
                                            const fatherMobileInput = document.getElementById('workerFatherMobile');
                                            const locationInput = document.getElementById('workerLocation');
                                            const commissionInput = document.getElementById('workerCommission');
                                            
                                            // Get values directly from form inputs
                                            // For edit mode, ensure we use existing values if inputs are empty
                                            const name = nameInput ? nameInput.value.trim() : (selectedWorkerForEdit ? selectedWorkerForEdit.name : '');
                                            const mobile = mobileInput ? mobileInput.value.trim() : (selectedWorkerForEdit ? selectedWorkerForEdit.mobile : '');
                                            const fatherName = fatherNameInput && fatherNameInput.value.trim() !== '' ? fatherNameInput.value.trim() : (selectedWorkerForEdit ? selectedWorkerForEdit.fatherName : '');
                                            const fatherMobile = fatherMobileInput && fatherMobileInput.value.trim() !== '' ? fatherMobileInput.value.trim() : (selectedWorkerForEdit ? selectedWorkerForEdit.fatherMobile : '');
                                            const location = locationInput ? locationInput.value.trim() : (selectedWorkerForEdit ? selectedWorkerForEdit.location : '');
                                            const commission = commissionInput && commissionInput.value !== '' ? (commissionInput.value || 0) : (selectedWorkerForEdit ? selectedWorkerForEdit.commission : 0);
                                            
                                            // Validate required fields
                                            if (!name || name === '') {
                                                alert('Worker name is required!');
                                                return;
                                            }
                                            
                                            // Debug log
                                            console.log('Form Data Being Sent:', {
                                                name,
                                                mobile,
                                                fatherName,
                                                fatherMobile,
                                                location,
                                                commission,
                                                isEdit: !!selectedWorkerForEdit,
                                                selectedWorkerData: selectedWorkerForEdit
                                            });
                                            
                                            // Add all fields to FormData (always send values - empty strings will be converted to null on backend)
                                            formData.append('name', name);
                                            formData.append('mobile', mobile || '');
                                            formData.append('father_name', fatherName || '');
                                            formData.append('father_mobile', fatherMobile || '');
                                            formData.append('location', location || '');
                                            formData.append('commission', commission || 0);
                                            
                                            // Add image files if selected (only if files are actually selected)
                                            const idCardFrontInput = document.getElementById('workerIdCardFront');
                                            const idCardBackInput = document.getElementById('workerIdCardBack');
                                            const fatherCardFrontInput = document.getElementById('workerFatherCardFront');
                                            const fatherCardBackInput = document.getElementById('workerFatherCardBack');
                                            
                                            const idCardFrontFile = idCardFrontInput && idCardFrontInput.files && idCardFrontInput.files[0] ? idCardFrontInput.files[0] : null;
                                            const idCardBackFile = idCardBackInput && idCardBackInput.files && idCardBackInput.files[0] ? idCardBackInput.files[0] : null;
                                            const fatherCardFrontFile = fatherCardFrontInput && fatherCardFrontInput.files && fatherCardFrontInput.files[0] ? fatherCardFrontInput.files[0] : null;
                                            const fatherCardBackFile = fatherCardBackInput && fatherCardBackInput.files && fatherCardBackInput.files[0] ? fatherCardBackInput.files[0] : null;
                                            
                                            // For new worker, images are required. For edit, only add if new file is selected
                                            if (idCardFrontFile) formData.append('id_card_front', idCardFrontFile);
                                            if (idCardBackFile) formData.append('id_card_back', idCardBackFile);
                                            if (fatherCardFrontFile) formData.append('father_card_front', fatherCardFrontFile);
                                            if (fatherCardBackFile) formData.append('father_card_back', fatherCardBackFile);
                                            
                                            // Add _method for PUT requests (Laravel method spoofing)
                                            if (selectedWorkerForEdit) {
                                                formData.append('_method', 'PUT');
                                            }
                                            
                                            try {
                                                const url = selectedWorkerForEdit 
                                                    ? API_ROUTES.workers.update(selectedWorkerForEdit.id)
                                                    : API_ROUTES.workers.store;
                                                
                                                const response = await fetch(url, {
                                                    method: 'POST',
                                                    headers: {
                                                        'X-CSRF-TOKEN': csrfToken,
                                                        'Accept': 'application/json'
                                                        // Don't set Content-Type for FormData - browser will set it with boundary
                                                    },
                                                    body: formData
                                                });
                                                
                                                const result = await response.json();
                                                
                                                if (result.success) {
                                                    alert(selectedWorkerForEdit ? 'Worker updated successfully!' : 'Worker added successfully!');
                                                    closeModal();
                                                    
                                                    // Reload workers
                                                    const reloadResponse = await fetch(API_ROUTES.workers.index);
                                                    const reloadData = await reloadResponse.json();
                                                    if (reloadData.success && reloadData.workers) {
                                                        setWorkers(reloadData.workers.map(mapWorkerFromApi));
                                                    }
                                                } else {
                                                    alert('Error: ' + (result.message || 'Unknown error'));
                                                }
                                            } catch (error) {
                                                console.error('Error saving worker:', error);
                                                alert('Error saving worker. Please try again.');
                                            }
                                        }}
                                    >
                                        <div className="space-y-4">
                                            <div>
                                                <label className="block text-xs sm:text-sm font-black text-slate-900 uppercase mb-2">WORKER NAME</label>
                                                <input 
                                                    type="text" 
                                                    name="name"
                                                    id="workerName"
                                                    defaultValue={selectedWorkerForEdit?.name || ''}
                                                    className="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none uppercase text-base"
                                                    placeholder="e.g. John Doe"
                                                    required
                                                    key={selectedWorkerForEdit ? `name-${selectedWorkerForEdit.id}` : 'name-new'}
                                                />
                                            </div>
                                            
                                            <div>
                                                <label className="block text-xs sm:text-sm font-black text-slate-900 uppercase mb-2">MOBILE NUMBER</label>
                                                <input 
                                                    type="tel" 
                                                    name="mobile"
                                                    id="workerMobile"
                                                    defaultValue={selectedWorkerForEdit?.mobile || ''}
                                                    className="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none text-base"
                                                    placeholder="e.g. 0300-1234567"
                                                    required
                                                />
                                            </div>
                                            
                                            <div>
                                                <label className="block text-xs sm:text-sm font-black text-slate-900 uppercase mb-2">FATHER NAME</label>
                                                <input 
                                                    type="text" 
                                                    name="father_name"
                                                    id="workerFatherName"
                                                    defaultValue={selectedWorkerForEdit?.fatherName || ''}
                                                    className="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none uppercase text-base"
                                                    placeholder="e.g. Muhammad Ali"
                                                    required={!selectedWorkerForEdit}
                                                />
                                            </div>
                                            
                                            <div>
                                                <label className="block text-xs sm:text-sm font-black text-slate-900 uppercase mb-2">FATHER MOBILE NUMBER</label>
                                                <input 
                                                    type="tel" 
                                                    name="father_mobile"
                                                    id="workerFatherMobile"
                                                    defaultValue={selectedWorkerForEdit?.fatherMobile || ''}
                                                    className="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none text-base"
                                                    placeholder="e.g. 0300-1234567"
                                                    required
                                                />
                                            </div>
                                            
                                            <div>
                                                <label className="block text-xs sm:text-sm font-black text-slate-900 uppercase mb-2">LOCATION / HOME ADDRESS</label>
                                                <textarea 
                                                    name="location"
                                                    id="workerLocation"
                                                    defaultValue={selectedWorkerForEdit?.location || ''}
                                                    className="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none text-base min-h-[80px]"
                                                    placeholder="Enter full address"
                                                    rows="3"
                                                    required={!selectedWorkerForEdit}
                                                ></textarea>
                                            </div>
                                            
                                            <div>
                                                <label className="block text-xs sm:text-sm font-black text-slate-900 uppercase mb-2">COMMISSION / SET BOX (%)</label>
                                                <input 
                                                    type="number" 
                                                    name="commission"
                                                    id="workerCommission"
                                                    defaultValue={selectedWorkerForEdit?.commission || 0}
                                                    className="w-full px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none text-base"
                                                    placeholder="0"
                                                    min="0"
                                                    max="100"
                                                    step="1"
                                                    required={!selectedWorkerForEdit}
                                                />
                                            </div>
                                            
                                            <div>
                                                <label className="block text-xs sm:text-sm font-black text-slate-900 uppercase mb-2">ID CARD PICTURE (FRONT)</label>
                                                <input 
                                                    type="file" 
                                                    id="workerIdCardFront"
                                                    accept="image/*"
                                                    className="w-full px-2 sm:px-4 py-2.5 sm:py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none text-sm file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-purple-100 file:text-purple-700"
                                                    required={!selectedWorkerForEdit}
                                                    onChange={(e) => onFileChange('idCardFront', e)}
                                                />
                                                {filePreviews.idCardFront && (
                                                    <div className="mt-2 flex flex-wrap items-center gap-2 sm:gap-3">
                                                        <img src={filePreviews.idCardFront} alt="ID Front preview" className="max-w-full w-40 sm:max-w-xs max-h-28 sm:max-h-32 border-2 border-slate-300 rounded-lg object-contain" />
                                                        <button type="button" onClick={() => clearFilePreview('idCardFront')} className="touch-target px-3 py-2 sm:px-4 sm:py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-xs sm:text-sm font-bold uppercase transition-colors">Cancel</button>
                                                    </div>
                                                )}
                                                {!filePreviews.idCardFront && selectedWorkerForEdit?.idCardFront && (
                                                    <div className="mt-2">
                                                        <p className="text-xs text-slate-500 mb-1">Current image:</p>
                                                        <img src={selectedWorkerForEdit.idCardFront} alt="ID Front" className="max-w-full w-40 sm:max-w-xs max-h-28 sm:max-h-32 border-2 border-slate-300 rounded-lg object-contain" />
                                                    </div>
                                                )}
                                            </div>
                                            
                                            <div>
                                                <label className="block text-xs sm:text-sm font-black text-slate-900 uppercase mb-2">ID CARD PICTURE (BACK)</label>
                                                <input 
                                                    type="file" 
                                                    id="workerIdCardBack"
                                                    accept="image/*"
                                                    className="w-full px-2 sm:px-4 py-2.5 sm:py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none text-sm file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-purple-100 file:text-purple-700"
                                                    required={!selectedWorkerForEdit}
                                                    onChange={(e) => onFileChange('idCardBack', e)}
                                                />
                                                {filePreviews.idCardBack && (
                                                    <div className="mt-2 flex flex-wrap items-center gap-2 sm:gap-3">
                                                        <img src={filePreviews.idCardBack} alt="ID Back preview" className="max-w-full w-40 sm:max-w-xs max-h-28 sm:max-h-32 border-2 border-slate-300 rounded-lg object-contain" />
                                                        <button type="button" onClick={() => clearFilePreview('idCardBack')} className="touch-target px-3 py-2 sm:px-4 sm:py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-xs sm:text-sm font-bold uppercase transition-colors">Cancel</button>
                                                    </div>
                                                )}
                                                {!filePreviews.idCardBack && selectedWorkerForEdit?.idCardBack && (
                                                    <div className="mt-2">
                                                        <p className="text-xs text-slate-500 mb-1">Current image:</p>
                                                        <img src={selectedWorkerForEdit.idCardBack} alt="ID Back" className="max-w-full w-40 sm:max-w-xs max-h-28 sm:max-h-32 border-2 border-slate-300 rounded-lg object-contain" />
                                                    </div>
                                                )}
                                            </div>
                                            
                                            <div>
                                                <label className="block text-xs sm:text-sm font-black text-slate-900 uppercase mb-2">FATHER / REFERENCE CARD (FRONT)</label>
                                                <input 
                                                    type="file" 
                                                    id="workerFatherCardFront"
                                                    accept="image/*"
                                                    className="w-full px-2 sm:px-4 py-2.5 sm:py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none text-sm file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-purple-100 file:text-purple-700"
                                                    required={!selectedWorkerForEdit}
                                                    onChange={(e) => onFileChange('fatherCardFront', e)}
                                                />
                                                {filePreviews.fatherCardFront && (
                                                    <div className="mt-2 flex flex-wrap items-center gap-2 sm:gap-3">
                                                        <img src={filePreviews.fatherCardFront} alt="Father Card Front preview" className="max-w-full w-40 sm:max-w-xs max-h-28 sm:max-h-32 border-2 border-slate-300 rounded-lg object-contain" />
                                                        <button type="button" onClick={() => clearFilePreview('fatherCardFront')} className="touch-target px-3 py-2 sm:px-4 sm:py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-xs sm:text-sm font-bold uppercase transition-colors">Cancel</button>
                                                    </div>
                                                )}
                                                {!filePreviews.fatherCardFront && selectedWorkerForEdit?.fatherCardFront && (
                                                    <div className="mt-2">
                                                        <p className="text-xs text-slate-500 mb-1">Current image:</p>
                                                        <img src={selectedWorkerForEdit.fatherCardFront} alt="Father Card Front" className="max-w-full w-40 sm:max-w-xs max-h-28 sm:max-h-32 border-2 border-slate-300 rounded-lg object-contain" />
                                                    </div>
                                                )}
                                            </div>
                                            
                                            <div>
                                                <label className="block text-xs sm:text-sm font-black text-slate-900 uppercase mb-2">FATHER / REFERENCE CARD (BACK)</label>
                                                <input 
                                                    type="file" 
                                                    id="workerFatherCardBack"
                                                    accept="image/*"
                                                    className="w-full px-2 sm:px-4 py-2.5 sm:py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none text-sm file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-purple-100 file:text-purple-700"
                                                    required={!selectedWorkerForEdit}
                                                    onChange={(e) => onFileChange('fatherCardBack', e)}
                                                />
                                                {filePreviews.fatherCardBack && (
                                                    <div className="mt-2 flex flex-wrap items-center gap-2 sm:gap-3">
                                                        <img src={filePreviews.fatherCardBack} alt="Father Card Back preview" className="max-w-full w-40 sm:max-w-xs max-h-28 sm:max-h-32 border-2 border-slate-300 rounded-lg object-contain" />
                                                        <button type="button" onClick={() => clearFilePreview('fatherCardBack')} className="touch-target px-3 py-2 sm:px-4 sm:py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-xs sm:text-sm font-bold uppercase transition-colors">Cancel</button>
                                                    </div>
                                                )}
                                                {!filePreviews.fatherCardBack && selectedWorkerForEdit?.fatherCardBack && (
                                                    <div className="mt-2">
                                                        <p className="text-xs text-slate-500 mb-1">Current image:</p>
                                                        <img src={selectedWorkerForEdit.fatherCardBack} alt="Father Card Back" className="max-w-full w-40 sm:max-w-xs max-h-28 sm:max-h-32 border-2 border-slate-300 rounded-lg object-contain" />
                                                    </div>
                                                )}
                                            </div>
                                            
                                            <div className="flex flex-col-reverse sm:flex-row gap-3 sm:gap-4 pt-4 pb-2 sm:pb-0">
                                                <button
                                                    type="button"
                                                    onClick={closeModal}
                                                    className="touch-target flex-1 px-4 py-3 sm:px-6 sm:py-3 bg-slate-600 text-white rounded-xl text-sm font-black uppercase hover:bg-slate-700 transition-colors"
                                                >
                                                    Cancel
                                                </button>
                                                <button
                                                    type="submit"
                                                    className="touch-target flex-1 px-4 py-3 sm:px-6 sm:py-3 bg-purple-600 text-white rounded-xl text-sm font-black uppercase hover:bg-purple-700 transition-colors"
                                                >
                                                    {selectedWorkerForEdit ? 'UPDATE WORKER' : 'SAVE WORKER'}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Pay Commission Modal (Cash / Bank) */}
                    {workerToPay && (
                        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" onClick={() => !paySubmitting && setWorkerToPay(null)}>
                            <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden" onClick={(e) => e.stopPropagation()}>
                                <div className="p-4 sm:p-6 border-b border-slate-200 bg-amber-500 text-white">
                                    <h2 className="text-xl font-black uppercase tracking-tighter">Pay Commission</h2>
                                    <p className="text-sm opacity-90 mt-1">{workerToPay.name}</p>
                                </div>
                                <div className="p-4 sm:p-6 space-y-4">
                                    <div>
                                        <label className="block text-xs sm:text-sm font-black text-slate-900 uppercase mb-2">Amount (Rs.)</label>
                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            value={payAmount}
                                            onChange={(e) => setPayAmount(e.target.value)}
                                            className="w-full px-3 py-2.5 border-2 border-slate-300 rounded-xl focus:border-amber-500 focus:outline-none text-base"
                                        />
                                        <p className="text-xs text-slate-500 mt-1">Total earned: Rs. {(workerToPay.totalEarned ?? 0).toLocaleString('en-PK', { minimumFractionDigits: 2 })} | Paid: Rs. {(workerToPay.totalPaid ?? 0).toLocaleString('en-PK', { minimumFractionDigits: 2 })} | Pending: Rs. {(workerToPay.cash_balance ?? workerToPay.pendingCommission ?? 0).toLocaleString('en-PK', { minimumFractionDigits: 2 })}</p>
                                        <p className="text-xs text-slate-400 mt-0.5">Today: Rs. {(workerToPay.dailyCommission ?? 0).toLocaleString('en-PK', { minimumFractionDigits: 2 })}</p>
                                    </div>
                                    <div>
                                        <label className="block text-xs sm:text-sm font-black text-slate-900 uppercase mb-2">Pay From (Your Account)</label>
                                        <select
                                            value={payMethodId}
                                            onChange={(e) => { setPayMethodId(e.target.value); setPayBankAccountId(bankAccounts[0]?.id || ''); }}
                                            className="w-full px-3 py-2.5 border-2 border-slate-300 rounded-xl focus:border-amber-500 focus:outline-none text-base"
                                        >
                                            {payMethodsList.length ? payMethodsList.map(m => (
                                                <option key={m.id} value={m.id}>
                                                    {(m.code || '').toLowerCase() === 'cash'
                                                        ? `My Cash Account (Rs. ${userCashBalance.toLocaleString('en-PK', { minimumFractionDigits: 2 })})`
                                                        : m.name}
                                                </option>
                                            )) : (
                                                <option value="">Select account</option>
                                            )}
                                        </select>
                                        <p className="text-xs text-slate-500 mt-1">
                                            {selectedPayMethod && (selectedPayMethod.code || '').toLowerCase() === 'cash'
                                                ? 'Commission will be deducted from your cash account and credited to worker\'s cash account.'
                                                : 'Commission will be deducted from the selected bank account and credited to worker\'s bank account.'}
                                        </p>
                                    </div>
                                    {selectedPayMethod && (selectedPayMethod.code || '').toLowerCase() === 'cash' && (
                                        <div className="border-t border-slate-200 pt-4">
                                            <p className="text-xs sm:text-sm font-bold text-slate-700 uppercase mb-2">Worker&apos;s cash account (commission jahan jay gi)</p>
                                            {(workerToPay.has_cash_account || workerToPay.hasCashAccount) ? (
                                                <p className="text-sm text-slate-600 bg-slate-100 rounded-lg px-3 py-2">
                                                    Cash account created. Balance: Rs. {(workerToPay.cash_balance ?? workerToPay.cashBalance ?? 0).toLocaleString('en-PK', { minimumFractionDigits: 2 })}. Commission will be credited here.
                                                </p>
                                            ) : (
                                                <div className="bg-amber-50 rounded-xl p-3 border border-amber-200">
                                                    <p className="text-xs text-amber-800 mb-2">Worker ka cash account pehle create karein. Commission worker ke cash account mein credit ho gi.</p>
                                                    <button type="button" onClick={createWorkerCashAccount} disabled={workerCashCreating} className="touch-target px-3 py-2 bg-amber-500 text-white rounded-lg text-sm font-bold uppercase hover:bg-amber-600 disabled:opacity-50">
                                                        {workerCashCreating ? 'Creating…' : 'Create worker cash account'}
                                                    </button>
                                                </div>
                                            )}
                                        </div>
                                    )}
                                    {needsBankAccount && (
                                        <>
                                            <div>
                                                <label className="block text-xs sm:text-sm font-black text-slate-900 uppercase mb-2">Select Bank Account (Pay From)</label>
                                                <select
                                                    value={payBankAccountId}
                                                    onChange={(e) => setPayBankAccountId(e.target.value)}
                                                    className="w-full px-3 py-2.5 border-2 border-slate-300 rounded-xl focus:border-amber-500 focus:outline-none text-base"
                                                >
                                                    <option value="">Select bank account</option>
                                                    {bankAccounts.map(acc => (
                                                        <option key={acc.id} value={acc.id}>
                                                            {(acc.bank && acc.bank.name) || 'Bank'} - {acc.account_title || acc.account_number || acc.id}
                                                        </option>
                                                    ))}
                                                </select>
                                            </div>
                                            <div className="border-t border-slate-200 pt-4">
                                                <p className="text-xs sm:text-sm font-bold text-slate-700 uppercase mb-2">Worker&apos;s bank (where payment goes)</p>
                                                {workerHasBank ? (
                                                    <p className="text-sm text-slate-600 bg-slate-100 rounded-lg px-3 py-2">
                                                        {(workerToPay.bankAccount && workerToPay.bankAccount.bank) ? (workerToPay.bankAccount.bank.name + ' – ' + (workerToPay.bankAccount.account_title || '') + ' – ****' + String(workerToPay.bankAccount.account_number || '').slice(-4)) : ((workerToPay.bankName || workerToPay.bank_name || workerBankName) || 'Bank') + ' – ' + ((workerToPay.bankAccountTitle || workerToPay.bank_account_title || workerBankTitle) || 'Account') + ' – ****' + String(workerToPay.bankAccountNumber || workerToPay.bank_account_number || workerBankNumber || '').slice(-4)}
                                                    </p>
                                                ) : (
                                                    <div className="space-y-2 bg-amber-50 rounded-xl p-3 border border-amber-200">
                                                        <p className="text-xs text-amber-800 font-bold">Link to Bank Account (from Bank module) – separate history</p>
                                                        <select value={workerBankAccountId} onChange={(e) => setWorkerBankAccountId(e.target.value)} className="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                                                            <option value="">Select bank account to link…</option>
                                                            {(bankAccounts || []).map(acc => (
                                                                <option key={acc.id} value={acc.id}>{(acc.bank && acc.bank.name) || 'Bank'} – {acc.account_title || acc.account_number || acc.id}</option>
                                                            ))}
                                                        </select>
                                                        <p className="text-xs text-slate-600">Or enter manually:</p>
                                                        <input type="text" placeholder="Bank name" value={workerBankName} onChange={(e) => setWorkerBankName(e.target.value)} className="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
                                                        <input type="text" placeholder="Account title" value={workerBankTitle} onChange={(e) => setWorkerBankTitle(e.target.value)} className="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
                                                        <input type="text" placeholder="Account number" value={workerBankNumber} onChange={(e) => setWorkerBankNumber(e.target.value)} className="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" />
                                                        <button type="button" onClick={saveWorkerBank} disabled={workerBankSaving || (!workerBankAccountId && !workerBankNumber.trim())} className="touch-target px-3 py-2 bg-amber-500 text-white rounded-lg text-sm font-bold uppercase hover:bg-amber-600 disabled:opacity-50">
                                                            {workerBankSaving ? 'Saving…' : 'Save worker bank account'}
                                                        </button>
                                                    </div>
                                                )}
                                            </div>
                                        </>
                                    )}
                                    <div className="flex gap-3 pt-2">
                                        <button
                                            type="button"
                                            onClick={() => setWorkerToPay(null)}
                                            disabled={paySubmitting}
                                            className="flex-1 py-2.5 bg-slate-500 text-white rounded-xl text-sm font-black uppercase hover:bg-slate-600 transition-colors disabled:opacity-50"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="button"
                                            onClick={submitPayCommission}
                                            disabled={paySubmitting}
                                            className="flex-1 py-2.5 bg-amber-500 text-white rounded-xl text-sm font-black uppercase hover:bg-amber-600 transition-colors disabled:opacity-50"
                                        >
                                            {paySubmitting ? 'Paying…' : 'Pay Commission'}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            );
        }

        // React 18 compatibility
        const rootElement = document.getElementById('root');
        if (typeof ReactDOM.createRoot !== 'undefined') {
            ReactDOM.createRoot(rootElement).render(<App />);
        } else {
            ReactDOM.render(<App />, rootElement);
        }
    </script>
</body>
</html>
