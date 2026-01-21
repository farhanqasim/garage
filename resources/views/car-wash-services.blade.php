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

        // Helper function to get icon SVG
        const getIconSVG = (iconName) => {
            const iconMap = {
                'car': <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/></svg>,
                'bus': <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M4 16c0 .88.39 1.67 1 2.22V20a1 1 0 001 1h1a1 1 0 001-1v-1h8v1a1 1 0 001 1h1a1 1 0 001-1v-1.78c.61-.55 1-1.34 1-2.22V6c0-3.31-2.69-6-6-6H6C2.69 0 0 2.69 0 6v10zm3.5 1c-.83 0-1.5-.67-1.5-1.5S6.67 14 7.5 14s1.5.67 1.5 1.5S8.33 17 7.5 17zm9 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm1.5-6H6V6h12v5z"/></svg>,
                'motorcycle': <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19.44 9.03L15.41 5H11v2h3.59l2 2H5c-2.8 0-5 2.2-5 5s2.2 5 5 5c2.46 0 4.45-1.69 4.9-4h1.65l2.77-2.77c-.21.54-.32 1.14-.32 1.77 0 2.8 2.2 5 5 5s5-2.2 5-5c0-2.65-1.97-4.77-4.56-4.97zM7 15c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm10 0c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3z"/></svg>,
                'truck': <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>,
                'auto-rickshaw': <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 2.01C18.72 1.42 18.16 1 17.5 1h-11c-.66 0-1.21.42-1.42 1.01L3 8v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1V8l-2.08-5.99zM6.5 12c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9s1.5.67 1.5 1.5S7.33 12 6.5 12zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 7l1.5-4.5h11L19 7H5z"/></svg>,
                'cycle': <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>,
                'tractor': <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><rect x="3" y="7" width="15" height="7" rx="1"/><line x1="4" y1="9" x2="4" y2="11" stroke="currentColor" strokeWidth="0.8" opacity="0.4"/><line x1="5.5" y1="9" x2="5.5" y2="11" stroke="currentColor" strokeWidth="0.8" opacity="0.4"/><line x1="7" y1="9" x2="7" y2="11" stroke="currentColor" strokeWidth="0.8" opacity="0.4"/><line x1="7" y1="7" x2="7" y2="9" stroke="currentColor" strokeWidth="0.8" opacity="0.4"/><line x1="11" y1="7" x2="11" y2="9" stroke="currentColor" strokeWidth="0.8" opacity="0.4"/><path d="M3 7 L3 5 L4 5 L4 6.5" stroke="currentColor" strokeWidth="2" fill="none"/><circle cx="6" cy="17" r="3.5" opacity="0.15"/><circle cx="6" cy="17" r="2.5" opacity="0.3"/><circle cx="6" cy="17" r="1.2"/><path d="M6 13.5 L6 20.5 M2.5 17 L9.5 17" stroke="currentColor" strokeWidth="1" opacity="0.7"/><circle cx="16" cy="17" r="4.5" opacity="0.15"/><circle cx="16" cy="17" r="3.2" opacity="0.3"/><circle cx="16" cy="17" r="1.3"/><path d="M16 12.5 L16 21.5 M11.5 17 L20.5 17" stroke="currentColor" strokeWidth="1.2" opacity="0.7"/><circle cx="9.5" cy="9.5" r="1.5"/><rect x="8.5" y="11" width="2.5" height="3" rx="0.5"/><circle cx="11.5" cy="12" r="1" opacity="0.8"/><line x1="11.5" y1="11" x2="11.5" y2="13" stroke="currentColor" strokeWidth="0.8" opacity="0.9"/><rect x="13" y="11.5" width="2" height="2.5" rx="0.3" opacity="0.7"/></svg>,
                'luxury-car': <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/></svg>,
                'clean': <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19.36 2.72L20.78 4.14 15.06 9.85C16.13 11.39 16.28 13.24 15.38 14.44L9.06 8.12C10.26 7.22 12.11 7.37 13.65 8.44L19.36 2.72M5.93 17.57C3.92 15.56 2.69 13.16 2.35 10.92L7.23 8.83L14.67 16.27L12.58 21.15C10.34 20.81 7.94 19.58 5.93 17.57Z"/></svg>
            };
            return iconMap[iconName] || iconMap['car'];
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
            
            // Initialize and update additional prices container display
            useEffect(() => {
                const additionalContainer = document.getElementById('additionalPricesContainer');
                if (additionalContainer) {
                    // Always set initial style
                    additionalContainer.style.display = 'none';
                    additionalContainer.style.marginTop = '12px';
                    
                    // Update display based on modal state
                    if (showAddModal && selectedServiceForEdit && selectedServiceForEdit.additionalPrices && Array.isArray(selectedServiceForEdit.additionalPrices) && selectedServiceForEdit.additionalPrices.length > 0) {
                        additionalContainer.style.display = 'block';
                    } else {
                        additionalContainer.style.display = 'none';
                    }
                }
            }, [showAddModal, selectedServiceForEdit]);
            
            // Reset form when modal closes and load data when editing
            useEffect(() => {
                if (showAddModal && selectedServiceForEdit) {
                    // Load service data for editing
                    setTimeout(() => {
                        const labelInput = document.getElementById('serviceLabel');
                        const priceInput = document.getElementById('serviceBasePrice');
                        const iconInput = document.getElementById('selectedIcon');
                        const colorInput = document.getElementById('selectedThemeColor');
                        const classInput = document.getElementById('selectedThemeClass');
                        const previewCard = document.getElementById('previewCard');
                        const previewLabel = document.getElementById('previewLabel');
                        const previewPrice = document.getElementById('previewPrice');
                        const additionalContainer = document.getElementById('additionalPricesContainer');
                        
                        if (labelInput && selectedServiceForEdit.label) labelInput.value = selectedServiceForEdit.label;
                        if (priceInput) priceInput.value = selectedServiceForEdit.basePrice || selectedServiceForEdit.base_price || 0;
                        if (iconInput) iconInput.value = selectedServiceForEdit.icon || 'car';
                        if (colorInput) colorInput.value = selectedServiceForEdit.colorValue || selectedServiceForEdit.color_value || '#3b82f6';
                        if (classInput) classInput.value = selectedServiceForEdit.color || 'bg-blue-600';
                        if (previewCard) previewCard.style.background = selectedServiceForEdit.colorValue || selectedServiceForEdit.color_value || '#3b82f6';
                        if (previewLabel && selectedServiceForEdit.label) previewLabel.textContent = selectedServiceForEdit.label.toUpperCase();
                        if (previewPrice) previewPrice.textContent = '· RS.' + (selectedServiceForEdit.basePrice || selectedServiceForEdit.base_price || 0);
                        
                        // Load additional prices
                        if (additionalContainer && selectedServiceForEdit.additionalPrices && Array.isArray(selectedServiceForEdit.additionalPrices) && selectedServiceForEdit.additionalPrices.length > 0) {
                            additionalContainer.innerHTML = '';
                            window.additionalPriceCounter = 0;
                            selectedServiceForEdit.additionalPrices.forEach((priceItem) => {
                                window.additionalPriceCounter++;
                                const priceId = 'additionalPrice_' + window.additionalPriceCounter;
                                const priceDiv = document.createElement('div');
                                priceDiv.className = 'additional-price-item';
                                priceDiv.style.cssText = 'display: flex; gap: 8px; margin-bottom: 8px; align-items: center;';
                                priceDiv.innerHTML = `
                                    <input 
                                        type="text" 
                                        class="category-form-input" 
                                        placeholder="Price Label (e.g., Premium, Deluxe)"
                                        style="flex: 1; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; text-transform: capitalize;"
                                        id="${priceId}_label"
                                        value="${priceItem.label || ''}"
                                    />
                                    <input 
                                        type="number" 
                                        class="category-form-input" 
                                        placeholder="Amount"
                                        min="0"
                                        step="1"
                                        style="width: 120px; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;"
                                        id="${priceId}_amount"
                                        value="${priceItem.amount || 0}"
                                    />
                                    <button 
                                        type="button" 
                                        style="padding: 10px 16px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 14px; transition: background 0.2s;"
                                        onmouseover="this.style.background='#dc2626'"
                                        onmouseout="this.style.background='#ef4444'"
                                        onclick="this.parentElement.remove()"
                                    >
                                        ×
                                    </button>
                                `;
                                additionalContainer.appendChild(priceDiv);
                            });
                            additionalContainer.style.display = 'block';
                        } else if (additionalContainer) {
                            additionalContainer.innerHTML = '';
                            additionalContainer.style.display = 'none';
                        }
                        
                        // Update icon selection
                        document.querySelectorAll('.icon-option').forEach(opt => {
                            opt.classList.remove('selected', 'border-emerald-500', 'bg-emerald-50');
                            const iconValue = opt.getAttribute('data-icon');
                            if (iconValue === (selectedServiceForEdit.icon || 'car')) {
                                opt.classList.add('selected', 'border-emerald-500', 'bg-emerald-50');
                            }
                        });
                        
                        // Update theme selection
                        const serviceColor = selectedServiceForEdit.colorValue || selectedServiceForEdit.color_value || '#3b82f6';
                        document.querySelectorAll('.theme-color-option').forEach(opt => {
                            opt.classList.remove('selected', 'border-slate-900', 'scale-110');
                            const optColor = opt.getAttribute('data-color');
                            if (optColor === serviceColor) {
                                opt.classList.add('selected', 'border-slate-900', 'scale-110');
                            }
                        });
                    }, 100);
                } else if (!showAddModal && !selectedServiceForEdit) {
                    // Reset form fields
                    const labelInput = document.getElementById('serviceLabel');
                    const priceInput = document.getElementById('serviceBasePrice');
                    const iconInput = document.getElementById('selectedIcon');
                    const colorInput = document.getElementById('selectedThemeColor');
                    const classInput = document.getElementById('selectedThemeClass');
                    const previewCard = document.getElementById('previewCard');
                    const previewLabel = document.getElementById('previewLabel');
                    const previewPrice = document.getElementById('previewPrice');
                    const additionalContainer = document.getElementById('additionalPricesContainer');
                    
                    if (labelInput) labelInput.value = '';
                    if (priceInput) priceInput.value = '0';
                    if (iconInput) iconInput.value = 'car';
                    if (colorInput) colorInput.value = '#3b82f6';
                    if (classInput) classInput.value = 'bg-blue-600';
                    if (previewCard) previewCard.style.background = '#3b82f6';
                    if (previewLabel) previewLabel.textContent = 'SERVICE LABEL';
                    if (previewPrice) previewPrice.textContent = '· RS.0';
                    if (additionalContainer) {
                        additionalContainer.innerHTML = '';
                        additionalContainer.style.display = 'none';
                    }
                    
                    // Reset selections
                    document.querySelectorAll('.icon-option').forEach(opt => {
                        opt.classList.remove('selected');
                        if (opt.getAttribute('data-icon') === 'car') {
                            opt.classList.add('selected');
                        }
                    });
                    document.querySelectorAll('.theme-color-option').forEach(opt => {
                        opt.classList.remove('selected');
                        if (opt.getAttribute('data-color') === '#3b82f6') {
                            opt.classList.add('selected');
                        }
                    });
                    
                    window.additionalPriceCounter = 0;
                }
            }, [showAddModal, selectedServiceForEdit]);

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
                    <header className="bg-emerald-600 text-white p-3 sm:p-4 md:p-6 shadow-2xl">
                        <div className="max-w-7xl mx-auto">
                            <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
                                <div className="flex-1 min-w-0">
                                    <h1 className="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-black uppercase tracking-tighter mb-1 sm:mb-2">Services Management</h1>
                                    <p className="text-xs sm:text-sm opacity-90 truncate">{branchName} • {userName}</p>
                                </div>
                                <div className="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                                    <button
                                        onClick={() => setShowAddModal(true)}
                                        className="px-4 sm:px-5 md:px-6 py-2 sm:py-2.5 md:py-3 bg-white/20 hover:bg-white/30 rounded-lg sm:rounded-xl text-xs sm:text-sm font-black uppercase transition-colors backdrop-blur-sm flex items-center justify-center gap-2"
                                    >
                                        <svg className="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span className="hidden sm:inline">Add Service</span>
                                        <span className="sm:hidden">Add</span>
                                    </button>
                                    <button
                                        onClick={() => window.location.href = '{!! $carWashRoute !!}'}
                                        className="px-4 sm:px-5 md:px-6 py-2 sm:py-2.5 md:py-3 bg-white/20 hover:bg-white/30 rounded-lg sm:rounded-xl text-xs sm:text-sm font-black uppercase transition-colors backdrop-blur-sm"
                                    >
                                        <span className="hidden sm:inline">← Back to Car Wash</span>
                                        <span className="sm:hidden">← Back</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </header>

                    {/* Main Content */}
                    <main className="max-w-7xl mx-auto p-3 sm:p-4 md:p-6">
                        <div className="bg-white rounded-2xl sm:rounded-3xl shadow-xl border-2 border-slate-200 overflow-hidden">
                            {services.length === 0 ? (
                                <div className="text-center py-12 sm:py-16 md:py-24 px-4">
                                    <div className="w-24 h-24 sm:w-32 sm:h-32 md:w-40 md:h-40 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 sm:mb-8 md:mb-10 shadow-lg">
                                        <svg className="w-12 h-12 sm:w-16 sm:h-16 md:w-20 md:h-20 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p className="text-xl sm:text-2xl md:text-3xl font-black text-slate-800 uppercase tracking-tight mb-2 sm:mb-3">No Services Found</p>
                                    <p className="text-sm sm:text-base md:text-lg text-slate-500 mt-4 sm:mt-6 font-bold">Add your first service to get started</p>
                                </div>
                            ) : (
                                <>
                                    {/* Desktop Table View */}
                                    <div className="hidden md:block overflow-x-auto">
                                        <table className="w-full">
                                            <thead className="bg-emerald-600 text-white">
                                                <tr>
                                                    <th className="px-4 lg:px-6 py-3 lg:py-4 text-left text-xs font-black uppercase tracking-wider">#</th>
                                                    <th className="px-4 lg:px-6 py-3 lg:py-4 text-left text-xs font-black uppercase tracking-wider">Service Name</th>
                                                    <th className="px-4 lg:px-6 py-3 lg:py-4 text-left text-xs font-black uppercase tracking-wider">Base Price</th>
                                                    <th className="px-4 lg:px-6 py-3 lg:py-4 text-left text-xs font-black uppercase tracking-wider">Additional Prices</th>
                                                    <th className="px-4 lg:px-6 py-3 lg:py-4 text-left text-xs font-black uppercase tracking-wider">Icon</th>
                                                    <th className="px-4 lg:px-6 py-3 lg:py-4 text-left text-xs font-black uppercase tracking-wider">Status</th>
                                                    <th className="px-4 lg:px-6 py-3 lg:py-4 text-center text-xs font-black uppercase tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody className="bg-white divide-y divide-slate-200">
                                                {services.map((service, idx) => {
                                                    const serviceColorValue = service.colorValue || service.color_value || '#3b82f6';
                                                    const hexToRgba = (hex, alpha) => {
                                                        const r = parseInt(hex.slice(1, 3), 16);
                                                        const g = parseInt(hex.slice(3, 5), 16);
                                                        const b = parseInt(hex.slice(5, 7), 16);
                                                        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
                                                    };
                                                    const rowStyle = {
                                                        backgroundColor: hexToRgba(serviceColorValue, 0.05),
                                                        borderLeft: `4px solid ${serviceColorValue}`
                                                    };
                                                    const iconStyle = { 
                                                        backgroundColor: serviceColorValue,
                                                        color: 'white'
                                                    };
                                                    return (
                                                    <tr key={service.id || idx} className="hover:opacity-90 transition-all" style={rowStyle}>
                                                        <td className="px-4 lg:px-6 py-3 lg:py-4 whitespace-nowrap">
                                                            <div className="w-10 h-10 rounded-lg flex items-center justify-center text-white font-black text-sm shadow-lg" style={iconStyle}>
                                                                {idx + 1}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3 lg:py-4 whitespace-nowrap">
                                                            <div className="text-sm font-black text-slate-900">{service.label || 'N/A'}</div>
                                                            {service.isDefault && <div className="text-xs text-emerald-600 font-bold mt-1">Default</div>}
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3 lg:py-4 whitespace-nowrap">
                                                            <div className="text-sm font-black text-blue-600">Rs.{(service.basePrice || service.base_price || 0).toFixed(2)}</div>
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3 lg:py-4">
                                                            <div className="text-sm text-slate-600">
                                                                {(service.additionalPrices || service.additional_prices || []).length > 0 ? (
                                                                    <span className="font-bold">{(service.additionalPrices || service.additional_prices).length} items</span>
                                                                ) : (
                                                                    <span className="text-slate-400">None</span>
                                                                )}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3 lg:py-4 whitespace-nowrap">
                                                            <div className="w-12 h-12 rounded-lg flex items-center justify-center text-white shadow-lg" style={iconStyle}>
                                                                {getIconSVG(service.icon || 'car')}
                                                            </div>
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3 lg:py-4 whitespace-nowrap">
                                                            <span className={`px-3 py-1 rounded-full text-xs font-black uppercase ${(service.status !== undefined ? service.status : true) ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'}`}>
                                                                {(service.status !== undefined ? service.status : true) ? 'Active' : 'Inactive'}
                                                            </span>
                                                        </td>
                                                        <td className="px-4 lg:px-6 py-3 lg:py-4 whitespace-nowrap text-center">
                                                            <div className="flex items-center justify-center gap-2">
                                                                <button
                                                                    onClick={() => {
                                                                        setSelectedServiceForEdit(service);
                                                                        setShowAddModal(true);
                                                                    }}
                                                                    className="px-3 py-2 bg-emerald-500 text-white rounded-lg text-xs font-black uppercase hover:bg-emerald-600 transition-colors shadow-md"
                                                                    title="Edit Service"
                                                                >
                                                                    Edit
                                                                </button>
                                                                <button
                                                                    onClick={async () => {
                                                                        const serviceLabel = service.label || 'N/A';
                                                                        if (confirm('Are you sure you want to delete service: ' + serviceLabel + '?')) {
                                                                            // Optimistically update UI first
                                                                            setServices(prev => prev.filter(s => s.id !== service.id));
                                                                            
                                                                            try {
                                                                                // Create AbortController for timeout
                                                                                const controller = new AbortController();
                                                                                const timeoutId = setTimeout(() => controller.abort(), 15000); // 15 second timeout
                                                                                
                                                                                const response = await fetch(API_ROUTES.services.destroy(service.id), {
                                                                                    method: 'DELETE',
                                                                                    headers: {
                                                                                        'Content-Type': 'application/json',
                                                                                        'X-CSRF-TOKEN': csrfToken,
                                                                                        'Accept': 'application/json'
                                                                                    },
                                                                                    signal: controller.signal
                                                                                });
                                                                                
                                                                                clearTimeout(timeoutId);
                                                                                
                                                                                if (!response.ok) {
                                                                                    throw new Error('Delete request failed');
                                                                                }
                                                                                
                                                                                const result = await response.json();
                                                                                
                                                                                if (result.success) {
                                                                                    // Service already removed from UI, no need to reload
                                                                                    alert('Service deleted successfully!');
                                                                                } else {
                                                                                    // Revert the optimistic update on error
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
                                                                                    alert('Error deleting service: ' + (result.message || 'Unknown error'));
                                                                                }
                                                                            } catch (error) {
                                                                                // Revert optimistic update on error
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
                                                                                
                                                                                if (error.name === 'AbortError') {
                                                                                    alert('Request timed out. Please check if the service was deleted and refresh the page.');
                                                                                } else {
                                                                                    console.error('Error deleting service:', error);
                                                                                    alert('Error deleting service. Please try again.');
                                                                                }
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

                                    {/* Mobile Card View */}
                                    <div className="md:hidden p-3 sm:p-4 space-y-3">
                                        {services.map((service, idx) => {
                                            const serviceColorValue = service.colorValue || service.color_value || '#3b82f6';
                                            const hexToRgba = (hex, alpha) => {
                                                const r = parseInt(hex.slice(1, 3), 16);
                                                const g = parseInt(hex.slice(3, 5), 16);
                                                const b = parseInt(hex.slice(5, 7), 16);
                                                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
                                            };
                                            const cardStyle = {
                                                backgroundColor: hexToRgba(serviceColorValue, 0.05),
                                                borderLeft: `4px solid ${serviceColorValue}`
                                            };
                                            const iconStyle = { 
                                                backgroundColor: serviceColorValue,
                                                color: 'white'
                                            };
                                            return (
                                                <div key={service.id || idx} className="bg-white rounded-xl shadow-md p-4 border-2 border-slate-100" style={cardStyle}>
                                                    <div className="flex items-start justify-between mb-3">
                                                        <div className="flex items-center gap-3 flex-1 min-w-0">
                                                            <div className="w-10 h-10 rounded-lg flex items-center justify-center text-white font-black text-sm shadow-lg flex-shrink-0" style={iconStyle}>
                                                                {idx + 1}
                                                            </div>
                                                            <div className="flex-1 min-w-0">
                                                                <div className="text-base font-black text-slate-900 truncate">{service.label || 'N/A'}</div>
                                                                {service.isDefault && <div className="text-xs text-emerald-600 font-bold mt-0.5">Default</div>}
                                                            </div>
                                                        </div>
                                                        <div className="w-10 h-10 rounded-lg flex items-center justify-center text-white shadow-lg flex-shrink-0" style={iconStyle}>
                                                            {getIconSVG(service.icon || 'car')}
                                                        </div>
                                                    </div>
                                                    
                                                    <div className="grid grid-cols-2 gap-3 mb-3">
                                                        <div>
                                                            <div className="text-xs text-slate-500 font-bold mb-1">Base Price</div>
                                                            <div className="text-sm font-black text-blue-600">Rs.{(service.basePrice || service.base_price || 0).toFixed(2)}</div>
                                                        </div>
                                                        <div>
                                                            <div className="text-xs text-slate-500 font-bold mb-1">Additional</div>
                                                            <div className="text-sm text-slate-600">
                                                                {(service.additionalPrices || service.additional_prices || []).length > 0 ? (
                                                                    <span className="font-bold">{(service.additionalPrices || service.additional_prices).length} items</span>
                                                                ) : (
                                                                    <span className="text-slate-400">None</span>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div className="flex items-center justify-between pt-3 border-t border-slate-200">
                                                        <span className={`px-2.5 py-1 rounded-full text-xs font-black uppercase ${(service.status !== undefined ? service.status : true) ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'}`}>
                                                            {(service.status !== undefined ? service.status : true) ? 'Active' : 'Inactive'}
                                                        </span>
                                                        <div className="flex items-center gap-2">
                                                            <button
                                                                onClick={() => {
                                                                    setSelectedServiceForEdit(service);
                                                                    setShowAddModal(true);
                                                                }}
                                                                className="px-3 py-1.5 bg-emerald-500 text-white rounded-lg text-xs font-black uppercase hover:bg-emerald-600 transition-colors shadow-md"
                                                                title="Edit Service"
                                                            >
                                                                Edit
                                                            </button>
                                                            <button
                                                                onClick={async () => {
                                                                    const serviceLabel = service.label || 'N/A';
                                                                    if (confirm('Are you sure you want to delete service: ' + serviceLabel + '?')) {
                                                                        // Optimistically update UI first
                                                                        setServices(prev => prev.filter(s => s.id !== service.id));
                                                                        
                                                                        try {
                                                                            // Create AbortController for timeout
                                                                            const controller = new AbortController();
                                                                            const timeoutId = setTimeout(() => controller.abort(), 15000); // 15 second timeout
                                                                            
                                                                            const response = await fetch(API_ROUTES.services.destroy(service.id), {
                                                                                method: 'DELETE',
                                                                                headers: {
                                                                                    'Content-Type': 'application/json',
                                                                                    'X-CSRF-TOKEN': csrfToken,
                                                                                    'Accept': 'application/json'
                                                                                },
                                                                                signal: controller.signal
                                                                            });
                                                                            
                                                                            clearTimeout(timeoutId);
                                                                            
                                                                            if (!response.ok) {
                                                                                throw new Error('Delete request failed');
                                                                            }
                                                                            
                                                                            const result = await response.json();
                                                                            
                                                                            if (result.success) {
                                                                                // Service already removed from UI, no need to reload
                                                                                alert('Service deleted successfully!');
                                                                            } else {
                                                                                // Revert the optimistic update on error
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
                                                                                alert('Error deleting service: ' + (result.message || 'Unknown error'));
                                                                            }
                                                                        } catch (error) {
                                                                            // Revert optimistic update on error
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
                                                                            
                                                                            if (error.name === 'AbortError') {
                                                                                alert('Request timed out. Please check if the service was deleted and refresh the page.');
                                                                            } else {
                                                                                console.error('Error deleting service:', error);
                                                                                alert('Error deleting service. Please try again.');
                                                                            }
                                                                        }
                                                                    }
                                                                }}
                                                                className="px-3 py-1.5 bg-red-500 text-white rounded-lg text-xs font-black uppercase hover:bg-red-600 transition-colors shadow-md"
                                                                title="Delete Service"
                                                            >
                                                                Delete
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </>
                            )}
                        </div>
                    </main>

                    {/* Add Service Modal */}
                    {showAddModal && (
                        <div 
                            className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-2 sm:p-3 md:p-4"
                            onClick={() => {
                                setShowAddModal(false);
                                setSelectedServiceForEdit(null);
                            }}
                        >
                            <div 
                                className="bg-white rounded-2xl sm:rounded-3xl shadow-2xl w-full max-w-md max-h-[95vh] sm:max-h-[90vh] overflow-y-auto"
                                onClick={(e) => e.stopPropagation()}
                            >
                                <div className="p-4 sm:p-5 md:p-6 border-b border-slate-200 bg-emerald-600 text-white">
                                    <div className="flex items-center justify-between gap-3">
                                        <div className="flex-1 min-w-0">
                                            <h2 className="text-lg sm:text-xl md:text-2xl font-black uppercase tracking-tighter">
                                                {selectedServiceForEdit ? 'EDIT SERVICE' : 'NEW SERVICE'}
                                            </h2>
                                            <p className="text-xs sm:text-sm opacity-90 mt-1">
                                                {selectedServiceForEdit ? 'UPDATE SERVICE INFORMATION' : 'ADD A NEW SERVICE TO STATION'}
                                            </p>
                                        </div>
                                        <button
                                            onClick={() => {
                                                setShowAddModal(false);
                                                setSelectedServiceForEdit(null);
                                            }}
                                            className="text-white hover:text-slate-200 transition-colors p-1.5 sm:p-2 flex-shrink-0"
                                        >
                                            <svg className="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <div className="p-4 sm:p-5 md:p-6 space-y-4 sm:space-y-5 md:space-y-6">
                                    <form 
                                        onSubmit={async (e) => {
                                            e.preventDefault();
                                            
                                            const labelInput = document.getElementById('serviceLabel');
                                            const basePriceInput = document.getElementById('serviceBasePrice');
                                            const selectedIconInput = document.getElementById('selectedIcon');
                                            const selectedThemeColorInput = document.getElementById('selectedThemeColor');
                                            const selectedThemeClassInput = document.getElementById('selectedThemeClass');
                                            const additionalPricesContainer = document.getElementById('additionalPricesContainer');
                                            
                                            const label = labelInput ? labelInput.value.trim().toUpperCase() : '';
                                            const basePrice = basePriceInput ? parseFloat(basePriceInput.value) || 0 : 0;
                                            const icon = selectedIconInput ? selectedIconInput.value : 'car';
                                            const colorValue = selectedThemeColorInput ? selectedThemeColorInput.value : '#3b82f6';
                                            const colorClass = selectedThemeClassInput ? selectedThemeClassInput.value : 'bg-blue-600';
                                            
                                            // Collect additional prices
                                            const additionalPrices = [];
                                            if (additionalPricesContainer) {
                                                const priceItems = additionalPricesContainer.querySelectorAll('.additional-price-item');
                                                priceItems.forEach(item => {
                                                    const labelInput = item.querySelector('input[placeholder*="Label"]');
                                                    const amountInput = item.querySelector('input[placeholder*="Amount"]');
                                                    if (labelInput && amountInput && labelInput.value.trim() && amountInput.value) {
                                                        additionalPrices.push({
                                                            label: labelInput.value.trim(),
                                                            amount: parseFloat(amountInput.value) || 0
                                                        });
                                                    }
                                                });
                                            }
                                            
                                            if (!label || label === '') {
                                                alert('Service name is required!');
                                                return;
                                            }
                                            
                                            const requestData = {
                                                label: label,
                                                base_price: basePrice,
                                                additional_prices: additionalPrices,
                                                icon: icon,
                                                color: colorClass,
                                                color_value: colorValue
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
                                                    
                                                    // Reset form
                                                    if (labelInput) labelInput.value = '';
                                                    if (basePriceInput) basePriceInput.value = '0';
                                                    if (additionalPricesContainer) additionalPricesContainer.innerHTML = '';
                                                    if (selectedIconInput) selectedIconInput.value = 'car';
                                                    if (selectedThemeColorInput) selectedThemeColorInput.value = '#3b82f6';
                                                    if (selectedThemeClassInput) selectedThemeClassInput.value = 'bg-blue-600';
                                                    
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
                                        {/* Category Name */}
                                        <div className="space-y-2">
                                            <label className="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">CATEGORY NAME</label>
                                            <input 
                                                type="text" 
                                                id="serviceLabel"
                                                defaultValue={(selectedServiceForEdit && selectedServiceForEdit.label) ? selectedServiceForEdit.label : ''}
                                                className="w-full px-3 sm:px-4 md:px-5 py-3 sm:py-3.5 md:py-4 border-none rounded-xl sm:rounded-2xl bg-slate-100 focus:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm sm:text-base font-semibold text-slate-900 uppercase"
                                                placeholder="e.g. Engine Detail"
                                                required
                                                onChange={(e) => {
                                                    const previewLabel = document.getElementById('previewLabel');
                                                    if (previewLabel) {
                                                        previewLabel.textContent = (e.target.value || 'SERVICE LABEL').toUpperCase();
                                                    }
                                                }}
                                            />
                                        </div>
                                        
                                        {/* Base Price */}
                                        <div className="space-y-2">
                                            <label className="block text-xs font-black text-slate-600 uppercase tracking-wider mb-2">BASE PRICE (RS.)</label>
                                            <input 
                                                type="number" 
                                                id="serviceBasePrice"
                                                defaultValue={selectedServiceForEdit?.basePrice || selectedServiceForEdit?.base_price || 0}
                                                className="w-full px-3 sm:px-4 md:px-5 py-3 sm:py-3.5 md:py-4 border-none rounded-xl sm:rounded-2xl bg-slate-100 focus:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm sm:text-base font-semibold text-slate-900"
                                                placeholder="0"
                                                min="0"
                                                step="1"
                                                required
                                                onChange={(e) => {
                                                    const previewPrice = document.getElementById('previewPrice');
                                                    if (previewPrice) {
                                                        previewPrice.textContent = '· RS.' + (e.target.value || 0);
                                                    }
                                                }}
                                            />
                                            <button 
                                                type="button" 
                                                onClick={() => {
                                                    if (!window.additionalPriceCounter) window.additionalPriceCounter = 0;
                                                    window.additionalPriceCounter++;
                                                    const container = document.getElementById('additionalPricesContainer');
                                                    if (!container) return;
                                                    
                                                    const priceId = 'additionalPrice_' + window.additionalPriceCounter;
                                                    const priceDiv = document.createElement('div');
                                                    priceDiv.className = 'additional-price-item';
                                                    priceDiv.style.cssText = 'display: flex; gap: 8px; margin-bottom: 8px; align-items: center;';
                                                    priceDiv.innerHTML = `
                                                        <input 
                                                            type="text" 
                                                            class="category-form-input" 
                                                            placeholder="Price Label (e.g., Premium, Deluxe)"
                                                            style="flex: 1; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; text-transform: capitalize;"
                                                            id="${priceId}_label"
                                                        />
                                                        <input 
                                                            type="number" 
                                                            class="category-form-input" 
                                                            placeholder="Amount"
                                                            min="0"
                                                            step="1"
                                                            style="width: 120px; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;"
                                                            id="${priceId}_amount"
                                                        />
                                                        <button 
                                                            type="button" 
                                                            style="padding: 10px 16px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 14px; transition: background 0.2s;"
                                                            onmouseover="this.style.background='#dc2626'"
                                                            onmouseout="this.style.background='#ef4444'"
                                                            onclick="this.parentElement.remove()"
                                                        >
                                                            ×
                                                        </button>
                                                    `;
                                                    container.appendChild(priceDiv);
                                                    container.style.display = 'block';
                                                }}
                                                className="w-full mt-3 px-3 sm:px-4 md:px-5 py-2.5 sm:py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 border-2 border-slate-300 rounded-lg sm:rounded-xl text-xs sm:text-sm font-bold transition-colors"
                                            >
                                                + ADD ADDITIONAL PRICE
                                            </button>
                                            <div 
                                                id="additionalPricesContainer" 
                                                className="mt-3"
                                            ></div>
                                        </div>
                                        
                                        {/* Choose Icon */}
                                        <div className="space-y-2 sm:space-y-3">
                                            <label className="block text-xs font-black text-slate-600 uppercase tracking-wider">CHOOSE ICON</label>
                                            <div className="grid grid-cols-3 gap-2 sm:gap-3">
                                                {['car', 'bus', 'motorcycle', 'truck', 'auto-rickshaw', 'cycle', 'tractor', 'luxury-car', 'clean'].map((iconName) => (
                                                    <div
                                                        key={iconName}
                                                        data-icon={iconName}
                                                        className={`icon-option aspect-square flex items-center justify-center border-2 rounded-xl cursor-pointer transition-all ${
                                                            ((selectedServiceForEdit && selectedServiceForEdit.icon) ? selectedServiceForEdit.icon : 'car') === iconName
                                                                ? 'border-emerald-500 bg-emerald-50 selected'
                                                                : 'border-slate-200 bg-slate-50 hover:border-emerald-300'
                                                        }`}
                                                        onClick={(e) => {
                                                            const iconInput = document.getElementById('selectedIcon');
                                                            if (iconInput) iconInput.value = iconName;
                                                            document.querySelectorAll('.icon-option').forEach(opt => opt.classList.remove('selected'));
                                                            e.currentTarget.classList.add('selected');
                                                        }}
                                                    >
                                                        {iconName === 'car' && (
                                                            <svg width="32" height="32" fill={(selectedServiceForEdit && selectedServiceForEdit.icon === iconName) ? "#10b981" : "#3b82f6"} viewBox="0 0 24 24">
                                                                <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                                                            </svg>
                                                        )}
                                                        {iconName === 'bus' && (
                                                            <svg width="32" height="32" fill={(selectedServiceForEdit && selectedServiceForEdit.icon === iconName) ? "#10b981" : "#10b981"} viewBox="0 0 24 24">
                                                                <path d="M4 16c0 .88.39 1.67 1 2.22V20a1 1 0 001 1h1a1 1 0 001-1v-1h8v1a1 1 0 001 1h1a1 1 0 001-1v-1.78c.61-.55 1-1.34 1-2.22V6c0-3.31-2.69-6-6-6H6C2.69 0 0 2.69 0 6v10zm3.5 1c-.83 0-1.5-.67-1.5-1.5S6.67 14 7.5 14s1.5.67 1.5 1.5S8.33 17 7.5 17zm9 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm1.5-6H6V6h12v5z"/>
                                                            </svg>
                                                        )}
                                                        {iconName === 'motorcycle' && (
                                                            <svg width="32" height="32" fill={(selectedServiceForEdit && selectedServiceForEdit.icon === iconName) ? "#10b981" : "#ef4444"} viewBox="0 0 24 24">
                                                                <path d="M19.44 9.03L15.41 5H11v2h3.59l2 2H5c-2.8 0-5 2.2-5 5s2.2 5 5 5c2.46 0 4.45-1.69 4.9-4h1.65l2.77-2.77c-.21.54-.32 1.14-.32 1.77 0 2.8 2.2 5 5 5s5-2.2 5-5c0-2.65-1.97-4.77-4.56-4.97zM7 15c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm10 0c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3z"/>
                                                            </svg>
                                                        )}
                                                        {iconName === 'truck' && (
                                                            <svg width="32" height="32" fill={(selectedServiceForEdit && selectedServiceForEdit.icon === iconName) ? "#10b981" : "#a855f7"} viewBox="0 0 24 24">
                                                                <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                                                            </svg>
                                                        )}
                                                        {iconName === 'auto-rickshaw' && (
                                                            <svg width="32" height="32" fill={(selectedServiceForEdit && selectedServiceForEdit.icon === iconName) ? "#10b981" : "#f97316"} viewBox="0 0 24 24">
                                                                <path d="M18.92 2.01C18.72 1.42 18.16 1 17.5 1h-11c-.66 0-1.21.42-1.42 1.01L3 8v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1V8l-2.08-5.99zM6.5 12c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9s1.5.67 1.5 1.5S7.33 12 6.5 12zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 7l1.5-4.5h11L19 7H5z"/>
                                                            </svg>
                                                        )}
                                                        {iconName === 'cycle' && (
                                                            <svg width="32" height="32" fill={(selectedServiceForEdit && selectedServiceForEdit.icon === iconName) ? "#10b981" : "#1e40af"} viewBox="0 0 24 24">
                                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                                                            </svg>
                                                        )}
                                                        {iconName === 'tractor' && (
                                                            <svg width="32" height="32" fill={(selectedServiceForEdit && selectedServiceForEdit.icon === iconName) ? "#10b981" : "#ec4899"} viewBox="0 0 24 24">
                                                                <rect x="3" y="7" width="15" height="7" rx="1" fill="currentColor"/>
                                                                <line x1="4" y1="9" x2="4" y2="11" stroke="currentColor" strokeWidth="0.8" opacity="0.6"/>
                                                                <line x1="5.5" y1="9" x2="5.5" y2="11" stroke="currentColor" strokeWidth="0.8" opacity="0.6"/>
                                                                <line x1="7" y1="9" x2="7" y2="11" stroke="currentColor" strokeWidth="0.8" opacity="0.6"/>
                                                                <line x1="7" y1="7" x2="7" y2="9" stroke="currentColor" strokeWidth="0.8" opacity="0.6"/>
                                                                <line x1="11" y1="7" x2="11" y2="9" stroke="currentColor" strokeWidth="0.8" opacity="0.6"/>
                                                                <path d="M3 7 L3 5 L4 5 L4 6.5" stroke="currentColor" strokeWidth="2" fill="none"/>
                                                                <circle cx="6" cy="17" r="3.5" fill="currentColor" opacity="0.2"/>
                                                                <circle cx="6" cy="17" r="2.5" fill="currentColor" opacity="0.4"/>
                                                                <circle cx="6" cy="17" r="1.2" fill="currentColor"/>
                                                                <path d="M6 13.5 L6 20.5 M2.5 17 L9.5 17" stroke="currentColor" strokeWidth="1" opacity="0.7"/>
                                                                <circle cx="16" cy="17" r="4.5" fill="currentColor" opacity="0.2"/>
                                                                <circle cx="16" cy="17" r="3.2" fill="currentColor" opacity="0.4"/>
                                                                <circle cx="16" cy="17" r="1.3" fill="currentColor"/>
                                                                <path d="M16 12.5 L16 21.5 M11.5 17 L20.5 17" stroke="currentColor" strokeWidth="1.2" opacity="0.7"/>
                                                                <circle cx="9.5" cy="9.5" r="1.5" fill="currentColor"/>
                                                                <rect x="8.5" y="11" width="2.5" height="3" rx="0.5" fill="currentColor"/>
                                                                <circle cx="11.5" cy="12" r="1" fill="currentColor" opacity="0.8"/>
                                                                <line x1="11.5" y1="11" x2="11.5" y2="13" stroke="currentColor" strokeWidth="0.8" opacity="0.9"/>
                                                                <rect x="13" y="11.5" width="2" height="2.5" rx="0.3" fill="currentColor" opacity="0.7"/>
                                                            </svg>
                                                        )}
                                                        {iconName === 'luxury-car' && (
                                                            <svg width="32" height="32" fill={(selectedServiceForEdit && selectedServiceForEdit.icon === iconName) ? "#10b981" : "#8b5cf6"} viewBox="0 0 24 24">
                                                                <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                                                            </svg>
                                                        )}
                                                        {iconName === 'clean' && (
                                                            <svg width="32" height="32" fill={(selectedServiceForEdit && selectedServiceForEdit.icon === iconName) ? "#10b981" : "#14b8a6"} viewBox="0 0 24 24">
                                                                <path d="M19.36 2.72L20.78 4.14 15.06 9.85C16.13 11.39 16.28 13.24 15.38 14.44L9.06 8.12C10.26 7.22 12.11 7.37 13.65 8.44L19.36 2.72M5.93 17.57C3.92 15.56 2.69 13.16 2.35 10.92L7.23 8.83L14.67 16.27L12.58 21.15C10.34 20.81 7.94 19.58 5.93 17.57Z"/>
                                                            </svg>
                                                        )}
                                                    </div>
                                                ))}
                                            </div>
                                            <input type="hidden" id="selectedIcon" defaultValue={(selectedServiceForEdit && selectedServiceForEdit.icon) ? selectedServiceForEdit.icon : 'car'} />
                                        </div>
                                        
                                        {/* Choose Theme */}
                                        <div className="space-y-2 sm:space-y-3">
                                            <label className="block text-xs font-black text-slate-600 uppercase tracking-wider">CHOOSE THEME</label>
                                            <div className="grid grid-cols-4 gap-2 sm:gap-3">
                                                {[
                                                    {color: '#3b82f6', class: 'bg-blue-600'},
                                                    {color: '#10b981', class: 'bg-emerald-600'},
                                                    {color: '#ef4444', class: 'bg-red-500'},
                                                    {color: '#a855f7', class: 'bg-purple-500'},
                                                    {color: '#f97316', class: 'bg-orange-500'},
                                                    {color: '#1e40af', class: 'bg-blue-800'},
                                                    {color: '#ec4899', class: 'bg-pink-500'},
                                                    {color: '#8b5cf6', class: 'bg-violet-500'},
                                                ].map((themeItem) => {
                                                    const themeColorValue = themeItem.color;
                                                    const themeClassValue = themeItem.class;
                                                    const divStyle = {backgroundColor: themeColorValue};
                                                    return (
                                                        <div
                                                            key={themeColorValue}
                                                            data-color={themeColorValue}
                                                            className={`theme-color-option aspect-square rounded-2xl cursor-pointer transition-all border-3 ${
                                                                ((selectedServiceForEdit && (selectedServiceForEdit.colorValue || selectedServiceForEdit.color_value)) ? (selectedServiceForEdit.colorValue || selectedServiceForEdit.color_value) : '#3b82f6') === themeColorValue
                                                                    ? 'border-slate-900 scale-110 shadow-lg selected'
                                                                    : 'border-transparent hover:scale-105'
                                                            }`}
                                                            style={divStyle}
                                                            onClick={(e) => {
                                                                const colorInput = document.getElementById('selectedThemeColor');
                                                                const classInput = document.getElementById('selectedThemeClass');
                                                                const previewCard = document.getElementById('previewCard');
                                                                if (colorInput) colorInput.value = themeColorValue;
                                                                if (classInput) classInput.value = themeClassValue;
                                                                if (previewCard) previewCard.style.background = themeColorValue;
                                                                document.querySelectorAll('.theme-color-option').forEach(opt => opt.classList.remove('selected'));
                                                                e.currentTarget.classList.add('selected');
                                                            }}
                                                        ></div>
                                                    );
                                                })}
                                            </div>
                                            <input type="hidden" id="selectedThemeColor" defaultValue={(selectedServiceForEdit && (selectedServiceForEdit.colorValue || selectedServiceForEdit.color_value)) ? (selectedServiceForEdit.colorValue || selectedServiceForEdit.color_value) : '#3b82f6'} />
                                            <input type="hidden" id="selectedThemeClass" defaultValue={(selectedServiceForEdit && selectedServiceForEdit.color) ? selectedServiceForEdit.color : 'bg-blue-600'} />
                                        </div>
                                        
                                        {/* Preview */}
                                        <div className="space-y-2 sm:space-y-3">
                                            <label className="block text-xs font-black text-slate-600 uppercase tracking-wider">PREVIEW</label>
                                            <div className="bg-slate-900 rounded-2xl sm:rounded-3xl p-4 sm:p-5 md:p-6 min-h-[100px] sm:min-h-[120px] flex items-center justify-end">
                                                <div 
                                                    id="previewCard"
                                                    className="px-4 sm:px-5 md:px-6 py-3 sm:py-3.5 md:py-4 rounded-xl sm:rounded-2xl inline-flex items-center gap-2 sm:gap-3 font-black text-xs sm:text-sm uppercase text-white"
                                                    style={(() => {
                                                        const bgColor = (selectedServiceForEdit && (selectedServiceForEdit.colorValue || selectedServiceForEdit.color_value)) ? (selectedServiceForEdit.colorValue || selectedServiceForEdit.color_value) : '#3b82f6';
                                                        return {background: bgColor};
                                                    })()}
                                                >
                                                    <span id="previewLabel">{(selectedServiceForEdit && selectedServiceForEdit.label) ? selectedServiceForEdit.label.toUpperCase() : 'SERVICE LABEL'}</span>
                                                    <span id="previewPrice" className="opacity-90">· RS.{(selectedServiceForEdit && (selectedServiceForEdit.basePrice || selectedServiceForEdit.base_price)) ? (selectedServiceForEdit.basePrice || selectedServiceForEdit.base_price) : 0}</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {/* Buttons */}
                                        <div className="flex flex-col gap-2 sm:gap-3 pt-3 sm:pt-4">
                                            <button
                                                type="submit"
                                                className="w-full px-4 sm:px-5 md:px-6 py-3 sm:py-3.5 md:py-4 bg-emerald-600 text-white rounded-xl sm:rounded-2xl text-xs sm:text-sm font-black uppercase hover:bg-emerald-700 transition-colors shadow-lg"
                                            >
                                                {selectedServiceForEdit ? 'UPDATE' : 'SAVE'}
                                            </button>
                                            {selectedServiceForEdit && (
                                                <button
                                                    type="button"
                                                    onClick={async () => {
                                                        if (confirm('Are you sure you want to delete this service?')) {
                                                            const serviceIdToDelete = selectedServiceForEdit.id;
                                                            
                                                            // Optimistically update UI first
                                                            setServices(prev => prev.filter(s => s.id !== serviceIdToDelete));
                                                            setShowAddModal(false);
                                                            setSelectedServiceForEdit(null);
                                                            
                                                            try {
                                                                // Create AbortController for timeout
                                                                const controller = new AbortController();
                                                                const timeoutId = setTimeout(() => controller.abort(), 15000); // 15 second timeout
                                                                
                                                                const response = await fetch(API_ROUTES.services.destroy(serviceIdToDelete), {
                                                                    method: 'DELETE',
                                                                    headers: {
                                                                        'X-CSRF-TOKEN': csrfToken,
                                                                        'Accept': 'application/json'
                                                                    },
                                                                    signal: controller.signal
                                                                });
                                                                
                                                                clearTimeout(timeoutId);
                                                                
                                                                if (!response.ok) {
                                                                    throw new Error('Delete request failed');
                                                                }
                                                                
                                                                const result = await response.json();
                                                                if (result.success) {
                                                                    // Service already removed from UI, no need to reload
                                                                    alert('Service deleted successfully!');
                                                                } else {
                                                                    // Revert the optimistic update on error
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
                                                                    alert('Error deleting service: ' + (result.message || 'Unknown error'));
                                                                }
                                                            } catch (error) {
                                                                // Revert optimistic update on error
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
                                                                
                                                                if (error.name === 'AbortError') {
                                                                    alert('Request timed out. Please check if the service was deleted and refresh the page.');
                                                                } else {
                                                                    console.error('Error deleting service:', error);
                                                                    alert('Error deleting service. Please try again.');
                                                                }
                                                            }
                                                        }
                                                    }}
                                                    className="w-full px-4 sm:px-5 md:px-6 py-3 sm:py-3.5 md:py-4 bg-red-500 text-white rounded-xl sm:rounded-2xl text-xs sm:text-sm font-black uppercase hover:bg-red-600 transition-colors shadow-lg"
                                                >
                                                    DELETE SERVICE
                                                </button>
                                            )}
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    setShowAddModal(false);
                                                    setSelectedServiceForEdit(null);
                                                }}
                                                className="w-full px-4 sm:px-5 md:px-6 py-3 sm:py-3.5 md:py-4 bg-slate-600 text-white rounded-xl sm:rounded-2xl text-xs sm:text-sm font-black uppercase hover:bg-slate-700 transition-colors"
                                            >
                                                CANCEL
                                            </button>
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
