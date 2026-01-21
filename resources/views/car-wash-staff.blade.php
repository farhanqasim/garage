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
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <div id="root"></div>

    <script type="text/babel">
        const { useState, useEffect } = React;

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
            idCardFront: w.id_card_front ? `/storage/${w.id_card_front}` : null,
            idCardBack: w.id_card_back ? `/storage/${w.id_card_back}` : null,
            fatherCardFront: w.father_card_front ? `/storage/${w.father_card_front}` : null,
            fatherCardBack: w.father_card_back ? `/storage/${w.father_card_back}` : null,
            status: w.status !== undefined ? w.status : true,
            dailyJobsCount: w.daily_jobs_count ?? 0,
            dailyCommission: w.daily_commission ?? 0,
        }));
        const branchName = @json($branchName);
        const userName = @json($userName);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const API_ROUTES = {
            workers: {
                index: '{{ route("car-wash.workers.index") }}',
                store: '{{ route("car-wash.workers.store") }}',
                update: (id) => `/car-wash/workers/${id}`,
                destroy: (id) => `/car-wash/workers/${id}`,
            },
        };

        function App() {
            const [workers, setWorkers] = useState(initialWorkers);
            const [selectedWorkerForEdit, setSelectedWorkerForEdit] = useState(null);
            const [showAddModal, setShowAddModal] = useState(false);

            // Load workers from backend
            useEffect(() => {
                fetch(API_ROUTES.workers.index)
                    .then(res => res.json())
                    .then(data => {
                                                        if (data.success && data.workers) {
                                                            const workersData = data.workers || [];
                                                            setWorkers(workersData.map(w => ({
                                                                id: w.id,
                                                                name: w.name,
                                                                mobile: w.mobile,
                                                                additionalMobiles: w.additional_mobiles ?? w.additionalMobiles ?? [],
                                                                fatherName: w.father_name ?? w.fatherName,
                                                                fatherMobile: w.father_mobile ?? w.fatherMobile,
                                                                fatherAdditionalMobiles: w.father_additional_mobiles ?? w.fatherAdditionalMobiles ?? [],
                                                                location: w.location,
                                                                commission: w.commission,
                                                                idCardFront: w.id_card_front ? `/storage/${w.id_card_front}` : (w.idCardFront || null),
                                                                idCardBack: w.id_card_back ? `/storage/${w.id_card_back}` : (w.idCardBack || null),
                                                                fatherCardFront: w.father_card_front ? `/storage/${w.father_card_front}` : (w.fatherCardFront || null),
                                                                fatherCardBack: w.father_card_back ? `/storage/${w.father_card_back}` : (w.fatherCardBack || null),
                                                                status: w.status !== undefined ? w.status : true,
                                                                dailyJobsCount: w.daily_jobs_count ?? w.dailyJobsCount ?? 0,
                                                                dailyCommission: w.daily_commission ?? w.dailyCommission ?? 0,
                                                            })));
                                                        }
                    })
                    .catch(err => console.error('Error loading workers:', err));
            }, []);

            return (
                <div className="min-h-screen bg-slate-50">
                    {/* Header */}
                    <header className="bg-purple-600 text-white p-6 shadow-2xl">
                        <div className="max-w-7xl mx-auto">
                            <div className="flex items-center justify-between">
                                <div>
                                    <h1 className="text-4xl font-black uppercase tracking-tighter mb-2">Staff Management</h1>
                                    <p className="text-sm opacity-90">{branchName} • {userName}</p>
                                </div>
                                <div className="flex gap-3">
                                    <button
                                        onClick={() => setShowAddModal(true)}
                                        className="px-6 py-3 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-black uppercase transition-colors backdrop-blur-sm flex items-center gap-2"
                                    >
                                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add Staff
                                    </button>
                                    <button
                                        onClick={() => window.location.href = '{{ route("car.wash") }}'}
                                        className="px-6 py-3 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-black uppercase transition-colors backdrop-blur-sm"
                                    >
                                        ← Back to Car Wash
                                    </button>
                                </div>
                            </div>
                        </div>
                    </header>

                    {/* Main Content */}
                    <main className="max-w-7xl mx-auto p-6">
                        <div className="bg-white rounded-3xl shadow-xl border-2 border-slate-200 overflow-hidden">
                            {workers.length === 0 ? (
                                <div className="text-center py-24">
                                    <div className="w-40 h-40 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-10 shadow-lg">
                                        <svg className="w-20 h-20 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <p className="text-3xl font-black text-slate-800 uppercase tracking-tight mb-3">No Staff Found</p>
                                    <p className="text-lg text-slate-500 mt-6 font-bold">Add your first staff member to get started</p>
                                </div>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead className="bg-purple-600 text-white">
                                            <tr>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">#</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Name</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Mobile</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Father Name</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Location</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Commission %</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Daily Jobs</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Daily Commission</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Status</th>
                                                <th className="px-6 py-4 text-center text-xs font-black uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-slate-200">
                                            {workers.map((worker, idx) => (
                                                <tr key={worker.id || idx} className="hover:bg-slate-50 transition-colors">
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center text-white font-black text-sm shadow-lg">
                                                            {idx + 1}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-black text-slate-900">{worker.name || 'N/A'}</div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-black text-slate-900">{worker.mobile || 'N/A'}</div>
                                                        {(worker.additionalMobiles || worker.additional_mobiles || []).length > 0 && (
                                                            <div className="text-xs text-slate-500 mt-1">+{(worker.additionalMobiles || worker.additional_mobiles).length} more</div>
                                                        )}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-black text-slate-900">{worker.fatherName || 'N/A'}</div>
                                                        {worker.fatherMobile && <div className="text-xs text-slate-500 mt-1">{worker.fatherMobile}</div>}
                                                    </td>
                                                    <td className="px-6 py-4">
                                                        <div className="text-sm text-slate-600 max-w-xs truncate">{worker.location || 'N/A'}</div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-black text-purple-600">{(worker.commission || 0)}%</div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="flex items-center gap-2">
                                                            <div className="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center text-white font-black text-xs shadow-md">
                                                                {worker.dailyJobsCount || 0}
                                                            </div>
                                                            <span className="text-xs text-slate-500 font-bold">Jobs</span>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-black text-emerald-600">
                                                            Rs. {(worker.dailyCommission || 0).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <span className={`px-3 py-1 rounded-full text-xs font-black uppercase ${(worker.status !== undefined ? worker.status : true) ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'}`}>
                                                            {(worker.status !== undefined ? worker.status : true) ? 'Active' : 'Inactive'}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-center">
                                                        <div className="flex items-center justify-center gap-2">
                                                            <button
                                                                onClick={() => setSelectedWorkerForEdit(worker)}
                                                                className="px-3 py-2 bg-emerald-500 text-white rounded-lg text-xs font-black uppercase hover:bg-emerald-600 transition-colors shadow-md"
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
                                                                                
                                                                                // Reload workers
                                                                                const reloadResponse = await fetch(API_ROUTES.workers.index);
                                                                                const reloadData = await reloadResponse.json();
                                                        if (reloadData.success && reloadData.workers) {
                                                            setWorkers(reloadData.workers.map(w => ({
                                                                id: w.id,
                                                                name: w.name,
                                                                mobile: w.mobile,
                                                                additionalMobiles: w.additional_mobiles ?? [],
                                                                fatherName: w.father_name,
                                                                fatherMobile: w.father_mobile,
                                                                fatherAdditionalMobiles: w.father_additional_mobiles ?? [],
                                                                location: w.location,
                                                                commission: w.commission,
                                                                idCardFront: w.id_card_front ? `/storage/${w.id_card_front}` : null,
                                                                idCardBack: w.id_card_back ? `/storage/${w.id_card_back}` : null,
                                                                fatherCardFront: w.father_card_front ? `/storage/${w.father_card_front}` : null,
                                                                fatherCardBack: w.father_card_back ? `/storage/${w.father_card_back}` : null,
                                                                status: w.status,
                                                                dailyJobsCount: w.daily_jobs_count ?? 0,
                                                                dailyCommission: w.daily_commission ?? 0,
                                                            })));
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
                                                                className="px-3 py-2 bg-red-500 text-white rounded-lg text-xs font-black uppercase hover:bg-red-600 transition-colors shadow-md"
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
                            )}
                        </div>
                    </main>

                    {/* Add/Edit Staff Modal */}
                    {(showAddModal || selectedWorkerForEdit) && (
                        <div 
                            className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                            onClick={() => {
                                setShowAddModal(false);
                                setSelectedWorkerForEdit(null);
                            }}
                        >
                            <div 
                                className="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
                                onClick={(e) => e.stopPropagation()}
                            >
                                <div className="p-6 border-b border-slate-200 bg-purple-600 text-white">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <h2 className="text-2xl font-black uppercase tracking-tighter">
                                                {selectedWorkerForEdit ? 'EDIT WORKER' : 'NEW WORKER'}
                                            </h2>
                                            <p className="text-sm opacity-90 mt-1">
                                                {selectedWorkerForEdit ? 'Update worker information' : 'ADD A NEW WORKER TO STATION'}
                                            </p>
                                        </div>
                                        <button
                                            onClick={() => {
                                                setShowAddModal(false);
                                                setSelectedWorkerForEdit(null);
                                            }}
                                            className="text-white hover:text-slate-200 transition-colors p-2"
                                        >
                                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <div className="p-6 space-y-4">
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
                                                    setShowAddModal(false);
                                                    setSelectedWorkerForEdit(null);
                                                    
                                                    // Reload workers
                                                    const reloadResponse = await fetch(API_ROUTES.workers.index);
                                                    const reloadData = await reloadResponse.json();
                                                    if (reloadData.success && reloadData.workers) {
                                                        setWorkers(reloadData.workers.map(w => ({
                                                            id: w.id,
                                                            name: w.name,
                                                            mobile: w.mobile,
                                                            additionalMobiles: w.additional_mobiles ?? [],
                                                            fatherName: w.father_name,
                                                            fatherMobile: w.father_mobile,
                                                            fatherAdditionalMobiles: w.father_additional_mobiles ?? [],
                                                            location: w.location,
                                                            commission: w.commission,
                                                            idCardFront: w.id_card_front ? (w.id_card_front.startsWith('http') ? w.id_card_front : (w.idCardFront || `/${w.id_card_front}`)) : (w.idCardFront || null),
                                                            idCardBack: w.id_card_back ? (w.id_card_back.startsWith('http') ? w.id_card_back : (w.idCardBack || `/${w.id_card_back}`)) : (w.idCardBack || null),
                                                            fatherCardFront: w.father_card_front ? (w.father_card_front.startsWith('http') ? w.father_card_front : (w.fatherCardFront || `/${w.father_card_front}`)) : (w.fatherCardFront || null),
                                                            fatherCardBack: w.father_card_back ? (w.father_card_back.startsWith('http') ? w.father_card_back : (w.fatherCardBack || `/${w.father_card_back}`)) : (w.fatherCardBack || null),
                                                            status: w.status !== undefined ? w.status : true,
                                                            dailyJobsCount: w.daily_jobs_count ?? 0,
                                                            dailyCommission: w.daily_commission ?? 0,
                                                        })));
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
                                                <label className="block text-sm font-black text-slate-900 uppercase mb-2">WORKER NAME</label>
                                                <input 
                                                    type="text" 
                                                    name="name"
                                                    id="workerName"
                                                    defaultValue={selectedWorkerForEdit?.name || ''}
                                                    className="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none uppercase"
                                                    placeholder="e.g. John Doe"
                                                    required
                                                    key={selectedWorkerForEdit ? `name-${selectedWorkerForEdit.id}` : 'name-new'}
                                                />
                                            </div>
                                            
                                            <div>
                                                <label className="block text-sm font-black text-slate-900 uppercase mb-2">MOBILE NUMBER</label>
                                                <input 
                                                    type="tel" 
                                                    name="mobile"
                                                    id="workerMobile"
                                                    defaultValue={selectedWorkerForEdit?.mobile || ''}
                                                    className="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none"
                                                    placeholder="e.g. 0300-1234567"
                                                    required
                                                />
                                            </div>
                                            
                                            <div>
                                                <label className="block text-sm font-black text-slate-900 uppercase mb-2">FATHER NAME</label>
                                                <input 
                                                    type="text" 
                                                    name="father_name"
                                                    id="workerFatherName"
                                                    defaultValue={selectedWorkerForEdit?.fatherName || ''}
                                                    className="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none uppercase"
                                                    placeholder="e.g. Muhammad Ali"
                                                    required={!selectedWorkerForEdit}
                                                />
                                            </div>
                                            
                                            <div>
                                                <label className="block text-sm font-black text-slate-900 uppercase mb-2">FATHER MOBILE NUMBER</label>
                                                <input 
                                                    type="tel" 
                                                    name="father_mobile"
                                                    id="workerFatherMobile"
                                                    defaultValue={selectedWorkerForEdit?.fatherMobile || ''}
                                                    className="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none"
                                                    placeholder="e.g. 0300-1234567"
                                                    required
                                                />
                                            </div>
                                            
                                            <div>
                                                <label className="block text-sm font-black text-slate-900 uppercase mb-2">LOCATION / HOME ADDRESS</label>
                                                <textarea 
                                                    name="location"
                                                    id="workerLocation"
                                                    defaultValue={selectedWorkerForEdit?.location || ''}
                                                    className="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none"
                                                    placeholder="Enter full address"
                                                    rows="3"
                                                    required={!selectedWorkerForEdit}
                                                ></textarea>
                                            </div>
                                            
                                            <div>
                                                <label className="block text-sm font-black text-slate-900 uppercase mb-2">COMMISSION / SET BOX (%)</label>
                                                <input 
                                                    type="number" 
                                                    name="commission"
                                                    id="workerCommission"
                                                    defaultValue={selectedWorkerForEdit?.commission || 0}
                                                    className="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none"
                                                    placeholder="0"
                                                    min="0"
                                                    max="100"
                                                    step="1"
                                                    required={!selectedWorkerForEdit}
                                                />
                                            </div>
                                            
                                            <div>
                                                <label className="block text-sm font-black text-slate-900 uppercase mb-2">ID CARD PICTURE (FRONT)</label>
                                                <input 
                                                    type="file" 
                                                    id="workerIdCardFront"
                                                    accept="image/*"
                                                    className="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none"
                                                    required={!selectedWorkerForEdit}
                                                />
                                                {selectedWorkerForEdit?.idCardFront && (
                                                    <div className="mt-2">
                                                        <p className="text-xs text-slate-500 mb-1">Current image:</p>
                                                        <img src={selectedWorkerForEdit.idCardFront} alt="ID Front" className="max-w-xs max-h-32 border-2 border-slate-300 rounded-lg" />
                                                    </div>
                                                )}
                                            </div>
                                            
                                            <div>
                                                <label className="block text-sm font-black text-slate-900 uppercase mb-2">ID CARD PICTURE (BACK)</label>
                                                <input 
                                                    type="file" 
                                                    id="workerIdCardBack"
                                                    accept="image/*"
                                                    className="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none"
                                                    required={!selectedWorkerForEdit}
                                                />
                                                {selectedWorkerForEdit?.idCardBack && (
                                                    <div className="mt-2">
                                                        <p className="text-xs text-slate-500 mb-1">Current image:</p>
                                                        <img src={selectedWorkerForEdit.idCardBack} alt="ID Back" className="max-w-xs max-h-32 border-2 border-slate-300 rounded-lg" />
                                                    </div>
                                                )}
                                            </div>
                                            
                                            <div>
                                                <label className="block text-sm font-black text-slate-900 uppercase mb-2">FATHER / REFERENCE CARD (FRONT)</label>
                                                <input 
                                                    type="file" 
                                                    id="workerFatherCardFront"
                                                    accept="image/*"
                                                    className="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none"
                                                    required={!selectedWorkerForEdit}
                                                />
                                                {selectedWorkerForEdit?.fatherCardFront && (
                                                    <div className="mt-2">
                                                        <p className="text-xs text-slate-500 mb-1">Current image:</p>
                                                        <img src={selectedWorkerForEdit.fatherCardFront} alt="Father Card Front" className="max-w-xs max-h-32 border-2 border-slate-300 rounded-lg" />
                                                    </div>
                                                )}
                                            </div>
                                            
                                            <div>
                                                <label className="block text-sm font-black text-slate-900 uppercase mb-2">FATHER / REFERENCE CARD (BACK)</label>
                                                <input 
                                                    type="file" 
                                                    id="workerFatherCardBack"
                                                    accept="image/*"
                                                    className="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-purple-500 focus:outline-none"
                                                    required={!selectedWorkerForEdit}
                                                />
                                                {selectedWorkerForEdit?.fatherCardBack && (
                                                    <div className="mt-2">
                                                        <p className="text-xs text-slate-500 mb-1">Current image:</p>
                                                        <img src={selectedWorkerForEdit.fatherCardBack} alt="Father Card Back" className="max-w-xs max-h-32 border-2 border-slate-300 rounded-lg" />
                                                    </div>
                                                )}
                                            </div>
                                            
                                            <div className="flex gap-4 pt-4">
                                                <button
                                                    type="button"
                                                    onClick={() => {
                                                        setShowAddModal(false);
                                                        setSelectedWorkerForEdit(null);
                                                    }}
                                                    className="flex-1 px-6 py-3 bg-slate-600 text-white rounded-xl text-sm font-black uppercase hover:bg-slate-700 transition-colors"
                                                >
                                                    Cancel
                                                </button>
                                                <button
                                                    type="submit"
                                                    className="flex-1 px-6 py-3 bg-purple-600 text-white rounded-xl text-sm font-black uppercase hover:bg-purple-700 transition-colors"
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
