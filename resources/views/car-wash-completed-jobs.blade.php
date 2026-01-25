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
        };

        function App() {
            const [completedJobs, setCompletedJobs] = useState(initialCompletedJobs);
            const [selectedJobForDetail, setSelectedJobForDetail] = useState(null);
            const [selectedJobForEdit, setSelectedJobForEdit] = useState(null);

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

            return (
                <div className="min-h-screen bg-slate-50">
                    {/* Header */}
                    <header className="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white p-6 shadow-2xl">
                        <div className="max-w-7xl mx-auto">
                            <div className="flex items-center justify-between">
                                <div>
                                    <h1 className="text-4xl font-black uppercase tracking-tighter mb-2">Completed Jobs</h1>
                                    <p className="text-sm opacity-90">{branchName} • {userName}</p>
                                </div>
                                <div className="flex items-center gap-2">
                                    <button
                                        onClick={() => window.location.href = '{{ route("car.wash") }}'}
                                        className="px-6 py-3 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-black uppercase transition-colors backdrop-blur-sm"
                                    >
                                        ← Back to Car Wash
                                    </button>
                                    <button
                                        onClick={() => window.location.href = '{{ route("car.wash.daily-report") }}'}
                                        className="px-6 py-3 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-black uppercase transition-colors backdrop-blur-sm"
                                    >
                                        Daily Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </header>

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
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Date/Time</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Vehicle No</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Customer</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Service</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Worker</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Amount</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Commission</th>
                                                <th className="px-6 py-4 text-center text-xs font-black uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-slate-200">
                                            {completedJobs.map((job, jobIdx) => {
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
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-black text-slate-900">{jobDateTime}</div>
                                                        <div className="text-xs text-slate-500">{startTime} - {endTime}</div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-black text-slate-900">{job.vehicleNo || job.vehicle_no || 'N/A'}</div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-black text-slate-900">{job.customerName || job.customer_name || 'N/A'}</div>
                                                        {job.mobile && <div className="text-xs text-slate-500">{job.mobile}</div>}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-black text-slate-900">{job.serviceName || job.service_name || job.service || 'N/A'}</div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-black text-slate-900">{job.workerName || job.worker_name || job.worker || 'N/A'}</div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-black text-blue-600">Rs.{(job.price || 0).toFixed(2)}</div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        {job.workerCommission > 0 ? (
                                                            <div>
                                                                <div className="text-sm font-black text-emerald-600 font-mono">Rs.{(job.commissionAmount || 0).toFixed(2)}</div>
                                                                <div className="text-xs text-slate-500">({job.workerCommission}%)</div>
                                                            </div>
                                                        ) : (
                                                            <div className="text-sm text-slate-400">-</div>
                                                        )}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-center">
                                                        <div className="flex items-center justify-center gap-2">
                                                            <button
                                                                onClick={async () => {
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
                                                                className="px-3 py-2 bg-blue-500 text-white rounded-lg text-xs font-black uppercase hover:bg-blue-600 transition-colors shadow-md"
                                                                title="View Details"
                                                            >
                                                                Detail
                                                            </button>
                                                            <button
                                                                onClick={() => setSelectedJobForEdit(job)}
                                                                className="px-3 py-2 bg-emerald-500 text-white rounded-lg text-xs font-black uppercase hover:bg-emerald-600 transition-colors shadow-md"
                                                                title="Edit Job"
                                                            >
                                                                Edit
                                                            </button>
                                                            <button
                                                                onClick={async () => {
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
                                                                className="px-3 py-2 bg-red-500 text-white rounded-lg text-xs font-black uppercase hover:bg-red-600 transition-colors shadow-md"
                                                                title="Delete Job"
                                                            >
                                                                Delete
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                );
                                            })}
                                        </tbody>
                                    </table>
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
                                                <p className="text-2xl font-black text-white">Rs.{(selectedJobForDetail.price || 0).toFixed(2)}</p>
                                            </div>
                                            <div className="print-commission print-card bg-gradient-to-br from-emerald-500 to-green-600 p-4 rounded-xl border-2 border-emerald-400">
                                                <p className="text-xs font-black text-white/90 uppercase mb-2">Commission</p>
                                                {selectedJobForDetail.workerCommission > 0 ? (
                                                    <div>
                                                        <p className="text-2xl font-black text-white font-mono">Rs.{(selectedJobForDetail.commissionAmount || 0).toFixed(2)}</p>
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
                                                            <p className="text-sm font-black text-orange-600">Rs.{(item.total || (item.quantity * item.price)).toFixed(2)}</p>
                                                        </div>
                                                    ))}
                                                    <div className="bg-orange-200 p-3 rounded-lg border-2 border-orange-300 flex justify-between items-center mt-4">
                                                        <p className="text-sm font-black text-orange-900 uppercase">Total Expense</p>
                                                        <p className="text-lg font-black text-orange-900">Rs.{(selectedJobForDetail.expense.totalAmount || 0).toFixed(2)}</p>
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
                                                    <p className="text-lg font-black text-blue-600 font-mono">Rs.{(selectedJobForDetail.price || 0).toFixed(2)}</p>
                                                </div>
                                                {selectedJobForDetail.workerCommission > 0 && (
                                                    <div className="flex justify-between items-center bg-white p-3 rounded-lg">
                                                        <p className="text-sm font-black text-slate-700">Worker Commission ({selectedJobForDetail.workerCommission}%):</p>
                                                        <p className="text-lg font-black text-emerald-600 font-mono">Rs.{(selectedJobForDetail.commissionAmount || 0).toFixed(2)}</p>
                                                    </div>
                                                )}
                                                {selectedJobForDetail.expense && selectedJobForDetail.expense.totalAmount > 0 && (
                                                    <div className="flex justify-between items-center bg-white p-3 rounded-lg">
                                                        <p className="text-sm font-black text-slate-700">Total Expenses:</p>
                                                        <p className="text-lg font-black text-orange-600 font-mono">Rs.{(selectedJobForDetail.expense.totalAmount || 0).toFixed(2)}</p>
                                                    </div>
                                                )}
                                                <div className="flex justify-between items-center bg-gradient-to-r from-blue-500 to-indigo-600 p-4 rounded-lg mt-4">
                                                    <p className="text-base font-black text-white uppercase">Net Amount:</p>
                                                    <p className="text-xl font-black text-white font-mono">
                                                        Rs.{((selectedJobForDetail.price || 0) - (selectedJobForDetail.commissionAmount || 0) - (selectedJobForDetail.expense?.totalAmount || 0)).toFixed(2)}
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
