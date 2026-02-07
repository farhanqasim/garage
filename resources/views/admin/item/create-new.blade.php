@extends('layouts.app')
@section('title', 'Create New Item')
@section('content')
@push('scripts')
<script>
// Suppress Cloudflare RUM 404 errors and preload warnings
(function() {
    // Suppress console warnings about preloaded resources
    const originalWarn = console.warn;
    console.warn = function(...args) {
        const message = args.join(' ');
        if (message.includes('preloaded') || 
            message.includes('preload') || 
            message.includes('was preloaded using link preload but not used')) {
            return; // Suppress preload warnings
        }
        if (originalWarn) {
            originalWarn.apply(console, args);
        }
    };
    
    // Suppress console errors for Cloudflare resources
    const originalError = window.onerror;
    window.onerror = function(msg, url, line, col, error) {
        if (url && (url.includes('cdn-cgi/rum') || url.includes('cdn-cgi/scripts') || url.includes('cloudflare'))) {
            return true; // Suppress error
        }
        if (originalError) {
            return originalError.apply(this, arguments);
        }
        return false;
    };
    
    // Suppress fetch errors for Cloudflare
    const originalFetch = window.fetch;
    if (originalFetch) {
        window.fetch = function(url, options) {
            const urlString = typeof url === 'string' ? url : (url && url.url ? url.url : '');
            if (urlString && (
                urlString.includes('cdn-cgi/rum') || 
                urlString.includes('cdn-cgi/scripts') || 
                urlString.includes('cloudflare') ||
                urlString.includes('/cdn-cgi/')
            )) {
                // Return a rejected promise that won't show errors
                return Promise.reject(new Error('Suppressed Cloudflare request')).catch(() => {});
            }
            return originalFetch.apply(this, arguments);
        };
    }
    
    // Suppress script loading errors
    document.addEventListener('error', function(e) {
        if (e.target && e.target.tagName === 'SCRIPT') {
            const src = e.target.src || e.target.getAttribute('src');
            if (src && (src.includes('cdn-cgi/rum') || src.includes('cdn-cgi/scripts') || src.includes('cloudflare'))) {
                e.preventDefault();
                return true;
            }
        }
    }, true);
    
    // Suppress XMLHttpRequest errors for Cloudflare RUM
    const originalXHROpen = XMLHttpRequest.prototype.open;
    const originalXHRSend = XMLHttpRequest.prototype.send;
    
    XMLHttpRequest.prototype.open = function(method, url, ...args) {
        if (typeof url === 'string' && (
            url.includes('cdn-cgi/rum') || 
            url.includes('cdn-cgi/scripts') || 
            url.includes('cloudflare') ||
            url.includes('/cdn-cgi/')
        )) {
            // Store flag to suppress this request
            this._suppressCloudflare = true;
            // Set status to 200 to prevent error
            Object.defineProperty(this, 'status', {
                get: function() { return 200; },
                configurable: true
            });
            Object.defineProperty(this, 'readyState', {
                get: function() { return 4; },
                configurable: true
            });
            Object.defineProperty(this, 'statusText', {
                get: function() { return 'OK'; },
                configurable: true
            });
        }
        return originalXHROpen.apply(this, [method, url, ...args]);
    };
    
    XMLHttpRequest.prototype.send = function(...args) {
        if (this._suppressCloudflare) {
            // Suppress the request silently
            this.onerror = null;
            this.onload = null;
            // Trigger onreadystatechange with success state
            if (this.onreadystatechange) {
                this.onreadystatechange();
            }
            return;
        }
        return originalXHRSend.apply(this, args);
    };
    
    // Also suppress console errors for POST requests to cdn-cgi
    const originalConsoleError = console.error;
    console.error = function(...args) {
        const message = args.join(' ') || '';
        if (typeof message === 'string' && (
            message.includes('cdn-cgi/rum') || 
            message.includes('cdn-cgi/scripts') ||
            message.includes('/cdn-cgi/') ||
            (message.includes('POST') && message.includes('cdn-cgi')) ||
            (message.includes('404') && message.includes('cdn-cgi')) ||
            (message.includes('Failed to load resource') && message.includes('cdn-cgi')) ||
            (message.includes('Failed to load resource') && message.includes('rum')) ||
            (message.includes('the server responded with a status of 404') && message.includes('cdn-cgi'))
        )) {
            return; // Suppress error
        }
        return originalConsoleError.apply(console, args);
    };
    
    // Suppress network errors in console
    const originalConsoleLog = console.log;
    console.log = function(...args) {
        const message = args.join(' ');
        if (typeof message === 'string' && (
            message.includes('cdn-cgi/rum') || 
            message.includes('cdn-cgi/scripts') ||
            message.includes('/cdn-cgi/') ||
            (message.includes('Failed to load resource') && message.includes('cdn-cgi')) ||
            (message.includes('Failed to load resource') && message.includes('rum')) ||
            (message.includes('404') && message.includes('cdn-cgi'))
        )) {
            return; // Suppress log
        }
        return originalConsoleLog.apply(console, args);
    };
    
    // Suppress network errors in browser DevTools
    // Intercept network requests before they're made
    if (window.performance && window.performance.getEntriesByType) {
        const originalGetEntries = window.performance.getEntriesByType;
        window.performance.getEntriesByType = function(type) {
            const entries = originalGetEntries.call(this, type);
            if (type === 'resource') {
                return entries.filter(entry => {
                    const name = entry.name || '';
                    return !name.includes('cdn-cgi/rum') && 
                           !name.includes('cdn-cgi/scripts') &&
                           !name.includes('/cdn-cgi/');
                });
            }
            return entries;
        };
    }
})();
</script>
@endpush
@push('styles')
<style>
    * {
        box-sizing: border-box;
    }
    
    body {
        background: #F3F4F7;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    
    .content {
        max-width: 100%;
        padding: 0;
        margin: 0;
        overflow-x: hidden;
    }
    
    .page-header {
        display: none;
    }
    
    .main-container {
        min-height: 100vh;
        background: #F3F4F7;
        padding-bottom: 40px;
        width: 100%;
    }
    
    /* Header - Responsive */
    .header-section {
        background: white;
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 50;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        width: 100%;
    }
    
    .header-brand {
        text-align: center;
        flex: 1;
    }
    
    .brand-title {
        color: #FF833E;
        font-weight: 900;
        font-size: clamp(18px, 4vw, 24px);
        line-height: 1;
    }
    
    .brand-subtitle {
        color: #344454;
        font-weight: 700;
        font-size: clamp(7px, 1.5vw, 9px);
        text-transform: uppercase;
        letter-spacing: 0.2em;
    }
    
    /* Type Tabs - Responsive */
    .type-tabs {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: clamp(6px, 1.5vw, 8px);
        padding: clamp(12px, 3vw, 16px);
        max-width: 100%;
        overflow-x: auto;
    }
    
    .type-tab {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: clamp(12px, 3vw, 16px);
        border-radius: clamp(12px, 3vw, 16px);
        border: 1px solid transparent;
        background: white;
        color: #9ca3af;
        transition: all 0.2s;
        cursor: pointer;
        min-height: 70px;
        touch-action: manipulation;
    }
    
    .type-tab:active {
        transform: scale(0.98);
    }
    
    .type-tab.active {
        background: #FF833E;
        border-color: #FF833E;
        color: white;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transform: scale(1.02);
    }
    
    .type-tab svg {
        width: clamp(18px, 4vw, 20px);
        height: clamp(18px, 4vw, 20px);
        margin-bottom: 4px;
    }
    
    .type-tab span {
        font-size: clamp(7px, 1.5vw, 8px);
        font-weight: 700;
        text-transform: uppercase;
        text-align: center;
    }
    
    /* Form Card - Responsive */
    .form-card {
        background: white;
        border-radius: clamp(20px, 5vw, 32px);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: clamp(16px, 4vw, 24px);
        margin: 0 clamp(8px, 2vw, 16px) 24px;
        max-width: 100%;
    }
    
    .form-title {
        font-size: clamp(12px, 3vw, 14px);
        font-weight: 900;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 12px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .form-title::before {
        content: '';
        width: 6px;
        height: 16px;
        background: #FF833E;
        border-radius: 9999px;
        flex-shrink: 0;
    }
    
    .form-field {
        margin-bottom: clamp(12px, 3vw, 16px);
    }
    
    .form-label {
        display: block;
        font-size: clamp(9px, 2vw, 10px);
        font-weight: 900;
        color: #6b7280;
        text-transform: uppercase;
        margin-bottom: 6px;
        letter-spacing: 0.05em;
    }
    
    .form-input {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: clamp(8px, 2vw, 12px);
        padding: clamp(10px, 2.5vw, 12px) clamp(14px, 3.5vw, 16px);
        font-size: clamp(13px, 3vw, 14px);
        font-weight: 500;
        background: white;
        outline: none;
        transition: all 0.2s;
        -webkit-appearance: none;
        appearance: none;
    }
    
    .form-input:focus {
        border-color: #FF833E;
        box-shadow: 0 0 0 3px rgba(255, 131, 62, 0.1);
    }
    
    .barcode-group {
        display: flex;
        gap: clamp(6px, 1.5vw, 8px);
        align-items: stretch;
    }
    
    .barcode-input {
        color: #000000 !important;
        flex: 1;
        border: 1px solid #d1d5db;
        border-radius: clamp(8px, 2vw, 12px);
        padding: clamp(10px, 2.5vw, 12px) clamp(14px, 3.5vw, 16px);
        background: #f9fafb;
        font-family: monospace;
        font-size: clamp(16px, 4vw, 18px) !important;
        font-weight: 700 !important;
        min-width: 0;
    }
    
    .refresh-btn {
        background: #FF833E;
        color: white;
        padding: clamp(10px, 2.5vw, 12px) clamp(14px, 3.5vw, 16px);
        border-radius: clamp(8px, 2vw, 12px);
        border: none;
        cursor: pointer;
        transition: transform 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 48px;
        touch-action: manipulation;
    }
    
    .refresh-btn:hover {
        transform: rotate(180deg);
    }
    
    .refresh-btn:active {
        transform: rotate(180deg) scale(0.95);
    }
    
    /* Grid - Responsive */
    .grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: clamp(12px, 3vw, 16px);
    }
    
    /* Vehicle Matching - Responsive */
    .vehicle-matching {
        background: #f8fafc;
        padding: clamp(16px, 4vw, 20px);
        border-radius: clamp(16px, 4vw, 24px);
        border: 1px solid #e2e8f0;
        margin: clamp(16px, 4vw, 20px) 0;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
    }
    
    .vehicle-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .vehicle-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: clamp(9px, 2vw, 10px);
        font-weight: 900;
        color: #FF833E;
        text-transform: uppercase;
        letter-spacing: 0.2em;
    }
    
    .attach-btn {
        background: #FF833E;
        color: white;
        font-size: clamp(8px, 2vw, 9px);
        font-weight: 900;
        padding: clamp(8px, 2vw, 10px) clamp(14px, 3.5vw, 16px);
        border-radius: 9999px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.2s;
        touch-action: manipulation;
        white-space: nowrap;
    }
    
    .attach-btn:active {
        transform: scale(0.95);
    }
    
    .year-warning {
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        background: #fef2f2;
        color: #dc2626;
        padding: clamp(8px, 2vw, 10px);
        border-radius: 8px;
        font-size: clamp(9px, 2vw, 10px);
        font-weight: 700;
        border: 1px solid #fecaca;
    }
    
    .add-year-btn {
        width: 100%;
        margin-top: 8px;
        background: #475569;
        color: white;
        font-size: clamp(8px, 2vw, 9px);
        font-weight: 900;
        padding: clamp(10px, 2.5vw, 12px);
        border-radius: clamp(8px, 2vw, 12px);
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        touch-action: manipulation;
    }
    
    .add-year-btn:active {
        transform: scale(0.98);
    }
    
    .year-ranges {
        display: flex;
        flex-wrap: wrap;
        gap: clamp(6px, 1.5vw, 8px);
        margin-top: 12px;
    }
    
    .year-range-badge {
        background: #fed7aa;
        color: #9a3412;
        padding: clamp(6px, 1.5vw, 8px) clamp(10px, 2.5vw, 12px);
        border-radius: 8px;
        font-size: clamp(8px, 2vw, 9px);
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #fdba74;
    }
    
    .linked-vehicles {
        margin-top: 24px;
        padding-top: 16px;
        border-top: 1px solid #cbd5e1;
    }
    
    .linked-vehicles-title {
        font-size: clamp(9px, 2vw, 10px);
        font-weight: 900;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .vehicle-item {
        background: white;
        border: 1px solid #e2e8f0;
        color: #344454;
        padding: clamp(10px, 2.5vw, 12px);
        border-radius: clamp(8px, 2vw, 12px);
        font-size: clamp(8px, 2vw, 9px);
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        gap: 12px;
    }
    
    .vehicle-item span {
        flex: 1;
        line-height: 1.4;
        word-break: break-word;
    }
    
    .vehicle-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }
    
    .action-btn {
        padding: clamp(6px, 1.5vw, 8px);
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        min-height: 36px;
        touch-action: manipulation;
    }
    
    .edit-btn {
        color: #60a5fa;
        background: transparent;
    }
    
    .edit-btn:hover,
    .edit-btn:active {
        background: #dbeafe;
    }
    
    .delete-btn {
        color: #f87171;
        background: transparent;
    }
    
    .delete-btn:hover,
    .delete-btn:active {
        background: #fee2e2;
    }
    
    .form-actions {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: clamp(10px, 2.5vw, 12px);
        margin-top: clamp(24px, 6vw, 32px);
    }
    
    .btn-cancel {
        background: #f1f5f9;
        color: #64748b;
        font-weight: 900;
        padding: clamp(14px, 3.5vw, 16px);
        border-radius: clamp(12px, 3vw, 16px);
        font-size: clamp(10px, 2.5vw, 11px);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        touch-action: manipulation;
    }
    
    .btn-cancel:active {
        transform: scale(0.98);
    }
    
    .btn-save {
        background: #FF833E;
        color: white;
        font-weight: 900;
        padding: clamp(14px, 3.5vw, 16px);
        border-radius: clamp(12px, 3vw, 16px);
        font-size: clamp(10px, 2.5vw, 11px);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        transition: all 0.2s;
        touch-action: manipulation;
    }
    
    .btn-save:active {
        transform: scale(0.98);
    }
    
    .searchable-select-wrapper {
        position: relative;
    }
    
    .searchable-select-wrapper .select2-container {
        width: 100% !important;
    }
    
    .searchable-select-wrapper .select2-selection {
        border: 1px solid #d1d5db !important;
        border-radius: clamp(8px, 2vw, 12px) !important;
        padding: clamp(10px, 2.5vw, 12px) !important;
        font-size: clamp(13px, 3vw, 14px) !important;
        font-weight: 500 !important;
        background: white !important;
        height: auto !important;
        min-height: 44px;
    }
    
    .searchable-select-wrapper .select2-selection__rendered {
        padding: 0 !important;
        line-height: 1.5;
    }
    
    .edit-icon-btn {
        background: #344454;
        color: white;
        padding: clamp(10px, 2.5vw, 12px);
        border-radius: clamp(6px, 1.5vw, 8px);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.2s;
        min-width: 44px;
        min-height: 44px;
        touch-action: manipulation;
    }
    
    .edit-icon-btn:active {
        transform: scale(0.95);
    }
    
    .edit-icon-btn.has-value {
        background: #0d6efd;
    }
    
    .input-with-edit {
        display: flex;
        gap: clamp(4px, 1vw, 6px);
        align-items: stretch;
    }
    
    .input-with-edit .form-input,
    .input-with-edit .select2-container {
        flex: 1;
        min-width: 0;
    }
    
    /* Select2 Mobile Improvements */
    .select2-dropdown {
        font-size: clamp(13px, 3vw, 14px) !important;
        border-radius: clamp(8px, 2vw, 12px) !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    }
    
    .select2-results__option {
        padding: clamp(10px, 2.5vw, 12px) clamp(14px, 3.5vw, 16px) !important;
        min-height: 44px;
        display: flex;
        align-items: center;
        line-height: 1.5 !important;
    }
    
    .select2-results__option--highlighted {
        background-color: #fff7ed !important;
        color: #FF833E !important;
    }
    
    .select2-search--dropdown .select2-search__field {
        padding: clamp(10px, 2.5vw, 12px) clamp(14px, 3.5vw, 16px) !important;
        font-size: clamp(13px, 3vw, 14px) !important;
        border-radius: clamp(6px, 1.5vw, 8px) !important;
        min-height: 44px;
    }
    
    @media (max-width: 768px) {
        .select2-container--open .select2-dropdown {
            position: fixed !important;
            top: auto !important;
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            max-height: 50vh !important;
            border-radius: 20px 20px 0 0 !important;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.2) !important;
        }
        
        .select2-container--open .select2-dropdown--below {
            border-top: 1px solid #e5e7eb !important;
        }
    }
    
    /* Edit Modal Responsive Styles */
    .edit-modal-container {
        max-width: calc(100vw - 2rem);
        padding: 1.5rem;
        border-radius: 1.5rem;
        transition: all 0.3s ease;
    }
    
    /* Modal Header */
    .edit-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .edit-modal-title {
        font-weight: 900;
        color: #344454;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-size: 10px;
        line-height: 1.2;
    }
    
    .edit-modal-close-btn {
        color: #9ca3af;
        transition: color 0.2s;
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
        flex-shrink: 0;
    }
    
    .edit-modal-close-btn:hover {
        color: #6b7280;
    }
    
    .edit-modal-close-icon {
        width: 18px;
        height: 18px;
    }
    
    /* Modal Input */
    .edit-modal-input {
        width: 100%;
        border: 2px solid #fff7ed;
        border-radius: 0.75rem;
        padding: 0.75rem;
        margin-bottom: 1.5rem;
        font-size: 12px;
        font-weight: 700;
        outline: none;
        box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
        text-transform: uppercase;
    }
    
    .edit-modal-input:focus {
        border-color: #FF833E;
    }
    
    /* Modal Buttons */
    .edit-modal-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .edit-modal-cancel-btn,
    .edit-modal-confirm-btn {
        flex: 1;
        font-weight: 900;
        border-radius: 0.75rem;
        text-transform: uppercase;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        min-height: 44px;
        font-size: 9px;
        padding: 0.75rem;
    }
    
    .edit-modal-cancel-btn {
        background: #f3f4f6;
        color: #9ca3af;
    }
    
    .edit-modal-confirm-btn {
        background: #FF833E;
        color: white;
        box-shadow: 0 10px 15px -3px rgba(255, 131, 62, 0.1), 0 4px 6px -2px rgba(255, 131, 62, 0.05);
    }
    
    /* Mobile - Small (up to 480px) */
    @media (max-width: 480px) {
        .edit-modal-container {
            max-width: calc(100vw - 2rem) !important;
            padding: 1.5rem !important;
            border-radius: 1.5rem !important;
        }
    }
    
    /* Tablet (481px to 768px) */
    @media (min-width: 481px) and (max-width: 768px) {
        .edit-modal-container {
            max-width: 26rem !important;
            padding: 1.75rem !important;
        }
    }
    
    /* Tablet - Large (769px to 1024px) */
    @media (min-width: 769px) and (max-width: 1024px) {
        .edit-modal-container {
            max-width: 30rem !important;
            padding: 2rem !important;
        }
    }
    
    /* PC - Desktop (1025px to 1440px) */
    @media (min-width: 1025px) {
        .edit-modal-container {
            max-width: 35rem !important;
            padding: 2.5rem !important;
            border-radius: 2.5rem !important;
        }
        
        .edit-modal-header {
            margin-bottom: 1.5rem !important;
        }
        
        .edit-modal-title {
            font-size: 14px !important;
        }
        
        .edit-modal-close-icon {
            width: 24px !important;
            height: 24px !important;
        }
        
        .edit-modal-input {
            font-size: 16px !important;
            padding: 1.25rem !important;
            margin-bottom: 2rem !important;
            border-radius: 1rem !important;
        }
        
        .edit-modal-buttons {
            gap: 0.75rem !important;
        }
        
        .edit-modal-cancel-btn,
        .edit-modal-confirm-btn {
            font-size: 12px !important;
            padding: 1.25rem !important;
            min-height: 48px !important;
            border-radius: 1rem !important;
        }
    }
    
    /* PC - Large Desktop (1441px and above) */
    @media (min-width: 1441px) {
        .edit-modal-container {
            max-width: 40rem !important;
            padding: 3rem !important;
            border-radius: 2.5rem !important;
        }
        
        .edit-modal-header {
            margin-bottom: 2rem !important;
        }
        
        .edit-modal-title {
            font-size: 16px !important;
        }
        
        .edit-modal-close-icon {
            width: 28px !important;
            height: 28px !important;
        }
        
        .edit-modal-input {
            font-size: 18px !important;
            padding: 1.5rem !important;
            margin-bottom: 2.5rem !important;
        }
        
        .edit-modal-buttons {
            gap: 1rem !important;
        }
        
        .edit-modal-cancel-btn,
        .edit-modal-confirm-btn {
            font-size: 13px !important;
            padding: 1.5rem !important;
            min-height: 52px !important;
        }
    }
    
    /* PC - Extra Large (1920px and above) */
    @media (min-width: 1920px) {
        .edit-modal-container {
            max-width: 45rem !important;
            padding: 3.5rem !important;
            border-radius: 3rem !important;
        }
        
        .edit-modal-header {
            margin-bottom: 2.5rem !important;
        }
        
        .edit-modal-title {
            font-size: 18px !important;
        }
        
        .edit-modal-close-icon {
            width: 32px !important;
            height: 32px !important;
        }
        
        .edit-modal-input {
            font-size: 20px !important;
            padding: 1.75rem !important;
            margin-bottom: 3rem !important;
        }
        
        .edit-modal-buttons {
            gap: 1.25rem !important;
        }
        
        .edit-modal-cancel-btn,
        .edit-modal-confirm-btn {
            font-size: 14px !important;
            padding: 1.75rem !important;
            min-height: 56px !important;
        }
    }
    
    /* Modal Backdrop Padding for PC */
    @media (min-width: 1025px) {
        div[x-show="showEditModal"] {
            padding: 2rem !important;
        }
    }
    
    @media (min-width: 1441px) {
        div[x-show="showEditModal"] {
            padding: 3rem !important;
        }
    }
    
    /* Responsive Breakpoints */
    
    /* Mobile - Small (up to 480px) */
    @media (max-width: 480px) {
        .type-tabs {
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
            padding: 12px;
        }
        
        .type-tab {
            padding: 10px 6px;
            min-height: 60px;
        }
        
        .grid-2 {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        
        .form-card {
            margin: 0 8px 16px;
            padding: 16px;
            border-radius: 20px;
        }
        
        .vehicle-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .attach-btn {
            width: 100%;
            justify-content: center;
        }
        
        .form-actions {
            grid-template-columns: 1fr;
        }
        
        .header-section {
            padding: 10px 12px;
        }
    }
    
    /* Mobile - Medium (481px to 768px) */
    @media (min-width: 481px) and (max-width: 768px) {
        .type-tabs {
            grid-template-columns: repeat(3, 1fr);
        }
        
        .grid-2 {
            grid-template-columns: 1fr;
        }
        
        .form-card {
            margin: 0 12px 20px;
            padding: 20px;
        }
    }
    
    /* Tablet (769px to 1024px) */
    @media (min-width: 769px) and (max-width: 1024px) {
        .type-tabs {
            grid-template-columns: repeat(5, 1fr);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-card {
            max-width: 800px;
            margin: 0 auto 24px;
        }
        
        main {
            max-width: 800px !important;
        }
    }
    
    /* Desktop (1025px and above) */
    @media (min-width: 1025px) {
        .type-tabs {
            max-width: 896px;
            margin: 0 auto;
        }
        
        .form-card {
            max-width: 896px;
            margin: 0 auto 24px;
        }
        
        main {
            max-width: 896px !important;
        }
        
        .type-tab:hover:not(.active) {
            background: #f9fafb;
            border-color: #e5e7eb;
        }
        
        .edit-btn:hover {
            background: #dbeafe;
        }
        
        .delete-btn:hover {
            background: #fee2e2;
        }
    }
    
    /* Large Desktop (1440px and above) */
    @media (min-width: 1440px) {
        main {
            max-width: 1200px !important;
        }
        
        .form-card {
            max-width: 1200px;
        }
        
        .type-tabs {
            max-width: 1200px;
        }
    }
    
    /* Touch Device Optimizations */
    @media (hover: none) and (pointer: coarse) {
        .type-tab,
        .attach-btn,
        .add-year-btn,
        .btn-cancel,
        .btn-save,
        .action-btn,
        .edit-icon-btn,
        .refresh-btn {
            -webkit-tap-highlight-color: transparent;
            user-select: none;
            -webkit-user-select: none;
        }
        
        .form-input,
        .searchable-select-wrapper .select2-selection {
            font-size: 16px !important; /* Prevents zoom on iOS */
        }
        
        .barcode-input {
            color: #000000 !important;
            font-size: 18px !important;
            font-weight: 700 !important;
        }
        
        /* Larger touch targets for mobile */
        .action-btn {
            min-width: 44px;
            min-height: 44px;
        }
    }
    
    /* Prevent text selection on buttons */
    button,
    .type-tab {
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
    }
    
    /* Smooth scrolling */
    html {
        scroll-behavior: smooth;
    }
    
    /* Prevent horizontal scroll */
    body,
    .main-container,
    .content {
        overflow-x: hidden;
        max-width: 100vw;
    }
    
    /* Better focus states for accessibility */
    button:focus-visible,
    .type-tab:focus-visible,
    .form-input:focus-visible {
        outline: 2px solid #FF833E;
        outline-offset: 2px;
    }
    
    /* Loading state improvements */
    .form-card {
        transition: opacity 0.3s ease;
    }
    
    /* Better spacing for long text */
    .vehicle-item span {
        overflow-wrap: break-word;
        word-wrap: break-word;
        hyphens: auto;
    }
    
    /* Print Styles */
    @media print {
        .header-section,
        .type-tabs,
        .form-actions,
        .attach-btn,
        .action-btn {
            display: none;
        }
        
        .form-card {
            box-shadow: none;
            border: 1px solid #ccc;
        }
    }
</style>
@endpush

<div class="main-container" x-data="productForm()">
    <!-- Main Content -->
    <main style="padding: clamp(12px, 3vw, 16px); max-width: 896px; margin: 0 auto; width: 100%; box-sizing: border-box;">
        <!-- Form Card -->
        <div class="form-card">
            <form id="newItemForm">
                @csrf
                
                <!-- Product Bar Code -->
                <div class="form-field">
                    <label class="form-label">Product Bar Code:</label>
                    <div class="barcode-group">
                        <!-- Hidden input for Alpine.js binding -->
                        <input type="hidden" x-model="formData.barcode" id="barcode_hidden" />
                        <!-- Visible input managed by pure JavaScript -->
                        <input 
                            type="text" 
                            id="barcode_input_field"
                            class="barcode-input" 
                            readonly
                        />
                        <button 
                            type="button"
                            onclick="window.generateNewBarcode()"
                            class="refresh-btn"
                        >
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Group Name -->
                <div class="form-field">
                    <label class="form-label">Group Name:</label>
                    <div class="input-with-edit">
                        <button 
                            type="button"
                            class="edit-icon-btn"
                            :class="{ 'has-value': formData.groupName }"
                            @click.stop.prevent="openEditModal('groupName', 'Group Name', $event)"
                            @mousedown.stop.prevent
                            style="pointer-events: auto !important; z-index: 10 !important; position: relative !important;"
                        >
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <select 
                            class="form-input searchable-select" 
                            id="group_select"
                            x-model="formData.groupName"
                            @change="updateEditButtonColor('group_select')"
                        >
                            <option value="">Please Select</option>
                            @if(isset($groups) && $groups->count() > 0)
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <!-- Company -->
                <div class="form-field">
                    <label class="form-label">Company:</label>
                    <div class="input-with-edit">
                        <button 
                            type="button"
                            class="edit-icon-btn"
                            :class="{ 'has-value': formData.company }"
                            @click.stop="openEditModal('company', 'Company')"
                            @mousedown.stop
                        >
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <select 
                            class="form-input searchable-select" 
                            id="company_select"
                            x-model="formData.company"
                            @change="updateEditButtonColor('company_select')"
                        >
                            <option value="">Please Select</option>
                            @foreach($Companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Product Name -->
                <div class="form-field">
                    <label class="form-label" style="font-size: 14px; font-weight: 900; letter-spacing: 0.08em;">Product Name:</label>
                    <div class="input-with-edit">
                        <button 
                            type="button"
                            class="edit-icon-btn"
                            :class="{ 'has-value': formData.productName }"
                            @click="openEditModal('productName', 'Product Name')"
                        >
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <select 
                            class="form-input searchable-select" 
                            id="product_name_select"
                            x-model="formData.productName"
                            @change="updateEditButtonColor('product_name_select')"
                        >
                            <option value="">Please Select</option>
                            @foreach($product as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Grid Fields for Battery Type -->
                <div class="grid-2">
                        <div class="form-field">
                            <label class="form-label">Plates:</label>
                            <select class="form-input searchable-select" id="plates_select" x-model="formData.plates">
                                <option value="">Please Select</option>
                                @foreach($platos as $plato)
                                    <option value="{{ $plato->id }}">{{ $plato->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Amperes:</label>
                            <select class="form-input searchable-select" id="amperes_select" x-model="formData.amperes">
                                <option value="">Please Select</option>
                                @foreach($amphors as $amphor)
                                    <option value="{{ $amphor->id }}">{{ $amphor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Volt:</label>
                            <select class="form-input searchable-select" id="volt_select" x-model="formData.volt">
                                <option value="">Please Select</option>
                                @foreach($volts as $volt)
                                    <option value="{{ $volt->id }}">{{ $volt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="form-label">CCA:</label>
                            <select class="form-input searchable-select" id="cca_select" x-model="formData.cca">
                                <option value="">Please Select</option>
                                @foreach($ccas as $cca)
                                    <option value="{{ $cca->id }}">{{ $cca->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Minus Pole:</label>
                            <select class="form-input searchable-select" id="minus_pole_select" x-model="formData.minusPole">
                                <option value="">Please Select</option>
                                @foreach($minspols as $minspol)
                                    <option value="{{ $minspol->id }}">{{ $minspol->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Technology:</label>
                            <select class="form-input searchable-select" id="technology_select" x-model="formData.technology">
                                <option value="">Please Select</option>
                                @foreach($technologies as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Made In:</label>
                            <select class="form-input searchable-select" id="made_in_select" x-model="formData.madeIn">
                                <option value="">Please Select</option>
                                @foreach($made_ins as $madeIn)
                                    <option value="{{ $madeIn->id }}">{{ $madeIn->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Warranty:</label>
                            <select class="form-input searchable-select" id="warranty_select" x-model="formData.warranty">
                                <option value="">Please Select</option>
                                @foreach($warrenties as $warranty)
                                    <option value="{{ $warranty->id }}">{{ $warranty->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Unit:</label>
                            <select class="form-input searchable-select" id="unit_select" x-model="formData.unit">
                                <option value="">Please Select</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                <!-- Sale Price -->
                <div class="form-field" style="border-top: 1px solid #e5e7eb; padding-top: 16px; margin-top: 16px;">
                    <label class="form-label">Sale Price:</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 12px; top: 10px; color: #9ca3af; font-size: 14px; font-weight: 700;">Rs.</span>
                        <input 
                            type="number" 
                            x-model="formData.salePrice" 
                            class="form-input" 
                            style="padding-left: 40px; font-weight: 700; color: #ea580c;"
                            placeholder="0.00"
                        />
                    </div>
                </div>

                <!-- Opening Stock -->
                <div class="form-field" style="margin-top: 16px;">
                    <label class="form-label">Opening Stock:</label>
                    <input 
                        type="number" 
                        x-model="formData.openingStock" 
                        class="form-input" 
                        style="font-weight: 700;"
                    />
                </div>

                <!-- Vehicle Matching Section -->
                <div class="vehicle-matching">
                        <div class="vehicle-header">
                            <div class="vehicle-title">
                                <svg width="16" height="16" fill="none" stroke="#FF833E" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                <span>Vehicle Matching:</span>
                            </div>
                            <button type="button" @click="handleAttachBtn" class="attach-btn">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Attach Set
                            </button>
                        </div>

                        <div class="grid-2">
                            <div class="form-field">
                                <label class="form-label" style="font-size: 9px;">
                                    <svg width="12" height="12" fill="none" stroke="#9ca3af" viewBox="0 0 24 24" style="display: inline; vertical-align: middle; margin-right: 4px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Brand:
                                </label>
                                <select class="form-input searchable-select" id="vBrand_select" x-model="formData.vBrand">
                                    <option value="">Select Brand</option>
                                    @foreach($carManufacturers as $manufacturer)
                                        <option value="{{ $manufacturer->id }}">{{ $manufacturer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-field">
                                <label class="form-label" style="font-size: 9px;">
                                    <svg width="12" height="12" fill="none" stroke="#9ca3af" viewBox="0 0 24 24" style="display: inline; vertical-align: middle; margin-right: 4px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                    Model:
                                </label>
                                <select class="form-input searchable-select" id="vModel_select" x-model="formData.vModel">
                                    <option value="">Select Model</option>
                                    @foreach($carModels as $model)
                                        <option value="{{ $model->id }}" data-manufacturer-id="{{ $model->car_manufacturer_id }}">{{ $model->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-field">
                                <label class="form-label" style="font-size: 9px;">
                                    <svg width="12" height="12" fill="none" stroke="#9ca3af" viewBox="0 0 24 24" style="display: inline; vertical-align: middle; margin-right: 4px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    CC:
                                </label>
                                <select class="form-input searchable-select" id="vCC_select" x-model="formData.vCC">
                                    <option value="">Select CC</option>
                                    @foreach($engineccs as $enginecc)
                                        <option value="{{ $enginecc->id }}">{{ $enginecc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid-2" style="margin-top: 8px; padding-top: 12px; border-top: 1px solid #cbd5e1;">
                            <div class="form-field">
                                <label class="form-label" style="font-size: 9px;">
                                    <svg width="12" height="12" fill="none" stroke="#9ca3af" viewBox="0 0 24 24" style="display: inline; vertical-align: middle; margin-right: 4px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Year From:
                                </label>
                                <select class="form-input searchable-select" id="vYearFrom_select" x-model="formData.vYearFrom">
                                    <option value="">Select Year</option>
                                    @for($year = 2030; $year >= 1990; $year--)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="form-field">
                                <label class="form-label" style="font-size: 9px;">
                                    <svg width="12" height="12" fill="none" stroke="#9ca3af" viewBox="0 0 24 24" style="display: inline; vertical-align: middle; margin-right: 4px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                    Year To:
                                </label>
                                <select class="form-input searchable-select" id="vYearTo_select" x-model="formData.vYearTo">
                                    <option value="">Select Year</option>
                                    @for($year = 2030; $year >= 1990; $year--)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div x-show="yearWarning" class="year-warning">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span x-text="yearWarning"></span>
                        </div>

                        <button type="button" @click="addYearRangeToSet" class="add-year-btn">Add Year Range</button>

                        <div class="year-ranges">
                            <template x-for="range in formData.tempYearRanges" :key="range">
                                <div class="year-range-badge">
                                    <span x-text="range"></span>
                                    <button type="button" @click="removeTempYearRange(range)" style="background: none; border: none; cursor: pointer; padding: 0;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="linked-vehicles">
                            <div class="linked-vehicles-title">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                Linked Vehicles:
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <template x-if="formData.compatibleVehicles.length > 0">
                                    <template x-for="v in formData.compatibleVehicles" :key="v">
                                        <div class="vehicle-item">
                                            <span x-text="v" style="line-height: 1.4;"></span>
                                            <div class="vehicle-actions">
                                                <button type="button" @click="handleEditVehicle(v)" class="action-btn edit-btn">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>
                                                <button type="button" @click="removeLinkedVehicle(v)" class="action-btn delete-btn">
                                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </template>
                                <template x-if="formData.compatibleVehicles.length === 0">
                                    <div style="font-size: 9px; color: #94a3b8; font-style: italic; text-align: center; padding: 12px; font-weight: 500;">
                                        Abhi tak koi gaadi attach nahi ki gayi.
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button 
                        type="button" 
                        @click="resetForm()"
                        class="btn-cancel"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button" 
                        @click="handleSave()"
                        class="btn-save"
                    >
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span x-text="editingItemId ? 'Update Item' : 'Save Item'"></span>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Edit Modal (React Component Style) -->
    <div x-show="showEditModal" 
         x-cloak
         class="fixed inset-0 bg-black/60 z-[100] flex items-center justify-center p-4 backdrop-blur-sm"
         style="position: fixed; inset: 0; background: rgba(0, 0, 0, 0.6); z-index: 100; display: flex; align-items: center; justify-content: center; padding: clamp(0.5rem, 2vw, 2rem); backdrop-filter: blur(4px);"
         @click.self="closeEditModal()"
         @keydown.escape.window="closeEditModal()">
        <div class="bg-white w-full rounded-[2.5rem] shadow-2xl edit-modal-container"
             style="background: white; width: 100%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            <div class="flex justify-between mb-6 edit-modal-header">
                <h3 class="font-black text-[#344454] uppercase tracking-widest edit-modal-title">
                    <span x-text="editModalType === 'add' ? 'Add New' : 'Edit'"></span> - <span x-text="editModalTarget.label"></span>
                </h3>
                <button @click="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors edit-modal-close-btn">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="edit-modal-close-icon">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <input 
                id="editModalInput"
                type="text" 
                x-model="editModalValue"
                @keydown.enter="handleEditModalConfirm()"
                class="w-full border-2 border-orange-50 rounded-2xl font-bold focus:border-[#FF833E] outline-none shadow-inner uppercase edit-modal-input"
                placeholder="Type here..."
                autofocus
            />
            <div class="flex gap-3 edit-modal-buttons">
                <button 
                    @click="closeEditModal()" 
                    class="flex-1 bg-gray-100 text-gray-400 font-black rounded-2xl uppercase transition-all active:scale-95 edit-modal-cancel-btn"
                >
                    Cancel
                </button>
                <button 
                    @click="handleEditModalConfirm()" 
                    class="flex-1 bg-[#FF833E] text-white font-black rounded-2xl uppercase active:scale-95 transition-all shadow-lg shadow-orange-100 edit-modal-confirm-btn"
                >
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function productForm() {
    const yearsList = Array.from({ length: 41 }, (_, i) => (2030 - i).toString());
    
    // Generate initial barcode immediately and store in sessionStorage to persist across refreshes
    const generateBarcodeValue = () => {
        return Math.floor(Math.random() * 10000000000).toString().padStart(10, '0');
    };
    
    // Get or create barcode from sessionStorage
    let initialBarcode = sessionStorage.getItem('current_barcode');
    if (!initialBarcode || initialBarcode.length !== 10) {
        initialBarcode = generateBarcodeValue();
        sessionStorage.setItem('current_barcode', initialBarcode);
    }
    
    // Set barcode in input field IMMEDIATELY before Alpine.js
    setTimeout(() => {
        const barcodeInput = document.getElementById('barcode_input_field');
        if (barcodeInput) {
            barcodeInput.value = initialBarcode;
            barcodeInput.setAttribute('data-barcode', initialBarcode);
        }
    }, 0);
    
    return {
        editingItemId: null,
        yearWarning: '',
        showEditModal: false,
        editModalType: 'edit',
        editModalTarget: { name: '', label: '', selectId: '', valueId: '' },
        editModalValue: '',
        formData: {
            barcode: initialBarcode,
            groupName: '',
            company: '',
            productName: '',
            plates: '',
            amperes: '',
            volt: '',
            cca: '',
            technology: '',
            madeIn: '',
            unit: 'Pcs',
            minusPole: '',
            warranty: '',
            salePrice: '',
            openingStock: '',
            vBrand: '',
            vModel: '',
            vCC: '',
            vYearFrom: '',
            vYearTo: '',
            tempYearRanges: [],
            compatibleVehicles: []
        },
        initialFormState: {
            barcode: initialBarcode,
            groupName: '',
            company: '',
            productName: '',
            plates: '',
            amperes: '',
            volt: '',
            cca: '',
            technology: '',
            madeIn: '',
            unit: 'Pcs',
            minusPole: '',
            warranty: '',
            salePrice: '',
            openingStock: '',
            vBrand: '',
            vModel: '',
            vCC: '',
            vYearFrom: '',
            vYearTo: '',
            tempYearRanges: [],
            compatibleVehicles: []
        },
        init() {
            // Get barcode from sessionStorage or use initial
            let savedBarcode = sessionStorage.getItem('current_barcode') || this.formData.barcode;
            
            // Validate
            if (!savedBarcode || savedBarcode.length !== 10) {
                savedBarcode = this.generateBarcode();
                sessionStorage.setItem('current_barcode', savedBarcode);
            }
            
            // RESTORE FORM DATA FROM localStorage (if exists) - DO THIS FIRST
            // Check both form_data_draft and last_saved_item
            let savedFormData = localStorage.getItem('form_data_draft');
            const lastSavedItem = localStorage.getItem('last_saved_item');
            
            // If no draft but last saved item exists, use that
            if (!savedFormData && lastSavedItem) {
                try {
                    const lastSaved = JSON.parse(lastSavedItem);
                    // Convert last saved item to draft format (exclude barcode)
                    savedFormData = JSON.stringify({
                        groupName: lastSaved.groupName || '',
                        company: lastSaved.company || '',
                        productName: lastSaved.productName || '',
                        plates: lastSaved.plates || '',
                        amperes: lastSaved.amperes || '',
                        volt: lastSaved.volt || '',
                        cca: lastSaved.cca || '',
                        minusPole: lastSaved.minusPole || '',
                        technology: lastSaved.technology || '',
                        madeIn: lastSaved.madeIn || '',
                        warranty: lastSaved.warranty || '',
                        unit: lastSaved.unit || '',
                        salePrice: lastSaved.salePrice || '',
                        openingStock: lastSaved.openingStock || '',
                        compatibleVehicles: lastSaved.compatibleVehicles || [],
                        tempYearRanges: lastSaved.tempYearRanges || []
                    });
                    // Also save as draft for future use
                    localStorage.setItem('form_data_draft', savedFormData);
                } catch(e) {
                    console.log('Error parsing last saved item:', e);
                }
            }
            
            if (savedFormData) {
                try {
                    const parsedData = JSON.parse(savedFormData);
                    // Restore all form fields IMMEDIATELY (don't wait)
                    if (parsedData.groupName !== undefined && parsedData.groupName !== null && parsedData.groupName !== '') {
                        this.formData.groupName = parsedData.groupName;
                    }
                    if (parsedData.company !== undefined && parsedData.company !== null && parsedData.company !== '') {
                        this.formData.company = parsedData.company;
                    }
                    if (parsedData.productName !== undefined && parsedData.productName !== null && parsedData.productName !== '') {
                        this.formData.productName = parsedData.productName;
                    }
                    if (parsedData.plates !== undefined && parsedData.plates !== null && parsedData.plates !== '') {
                        this.formData.plates = parsedData.plates;
                    }
                    if (parsedData.amperes !== undefined && parsedData.amperes !== null && parsedData.amperes !== '') {
                        this.formData.amperes = parsedData.amperes;
                    }
                    if (parsedData.volt !== undefined && parsedData.volt !== null && parsedData.volt !== '') {
                        this.formData.volt = parsedData.volt;
                    }
                    if (parsedData.cca !== undefined && parsedData.cca !== null && parsedData.cca !== '') {
                        this.formData.cca = parsedData.cca;
                    }
                    if (parsedData.minusPole !== undefined && parsedData.minusPole !== null && parsedData.minusPole !== '') {
                        this.formData.minusPole = parsedData.minusPole;
                    }
                    if (parsedData.technology !== undefined && parsedData.technology !== null && parsedData.technology !== '') {
                        this.formData.technology = parsedData.technology;
                    }
                    if (parsedData.madeIn !== undefined && parsedData.madeIn !== null && parsedData.madeIn !== '') {
                        this.formData.madeIn = parsedData.madeIn;
                    }
                    if (parsedData.warranty !== undefined && parsedData.warranty !== null && parsedData.warranty !== '') {
                        this.formData.warranty = parsedData.warranty;
                    }
                    if (parsedData.unit !== undefined && parsedData.unit !== null && parsedData.unit !== '') {
                        this.formData.unit = parsedData.unit;
                    }
                    if (parsedData.salePrice !== undefined && parsedData.salePrice !== null && parsedData.salePrice !== '') {
                        this.formData.salePrice = parsedData.salePrice;
                    }
                    if (parsedData.openingStock !== undefined && parsedData.openingStock !== null && parsedData.openingStock !== '') {
                        this.formData.openingStock = parsedData.openingStock;
                    }
                    if (parsedData.compatibleVehicles && Array.isArray(parsedData.compatibleVehicles)) {
                        this.formData.compatibleVehicles = parsedData.compatibleVehicles;
                    }
                    if (parsedData.tempYearRanges && Array.isArray(parsedData.tempYearRanges)) {
                        this.formData.tempYearRanges = parsedData.tempYearRanges;
                    }
                    
                    // Restore Select2 values with multiple attempts to ensure they're visible
                    // Make function accessible globally for monitoring
                    window.restoreSelect2Values = () => {
                        // Prioritize last_saved_item over form_data_draft
                        let savedData = localStorage.getItem('last_saved_item');
                        if (!savedData) {
                            savedData = localStorage.getItem('form_data_draft');
                        }
                        if (!savedData) return;
                        
                        let parsedData;
                        try {
                            parsedData = JSON.parse(savedData);
                        } catch(e) {
                            return;
                        }
                        
                        const restoreSelect = (selectId, value) => {
                            if (!value || value === '' || value === null || value === undefined) return;
                            const $select = $('#' + selectId);
                            if ($select.length) {
                                // Wait for Select2 to be initialized
                                if (!$select.data('select2')) {
                                    // If Select2 not initialized, try again later (max 5 attempts)
                                    if (!window._restoreAttempts) window._restoreAttempts = {};
                                    if (!window._restoreAttempts[selectId]) window._restoreAttempts[selectId] = 0;
                                    if (window._restoreAttempts[selectId] < 5) {
                                        window._restoreAttempts[selectId]++;
                                        setTimeout(() => restoreSelect(selectId, value), 200);
                                    }
                                    return;
                                }
                                
                                // Get option text before setting value (this should be the name, not ID)
                                const $option = $select.find('option[value="' + value + '"]');
                                let optionText = $option.length ? $option.text().trim() : value;
                                
                                // If optionText is empty or same as value (ID), try to get it from Select2 data
                                if (!optionText || optionText === value || optionText === '') {
                                    // Try to get text from Select2's data
                                    const select2Data = $select.data('select2');
                                    if (select2Data && select2Data.data) {
                                        const optionData = select2Data.data.find(function(item) {
                                            return item.id == value;
                                        });
                                        if (optionData && optionData.text) {
                                            optionText = optionData.text;
                                        }
                                    }
                                    // If still empty, use value as fallback
                                    if (!optionText || optionText === '') {
                                        optionText = value;
                                    }
                                }
                                
                                // Set value in Select2
                                $select.val(value);
                                $select.trigger('change.select2');
                                $select.trigger('change');
                                
                                // Force update the rendered display immediately with the actual option text (name, not ID)
                                const $container = $select.next('.select2-container');
                                if ($container.length) {
                                    const $rendered = $container.find('.select2-selection__rendered');
                                    if ($rendered.length) {
                                        // Always update display text with the actual option text (name, not ID)
                                        $rendered.text(optionText);
                                        $rendered.attr('title', optionText);
                                        
                                        // Also update the container title
                                        $container.find('.select2-selection').attr('title', optionText);
                                        
                                        // Force a re-render by triggering Select2 update
                                        $select.trigger('select2:select');
                                    }
                                }
                                
                                // Additional force update after a short delay to ensure text is correct (name, not ID)
                                setTimeout(() => {
                                    const $container2 = $select.next('.select2-container');
                                    if ($container2.length) {
                                        const $rendered2 = $container2.find('.select2-selection__rendered');
                                        if ($rendered2.length) {
                                            // Get fresh option text
                                            const $option2 = $select.find('option[value="' + value + '"]');
                                            const freshOptionText = $option2.length ? $option2.text().trim() : optionText;
                                            const currentText = $rendered2.text().trim();
                                            
                                            // Only update if current text is wrong (ID instead of name)
                                            if (currentText === 'Please Select' || currentText === '' || currentText === value || currentText !== freshOptionText) {
                                                $rendered2.text(freshOptionText);
                                                $rendered2.attr('title', freshOptionText);
                                                $container2.find('.select2-selection').attr('title', freshOptionText);
                                            }
                                        }
                                    }
                                }, 100);
                                
                                // Also update Alpine.js
                                try {
                                    const alpineComponent = Alpine.$data($select[0].closest('[x-data]'));
                                    if (alpineComponent && alpineComponent.formData) {
                                        const fieldMap = {
                                            'group_select': 'groupName',
                                            'company_select': 'company',
                                            'product_name_select': 'productName',
                                            'plates_select': 'plates',
                                            'amperes_select': 'amperes',
                                            'volt_select': 'volt',
                                            'cca_select': 'cca',
                                            'minus_pole_select': 'minusPole',
                                            'technology_select': 'technology',
                                            'made_in_select': 'madeIn',
                                            'warranty_select': 'warranty',
                                            'unit_select': 'unit'
                                        };
                                        const fieldName = fieldMap[selectId];
                                        if (fieldName) {
                                            alpineComponent.formData[fieldName] = value;
                                        }
                                    }
                                } catch(e) {
                                    console.log('Alpine sync error:', e);
                                }
                            }
                        };
                        
                        if (parsedData.groupName) restoreSelect('group_select', parsedData.groupName);
                        if (parsedData.company) restoreSelect('company_select', parsedData.company);
                        if (parsedData.productName) restoreSelect('product_name_select', parsedData.productName);
                        if (parsedData.plates) restoreSelect('plates_select', parsedData.plates);
                        if (parsedData.amperes) restoreSelect('amperes_select', parsedData.amperes);
                        if (parsedData.volt) restoreSelect('volt_select', parsedData.volt);
                        if (parsedData.cca) restoreSelect('cca_select', parsedData.cca);
                        if (parsedData.minusPole) restoreSelect('minus_pole_select', parsedData.minusPole);
                        if (parsedData.technology) restoreSelect('technology_select', parsedData.technology);
                        if (parsedData.madeIn) restoreSelect('made_in_select', parsedData.madeIn);
                        if (parsedData.warranty) restoreSelect('warranty_select', parsedData.warranty);
                        if (parsedData.unit) restoreSelect('unit_select', parsedData.unit);
                    };
                    
                    // Call restore function immediately
                    window.restoreSelect2Values();
                    
                    // Try multiple times with different delays to ensure Select2 is initialized
                    setTimeout(window.restoreSelect2Values, 100);
                    setTimeout(window.restoreSelect2Values, 300);
                    setTimeout(window.restoreSelect2Values, 500);
                    setTimeout(window.restoreSelect2Values, 800);
                    setTimeout(window.restoreSelect2Values, 1000);
                    setTimeout(window.restoreSelect2Values, 1500);
                    setTimeout(window.restoreSelect2Values, 2000);
                    setTimeout(window.restoreSelect2Values, 3000);
                    setTimeout(window.restoreSelect2Values, 4000);
                    
                    // Also try after Select2 initialization events
                    $(document).on('select2:ready', window.restoreSelect2Values);
                    $(document).on('select2:open', function() {
                        // Restore when dropdown opens (in case values were cleared)
                        setTimeout(window.restoreSelect2Values, 50);
                    });
                    
                    // Also restore on page visibility change (when user comes back to tab)
                    document.addEventListener('visibilitychange', function() {
                        if (!document.hidden) {
                            setTimeout(window.restoreSelect2Values, 100);
                        }
                    });
                    
                    // Light monitoring - only check if values are missing (reduced frequency)
                    let restoreCheckCount = 0;
                    const monitorInterval = setInterval(() => {
                        restoreCheckCount++;
                        // Only check every 10 seconds and max 6 times (1 minute total)
                        if (restoreCheckCount <= 6 && restoreCheckCount % 5 === 0) {
                            if (typeof window.restoreSelect2Values === 'function') {
                                // Only restore if values are actually missing
                                const savedData = localStorage.getItem('form_data_draft');
                                if (savedData) {
                                    try {
                                        const parsedData = JSON.parse(savedData);
                                        // Check if any Select2 value is missing
                                        const $groupSelect = $('#group_select');
                                        if (parsedData.groupName && (!$groupSelect.val() || $groupSelect.val() !== parsedData.groupName)) {
                                            window.restoreSelect2Values();
                                            return;
                                        }
                                    } catch(e) {
                                        // Ignore
                                    }
                                }
                            }
                        }
                        // Stop monitoring after 1 minute
                        if (restoreCheckCount >= 6) {
                            clearInterval(monitorInterval);
                        }
                    }, 10000); // Check every 10 seconds instead of 2
                    
                    // Store interval ID for cleanup if needed
                    this._restoreMonitorInterval = monitorInterval;
                } catch(e) {
                    console.log('Could not restore form data:', e);
                }
            }
            
            // Set in formData
            this.formData.barcode = savedBarcode;
            this.initialFormState.barcode = savedBarcode;
            
            // AUTO-SAVE: Save form data to localStorage whenever it changes (debounced to prevent timeout)
            let saveTimeout = null;
            this.$watch('formData', (newData) => {
                // Debounce the save operation to prevent too frequent writes
                if (saveTimeout) {
                    clearTimeout(saveTimeout);
                }
                saveTimeout = setTimeout(() => {
                    // Don't save barcode to form draft (it's handled separately)
                    const dataToSave = {
                        groupName: newData.groupName,
                        company: newData.company,
                        productName: newData.productName,
                        plates: newData.plates,
                        amperes: newData.amperes,
                        volt: newData.volt,
                        cca: newData.cca,
                        minusPole: newData.minusPole,
                        technology: newData.technology,
                        madeIn: newData.madeIn,
                        warranty: newData.warranty,
                        unit: newData.unit,
                        salePrice: newData.salePrice,
                        openingStock: newData.openingStock,
                        compatibleVehicles: newData.compatibleVehicles,
                        tempYearRanges: newData.tempYearRanges
                    };
                    localStorage.setItem('form_data_draft', JSON.stringify(dataToSave));
                }, 500); // Debounce: save only after 500ms of no changes
            }, { deep: true });
            
            // Set input field IMMEDIATELY - don't wait for $nextTick
            const barcodeInput = document.getElementById('barcode_input_field');
            if (barcodeInput) {
                barcodeInput.value = savedBarcode;
                barcodeInput.setAttribute('data-barcode', savedBarcode);
                
                // Override value property to prevent clearing
                const self = this;
                const originalValueDescriptor = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
                Object.defineProperty(barcodeInput, 'value', {
                    get: function() {
                        const current = originalValueDescriptor.get.call(this);
                        if (!current || current === '') {
                            const saved = sessionStorage.getItem('current_barcode') || savedBarcode;
                            originalValueDescriptor.set.call(this, saved);
                            self.formData.barcode = saved;
                            return saved;
                        }
                        return current;
                    },
                    set: function(newValue) {
                        if (newValue && newValue.length === 10) {
                            originalValueDescriptor.set.call(this, newValue);
                            sessionStorage.setItem('current_barcode', newValue);
                            self.formData.barcode = newValue;
                        } else if (!newValue || newValue === '') {
                            // Block empty values - restore saved
                            const saved = sessionStorage.getItem('current_barcode') || savedBarcode;
                            originalValueDescriptor.set.call(this, saved);
                            self.formData.barcode = saved;
                        } else {
                            originalValueDescriptor.set.call(this, newValue);
                        }
                    },
                    configurable: true
                });
            }
            
            // Watch for changes in formData
            this.$watch('formData.barcode', (newValue) => {
                if (newValue && newValue.length === 10) {
                    sessionStorage.setItem('current_barcode', newValue);
                    const barcodeInput = document.getElementById('barcode_input_field');
                    if (barcodeInput && barcodeInput.value !== newValue) {
                        barcodeInput.value = newValue;
                    }
                } else {
                    // Invalid - restore
                    const saved = sessionStorage.getItem('current_barcode');
                    if (saved) {
                        this.formData.barcode = saved;
                    } else {
                        this.formData.barcode = this.generateBarcode();
                    }
                }
            });
            
            // Light monitoring - every 2 seconds (reduced frequency to prevent timeout)
            let barcodeMonitorCount = 0;
            const monitorInterval = setInterval(() => {
                barcodeMonitorCount++;
                const barcodeInput = document.getElementById('barcode_input_field');
                if (barcodeInput) {
                    const saved = sessionStorage.getItem('current_barcode') || this.formData.barcode || this.generateBarcode();
                    
                    // Only update if value is actually missing or invalid
                    if (!this.formData.barcode || this.formData.barcode === '' || this.formData.barcode.length !== 10) {
                        this.formData.barcode = saved;
                        sessionStorage.setItem('current_barcode', saved);
                    }
                    
                    // Only update if value is actually missing or invalid
                    if (!barcodeInput.value || barcodeInput.value === '' || barcodeInput.value.length !== 10) {
                        barcodeInput.value = this.formData.barcode;
                    }
                    
                    // Sync only if values are different
                    if (barcodeInput.value !== this.formData.barcode) {
                        if (barcodeInput.value && barcodeInput.value.length === 10) {
                            this.formData.barcode = barcodeInput.value;
                            sessionStorage.setItem('current_barcode', barcodeInput.value);
                        } else if (this.formData.barcode && this.formData.barcode.length === 10) {
                            barcodeInput.value = this.formData.barcode;
                        }
                    }
                }
                // Stop monitoring after 15 checks (30 seconds)
                if (barcodeMonitorCount >= 15) {
                    clearInterval(monitorInterval);
                }
            }, 2000); // Reduced from 50ms to 2 seconds
            
            // Store interval ID for cleanup if needed
            this._barcodeMonitorInterval = monitorInterval;
        },
        generateBarcode() {
            const newBarcode = Math.floor(Math.random() * 10000000000).toString().padStart(10, '0');
            this.formData.barcode = newBarcode;
            sessionStorage.setItem('current_barcode', newBarcode);
            // Update input field via Alpine.js reactivity
            this.$nextTick(() => {
                const barcodeInput = document.querySelector('.barcode-input');
                if (barcodeInput) {
                    barcodeInput.value = newBarcode;
                }
            });
            return newBarcode;
        },
        checkDuplicateYear(year) {
            if (!year) {
                this.yearWarning = '';
                return false;
            }
            const target = parseInt(year);
            let isDuplicate = this.formData.tempYearRanges.some(range => {
                const [start, end] = range.split('-').map(Number);
                return target >= start && target <= end;
            });
            if (!isDuplicate && this.formData.vBrand && this.formData.vModel) {
                const currentKey = `${this.formData.vBrand.toLowerCase()} | ${this.formData.vModel.toLowerCase()}`;
                isDuplicate = this.formData.compatibleVehicles.some(vStr => {
                    if (!vStr.toLowerCase().includes(currentKey)) return false;
                    const yearsPart = vStr.split('Years: ')[1];
                    if (!yearsPart) return false;
                    return yearsPart.split(', ').some(r => {
                        const [start, end] = r.split('-').map(Number);
                        return target >= start && target <= end;
                    });
                });
            }
            this.yearWarning = isDuplicate ? `Saal ${year} pehle se Linked Vehicles mein mojood hai!` : '';
            return isDuplicate;
        },
        addYearRangeToSet() {
            const { vYearFrom, vYearTo } = this.formData;
            if (!vYearFrom || !vYearTo) {
                alert("Saal From aur To dono select karein.");
                return;
            }
            if (parseInt(vYearFrom) > parseInt(vYearTo)) {
                alert("Year From selection ghalat hai.");
                return;
            }
            if (this.checkDuplicateYear(vYearFrom) || this.checkDuplicateYear(vYearTo)) {
                return;
            }
            const rangeStr = `${vYearFrom}-${vYearTo}`;
            this.formData.tempYearRanges = [...this.formData.tempYearRanges, rangeStr].sort((a, b) => parseInt(a) - parseInt(b));
            this.formData.vYearFrom = '';
            this.formData.vYearTo = '';
        },
        removeTempYearRange(range) {
            this.formData.tempYearRanges = this.formData.tempYearRanges.filter(r => r !== range);
        },
        handleAttachBtn() {
            const { vBrand, vModel, vCC, tempYearRanges, compatibleVehicles } = this.formData;
            if (!vBrand || !vModel) {
                alert("Brand aur Model lazmi hain.");
                return;
            }
            const vehicleBase = `${vBrand} | ${vModel}${vCC ? ' | ' + vCC : ''}`;
            const searchKey = `${vBrand.toLowerCase()} | ${vModel.toLowerCase()}`;
            const existingIndex = compatibleVehicles.findIndex(v => v.toLowerCase().startsWith(searchKey));
            let newList = [...compatibleVehicles];
            if (existingIndex !== -1) {
                const existingStr = newList[existingIndex];
                const existingYearsPart = existingStr.split('Years: ')[1] || "";
                const existingRanges = existingYearsPart ? existingYearsPart.split(', ') : [];
                const combined = [...new Set([...existingRanges, ...tempYearRanges])].sort((a, b) => parseInt(a) - parseInt(b));
                newList[existingIndex] = `${vehicleBase} | Years: ${combined.join(', ')}`;
            } else {
                const yearDisplay = tempYearRanges.length > 0 ? `Years: ${tempYearRanges.join(', ')}` : "All Years";
                newList.push(`${vehicleBase} | ${yearDisplay}`);
            }
            this.formData.compatibleVehicles = newList;
            this.formData.vBrand = '';
            this.formData.vModel = '';
            this.formData.vCC = '';
            this.formData.vYearFrom = '';
            this.formData.vYearTo = '';
            this.formData.tempYearRanges = [];
        },
        removeLinkedVehicle(vStr) {
            this.formData.compatibleVehicles = this.formData.compatibleVehicles.filter(v => v !== vStr);
        },
        handleEditVehicle(vStr) {
            const parts = vStr.split(' | ');
            let brand = parts[0];
            let model = parts[1];
            let cc = '';
            let yearsPart = '';
            if (parts.length === 4) {
                cc = parts[2];
                yearsPart = parts[3];
            } else if (parts.length === 3) {
                if (parts[2].includes('Years:') || parts[2].includes('All Years')) {
                    yearsPart = parts[2];
                } else {
                    cc = parts[2];
                }
            }
            let ranges = [];
            if (yearsPart && yearsPart.includes('Years: ')) {
                ranges = yearsPart.split('Years: ')[1].split(', ');
            }
            this.formData.vBrand = brand;
            this.formData.vModel = model;
            this.formData.vCC = cc;
            this.formData.tempYearRanges = ranges;
            this.formData.compatibleVehicles = this.formData.compatibleVehicles.filter(v => v !== vStr);
            window.scrollTo({ top: 300, behavior: 'smooth' });
        },
        resetForm() {
            this.editingItemId = null;
            this.formData = { ...this.initialFormState };
            // Generate new barcode after reset
            const newBarcode = Math.floor(Math.random() * 10000000000).toString().padStart(10, '0');
            this.formData.barcode = newBarcode;
            this.initialFormState.barcode = newBarcode;
            sessionStorage.setItem('current_barcode', newBarcode);
        },
        handleSave() {
            if (!this.formData.productName) {
                alert("Product Name select karein.");
                return;
            }
            
            // Get barcode from input field directly (more reliable)
            const barcodeInput = document.getElementById('barcode_input_field');
            const barcodeValue = barcodeInput ? barcodeInput.value : (this.formData.barcode || sessionStorage.getItem('current_barcode'));
            
            if (!barcodeValue || barcodeValue.length !== 10) {
                alert("Barcode generate nahi hua. Please refresh the page.");
                return;
            }
            
            // Update formData with actual barcode value
            this.formData.barcode = barcodeValue;
            sessionStorage.setItem('current_barcode', barcodeValue);
            
            // Sync Select2 values with formData before saving
            // Get actual values from Select2 dropdowns
            const groupSelect = document.getElementById('group_select');
            const companySelect = document.getElementById('company_select');
            const productSelect = document.getElementById('product_name_select');
            const platesSelect = document.getElementById('plates_select');
            const amperesSelect = document.getElementById('amperes_select');
            const voltSelect = document.getElementById('volt_select');
            const ccaSelect = document.getElementById('cca_select');
            const minusPoleSelect = document.getElementById('minus_pole_select');
            const technologySelect = document.getElementById('technology_select');
            const madeInSelect = document.getElementById('made_in_select');
            const warrantySelect = document.getElementById('warranty_select');
            const unitSelect = document.getElementById('unit_select');
            
            // Update formData with actual Select2 values
            if (groupSelect && $(groupSelect).data('select2')) {
                const groupVal = $(groupSelect).val();
                if (groupVal) this.formData.groupName = groupVal;
            }
            if (companySelect && $(companySelect).data('select2')) {
                const companyVal = $(companySelect).val();
                if (companyVal) this.formData.company = companyVal;
            }
            if (productSelect && $(productSelect).data('select2')) {
                const productVal = $(productSelect).val();
                if (productVal) this.formData.productName = productVal;
            }
            if (platesSelect && $(platesSelect).data('select2')) {
                const platesVal = $(platesSelect).val();
                if (platesVal) this.formData.plates = platesVal;
            }
            if (amperesSelect && $(amperesSelect).data('select2')) {
                const amperesVal = $(amperesSelect).val();
                if (amperesVal) this.formData.amperes = amperesVal;
            }
            if (voltSelect && $(voltSelect).data('select2')) {
                const voltVal = $(voltSelect).val();
                if (voltVal) this.formData.volt = voltVal;
            }
            if (ccaSelect && $(ccaSelect).data('select2')) {
                const ccaVal = $(ccaSelect).val();
                if (ccaVal) this.formData.cca = ccaVal;
            }
            if (minusPoleSelect && $(minusPoleSelect).data('select2')) {
                const minusPoleVal = $(minusPoleSelect).val();
                if (minusPoleVal) this.formData.minusPole = minusPoleVal;
            }
            if (technologySelect && $(technologySelect).data('select2')) {
                const technologyVal = $(technologySelect).val();
                if (technologyVal) this.formData.technology = technologyVal;
            }
            if (madeInSelect && $(madeInSelect).data('select2')) {
                const madeInVal = $(madeInSelect).val();
                if (madeInVal) this.formData.madeIn = madeInVal;
            }
            if (warrantySelect && $(warrantySelect).data('select2')) {
                const warrantyVal = $(warrantySelect).val();
                if (warrantyVal) this.formData.warranty = warrantyVal;
            }
            if (unitSelect && $(unitSelect).data('select2')) {
                const unitVal = $(unitSelect).val();
                if (unitVal) this.formData.unit = unitVal;
            }
            
            // Prepare form data
            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('bar_code', barcodeValue);
            formData.append('p_id', this.formData.productName || '');
            formData.append('group', this.formData.groupName || '');
            formData.append('company_id', this.formData.company || '');
            formData.append('sale_price', this.formData.salePrice || '0');
            formData.append('on_hand', this.formData.openingStock || '0');
            formData.append('unit', this.formData.unit || '');
            formData.append('plat_id', this.formData.plates || '');
            formData.append('amphors', this.formData.amperes || '');
            formData.append('volt', this.formData.volt || '');
            formData.append('cca', this.formData.cca || '');
            formData.append('minus_pole_direction', this.formData.minusPole || '');
            formData.append('technology', this.formData.technology || '');
            formData.append('made_in', this.formData.madeIn || '');
            formData.append('warrenty', this.formData.warranty || '');
            
            // Debug: Log form data being sent
            console.log('Saving form data:', {
                bar_code: barcodeValue,
                p_id: this.formData.productName,
                group: this.formData.groupName,
                company_id: this.formData.company,
                sale_price: this.formData.salePrice,
                on_hand: this.formData.openingStock,
                unit: this.formData.unit,
                plat_id: this.formData.plates,
                amphors: this.formData.amperes,
                volt: this.formData.volt,
                cca: this.formData.cca,
                minus_pole_direction: this.formData.minusPole,
                technology: this.formData.technology,
                made_in: this.formData.madeIn,
                warrenty: this.formData.warranty
            });
            
            // Show loading state
            const saveButton = document.querySelector('.btn-save');
            const originalText = saveButton.innerHTML;
            // Store original text for error recovery
            saveButton.setAttribute('data-original-text', originalText);
            saveButton.disabled = true;
            saveButton.innerHTML = '<svg class="animate-spin" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Saving to Database...';
            
            // Submit via AJAX
            fetch('{{ route("all.items.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                // Check response status first
                if (!response.ok) {
                    // If response is not OK, try to get error message
                    return response.json().then(errData => {
                        throw new Error(errData.message || 'Failed to save item');
                    }).catch(() => {
                        throw new Error('Failed to save item. Status: ' + response.status);
                    });
                }
                
                // Check if response is JSON or HTML redirect
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    return response.json();
                } else {
                    // If it's a redirect or HTML, treat as success (Laravel redirects on success)
                    return { success: true, message: 'Item saved successfully to database!' };
                }
            })
            .then(data => {
                // Check for success in multiple ways
                if (data.success || data.status === 'success' || data.status === true || response.status === 200) {
                    // Save successful - show success message
                    const successMsg = "Item successfully saved to database permanently! Barcode: " + this.formData.barcode + "\n\nRedirecting to items list...";
                    
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success("Item saved successfully to database! Barcode: " + this.formData.barcode, "Success", {
                            timeOut: 3000,
                            progressBar: true
                        });
                    } else {
                        alert(successMsg);
                    }
                    
                    // Store form data in localStorage for persistence (backup)
                    const formDataToStore = {
                        barcode: this.formData.barcode,
                        groupName: this.formData.groupName,
                        company: this.formData.company,
                        productName: this.formData.productName,
                        plates: this.formData.plates,
                        amperes: this.formData.amperes,
                        volt: this.formData.volt,
                        cca: this.formData.cca,
                        minusPole: this.formData.minusPole,
                        technology: this.formData.technology,
                        madeIn: this.formData.madeIn,
                        warranty: this.formData.warranty,
                        unit: this.formData.unit,
                        salePrice: this.formData.salePrice,
                        openingStock: this.formData.openingStock,
                        compatibleVehicles: this.formData.compatibleVehicles,
                        tempYearRanges: this.formData.tempYearRanges
                    };
                    localStorage.setItem('last_saved_item', JSON.stringify(formDataToStore));
                    
                    // Keep form draft for next item (optional)
                    // localStorage.removeItem('form_data_draft');
                    
                    // Redirect to items list page to show saved item
                    setTimeout(() => {
                        window.location.href = '{{ route("all.items") }}';
                    }, 1500); // Wait 1.5 seconds to show success message
                } else {
                    let errorMsg = "Error saving item.";
                    if (data.message) {
                        errorMsg = data.message;
                    } else if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('\n');
                    } else if (typeof data === 'string') {
                        errorMsg = data;
                    }
                    alert(errorMsg);
                }
            })
            .catch(error => {
                console.error('Error saving item to database:', error);
                let errorMsg = "Error saving item to database.";
                
                if (error.message) {
                    errorMsg = error.message;
                }
                
                // Show error message
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMsg, "Database Error", {
                        timeOut: 5000,
                        progressBar: true
                    });
                } else {
                    alert(errorMsg + "\n\nPlease check console for details.\n\nData was NOT saved to database.");
                }
                
                // Don't redirect on error - let user fix and retry
                // Re-enable save button
                const saveButton = document.querySelector('.btn-save');
                if (saveButton) {
                    saveButton.disabled = false;
                    const originalText = saveButton.getAttribute('data-original-text') || '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span x-text="editingItemId ? \'Update Item\' : \'Save Item\'"></span>';
                    saveButton.innerHTML = originalText;
                }
            })
            .finally(() => {
                saveButton.disabled = false;
                saveButton.innerHTML = originalText;
            });
        },
        openEditModal(field, label, evt) {
            // Get event from parameter or global
            const e = evt || event || window.event;
            
            // Prevent any default behavior and stop propagation
            if (e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
            }
            
            console.log('openEditModal called for:', field, label);
            
            // Get the current value from the select
            const selectIdMap = {
                'groupName': 'group_select',
                'company': 'company_select',
                'productName': 'product_name_select',
                'plates': 'plates_select',
                'amperes': 'amperes_select',
                'volt': 'volt_select',
                'cca': 'cca_select',
                'minusPole': 'minus_pole_select',
                'technology': 'technology_select',
                'madeIn': 'made_in_select',
                'warranty': 'warranty_select',
                'unit': 'unit_select'
            };
            
            const selectId = selectIdMap[field];
            if (!selectId) {
                alert(`Edit functionality not available for ${label}`);
                return false;
            }
            
            const select = document.getElementById(selectId);
            if (!select) {
                alert(`Select element not found for ${label}`);
                return false;
            }
            
            // Close Select2 dropdown if open (do this first)
            if (typeof $ !== 'undefined' && $(select).data('select2')) {
                $(select).select2('close');
            }
            
            // Close all other open Select2 dropdowns
            if (typeof $ !== 'undefined') {
                $('.select2-container--open').each(function() {
                    const $select2 = $(this).prev('select');
                    if ($select2.length && $select2.data('select2')) {
                        $select2.select2('close');
                    }
                });
            }
            
            const currentValue = select.value;
            if (!currentValue || currentValue === '') {
                alert(`Please select a ${label} first`);
                return false;
            }
            
            // Get the current text (name) from the selected option
            const selectedOption = select.options[select.selectedIndex];
            let currentText = selectedOption ? selectedOption.text.trim() : '';
            
            // If text is empty, try to get from Select2
            if (!currentText && typeof $ !== 'undefined' && $(select).data('select2')) {
                try {
                    const select2Data = $(select).select2('data');
                    if (select2Data && select2Data.length > 0 && select2Data[0].text) {
                        currentText = select2Data[0].text.trim();
                    }
                } catch(e) {
                    console.log('Error getting Select2 data:', e);
                }
            }
            
            if (!currentText) {
                currentText = currentValue;
            }
            
            // Route map for edit/show
            const showRouteMap = {
                'group_select': '{{ route("show.groups", ":id") ?? "" }}',
                'company_select': '{{ route("show.company", ":id") ?? "" }}',
                'product_name_select': '{{ route("show.product", ":id") ?? "" }}',
                'plates_select': '{{ route("show.plate", ":id") ?? "" }}',
                'amperes_select': '{{ route("show.ampere", ":id") ?? "" }}',
                'volt_select': '{{ route("show.volt", ":id") ?? "" }}',
                'cca_select': '{{ route("show.cca", ":id") ?? "" }}',
                'minus_pole_select': '{{ route("show.minuspool", ":id") ?? "" }}',
                'technology_select': '{{ route("show.technology", ":id") ?? "" }}',
                'made_in_select': '{{ route("show.madeins", ":id") ?? "" }}',
                'warranty_select': '{{ route("show.warrenty", ":id") ?? "" }}',
                'unit_select': '{{ route("show.unit", ":id") ?? "" }}'
            };
            
            const showRoute = showRouteMap[selectId];
            if (!showRoute) {
                alert(`Edit route not configured for ${label}`);
                return;
            }
            
            // Replace :id with actual ID
            const editUrl = showRoute.replace(':id', currentValue);
            
            // Prevent Select2 dropdown from opening
            if (typeof $ !== 'undefined' && $(select).data('select2')) {
                // Close any open Select2 dropdowns first
                $('.select2-container--open').each(function() {
                    const $select2 = $(this).prev('select');
                    if ($select2.length && $select2.data('select2')) {
                        $select2.select2('close');
                    }
                });
                // Also trigger blur to close
                $(select).select2('close');
            }
            
            // Open edit modal (similar to React component)
            setTimeout(() => {
                this.showEditModal = true;
                this.editModalType = 'edit';
                this.editModalTarget = {
                    name: field,
                    label: label,
                    selectId: selectId,
                    valueId: currentValue
                };
                this.editModalValue = currentText;
                
                // Focus the input after modal opens
                setTimeout(() => {
                    const editInput = document.getElementById('editModalInput');
                    if (editInput) {
                        editInput.focus();
                        editInput.select();
                    }
                }, 100);
            }, 50);
        },
        handleEditModalConfirm() {
            if (!this.editModalValue || !this.editModalValue.trim()) {
                alert('Please enter a value');
                return;
            }
            
            const { selectId, valueId, label } = this.editModalTarget;
            const newName = this.editModalValue.trim();
            
            // Disable confirm button during update
            const confirmBtn = document.querySelector('button[\\@click*="handleEditModalConfirm"]');
            if (confirmBtn) {
                confirmBtn.disabled = true;
                const originalText = confirmBtn.textContent;
                confirmBtn.textContent = 'Updating...';
            }
            
            // Update via AJAX and close modal after success
            this.updateDropdownValue(selectId, valueId, newName, label)
            .then((data) => {
                // Close modal immediately after successful update
                this.showEditModal = false;
                this.editModalValue = '';
                this.editModalTarget = { name: '', label: '', selectId: '', valueId: '' };
            })
            .catch((error) => {
                console.error('Update error:', error);
                // Re-enable button on error
                if (confirmBtn) {
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = originalText || 'Confirm';
                }
                // Don't close modal on error - let user retry
            });
        },
        updateDropdownValue(selectId, valueId, newName, label) {
            // Update route map
            const updateRouteMap = {
                'group_select': '{{ route("post.groups.update", ":id") ?? "" }}',
                'company_select': '{{ route("update.company", ":id") ?? "" }}',
                'product_name_select': '{{ route("update.product", ":id") ?? "" }}',
                'plates_select': '{{ route("update.platos", ":id") ?? "" }}',
                'amperes_select': '{{ route("update.ampere", ":id") ?? "" }}',
                'volt_select': '{{ route("update.volt", ":id") ?? "" }}',
                'cca_select': '{{ route("update.cca", ":id") ?? "" }}',
                'minus_pole_select': '{{ route("update.minuspool", ":id") ?? "" }}',
                'technology_select': '{{ route("update.technology", ":id") ?? "" }}',
                'made_in_select': '{{ route("update.madeins", ":id") ?? "" }}',
                'warranty_select': '{{ route("update.warrenty", ":id") ?? "" }}',
                'unit_select': '{{ route("update.unit", ":id") ?? "" }}'
            };
            
            const updateRoute = updateRouteMap[selectId];
            if (!updateRoute) {
                alert(`Update route not configured for ${label}`);
                return;
            }
            
            const updateUrl = updateRoute.replace(':id', valueId);
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                             document.querySelector('input[name="_token"]')?.value || '';
            
            if (!csrfToken) {
                alert('CSRF token not found. Please refresh the page.');
                return;
            }
            
            // Send update request and return promise
            return fetch(updateUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: newName
                })
            })
            .then(response => {
                // Check if response is ok
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || `Server error: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success || data.id || data.message) {
                    // Update the option text in the select
                    const select = document.getElementById(selectId);
                    if (select) {
                        const option = select.querySelector(`option[value="${valueId}"]`);
                        if (option) {
                            option.textContent = newName;
                            // Trigger Select2 update to refresh display
                            if (typeof $ !== 'undefined' && $(select).data('select2')) {
                                $(select).trigger('change.select2');
                                $(select).trigger('change');
                                
                                // Force update the rendered display
                                const $container = $(select).next('.select2-container');
                                if ($container.length) {
                                    const $rendered = $container.find('.select2-selection__rendered');
                                    if ($rendered.length) {
                                        $rendered.text(newName);
                                        $rendered.attr('title', newName);
                                    }
                                }
                            }
                        }
                    }
                    
                    // Show success message (optional - brief, modal will close)
                    if (typeof toastr !== 'undefined') {
                        toastr.success(`${label} updated!`, '', { timeOut: 1500 });
                    }
                    
                    // Return data for promise chain (modal will close in handleEditModalConfirm)
                    return data;
                } else {
                    throw new Error(`Failed to update ${label}`);
                }
            })
            .catch(error => {
                console.error('Error updating:', error);
                alert(`Error updating ${label}: ${error.message || 'Please try again.'}`);
                throw error; // Re-throw for promise chain
            });
        },
        updateEditButtonColor(selectId) {
            const select = document.getElementById(selectId);
            if (!select) return;
            const hasValue = select.value !== '';
            const button = select.closest('.input-with-edit')?.querySelector('.edit-icon-btn');
            if (button) {
                if (hasValue) {
                    button.classList.add('has-value');
                } else {
                    button.classList.remove('has-value');
                }
            }
        },
        // Watch barcode to ensure it stays visible
        watchBarcode() {
            // This will be called whenever barcode changes
            this.$watch('formData.barcode', (value) => {
                if (value) {
                    setTimeout(() => {
                        const barcodeInput = document.querySelector('.barcode-input');
                        if (barcodeInput && barcodeInput.value !== value) {
                            barcodeInput.value = value;
                        }
                    }, 10);
                }
            });
        }
    };
}

// Ultimate barcode protection - completely isolated from Alpine.js
(function() {
    const generateBarcode = () => {
        return Math.floor(Math.random() * 10000000000).toString().padStart(10, '0');
    };
    
    // Get or create barcode
    let stableBarcode = sessionStorage.getItem('current_barcode');
    if (!stableBarcode || stableBarcode.length !== 10) {
        stableBarcode = generateBarcode();
        sessionStorage.setItem('current_barcode', stableBarcode);
    }
    
    // Global function to generate new barcode and check database
    window.generateNewBarcode = async function() {
        const barcodeInput = document.getElementById('barcode_input_field');
        if (!barcodeInput) return;
        
        // Show loading state on refresh button
        const refreshBtn = barcodeInput.closest('.barcode-group')?.querySelector('.refresh-btn');
        if (refreshBtn) {
            refreshBtn.disabled = true;
            refreshBtn.style.opacity = '0.5';
            refreshBtn.style.pointerEvents = 'none';
        }
        
        let attempts = 0;
        let newBarcode = '';
        let isUnique = false;
        
        // Try to generate unique barcode (max 10 attempts)
        while (!isUnique && attempts < 10) {
            newBarcode = generateBarcode();
            attempts++;
            
            try {
                // Check if barcode exists in database
                const formData = new FormData();
                formData.append('bar_code', newBarcode);
                formData.append('_token', document.querySelector('input[name="_token"]').value);
                
                const response = await fetch('{{ route("check.barcode") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.exists === false || !data.exists) {
                        isUnique = true;
                    }
                } else {
                    // If endpoint doesn't exist or fails, assume unique and proceed
                    isUnique = true;
                }
            } catch(error) {
                // If check fails, assume unique and proceed
                console.log('Barcode check failed, assuming unique:', error);
                isUnique = true;
            }
        }
        
        // If still not unique after 10 attempts, use the last generated one
        if (!isUnique) {
            newBarcode = generateBarcode();
        }
        
        // Update barcode everywhere
        stableBarcode = newBarcode;
        sessionStorage.setItem('current_barcode', newBarcode);
        barcodeInput.value = newBarcode;
        
        // Force update input field
        if (barcodeInput.setAttribute) {
            barcodeInput.setAttribute('value', newBarcode);
        }
        
        // Update Alpine.js
        try {
            const alpineEl = document.querySelector('[x-data]');
            if (alpineEl && window.Alpine) {
                const data = window.Alpine.$data(alpineEl);
                if (data && data.formData) {
                    data.formData.barcode = newBarcode;
                }
                // Also update hidden input
                const hiddenInput = document.getElementById('barcode_hidden');
                if (hiddenInput) {
                    hiddenInput.value = newBarcode;
                }
            }
        } catch(e) {
            console.log('Alpine.js update failed:', e);
        }
        
        // Restore button state
        if (refreshBtn) {
            refreshBtn.disabled = false;
            refreshBtn.style.opacity = '1';
            refreshBtn.style.pointerEvents = 'auto';
        }
        
        // Ensure barcode is visible
        setTimeout(() => {
            if (barcodeInput.value !== newBarcode) {
                barcodeInput.value = newBarcode;
            }
        }, 100);
    };
    
    // Set up barcode input protection
    const setupBarcode = () => {
        const barcodeInput = document.getElementById('barcode_input_field');
        if (!barcodeInput) return false;
        
        // Set value immediately and ensure it's visible
        barcodeInput.value = stableBarcode;
        barcodeInput.setAttribute('value', stableBarcode);
        barcodeInput.style.display = 'block';
        barcodeInput.style.visibility = 'visible';
        barcodeInput.style.opacity = '1';
        
        // Force display multiple times
        setTimeout(() => {
            if (barcodeInput.value !== stableBarcode) {
                barcodeInput.value = stableBarcode;
                barcodeInput.setAttribute('value', stableBarcode);
            }
        }, 0);
        setTimeout(() => {
            if (barcodeInput.value !== stableBarcode) {
                barcodeInput.value = stableBarcode;
                barcodeInput.setAttribute('value', stableBarcode);
            }
        }, 50);
        
        // Override value property
        const originalDescriptor = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
        if (!originalDescriptor) return false;
        
        Object.defineProperty(barcodeInput, 'value', {
            get: function() {
                const val = originalDescriptor.get.call(this);
                if (!val || val === '' || val.length !== 10) {
                    const saved = sessionStorage.getItem('current_barcode') || stableBarcode;
                    originalDescriptor.set.call(this, saved);
                    return saved;
                }
                return val;
            },
            set: function(newValue) {
                if (newValue && newValue.length === 10) {
                    stableBarcode = newValue;
                    sessionStorage.setItem('current_barcode', newValue);
                    originalDescriptor.set.call(this, newValue);
                    // Update Alpine.js
                    try {
                        const alpineEl = this.closest('[x-data]');
                        if (alpineEl && window.Alpine) {
                            const data = window.Alpine.$data(alpineEl);
                            if (data && data.formData) {
                                data.formData.barcode = newValue;
                            }
                        }
                    } catch(e) {}
                } else if (!newValue || newValue === '') {
                    // Block empty - restore
                    const saved = sessionStorage.getItem('current_barcode') || stableBarcode;
                    stableBarcode = saved;
                    originalDescriptor.set.call(this, saved);
                    // Update Alpine.js
                    try {
                        const alpineEl = this.closest('[x-data]');
                        if (alpineEl && window.Alpine) {
                            const data = window.Alpine.$data(alpineEl);
                            if (data && data.formData) {
                                data.formData.barcode = saved;
                            }
                        }
                    } catch(e) {}
                } else {
                    originalDescriptor.set.call(this, newValue);
                }
            },
            configurable: true,
            enumerable: true
        });
        
        return true;
    };
    
    // Try to set up immediately
    let isSetup = false;
    const trySetup = () => {
        if (!isSetup) {
            isSetup = setupBarcode();
        }
    };
    
    // Multiple setup attempts
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', trySetup);
    } else {
        trySetup();
    }
    setTimeout(trySetup, 0);
    setTimeout(trySetup, 10);
    setTimeout(trySetup, 50);
    
    // After Alpine initializes
    document.addEventListener('alpine:init', () => {
        setTimeout(trySetup, 0);
        setTimeout(trySetup, 50);
        setTimeout(trySetup, 100);
    });
    
    // Light monitoring every 2 seconds (reduced to prevent timeout)
    let barcodeCheckCount = 0;
    const barcodeMonitor = setInterval(() => {
        barcodeCheckCount++;
        const barcodeInput = document.getElementById('barcode_input_field');
        if (barcodeInput) {
            const saved = sessionStorage.getItem('current_barcode') || stableBarcode;
            // Only update if value is actually missing or invalid
            if (!barcodeInput.value || barcodeInput.value === '' || barcodeInput.value.length !== 10) {
                barcodeInput.value = saved;
                stableBarcode = saved;
                // Sync with Alpine.js hidden input
                try {
                    const hiddenInput = document.getElementById('barcode_hidden');
                    if (hiddenInput) {
                        hiddenInput.value = barcodeInput.value;
                    }
                    const alpineEl = barcodeInput.closest('[x-data]');
                    if (alpineEl && window.Alpine) {
                        const data = window.Alpine.$data(alpineEl);
                        if (data && data.formData) {
                            data.formData.barcode = barcodeInput.value;
                        }
                    }
                } catch(e) {}
            }
        } else if (barcodeCheckCount < 10) {
            // Only try setup a few times
            trySetup();
        }
        // Stop monitoring after 20 checks (40 seconds)
        if (barcodeCheckCount >= 20) {
            clearInterval(barcodeMonitor);
        }
    }, 2000); // Reduced from 50ms to 2 seconds
})();

// Function to check and show "Add New" button in Select2 dropdowns
// Lock to prevent multiple simultaneous button creations
const buttonCreationLock = {};
const buttonExistenceCheck = {}; // Track which buttons exist

function checkAndShowAddNewButtonForDropdown(selectId, searchTerm) {
    // Early return if no open Select2
    const $openSelect2 = $('.select2-container--open');
    if (!$openSelect2.length) {
        return;
    }
    
    const $select = $('#' + selectId);
    if (!$select.length || !$select.data('select2')) {
        return;
    }
    
    const $selectContainer = $select.next('.select2-container');
    if (!$selectContainer.is($openSelect2)) {
        return; // Not the correct dropdown
    }
    
    // Get search term early for lock key
    const $searchInput = $openSelect2.find('.select2-search__field');
    const currentSearch = searchTerm || ($searchInput.length ? $searchInput.val() : '');
    const searchVal = currentSearch ? currentSearch.trim() : '';
    const lockKey = selectId + '_' + searchVal;
    
    // CRITICAL: Check lock and existence BEFORE doing anything else
    if (buttonCreationLock[lockKey] || buttonExistenceCheck[lockKey]) {
        return; // Already processing or exists, skip completely
    }
    
    // CRITICAL: Check if button already exists in DOM BEFORE processing
    const $existingCheck = $openSelect2.find('.select2-results__option--add-new[data-select-id="' + selectId + '"]');
    if ($existingCheck.length > 0) {
        buttonExistenceCheck[lockKey] = true;
        return; // Button already exists, skip
    }
    
    const $noResultsMsg = $openSelect2.find('.select2-results__message');
    const $results = $openSelect2.find('.select2-results__option--selectable:not(.select2-results__option--loading):not(.select2-results__option--add-new)');
    const $resultsContainer = $openSelect2.find('.select2-results');
    const $resultsList = $resultsContainer.find('ul.select2-results__options');
    
    if (!$resultsContainer.length) {
        return;
    }
    
    // Check if there are any results
    const hasResults = $results.length > 0;
    const hasNoResults = ($noResultsMsg.length && $noResultsMsg.is(':visible')) || 
                        ($results.length === 0 && searchVal.length > 0);
    
    // STEP 1: Remove ALL existing "Add New" buttons FIRST (very aggressive - remove from everywhere)
    $('.select2-container--open .select2-results__option--add-new').remove();
    $('.select2-results__option--add-new').remove();
    $resultsContainer.find('.select2-results__option--add-new').remove();
    $openSelect2.find('.select2-results__option--add-new').remove();
    if ($resultsList.length) {
        $resultsList.find('.select2-results__option--add-new').remove();
    }
    
    // STEP 2: Double check - if button still exists after removal, skip
    const $finalCheck = $openSelect2.find('.select2-results__option--add-new');
    if ($finalCheck.length > 0) {
        console.log('Button still exists after removal, skipping');
        return;
    }
    
    // Show "Add New" button if no results and search term exists
    if (hasNoResults && searchVal.length > 0) {
        // Set lock and existence flag IMMEDIATELY to prevent multiple creations
        buttonCreationLock[lockKey] = true;
        buttonExistenceCheck[lockKey] = true;
        
        // Hide "NO RESULTS FOUND" message
        if ($noResultsMsg.length) {
            $noResultsMsg.hide().css('display', 'none');
        }
        
        // Escape search value for use in HTML
        const escapedSearchVal = searchVal.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        
        // Create a unique button ID
        const buttonId = 'add-btn-' + selectId.replace(/[^a-zA-Z0-9]/g, '-') + '-' + Date.now();
        
        // Store search value globally for this button
        window['addBtn_' + buttonId] = {
            selectId: selectId,
            searchVal: searchVal
        };
        
        // Create button HTML - use li click instead of button to avoid Select2 interference
        const buttonHtml = `
            <li class="select2-results__option select2-results__option--add-new" 
                role="option" 
                data-button-id="${buttonId}"
                data-select-id="${selectId}"
                data-search-value="${escapedSearchVal}"
                onclick="
                    event.stopPropagation();
                    event.preventDefault();
                    var btnData = window['addBtn_${buttonId}'];
                    if (!btnData) {
                        btnData = { selectId: '${selectId}', searchVal: '${escapedSearchVal.replace(/'/g, "\\'")}' };
                    }
                    console.log('LI clicked:', btnData);
                    if (typeof $ !== 'undefined' && $('#' + btnData.selectId).length) {
                        $('#' + btnData.selectId).select2('close');
                    }
                    setTimeout(function() {
                        if (typeof window.openAddNewModal === 'function') {
                            window.openAddNewModal(btnData.selectId, btnData.searchVal);
                        } else {
                            alert('Error: Please refresh the page.');
                        }
                    }, 200);
                    return false;
                "
                style="padding: 8px 12px; border-top: 1px solid #e5e7eb; background: white; cursor: pointer; list-style: none; pointer-events: auto;">
                <div style="background: #FFE5D4; color: #FF833E; border: 1px solid #FF833E; border-radius: 8px; padding: 10px 16px; font-weight: 700; font-size: 14px; text-align: center; width: 100%; box-shadow: none; transition: all 0.2s; cursor: pointer; display: block;"
                     onmouseover="this.style.background='#FF833E'; this.style.color='white';"
                     onmouseout="this.style.background='#FFE5D4'; this.style.color='#FF833E';">
                    <span style="font-weight: 700; margin-right: 4px;">+</span> Add "<span class="dropdown-search-term fw-bold" style="font-weight: 700;">${searchVal}</span>"
                </div>
            </li>
        `;
        
        // Insert button
        if ($noResultsMsg.length) {
            $noResultsMsg.before(buttonHtml);
        } else if ($resultsList.length) {
            $resultsList.append(buttonHtml);
        } else {
            $resultsContainer.append(buttonHtml);
        }
        
        // Attach click handler on the li element (not button) to avoid Select2 interference
        setTimeout(function() {
            const $newLi = $('[data-button-id="' + buttonId + '"]');
            if ($newLi.length) {
                // Remove any existing handlers
                $newLi.off('click.addNewLi mousedown.addNewLi');
                
                // Add click handler
                $newLi.on('click.addNewLi mousedown.addNewLi', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    
                    const btnData = window['addBtn_' + buttonId] || {
                        selectId: $(this).attr('data-select-id'),
                        searchVal: $(this).attr('data-search-value') || searchVal
                    };
                    
                    console.log('jQuery LI handler triggered:', btnData);
                    
                    // Decode HTML entities
                    if (btnData.searchVal && btnData.searchVal.includes('&quot;')) {
                        btnData.searchVal = btnData.searchVal.replace(/&quot;/g, '"');
                    }
                    
                    // Close Select2
                    if (typeof $ !== 'undefined' && $('#' + btnData.selectId).length) {
                        try {
                            $('#' + btnData.selectId).select2('close');
                        } catch(err) {
                            console.log('Select2 close error:', err);
                        }
                    }
                    
                    // Open modal
                    setTimeout(function() {
                        console.log('Opening modal:', btnData.selectId, btnData.searchVal);
                        if (typeof window.openAddNewModal === 'function') {
                            window.openAddNewModal(btnData.selectId, btnData.searchVal);
                        } else if (typeof openAddNewModal === 'function') {
                            openAddNewModal(btnData.selectId, btnData.searchVal);
                        } else {
                            console.error('openAddNewModal not found');
                            alert('Error: Modal function not found. Please refresh the page.');
                        }
                    }, 200);
                    
                    return false;
                });
            }
            
            // Release lock after button is created (but keep existence check)
            setTimeout(function() {
                delete buttonCreationLock[lockKey];
            }, 1000);
        }, 100);
        
        // Clear existence check after longer delay to prevent rapid re-creation
        setTimeout(function() {
            delete buttonExistenceCheck[lockKey];
        }, 2000);
    } else if (hasResults) {
        // Remove any existing buttons if results found
        $openSelect2.find('.select2-results__option--add-new').remove();
        
        // Release lock if not creating button
        if (buttonCreationLock[lockKey]) {
            delete buttonCreationLock[lockKey];
        }
        if (buttonExistenceCheck[lockKey]) {
            delete buttonExistenceCheck[lockKey];
        }
        
        // Show "NO RESULTS FOUND" message if it was hidden and no results
        if ($noResultsMsg.length && !hasResults) {
            $noResultsMsg.show();
        }
    } else {
        // Remove any existing buttons if no search term
        if (!searchVal || searchVal.length === 0) {
            $openSelect2.find('.select2-results__option--add-new').remove();
        }
        
        // Release lock if not creating button
        if (buttonCreationLock[lockKey]) {
            delete buttonCreationLock[lockKey];
        }
        if (buttonExistenceCheck[lockKey]) {
            delete buttonExistenceCheck[lockKey];
        }
    }
}

$(document).ready(function() {
    // Wait for Alpine.js to initialize
    setTimeout(function() {
        // Initialize Select2 for all searchable selects
        $('.searchable-select').each(function() {
            const $select = $(this);
            if (!$select.data('select2')) {
                // Remove placeholder option from DOM before initializing Select2
                const $placeholderOption = $select.find('option[value=""]');
                if ($placeholderOption.length && $placeholderOption.text().trim() === 'Please Select') {
                    $placeholderOption.remove();
                }
                
                $select.select2({
                    placeholder: 'Please Select',
                    allowClear: true,
                    width: '100%'
                });
                
                // Restore value from localStorage immediately after Select2 initialization
                const selectId = $select.attr('id');
                if (selectId) {
                    // Check both form_data_draft and last_saved_item (prioritize last_saved_item)
                    let savedFormData = localStorage.getItem('form_data_draft');
                    const lastSavedItem = localStorage.getItem('last_saved_item');
                    
                    // If last_saved_item exists, use that (it has the most recent saved data)
                    if (lastSavedItem) {
                        try {
                            const lastSaved = JSON.parse(lastSavedItem);
                            // Convert to draft format for consistency
                            savedFormData = JSON.stringify({
                                groupName: lastSaved.groupName || '',
                                company: lastSaved.company || '',
                                productName: lastSaved.productName || '',
                                plates: lastSaved.plates || '',
                                amperes: lastSaved.amperes || '',
                                volt: lastSaved.volt || '',
                                cca: lastSaved.cca || '',
                                minusPole: lastSaved.minusPole || '',
                                technology: lastSaved.technology || '',
                                madeIn: lastSaved.madeIn || '',
                                warranty: lastSaved.warranty || '',
                                unit: lastSaved.unit || ''
                            });
                        } catch(e) {
                            // If parsing fails, use form_data_draft if available
                        }
                    }
                    
                    if (savedFormData) {
                        try {
                            const parsedData = JSON.parse(savedFormData);
                            const fieldMap = {
                                'group_select': 'groupName',
                                'company_select': 'company',
                                'product_name_select': 'productName',
                                'plates_select': 'plates',
                                'amperes_select': 'amperes',
                                'volt_select': 'volt',
                                'cca_select': 'cca',
                                'minus_pole_select': 'minusPole',
                                'technology_select': 'technology',
                                'made_in_select': 'madeIn',
                                'warranty_select': 'warranty',
                                'unit_select': 'unit'
                            };
                            const fieldName = fieldMap[selectId];
                            if (fieldName && parsedData[fieldName] && parsedData[fieldName] !== '') {
                                // Restore value immediately
                                setTimeout(() => {
                                    $select.val(parsedData[fieldName]).trigger('change.select2');
                                    $select.trigger('change');
                                    
                                    // Force update display text
                                    const $container = $select.next('.select2-container');
                                    if ($container.length) {
                                        const $rendered = $container.find('.select2-selection__rendered');
                                        const $option = $select.find('option[value="' + parsedData[fieldName] + '"]');
                                        const optionText = $option.length ? $option.text() : parsedData[fieldName];
                                        if (optionText && $rendered.length) {
                                            $rendered.text(optionText);
                                            $rendered.attr('title', optionText);
                                            // Also update container title
                                            $container.find('.select2-selection').attr('title', optionText);
                                        }
                                    }
                                }, 50);
                            }
                        } catch(e) {
                            console.log('Error restoring Select2 value:', e);
                        }
                    }
                }
                
                // Ensure placeholder is not shown as selected value (only if no saved value)
                const hasSavedValue = selectId && (() => {
                    const savedFormData = localStorage.getItem('form_data_draft');
                    if (savedFormData) {
                        try {
                            const parsedData = JSON.parse(savedFormData);
                            const fieldMap = {
                                'group_select': 'groupName',
                                'company_select': 'company',
                                'product_name_select': 'productName',
                                'plates_select': 'plates',
                                'amperes_select': 'amperes',
                                'volt_select': 'volt',
                                'cca_select': 'cca',
                                'minus_pole_select': 'minusPole',
                                'technology_select': 'technology',
                                'made_in_select': 'madeIn',
                                'warranty_select': 'warranty',
                                'unit_select': 'unit'
                            };
                            const fieldName = fieldMap[selectId];
                            return fieldName && parsedData[fieldName] && parsedData[fieldName] !== '';
                        } catch(e) {
                            return false;
                        }
                    }
                    return false;
                })();
                
                if (!$select.val() && !hasSavedValue) {
                    $select.val(null).trigger('change');
                }
                
                // Sync Select2 changes with Alpine.js
                $select.on('change', function() {
                    const selectId = $(this).attr('id');
                    const value = $(this).val();
                    
                    // Update Alpine.js data
                    const alpineComponent = Alpine.$data(this.closest('[x-data]'));
                    if (alpineComponent && alpineComponent.formData) {
                        const fieldMap = {
                            'group_select': 'groupName',
                            'company_select': 'company',
                            'product_name_select': 'productName',
                            'plates_select': 'plates',
                            'amperes_select': 'amperes',
                            'volt_select': 'volt',
                            'cca_select': 'cca',
                            'minus_pole_select': 'minusPole',
                            'technology_select': 'technology',
                            'made_in_select': 'madeIn',
                            'warranty_select': 'warranty',
                            'unit_select': 'unit',
                            'vBrand_select': 'vBrand',
                            'vModel_select': 'vModel',
                            'vCC_select': 'vCC',
                            'vYearFrom_select': 'vYearFrom',
                            'vYearTo_select': 'vYearTo'
                        };
                        
                        const fieldName = fieldMap[selectId];
                        if (fieldName) {
                            alpineComponent.formData[fieldName] = value || '';
                            
                            // Auto-save to localStorage when Select2 value changes
                            try {
                                const dataToSave = {
                                    groupName: alpineComponent.formData.groupName || '',
                                    company: alpineComponent.formData.company || '',
                                    productName: alpineComponent.formData.productName || '',
                                    plates: alpineComponent.formData.plates || '',
                                    amperes: alpineComponent.formData.amperes || '',
                                    volt: alpineComponent.formData.volt || '',
                                    cca: alpineComponent.formData.cca || '',
                                    minusPole: alpineComponent.formData.minusPole || '',
                                    technology: alpineComponent.formData.technology || '',
                                    madeIn: alpineComponent.formData.madeIn || '',
                                    warranty: alpineComponent.formData.warranty || '',
                                    unit: alpineComponent.formData.unit || '',
                                    salePrice: alpineComponent.formData.salePrice || '',
                                    openingStock: alpineComponent.formData.openingStock || '',
                                    compatibleVehicles: alpineComponent.formData.compatibleVehicles || [],
                                    tempYearRanges: alpineComponent.formData.tempYearRanges || []
                                };
                                localStorage.setItem('form_data_draft', JSON.stringify(dataToSave));
                            } catch(e) {
                                console.log('Error saving to localStorage:', e);
                            }
                            
                            // Check for year validation
                            if (selectId === 'vYearFrom_select' || selectId === 'vYearTo_select') {
                                alpineComponent.checkDuplicateYear(value);
                            }
                            
                            // Update edit button color
                            alpineComponent.updateEditButtonColor(selectId);
                        }
                    }
                });
                
                // Monitor search input for "Add New" button
                const selectIdForAdd = $select.attr('id');
                
                // Handle typing on Select2 container - redirect to search input
                const $container = $select.next('.select2-container');
                const $selection = $container.find('.select2-selection');
                
                // Remove previous handlers
                $selection.off('keydown.typingRedirect');
                
                // Handle keydown on selection container
                $selection.on('keydown.typingRedirect', function(e) {
                    // Check if it's a printable character (not special keys)
                    const isPrintable = e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey;
                    
                    if (isPrintable) {
                        // Open dropdown if not already open
                        if (!$container.hasClass('select2-container--open')) {
                            $select.select2('open');
                        }
                        
                        // Focus search input and send the character
                        setTimeout(() => {
                            const $searchInput = $container.find('.select2-search__field');
                            if ($searchInput.length && $searchInput[0]) {
                                $searchInput[0].focus();
                                $searchInput[0].click();
                                
                                // Get current value and append the new character
                                const currentVal = $searchInput.val() || '';
                                $searchInput.val(currentVal + e.key);
                                
                                // Trigger input event to update Select2
                                $searchInput.trigger('input');
                                
                                // Prevent default to avoid any other behavior
                                e.preventDefault();
                                e.stopPropagation();
                            }
                        }, 10);
                    }
                });
                
                // Handle when dropdown opens
                $select.on('select2:open', function() {
                    // AUTO CLICK/FOCUS: Click and focus search input immediately
                    const clickAndFocusSearchInput = () => {
                        const $container = $select.next('.select2-container');
                        const $searchInput = $container.find('.select2-search__field');
                        
                        if ($searchInput.length && $searchInput[0]) {
                            try {
                                // Multiple methods to ensure it works
                                $searchInput[0].click(); // Click first
                                $searchInput[0].focus(); // Then focus
                                $searchInput.focus(); // jQuery focus
                                
                                // Also trigger focus event
                                $searchInput.trigger('focus');
                                $searchInput.trigger('click');
                                
                                // Select text if any
                                if ($searchInput[0].value) {
                                    $searchInput[0].select();
                                }
                                
                                // Force focus using setAttribute
                                $searchInput[0].setAttribute('tabindex', '0');
                                $searchInput[0].focus();
                            } catch(e) {
                                console.log('Focus error:', e);
                            }
                        }
                    };
                    
                    // Try immediately and with multiple delays to ensure it works
                    clickAndFocusSearchInput();
                    setTimeout(clickAndFocusSearchInput, 0);
                    setTimeout(clickAndFocusSearchInput, 5);
                    setTimeout(clickAndFocusSearchInput, 10);
                    setTimeout(clickAndFocusSearchInput, 20);
                    setTimeout(clickAndFocusSearchInput, 50);
                    setTimeout(clickAndFocusSearchInput, 100);
                    setTimeout(clickAndFocusSearchInput, 150);
                    
                    // Also use requestAnimationFrame multiple times
                    requestAnimationFrame(() => {
                        clickAndFocusSearchInput();
                        requestAnimationFrame(() => {
                            clickAndFocusSearchInput();
                            requestAnimationFrame(() => {
                                clickAndFocusSearchInput();
                            });
                        });
                    });
                    
                    setTimeout(() => {
                        const $container = $select.next('.select2-container');
                        const $searchInput = $container.find('.select2-search__field');
                        
                        if ($searchInput.length) {
                            
                            // Remove previous handlers
                            $searchInput.off('input.addNewButton');
                            
                            // Check on input with delay and debounce
                            let inputDebounceTimer = null;
                            $searchInput.on('input.addNewButton', function() {
                                const searchVal = $(this).val();
                                
                                // Clear previous timer
                                if (inputDebounceTimer) {
                                    clearTimeout(inputDebounceTimer);
                                }
                                
                                // Debounce the check
                                inputDebounceTimer = setTimeout(() => {
                                    checkAndShowAddNewButtonForDropdown(selectIdForAdd, searchVal);
                                }, 300);
                            });
                            
                            // Check on open with debounce
                            setTimeout(() => {
                                checkAndShowAddNewButtonForDropdown(selectIdForAdd, $searchInput.val());
                            }, 300);
                        }
                    }, 50);
                });
            }
        });
    }, 500);
    
    // Global handler for Select2 search - show "Add New" button when no results
    // Use debounce to prevent multiple simultaneous calls
    const addButtonDebounce = {};
    
    $(document).on('select2:open', '.searchable-select', function() {
        const selectId = $(this).attr('id');
        if (!selectId) return;
        
        const $select = $(this);
        const $container = $select.next('.select2-container');
        
        // AUTO CLICK/FOCUS: Click and focus search input when dropdown opens
        function autoFocusSearchInput() {
            const $searchInput = $container.find('.select2-search__field');
            if ($searchInput.length && $searchInput[0]) {
                try {
                    // Multiple methods to ensure it works
                    $searchInput[0].click();
                    $searchInput[0].focus();
                    $searchInput.focus();
                    
                    // Also trigger focus and click events
                    $searchInput.trigger('focus');
                    $searchInput.trigger('click');
                    
                    // Force focus using setAttribute
                    $searchInput[0].setAttribute('tabindex', '0');
                    $searchInput[0].focus();
                    
                    // Select text if any
                    if ($searchInput[0].value) {
                        $searchInput[0].select();
                    }
                } catch(e) {
                    console.log('Focus error:', e);
                }
            }
        }
        
        // Try to focus immediately and with multiple delays
        autoFocusSearchInput();
        setTimeout(autoFocusSearchInput, 0);
        setTimeout(autoFocusSearchInput, 5);
        setTimeout(autoFocusSearchInput, 10);
        setTimeout(autoFocusSearchInput, 20);
        setTimeout(autoFocusSearchInput, 50);
        setTimeout(autoFocusSearchInput, 100);
        setTimeout(autoFocusSearchInput, 150);
        setTimeout(autoFocusSearchInput, 200);
        
        // Also use requestAnimationFrame multiple times
        requestAnimationFrame(() => {
            autoFocusSearchInput();
            requestAnimationFrame(() => {
                autoFocusSearchInput();
                requestAnimationFrame(() => {
                    autoFocusSearchInput();
                });
            });
        });
        
        // Clear any existing debounce timer for this select
        if (addButtonDebounce[selectId]) {
            clearTimeout(addButtonDebounce[selectId]);
        }
        
        // Monitor search input changes with debounce
        function setupSearchMonitoring() {
            const $searchInput = $container.find('.select2-search__field');
            if ($searchInput.length) {
                // Remove previous handlers to avoid duplicates
                $searchInput.off('input.addNewButtonGlobal');
                
                // Add new handler with debounce
                $searchInput.on('input.addNewButtonGlobal', function() {
                    const searchTerm = $(this).val();
                    
                    // Clear previous debounce
                    if (addButtonDebounce[selectId]) {
                        clearTimeout(addButtonDebounce[selectId]);
                    }
                    
                    // Debounce the function call
                    addButtonDebounce[selectId] = setTimeout(function() {
                        checkAndShowAddNewButtonForDropdown(selectId, searchTerm);
                    }, 300);
                });
                
                // Check immediately with debounce
                addButtonDebounce[selectId] = setTimeout(function() {
                    checkAndShowAddNewButtonForDropdown(selectId, $searchInput.val());
                }, 300);
            }
        }
        
        // Try multiple times to catch the search input
        setTimeout(setupSearchMonitoring, 50);
        setTimeout(setupSearchMonitoring, 100);
        setTimeout(setupSearchMonitoring, 200);
    });
    
    // Monitor Select2 results using MutationObserver (modern approach)
    // Use debounce to prevent multiple calls
    let mutationDebounceTimer = null;
    const select2ResultsObserver = new MutationObserver(function(mutations) {
        // Clear previous timer
        if (mutationDebounceTimer) {
            clearTimeout(mutationDebounceTimer);
        }
        
        // Debounce the check
        mutationDebounceTimer = setTimeout(function() {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'attributes') {
                    const $openSelect2 = $('.select2-container--open');
                    if ($openSelect2.length) {
                        const $select = $openSelect2.prev('select.searchable-select');
                        if ($select.length) {
                            const selectId = $select.attr('id');
                            if (selectId) {
                                const $searchInput = $openSelect2.find('.select2-search__field');
                                const searchTerm = $searchInput.length ? $searchInput.val() : '';
                                
                                // Only check if button doesn't already exist
                                const $existingButton = $openSelect2.find('.select2-results__option--add-new[data-select-id="' + selectId + '"]');
                                if ($existingButton.length === 0) {
                                    checkAndShowAddNewButtonForDropdown(selectId, searchTerm);
                                }
                            }
                        }
                    }
                }
            });
        }, 300);
    });
    
    // Observe Select2 results containers when they appear
    function observeSelect2Results() {
        $('.select2-results__options').each(function() {
            if (!$(this).data('observed')) {
                $(this).data('observed', true);
                select2ResultsObserver.observe(this, {
                    childList: true,
                    subtree: true,
                    attributes: true
                });
            }
        });
    }
    
    // Start observing when Select2 opens
    $(document).on('select2:open', '.searchable-select', function() {
        // AUTO CLICK: Click search input when dropdown opens
        const $select = $(this);
        const $container = $select.next('.select2-container');
        
        const autoClickSearch = () => {
            const $searchInput = $container.find('.select2-search__field');
            if ($searchInput.length && $searchInput[0]) {
                try {
                    // Multiple methods to ensure it works
                    $searchInput[0].click();
                    $searchInput[0].focus();
                    $searchInput.focus();
                    
                    // Also trigger focus and click events
                    $searchInput.trigger('focus');
                    $searchInput.trigger('click');
                    
                    // Force focus using setAttribute
                    $searchInput[0].setAttribute('tabindex', '0');
                    $searchInput[0].focus();
                } catch(e) {
                    console.log('Auto-click error:', e);
                }
            }
        };
        
        // Try multiple times with different delays
        autoClickSearch();
        setTimeout(autoClickSearch, 0);
        setTimeout(autoClickSearch, 5);
        setTimeout(autoClickSearch, 10);
        setTimeout(autoClickSearch, 20);
        setTimeout(autoClickSearch, 50);
        setTimeout(autoClickSearch, 100);
        setTimeout(autoClickSearch, 150);
        
        // Also use requestAnimationFrame multiple times
        requestAnimationFrame(() => {
            autoClickSearch();
            requestAnimationFrame(() => {
                autoClickSearch();
                requestAnimationFrame(() => {
                    autoClickSearch();
                });
            });
        });
        
        setTimeout(observeSelect2Results, 100);
        setTimeout(observeSelect2Results, 200);
    });
    
    // Also observe any existing results containers
    setTimeout(observeSelect2Results, 1000);
    
    // Light monitoring for open Select2 dropdowns (reduced frequency)
    let select2CheckCount = 0;
    const select2Monitor = setInterval(function() {
        select2CheckCount++;
        const $openSelect2 = $('.select2-container--open');
        if ($openSelect2.length) {
            const $select = $openSelect2.prev('select.searchable-select');
            if ($select.length) {
                const selectId = $select.attr('id');
                if (selectId) {
                    const $searchInput = $openSelect2.find('.select2-search__field');
                    const searchTerm = $searchInput.length ? $searchInput.val() : '';
                    if (searchTerm && searchTerm.trim() !== '') {
                        checkAndShowAddNewButtonForDropdown(selectId, searchTerm);
                    }
                }
            }
        }
        // Stop monitoring after 30 checks (30 seconds)
        if (select2CheckCount >= 30) {
            clearInterval(select2Monitor);
        }
    }, 1000); // Reduced from 300ms to 1 second
    
    // Global handler for typing on Select2 selection container
    // This allows typing directly on the Select2 container to type in search input
    $(document).on('keydown', '.select2-container .select2-selection', function(e) {
        const $container = $(this).closest('.select2-container');
        const $select = $container.prev('select.searchable-select');
        
        if (!$select.length) return;
        
        // Check if it's a printable character (not special keys)
        const isPrintable = e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey;
        
        if (isPrintable) {
            // Open dropdown if not already open
            if (!$container.hasClass('select2-container--open')) {
                $select.select2('open');
            }
            
            // Focus search input and send the character
            setTimeout(() => {
                const $searchInput = $container.find('.select2-search__field');
                if ($searchInput.length && $searchInput[0]) {
                    $searchInput[0].focus();
                    $searchInput[0].click();
                    
                    // Get current value and append the new character
                    const currentVal = $searchInput.val() || '';
                    $searchInput.val(currentVal + e.key);
                    
                    // Trigger input event to update Select2
                    $searchInput.trigger('input');
                    $searchInput.trigger('keyup');
                    
                    // Prevent default to avoid any other behavior
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, 10);
        }
    });
    
    // Re-initialize Select2 when Alpine.js shows/hides elements
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' || mutation.addedNodes.length) {
                setTimeout(function() {
                    $('.searchable-select').each(function() {
                        if (!$(this).data('select2')) {
                            $(this).select2({
                                placeholder: 'Please Select',
                                allowClear: true,
                                width: '100%'
                            });
                        }
                    });
                }, 100);
            }
        });
    });
    
    // Observe the form card for changes
    const formCard = document.querySelector('.form-card');
    if (formCard) {
        observer.observe(formCard, {
            attributes: true,
            childList: true,
            subtree: true
        });
    }
});

// Event delegation for "Add New" button clicks (works for dynamically created buttons)
// Handle clicks on both the li element and button
$(document).on('mousedown click touchstart', '.select2-results__option--add-new, .add-new-dropdown-btn', function(e) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
    
    console.log('Add New button clicked (delegation):', e.type);
    
    const $element = $(this);
    const buttonId = $element.attr('data-button-id');
    
    // Try to get data from global storage first
    let btnData = null;
    if (buttonId && window['addBtn_' + buttonId]) {
        btnData = window['addBtn_' + buttonId];
    }
    
    // Fallback to data attributes
    let selectId = btnData ? btnData.selectId : ($element.attr('data-select-id') || $element.closest('[data-select-id]').attr('data-select-id'));
    let searchValue = btnData ? btnData.searchVal : ($element.attr('data-search-value') || $element.closest('[data-search-value]').attr('data-search-value') || '');
    
    // Decode HTML entities if needed
    if (searchValue && searchValue.includes('&quot;')) {
        searchValue = searchValue.replace(/&quot;/g, '"');
    }
    if (searchValue && searchValue.includes('&#39;')) {
        searchValue = searchValue.replace(/&#39;/g, "'");
    }
    
    console.log('Select ID:', selectId, 'Search Value:', searchValue);
    
    if (!selectId) {
        console.error('Select ID not found');
        return false;
    }
    
    const $select = $('#' + selectId);
    
    if (!$select.length) {
        console.error('Select element not found for ID:', selectId);
        return false;
    }
    
    // If no search value, try to get from element text
    if (!searchValue) {
        const elementText = $element.text();
        const match = elementText.match(/Add\s+"([^"]+)"/);
        if (match && match[1]) {
            searchValue = match[1];
        }
    }
    
    // If still no search value, try from search input
    if (!searchValue) {
        const $openSelect2 = $select.next('.select2-container');
        if ($openSelect2.length) {
            const $searchInput = $openSelect2.find('.select2-search__field');
            if ($searchInput.length) {
                searchValue = $searchInput.val().trim();
            }
        }
    }
    
    console.log('Final search value:', searchValue);
    
    if (!searchValue) {
        console.error('Search value not found');
        return false;
    }
    
    // Close Select2 dropdown immediately
    try {
        $select.select2('close');
    } catch(err) {
        console.log('Select2 close error:', err);
    }
    
    // Open add new modal with pre-filled value
    setTimeout(function() {
        console.log('Opening modal for:', selectId, 'with value:', searchValue);
        if (typeof window.openAddNewModal === 'function') {
            window.openAddNewModal(selectId, searchValue);
        } else if (typeof openAddNewModal === 'function') {
            openAddNewModal(selectId, searchValue);
        } else {
            console.error('openAddNewModal function not found');
            alert('Error: Modal function not found. Please refresh the page.');
        }
    }, 150);
    
    return false;
});

// Function to open Add New modal (make it globally accessible)
window.openAddNewModal = function(selectId, preFilledValue) {
    console.log('openAddNewModal called with:', selectId, preFilledValue);
    
    const fieldLabels = {
        'group_select': 'Group Name',
        'company_select': 'Company',
        'product_name_select': 'Product Name',
        'plates_select': 'Plates',
        'amperes_select': 'Amperes',
        'volt_select': 'Volt',
        'cca_select': 'CCA',
        'minus_pole_select': 'Minus Pole',
        'technology_select': 'Technology',
        'made_in_select': 'Made In',
        'warranty_select': 'Warranty',
        'unit_select': 'Unit',
        'vBrand_select': 'Brand',
        'vModel_select': 'Model',
        'vCC_select': 'CC'
    };
    
    const fieldLabel = fieldLabels[selectId] || 'Option';
    
    // Set modal title and input value (uppercase with dash format)
    $('#addNewModalTitle').text('ADD NEW - ' + fieldLabel.toUpperCase());
    $('#addNewModalInput').val(preFilledValue || '');
    $('#addNewModalInput').attr('data-select-id', selectId);
    $('#addNewModalInput').attr('data-field-label', fieldLabel);
    
    // Show modal - React style (simple display)
    const modalElement = document.getElementById('addNewModal');
    if (!modalElement) {
        console.error('Modal element not found');
        return;
    }
    
    // Show modal
    modalElement.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Focus input after modal is shown
    setTimeout(function() {
        const input = document.getElementById('addNewModalInput');
        if (input) {
            input.focus();
            input.select();
        }
    }, 100);
};

// Also create a regular function for compatibility
function openAddNewModal(selectId, preFilledValue) {
    return window.openAddNewModal(selectId, preFilledValue);
}

// Close modal function
function closeAddNewModal() {
    const modalElement = document.getElementById('addNewModal');
    if (modalElement) {
        modalElement.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Close modal on backdrop click
document.addEventListener('click', function(e) {
    const modal = document.getElementById('addNewModal');
    if (modal && e.target === modal) {
        closeAddNewModal();
    }
});

// Close modal on close button
$(document).on('click', '#addNewModalCloseBtn, #addNewModalCancelBtn', function() {
    closeAddNewModal();
});

// Handle save button in modal (Confirm button)
$(document).on('click', '#addNewModalConfirmBtn', function() {
    const inputValue = $('#addNewModalInput').val().trim();
    const selectId = $('#addNewModalInput').attr('data-select-id');
    const fieldLabel = $('#addNewModalInput').attr('data-field-label');
    
    if (!inputValue) {
        alert('Please enter a value');
        return;
    }
    
    if (!selectId) {
        alert('Error: Select ID not found');
        return;
    }
    
    const $select = $('#' + selectId);
    if (!$select.length) {
        alert('Error: Select element not found');
        return;
    }
    
    // Disable save button
    const $saveBtn = $(this);
    $saveBtn.prop('disabled', true).text('Saving...');
    
    // Determine route based on select ID
    const routeMap = {
        'group_select': '{{ route("post.groups") ?? "" }}',
        'company_select': '{{ route("post.companies") ?? "" }}',
        'product_name_select': '{{ route("post.product") ?? "" }}',
        'plates_select': '{{ route("post.platos") ?? "" }}',
        'amperes_select': '{{ route("post.amphors") ?? "" }}',
        'volt_select': '{{ route("post.volts") ?? "" }}',
        'cca_select': '{{ route("post.cca") ?? "" }}',
        'minus_pole_select': '{{ route("post.minuspool") ?? "" }}',
        'technology_select': '{{ route("post.technology") ?? "" }}',
        'made_in_select': '{{ route("post.made_ins") ?? "" }}',
        'warranty_select': '{{ route("post.warrenty") ?? "" }}',
        'unit_select': '{{ route("post.units") ?? "" }}',
        'vBrand_select': '{{ route("post.car.manufacturer") ?? "" }}',
        'vModel_select': '{{ route("post.car.model") ?? "" }}',
        'vCC_select': '{{ route("post.engine.cc") ?? "" }}'
    };
    
    const saveRoute = routeMap[selectId];
    
    if (saveRoute) {
        // Get CSRF token from multiple sources
        let csrfToken = '';
        
        // Try meta tag first (most reliable)
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        if (metaToken) {
            csrfToken = metaToken.getAttribute('content');
        }
        
        // Fallback to form input
        if (!csrfToken) {
            const tokenInput = document.querySelector('input[name="_token"]');
            if (tokenInput) {
                csrfToken = tokenInput.value;
            }
        }
        
        // Fallback to jQuery
        if (!csrfToken && typeof $ !== 'undefined') {
            csrfToken = $('input[name="_token"]').val() || $('meta[name="csrf-token"]').attr('content') || '';
        }
        
        if (!csrfToken) {
            alert('CSRF token not found. Please refresh the page and try again.');
            $saveBtn.prop('disabled', false).text('Confirm');
            return;
        }
        
        // Save to database via AJAX
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('name', inputValue);
        
        // Add type if needed (for product)
        if (selectId === 'product_name_select') {
            const typeInput = document.querySelector('input[name="type"]');
            if (typeInput) {
                formData.append('type', typeInput.value || '');
            }
        }
        
        fetch(saveRoute, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            // Handle 419 CSRF token expired error
            if (response.status === 419) {
                throw new Error('CSRF token expired. Please refresh the page and try again.');
            }
            
            // Handle other HTTP errors
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Server error: ' + response.status);
                });
            }
            
            return response.json();
        })
        .then(data => {
            if (data.success || data.id) {
                // Add option to select
                const optionValue = data.id || data.data?.id || inputValue;
                const optionText = data.name || data.data?.name || inputValue;
                
                const $newOption = $('<option>', {
                    value: optionValue,
                    text: optionText
                });
                
                $select.append($newOption);
                $select.val(optionValue).trigger('change');
                
                // Update Alpine.js if available
                try {
                    const alpineEl = $select.closest('[x-data]')[0];
                    if (alpineEl && window.Alpine) {
                        const alpineData = window.Alpine.$data(alpineEl);
                        if (alpineData && alpineData.formData) {
                            const fieldMap = {
                                'group_select': 'groupName',
                                'company_select': 'company',
                                'product_name_select': 'productName',
                                'plates_select': 'plates',
                                'amperes_select': 'amperes',
                                'volt_select': 'volt',
                                'cca_select': 'cca',
                                'minus_pole_select': 'minusPole',
                                'technology_select': 'technology',
                                'made_in_select': 'madeIn',
                                'warranty_select': 'warranty',
                                'unit_select': 'unit',
                                'vBrand_select': 'vBrand',
                                'vModel_select': 'vModel',
                                'vCC_select': 'vCC'
                            };
                            
                            const fieldName = fieldMap[selectId];
                            if (fieldName) {
                                alpineData.formData[fieldName] = optionValue;
                            }
                        }
                    }
                } catch(e) {
                    console.log('Alpine.js update failed:', e);
                }
                
                // Close modal
                closeAddNewModal();
                
                // Show success message
                if (typeof toastr !== 'undefined') {
                    toastr.success(fieldLabel + ' added successfully!');
                } else {
                    alert(fieldLabel + ' added successfully!');
                }
            } else {
                throw new Error(data.message || 'Failed to save');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorMsg = error.message || 'Unknown error';
            
            // Special handling for CSRF errors
            if (errorMsg.includes('CSRF') || errorMsg.includes('419') || errorMsg.includes('token expired') || errorMsg.includes('PAGE EXPIRED')) {
                alert('Session expired. Please refresh the page and try again.');
                // Refresh the page after 2 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                alert('Error saving ' + fieldLabel + ': ' + errorMsg);
            }
        })
        .finally(() => {
            $saveBtn.prop('disabled', false).text('Confirm');
        });
    } else {
        // No route - just add to dropdown locally
        const $newOption = $('<option>', {
            value: inputValue,
            text: inputValue
        });
        $select.append($newOption);
        $select.val(inputValue).trigger('change');
        
        // Update Alpine.js
        try {
            const alpineEl = $select.closest('[x-data]')[0];
            if (alpineEl && window.Alpine) {
                const alpineData = window.Alpine.$data(alpineEl);
                if (alpineData && alpineData.formData) {
                    const fieldMap = {
                        'group_select': 'groupName',
                        'company_select': 'company',
                        'product_name_select': 'productName',
                        'plates_select': 'plates',
                        'amperes_select': 'amperes',
                        'volt_select': 'volt',
                        'cca_select': 'cca',
                        'minus_pole_select': 'minusPole',
                        'technology_select': 'technology',
                        'made_in_select': 'madeIn',
                        'warranty_select': 'warranty',
                        'unit_select': 'unit',
                        'vBrand_select': 'vBrand',
                        'vModel_select': 'vModel',
                        'vCC_select': 'vCC'
                    };
                    
                    const fieldName = fieldMap[selectId];
                    if (fieldName) {
                        alpineData.formData[fieldName] = inputValue;
                    }
                }
            }
        } catch(e) {}
        
        // Close modal
        closeAddNewModal();
        
        // Show success message
        if (typeof toastr !== 'undefined') {
            toastr.success(fieldLabel + ' added successfully!');
        } else {
            alert(fieldLabel + ' added successfully!');
        }
        
        $saveBtn.prop('disabled', false).text('Save');
    }
});

// Handle Enter key in modal input (already handled in HTML onkeypress, but keeping for compatibility)
$(document).on('keypress', '#addNewModalInput', function(e) {
    if (e.which === 13 || e.keyCode === 13) {
        e.preventDefault();
        $('#addNewModalConfirmBtn').click();
    }
});
</script>
@endpush

<!-- Add New Modal - React Style -->
<div id="addNewModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); z-index: 10000; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: white; width: 100%; max-width: 400px; border-radius: 12px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 id="addNewModalTitle" style="font-weight: 700; color: #344454; text-transform: uppercase; letter-spacing: 0.05em; font-size: 14px; margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                ADD NEW - COMPANY
            </h3>
            <button type="button" id="addNewModalCloseBtn" style="background: none; border: none; color: #9ca3af; cursor: pointer; padding: 4px; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; transition: color 0.2s; font-size: 18px; line-height: 1;" onmouseover="this.style.color='#6b7280'" onmouseout="this.style.color='#9ca3af'">
                ×
            </button>
        </div>
        <input 
            type="text" 
            id="addNewModalInput" 
            autofocus
            style="width: 100%; border: 1px solid #FF833E; border-radius: 8px; padding: 12px 16px; margin-bottom: 24px; font-size: 14px; font-weight: 600; outline: none; color: #344454; text-transform: uppercase; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; box-sizing: border-box;"
            placeholder="Type here..."
            onkeypress="if(event.key === 'Enter') { document.getElementById('addNewModalConfirmBtn').click(); }"
            onfocus="this.style.borderColor='#FF833E'; this.style.boxShadow='0 0 0 3px rgba(255, 131, 62, 0.1)'"
            onblur="this.style.borderColor='#FF833E'; this.style.boxShadow='none'"
        />
        <div style="display: flex; gap: 12px;">
            <button 
                type="button" 
                id="addNewModalCancelBtn" 
                style="flex: 1; background: #f3f4f6; color: #344454; font-weight: 700; padding: 12px 20px; border-radius: 8px; text-transform: uppercase; font-size: 12px; letter-spacing: 0.05em; border: none; cursor: pointer; transition: all 0.2s; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;"
                onmouseover="this.style.background='#e5e7eb'"
                onmouseout="this.style.background='#f3f4f6'"
            >
                CANCEL
            </button>
            <button 
                type="button" 
                id="addNewModalConfirmBtn"
                style="flex: 1; background: #FF833E; color: white; font-weight: 700; padding: 12px 20px; border-radius: 8px; text-transform: uppercase; font-size: 12px; letter-spacing: 0.05em; border: none; cursor: pointer; transition: all 0.2s; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);"
                onmouseover="this.style.background='#ff6b35'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)'"
                onmouseout="this.style.background='#FF833E'; this.style.boxShadow='0 1px 3px 0 rgba(0, 0, 0, 0.1)'"
            >
                CONFIRM
            </button>
        </div>
    </div>
</div>

@endsection
