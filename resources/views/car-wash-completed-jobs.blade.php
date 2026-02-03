<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Jobs - Elite Car Wash</title>
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
        
        @media print {
            @page {
                size: A4;
                margin: 15mm;
            }
            body * {
                visibility: hidden;
            }
            #jobDetailPrint, #jobDetailPrint * {
                visibility: visible;
            }
            #jobDetailPrint {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                max-width: 100%;
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 0;
                background: white;
                page-break-after: avoid;
            }
            #jobDetailPrint .print-header {
                background: linear-gradient(to right, #2563eb, #4f46e5) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color: white !important;
                padding: 20px;
                margin-bottom: 20px;
            }
            #jobDetailPrint .print-section {
                page-break-inside: avoid;
                margin-bottom: 20px;
            }
            #jobDetailPrint .print-card {
                background: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                border: 2px solid #e2e8f0 !important;
                padding: 15px;
                margin-bottom: 15px;
                border-radius: 8px;
            }
            #jobDetailPrint .print-amount {
                background: linear-gradient(to bottom right, #3b82f6, #4f46e5) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color: white !important;
            }
            #jobDetailPrint .print-commission {
                background: linear-gradient(to bottom right, #10b981, #059669) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color: white !important;
            }
            button {
                display: none !important;
            }
            .no-print {
                display: none !important;
            }
        }
        .worker-label {
            font-family: 'Segoe UI Emoji', ui-sans-serif, system-ui, sans-serif;
        }
    </style>
    
    <!-- html2pdf library for PDF download -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body class="bg-slate-50 min-h-screen">
    <div id="root"></div>

    <script type="text/babel">
        const { useState, useEffect } = React;

        // Initial data from backend
        const initialCompletedJobs = @json($completedJobs);
        const branchName = @json($branchName);
        const userName = @json($userName);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const API_ROUTES = {
            jobs: {
                completed: '{{ route("car-wash.jobs.completed") }}',
                update: (id) => `/car-wash/jobs/${id}`,
                destroy: (id) => `/car-wash/jobs/${id}`,
            },
            inspections: {
                show: (jobId) => `/car-wash/inspections/${jobId}`,
            },
            expenses: {
                show: (jobId) => `/car-wash/expenses/${jobId}`,
            },
            payments: {
                store: '{{ route("car-wash.payments.store") }}',
                reverseLastForWorker: '{{ route("car-wash.payments.reverse-last-for-worker") }}',
                cashAccountBalance: '{{ route("car-wash.payments.cash-account-balance") }}',
                pendingCommission: (workerId) => '{{ url("/car-wash/payments/pending-commission") }}/' + workerId,
                cashMethod: '{{ route("car-wash.payments.cash-method") }}',
            },
            workers: {
                cashTimeline: (workerId) => '{{ url("/car-wash/workers") }}/' + workerId + '/cash-timeline',
            },
        };

        function App() {
            const getTodayLocal = () => {
                const d = new Date();
                return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            };
            const [completedJobs, setCompletedJobs] = useState(initialCompletedJobs);
            const [selectedJobForDetail, setSelectedJobForDetail] = useState(null);
            const [selectedJobForEdit, setSelectedJobForEdit] = useState(null);
            const [dateFrom, setDateFrom] = useState(() => getTodayLocal());
            const [dateTo, setDateTo] = useState(() => getTodayLocal());
            const [selectedWorker, setSelectedWorker] = useState('');
            const [openDropdownId, setOpenDropdownId] = useState(null);
            const [showCashPayModal, setShowCashPayModal] = useState(false);
            const [userCashBalance, setUserCashBalance] = useState(null);
            const [workerPendingCommission, setWorkerPendingCommission] = useState(null);
            const [cashPayLoading, setCashPayLoading] = useState(false);
            const [cashPaySending, setCashPaySending] = useState(false);
            const [cashMethodId, setCashMethodId] = useState(null);
            const [cashTimelineTransactions, setCashTimelineTransactions] = useState([]);
            const [cashTimelineLoading, setCashTimelineLoading] = useState(false);
            
            // Get unique workers from completed jobs
            const uniqueWorkers = [...new Set(completedJobs.map(job => job.workerName || job.worker_name || job.worker).filter(Boolean))].sort();
            
            // Filter jobs based on date range and worker
            const filteredJobs = completedJobs.filter(job => {
                const jobDate = job.endTime ? new Date(job.endTime).toISOString().split('T')[0] : (job.created_at ? new Date(job.created_at).toISOString().split('T')[0] : '');
                const dateMatch = jobDate >= dateFrom && jobDate <= dateTo;
                const workerMatch = !selectedWorker || (job.workerName || job.worker_name || job.worker || '').toUpperCase() === selectedWorker.toUpperCase();
                return dateMatch && workerMatch;
            });

            // Close dropdown when clicking outside
            useEffect(() => {
                const handleClickOutside = (event) => {
                    if (openDropdownId && !event.target.closest('.dropdown-container')) {
                        setOpenDropdownId(null);
                    }
                };
                if (openDropdownId) {
                    document.addEventListener('mousedown', handleClickOutside);
                }
                return () => {
                    document.removeEventListener('mousedown', handleClickOutside);
                };
            }, [openDropdownId]);

            // Load completed jobs from backend
            useEffect(() => {
                fetch(API_ROUTES.jobs.completed)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.jobs) {
                            setCompletedJobs(data.jobs);
                        }
                    })
                    .catch(err => console.error('Error loading completed jobs:', err));
            }, []);

            // Load worker cash timeline when a worker is selected (Commission / Pay / Total balance)
            useEffect(() => {
                if (!selectedWorker) {
                    setCashTimelineTransactions([]);
                    return;
                }
                const jobWithWorker = completedJobs.find(j => (j.workerName || j.worker_name || j.worker) === selectedWorker);
                const workerId = jobWithWorker && (jobWithWorker.workerId != null || jobWithWorker.worker_id != null) ? (jobWithWorker.workerId ?? jobWithWorker.worker_id) : null;
                if (!workerId) {
                    setCashTimelineTransactions([]);
                    return;
                }
                setCashTimelineLoading(true);
                fetch(API_ROUTES.workers.cashTimeline(workerId), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.transactions) {
                            setCashTimelineTransactions(data.transactions);
                        } else {
                            setCashTimelineTransactions([]);
                        }
                    })
                    .catch(() => setCashTimelineTransactions([]))
                    .finally(() => setCashTimelineLoading(false));
            }, [selectedWorker, completedJobs]);

            return (
                <div className="min-h-screen bg-slate-50">
                    {/* Header */}
                    <header className="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white p-3 sm:p-4 md:p-6 shadow-2xl">
                        <div className="max-w-7xl mx-auto">
                            <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
                                <div className="flex-1 min-w-0">
                                    <h1 className="text-2xl sm:text-3xl md:text-4xl font-black uppercase tracking-tighter mb-1 sm:mb-2">Completed Jobs</h1>
                                    <p className="text-xs sm:text-sm opacity-90 truncate">{branchName} • {userName}</p>
                                </div>
                                <div className="flex flex-col gap-2 w-full sm:w-auto">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <button
                                            onClick={() => window.location.href = '{{ route("car.wash") }}'}
                                            className="px-3 sm:px-4 md:px-6 py-2 sm:py-2.5 md:py-3 bg-white/20 hover:bg-white/30 rounded-lg sm:rounded-xl text-xs sm:text-sm font-black uppercase transition-colors backdrop-blur-sm flex-1 sm:flex-initial"
                                        >
                                            <span className="hidden sm:inline">← Back to Car Wash</span>
                                            <span className="sm:hidden">← Back</span>
                                        </button>
                                        <button
                                            onClick={() => window.location.href = '{{ route("car.wash.daily-report") }}'}
                                            className="px-3 sm:px-4 md:px-6 py-2 sm:py-2.5 md:py-3 bg-white/20 hover:bg-white/30 rounded-lg sm:rounded-xl text-xs sm:text-sm font-black uppercase transition-colors backdrop-blur-sm flex-1 sm:flex-initial"
                                        >
                                            Daily Report
                                        </button>
                                    </div>
                                    <div className="flex flex-col gap-1.5 w-full">
                                        <div className="text-center sm:text-left">
                                            <span className="text-xs sm:text-sm font-bold uppercase opacity-90">Select Range</span>
                                        </div>
                                        <div className="flex items-center gap-1.5">
                                            <input
                                                type="date"
                                                value={dateFrom}
                                                onChange={(e) => setDateFrom(e.target.value)}
                                                className="flex-1 px-2 py-1.5 border-2 border-white/30 bg-white/10 rounded-lg text-white text-xs font-bold focus:border-white/50 focus:outline-none placeholder-white/50"
                                            />
                                            <span className="text-[10px] font-bold text-white/90 whitespace-nowrap">To</span>
                                            <input
                                                type="date"
                                                value={dateTo}
                                                onChange={(e) => setDateTo(e.target.value)}
                                                className="flex-1 px-2 py-1.5 border-2 border-white/30 bg-white/10 rounded-lg text-white text-xs font-bold focus:border-white/50 focus:outline-none placeholder-white/50"
                                            />
                                        </div>
                                    </div>
                                    <div className="flex flex-col gap-1.5 w-full sm:w-auto">
                                        <div className="text-center sm:text-left">
                                            <span className="text-xs sm:text-sm font-bold uppercase opacity-90 worker-label">Worker</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <select
                                                value={selectedWorker}
                                                onChange={(e) => setSelectedWorker(e.target.value)}
                                                className="flex-1 min-w-0 px-2 py-1.5 border-2 border-white/30 bg-white/10 rounded-lg text-white text-xs font-bold focus:border-white/50 focus:outline-none"
                                            >
                                                <option value="" className="bg-slate-800 text-white">All</option>
                                                {uniqueWorkers.map((worker, idx) => (
                                                    <option key={idx} value={worker} className="bg-slate-800 text-white">{worker}</option>
                                                ))}
                                            </select>
                                            <button
                                                type="button"
                                                disabled={!selectedWorker}
                                                onClick={async () => {
                                                    if (!selectedWorker) return;
                                                    const jobWithWorker = completedJobs.find(j => (j.workerName || j.worker_name || j.worker) === selectedWorker);
                                                    const workerId = jobWithWorker && (jobWithWorker.workerId != null || jobWithWorker.worker_id != null) ? (jobWithWorker.workerId ?? jobWithWorker.worker_id) : null;
                                                    if (!workerId) {
                                                        alert('Worker ID not found. Please try another worker or refresh the page.');
                                                        return;
                                                    }
                                                    setShowCashPayModal(true);
                                                    setCashPayLoading(true);
                                                    setUserCashBalance(null);
                                                    setWorkerPendingCommission(null);
                                                    try {
                                                        const [balanceRes, commissionRes, methodRes] = await Promise.all([
                                                            fetch(API_ROUTES.payments.cashAccountBalance, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } }),
                                                            fetch(API_ROUTES.payments.pendingCommission(workerId), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } }),
                                                            fetch(API_ROUTES.payments.cashMethod, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } }),
                                                        ]);
                                                        const balanceData = balanceRes.ok ? await balanceRes.json() : {};
                                                        const commissionData = commissionRes.ok ? await commissionRes.json() : {};
                                                        const methodData = methodRes.ok ? await methodRes.json() : {};
                                                        setUserCashBalance(balanceData.balance != null ? balanceData.balance : null);
                                                        setWorkerPendingCommission(commissionData.pending_commission != null ? commissionData.pending_commission : null);
                                                        setCashMethodId(methodData.id || null);
                                                    } catch (e) {
                                                        console.error(e);
                                                        alert('Failed to load cash pay data.');
                                                    }
                                                    setCashPayLoading(false);
                                                }}
                                                className="px-3 py-1.5 sm:px-4 sm:py-2 bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg text-white text-xs font-black uppercase transition-colors whitespace-nowrap flex-shrink-0"
                                                title={selectedWorker ? 'Pay commission by cash to selected worker' : 'Select a worker first'}
                                            >
                                                Cash Pay
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>

                    {/* Cash Pay Modal */}
                    {showCashPayModal && (
                        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" onClick={() => !cashPaySending && setShowCashPayModal(false)}>
                            <div className="bg-white rounded-2xl shadow-2xl border-2 border-slate-200 w-full max-w-md overflow-hidden" onClick={e => e.stopPropagation()}>
                                <div className="p-4 sm:p-6 border-b border-slate-200 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white">
                                    <h3 className="text-lg font-black uppercase">Cash Pay – {selectedWorker}</h3>
                                    <p className="text-xs opacity-90 mt-0.5">Pay commission from your cash account</p>
                                </div>
                                <div className="p-4 sm:p-6 space-y-4">
                                    {cashPayLoading ? (
                                        <div className="text-center py-6 text-slate-500 font-bold">Loading…</div>
                                    ) : (
                                        <>
                                            <div className="bg-slate-50 rounded-xl p-4 border-2 border-slate-200">
                                                <p className="text-xs font-bold text-slate-500 uppercase mb-1">Your cash account</p>
                                                <p className="text-xl font-black text-slate-900 font-mono">
                                                    Rs.{userCashBalance != null ? Number(userCashBalance).toFixed(2) : '—'}
                                                </p>
                                            </div>
                                            <div className="bg-emerald-50 rounded-xl p-4 border-2 border-emerald-200">
                                                <p className="text-xs font-bold text-emerald-700 uppercase mb-1">Worker total commission (pending)</p>
                                                <p className="text-xl font-black text-emerald-800 font-mono">
                                                    Rs.{workerPendingCommission != null ? Number(workerPendingCommission).toFixed(2) : '—'}
                                                </p>
                                            </div>
                                            <button
                                                type="button"
                                                disabled={cashPaySending || userCashBalance == null || workerPendingCommission == null || workerPendingCommission <= 0 || !cashMethodId || (userCashBalance != null && Number(userCashBalance) < Number(workerPendingCommission))}
                                                onClick={async () => {
                                                    const jobWithWorker = completedJobs.find(j => (j.workerName || j.worker_name || j.worker) === selectedWorker);
                                                    const workerId = jobWithWorker && (jobWithWorker.workerId != null || jobWithWorker.worker_id != null) ? (jobWithWorker.workerId ?? jobWithWorker.worker_id) : null;
                                                    if (!workerId || !cashMethodId || workerPendingCommission == null || workerPendingCommission <= 0) return;
                                                    setCashPaySending(true);
                                                    try {
                                                        const today = new Date().toISOString().split('T')[0];
                                                        const formData = new FormData();
                                                        formData.append('_token', csrfToken);
                                                        formData.append('payment_type', 'commission');
                                                        formData.append('worker_id', workerId);
                                                        formData.append('amount', Number(workerPendingCommission));
                                                        formData.append('payment_date', today);
                                                        formData.append('payment_method_id', cashMethodId);
                                                        formData.append('notes', 'Cash pay from Completed Jobs – ' + selectedWorker);
                                                        const jobIdsForWorker = completedJobs.filter(j => (j.workerId ?? j.worker_id) == workerId && (j.commissionAmount > 0)).map(j => j.id);
                                                        jobIdsForWorker.forEach(id => formData.append('job_ids[]', id));
                                                        const res = await fetch(API_ROUTES.payments.store, {
                                                            method: 'POST',
                                                            body: formData,
                                                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                                                        });
                                                        const data = res.ok ? await res.json() : {};
                                                        if (data.success) {
                                                            setShowCashPayModal(false);
                                                            const listRes = await fetch(API_ROUTES.jobs.completed);
                                                            const listData = listRes.ok ? await listRes.json() : {};
                                                            if (listData.success && listData.jobs) setCompletedJobs(listData.jobs);
                                                            alert('Payment successful.');
                                                        } else {
                                                            alert(data.message || 'Payment failed.');
                                                        }
                                                    } catch (e) {
                                                        console.error(e);
                                                        alert('Payment failed.');
                                                    }
                                                    setCashPaySending(false);
                                                }}
                                                className="w-full py-3 sm:py-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black uppercase rounded-xl transition-colors"
                                            >
                                                {cashPaySending ? 'Paying…' : 'Pay'}
                                            </button>
                                        </>
                                    )}
                                </div>
                                <div className="p-3 sm:p-4 border-t border-slate-200 bg-slate-50 flex justify-end">
                                    <button type="button" onClick={() => !cashPaySending && setShowCashPayModal(false)} disabled={cashPaySending} className="px-4 py-2 text-slate-600 font-bold hover:text-slate-900 disabled:opacity-50">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Main Content */}
                    <main className="max-w-7xl mx-auto p-6">
                        <div className="bg-white rounded-3xl shadow-xl border-2 border-slate-200 overflow-hidden">
                            {completedJobs.length === 0 ? (
                                <div className="text-center py-24">
                                    <div className="w-40 h-40 bg-gradient-to-br from-blue-300 via-indigo-300 to-purple-300 rounded-full flex items-center justify-center mx-auto mb-10 shadow-lg">
                                        <svg className="w-20 h-20 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <p className="text-3xl font-black text-slate-800 uppercase tracking-tight mb-3">No Completed Jobs Found</p>
                                    <p className="text-lg text-slate-500 mt-6 font-bold">Completed jobs will appear here once you complete them</p>
                                </div>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead className="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white">
                                            <tr>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">#</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">ITEM NAME</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Date/Time</th>
                                                <th className="px-3 sm:px-4 md:px-6 py-4 text-left text-[10px] sm:text-xs font-black uppercase tracking-wider w-[100px] sm:w-auto">Vehicle No</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Amount</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Commission</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Commission Paid</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Total Balance</th>
                                                <th className="px-6 py-4 text-center text-xs font-black uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-slate-200">
                                            {filteredJobs.map((job, jobIdx) => {
                                                const startTime = job.startTime ? new Date(job.startTime).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : 'N/A';
                                                const endTime = job.endTime ? new Date(job.endTime).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : 'N/A';
                                                const jobDate = job.endTime ? new Date(job.endTime).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A';
                                                const jobDateTime = jobDate !== 'N/A' ? `${jobDate} ${endTime}` : 'N/A';
                                                
                                                return (
                                                <tr key={job.id || jobIdx} className="hover:bg-slate-50 transition-colors">
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="flex items-center">
                                                            <div className="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white font-black text-sm shadow-lg">
                                                                {jobIdx + 1}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-normal leading-tight">
                                                        {(() => {
                                                            // Format date as DD/MM/YY
                                                            const formatDate = (dateStr) => {
                                                                if (!dateStr) return 'N/A';
                                                                const date = new Date(dateStr);
                                                                const day = String(date.getDate()).padStart(2, '0');
                                                                const month = String(date.getMonth() + 1).padStart(2, '0');
                                                                const year = String(date.getFullYear()).slice(-2);
                                                                return `${day}/${month}/${year}`;
                                                            };
                                                            
                                                            // Format time as HH:MM AM/PM
                                                            const formatTime = (dateStr) => {
                                                                if (!dateStr) return 'N/A';
                                                                const date = new Date(dateStr);
                                                                let hours = date.getHours();
                                                                const minutes = String(date.getMinutes()).padStart(2, '0');
                                                                const ampm = hours >= 12 ? 'PM' : 'AM';
                                                                hours = hours % 12;
                                                                hours = hours ? hours : 12;
                                                                return `${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;
                                                            };
                                                            
                                                            // Calculate total time
                                                            const calculateTotalTime = (startStr, endStr) => {
                                                                if (!startStr || !endStr) return '';
                                                                const start = new Date(startStr);
                                                                const end = new Date(endStr);
                                                                const diffMs = end - start;
                                                                const diffMins = Math.floor(diffMs / 60000);
                                                                const hours = Math.floor(diffMins / 60);
                                                                const mins = diffMins % 60;
                                                                if (hours > 0 && mins > 0) return `${hours}h ${mins}m`;
                                                                if (hours > 0) return `${hours}h`;
                                                                if (mins > 0) return `${mins}m`;
                                                                return '';
                                                            };
                                                            
                                                            const jobDate = job.endTime ? formatDate(job.endTime) : 'N/A';
                                                            const startTimeFormatted = job.startTime ? formatTime(job.startTime) : 'N/A';
                                                            const endTimeFormatted = job.endTime ? formatTime(job.endTime) : 'N/A';
                                                            const totalTime = calculateTotalTime(job.startTime, job.endTime);
                                                            const workerName = job.workerName || job.worker_name || '';
                                                            
                                                            return (
                                                                <div className="flex flex-col">
                                                                    <span className="font-bold text-slate-900">{jobDate}</span>
                                                                    {startTimeFormatted !== 'N/A' && endTimeFormatted !== 'N/A' && (
                                                                        <span className="text-[8px] sm:text-[9px] text-slate-600">{startTimeFormatted} - {endTimeFormatted}</span>
                                                                    )}
                                                                    {totalTime && (
                                                                        <span className="text-[8px] sm:text-[9px] font-semibold inline-flex items-center gap-0.5 text-slate-700">
                                                                            <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                            </svg>
                                                                            {totalTime}
                                                                        </span>
                                                                    )}
                                                                </div>
                                                            );
                                                        })()}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-normal leading-tight">
                                                        <div className="flex flex-col">
                                                            {(job.serviceName || job.service_name || job.service) && (
                                                                <span className="text-sm font-black text-slate-900 mb-0.5 whitespace-nowrap">{(job.serviceName || job.service_name || job.service).replace(/\s+/g, '-')}</span>
                                                            )}
                                                            <span className="font-semibold text-slate-900 whitespace-nowrap">{job.vehicleNo || job.vehicle_no || 'N/A'}</span>
                                                            {job.customerName || job.customer_name ? (
                                                                <span className="text-[8px] sm:text-[9px] text-slate-600">{job.customerName || job.customer_name}</span>
                                                            ) : null}
                                                            {job.mobile ? (
                                                                <span className="text-[8px] sm:text-[9px] text-slate-500">
                                                                    {job.mobile}
                                                                    {job.userName || job.user_name ? (
                                                                        <>
                                                                            <br />
                                                                            <span className="text-[8px] sm:text-[9px] text-slate-400 italic">({job.userName || job.user_name})</span>
                                                                        </>
                                                                    ) : null}
                                                                </span>
                                                            ) : job.userName || job.user_name ? (
                                                                <span className="text-[8px] sm:text-[9px] text-slate-400 italic">({job.userName || job.user_name})</span>
                                                            ) : null}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-black text-blue-600">Rs.{Math.round(job.price || 0)}</div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-normal leading-tight">
                                                        {job.workerCommission > 0 ? (
                                                            <div className="flex flex-col">
                                                                <div className="text-sm font-black text-emerald-600 font-mono">Rs.{Math.round(job.commissionAmount || 0)}</div>
                                                                <div className="text-xs text-slate-500">({job.workerCommission}%)</div>
                                                                {(job.workerName || job.worker_name || job.worker) && (
                                                                    <span className="text-[8px] sm:text-[9px] text-slate-600">({job.workerName || job.worker_name || job.worker})</span>
                                                                )}
                                                            </div>
                                                        ) : (
                                                            <div className="text-sm text-slate-400">-</div>
                                                        )}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="flex flex-col">
                                                            {(job.commissionPaid != null && Number(job.commissionPaid) > 0) ? (
                                                                <>
                                                                    <span className="text-sm font-black text-emerald-700 font-mono">{Math.round(Number(job.commissionPaid))}</span>
                                                                    <span className="text-xs font-bold text-slate-500 uppercase mt-0.5">paid</span>
                                                                </>
                                                            ) : (job.commissionAmount != null && job.commissionAmount > 0) ? (
                                                                <span className="text-sm font-mono text-slate-500">0</span>
                                                            ) : (
                                                                <span className="text-sm text-slate-400">—</span>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-black text-slate-800 font-mono">
                                                            {job.workerBalance != null ? `Rs.${Math.round(Number(job.workerBalance))}` : '—'}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-center">
                                                        <div className="relative inline-block dropdown-container">
                                                            <button
                                                                onClick={(e) => {
                                                                    e.stopPropagation();
                                                                    setOpenDropdownId(openDropdownId === job.id ? null : job.id);
                                                                }}
                                                                className="px-4 py-2 bg-slate-600 text-white rounded-lg text-xs font-black uppercase hover:bg-slate-700 transition-colors shadow-md flex items-center gap-2"
                                                                title="Actions"
                                                            >
                                                                Actions
                                                                <svg className={`w-4 h-4 transition-transform ${openDropdownId === job.id ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                                                                </svg>
                                                            </button>
                                                            {openDropdownId === job.id && (
                                                                <div className="absolute right-0 mt-1 w-36 bg-white rounded-lg shadow-xl border-2 border-slate-200 z-50 overflow-hidden">
                                                                    <button
                                                                        onClick={async (e) => {
                                                                            e.stopPropagation();
                                                                            setOpenDropdownId(null);
                                                                            try {
                                                                                // Load inspection
                                                                                let inspection = null;
                                                                                try {
                                                                                    const inspResponse = await fetch(API_ROUTES.inspections.show(job.id));
                                                                                    if (inspResponse.ok) {
                                                                                        const inspData = await inspResponse.json();
                                                                                        if (inspData.success) inspection = inspData.inspection;
                                                                                    }
                                                                                } catch (e) {
                                                                                    console.log('No inspection found');
                                                                                }
                                                                                
                                                                                // Load expense
                                                                                let expense = null;
                                                                                try {
                                                                                    const expResponse = await fetch(API_ROUTES.expenses.show(job.id));
                                                                                    if (expResponse.ok) {
                                                                                        const expData = await expResponse.json();
                                                                                        if (expData.success) expense = expData.expense;
                                                                                    }
                                                                                } catch (e) {
                                                                                    console.log('No expense found');
                                                                                }
                                                                                
                                                                                setSelectedJobForDetail({ ...job, inspection, expense });
                                                                            } catch (error) {
                                                                                console.error('Error loading job details:', error);
                                                                                setSelectedJobForDetail(job);
                                                                            }
                                                                        }}
                                                                        className="w-full px-4 py-2.5 text-left text-xs font-bold text-blue-600 hover:bg-blue-50 transition-colors flex items-center gap-2"
                                                                    >
                                                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                        </svg>
                                                                        Detail
                                                                    </button>
                                                                    <button
                                                                        onClick={(e) => {
                                                                            e.stopPropagation();
                                                                            setOpenDropdownId(null);
                                                                            setSelectedJobForEdit(job);
                                                                        }}
                                                                        className="w-full px-4 py-2.5 text-left text-xs font-bold text-emerald-600 hover:bg-emerald-50 transition-colors border-t border-slate-200 flex items-center gap-2"
                                                                    >
                                                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                        </svg>
                                                                        Edit
                                                                    </button>
                                                                    <button
                                                                        onClick={async (e) => {
                                                                            e.stopPropagation();
                                                                            setOpenDropdownId(null);
                                                                            if (confirm(`Are you sure you want to delete job #${jobIdx + 1}?`)) {
                                                                                try {
                                                                                    const response = await fetch(API_ROUTES.jobs.destroy(job.id), {
                                                                                        method: 'DELETE',
                                                                                        headers: {
                                                                                            'Content-Type': 'application/json',
                                                                                            'X-CSRF-TOKEN': csrfToken,
                                                                                            'Accept': 'application/json'
                                                                                        }
                                                                                    });
                                                                                    
                                                                                    const result = await response.json();
                                                                                    
                                                                                    if (result.success) {
                                                                                        setCompletedJobs(prev => prev.filter(j => j.id !== job.id));
                                                                                        alert('Job deleted successfully!');
                                                                                        
                                                                                        // Reload completed jobs from backend
                                                                                        const reloadResponse = await fetch(API_ROUTES.jobs.completed);
                                                                                        const reloadData = await reloadResponse.json();
                                                                                        if (reloadData.success && reloadData.jobs) {
                                                                                            setCompletedJobs(reloadData.jobs);
                                                                                        }
                                                                                    } else {
                                                                                        alert('Error deleting job: ' + (result.message || 'Unknown error'));
                                                                                    }
                                                                                } catch (error) {
                                                                                    console.error('Error deleting job:', error);
                                                                                    alert('Error deleting job. Please try again.');
                                                                                }
                                                                            }
                                                                        }}
                                                                        className="w-full px-4 py-2.5 text-left text-xs font-bold text-red-600 hover:bg-red-50 transition-colors border-t border-slate-200 flex items-center gap-2"
                                                                    >
                                                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                        </svg>
                                                                        Delete
                                                                    </button>
                                                                    {(job.workerId != null || job.worker_id != null) && (job.commissionAmount > 0 || job.workerCommission > 0) && (
                                                                        <button
                                                                            onClick={async (e) => {
                                                                                e.stopPropagation();
                                                                                setOpenDropdownId(null);
                                                                                const wid = job.workerId ?? job.worker_id;
                                                                                if (!confirm('Reverse the last cash commission payment for this worker? Only commission payment will be reversed. Your cash will be refunded and the amount added back to the worker\'s balance.')) return;
                                                                                try {
                                                                                    const response = await fetch(API_ROUTES.payments.reverseLastForWorker, {
                                                                                        method: 'POST',
                                                                                        headers: {
                                                                                            'Content-Type': 'application/json',
                                                                                            'X-CSRF-TOKEN': csrfToken,
                                                                                            'Accept': 'application/json'
                                                                                        },
                                                                                        body: JSON.stringify({ worker_id: wid, job_id: job.id })
                                                                                    });
                                                                                    const result = await response.json();
                                                                                    if (result.success) {
                                                                                        alert('Commission payment reversed successfully.');
                                                                                        const reloadResponse = await fetch(API_ROUTES.jobs.completed);
                                                                                        const reloadData = await reloadResponse.json();
                                                                                        if (reloadData.success && reloadData.jobs) setCompletedJobs(reloadData.jobs);
                                                                                        if (selectedWorker) {
                                                                                            const jobWithWorker = reloadData.jobs && reloadData.jobs.find(j => (j.workerName || j.worker_name || j.worker) === selectedWorker);
                                                                                            const sid = jobWithWorker && (jobWithWorker.workerId != null || jobWithWorker.worker_id != null) ? (jobWithWorker.workerId ?? jobWithWorker.worker_id) : null;
                                                                                            if (sid) {
                                                                                                const tlRes = await fetch(API_ROUTES.workers.cashTimeline(sid), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                                                                                                const tlData = await tlRes.json();
                                                                                                if (tlData.success && tlData.transactions) setCashTimelineTransactions(tlData.transactions);
                                                                                            }
                                                                                        }
                                                                                    } else {
                                                                                        alert('Error: ' + (result.message || 'Could not reverse commission payment.'));
                                                                                    }
                                                                                } catch (err) {
                                                                                    console.error(err);
                                                                                    alert('Error reversing commission payment. Please try again.');
                                                                                }
                                                                            }}
                                                                            className="w-full px-4 py-2.5 text-left text-xs font-bold text-amber-700 hover:bg-amber-50 transition-colors border-t border-slate-200 flex items-center gap-2"
                                                                        >
                                                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                                            </svg>
                                                                            Reverse commission payment
                                                                        </button>
                                                                    )}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </td>
                                                </tr>
                                                );
                                            })}
                                        </tbody>
                                        <tfoot className="bg-slate-100 border-t-2 border-slate-300">
                                            <tr>
                                                <td className="px-6 py-3 text-xs font-black text-slate-600 uppercase" colSpan="5">Total earning (commission sum)</td>
                                                                <td className="px-6 py-3 whitespace-nowrap">
                                                    <div className="text-sm font-black text-emerald-700 font-mono">
                                                        Rs {Math.round(filteredJobs.reduce((sum, j) => sum + (Number(j.commissionPaid) || 0), 0))}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-3"></td>
                                                <td className="px-6 py-3"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            )}

                            {/* Commission / Pay / Total balance (running) – when a worker is selected */}
                            {selectedWorker && (
                                <div className="mt-6 bg-white rounded-3xl shadow-xl border-2 border-slate-200 overflow-hidden">
                                    <div className="px-6 py-4 border-b border-slate-200 bg-slate-50">
                                        <h3 className="text-base font-black text-slate-800 uppercase tracking-tight">Commission / Pay / Total balance</h3>
                                        <p className="text-xs text-slate-500 mt-0.5">{selectedWorker} – running balance</p>
                                    </div>
                                    <div className="overflow-x-auto">
                                        {cashTimelineLoading ? (
                                            <div className="p-8 text-center text-slate-500 font-bold">Loading…</div>
                                        ) : (
                                            <table className="w-full min-w-[320px]">
                                                <thead className="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white">
                                                    <tr>
                                                        <th className="px-6 py-3 text-left text-xs font-black uppercase tracking-wider">Commission</th>
                                                        <th className="px-6 py-3 text-left text-xs font-black uppercase tracking-wider">Pay</th>
                                                        <th className="px-6 py-3 text-left text-xs font-black uppercase tracking-wider">Total balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody className="bg-white divide-y divide-slate-200">
                                                    {(() => {
                                                        let running = 0;
                                                        return cashTimelineTransactions.length === 0 ? (
                                                            <tr><td colSpan="3" className="px-6 py-8 text-center text-slate-500">No transactions yet.</td></tr>
                                                        ) : (
                                                            cashTimelineTransactions.map((tx, idx) => {
                                                                if (tx.type === 'credit') {
                                                                    running += Number(tx.amount) || 0;
                                                                    return (
                                                                        <tr key={idx} className="hover:bg-slate-50">
                                                                            <td className="px-6 py-3 text-sm font-black text-slate-800 font-mono">{Math.round(tx.amount)}</td>
                                                                            <td className="px-6 py-3 text-slate-400">—</td>
                                                                            <td className="px-6 py-3 text-sm font-black text-emerald-700 font-mono">{Math.round(running)}</td>
                                                                        </tr>
                                                                    );
                                                                } else {
                                                                    running -= Number(tx.amount) || 0;
                                                                    return (
                                                                        <tr key={idx} className="hover:bg-slate-50">
                                                                            <td className="px-6 py-3 text-slate-400">—</td>
                                                                            <td className="px-6 py-3 text-sm font-black text-slate-800 font-mono">{Math.round(tx.amount)}</td>
                                                                            <td className="px-6 py-3 text-sm font-black text-emerald-700 font-mono">{Math.round(running)}</td>
                                                                        </tr>
                                                                    );
                                                                }
                                                            })
                                                        );
                                                    })()}
                                                </tbody>
                                            </table>
                                        )}
                                    </div>
                                </div>
                            )}
                        </div>
                    </main>

                    {/* Job Detail Modal with Print - Full Screen */}
                    {selectedJobForDetail && (
                        <div className="fixed inset-0 bg-black/80 backdrop-blur-xl z-50 flex items-center justify-center p-2" onClick={() => setSelectedJobForDetail(null)}>
                            <div className="bg-white rounded-2xl shadow-2xl w-full h-full max-w-[98vw] max-h-[98vh] overflow-hidden flex flex-col" onClick={(e) => e.stopPropagation()} id="jobDetailPrint">
                                {/* Header */}
                                <div className="print-header p-6 border-b border-slate-200 bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <h2 className="text-2xl font-black uppercase tracking-tighter">Job Details</h2>
                                            <p className="text-sm opacity-90 mt-1">Complete job information with inspections and expenses</p>
                                            <p className="text-xs opacity-80 mt-2">
                                                {selectedJobForDetail.endTime ? new Date(selectedJobForDetail.endTime).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) : ''}
                                            </p>
                                        </div>
                                        <div className="no-print flex items-center gap-3">
                                            <a
                                                href={`/car-wash/jobs/${selectedJobForDetail.id}`}
                                                target="_blank"
                                                className="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-xs font-black uppercase transition-colors backdrop-blur-sm flex items-center gap-2"
                                                title="Open in New Page"
                                            >
                                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                                New Page
                                            </a>
                                            <button
                                                onClick={() => setSelectedJobForDetail(null)}
                                                className="text-white hover:text-slate-200 transition-colors p-2 rounded-lg hover:bg-white/20"
                                                aria-label="Close job details"
                                            >
                                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                {/* Job Info - Full Scrollable */}
                                <div className="flex-1 overflow-y-auto p-6 bg-gradient-to-b from-slate-50 to-white">
                                    <div className="space-y-4 max-w-7xl mx-auto">
                                        {/* Customer & Vehicle Info */}
                                        <div className="print-section grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div className="print-card bg-slate-50 p-4 rounded-xl border-2 border-slate-200">
                                                <p className="text-xs font-black text-slate-500 uppercase mb-2">Vehicle No</p>
                                                <p className="text-lg font-black text-slate-900">{selectedJobForDetail.vehicleNo || selectedJobForDetail.vehicle_no || 'N/A'}</p>
                                            </div>
                                            <div className="print-card bg-slate-50 p-4 rounded-xl border-2 border-slate-200">
                                                <p className="text-xs font-black text-slate-500 uppercase mb-2">Customer</p>
                                                <p className="text-lg font-black text-slate-900">{selectedJobForDetail.customerName || selectedJobForDetail.customer_name || 'N/A'}</p>
                                            </div>
                                            <div className="print-card bg-slate-50 p-4 rounded-xl border-2 border-slate-200">
                                                <p className="text-xs font-black text-slate-500 uppercase mb-2">Mobile</p>
                                                <p className="text-lg font-black text-slate-900">{selectedJobForDetail.mobile || 'N/A'}</p>
                                            </div>
                                        </div>
                                        
                                        {/* Service & Worker */}
                                        <div className="print-section grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div className="print-card bg-slate-50 p-4 rounded-xl border-2 border-slate-200">
                                                <p className="text-xs font-black text-slate-500 uppercase mb-2">Service</p>
                                                <p className="text-lg font-black text-slate-900">{selectedJobForDetail.serviceName || selectedJobForDetail.service_name || selectedJobForDetail.service || 'N/A'}</p>
                                            </div>
                                            <div className="print-card bg-slate-50 p-4 rounded-xl border-2 border-slate-200">
                                                <p className="text-xs font-black text-slate-500 uppercase mb-2">Worker</p>
                                                <p className="text-lg font-black text-slate-900">{selectedJobForDetail.workerName || selectedJobForDetail.worker_name || selectedJobForDetail.worker || 'N/A'}</p>
                                            </div>
                                        </div>
                                        
                                        {/* Time, Amount & Commission */}
                                        <div className="print-section grid grid-cols-1 md:grid-cols-4 gap-4">
                                            <div className="print-card bg-slate-50 p-4 rounded-xl border-2 border-slate-200">
                                                <p className="text-xs font-black text-slate-500 uppercase mb-2">Start Time</p>
                                                <p className="text-lg font-black text-slate-900">
                                                    {selectedJobForDetail.startTime ? new Date(selectedJobForDetail.startTime).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A'}
                                                </p>
                                            </div>
                                            <div className="print-card bg-slate-50 p-4 rounded-xl border-2 border-slate-200">
                                                <p className="text-xs font-black text-slate-500 uppercase mb-2">End Time</p>
                                                <p className="text-lg font-black text-slate-900">
                                                    {selectedJobForDetail.endTime ? new Date(selectedJobForDetail.endTime).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A'}
                                                </p>
                                            </div>
                                            <div className="print-amount print-card bg-gradient-to-br from-blue-500 to-indigo-600 p-4 rounded-xl border-2 border-blue-400">
                                                <p className="text-xs font-black text-white/90 uppercase mb-2">Amount</p>
                                                <p className="text-2xl font-black text-white">Rs.{Math.round(selectedJobForDetail.price || 0)}</p>
                                            </div>
                                            <div className="print-commission print-card bg-gradient-to-br from-emerald-500 to-green-600 p-4 rounded-xl border-2 border-emerald-400">
                                                <p className="text-xs font-black text-white/90 uppercase mb-2">Commission</p>
                                                {selectedJobForDetail.workerCommission > 0 ? (
                                                    <div>
                                                        <p className="text-2xl font-black text-white font-mono">Rs.{Math.round(selectedJobForDetail.commissionAmount || 0)}</p>
                                                        <p className="text-xs text-white/80 mt-1">({selectedJobForDetail.workerCommission}%)</p>
                                                    </div>
                                                ) : (
                                                    <p className="text-lg font-black text-white/70">-</p>
                                                )}
                                            </div>
                                        </div>
                                        
                                        {/* Inspection Details */}
                                        {selectedJobForDetail.inspection && selectedJobForDetail.inspection.inspectionItems && (
                                            <div className="print-section bg-purple-50 p-6 rounded-xl border-2 border-purple-200">
                                                <h3 className="text-lg font-black text-purple-900 uppercase mb-4">Inspection Details</h3>
                                                <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
                                                    {Object.keys(selectedJobForDetail.inspection.inspectionItems).map(itemId => {
                                                        const item = selectedJobForDetail.inspection.inspectionItems[itemId];
                                                        const itemNames = {
                                                            'engine_oil': 'Engine Oil',
                                                            'gear_oil': 'Gear Oil',
                                                            'brake_oil': 'Brake Oil',
                                                            'air_filter': 'Air Filter',
                                                            'radiator_water': 'Radiator Water',
                                                            'shower_water': 'Shower Water',
                                                            'power_oil': 'Power Oil',
                                                            'horn': 'Horn',
                                                            'head_lights': 'Head Lights',
                                                            'indicator': 'Indicator',
                                                            'brake_pad': 'Brake Pad',
                                                            'ac_filter': 'AC Filter'
                                                        };
                                                        const statusIcons = {
                                                            'excellent': '⭐',
                                                            'good': '✅',
                                                            'average': '⚠️',
                                                            'poor': '❌'
                                                        };
                                                        return (
                                                            <div key={itemId} className="bg-white p-3 rounded-lg border border-purple-200">
                                                                <p className="text-xs font-black text-slate-600 uppercase mb-1">{itemNames[itemId] || itemId}</p>
                                                                <p className="text-sm font-black text-purple-700">{statusIcons[item.status] || '⚪'} {item.status || 'N/A'}</p>
                                                            </div>
                                                        );
                                                    })}
                                                </div>
                                            </div>
                                        )}
                                        
                                        {/* Expense Details */}
                                        {selectedJobForDetail.expense && selectedJobForDetail.expense.expenseItems && selectedJobForDetail.expense.expenseItems.length > 0 && (
                                            <div className="print-section bg-orange-50 p-6 rounded-xl border-2 border-orange-200">
                                                <h3 className="text-lg font-black text-orange-900 uppercase mb-4">Expense Details</h3>
                                                <div className="space-y-2">
                                                    {selectedJobForDetail.expense.expenseItems.map((item, idx) => (
                                                        <div key={idx} className="bg-white p-3 rounded-lg border border-orange-200 flex justify-between items-center">
                                                            <div>
                                                                <p className="text-sm font-black text-slate-900">{item.name}</p>
                                                                <p className="text-xs text-slate-500">Qty: {item.quantity} × Rs.{item.price}</p>
                                                            </div>
                                                            <p className="text-sm font-black text-orange-600">Rs.{Math.round(item.total || (item.quantity * item.price))}</p>
                                                        </div>
                                                    ))}
                                                    <div className="bg-orange-200 p-3 rounded-lg border-2 border-orange-300 flex justify-between items-center mt-4">
                                                        <p className="text-sm font-black text-orange-900 uppercase">Total Expense</p>
                                                        <p className="text-lg font-black text-orange-900">Rs.{Math.round(selectedJobForDetail.expense.totalAmount || 0)}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        )}
                                        
                                        {/* Summary Section */}
                                        <div className="print-section bg-gradient-to-br from-slate-100 to-slate-200 p-6 rounded-xl border-2 border-slate-300">
                                            <h3 className="text-lg font-black text-slate-900 uppercase mb-4">Summary</h3>
                                            <div className="space-y-3">
                                                <div className="flex justify-between items-center bg-white p-3 rounded-lg">
                                                    <p className="text-sm font-black text-slate-700">Total Amount:</p>
                                                    <p className="text-lg font-black text-blue-600 font-mono">Rs.{Math.round(selectedJobForDetail.price || 0)}</p>
                                                </div>
                                                {selectedJobForDetail.workerCommission > 0 && (
                                                    <div className="flex justify-between items-center bg-white p-3 rounded-lg">
                                                        <p className="text-sm font-black text-slate-700">Worker Commission ({selectedJobForDetail.workerCommission}%):</p>
                                                        <p className="text-lg font-black text-emerald-600 font-mono">Rs.{Math.round(selectedJobForDetail.commissionAmount || 0)}</p>
                                                    </div>
                                                )}
                                                {selectedJobForDetail.expense && selectedJobForDetail.expense.totalAmount > 0 && (
                                                    <div className="flex justify-between items-center bg-white p-3 rounded-lg">
                                                        <p className="text-sm font-black text-slate-700">Total Expenses:</p>
                                                        <p className="text-lg font-black text-orange-600 font-mono">Rs.{Math.round(selectedJobForDetail.expense.totalAmount || 0)}</p>
                                                    </div>
                                                )}
                                                <div className="flex justify-between items-center bg-gradient-to-r from-blue-500 to-indigo-600 p-4 rounded-lg mt-4">
                                                    <p className="text-base font-black text-white uppercase">Net Amount:</p>
                                                    <p className="text-xl font-black text-white font-mono">
                                                        Rs.{Math.round((selectedJobForDetail.price || 0) - (selectedJobForDetail.commissionAmount || 0) - (selectedJobForDetail.expense?.totalAmount || 0))}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {/* Notes/Comments */}
                                        {selectedJobForDetail.notes && (
                                            <div className="print-section bg-slate-50 p-4 rounded-xl border-2 border-slate-200">
                                                <p className="text-xs font-black text-slate-500 uppercase mb-2">Notes</p>
                                                <p className="text-sm text-slate-900">{selectedJobForDetail.notes}</p>
                                            </div>
                                        )}
                                    </div>
                                </div>
                                
                                {/* Footer with Print & PDF Buttons */}
                                <div className="no-print p-6 border-t border-slate-200 bg-slate-50">
                                    <div className="flex gap-4">
                                        <button
                                            onClick={() => setSelectedJobForDetail(null)}
                                            className="flex-1 px-6 py-3 bg-slate-700 text-white rounded-xl text-sm font-black uppercase hover:bg-slate-800 transition-colors"
                                        >
                                            Close
                                        </button>
                                        <button
                                            onClick={() => {
                                                window.print();
                                            }}
                                            className="flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl text-sm font-black uppercase hover:bg-blue-700 transition-colors flex items-center justify-center gap-2"
                                        >
                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                            Print
                                        </button>
                                        <button
                                            onClick={() => {
                                                const element = document.getElementById('jobDetailPrint');
                                                const opt = {
                                                    margin: [10, 10, 10, 10],
                                                    filename: `job-${selectedJobForDetail.id || 'detail'}-${new Date().toISOString().split('T')[0]}.pdf`,
                                                    image: { type: 'jpeg', quality: 0.98 },
                                                    html2canvas: { scale: 2, useCORS: true },
                                                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                                                };
                                                
                                                html2pdf().set(opt).from(element).save();
                                            }}
                                            className="flex-1 px-6 py-3 bg-emerald-600 text-white rounded-xl text-sm font-black uppercase hover:bg-emerald-700 transition-colors flex items-center justify-center gap-2"
                                        >
                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Download PDF
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Job Edit Modal - Same as in car-wash.blade.php */}
                    {selectedJobForEdit && (
                        <div className="fixed inset-0 bg-black/80 backdrop-blur-xl z-50 flex items-center justify-center p-4" onClick={() => setSelectedJobForEdit(null)}>
                            <div className="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col" onClick={(e) => e.stopPropagation()}>
                                <div className="p-6 border-b border-slate-200 bg-gradient-to-r from-emerald-600 to-green-600 text-white">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <h2 className="text-2xl font-black uppercase tracking-tighter">Edit Job</h2>
                                            <p className="text-sm opacity-90 mt-1">Update job information</p>
                                        </div>
                                        <button onClick={() => setSelectedJobForEdit(null)} className="text-white hover:text-slate-200 transition-colors p-2 rounded-lg hover:bg-white/20">
                                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div className="flex-1 overflow-y-auto p-6">
                                    <div className="space-y-4">
                                        <div>
                                            <label className="text-sm font-black text-slate-900 uppercase block mb-2">Customer Name</label>
                                            <input type="text" id="editCustomerName" defaultValue={selectedJobForEdit.customerName || selectedJobForEdit.customer_name || ''} className="w-full px-4 py-3 text-sm text-slate-900 border-2 border-slate-300 rounded-xl bg-white focus:border-emerald-500 focus:outline-none" />
                                        </div>
                                        <div>
                                            <label className="text-sm font-black text-slate-900 uppercase block mb-2">Vehicle No</label>
                                            <input type="text" id="editVehicleNo" defaultValue={selectedJobForEdit.vehicleNo || selectedJobForEdit.vehicle_no || ''} className="w-full px-4 py-3 text-sm text-slate-900 border-2 border-slate-300 rounded-xl bg-white focus:border-emerald-500 focus:outline-none" />
                                        </div>
                                        <div>
                                            <label className="text-sm font-black text-slate-900 uppercase block mb-2">Mobile</label>
                                            <input type="tel" id="editMobile" defaultValue={selectedJobForEdit.mobile || ''} className="w-full px-4 py-3 text-sm text-slate-900 border-2 border-slate-300 rounded-xl bg-white focus:border-emerald-500 focus:outline-none" />
                                        </div>
                                        <div>
                                            <label className="text-sm font-black text-slate-900 uppercase block mb-2">Service</label>
                                            <input type="text" id="editService" defaultValue={selectedJobForEdit.serviceName || selectedJobForEdit.service_name || selectedJobForEdit.service || ''} className="w-full px-4 py-3 text-sm text-slate-900 border-2 border-slate-300 rounded-xl bg-white focus:border-emerald-500 focus:outline-none" />
                                        </div>
                                        <div>
                                            <label className="text-sm font-black text-slate-900 uppercase block mb-2">Worker</label>
                                            <input type="text" id="editWorker" defaultValue={selectedJobForEdit.workerName || selectedJobForEdit.worker_name || selectedJobForEdit.worker || ''} className="w-full px-4 py-3 text-sm text-slate-900 border-2 border-slate-300 rounded-xl bg-white focus:border-emerald-500 focus:outline-none" />
                                        </div>
                                        <div>
                                            <label className="text-sm font-black text-slate-900 uppercase block mb-2">Amount (Rs.)</label>
                                            <input type="number" id="editAmount" defaultValue={selectedJobForEdit.price || 0} min="0" step="1" className="w-full px-4 py-3 text-sm text-slate-900 border-2 border-slate-300 rounded-xl bg-white focus:border-emerald-500 focus:outline-none" />
                                        </div>
                                        <div>
                                            <label className="text-sm font-black text-slate-900 uppercase block mb-2">Notes</label>
                                            <textarea id="editNotes" defaultValue={selectedJobForEdit.notes || ''} rows="3" className="w-full px-4 py-3 text-sm text-slate-900 border-2 border-slate-300 rounded-xl bg-white focus:border-emerald-500 focus:outline-none resize-none" />
                                        </div>
                                    </div>
                                </div>
                                <div className="p-6 border-t border-slate-200 bg-slate-50">
                                    <div className="flex gap-4">
                                        <button onClick={() => setSelectedJobForEdit(null)} className="flex-1 px-6 py-3 bg-slate-700 text-white rounded-xl text-sm font-black uppercase hover:bg-slate-800 transition-colors">Cancel</button>
                                        <button onClick={async () => {
                                            const updateData = {
                                                customer_name: document.getElementById('editCustomerName').value.trim().toUpperCase(),
                                                vehicle_no: document.getElementById('editVehicleNo').value.trim().toUpperCase(),
                                                mobile: document.getElementById('editMobile').value.trim(),
                                                service_name: document.getElementById('editService').value.trim().toUpperCase(),
                                                worker_name: document.getElementById('editWorker').value.trim().toUpperCase(),
                                                price: parseFloat(document.getElementById('editAmount').value) || 0,
                                                notes: document.getElementById('editNotes').value.trim()
                                            };
                                            try {
                                                const response = await fetch(API_ROUTES.jobs.update(selectedJobForEdit.id), {
                                                    method: 'PUT',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': csrfToken,
                                                        'Accept': 'application/json'
                                                    },
                                                    body: JSON.stringify(updateData)
                                                });
                                                const result = await response.json();
                                                if (result.success) {
                                                    alert('Job updated successfully!');
                                                    setSelectedJobForEdit(null);
                                                    const reloadResponse = await fetch(API_ROUTES.jobs.completed);
                                                    const reloadData = await reloadResponse.json();
                                                    if (reloadData.success && reloadData.jobs) {
                                                        setCompletedJobs(reloadData.jobs);
                                                    }
                                                } else {
                                                    alert('Error updating job: ' + (result.message || 'Unknown error'));
                                                }
                                            } catch (error) {
                                                console.error('Error updating job:', error);
                                                alert('Error updating job. Please try again.');
                                            }
                                        }} className="flex-1 px-6 py-3 bg-emerald-600 text-white rounded-xl text-sm font-black uppercase hover:bg-emerald-700 transition-colors">Update Job</button>
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
