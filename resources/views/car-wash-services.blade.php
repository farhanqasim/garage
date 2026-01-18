<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services - Elite Car Wash</title>
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
        
        /* Category Modal Styles */
        .category-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(8px);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease-out;
        }
        
        .category-modal-overlay.show {
            opacity: 1;
            pointer-events: all;
        }
        
        .category-modal {
            background: white;
            border-radius: 32px;
            width: 100%;
            max-width: 480px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: scale(0.95) translateY(20px);
            transition: transform 0.3s ease-out;
        }
        
        .category-modal-overlay.show .category-modal {
            transform: scale(1) translateY(0);
        }
        
        .category-modal-header {
            background: white;
            color: #1e293b;
            padding: 28px 28px 0 28px;
            border-radius: 32px 32px 0 0;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }
        
        .category-modal-title {
            font-size: 24px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0;
            color: #0f172a;
        }
        
        .category-modal-close {
            background: #f1f5f9;
            border: none;
            color: #64748b;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        
        .category-modal-close:hover {
            background: #e2e8f0;
            color: #1e293b;
            transform: scale(1.05);
        }
        
        .category-modal-body {
            padding: 32px 28px;
            background: white;
        }
        
        .category-modal-subtitle {
            font-size: 11px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 4px;
            margin-bottom: 28px;
        }
        
        .category-form-group {
            margin-bottom: 28px;
        }
        
        .category-form-label {
            display: block;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #64748b;
            margin-bottom: 12px;
        }
        
        .category-form-input {
            width: 100%;
            padding: 18px 20px;
            border: none;
            border-radius: 20px;
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            background: #f1f5f9;
            transition: all 0.2s;
            outline: none;
            box-sizing: border-box;
        }
        
        .category-form-input:focus {
            background: #e2e8f0;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .category-form-input::placeholder {
            color: #94a3b8;
            font-weight: 500;
        }
        
        .icon-selector {
            margin-bottom: 24px;
        }
        
        .icon-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 12px;
        }
        
        .icon-option {
            width: 100%;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.2s;
            color: #64748b;
            position: relative;
        }
        
        .icon-option:hover {
            border-color: #3b82f6;
            background: #eff6ff;
            color: #3b82f6;
            transform: scale(1.05);
        }
        
        .icon-option.selected {
            border-color: #3b82f6 !important;
            background: #eff6ff !important;
            box-shadow: 0 0 0 2px white, 0 0 0 4px #3b82f6 !important;
        }
        
        .icon-option.selected::after {
            content: '✓';
            position: absolute;
            top: 4px;
            right: 4px;
            width: 18px;
            height: 18px;
            background: #10b981;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        .icon-option svg {
            width: 24px;
            height: 24px;
        }
        
        .theme-selector {
            margin-bottom: 28px;
        }
        
        .theme-colors-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-top: 12px;
        }
        
        .theme-color-option {
            aspect-ratio: 1;
            border-radius: 16px;
            border: 3px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        
        .theme-color-option:hover {
            transform: scale(1.05);
        }
        
        .theme-color-option.selected {
            border-color: #1e293b !important;
            box-shadow: 0 0 0 3px white, 0 0 0 5px #1e293b !important;
            transform: scale(1.1);
        }
        
        .theme-color-option.selected::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 28px;
            font-weight: 900;
            text-shadow: 0 2px 8px rgba(0,0,0,0.9), 0 0 6px rgba(0,0,0,0.6);
            z-index: 10;
            line-height: 1;
        }
        
        .preview-section {
            margin-bottom: 32px;
        }
        
        .preview-label {
            display: block;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #64748b;
            margin-bottom: 12px;
        }
        
        .preview-container {
            background: #0f172a;
            border-radius: 24px;
            padding: 24px;
            min-height: 120px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }
        
        .preview-service-card {
            padding: 16px 24px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: white;
            min-width: 200px;
            justify-content: center;
        }
        
        .preview-service-card .price {
            font-weight: 900;
            opacity: 0.9;
        }
        
        .category-form-actions {
            margin-top: 0;
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        
        .category-btn {
            width: 100%;
            padding: 18px 24px;
            border-radius: 20px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 13px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .category-btn-submit {
            background: #3b82f6;
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .category-btn-submit:hover {
            background: #2563eb;
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
            transform: translateY(-2px);
        }
        
        .category-btn-delete {
            background: #ef4444;
            color: white;
            margin-top: 12px;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        
        .category-btn-delete:hover {
            background: #dc2626;
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
            transform: translateY(-2px);
        }
        
        .additional-price-item {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
        }
        
        .additional-price-item input {
            flex: 1;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .additional-price-item button {
            padding: 12px 20px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.2s;
        }
        
        .additional-price-item button:hover {
            background: #dc2626;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <div id="root"></div>

    @php
        $initialServicesJson = json_encode($services);
        $branchNameJson = json_encode($branchName);
        $userNameJson = json_encode($userName);
        $servicesRoute = route("car-wash.services.index");
        $storeRoute = route("car-wash.services.store");
        $carWashRoute = route("car.wash");
    @endphp
    
    <script type="text/babel">
        const { useState, useEffect } = React;

        // Initial data from backend
        const initialServices = {!! $initialServicesJson !!};
        const branchName = {!! $branchNameJson !!};
        const userName = {!! $userNameJson !!};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        const API_ROUTES = {
            services: {
                index: '{!! $servicesRoute !!}',
                store: '{!! $storeRoute !!}',
                update: (id) => `/car-wash/services/${id}`,
                destroy: (id) => `/car-wash/services/${id}`,
                toggleStatus: (id) => `/car-wash/services/${id}/toggle-status`,
            },
        };

        function App() {
            const [services, setServices] = useState(() => {
                // Map initial services to ensure colorValue is always present
                if (initialServices && Array.isArray(initialServices)) {
                    return initialServices.map(s => ({
                        id: s.id,
                        label: s.label || '',
                        basePrice: parseFloat(s.basePrice || s.base_price || 0),
                        additionalPrices: s.additionalPrices || s.additional_prices || [],
                        icon: s.icon || null,
                        color: s.color || null,
                        colorValue: s.colorValue || s.color_value || '#3b82f6',
                        isDefault: s.isDefault || s.is_default || false,
                        status: s.status !== undefined ? s.status : true,
                    }));
                }
                return [];
            });
            const [selectedServiceForEdit, setSelectedServiceForEdit] = useState(null);
            const [showAddModal, setShowAddModal] = useState(false);

            // Load services from backend
            useEffect(() => {
                fetch(API_ROUTES.services.index)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.services) {
                            setServices(data.services.map(s => ({
                                id: s.id,
                                label: s.label,
                                basePrice: parseFloat(s.base_price) || 0,
                                additionalPrices: s.additional_prices ?? [],
                                icon: s.icon,
                                color: s.color,
                                colorValue: s.color_value || '#3b82f6',
                                isDefault: s.is_default,
                                status: s.status,
                            })));
                        }
                    })
                    .catch(err => console.error('Error loading services:', err));
            }, []);

            return (
                <div className="min-h-screen bg-slate-50">
                    {/* Header */}
                    <header className="bg-emerald-600 text-white p-6 shadow-2xl">
                        <div className="max-w-7xl mx-auto">
                            <div className="flex items-center justify-between">
                                <div>
                                    <h1 className="text-4xl font-black uppercase tracking-tighter mb-2">Services Management</h1>
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
                                        Add Service
                                    </button>
                                    <button
                                        onClick={() => window.location.href = '{!! $carWashRoute !!}'}
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
                            {services.length === 0 ? (
                                <div className="text-center py-24">
                                    <div className="w-40 h-40 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-10 shadow-lg">
                                        <svg className="w-20 h-20 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p className="text-3xl font-black text-slate-800 uppercase tracking-tight mb-3">No Services Found</p>
                                    <p className="text-lg text-slate-500 mt-6 font-bold">Add your first service to get started</p>
                                </div>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead className="bg-emerald-600 text-white">
                                            <tr>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">#</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Service Name</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Base Price</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Additional Prices</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Icon</th>
                                                <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Status</th>
                                                <th className="px-6 py-4 text-center text-xs font-black uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-slate-200">
                                            {services.map((service, idx) => {
                                                const serviceColorValue = service.colorValue || service.color_value || '#3b82f6';
                                                const iconStyle = { backgroundColor: serviceColorValue };
                                                return (
                                                <tr key={service.id || idx} className="hover:bg-slate-50 transition-colors">
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="w-10 h-10 bg-emerald-600 rounded-lg flex items-center justify-center text-white font-black text-sm shadow-lg">
                                                            {idx + 1}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-black text-slate-900">{service.label || 'N/A'}</div>
                                                        {service.isDefault && <div className="text-xs text-emerald-600 font-bold mt-1">Default</div>}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-black text-blue-600">Rs.{(service.basePrice || service.base_price || 0).toFixed(2)}</div>
                                                    </td>
                                                    <td className="px-6 py-4">
                                                        <div className="text-sm text-slate-600">
                                                            {(service.additionalPrices || service.additional_prices || []).length > 0 ? (
                                                                <span className="font-bold">{(service.additionalPrices || service.additional_prices).length} items</span>
                                                            ) : (
                                                                <span className="text-slate-400">None</span>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="w-12 h-12 rounded-lg flex items-center justify-center text-white text-xl" style={iconStyle}>
                                                            {service.icon || '🚗'}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <span className={`px-3 py-1 rounded-full text-xs font-black uppercase ${(service.status !== undefined ? service.status : true) ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'}`}>
                                                            {(service.status !== undefined ? service.status : true) ? 'Active' : 'Inactive'}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-center">
                                                        <div className="flex items-center justify-center gap-2">
                                                            <button
                                                                onClick={() => setSelectedServiceForEdit(service)}
                                                                className="px-3 py-2 bg-emerald-500 text-white rounded-lg text-xs font-black uppercase hover:bg-emerald-600 transition-colors shadow-md"
                                                                title="Edit Service"
                                                            >
                                                                Edit
                                                            </button>
                                                            <button
                                                                onClick={async () => {
                                                                    const serviceLabel = service.label || 'N/A';
                                                                    if (confirm('Are you sure you want to delete service: ' + serviceLabel + '?')) {
                                                                        try {
                                                                            const response = await fetch(API_ROUTES.services.destroy(service.id), {
                                                                                method: 'DELETE',
                                                                                headers: {
                                                                                    'Content-Type': 'application/json',
                                                                                    'X-CSRF-TOKEN': csrfToken,
                                                                                    'Accept': 'application/json'
                                                                                }
                                                                            });
                                                                            
                                                                            const result = await response.json();
                                                                            
                                                                            if (result.success) {
                                                                                setServices(prev => prev.filter(s => s.id !== service.id));
                                                                                alert('Service deleted successfully!');
                                                                                
                                                                                // Reload services
                                                                                const reloadResponse = await fetch(API_ROUTES.services.index);
                                                                                const reloadData = await reloadResponse.json();
                                                                                if (reloadData.success && reloadData.services) {
                                                                                    setServices(reloadData.services.map(s => ({
                                                                                        id: s.id,
                                                                                        label: s.label,
                                                                                        basePrice: parseFloat(s.base_price),
                                                                                        additionalPrices: s.additional_prices ?? [],
                                                                                        icon: s.icon,
                                                                                        color: s.color,
                                                                                        colorValue: s.color_value || '#3b82f6',
                                                                                        isDefault: s.is_default,
                                                                                        status: s.status,
                                                                                    })));
                                                                                }
                                                                            } else {
                                                                                alert('Error deleting service: ' + (result.message || 'Unknown error'));
                                                                            }
                                                                        } catch (error) {
                                                                            console.error('Error deleting service:', error);
                                                                            alert('Error deleting service. Please try again.');
                                                                        }
                                                                    }
                                                                }}
                                                                className="px-3 py-2 bg-red-500 text-white rounded-lg text-xs font-black uppercase hover:bg-red-600 transition-colors shadow-md"
                                                                title="Delete Service"
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

                    {/* Add Service Modal */}
                    {showAddModal && (
                        <div 
                            className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                            onClick={() => {
                                setShowAddModal(false);
                            }}
                        >
                            <div 
                                className="bg-white rounded-3xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto"
                                onClick={(e) => e.stopPropagation()}
                            >
                                <div className="p-6 border-b border-slate-200 bg-emerald-600 text-white">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <h2 className="text-2xl font-black uppercase tracking-tighter">
                                                NEW SERVICE
                                            </h2>
                                            <p className="text-sm opacity-90 mt-1">
                                                ADD A NEW SERVICE TO STATION
                                            </p>
                                        </div>
                                        <button
                                            onClick={() => {
                                                setShowAddModal(false);
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
                                        onSubmit={async (e) => {
                                            e.preventDefault();
                                            
                                            const labelInput = document.getElementById('serviceLabel');
                                            const basePriceInput = document.getElementById('serviceBasePrice');
                                            
                                            const label = labelInput ? labelInput.value.trim().toUpperCase() : '';
                                            const basePrice = basePriceInput ? parseFloat(basePriceInput.value) || 0 : 0;
                                            
                                            if (!label || label === '') {
                                                alert('Service name is required!');
                                                return;
                                            }
                                            
                                            const requestData = {
                                                label: label,
                                                base_price: basePrice,
                                                additional_prices: [],
                                                icon: 'car',
                                                color: 'bg-blue-600',
                                                color_value: '#3b82f6'
                                            };
                                            
                                            try {
                                                const url = selectedServiceForEdit 
                                                    ? API_ROUTES.services.update(selectedServiceForEdit.id)
                                                    : API_ROUTES.services.store;
                                                
                                                const method = selectedServiceForEdit ? 'PUT' : 'POST';
                                                
                                                const response = await fetch(url, {
                                                    method: method,
                                                    headers: {
                                                        'X-CSRF-TOKEN': csrfToken,
                                                        'Content-Type': 'application/json',
                                                        'Accept': 'application/json'
                                                    },
                                                    body: JSON.stringify(requestData)
                                                });
                                                
                                                const result = await response.json();
                                                
                                                if (!response.ok) {
                                                    // Handle validation errors
                                                    if (result.errors) {
                                                        const errorMessages = Object.values(result.errors).flat().join('\n');
                                                        alert('Validation Error:\n' + errorMessages);
                                                    } else {
                                                        alert('Error: ' + (result.message || 'Unknown error'));
                                                    }
                                                    return;
                                                }
                                                
                                                if (result.success) {
                                                    alert(selectedServiceForEdit ? 'Service updated successfully!' : 'Service added successfully!');
                                                    setShowAddModal(false);
                                                    setSelectedServiceForEdit(null);
                                                    
                                                    // Reload services
                                                    const reloadResponse = await fetch(API_ROUTES.services.index);
                                                    const reloadData = await reloadResponse.json();
                                                    if (reloadData.success && reloadData.services) {
                                                        setServices(reloadData.services.map(s => ({
                                                            id: s.id,
                                                            label: s.label,
                                                            basePrice: parseFloat(s.base_price) || 0,
                                                            additionalPrices: s.additional_prices ?? [],
                                                            icon: s.icon,
                                                            color: s.color,
                                                            colorValue: s.color_value || '#3b82f6',
                                                            isDefault: s.is_default,
                                                            status: s.status,
                                                        })));
                                                    }
                                                } else {
                                                    alert('Error: ' + (result.message || 'Unknown error'));
                                                }
                                            } catch (error) {
                                                console.error('Error saving service:', error);
                                                alert('Error saving service. Please try again.\n' + error.message);
                                            }
                                        }}
                                    >
                                        <div className="space-y-4">
                                            <div>
                                                <label className="block text-sm font-black text-slate-900 uppercase mb-2">SERVICE NAME</label>
                                                <input 
                                                    type="text" 
                                                    name="label"
                                                    id="serviceLabel"
                                                    defaultValue={selectedServiceForEdit?.label || ''}
                                                    className="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-emerald-500 focus:outline-none uppercase"
                                                    placeholder="e.g. MINI CAR WASH"
                                                    required
                                                />
                                            </div>
                                            
                                            <div>
                                                <label className="block text-sm font-black text-slate-900 uppercase mb-2">BASE PRICE (RS.)</label>
                                                <input 
                                                    type="number" 
                                                    name="base_price"
                                                    id="serviceBasePrice"
                                                    defaultValue={selectedServiceForEdit?.basePrice || selectedServiceForEdit?.base_price || 0}
                                                    className="w-full px-4 py-3 border-2 border-slate-300 rounded-xl focus:border-emerald-500 focus:outline-none"
                                                    placeholder="0"
                                                    min="0"
                                                    step="0.01"
                                                    required
                                                />
                                            </div>
                                            
                                            <div className="flex gap-4 pt-4">
                                                <button
                                                    type="button"
                                                    onClick={() => {
                                                        setShowAddModal(false);
                                                        setSelectedServiceForEdit(null);
                                                    }}
                                                    className="flex-1 px-6 py-3 bg-slate-600 text-white rounded-xl text-sm font-black uppercase hover:bg-slate-700 transition-colors"
                                                >
                                                    Cancel
                                                </button>
                                                <button
                                                    type="submit"
                                                    className="flex-1 px-6 py-3 bg-emerald-600 text-white rounded-xl text-sm font-black uppercase hover:bg-emerald-700 transition-colors"
                                                >
                                                    {selectedServiceForEdit ? 'UPDATE SERVICE' : 'SAVE SERVICE'}
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
