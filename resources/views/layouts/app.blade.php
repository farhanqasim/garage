@php
  $themeDefaults = [
    'theme' => 'light',
    'sidebar' => 'light',
    'color' => 'primary',
    'layout' => 'default',
    'topbar' => 'white',
    'width' => 'fluid',
    'rtl' => false,
    'sidebar_bg' => null,
    'topbar_bg' => null,
  ];
  $themeSettings = auth()->check() ? array_merge($themeDefaults, auth()->user()->theme_settings ?? []) : $themeDefaults;
  $isRtl = !empty($themeSettings['rtl']);
@endphp
<!DOCTYPE html>
<html lang="{{ $isRtl ? 'ar' : 'en' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" data-layout-mode="light_mode" data-theme="{{ $themeSettings['theme'] ?? 'light' }}" data-sidebar="{{ $themeSettings['sidebar'] ?? 'light' }}" data-color="{{ $themeSettings['color'] ?? 'primary' }}" data-layout="{{ $themeSettings['layout'] ?? 'default' }}" data-topbar="{{ $themeSettings['topbar'] ?? 'white' }}" data-width="{{ $themeSettings['width'] ?? 'fluid' }}" data-rtl="{{ $isRtl ? '1' : '0' }}">
<head>
  <!-- Meta Tags -->
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description"
    content="Dreams POS is a powerful Bootstrap based Inventory Management Admin Template designed for businesses, offering seamless invoicing, project tracking, and estimates.">
  <meta name="keywords"
    content="inventory management, admin dashboard, bootstrap template, invoicing, estimates, business management, responsive admin, POS system">
  <meta name="author" content=" Technologies">
  <meta name="robots" content="index, follow">
   <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Auto session refresh + retry on 401 -->
  <script>
  (function() {
    var _fetch = window.fetch;
    var RETRY_KEY = 'fetchRetryRequest';
    window.fetch = function(url, opts) {
      opts = opts || {};
      return _fetch.apply(this, arguments).then(function(res) {
        if (res.status === 401) {
          var clone = res.clone();
          return clone.text().then(function(t) {
            var isSessionErr = !t || t.toLowerCase().indexOf('session') >= 0 || t.toLowerCase().indexOf('unauth') >= 0 || t.toLowerCase().indexOf('expir') >= 0;
            if (isSessionErr && !sessionStorage.getItem(RETRY_KEY)) {
              var u = typeof url === 'string' ? url : (url && url.url);
              var headers = {};
              if (opts.headers) {
                if (opts.headers instanceof Headers) { opts.headers.forEach(function(v,k){ headers[k]=v; }); }
                else if (typeof opts.headers === 'object') { headers = Object.assign({}, opts.headers); }
              }
              var canRetry = u && (opts.method === 'POST' || opts.method === 'PUT' || opts.method === 'PATCH') && (typeof opts.body === 'string' || opts.body == null);
              if (canRetry) {
                try {
                  sessionStorage.setItem(RETRY_KEY, JSON.stringify({ url: u, method: opts.method || 'POST', headers: headers, body: opts.body || null }));
                } catch (e) {}
              }
              if (typeof toastr !== 'undefined') toastr.info('Session refreshed. Retrying...');
              else alert('Session refreshed. Retrying...');
              location.reload();
            }
            return res;
          }).catch(function() { return res; });
        }
        return res;
      });
    };
    document.addEventListener('DOMContentLoaded', function() {
      try {
        var s = sessionStorage.getItem(RETRY_KEY);
        if (s) {
          sessionStorage.removeItem(RETRY_KEY);
          var d = JSON.parse(s);
          var token = document.querySelector('meta[name="csrf-token"]');
          if (token && d.headers) d.headers['X-CSRF-TOKEN'] = token.getAttribute('content');
          if (d.headers && !d.headers['Accept']) d.headers['Accept'] = 'application/json';
          if (d.headers && !d.headers['X-Requested-With']) d.headers['X-Requested-With'] = 'XMLHttpRequest';
          if (d.headers && !d.headers['Content-Type'] && d.body) d.headers['Content-Type'] = 'application/json';
          _fetch(d.url, { method: d.method || 'POST', headers: d.headers || {}, body: d.body }).then(function(r) {
            if (r.ok && typeof toastr !== 'undefined') toastr.success('Done.');
            else if (!r.ok && typeof toastr !== 'undefined') toastr.error('Please try again.');
          }).catch(function() { if (typeof toastr !== 'undefined') toastr.error('Please try again.'); });
        }
      } catch (e) {}
    });
  })();
  </script>

  <title>@yield('title') | {{env('APP_NAME')}}</title>
   <link rel="icon" href="{{ setting_value('favicon', asset('assets/img/favicon.png')) }}" type="image/x-icon"/>
  <!-- Global error handler to suppress null element addEventListener errors -->
  <script>
    window.addEventListener('error', function(e) {
      if (e.message && e.message.includes('addEventListener') && e.message.includes('null')) {
        e.preventDefault();
        console.warn('Suppressed addEventListener error on null element:', e.filename, e.lineno);
        return true;
      }
    }, true);
  </script>
  @auth
  <script>
    window.THEME_FROM_SERVER = @json($themeSettings);
    window.THEME_SETTINGS_INDEX_URL = @json(route('theme.settings.index'));
    window.THEME_SETTINGS_SAVE_URL = @json(route('theme.settings.update'));
    window.THEME_SETTINGS_CSRF = @json(csrf_token());
    @can('view_setting')
    window.THEME_CUSTOMIZER_ALLOWED = true;
    @endcan
  </script>
  @endauth
  <script src="{{asset('assets/js/theme-script.js')}}" type="f89f8e290dd47aa8bc06c7c9-text/javascript"></script>
  <!-- Apple Touch Icon -->
  <link rel="apple-touch-icon" sizes="180x180" href="{{asset('assets/img/apple-touch-icon.png')}}">
  {{-- <!-- Bootstrap CSS --> --}}
  <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">



  <!-- Datetimepicker CSS -->
  <link rel="stylesheet" href="{{asset('assets/css/bootstrap-datetimepicker.min.css')}}">
  	<!-- Datatable CSS -->
   <link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap5.min.css') }}">
  <!-- animation CSS -->
  <link rel="stylesheet" href="{{asset('assets/css/animate.css')}}">
  <!-- Select2 CSS -->
  <link rel="stylesheet" href="{{asset('assets/plugins/select2/css/select2.min.css')}}">

  <!-- Daterangepikcer CSS -->
  <link rel="stylesheet" href="{{asset('assets/plugins/daterangepicker/daterangepicker.css')}}">
  <!-- Tabler Icon CSS -->
  <link rel="stylesheet" href="{{asset('assets/plugins/tabler-icons/tabler-icons.min.css')}}">
  <!-- Fontawesome CSS -->
  <link rel="stylesheet" href="{{asset('assets/plugins/fontawesome/css/fontawesome.min.css')}}">
  <link rel="stylesheet" href="{{asset('assets/plugins/fontawesome/css/all.min.css')}}">
  <!-- Color Picker Css -->
  <link rel="stylesheet" href="{{asset('assets/plugins/%40simonwep/pickr/themes/nano.min.css')}}">
  <!-- Main CSS -->
  <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<!-- Summernote CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
{{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}

<!-- (Optional) Bootstrap 5 CSS (if not already added) -->
<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->

  <style>
/* All common input types + select */

label{
    font-weight: bold;
    font-size: 15px;
    text-transform: uppercase;
}
    .select2-container--default .select2-selection--single .select2-selection__rendered,
    .select2-results__option {
        text-transform: capitalize;
            font-weight: bold;
          font-size: 15px;
    }

    th ,td{
        font-size: 14px !important;
        font-weight: bold !important;
        text-transform: uppercase;
    }
    .select2-selection__rendered {
        text-transform: uppercase !important;
    }
    .select2-results__option{
        text-transform: uppercase !important;
    }
    .select2-selection__clear{
        margin-right: 30px !important;
    }


    .highlight {
        background: #fe9f43;
        color: white;
        padding: 2px;
        border-radius: 3px;
    }
    .scrollbar-w-14::-webkit-scrollbar {
        width: 8px !important;
        }

        .inputswidth{
            width: 75% !important;
        }

        /* ============================================
           COMPREHENSIVE RESPONSIVE STYLES
           ============================================ */
        
        /* Mobile-first responsive adjustments */
        @media (max-width: 768px) {
            /* Input widths - full width on mobile */
            .inputswidth {
                width: 100% !important;
            }
            
            /* Type boxes - stack on mobile */
            .type-box {
                padding: 15px !important;
                font-size: 14px !important;
            }
            
            .type-box .fs-1 {
                font-size: 2rem !important;
            }
            
            /* Form controls - better mobile sizing */
            .form-control, .select2-container {
                font-size: 16px !important; /* Prevents zoom on iOS */
            }
            
            /* Modal adjustments */
            .modal-dialog {
                margin: 0.5rem !important;
            }
            
            .modal-content {
                border-radius: 0.5rem !important;
            }
            
            /* Table responsive improvements */
            .table-responsive {
                font-size: 12px !important;
            }
            
            .table th, .table td {
                padding: 0.5rem !important;
                font-size: 12px !important;
            }
            
            /* Card padding */
            .card-body {
                padding: 1rem !important;
            }
            
            /* Page header adjustments */
            .page-header {
                flex-direction: column;
                align-items: flex-start !important;
            }
            
            .page-header .add-item {
                flex-direction: column;
                width: 100%;
            }
            
            /* Button groups - stack on mobile */
            .btn-group, .input-group {
                flex-wrap: wrap;
            }
            
            .input-group .btn {
                flex: 1 1 auto;
                margin-top: 0.5rem;
            }
            
            /* Navigation improvements */
            .table-top-head {
                margin-top: 1rem;
            }
            
            /* Reduce font sizes on mobile */
            label {
                font-size: 13px !important;
            }
            
            h4 {
                font-size: 1.25rem !important;
            }
            
            h5 {
                font-size: 1.1rem !important;
            }
            
            h6 {
                font-size: 1rem !important;
            }
            
            /* Badge adjustments */
            .badge {
                font-size: 0.7rem !important;
                padding: 0.35em 0.65em !important;
            }
            
            /* Year range badges */
            .year-range-item {
                margin-bottom: 0.75rem !important;
            }
            
            /* Select2 dropdown adjustments */
            .select2-container {
                width: 100% !important;
            }
            
            /* Prevent horizontal scroll */
            body {
                overflow-x: hidden;
            }
            
            .container, .container-fluid {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
        }
        
        /* Tablet adjustments */
        @media (min-width: 769px) and (max-width: 1024px) {
            .inputswidth {
                width: 85% !important;
            }
            
            .type-box {
                padding: 18px !important;
            }
            
            .table th, .table td {
                padding: 0.75rem !important;
                font-size: 13px !important;
            }
        }
        
        /* Small mobile devices */
        @media (max-width: 576px) {
            /* Type boxes - full width on very small screens */
            .type-box {
                padding: 12px !important;
                font-size: 12px !important;
                margin-bottom: 0.5rem;
            }
            
            /* Form columns - full width */
            .col-md-4, .col-md-6, .col-md-12 {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
                margin-bottom: 1rem;
            }
            
           
            
          
            
            .modal-body {
                flex: 1;
                overflow-y: auto;
            }
            
            /* Table - hide less important columns */
            .table-responsive table {
                font-size: 11px !important;
            }
            
            /* Action buttons - smaller */
            .btn-sm {
                padding: 0.25rem 0.5rem !important;
                font-size: 0.75rem !important;
            }
            
            /* Dropdown menus */
            .dropdown-menu {
                font-size: 14px !important;
            }
            .input-group .form-control,
            .input-group .select2-container {
                width: 100% !important;
                margin-bottom: 0.5rem;
            }
            
            .input-group .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            /* Year range inputs */
            .year-range-item .row {
                margin: 0;
            }
            
            .year-range-item .col-5 {
                flex: 0 0 48%;
                max-width: 48%;
                padding: 0.25rem;
            }
            
            .year-range-item .col-2 {
                flex: 0 0 4%;
                max-width: 4%;
                padding: 0.25rem;
            }
        }
        
        /* Extra large screens - optimize spacing */
        @media (min-width: 1400px) {
            .container {
                max-width: 1320px;
            }
        }
        
        /* Print styles */
        @media print {
            .no-print {
                display: none !important;
            }
            
            .table {
                font-size: 10px !important;
            }
        }
        
        /* Landscape mobile orientation */
        @media (max-width: 768px) and (orientation: landscape) {
            .modal-dialog {
                margin: 0.5rem !important;
                max-width: 95% !important;
            }
            
         
        }
        
        /* Touch device improvements */
        @media (hover: none) and (pointer: coarse) {
            /* Larger touch targets */
            .btn {
                min-height: 44px;
                min-width: 44px;
            }
            
            .form-control, select, .select2-selection {
                min-height: 44px;
            }
            
            /* Better spacing for touch */
            .input-group .btn {
                padding: 0.5rem 1rem;
            }
        }
        
        /* Prevent text size adjustment on iOS */
        @supports (-webkit-touch-callout: none) {
            input, select, textarea {
                font-size: 16px !important;
            }
        }

        .edit_unit{
            width: 80% !important;
        }

  </style>

  @stack('styles')
</head>
<body @if(!empty($themeSettings['sidebar_bg'])) data-sidebarbg="{{ $themeSettings['sidebar_bg'] }}" @endif @if(!empty($themeSettings['topbar_bg'])) data-topbarbg="{{ $themeSettings['topbar_bg'] }}" @endif>
  {{-- <div id="global-loader">
    <div class="whirly-loader"> </div>
  </div> --}}
  <!-- Main Wrapper -->
  <div class="main-wrapper">

    <!-- Header -->
      @include('include.header')
    <!-- /Header -->
    <!-- Sidebar -->
     @include('include.sidebar')
    <!-- /Sidebar -->
    <div class="page-wrapper">
      @yield('content')
       @include('include.footer')
    </div>

  </div>
  <!-- /Main Wrapper -->

  <!-- Add Stock -->
  <div class="modal fade" id="add-stock">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div class="page-title">
            <h4>Add Stock</h4>
          </div>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="">
          <div class="modal-body">
            <div class="row">
              <div class="col-lg-12">
                <div class="mb-3">
                  <label class="form-label">Warehouse <span class="text-danger ms-1">*</span></label>
                  <select class="select">
                    <option>Select</option>
                    <option>Lobar Handy</option>
                    <option>Quaint Warehouse</option>
                  </select>
                </div>
              </div>
              <div class="col-lg-12">
                <div class="mb-3">
                  <label class="form-label">Store <span class="text-danger ms-1">*</span></label>
                  <select class="select">
                    <option>Select</option>
                    <option>Selosy</option>
                    <option>Logerro</option>
                  </select>
                </div>
              </div>
              <div class="col-lg-12">
                <div class="mb-3">
                  <label class="form-label">Responsible Person <span class="text-danger ms-1">*</span></label>
                  <select class="select">
                    <option>Select</option>
                    <option>Steven</option>
                    <option>Gravely</option>
                  </select>
                </div>
              </div>
              <div class="col-lg-12">
                <div class="search-form mb-0">
                  <label class="form-label">Product <span class="text-danger ms-1">*</span></label>
                  <div class="position-relative">
                    <input type="text" class="form-control" placeholder="Select Product">
                    <i data-feather="search" class="feather-search"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-md btn-dark me-2" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-md btn-primary">Add Stock</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- /Add Stock -->


  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/feather-icons"></script>
  <!-- jQuery -->
  {{-- <script src="{{asset('assets/js/jquery-3.7.1.min.js')}}" ></script> --}}

  <!-- Feather Icon JS -->
  <script src="{{asset('assets/js/feather.min.js')}}" ></script>

  <!-- Slimscroll JS -->
  <script src="{{asset('assets/js/jquery.slimscroll.min.js')}}" type="f89f8e290dd47aa8bc06c7c9-text/javascript"></script>

	<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}" type="a29272631196a62b967d88a8-text/javascript"></script>
<script src="{{ asset('assets/js/dataTables.bootstrap5.min.js') }}" type="a29272631196a62b967d88a8-text/javascript"></script>
  <!-- Bootstrap Core JS -->
  <script src="{{asset('assets/js/bootstrap.bundle.min.js')}}" type="f89f8e290dd47aa8bc06c7c9-text/javascript"></script>

  <!-- ApexChart JS -->
  <script src="{{asset('assets/plugins/apexchart/apexcharts.min.js')}}" type="f89f8e290dd47aa8bc06c7c9-text/javascript"></script>
  <script src="{{asset('assets/plugins/apexchart/chart-data.js')}}" type="f89f8e290dd47aa8bc06c7c9-text/javascript"></script>

  <!-- Chart JS -->
  <script src="{{asset('assets/plugins/chartjs/chart.min.js')}}" type="f89f8e290dd47aa8bc06c7c9-text/javascript"></script>
  <script src="{{asset('assets/plugins/chartjs/chart-data.js')}}" type="f89f8e290dd47aa8bc06c7c9-text/javascript"></script>

  <!-- Daterangepikcer JS -->
  <script src="{{asset('assets/js/moment.min.js')}}" type="f89f8e290dd47aa8bc06c7c9-text/javascript"></script>
  	<script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}" type="a29272631196a62b967d88a8-text/javascript"></script>
  <script src="{{asset('assets/plugins/daterangepicker/daterangepicker.js')}}" type="f89f8e290dd47aa8bc06c7c9-text/javascript"></script>

  <!-- Select2 JS -->
  <script src="{{asset('assets/plugins/select2/js/select2.min.js')}}" type="f89f8e290dd47aa8bc06c7c9-text/javascript"></script>

  <!-- Color Picker JS -->
  <script src="{{asset('assets/plugins/%40simonwep/pickr/pickr.es5.min.js')}}" type="f89f8e290dd47aa8bc06c7c9-text/javascript"></script>
{{-- <script src="{{ asset('assets/js/custom-select2.js') }}" ></script> --}}
  <!-- Custom JS -->
  <script src="{{asset('assets/js/theme-colorpicker.js')}}" type="f89f8e290dd47aa8bc06c7c9-text/javascript"></script>
  <script src="{{asset('assets/js/script.js')}}" type="f89f8e290dd47aa8bc06c7c9-text/javascript"></script>

  <script src="{{asset('assets/cdn-cgi/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js')}}" data-cf-settings="f89f8e290dd47aa8bc06c7c9-|49" defer onerror="this.onerror=null;">
  </script>

	<script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015" integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ==" data-cf-beacon='{"version":"2024.11.0","token":"3ca157e612a14eccbb30cf6db6691c29","server_timing":{"name":{"cfCacheStatus":true,"cfEdge":true,"cfExtPri":true,"cfL4":true,"cfOrigin":true,"cfSpeedBrain":true},"location_startswith":null}}' crossorigin="anonymous"></script>
  
  <script>
  // Suppress 404 errors for Cloudflare resources and missing files
  (function() {
      const originalError = window.onerror;
      window.onerror = function(msg, url, line, col, error) {
          // Suppress Cloudflare cdn-cgi/rum errors
          if (url && (url.includes('cdn-cgi/rum') || url.includes('cdn-cgi/scripts') || url.includes('cloudflare'))) {
              return true; // Suppress error
          }
          // Suppress 404 errors for images and SVGs
          if (msg && (msg.includes('404') || msg.includes('Failed to load resource'))) {
              if (url && (url.includes('.svg') || url.includes('.png') || url.includes('.jpg') || url.includes('.jpeg') || url.includes('cdn-cgi') || url.includes('cloudflare'))) {
                  return true; // Suppress error
              }
          }
          // Call original error handler for other errors
          if (originalError) {
              return originalError.apply(this, arguments);
          }
          return false;
      };
      
      // Suppress console errors for Cloudflare and missing files
      const originalConsoleError = console.error;
      console.error = function(...args) {
          const message = args[0] || '';
          if (typeof message === 'string') {
              // Suppress Cloudflare errors
              if (message.includes('cdn-cgi/rum') || message.includes('cdn-cgi/scripts') || message.includes('cloudflare') || message.includes('POST http') && message.includes('cdn-cgi')) {
                  return; // Suppress
              }
              // Suppress 404 errors for images and Cloudflare
              if (message.includes('404') || message.includes('Failed to load resource')) {
                  if (message.includes('.svg') || message.includes('.png') || message.includes('.jpg') || message.includes('.jpeg') || message.includes('cdn-cgi') || message.includes('cloudflare')) {
                      return; // Suppress
                  }
              }
          }
          originalConsoleError.apply(console, args);
      };
      
      // Suppress fetch/XMLHttpRequest errors for Cloudflare RUM
      const originalFetch = window.fetch;
      if (originalFetch) {
          window.fetch = function(...args) {
              const url = args[0];
              if (typeof url === 'string' && (url.includes('cdn-cgi/rum') || url.includes('cloudflare'))) {
                  return Promise.reject(new Error('Suppressed Cloudflare RUM request'));
              }
              return originalFetch.apply(this, args).catch(function(error) {
                  if (error && error.message && error.message.includes('Suppressed')) {
                      return Promise.resolve(new Response(null, { status: 200 }));
                  }
                  throw error;
              });
          };
      }
      
      // Suppress XMLHttpRequest errors for Cloudflare
      const originalXHROpen = XMLHttpRequest.prototype.open;
      XMLHttpRequest.prototype.open = function(method, url, ...rest) {
          if (typeof url === 'string' && (url.includes('cdn-cgi/rum') || url.includes('cloudflare'))) {
              this._suppressError = true;
          }
          return originalXHROpen.apply(this, [method, url, ...rest]);
      };
      
      const originalXHRSend = XMLHttpRequest.prototype.send;
      XMLHttpRequest.prototype.send = function(...args) {
          if (this._suppressError) {
              this.addEventListener('error', function(e) {
                  e.stopPropagation();
              }, true);
              this.addEventListener('loadend', function() {
                  if (this.status === 404 && this.responseURL && this.responseURL.includes('cdn-cgi')) {
                      // Suppress 404 for Cloudflare endpoints
                  }
              });
          }
          return originalXHRSend.apply(this, args);
      };
  })();
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
<script src="//unpkg.com/alpinejs" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>

<script>
    // function to initialize yearpicker
    function initYearPicker() {
        $('.yearpicker').datepicker({
            format: "yyyy",
            viewMode: "years",
            minViewMode: "years",
            autoclose: true
        });
    }
    // initialize on page load
    initYearPicker();

</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("tableSearch");
    const table = document.getElementById("searchableTable");
    if (!searchInput || !table) return;

    function removeHighlights() {
        const highlighted = table.querySelectorAll(".highlight");
        highlighted.forEach(span => span.outerHTML = span.innerText);
    }

    function highlightText(text) {
        if (text.trim() === "") return;
        const regex = new RegExp(text, "gi");

        table.querySelectorAll("tbody tr").forEach(row => {
            row.querySelectorAll("td").forEach(cell => {

                // ❌ Skip highlight in actions column
                if (cell.classList.contains("no-highlight")) return;

                const original = cell.textContent;
                if (original.trim() !== "") {
                    const newHTML = original.replace(regex, match => `<span class="highlight">${match}</span>`);
                    cell.innerHTML = newHTML;
                }
            });
        });
    }

    searchInput.addEventListener("keyup", function () {
        const value = this.value.toLowerCase();

        document.querySelectorAll("#searchableTable tbody tr").forEach(row => {
            const rowText = row.innerText.toLowerCase();
            row.style.display = rowText.includes(value) ? "" : "none";
        });

        removeHighlights();
        highlightText(this.value);
    });
});
</script>

<script>
// 🔊 Global function to play delete sound (can be called from anywhere)
function playDeleteSound() {
    const deleteSound = document.getElementById('deleteSound');
    if (deleteSound) {
        deleteSound.currentTime = 0; // Reset to start
        deleteSound.play().catch(function(error) {
            // If audio play fails (e.g., user interaction required), just continue silently
            console.log('Delete sound play failed:', error);
        });
    }
}

// 🔊 Global function to play save sound (can be called from anywhere)
function playSaveSound() {
    const saveSound = document.getElementById('saveSound');
    if (saveSound) {
        saveSound.currentTime = 0; // Reset to start
        saveSound.play().catch(function(error) {
            // If audio play fails (e.g., user interaction required), just continue silently
            console.log('Save sound play failed:', error);
        });
    }
}

function confirmDelete(formId, customMessage = null) {
    // Enhanced check: Try multiple methods to find the form
    let deleteForm = document.getElementById(formId);
    
    // If not found by ID, try querySelector
    if (!deleteForm) {
        deleteForm = document.querySelector('#' + formId.replace(/[^a-zA-Z0-9_-]/g, '\\$&'));
    }
    
    // If still not found, try to find by action attribute pattern
    if (!deleteForm && formId.includes('delete-form-')) {
        const itemId = formId.replace('delete-form-', '');
        deleteForm = document.querySelector(`form[action*="${itemId}"]`);
    }
    
    // Final check
    if (!deleteForm) {
        console.error('Delete form not found:', formId);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Delete form not found. Please refresh the page and try again.',
            footer: '<small>Form ID: ' + formId + '</small>'
        });
        return;
    }

    const message = customMessage || 'Are you sure you want to delete this item?';
    Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Final check before submitting - try to find form again
            let form = document.getElementById(formId);
            if (!form) {
                form = document.querySelector('#' + formId.replace(/[^a-zA-Z0-9_-]/g, '\\$&'));
            }
            if (!form && formId.includes('delete-form-')) {
                const itemId = formId.replace('delete-form-', '');
                form = document.querySelector(`form[action*="${itemId}"]`);
            }
            
            if (form && form.tagName === 'FORM') {
                // 🔊 Play delete sound before submitting
                playDeleteSound();
                // Submit the form
                form.submit();
            } else {
                console.error('Delete form not found before submission:', formId);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Delete form not found. Please refresh the page and try again.',
                    footer: '<small>Form ID: ' + formId + '</small>'
                });
            }
        }
    });
}
</script>

@stack('scripts')
<script>
  // Global authentication check - redirect to login if session expired
  $(document).ready(function() {
    // Check if user is authenticated on page load
    @if(!auth()->check())
      window.location.href = '{{ url("/") }}';
    @endif
    
    // Intercept all AJAX requests to handle authentication
    $.ajaxSetup({
      statusCode: {
        401: function() {
          toastr.error('Your session has expired. Please login again.');
          setTimeout(function() {
            window.location.href = '{{ url("/") }}';
          }, 2000);
        }
      }
    });
  });

  $(document).ready(function() {
    $('#summernote').summernote({
      placeholder: 'Write something awesome here...',
      tabsize: 2,
      height: 300, // height in px
      toolbar: [
        ['style', ['style']],
        ['font', ['bold', 'underline', 'italic', 'clear']],
        ['fontname', ['fontname']],
        ['color', ['color']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['insert', ['link', 'picture', 'video']],
        ['view', ['fullscreen', 'codeview', 'help']]
      ]
    });
  });
</script>



  <!-- ✅ Toastr Configuration -->
  <script>
  toastr.options = {
      "closeButton": true,
      "debug": false,
      "newestOnTop": true,
      "progressBar": true,
      "positionClass": "toast-top-right",
      "preventDuplicates": false,
      "showDuration": "1000",
      "hideDuration": "3000",
      "timeOut": "4000",
      "extendedTimeOut": "3000",
      "showEasing": "swing",
      "hideEasing": "linear",
      "showMethod": "fadeIn",
      "hideMethod": "fadeOut"
  };
  </script>

  <!-- ✅ Laravel Flash Message Integration - run after DOM ready so toastr + saveSound element exist -->
  <script>
  $(document).ready(function() {
  @if (session('success'))
      @php
          $referer = request()->header('referer');
          $isCreateEditPage = request()->is('admin/item/create') || request()->is('admin/item/*/edit') || request()->is('all/items/create') || request()->is('item/edit/*');
          $showSuccess = !$isCreateEditPage;
          if ($isCreateEditPage) {
              $currentUrl = request()->url();
              $showSuccess = ($referer && $referer !== $currentUrl) || true;
          }
          $successMessage = session('success');
          $isDeleteMessage = stripos($successMessage, 'delete') !== false || stripos($successMessage, 'deleted') !== false || stripos($successMessage, 'restored') !== false;
      @endphp
      @if ($showSuccess)
          if (typeof toastr !== 'undefined') toastr.success({{ json_encode(session('success')) }});
          @if (!$isDeleteMessage)
          if (typeof playSaveSound === 'function') setTimeout(function() { playSaveSound(); }, 150);
          @endif
      @endif
  @endif

  @if (session('error'))
      if (typeof toastr !== 'undefined') toastr.error({{ json_encode(session('error')) }});
  @endif

  @if (session('warning'))
      if (typeof toastr !== 'undefined') toastr.warning({{ json_encode(session('warning')) }});
  @endif

  @if (session('info'))
      if (typeof toastr !== 'undefined') toastr.info({{ json_encode(session('info')) }});
  @endif
  });



  </script>
  <script>
    // Global AJAX success handler - only show messages for actual form submissions
    // Don't show messages for data loading calls (GET requests)
    $(document).ajaxSuccess(function(event, xhr, settings) {
        try {
            // Skip if this is a GET request (data loading, not form submission)
            if (settings.type && settings.type.toUpperCase() === 'GET') {
                return;
            }
            
            // Skip if URL contains data loading endpoints
            const url = settings.url || '';
            const method = settings.type || '';
            
            // Skip data loading operations
            if (method.toUpperCase() === 'GET' ||
                url.includes('/items/by-type/') || 
                url.includes('/categories/') && url.includes('/subcategories') ||
                url.includes('/load') ||
                url.includes('/fetch') ||
                url.includes('/get') ||
                url.includes('/show') ||
                url.includes('/units/') && method.toUpperCase() === 'GET') {
                return;
            }
            
            // Skip delete operations - they have their own sound
            if (method.toUpperCase() === 'DELETE' ||
                url.includes('/delete') ||
                url.includes('/destroy') ||
                url.includes('/destory') ||
                url.includes('/force-delete')) {
                return;
            }
            
            // Skip item create/update - those pages show their own toast + save sound (avoids duplicate toasts)
            if (url.indexOf('/all/items/store') !== -1 || url.indexOf('/item/update/') !== -1) {
                return;
            }
            
            let response = xhr.responseJSON;
            if (!response) return;

            // Only show success message if:
            // 1. Response has success: true
            // 2. Response has a message (not just data loading)
            // 3. It's not a data fetching operation
            // 4. It's a save/create/update operation (not delete)
            if (response.success === true && response.message && !response.items && !response.data) {
                toastr.success(response.message);
                // 🔊 Play save sound only for actual save/create/update operations
                playSaveSound();
            } else if (response.success === false && response.message) {
                toastr.error(response.message);
            }
        } catch (e) {
            console.log("Not JSON response or error parsing", e);
        }
    });

    $(document).ajaxError(function(event, xhr) {
        // Handle authentication errors (401 Unauthorized)
        if (xhr.status === 401) {
            let response = xhr.responseJSON;
            let redirectUrl = (response && response.redirect) ? response.redirect : '{{ url("/") }}';
            
            toastr.error(response?.message || 'Your session has expired. Please login again.');
            
            // Redirect to login after a short delay
            setTimeout(function() {
                window.location.href = redirectUrl;
            }, 2000);
            return;
        }
        
        // Handle other errors
        let response = xhr.responseJSON;
        if (response && response.message) {
            toastr.error(response.message);
        } else {
            toastr.error("Server Error! Please try again.");
        }
    });
</script>


  <script>
        $('.searchable-select').select2({
            templateResult: function(data) {
                // Hide disabled/filtered options in Select2 dropdown
                if (data.element && (data.element.disabled || $(data.element).hasClass('filtered-out'))) {
                    return null;
                }
                return data.text;
            },
            templateSelection: function(data) {
                return data.text;
            },
            width: '100%',
            placeholder: 'Please Select',
            allowClear: true
        });
  </script>


<script>
document.addEventListener("DOMContentLoaded", function() {
  const searchInput = document.getElementById("tableSearch");

  if (searchInput) {
    searchInput.addEventListener("keyup", function() {
      const filter = searchInput.value.toLowerCase();
      const tables = document.querySelectorAll("table[id^='searchableTable']");

      tables.forEach(table => {
        const rows = table.getElementsByTagName("tr");
        for (let i = 1; i < rows.length; i++) { // skip the header
          const cells = rows[i].getElementsByTagName("td");
          let match = false;
          for (let j = 0; j < cells.length; j++) {
            const cellText = cells[j].textContent.toLowerCase();
            if (cellText.includes(filter)) {
              match = true;
              break;
            }
          }
          rows[i].style.display = match ? "" : "none";
        }
      });
    });
  }
});
</script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {


            // ✅ Status Toggle JS (your existing logic)
            document.querySelectorAll('.status-checkbox').forEach(function(input) {
                input.addEventListener('change', function() {
                    let form = this.closest('form');
                    let hiddenStatusInput = form.querySelector('input[name="status"]');
                    hiddenStatusInput.value = this.checked ? 'active' : 'inactive';
                    form.submit();
                });
            });
        });
</script>

<script>
        document.addEventListener('DOMContentLoaded', function() {
            var exportExcel = document.querySelector('.export-excel');
            var exportPdf = document.querySelector('.export-pdf');
            if (exportExcel) {
                exportExcel.addEventListener('click', function() {
                    var table = document.getElementById('searchableTable');
                    if (table && typeof XLSX !== 'undefined') {
                        var wb = XLSX.utils.table_to_book(table, {sheet:"Units"});
                        XLSX.writeFile(wb, "units.xlsx");
                    }
                });
            }
            if (exportPdf) {
                exportPdf.addEventListener('click', function() {
                    var table = document.getElementById('searchableTable');
                    if (table && typeof html2pdf !== 'undefined') {
                        var opt = {
                            margin: 0.5,
                            filename: 'units.pdf',
                            image: { type: 'jpeg', quality: 0.95 },
                            html2canvas: { scale: 2 },
                            jsPDF: { unit: 'in', format: 'A4', orientation: 'portrait' }
                        };
                        html2pdf().from(table).set(opt).save();
                    }
                });
            }
        });
</script>

<!-- Delete Sound Audio Element - Available Globally -->
<audio id="deleteSound" src="{{ asset('deleteaudio_ubWu5Ok3.mp3') }}" preload="auto"></audio>

<!-- Save Sound Audio Element - Available Globally -->
<audio id="saveSound" src="{{ asset('savesuccessfully.mp3') }}" preload="auto"></audio>

</body>

</html>
