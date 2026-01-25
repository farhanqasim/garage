<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elite Car Wash - Service Station</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Suppress Console Warnings and React DevTools Message -->
    <script>
        // Suppress Tailwind CDN and Babel warnings
        (function() {
            const originalWarn = console.warn;
            const originalLog = console.log;
            
            console.warn = function(...args) {
                const message = args[0] || '';
                // Suppress Tailwind CDN production warning
                if (typeof message === 'string' && (
                    message.includes('cdn.tailwindcss.com should not be used in production') ||
                    message.includes('Tailwind CSS in production')
                )) {
                    return;
                }
                // Suppress Babel transformer warning
                if (typeof message === 'string' && (
                    message.includes('in-browser Babel transformer') ||
                    message.includes('precompile your scripts for production')
                )) {
                    return;
                }
                // Suppress React DevTools message
                if (typeof message === 'string' && (
                    message.includes('Download the React DevTools') ||
                    message.includes('reactjs.org/link/react-devtools')
                )) {
                    return;
                }
                // Allow other warnings
                originalWarn.apply(console, args);
            };
            
            // Suppress React DevTools log messages
            console.log = function(...args) {
                const message = args[0] || '';
                // Suppress React DevTools download message
                if (typeof message === 'string' && (
                    message.includes('Download the React DevTools') ||
                    message.includes('reactjs.org/link/react-devtools')
                )) {
                    return;
                }
                // Allow other logs (but you can remove this if you want to suppress all logs)
                originalLog.apply(console, args);
            };
        })();
    </script>
    
    <!-- Tailwind CSS -->
    <script>
        // Suppress Tailwind CDN production warning
        window.tailwind = { config: { suppressWarnings: true } };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- React and ReactDOM (Production builds to avoid DevTools warning) -->
    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    
    <!-- Babel Standalone for JSX transformation (Development only - for production, precompile with build tools) -->
    <script>
        // Suppress Babel transformer warning
        if (typeof Babel !== 'undefined') {
            Babel.registerPreset('env', {});
        }
    </script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    
    <!-- html2pdf library for PDF download -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <!-- Tesseract.js for OCR (Number Plate Recognition) -->
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
                'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue',
                sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }
        
        #root {
            width: 100%;
            min-height: 100vh;
        }
        
        /* Settings button styling */
        .settings-button {
            background: transparent;
            padding: 12px;
            border-radius: 12px;
            text-decoration: none;
            color: #3b82f6;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .settings-button:hover {
            color: #2563eb;
            transform: translateY(-2px);
        }
        
        .settings-button svg {
            width: 24px;
            height: 24px;
        }
        
        /* Settings dropdown menu */
        .settings-menu-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .settings-dropdown {
            position: fixed;
            top: 80px;
            right: 20px;
            width: 280px;
            background: white;
            border-radius: 28px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
            border: 2px solid #e2e8f0;
            padding: 20px 16px;
            z-index: 1100;
            opacity: 0;
            transform: translateY(-10px) scale(0.95);
            pointer-events: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .settings-dropdown.show {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: all;
        }
        
        .settings-dropdown-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: #1e293b;
            border: none;
            background: transparent;
        }
        
        .settings-dropdown-item:hover {
            background: #f8fafc;
        }
        
        .settings-dropdown-item:active {
            transform: scale(0.98);
        }
        
        .settings-dropdown-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: #3b82f6;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 2px solid #3b82f6;
        }
        
        .settings-dropdown-item:hover .settings-dropdown-icon {
            transform: scale(1.05);
        }
        
        .settings-dropdown-icon svg {
            width: 22px;
            height: 22px;
            color: white;
            stroke-width: 2.5;
        }
        
        .settings-dropdown-content {
            flex: 1;
        }
        
        .settings-dropdown-title {
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #0f172a;
            line-height: 1.3;
        }
        
        .settings-dropdown-subtitle {
            display: none;
        }
        
        .settings-dropdown-arrow {
            display: none;
        }
        
        /* Add Category Modal */
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
        
        .category-form-input#categoryName {
            text-transform: uppercase;
        }
        
        .category-form-input:focus {
            background: #e2e8f0;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .category-form-input::placeholder {
            color: #94a3b8;
            font-weight: 500;
            text-transform: none;
        }
        
        /* Theme Selector */
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
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }
        
        /* Preview Section */
        .preview-section {
            margin-bottom: 32px;
        }
        
        .preview-label {
            display: block;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: white;
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
        
        .category-btn-submit:active {
            transform: translateY(0);
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
        
        .category-btn-delete:active {
            transform: translateY(0);
        }
        
        /* File Upload Styles */
        .file-upload-container {
            position: relative;
        }
        
        .file-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            overflow: hidden;
        }
        
        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 18px 20px;
            border-radius: 20px;
            font-size: 16px;
            font-weight: 600;
            color: #64748b;
            background: #f1f5f9;
            border: 2px dashed #cbd5e1;
            cursor: pointer;
            transition: all 0.2s;
            outline: none;
        }
        
        .file-upload-label:hover {
            background: #e2e8f0;
            border-color: #3b82f6;
            color: #3b82f6;
        }
        
        .file-upload-label svg {
            width: 20px;
            height: 20px;
        }
        
        .image-preview {
            margin-top: 12px;
            display: none;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        
        .image-preview.show {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        .image-preview img {
            width: 100%;
            max-height: 250px;
            object-fit: contain;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
            padding: 8px;
            display: block;
        }
        
        .image-preview .remove-image-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            cursor: pointer;
            font-weight: bold;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transition: all 0.2s;
            z-index: 10;
        }
        
        .image-preview .remove-image-btn:hover {
            background: #dc2626;
            transform: scale(1.1);
        }
        
        textarea.category-form-input {
            min-height: 80px;
            resize: vertical;
            font-family: inherit;
        }
        
        /* Print Styles */
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
        /* Accessibility improvements */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }
        .focus\:not-sr-only:focus {
            position: static;
            width: auto;
            height: auto;
            padding: inherit;
            margin: inherit;
            overflow: visible;
            clip: auto;
            white-space: normal;
        }
        /* Keyboard focus styles */
        *:focus-visible {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .bg-slate-950 {
                background-color: #000000;
            }
            .text-white {
                color: #ffffff;
            }
            .border-slate-200 {
                border-color: #000000;
            }
        }
        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
        /* Loading states */
        .loading {
            position: relative;
            pointer-events: none;
            opacity: 0.6;
        }
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #3b82f6;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        .blink-animation {
            animation: blink 1s ease-in-out infinite;
        }
        /* Mobile responsive improvements */
        @media (max-width: 640px) {
            header {
                padding: 1rem !important;
            }
            .category-modal {
                width: 95% !important;
                max-width: 95% !important;
                margin: 1rem auto !important;
                max-height: 90vh !important;
            }
            .grid-cols-3 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
            
            .text-2xl {
                font-size: 1.5rem;
            }
            
            .gap-3 {
                gap: 0.75rem;
            }
        }
        /* Touch target sizes for mobile */
        button, a, [role="button"] {
            min-height: 44px;
            min-width: 44px;
        }
    </style>
</head>
<body>
    <div class="settings-menu-container">
        <div class="settings-dropdown" id="settingsDropdown">
            <a href="{{ route('car.wash.services') }}" class="settings-dropdown-item" id="addNewCategoryBtn">
                <div class="settings-dropdown-icon" style="background: #3b82f6; border-color: #3b82f6;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <div class="settings-dropdown-content">
                    <div class="settings-dropdown-title">Add New Service</div>
                </div>
            </a>
            <a href="{{ route('car.wash.staff') }}" class="settings-dropdown-item" id="addNewWorkerBtn">
                <div class="settings-dropdown-icon" style="background: #8b5cf6; border-color: #8b5cf6;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="settings-dropdown-content">
                    <div class="settings-dropdown-title">Staff Settings</div>
                </div>
            </a>
            <a href="#" class="settings-dropdown-item" id="addExpenseBtn">
                <div class="settings-dropdown-icon" style="background: #ef4444; border-color: #ef4444;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="settings-dropdown-content">
                    <div class="settings-dropdown-title">Add Expense</div>
                </div>
            </a>
        </div>
    </div>
    <!-- Add Category Modal -->
    <div class="category-modal-overlay" id="categoryModalOverlay">
        <div class="category-modal">
            <div class="category-modal-header">
                <div>
                    <h2 class="category-modal-title">NEW CATEGORY</h2>
                    <p class="category-modal-subtitle">ADD A NEW SERVICE TO STATION</p>
                </div>
                <button class="category-modal-close" id="categoryModalClose" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="category-modal-body">
                <form id="categoryForm">
                    <div class="category-form-group">
                        <label class="category-form-label" for="categoryName">CATEGORY NAME</label>
                        <input 
                            type="text" 
                            id="categoryName" 
                            name="categoryName" 
                            class="category-form-input" 
                            placeholder="e.g. Engine Detail"
                            required
                        />
                    </div>
                    <div class="category-form-group">
                        <label class="category-form-label" for="categoryPrice">BASE PRICE (RS.)</label>
                        <input 
                            type="number" 
                            id="categoryPrice" 
                            name="categoryPrice" 
                            class="category-form-input" 
                            placeholder="0"
                            min="0"
                            step="1"
                            value="0"
                            required
                        />
                        <button 
                            type="button" 
                            id="addAdditionalPriceBtn" 
                            class="category-btn"
                            style="margin-top: 12px; width: 100%; background: #f1f5f9; color: #475569; border: 2px solid #e2e8f0; font-weight: 700; padding: 12px; border-radius: 12px; cursor: pointer; transition: all 0.2s;"
                            onmouseover="this.style.background='#e2e8f0'"
                            onmouseout="this.style.background='#f1f5f9'"
                        >
                            + ADD ADDITIONAL PRICE
                        </button>
                        <div id="additionalPricesContainer" style="margin-top: 12px;">
                            <!-- Additional prices will be added here dynamically -->
                        </div>
                    </div>
                    <div class="category-form-group icon-selector">
                        <label class="category-form-label">CHOOSE ICON</label>
                        <div class="icon-grid" id="iconGrid">
                            <div class="icon-option selected" data-icon="car" title="Car">
                                <svg width="32" height="32" fill="#3b82f6" viewBox="0 0 24 24">
                                    <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                                </svg>
                            </div>
                            <div class="icon-option" data-icon="bus" title="Bus">
                                <svg width="32" height="32" fill="#10b981" viewBox="0 0 24 24">
                                    <path d="M4 16c0 .88.39 1.67 1 2.22V20a1 1 0 001 1h1a1 1 0 001-1v-1h8v1a1 1 0 001 1h1a1 1 0 001-1v-1.78c.61-.55 1-1.34 1-2.22V6c0-3.31-2.69-6-6-6H6C2.69 0 0 2.69 0 6v10zm3.5 1c-.83 0-1.5-.67-1.5-1.5S6.67 14 7.5 14s1.5.67 1.5 1.5S8.33 17 7.5 17zm9 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm1.5-6H6V6h12v5z"/>
                                </svg>
                            </div>
                            <div class="icon-option" data-icon="motorcycle" title="Motorcycle">
                                <svg width="32" height="32" fill="#ef4444" viewBox="0 0 24 24">
                                    <path d="M19.44 9.03L15.41 5H11v2h3.59l2 2H5c-2.8 0-5 2.2-5 5s2.2 5 5 5c2.46 0 4.45-1.69 4.9-4h1.65l2.77-2.77c-.21.54-.32 1.14-.32 1.77 0 2.8 2.2 5 5 5s5-2.2 5-5c0-2.65-1.97-4.77-4.56-4.97zM7 15c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm10 0c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3z"/>
                                </svg>
                            </div>
                            <div class="icon-option" data-icon="truck" title="Truck">
                                <svg width="32" height="32" fill="#a855f7" viewBox="0 0 24 24">
                                    <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                                </svg>
                            </div>
                            <div class="icon-option" data-icon="auto-rickshaw" title="Auto Rickshaw">
                                <svg width="32" height="32" fill="#f97316" viewBox="0 0 24 24">
                                    <path d="M18.92 2.01C18.72 1.42 18.16 1 17.5 1h-11c-.66 0-1.21.42-1.42 1.01L3 8v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1V8l-2.08-5.99zM6.5 12c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9s1.5.67 1.5 1.5S7.33 12 6.5 12zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 7l1.5-4.5h11L19 7H5z"/>
                                </svg>
                            </div>
                            <div class="icon-option" data-icon="cycle" title="Cycle">
                                <svg width="32" height="32" fill="#1e40af" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                                </svg>
                            </div>
                            <div class="icon-option" data-icon="tractor" title="Tractor">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <!-- Tractor Body -->
                                    <rect x="3" y="7" width="15" height="7" rx="1" fill="#ec4899"/>
                                    <!-- Front Grille/Vents -->
                                    <line x1="4" y1="9" x2="4" y2="11" stroke="#ec4899" stroke-width="0.8" opacity="0.6"/>
                                    <line x1="5.5" y1="9" x2="5.5" y2="11" stroke="#ec4899" stroke-width="0.8" opacity="0.6"/>
                                    <line x1="7" y1="9" x2="7" y2="11" stroke="#ec4899" stroke-width="0.8" opacity="0.6"/>
                                    <!-- Top Panel Divisions -->
                                    <line x1="7" y1="7" x2="7" y2="9" stroke="#ec4899" stroke-width="0.8" opacity="0.6"/>
                                    <line x1="11" y1="7" x2="11" y2="9" stroke="#ec4899" stroke-width="0.8" opacity="0.6"/>
                                    <!-- Exhaust Pipe -->
                                    <path d="M3 7 L3 5 L4 5 L4 6.5" stroke="#ec4899" stroke-width="2" fill="none"/>
                                    <!-- Front Wheel (Smaller) -->
                                    <circle cx="6" cy="17" r="3.5" fill="#ec4899" opacity="0.2"/>
                                    <circle cx="6" cy="17" r="2.5" fill="#ec4899" opacity="0.4"/>
                                    <circle cx="6" cy="17" r="1.2" fill="#ec4899"/>
                                    <path d="M6 13.5 L6 20.5 M2.5 17 L9.5 17" stroke="#ec4899" stroke-width="1" opacity="0.7"/>
                                    <!-- Rear Wheel (Larger) -->
                                    <circle cx="16" cy="17" r="4.5" fill="#ec4899" opacity="0.2"/>
                                    <circle cx="16" cy="17" r="3.2" fill="#ec4899" opacity="0.4"/>
                                    <circle cx="16" cy="17" r="1.3" fill="#ec4899"/>
                                    <path d="M16 12.5 L16 21.5 M11.5 17 L20.5 17" stroke="#ec4899" stroke-width="1.2" opacity="0.7"/>
                                    <!-- Driver Head -->
                                    <circle cx="9.5" cy="9.5" r="1.5" fill="#ec4899"/>
                                    <!-- Driver Body -->
                                    <rect x="8.5" y="11" width="2.5" height="3" rx="0.5" fill="#ec4899"/>
                                    <!-- Steering Wheel -->
                                    <circle cx="11.5" cy="12" r="1" fill="#ec4899" opacity="0.8"/>
                                    <line x1="11.5" y1="11" x2="11.5" y2="13" stroke="#ec4899" stroke-width="0.8" opacity="0.9"/>
                                    <!-- Seat -->
                                    <rect x="13" y="11.5" width="2" height="2.5" rx="0.3" fill="#ec4899" opacity="0.7"/>
                                </svg>
                            </div>
                            <div class="icon-option" data-icon="luxury-car" title="Luxury Car">
                                <svg width="32" height="32" fill="#8b5cf6" viewBox="0 0 24 24">
                                    <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                                </svg>
                            </div>
                            <div class="icon-option" data-icon="clean" title="Clean/Carpet">
                                <svg width="32" height="32" fill="#14b8a6" viewBox="0 0 24 24">
                                    <path d="M19.36 2.72L20.78 4.14 15.06 9.85C16.13 11.39 16.28 13.24 15.38 14.44L9.06 8.12C10.26 7.22 12.11 7.37 13.65 8.44L19.36 2.72M5.93 17.57C3.92 15.56 2.69 13.16 2.35 10.92L7.23 8.83L14.67 16.27L12.58 21.15C10.34 20.81 7.94 19.58 5.93 17.57Z"/>
                                </svg>
                            </div>
                        </div>
                        <input type="hidden" id="selectedIcon" value="car">
                    </div>
                    <div class="category-form-group theme-selector">
                        <label class="category-form-label">CHOOSE THEME</label>
                        <div class="theme-colors-grid" id="themeColorsGrid">
                            <div class="theme-color-option selected" data-color="#3b82f6" data-class="bg-blue-600" style="background: #3b82f6;"></div>
                            <div class="theme-color-option" data-color="#10b981" data-class="bg-emerald-600" style="background: #10b981;"></div>
                            <div class="theme-color-option" data-color="#ef4444" data-class="bg-red-500" style="background: #ef4444;"></div>
                            <div class="theme-color-option" data-color="#a855f7" data-class="bg-purple-500" style="background: #a855f7;"></div>
                            <div class="theme-color-option" data-color="#f97316" data-class="bg-orange-500" style="background: #f97316;"></div>
                            <div class="theme-color-option" data-color="#1e40af" data-class="bg-blue-800" style="background: #1e40af;"></div>
                            <div class="theme-color-option" data-color="#ec4899" data-class="bg-pink-500" style="background: #ec4899;"></div>
                            <div class="theme-color-option" data-color="#8b5cf6" data-class="bg-violet-500" style="background: #8b5cf6;"></div>
                        </div>
                        <input type="hidden" id="selectedThemeColor" value="#3b82f6">
                        <input type="hidden" id="selectedThemeClass" value="bg-blue-600">
                    </div>
                    <div class="category-form-group preview-section">
                        <label class="preview-label">PREVIEW</label>
                        <div class="preview-container">
                            <div class="preview-service-card" id="previewCard" style="background: #3b82f6;">
                                <span id="previewLabel">SERVICE LABEL</span>
                                <span class="price">· RS.0</span>
                            </div>
                        </div>
                    </div>
                    <div class="category-form-actions">
                        <button type="submit" class="category-btn category-btn-submit" id="categorySubmitBtn">SAVE</button>
                        <button type="button" id="deleteCategoryBtn" class="category-btn category-btn-delete">DELETE SERVICE</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Add Worker Modal -->
    <div class="category-modal-overlay" id="workerModalOverlay">
        <div class="category-modal">
            <div class="category-modal-header">
                <div>
                    <h2 class="category-modal-title">NEW WORKER</h2>
                    <p class="category-modal-subtitle">ADD A NEW WORKER TO STATION</p>
                </div>
                <button class="category-modal-close" id="workerModalClose" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="category-modal-body">
                <form id="workerForm">
                    <div class="category-form-group">
                        <label class="category-form-label" for="workerName">WORKER NAME</label>
                        <input 
                            type="text" 
                            id="workerName" 
                            name="workerName" 
                            class="category-form-input" 
                            placeholder="e.g. John Doe"
                            required
                            style="text-transform: uppercase;"
                        />
                    </div>
                    
                    <div class="category-form-group">
                        <label class="category-form-label" for="workerMobile">MOBILE NUMBER</label>
                        <input 
                            type="tel" 
                            id="workerMobile" 
                            name="workerMobile" 
                            class="category-form-input" 
                            placeholder="e.g. 0300-1234567"
                            required
                        />
                        <div id="workerMobileNumbersContainer" style="margin-top: 12px; display: none;">
                            <!-- Additional mobile numbers will be added here dynamically -->
                        </div>
                        <button 
                            type="button" 
                            id="addWorkerMobileBtn" 
                            class="category-btn"
                            style="margin-top: 12px; width: 100%; background: #f1f5f9; color: #475569; border: 2px solid #e2e8f0; font-weight: 700; padding: 12px; border-radius: 12px; cursor: pointer; transition: all 0.2s;"
                            onmouseover="this.style.background='#e2e8f0'"
                            onmouseout="this.style.background='#f1f5f9'"
                        >
                            + ADD NEW NUMBER
                        </button>
                    </div>
                    
                    <div class="category-form-group">
                        <label class="category-form-label" for="workerFatherName">FATHER NAME</label>
                        <input 
                            type="text" 
                            id="workerFatherName" 
                            name="workerFatherName" 
                            class="category-form-input" 
                            placeholder="e.g. Muhammad Ali"
                            required
                            style="text-transform: uppercase;"
                        />
                    </div>
                    
                    <div class="category-form-group">
                        <label class="category-form-label" for="workerFatherMobile">FATHER MOBILE NUMBER</label>
                        <input 
                            type="tel" 
                            id="workerFatherMobile" 
                            name="workerFatherMobile" 
                            class="category-form-input" 
                            placeholder="e.g. 0300-1234567"
                            required
                        />
                    </div>
                    
                    <div class="category-form-group">
                        <label class="category-form-label" for="workerLocation">LOCATION / HOME ADDRESS</label>
                        <textarea 
                            id="workerLocation" 
                            name="workerLocation" 
                            class="category-form-input" 
                            placeholder="Enter full address"
                            rows="3"
                            style="resize: none; min-height: 80px;"
                            required
                        ></textarea>
                    </div>
                    
                    <div class="category-form-group">
                        <label class="category-form-label" for="workerCommission">COMMISSION / SET BOX (%)</label>
                        <input 
                            type="number" 
                            id="workerCommission" 
                            name="workerCommission" 
                            class="category-form-input" 
                            placeholder="0"
                            min="0"
                            max="100"
                            step="1"
                            value="0"
                            required
                        />
                    </div>
                    
                    <div class="category-form-group">
                        <label class="category-form-label">ID CARD PICTURE (FRONT)</label>
                        <div class="file-upload-container">
                            <input 
                                type="file" 
                                id="workerIdCardFront" 
                                name="workerIdCardFront" 
                                accept="image/*"
                                class="file-input"
                                required
                            />
                            <label for="workerIdCardFront" class="file-upload-label">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 8px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span id="workerIdCardFrontLabel">Choose Front Image</span>
                            </label>
                            <div id="workerIdCardFrontPreview" class="image-preview"></div>
                        </div>
                    </div>
                    
                    <div class="category-form-group">
                        <label class="category-form-label">ID CARD PICTURE (BACK)</label>
                        <div class="file-upload-container">
                            <input 
                                type="file" 
                                id="workerIdCardBack" 
                                name="workerIdCardBack" 
                                accept="image/*"
                                class="file-input"
                                required
                            />
                            <label for="workerIdCardBack" class="file-upload-label">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 8px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span id="workerIdCardBackLabel">Choose Back Image</span>
                            </label>
                            <div id="workerIdCardBackPreview" class="image-preview"></div>
                        </div>
                    </div>
                    
                    <div class="category-form-group">
                        <label class="category-form-label">FATHER / REFERENCE CARD (FRONT)</label>
                        <div class="file-upload-container">
                            <input 
                                type="file" 
                                id="workerFatherCardFront" 
                                name="workerFatherCardFront" 
                                accept="image/*"
                                class="file-input"
                                required
                            />
                            <label for="workerFatherCardFront" class="file-upload-label">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 8px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span id="workerFatherCardFrontLabel">Choose Front Image</span>
                            </label>
                            <div id="workerFatherCardFrontPreview" class="image-preview"></div>
                        </div>
                    </div>
                    
                    <div class="category-form-group">
                        <label class="category-form-label">FATHER / REFERENCE CARD (BACK)</label>
                        <div class="file-upload-container">
                            <input 
                                type="file" 
                                id="workerFatherCardBack" 
                                name="workerFatherCardBack" 
                                accept="image/*"
                                class="file-input"
                                required
                            />
                            <label for="workerFatherCardBack" class="file-upload-label">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 8px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span id="workerFatherCardBackLabel">Choose Back Image</span>
                            </label>
                            <div id="workerFatherCardBackPreview" class="image-preview"></div>
                        </div>
                    </div>
                    
                    <div class="category-form-actions">
                        <button type="submit" id="workerSubmitBtn" class="category-btn category-btn-submit">CONFIRM & SAVE</button>
                        <button type="button" id="deleteWorkerBtn" class="category-btn category-btn-delete" style="display: none;">DELETE WORKER</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Worker Details Modal -->
    <div class="category-modal-overlay" id="workerDetailsModalOverlay">
        <div class="category-modal" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
            <div class="category-modal-header">
                <div>
                    <h2 class="category-modal-title">WORKER DETAILS</h2>
                    <p class="category-modal-subtitle">ALL REGISTERED WORKERS</p>
                </div>
                <button class="category-modal-close" id="workerDetailsModalClose" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="category-modal-body">
                <div id="workerDetailsList" style="display: grid; gap: 20px;">
                    <!-- Workers will be loaded here -->
                </div>
            </div>
        </div>
    </div>
    
    <div id="root"></div>
    
    <script type="text/babel">
        const { useState, useEffect, useMemo, useRef } = React;
        
        // Note: This is a simplified version that works in the browser
        // The full React app code you provided uses ES6 modules and Firebase v9+ modular SDK
        // For production, the app should be built using Vite/Webpack
        
        // Firebase Configuration - Update with your Firebase config
        window.__firebase_config = JSON.stringify({
            apiKey: "YOUR_API_KEY",
            authDomain: "YOUR_AUTH_DOMAIN",
            projectId: "YOUR_PROJECT_ID",
            storageBucket: "YOUR_STORAGE_BUCKET",
            messagingSenderId: "YOUR_MESSAGING_SENDER_ID",
            appId: "YOUR_APP_ID"
        });
        window.__app_id = 'service-station-app-pro';
        
        // Home route URL
        const homeRoute = '{{ route("home") }}';
        
        // Get user and branch info from Blade (passed from controller)
        const branchName = @json(isset($branchName) ? $branchName : 'No Branch');
        const userName = @json(isset($userName) ? $userName : 'Guest');
        
        // Get data from backend (passed from controller)
        const initialServices = @json(isset($services) ? $services : []);
        const initialWorkers = @json(isset($workers) ? $workers : []);
        const initialActiveJobs = @json(isset($activeJobs) ? $activeJobs : []);
        const initialCompletedJobs = @json(isset($completedJobs) ? $completedJobs : []);
        
        // API Routes for car wash
        const API_ROUTES = {
            services: {
                index: '{{ route("car-wash.services.index") }}',
                store: '{{ route("car-wash.services.store") }}',
                update: (id) => `{{ url('/car-wash/services') }}/${id}`,
                destroy: (id) => `{{ url('/car-wash/services') }}/${id}`,
            },
            workers: {
                index: '{{ route("car-wash.workers.index") }}',
                store: '{{ route("car-wash.workers.store") }}',
                update: (id) => `{{ url('/car-wash/workers') }}/${id}`,
                destroy: (id) => `{{ url('/car-wash/workers') }}/${id}`,
            },
            jobs: {
                index: '{{ route("car-wash.jobs.index") }}',
                active: '{{ route("car-wash.jobs.active") }}',
                completed: '{{ route("car-wash.jobs.completed") }}',
                todayStats: '{{ route("car-wash.jobs.today-stats") }}',
                store: '{{ route("car-wash.jobs.store") }}',
                update: (id) => `{{ url('/car-wash/jobs') }}/${id}`,
                complete: (id) => `{{ url('/car-wash/jobs') }}/${id}/complete`,
                cancel: (id) => `{{ url('/car-wash/jobs') }}/${id}/cancel`,
                destroy: (id) => `{{ url('/car-wash/jobs') }}/${id}`,
            },
            inspections: {
                show: (jobId) => `{{ url('/car-wash/inspections') }}/${jobId}`,
                store: (jobId) => `{{ url('/car-wash/inspections') }}/${jobId}`,
            },
            expenses: {
                show: (jobId) => `{{ url('/car-wash/expenses') }}/${jobId}`,
                store: (jobId) => `{{ url('/car-wash/expenses') }}/${jobId}`,
            },
            banks: {
                index: '{{ route("car-wash.banks.index") }}',
            },
            cashTransfers: {
                store: '{{ route("car-wash.cash-transfers.store") }}',
            },
            payments: {
                cashAccountBalance: '{{ route("car-wash.payments.cash-account-balance") }}',
                branchUsers: '{{ route("car-wash.payments.branch-users") }}',
                transferToUser: '{{ route("car-wash.payments.transfer-to-user") }}',
            }
        };
        
        // CSRF Token for API calls
        const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') : '';
        
        // Simplified App Component (UI structure without Firebase)
        const App = () => {
            const [view, setView] = useState('dashboard');
            const [stats, setStats] = useState({ todayRevenue: 0, todayExpensesTotal: 0, todayGrandTotal: 0, cashOnHand: 0 });
            
            // Function to refresh cash account balance
            const refreshCashBalance = () => {
                if (API_ROUTES.payments && API_ROUTES.payments.cashAccountBalance) {
                    fetch(API_ROUTES.payments.cashAccountBalance)
                        .then(res => res.json())
                        .then(balanceData => {
                            if (balanceData.success) {
                                const newBalance = parseFloat(balanceData.balance) || 0;
                                setStats(prev => ({
                                    ...prev,
                                    cashOnHand: newBalance
                                }));
                            }
                        })
                        .catch(err => {
                            console.error('Error refreshing cash account balance:', err);
                        });
                }
            };
            
            // Load stats from API on mount - simplified to avoid errors
            useEffect(() => {
                // Fetch user's cash account balance first
                let userCashBalance = 0;
                if (API_ROUTES.payments && API_ROUTES.payments.cashAccountBalance) {
                    fetch(API_ROUTES.payments.cashAccountBalance)
                        .then(res => res.json())
                        .then(balanceData => {
                            if (balanceData.success) {
                                userCashBalance = parseFloat(balanceData.balance) || 0;
                            }
                        })
                        .catch(err => {
                            console.error('Error loading cash account balance:', err);
                        });
                }
                
                // Calculate stats from completed jobs
                fetch(API_ROUTES.jobs.completed)
                    .then(res => res.json())
                    .then(completedData => {
                        if (completedData.success && completedData.jobs) {
                            const today = new Date().toDateString();
                            const todayJobs = completedData.jobs.filter(job => {
                                if (!job.endTime) return false;
                                try {
                                    const jobDate = new Date(job.endTime).toDateString();
                                    return jobDate === today;
                                } catch (e) {
                                    return false;
                                }
                            });
                            const todayRevenue = todayJobs.reduce((sum, job) => sum + (parseFloat(job.price) || 0), 0);
                            
                            // Use user's cash account balance instead of calculating from jobs
                            const cashOnHand = userCashBalance;
                            
                            // Try to get expenses from todayStats API if available
                            if (API_ROUTES.jobs.todayStats) {
                                fetch(API_ROUTES.jobs.todayStats)
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success && data.stats) {
                                            setStats({
                                                todayRevenue: todayRevenue,
                                                todayExpensesTotal: data.stats.todayExpensesTotal || 0,
                                                todayGrandTotal: todayRevenue - (data.stats.todayExpensesTotal || 0),
                                                cashOnHand: cashOnHand
                                            });
                                        } else {
                                            // Fallback: calculate without expenses
                                            setStats({
                                                todayRevenue: todayRevenue,
                                                todayExpensesTotal: 0,
                                                todayGrandTotal: todayRevenue,
                                                cashOnHand: cashOnHand
                                            });
                                        }
                                    })
                                    .catch(err => {
                                        console.error('Error loading stats from API:', err);
                                        // Fallback: calculate without expenses
                                        setStats({
                                            todayRevenue: todayRevenue,
                                            todayExpensesTotal: 0,
                                            todayGrandTotal: todayRevenue,
                                            cashOnHand: cashOnHand
                                        });
                                    });
                            } else {
                                // No todayStats API, just use revenue
                                setStats({
                                    todayRevenue: todayRevenue,
                                    todayExpensesTotal: 0,
                                    todayGrandTotal: todayRevenue,
                                    cashOnHand: cashOnHand
                                });
                            }
                        } else {
                            // No jobs, set default stats with cash balance
                            setStats({
                                todayRevenue: 0,
                                todayExpensesTotal: 0,
                                todayGrandTotal: 0,
                                cashOnHand: userCashBalance
                            });
                        }
                    })
                    .catch(err => {
                        console.error('Error loading completed jobs for stats:', err);
                        // Set default stats on error with cash balance
                        setStats({
                            todayRevenue: 0,
                            todayExpensesTotal: 0,
                            todayGrandTotal: 0,
                            cashOnHand: userCashBalance
                        });
                    });
            }, []);
            // Load categories from backend (passed from controller) - database only
            const [categories, setCategories] = useState(() => {
                // Use backend services from database
                return initialServices && Array.isArray(initialServices) ? initialServices : [];
            });
            const [selectedService, setSelectedService] = useState(null);
            const [selectedAdditionalPrices, setSelectedAdditionalPrices] = useState(new Set());
            const [formData, setFormData] = useState({
                customerName: '',
                vehicleNo: '',
                mobile: '',
                worker: '', // Must be explicitly selected - no default value
                workerId: null, // Worker ID for database
                price: 0
            });
            
            // Debug: Log formData changes
            useEffect(() => {
                console.log('formData updated:', formData);
            }, [formData]);
            
            // Load workers from backend - database only (keep full objects with id)
            const [workers, setWorkers] = useState(() => {
                // Use backend workers from database - keep full objects
                if (initialWorkers && Array.isArray(initialWorkers) && initialWorkers.length > 0) {
                    const filtered = initialWorkers.filter(w => w && (typeof w === 'object' || typeof w === 'string'));
                    console.log('Initial workers loaded:', filtered);
                    return filtered;
                }
                console.log('No initial workers found');
                return [];
            });
            
            // Listen for worker updates - fetch from API
            useEffect(() => {
                const handleWorkersUpdate = async () => {
                    try {
                        const response = await fetch(API_ROUTES.workers.index);
                        const data = await response.json();
                        if (data.success && data.workers) {
                            // Keep full worker objects with id
                            const filtered = data.workers.filter(w => w && (typeof w === 'object' || typeof w === 'string'));
                            console.log('Workers loaded from API:', filtered);
                            setWorkers(filtered);
                        }
                    } catch (error) {
                        console.error('Error loading workers from API:', error);
                    }
                };
                
                window.addEventListener('workersUpdated', handleWorkersUpdate);
                window.addEventListener('refreshStaffList', handleWorkersUpdate);
                
                return () => {
                    window.removeEventListener('workersUpdated', handleWorkersUpdate);
                    window.removeEventListener('refreshStaffList', handleWorkersUpdate);
                };
            }, []);
            
            // Get workers - use state directly, not a constant (full objects with id)
            const WORKERS = workers || [];
            const [editingCategory, setEditingCategory] = useState(null);
            const [showServicesDropdown, setShowServicesDropdown] = useState(false);
            const [showAllServicesModal, setShowAllServicesModal] = useState(false);
            const [showAllStaffModal, setShowAllStaffModal] = useState(false);
            const [expenseModalJobId, setExpenseModalJobId] = useState(null);
            const [expenseItems, setExpenseItems] = useState({});
            const [customExpenses, setCustomExpenses] = useState([]);
            const [showAddCustomExpense, setShowAddCustomExpense] = useState(false);
            const [newCustomExpenseName, setNewCustomExpenseName] = useState('');
            const [showExpenseDetailsModal, setShowExpenseDetailsModal] = useState(false);
            const [expenseHistory, setExpenseHistory] = useState([]);
            const [showCompletedJobsModal, setShowCompletedJobsModal] = useState(false);
            const [selectedJobForDetail, setSelectedJobForDetail] = useState(null);
            const [selectedJobForEdit, setSelectedJobForEdit] = useState(null);
            const [completedJobs, setCompletedJobs] = useState(() => {
                // Use backend completed jobs from database only
                return initialCompletedJobs && Array.isArray(initialCompletedJobs) ? initialCompletedJobs : [];
            });
            const [dateRangeStart, setDateRangeStart] = useState(() => {
                const today = new Date();
                return today.toISOString().split('T')[0];
            });
            const [dateRangeEnd, setDateRangeEnd] = useState(() => {
                const today = new Date();
                return today.toISOString().split('T')[0];
            });
            const [filteredCompletedJobs, setFilteredCompletedJobs] = useState([]);
            const [showAvgDetailsModal, setShowAvgDetailsModal] = useState(false);
            const [completeModalJobId, setCompleteModalJobId] = useState(null);
            const [selectedRating, setSelectedRating] = useState('');
            const [jobComment, setJobComment] = useState('');
            const [paymentMethod, setPaymentMethod] = useState('cash');
            const [selectedWorkerFilter, setSelectedWorkerFilter] = useState(null);
            const [showWorkerFilterModal, setShowWorkerFilterModal] = useState(false);
            const [showCashTransferModal, setShowCashTransferModal] = useState(false);
            const [transferTab, setTransferTab] = useState('cash'); // 'cash', 'bank', or 'user'
            const [transferMethods, setTransferMethods] = useState([
                { id: 'admin_cash', name: 'Admin Cash', icon: '💵', type: 'cash', balance: 0, subtitle: '', bankId: null }
            ]);
            const [transferAmount, setTransferAmount] = useState('');
            const [selectedTransferMethod, setSelectedTransferMethod] = useState(null);
            const [branchUsers, setBranchUsers] = useState([]);
            const [selectedUserId, setSelectedUserId] = useState(null);
            const [transferNote, setTransferNote] = useState('');
            
            // Load banks/transfer methods from API
            useEffect(() => {
                fetch(API_ROUTES.banks.index, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.banks) {
                            // Map banks to transfer methods format
                            const bankMethods = data.banks.map(bank => ({
                                id: `bank_${bank.id}`,
                                name: bank.name,
                                icon: bank.icon === 'bank' ? 'bank' : bank.icon,
                                type: bank.type,
                                balance: bank.balance || 0,
                                subtitle: bank.subtitle || '',
                                bankId: bank.id
                            }));
                            
                            // Combine with default admin cash
                            setTransferMethods(prev => [
                                prev[0], // Keep admin cash
                                ...bankMethods
                            ]);
                        }
                    })
                    .catch(err => {
                        console.error('Error loading banks:', err);
                        // Keep default methods on error
                    });
            }, []);
            const [currentTime, setCurrentTime] = useState(new Date());
            const [inspectionModalJobId, setInspectionModalJobId] = useState(null);
            const [inspectionData, setInspectionData] = useState({});
            const [completedInspections, setCompletedInspections] = useState(new Set());
            const pendingInspectionAlertsRef = React.useRef(new Set()); // Track jobs that have shown alerts (using ref for synchronous access)
            const userInteractionRef = React.useRef(false); // Track if user has interacted with page (required for speech)
            const [inspectionNotification, setInspectionNotification] = useState(null); // Custom notification state
            const [isRecording, setIsRecording] = useState(false);
            const [audioBlob, setAudioBlob] = useState(null);
            const [audioUrl, setAudioUrl] = useState(null);
            const [mediaRecorder, setMediaRecorder] = useState(null);
            const [recognition, setRecognition] = useState(null);
            const recognitionTranscriptRef = React.useRef(''); // Store transcript for mobile compatibility
            const [activeJobs, setActiveJobs] = useState(() => {
                // Use backend active jobs from database only
                return initialActiveJobs && Array.isArray(initialActiveJobs) ? initialActiveJobs : [];
            });
            
            // Default services
            const defaultServices = [
                { label: 'Mini Car Wash', basePrice: 300, color: '#3b82f6' },
                { label: 'Full Service', basePrice: 1500, color: '#10b981' }
            ];
            
            // Show all default services (no deletion tracking via localStorage)
            const activeDefaultServices = defaultServices;
            
            // Combine default services and categories for dashboard
            const allServices = [...activeDefaultServices, ...categories];
            
            // Services for All Services modal (all services including defaults)
            const modalServices = allServices;
            
            // Helper function to get icon SVG
            const getIconSVG = (iconName) => {
                const iconMap = {
                    'car': <svg className="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/></svg>,
                    'bus': <svg className="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M4 16c0 .88.39 1.67 1 2.22V20a1 1 0 001 1h1a1 1 0 001-1v-1h8v1a1 1 0 001 1h1a1 1 0 001-1v-1.78c.61-.55 1-1.34 1-2.22V6c0-3.31-2.69-6-6-6H6C2.69 0 0 2.69 0 6v10zm3.5 1c-.83 0-1.5-.67-1.5-1.5S6.67 14 7.5 14s1.5.67 1.5 1.5S8.33 17 7.5 17zm9 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm1.5-6H6V6h12v5z"/></svg>,
                    'motorcycle': <svg className="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19.44 9.03L15.41 5H11v2h3.59l2 2H5c-2.8 0-5 2.2-5 5s2.2 5 5 5c2.46 0 4.45-1.69 4.9-4h1.65l2.77-2.77c-.21.54-.32 1.14-.32 1.77 0 2.8 2.2 5 5 5s5-2.2 5-5c0-2.65-1.97-4.77-4.56-4.97zM7 15c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm10 0c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3z"/></svg>,
                    'truck': <svg className="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>,
                    'auto-rickshaw': <svg className="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 2.01C18.72 1.42 18.16 1 17.5 1h-11c-.66 0-1.21.42-1.42 1.01L3 8v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1V8l-2.08-5.99zM6.5 12c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9s1.5.67 1.5 1.5S7.33 12 6.5 12zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 7l1.5-4.5h11L19 7H5z"/></svg>,
                    'cycle': <svg className="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>,
                    'tractor': <svg className="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6" fill="currentColor" viewBox="0 0 24 24"><rect x="3" y="7" width="15" height="7" rx="1"/><line x1="4" y1="9" x2="4" y2="11" stroke="currentColor" strokeWidth="0.8" opacity="0.4"/><line x1="5.5" y1="9" x2="5.5" y2="11" stroke="currentColor" strokeWidth="0.8" opacity="0.4"/><line x1="7" y1="9" x2="7" y2="11" stroke="currentColor" strokeWidth="0.8" opacity="0.4"/><line x1="7" y1="7" x2="7" y2="9" stroke="currentColor" strokeWidth="0.8" opacity="0.4"/><line x1="11" y1="7" x2="11" y2="9" stroke="currentColor" strokeWidth="0.8" opacity="0.4"/><path d="M3 7 L3 5 L4 5 L4 6.5" stroke="currentColor" strokeWidth="2" fill="none"/><circle cx="6" cy="17" r="3.5" opacity="0.15"/><circle cx="6" cy="17" r="2.5" opacity="0.3"/><circle cx="6" cy="17" r="1.2"/><path d="M6 13.5 L6 20.5 M2.5 17 L9.5 17" stroke="currentColor" strokeWidth="1" opacity="0.7"/><circle cx="16" cy="17" r="4.5" opacity="0.15"/><circle cx="16" cy="17" r="3.2" opacity="0.3"/><circle cx="16" cy="17" r="1.3"/><path d="M16 12.5 L16 21.5 M11.5 17 L20.5 17" stroke="currentColor" strokeWidth="1.2" opacity="0.7"/><circle cx="9.5" cy="9.5" r="1.5"/><rect x="8.5" y="11" width="2.5" height="3" rx="0.5"/><circle cx="11.5" cy="12" r="1" opacity="0.8"/><line x1="11.5" y1="11" x2="11.5" y2="13" stroke="currentColor" strokeWidth="0.8" opacity="0.9"/><rect x="13" y="11.5" width="2" height="2.5" rx="0.3" opacity="0.7"/></svg>,
                    'luxury-car': <svg className="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/></svg>,
                    'clean': <svg className="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19.36 2.72L20.78 4.14 15.06 9.85C16.13 11.39 16.28 13.24 15.38 14.44L9.06 8.12C10.26 7.22 12.11 7.37 13.65 8.44L19.36 2.72M5.93 17.57C3.92 15.56 2.69 13.16 2.35 10.92L7.23 8.83L14.67 16.27L12.58 21.15C10.34 20.81 7.94 19.58 5.93 17.57Z"/></svg>
                };
                return iconMap[iconName] || iconMap['car'];
            };
            
            // Helper function to get hex color from Tailwind class
            const getColorValue = (colorClass) => {
                const colorMap = {
                    'bg-blue-600': '#2563eb',
                    'bg-emerald-600': '#10b981',
                    'bg-red-500': '#ef4444',
                    'bg-purple-500': '#a855f7',
                    'bg-orange-500': '#f97316',
                    'bg-blue-800': '#1e40af',
                    'bg-pink-500': '#ec4899',
                    'bg-violet-500': '#8b5cf6'
                };
                return colorMap[colorClass] || '#3b82f6';
            };
            
            // Listen for category updates - fetch from API
            useEffect(() => {
                const handleCategoriesUpdate = async () => {
                    try {
                        const response = await fetch(API_ROUTES.services.index);
                        const data = await response.json();
                        if (data.success && data.services) {
                            // Map services to convert base_price to basePrice and additional_prices to additionalPrices for frontend compatibility
                            const mappedServices = data.services.map(service => {
                                // Parse additional_prices if it's a string, otherwise use as is
                                let additionalPrices = [];
                                if (service.additional_prices) {
                                    additionalPrices = typeof service.additional_prices === 'string' 
                                        ? JSON.parse(service.additional_prices) 
                                        : service.additional_prices;
                                } else if (service.additionalPrices) {
                                    additionalPrices = typeof service.additionalPrices === 'string' 
                                        ? JSON.parse(service.additionalPrices) 
                                        : service.additionalPrices;
                                }
                                
                                return {
                                ...service,
                                    basePrice: service.base_price || service.basePrice || 0,
                                    additionalPrices: additionalPrices
                                };
                            });
                            setCategories(mappedServices);
                        }
                    } catch (error) {
                        console.error('Error loading categories from API:', error);
                    }
                };
                
                // Listen to custom event for same-window updates
                window.addEventListener('categoriesUpdated', handleCategoriesUpdate);
                
                // Fetch on mount
                handleCategoriesUpdate();
                
                return () => {
                    window.removeEventListener('categoriesUpdated', handleCategoriesUpdate);
                };
            }, []);
            
            // View state - no need to save to localStorage
            
            // Listen for navigate to dashboard event
            useEffect(() => {
                const handleNavigateToDashboard = () => {
                    console.log('Navigating to dashboard');
                    setView('dashboard');
                };
                
                window.addEventListener('navigateToDashboard', handleNavigateToDashboard);
                
                return () => {
                    window.removeEventListener('navigateToDashboard', handleNavigateToDashboard);
                };
            }, []);
            
            // Stats are calculated from database, no need to save to localStorage
            
            // Expense history stored in database, no need to save to localStorage
            
            // Initialize expense items with quantity 1 when modal opens
            useEffect(() => {
                if (expenseModalJobId) {
                    const defaultItems = ['Tea', 'Cold Drink', 'Mineral Water'];
                    const newItems = { ...expenseItems };
                    let needsUpdate = false;
                    
                    defaultItems.forEach(itemName => {
                        if (newItems[itemName] === undefined) {
                            newItems[itemName] = { quantity: 1, price: '' };
                            needsUpdate = true;
                        } else if (newItems[itemName].quantity === 0 || newItems[itemName].quantity === undefined) {
                            newItems[itemName] = { ...newItems[itemName], quantity: 1 };
                            needsUpdate = true;
                        }
                    });
                    
                    if (needsUpdate) {
                        setExpenseItems(newItems);
                    }
                }
            }, [expenseModalJobId]);
            
            // Update current time every second for timer
            useEffect(() => {
                const timer = setInterval(() => {
                    setCurrentTime(new Date());
                }, 1000);
                
                return () => clearInterval(timer);
            }, []);
            
            // Initialize speech synthesis voices and track user interaction
            useEffect(() => {
                if ('speechSynthesis' in window) {
                    // Load voices on component mount (some browsers need this)
                    const loadVoices = () => {
                        const voices = window.speechSynthesis.getVoices();
                        console.log('Available voices:', voices.length);
                    };
                    loadVoices();
                    if (window.speechSynthesis.onvoiceschanged !== undefined) {
                        window.speechSynthesis.onvoiceschanged = loadVoices;
                    }
                }
                
                // Track user interactions to enable speech synthesis
                const enableSpeech = () => {
                    userInteractionRef.current = true;
                    // Test speech synthesis on first interaction to enable it
                    if ('speechSynthesis' in window) {
                        try {
                            const testUtterance = new SpeechSynthesisUtterance('');
                            testUtterance.volume = 0; // Silent test
                            window.speechSynthesis.speak(testUtterance);
                            window.speechSynthesis.cancel(); // Cancel immediately
                            console.log('✅ Speech synthesis enabled after user interaction');
                        } catch (e) {
                            console.log('Speech test:', e);
                        }
                    }
                };
                
                // Listen for any user interaction (persistent, not once)
                document.addEventListener('click', enableSpeech, { passive: true });
                document.addEventListener('touchstart', enableSpeech, { passive: true });
                document.addEventListener('keydown', enableSpeech, { passive: true });
                
                return () => {
                    document.removeEventListener('click', enableSpeech);
                    document.removeEventListener('touchstart', enableSpeech);
                    document.removeEventListener('keydown', enableSpeech);
                };
            }, []);
            
            // Check for pending inspections and show alerts
            useEffect(() => {
                // If no active jobs, clear all alerts and stop voice
                if (activeJobs.length === 0) {
                    pendingInspectionAlertsRef.current.clear();
                    if ('speechSynthesis' in window) {
                        window.speechSynthesis.cancel();
                    }
                    return;
                }
                
                // Remove alerts for jobs that are no longer active
                const activeJobIds = new Set(activeJobs.map(j => j.id));
                const filtered = new Set();
                pendingInspectionAlertsRef.current.forEach(jobId => {
                    if (activeJobIds.has(jobId)) {
                        filtered.add(jobId);
                    }
                });
                pendingInspectionAlertsRef.current = filtered;
                
                const inspectionCheckInterval = setInterval(() => {
                    // Check again if activeJobs is empty, stop voice if so
                    if (activeJobs.length === 0) {
                        pendingInspectionAlertsRef.current.clear();
                        if ('speechSynthesis' in window) {
                            window.speechSynthesis.cancel();
                        }
                        return;
                    }
                    
                    activeJobs.forEach((job) => {
                        // Skip if inspection already completed
                        if (completedInspections.has(job.id)) {
                            // Remove from pending alerts if it was there
                            pendingInspectionAlertsRef.current.delete(job.id);
                            return;
                        }
                        
                        // Check if job is older than 30 seconds
                        const jobStartTime = new Date(job.startTime);
                        const elapsed = currentTime - jobStartTime;
                        const thirtySeconds = 30 * 1000;
                        
                        // If 30 seconds passed and inspection not completed
                        if (elapsed >= thirtySeconds && !completedInspections.has(job.id)) {
                            // Get vehicle number (job name) - this is what shows in the DOM
                            const vehicleNumber = job.vehicleNo || job.vehicle_no || 'Unknown';
                            
                            // Function to speak the inspection pending message
                            const speakMessage = () => {
                                if (!('speechSynthesis' in window)) {
                                    return;
                                }
                                
                                try {
                                    // Cancel any ongoing speech
                                    window.speechSynthesis.cancel();
                                    
                                    // Get voices
                                    let voices = window.speechSynthesis.getVoices();
                                    
                                    // If no voices, wait a bit for them to load
                                    if (voices.length === 0) {
                                    setTimeout(() => {
                                            voices = window.speechSynthesis.getVoices();
                                            attemptSpeak(voices);
                                        }, 200);
                                    } else {
                                        attemptSpeak(voices);
                                    }
                                } catch (error) {
                                    console.error('Error in speakMessage:', error);
                                }
                            };
                            
                            const attemptSpeak = (voices) => {
                                try {
                                    const textToSpeak = `${vehicleNumber} inspection pending`;
                                    const utterance = new SpeechSynthesisUtterance(textToSpeak);
                                        utterance.lang = 'en-US';
                                    utterance.rate = 0.9;
                                        utterance.pitch = 1;
                                        utterance.volume = 1;
                                        
                                    // Try to select English voice
                                    if (voices && voices.length > 0) {
                                        const englishVoice = voices.find(v => v.lang.toLowerCase().startsWith('en')) || voices[0];
                                        if (englishVoice) {
                                            utterance.voice = englishVoice;
                                        }
                                    }
                                    
                                    // Event handlers
                                    utterance.onstart = () => {
                                        console.log('🔊 VOICE STARTED:', textToSpeak);
                                    };
                                    
                                    utterance.onend = () => {
                                        console.log('✅ VOICE COMPLETED');
                                    };
                                    
                                        utterance.onerror = (e) => {
                                        // Log all errors for debugging
                                        console.error('VOICE ERROR:', e.error, 'for', vehicleNumber);
                                    };
                                    
                                    // Speak it
                                    window.speechSynthesis.speak(utterance);
                                    console.log('📢 SPEAKING:', textToSpeak);
                                } catch (speakErr) {
                                    console.error('Error in attemptSpeak:', speakErr);
                                }
                            };
                            
                            // Show custom notification (non-blocking)
                            setInspectionNotification({
                                jobId: job.id,
                                vehicleNumber: vehicleNumber,
                                message: `${vehicleNumber} - Inspection Pending`
                            });
                            
                            // Try to speak automatically (will work if user has already interacted)
                            if ('speechSynthesis' in window && userInteractionRef.current) {
                                try {
                                    window.speechSynthesis.cancel();
                                    const textToSpeak = `${vehicleNumber} inspection pending`;
                                    const utterance = new SpeechSynthesisUtterance(textToSpeak);
                                    utterance.lang = 'en-US';
                                    utterance.rate = 0.9;
                                    utterance.pitch = 1;
                                    utterance.volume = 1;
                                    
                                    let voices = window.speechSynthesis.getVoices();
                                    if (voices.length > 0) {
                                        const englishVoice = voices.find(v => v.lang.toLowerCase().startsWith('en')) || voices[0];
                                        if (englishVoice) utterance.voice = englishVoice;
                                    }
                                    
                                    utterance.onstart = () => console.log('🔊 VOICE STARTED');
                                    utterance.onend = () => console.log('✅ VOICE COMPLETED');
                                    utterance.onerror = (e) => {
                                            if (e.error !== 'interrupted' && e.error !== 'canceled') {
                                            console.error('❌ VOICE ERROR:', e.error);
                                            }
                                        };
                                        
                                        window.speechSynthesis.speak(utterance);
                                    console.log('📢 SPEAKING:', textToSpeak);
                            } catch (error) {
                                    console.error('Error speaking:', error);
                                }
                            }
                        }
                    });
                }, 5000); // Check every 5 seconds for faster response
                
                return () => {
                    clearInterval(inspectionCheckInterval);
                    // Stop voice when component unmounts or effect re-runs
                    if ('speechSynthesis' in window) {
                        window.speechSynthesis.cancel();
                    }
                };
            }, [activeJobs, currentTime, completedInspections]);
            
            // Remove alert and stop speech when inspection is completed
            useEffect(() => {
                // When inspection is completed, stop any ongoing speech immediately
                if (completedInspections.size > 0 && 'speechSynthesis' in window) {
                    window.speechSynthesis.cancel();
                }
            }, [completedInspections]);
            
            // Active jobs stored in database, no need to save to localStorage
            
            // Completed jobs stored in database, no need to save to localStorage
            
            // Refresh completed jobs when completed jobs modal opens - fetch from API first
            useEffect(() => {
                if (showCompletedJobsModal) {
                    // First, try to fetch from API (this will get ALL completed jobs, not just today's)
                    fetch(API_ROUTES.jobs.completed)
                        .then(res => res.json())
                        .then(data => {
                            if (data.success && data.jobs && Array.isArray(data.jobs)) {
                                // Update completed jobs from API
                                setCompletedJobs(data.jobs);
                            } else {
                                // If API fails, set empty array
                                setCompletedJobs([]);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching completed jobs from API:', error);
                            // Set empty array if API fails
                            setCompletedJobs([]);
                        });
                }
            }, [showCompletedJobsModal]);
            
            // Filter completed jobs based on date range and worker filter
            useEffect(() => {
                if (completedJobs && Array.isArray(completedJobs)) {
                    const startDate = new Date(dateRangeStart);
                    startDate.setHours(0, 0, 0, 0);
                    const endDate = new Date(dateRangeEnd);
                    endDate.setHours(23, 59, 59, 999);
                    
                    const filtered = completedJobs.filter(job => {
                        if (!job.endTime) return false;
                        const jobDate = new Date(job.endTime);
                        const dateMatch = jobDate >= startDate && jobDate <= endDate;
                        
                        // Filter by worker if selected
                        if (selectedWorkerFilter) {
                            const jobWorkerName = (job.workerName || job.worker_name || job.worker || '').trim();
                            const selectedWorkerName = selectedWorkerFilter.trim();
                            return dateMatch && jobWorkerName === selectedWorkerName;
                        }
                        
                        return dateMatch;
                    });
                    
                    setFilteredCompletedJobs(filtered);
                } else {
                    setFilteredCompletedJobs([]);
                }
            }, [completedJobs, dateRangeStart, dateRangeEnd, selectedWorkerFilter]);
            
            // Update staff list when All Staff modal opens - fetch from API
            useEffect(() => {
                if (showAllStaffModal) {
                    setTimeout(async () => {
                        const staffListContainer = document.getElementById('staffListContainer');
                        if (staffListContainer) {
                            try {
                                const response = await fetch(API_ROUTES.workers.index);
                                const data = await response.json();
                                const workers = (data.success && data.workers) ? data.workers : [];
                                
                                if (workers.length === 0) {
                                    staffListContainer.innerHTML = '<div class="text-center py-8 text-slate-400"><p>No staff members found</p></div>';
                                    return;
                                }
                                
                                staffListContainer.innerHTML = workers.map(worker => {
                                    const workerId = worker.id || '';
                                    return `
                                    <div 
                                        class="flex items-center gap-4 p-4 rounded-2xl border-2 border-slate-100 hover:border-purple-200 hover:bg-slate-50 transition-colors cursor-pointer select-none"
                                        data-worker-id="${workerId}"
                                    >
                                        <div class="w-16 h-16 rounded-2xl bg-purple-600 flex items-center justify-center text-white font-black text-lg shadow-lg flex-shrink-0">
                                            ${worker.name ? worker.name.charAt(0) : 'S'}
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-base font-black text-slate-900 uppercase">${worker.name || 'N/A'}</p>
                                            <p class="text-sm text-slate-500 font-mono mt-1">${worker.mobile || 'No mobile'}</p>
                                            <p class="text-xs text-slate-400 mt-1">Commission: ${worker.commission || 0}%</p>
                                        </div>
                                        <p class="text-xs text-slate-400">Hold to Edit</p>
                                    </div>
                                `;
                                }).join('');
                                
                                // Attach event listeners after rendering
                                workers.forEach(worker => {
                                const workerId = worker.id || '';
                                const workerElement = staffListContainer.querySelector(`[data-worker-id="${workerId}"]`);
                                if (workerElement) {
                                    let pressTimer = null;
                                    
                                    const handlePressStart = (e) => {
                                        e.preventDefault();
                                        pressTimer = setTimeout(() => {
                                            // Long press detected - open edit modal
                                            setShowAllStaffModal(false);
                                            setTimeout(() => {
                                                const workerModal = document.getElementById('workerModalOverlay');
                                                if (workerModal) {
                                                    const workerNameInput = document.getElementById('workerName');
                                                    const workerMobileInput = document.getElementById('workerMobile');
                                                    const workerFatherNameInput = document.getElementById('workerFatherName');
                                                    const workerFatherMobileInput = document.getElementById('workerFatherMobile');
                                                    const workerLocationInput = document.getElementById('workerLocation');
                                                    const workerCommissionInput = document.getElementById('workerCommission');
                                                    
                                                    if (workerNameInput) workerNameInput.value = worker.name || '';
                                                    if (workerMobileInput) workerMobileInput.value = worker.mobile || '';
                                                    if (workerFatherNameInput) workerFatherNameInput.value = worker.fatherName || '';
                                                    if (workerFatherMobileInput) workerFatherMobileInput.value = worker.fatherMobile || '';
                                                    if (workerLocationInput) workerLocationInput.value = worker.location || '';
                                                    if (workerCommissionInput) workerCommissionInput.value = worker.commission || 0;
                                                    
                                                    // Update button text to UPDATE when editing
                                                    setTimeout(() => {
                                                        const workerSubmitBtn = document.getElementById('workerSubmitBtn');
                                                        if (workerSubmitBtn) {
                                                            workerSubmitBtn.textContent = 'UPDATE';
                                                        }
                                                    }, 100);
                                                    
                                                    // Load images if they exist
                                                    const workerIdCardFrontPreview = document.getElementById('workerIdCardFrontPreview');
                                                    const workerIdCardBackPreview = document.getElementById('workerIdCardBackPreview');
                                                    const workerFatherCardFrontPreview = document.getElementById('workerFatherCardFrontPreview');
                                                    const workerFatherCardBackPreview = document.getElementById('workerFatherCardBackPreview');
                                                    const workerIdCardFrontLabel = document.getElementById('workerIdCardFrontLabel');
                                                    const workerIdCardBackLabel = document.getElementById('workerIdCardBackLabel');
                                                    const workerFatherCardFrontLabel = document.getElementById('workerFatherCardFrontLabel');
                                                    const workerFatherCardBackLabel = document.getElementById('workerFatherCardBackLabel');
                                                    
                                                    // Load ID Card Front
                                                    if (worker.idCardFront && workerIdCardFrontPreview) {
                                                        workerIdCardFrontPreview.innerHTML = `
                                                            <div style="position: relative; display: inline-block;">
                                                                <img src="${worker.idCardFront}" alt="Front ID Card" style="max-width: 100%; border-radius: 8px;">
                                                                <button type="button" class="delete-image-btn" onclick="
                                                                    const preview = document.getElementById('workerIdCardFrontPreview');
                                                                    const input = document.getElementById('workerIdCardFront');
                                                                    const label = document.getElementById('workerIdCardFrontLabel');
                                                                    if (preview) { preview.innerHTML = ''; preview.classList.remove('show'); }
                                                                    if (input) { input.value = ''; }
                                                                    if (label) { label.textContent = 'Choose Front Image'; }
                                                                    preview.setAttribute('data-deleted', 'true');
                                                                " style="position: absolute; top: 8px; right: 8px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; font-weight: 700; font-size: 16px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2);" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">×</button>
                                                            </div>
                                                        `;
                                                        workerIdCardFrontPreview.classList.add('show');
                                                        if (workerIdCardFrontLabel) workerIdCardFrontLabel.textContent = 'ID Card Front (Loaded)';
                                                    }
                                                    
                                                    // Load ID Card Back
                                                    if (worker.idCardBack && workerIdCardBackPreview) {
                                                        workerIdCardBackPreview.innerHTML = `
                                                            <div style="position: relative; display: inline-block;">
                                                                <img src="${worker.idCardBack}" alt="Back ID Card" style="max-width: 100%; border-radius: 8px;">
                                                                <button type="button" class="delete-image-btn" onclick="
                                                                    const preview = document.getElementById('workerIdCardBackPreview');
                                                                    const input = document.getElementById('workerIdCardBack');
                                                                    const label = document.getElementById('workerIdCardBackLabel');
                                                                    if (preview) { preview.innerHTML = ''; preview.classList.remove('show'); }
                                                                    if (input) { input.value = ''; }
                                                                    if (label) { label.textContent = 'Choose Back Image'; }
                                                                    preview.setAttribute('data-deleted', 'true');
                                                                " style="position: absolute; top: 8px; right: 8px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; font-weight: 700; font-size: 16px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2);" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">×</button>
                                                            </div>
                                                        `;
                                                        workerIdCardBackPreview.classList.add('show');
                                                        if (workerIdCardBackLabel) workerIdCardBackLabel.textContent = 'ID Card Back (Loaded)';
                                                    }
                                                    
                                                    // Load Father Card Front
                                                    if (worker.fatherCardFront && workerFatherCardFrontPreview) {
                                                        workerFatherCardFrontPreview.innerHTML = `
                                                            <div style="position: relative; display: inline-block;">
                                                                <img src="${worker.fatherCardFront}" alt="Father Card Front" style="max-width: 100%; border-radius: 8px;">
                                                                <button type="button" class="delete-image-btn" onclick="
                                                                    const preview = document.getElementById('workerFatherCardFrontPreview');
                                                                    const input = document.getElementById('workerFatherCardFront');
                                                                    const label = document.getElementById('workerFatherCardFrontLabel');
                                                                    if (preview) { preview.innerHTML = ''; preview.classList.remove('show'); }
                                                                    if (input) { input.value = ''; }
                                                                    if (label) { label.textContent = 'Choose Front Image'; }
                                                                    preview.setAttribute('data-deleted', 'true');
                                                                " style="position: absolute; top: 8px; right: 8px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; font-weight: 700; font-size: 16px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2);" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">×</button>
                                                            </div>
                                                        `;
                                                        workerFatherCardFrontPreview.classList.add('show');
                                                        if (workerFatherCardFrontLabel) workerFatherCardFrontLabel.textContent = 'Father Card Front (Loaded)';
                                                    }
                                                    
                                                    // Load Father Card Back
                                                    if (worker.fatherCardBack && workerFatherCardBackPreview) {
                                                        workerFatherCardBackPreview.innerHTML = `
                                                            <div style="position: relative; display: inline-block;">
                                                                <img src="${worker.fatherCardBack}" alt="Father Card Back" style="max-width: 100%; border-radius: 8px;">
                                                                <button type="button" class="delete-image-btn" onclick="
                                                                    const preview = document.getElementById('workerFatherCardBackPreview');
                                                                    const input = document.getElementById('workerFatherCardBack');
                                                                    const label = document.getElementById('workerFatherCardBackLabel');
                                                                    if (preview) { preview.innerHTML = ''; preview.classList.remove('show'); }
                                                                    if (input) { input.value = ''; }
                                                                    if (label) { label.textContent = 'Choose Back Image'; }
                                                                    preview.setAttribute('data-deleted', 'true');
                                                                " style="position: absolute; top: 8px; right: 8px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; font-weight: 700; font-size: 16px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2);" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">×</button>
                                                            </div>
                                                        `;
                                                        workerFatherCardBackPreview.classList.add('show');
                                                        if (workerFatherCardBackLabel) workerFatherCardBackLabel.textContent = 'Father Card Back (Loaded)';
                                                    }
                                                    
                                                    // Load additional worker mobile numbers (with names)
                                                    const workerMobileNumbersContainer = document.getElementById('workerMobileNumbersContainer');
                                                    if (workerMobileNumbersContainer) {
                                                        workerMobileNumbersContainer.innerHTML = '';
                                                        if (worker.additionalMobiles && worker.additionalMobiles.length > 0) {
                                                            worker.additionalMobiles.forEach((item, index) => {
                                                                window.workerMobileCounter = index + 1;
                                                                const mobileId = 'workerMobile_' + window.workerMobileCounter;
                                                                const mobileValue = typeof item === 'string' ? item : (item.mobile || '');
                                                                const nameValue = typeof item === 'object' ? (item.name || '') : '';
                                                                const mobileDiv = document.createElement('div');
                                                                mobileDiv.id = mobileId;
                                                                mobileDiv.className = 'additional-mobile-item';
                                                                mobileDiv.style.cssText = 'display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; background: #f8fafc;';
                                                                mobileDiv.innerHTML = `
                                                                    <div style="display: flex; gap: 8px; align-items: center;">
                                                                        <input 
                                                                            type="text" 
                                                                            class="category-form-input" 
                                                                            placeholder="Contact Name"
                                                                            style="flex: 1; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; text-transform: uppercase;"
                                                                            id="${mobileId}_name"
                                                                            value="${nameValue}"
                                                                        />
                                                                        <button 
                                                                            type="button" 
                                                                            class="remove-mobile-btn"
                                                                            style="padding: 10px 16px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 14px; transition: background 0.2s;"
                                                                            onmouseover="this.style.background='#dc2626'"
                                                                            onmouseout="this.style.background='#ef4444'"
                                                                            onclick="this.parentElement.parentElement.remove()"
                                                                        >
                                                                            ×
                                                                        </button>
                                                                    </div>
                                                                    <input 
                                                                        type="tel" 
                                                                        class="category-form-input" 
                                                                        placeholder="e.g. 0300-1234567"
                                                                        style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;"
                                                                        id="${mobileId}_mobile"
                                                                        value="${mobileValue}"
                                                                    />
                                                                `;
                                                                workerMobileNumbersContainer.appendChild(mobileDiv);
                                                            });
                                                            workerMobileNumbersContainer.style.display = 'block';
                                                        } else {
                                                            workerMobileNumbersContainer.style.display = 'none';
                                                        }
                                                    }
                                                    
                                                    // Load images if they exist
                                                    const workerIdCardFrontPreview2 = document.getElementById('workerIdCardFrontPreview');
                                                    const workerIdCardBackPreview2 = document.getElementById('workerIdCardBackPreview');
                                                    const workerFatherCardFrontPreview2 = document.getElementById('workerFatherCardFrontPreview');
                                                    const workerFatherCardBackPreview2 = document.getElementById('workerFatherCardBackPreview');
                                                    const workerIdCardFrontLabel2 = document.getElementById('workerIdCardFrontLabel');
                                                    const workerIdCardBackLabel2 = document.getElementById('workerIdCardBackLabel');
                                                    const workerFatherCardFrontLabel2 = document.getElementById('workerFatherCardFrontLabel');
                                                    const workerFatherCardBackLabel2 = document.getElementById('workerFatherCardBackLabel');
                                                    
                                                    // Load ID Card Front
                                                    if (worker.idCardFront && workerIdCardFrontPreview2) {
                                                        workerIdCardFrontPreview2.innerHTML = `
                                                            <div style="position: relative; display: inline-block;">
                                                                <img src="${worker.idCardFront}" alt="Front ID Card" style="max-width: 100%; border-radius: 8px;">
                                                                <button type="button" class="delete-image-btn" onclick="
                                                                    const preview = document.getElementById('workerIdCardFrontPreview');
                                                                    const input = document.getElementById('workerIdCardFront');
                                                                    const label = document.getElementById('workerIdCardFrontLabel');
                                                                    if (preview) { preview.innerHTML = ''; preview.classList.remove('show'); }
                                                                    if (input) { input.value = ''; }
                                                                    if (label) { label.textContent = 'Choose Front Image'; }
                                                                    preview.setAttribute('data-deleted', 'true');
                                                                " style="position: absolute; top: 8px; right: 8px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; font-weight: 700; font-size: 16px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2);" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">×</button>
                                                            </div>
                                                        `;
                                                        workerIdCardFrontPreview2.classList.add('show');
                                                        if (workerIdCardFrontLabel2) workerIdCardFrontLabel2.textContent = 'ID Card Front (Loaded)';
                                                    }
                                                    
                                                    // Load ID Card Back
                                                    if (worker.idCardBack && workerIdCardBackPreview2) {
                                                        workerIdCardBackPreview2.innerHTML = `
                                                            <div style="position: relative; display: inline-block;">
                                                                <img src="${worker.idCardBack}" alt="Back ID Card" style="max-width: 100%; border-radius: 8px;">
                                                                <button type="button" class="delete-image-btn" onclick="
                                                                    const preview = document.getElementById('workerIdCardBackPreview');
                                                                    const input = document.getElementById('workerIdCardBack');
                                                                    const label = document.getElementById('workerIdCardBackLabel');
                                                                    if (preview) { preview.innerHTML = ''; preview.classList.remove('show'); }
                                                                    if (input) { input.value = ''; }
                                                                    if (label) { label.textContent = 'Choose Back Image'; }
                                                                    preview.setAttribute('data-deleted', 'true');
                                                                " style="position: absolute; top: 8px; right: 8px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; font-weight: 700; font-size: 16px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2);" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">×</button>
                                                            </div>
                                                        `;
                                                        workerIdCardBackPreview2.classList.add('show');
                                                        if (workerIdCardBackLabel2) workerIdCardBackLabel2.textContent = 'ID Card Back (Loaded)';
                                                    }
                                                    
                                                    // Load Father Card Front
                                                    if (worker.fatherCardFront && workerFatherCardFrontPreview2) {
                                                        workerFatherCardFrontPreview2.innerHTML = `
                                                            <div style="position: relative; display: inline-block;">
                                                                <img src="${worker.fatherCardFront}" alt="Father Card Front" style="max-width: 100%; border-radius: 8px;">
                                                                <button type="button" class="delete-image-btn" onclick="
                                                                    const preview = document.getElementById('workerFatherCardFrontPreview');
                                                                    const input = document.getElementById('workerFatherCardFront');
                                                                    const label = document.getElementById('workerFatherCardFrontLabel');
                                                                    if (preview) { preview.innerHTML = ''; preview.classList.remove('show'); }
                                                                    if (input) { input.value = ''; }
                                                                    if (label) { label.textContent = 'Choose Front Image'; }
                                                                    preview.setAttribute('data-deleted', 'true');
                                                                " style="position: absolute; top: 8px; right: 8px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; font-weight: 700; font-size: 16px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2);" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">×</button>
                                                            </div>
                                                        `;
                                                        workerFatherCardFrontPreview2.classList.add('show');
                                                        if (workerFatherCardFrontLabel2) workerFatherCardFrontLabel2.textContent = 'Father Card Front (Loaded)';
                                                    }
                                                    
                                                    // Load Father Card Back
                                                    if (worker.fatherCardBack && workerFatherCardBackPreview2) {
                                                        workerFatherCardBackPreview2.innerHTML = `
                                                            <div style="position: relative; display: inline-block;">
                                                                <img src="${worker.fatherCardBack}" alt="Father Card Back" style="max-width: 100%; border-radius: 8px;">
                                                                <button type="button" class="delete-image-btn" onclick="
                                                                    const preview = document.getElementById('workerFatherCardBackPreview');
                                                                    const input = document.getElementById('workerFatherCardBack');
                                                                    const label = document.getElementById('workerFatherCardBackLabel');
                                                                    if (preview) { preview.innerHTML = ''; preview.classList.remove('show'); }
                                                                    if (input) { input.value = ''; }
                                                                    if (label) { label.textContent = 'Choose Back Image'; }
                                                                    preview.setAttribute('data-deleted', 'true');
                                                                " style="position: absolute; top: 8px; right: 8px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; font-weight: 700; font-size: 16px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2);" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">×</button>
                                                            </div>
                                                        `;
                                                        workerFatherCardBackPreview2.classList.add('show');
                                                        if (workerFatherCardBackLabel2) workerFatherCardBackLabel2.textContent = 'Father Card Back (Loaded)';
                                                    }
                                                        
                                                        // Load additional father mobile numbers (with names)
                                                        const workerFatherMobileNumbersContainer = document.getElementById('workerFatherMobileNumbersContainer');
                                                        if (workerFatherMobileNumbersContainer) {
                                                            workerFatherMobileNumbersContainer.innerHTML = '';
                                                            if (worker.fatherAdditionalMobiles && worker.fatherAdditionalMobiles.length > 0) {
                                                            worker.fatherAdditionalMobiles.forEach((item, index) => {
                                                                window.fatherMobileCounter = index + 1;
                                                                const mobileId = 'fatherMobile_' + window.fatherMobileCounter;
                                                                const mobileValue = typeof item === 'string' ? item : (item.mobile || '');
                                                                const nameValue = typeof item === 'object' ? (item.name || '') : '';
                                                                const mobileDiv = document.createElement('div');
                                                                mobileDiv.id = mobileId;
                                                                mobileDiv.className = 'additional-mobile-item';
                                                                mobileDiv.style.cssText = 'display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; background: #f8fafc;';
                                                                mobileDiv.innerHTML = `
                                                                    <div style="display: flex; gap: 8px; align-items: center;">
                                                                        <input 
                                                                            type="text" 
                                                                            class="category-form-input" 
                                                                            placeholder="Contact Name"
                                                                            style="flex: 1; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; text-transform: uppercase;"
                                                                            id="${mobileId}_name"
                                                                            value="${nameValue}"
                                                                        />
                                                                        <button 
                                                                            type="button" 
                                                                            class="remove-mobile-btn"
                                                                            style="padding: 10px 16px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 14px; transition: background 0.2s;"
                                                                            onmouseover="this.style.background='#dc2626'"
                                                                            onmouseout="this.style.background='#ef4444'"
                                                                            onclick="this.parentElement.parentElement.remove()"
                                                                        >
                                                                            ×
                                                                        </button>
                                                                    </div>
                                                                    <input 
                                                                        type="tel" 
                                                                        class="category-form-input" 
                                                                        placeholder="e.g. 0300-1234567"
                                                                        style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;"
                                                                        id="${mobileId}_mobile"
                                                                        value="${mobileValue}"
                                                                    />
                                                                `;
                                                                workerFatherMobileNumbersContainer.appendChild(mobileDiv);
                                                            });
                                                            workerFatherMobileNumbersContainer.style.display = 'block';
                                                        } else {
                                                            workerFatherMobileNumbersContainer.style.display = 'none';
                                                        }
                                                    }
                                                    
                                                    // Update button text to UPDATE when editing
                                                    setTimeout(() => {
                                                        const workerSubmitBtn = document.getElementById('workerSubmitBtn');
                                                        if (workerSubmitBtn) {
                                                            workerSubmitBtn.textContent = 'UPDATE';
                                                        }
                                                    }, 100);
                                                    
                                                    workerModal.setAttribute('data-editing-worker-id', worker.id || '');
                                                    workerModal.style.display = 'block'; // Ensure it's visible
                                                    workerModal.classList.add('show');
                                                    document.body.style.overflow = 'hidden';
                                                }
                                            }, 200);
                                        }, 500);
                                    };
                                    
                                    const handlePressEnd = () => {
                                        if (pressTimer) {
                                            clearTimeout(pressTimer);
                                            pressTimer = null;
                                        }
                                    };
                                    
                                    workerElement.addEventListener('mousedown', handlePressStart);
                                    workerElement.addEventListener('mouseup', handlePressEnd);
                                    workerElement.addEventListener('mouseleave', handlePressEnd);
                                    workerElement.addEventListener('touchstart', handlePressStart);
                                    workerElement.addEventListener('touchend', handlePressEnd);
                                    }
                                });
                            } catch (error) {
                                console.error('Error loading workers from API:', error);
                                if (staffListContainer) {
                                    staffListContainer.innerHTML = '<div class="text-center py-8 text-slate-400"><p>Error loading staff</p></div>';
                                }
                            }
                        }
                    }, 100);
                }
            }, [showAllStaffModal]);
            
            // Listen for staff updates - fetch from API
            useEffect(() => {
                const handleStaffUpdate = async () => {
                    if (showAllStaffModal) {
                        setTimeout(async () => {
                            const staffListContainer = document.getElementById('staffListContainer');
                            if (staffListContainer) {
                                try {
                                    const response = await fetch(API_ROUTES.workers.index);
                                    const data = await response.json();
                                    const workers = (data.success && data.workers) ? data.workers : [];
                                    
                                    if (workers.length === 0) {
                                        staffListContainer.innerHTML = '<div class="text-center py-8 text-slate-400"><p>No staff members found</p></div>';
                                        return;
                                    }
                                    
                                    staffListContainer.innerHTML = workers.map(worker => {
                                    const workerId = worker.id || '';
                                    return `
                                        <div 
                                            class="flex items-center gap-4 p-4 rounded-2xl border-2 border-slate-100 hover:border-purple-200 hover:bg-slate-50 transition-colors cursor-pointer select-none"
                                            data-worker-id="${workerId}"
                                        >
                                            <div class="w-16 h-16 rounded-2xl bg-purple-600 flex items-center justify-center text-white font-black text-lg shadow-lg flex-shrink-0">
                                                ${worker.name ? worker.name.charAt(0) : 'S'}
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-base font-black text-slate-900 uppercase">${worker.name || 'N/A'}</p>
                                                <p class="text-sm text-slate-500 font-mono mt-1">${worker.mobile || 'No mobile'}</p>
                                                <p class="text-xs text-slate-400 mt-1">Commission: ${worker.commission || 0}%</p>
                                            </div>
                                            <p class="text-xs text-slate-400">Hold to Edit</p>
                                        </div>
                                    `;
                                    }).join('');
                                    
                                    // Re-attach event listeners
                                    workers.forEach(worker => {
                                    const workerId = worker.id || '';
                                    const workerElement = staffListContainer.querySelector(`[data-worker-id="${workerId}"]`);
                                    if (workerElement) {
                                        let pressTimer = null;
                                        
                                        const handlePressStart = (e) => {
                                            e.preventDefault();
                                            pressTimer = setTimeout(() => {
                                                setShowAllStaffModal(false);
                                                setTimeout(() => {
                                                    const workerModal = document.getElementById('workerModalOverlay');
                                                    if (workerModal) {
                                                        const workerNameInput = document.getElementById('workerName');
                                                        const workerMobileInput = document.getElementById('workerMobile');
                                                        const workerFatherNameInput = document.getElementById('workerFatherName');
                                                        const workerFatherMobileInput = document.getElementById('workerFatherMobile');
                                                        const workerLocationInput = document.getElementById('workerLocation');
                                                        const workerCommissionInput = document.getElementById('workerCommission');
                                                        
                                                        if (workerNameInput) workerNameInput.value = worker.name || '';
                                                        if (workerMobileInput) workerMobileInput.value = worker.mobile || '';
                                                        if (workerFatherNameInput) workerFatherNameInput.value = worker.fatherName || '';
                                                        if (workerFatherMobileInput) workerFatherMobileInput.value = worker.fatherMobile || '';
                                                        if (workerLocationInput) workerLocationInput.value = worker.location || '';
                                                        if (workerCommissionInput) workerCommissionInput.value = worker.commission || 0;
                                                        
                                                    // Load additional worker mobile numbers (with names)
                                                    const workerMobileNumbersContainer = document.getElementById('workerMobileNumbersContainer');
                                                    if (workerMobileNumbersContainer && worker.additionalMobiles && worker.additionalMobiles.length > 0) {
                                                        workerMobileNumbersContainer.innerHTML = '';
                                                        worker.additionalMobiles.forEach((item, index) => {
                                                            window.workerMobileCounter = index + 1;
                                                            const mobileId = 'workerMobile_' + window.workerMobileCounter;
                                                            const mobileValue = typeof item === 'string' ? item : (item.mobile || '');
                                                            const nameValue = typeof item === 'object' ? (item.name || '') : '';
                                                            const mobileDiv = document.createElement('div');
                                                            mobileDiv.id = mobileId;
                                                            mobileDiv.className = 'additional-mobile-item';
                                                            mobileDiv.style.cssText = 'display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; background: #f8fafc;';
                                                            mobileDiv.innerHTML = `
                                                                <div style="display: flex; gap: 8px; align-items: center;">
                                                                    <input 
                                                                        type="text" 
                                                                        class="category-form-input" 
                                                                        placeholder="Contact Name"
                                                                        style="flex: 1; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; text-transform: uppercase;"
                                                                        id="${mobileId}_name"
                                                                        value="${nameValue}"
                                                                    />
                                                                    <button 
                                                                        type="button" 
                                                                        class="remove-mobile-btn"
                                                                        style="padding: 10px 16px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 14px; transition: background 0.2s;"
                                                                        onmouseover="this.style.background='#dc2626'"
                                                                        onmouseout="this.style.background='#ef4444'"
                                                                        onclick="this.parentElement.parentElement.remove()"
                                                                    >
                                                                        ×
                                                                    </button>
                                                                </div>
                                                                <input 
                                                                    type="tel" 
                                                                    class="category-form-input" 
                                                                    placeholder="e.g. 0300-1234567"
                                                                    style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;"
                                                                    id="${mobileId}_mobile"
                                                                    value="${mobileValue}"
                                                                />
                                                            `;
                                                            workerMobileNumbersContainer.appendChild(mobileDiv);
                                                        });
                                                        workerMobileNumbersContainer.style.display = 'block';
                                                    }
                                                        
                                                        // Load additional father mobile numbers (with names)
                                                        const workerFatherMobileNumbersContainer = document.getElementById('workerFatherMobileNumbersContainer');
                                                        if (workerFatherMobileNumbersContainer && worker.fatherAdditionalMobiles && worker.fatherAdditionalMobiles.length > 0) {
                                                            workerFatherMobileNumbersContainer.innerHTML = '';
                                                            worker.fatherAdditionalMobiles.forEach((item, index) => {
                                                                window.fatherMobileCounter = index + 1;
                                                                const mobileId = 'fatherMobile_' + window.fatherMobileCounter;
                                                                const mobileValue = typeof item === 'string' ? item : (item.mobile || '');
                                                                const nameValue = typeof item === 'object' ? (item.name || '') : '';
                                                                const mobileDiv = document.createElement('div');
                                                                mobileDiv.id = mobileId;
                                                                mobileDiv.className = 'additional-mobile-item';
                                                                mobileDiv.style.cssText = 'display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; background: #f8fafc;';
                                                                mobileDiv.innerHTML = `
                                                                    <div style="display: flex; gap: 8px; align-items: center;">
                                                                        <input 
                                                                            type="text" 
                                                                            class="category-form-input" 
                                                                            placeholder="Contact Name"
                                                                            style="flex: 1; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; text-transform: uppercase;"
                                                                            id="${mobileId}_name"
                                                                            value="${nameValue}"
                                                                        />
                                                                        <button 
                                                                            type="button" 
                                                                            class="remove-mobile-btn"
                                                                            style="padding: 10px 16px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 14px; transition: background 0.2s;"
                                                                            onmouseover="this.style.background='#dc2626'"
                                                                            onmouseout="this.style.background='#ef4444'"
                                                                            onclick="this.parentElement.parentElement.remove()"
                                                                        >
                                                                            ×
                                                                        </button>
                                                                    </div>
                                                                    <input 
                                                                        type="tel" 
                                                                        class="category-form-input" 
                                                                        placeholder="e.g. 0300-1234567"
                                                                        style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;"
                                                                        id="${mobileId}_mobile"
                                                                        value="${mobileValue}"
                                                                    />
                                                                `;
                                                                workerFatherMobileNumbersContainer.appendChild(mobileDiv);
                                                            });
                                                            workerFatherMobileNumbersContainer.style.display = 'block';
                                                        }
                                                        
                                                        workerModal.setAttribute('data-editing-worker-id', worker.id || '');
                                                        workerModal.style.display = 'block'; // Ensure it's visible
                                                        workerModal.classList.add('show');
                                                        document.body.style.overflow = 'hidden';
                                                    }
                                                }, 200);
                                            }, 500);
                                        };
                                        
                                        const handlePressEnd = () => {
                                            if (pressTimer) {
                                                clearTimeout(pressTimer);
                                                pressTimer = null;
                                            }
                                        };
                                        
                                        workerElement.addEventListener('mousedown', handlePressStart);
                                        workerElement.addEventListener('mouseup', handlePressEnd);
                                        workerElement.addEventListener('mouseleave', handlePressEnd);
                                        workerElement.addEventListener('touchstart', handlePressStart);
                                        workerElement.addEventListener('touchend', handlePressEnd);
                                    }
                                    });
                                } catch (error) {
                                console.error('Error loading workers from API:', error);
                                if (staffListContainer) {
                                    staffListContainer.innerHTML = '<div class="text-center py-8 text-slate-400"><p>Error loading staff</p></div>';
                                }
                            }
                        }
                        }, 100);
                    }
                };
                
                window.addEventListener('workersUpdated', handleStaffUpdate);
                window.addEventListener('refreshStaffList', handleStaffUpdate);
                
                return () => {
                    window.removeEventListener('workersUpdated', handleStaffUpdate);
                    window.removeEventListener('refreshStaffList', handleStaffUpdate);
                };
            }, [showAllStaffModal]);
            
            return (
                <div className="min-h-screen bg-slate-50 font-sans pb-28 text-slate-900 overflow-x-hidden" role="application" aria-label="Elite Car Wash Service Station">
                    {/* Skip to main content link for accessibility */}
                    <a href="#main-content" className="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:bg-blue-600 focus:text-white focus:rounded-lg focus:shadow-lg">
                        Skip to main content
                    </a>
                    
                    {/* Header */}
                    <header className="bg-slate-950 text-white p-3 sm:p-4 md:p-6 rounded-b-2xl sm:rounded-b-[30px] md:rounded-b-[45px] shadow-2xl relative z-50" role="banner">
                        <div className="flex justify-between items-center mb-4 sm:mb-6 md:mb-8 flex-wrap gap-2 sm:gap-3 md:gap-4">
                            <div className="flex items-center gap-2 sm:gap-3 md:gap-4 flex-1 min-w-0">
                                <a 
                                    href={homeRoute} 
                                    className="text-white hover:text-blue-400 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-slate-950 rounded-lg p-1.5 sm:p-2 flex-shrink-0" 
                                    title="Back to Dashboard"
                                    aria-label="Back to Dashboard"
                                >
                                    <svg className="w-5 h-5 sm:w-5.5 sm:h-5.5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                </a>
                                <div className="flex-1 min-w-0">
                                    <h1 className="text-lg sm:text-xl md:text-2xl font-black italic tracking-tighter uppercase leading-none text-blue-400 truncate">
                                        Elite Car Wash
                                    </h1>
                                    <div className="flex items-center gap-1 sm:gap-1.5 md:gap-2 mt-0.5 sm:mt-1 flex-wrap" aria-label="Branch and user information">
                                        <span className="text-[9px] sm:text-[10px] opacity-70 font-bold uppercase truncate" aria-label="Branch name">
                                            {branchName}
                                        </span>
                                        <span className="text-[9px] sm:text-[10px] opacity-50 hidden sm:inline" aria-hidden="true">•</span>
                                        <span className="text-[9px] sm:text-[10px] opacity-70 font-semibold truncate" aria-label="User name">
                                            {userName}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div className="flex items-center gap-2 sm:gap-2.5 md:gap-3 relative flex-shrink-0">
                                <button 
                                    className="text-white hover:text-blue-400 transition-colors p-1.5 sm:p-1.5 md:p-2 relative focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-slate-950 rounded-lg flex-shrink-0" 
                                    title="Settings"
                                    id="reactSettingsBtn"
                                    aria-label="Open settings menu"
                                    aria-expanded={showServicesDropdown}
                                    aria-haspopup="true"
                                    onClick={(e) => {
                                        if (e && e.preventDefault) e.preventDefault();
                                        if (e && e.stopPropagation) e.stopPropagation();
                                        setShowServicesDropdown(!showServicesDropdown);
                                    }}
                                    onKeyDown={(e) => {
                                        if (e.key === 'Escape') {
                                            setShowServicesDropdown(false);
                                        }
                                    }}
                                >
                                    <svg className="w-5 h-5 sm:w-5.5 sm:h-5.5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    
                                    {/* Settings Dropdown Menu */}
                                    {showServicesDropdown && (
                                        <div 
                                            className="absolute top-full right-0 mt-2 sm:mt-2.5 md:mt-3 w-56 sm:w-60 md:w-64 bg-white rounded-2xl sm:rounded-[25px] md:rounded-3xl shadow-2xl border-2 border-slate-100 z-50 overflow-hidden" 
                                            role="menu"
                                            aria-label="Settings menu"
                                        >
                                            {/* ALL SERVICES - Navigate to services page */}
                                            <button
                                                onClick={() => {
                                                    setShowServicesDropdown(false);
                                                    window.location.href ='{{ route("car.wash.services") }}';
                                                }}
                                                onKeyDown={(e) => {
                                                    if (e.key === 'Enter' || e.key === ' ') {
                                                        e.preventDefault();
                                                        setShowServicesDropdown(false);
                                                        window.location.href = '{{ route("car.wash.services") }}';
                                                    }
                                                }}
                                                className="w-full flex items-center gap-2 sm:gap-3 md:gap-4 px-4 sm:px-5 md:px-6 py-3.5 sm:py-4 md:py-5 hover:bg-slate-50 transition-colors border-b border-slate-100 focus:outline-none focus:bg-slate-50 focus:ring-2 focus:ring-blue-500 rounded-t-2xl sm:rounded-t-[25px] md:rounded-t-3xl"
                                                role="menuitem"
                                                aria-label="View all services"
                                            >
                                                <div className="w-10 h-10 sm:w-11 sm:h-11 md:w-12 md:h-12 rounded-xl sm:rounded-2xl bg-emerald-600 flex items-center justify-center flex-shrink-0">
                                                    <svg className="w-5 h-5 sm:w-5.5 sm:h-5.5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                                    </svg>
                                                </div>
                                                <span className="text-sm sm:text-base font-black text-slate-900 uppercase tracking-wide truncate">All Services</span>
                                            </button>
                                            
                                            {/* ALL STAFF */}
                                            <button
                                                onClick={() => {
                                                    setShowServicesDropdown(false);
                                                    window.location.href = '{{ route("car.wash.staff") }}';
                                                }}
                                                onKeyDown={(e) => {
                                                    if (e.key === 'Enter' || e.key === ' ') {
                                                        e.preventDefault();
                                                        setShowServicesDropdown(false);
                                                        window.location.href = '{{ route("car.wash.staff") }}';
                                                    }
                                                }}
                                                className="w-full flex items-center gap-2 sm:gap-3 md:gap-4 px-4 sm:px-5 md:px-6 py-3.5 sm:py-4 md:py-5 hover:bg-slate-50 transition-colors border-b border-slate-100 focus:outline-none focus:bg-slate-50 focus:ring-2 focus:ring-blue-500"
                                                role="menuitem"
                                                aria-label="View all staff members"
                                            >
                                                <div className="w-10 h-10 sm:w-11 sm:h-11 md:w-12 md:h-12 rounded-xl sm:rounded-2xl bg-purple-600 flex items-center justify-center flex-shrink-0">
                                                    <svg className="w-5 h-5 sm:w-5.5 sm:h-5.5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                </div>
                                                <span className="text-sm sm:text-base font-black text-slate-900 uppercase tracking-wide truncate">All Staff</span>
                                            </button>
                                            
                                            {/* COMPLETED JOBS */}
                                            <button
                                                onClick={() => {
                                                    setShowServicesDropdown(false);
                                                    window.location.href = '{{ route("car.wash.completed-jobs") }}';
                                                }}
                                                onKeyDown={(e) => {
                                                    if (e.key === 'Enter' || e.key === ' ') {
                                                        e.preventDefault();
                                                        setShowServicesDropdown(false);
                                                        window.location.href = '{{ route("car.wash.completed-jobs") }}';
                                                    }
                                                }}
                                                className="w-full flex items-center gap-2 sm:gap-3 md:gap-4 px-4 sm:px-5 md:px-6 py-3.5 sm:py-4 md:py-5 hover:bg-slate-50 transition-colors border-b border-slate-100 focus:outline-none focus:bg-slate-50 focus:ring-2 focus:ring-blue-500"
                                                role="menuitem"
                                                aria-label="View completed jobs"
                                            >
                                                <div className="w-10 h-10 sm:w-11 sm:h-11 md:w-12 md:h-12 rounded-xl sm:rounded-2xl bg-blue-600 flex items-center justify-center flex-shrink-0">
                                                    <svg className="w-5 h-5 sm:w-5.5 sm:h-5.5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <span className="text-sm sm:text-base font-black text-slate-900 uppercase tracking-wide truncate">Completed Jobs</span>
                                            </button>
                                            
                                            {/* DAILY REPORT */}
                                            <button
                                                onClick={() => {
                                                    setShowServicesDropdown(false);
                                                    window.location.href = '{{ route("car.wash.daily-report") }}';
                                                }}
                                                onKeyDown={(e) => {
                                                    if (e.key === 'Enter' || e.key === ' ') {
                                                        e.preventDefault();
                                                        setShowServicesDropdown(false);
                                                        window.location.href = '{{ route("car.wash.daily-report") }}';
                                                    }
                                                }}
                                                className="w-full flex items-center gap-2 sm:gap-3 md:gap-4 px-4 sm:px-5 md:px-6 py-3.5 sm:py-4 md:py-5 hover:bg-slate-50 transition-colors border-b border-slate-100 focus:outline-none focus:bg-slate-50 focus:ring-2 focus:ring-blue-500"
                                                role="menuitem"
                                                aria-label="Daily jobs report"
                                            >
                                                <div className="w-10 h-10 sm:w-11 sm:h-11 md:w-12 md:h-12 rounded-xl sm:rounded-2xl bg-indigo-600 flex items-center justify-center flex-shrink-0">
                                                    <svg className="w-5 h-5 sm:w-5.5 sm:h-5.5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </div>
                                                <span className="text-sm sm:text-base font-black text-slate-900 uppercase tracking-wide truncate">Daily Report</span>
                                            </button>
                                            
                                            {/* ADD EXPENSE */}
                                            <button
                                                onClick={() => {
                                                    setShowServicesDropdown(false);
                                                    alert('Add Expense feature coming soon!');
                                                }}
                                                onKeyDown={(e) => {
                                                    if (e.key === 'Enter' || e.key === ' ') {
                                                        e.preventDefault();
                                                        setShowServicesDropdown(false);
                                                        alert('Add Expense feature coming soon!');
                                                    }
                                                }}
                                                className="w-full flex items-center gap-2 sm:gap-3 md:gap-4 px-4 sm:px-5 md:px-6 py-3.5 sm:py-4 md:py-5 hover:bg-slate-50 transition-colors border-b border-slate-100 focus:outline-none focus:bg-slate-50 focus:ring-2 focus:ring-blue-500"
                                                role="menuitem"
                                                aria-label="Add expense (coming soon)"
                                            >
                                                <div className="w-10 h-10 sm:w-11 sm:h-11 md:w-12 md:h-12 rounded-xl sm:rounded-2xl border-2 border-red-500 flex items-center justify-center flex-shrink-0">
                                                    <svg className="w-5 h-5 sm:w-5.5 sm:h-5.5 md:w-6 md:h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                </div>
                                                <span className="text-sm sm:text-base font-black text-slate-900 uppercase tracking-wide truncate">Add Expense</span>
                                            </button>
                                        </div>
                                    )}
                                </button>
                                
                                {/* Click outside to close dropdown */}
                                {showServicesDropdown && (
                                    <div 
                                        className="fixed inset-0 z-40" 
                                        onClick={() => setShowServicesDropdown(false)}
                                    ></div>
                                )}
                            </div>
                        </div>
                        
                        <div className="grid grid-cols-4 gap-2 sm:gap-2.5 md:gap-3" role="group" aria-label="Today's statistics">
                            <button
                                type="button"
                                className="bg-white/5 p-2.5 sm:p-3 md:p-4 rounded-xl sm:rounded-2xl md:rounded-[28px] border border-white/5 text-center cursor-pointer hover:bg-white/10 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-slate-950"
                                onMouseDown={(e) => {
                                    const startTime = Date.now();
                                    const handleMouseUp = () => {
                                        const duration = Date.now() - startTime;
                                        if (duration >= 500) { // 500ms = long press
                                            setShowSaleDetailsModal(true);
                                        }
                                    };
                                    document.addEventListener('mouseup', handleMouseUp, { once: true });
                                }}
                                onTouchStart={(e) => {
                                    const startTime = Date.now();
                                    const touch = e.touches[0];
                                    const handleTouchEnd = () => {
                                        const duration = Date.now() - startTime;
                                        if (duration >= 500) { // 500ms = long press
                                            e.preventDefault();
                                            setShowSaleDetailsModal(true);
                                        }
                                    };
                                    document.addEventListener('touchend', handleTouchEnd, { once: true });
                                }}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter' || e.key === ' ') {
                                        e.preventDefault();
                                        setShowCompletedJobsModal(true);
                                    }
                                }}
                                onClick={() => setShowCompletedJobsModal(true)}
                                title="Click or long press to view completed jobs"
                                aria-label="Total revenue today. Click to view details"
                            >
                                <p className="text-[7px] sm:text-[8px] opacity-50 font-black uppercase mb-0.5 sm:mb-1">Total</p>
                                <p className="text-xs sm:text-sm font-black text-emerald-400 font-mono truncate" aria-label="Total revenue amount">
                                    Rs.{stats && typeof stats.todayRevenue !== 'undefined' ? stats.todayRevenue : 0}
                                </p>
                            </button>
                            <button
                                type="button"
                                className="bg-white/5 p-2.5 sm:p-3 md:p-4 rounded-xl sm:rounded-2xl md:rounded-[28px] border border-white/5 text-center cursor-pointer hover:bg-white/10 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-slate-950"
                                onMouseDown={(e) => {
                                    const startTime = Date.now();
                                    const handleMouseUp = () => {
                                        const duration = Date.now() - startTime;
                                        if (duration >= 500) { // 500ms = long press
                                            setShowExpenseDetailsModal(true);
                                        }
                                    };
                                    document.addEventListener('mouseup', handleMouseUp, { once: true });
                                }}
                                onTouchStart={(e) => {
                                    const startTime = Date.now();
                                    const touch = e.touches[0];
                                    const handleTouchEnd = () => {
                                        const duration = Date.now() - startTime;
                                        if (duration >= 500) { // 500ms = long press
                                            e.preventDefault();
                                            setShowExpenseDetailsModal(true);
                                        }
                                    };
                                    document.addEventListener('touchend', handleTouchEnd, { once: true });
                                }}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter' || e.key === ' ') {
                                        e.preventDefault();
                                        setShowExpenseDetailsModal(true);
                                    }
                                }}
                                onClick={() => setShowExpenseDetailsModal(true)}
                                title="Click or long press to view expense details"
                                aria-label="Total expenses today. Click to view details"
                            >
                                <p className="text-[7px] sm:text-[8px] opacity-50 font-black uppercase mb-0.5 sm:mb-1">Exp.</p>
                                <p className="text-xs sm:text-sm font-black text-rose-400 font-mono truncate" aria-label="Total expenses amount">
                                    Rs.{stats && typeof stats.todayExpensesTotal !== 'undefined' ? stats.todayExpensesTotal : 0}
                                </p>
                            </button>
                            <div className="bg-white/5 p-2.5 sm:p-3 md:p-4 rounded-xl sm:rounded-2xl md:rounded-[28px] border border-white/5 text-center" role="status" aria-label="Grand total today">
                                <p className="text-[7px] sm:text-[8px] opacity-50 font-black uppercase mb-0.5 sm:mb-1">G. Total</p>
                                <p className="text-xs sm:text-sm font-black text-blue-400 font-mono truncate" aria-label="Grand total amount">
                                    Rs.{stats && typeof stats.todayGrandTotal !== 'undefined' ? stats.todayGrandTotal : 0}
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setShowCashTransferModal(true)}
                                className="bg-white/5 p-2.5 sm:p-3 md:p-4 rounded-xl sm:rounded-2xl md:rounded-[28px] border border-white/5 text-center cursor-pointer hover:bg-white/10 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-slate-950"
                                title="Click to transfer cash"
                                aria-label="Cash on hand today"
                            >
                                <p className="text-[7px] sm:text-[8px] opacity-50 font-black uppercase mb-0.5 sm:mb-1">ON HAND</p>
                                <p className="text-xs sm:text-sm font-black text-yellow-400 font-mono truncate" aria-label="Cash on hand amount">
                                    Rs.{stats && typeof stats.cashOnHand !== 'undefined' ? stats.cashOnHand : 0}
                                </p>
                            </button>
                        </div>
                    </header>
                    
                    <main id="main-content" className="p-3 sm:p-4 md:p-5" role="main" aria-label="Main content">
                        {view === 'dashboard' && (
                            <div className="space-y-8 animate-in fade-in duration-500" role="region" aria-label="Dashboard">
                                {/* Services Modal - Full Screen Overlay */}
                                {showAllServicesModal && (
                                    <div 
                                        className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" 
                                        onClick={() => setShowAllServicesModal(false)}
                                        role="dialog"
                                        aria-modal="true"
                                        aria-labelledby="services-modal-title"
                                        aria-describedby="services-modal-description"
                                    >
                                        <div 
                                            className="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col" 
                                            onClick={(e) => e.stopPropagation()}
                                            role="document"
                                        >
                                            {/* Header */}
                                            <div className="p-6 border-b border-slate-200 bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                                                <div className="flex items-center justify-between">
                                                    <div>
                                                        <h2 id="services-modal-title" className="text-2xl font-black uppercase tracking-tighter">All Services</h2>
                                                        <p id="services-modal-description" className="text-sm opacity-90 mt-1">{modalServices.length} services available</p>
                                                    </div>
                                                    <button
                                                        onClick={() => setShowAllServicesModal(false)}
                                                        onKeyDown={(e) => {
                                                            if (e.key === 'Escape') {
                                                                setShowAllServicesModal(false);
                                                            }
                                                        }}
                                                        className="text-white hover:text-slate-200 transition-colors p-2 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-700 rounded-lg"
                                                        aria-label="Close services modal"
                                                    >
                                                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            {/* Services List */}
                                            <div className="flex-1 overflow-y-auto p-6">
                                                {modalServices.length === 0 ? (
                                                    <div className="text-center py-12">
                                                        <svg className="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                        </svg>
                                                        <p className="text-lg font-bold text-slate-400 mb-2">No services available</p>
                                                        <p className="text-sm text-slate-500 mb-6">Add your first service to get started</p>
                                                        <button
                                                            onClick={() => {
                                                                setShowServicesDropdown(false);
                                                                setTimeout(() => {
                                                                    const modal = document.getElementById('categoryModalOverlay');
                                                                    if (modal) {
                                                                        modal.classList.add('show');
                                                                        document.body.style.overflow = 'hidden';
                                                                    }
                                                                }, 200);
                                                            }}
                                                            className="px-6 py-3 bg-blue-600 text-white rounded-xl text-sm font-black uppercase hover:bg-blue-700 transition-colors shadow-lg"
                                                        >
                                                            + Add New Service
                                                        </button>
                                                    </div>
                                                ) : (
                                                    <div className="grid grid-cols-1 gap-3">
                                                        {modalServices.map((service, index) => {
                                                            const bgColor = service.colorValue || service.color || '#3b82f6';
                                                            const styleObj = { backgroundColor: bgColor };
                                                            
                                                            // Click handler for edit
                                                            const handleEditClick = () => {
                                                                setShowAllServicesModal(false);
                                                                setTimeout(() => {
                                                                    const modal = document.getElementById('categoryModalOverlay');
                                                                    if (modal) {
                                                                        // Check if service exists in categories from database
                                                                        // Use current categories state instead of localStorage
                                                                        console.log('Checking service:', service);
                                                                        console.log('Current categories:', categories);
                                                                        
                                                                        let existingCategory = null;
                                                                        if (service.id) {
                                                                            // First try to find by ID
                                                                            existingCategory = categories.find(cat => cat.id === service.id);
                                                                            console.log('Found by ID:', existingCategory);
                                                                        }
                                                                        
                                                                        // If not found by ID, try to find by label (case-insensitive)
                                                                        if (!existingCategory && service.label) {
                                                                            const serviceLabelUpper = service.label.toUpperCase().trim();
                                                                            existingCategory = categories.find(cat => {
                                                                                const catLabelUpper = (cat.label || '').toUpperCase().trim();
                                                                                return catLabelUpper === serviceLabelUpper;
                                                                            });
                                                                            console.log('Found by label (case-insensitive):', existingCategory, 'for label:', serviceLabelUpper);
                                                                        }
                                                                        
                                                                        const isEditing = !!existingCategory;
                                                                        const editingId = existingCategory ? existingCategory.id : null;
                                                                        
                                                                        console.log('Final result - isEditing:', isEditing, 'editingId:', editingId);
                                                                        
                                                                        const categoryNameInput = document.getElementById('categoryName');
                                                                        const categoryPriceInput = document.getElementById('categoryPrice');
                                                                        const selectedThemeColorInput = document.getElementById('selectedThemeColor');
                                                                        const selectedThemeClassInput = document.getElementById('selectedThemeClass');
                                                                        const previewCard = document.getElementById('previewCard');
                                                                        const previewLabel = document.getElementById('previewLabel');
                                                                        const previewPrice = previewCard ? previewCard.querySelector('.price') : null;
                                                                        
                                                                        if (categoryNameInput) categoryNameInput.value = service.label || '';
                                                                        if (categoryPriceInput) categoryPriceInput.value = service.basePrice || service.base_price || 0;
                                                                        
                                                                        // Use existing category values if editing, otherwise use service values
                                                                        const colorValue = existingCategory?.colorValue || service.colorValue;
                                                                        const colorClass = existingCategory?.color || service.color;
                                                                        const iconValue = existingCategory?.icon || service.icon || 'car';
                                                                        
                                                                        if (colorValue && selectedThemeColorInput) {
                                                                            selectedThemeColorInput.value = colorValue;
                                                                        }
                                                                        if (colorClass && selectedThemeClassInput) {
                                                                            selectedThemeClassInput.value = colorClass;
                                                                        }
                                                                        
                                                                        // Set selected icon
                                                                        const selectedIconInput = document.getElementById('selectedIcon');
                                                                        if (selectedIconInput) selectedIconInput.value = iconValue;
                                                                        
                                                                        const iconOptions = document.querySelectorAll('.icon-option');
                                                                        iconOptions.forEach(opt => {
                                                                            opt.classList.remove('selected');
                                                                            if (opt.getAttribute('data-icon') === iconValue) {
                                                                                opt.classList.add('selected');
                                                                            }
                                                                        });
                                                                        
                                                                        const themeColorOptions = document.querySelectorAll('.theme-color-option');
                                                                        themeColorOptions.forEach(opt => {
                                                                            opt.classList.remove('selected');
                                                                            const optColor = opt.getAttribute('data-color');
                                                                            if (optColor === colorValue) {
                                                                                opt.classList.add('selected');
                                                                            }
                                                                        });
                                                                        
                                                                        if (previewLabel) previewLabel.textContent = (service.label || 'SERVICE LABEL').toUpperCase();
                                                                        if (previewPrice) previewPrice.textContent = '· RS.' + (service.basePrice || service.base_price || 0);
                                                                        if (previewCard && colorValue) {
                                                                            previewCard.style.background = colorValue;
                                                                        }
                                                                        
                                                                        // Load additional prices if editing
                                                                        const additionalPricesContainer = document.getElementById('additionalPricesContainer');
                                                                        if (additionalPricesContainer) {
                                                                            // Clear existing additional prices
                                                                            additionalPricesContainer.innerHTML = '';
                                                                            
                                                                            // Load additional prices from existing category
                                                                            if (existingCategory && existingCategory.additionalPrices && existingCategory.additionalPrices.length > 0) {
                                                                                console.log('Loading additional prices:', existingCategory.additionalPrices);
                                                                                existingCategory.additionalPrices.forEach((priceItem, index) => {
                                                                                    const priceId = 'additionalPrice_' + (index + 1);
                                                                                    const priceDiv = document.createElement('div');
                                                                                    priceDiv.id = priceId;
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
                                                                                            class="remove-price-btn"
                                                                                            style="padding: 10px 16px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 14px; transition: background 0.2s;"
                                                                                            onmouseover="this.style.background='#dc2626'"
                                                                                            onmouseout="this.style.background='#ef4444'"
                                                                                            onclick="this.parentElement.remove()"
                                                                                        >
                                                                                            ×
                                                                                        </button>
                                                                                    `;
                                                                                    
                                                                                    additionalPricesContainer.appendChild(priceDiv);
                                                                                    
                                                                                    // Add auto-capitalize functionality
                                                                                    const labelInput = priceDiv.querySelector(`#${priceId}_label`);
                                                                                    if (labelInput) {
                                                                                        labelInput.addEventListener('input', function(e) {
                                                                                            const value = e.target.value;
                                                                                            if (value.length > 0) {
                                                                                                const words = value.split(' ');
                                                                                                const capitalizedWords = words.map(word => {
                                                                                                    if (word.length > 0) {
                                                                                                        return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
                                                                                                    }
                                                                                                    return word;
                                                                                                });
                                                                                                const newValue = capitalizedWords.join(' ');
                                                                                                if (newValue !== value) {
                                                                                                    const cursorPos = e.target.selectionStart;
                                                                                                    e.target.value = newValue;
                                                                                                    setTimeout(() => {
                                                                                                        e.target.setSelectionRange(cursorPos, cursorPos);
                                                                                                    }, 0);
                                                                                                }
                                                                                            }
                                                                                        });
                                                                                    }
                                                                                });
                                                                                additionalPricesContainer.style.display = 'block';
                                                                                // Update counter to avoid ID conflicts
                                                                                window.additionalPriceCounter = existingCategory.additionalPrices.length;
                                                                            } else {
                                                                                additionalPricesContainer.style.display = 'none';
                                                                                // Reset counter if no additional prices
                                                                                window.additionalPriceCounter = 0;
                                                                            }
                                                                        }
                                                                        
                                                                        modal.classList.add('show');
                                                                        document.body.style.overflow = 'hidden';
                                                                        
                                                                        // Set editing ID FIRST (before opening modal)
                                                                        if (editingId) {
                                                                            modal.setAttribute('data-editing-id', editingId);
                                                                            console.log('Set editing ID:', editingId, 'for service:', service.label);
                                                                        } else {
                                                                            modal.removeAttribute('data-editing-id');
                                                                            console.log('Removed editing ID for new service:', service.label);
                                                                        }
                                                                        
                                                                        console.log('isEditing:', isEditing, 'editingId:', editingId, 'service:', service);
                                                                        
                                                                        // Update button text with multiple attempts to ensure it sticks
                                                                        const updateButtonText = () => {
                                                                            const submitBtn = document.getElementById('categorySubmitBtn');
                                                                            if (submitBtn) {
                                                                                const currentEditingId = modal.getAttribute('data-editing-id');
                                                                                if (currentEditingId) {
                                                                                    submitBtn.textContent = 'UPDATE';
                                                                                    console.log('Button text set to UPDATE for editing ID:', currentEditingId);
                                                                                } else {
                                                                                    submitBtn.textContent = 'SAVE';
                                                                                    console.log('Button text set to SAVE (no editing ID)');
                                                                                }
                                                                            } else {
                                                                                console.warn('categorySubmitBtn not found');
                                                                            }
                                                                        };
                                                                        
                                                                        // Update immediately
                                                                        updateButtonText();
                                                                        
                                                                        // Update after short delay
                                                                        setTimeout(updateButtonText, 100);
                                                                        
                                                                        // Update after modal is fully visible
                                                                        setTimeout(updateButtonText, 300);
                                                                        
                                                                        // Update after longer delay to override any other handlers
                                                                        setTimeout(updateButtonText, 500);
                                                                    }
                                                                }, 200);
                                                            };
                                                            
                                                            return (
                                                                <div
                                                                    key={service.id || index}
                                                                    onClick={handleEditClick}
                                                                    className="flex items-center gap-4 p-4 rounded-2xl cursor-pointer hover:bg-slate-50 active:bg-blue-50 transition-colors border-2 border-slate-100 hover:border-blue-200 select-none"
                                                                >
                                                                    <div 
                                                                        className="w-16 h-16 rounded-2xl flex items-center justify-center text-white font-black text-lg shadow-lg"
                                                                        style={styleObj}
                                                                    >
                                                                        {service.label ? service.label.charAt(0) : 'S'}
                                                                    </div>
                                                                    <div className="flex-1">
                                                                        <p className="text-base font-black text-slate-900 uppercase">{service.label}</p>
                                                                        <p className="text-sm text-slate-500 font-mono mt-1">Rs. {service.basePrice || service.base_price || 0}</p>
                                                                    </div>
                                                                    <div className="flex items-center gap-2">
                                                                        <span className="text-xs text-slate-400 font-bold">Click to Edit</span>
                                                                        <svg className="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            );
                                                        })}
                                                    </div>
                                                )}
                                            </div>
                                            
                                            {/* Footer with Add New Button */}
                                            {allServices.length > 0 && (
                                                <div className="p-6 border-t border-slate-200 bg-slate-50">
                                                    <button
                                                        onClick={() => {
                                                            setShowServicesDropdown(false);
                                                            setTimeout(() => {
                                                                const modal = document.getElementById('categoryModalOverlay');
                                                                if (modal) {
                                                                    modal.classList.add('show');
                                                                    document.body.style.overflow = 'hidden';
                                                                }
                                                            }, 200);
                                                        }}
                                                        className="w-full px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-2xl text-sm font-black uppercase hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg flex items-center justify-center gap-2"
                                                    >
                                                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                                                        </svg>
                                                        Add New Service
                                                    </button>
                                                </div>
                                            )}
                                        </div>
                                        </div>
                                    )}
                                
                                {/* All Staff Modal - Full Screen Overlay */}
                                {showAllStaffModal && (
                                    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" onClick={() => setShowAllStaffModal(false)}>
                                        <div className="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col" onClick={(e) => e.stopPropagation()}>
                                            {/* Header */}
                                            <div className="p-6 border-b border-slate-200 bg-gradient-to-r from-purple-600 to-purple-700 text-white">
                                                <div className="flex items-center justify-between">
                                                    <div>
                                                        <h2 className="text-2xl font-black uppercase tracking-tighter">All Staff</h2>
                                                        <p className="text-sm opacity-90 mt-1">Manage your staff members</p>
                                                    </div>
                                                    <button
                                                        onClick={() => setShowAllStaffModal(false)}
                                                        className="text-white hover:text-slate-200 transition-colors p-2"
                                                    >
                                                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            {/* Staff List */}
                                            <div className="flex-1 overflow-y-auto p-6">
                                                <div id="staffListContainer" className="space-y-3">
                                                    {/* Staff list will be loaded here */}
                                                </div>
                                            </div>
                                            
                                            {/* Footer with Add Staff Button */}
                                            <div className="p-6 border-t border-slate-200 bg-slate-50">
                                                <button
                                                    onClick={() => {
                                                        setShowAllStaffModal(false);
                                                        setTimeout(() => {
                                                            const workerModal = document.getElementById('workerModalOverlay');
                                                            if (workerModal) {
                                                                workerModal.style.display = 'block'; // Ensure it's visible
                                                                workerModal.classList.add('show');
                                                                document.body.style.overflow = 'hidden';
                                                            }
                                                        }, 200);
                                                    }}
                                                    className="w-full px-6 py-4 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-2xl text-sm font-black uppercase hover:from-purple-700 hover:to-purple-800 transition-all shadow-lg flex items-center justify-center gap-2"
                                                >
                                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                                                    </svg>
                                                    Add Staff
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                )}
                                
                                {/* Complete Job Comments Modal */}
                                {completeModalJobId && (() => {
                                    const job = activeJobs.find(j => j.id === completeModalJobId);
                                    if (!job) return null;
                                    
                                    // Get expense items for this job (from expenseItems state or from backend)
                                    const currentJobExpenseItems = expenseItems || {};
                                    const currentJobCustomExpenses = customExpenses || [];
                                    
                                    // Define required inspection items
                                    const requiredInspectionItems = [
                                        'engine_oil', 'wiper_rubber', 'brake_oil', 'air_filter', 
                                        'radiator_water', 'shower_water', 'horn', 
                                        'head_lights', 'indicator', 'brake_pad', 'ac_filter'
                                    ];
                                    
                                    // Check if inspection was completed for this job
                                    const inspectionCompleted = completedInspections.has(job.id);
                                    
                                    // Check if all inspection items are currently rated (in progress)
                                    const allItemsCurrentlyRated = requiredInspectionItems.every(itemId => {
                                        const itemData = inspectionData[itemId];
                                        return itemData && itemData.status && itemData.status !== '';
                                    });
                                    
                                    // All items are rated if inspection was completed OR all items are currently rated
                                    const allItemsRated = inspectionCompleted || allItemsCurrentlyRated;
                                    
                                    // Function to handle modal close attempt
                                    const handleCloseAttempt = () => {
                                        if (!allItemsRated) {
                                            alert('Please complete all inspection items before closing. All inspection comments must be passed.');
                                            return false;
                                        }
                                        return true;
                                    };
                                    
                                    return (
                                        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-2 sm:p-3 md:p-4" onClick={() => {
                                            if (handleCloseAttempt()) {
                                                setCompleteModalJobId(null);
                                                setSelectedRating('');
                                                setJobComment('');
                                            }
                                        }}>
                                            <div className="bg-white rounded-xl sm:rounded-2xl md:rounded-3xl shadow-2xl w-full max-w-md max-h-[95vh] sm:max-h-[90vh] overflow-hidden flex flex-col" onClick={(e) => e.stopPropagation()}>
                                                {/* Header */}
                                                <div className="p-3 sm:p-4 md:p-6 border-b border-slate-200 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white">
                                                    <div className="flex items-center justify-between gap-2 sm:gap-4">
                                                        <div className="flex-1 min-w-0">
                                                            <h2 className="text-lg sm:text-xl md:text-2xl font-black uppercase tracking-tighter">Complete Job</h2>
                                                            <p className="text-xs sm:text-sm opacity-90 mt-0.5 sm:mt-1 hidden sm:block">Rate the service</p>
                                                        </div>
                                                        <button
                                                            onClick={() => {
                                                                if (handleCloseAttempt()) {
                                                                    setCompleteModalJobId(null);
                                                                    setSelectedRating('');
                                                                    setJobComment('');
                                                                }
                                                            }}
                                                            className="text-white hover:text-slate-200 transition-colors p-1.5 sm:p-2 flex-shrink-0"
                                                        >
                                                            <svg className="w-5 h-5 sm:w-5 sm:h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                {/* Job Info */}
                                                <div className="p-3 sm:p-4 md:p-6 border-b border-slate-200 bg-slate-50">
                                                    <div className="flex items-center gap-2 sm:gap-3 mb-1.5 sm:mb-2 flex-wrap">
                                                        <span className="text-xs sm:text-sm font-black text-slate-900 uppercase truncate">{job.vehicleNo}</span>
                                                        <span className="text-xs text-slate-500 hidden sm:inline">•</span>
                                                        <span className="text-[10px] sm:text-xs text-slate-600 truncate">{job.customerName}</span>
                                                    </div>
                                                    <div className="text-[10px] sm:text-xs text-slate-500">
                                                        Service: {job.service} • Rs.{job.price}
                                                    </div>
                                                </div>
                                                
                                                {/* Rating Section */}
                                                <div className="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6">
                                                    {/* Inspection Validation Message */}
                                                    {!allItemsRated && (
                                                        <div className="mb-3 sm:mb-4 p-3 sm:p-4 bg-red-50 border-2 border-red-200 rounded-lg sm:rounded-xl">
                                                            <div className="flex items-center gap-2 mb-1.5 sm:mb-2">
                                                                <span className="text-lg sm:text-xl flex-shrink-0">⚠️</span>
                                                                <span className="text-xs sm:text-sm font-black text-red-900 uppercase">Inspection Required</span>
                                                            </div>
                                                            <p className="text-[10px] sm:text-xs text-red-700 mb-1.5 sm:mb-2">Please complete all inspection items before completing the job. All inspection comments must be passed.</p>
                                                            {!inspectionCompleted && (
                                                                <div className="text-[9px] sm:text-xs text-red-600">
                                                                    <span className="font-bold">Missing items:</span>
                                                                    <ul className="list-disc list-inside mt-0.5 sm:mt-1 space-y-0.5">
                                                                        {requiredInspectionItems
                                                                            .filter(itemId => {
                                                                                const itemData = inspectionData[itemId];
                                                                                return !itemData || !itemData.status || itemData.status === '';
                                                                            })
                                                                            .map(itemId => {
                                                                                const itemNames = {
                                                                                    'engine_oil': 'Engine Oil',
                                                                                    'wiper_rubber': 'Wiper Rubber',
                                                                                    'brake_oil': 'Brake Oil',
                                                                                    'air_filter': 'Air Filter',
                                                                                    'radiator_water': 'Radiator Water',
                                                                                    'shower_water': 'Shower Water level',
                                                                                    'horn': 'Horn',
                                                                                    'head_lights': 'Head Lights',
                                                                                    'indicator': 'Indicator',
                                                                                    'brake_pad': 'Brake Pad',
                                                                                    'ac_filter': 'AC Filter'
                                                                                };
                                                                                return <li key={itemId}>{itemNames[itemId] || itemId}</li>;
                                                                            })
                                                                        }
                                                                    </ul>
                                                                </div>
                                                            )}
                                                            {!inspectionCompleted && (
                                                                <button
                                                                    onClick={() => {
                                                                        setInspectionModalJobId(job.id);
                                                                    }}
                                                                    className="mt-2 sm:mt-3 w-full px-3 sm:px-4 py-1.5 sm:py-2 bg-purple-500 text-white rounded-lg sm:rounded-xl text-[10px] sm:text-xs font-black uppercase hover:bg-purple-600 transition-colors"
                                                                >
                                                                    Open Inspection Modal
                                                                </button>
                                                            )}
                                                        </div>
                                                    )}
                                                    
                                                    <div className="mb-4 sm:mb-5 md:mb-6">
                                                        <label className="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-2 sm:mb-3">Service Rating</label>
                                                        <div className="grid grid-cols-2 gap-2 sm:gap-2.5 md:gap-3">
                                                            {[
                                                                { value: 'excellent', label: 'Excellent', color: 'bg-green-500', icon: '⭐' },
                                                                { value: 'good', label: 'Good', color: 'bg-blue-500', icon: '👍' },
                                                                { value: 'average', label: 'Average', color: 'bg-yellow-500', icon: '😐' },
                                                                { value: 'poor', label: 'Poor', color: 'bg-red-500', icon: '👎' }
                                                            ].map((rating) => (
                                                                <button
                                                                    key={rating.value}
                                                                    type="button"
                                                                    onClick={() => setSelectedRating(rating.value)}
                                                                    className={`p-3 sm:p-3.5 md:p-4 rounded-xl sm:rounded-2xl border-2 transition-all ${
                                                                        selectedRating === rating.value
                                                                            ? `${rating.color} text-white border-${rating.color.replace('bg-', '')} shadow-lg scale-105`
                                                                            : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300'
                                                                    }`}
                                                                >
                                                                    <div className="text-xl sm:text-2xl mb-0.5 sm:mb-1">{rating.icon}</div>
                                                                    <div className="text-[10px] sm:text-xs font-black uppercase">{rating.label}</div>
                                                                </button>
                                                            ))}
                                                        </div>
                                                    </div>
                                                    
                                                    {/* Payment Method Section */}
                                                    <div className="mb-3 sm:mb-4">
                                                        <label className="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-2 sm:mb-3">Payment Method</label>
                                                        <div className="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-2.5">
                                                            {[
                                                                { value: 'cash', label: 'Cash', icon: '💵', color: 'bg-green-500' },
                                                                { value: 'bank', label: 'Bank', icon: '🏦', color: 'bg-blue-500' },
                                                                { value: 'card', label: 'Card', icon: '💳', color: 'bg-purple-500' },
                                                                { value: 'other', label: 'Other', icon: '💰', color: 'bg-slate-500' }
                                                            ].map((method) => (
                                                                <button
                                                                    key={method.value}
                                                                    type="button"
                                                                    onClick={() => setPaymentMethod(method.value)}
                                                                    className={`p-2.5 sm:p-3 rounded-xl sm:rounded-2xl border-2 transition-all ${
                                                                        paymentMethod === method.value
                                                                            ? `${method.color} text-white border-${method.color.replace('bg-', '')} shadow-lg scale-105`
                                                                            : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300'
                                                                    }`}
                                                                >
                                                                    <div className="text-lg sm:text-xl mb-0.5 sm:mb-1">{method.icon}</div>
                                                                    <div className="text-[9px] sm:text-[10px] font-black uppercase">{method.label}</div>
                                                                </button>
                                                            ))}
                                                        </div>
                                                    </div>
                                                    
                                                    {/* Comments Section */}
                                                    <div className="mb-3 sm:mb-4">
                                                        <label className="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-1.5 sm:mb-2">Comments (Optional)</label>
                                                        <div className="relative">
                                                            <textarea
                                                                value={jobComment}
                                                                onChange={(e) => setJobComment(e.target.value)}
                                                                placeholder="Enter any comments or notes..."
                                                                className="w-full px-3 sm:px-4 py-2 sm:py-2.5 md:py-3 pr-10 sm:pr-12 text-xs sm:text-sm text-slate-900 border-2 border-slate-300 rounded-lg sm:rounded-xl bg-white focus:border-emerald-500 focus:outline-none resize-none"
                                                                rows="3"
                                                            />
                                                            <button
                                                                type="button"
                                                                onClick={() => {
                                                                    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
                                                                        alert('Speech recognition is not supported in your browser. Please use Chrome, Edge, or Safari.');
                                                                        return;
                                                                    }
                                                                    
                                                                    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                                                                    if (!SpeechRecognition) {
                                                                        alert('Speech recognition is not available.');
                                                                        return;
                                                                    }
                                                                    
                                                                    if (isRecording) {
                                                                        // Stop recording
                                                                        if (recognition) {
                                                                            recognition.stop();
                                                                            setRecognition(null);
                                                                        }
                                                                        setIsRecording(false);
                                                                    } else {
                                                                        // Start recording
                                                                        const recognitionInstance = new SpeechRecognition();
                                                                        recognitionInstance.continuous = true;
                                                                        recognitionInstance.interimResults = true;
                                                                        recognitionInstance.lang = 'en-US';
                                                                        
                                                                        let processedResults = new Set(); // Track processed result indices
                                                                        
                                                                        recognitionInstance.onstart = () => {
                                                                            setIsRecording(true);
                                                                            processedResults.clear();
                                                                        };
                                                                        
                                                                        recognitionInstance.onresult = (event) => {
                                                                            // Process all results, but only add final ones we haven't processed yet
                                                                            for (let i = 0; i < event.results.length; i++) {
                                                                                const result = event.results[i];
                                                                                const transcript = result[0].transcript.trim();
                                                                                
                                                                                // Only process final results that we haven't seen before
                                                                                if (result.isFinal && transcript && !processedResults.has(i)) {
                                                                                    processedResults.add(i);
                                                                                    setJobComment(prev => {
                                                                                        const trimmed = prev.trim();
                                                                                        return trimmed ? trimmed + ' ' + transcript : transcript;
                                                                                    });
                                                                                }
                                                                            }
                                                                        };
                                                                        
                                                                        recognitionInstance.onerror = (event) => {
                                                                            console.error('Speech recognition error:', event.error);
                                                                            if (event.error === 'no-speech') {
                                                                                alert('No speech detected. Please try again.');
                                                                            } else if (event.error === 'not-allowed') {
                                                                                alert('Microphone permission denied. Please allow microphone access.');
                                                                            }
                                                                            setIsRecording(false);
                                                                            setRecognition(null);
                                                                        };
                                                                        
                                                                        recognitionInstance.onend = () => {
                                                                            setIsRecording(false);
                                                                            setRecognition(null);
                                                                        };
                                                                        
                                                                        try {
                                                                            recognitionInstance.start();
                                                                            setRecognition(recognitionInstance);
                                                                        } catch (error) {
                                                                            console.error('Error starting speech recognition:', error);
                                                                            alert('Error starting speech recognition. Please try again.');
                                                                            setIsRecording(false);
                                                                        }
                                                                    }
                                                                }}
                                                                className={`absolute right-2 sm:right-3 top-2 sm:top-2.5 p-1.5 sm:p-2 rounded-lg sm:rounded-xl transition-all ${
                                                                    isRecording 
                                                                        ? 'bg-red-500 hover:bg-red-600 text-white animate-pulse' 
                                                                        : 'bg-emerald-500 hover:bg-emerald-600 text-white'
                                                                }`}
                                                                title={isRecording ? 'Stop recording' : 'Start voice recording'}
                                                            >
                                                                {isRecording ? (
                                                                    <svg className="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                                                                        <path d="M6 6h12v12H6z"/>
                                                                    </svg>
                                                                ) : (
                                                                    <svg className="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                                                                        <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                                                                        <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                                                                    </svg>
                                                                )}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                {/* Footer */}
                                                <div className="p-3 sm:p-4 md:p-6 border-t border-slate-200 bg-slate-50">
                                                    <button
                                                        onClick={async () => {
                                                            // Check if all inspection items are rated
                                                            if (!allItemsRated) {
                                                                alert('Please complete all inspection items before completing the job. All inspection comments must be passed.');
                                                                return;
                                                            }
                                                            
                                                            try {
                                                                // Save inspection to backend if not already saved
                                                                if (inspectionData && Object.keys(inspectionData).length > 0) {
                                                                    const inspectionResponse = await fetch(API_ROUTES.inspections.store(job.id), {
                                                                        method: 'POST',
                                                                        headers: {
                                                                            'Content-Type': 'application/json',
                                                                            'X-CSRF-TOKEN': csrfToken,
                                                                            'Accept': 'application/json'
                                                                        },
                                                                        body: JSON.stringify({
                                                                            inspection_items: inspectionData,
                                                                            is_completed: true
                                                                        })
                                                                    });
                                                                    
                                                                    const inspectionResult = await inspectionResponse.json();
                                                                    if (!inspectionResult.success) {
                                                                        console.error('Error saving inspection:', inspectionResult.message);
                                                                    }
                                                                }
                                                                
                                                                // Save expense to backend if exists
                                                                const expenseItemsArray = [];
                                                                let totalExpenseAmount = 0;
                                                                
                                                                // Get expense items from expenseItems state
                                                                Object.keys(currentJobExpenseItems).forEach(itemName => {
                                                                    const item = currentJobExpenseItems[itemName];
                                                                    if (item && item.quantity > 0 && item.price !== '' && parseFloat(item.price) > 0) {
                                                                        const priceValue = parseFloat(item.price) || 0;
                                                                        const total = item.quantity * priceValue;
                                                                        totalExpenseAmount += total;
                                                                        
                                                                        expenseItemsArray.push({
                                                                            name: itemName,
                                                                            quantity: item.quantity,
                                                                            price: priceValue,
                                                                            total: total
                                                                        });
                                                                    }
                                                                });
                                                                
                                                                // Add custom expenses
                                                                currentJobCustomExpenses.forEach(customItem => {
                                                                    const item = currentJobExpenseItems[customItem.id];
                                                                    if (item && item.quantity > 0 && item.price !== '' && parseFloat(item.price) > 0) {
                                                                        const priceValue = parseFloat(item.price) || 0;
                                                                        const total = item.quantity * priceValue;
                                                                        totalExpenseAmount += total;
                                                                        
                                                                        expenseItemsArray.push({
                                                                            name: customItem.name,
                                                                            quantity: item.quantity,
                                                                            price: priceValue,
                                                                            total: total
                                                                        });
                                                                    }
                                                                });
                                                                
                                                                if (expenseItemsArray.length > 0) {
                                                                    const expenseResponse = await fetch(API_ROUTES.expenses.store(job.id), {
                                                                        method: 'POST',
                                                                        headers: {
                                                                            'Content-Type': 'application/json',
                                                                            'X-CSRF-TOKEN': csrfToken,
                                                                            'Accept': 'application/json'
                                                                        },
                                                                        body: JSON.stringify({
                                                                            expense_items: expenseItemsArray,
                                                                            total_amount: totalExpenseAmount
                                                                        })
                                                                    });
                                                                    
                                                                    const expenseResult = await expenseResponse.json();
                                                                    if (!expenseResult.success) {
                                                                        console.error('Error saving expense:', expenseResult.message);
                                                                    }
                                                                }
                                                                
                                                                // Complete job on backend
                                                                const completeResponse = await fetch(API_ROUTES.jobs.complete(job.id), {
                                                                    method: 'POST',
                                                                    headers: {
                                                                        'Content-Type': 'application/json',
                                                                        'X-CSRF-TOKEN': csrfToken,
                                                                        'Accept': 'application/json'
                                                                    },
                                                                    body: JSON.stringify({
                                                                        rating: selectedRating,
                                                                        notes: jobComment || '',
                                                                        payment_method: paymentMethod
                                                                    })
                                                                });
                                                                
                                                                const completeResult = await completeResponse.json();
                                                                
                                                                if (!completeResult.success) {
                                                                    alert('Error completing job: ' + (completeResult.message || 'Unknown error'));
                                                                    return;
                                                                }
                                                                
                                                                // Create completed job object with updated status
                                                                const completedJob = {
                                                                    ...job,
                                                                    status: 'completed',
                                                                    endTime: new Date().toISOString(),
                                                                    rating: selectedRating,
                                                                    comment: jobComment,
                                                                    paymentMethod: paymentMethod
                                                                };
                                                                
                                                                // Complete job - remove from active jobs
                                                                setActiveJobs(prev => prev.filter(j => j.id !== job.id));
                                                                
                                                                // Add completed job to state (will be saved to database via API)
                                                                setCompletedJobs(prev => {
                                                                    // Add new job to existing list
                                                                    return [...prev, completedJob];
                                                                });
                                                                
                                                                // Reload completed jobs from API to get updated list
                                                                fetch(API_ROUTES.jobs.completed)
                                                                    .then(res => res.json())
                                                                    .then(data => {
                                                                        if (data.success && data.jobs) {
                                                                            setCompletedJobs(data.jobs);
                                                                        }
                                                                    })
                                                                    .catch(err => console.error('Error reloading completed jobs:', err));
                                                            
                                                            // Update stats
                                                            const isCashPayment = paymentMethod === 'cash';
                                                            // Refresh cash balance from database after job completion
                                                            refreshCashBalance();
                                                            
                                                            // Update stats (revenue will be recalculated from completed jobs)
                                                            setStats(prev => ({
                                                                ...prev,
                                                                todayRevenue: (prev.todayRevenue || 0) + job.price,
                                                                todayGrandTotal: (prev.todayGrandTotal || 0) + job.price,
                                                                // cashOnHand will be updated by refreshCashBalance
                                                            }));
                                                            
                                                            console.log('Job completed:', completedJob);
                                                            
                                                            setCompleteModalJobId(null);
                                                            setSelectedRating('');
                                                            setJobComment('');
                                                            setPaymentMethod('cash');
                                                        } catch (error) {
                                                            console.error('Error completing job:', error);
                                                            alert('Error completing job. Please try again.');
                                                        }
                                                    }}
                                                        className={`w-full px-4 sm:px-5 md:px-6 py-2.5 sm:py-3 md:py-4 rounded-xl sm:rounded-2xl text-xs sm:text-sm font-black uppercase transition-all shadow-lg ${
                                                            allItemsRated 
                                                                ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white hover:from-emerald-600 hover:to-emerald-700' 
                                                                : 'bg-slate-300 text-slate-500 cursor-not-allowed'
                                                        }`}
                                                        disabled={!allItemsRated}
                                                    >
                                                        <span className="hidden sm:inline">{allItemsRated ? 'Confirm & Complete' : 'Complete All Inspections First'}</span>
                                                        <span className="sm:hidden">{allItemsRated ? 'Complete' : 'Complete Inspections'}</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })(                                )}
                                
                                {/* Car Inspection Modal */}
                                {inspectionModalJobId && (() => {
                                    const job = activeJobs.find(j => j.id === inspectionModalJobId);
                                    if (!job) return null;
                                    
                                    const inspectionItems = [
                                        { id: 'engine_oil', name: 'Engine Oil', icon: '🛢️' },
                                        { id: 'wiper_rubber', name: 'Wiper Rubber', icon: '🧹' },
                                        { id: 'brake_oil', name: 'Brake Oil', icon: '🛑' },
                                        { id: 'air_filter', name: 'Air Filter', icon: '🌬️' },
                                        { id: 'radiator_water', name: 'Radiator Water', icon: '💧' },
                                        { id: 'shower_water', name: 'Shower Water level', icon: '🚿' },
                                        { id: 'horn', name: 'Horn', icon: '📢' },
                                        { id: 'head_lights', name: 'Head Lights', icon: '💡' },
                                        { id: 'indicator', name: 'Indicator', icon: '↪️' },
                                        { id: 'brake_pad', name: 'Brake Pad', icon: '🛞' },
                                        { id: 'ac_filter', name: 'AC Filter', icon: '❄️' }
                                    ];
                                    
                                    const getStatusIcon = (status) => {
                                        if (status === 'excellent') return { icon: '⭐', color: 'text-blue-600', bg: 'bg-blue-50', border: 'border-blue-200' };
                                        if (status === 'good') return { icon: '✅', color: 'text-green-600', bg: 'bg-green-50', border: 'border-green-200' };
                                        if (status === 'average') return { icon: '⚠️', color: 'text-yellow-600', bg: 'bg-yellow-50', border: 'border-yellow-200' };
                                        if (status === 'poor') return { icon: '❌', color: 'text-red-600', bg: 'bg-red-50', border: 'border-red-200' };
                                        return { icon: '⚪', color: 'text-slate-400', bg: 'bg-slate-50', border: 'border-slate-200' };
                                    };
                                    
                                    return (
                                        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-2 sm:p-3 md:p-4" onClick={() => {
                                            setInspectionModalJobId(null);
                                            setInspectionData({});
                                        }}>
                                            <div className="bg-white rounded-xl sm:rounded-2xl md:rounded-3xl shadow-2xl w-full max-w-2xl max-h-[95vh] sm:max-h-[90vh] overflow-hidden flex flex-col" onClick={(e) => e.stopPropagation()}>
                                                {/* Header */}
                                                <div className="p-3 sm:p-4 md:p-6 border-b border-slate-200 bg-gradient-to-r from-purple-500 to-purple-600 text-white">
                                                    <div className="flex items-center justify-between gap-2 sm:gap-4">
                                                        <div className="flex-1 min-w-0">
                                                            <h2 className="text-lg sm:text-xl md:text-2xl font-black uppercase tracking-tighter">Car Inspection</h2>
                                                            <p className="text-xs sm:text-sm opacity-90 mt-0.5 sm:mt-1 hidden sm:block">Rate each inspection item</p>
                                                        </div>
                                                        <button
                                                            onClick={() => {
                                                                setInspectionModalJobId(null);
                                                                setInspectionData({});
                                                            }}
                                                            className="text-white hover:text-slate-200 transition-colors p-1.5 sm:p-2 flex-shrink-0"
                                                        >
                                                            <svg className="w-5 h-5 sm:w-5 sm:h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                {/* Job Info */}
                                                <div className="p-3 sm:p-4 border-b border-slate-200 bg-slate-50">
                                                    <div className="flex items-center gap-2 sm:gap-3 flex-wrap">
                                                        <span className="text-xs sm:text-sm font-black text-slate-900 uppercase truncate">{job.vehicleNo}</span>
                                                        <span className="text-xs text-slate-500 hidden sm:inline">•</span>
                                                        <span className="text-[10px] sm:text-xs text-slate-600 truncate">{job.customerName}</span>
                                                    </div>
                                                </div>
                                                
                                                {/* Inspection Items */}
                                                <div className="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6">
                                                    <div className="space-y-3 sm:space-y-3.5 md:space-y-4">
                                                        {inspectionItems.map((item) => {
                                                            const itemData = inspectionData[item.id] || { status: '' };
                                                            const statusInfo = getStatusIcon(itemData.status);
                                                            
                                                            return (
                                                                <div key={item.id} className={`bg-white p-3 sm:p-4 md:p-5 rounded-xl sm:rounded-2xl border-2 ${statusInfo.border} transition-all hover:shadow-lg`}>
                                                                    <div className="flex items-center justify-between mb-3 sm:mb-4 gap-2">
                                                                        <div className="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                                                                            <span className="text-xl sm:text-2xl flex-shrink-0">{item.icon}</span>
                                                                            <span className="text-sm sm:text-base font-black text-slate-900 uppercase truncate">{item.name}</span>
                                                                        </div>
                                                                        <div className={`px-2 sm:px-3 py-1 rounded-lg ${statusInfo.bg} ${statusInfo.color} font-bold text-xs sm:text-sm flex-shrink-0`}>
                                                                            {statusInfo.icon}
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div>
                                                                        {(() => {
                                                                            // Calculate status counts and percentages for all items
                                                                            const statusCounts = {
                                                                                excellent: 0,
                                                                                good: 0,
                                                                                average: 0,
                                                                                poor: 0,
                                                                                total: 0
                                                                            };
                                                                            
                                                                            inspectionItems.forEach((inspectionItem) => {
                                                                                const itemData = inspectionData[inspectionItem.id];
                                                                                if (itemData && itemData.status && itemData.status !== '') {
                                                                                    statusCounts[itemData.status] = (statusCounts[itemData.status] || 0) + 1;
                                                                                    statusCounts.total++;
                                                                                }
                                                                            });
                                                                            
                                                                            const calculatePercentage = (count) => {
                                                                                if (statusCounts.total === 0) return 0;
                                                                                return Math.round((count / statusCounts.total) * 100);
                                                                            };
                                                                            
                                                                            const statusOptions = {
                                                                                'excellent': { 
                                                                                    label: 'Excellent', 
                                                                                    icon: '⭐', 
                                                                                    gradient: 'from-blue-500 via-blue-600 to-indigo-600',
                                                                                    hoverGradient: 'hover:from-blue-600 hover:via-blue-700 hover:to-indigo-700',
                                                                                    shadow: 'shadow-blue-500/50',
                                                                                    border: 'border-blue-400/60',
                                                                                    count: statusCounts.excellent,
                                                                                    percentage: calculatePercentage(statusCounts.excellent)
                                                                                },
                                                                                'good': { 
                                                                                    label: 'Good', 
                                                                                    icon: '✅', 
                                                                                    gradient: 'from-green-500 via-emerald-600 to-teal-600',
                                                                                    hoverGradient: 'hover:from-green-600 hover:via-emerald-700 hover:to-teal-700',
                                                                                    shadow: 'shadow-green-500/50',
                                                                                    border: 'border-green-400/60',
                                                                                    count: statusCounts.good,
                                                                                    percentage: calculatePercentage(statusCounts.good)
                                                                                },
                                                                                'average': { 
                                                                                    label: 'Average', 
                                                                                    icon: '⚠️', 
                                                                                    gradient: 'from-yellow-500 via-amber-600 to-orange-600',
                                                                                    hoverGradient: 'hover:from-yellow-600 hover:via-amber-700 hover:to-orange-700',
                                                                                    shadow: 'shadow-yellow-500/50',
                                                                                    border: 'border-yellow-400/60',
                                                                                    count: statusCounts.average,
                                                                                    percentage: calculatePercentage(statusCounts.average)
                                                                                },
                                                                                'poor': { 
                                                                                    label: 'Poor', 
                                                                                    icon: '❌', 
                                                                                    gradient: 'from-red-500 via-rose-600 to-pink-600',
                                                                                    hoverGradient: 'hover:from-red-600 hover:via-rose-700 hover:to-pink-700',
                                                                                    shadow: 'shadow-red-500/50',
                                                                                    border: 'border-red-400/60',
                                                                                    count: statusCounts.poor,
                                                                                    percentage: calculatePercentage(statusCounts.poor)
                                                                                }
                                                                            };
                                                                            
                                                                            // Dropdown with percentages
                                                                            return (
                                                                                <div className="relative">
                                                                                    <select
                                                                                        value={itemData.status || ''}
                                                                                        onChange={(e) => {
                                                                                            setInspectionData(prev => ({
                                                                                                ...prev,
                                                                                                [item.id]: { ...prev[item.id], status: e.target.value }
                                                                                            }));
                                                                                        }}
                                                                                        className={`w-full px-4 py-3 sm:py-3.5 rounded-xl sm:rounded-2xl border-2 font-black text-sm sm:text-base transition-all duration-200 appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 ${
                                                                                            itemData.status === 'excellent' 
                                                                                                ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white border-blue-400 focus:ring-blue-500' 
                                                                                            : itemData.status === 'good'
                                                                                                ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white border-green-400 focus:ring-green-500'
                                                                                            : itemData.status === 'average'
                                                                                                ? 'bg-gradient-to-r from-yellow-500 to-amber-600 text-white border-yellow-400 focus:ring-yellow-500'
                                                                                            : itemData.status === 'poor'
                                                                                                ? 'bg-gradient-to-r from-red-500 to-rose-600 text-white border-red-400 focus:ring-red-500'
                                                                                            : 'bg-slate-50 text-slate-700 border-slate-300 focus:ring-slate-500 hover:bg-slate-100'
                                                                                        }`}
                                                                                    >
                                                                                        <option value="" className="bg-white text-slate-700">Select Status</option>
                                                                                        {Object.entries(statusOptions).map(([value, status]) => (
                                                                                            <option 
                                                                                                key={value} 
                                                                                                value={value}
                                                                                                className={`${
                                                                                                    value === 'excellent' ? 'bg-blue-50 text-blue-700' :
                                                                                                    value === 'good' ? 'bg-green-50 text-green-700' :
                                                                                                    value === 'average' ? 'bg-yellow-50 text-yellow-700' :
                                                                                                    'bg-red-50 text-red-700'
                                                                                                }`}
                                                                                            >
                                                                                                {status.icon} {status.label} {statusCounts.total > 0 ? `(${status.percentage}% - ${status.count} items)` : ''}
                                                                                            </option>
                                                                                        ))}
                                                                                    </select>
                                                                                    {/* Custom dropdown arrow */}
                                                                                    <div className="absolute right-3 sm:right-4 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                                                                        <svg className={`w-5 h-5 ${itemData.status ? 'text-white' : 'text-slate-500'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M19 9l-7 7-7-7" />
                                                                                        </svg>
                                                                                    </div>
                                                                                    
                                                                                    {/* Selected status display */}
                                                                                    {itemData.status && itemData.status !== '' && (
                                                                                        <div className="mt-2 flex items-center gap-2">
                                                                                            <span className="text-xs sm:text-sm text-slate-600 font-bold">
                                                                                                Selected: {statusOptions[itemData.status].icon} {statusOptions[itemData.status].label}
                                                                                                {statusCounts.total > 0 && (
                                                                                                    <span className="ml-1 text-slate-500">
                                                                                                        ({statusOptions[itemData.status].percentage}% - {statusOptions[itemData.status].count} items)
                                                                                                    </span>
                                                                                                )}
                                                                                            </span>
                                                                                        </div>
                                                                                    )}
                                                                                </div>
                                                                            );
                                                                        })()}
                                                                    </div>
                                                                </div>
                                                            );
                                                        })}
                                                    </div>
                                                </div>
                                                
                                                {/* Footer */}
                                                <div className="p-3 sm:p-4 md:p-6 border-t border-slate-200 bg-slate-50">
                                                    <button
                                                        onClick={async () => {
                                                            console.log('Inspection completed:', {
                                                                jobId: inspectionModalJobId,
                                                                inspectionData
                                                            });
                                                            
                                                            // Check if all items are rated
                                                            const requiredInspectionItems = [
                                                                'engine_oil', 'wiper_rubber', 'brake_oil', 'air_filter', 
                                                                'radiator_water', 'shower_water', 'horn', 
                                                                'head_lights', 'indicator', 'brake_pad', 'ac_filter'
                                                            ];
                                                            
                                                            const allItemsRated = requiredInspectionItems.every(itemId => {
                                                                const itemData = inspectionData[itemId];
                                                                return itemData && itemData.status && itemData.status !== '';
                                                            });
                                                            
                                                            if (!allItemsRated) {
                                                                alert('Please rate all inspection items before saving.');
                                                                return;
                                                            }
                                                            
                                                            // Save inspection to backend
                                                            try {
                                                                const response = await fetch(API_ROUTES.inspections.store(inspectionModalJobId), {
                                                                    method: 'POST',
                                                                    headers: {
                                                                        'Content-Type': 'application/json',
                                                                        'X-CSRF-TOKEN': csrfToken,
                                                                        'Accept': 'application/json'
                                                                    },
                                                                    body: JSON.stringify({
                                                                        inspection_items: inspectionData,
                                                                        is_completed: true
                                                                    })
                                                                });
                                                                
                                                                const result = await response.json();
                                                                
                                                                if (result.success) {
                                                                    // Mark inspection as completed
                                                                    setCompletedInspections(prev => new Set([...prev, inspectionModalJobId]));
                                                                    
                                                                    alert('Car inspection saved successfully!');
                                                                    setInspectionModalJobId(null);
                                                                    setInspectionData({});
                                                                } else {
                                                                    alert('Error saving inspection: ' + (result.message || 'Unknown error'));
                                                                }
                                                            } catch (error) {
                                                                console.error('Error saving inspection:', error);
                                                                alert('Error saving inspection. Please try again.');
                                                            }
                                                        }}
                                                        className="w-full px-4 sm:px-5 md:px-6 py-2.5 sm:py-3 md:py-4 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl sm:rounded-2xl text-xs sm:text-sm font-black uppercase hover:from-purple-600 hover:to-purple-700 transition-all shadow-lg"
                                                    >
                                                        Save Inspection
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })()}
                                
                                {/* Expense Modal */}
                                {expenseModalJobId && (
                                    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-2 sm:p-3 md:p-4" onClick={() => {
                                        setExpenseModalJobId(null);
                                        setExpenseItems({});
                                        setCustomExpenses([]);
                                    }}>
                                        <div className="bg-white rounded-xl sm:rounded-2xl md:rounded-3xl shadow-2xl w-full max-w-md max-h-[95vh] sm:max-h-[90vh] overflow-hidden flex flex-col" onClick={(e) => e.stopPropagation()}>
                                            {/* Header */}
                                            <div className="p-3 sm:p-4 md:p-6 border-b border-slate-200 bg-gradient-to-r from-orange-500 to-orange-600 text-white">
                                                <div className="flex items-center justify-between gap-2 sm:gap-4">
                                                    <div className="flex-1 min-w-0">
                                                        <h2 className="text-lg sm:text-xl md:text-2xl font-black uppercase tracking-tighter">Refreshment Expense</h2>
                                                        <p className="text-xs sm:text-sm opacity-90 mt-0.5 sm:mt-1 hidden sm:block">Select items and quantities</p>
                                                    </div>
                                                    <button
                                                        onClick={() => {
                                                            setExpenseModalJobId(null);
                                                            setExpenseItems({});
                                                            setCustomExpenses([]);
                                                        }}
                                                        className="text-white hover:text-slate-200 transition-colors p-1.5 sm:p-2 flex-shrink-0"
                                                    >
                                                        <svg className="w-5 h-5 sm:w-5 sm:h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            {/* Refreshment Items */}
                                            <div className="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6">
                                                <div className="space-y-3 sm:space-y-3.5 md:space-y-4">
                                                    {[
                                                        { name: 'Tea', icon: '☕', defaultPrice: 50 },
                                                        { name: 'Cold Drink', icon: '🥤', defaultPrice: 100 },
                                                        { name: 'Mineral Water', icon: '💧', defaultPrice: 30 }
                                                    ].map((item) => {
                                                        const quantity = expenseItems[item.name]?.quantity !== undefined ? expenseItems[item.name].quantity : 1;
                                                        const price = expenseItems[item.name]?.price !== undefined ? expenseItems[item.name].price : '';
                                                        const priceValue = price === '' ? 0 : parseFloat(price) || 0;
                                                        const total = quantity * priceValue;
                                                        
                                                        return (
                                                            <div key={item.name} className="bg-slate-50 p-3 sm:p-4 rounded-xl sm:rounded-2xl border-2 border-slate-100">
                                                                <div className="flex items-center justify-between mb-2 sm:mb-3 gap-2">
                                                                    <div className="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                                                                        <span className="text-xl sm:text-2xl flex-shrink-0">{item.icon}</span>
                                                                        <span className="text-xs sm:text-sm font-black text-slate-900 uppercase truncate">{item.name}</span>
                                                                    </div>
                                                                    <div className="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
                                                                        <button
                                                                            type="button"
                                                                            onClick={() => {
                                                                                if (quantity > 1) {
                                                                                    const currentPrice = expenseItems[item.name]?.price !== undefined ? expenseItems[item.name].price : '';
                                                                                    setExpenseItems(prev => ({
                                                                                        ...prev,
                                                                                        [item.name]: { quantity: quantity - 1, price: currentPrice }
                                                                                    }));
                                                                                }
                                                                            }}
                                                                            className="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white border-2 border-slate-200 flex items-center justify-center text-slate-600 font-black hover:bg-slate-100 text-sm sm:text-base"
                                                                        >
                                                                            −
                                                                        </button>
                                                                        <span className="w-8 sm:w-10 text-center text-xs sm:text-sm font-black text-slate-900">{quantity}</span>
                                                                        <button
                                                                            type="button"
                                                                            onClick={() => {
                                                                                const currentPrice = expenseItems[item.name]?.price !== undefined ? expenseItems[item.name].price : '';
                                                                                setExpenseItems(prev => ({
                                                                                    ...prev,
                                                                                    [item.name]: { quantity: quantity + 1, price: currentPrice }
                                                                                }));
                                                                            }}
                                                                            className="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white border-2 border-slate-200 flex items-center justify-center text-slate-600 font-black hover:bg-slate-100 text-sm sm:text-base"
                                                                        >
                                                                            +
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                
                                                                {/* Price Input Box - Always Visible */}
                                                                <div className="pt-2 sm:pt-3 border-t border-slate-200">
                                                                    <div className="mb-1.5 sm:mb-2">
                                                                        <label className="text-[9px] sm:text-[10px] font-bold text-slate-500 uppercase block mb-1">Price (Rs.)</label>
                                                                        <input
                                                                            type="number"
                                                                            value={price}
                                                                            onChange={(e) => {
                                                                                const inputValue = e.target.value;
                                                                                let priceValue;
                                                                                if (inputValue === '') {
                                                                                    priceValue = '';
                                                                                } else {
                                                                                    const parsed = parseFloat(inputValue);
                                                                                    priceValue = isNaN(parsed) ? '' : parsed;
                                                                                }
                                                                                setExpenseItems(prev => ({
                                                                                    ...prev,
                                                                                    [item.name]: { quantity, price: priceValue }
                                                                                }));
                                                                            }}
                                                                            className="w-full px-3 sm:px-4 py-2 sm:py-2.5 md:py-3 text-sm sm:text-base font-black text-slate-900 border-2 border-slate-300 rounded-lg sm:rounded-xl bg-white focus:border-orange-500 focus:outline-none"
                                                                            min="0"
                                                                            step="10"
                                                                            placeholder="Enter price"
                                                                        />
                                                                    </div>
                                                                    {quantity >= 1 && (
                                                                        <div className="flex items-center justify-between pt-1.5 sm:pt-2">
                                                                            <span className="text-[10px] sm:text-xs text-slate-500">Total:</span>
                                                                            <span className="text-sm sm:text-base font-black text-orange-600">Rs.{total}</span>
                                                                        </div>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        );
                                                    })}
                                                    
                                                    {/* Custom Expenses */}
                                                    {customExpenses.map((customItem) => {
                                                        const quantity = expenseItems[customItem.id]?.quantity !== undefined ? expenseItems[customItem.id].quantity : 1;
                                                        const price = expenseItems[customItem.id]?.price !== undefined ? expenseItems[customItem.id].price : '';
                                                        const priceValue = price === '' ? 0 : parseFloat(price) || 0;
                                                        const total = quantity * priceValue;
                                                        
                                                        return (
                                                            <div key={customItem.id} className="bg-slate-50 p-3 sm:p-4 rounded-xl sm:rounded-2xl border-2 border-slate-100">
                                                                <div className="flex items-center justify-between mb-2 sm:mb-3 gap-2">
                                                                    <div className="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                                                                        <span className="text-xl sm:text-2xl flex-shrink-0">➕</span>
                                                                        <span className="text-xs sm:text-sm font-black text-slate-900 uppercase truncate">{customItem.name}</span>
                                                                    </div>
                                                                    <div className="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
                                                                        <button
                                                                            type="button"
                                                                            onClick={() => {
                                                                                if (quantity > 1) {
                                                                                    const currentPrice = expenseItems[customItem.id]?.price !== undefined ? expenseItems[customItem.id].price : '';
                                                                                    setExpenseItems(prev => ({
                                                                                        ...prev,
                                                                                        [customItem.id]: { quantity: quantity - 1, price: currentPrice }
                                                                                    }));
                                                                                }
                                                                            }}
                                                                            className="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white border-2 border-slate-200 flex items-center justify-center text-slate-600 font-black hover:bg-slate-100 text-sm sm:text-base"
                                                                        >
                                                                            −
                                                                        </button>
                                                                        <span className="w-8 sm:w-10 text-center text-xs sm:text-sm font-black text-slate-900">{quantity}</span>
                                                                        <button
                                                                            type="button"
                                                                            onClick={() => {
                                                                                const currentPrice = expenseItems[customItem.id]?.price !== undefined ? expenseItems[customItem.id].price : '';
                                                                                setExpenseItems(prev => ({
                                                                                    ...prev,
                                                                                    [customItem.id]: { quantity: quantity + 1, price: currentPrice }
                                                                                }));
                                                                            }}
                                                                            className="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-white border-2 border-slate-200 flex items-center justify-center text-slate-600 font-black hover:bg-slate-100 text-sm sm:text-base"
                                                                        >
                                                                            +
                                                                        </button>
                                                                        <button
                                                                            type="button"
                                                                            onClick={() => {
                                                                                setCustomExpenses(prev => prev.filter(item => item.id !== customItem.id));
                                                                                setExpenseItems(prev => {
                                                                                    const newItems = { ...prev };
                                                                                    delete newItems[customItem.id];
                                                                                    return newItems;
                                                                                });
                                                                            }}
                                                                            className="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-red-100 border-2 border-red-200 flex items-center justify-center text-red-600 font-black hover:bg-red-200 text-sm sm:text-base"
                                                                        >
                                                                            ×
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                
                                                                {/* Price Input Box - Always Visible */}
                                                                <div className="pt-2 sm:pt-3 border-t border-slate-200">
                                                                    <div className="mb-1.5 sm:mb-2">
                                                                        <label className="text-[9px] sm:text-[10px] font-bold text-slate-500 uppercase block mb-1">Price (Rs.)</label>
                                                                        <input
                                                                            type="number"
                                                                            value={price}
                                                                            onChange={(e) => {
                                                                                const inputValue = e.target.value;
                                                                                let priceValue;
                                                                                if (inputValue === '') {
                                                                                    priceValue = '';
                                                                                } else {
                                                                                    const parsed = parseFloat(inputValue);
                                                                                    priceValue = isNaN(parsed) ? '' : parsed;
                                                                                }
                                                                                setExpenseItems(prev => ({
                                                                                    ...prev,
                                                                                    [customItem.id]: { quantity, price: priceValue }
                                                                                }));
                                                                            }}
                                                                            className="w-full px-3 sm:px-4 py-2 sm:py-2.5 md:py-3 text-sm sm:text-base font-black text-slate-900 border-2 border-slate-300 rounded-lg sm:rounded-xl bg-white focus:border-orange-500 focus:outline-none"
                                                                            min="0"
                                                                            step="10"
                                                                            placeholder="Enter price"
                                                                        />
                                                                    </div>
                                                                    {quantity >= 1 && (
                                                                        <div className="flex items-center justify-between pt-1.5 sm:pt-2">
                                                                            <span className="text-[10px] sm:text-xs text-slate-500">Total:</span>
                                                                            <span className="text-sm sm:text-base font-black text-orange-600">Rs.{total}</span>
                                                                        </div>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        );
                                                    })}
                                                </div>
                                                
                                                {/* Add Other Expense Button */}
                                                <div className="mt-3 sm:mt-4">
                                                    <button
                                                        type="button"
                                                        onClick={() => {
                                                            setShowAddCustomExpense(true);
                                                            setNewCustomExpenseName('');
                                                        }}
                                                        className="w-full px-4 sm:px-5 md:px-6 py-3 sm:py-3.5 md:py-4 bg-slate-200 text-slate-700 rounded-xl sm:rounded-2xl text-xs sm:text-sm font-black uppercase hover:bg-slate-300 transition-all border-2 border-slate-300"
                                                    >
                                                        + Add Other Expense
                                                    </button>
                                                </div>
                                                
                                                {/* Add Custom Expense Input Modal */}
                                                {showAddCustomExpense && (
                                                    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] flex items-center justify-center p-2 sm:p-3 md:p-4" onClick={() => {
                                                        setShowAddCustomExpense(false);
                                                        setNewCustomExpenseName('');
                                                    }}>
                                                        <div className="bg-white rounded-xl sm:rounded-2xl md:rounded-3xl shadow-2xl w-full max-w-sm p-4 sm:p-5 md:p-6" onClick={(e) => e.stopPropagation()}>
                                                            <h3 className="text-lg sm:text-xl font-black uppercase mb-3 sm:mb-4 text-slate-900">Add Custom Expense</h3>
                                                            <input
                                                                type="text"
                                                                value={newCustomExpenseName}
                                                                onChange={(e) => setNewCustomExpenseName(e.target.value)}
                                                                placeholder="Enter expense name"
                                                                className="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base font-bold text-slate-900 border-2 border-slate-300 rounded-lg sm:rounded-xl bg-white focus:border-orange-500 focus:outline-none mb-3 sm:mb-4"
                                                                autoFocus
                                                                onKeyPress={(e) => {
                                                                    if (e.key === 'Enter' && newCustomExpenseName.trim()) {
                                                                        const newCustomExpense = {
                                                                            id: `custom_${Date.now()}`,
                                                                            name: newCustomExpenseName.trim()
                                                                        };
                                                                        setCustomExpenses(prev => [...prev, newCustomExpense]);
                                                                        setExpenseItems(prev => ({
                                                                            ...prev,
                                                                            [newCustomExpense.id]: { quantity: 1, price: '' }
                                                                        }));
                                                                        setShowAddCustomExpense(false);
                                                                        setNewCustomExpenseName('');
                                                                    }
                                                                }}
                                                            />
                                                            <div className="flex flex-col sm:flex-row gap-2 sm:gap-3">
                                                                <button
                                                                    type="button"
                                                                    onClick={() => {
                                                                        if (newCustomExpenseName.trim()) {
                                                                            const newCustomExpense = {
                                                                                id: `custom_${Date.now()}`,
                                                                                name: newCustomExpenseName.trim()
                                                                            };
                                                                            setCustomExpenses(prev => [...prev, newCustomExpense]);
                                                                            setExpenseItems(prev => ({
                                                                                ...prev,
                                                                                [newCustomExpense.id]: { quantity: 1, price: '' }
                                                                            }));
                                                                            setShowAddCustomExpense(false);
                                                                            setNewCustomExpenseName('');
                                                                        }
                                                                    }}
                                                                    className="flex-1 px-4 sm:px-5 md:px-6 py-2.5 sm:py-3 bg-orange-500 text-white rounded-lg sm:rounded-xl text-xs sm:text-sm font-black uppercase hover:bg-orange-600 transition-all"
                                                                >
                                                                    Add
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    onClick={() => {
                                                                        setShowAddCustomExpense(false);
                                                                        setNewCustomExpenseName('');
                                                                    }}
                                                                    className="flex-1 px-4 sm:px-5 md:px-6 py-2.5 sm:py-3 bg-slate-200 text-slate-700 rounded-lg sm:rounded-xl text-xs sm:text-sm font-black uppercase hover:bg-slate-300 transition-all"
                                                                >
                                                                    Cancel
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                )}
                                                
                                                {/* Total */}
                                                <div className="mt-4 sm:mt-5 md:mt-6 pt-3 sm:pt-4 border-t-2 border-slate-200">
                                                    <div className="flex items-center justify-between">
                                                        <span className="text-sm sm:text-base font-black text-slate-900 uppercase">Total:</span>
                                                        <span className="text-xl sm:text-2xl font-black text-orange-600">
                                                            Rs.{(() => {
                                                                let total = 0;
                                                                Object.keys(expenseItems).forEach((key) => {
                                                                    const item = expenseItems[key];
                                                                    if (!item) return;
                                                                    
                                                                    // Get quantity
                                                                    const qty = Number(item.quantity) || 1;
                                                                    
                                                                    // Get price - handle string, number, or empty
                                                                    let price = 0;
                                                                    if (item.price !== undefined && item.price !== null && item.price !== '') {
                                                                        if (typeof item.price === 'number') {
                                                                            price = item.price;
                                                                        } else if (typeof item.price === 'string') {
                                                                            price = parseFloat(item.price) || 0;
                                                                        } else {
                                                                            price = Number(item.price) || 0;
                                                                        }
                                                                    }
                                                                    
                                                                    total += qty * price;
                                                                });
                                                                return total;
                                                            })()}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            {/* Footer */}
                                            <div className="p-3 sm:p-4 md:p-6 border-t border-slate-200 bg-slate-50">
                                                <button
                                                    onClick={async () => {
                                                        // Prepare expense items array
                                                        const expenseItemsArray = [];
                                                        let totalAmount = 0;
                                                        
                                                        // Process default expense items
                                                        Object.keys(expenseItems).forEach((key) => {
                                                            const item = expenseItems[key];
                                                            if (!item) return;
                                                            
                                                            // Skip custom expense items (they have custom_ prefix)
                                                            if (key.startsWith('custom_')) return;
                                                            
                                                            // Get quantity
                                                            const qty = Number(item.quantity) || 0;
                                                            
                                                            // Get price - handle string, number, or empty
                                                            let price = 0;
                                                            if (item.price !== undefined && item.price !== null && item.price !== '') {
                                                                if (typeof item.price === 'number') {
                                                                    price = item.price;
                                                                } else if (typeof item.price === 'string') {
                                                                    price = parseFloat(item.price) || 0;
                                                                } else {
                                                                    price = Number(item.price) || 0;
                                                                }
                                                            }
                                                            
                                                            if (qty > 0 && price > 0) {
                                                                const total = qty * price;
                                                                totalAmount += total;
                                                                
                                                                expenseItemsArray.push({
                                                                    name: key,
                                                                    quantity: qty,
                                                                    price: price,
                                                                    total: total
                                                                });
                                                            }
                                                        });
                                                        
                                                        // Process custom expenses
                                                        customExpenses.forEach(customItem => {
                                                            const item = expenseItems[customItem.id];
                                                            if (item) {
                                                                const qty = Number(item.quantity) || 0;
                                                                let price = 0;
                                                                if (item.price !== undefined && item.price !== null && item.price !== '') {
                                                                    if (typeof item.price === 'number') {
                                                                        price = item.price;
                                                                    } else if (typeof item.price === 'string') {
                                                                        price = parseFloat(item.price) || 0;
                                                                    } else {
                                                                        price = Number(item.price) || 0;
                                                                    }
                                                                }
                                                                
                                                                if (qty > 0 && price > 0) {
                                                                    const total = qty * price;
                                                                    totalAmount += total;
                                                                    
                                                                    expenseItemsArray.push({
                                                                        name: customItem.name,
                                                                        quantity: qty,
                                                                        price: price,
                                                                        total: total
                                                                    });
                                                                }
                                                            }
                                                        });
                                                        
                                                        // Save expense to backend if there are items
                                                        if (expenseItemsArray.length > 0) {
                                                            try {
                                                                const response = await fetch(API_ROUTES.expenses.store(expenseModalJobId), {
                                                                    method: 'POST',
                                                                    headers: {
                                                                        'Content-Type': 'application/json',
                                                                        'X-CSRF-TOKEN': csrfToken,
                                                                        'Accept': 'application/json'
                                                                    },
                                                                    body: JSON.stringify({
                                                                        expense_items: expenseItemsArray,
                                                                        total_amount: totalAmount
                                                                    })
                                                                });
                                                                
                                                                const result = await response.json();
                                                                
                                                                if (result.success) {
                                                                    // Get job reference
                                                                    const job = activeJobs.find(j => j.id === expenseModalJobId);
                                                                    const reference = job ? `${job.vehicleNo} - ${job.customerName}` : `Job #${expenseModalJobId}`;
                                                                    
                                                                    // Create expense record for localStorage history
                                                                    const expenseRecord = {
                                                                        id: Date.now().toString(),
                                                                        reference: reference,
                                                                        jobId: expenseModalJobId,
                                                                        vehicleNo: job ? job.vehicleNo : '',
                                                                        customerName: job ? job.customerName : '',
                                                                        mobile: job ? job.mobile : '',
                                                                        date: new Date().toISOString(),
                                                                        items: expenseItemsArray,
                                                                        debit: totalAmount,
                                                                        credit: 0,
                                                                        subtotal: totalAmount
                                                                    };
                                                                    
                                                                    // Add to expense history
                                                                    setExpenseHistory(prev => [...prev, expenseRecord]);
                                                                    
                                                                    // Update stats with expense
                                                                    setStats(prev => ({
                                                                        ...prev,
                                                                        todayExpensesTotal: (prev.todayExpensesTotal || 0) + totalAmount,
                                                                        todayGrandTotal: (prev.todayGrandTotal || 0) - totalAmount
                                                                    }));
                                                                    
                                                                    console.log('Refreshment expense saved to backend:', result);
                                                                    
                                                                    alert(`Refreshment expense of Rs.${totalAmount} saved successfully!`);
                                                                    setExpenseModalJobId(null);
                                                                    setExpenseItems({});
                                                                    setCustomExpenses([]);
                                                                } else {
                                                                    alert('Error saving expense: ' + (result.message || 'Unknown error'));
                                                                }
                                                            } catch (error) {
                                                                console.error('Error saving expense:', error);
                                                                alert('Error saving expense. Please try again.');
                                                            }
                                                        } else {
                                                            alert('Please add at least one expense item with quantity and price.');
                                                        }
                                                    }}
                                                    className="w-full px-4 sm:px-5 md:px-6 py-2.5 sm:py-3 md:py-4 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl sm:rounded-2xl text-xs sm:text-sm font-black uppercase hover:from-orange-600 hover:to-orange-700 transition-all shadow-lg"
                                                >
                                                    Save Expense
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                )}
                                
                                {/* Expense Details Modal - Modern Card Style */}
                                {showExpenseDetailsModal && (
                                    <div className="fixed inset-0 bg-black/60 backdrop-blur-md z-50 flex items-center justify-center p-2 sm:p-3 md:p-4" onClick={() => setShowExpenseDetailsModal(false)}>
                                        <div className="bg-gradient-to-br from-white via-slate-50 to-white rounded-2xl sm:rounded-[30px] md:rounded-[40px] shadow-2xl w-full max-w-5xl max-h-[95vh] sm:max-h-[90vh] overflow-hidden flex flex-col border-2 sm:border-3 md:border-4 border-slate-200" onClick={(e) => e.stopPropagation()}>
                                            {/* Header with Icon - Auto Adjust */}
                                            <div className="relative p-3 sm:p-4 md:p-6 lg:p-8 bg-gradient-to-r from-orange-500 via-rose-500 to-pink-500 text-white overflow-hidden">
                                                <div className="absolute inset-0 bg-black/10"></div>
                                                <div className="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 sm:gap-3 md:gap-4">
                                                    <div className="flex items-center gap-2 sm:gap-3 md:gap-4 flex-1 min-w-0">
                                                        <div className="w-10 h-10 sm:w-12 sm:h-12 md:w-14 md:h-14 lg:w-16 lg:h-16 bg-white/20 backdrop-blur-sm rounded-lg sm:rounded-xl md:rounded-2xl flex items-center justify-center border-2 border-white/30 flex-shrink-0">
                                                            <svg className="w-5 h-5 sm:w-6 sm:h-6 md:w-7 md:h-7 lg:w-8 lg:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                            </svg>
                                                        </div>
                                                        <div className="flex-1 min-w-0">
                                                            <h2 className="text-lg sm:text-xl md:text-2xl lg:text-3xl font-black uppercase tracking-tight truncate">Expense Report</h2>
                                                            <p className="text-[10px] sm:text-xs md:text-sm opacity-90 mt-0.5 sm:mt-1 flex items-center gap-1.5 sm:gap-2">
                                                                <span className="text-sm sm:text-base">📋</span> 
                                                                <span className="truncate hidden sm:inline">Reference wise expense details</span>
                                                                <span className="truncate sm:hidden">Expense details</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <button
                                                        onClick={() => setShowExpenseDetailsModal(false)}
                                                        className="w-8 h-8 sm:w-9 sm:h-9 md:w-10 md:h-10 bg-white/20 hover:bg-white/30 rounded-lg sm:rounded-xl flex items-center justify-center transition-all backdrop-blur-sm border border-white/30 flex-shrink-0 self-start sm:self-auto"
                                                    >
                                                        <svg className="w-4 h-4 sm:w-4.5 sm:h-4.5 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            {/* Content */}
                                            <div className="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6 bg-gradient-to-b from-slate-50 to-white">
                                                {expenseHistory.length === 0 ? (
                                                    <div className="text-center py-8 sm:py-12 md:py-16">
                                                        <div className="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 bg-gradient-to-br from-slate-200 to-slate-300 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-5 md:mb-6">
                                                            <svg className="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                        </div>
                                                        <p className="text-base sm:text-lg md:text-xl font-black text-slate-700 uppercase tracking-wide">No Expenses Yet</p>
                                                        <p className="text-xs sm:text-sm text-slate-500 mt-1 sm:mt-2">Expenses will appear here once you add them</p>
                                                    </div>
                                                ) : (
                                                    <div className="space-y-4 sm:space-y-5 md:space-y-6">
                                                        {expenseHistory.map((expense, expIdx) => {
                                                            // Try to get job data from activeJobs if not in expense record
                                                            const job = activeJobs.find(j => j.id === expense.jobId);
                                                            const vehicleNo = expense.vehicleNo || job?.vehicleNo || 'N/A';
                                                            const customerName = expense.customerName || job?.customerName || 'N/A';
                                                            const mobile = expense.mobile || job?.mobile || 'N/A';
                                                            
                                                            return (
                                                            <div key={expense.id} className="bg-white rounded-2xl sm:rounded-[25px] md:rounded-[30px] border-2 border-slate-200 shadow-xl hover:shadow-2xl transition-all overflow-hidden">
                                                                {/* Customer Info Header */}
                                                                <div className="bg-gradient-to-r from-slate-100 via-slate-50 to-slate-100 p-3 sm:p-4 md:p-5 border-b-2 border-slate-300">
                                                                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3 md:gap-4">
                                                                        <div className="bg-white rounded-lg sm:rounded-xl p-3 sm:p-4 border-2 border-slate-200 shadow-sm hover:shadow-md transition-all">
                                                                            <div className="flex items-center gap-1.5 sm:gap-2 mb-1.5 sm:mb-2">
                                                                                <div className="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 bg-blue-500 rounded-md sm:rounded-lg flex items-center justify-center flex-shrink-0">
                                                                                    <svg className="w-3 h-3 sm:w-3.5 sm:h-3.5 md:w-4 md:h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                                                    </svg>
                                                                                </div>
                                                                                <p className="text-[8px] sm:text-[9px] font-black text-slate-500 uppercase tracking-widest">Vehicle Number</p>
                                                                            </div>
                                                                            <p className="text-xs sm:text-sm md:text-base font-black text-slate-900 ml-7 sm:ml-8 md:ml-10 truncate">{vehicleNo}</p>
                                                                        </div>
                                                                        <div className="bg-white rounded-lg sm:rounded-xl p-3 sm:p-4 border-2 border-slate-200 shadow-sm hover:shadow-md transition-all">
                                                                            <div className="flex items-center gap-1.5 sm:gap-2 mb-1.5 sm:mb-2">
                                                                                <div className="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 bg-emerald-500 rounded-md sm:rounded-lg flex items-center justify-center flex-shrink-0">
                                                                                    <svg className="w-3 h-3 sm:w-3.5 sm:h-3.5 md:w-4 md:h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                                                    </svg>
                                                                                </div>
                                                                                <p className="text-[8px] sm:text-[9px] font-black text-slate-500 uppercase tracking-widest">Customer Name</p>
                                                                            </div>
                                                                            <p className="text-xs sm:text-sm md:text-base font-black text-slate-900 ml-7 sm:ml-8 md:ml-10 truncate">{customerName}</p>
                                                                        </div>
                                                                        <div className="bg-white rounded-lg sm:rounded-xl p-3 sm:p-4 border-2 border-slate-200 shadow-sm hover:shadow-md transition-all sm:col-span-2 lg:col-span-1">
                                                                            <div className="flex items-center gap-1.5 sm:gap-2 mb-1.5 sm:mb-2">
                                                                                <div className="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 bg-purple-500 rounded-md sm:rounded-lg flex items-center justify-center flex-shrink-0">
                                                                                    <svg className="w-3 h-3 sm:w-3.5 sm:h-3.5 md:w-4 md:h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                                                    </svg>
                                                                                </div>
                                                                                <p className="text-[8px] sm:text-[9px] font-black text-slate-500 uppercase tracking-widest">Mobile Number</p>
                                                                            </div>
                                                                            <p className="text-xs sm:text-sm md:text-base font-black text-slate-900 ml-7 sm:ml-8 md:ml-10 truncate">{mobile}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                {/* Items List - Clean Table Style */}
                                                                <div className="p-3 sm:p-4 md:p-6">
                                                                    <div className="space-y-2 sm:space-y-2.5 md:space-y-3">
                                                                        {expense.items.map((item, idx) => (
                                                                            <div key={idx} className="bg-gradient-to-r from-slate-50 to-white rounded-lg sm:rounded-xl p-3 sm:p-4 border-2 border-slate-200 hover:border-orange-400 hover:shadow-lg transition-all">
                                                                                <div className="grid grid-cols-12 gap-2 sm:gap-3 md:gap-4 items-center">
                                                                                    <div className="col-span-2 sm:col-span-1 flex justify-center">
                                                                                        <div className="w-8 h-8 sm:w-9 sm:h-9 md:w-10 md:h-10 bg-gradient-to-br from-orange-400 to-rose-400 rounded-lg sm:rounded-xl flex items-center justify-center text-white font-black text-xs sm:text-sm shadow-md">
                                                                                            {idx + 1}
                                                                                        </div>
                                                                                    </div>
                                                                                    <div className="col-span-10 sm:col-span-5">
                                                                                        <p className="text-xs sm:text-sm font-black text-slate-900 uppercase mb-1 sm:mb-2 truncate">{item.name}</p>
                                                                                        <div className="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                                                                                            <span className="text-[9px] sm:text-[10px] text-slate-600 font-bold bg-slate-200 px-2 sm:px-2.5 md:px-3 py-0.5 sm:py-1 rounded-md sm:rounded-lg">
                                                                                                Qty: {item.quantity}
                                                                                            </span>
                                                                                            <span className="text-[9px] sm:text-[10px] text-slate-600 font-bold bg-slate-200 px-2 sm:px-2.5 md:px-3 py-0.5 sm:py-1 rounded-md sm:rounded-lg">
                                                                                                Rate: Rs.{typeof item.price === 'number' ? item.price.toFixed(2) : (parseFloat(item.price) || 0).toFixed(2)}
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div className="col-span-12 sm:col-span-6 text-left sm:text-right mt-2 sm:mt-0">
                                                                                        <p className="text-sm sm:text-base md:text-lg font-black text-slate-900">Rs.{typeof item.subtotal === 'number' ? item.subtotal.toFixed(2) : (parseFloat(item.subtotal) || 0).toFixed(2)}</p>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        ))}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            );
                                                        })}
                                                        
                                                        {/* Grand Total - All Expenses */}
                                                        {expenseHistory.length > 0 && (
                                                            <div className="mt-4 sm:mt-5 md:mt-6 pt-4 sm:pt-5 md:pt-6 border-t-2 sm:border-t-3 md:border-t-4 border-slate-400">
                                                                <div className="bg-gradient-to-r from-orange-500 via-rose-500 to-pink-500 rounded-2xl sm:rounded-[25px] md:rounded-[30px] p-4 sm:p-5 md:p-6 border-2 sm:border-3 md:border-4 border-orange-300 shadow-2xl">
                                                                    <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
                                                                        <div className="flex items-center gap-2 sm:gap-3 md:gap-4">
                                                                            <div className="w-10 h-10 sm:w-12 sm:h-12 md:w-14 md:h-14 bg-white/20 backdrop-blur-sm rounded-xl sm:rounded-2xl flex items-center justify-center border-2 border-white/30 flex-shrink-0">
                                                                                <svg className="w-5 h-5 sm:w-6 sm:h-6 md:w-7 md:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                                                </svg>
                                                                            </div>
                                                                            <div>
                                                                                <p className="text-[10px] sm:text-xs font-black text-white/80 uppercase tracking-[0.2em] sm:tracking-[0.3em] mb-0.5 sm:mb-1">Grand Total</p>
                                                                                <p className="text-xl sm:text-2xl md:text-3xl font-black text-white font-mono">
                                                                                    Rs.{(expenseHistory && Array.isArray(expenseHistory) ? expenseHistory.reduce((sum, exp) => sum + (parseFloat(exp.subtotal) || 0), 0) : 0).toFixed(2)}
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                        <div className="text-left sm:text-right">
                                                                            <p className="text-[10px] sm:text-xs font-black text-white/80 uppercase tracking-widest mb-0.5 sm:mb-1">Total Records</p>
                                                                            <p className="text-lg sm:text-xl md:text-2xl font-black text-white">{expenseHistory.length}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        )}
                                                    </div>
                                                )}
                                            </div>
                                            
                                            {/* Footer */}
                                            <div className="p-3 sm:p-4 md:p-6 border-t-2 border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                                                <button
                                                    onClick={() => setShowExpenseDetailsModal(false)}
                                                    className="w-full px-4 sm:px-6 md:px-8 py-2.5 sm:py-3 md:py-4 bg-gradient-to-r from-slate-700 to-slate-800 text-white rounded-xl sm:rounded-2xl text-xs sm:text-sm font-black uppercase hover:from-slate-800 hover:to-slate-900 transition-all shadow-lg hover:shadow-xl transform hover:scale-[1.02]"
                                                >
                                                    Close Report
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                )}
                                
                                {/* Cash Transfer Modal */}
                                {showCashTransferModal && (
                                    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-2 sm:p-3 md:p-4" onClick={() => {
                                        setShowCashTransferModal(false);
                                        setTransferAmount('');
                                        setSelectedTransferMethod(null);
                                        setSelectedUserId(null);
                                        setTransferNote('');
                                    }}>
                                        <div className="bg-white rounded-xl sm:rounded-2xl md:rounded-3xl shadow-2xl w-full max-w-md max-h-[95vh] sm:max-h-[90vh] overflow-hidden flex flex-col" onClick={(e) => e.stopPropagation()}>
                                            {/* Header */}
                                            <div className="p-3 sm:p-4 md:p-6 border-b border-slate-200 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white">
                                                <div className="flex items-center justify-between gap-2 sm:gap-4">
                                                    <div className="flex-1 min-w-0">
                                                        <h2 className="text-lg sm:text-xl md:text-2xl font-black uppercase tracking-tighter">Money Transfer</h2>
                                                        <p className="text-xs sm:text-sm opacity-90 mt-0.5 sm:mt-1">Transfer money to different accounts</p>
                                                    </div>
                                                    <button
                                                        onClick={() => {
                                                            setShowCashTransferModal(false);
                                                            setTransferAmount('');
                                                            setSelectedTransferMethod(null);
                                                        }}
                                                        className="text-white hover:text-slate-200 transition-colors p-1.5 sm:p-2 flex-shrink-0"
                                                    >
                                                        <svg className="w-5 h-5 sm:w-5 sm:h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            {/* Tabs */}
                                            <div className="flex border-b border-slate-200 bg-slate-50">
                                                <button
                                                    type="button"
                                                    onClick={() => {
                                                        setTransferTab('cash');
                                                        setSelectedTransferMethod(null);
                                                    }}
                                                    className={`flex-1 px-3 py-2.5 text-xs sm:text-sm font-black uppercase transition-colors ${
                                                        transferTab === 'cash'
                                                            ? 'bg-yellow-500 text-white border-b-2 border-yellow-600'
                                                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                                    }`}
                                                >
                                                    Cash
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => {
                                                        setTransferTab('bank');
                                                        setSelectedTransferMethod(null);
                                                    }}
                                                    className={`flex-1 px-3 py-2.5 text-xs sm:text-sm font-black uppercase transition-colors ${
                                                        transferTab === 'bank'
                                                            ? 'bg-blue-500 text-white border-b-2 border-blue-600'
                                                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                                    }`}
                                                >
                                                    Bank
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={async () => {
                                                        setTransferTab('user');
                                                        setSelectedUserId(null);
                                                        // Load branch users when switching to user tab
                                                        try {
                                                            const response = await fetch(API_ROUTES.payments.branchUsers, {
                                                                headers: {
                                                                    'Accept': 'application/json',
                                                                    'X-Requested-With': 'XMLHttpRequest'
                                                                }
                                                            });
                                                            const data = await response.json();
                                                            if (data.success && data.users) {
                                                                setBranchUsers(data.users);
                                                            }
                                                        } catch (error) {
                                                            console.error('Error loading branch users:', error);
                                                        }
                                                    }}
                                                    className={`flex-1 px-3 py-2.5 text-xs sm:text-sm font-black uppercase transition-colors ${
                                                        transferTab === 'user'
                                                            ? 'bg-green-500 text-white border-b-2 border-green-600'
                                                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                                    }`}
                                                >
                                                    User
                                                </button>
                                            </div>
                                            
                                            {/* Content */}
                                            <div className="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6">
                                                {transferTab === 'cash' ? (
                                                    <>
                                                        {/* Available Cash */}
                                                        <div className="mb-4 sm:mb-5 bg-gradient-to-br from-yellow-50 to-yellow-100 p-3 sm:p-4 rounded-xl sm:rounded-2xl border-2 border-yellow-200">
                                                            <div className="text-xs sm:text-sm font-bold text-yellow-700 uppercase mb-1">Available Cash</div>
                                                            <div className="text-2xl sm:text-3xl font-black text-yellow-600 font-mono">
                                                                Rs.{stats && typeof stats.cashOnHand !== 'undefined' ? stats.cashOnHand : 0}
                                                            </div>
                                                        </div>
                                                        
                                                        {/* Transfer Amount */}
                                                        <div className="mb-4 sm:mb-5">
                                                            <label className="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-2 sm:mb-3">Transfer Amount</label>
                                                            <input
                                                                type="number"
                                                                value={transferAmount}
                                                                onChange={(e) => setTransferAmount(e.target.value)}
                                                                placeholder="Enter amount"
                                                                className="w-full px-3 sm:px-4 py-2 sm:py-2.5 md:py-3 text-sm sm:text-base text-slate-900 border-2 border-slate-300 rounded-lg sm:rounded-xl bg-white focus:border-yellow-500 focus:outline-none font-mono"
                                                                min="0"
                                                                max={stats && typeof stats.cashOnHand !== 'undefined' ? stats.cashOnHand : 0}
                                                            />
                                                        </div>
                                                        
                                                        {/* Transfer Methods - Only Cash Methods */}
                                                        <div className="mb-4 sm:mb-5">
                                                            <label className="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-2 sm:mb-3">Select Transfer Method</label>
                                                            <div className="grid grid-cols-2 gap-2 sm:gap-2.5">
                                                                {transferMethods.filter(method => method.type === 'cash').map((method) => (
                                                                    <button
                                                                        key={method.id}
                                                                        type="button"
                                                                        onClick={() => setSelectedTransferMethod(method.id)}
                                                                        className={`p-3 sm:p-4 rounded-xl sm:rounded-2xl border-2 transition-all relative ${
                                                                            selectedTransferMethod === method.id
                                                                                ? 'bg-yellow-500 text-white border-yellow-600 shadow-lg scale-105'
                                                                                : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300'
                                                                        }`}
                                                                    >
                                                                        <div className="text-xl sm:text-2xl mb-1 sm:mb-2">{method.icon}</div>
                                                                        <div className="text-[10px] sm:text-xs font-black uppercase mb-0.5">{method.name}</div>
                                                                        {method.subtitle && (
                                                                            <div className={`text-[8px] sm:text-[9px] font-bold mb-1 ${
                                                                                selectedTransferMethod === method.id
                                                                                    ? 'text-yellow-100'
                                                                                    : 'text-slate-400'
                                                                            }`}>
                                                                                {method.subtitle}
                                                                            </div>
                                                                        )}
                                                                        <div className={`text-[9px] sm:text-[10px] font-bold mt-1 ${
                                                                            selectedTransferMethod === method.id
                                                                                ? 'text-yellow-100'
                                                                                : 'text-slate-500'
                                                                        }`}>
                                                                            Balance: Rs.{method.balance || 0}
                                                                        </div>
                                                                    </button>
                                                                ))}
                                                            </div>
                                                        </div>
                                                    </>
                                                ) : transferTab === 'bank' ? (
                                                    <>
                                                        {/* Available Cash for Bank Transfer */}
                                                        <div className="mb-4 sm:mb-5 bg-gradient-to-br from-blue-50 to-blue-100 p-3 sm:p-4 rounded-xl sm:rounded-2xl border-2 border-blue-200">
                                                            <div className="text-xs sm:text-sm font-bold text-blue-700 uppercase mb-1">Available Cash</div>
                                                            <div className="text-2xl sm:text-3xl font-black text-blue-600 font-mono">
                                                                Rs.{stats && typeof stats.cashOnHand !== 'undefined' ? stats.cashOnHand : 0}
                                                            </div>
                                                        </div>
                                                        
                                                        {/* Transfer Amount */}
                                                        <div className="mb-4 sm:mb-5">
                                                            <label className="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-2 sm:mb-3">Transfer Amount</label>
                                                            <input
                                                                type="number"
                                                                value={transferAmount}
                                                                onChange={(e) => setTransferAmount(e.target.value)}
                                                                placeholder="Enter amount"
                                                                className="w-full px-3 sm:px-4 py-2 sm:py-2.5 md:py-3 text-sm sm:text-base text-slate-900 border-2 border-slate-300 rounded-lg sm:rounded-xl bg-white focus:border-blue-500 focus:outline-none font-mono"
                                                                min="0"
                                                                max={stats && typeof stats.cashOnHand !== 'undefined' ? stats.cashOnHand : 0}
                                                            />
                                                        </div>
                                                        
                                                        {/* Bank Transfer - Show All Banks */}
                                                        <div className="mb-4 sm:mb-5">
                                                            <label className="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-2 sm:mb-3">Select Bank</label>
                                                            <div className="grid grid-cols-2 gap-2 sm:gap-2.5">
                                                                {transferMethods.filter(method => method.type === 'bank' || method.bankId).map((method) => (
                                                                    <button
                                                                        key={method.id}
                                                                        type="button"
                                                                        onClick={() => setSelectedTransferMethod(method.id)}
                                                                        className={`p-3 sm:p-4 rounded-xl sm:rounded-2xl border-2 transition-all relative ${
                                                                            selectedTransferMethod === method.id
                                                                                ? 'bg-blue-500 text-white border-blue-600 shadow-lg scale-105'
                                                                                : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300'
                                                                        }`}
                                                                    >
                                                                        <div className="mb-1 sm:mb-2 flex items-center justify-center">
                                                                            <svg className={`w-8 h-8 sm:w-10 sm:h-10 ${selectedTransferMethod === method.id ? 'text-white' : 'text-blue-600'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                                            </svg>
                                                                        </div>
                                                                        <div className="text-[10px] sm:text-xs font-black uppercase mb-0.5">{method.name}</div>
                                                                        {method.subtitle && (
                                                                            <div className={`text-[8px] sm:text-[9px] font-bold mb-1 ${
                                                                                selectedTransferMethod === method.id
                                                                                    ? 'text-blue-100'
                                                                                    : 'text-slate-400'
                                                                            }`}>
                                                                                {method.subtitle}
                                                                            </div>
                                                                        )}
                                                                        <div className={`text-[9px] sm:text-[10px] font-bold mt-1 ${
                                                                            selectedTransferMethod === method.id
                                                                                ? 'text-blue-100'
                                                                                : 'text-slate-500'
                                                                        }`}>
                                                                            Balance: Rs.{method.balance || 0}
                                                                        </div>
                                                                    </button>
                                                                ))}
                                                            </div>
                                                        </div>
                                                        
                                                        {/* Add New Bank Button */}
                                                        <div className="mb-4 sm:mb-5">
                                                            <button
                                                                type="button"
                                                                onClick={async () => {
                                                                    const newMethodName = prompt('Enter new bank name:');
                                                                    if (newMethodName && newMethodName.trim()) {
                                                                        // Create bank in database
                                                                        try {
                                                                            const response = await fetch('{{ route("admin.banks.store") }}', {
                                                                                method: 'POST',
                                                                                headers: {
                                                                                    'Content-Type': 'application/json',
                                                                                    'X-CSRF-TOKEN': csrfToken,
                                                                                    'Accept': 'application/json'
                                                                                },
                                                                                body: JSON.stringify({
                                                                                    name: newMethodName.trim(),
                                                                                    status: true
                                                                                })
                                                                            });
                                                                            
                                                                            const result = await response.json();
                                                                            
                                                                            if (result.success || response.ok) {
                                                                                // Reload banks from API
                                                                                const banksResponse = await fetch(API_ROUTES.banks.index, {
                                                                                    headers: {
                                                                                        'Accept': 'application/json',
                                                                                        'X-Requested-With': 'XMLHttpRequest'
                                                                                    }
                                                                                });
                                                                                const banksData = await banksResponse.json();
                                                                                
                                                                                if (banksData.success && banksData.banks) {
                                                                                    const bankMethods = banksData.banks.map(bank => ({
                                                                                        id: `bank_${bank.id}`,
                                                                                        name: bank.name,
                                                                                        icon: bank.icon === 'bank' ? 'bank' : bank.icon,
                                                                                        type: bank.type,
                                                                                        balance: bank.balance || 0,
                                                                                        subtitle: bank.subtitle || '',
                                                                                        bankId: bank.id
                                                                                    }));
                                                                                    
                                                                                    setTransferMethods([
                                                                                        { id: 'admin_cash', name: 'Admin Cash', icon: '💵', type: 'cash', balance: 0, subtitle: '', bankId: null },
                                                                                        ...bankMethods
                                                                                    ]);
                                                                                    
                                                                                    alert('New bank added successfully!');
                                                                                }
                                                                            } else {
                                                                                alert('Error creating bank. Please try again.');
                                                                            }
                                                                        } catch (error) {
                                                                            console.error('Error creating bank:', error);
                                                                            alert('Error creating bank. Please try again.');
                                                                        }
                                                                    }
                                                                }}
                                                                className="w-full px-3 sm:px-4 py-2 sm:py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg sm:rounded-xl text-xs sm:text-sm font-black uppercase transition-colors border-2 border-slate-300"
                                                            >
                                                                + Add New Bank
                                                            </button>
                                                        </div>
                                                    </>
                                                ) : transferTab === 'user' ? (
                                                    <>
                                                        {/* Available Cash for User Transfer */}
                                                        <div className="mb-4 sm:mb-5 bg-gradient-to-br from-green-50 to-green-100 p-3 sm:p-4 rounded-xl sm:rounded-2xl border-2 border-green-200">
                                                            <div className="text-xs sm:text-sm font-bold text-green-700 uppercase mb-1">Available Cash</div>
                                                            <div className="text-2xl sm:text-3xl font-black text-green-600 font-mono">
                                                                Rs.{stats && typeof stats.cashOnHand !== 'undefined' ? stats.cashOnHand : 0}
                                                            </div>
                                                        </div>
                                                        
                                                        {/* Transfer Amount */}
                                                        <div className="mb-4 sm:mb-5">
                                                            <label className="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-2 sm:mb-3">Transfer Amount</label>
                                                            <input
                                                                type="number"
                                                                value={transferAmount}
                                                                onChange={(e) => setTransferAmount(e.target.value)}
                                                                placeholder="Enter amount"
                                                                className="w-full px-3 sm:px-4 py-2 sm:py-2.5 md:py-3 text-sm sm:text-base text-slate-900 border-2 border-slate-300 rounded-lg sm:rounded-xl bg-white focus:border-green-500 focus:outline-none font-mono"
                                                                min="0"
                                                                max={stats && typeof stats.cashOnHand !== 'undefined' ? stats.cashOnHand : 0}
                                                            />
                                                        </div>
                                                        
                                                        {/* Select User */}
                                                        <div className="mb-4 sm:mb-5">
                                                            <label className="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-2 sm:mb-3">Select User</label>
                                                            {branchUsers.length === 0 ? (
                                                                <div className="text-center py-4 text-slate-500 text-sm">
                                                                    No users found in your branch
                                                                </div>
                                                            ) : (
                                                                <div className="space-y-2 max-h-60 overflow-y-auto">
                                                                    {branchUsers.map((user) => (
                                                                        <button
                                                                            key={user.id}
                                                                            type="button"
                                                                            onClick={() => setSelectedUserId(user.id)}
                                                                            className={`w-full p-3 sm:p-4 rounded-xl sm:rounded-2xl border-2 transition-all text-left ${
                                                                                selectedUserId === user.id
                                                                                    ? 'bg-green-500 text-white border-green-600 shadow-lg'
                                                                                    : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300'
                                                                            }`}
                                                                        >
                                                                            <div className="font-bold text-sm sm:text-base">{user.name}</div>
                                                                            <div className={`text-xs sm:text-sm mt-1 ${
                                                                                selectedUserId === user.id ? 'text-green-100' : 'text-slate-400'
                                                                            }`}>
                                                                                {user.email}
                                                                            </div>
                                                                            {user.phone && (
                                                                                <div className={`text-xs mt-0.5 ${
                                                                                    selectedUserId === user.id ? 'text-green-100' : 'text-slate-400'
                                                                                }`}>
                                                                                    {user.phone}
                                                                                </div>
                                                                            )}
                                                                        </button>
                                                                    ))}
                                                                </div>
                                                            )}
                                                        </div>
                                                        
                                                        {/* Transfer Note */}
                                                        <div className="mb-4 sm:mb-5">
                                                            <label className="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-2 sm:mb-3">Note (Optional)</label>
                                                            <textarea
                                                                value={transferNote}
                                                                onChange={(e) => setTransferNote(e.target.value)}
                                                                placeholder="Add a note for this transfer..."
                                                                className="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm text-slate-900 border-2 border-slate-300 rounded-lg sm:rounded-xl bg-white focus:border-green-500 focus:outline-none"
                                                                rows="2"
                                                                maxLength="500"
                                                            />
                                                        </div>
                                                    </>
                                                ) : null}
                                            </div>
                                            
                                            {/* Footer */}
                                            <div className="p-3 sm:p-4 md:p-6 border-t border-slate-200 bg-slate-50">
                                                <button
                                                    onClick={async () => {
                                                        if (!transferAmount || parseFloat(transferAmount) <= 0) {
                                                            alert('Please enter a valid transfer amount.');
                                                            return;
                                                        }
                                                        
                                                        const amount = parseFloat(transferAmount);
                                                        const availableCash = stats && typeof stats.cashOnHand !== 'undefined' ? stats.cashOnHand : 0;
                                                        
                                                        if (amount > availableCash) {
                                                            alert(`Insufficient cash. Available: Rs.${availableCash}`);
                                                            return;
                                                        }
                                                        
                                                        // Handle user transfer
                                                        if (transferTab === 'user') {
                                                            if (!selectedUserId) {
                                                                alert('Please select a user to transfer to.');
                                                                return;
                                                            }
                                                            
                                                            try {
                                                                const response = await fetch(API_ROUTES.payments.transferToUser, {
                                                                    method: 'POST',
                                                                    headers: {
                                                                        'Content-Type': 'application/json',
                                                                        'X-CSRF-TOKEN': csrfToken,
                                                                        'Accept': 'application/json'
                                                                    },
                                                                    body: JSON.stringify({
                                                                        to_user_id: selectedUserId,
                                                                        amount: amount,
                                                                        note: transferNote || null
                                                                    })
                                                                });
                                                                
                                                                const result = await response.json();
                                                                
                                                                if (!result.success) {
                                                                    alert('Error transferring cash: ' + (result.message || 'Unknown error'));
                                                                    return;
                                                                }
                                                                
                                                                // Refresh cash balance from database after transfer
                                                                refreshCashBalance();
                                                                
                                                                const selectedUser = branchUsers.find(u => u.id === selectedUserId);
                                                                alert(`Rs.${amount} transferred to ${selectedUser?.name || 'user'} successfully!`);
                                                                
                                                                setShowCashTransferModal(false);
                                                                setTransferAmount('');
                                                                setSelectedUserId(null);
                                                                setTransferNote('');
                                                            } catch (error) {
                                                                console.error('Error transferring cash:', error);
                                                                alert('Error transferring cash. Please try again.');
                                                            }
                                                            return;
                                                        }
                                                        
                                                        // Handle cash/bank transfer (existing logic)
                                                        if (!selectedTransferMethod) {
                                                            alert('Please select a transfer method.');
                                                            return;
                                                        }
                                                        
                                                        // Transfer cash
                                                        try {
                                                            const selectedMethod = transferMethods.find(m => m.id === selectedTransferMethod);
                                                            
                                                            // If it's a bank transfer (has bankId), save to database
                                                            if (selectedMethod?.bankId) {
                                                                const response = await fetch(API_ROUTES.cashTransfers.store, {
                                                                    method: 'POST',
                                                                    headers: {
                                                                        'Content-Type': 'application/json',
                                                                        'X-CSRF-TOKEN': csrfToken,
                                                                        'Accept': 'application/json'
                                                                    },
                                                                    body: JSON.stringify({
                                                                        bank_id: selectedMethod.bankId,
                                                                        amount: amount,
                                                                        notes: `Cash transfer to ${selectedMethod.name}`
                                                                    })
                                                                });
                                                                
                                                                const result = await response.json();
                                                                
                                                                if (!result.success) {
                                                                    alert('Error transferring cash: ' + (result.message || 'Unknown error'));
                                                                    return;
                                                                }
                                                            }
                                                            
                                                            // Refresh cash balance from database after transfer
                                                            refreshCashBalance();
                                                            
                                                            // Update transfer method balance
                                                            setTransferMethods(prev => prev.map(method => 
                                                                method.id === selectedTransferMethod
                                                                    ? { ...method, balance: (method.balance || 0) + amount }
                                                                    : method
                                                            ));
                                                            
                                                            alert(`Rs.${amount} transferred to ${selectedMethod?.name || 'selected method'} successfully!`);
                                                            
                                                            setShowCashTransferModal(false);
                                                            setTransferAmount('');
                                                            setSelectedTransferMethod(null);
                                                        } catch (error) {
                                                            console.error('Error transferring cash:', error);
                                                            alert('Error transferring cash. Please try again.');
                                                        }
                                                    }}
                                                    className={`w-full px-4 sm:px-5 md:px-6 py-2.5 sm:py-3 md:py-4 rounded-xl sm:rounded-2xl text-xs sm:text-sm font-black uppercase transition-all shadow-lg ${
                                                        transferTab === 'cash'
                                                            ? 'bg-gradient-to-r from-yellow-500 to-yellow-600 text-white hover:from-yellow-600 hover:to-yellow-700'
                                                            : transferTab === 'bank'
                                                            ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white hover:from-blue-600 hover:to-blue-700'
                                                            : 'bg-gradient-to-r from-green-500 to-green-600 text-white hover:from-green-600 hover:to-green-700'
                                                    }`}
                                                >
                                                    {transferTab === 'cash' ? 'Transfer Cash' : transferTab === 'bank' ? 'Transfer to Bank' : 'Transfer to User'}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                )}
                                
                                {/* Completed Jobs Modal - Table View with Edit/Delete/Detail */}
                                {showCompletedJobsModal && (
                                    <div className="fixed inset-0 bg-gradient-to-br from-black/80 via-slate-900/90 to-black/80 backdrop-blur-xl z-50 flex items-center justify-center p-1 sm:p-3 md:p-4" onClick={() => setShowCompletedJobsModal(false)}>
                                        <div className="bg-gradient-to-br from-white via-slate-50 to-white rounded-xl sm:rounded-[60px] md:rounded-[70px] shadow-[0_25px_100px_-12px_rgba(0,0,0,0.5)] w-full max-w-7xl max-h-[98vh] overflow-hidden flex flex-col border-2 sm:border-[6px] border-blue-300/60 relative" onClick={(e) => e.stopPropagation()}>
                                            {/* Header - Compact */}
                                            <div className="relative p-3 sm:p-5 md:p-6 bg-gradient-to-br from-blue-600 via-indigo-600 via-purple-600 to-blue-600 text-white overflow-hidden">
                                                {/* Animated Background Elements */}
                                                <div className="absolute inset-0">
                                                    <div className="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-2xl -mr-32 -mt-32"></div>
                                                    <div className="absolute bottom-0 left-0 w-56 h-56 bg-white/10 rounded-full blur-2xl -ml-28 -mb-28"></div>
                                                </div>
                                                
                                                <div className="relative flex items-center justify-between z-10">
                                                        <div className="flex items-center gap-2 sm:gap-4">
                                                        <div className="relative">
                                                                <div className="w-8 h-8 sm:w-10 md:w-12 sm:h-10 md:h-12 bg-white/25 backdrop-blur-xl rounded-lg sm:rounded-xl flex items-center justify-center border-2 border-white/50 shadow-lg">
                                                                    <svg className="w-4 h-4 sm:w-5 md:w-6 sm:h-5 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                        <div className="flex-1">
                                                                <h2 className="text-base sm:text-xl md:text-2xl lg:text-3xl font-black uppercase tracking-tight drop-shadow-lg">
                                                                COMPLETED JOBS
                                                            </h2>
                                                                <div className="flex items-center gap-2 sm:gap-3 mt-1 sm:mt-2">
                                                                    {(() => {
                                                                        const formatDate = (dateString) => {
                                                                            if (!dateString) return '';
                                                                            const date = new Date(dateString + 'T00:00:00');
                                                                            const day = date.getDate();
                                                                            const month = date.toLocaleDateString('en-US', { month: 'short' });
                                                                            const year = date.getFullYear();
                                                                            return `${day} - ${month} - ${year}`;
                                                                        };
                                                                        return (
                                                                            <>
                                                                                <div className="relative w-32 sm:w-36 md:w-40">
                                                                                    <input
                                                                                        type="date"
                                                                                        value={dateRangeStart}
                                                                                        onChange={(e) => setDateRangeStart(e.target.value)}
                                                                                        className="text-[9px] sm:text-[10px] md:text-xs px-2 sm:px-3 py-1 sm:py-1.5 bg-white/25 backdrop-blur-xl border-2 border-white/50 rounded-lg sm:rounded-xl text-white font-semibold focus:outline-none focus:ring-2 focus:ring-white/50 [color-scheme:dark] w-full opacity-0 absolute z-10 cursor-pointer"
                                                                                    />
                                                                                    <div className="text-[9px] sm:text-[10px] md:text-xs px-2 sm:px-3 py-1 sm:py-1.5 bg-white/25 backdrop-blur-xl border-2 border-white/50 rounded-lg sm:rounded-xl text-white font-semibold w-full pointer-events-none">
                                                                                        {formatDate(dateRangeStart) || 'Select Date'}
                                                                                    </div>
                                                                                </div>
                                                                                <span className="text-white/80 font-bold text-xs sm:text-sm">to</span>
                                                                                <div className="relative w-32 sm:w-36 md:w-40">
                                                                                    <input
                                                                                        type="date"
                                                                                        value={dateRangeEnd}
                                                                                        onChange={(e) => setDateRangeEnd(e.target.value)}
                                                                                        className="text-[9px] sm:text-[10px] md:text-xs px-2 sm:px-3 py-1 sm:py-1.5 bg-white/25 backdrop-blur-xl border-2 border-white/50 rounded-lg sm:rounded-xl text-white font-semibold focus:outline-none focus:ring-2 focus:ring-white/50 [color-scheme:dark] w-full opacity-0 absolute z-10 cursor-pointer"
                                                                                    />
                                                                                    <div className="text-[9px] sm:text-[10px] md:text-xs px-2 sm:px-3 py-1 sm:py-1.5 bg-white/25 backdrop-blur-xl border-2 border-white/50 rounded-lg sm:rounded-xl text-white font-semibold w-full pointer-events-none">
                                                                                        {formatDate(dateRangeEnd) || 'Select Date'}
                                                                                    </div>
                                                                                </div>
                                                                            </>
                                                                        );
                                                                    })()}
                                                                </div>
                                                        </div>
                                                    </div>
                                                    <button
                                                        onClick={() => setShowCompletedJobsModal(false)}
                                                            className="w-8 h-8 sm:w-10 sm:h-10 bg-white/25 hover:bg-white/35 rounded-lg sm:rounded-xl flex items-center justify-center transition-all backdrop-blur-xl border-2 border-white/50 hover:scale-110 hover:rotate-90 shadow-lg"
                                                        aria-label="Close completed jobs modal"
                                                    >
                                                            <svg className="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            {/* Statistics Cards - Compact */}
                                            {filteredCompletedJobs && Array.isArray(filteredCompletedJobs) && filteredCompletedJobs.length > 0 && (
                                                <div className="p-2 sm:p-4 md:p-5 bg-gradient-to-b from-slate-50 via-white to-slate-50 border-b-2 border-slate-200">
                                                    <div className="grid grid-cols-4 gap-1.5 sm:gap-2 md:gap-3 max-w-5xl mx-auto">
                                                        {/* Workers Card */}
                                                        <div 
                                                            onClick={() => setShowWorkerFilterModal(true)}
                                                            className="group relative bg-gradient-to-br from-orange-500 via-amber-500 to-orange-600 rounded-lg sm:rounded-xl md:rounded-2xl p-2 sm:p-3 md:p-4 shadow-lg border-2 border-orange-300/60 transform hover:scale-[1.02] transition-all duration-200 overflow-hidden cursor-pointer"
                                                        >
                                                            <div className="absolute top-0 right-0 w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 bg-white/10 rounded-full blur-xl -mr-8 sm:-mr-10 md:-mr-12 -mt-8 sm:-mt-10 md:-mt-12"></div>
                                                            <div className="relative z-10">
                                                                <div className="flex items-center gap-1 sm:gap-2 mb-1 sm:mb-2 hidden sm:flex">
                                                                    <div className="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 bg-white/25 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                                                        <svg className="w-3 h-3 sm:w-3.5 sm:h-3.5 md:w-4 md:h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                                <p className="text-[7px] sm:text-[8px] md:text-[9px] font-black text-white/95 uppercase tracking-wider mb-0.5 sm:mb-1">WORKERS</p>
                                                                <p className="text-xs sm:text-sm md:text-lg lg:text-xl font-black text-white">
                                                                    {(() => {
                                                                        if (!filteredCompletedJobs || !Array.isArray(filteredCompletedJobs)) return 0;
                                                                        const uniqueWorkers = new Set();
                                                                        filteredCompletedJobs.forEach(job => {
                                                                            const workerName = job.workerName || job.worker_name || job.worker;
                                                                            if (workerName && workerName !== 'N/A' && workerName.trim() !== '') {
                                                                                uniqueWorkers.add(workerName.trim());
                                                                            }
                                                                        });
                                                                        return uniqueWorkers.size;
                                                                    })()}
                                                                </p>
                                                                {selectedWorkerFilter && (
                                                                    <p className="text-[8px] sm:text-[9px] text-white/90 mt-1 truncate" title={selectedWorkerFilter}>
                                                                        {selectedWorkerFilter}
                                                                    </p>
                                                                )}
                                                            </div>
                                                        </div>
                                                        
                                                        {/* Total Revenue Card */}
                                                        <div className="group relative bg-gradient-to-br from-emerald-500 via-green-500 to-emerald-600 rounded-lg sm:rounded-xl md:rounded-2xl p-2 sm:p-3 md:p-4 shadow-lg border-2 border-emerald-300/60 transform hover:scale-[1.02] transition-all duration-200 overflow-hidden">
                                                            <div className="absolute top-0 right-0 w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 bg-white/10 rounded-full blur-xl -mr-8 sm:-mr-10 md:-mr-12 -mt-8 sm:-mt-10 md:-mt-12"></div>
                                                            <div className="relative z-10">
                                                                <div className="flex items-center gap-1 sm:gap-2 mb-1 sm:mb-2 hidden sm:flex">
                                                                    <div className="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 bg-white/25 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                                                        <svg className="w-3 h-3 sm:w-3.5 sm:h-3.5 md:w-4 md:h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                                <p className="text-[7px] sm:text-[8px] md:text-[9px] font-black text-white/95 uppercase tracking-wider mb-0.5 sm:mb-1">REVENUE</p>
                                                                <p className="text-xs sm:text-sm md:text-lg lg:text-xl font-black text-white font-mono">
                                                                    Rs.{Math.round(filteredCompletedJobs && Array.isArray(filteredCompletedJobs) ? filteredCompletedJobs.reduce((sum, job) => sum + (parseFloat(job.price) || 0), 0) : 0)}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        
                                                        {/* Total Jobs Card */}
                                                        <div className="group relative bg-gradient-to-br from-blue-500 via-indigo-500 to-blue-600 rounded-lg sm:rounded-xl md:rounded-2xl p-2 sm:p-3 md:p-4 shadow-lg border-2 border-blue-300/60 transform hover:scale-[1.02] transition-all duration-200 overflow-hidden">
                                                            <div className="absolute top-0 right-0 w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 bg-white/10 rounded-full blur-xl -mr-8 sm:-mr-10 md:-mr-12 -mt-8 sm:-mt-10 md:-mt-12"></div>
                                                            <div className="relative z-10">
                                                                <div className="flex items-center gap-1 sm:gap-2 mb-1 sm:mb-2 hidden sm:flex">
                                                                    <div className="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 bg-white/25 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                                                        <svg className="w-3 h-3 sm:w-3.5 sm:h-3.5 md:w-4 md:h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                                <p className="text-[7px] sm:text-[8px] md:text-[9px] font-black text-white/95 uppercase tracking-wider mb-0.5 sm:mb-1">JOBS</p>
                                                                <p className="text-xs sm:text-sm md:text-lg lg:text-xl font-black text-white">
                                                                    {filteredCompletedJobs && Array.isArray(filteredCompletedJobs) ? filteredCompletedJobs.length : 0}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        
                                                        {/* Average Amount Card */}
                                                        <div 
                                                            onClick={() => setShowAvgDetailsModal(true)}
                                                            className="group relative bg-gradient-to-br from-purple-500 via-pink-500 to-purple-600 rounded-lg sm:rounded-xl md:rounded-2xl p-2 sm:p-3 md:p-4 shadow-lg border-2 border-purple-300/60 transform hover:scale-[1.02] transition-all duration-200 overflow-hidden cursor-pointer"
                                                        >
                                                            <div className="absolute top-0 right-0 w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 bg-white/10 rounded-full blur-xl -mr-8 sm:-mr-10 md:-mr-12 -mt-8 sm:-mt-10 md:-mt-12"></div>
                                                            <div className="relative z-10">
                                                                <div className="flex items-center gap-1 sm:gap-2 mb-1 sm:mb-2 hidden sm:flex">
                                                                    <div className="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 bg-white/25 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                                                        <svg className="w-3 h-3 sm:w-3.5 sm:h-3.5 md:w-4 md:h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                                <p className="text-[7px] sm:text-[8px] md:text-[9px] font-black text-white/95 uppercase tracking-wider mb-0.5 sm:mb-1">AVG</p>
                                                                <p className="text-xs sm:text-sm md:text-lg lg:text-xl font-black text-white font-mono">
                                                                    Rs.{Math.round(filteredCompletedJobs && Array.isArray(filteredCompletedJobs) && filteredCompletedJobs.length > 0 ? (filteredCompletedJobs.reduce((sum, job) => sum + (parseFloat(job.price) || 0), 0) / filteredCompletedJobs.length) : 0)}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            )}
                                            
                                            {/* Content - Compact Table View */}
                                            <div className="flex-1 overflow-y-auto p-2 sm:p-4 md:p-6 bg-gradient-to-b from-slate-50 via-white to-slate-50">
                                                {!filteredCompletedJobs || !Array.isArray(filteredCompletedJobs) || filteredCompletedJobs.length === 0 ? (
                                                    <div className="text-center py-8 sm:py-12 md:py-16">
                                                        <div className="relative w-20 h-20 sm:w-24 sm:h-24 md:w-32 md:h-32 bg-gradient-to-br from-blue-400 via-indigo-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-6 shadow-lg">
                                                            <svg className="w-10 h-10 sm:w-12 sm:h-12 md:w-16 md:h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                            </svg>
                                                        </div>
                                                        <p className="text-lg sm:text-xl md:text-2xl font-black text-slate-800 uppercase mb-2">No Completed Jobs</p>
                                                        <p className="text-xs sm:text-sm text-slate-500">Completed jobs will appear here</p>
                                                    </div>
                                                ) : (
                                                    <div className="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
                                                        <div className="overflow-x-auto -mx-2 sm:mx-0">
                                                            <table className="w-full min-w-[800px]">
                                                                <thead className="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white">
                                                                    <tr>
                                                                        <th className="px-2 sm:px-3 py-2 sm:py-2.5 text-left text-[8px] sm:text-[9px] md:text-[10px] font-black uppercase tracking-wider">#</th>
                                                                        <th className="px-2 sm:px-3 py-2 sm:py-2.5 text-left text-[8px] sm:text-[9px] md:text-[10px] font-black uppercase tracking-wider">Date/Time</th>
                                                                        <th className="px-2 sm:px-3 py-2 sm:py-2.5 text-left text-[8px] sm:text-[9px] md:text-[10px] font-black uppercase tracking-wider">Vehicle</th>
                                                                        <th className="px-2 sm:px-3 py-2 sm:py-2.5 text-left text-[8px] sm:text-[9px] md:text-[10px] font-black uppercase tracking-wider">
                                                                            <div className="flex items-start justify-between gap-2">
                                                                                <div>Worker</div>
                                                                                <div className="text-[8px] sm:text-[9px] md:text-[10px] font-black uppercase tracking-wider text-right flex-shrink-0">Amount</div>
                                                                            </div>
                                                                        </th>
                                                                        <th className="px-2 sm:px-3 py-2 sm:py-2.5 text-left text-[8px] sm:text-[9px] md:text-[10px] font-black uppercase tracking-wider hidden sm:table-cell">Amount</th>
                                                                        <th className="px-2 sm:px-3 py-2 sm:py-2.5 text-left text-[8px] sm:text-[9px] md:text-[10px] font-black uppercase tracking-wider hidden md:table-cell">Worker</th>
                                                                        <th className="px-2 sm:px-3 py-2 sm:py-2.5 text-left text-[8px] sm:text-[9px] md:text-[10px] font-black uppercase tracking-wider hidden lg:table-cell">Commission</th>
                                                                        <th className="px-2 sm:px-3 py-2 sm:py-2.5 text-center text-[8px] sm:text-[9px] md:text-[10px] font-black uppercase tracking-wider">Actions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody className="bg-white divide-y divide-slate-100">
                                                                    {filteredCompletedJobs && Array.isArray(filteredCompletedJobs) && filteredCompletedJobs.map((job, jobIdx) => {
                                                                        const startTime = job.startTime ? new Date(job.startTime).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : 'N/A';
                                                                        const endTime = job.endTime ? new Date(job.endTime).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : 'N/A';
                                                                        // Format date as "DD MMM YYYY" (e.g., "20 Jan 2026")
                                                                        const jobDate = job.endTime ? (() => {
                                                                            const date = new Date(job.endTime);
                                                                            const day = date.getDate();
                                                                            const month = date.toLocaleDateString('en-US', { month: 'short' });
                                                                            const year = date.getFullYear();
                                                                            return `${day} ${month} ${year}`;
                                                                        })() : 'N/A';
                                                                        const jobDateTime = jobDate !== 'N/A' ? `${jobDate} ${endTime}` : 'N/A';
                                                                        
                                                                        // Calculate duration
                                                                        let durationText = 'N/A';
                                                                        if (job.startTime && job.endTime) {
                                                                            const start = new Date(job.startTime);
                                                                            const end = new Date(job.endTime);
                                                                            const diffMs = end - start;
                                                                            const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                                                                            const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                                                                            
                                                                            if (diffHours > 0 && diffMinutes > 0) {
                                                                                durationText = `${diffHours}h ${diffMinutes}m`;
                                                                            } else if (diffHours > 0) {
                                                                                durationText = `${diffHours}h`;
                                                                            } else if (diffMinutes > 0) {
                                                                                durationText = `${diffMinutes}m`;
                                                                            } else {
                                                                                durationText = '0m';
                                                                            }
                                                                        } else if (job.durationSeconds) {
                                                                            // Fallback to durationSeconds if available
                                                                            const totalMinutes = Math.floor(job.durationSeconds / 60);
                                                                            const hours = Math.floor(totalMinutes / 60);
                                                                            const minutes = totalMinutes % 60;
                                                                            
                                                                            if (hours > 0 && minutes > 0) {
                                                                                durationText = `${hours}h ${minutes}m`;
                                                                            } else if (hours > 0) {
                                                                                durationText = `${hours}h`;
                                                                            } else if (minutes > 0) {
                                                                                durationText = `${minutes}m`;
                                                                            } else {
                                                                                durationText = '0m';
                                                                            }
                                                                        }
                                                                        
                                                                        return (
                                                                            <tr key={job.id || jobIdx} className="hover:bg-blue-50/50 transition-colors group">
                                                                                <td className="px-2 sm:px-3 py-2 sm:py-3 whitespace-nowrap">
                                                                                    <div className="w-5 h-5 sm:w-6 sm:h-6 md:w-7 md:h-7 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-md sm:rounded-lg flex items-center justify-center text-white font-black text-[9px] sm:text-[10px] md:text-[11px] shadow-md">
                                                                                        {jobIdx + 1}
                                                                                    </div>
                                                                                </td>
                                                                                <td className="px-2 sm:px-3 py-2 sm:py-3 whitespace-nowrap">
                                                                                    <div className="text-[9px] sm:text-[10px] md:text-[11px] font-black text-slate-900">{jobDate}</div>
                                                                                    <div className="text-[8px] sm:text-[9px] md:text-[10px] text-slate-600 mt-0.5">{startTime} - {endTime}</div>
                                                                                    <div className="text-[8px] sm:text-[9px] md:text-[10px] text-blue-600 font-semibold mt-0.5">⏱️ {durationText}</div>
                                                                                </td>
                                                                                <td className="px-2 sm:px-3 py-2 sm:py-3 whitespace-nowrap">
                                                                                    <div className="text-[9px] sm:text-[10px] md:text-[11px] font-black text-slate-900">{job.vehicleNo || job.vehicle_no || 'N/A'}</div>
                                                                                    <div className="text-[8px] sm:text-[9px] md:text-[10px] text-slate-600 mt-0.5">{job.customerName || job.customer_name || 'N/A'}</div>
                                                                                    {job.mobile && <div className="text-[8px] sm:text-[9px] md:text-[10px] text-slate-500 mt-0.5 font-mono">{job.mobile}</div>}
                                                                                </td>
                                                                                <td className="px-2 sm:px-3 py-2 sm:py-3 whitespace-nowrap">
                                                                                    <div className="flex items-start justify-between gap-2 sm:gap-3">
                                                                                        <div className="flex-1 min-w-0">
                                                                                            <div className="text-[9px] sm:text-[10px] md:text-[11px] font-black text-blue-600 mb-0.5">{job.serviceName || job.service_name || job.service || 'N/A'}</div>
                                                                                            <div className="text-[9px] sm:text-[10px] md:text-[11px] font-black text-slate-900">{job.workerName || job.worker_name || job.worker || 'N/A'}</div>
                                                                                            <div className="text-[8px] sm:text-[9px] md:text-[10px] text-slate-500 mt-0.5">{userName || 'Guest'}</div>
                                                                                        </div>
                                                                                        <div className="text-sm sm:text-base md:text-lg font-black text-blue-600 font-mono flex-shrink-0 text-right">
                                                                                            {Math.round(Number(job.price) || 0) > 0 ? `Rs.${Math.round(Number(job.price) || 0)}` : ''}
                                                                                        </div>
                                                                                    </div>
                                                                                </td>
                                                                                <td className="px-2 sm:px-3 py-2 sm:py-3 whitespace-nowrap hidden sm:table-cell">
                                                                                    <div className="text-sm sm:text-base md:text-lg font-black text-blue-600 font-mono">Rs.{Math.round(Number(job.price) || 0)}</div>
                                                                                </td>
                                                                                <td className="px-2 sm:px-3 py-2 sm:py-3 whitespace-nowrap hidden lg:table-cell">
                                                                                    {job.workerCommission > 0 ? (
                                                                                        <div>
                                                                                            <div className="text-[9px] sm:text-[10px] md:text-[11px] font-black text-emerald-600 font-mono">Rs.{(Number(job.commissionAmount) || 0).toFixed(2)}</div>
                                                                                            <div className="text-[8px] sm:text-[9px] text-slate-500">({job.workerCommission}%)</div>
                                                                                        </div>
                                                                                    ) : (
                                                                                        <div className="text-[9px] sm:text-[10px] text-slate-400">-</div>
                                                                                    )}
                                                                                </td>
                                                                                <td className="px-2 sm:px-3 py-2 sm:py-3 whitespace-nowrap text-center">
                                                                                    <div className="flex items-center justify-center gap-1.5 sm:gap-2 flex-wrap">
                                                                                        <button
                                                                                            onClick={async () => {
                                                                                                try {
                                                                                                    const jobResponse = await fetch(API_ROUTES.jobs.index);
                                                                                                    const jobData = await jobResponse.json();
                                                                                                    const fullJob = jobData.jobs?.find(j => j.id === job.id) || job;
                                                                                                    
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
                                                                                                    
                                                                                                    setSelectedJobForDetail({ ...fullJob, inspection, expense });
                                                                                                } catch (error) {
                                                                                                    console.error('Error loading job details:', error);
                                                                                                    setSelectedJobForDetail(job);
                                                                                                }
                                                                                            }}
                                                                                            className="group relative w-8 h-8 sm:w-9 sm:h-9 md:w-10 md:h-10 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg sm:rounded-xl flex items-center justify-center transition-all duration-200 hover:from-blue-600 hover:to-blue-700 hover:scale-110 shadow-md hover:shadow-lg border-2 border-blue-400/50 hover:border-blue-300"
                                                                                            title="View Details"
                                                                                        >
                                                                                            <svg className="w-4 h-4 sm:w-4.5 sm:h-4.5 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                                            </svg>
                                                                                        </button>
                                                                                        <button
                                                                                            onClick={() => {
                                                                                                setSelectedJobForEdit(job);
                                                                                            }}
                                                                                            className="group relative w-8 h-8 sm:w-9 sm:h-9 md:w-10 md:h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 text-white rounded-lg sm:rounded-xl flex items-center justify-center transition-all duration-200 hover:from-emerald-600 hover:to-emerald-700 hover:scale-110 shadow-md hover:shadow-lg border-2 border-emerald-400/50 hover:border-emerald-300"
                                                                                            title="Edit Job"
                                                                                        >
                                                                                            <svg className="w-4 h-4 sm:w-4.5 sm:h-4.5 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                                            </svg>
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
                                                                                            className="group relative w-8 h-8 sm:w-9 sm:h-9 md:w-10 md:h-10 bg-gradient-to-br from-red-500 to-red-600 text-white rounded-lg sm:rounded-xl flex items-center justify-center transition-all duration-200 hover:from-red-600 hover:to-red-700 hover:scale-110 shadow-md hover:shadow-lg border-2 border-red-400/50 hover:border-red-300"
                                                                                            title="Delete Job"
                                                                                        >
                                                                                            <svg className="w-4 h-4 sm:w-4.5 sm:h-4.5 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                                            </svg>
                                                                                        </button>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        );
                                                                    })}
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                            
                                            {/* Footer - Compact */}
                                            <div className="p-2 sm:p-4 md:p-5 border-t-2 border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50">
                                                <div className="flex justify-end">
                                                    <button
                                                        onClick={() => setShowCompletedJobsModal(false)}
                                                        className="px-4 sm:px-6 py-1.5 sm:py-2 md:py-2.5 bg-gradient-to-r from-slate-600 to-slate-700 text-white rounded-lg sm:rounded-xl text-xs sm:text-sm font-black uppercase hover:from-slate-700 hover:to-slate-800 transition-all shadow-lg hover:shadow-xl transform hover:scale-105"
                                                    >
                                                        Close
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                )}
                                
                                {/* Average Details Modal */}
                                {showAvgDetailsModal && (() => {
                                    const totalJobs = filteredCompletedJobs && Array.isArray(filteredCompletedJobs) ? filteredCompletedJobs.length : 0;
                                    const totalAmount = filteredCompletedJobs && Array.isArray(filteredCompletedJobs) ? filteredCompletedJobs.reduce((sum, job) => sum + (parseFloat(job.price) || 0), 0) : 0;
                                    const averageAmount = totalJobs > 0 ? totalAmount / totalJobs : 0;
                                    
                                    return (
                                        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] flex items-center justify-center p-2 sm:p-3 md:p-4" onClick={() => setShowAvgDetailsModal(false)}>
                                            <div className="bg-white rounded-xl sm:rounded-2xl md:rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col" onClick={(e) => e.stopPropagation()}>
                                                {/* Header */}
                                                <div className="p-4 sm:p-5 md:p-6 bg-gradient-to-br from-purple-500 via-pink-500 to-purple-600 text-white">
                                                    <div className="flex items-center justify-between">
                                                        <div>
                                                            <h2 className="text-lg sm:text-xl md:text-2xl font-black uppercase">Average Amount Details</h2>
                                                            <p className="text-xs sm:text-sm opacity-90 mt-1">How the average was calculated</p>
                                                        </div>
                                                        <button
                                                            onClick={() => setShowAvgDetailsModal(false)}
                                                            className="w-8 h-8 sm:w-10 sm:h-10 bg-white/25 hover:bg-white/35 rounded-lg sm:rounded-xl flex items-center justify-center transition-all"
                                                        >
                                                            <svg className="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                {/* Content */}
                                                <div className="flex-1 overflow-y-auto p-4 sm:p-5 md:p-6">
                                                    <div className="space-y-4 sm:space-y-5">
                                                        {/* Calculation Summary */}
                                                        <div className="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl sm:rounded-2xl p-4 sm:p-5 border-2 border-purple-200">
                                                            <h3 className="text-sm sm:text-base font-black text-purple-900 uppercase mb-3 sm:mb-4">Calculation Formula</h3>
                                                            <div className="space-y-2 sm:space-y-3">
                                                                <div className="flex items-center justify-between">
                                                                    <span className="text-xs sm:text-sm font-bold text-slate-700">Total Amount:</span>
                                                                    <span className="text-sm sm:text-base font-black text-purple-600 font-mono">Rs.{totalAmount.toFixed(2)}</span>
                                                                </div>
                                                                <div className="flex items-center justify-between">
                                                                    <span className="text-xs sm:text-sm font-bold text-slate-700">Total Jobs:</span>
                                                                    <span className="text-sm sm:text-base font-black text-purple-600">{totalJobs}</span>
                                                                </div>
                                                                <div className="border-t-2 border-purple-300 pt-2 sm:pt-3 mt-2 sm:mt-3">
                                                                    <div className="flex items-center justify-between">
                                                                        <span className="text-xs sm:text-sm font-bold text-slate-700">Average = Total Amount ÷ Total Jobs</span>
                                                                        <span className="text-base sm:text-lg md:text-xl font-black text-purple-700 font-mono">Rs.{averageAmount.toFixed(2)}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        {/* Jobs Breakdown */}
                                                        {filteredCompletedJobs && Array.isArray(filteredCompletedJobs) && filteredCompletedJobs.length > 0 && (
                                                            <div>
                                                                <h3 className="text-sm sm:text-base font-black text-slate-900 uppercase mb-3 sm:mb-4">Jobs Breakdown</h3>
                                                                <div className="space-y-2 max-h-60 sm:max-h-80 overflow-y-auto">
                                                                    {filteredCompletedJobs.map((job, idx) => (
                                                                        <div key={job.id || idx} className="bg-slate-50 rounded-lg sm:rounded-xl p-3 sm:p-4 border border-slate-200">
                                                                            <div className="flex items-center justify-between">
                                                                                <div className="flex-1 min-w-0">
                                                                                    <p className="text-xs sm:text-sm font-black text-slate-900 truncate">
                                                                                        {job.vehicleNo || job.vehicle_no || 'N/A'} - {job.customerName || job.customer_name || 'N/A'}
                                                                                    </p>
                                                                                    <p className="text-[10px] sm:text-xs text-slate-500 mt-0.5">
                                                                                        {job.serviceName || job.service_name || job.service || 'N/A'}
                                                                                    </p>
                                                                                </div>
                                                                                <div className="ml-3 flex-shrink-0">
                                                                                    <span className="text-sm sm:text-base font-black text-purple-600 font-mono">
                                                                                        Rs.{(parseFloat(job.price) || 0).toFixed(2)}
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    ))}
                                                                </div>
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                                
                                                {/* Footer */}
                                                <div className="p-3 sm:p-4 md:p-5 border-t border-slate-200 bg-slate-50">
                                                    <button
                                                        onClick={() => setShowAvgDetailsModal(false)}
                                                        className="w-full px-4 sm:px-6 py-2 sm:py-2.5 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg sm:rounded-xl text-xs sm:text-sm font-black uppercase hover:from-purple-600 hover:to-pink-600 transition-all shadow-lg"
                                                    >
                                                        Close
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })()}

                                {/* Worker Filter Modal */}
                                {showWorkerFilterModal && (
                                    <div className="fixed inset-0 bg-black/80 backdrop-blur-xl z-[70] flex items-center justify-center p-2 sm:p-3 md:p-4">
                                        <div className="bg-gradient-to-br from-white via-slate-50 to-white rounded-xl sm:rounded-2xl md:rounded-3xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-hidden flex flex-col border-2 sm:border-[4px] border-orange-300/60">
                                            {/* Header */}
                                            <div className="relative p-3 sm:p-4 md:p-5 bg-gradient-to-br from-orange-500 via-amber-500 to-orange-600 text-white overflow-hidden">
                                                <div className="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-16 -mt-16"></div>
                                                <div className="relative z-10 flex items-center justify-between">
                                                    <div>
                                                        <h2 className="text-base sm:text-lg md:text-xl font-black uppercase tracking-wider">Filter by Worker</h2>
                                                        <p className="text-[10px] sm:text-xs text-white/90 mt-0.5">Select a worker to filter jobs</p>
                                                    </div>
                                                    <button
                                                        onClick={() => setShowWorkerFilterModal(false)}
                                                        className="w-8 h-8 sm:w-10 sm:h-10 bg-white/25 hover:bg-white/35 rounded-lg sm:rounded-xl flex items-center justify-center transition-all backdrop-blur-xl border-2 border-white/50 hover:scale-110 hover:rotate-90 shadow-lg"
                                                        aria-label="Close worker filter modal"
                                                    >
                                                        <svg className="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            {/* Content */}
                                            <div className="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6">
                                                <div className="space-y-2 sm:space-y-3">
                                                    {/* All Workers Option */}
                                                    <button
                                                        onClick={() => {
                                                            setSelectedWorkerFilter(null);
                                                            setShowWorkerFilterModal(false);
                                                        }}
                                                        className={`w-full p-3 sm:p-4 rounded-lg sm:rounded-xl border-2 transition-all text-left ${
                                                            !selectedWorkerFilter 
                                                                ? 'bg-gradient-to-br from-orange-500 to-amber-500 text-white border-orange-400 shadow-lg' 
                                                                : 'bg-white hover:bg-slate-50 text-slate-900 border-slate-200 hover:border-orange-300'
                                                        }`}
                                                    >
                                                        <div className="flex items-center justify-between">
                                                            <span className="text-sm sm:text-base font-black">All Workers</span>
                                                            {!selectedWorkerFilter && (
                                                                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                                                </svg>
                                                            )}
                                                        </div>
                                                    </button>
                                                    
                                                    {/* Worker List */}
                                                    {(() => {
                                                        if (!completedJobs || !Array.isArray(completedJobs)) return null;
                                                        const uniqueWorkers = new Set();
                                                        completedJobs.forEach(job => {
                                                            const workerName = job.workerName || job.worker_name || job.worker;
                                                            if (workerName && workerName !== 'N/A' && workerName.trim() !== '') {
                                                                uniqueWorkers.add(workerName.trim());
                                                            }
                                                        });
                                                        const workersArray = Array.from(uniqueWorkers).sort();
                                                        
                                                        if (workersArray.length === 0) {
                                                            return (
                                                                <div className="text-center py-8 text-slate-500">
                                                                    <p className="text-sm">No workers found</p>
                                                                </div>
                                                            );
                                                        }
                                                        
                                                        return workersArray.map((workerName, idx) => (
                                                            <button
                                                                key={idx}
                                                                onClick={() => {
                                                                    setSelectedWorkerFilter(workerName);
                                                                    setShowWorkerFilterModal(false);
                                                                }}
                                                                className={`w-full p-3 sm:p-4 rounded-lg sm:rounded-xl border-2 transition-all text-left ${
                                                                    selectedWorkerFilter === workerName
                                                                        ? 'bg-gradient-to-br from-orange-500 to-amber-500 text-white border-orange-400 shadow-lg' 
                                                                        : 'bg-white hover:bg-slate-50 text-slate-900 border-slate-200 hover:border-orange-300'
                                                                }`}
                                                            >
                                                                <div className="flex items-center justify-between">
                                                                    <div className="flex items-center gap-2 sm:gap-3">
                                                                        <div className="w-8 h-8 sm:w-10 sm:h-10 bg-white/25 backdrop-blur-sm rounded-lg flex items-center justify-center flex-shrink-0">
                                                                            <svg className="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                                            </svg>
                                                                        </div>
                                                                        <span className="text-sm sm:text-base font-black">{workerName}</span>
                                                                    </div>
                                                                    {selectedWorkerFilter === workerName && (
                                                                        <svg className="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                                                        </svg>
                                                                    )}
                                                                </div>
                                                            </button>
                                                        ));
                                                    })()}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                )}
                                
                                {/* Job Detail Modal with Print - Full Screen */}
                                {selectedJobForDetail && (
                                    <div className="fixed inset-0 bg-black/80 backdrop-blur-xl z-[60] flex items-center justify-center p-1 sm:p-2" onClick={() => setSelectedJobForDetail(null)}>
                                        <div className="bg-white rounded-xl sm:rounded-2xl shadow-2xl w-full h-full max-w-[98vw] max-h-[98vh] overflow-hidden flex flex-col" onClick={(e) => e.stopPropagation()} id="jobDetailPrint">
                                            {/* Header */}
                                            <div className="print-header p-3 sm:p-4 md:p-6 border-b border-slate-200 bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                                                <div className="flex items-start sm:items-center justify-between gap-2 sm:gap-4">
                                                    <div className="flex-1 min-w-0">
                                                        <h2 className="text-base sm:text-xl md:text-2xl font-black uppercase tracking-tighter">Job Details</h2>
                                                        <p className="text-[10px] sm:text-xs md:text-sm opacity-90 mt-0.5 sm:mt-1 hidden sm:block">Complete job information with inspections and expenses</p>
                                                        <p className="text-[9px] sm:text-[10px] md:text-xs opacity-80 mt-1 sm:mt-2">
                                                            {selectedJobForDetail.endTime ? new Date(selectedJobForDetail.endTime).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) : ''}
                                                        </p>
                                                    </div>
                                                    <div className="no-print flex items-center gap-1.5 sm:gap-2 md:gap-3 flex-shrink-0">
                                                        <a
                                                            href={`/car-wash/jobs/${selectedJobForDetail.id}`}
                                                            target="_blank"
                                                            className="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 bg-white/20 hover:bg-white/30 rounded-lg sm:rounded-xl text-[9px] sm:text-[10px] md:text-xs font-black uppercase transition-colors backdrop-blur-sm flex items-center gap-1 sm:gap-2"
                                                            title="Open in New Page"
                                                        >
                                                            <svg className="w-3 h-3 sm:w-3.5 sm:h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                            </svg>
                                                            <span className="hidden sm:inline">New Page</span>
                                                        </a>
                                                        <button
                                                            onClick={() => setSelectedJobForDetail(null)}
                                                            className="text-white hover:text-slate-200 transition-colors p-1.5 sm:p-2 rounded-lg hover:bg-white/20"
                                                            aria-label="Close job details"
                                                        >
                                                            <svg className="w-4 h-4 sm:w-5 md:w-6 sm:h-5 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            {/* Job Info - Full Scrollable */}
                                            <div className="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6 bg-gradient-to-b from-slate-50 to-white">
                                                <div className="space-y-3 sm:space-y-4 max-w-7xl mx-auto">
                                                    {/* Customer & Vehicle Info */}
                                                    <div className="print-section grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3 md:gap-4">
                                                        <div className="print-card bg-slate-50 p-3 sm:p-4 rounded-lg sm:rounded-xl border-2 border-slate-200">
                                                            <p className="text-[10px] sm:text-xs font-black text-slate-500 uppercase mb-1 sm:mb-2">Vehicle No</p>
                                                            <p className="text-sm sm:text-base md:text-lg font-black text-slate-900">{selectedJobForDetail.vehicleNo || selectedJobForDetail.vehicle_no || 'N/A'}</p>
                                                        </div>
                                                        <div className="print-card bg-slate-50 p-3 sm:p-4 rounded-lg sm:rounded-xl border-2 border-slate-200">
                                                            <p className="text-[10px] sm:text-xs font-black text-slate-500 uppercase mb-1 sm:mb-2">Customer</p>
                                                            <p className="text-sm sm:text-base md:text-lg font-black text-slate-900">{selectedJobForDetail.customerName || selectedJobForDetail.customer_name || 'N/A'}</p>
                                                        </div>
                                                        <div className="print-card bg-slate-50 p-3 sm:p-4 rounded-lg sm:rounded-xl border-2 border-slate-200">
                                                            <p className="text-[10px] sm:text-xs font-black text-slate-500 uppercase mb-1 sm:mb-2">Mobile</p>
                                                            <p className="text-sm sm:text-base md:text-lg font-black text-slate-900">{selectedJobForDetail.mobile || 'N/A'}</p>
                                                        </div>
                                                    </div>
                                                    
                                                    {/* Service & Worker */}
                                                    <div className="print-section grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 md:gap-4">
                                                        <div className="print-card bg-slate-50 p-3 sm:p-4 rounded-lg sm:rounded-xl border-2 border-slate-200">
                                                            <p className="text-[10px] sm:text-xs font-black text-slate-500 uppercase mb-1 sm:mb-2">Service</p>
                                                            <p className="text-sm sm:text-base md:text-lg font-black text-slate-900">{selectedJobForDetail.serviceName || selectedJobForDetail.service_name || selectedJobForDetail.service || 'N/A'}</p>
                                                        </div>
                                                        <div className="print-card bg-slate-50 p-3 sm:p-4 rounded-lg sm:rounded-xl border-2 border-slate-200">
                                                            <p className="text-[10px] sm:text-xs font-black text-slate-500 uppercase mb-1 sm:mb-2">Worker</p>
                                                            <p className="text-sm sm:text-base md:text-lg font-black text-slate-900">{selectedJobForDetail.workerName || selectedJobForDetail.worker_name || selectedJobForDetail.worker || 'N/A'}</p>
                                                        </div>
                                                    </div>
                                                    
                                                    {/* Time, Amount & Commission */}
                                                    <div className="print-section grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 md:gap-4">
                                                        <div className="print-card bg-slate-50 p-3 sm:p-4 rounded-lg sm:rounded-xl border-2 border-slate-200">
                                                            <p className="text-[10px] sm:text-xs font-black text-slate-500 uppercase mb-1 sm:mb-2">Start Time</p>
                                                            <p className="text-xs sm:text-sm md:text-lg font-black text-slate-900">
                                                                {selectedJobForDetail.startTime ? new Date(selectedJobForDetail.startTime).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A'}
                                                            </p>
                                                        </div>
                                                        <div className="print-card bg-slate-50 p-3 sm:p-4 rounded-lg sm:rounded-xl border-2 border-slate-200">
                                                            <p className="text-[10px] sm:text-xs font-black text-slate-500 uppercase mb-1 sm:mb-2">End Time</p>
                                                            <p className="text-xs sm:text-sm md:text-lg font-black text-slate-900">
                                                                {selectedJobForDetail.endTime ? new Date(selectedJobForDetail.endTime).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A'}
                                                            </p>
                                                        </div>
                                                        <div className="print-amount print-card bg-gradient-to-br from-blue-500 to-indigo-600 p-3 sm:p-4 rounded-lg sm:rounded-xl border-2 border-blue-400">
                                                            <p className="text-[10px] sm:text-xs font-black text-white/90 uppercase mb-1 sm:mb-2">Amount</p>
                                                            <p className="text-lg sm:text-xl md:text-2xl font-black text-white">Rs.{(Number(selectedJobForDetail.price) || 0).toFixed(2)}</p>
                                                        </div>
                                                        <div className="print-commission print-card bg-gradient-to-br from-emerald-500 to-green-600 p-3 sm:p-4 rounded-lg sm:rounded-xl border-2 border-emerald-400">
                                                            <p className="text-[10px] sm:text-xs font-black text-white/90 uppercase mb-1 sm:mb-2">Commission</p>
                                                            {selectedJobForDetail.workerCommission > 0 ? (
                                                                <div>
                                                                    <p className="text-lg sm:text-xl md:text-2xl font-black text-white font-mono">Rs.{(Number(selectedJobForDetail.commissionAmount) || 0).toFixed(2)}</p>
                                                                    <p className="text-[9px] sm:text-[10px] md:text-xs text-white/80 mt-0.5 sm:mt-1">({selectedJobForDetail.workerCommission}%)</p>
                                                                </div>
                                                            ) : (
                                                                <p className="text-sm sm:text-base md:text-lg font-black text-white/70">-</p>
                                                            )}
                                                        </div>
                                                    </div>
                                                    
                                                    {/* Inspection Details */}
                                                    {selectedJobForDetail.inspection && selectedJobForDetail.inspection.inspectionItems && (
                                                        <div className="print-section bg-purple-50 p-3 sm:p-4 md:p-6 rounded-lg sm:rounded-xl border-2 border-purple-200">
                                                            <h3 className="text-sm sm:text-base md:text-lg font-black text-purple-900 uppercase mb-2 sm:mb-3 md:mb-4">Inspection Details</h3>
                                                            <div className="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-2 sm:gap-2.5 md:gap-3">
                                                                {Object.keys(selectedJobForDetail.inspection.inspectionItems).map(itemId => {
                                                                    const item = selectedJobForDetail.inspection.inspectionItems[itemId];
                                                                    const itemNames = {
                                                                        'engine_oil': 'Engine Oil',
                                                                        'wiper_rubber': 'Wiper Rubber',
                                                                        'brake_oil': 'Brake Oil',
                                                                        'air_filter': 'Air Filter',
                                                                        'radiator_water': 'Radiator Water',
                                                                        'shower_water': 'Shower Water',
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
                                                                        <div key={itemId} className="bg-white p-2 sm:p-2.5 md:p-3 rounded-md sm:rounded-lg border border-purple-200">
                                                                            <p className="text-[9px] sm:text-[10px] md:text-xs font-black text-slate-600 uppercase mb-0.5 sm:mb-1">{itemNames[itemId] || itemId}</p>
                                                                            <p className="text-[10px] sm:text-xs md:text-sm font-black text-purple-700">{statusIcons[item.status] || '⚪'} {item.status || 'N/A'}</p>
                                                                        </div>
                                                                    );
                                                                })}
                                                            </div>
                                                        </div>
                                                    )}
                                                    
                                                    {/* Expense Details */}
                                                    {selectedJobForDetail.expense && selectedJobForDetail.expense.expenseItems && selectedJobForDetail.expense.expenseItems.length > 0 && (
                                                        <div className="print-section bg-orange-50 p-3 sm:p-4 md:p-6 rounded-lg sm:rounded-xl border-2 border-orange-200">
                                                            <h3 className="text-sm sm:text-base md:text-lg font-black text-orange-900 uppercase mb-2 sm:mb-3 md:mb-4">Expense Details</h3>
                                                            <div className="space-y-1.5 sm:space-y-2">
                                                                {selectedJobForDetail.expense.expenseItems.map((item, idx) => (
                                                                    <div key={idx} className="bg-white p-2 sm:p-2.5 md:p-3 rounded-md sm:rounded-lg border border-orange-200 flex justify-between items-center gap-2">
                                                                        <div className="flex-1 min-w-0">
                                                                            <p className="text-xs sm:text-sm font-black text-slate-900 truncate">{item.name}</p>
                                                                            <p className="text-[10px] sm:text-xs text-slate-500">Qty: {item.quantity} × Rs.{item.price}</p>
                                                                        </div>
                                                                        <p className="text-xs sm:text-sm font-black text-orange-600 flex-shrink-0">Rs.{(typeof (item.total || (item.quantity * item.price)) === 'number' ? (item.total || (item.quantity * item.price)) : parseFloat(item.total || (item.quantity * item.price) || 0)).toFixed(2)}</p>
                                                                    </div>
                                                                ))}
                                                                <div className="bg-orange-200 p-2 sm:p-2.5 md:p-3 rounded-md sm:rounded-lg border-2 border-orange-300 flex justify-between items-center mt-2 sm:mt-3 md:mt-4">
                                                                    <p className="text-xs sm:text-sm font-black text-orange-900 uppercase">Total Expense</p>
                                                                    <p className="text-sm sm:text-base md:text-lg font-black text-orange-900">Rs.{(Number(selectedJobForDetail.expense?.totalAmount) || 0).toFixed(2)}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    )}
                                                    
                                                    {/* Summary Section */}
                                                    <div className="print-section bg-gradient-to-br from-slate-100 to-slate-200 p-3 sm:p-4 md:p-6 rounded-lg sm:rounded-xl border-2 border-slate-300">
                                                        <h3 className="text-sm sm:text-base md:text-lg font-black text-slate-900 uppercase mb-2 sm:mb-3 md:mb-4">Summary</h3>
                                                        <div className="space-y-2 sm:space-y-2.5 md:space-y-3">
                                                            <div className="flex justify-between items-center bg-white p-2 sm:p-2.5 md:p-3 rounded-md sm:rounded-lg gap-2">
                                                                <p className="text-xs sm:text-sm font-black text-slate-700">Total Amount:</p>
                                                                <p className="text-sm sm:text-base md:text-lg font-black text-blue-600 font-mono">Rs.{(Number(selectedJobForDetail.price) || 0).toFixed(2)}</p>
                                                            </div>
                                                            {selectedJobForDetail.workerCommission > 0 && (
                                                                <div className="flex justify-between items-center bg-white p-2 sm:p-2.5 md:p-3 rounded-md sm:rounded-lg gap-2">
                                                                    <p className="text-xs sm:text-sm font-black text-slate-700">Worker Commission ({selectedJobForDetail.workerCommission}%):</p>
                                                                    <p className="text-sm sm:text-base md:text-lg font-black text-emerald-600 font-mono">Rs.{(Number(selectedJobForDetail.commissionAmount) || 0).toFixed(2)}</p>
                                                                </div>
                                                            )}
                                                            {selectedJobForDetail.expense && selectedJobForDetail.expense.totalAmount > 0 && (
                                                                <div className="flex justify-between items-center bg-white p-2 sm:p-2.5 md:p-3 rounded-md sm:rounded-lg gap-2">
                                                                    <p className="text-xs sm:text-sm font-black text-slate-700">Total Expenses:</p>
                                                                    <p className="text-sm sm:text-base md:text-lg font-black text-orange-600 font-mono">Rs.{(Number(selectedJobForDetail.expense?.totalAmount) || 0).toFixed(2)}</p>
                                                                </div>
                                                            )}
                                                            <div className="flex justify-between items-center bg-gradient-to-r from-blue-500 to-indigo-600 p-3 sm:p-3.5 md:p-4 rounded-md sm:rounded-lg mt-2 sm:mt-3 md:mt-4 gap-2">
                                                                <p className="text-sm sm:text-base font-black text-white uppercase">Net Amount:</p>
                                                                <p className="text-base sm:text-lg md:text-xl font-black text-white font-mono">
                                                                    Rs.{(Number(selectedJobForDetail.price || 0) - Number(selectedJobForDetail.commissionAmount || 0) - Number(selectedJobForDetail.expense?.totalAmount || 0)).toFixed(2)}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    {/* Notes/Comments */}
                                                    {selectedJobForDetail.notes && (
                                                        <div className="print-section bg-slate-50 p-3 sm:p-4 rounded-lg sm:rounded-xl border-2 border-slate-200">
                                                            <p className="text-[10px] sm:text-xs font-black text-slate-500 uppercase mb-1 sm:mb-2">Notes</p>
                                                            <p className="text-xs sm:text-sm text-slate-900">{selectedJobForDetail.notes}</p>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                            
                                            {/* Footer with Print & PDF Buttons */}
                                            <div className="no-print p-3 sm:p-4 md:p-6 border-t border-slate-200 bg-slate-50">
                                                <div className="flex flex-col sm:flex-row gap-2 sm:gap-3 md:gap-4">
                                                    <button
                                                        onClick={() => setSelectedJobForDetail(null)}
                                                        className="flex-1 px-4 sm:px-5 md:px-6 py-2 sm:py-2.5 md:py-3 bg-slate-700 text-white rounded-lg sm:rounded-xl text-xs sm:text-sm font-black uppercase hover:bg-slate-800 transition-colors"
                                                    >
                                                        Close
                                                    </button>
                                                    <button
                                                        onClick={() => {
                                                            window.print();
                                                        }}
                                                        className="flex-1 px-4 sm:px-5 md:px-6 py-2 sm:py-2.5 md:py-3 bg-blue-600 text-white rounded-lg sm:rounded-xl text-xs sm:text-sm font-black uppercase hover:bg-blue-700 transition-colors flex items-center justify-center gap-1.5 sm:gap-2"
                                                    >
                                                        <svg className="w-4 h-4 sm:w-4.5 sm:h-4.5 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                                        className="flex-1 px-4 sm:px-5 md:px-6 py-2 sm:py-2.5 md:py-3 bg-emerald-600 text-white rounded-lg sm:rounded-xl text-xs sm:text-sm font-black uppercase hover:bg-emerald-700 transition-colors flex items-center justify-center gap-1.5 sm:gap-2"
                                                    >
                                                        <svg className="w-4 h-4 sm:w-4.5 sm:h-4.5 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                        <span className="hidden sm:inline">Download PDF</span>
                                                        <span className="sm:hidden">PDF</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                )}
                                
                                {/* Job Edit Modal */}
                                {selectedJobForEdit && (
                                    <div className="fixed inset-0 bg-black/80 backdrop-blur-xl z-[60] flex items-center justify-center p-2 sm:p-3 md:p-4" onClick={() => setSelectedJobForEdit(null)}>
                                        <div className="bg-white rounded-xl sm:rounded-2xl md:rounded-3xl shadow-2xl w-full max-w-2xl max-h-[95vh] sm:max-h-[90vh] overflow-hidden flex flex-col" onClick={(e) => e.stopPropagation()}>
                                            {/* Header */}
                                            <div className="p-3 sm:p-4 md:p-6 border-b border-slate-200 bg-gradient-to-r from-emerald-600 to-green-600 text-white">
                                                <div className="flex items-center justify-between gap-2 sm:gap-4">
                                                    <div className="flex-1 min-w-0">
                                                        <h2 className="text-lg sm:text-xl md:text-2xl font-black uppercase tracking-tighter">Edit Job</h2>
                                                        <p className="text-xs sm:text-sm opacity-90 mt-0.5 sm:mt-1 hidden sm:block">Update job information</p>
                                                    </div>
                                                    <button
                                                        onClick={() => setSelectedJobForEdit(null)}
                                                        className="text-white hover:text-slate-200 transition-colors p-1.5 sm:p-2 rounded-lg hover:bg-white/20 flex-shrink-0"
                                                    >
                                                        <svg className="w-5 h-5 sm:w-5 sm:h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            {/* Edit Form */}
                                            <div className="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6">
                                                <div className="space-y-3 sm:space-y-3.5 md:space-y-4">
                                                    <div>
                                                        <label className="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-1.5 sm:mb-2">Customer Name</label>
                                                        <input
                                                            type="text"
                                                            id="editCustomerName"
                                                            defaultValue={selectedJobForEdit.customerName || selectedJobForEdit.customer_name || ''}
                                                            className="w-full px-3 sm:px-4 py-2 sm:py-2.5 md:py-3 text-sm text-slate-900 border-2 border-slate-300 rounded-lg sm:rounded-xl bg-white focus:border-emerald-500 focus:outline-none"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-1.5 sm:mb-2">Vehicle No</label>
                                                        <input
                                                            type="text"
                                                            id="editVehicleNo"
                                                            defaultValue={selectedJobForEdit.vehicleNo || selectedJobForEdit.vehicle_no || ''}
                                                            className="w-full px-3 sm:px-4 py-2 sm:py-2.5 md:py-3 text-sm text-slate-900 border-2 border-slate-300 rounded-lg sm:rounded-xl bg-white focus:border-emerald-500 focus:outline-none"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-1.5 sm:mb-2">Mobile</label>
                                                        <input
                                                            type="tel"
                                                            id="editMobile"
                                                            defaultValue={selectedJobForEdit.mobile || ''}
                                                            className="w-full px-3 sm:px-4 py-2 sm:py-2.5 md:py-3 text-sm text-slate-900 border-2 border-slate-300 rounded-lg sm:rounded-xl bg-white focus:border-emerald-500 focus:outline-none"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-1.5 sm:mb-2">Service</label>
                                                        <input
                                                            type="text"
                                                            id="editService"
                                                            defaultValue={selectedJobForEdit.serviceName || selectedJobForEdit.service_name || selectedJobForEdit.service || ''}
                                                            className="w-full px-3 sm:px-4 py-2 sm:py-2.5 md:py-3 text-sm text-slate-900 border-2 border-slate-300 rounded-lg sm:rounded-xl bg-white focus:border-emerald-500 focus:outline-none"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-1.5 sm:mb-2">Worker</label>
                                                        <input
                                                            type="text"
                                                            id="editWorker"
                                                            defaultValue={selectedJobForEdit.workerName || selectedJobForEdit.worker_name || selectedJobForEdit.worker || ''}
                                                            className="w-full px-3 sm:px-4 py-2 sm:py-2.5 md:py-3 text-sm text-slate-900 border-2 border-slate-300 rounded-lg sm:rounded-xl bg-white focus:border-emerald-500 focus:outline-none"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-1.5 sm:mb-2">Amount (Rs.)</label>
                                                        <input
                                                            type="number"
                                                            id="editAmount"
                                                            defaultValue={selectedJobForEdit.price || 0}
                                                            min="0"
                                                            step="1"
                                                            className="w-full px-3 sm:px-4 py-2 sm:py-2.5 md:py-3 text-sm text-slate-900 border-2 border-slate-300 rounded-lg sm:rounded-xl bg-white focus:border-emerald-500 focus:outline-none"
                                                        />
                                                    </div>
                                                    <div>
                                                        <label className="text-xs sm:text-sm font-black text-slate-900 uppercase block mb-1.5 sm:mb-2">Notes</label>
                                                        <textarea
                                                            id="editNotes"
                                                            defaultValue={selectedJobForEdit.notes || ''}
                                                            rows="3"
                                                            className="w-full px-3 sm:px-4 py-2 sm:py-2.5 md:py-3 text-sm text-slate-900 border-2 border-slate-300 rounded-lg sm:rounded-xl bg-white focus:border-emerald-500 focus:outline-none resize-none"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            {/* Footer */}
                                            <div className="p-3 sm:p-4 md:p-6 border-t border-slate-200 bg-slate-50">
                                                <div className="flex flex-col sm:flex-row gap-2 sm:gap-3 md:gap-4">
                                                    <button
                                                        onClick={() => setSelectedJobForEdit(null)}
                                                        className="flex-1 px-4 sm:px-5 md:px-6 py-2 sm:py-2.5 md:py-3 bg-slate-700 text-white rounded-lg sm:rounded-xl text-xs sm:text-sm font-black uppercase hover:bg-slate-800 transition-colors"
                                                    >
                                                        Cancel
                                                    </button>
                                                    <button
                                                        onClick={async () => {
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
                                                                    
                                                                    // Reload completed jobs
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
                                                        }}
                                                        className="flex-1 px-4 sm:px-5 md:px-6 py-2 sm:py-2.5 md:py-3 bg-emerald-600 text-white rounded-lg sm:rounded-xl text-xs sm:text-sm font-black uppercase hover:bg-emerald-700 transition-colors"
                                                    >
                                                        Update Job
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                )}
                                
                                <section>
                                    <div className="flex items-center justify-between mb-3 sm:mb-4 px-2">
                                        <h2 className="text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                            Select Service
                                        </h2>
                                    </div>
                                    {categories.length === 0 ? (
                                        // No services in database - show button to add services
                                        <div className="bg-white border-2 border-dashed border-slate-300 p-6 sm:p-8 md:p-10 rounded-2xl sm:rounded-[30px] md:rounded-[40px] text-center">
                                            <div className="mb-4 sm:mb-6">
                                                <svg className="w-12 h-12 sm:w-16 sm:h-16 md:w-20 md:h-20 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                        </svg>
                                                    </div>
                                            <p className="text-sm sm:text-base md:text-lg font-black text-slate-700 uppercase tracking-tight mb-2 sm:mb-3">
                                                No Services Available
                                            </p>
                                            <p className="text-xs sm:text-sm text-slate-500 mb-4 sm:mb-6 font-semibold">
                                                Add your first service to get started
                                            </p>
                                            <button
                                                onClick={() => window.location.href = '{{ route("car.wash.services") }}'}
                                                className="px-6 sm:px-8 md:px-10 py-3 sm:py-3.5 md:py-4 bg-emerald-600 text-white rounded-xl sm:rounded-2xl text-xs sm:text-sm md:text-base font-black uppercase hover:bg-emerald-700 transition-colors shadow-lg hover:shadow-xl transform hover:scale-105"
                                            >
                                                Add Services
                                                </button>
                                        </div>
                                    ) : (
                                        <div className="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 gap-2 sm:gap-3 md:gap-4">
                                            {/* New Categories - Display services from database */}
                                        {categories.map((category) => {
                                            // Use colorValue if available, otherwise get from color class, fallback to blue
                                            const bgColorVal = category.colorValue || (category.color ? getColorValue(category.color) : '#3b82f6');
                                            const styleObj = { backgroundColor: bgColorVal };
                                            return (
                                                <button
                                                    key={category.id}
                                                    type="button"
                                                        className="text-white p-3 sm:p-4 md:p-6 rounded-2xl sm:rounded-[30px] md:rounded-[35px] shadow-xl flex flex-col items-center gap-2 sm:gap-2.5 md:gap-3 cursor-pointer active:scale-95 transition-all hover:shadow-2xl hover:scale-105 w-full border-none outline-none transform"
                                                    style={styleObj}
                                                    onClick={() => {
                                                        setSelectedService(category);
                                                        setSelectedAdditionalPrices(new Set()); // Reset selections
                                                            // Parse base price as number and format to 2 decimal places
                                                            const basePrice = parseFloat(category.basePrice || category.base_price || 0) || 0;
                                                            setFormData({ ...formData, price: basePrice.toFixed(2) });
                                                        setView('entry');
                                                    }}
                                                >
                                                        <div className="bg-white/20 p-2 sm:p-2.5 md:p-3 rounded-xl sm:rounded-2xl backdrop-blur-sm">
                                                        {getIconSVG(category.icon || 'cycle')}
                                                    </div>
                                                    <div className="text-center">
                                                            <p className="font-black text-[9px] sm:text-[10px] md:text-[11px] uppercase tracking-tighter leading-tight">{category.label}</p>
                                                            <p className="text-[8px] sm:text-[9px] md:text-[10px] opacity-90 mt-0.5 sm:mt-1 font-bold">Rs.{category.basePrice || category.base_price || 0}</p>
                                                    </div>
                                                </button>
                                            );
                                        })}
                                    </div>
                                    )}
                                </section>
                                
                                <section>
                                    <h2 className="text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 sm:mb-4 px-2">
                                        Active Jobs
                                    </h2>
                                    {activeJobs.length === 0 ? (
                                        <div className="bg-white border-2 border-dashed border-slate-200 p-6 sm:p-8 md:p-10 rounded-2xl sm:rounded-[30px] md:rounded-[40px] text-center">
                                            <p className="text-slate-300 font-black text-[9px] sm:text-[10px] uppercase tracking-widest">
                                                No active vehicles
                                            </p>
                                        </div>
                                    ) : (
                                        <div className="space-y-2 sm:space-y-2.5 md:space-y-3">
                                            {activeJobs.map((job) => {
                                                const serviceColorVal = allServices.find(s => s.label === job.service)?.colorValue || '#3b82f6';
                                                // Fix timer calculation - handle null/invalid startTime
                                                let timeString = 'N/A';
                                                let timerString = '0:00';
                                                
                                                if (job.startTime) {
                                                    const startTime = new Date(job.startTime);
                                                    if (!isNaN(startTime.getTime())) {
                                                        timeString = startTime.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                                                        
                                                        // Calculate elapsed time from start
                                                        const elapsed = Math.max(0, currentTime.getTime() - startTime.getTime());
                                                        const totalSeconds = Math.floor(elapsed / 1000);
                                                        const hours = Math.floor(totalSeconds / 3600);
                                                        const minutes = Math.floor((totalSeconds % 3600) / 60);
                                                        const seconds = totalSeconds % 60;
                                                        
                                                        // Format timer starting from 0:00
                                                        timerString = hours > 0 
                                                            ? `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
                                                            : `${minutes}:${seconds.toString().padStart(2, '0')}`;
                                                    }
                                                }
                                                
                                                const styleObj = { backgroundColor: serviceColorVal };
                                                
                                                return (
                                                    <div 
                                                        key={job.id}
                                                        className="bg-white p-3 sm:p-4 md:p-6 rounded-2xl sm:rounded-[30px] md:rounded-[35px] shadow-lg border-2 border-slate-100"
                                                    >
                                                        <div className="flex items-center justify-between mb-3 sm:mb-4 gap-2">
                                                            <div className="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                                                                <div 
                                                                    className="w-10 h-10 sm:w-11 sm:h-11 md:w-12 md:h-12 rounded-xl sm:rounded-2xl flex items-center justify-center text-white font-black text-sm sm:text-base md:text-lg shadow-lg flex-shrink-0"
                                                                    style={styleObj}
                                                                >
                                                                    {job.vehicleNo ? job.vehicleNo.charAt(0) : 'V'}
                                                                </div>
                                                                <div className="flex-1 min-w-0">
                                                                    <p className="text-sm sm:text-base md:text-lg font-black text-slate-900 uppercase tracking-tight truncate">
                                                                        {job.vehicleNo}
                                                                    </p>
                                                                    <p className="text-[9px] sm:text-[10px] text-slate-500 font-black mt-0.5 truncate">
                                                                        {job.customerName}
                                                                    </p>
                                                                    {job.mobile && (
                                                                        <p className="text-[9px] sm:text-[10px] text-slate-400 font-mono font-black mt-0.5 truncate">
                                                                            {job.mobile}
                                                                        </p>
                                                                    )}
                                                                </div>
                                                            </div>
                                                            <div className="text-right flex-shrink-0">
                                                                <p className="text-lg sm:text-xl md:text-2xl font-black text-emerald-600 font-mono">
                                                                    Rs.{typeof job.price === 'number' ? job.price.toFixed(0) : job.price}
                                                                </p>
                                                                <div className="mt-0.5 sm:mt-1 flex items-center gap-2 sm:gap-3">
                                                                    <div className="text-left">
                                                                        <p className="text-[7px] sm:text-[8px] text-slate-500 uppercase font-bold">Start Time</p>
                                                                        <p className="text-[9px] sm:text-[10px] text-slate-600 font-mono font-semibold">
                                                                            {timeString}
                                                                        </p>
                                                                    </div>
                                                                    <div className="text-right">
                                                                        <p className="text-[7px] sm:text-[8px] text-slate-400 uppercase font-bold">Timer</p>
                                                                        <p className="text-[9px] sm:text-[10px] text-blue-600 font-mono font-black">
                                                                        {timerString}
                                                                    </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between pt-3 sm:pt-4 border-t border-slate-100 gap-2 sm:gap-2">
                                                            <div className="flex flex-col gap-0.5 order-2 sm:order-1">
                                                                {job.worker && (
                                                                    <span className="text-[10px] sm:text-[11px] text-blue-600 font-bold truncate">
                                                                        👤 {job.worker}
                                                                    </span>
                                                                )}
                                                                <span className="text-[8px] sm:text-[9px] text-slate-500 truncate">
                                                                    {userName || 'Guest'}
                                                                </span>
                                                            </div>
                                                            <div className="flex items-center gap-1.5 sm:gap-2 flex-wrap order-1 sm:order-2 sm:ml-auto justify-end">
                                                                {(() => {
                                                                    const inspectionCompleted = completedInspections.has(job.id);
                                                                    const jobStarted = job.startTime && job.startTime !== null;
                                                                    const shouldBlink = jobStarted && !inspectionCompleted;
                                                                    return (
                                                                <button
                                                                    onClick={() => {
                                                                        setInspectionModalJobId(job.id);
                                                                        setInspectionData({});
                                                                    }}
                                                                            className={`bg-purple-500 text-white px-2 sm:px-2.5 md:px-3 py-1.5 sm:py-1.5 md:py-2 rounded-lg sm:rounded-xl text-[8px] sm:text-[9px] md:text-[10px] font-black uppercase tracking-widest hover:bg-purple-600 transition-colors shadow-md flex-shrink-0 ${shouldBlink ? 'blink-animation' : ''}`}
                                                                >
                                                                            <span className="hidden sm:inline">INSPECTION</span>
                                                                            <span className="sm:hidden">INSP</span>
                                                                </button>
                                                                    );
                                                                })()}
                                                                <button
                                                                    onClick={() => {
                                                                        setExpenseModalJobId(job.id);
                                                                        setExpenseItems({});
                                                                        setCustomExpenses([]);
                                                                    }}
                                                                    className="bg-orange-500 text-white px-2 sm:px-2.5 md:px-3 py-1.5 sm:py-1.5 md:py-2 rounded-lg sm:rounded-xl text-[8px] sm:text-[9px] md:text-[10px] font-black uppercase tracking-widest hover:bg-orange-600 transition-colors shadow-md flex-shrink-0"
                                                                >
                                                                    EXPENSE
                                                                </button>
                                                                <button
                                                                    onClick={() => {
                                                                        setCompleteModalJobId(job.id);
                                                                        setSelectedRating('');
                                                                        setJobComment('');
                                                                    }}
                                                                    className="bg-emerald-600 text-white px-2.5 sm:px-3 md:px-4 py-1.5 sm:py-1.5 md:py-2 rounded-lg sm:rounded-xl text-[8px] sm:text-[9px] md:text-[10px] font-black uppercase tracking-widest hover:bg-emerald-700 transition-colors shadow-md flex-shrink-0"
                                                                >
                                                                    COMPLETE
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    )}
                                </section>
                            </div>
                        )}
                        
                        {view === 'entry' && selectedService && (
                            <div className="bg-white rounded-2xl sm:rounded-[35px] md:rounded-[45px] p-4 sm:p-6 md:p-8 shadow-2xl animate-in slide-in-from-bottom">
                                <div className="flex justify-between items-center mb-4 sm:mb-6 md:mb-8 gap-2">
                                    <button 
                                        onClick={() => setView('dashboard')} 
                                        className="p-2 sm:p-2.5 md:p-3 bg-slate-100 rounded-xl sm:rounded-2xl flex-shrink-0"
                                    >
                                        <svg className="w-4 h-4 sm:w-4.5 sm:h-4.5 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </button>
                                    <h2 className="text-base sm:text-lg md:text-xl font-black uppercase italic tracking-tighter">CHECK-IN</h2>
                                    <div className="w-8 sm:w-9 md:w-10"></div>
                                </div>
                                
                                <form onSubmit={async (e) => {
                                    e.preventDefault();
                                    
                                    // Validate mobile number
                                    if (formData.mobile && formData.mobile.length < 11) {
                                        alert('Please enter complete 11 digit mobile number');
                                        return;
                                    }
                                    
                                    // Validate worker selection - COMPULSORY
                                    console.log('Form submission - formData:', formData);
                                    console.log('Form submission - WORKERS:', WORKERS);
                                    
                                    if (!formData.worker || formData.worker.trim() === '' || !formData.workerId) {
                                        console.error('Worker validation failed:', {
                                            worker: formData.worker,
                                            workerId: formData.workerId,
                                            formData: formData
                                        });
                                        alert('⚠️ COMPULSORY: Please select a worker from the list before starting the job!\n\nSelected: ' + (formData.worker || 'None') + '\nWorker ID: ' + (formData.workerId || 'None'));
                                        // Scroll to worker section if possible
                                        const workerSection = document.querySelector('[class*="ASSIGN WORKER"]')?.closest('div');
                                        if (workerSection) {
                                            workerSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                        }
                                        return;
                                    }
                                    
                                    // Validate that selected worker exists in WORKERS list
                                    const workerNames = WORKERS.map(w => typeof w === 'string' ? w : (w.name || ''));
                                    if (!workerNames.includes(formData.worker.trim())) {
                                        alert('⚠️ ERROR: Selected worker is not valid. Please select a worker from the list!');
                                        // Scroll to worker section if possible
                                        const workerSection = document.querySelector('[class*="ASSIGN WORKER"]')?.closest('div');
                                        if (workerSection) {
                                            workerSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                        }
                                        return;
                                    }
                                    
                                    // Validate customer name OR voice recording
                                    if ((!formData.customerName || formData.customerName.trim() === '') && !audioBlob) {
                                        alert('Please enter customer name OR record voice');
                                        return;
                                    }
                                    
                                    // Convert audio blob to base64 for storage
                                    let audioBase64 = null;
                                    if (audioBlob && audioBlob instanceof Blob) {
                                        audioBase64 = await new Promise((resolve) => {
                                            const reader = new FileReader();
                                            reader.onloadend = () => {
                                                resolve(reader.result);
                                            };
                                            reader.onerror = () => {
                                                resolve(null);
                                            };
                                            reader.readAsDataURL(audioBlob);
                                        });
                                    }
                                    
                                    // Find service_id and worker_id
                                    let serviceId = null;
                                    let workerId = null;
                                    
                                    if (selectedService) {
                                        // Check if selectedService has an id (from database)
                                        if (selectedService.id) {
                                            serviceId = selectedService.id;
                                        } else {
                                            // Try to find in allServices by label
                                            const foundService = allServices.find(s => s.label === selectedService.label);
                                            if (foundService && foundService.id) {
                                                serviceId = foundService.id;
                                            }
                                        }
                                    }
                                    
                                    // Find worker_id - prefer direct ID, fallback to name matching
                                    if (formData.workerId) {
                                        // Direct worker ID from formData
                                        workerId = formData.workerId;
                                    } else if (formData.worker) {
                                        // Fallback: Find worker by name
                                        const foundWorker = WORKERS.find(w => {
                                            const workerName = typeof w === 'string' ? w : (w.name || '');
                                            return workerName === formData.worker.trim();
                                        });
                                        if (foundWorker && typeof foundWorker !== 'string' && foundWorker.id) {
                                            workerId = foundWorker.id;
                                        }
                                    }
                                    
                                    // Validate worker_id is present
                                    if (!workerId) {
                                        alert('⚠️ ERROR: Worker ID not found. Please select a worker again.');
                                        // Scroll to worker section
                                        const workerSection = document.querySelector('[class*="ASSIGN WORKER"]')?.closest('div');
                                        if (workerSection) {
                                            workerSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                        }
                                        return;
                                    }
                                    
                                    // Prepare job data for backend
                                    // Parse price as number (in case it's stored as string from toFixed)
                                    const finalPrice = parseFloat(formData.price || 0) || 0;
                                    const jobData = {
                                        service_id: serviceId,
                                        worker_id: workerId, // Required field - must be present
                                        customer_name: formData.customerName || null,
                                        vehicle_no: formData.vehicleNo || null,
                                        mobile: formData.mobile || null,
                                        service_name: selectedService ? selectedService.label : 'Unknown Service',
                                        price: finalPrice,
                                        additional_prices: selectedService?.additionalPrices || [],
                                        worker_name: formData.worker || null,
                                    };
                                    
                                    // Double check worker_id before sending
                                    if (!jobData.worker_id) {
                                        console.error('Worker ID is missing in jobData:', jobData);
                                        alert('⚠️ ERROR: Worker ID is missing. Please select a worker again.');
                                        return;
                                    }
                                    
                                    try {
                                        // Save job to database
                                        const response = await fetch(API_ROUTES.jobs.store, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': csrfToken,
                                                'Accept': 'application/json',
                                            },
                                            body: JSON.stringify(jobData),
                                        });
                                        
                                        const result = await response.json();
                                        
                                        if (!response.ok) {
                                            throw new Error(result.message || 'Failed to create job');
                                        }
                                        
                                        // Create job object with database ID (match the format from HomeController)
                                        const newJob = {
                                            id: result.job?.id || Date.now().toString(),
                                            serviceId: result.job?.serviceId || serviceId,
                                            workerId: result.job?.workerId || workerId,
                                            customerName: result.job?.customerName || formData.customerName || 'N/A',
                                            vehicleNo: result.job?.vehicleNo || formData.vehicleNo || 'N/A',
                                            mobile: result.job?.mobile || formData.mobile || '',
                                            service: result.job?.serviceName || (selectedService ? selectedService.label : 'Unknown Service'),
                                            price: result.job?.price || formData.price || 0,
                                            worker: result.job?.workerName || formData.worker || '',
                                            startTime: result.job?.startTime || new Date().toISOString(),
                                            status: 'active',
                                            voiceRecording: audioBase64
                                        };
                                        
                                        // Add to active jobs
                                        setActiveJobs(prev => [...prev, newJob]);
                                        
                                        console.log('Job created successfully:', result);
                                    } catch (error) {
                                        console.error('Error creating job:', error);
                                        alert('Error creating job: ' + error.message);
                                        return; // Don't reset form if there was an error
                                    }
                                    
                                    // Reset form and go back to dashboard
                                    setView('dashboard');
                                    setFormData({ customerName: '', vehicleNo: '', mobile: '', worker: '', workerId: null, price: 0 }); // Reset worker to empty - must be selected
                                    setSelectedService(null);
                                    setAudioBlob(null);
                                    if (audioUrl) {
                                        URL.revokeObjectURL(audioUrl);
                                    }
                                    setAudioUrl(null);
                                    setIsRecording(false);
                                    setMediaRecorder(null);
                                    setRecognition(null);
                                }} className="space-y-4 sm:space-y-5 md:space-y-6">
                                    <div className="p-4 sm:p-5 md:p-6 rounded-2xl sm:rounded-[30px] md:rounded-[35px] bg-slate-50 shadow-inner text-center">
                                        <input
                                            required
                                            className="w-full bg-transparent p-1.5 sm:p-2 font-black uppercase text-2xl sm:text-3xl md:text-4xl text-center font-mono outline-none text-slate-900"
                                            placeholder="ABC-123"
                                            value={formData.vehicleNo}
                                            onChange={(e) => {
                                                let value = e.target.value.toUpperCase().replace(/\s/g, '-');
                                                setFormData({ ...formData, vehicleNo: value });
                                            }}
                                        />
                                        <p className="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 mt-1.5 sm:mt-2 tracking-widest">VEHICLE PLATE NUMBER</p>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                if (formData.vehicleNo) {
                                                    alert('Vehicle selected: ' + formData.vehicleNo);
                                                } else {
                                                    alert('Please enter vehicle plate number');
                                                }
                                            }}
                                            className="mt-3 sm:mt-4 bg-blue-600 text-white px-4 sm:px-5 md:px-6 py-2 sm:py-2.5 md:py-3 rounded-xl sm:rounded-2xl font-black uppercase text-[9px] sm:text-[10px] tracking-widest hover:bg-blue-700 transition-colors shadow-lg"
                                        >
                                            SELECT VEHICLE
                                        </button>
                                    </div>
                                    
                                    <div className="space-y-3 sm:space-y-3.5 md:space-y-4">
                                        <div className="bg-slate-50 px-4 sm:px-5 md:px-6 py-3 sm:py-3.5 md:py-4 rounded-xl sm:rounded-2xl">
                                            <label className="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase mb-1.5 sm:mb-2 block">
                                                Customer Name <span className="text-red-500">*</span>
                                            </label>
                                            <div className="flex gap-1.5 sm:gap-2">
                                                <input
                                                    type="text"
                                                    className={`flex-1 bg-transparent font-bold text-xs outline-none border-2 rounded-lg px-2.5 sm:px-3 py-1.5 sm:py-2 ${
                                                        !formData.customerName && !audioBlob 
                                                            ? 'border-red-400 text-red-600' 
                                                            : 'border-slate-300 text-slate-900'
                                                    }`}
                                                    placeholder={audioBlob ? "Voice recorded (or enter name)" : "Enter customer name or use mic"}
                                                    value={formData.customerName}
                                                    onChange={(e) => {
                                                        setFormData({ ...formData, customerName: e.target.value });
                                                        if (e.target.value.trim()) {
                                                            setAudioBlob(null); // Clear voice if name is entered
                                                        }
                                                    }}
                                                />
                                                <button
                                                    type="button"
                                                    onClick={async () => {
                                                        try {
                                                            if (isRecording) {
                                                                // Stop recording
                                                                if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                                                                    mediaRecorder.stop();
                                                                }
                                                                if (recognition) {
                                                                    recognition.stop();
                                                                }
                                                                setIsRecording(false);
                                                                return;
                                                            }
                                                            
                                                            // Check if getUserMedia is available
                                                            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                                                                alert('Microphone access is not available in this browser. Please use a modern browser.');
                                                                return;
                                                            }
                                                            
                                                            // Request microphone permission
                                                            let stream;
                                                            try {
                                                                stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                                                            } catch (permError) {
                                                                if (permError.name === 'NotAllowedError' || permError.name === 'PermissionDeniedError') {
                                                                    alert('Microphone permission denied. Please allow microphone access in your browser settings and try again.');
                                                                    return;
                                                                } else if (permError.name === 'NotFoundError' || permError.name === 'DevicesNotFoundError') {
                                                                    alert('No microphone found. Please connect a microphone and try again.');
                                                                    return;
                                                                } else {
                                                                    alert('Unable to access microphone. Please check your browser settings.');
                                                                    return;
                                                                }
                                                            }
                                                            
                                                            // Start audio recording
                                                            const recorder = new MediaRecorder(stream);
                                                            const audioChunks = [];
                                                            
                                                            recorder.ondataavailable = (event) => {
                                                                if (event.data.size > 0) {
                                                                    audioChunks.push(event.data);
                                                                }
                                                            };
                                                            
                                                            recorder.onstop = () => {
                                                                // Clear the fallback timeout if recording stopped early
                                                                if (window.currentRecordingTimeout) {
                                                                    clearTimeout(window.currentRecordingTimeout);
                                                                }
                                                                
                                                                const blob = new Blob(audioChunks, { type: 'audio/webm' });
                                                                setAudioBlob(blob);
                                                                const url = URL.createObjectURL(blob);
                                                                setAudioUrl(url);
                                                                setIsRecording(false); // Update button state
                                                                if (stream) {
                                                                    stream.getTracks().forEach(track => track.stop());
                                                                }
                                                                
                                                                // On mobile, check if we have transcript after recording stops
                                                                // Sometimes recognition ends before recorder, so check transcript here too
                                                                // Use ref to get the stored transcript (works across closures)
                                                                setTimeout(() => {
                                                                    if (recognitionTranscriptRef.current && recognitionTranscriptRef.current.trim()) {
                                                                        const customerName = recognitionTranscriptRef.current.trim().toUpperCase();
                                                                        console.log('✅ Setting customer name from transcript ref after recording stop:', customerName);
                                                                        setFormData(prev => {
                                                                            // Only update if not already set or if this is better
                                                                            if (!prev.customerName || customerName.length > prev.customerName.length) {
                                                                                return { ...prev, customerName: customerName };
                                                                            }
                                                                            return prev;
                                                                        });
                                                                        // Clear audio since we have text
                                                                        setAudioBlob(null);
                                                                        if (audioUrl) {
                                                                            URL.revokeObjectURL(audioUrl);
                                                                        }
                                                                        setAudioUrl(null);
                                                                    }
                                                                }, 500); // Small delay to ensure recognition has processed
                                                            };
                                                            
                                                            // Clear transcript ref for new recording
                                                            recognitionTranscriptRef.current = '';
                                                            
                                                            // Start speech recognition for text
                                                            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                                                            let recognitionInstance = null;
                                                            
                                                            if (SpeechRecognition) {
                                                                recognitionInstance = new SpeechRecognition();
                                                                recognitionInstance.lang = 'en-US';
                                                                recognitionInstance.continuous = true; // Changed to true for mobile compatibility
                                                                recognitionInstance.interimResults = true; // Changed to true to get results on mobile
                                                                
                                                                recognitionInstance.onresult = (event) => {
                                                                    let interimTranscript = '';
                                                                    let finalTranscript = '';
                                                                    
                                                                    // Process all results (mobile browsers may send multiple results)
                                                                    for (let i = event.resultIndex; i < event.results.length; i++) {
                                                                        const transcript = event.results[i][0].transcript;
                                                                        if (event.results[i].isFinal) {
                                                                            finalTranscript += transcript + ' ';
                                                                            // Store in ref for later use
                                                                            recognitionTranscriptRef.current += transcript + ' ';
                                                                        } else {
                                                                            interimTranscript += transcript;
                                                                        }
                                                                    }
                                                                    
                                                                    // Update formData immediately for mobile browsers (they may not send final results)
                                                                    if (finalTranscript.trim()) {
                                                                        // Final result - use this
                                                                        const customerName = finalTranscript.trim().toUpperCase();
                                                                        console.log('✅ Final transcript:', customerName);
                                                                        setFormData(prev => ({ ...prev, customerName: customerName }));
                                                                        setAudioBlob(null); // Clear audio blob when we have text
                                                                        if (audioUrl) {
                                                                            URL.revokeObjectURL(audioUrl);
                                                                            setAudioUrl(null);
                                                                        }
                                                                    } else if (interimTranscript.trim()) {
                                                                        // Interim result - update for preview (mobile browsers)
                                                                        const customerName = interimTranscript.trim().toUpperCase();
                                                                        console.log('📝 Interim transcript:', customerName);
                                                                        // Store interim in ref too (for mobile fallback)
                                                                        recognitionTranscriptRef.current = customerName;
                                                                        setFormData(prev => ({ ...prev, customerName: customerName }));
                                                                    }
                                                                };
                                                                
                                                                recognitionInstance.onerror = (event) => {
                                                                    console.error('Speech recognition error:', event.error);
                                                                    // On mobile, some errors are not critical (like no-speech)
                                                                    if (event.error === 'no-speech' || event.error === 'audio-capture') {
                                                                        // Don't show alert for these - user might still be speaking
                                                                        console.log('Speech recognition info:', event.error);
                                                                    } else {
                                                                        console.error('Speech recognition error:', event.error);
                                                                    }
                                                                };
                                                                
                                                                recognitionInstance.onend = () => {
                                                                    console.log('🎤 Speech recognition ended');
                                                                    console.log('📝 Stored transcript:', recognitionTranscriptRef.current);
                                                                    
                                                                    // Use stored transcript from ref (works for mobile)
                                                                    if (recognitionTranscriptRef.current && recognitionTranscriptRef.current.trim()) {
                                                                        const customerName = recognitionTranscriptRef.current.trim().toUpperCase();
                                                                        console.log('✅ Setting customer name from transcript ref:', customerName);
                                                                        setFormData(prev => {
                                                                            // Only update if not already set or if this is more complete
                                                                            if (!prev.customerName || customerName.length > prev.customerName.length) {
                                                                                return { ...prev, customerName: customerName };
                                                                            }
                                                                            return prev;
                                                                        });
                                                                        setAudioBlob(null); // Clear audio blob when we have text
                                                                        if (audioUrl) {
                                                                            URL.revokeObjectURL(audioUrl);
                                                                            setAudioUrl(null);
                                                                        }
                                                                    }
                                                                    
                                                                    // Stop audio recording when speech ends
                                                                    if (recorder && recorder.state !== 'inactive') {
                                                                        recorder.stop();
                                                                    }
                                                                    setIsRecording(false);
                                                                    if (stream) {
                                                                        stream.getTracks().forEach(track => track.stop());
                                                                    }
                                                                    // Clear fallback timeout
                                                                    if (window.currentRecordingTimeout) {
                                                                        clearTimeout(window.currentRecordingTimeout);
                                                                    }
                                                                };
                                                                
                                                                // Mobile browsers may need onstart event
                                                                recognitionInstance.onstart = () => {
                                                                    console.log('🎤 Speech recognition started');
                                                                };
                                                                
                                                                // Mobile browsers may restart recognition automatically
                                                                recognitionInstance.onnomatch = () => {
                                                                    console.log('⚠️ No speech match found');
                                                                };
                                                                
                                                                setRecognition(recognitionInstance);
                                                                
                                                                try {
                                                                    recognitionInstance.start();
                                                                    console.log('✅ Speech recognition started successfully');
                                                                } catch (startError) {
                                                                    console.error('Error starting speech recognition:', startError);
                                                                    // On mobile, if recognition fails, still allow audio recording
                                                                    // Don't show alert - just log it, audio will still record
                                                                    console.log('⚠️ Voice recognition not available. Audio will be recorded only.');
                                                                }
                                                            } else {
                                                                console.log('⚠️ Speech recognition not supported - audio recording only');
                                                                // Show helpful message on mobile
                                                                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                                                                if (isMobile) {
                                                                    console.log('📱 Mobile browser detected - speech recognition may not be available');
                                                                }
                                                            }
                                                            
                                                            // Start audio recording
                                                            recorder.start();
                                                            setMediaRecorder(recorder);
                                                            setIsRecording(true);
                                                            
                                                            // Fallback: Auto stop after 8 seconds if speech doesn't end naturally (longer for mobile)
                                                            const recordingTimeout = setTimeout(() => {
                                                                console.log('⏱️ Recording timeout reached');
                                                                if (recorder && recorder.state !== 'inactive') {
                                                                    recorder.stop();
                                                                }
                                                                if (recognitionInstance && recognitionInstance.state !== 'inactive') {
                                                                    try {
                                                                        recognitionInstance.stop();
                                                                    } catch (e) {
                                                                        console.log('Error stopping recognition:', e);
                                                                    }
                                                                }
                                                                setIsRecording(false);
                                                                if (stream) {
                                                                    stream.getTracks().forEach(track => track.stop());
                                                                }
                                                                
                                                                // Final check - if we have any transcript in ref, use it
                                                                if (recognitionTranscriptRef.current && recognitionTranscriptRef.current.trim()) {
                                                                    const customerName = recognitionTranscriptRef.current.trim().toUpperCase();
                                                                    console.log('✅ Final timeout check - setting customer name:', customerName);
                                                                    if (customerName) {
                                                                        setFormData(prev => ({ ...prev, customerName: customerName }));
                                                                        setAudioBlob(null);
                                                                        if (audioUrl) {
                                                                            URL.revokeObjectURL(audioUrl);
                                                                            setAudioUrl(null);
                                                                        }
                                                                    }
                                                                }
                                                            }, 8000); // 8 seconds fallback (longer for mobile)
                                                            
                                                            // Store timeout ID to clear if speech ends early
                                                            window.currentRecordingTimeout = recordingTimeout;
                                                            
                                                        } catch (error) {
                                                            setIsRecording(false);
                                                            // Error already handled in getUserMedia try-catch above
                                                            // Only log unexpected errors
                                                            if (error.name !== 'NotAllowedError' && error.name !== 'PermissionDeniedError') {
                                                                console.error('Unexpected recording error:', error);
                                                                alert('Error starting recording. Please try again.');
                                                            }
                                                        }
                                                    }}
                                                    className={`px-2 sm:px-2.5 md:px-3 py-1.5 sm:py-2 rounded-lg text-xs font-bold transition-all border-2 flex-shrink-0 ${
                                                        isRecording 
                                                            ? 'bg-red-500 border-red-500 text-white animate-pulse' 
                                                            : 'bg-white border-slate-300 text-slate-600 hover:bg-slate-50'
                                                    }`}
                                                >
                                                    {isRecording ? '⏹️' : '🎤'}
                                                </button>
                                            </div>
                                            {!formData.customerName && !audioBlob && (
                                                <p className="text-[8px] sm:text-[9px] text-red-500 font-bold mt-1.5 sm:mt-2">
                                                    ⚠️ Please enter customer name OR record voice (Required)
                                                </p>
                                            )}
                                            {audioBlob && (
                                                <div className="mt-1.5 sm:mt-2">
                                                    <p className="text-[8px] sm:text-[9px] text-green-600 font-bold mb-1">
                                                        ✅ Voice recorded
                                                        {!formData.customerName && (
                                                            <span className="text-orange-600 ml-1">(Text not recognized - please type name manually)</span>
                                                        )}
                                                    </p>
                                                    {audioUrl && (
                                                        <audio controls className="w-full h-7 sm:h-8" src={audioUrl}>
                                                            Your browser does not support audio playback.
                                                        </audio>
                                                    )}
                                                    {!formData.customerName && (
                                                        <p className="text-[8px] sm:text-[9px] text-orange-600 font-semibold mt-1">
                                                            💡 Tip: On mobile, speech recognition may not work. Please type the customer name manually.
                                                        </p>
                                                    )}
                                                </div>
                                            )}
                                        </div>
                                        
                                        <div className="bg-slate-50 px-4 sm:px-5 md:px-6 py-3 sm:py-3.5 md:py-4 rounded-xl sm:rounded-2xl">
                                            <div className="flex items-center gap-1.5 sm:gap-2 mb-1">
                                                <svg className="w-3.5 h-3.5 sm:w-4 sm:h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                </svg>
                                                <span className="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase">Mobile Number</span>
                                            </div>
                                            <input
                                                type="tel"
                                                className={`w-full bg-transparent font-bold text-xs outline-none border-b-2 ${
                                                    formData.mobile && formData.mobile.length < 11 
                                                        ? 'text-red-600 border-red-400' 
                                                        : 'text-slate-900 border-slate-300'
                                                }`}
                                                placeholder="Enter mobile number"
                                                value={formData.mobile}
                                                maxLength={11}
                                                onChange={(e) => {
                                                    // Allow only numbers
                                                    const value = e.target.value.replace(/\D/g, '');
                                                    // Limit to 11 digits
                                                    const limitedValue = value.slice(0, 11);
                                                    setFormData({ ...formData, mobile: limitedValue });
                                                }}
                                                onBlur={(e) => {
                                                    if (e.target.value && e.target.value.length < 11) {
                                                        alert('Please enter complete 11 digit mobile number');
                                                    }
                                                }}
                                            />
                                            {formData.mobile && formData.mobile.length < 11 && formData.mobile.length > 0 && (
                                                <p className="text-[8px] sm:text-[9px] text-red-500 font-bold mt-1">
                                                    ⚠️ Please enter complete 11 digit number
                                                </p>
                                            )}
                                        </div>
                                        
                                        <div>
                                            <p className="text-[9px] sm:text-[10px] font-black uppercase text-slate-400 ml-1 sm:ml-2 mb-2 sm:mb-3 tracking-widest">
                                                ASSIGN WORKER 
                                                <span className="text-red-500 text-base sm:text-lg ml-0.5 sm:ml-1">*</span>
                                                <span className="text-red-500 ml-1.5 sm:ml-2 text-[8px] sm:text-[9px]">(COMPULSORY)</span>
                                            </p>
                                            {WORKERS.length === 0 ? (
                                                <div className="bg-red-50 border-2 border-red-200 px-4 sm:px-5 md:px-6 py-3 sm:py-4 rounded-xl sm:rounded-2xl text-center">
                                                    <p className="text-xs text-red-600 font-bold">⚠️ No workers available</p>
                                                    <p className="text-[9px] sm:text-[10px] text-red-400 mt-1">Add workers from Settings → All Staff</p>
                                                </div>
                                            ) : (
                                                <div>
                                                    {!formData.worker && (
                                                        <div className="mb-2 sm:mb-3 bg-red-100 border-2 border-red-500 rounded-lg sm:rounded-xl p-3 sm:p-4 animate-pulse">
                                                            <p className="text-[10px] sm:text-[11px] text-red-700 font-black uppercase tracking-wide flex items-center gap-1.5 sm:gap-2">
                                                                <svg className="w-4 h-4 sm:w-5 sm:h-5 animate-bounce flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                                </svg>
                                                                ⚠️ COMPULSORY: Please Select a Worker
                                                            </p>
                                                            <p className="text-[8px] sm:text-[9px] text-red-600 font-semibold mt-0.5 sm:mt-1 ml-5 sm:ml-7">You must select a worker before starting the job</p>
                                                        </div>
                                                    )}
                                                    <div className={`grid grid-cols-2 gap-1.5 sm:gap-2 ${!formData.worker ? 'animate-pulse' : ''}`}>
                                                        {WORKERS.map((worker, index) => {
                                                            const workerName = typeof worker === 'string' ? worker : (worker.name || 'Unknown');
                                                            const workerKey = typeof worker === 'string' ? worker : (worker.id || index);
                                                            const workerId = typeof worker === 'string' ? null : (worker.id || null);
                                                            const isSelected = formData.worker === workerName || formData.workerId === workerId;
                                                            
                                                            return (
                                                                <button
                                                                    key={workerKey}
                                                                    type="button"
                                                                    onClick={(e) => {
                                                                        e.preventDefault();
                                                                        e.stopPropagation();
                                                                        
                                                                        console.log('🔵 Worker button clicked:', { 
                                                                            workerName, 
                                                                            workerId, 
                                                                            worker, 
                                                                            workerHasId: !!(worker && worker.id),
                                                                            currentFormData: formData 
                                                                        });
                                                                        
                                                                        if (!workerId) {
                                                                            console.error('❌ Worker ID is null for worker:', worker);
                                                                            alert('Error: Worker ID not found. Please refresh the page.\n\nWorker: ' + workerName + '\nWorker Object: ' + JSON.stringify(worker));
                                                                            return;
                                                                        }
                                                                        
                                                                        // Use functional update to ensure we get latest state
                                                                        setFormData(prevFormData => {
                                                                            const updatedData = { 
                                                                                ...prevFormData, 
                                                                                worker: workerName, 
                                                                                workerId: workerId 
                                                                            };
                                                                            console.log('✅ Updating formData:', {
                                                                                previous: prevFormData,
                                                                                updated: updatedData
                                                                            });
                                                                            return updatedData;
                                                                        });
                                                                    }}
                                                                    className={`p-3 sm:p-3.5 md:p-4 rounded-xl sm:rounded-2xl text-[8px] sm:text-[9px] font-black uppercase border-2 transition-all cursor-pointer ${
                                                                        isSelected
                                                                            ? 'bg-slate-900 border-slate-900 text-white shadow-lg scale-105'
                                                                            : !formData.worker
                                                                                ? 'bg-red-50 border-red-500 text-red-700 hover:border-red-600 hover:bg-red-100 shadow-md animate-pulse'
                                                                                : 'bg-white border-slate-100 text-slate-400 hover:border-slate-200 hover:bg-slate-50'
                                                                    }`}
                                                                    onMouseDown={(e) => e.stopPropagation()}
                                                                    onMouseUp={(e) => e.stopPropagation()}
                                                                >
                                                                    {workerName}
                                                                </button>
                                                            );
                                                        })}
                                                    </div>
                                                </div>
                                            )}
                                        </div>
                                        
                                        {/* Service Prices Section - Advanced Box Style */}
                                        {selectedService && (
                                            <div className="mt-4 sm:mt-5 md:mt-6 bg-gradient-to-br from-white via-slate-50 to-slate-100 px-4 sm:px-5 md:px-6 py-4 sm:py-4.5 md:py-5 rounded-2xl sm:rounded-[25px] md:rounded-3xl border-2 border-slate-300 shadow-xl">
                                                {/* Header with Icon */}
                                                <div className="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4 md:mb-5 pb-3 sm:pb-3.5 md:pb-4 border-b-2 border-slate-200">
                                                    <div className="w-10 h-10 sm:w-11 sm:h-11 md:w-12 md:h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
                                                        <svg className="w-5 h-5 sm:w-5.5 sm:h-5.5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    </div>
                                                    <div className="flex-1 min-w-0">
                                                        <p className="text-[10px] sm:text-xs font-black uppercase text-slate-800 tracking-widest">SERVICE PRICES</p>
                                                        <p className="text-[8px] sm:text-[9px] text-slate-500 font-semibold mt-0.5">Select services to include</p>
                                                    </div>
                                                </div>
                                                
                                                {/* Base Price - Always Included */}
                                                <div className="mb-3 sm:mb-3.5 md:mb-4 bg-white rounded-xl sm:rounded-2xl p-3 sm:p-3.5 md:p-4 border-2 border-green-200 shadow-md">
                                                    <div className="flex items-center justify-between gap-2 sm:gap-3">
                                                        <div className="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                                                            <div className="w-7 h-7 sm:w-8 sm:h-8 bg-green-500 rounded-lg sm:rounded-xl flex items-center justify-center shadow-sm flex-shrink-0">
                                                                <svg className="w-4 h-4 sm:w-4.5 sm:h-4.5 md:w-5 md:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                                                                </svg>
                                                            </div>
                                                            <div className="flex-1 min-w-0">
                                                                <span className="text-xs sm:text-sm font-black text-slate-800 block">Base Price</span>
                                                                <span className="text-[8px] sm:text-[9px] text-slate-500 font-semibold">Always included</span>
                                                            </div>
                                                        </div>
                                                        <span className="text-base sm:text-lg md:text-xl font-black text-slate-900 font-mono bg-slate-50 px-3 sm:px-3.5 md:px-4 py-1.5 sm:py-2 rounded-lg sm:rounded-xl border-2 border-slate-200 flex-shrink-0">
                                                            Rs. {selectedService.basePrice || selectedService.base_price || 0}
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                {/* Additional Prices (Selectable) */}
                                                {selectedService.additionalPrices && selectedService.additionalPrices.length > 0 && (
                                                    <div className="space-y-2 sm:space-y-2.5 md:space-y-3 mb-3 sm:mb-3.5 md:mb-4">
                                                        <p className="text-[9px] sm:text-[10px] font-black text-slate-700 uppercase mb-2 sm:mb-2.5 md:mb-3 tracking-wider flex items-center gap-1.5 sm:gap-2">
                                                            <span className="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-blue-600 rounded-full flex-shrink-0"></span>
                                                            Additional Services (Optional)
                                                        </p>
                                                        {selectedService.additionalPrices.map((additionalPrice, index) => {
                                                            const isSelected = selectedAdditionalPrices.has(index);
                                                            return (
                                                                <label 
                                                                    key={index} 
                                                                    className={`flex items-center justify-between p-3 sm:p-3.5 md:p-4 rounded-xl sm:rounded-2xl cursor-pointer transition-all transform ${
                                                                        isSelected 
                                                                            ? 'bg-gradient-to-r from-blue-50 via-blue-100 to-blue-50 border-2 border-blue-500 shadow-lg scale-[1.02]' 
                                                                            : 'bg-white border-2 border-slate-200 hover:border-blue-300 hover:shadow-md'
                                                                    }`}
                                                                >
                                                                    <div className="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                                                                        <div className={`relative w-5 h-5 sm:w-6 sm:h-6 rounded-md sm:rounded-lg border-2 flex items-center justify-center transition-all shadow-sm flex-shrink-0 ${
                                                                            isSelected 
                                                                                ? 'bg-blue-600 border-blue-600' 
                                                                                : 'bg-white border-slate-300'
                                                                        }`}>
                                                                            {isSelected && (
                                                                                <svg className="w-3.5 h-3.5 sm:w-4 sm:h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                                                                                </svg>
                                                                            )}
                                                                        </div>
                                                                        <div className="flex-1 min-w-0">
                                                                            <span className={`text-xs sm:text-sm font-black block truncate ${isSelected ? 'text-blue-900' : 'text-slate-800'}`}>
                                                                                {additionalPrice.label || `Additional ${index + 1}`}
                                                                            </span>
                                                                            {isSelected && (
                                                                                <span className="text-[8px] sm:text-[9px] text-blue-600 font-bold mt-0.5 block">Selected</span>
                                                                            )}
                                                                        </div>
                                                                    </div>
                                                                    <span className={`text-sm sm:text-base font-black font-mono px-2.5 sm:px-3 md:px-4 py-1.5 sm:py-2 rounded-lg sm:rounded-xl flex-shrink-0 ${
                                                                        isSelected 
                                                                            ? 'bg-blue-600 text-white shadow-md' 
                                                                            : 'bg-slate-100 text-slate-800'
                                                                    }`}>
                                                                        Rs. {additionalPrice.amount || 0}
                                                                    </span>
                                                                    <input
                                                                        type="checkbox"
                                                                        checked={isSelected}
                                                                        onChange={(e) => {
                                                                            const newSelected = new Set(selectedAdditionalPrices);
                                                                            if (e.target.checked) {
                                                                                newSelected.add(index);
                                                                            } else {
                                                                                newSelected.delete(index);
                                                                            }
                                                                            setSelectedAdditionalPrices(newSelected);
                                                                            
                                                                            // Update total price - parse all values as numbers
                                                                            let total = parseFloat(selectedService.basePrice || selectedService.base_price || 0) || 0;
                                                                            newSelected.forEach(selectedIndex => {
                                                                                const ap = selectedService.additionalPrices[selectedIndex];
                                                                                if (ap && ap.amount) {
                                                                                    total += parseFloat(ap.amount || 0) || 0;
                                                                                }
                                                                            });
                                                                            setFormData({ ...formData, price: total.toFixed(2) });
                                                                        }}
                                                                        className="sr-only"
                                                                    />
                                                                </label>
                                                            );
                                                        })}
                                                    </div>
                                                )}
                                            </div>
                                        )}
                                    </div>
                                    
                                    <div className="bg-slate-900 p-4 sm:p-6 md:p-8 rounded-2xl sm:rounded-[30px] md:rounded-[40px] text-white flex flex-col sm:flex-row justify-between items-center gap-3 sm:gap-4 md:gap-0 mt-6 sm:mt-7 md:mt-8">
                                        <div className="flex-1 min-w-0">
                                            <p className="text-[9px] sm:text-[10px] font-bold opacity-50 tracking-widest">PRICE</p>
                                            <p className="text-2xl sm:text-3xl md:text-4xl font-black font-mono text-emerald-400">Rs.{formData.price}</p>
                                        </div>
                                        <button 
                                            type="submit" 
                                            disabled={!formData.worker || formData.worker.trim() === ''}
                                            title={!formData.worker || formData.worker.trim() === '' ? 'Please select a worker first' : 'Start the job'}
                                            className={`w-full sm:w-auto px-6 sm:px-7 md:px-8 py-3 sm:py-4 md:py-5 rounded-xl sm:rounded-[18px] md:rounded-[22px] font-black uppercase text-[9px] sm:text-[10px] tracking-widest transition-all relative ${
                                                !formData.worker || formData.worker.trim() === ''
                                                    ? 'bg-slate-600 text-slate-300 cursor-not-allowed opacity-50'
                                                    : 'bg-blue-600 text-white hover:bg-blue-700 shadow-lg hover:shadow-xl'
                                            }`}
                                        >
                                            <span className="hidden sm:inline">{!formData.worker || formData.worker.trim() === '' ? 'SELECT WORKER FIRST' : 'START JOB'}</span>
                                            <span className="sm:hidden">{!formData.worker || formData.worker.trim() === '' ? 'SELECT WORKER' : 'START JOB'}</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        )}
                    </main>
                    
                    {/* Bottom Nav */}
                    <nav className="fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-2xl border-t border-slate-100 flex justify-around p-3 sm:p-4 md:p-5 z-40 rounded-t-2xl sm:rounded-t-[30px] md:rounded-t-[40px] shadow-2xl">
                        <button 
                            onClick={() => setView('dashboard')} 
                            className={`p-3 sm:p-3.5 md:p-4 rounded-2xl sm:rounded-[25px] md:rounded-3xl transition-all ${view === 'dashboard' ? 'bg-slate-950 text-white shadow-xl -translate-y-1 sm:-translate-y-2' : 'text-slate-300'}`}
                        >
                            <svg className="w-5 h-5 sm:w-5.5 sm:h-5.5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </button>
                        <button className="p-3 sm:p-3.5 md:p-4 rounded-2xl sm:rounded-[25px] md:rounded-3xl text-slate-300">
                            <svg className="w-5 h-5 sm:w-5.5 sm:h-5.5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </button>
                    </nav>
                </div>
            );
        };
        
        // Wait for DOM and Babel to be ready, then render React app
        function initApp() {
        // Check if React 18 createRoot is available, otherwise use legacy render
            try {
        const rootElement = document.getElementById('root');
        if (rootElement) {
                    if (typeof React === 'undefined' || typeof ReactDOM === 'undefined') {
                        console.error('React or ReactDOM not loaded!');
                        rootElement.innerHTML = '<div style="padding: 20px; text-align: center; color: red;"><h2>Error: React libraries not loaded</h2><p>Please refresh the page.</p></div>';
                        return;
                    }
                    
            if (ReactDOM.createRoot) {
                // React 18+
                const root = ReactDOM.createRoot(rootElement);
                root.render(<App />);
            } else {
                // React 17 and below (legacy)
                ReactDOM.render(<App />, rootElement);
            }
        } else {
            console.error('Root element not found!');
                }
            } catch (error) {
                console.error('Error rendering React app:', error);
                const rootElement = document.getElementById('root');
                if (rootElement) {
                    rootElement.innerHTML = '<div style="padding: 20px; text-align: center; color: red;"><h2>Error Loading Application</h2><p>' + (error.message || 'Unknown error') + '</p><p>Please refresh the page.</p></div>';
                }
            }
        }
        
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initApp);
        } else {
            // DOM is already ready
            setTimeout(initApp, 100); // Small delay to ensure Babel is ready
        }
    </script>
    
    <!-- Settings Dropdown JavaScript -->
    <script>
        // Function to open category modal
        function openCategoryModal() {
            const categoryModal = document.getElementById('categoryModalOverlay');
            if (categoryModal) {
                categoryModal.classList.add('show');
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
                console.log('Category modal opened');
                
                // Clear additional prices container if not editing
                const isEditing = categoryModal.getAttribute('data-editing-id');
                if (!isEditing) {
                    const additionalPricesContainer = document.getElementById('additionalPricesContainer');
                    if (additionalPricesContainer) {
                        additionalPricesContainer.innerHTML = '';
                        additionalPricesContainer.style.display = 'none';
                        // Reset counter
                        window.additionalPriceCounter = 0;
                    }
                }
                
                // Set button text based on edit mode (with delay to ensure DOM is ready)
                // Use multiple attempts to ensure button text is set correctly
                const updateButtonText = () => {
                    const submitBtn = document.getElementById('categorySubmitBtn');
                    const isEditing = categoryModal.getAttribute('data-editing-id');
                    if (submitBtn) {
                        if (isEditing) {
                            submitBtn.textContent = 'UPDATE';
                            console.log('Button text set to UPDATE (editing mode), ID:', isEditing);
                        } else {
                            submitBtn.textContent = 'SAVE';
                            console.log('Button text set to SAVE (new service)');
                        }
                    } else {
                        console.warn('categorySubmitBtn not found, retrying...');
                    }
                };
                
                // Try immediately
                updateButtonText();
                
                // Try after short delay
                setTimeout(updateButtonText, 100);
                
                // Try after longer delay (in case edit handler runs after this)
                setTimeout(updateButtonText, 400);
                
                // Theme selection uses event delegation, works automatically
                
                return true;
            } else {
                console.error('Category modal not found!');
                return false;
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const settingsDropdown = document.getElementById('settingsDropdown');
            
            // Handle Add New Category button click - Redirect to services page
            document.addEventListener('click', function(e) {
                const addNewCategoryBtn = document.getElementById('addNewCategoryBtn');
                if (addNewCategoryBtn && (e.target === addNewCategoryBtn || addNewCategoryBtn.contains(e.target))) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Add New Service button clicked - Redirecting to services page');
                    
                    // Close dropdown first
                    if (settingsDropdown) {
                        settingsDropdown.classList.remove('show');
                    }
                    
                    // Redirect to services page
                    window.location.href = '{{ route("car.wash.services") }}';
                    return false;
                }
            }, true); // Capture phase - fires before bubble phase
            
            // Also handle direct click on settings button to open modal (backup method)
            // This ensures modal opens even if dropdown doesn't work
            setTimeout(function() {
                const reactSettingsBtn = document.getElementById('reactSettingsBtn');
                if (reactSettingsBtn) {
                    // Add a double-click handler to directly open modal
                    reactSettingsBtn.addEventListener('dblclick', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        openCategoryModal();
                        if (settingsDropdown) {
                            settingsDropdown.classList.remove('show');
                        }
                    });
                }
            }, 1000);
            
            // Close dropdown when clicking outside (but not on modal buttons or settings button)
            document.addEventListener('click', function(e) {
                // Only close if dropdown is actually open
                if (!settingsDropdown || !settingsDropdown.classList.contains('show')) {
                    return;
                }
                
                // Check if click is on settings button or inside dropdown
                const isSettingsButton = e.target.id === 'reactSettingsBtn' ||
                                       e.target.closest('#reactSettingsBtn') ||
                                       e.target.closest('button[title="Settings"]') ||
                                       (e.target.closest('button') && e.target.closest('button').getAttribute('title') === 'Settings');
                
                // Check if click is inside dropdown or on dropdown items
                const isInsideDropdown = e.target.closest('.settings-menu-container') ||
                                      e.target.closest('#settingsDropdown') ||
                                      e.target.id === 'addNewCategoryBtn' ||
                                      e.target.closest('#addNewCategoryBtn') ||
                                      e.target.id === 'addNewWorkerBtn' ||
                                      e.target.closest('#addNewWorkerBtn') ||
                                      e.target.id === 'addExpenseBtn' ||
                                      e.target.closest('#addExpenseBtn');
                
                // Only close if click is outside dropdown and not on settings button
                if (!isInsideDropdown && !isSettingsButton) {
                    settingsDropdown.classList.remove('show');
                }
            });
                
                // Handle Add New Worker button click
                const addNewWorkerBtn = document.getElementById('addNewWorkerBtn');
                if (addNewWorkerBtn) {
                    addNewWorkerBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        settingsDropdown.classList.remove('show');
                        
                        // Reset worker modal for new worker
                        closeWorkerModal();
                        
                        // Show worker modal
                        setTimeout(() => {
                            const workerModal = document.getElementById('workerModalOverlay');
                            if (workerModal) {
                                // Reset modal title
                                const workerModalTitle = workerModal.querySelector('.category-modal-title');
                                const workerModalSubtitle = workerModal.querySelector('.category-modal-subtitle');
                                if (workerModalTitle) workerModalTitle.textContent = 'NEW WORKER';
                                if (workerModalSubtitle) workerModalSubtitle.textContent = 'ADD A NEW WORKER TO STATION';
                                
                                // Hide delete button
                                const deleteWorkerBtn = document.getElementById('deleteWorkerBtn');
                                if (deleteWorkerBtn) deleteWorkerBtn.style.display = 'none';
                                
                                // Remove editing ID if any
                                workerModal.removeAttribute('data-editing-worker-id');
                                
                                // Show modal
                                workerModal.style.display = 'block';
                                workerModal.classList.add('show');
                                document.body.style.overflow = 'hidden';
                            }
                        }, 100);
                    });
                }
                
                // Handle Add Expense button click
                const addExpenseBtn = document.getElementById('addExpenseBtn');
                if (addExpenseBtn) {
                    addExpenseBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        settingsDropdown.classList.remove('show');
                        // Add expense functionality - can be implemented later
                        alert('Add Expense feature coming soon!');
                    });
                }
                
            // Worker Details Modal handlers
            const workerDetailsModalOverlay = document.getElementById('workerDetailsModalOverlay');
            const workerDetailsModalClose = document.getElementById('workerDetailsModalClose');
            const workerDetailsList = document.getElementById('workerDetailsList');
            
            function closeWorkerDetailsModal() {
                if (workerDetailsModalOverlay) {
                    workerDetailsModalOverlay.classList.remove('show');
                }
            }
            
            function loadWorkersList() {
                if (!workerDetailsList) return;
                
                // Fetch workers from backend API
                fetch(API_ROUTES.workers.index, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.workers) {
                        const workers = data.workers;
                        
                        // Workers saved to database via API, no localStorage needed
                        
                        if (workers.length === 0) {
                            workerDetailsList.innerHTML = '<div style="text-align: center; padding: 40px; color: #94a3b8;"><p style="font-weight: 600; font-size: 16px;">No workers found</p><p style="font-size: 14px; margin-top: 8px;">Add a new worker to get started</p></div>';
                            return;
                        }
                        
                        workerDetailsList.innerHTML = workers.map(worker => {
                            const imagesHtml = `
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 12px;">
                                    ${worker.idCardFront ? `<div><p style="font-size: 10px; font-weight: 700; color: #64748b; margin-bottom: 6px; text-transform: uppercase;">ID Card Front</p><img src="${worker.idCardFront}" style="width: 100%; border-radius: 12px; border: 2px solid #e2e8f0;" /></div>` : ''}
                                    ${worker.idCardBack ? `<div><p style="font-size: 10px; font-weight: 700; color: #64748b; margin-bottom: 6px; text-transform: uppercase;">ID Card Back</p><img src="${worker.idCardBack}" style="width: 100%; border-radius: 12px; border: 2px solid #e2e8f0;" /></div>` : ''}
                                    ${worker.fatherCardFront ? `<div><p style="font-size: 10px; font-weight: 700; color: #64748b; margin-bottom: 6px; text-transform: uppercase;">Father Card Front</p><img src="${worker.fatherCardFront}" style="width: 100%; border-radius: 12px; border: 2px solid #e2e8f0;" /></div>` : ''}
                                    ${worker.fatherCardBack ? `<div><p style="font-size: 10px; font-weight: 700; color: #64748b; margin-bottom: 6px; text-transform: uppercase;">Father Card Back</p><img src="${worker.fatherCardBack}" style="width: 100%; border-radius: 12px; border: 2px solid #e2e8f0;" /></div>` : ''}
                                </div>
                            `;
                            
                            return `
                                <div style="background: #f8fafc; border-radius: 24px; padding: 24px; border: 2px solid #e2e8f0; margin-bottom: 16px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                                        <div style="flex: 1;">
                                            <h3 style="font-size: 18px; font-weight: 800; color: #1e293b; margin: 0 0 8px 0;">${worker.name || 'N/A'}</h3>
                                            <p style="font-size: 12px; color: #64748b; margin: 0;">${worker.mobile || 'N/A'}</p>
                                        </div>
                                        <div style="display: flex; gap: 8px;">
                                            <button 
                                                type="button" 
                                                class="edit-worker-btn" 
                                                data-worker-id="${worker.id}"
                                                style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 12px; transition: all 0.2s;"
                                                onmouseover="this.style.background='#2563eb'"
                                                onmouseout="this.style.background='#3b82f6'"
                                            >
                                                EDIT
                                            </button>
                                            <button 
                                                type="button" 
                                                class="delete-worker-btn" 
                                                data-worker-id="${worker.id}"
                                                data-worker-name="${worker.name}"
                                                style="padding: 8px 16px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 12px; transition: all 0.2s;"
                                                onmouseover="this.style.background='#dc2626'"
                                                onmouseout="this.style.background='#ef4444'"
                                            >
                                                DELETE
                                            </button>
                                        </div>
                                    </div>
                                    <div style="display: grid; gap: 16px;">
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                            <div>
                                                <p style="font-size: 10px; font-weight: 800; color: #64748b; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.1em;">Father Name</p>
                                                <p style="font-size: 16px; font-weight: 700; color: #1e293b;">${worker.fatherName || 'N/A'}</p>
                                            </div>
                                            <div>
                                                <p style="font-size: 10px; font-weight: 800; color: #64748b; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.1em;">Commission</p>
                                                <p style="font-size: 16px; font-weight: 700; color: #1e293b;">${worker.commission || 0}%</p>
                                            </div>
                                        </div>
                                        <div>
                                            <p style="font-size: 10px; font-weight: 800; color: #64748b; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.1em;">Location / Address</p>
                                            <p style="font-size: 14px; font-weight: 600; color: #475569; line-height: 1.6;">${worker.location || 'N/A'}</p>
                                        </div>
                                        ${imagesHtml}
                                    </div>
                                </div>
                            `;
                        }).join('');
                        
                        // Add event listeners for edit and delete buttons
                        attachWorkerActionListeners();
                    } else {
                        // No workers found
                        workerDetailsList.innerHTML = '<div style="text-align: center; padding: 40px; color: #94a3b8;"><p style="font-weight: 600; font-size: 16px;">No workers found</p><p style="font-size: 14px; margin-top: 8px;">Add a new worker to get started</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading workers from API:', error);
                    workerDetailsList.innerHTML = '<div style="text-align: center; padding: 40px; color: #94a3b8;"><p style="font-weight: 600; font-size: 16px;">Error loading workers</p><p style="font-size: 14px; margin-top: 8px;">Please try again</p></div>';
                });
            }
            
            // Function to attach event listeners for edit and delete buttons
            function attachWorkerActionListeners() {
                // Edit button handlers
                document.querySelectorAll('.edit-worker-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const workerId = this.getAttribute('data-worker-id');
                        editWorker(workerId);
                    });
                });
                
                // Delete button handlers
                document.querySelectorAll('.delete-worker-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const workerId = this.getAttribute('data-worker-id');
                        const workerName = this.getAttribute('data-worker-name');
                        deleteWorker(workerId, workerName);
                    });
                });
            }
            
            // Function to open worker modal in edit mode
            function editWorker(workerId) {
                // Fetch worker data from backend
                fetch(API_ROUTES.workers.index, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    console.log('Fetching worker data, response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Worker data received:', data);
                    if (data.success && data.workers) {
                        const worker = data.workers.find(w => w.id == workerId || w.id === workerId);
                        console.log('Found worker:', worker);
                        if (worker) {
                            console.log('Worker images:', {
                                idCardFront: worker.idCardFront,
                                idCardBack: worker.idCardBack,
                                fatherCardFront: worker.fatherCardFront,
                                fatherCardBack: worker.fatherCardBack
                            });
                            // Populate form with worker data
                            const workerModal = document.getElementById('workerModalOverlay');
                            const workerModalTitle = workerModal.querySelector('.category-modal-title');
                            const workerModalSubtitle = workerModal.querySelector('.category-modal-subtitle');
                            const workerSubmitBtn = document.getElementById('workerSubmitBtn');
                            const deleteWorkerBtn = document.getElementById('deleteWorkerBtn');
                            
                            // Update modal title
                            if (workerModalTitle) workerModalTitle.textContent = 'EDIT WORKER';
                            if (workerModalSubtitle) workerModalSubtitle.textContent = 'UPDATE WORKER INFORMATION';
                            
                            // Set editing ID
                            workerModal.setAttribute('data-editing-worker-id', worker.id);
                            
                            // Show delete button
                            if (deleteWorkerBtn) deleteWorkerBtn.style.display = 'block';
                            
                            // Update button text
                            if (workerSubmitBtn) workerSubmitBtn.textContent = 'UPDATE & SAVE';
                            
                            // Fill form fields
                            document.getElementById('workerName').value = worker.name || '';
                            document.getElementById('workerMobile').value = worker.mobile || '';
                            document.getElementById('workerFatherName').value = worker.fatherName || '';
                            document.getElementById('workerFatherMobile').value = worker.fatherMobile || '';
                            document.getElementById('workerLocation').value = worker.location || '';
                            document.getElementById('workerCommission').value = worker.commission || 0;
                            
                            // Load additional mobiles
                            const workerMobileContainer = document.getElementById('workerMobileNumbersContainer');
                            if (workerMobileContainer && worker.additionalMobiles && worker.additionalMobiles.length > 0) {
                                workerMobileContainer.style.display = 'block';
                                workerMobileContainer.innerHTML = worker.additionalMobiles.map((item, index) => `
                                    <div class="additional-mobile-item" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; background: #f8fafc;">
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <input type="text" class="category-form-input" placeholder="Contact Name" value="${item.name || ''}" style="flex: 1; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; text-transform: uppercase;" />
                                            <button type="button" class="remove-mobile-btn" style="padding: 10px 16px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 14px;" onclick="this.parentElement.parentElement.remove()">×</button>
                                        </div>
                                        <input type="tel" class="category-form-input" placeholder="e.g. 0300-1234567" value="${item.mobile || ''}" style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;" />
                                    </div>
                                `).join('');
                            }
                            
                            // Load father additional mobiles
                            const fatherMobileContainer = document.getElementById('workerFatherMobileNumbersContainer');
                            if (fatherMobileContainer && worker.fatherAdditionalMobiles && worker.fatherAdditionalMobiles.length > 0) {
                                fatherMobileContainer.style.display = 'block';
                                fatherMobileContainer.innerHTML = worker.fatherAdditionalMobiles.map((item, index) => `
                                    <div class="additional-mobile-item" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; background: #f8fafc;">
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <input type="text" class="category-form-input" placeholder="Contact Name" value="${item.name || ''}" style="flex: 1; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; text-transform: uppercase;" />
                                            <button type="button" class="remove-mobile-btn" style="padding: 10px 16px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 14px;" onclick="this.parentElement.parentElement.remove()">×</button>
                                        </div>
                                        <input type="tel" class="category-form-input" placeholder="e.g. 0300-1234567" value="${item.mobile || ''}" style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;" />
                                    </div>
                                `).join('');
                            }
                            
                            // Clear all image previews first
                            const workerIdCardFrontPreview = document.getElementById('workerIdCardFrontPreview');
                            const workerIdCardBackPreview = document.getElementById('workerIdCardBackPreview');
                            const workerFatherCardFrontPreview = document.getElementById('workerFatherCardFrontPreview');
                            const workerFatherCardBackPreview = document.getElementById('workerFatherCardBackPreview');
                            const workerIdCardFrontLabel = document.getElementById('workerIdCardFrontLabel');
                            const workerIdCardBackLabel = document.getElementById('workerIdCardBackLabel');
                            const workerFatherCardFrontLabel = document.getElementById('workerFatherCardFrontLabel');
                            const workerFatherCardBackLabel = document.getElementById('workerFatherCardBackLabel');
                            const workerIdCardFrontInput = document.getElementById('workerIdCardFront');
                            const workerIdCardBackInput = document.getElementById('workerIdCardBack');
                            const workerFatherCardFrontInput = document.getElementById('workerFatherCardFront');
                            const workerFatherCardBackInput = document.getElementById('workerFatherCardBack');
                            
                            // Load images if they exist - with remove buttons
                            if (worker.idCardFront) {
                                if (workerIdCardFrontPreview) {
                                    workerIdCardFrontPreview.innerHTML = `
                                        <div style="position: relative; width: 100%; margin-top: 12px;">
                                            <img src="${worker.idCardFront}" alt="Front ID Card" 
                                                 style="width: 100%; max-height: 250px; object-fit: contain; border-radius: 12px; border: 2px solid #e2e8f0; display: block; background: #f8fafc; padding: 8px;"
                                                 onerror="console.error('Error loading ID Card Front image:', '${worker.idCardFront}'); this.style.display='none';"
                                                 onload="console.log('ID Card Front image loaded successfully');"
                                            />
                                            <button type="button" 
                                                    class="remove-image-btn" 
                                                    onclick="removeExistingImage('workerIdCardFrontPreview', 'workerIdCardFront')"
                                                    style="position: absolute; top: 8px; right: 8px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; font-weight: bold; font-size: 18px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: all 0.2s;"
                                                    onmouseover="this.style.background='#dc2626'; this.style.transform='scale(1.1)'"
                                                    onmouseout="this.style.background='#ef4444'; this.style.transform='scale(1)'"
                                                    title="Remove Image">
                                                ×
                                            </button>
                                        </div>
                                    `;
                                    workerIdCardFrontPreview.classList.add('show');
                                    workerIdCardFrontPreview.style.display = 'block';
                                    workerIdCardFrontPreview.style.marginTop = '12px';
                                    workerIdCardFrontPreview.setAttribute('data-existing-image', worker.idCardFront);
                                }
                                if (workerIdCardFrontLabel) {
                                    workerIdCardFrontLabel.textContent = 'Change Front Image';
                                }
                            } else {
                                if (workerIdCardFrontPreview) {
                                    workerIdCardFrontPreview.innerHTML = '';
                                    workerIdCardFrontPreview.classList.remove('show');
                                    workerIdCardFrontPreview.style.display = 'none';
                                }
                                if (workerIdCardFrontLabel) {
                                    workerIdCardFrontLabel.textContent = 'Choose Front Image';
                                }
                            }
                            
                            if (worker.idCardBack) {
                                if (workerIdCardBackPreview) {
                                    workerIdCardBackPreview.innerHTML = `
                                        <div style="position: relative; width: 100%; margin-top: 12px;">
                                            <img src="${worker.idCardBack}" alt="Back ID Card" 
                                                 style="width: 100%; max-height: 250px; object-fit: contain; border-radius: 12px; border: 2px solid #e2e8f0; display: block; background: #f8fafc; padding: 8px;"
                                                 onerror="console.error('Error loading ID Card Back image:', '${worker.idCardBack}'); this.style.display='none';"
                                                 onload="console.log('ID Card Back image loaded successfully');"
                                            />
                                            <button type="button" 
                                                    class="remove-image-btn" 
                                                    onclick="removeExistingImage('workerIdCardBackPreview', 'workerIdCardBack')"
                                                    style="position: absolute; top: 8px; right: 8px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; font-weight: bold; font-size: 18px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: all 0.2s;"
                                                    onmouseover="this.style.background='#dc2626'; this.style.transform='scale(1.1)'"
                                                    onmouseout="this.style.background='#ef4444'; this.style.transform='scale(1)'"
                                                    title="Remove Image">
                                                ×
                                            </button>
                                        </div>
                                    `;
                                    workerIdCardBackPreview.classList.add('show');
                                    workerIdCardBackPreview.style.display = 'block';
                                    workerIdCardBackPreview.style.marginTop = '12px';
                                    workerIdCardBackPreview.setAttribute('data-existing-image', worker.idCardBack);
                                }
                                if (workerIdCardBackLabel) {
                                    workerIdCardBackLabel.textContent = 'Change Back Image';
                                }
                            } else {
                                if (workerIdCardBackPreview) {
                                    workerIdCardBackPreview.innerHTML = '';
                                    workerIdCardBackPreview.classList.remove('show');
                                    workerIdCardBackPreview.style.display = 'none';
                                }
                                if (workerIdCardBackLabel) {
                                    workerIdCardBackLabel.textContent = 'Choose Back Image';
                                }
                            }
                            
                            if (worker.fatherCardFront) {
                                if (workerFatherCardFrontPreview) {
                                    workerFatherCardFrontPreview.innerHTML = `
                                        <div style="position: relative; width: 100%; margin-top: 12px;">
                                            <img src="${worker.fatherCardFront}" alt="Father Card Front" 
                                                 style="width: 100%; max-height: 250px; object-fit: contain; border-radius: 12px; border: 2px solid #e2e8f0; display: block; background: #f8fafc; padding: 8px;"
                                                 onerror="console.error('Error loading Father Card Front image:', '${worker.fatherCardFront}'); this.style.display='none';"
                                                 onload="console.log('Father Card Front image loaded successfully');"
                                            />
                                            <button type="button" 
                                                    class="remove-image-btn" 
                                                    onclick="removeExistingImage('workerFatherCardFrontPreview', 'workerFatherCardFront')"
                                                    style="position: absolute; top: 8px; right: 8px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; font-weight: bold; font-size: 18px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: all 0.2s;"
                                                    onmouseover="this.style.background='#dc2626'; this.style.transform='scale(1.1)'"
                                                    onmouseout="this.style.background='#ef4444'; this.style.transform='scale(1)'"
                                                    title="Remove Image">
                                                ×
                                            </button>
                                        </div>
                                    `;
                                    workerFatherCardFrontPreview.classList.add('show');
                                    workerFatherCardFrontPreview.style.display = 'block';
                                    workerFatherCardFrontPreview.style.marginTop = '12px';
                                    workerFatherCardFrontPreview.setAttribute('data-existing-image', worker.fatherCardFront);
                                }
                                if (workerFatherCardFrontLabel) {
                                    workerFatherCardFrontLabel.textContent = 'Change Father Card Front';
                                }
                            } else {
                                if (workerFatherCardFrontPreview) {
                                    workerFatherCardFrontPreview.innerHTML = '';
                                    workerFatherCardFrontPreview.classList.remove('show');
                                    workerFatherCardFrontPreview.style.display = 'none';
                                }
                                if (workerFatherCardFrontLabel) {
                                    workerFatherCardFrontLabel.textContent = 'Choose Front Image';
                                }
                            }
                            
                            if (worker.fatherCardBack) {
                                if (workerFatherCardBackPreview) {
                                    workerFatherCardBackPreview.innerHTML = `
                                        <div style="position: relative; width: 100%; margin-top: 12px;">
                                            <img src="${worker.fatherCardBack}" alt="Father Card Back" 
                                                 style="width: 100%; max-height: 250px; object-fit: contain; border-radius: 12px; border: 2px solid #e2e8f0; display: block; background: #f8fafc; padding: 8px;"
                                                 onerror="console.error('Error loading Father Card Back image:', '${worker.fatherCardBack}'); this.style.display='none';"
                                                 onload="console.log('Father Card Back image loaded successfully');"
                                            />
                                            <button type="button" 
                                                    class="remove-image-btn" 
                                                    onclick="removeExistingImage('workerFatherCardBackPreview', 'workerFatherCardBack')"
                                                    style="position: absolute; top: 8px; right: 8px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; font-weight: bold; font-size: 18px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: all 0.2s;"
                                                    onmouseover="this.style.background='#dc2626'; this.style.transform='scale(1.1)'"
                                                    onmouseout="this.style.background='#ef4444'; this.style.transform='scale(1)'"
                                                    title="Remove Image">
                                                ×
                                            </button>
                                        </div>
                                    `;
                                    workerFatherCardBackPreview.classList.add('show');
                                    workerFatherCardBackPreview.style.display = 'block';
                                    workerFatherCardBackPreview.style.marginTop = '12px';
                                    workerFatherCardBackPreview.setAttribute('data-existing-image', worker.fatherCardBack);
                                }
                                if (workerFatherCardBackLabel) {
                                    workerFatherCardBackLabel.textContent = 'Change Father Card Back';
                                }
                            } else {
                                if (workerFatherCardBackPreview) {
                                    workerFatherCardBackPreview.innerHTML = '';
                                    workerFatherCardBackPreview.classList.remove('show');
                                    workerFatherCardBackPreview.style.display = 'none';
                                }
                                if (workerFatherCardBackLabel) {
                                    workerFatherCardBackLabel.textContent = 'Choose Back Image';
                                }
                            }
                            
                            // Clear file inputs so new uploads replace existing images
                            if (workerIdCardFrontInput) workerIdCardFrontInput.value = '';
                            if (workerIdCardBackInput) workerIdCardBackInput.value = '';
                            if (workerFatherCardFrontInput) workerFatherCardFrontInput.value = '';
                            if (workerFatherCardBackInput) workerFatherCardBackInput.value = '';
                            
                            console.log('Loaded worker images:', {
                                idCardFront: worker.idCardFront,
                                idCardBack: worker.idCardBack,
                                fatherCardFront: worker.fatherCardFront,
                                fatherCardBack: worker.fatherCardBack
                            });
                            
                            // Show modal first
                            workerModal.style.display = 'block';
                            workerModal.classList.add('show');
                            document.body.style.overflow = 'hidden';
                            
                            // Force images to display after modal is visible (using setTimeout to ensure DOM is ready)
                            setTimeout(() => {
                                // Re-apply image display after a small delay to ensure modal is fully rendered
                                if (worker.idCardFront && workerIdCardFrontPreview) {
                                    workerIdCardFrontPreview.style.display = 'block';
                                    workerIdCardFrontPreview.style.visibility = 'visible';
                                    workerIdCardFrontPreview.style.opacity = '1';
                                    // Force a reflow to ensure images render
                                    workerIdCardFrontPreview.offsetHeight;
                                }
                                if (worker.idCardBack && workerIdCardBackPreview) {
                                    workerIdCardBackPreview.style.display = 'block';
                                    workerIdCardBackPreview.style.visibility = 'visible';
                                    workerIdCardBackPreview.style.opacity = '1';
                                    workerIdCardBackPreview.offsetHeight;
                                }
                                if (worker.fatherCardFront && workerFatherCardFrontPreview) {
                                    workerFatherCardFrontPreview.style.display = 'block';
                                    workerFatherCardFrontPreview.style.visibility = 'visible';
                                    workerFatherCardFrontPreview.style.opacity = '1';
                                    workerFatherCardFrontPreview.offsetHeight;
                                }
                                if (worker.fatherCardBack && workerFatherCardBackPreview) {
                                    workerFatherCardBackPreview.style.display = 'block';
                                    workerFatherCardBackPreview.style.visibility = 'visible';
                                    workerFatherCardBackPreview.style.opacity = '1';
                                    workerFatherCardBackPreview.offsetHeight;
                                }
                                
                                console.log('Images should now be visible. Check browser console for any loading errors.');
                            }, 150);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching worker data:', error);
                    alert('Error loading worker data. Please try again.');
                });
            }
            
            // Function to remove existing image when X button is clicked
            window.removeExistingImage = function(previewId, inputId) {
                if (!confirm('Are you sure you want to remove this image?')) {
                    return;
                }
                
                const preview = document.getElementById(previewId);
                const input = document.getElementById(inputId);
                const label = document.getElementById(inputId.replace('Input', 'Label') || previewId.replace('Preview', 'Label'));
                
                if (preview) {
                    preview.innerHTML = '';
                    preview.classList.remove('show');
                    preview.style.display = 'none';
                    preview.removeAttribute('data-existing-image');
                    preview.setAttribute('data-deleted', 'true');
                }
                
                if (input) {
                    input.value = '';
                }
                
                // Reset label
                if (previewId === 'workerIdCardFrontPreview' && workerIdCardFrontLabel) {
                    workerIdCardFrontLabel.textContent = 'Choose Front Image';
                } else if (previewId === 'workerIdCardBackPreview' && workerIdCardBackLabel) {
                    workerIdCardBackLabel.textContent = 'Choose Back Image';
                } else if (previewId === 'workerFatherCardFrontPreview' && workerFatherCardFrontLabel) {
                    workerFatherCardFrontLabel.textContent = 'Choose Front Image';
                } else if (previewId === 'workerFatherCardBackPreview' && workerFatherCardBackLabel) {
                    workerFatherCardBackLabel.textContent = 'Choose Back Image';
                }
            };
            
            // Function to delete a worker
            function deleteWorker(workerId, workerName) {
                if (!confirm(`Are you sure you want to delete worker "${workerName}"? This action cannot be undone.`)) {
                    return;
                }
                
                // Delete from backend via API
                fetch(API_ROUTES.workers.destroy(workerId), {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Worker deleted from backend:', workerId);
                        
                        // Refresh the list from API
                        loadWorkersList();
                        
                        // Dispatch event to notify React component
                        window.dispatchEvent(new CustomEvent('workersUpdated'));
                    } else {
                        console.error('Error deleting worker:', data.message);
                        alert('Error deleting worker: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('API Error during delete:', error);
                    alert('Error deleting worker. Please try again.');
                });
            }
            
            if (workerDetailsModalClose) {
                workerDetailsModalClose.addEventListener('click', closeWorkerDetailsModal);
            }
            
            if (workerDetailsModalOverlay) {
                workerDetailsModalOverlay.addEventListener('click', function(e) {
                    if (e.target === workerDetailsModalOverlay) {
                        closeWorkerDetailsModal();
                    }
                });
            }
            
            // Listen for worker updates to refresh the list
            window.addEventListener('workersUpdated', function() {
                if (workerDetailsModalOverlay && workerDetailsModalOverlay.classList.contains('show')) {
                    loadWorkersList();
                }
            });
            
            // Category Modal handlers
            const categoryModalOverlay = document.getElementById('categoryModalOverlay');
            const categoryModalClose = document.getElementById('categoryModalClose');
            const categoryCancelBtn = document.getElementById('categoryCancelBtn');
            const categoryForm = document.getElementById('categoryForm');
            
            // Listen for edit modal event
            window.addEventListener('openEditModal', function(e) {
                const categoryData = e.detail;
                const categoryModal = document.getElementById('categoryModalOverlay');
                if (categoryModal) {
                    const categoryNameInput = document.getElementById('categoryName');
                    const categoryPriceInput = document.getElementById('categoryPrice');
                    if (categoryNameInput) categoryNameInput.value = categoryData.label || '';
                    if (categoryPriceInput) categoryPriceInput.value = categoryData.basePrice || categoryData.base_price || 0;
                    categoryModal.classList.add('show');
                }
            });
            
            // Icon selection using document-level event delegation
            document.addEventListener('click', function(e) {
                // Check if clicked element is an icon option or inside one
                const clickedIconOption = e.target.closest('.icon-option');
                if (clickedIconOption) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Get all icon options
                    const allIcons = document.querySelectorAll('.icon-option');
                    
                    // Remove selected from all
                    allIcons.forEach(icon => {
                        icon.classList.remove('selected');
                    });
                    
                    // Add selected to clicked icon
                    clickedIconOption.classList.add('selected');
                    
                    // Update hidden input
                    const selectedIcon = clickedIconOption.getAttribute('data-icon');
                    const selectedIconInput = document.getElementById('selectedIcon');
                    if (selectedIconInput) {
                        selectedIconInput.value = selectedIcon;
                    }
                    return;
                }
                
                // Check if clicked element is a theme color option or inside one
                const clickedOption = e.target.closest('.theme-color-option');
                if (!clickedOption) return;
                
                e.preventDefault();
                e.stopPropagation();
                
                // Get all theme options
                const allOptions = document.querySelectorAll('.theme-color-option');
                
                // Remove selected from all
                allOptions.forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Add selected to clicked option
                clickedOption.classList.add('selected');
                
                // Update hidden inputs
                const color = clickedOption.getAttribute('data-color');
                const colorClass = clickedOption.getAttribute('data-class');
                const selectedColorInput = document.getElementById('selectedThemeColor');
                const selectedClassInput = document.getElementById('selectedThemeClass');
                const previewCard = document.getElementById('previewCard');
                
                if (selectedColorInput) {
                    selectedColorInput.value = color;
                    console.log('Selected color:', color);
                }
                if (selectedClassInput) {
                    selectedClassInput.value = colorClass;
                    console.log('Selected color class:', colorClass);
                }
                if (previewCard) {
                    previewCard.style.background = color;
                    console.log('Preview card updated');
                }
                
                console.log('Theme selection complete');
            });
            
            // Initialize form inputs
            const categoryNameInput = document.getElementById('categoryName');
            const categoryPriceInput = document.getElementById('categoryPrice');
            const previewCard = document.getElementById('previewCard');
            const previewLabel = document.getElementById('previewLabel');
            const previewPrice = previewCard ? previewCard.querySelector('.price') : null;
            
            // Preview update on input change
            function updatePreview() {
                if (previewLabel && categoryNameInput) {
                    const name = categoryNameInput.value.trim() || 'SERVICE LABEL';
                    previewLabel.textContent = name.toUpperCase();
                }
                if (previewPrice && categoryPriceInput) {
                    const price = categoryPriceInput.value || '0';
                    previewPrice.textContent = '· RS.' + parseInt(price);
                }
            }
            
            if (categoryNameInput) {
                categoryNameInput.addEventListener('input', updatePreview);
            }
            if (categoryPriceInput) {
                categoryPriceInput.addEventListener('input', updatePreview);
            }
            
            function closeCategoryModal() {
                console.log('closeCategoryModal called');
                // Get fresh references to DOM elements
                const currentModalOverlay = document.getElementById('categoryModalOverlay');
                if (currentModalOverlay) {
                    currentModalOverlay.classList.remove('show');
                    currentModalOverlay.style.display = 'none'; // Force hide
                    currentModalOverlay.style.visibility = 'hidden'; // Additional hide
                    currentModalOverlay.style.opacity = '0'; // Additional hide
                    document.body.style.overflow = ''; // Reset body overflow
                    console.log('Modal overlay class removed');
                    
                    const currentForm = document.getElementById('categoryForm');
                    if (currentForm) {
                        currentForm.reset();
                        // Reset theme selection
                        const themeColorOptions = document.querySelectorAll('.theme-color-option');
                        const selectedThemeColorInput = document.getElementById('selectedThemeColor');
                        const selectedThemeClassInput = document.getElementById('selectedThemeClass');
                        const currentPreviewCard = document.getElementById('previewCard');
                        const currentPreviewLabel = document.getElementById('previewLabel');
                        const currentPreviewPrice = currentPreviewCard ? currentPreviewCard.querySelector('.price') : null;
                        const currentCategoryPriceInput = document.getElementById('categoryPrice');
                        
                        if (themeColorOptions.length > 0) {
                            themeColorOptions.forEach(opt => opt.classList.remove('selected'));
                            themeColorOptions[0].classList.add('selected');
                            if (selectedThemeColorInput) selectedThemeColorInput.value = '#3b82f6';
                            if (selectedThemeClassInput) selectedThemeClassInput.value = 'bg-blue-600';
                            if (currentPreviewCard) currentPreviewCard.style.background = '#3b82f6';
                        }
                        
                        // Reset icon selection
                        const iconOptions = document.querySelectorAll('.icon-option');
                        const selectedIconInput = document.getElementById('selectedIcon');
                        if (iconOptions.length > 0) {
                            iconOptions.forEach(opt => opt.classList.remove('selected'));
                            iconOptions[0].classList.add('selected');
                            if (selectedIconInput) selectedIconInput.value = 'car';
                        }
                        // Reset preview
                        if (currentPreviewLabel) currentPreviewLabel.textContent = 'SERVICE LABEL';
                        if (currentPreviewPrice) currentPreviewPrice.textContent = '· RS.0';
                        if (currentCategoryPriceInput) currentCategoryPriceInput.value = '0';
                        
                        // Remove editing attribute
                        if (currentModalOverlay) currentModalOverlay.removeAttribute('data-editing-id');
                        
                        // Reset button text to SAVE
                        const submitBtn = document.getElementById('categorySubmitBtn');
                        if (submitBtn) submitBtn.textContent = 'SAVE';
                    }
                } else {
                    console.error('categoryModalOverlay not found!');
                }
            }
            
            // Close button handler - only close modal, stay on same page
            if (categoryModalClose) {
                // Handle click on button itself
                categoryModalClose.addEventListener('click', function(e) {
                    if (e.preventDefault) e.preventDefault();
                    if (e.stopPropagation) e.stopPropagation();
                    console.log('Close button clicked - closing modal only');
                    
                    // Just close the modal, stay on same page
                    closeCategoryModal();
                    
                    return false;
                }, true); // Use capture phase
                
                // Also handle clicks on SVG inside button
                const closeButtonSVG = categoryModalClose.querySelector('svg');
                if (closeButtonSVG) {
                    closeButtonSVG.addEventListener('click', function(e) {
                        if (e.preventDefault) e.preventDefault();
                        if (e.stopPropagation) e.stopPropagation();
                        console.log('Close SVG clicked - closing modal only');
                        
                        // Just close the modal, stay on same page
                        closeCategoryModal();
                        
                        return false;
                    }, true);
                }
            } else {
                console.error('categoryModalClose button not found!');
            }
            
            // Also use document-level event delegation as backup
            document.addEventListener('click', function(e) {
                // Check if click is on close button or its children
                const closeBtn = e.target.closest('#categoryModalClose');
                if (closeBtn) {
                    if (e.preventDefault) e.preventDefault();
                    if (e.stopPropagation) e.stopPropagation();
                    console.log('Close button clicked via delegation - closing modal only');
                    
                    // Just close the modal, stay on same page
                    closeCategoryModal();
                    
                    return false;
                }
            }, true); // Capture phase
            
            if (categoryCancelBtn) {
                categoryCancelBtn.addEventListener('click', closeCategoryModal);
            }
            
            if (categoryModalOverlay) {
                categoryModalOverlay.addEventListener('click', function(e) {
                    if (e.target === categoryModalOverlay) {
                        closeCategoryModal();
                    }
                });
            }
            
            if (categoryForm) {
                categoryForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    console.log('Form submit triggered');
                    
                    // Get current values
                    const currentCategoryNameInput = document.getElementById('categoryName');
                    const currentCategoryPriceInput = document.getElementById('categoryPrice');
                    const currentSelectedColorInput = document.getElementById('selectedThemeColor');
                    const currentSelectedClassInput = document.getElementById('selectedThemeClass');
                    const categoryModalOverlay = document.getElementById('categoryModalOverlay');
                    
                    const categoryName = currentCategoryNameInput ? currentCategoryNameInput.value.trim().toUpperCase() : '';
                    const categoryPrice = currentCategoryPriceInput ? parseInt(currentCategoryPriceInput.value) || 0 : 0;
                    const selectedColor = currentSelectedColorInput ? currentSelectedColorInput.value : '#3b82f6';
                    const selectedColorClass = currentSelectedClassInput ? currentSelectedClassInput.value : 'bg-blue-600';
                    
                    // Get selected icon
                    const selectedIconInput = document.getElementById('selectedIcon');
                    const selectedIcon = selectedIconInput ? selectedIconInput.value : 'car';
                    
                    // Collect additional prices
                    const additionalPrices = [];
                    const additionalPriceItems = document.querySelectorAll('.additional-price-item');
                    console.log('Found additional price items:', additionalPriceItems.length);
                    additionalPriceItems.forEach((item, index) => {
                        const labelInput = item.querySelector('input[type="text"]');
                        const amountInput = item.querySelector('input[type="number"]');
                        console.log(`Item ${index}:`, {
                            labelInput: labelInput ? labelInput.value : 'not found',
                            amountInput: amountInput ? amountInput.value : 'not found'
                        });
                        if (labelInput && amountInput) {
                            const label = labelInput.value.trim();
                            const amount = parseInt(amountInput.value) || 0;
                            if (label && amount > 0) {
                                additionalPrices.push({
                                    label: label,
                                    amount: amount
                                });
                                console.log('Added additional price:', { label, amount });
                            }
                        }
                    });
                    console.log('All additional prices collected:', additionalPrices);
                    
                    console.log('Form data:', { categoryName, categoryPrice, selectedColor, selectedColorClass, selectedIcon, additionalPrices });
                    
                    if (!categoryName) {
                        alert('Please enter a category name');
                        return;
                    }
                    
                    // Check if we're editing an existing category
                    const editingId = categoryModalOverlay ? categoryModalOverlay.getAttribute('data-editing-id') : null;
                    
                    // Prepare data for API
                    const serviceData = {
                        label: categoryName,
                        base_price: categoryPrice,
                        additional_prices: additionalPrices,
                        icon: selectedIcon,
                        color: selectedColorClass,
                        color_value: selectedColor
                    };
                    
                    // Determine API URL and method
                    const apiUrl = editingId ? API_ROUTES.services.update(editingId) : API_ROUTES.services.store;
                    const method = editingId ? 'PUT' : 'POST';
                    
                    // Save to backend via API
                    fetch(apiUrl, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(serviceData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Service saved to backend:', data.service);
                            
                            // Dispatch custom event to notify React component to reload from API
                            window.dispatchEvent(new CustomEvent('categoriesUpdated'));
                        } else {
                            console.error('Error saving service:', data.message);
                            alert('Error saving service: ' + (data.message || 'Unknown error'));
                        }
                    })
                        .catch(error => {
                            console.error('API Error:', error);
                            alert('Error saving service. Please try again.');
                        });
                    
                    // Remove editing attribute
                    if (categoryModalOverlay) {
                        categoryModalOverlay.removeAttribute('data-editing-id');
                    }
                    
                    // Reset button text to SAVE
                    const submitBtn = document.getElementById('categorySubmitBtn');
                    if (submitBtn) submitBtn.textContent = 'SAVE';
                    
                    console.log('Selected color saved:', selectedColor);
                    console.log('Selected color class saved:', selectedColorClass);
                    
                    // Close modal IMMEDIATELY - FORCE CLOSE before processing
                    const categoryModalOverlayEl = document.getElementById('categoryModalOverlay');
                    if (categoryModalOverlayEl) {
                        categoryModalOverlayEl.classList.remove('show');
                        categoryModalOverlayEl.style.display = 'none';
                        categoryModalOverlayEl.style.visibility = 'hidden';
                        categoryModalOverlayEl.style.opacity = '0';
                        document.body.style.overflow = '';
                        categoryModalOverlayEl.removeAttribute('data-editing-id');
                        console.log('Category modal closed immediately on form submit');
                    }
                    
                    // Close modal and reset form
                    closeCategoryModal();
                    
                    // Dispatch custom event to notify React component IMMEDIATELY and after delay
                    window.dispatchEvent(new CustomEvent('categoriesUpdated'));
                    setTimeout(function() {
                        window.dispatchEvent(new CustomEvent('categoriesUpdated'));
                    }, 100);
                });
            } else {
                console.error('Category form not found!');
            }
            
            // Delete category button handler
            const deleteCategoryBtn = document.getElementById('deleteCategoryBtn');
            if (deleteCategoryBtn) {
                deleteCategoryBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    console.log('Delete button clicked');
                    
                    const categoryModalOverlay = document.getElementById('categoryModalOverlay');
                    const categoryNameInput = document.getElementById('categoryName');
                    const categoryName = categoryNameInput ? categoryNameInput.value.trim().toUpperCase() : '';
                    
                    // First try to get editing ID from modal attribute
                    let editingId = categoryModalOverlay ? categoryModalOverlay.getAttribute('data-editing-id') : null;
                    
                    console.log('Editing ID from attribute:', editingId);
                    console.log('Category name from input:', categoryName);
                    
                    // If no editing ID but we have a category name, try to find it from API
                    if (!editingId && categoryName) {
                        // Fetch categories from API to find by name
                        fetch(API_ROUTES.services.index)
                            .then(res => res.json())
                            .then(apiData => {
                                if (apiData.success && apiData.services) {
                                    const foundCategory = apiData.services.find(cat => {
                                        const catLabel = (cat.label || '').toUpperCase().trim();
                                        return catLabel === categoryName;
                                    });
                                    if (foundCategory) {
                                        editingId = foundCategory.id;
                                        console.log('Found category by name from API, ID:', editingId);
                                    }
                                }
                            })
                            .catch(err => console.error('Error fetching categories:', err));
                        
                        // Check if it's a default service (cannot delete defaults, they're hardcoded)
                        const defaultServices = [
                            { label: 'Mini Car Wash', basePrice: 300 },
                            { label: 'Full Service', basePrice: 1500 }
                        ];
                        const isDefault = defaultServices.some(s => s.label.toUpperCase().trim() === categoryName);
                        if (isDefault) {
                            alert('Default services cannot be deleted. They are always available.');
                            return false;
                        }
                    }
                    
                    if (!editingId) {
                        alert('Service not found. Please make sure you are editing an existing saved service.');
                        return false;
                    }
                    
                    if (confirm('Are you sure you want to delete this service? This action cannot be undone.')) {
                        // Delete from backend via API
                        fetch(API_ROUTES.services.destroy(editingId), {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Service deleted from backend:', editingId);
                                
                                // Close modal
                                closeCategoryModal();
                                
                                // Dispatch custom event to notify React component to reload from API
                                setTimeout(function() {
                                    window.dispatchEvent(new CustomEvent('categoriesUpdated'));
                                    console.log('categoriesUpdated event dispatched after deletion');
                                }, 100);
                            } else {
                                console.error('Error deleting service:', data.message);
                                alert('Error deleting service: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('API Error during delete:', error);
                            alert('Error deleting service. Please try again.');
                        });
                    }
                    
                    return false;
                }, true); // Use capture phase to ensure it fires
            } else {
                console.error('deleteCategoryBtn not found!');
            }
            
            // Also use document-level event delegation as backup for delete button
            document.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('#deleteCategoryBtn');
                if (deleteBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    console.log('Delete button clicked via delegation');
                    
                    const categoryModalOverlay = document.getElementById('categoryModalOverlay');
                    const categoryNameInput = document.getElementById('categoryName');
                    const categoryName = categoryNameInput ? categoryNameInput.value.trim().toUpperCase() : '';
                    
                    let editingId = categoryModalOverlay ? categoryModalOverlay.getAttribute('data-editing-id') : null;
                    
                    // If no editing ID but we have a category name, try to find it from API
                    if (!editingId && categoryName) {
                        // Fetch from API to find by name
                        fetch(API_ROUTES.services.index)
                            .then(res => res.json())
                            .then(apiData => {
                                if (apiData.success && apiData.services) {
                                    const foundCategory = apiData.services.find(cat => {
                                        const catLabel = (cat.label || '').toUpperCase().trim();
                                        return catLabel === categoryName;
                                    });
                                    if (foundCategory) {
                                        editingId = foundCategory.id;
                                    }
                                }
                            })
                            .catch(err => console.error('Error fetching categories:', err));
                        
                        // Check if it's a default service (cannot delete defaults)
                        const defaultServices = [
                            { label: 'Mini Car Wash', basePrice: 300 },
                            { label: 'Full Service', basePrice: 1500 }
                        ];
                        const isDefault = defaultServices.some(s => s.label.toUpperCase().trim() === categoryName);
                        if (isDefault) {
                            alert('Default services cannot be deleted. They are always available.');
                            return false;
                        }
                    }
                    
                    if (!editingId) {
                        alert('Service not found. Please make sure you are editing an existing saved service.');
                        return false;
                    }
                    
                    if (confirm('Are you sure you want to delete this service? This action cannot be undone.')) {
                        // Delete from backend via API
                        fetch(API_ROUTES.services.destroy(editingId), {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Category deleted:', editingId);
                                
                                closeCategoryModal();
                                
                                setTimeout(function() {
                                    window.dispatchEvent(new CustomEvent('categoriesUpdated'));
                                }, 100);
                            } else {
                                alert('Error deleting service: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('API Error:', error);
                            alert('Error deleting service. Please try again.');
                        });
                    }
                    
                    return false;
                }
            }, true);
            
            // Add Additional Price Button Handler
            const addAdditionalPriceBtn = document.getElementById('addAdditionalPriceBtn');
            const additionalPricesContainer = document.getElementById('additionalPricesContainer');
            window.additionalPriceCounter = window.additionalPriceCounter || 0;
            let additionalPriceCounter = window.additionalPriceCounter;
            
            if (addAdditionalPriceBtn && additionalPricesContainer) {
                addAdditionalPriceBtn.addEventListener('click', function() {
                    window.additionalPriceCounter = (window.additionalPriceCounter || 0) + 1;
                    additionalPriceCounter = window.additionalPriceCounter;
                    const priceId = 'additionalPrice_' + additionalPriceCounter;
                    
                    const priceDiv = document.createElement('div');
                    priceDiv.id = priceId;
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
                            class="remove-price-btn"
                            style="padding: 10px 16px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 14px; transition: background 0.2s;"
                            onmouseover="this.style.background='#dc2626'"
                            onmouseout="this.style.background='#ef4444'"
                            onclick="this.parentElement.remove()"
                        >
                            ×
                        </button>
                    `;
                    
                    // Add auto-capitalize functionality for the label input (capitalize first letter of each word)
                    const labelInput = priceDiv.querySelector(`#${priceId}_label`);
                    if (labelInput) {
                        labelInput.addEventListener('input', function(e) {
                            const value = e.target.value;
                            if (value.length > 0) {
                                // Capitalize first letter of each word
                                const words = value.split(' ');
                                const capitalizedWords = words.map(word => {
                                    if (word.length > 0) {
                                        return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
                                    }
                                    return word;
                                });
                                const newValue = capitalizedWords.join(' ');
                                if (newValue !== value) {
                                    // Set cursor position after the change
                                    const cursorPos = e.target.selectionStart;
                                    e.target.value = newValue;
                                    // Restore cursor position
                                    setTimeout(() => {
                                        e.target.setSelectionRange(cursorPos, cursorPos);
                                    }, 0);
                                }
                            }
                        });
                    }
                    
                    additionalPricesContainer.appendChild(priceDiv);
                    additionalPricesContainer.style.display = 'block';
                    
                    // Move button to bottom after adding price field
                    const addBtn = document.getElementById('addAdditionalPriceBtn');
                    if (addBtn && additionalPricesContainer.parentNode) {
                        additionalPricesContainer.parentNode.appendChild(addBtn);
                    }
                });
            }
            
            // Worker Modal handlers
            const workerModalOverlay = document.getElementById('workerModalOverlay');
            const workerModalClose = document.getElementById('workerModalClose');
            const workerForm = document.getElementById('workerForm');
            const workerNameInput = document.getElementById('workerName');
            const workerMobileInput = document.getElementById('workerMobile');
            const workerFatherNameInput = document.getElementById('workerFatherName');
            const workerFatherMobileInput = document.getElementById('workerFatherMobile');
            const workerLocationInput = document.getElementById('workerLocation');
            const workerCommissionInput = document.getElementById('workerCommission');
            
            // Additional mobile numbers handlers
            const addWorkerMobileBtn = document.getElementById('addWorkerMobileBtn');
            const workerMobileNumbersContainer = document.getElementById('workerMobileNumbersContainer');
            const addFatherMobileBtn = document.getElementById('addFatherMobileBtn');
            const workerFatherMobileNumbersContainer = document.getElementById('workerFatherMobileNumbersContainer');
            window.workerMobileCounter = window.workerMobileCounter || 0;
            window.fatherMobileCounter = window.fatherMobileCounter || 0;
            const workerIdCardFrontInput = document.getElementById('workerIdCardFront');
            const workerIdCardBackInput = document.getElementById('workerIdCardBack');
            const workerFatherCardFrontInput = document.getElementById('workerFatherCardFront');
            const workerFatherCardBackInput = document.getElementById('workerFatherCardBack');
            const workerIdCardFrontLabel = document.getElementById('workerIdCardFrontLabel');
            const workerIdCardBackLabel = document.getElementById('workerIdCardBackLabel');
            const workerFatherCardFrontLabel = document.getElementById('workerFatherCardFrontLabel');
            const workerFatherCardBackLabel = document.getElementById('workerFatherCardBackLabel');
            const workerIdCardFrontPreview = document.getElementById('workerIdCardFrontPreview');
            const workerIdCardBackPreview = document.getElementById('workerIdCardBackPreview');
            const workerFatherCardFrontPreview = document.getElementById('workerFatherCardFrontPreview');
            const workerFatherCardBackPreview = document.getElementById('workerFatherCardBackPreview');
            
            function closeWorkerModal() {
                const workerModalOverlay = document.getElementById('workerModalOverlay');
                if (workerModalOverlay) {
                    workerModalOverlay.classList.remove('show');
                    workerModalOverlay.style.display = 'none'; // Force hide
                    workerModalOverlay.style.visibility = 'hidden'; // Additional hide
                    workerModalOverlay.style.opacity = '0'; // Additional hide
                    document.body.style.overflow = ''; // Reset body overflow
                    if (workerForm) {
                        workerForm.reset();
                        // Reset previews
                        if (workerIdCardFrontPreview) {
                            workerIdCardFrontPreview.classList.remove('show');
                            workerIdCardFrontPreview.innerHTML = '';
                        }
                        if (workerIdCardBackPreview) {
                            workerIdCardBackPreview.classList.remove('show');
                            workerIdCardBackPreview.innerHTML = '';
                        }
                        if (workerFatherCardFrontPreview) {
                            workerFatherCardFrontPreview.classList.remove('show');
                            workerFatherCardFrontPreview.innerHTML = '';
                        }
                        if (workerFatherCardBackPreview) {
                            workerFatherCardBackPreview.classList.remove('show');
                            workerFatherCardBackPreview.innerHTML = '';
                        }
                        if (workerIdCardFrontLabel) workerIdCardFrontLabel.textContent = 'Choose Front Image';
                        if (workerIdCardBackLabel) workerIdCardBackLabel.textContent = 'Choose Back Image';
                        if (workerFatherCardFrontLabel) workerFatherCardFrontLabel.textContent = 'Choose Front Image';
                        if (workerFatherCardBackLabel) workerFatherCardBackLabel.textContent = 'Choose Back Image';
                    }
                    // Reset modal title and subtitle
                    const workerModalTitle = workerModalOverlay.querySelector('.category-modal-title');
                    const workerModalSubtitle = workerModalOverlay.querySelector('.category-modal-subtitle');
                    if (workerModalTitle) workerModalTitle.textContent = 'NEW WORKER';
                    if (workerModalSubtitle) workerModalSubtitle.textContent = 'ADD A NEW WORKER TO STATION';
                    
                    // Reset button text to CONFIRM & SAVE
                    const workerSubmitBtn = document.getElementById('workerSubmitBtn');
                    if (workerSubmitBtn) {
                        workerSubmitBtn.textContent = 'CONFIRM & SAVE';
                    }
                    
                    // Hide delete button
                    const deleteWorkerBtn = document.getElementById('deleteWorkerBtn');
                    if (deleteWorkerBtn) {
                        deleteWorkerBtn.style.display = 'none';
                    }
                    
                    // Remove editing attribute
                    workerModalOverlay.removeAttribute('data-editing-worker-id');
                    // Clear additional mobile numbers containers
                    const workerMobileNumbersContainer = document.getElementById('workerMobileNumbersContainer');
                    const workerFatherMobileNumbersContainer = document.getElementById('workerFatherMobileNumbersContainer');
                    if (workerMobileNumbersContainer) {
                        workerMobileNumbersContainer.innerHTML = '';
                        workerMobileNumbersContainer.style.display = 'none';
                        window.workerMobileCounter = 0;
                    }
                    if (workerFatherMobileNumbersContainer) {
                        workerFatherMobileNumbersContainer.innerHTML = '';
                        workerFatherMobileNumbersContainer.style.display = 'none';
                        window.fatherMobileCounter = 0;
                    }
                }
            }
            
            if (workerModalClose) {
                workerModalClose.addEventListener('click', closeWorkerModal);
            }
            
            if (workerModalOverlay) {
                workerModalOverlay.addEventListener('click', function(e) {
                    if (e.target === workerModalOverlay) {
                        closeWorkerModal();
                    }
                });
            }
            
            // File upload preview handlers
            if (workerIdCardFrontInput) {
                workerIdCardFrontInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        if (workerIdCardFrontLabel) {
                            workerIdCardFrontLabel.textContent = file.name;
                        }
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            if (workerIdCardFrontPreview) {
                                workerIdCardFrontPreview.innerHTML = '<img src="' + e.target.result + '" alt="Front ID Card">';
                                workerIdCardFrontPreview.classList.add('show');
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            if (workerIdCardBackInput) {
                workerIdCardBackInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        if (workerIdCardBackLabel) {
                            workerIdCardBackLabel.textContent = file.name;
                        }
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            if (workerIdCardBackPreview) {
                                workerIdCardBackPreview.innerHTML = '<img src="' + e.target.result + '" alt="Back ID Card">';
                                workerIdCardBackPreview.classList.add('show');
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // Father card front input handler
            if (workerFatherCardFrontInput) {
                workerFatherCardFrontInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        if (workerFatherCardFrontLabel) {
                            workerFatherCardFrontLabel.textContent = file.name;
                        }
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            if (workerFatherCardFrontPreview) {
                                workerFatherCardFrontPreview.innerHTML = '<img src="' + e.target.result + '" alt="Father Card Front">';
                                workerFatherCardFrontPreview.classList.add('show');
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // Father card back input handler
            if (workerFatherCardBackInput) {
                workerFatherCardBackInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        if (workerFatherCardBackLabel) {
                            workerFatherCardBackLabel.textContent = file.name;
                        }
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            if (workerFatherCardBackPreview) {
                                workerFatherCardBackPreview.innerHTML = '<img src="' + e.target.result + '" alt="Father Card Back">';
                                workerFatherCardBackPreview.classList.add('show');
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // Add Worker Mobile Number Button Handler
            if (addWorkerMobileBtn && workerMobileNumbersContainer) {
                addWorkerMobileBtn.addEventListener('click', function() {
                    window.workerMobileCounter = (window.workerMobileCounter || 0) + 1;
                    const mobileId = 'workerMobile_' + window.workerMobileCounter;
                    
                    const mobileDiv = document.createElement('div');
                    mobileDiv.id = mobileId;
                    mobileDiv.className = 'additional-mobile-item';
                    mobileDiv.style.cssText = 'display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; background: #f8fafc;';
                    
                    mobileDiv.innerHTML = `
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <input 
                                type="text" 
                                class="category-form-input" 
                                placeholder="Contact Name"
                                style="flex: 1; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; text-transform: uppercase;"
                                id="${mobileId}_name"
                            />
                            <button 
                                type="button" 
                                class="remove-mobile-btn"
                                style="padding: 10px 16px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 14px; transition: background 0.2s;"
                                onmouseover="this.style.background='#dc2626'"
                                onmouseout="this.style.background='#ef4444'"
                                onclick="this.parentElement.parentElement.remove()"
                            >
                                ×
                            </button>
                        </div>
                        <input 
                            type="tel" 
                            class="category-form-input" 
                            placeholder="e.g. 0300-1234567"
                            style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;"
                            id="${mobileId}_mobile"
                        />
                    `;
                    
                    workerMobileNumbersContainer.appendChild(mobileDiv);
                    workerMobileNumbersContainer.style.display = 'block';
                });
            }
            
            // Add Father Mobile Number Button Handler
            if (addFatherMobileBtn && workerFatherMobileNumbersContainer) {
                addFatherMobileBtn.addEventListener('click', function() {
                    window.fatherMobileCounter = (window.fatherMobileCounter || 0) + 1;
                    const mobileId = 'fatherMobile_' + window.fatherMobileCounter;
                    
                    const mobileDiv = document.createElement('div');
                    mobileDiv.id = mobileId;
                    mobileDiv.className = 'additional-mobile-item';
                    mobileDiv.style.cssText = 'display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; padding: 12px; border: 2px solid #e2e8f0; border-radius: 12px; background: #f8fafc;';
                    
                    mobileDiv.innerHTML = `
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <input 
                                type="text" 
                                class="category-form-input" 
                                placeholder="Contact Name"
                                style="flex: 1; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; text-transform: uppercase;"
                                id="${mobileId}_name"
                            />
                            <button 
                                type="button" 
                                class="remove-mobile-btn"
                                style="padding: 10px 16px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 14px; transition: background 0.2s;"
                                onmouseover="this.style.background='#dc2626'"
                                onmouseout="this.style.background='#ef4444'"
                                onclick="this.parentElement.parentElement.remove()"
                            >
                                ×
                            </button>
                        </div>
                        <input 
                            type="tel" 
                            class="category-form-input" 
                            placeholder="e.g. 0300-1234567"
                            style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px;"
                            id="${mobileId}_mobile"
                        />
                    `;
                    
                    workerFatherMobileNumbersContainer.appendChild(mobileDiv);
                    workerFatherMobileNumbersContainer.style.display = 'block';
                });
            }
            
            if (workerForm) {
                workerForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const workerName = workerNameInput ? workerNameInput.value.trim().toUpperCase() : '';
                    
                    if (!workerName) {
                        alert('Please enter a worker name');
                        return;
                    }
                    
                    // Check if we're editing an existing worker
                    const workerModalOverlay = document.getElementById('workerModalOverlay');
                    const editingWorkerId = workerModalOverlay ? workerModalOverlay.getAttribute('data-editing-worker-id') : null;
                    
                    // Close modal IMMEDIATELY before processing files - FORCE CLOSE
                    // Use multiple methods to ensure modal closes
                    if (workerModalOverlay) {
                        // Method 1: Remove show class
                        workerModalOverlay.classList.remove('show');
                        // Method 2: Force hide with display
                        workerModalOverlay.style.display = 'none';
                        workerModalOverlay.style.visibility = 'hidden';
                        workerModalOverlay.style.opacity = '0';
                        // Method 3: Reset body overflow
                        document.body.style.overflow = '';
                        // Method 4: Remove editing attribute
                        workerModalOverlay.removeAttribute('data-editing-worker-id');
                        console.log('Modal closed immediately on form submit');
                    }
                    
                    // Also call closeWorkerModal function for complete cleanup
                    setTimeout(() => {
                        closeWorkerModal();
                    }, 10);
                    
                    // Function to read file as base64
                    const readFileAsBase64 = (fileInput) => {
                        return new Promise((resolve) => {
                            if (!fileInput || !fileInput.files || !fileInput.files[0]) {
                                resolve(null);
                                return;
                            }
                            const file = fileInput.files[0];
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                resolve(e.target.result);
                            };
                            reader.onerror = function() {
                                resolve(null);
                            };
                            reader.readAsDataURL(file);
                        });
                    };
                    
                    // Read all image files
                    Promise.all([
                        readFileAsBase64(workerIdCardFrontInput),
                        readFileAsBase64(workerIdCardBackInput),
                        readFileAsBase64(workerFatherCardFrontInput),
                        readFileAsBase64(workerFatherCardBackInput)
                    ]).then(([idCardFront, idCardBack, fatherCardFront, fatherCardBack]) => {
                        // Collect additional worker mobile numbers (with names)
                        const workerAdditionalMobiles = [];
                        const workerMobileItems = document.querySelectorAll('#workerMobileNumbersContainer .additional-mobile-item');
                        console.log('Found additional mobile items:', workerMobileItems.length);
                        workerMobileItems.forEach((item, index) => {
                            const nameInput = item.querySelector('input[type="text"]');
                            const mobileInput = item.querySelector('input[type="tel"]');
                            const name = nameInput ? nameInput.value.trim().toUpperCase() : '';
                            const mobile = mobileInput ? mobileInput.value.trim() : '';
                            console.log(`Additional mobile ${index + 1}:`, { name, mobile });
                            if (name || mobile) {
                                workerAdditionalMobiles.push({
                                    name: name,
                                    mobile: mobile
                                });
                            }
                        });
                        console.log('All additional mobiles collected:', workerAdditionalMobiles);
                        
                        // Collect additional father mobile numbers (with names)
                        const fatherAdditionalMobiles = [];
                        const fatherMobileItems = document.querySelectorAll('#workerFatherMobileNumbersContainer .additional-mobile-item');
                        fatherMobileItems.forEach(item => {
                            const nameInput = item.querySelector('input[type="text"]');
                            const mobileInput = item.querySelector('input[type="tel"]');
                            const name = nameInput ? nameInput.value.trim().toUpperCase() : '';
                            const mobile = mobileInput ? mobileInput.value.trim() : '';
                            if (name || mobile) {
                                fatherAdditionalMobiles.push({
                                    name: name,
                                    mobile: mobile
                                });
                            }
                        });
                        
                        // Check if we're editing an existing worker (reuse existing variable)
                        const editingWorkerId = workerModalOverlay ? workerModalOverlay.getAttribute('data-editing-worker-id') : null;
                        
                        // Check if images were deleted (have data-deleted attribute)
                        const idCardFrontPreview = document.getElementById('workerIdCardFrontPreview');
                        const idCardBackPreview = document.getElementById('workerIdCardBackPreview');
                        const fatherCardFrontPreview = document.getElementById('workerFatherCardFrontPreview');
                        const fatherCardBackPreview = document.getElementById('workerFatherCardBackPreview');
                        
                        // Get main mobile number
                        const mainMobile = workerMobileInput ? workerMobileInput.value.trim() : '';
                        console.log('Main mobile number:', mainMobile);
                        console.log('Additional mobiles:', workerAdditionalMobiles);
                        
                        // Prepare worker data for API - only include image fields when they're being changed
                        const workerData = {
                            name: workerName,
                            mobile: mainMobile,
                            additional_mobiles: workerAdditionalMobiles,
                            father_name: workerFatherNameInput ? workerFatherNameInput.value.trim().toUpperCase() : '',
                            father_mobile: workerFatherMobileInput ? workerFatherMobileInput.value.trim() : '',
                            father_additional_mobiles: fatherAdditionalMobiles,
                            location: workerLocationInput ? workerLocationInput.value.trim() : '',
                            commission: workerCommissionInput ? parseInt(workerCommissionInput.value) || 0 : 0
                        };
                        
                        // Only include image fields if:
                        // 1. New image uploaded (base64 data exists)
                        // 2. Image was deleted (data-deleted attribute is true) - send null
                        // Don't include if editing and want to preserve existing (no new file, not deleted)
                        
                        if (editingWorkerId) {
                            // Editing mode - only send images if changed
                            if (idCardFrontPreview && idCardFrontPreview.getAttribute('data-deleted') === 'true') {
                                workerData.id_card_front = null; // Delete image
                            } else if (idCardFront) {
                                workerData.id_card_front = idCardFront; // New image uploaded
                            }
                            // If neither condition, don't include field (preserve existing)
                            
                            if (idCardBackPreview && idCardBackPreview.getAttribute('data-deleted') === 'true') {
                                workerData.id_card_back = null;
                            } else if (idCardBack) {
                                workerData.id_card_back = idCardBack;
                            }
                            
                            if (fatherCardFrontPreview && fatherCardFrontPreview.getAttribute('data-deleted') === 'true') {
                                workerData.father_card_front = null;
                            } else if (fatherCardFront) {
                                workerData.father_card_front = fatherCardFront;
                            }
                            
                            if (fatherCardBackPreview && fatherCardBackPreview.getAttribute('data-deleted') === 'true') {
                                workerData.father_card_back = null;
                            } else if (fatherCardBack) {
                                workerData.father_card_back = fatherCardBack;
                            }
                        } else {
                            // Creating new worker - include all images
                            workerData.id_card_front = idCardFront || null;
                            workerData.id_card_back = idCardBack || null;
                            workerData.father_card_front = fatherCardFront || null;
                            workerData.father_card_back = fatherCardBack || null;
                        }
                        
                        console.log('Image status:', {
                            idCardFront: workerData.id_card_front ? (typeof workerData.id_card_front === 'string' && workerData.id_card_front.startsWith('data:') ? 'new file' : 'preserved') : 'deleted/null',
                            idCardBack: workerData.id_card_back ? (typeof workerData.id_card_back === 'string' && workerData.id_card_back.startsWith('data:') ? 'new file' : 'preserved') : 'deleted/null',
                            fatherCardFront: workerData.father_card_front ? (typeof workerData.father_card_front === 'string' && workerData.father_card_front.startsWith('data:') ? 'new file' : 'preserved') : 'deleted/null',
                            fatherCardBack: workerData.father_card_back ? (typeof workerData.father_card_back === 'string' && workerData.father_card_back.startsWith('data:') ? 'new file' : 'preserved') : 'deleted/null'
                        });
                        
                        console.log('Worker data being sent to API:', JSON.stringify(workerData, null, 2));
                        
                        // Determine API URL and method
                        const apiUrl = editingWorkerId ? API_ROUTES.workers.update(editingWorkerId) : API_ROUTES.workers.store;
                        const method = editingWorkerId ? 'PUT' : 'POST';
                        
                        // Save to backend via API
                        fetch(apiUrl, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(workerData)
                        })
                        .then(response => {
                            console.log('API Response status:', response.status);
                            return response.json();
                        })
                        .then(data => {
                            console.log('API Response data:', data);
                            if (data.success) {
                                console.log('Worker saved to backend:', data.worker);
                                console.log('Saved mobile:', data.worker.mobile);
                                console.log('Saved additional mobiles:', data.worker.additionalMobiles);
                                
                                // Dispatch custom event to notify React component to reload from API
                                window.dispatchEvent(new CustomEvent('workersUpdated'));
                            } else {
                                console.error('Error saving worker:', data.message);
                                alert('Error saving worker: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('API Error:', error);
                            alert('Error saving worker. Please try again.');
                        });
                        
                        // Remove editing attribute
                        if (workerModalOverlay) {
                            workerModalOverlay.removeAttribute('data-editing-worker-id');
                        }
                        console.log('Worker saved successfully');
                        
                        // Ensure modal is closed (in case it wasn't closed earlier) - FORCE CLOSE
                        const workerModalOverlayEl = document.getElementById('workerModalOverlay');
                        if (workerModalOverlayEl) {
                            workerModalOverlayEl.classList.remove('show');
                            workerModalOverlayEl.style.display = 'none';
                            workerModalOverlayEl.style.visibility = 'hidden';
                            workerModalOverlayEl.style.opacity = '0';
                            document.body.style.overflow = '';
                            workerModalOverlayEl.removeAttribute('data-editing-worker-id');
                            console.log('Modal force closed in Promise.then()');
                        }
                        
                        // Reset form and cleanup
                        closeWorkerModal();
                        console.log('Form reset and cleanup completed');
                        
                        // Dispatch custom event to notify React component AFTER modal is closed
                        setTimeout(() => {
                            window.dispatchEvent(new CustomEvent('workersUpdated'));
                            
                            // If All Staff modal is open, refresh it
                            const staffListContainer = document.getElementById('staffListContainer');
                            if (staffListContainer) {
                                // Trigger React to refresh staff list
                                window.dispatchEvent(new CustomEvent('refreshStaffList'));
                            }
                        }, 200);
                    }).catch((error) => {
                        console.error('Error saving worker:', error);
                        alert('Error saving worker. Please try again.');
                        // Close modal even on error - FORCE CLOSE
                        const workerModalOverlayEl = document.getElementById('workerModalOverlay');
                        if (workerModalOverlayEl) {
                            workerModalOverlayEl.classList.remove('show');
                            workerModalOverlayEl.style.display = 'none';
                            workerModalOverlayEl.style.visibility = 'hidden';
                            workerModalOverlayEl.style.opacity = '0';
                            document.body.style.overflow = '';
                            console.log('Modal force closed on error');
                        }
                        // Also call closeWorkerModal
                        closeWorkerModal();
                    });
                });
            }
            
            // Delete Worker Button Handler
            const deleteWorkerBtn = document.getElementById('deleteWorkerBtn');
            if (deleteWorkerBtn) {
                deleteWorkerBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const workerModalOverlay = document.getElementById('workerModalOverlay');
                    const editingWorkerId = workerModalOverlay ? workerModalOverlay.getAttribute('data-editing-worker-id') : null;
                    const workerNameInput = document.getElementById('workerName');
                    const workerName = workerNameInput ? workerNameInput.value.trim().toUpperCase() : '';
                    
                    if (!editingWorkerId) {
                        alert('No worker selected for deletion. Please edit an existing worker first.');
                        return false;
                    }
                    
                    if (confirm(`Are you sure you want to delete worker "${workerName}"? This action cannot be undone.`)) {
                        // Delete from backend via API
                        fetch(API_ROUTES.workers.destroy(editingWorkerId), {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Worker deleted from backend:', editingWorkerId);
                                
                                // Worker deleted from database via API, no localStorage needed
                                
                                // Close modal
                                closeWorkerModal();
                                
                                // Dispatch custom event to notify React component
                                setTimeout(() => {
                                    window.dispatchEvent(new CustomEvent('workersUpdated'));
                                    
                                    // Refresh worker details list if modal is open
                                    const workerDetailsModal = document.getElementById('workerDetailsModalOverlay');
                                    if (workerDetailsModal && workerDetailsModal.classList.contains('show')) {
                                        loadWorkersList();
                                    }
                                }, 100);
                            } else {
                                console.error('Error deleting worker:', data.message);
                                alert('Error deleting worker: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('API Error during delete:', error);
                            alert('Error deleting worker. Please try again.');
                        });
                    }
                    
                    return false;
                }, true); // Use capture phase to ensure it fires
            }
            
            // Also use document-level event delegation as backup for delete button
            document.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('#deleteWorkerBtn');
                if (deleteBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const workerModalOverlay = document.getElementById('workerModalOverlay');
                    const editingWorkerId = workerModalOverlay ? workerModalOverlay.getAttribute('data-editing-worker-id') : null;
                    const workerNameInput = document.getElementById('workerName');
                    const workerName = workerNameInput ? workerNameInput.value.trim().toUpperCase() : '';
                    
                    if (!editingWorkerId) {
                        alert('No worker selected for deletion. Please edit an existing worker first.');
                        return false;
                    }
                    
                    if (confirm(`Are you sure you want to delete worker "${workerName}"? This action cannot be undone.`)) {
                        // Delete from backend via API
                        fetch(API_ROUTES.workers.destroy(editingWorkerId), {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                closeWorkerModal();
                                setTimeout(() => {
                                    window.dispatchEvent(new CustomEvent('workersUpdated'));
                                }, 100);
                            } else {
                                alert('Error deleting worker: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('API Error:', error);
                            alert('Error deleting worker. Please try again.');
                        });
                    }
                    
                    return false;
                }
            }, true);
        });
    </script>
</body>
</html>
