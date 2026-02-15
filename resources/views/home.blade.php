@extends('layouts.app')
@section('title', __('Dashboard'))
@section('content')
<style>
  .sales-banner-btn:hover .sales-banner-inner { opacity: 0.95; transform: translateY(-1px); }
  .sales-banner-btn .sales-banner-inner { transition: opacity 0.2s ease, transform 0.2s ease; }
</style>
<div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2">
          <div class="mb-3">
            <h1 class="mb-1">Welcome, {{ auth()->user()->name ?? 'Guest' }} bhai</h1>
            <p class="fw-medium">You have <span class="text-primary fw-bold">{{ $todayOrdersCount ?? 0 }}</span> Orders, Today</p>
            @can('view_car_wash_jobs')
            <div class="d-flex align-items-center gap-2 flex-wrap mt-3">
              <a href="{{ route('car.wash') }}" class="btn btn-primary btn-lg">
                <i class="ti ti-car me-2"></i> Elite Car Wash
              </a>
            </div>
            @endcan
          </div>
          <div class="d-flex align-items-center gap-3">
          <div class="input-icon-start position-relative mb-3">
            <span class="input-icon-addon fs-16 text-gray-9">
              <i class="ti ti-calendar"></i>
            </span>
            <input type="text" class="form-control date-range bookingrange" placeholder="Search Product">
            </div>
          </div>
        </div>
        <!-- Purchase & Sales banners - same size as Total Sales card (col-xl-3) -->
        <div class="row mb-4">
          @canany(['view_purchases', 'add_purchases'])
          <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <a href="{{ route('purchases.create') }}" class="d-flex flex-fill text-decoration-none sales-banner-btn">
              <div class="sales-banner-inner card flex-fill border-0 overflow-hidden sale-widget" style="background: linear-gradient(135deg, #0d9488 0%, #059669 100%);">
                <div class="card-body d-flex align-items-center text-white">
                  <span class="sale-icon bg-white rounded-2 d-flex align-items-center justify-content-center" style="color: #059669;">
                    <i class="ti ti-shopping-bag fs-24"></i>
                  </span>
                  <div class="ms-2">
                    <p class="text-white mb-1">Purchase</p>
                    <div class="d-inline-flex align-items-center flex-wrap gap-2">
                      <span class="text-white fw-semibold">New Purchase</span>
                      <i class="ti ti-arrow-right fs-18 opacity-90"></i>
                    </div>
                  </div>
                </div>
              </div>
            </a>
          </div>
          @endcanany
          @canany(['view_sales', 'add_sales'])
          <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <a href="{{ route('create.sale') }}" class="d-flex flex-fill text-decoration-none sales-banner-btn">
              <div class="sales-banner-inner card flex-fill border-0 overflow-hidden sale-widget" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body d-flex align-items-center text-white">
                  <span class="sale-icon bg-white text-primary">
                    <i class="ti ti-shopping-cart fs-24"></i>
                  </span>
                  <div class="ms-2">
                    <p class="text-white mb-1">Sales</p>
                    <div class="d-inline-flex align-items-center flex-wrap gap-2">
                      <span class="text-white fw-semibold">Open Sales</span>
                      <i class="ti ti-arrow-right fs-18 opacity-90"></i>
                    </div>
                  </div>
                </div>
              </div>
            </a>
          </div>
          @endcanany
          @can('add_items')
          <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <a href="{{ route('all.items.create.new') }}" class="d-flex flex-fill text-decoration-none sales-banner-btn">
              <div class="sales-banner-inner card flex-fill border-0 overflow-hidden sale-widget" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <div class="card-body d-flex align-items-center text-white">
                  <span class="sale-icon bg-white rounded-2 d-flex align-items-center justify-content-center" style="color: #d97706;">
                    <i class="ti ti-package fs-24"></i>
                  </span>
                  <div class="ms-2">
                    <p class="text-white mb-1">Items</p>
                    <div class="d-inline-flex align-items-center flex-wrap gap-2">
                      <span class="text-white fw-semibold">Create Item</span>
                      <i class="ti ti-arrow-right fs-18 opacity-90"></i>
                    </div>
                  </div>
                </div>
              </div>
            </a>
          </div>
          @endcan
        </div>
        @canany(['view_items', 'add_items', 'view_parts', 'view_filters', 'view_break_pad', 'view_oil', 'view_battery', 'view_scrap', 'view_services'])
        @if(isset($lowStockItems) && $lowStockItems->isNotEmpty())
        <div class="alert bg-orange-transparent alert-dismissible fade show mb-4">
          <div>
            <span><i class="ti ti-info-circle fs-14 text-orange me-2"></i>Your Product </span>
            <span class="text-orange fw-semibold">{{ $lowStockItems->first()->short_disc ?? $lowStockItems->first()->p_id ?? 'Item' }} is running Low, </span>
            already below {{ (int)($lowStockItems->first()->l_stock ?? 5) }} Pcs.,
            <a href="{{ route('all.items') }}" class="link-orange text-decoration-underline fw-semibold">Add Stock</a>
          </div>
          <button type="button" class="btn-close text-gray-9 fs-14" data-bs-dismiss="alert" aria-label="Close"><i class="ti ti-x"></i></button>
        </div>
        @endif
        @endcanany
        <div class="row">
          @canany(['view_sales', 'add_sales'])
          <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <div class="card bg-primary sale-widget flex-fill">
              <div class="card-body d-flex align-items-center">
                <span class="sale-icon bg-white text-primary">
                  <i class="ti ti-file-text fs-24"></i>
                </span>
                <div class="ms-2">
                  <p class="text-white mb-1">Total Sales</p>
                  <div class="d-inline-flex align-items-center flex-wrap gap-2">
                    <h4 class="text-white">{{ number_format($totalSales ?? 0, 0) }}</h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <div class="card bg-secondary sale-widget flex-fill">
              <div class="card-body d-flex align-items-center">
                <span class="sale-icon bg-white text-secondary">
                  <i class="ti ti-repeat fs-24"></i>
                </span>
                <div class="ms-2">
                  <p class="text-white mb-1">Total Sales Return</p>
                  <div class="d-inline-flex align-items-center flex-wrap gap-2">
                    <h4 class="text-white">{{ number_format($totalSalesReturn ?? 0, 0) }}</h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endcanany
          @canany(['view_purchases', 'add_purchases'])
          <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <div class="card bg-teal sale-widget flex-fill">
              <div class="card-body d-flex align-items-center">
                <span class="sale-icon bg-white text-teal">
                  <i class="ti ti-gift fs-24"></i>
                </span>
                <div class="ms-2">
                  <p class="text-white mb-1">Total Purchase</p>
                  <div class="d-inline-flex align-items-center flex-wrap gap-2">
                    <h4 class="text-white">{{ number_format($totalPurchase ?? 0, 0) }}</h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-sm-6 col-12 d-flex">
            <div class="card bg-info sale-widget flex-fill">
              <div class="card-body d-flex align-items-center">
                <span class="sale-icon bg-white text-info">
                  <i class="ti ti-brand-pocket fs-24"></i>
                </span>
                <div class="ms-2">
                  <p class="text-white mb-1">Total Purchase Return</p>
                  <div class="d-inline-flex align-items-center flex-wrap gap-2">
                    <h4 class="text-white">{{ number_format($totalPurchaseReturn ?? 0, 0) }}</h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endcanany
        </div>
        <div class="row">
          <!-- Sales & Purchase -->
          @canany(['view_sales', 'add_sales', 'view_purchases', 'add_purchases'])
          <div class="col-xxl-8 col-xl-7 col-sm-12 col-12 d-flex">
            <div class="card flex-fill">
              <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-inline-flex align-items-center">
                  <span class="title-icon bg-soft-primary fs-16 me-2"><i class="ti ti-shopping-cart"></i></span>
                  <h5 class="card-title mb-0">Sales & Purchase</h5>
                </div>
                <ul class="nav btn-group custom-btn-group">
                  <a class="btn btn-outline-light" href="javascript:void(0);">1D</a>
                  <a class="btn btn-outline-light" href="javascript:void(0);">1W</a>
                  <a class="btn btn-outline-light" href="javascript:void(0);">1M</a>
                  <a class="btn btn-outline-light" href="javascript:void(0);">3M</a>
                  <a class="btn btn-outline-light" href="javascript:void(0);">6M</a>
                  <a class="btn btn-outline-light active" href="javascript:void(0);">1Y</a>
                </ul>
              </div>
              <div class="card-body pb-0">
                <div>
                  <div class="d-flex align-items-center gap-2">
                    <div class="border p-2 br-8">
                      <p class="d-inline-flex align-items-center mb-1"><i class="ti ti-circle-filled fs-8 text-primary-300 me-1"></i>Total Purchase</p>
                      <h4>{{ number_format($totalPurchase ?? 0, 0) }}</h4>
                    </div>
                    <div class="border p-2 br-8">
                      <p class="d-inline-flex align-items-center mb-1"><i class="ti ti-circle-filled fs-8 text-primary me-1"></i>Total Sales</p>
                      <h4>{{ number_format($totalSales ?? 0, 0) }}</h4>
                    </div>
                  </div>
                  <div id="sales-daychart"></div>
                </div>
              </div>
            </div>
          </div>
          @endcanany
          <!-- Overall Information -->
          @canany(['view_supplier', 'view_customer', 'view_sales'])
          <div class="col-xxl-4 col-xl-5 d-flex">
            <div class="card flex-fill">
              <div class="card-header">
                <div class="d-inline-flex align-items-center">
                  <span class="title-icon bg-soft-info fs-16 me-2"><i class="ti ti-info-circle"></i></span>
                  <h5 class="card-title mb-0">Overall Information</h5>
                </div>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  @can('view_supplier')
                  <div class="col-md-4">
                    <div class="info-item border bg-light p-3 text-center">
                      <div class="mb-2 text-info fs-24">
                        <i class="ti ti-user-check"></i>
                      </div>
                      <p class="mb-1">Suppliers</p>
                      <h5>{{ $suppliersCount ?? 0 }}</h5>
                    </div>
                  </div>
                  @endcan
                  @can('view_customer')
                  <div class="col-md-4">
                    <div class="info-item border bg-light p-3 text-center">
                      <div class="mb-2 text-orange fs-24">
                        <i class="ti ti-users"></i>
                      </div>
                      <p class="mb-1">Customer</p>
                      <h5>{{ $customersCount ?? 0 }}</h5>
                    </div>
                  </div>
                  @endcan
                  @canany(['view_sales', 'add_sales'])
                  <div class="col-md-4">
                    <div class="info-item border bg-light p-3 text-center">
                      <div class="mb-2 text-teal fs-24">
                        <i class="ti ti-shopping-cart"></i>
                      </div>
                      <p class="mb-1">Orders</p>
                      <h5>{{ $ordersCount ?? 0 }}</h5>
                    </div>
                  </div>
                  @endcanany
                </div>
              </div>
              <div class="card-footer pb-sm-0">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                  <h5>Customers Overview</h5>
                  <div class="dropdown dropdown-wraper">
                    <a href="javascript:void(0);" class="dropdown-toggle btn btn-sm btn-white" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="ti ti-calendar me-1"></i>Today
                    </a>
                    <ul class="dropdown-menu p-3">
                      <li>
                        <a href="javascript:void(0);" class="dropdown-item">Today</a>
                      </li>
                      <li>
                        <a href="javascript:void(0);" class="dropdown-item">Weekly</a>
                      </li>
                      <li>
                        <a href="javascript:void(0);" class="dropdown-item">Monthly</a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="row align-items-center">
                  <div class="col-sm-5">
                    <div id="customer-chart"></div>
                  </div>
                  <div class="col-sm-7">
                    <div class="row gx-0">
                      <div class="col-sm-6">
                        <div class="text-center border-end">
                          <h2 class="mb-1">{{ $ordersCount ?? 0 }}</h2>
                          <p class="text-orange mb-2">Orders</p>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <div class="text-center">
                          <h2 class="mb-1">{{ $customersCount ?? 0 }}</h2>
                          <p class="text-teal mb-2">Customers</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endcanany
        </div>
        <div class="row">
          <!-- Top Selling Products -->
          @canany(['view_sales', 'view_items', 'add_items', 'view_parts', 'view_services'])
          <div class="col-xxl-4 col-md-6 d-flex">
            <div class="card flex-fill">
              <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-inline-flex align-items-center">
                  <span class="title-icon bg-soft-pink fs-16 me-2"><i class="ti ti-box"></i></span>
                  <h5 class="card-title mb-0">Top Selling Products</h5>
                </div>
                <div class="dropdown">
                  <a href="javascript:void(0);" class="dropdown-toggle btn btn-sm btn-white" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ti ti-calendar me-1"></i>Today
                  </a>
                  <ul class="dropdown-menu p-3">
                    <li>
                      <a href="javascript:void(0);" class="dropdown-item">Today</a>
                    </li>
                    <li>
                      <a href="javascript:void(0);" class="dropdown-item">Weekly</a>
                    </li>
                    <li>
                      <a href="javascript:void(0);" class="dropdown-item">Monthly</a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="card-body sell-product">
                @forelse($topSellingProducts ?? [] as $prod)
                <div class="d-flex align-items-center justify-content-between {{ !$loop->last ? 'border-bottom' : '' }}">
                  <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-light d-flex align-items-center justify-content-center">
                      <i class="ti ti-box fs-24 text-muted"></i>
                    </div>
                    <div class="ms-2">
                      <h6 class="fw-bold mb-1"><a href="{{ route('all.items') }}">Item #{{ $prod->id }}</a></h6>
                      <div class="d-flex align-items-center item-list">
                        <p>{{ number_format($prod->sale_price ?? 0, 0) }}</p>
                        <p>{{ (int)($prod->total_qty ?? 0) }}+ Sales</p>
                      </div>
                    </div>
                  </div>
                </div>
                @empty
                <p class="text-muted mb-0">No sales data yet.</p>
                @endforelse
              </div>
            </div>
          </div>
          @endcanany
          <!-- Low Stock Products -->
          @canany(['view_items', 'add_items', 'view_parts', 'view_services'])
          <div class="col-xxl-4 col-md-6 d-flex">
            <div class="card flex-fill">
              <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-inline-flex align-items-center">
                  <span class="title-icon bg-soft-danger fs-16 me-2"><i class="ti ti-alert-triangle"></i></span>
                  <h5 class="card-title mb-0">Low Stock Products</h5>
                </div>
                <a href="{{ route('all.items') }}" class="fs-13 fw-medium text-decoration-underline">View All</a>
              </div>
              <div class="card-body">
                @forelse($lowStockItems ?? [] as $item)
                <div class="d-flex align-items-center justify-content-between {{ !$loop->last ? 'mb-4' : 'mb-0' }}">
                  <div class="d-flex align-items-center">
                    @if($item->image ?? null)
                    <a href="{{ route('all.items') }}" class="avatar avatar-lg"><img src="{{ is_string($item->image) ? $item->image : asset('images/default-item.jpg') }}" alt="img"></a>
                    @else
                    <div class="avatar avatar-lg bg-light d-flex align-items-center justify-content-center"><i class="ti ti-box fs-24 text-muted"></i></div>
                    @endif
                    <div class="ms-2">
                      <h6 class="fw-bold mb-1"><a href="{{ route('all.items') }}">{{ $item->short_disc ?? 'Item #'.$item->id }}</a></h6>
                      <p class="fs-13">ID : #{{ $item->id }}</p>
                    </div>
                  </div>
                  <div class="text-end">
                    <p class="fs-13 mb-1">Instock</p>
                    <h6 class="text-orange fw-medium">{{ (int)($item->on_hand ?? 0) }}</h6>
                  </div>
                </div>
                @empty
                <p class="text-muted mb-0">No low stock items.</p>
                @endforelse
              </div>
            </div>
          </div>
          @endcanany
          <!-- Recent Sales -->
          @canany(['view_sales', 'add_sales'])
          <div class="col-xxl-4 col-md-12 d-flex">
            <div class="card flex-fill">
              <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-inline-flex align-items-center">
                  <span class="title-icon bg-soft-pink fs-16 me-2"><i class="ti ti-box"></i></span>
                  <h5 class="card-title mb-0">Recent Sales</h5>
                </div>
                <div class="dropdown">
                  <a href="javascript:void(0);" class="dropdown-toggle btn btn-sm btn-white" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ti ti-calendar me-1"></i>Weekly
                  </a>
                  <ul class="dropdown-menu p-3">
                    <li>
                      <a href="javascript:void(0);" class="dropdown-item">Today</a>
                    </li>
                    <li>
                      <a href="javascript:void(0);" class="dropdown-item">Weekly</a>
                    </li>
                    <li>
                      <a href="javascript:void(0);" class="dropdown-item">Monthly</a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="card-body">
                @forelse($recentSales ?? [] as $sale)
                <div class="d-flex align-items-center justify-content-between {{ !$loop->last ? 'mb-4' : 'mb-0' }}">
                  <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-light d-flex align-items-center justify-content-center"><i class="ti ti-shopping-cart fs-24 text-muted"></i></div>
                    <div class="ms-2">
                      <h6 class="fw-bold mb-1"><a href="{{ route('all_sales') }}">{{ optional($sale->customer)->company ?? 'Customer #'.$sale->id }}</a></h6>
                      <div class="d-flex align-items-center item-list">
                        <p class="text-gray-9">{{ number_format($sale->grand_total ?? 0, 0) }}</p>
                      </div>
                    </div>
                  </div>
                  <div class="text-end">
                    <p class="fs-13 mb-1">{{ $sale->sale_date ? $sale->sale_date->format('d M Y') : '' }}</p>
                    <span class="badge badge-{{ $sale->status === 'completed' ? 'success' : ($sale->status === 'pending' ? 'warning' : 'secondary') }} badge-xs d-inline-flex align-items-center"><i class="ti ti-circle-filled fs-5 me-1"></i>{{ ucfirst($sale->status ?? 'N/A') }}</span>
                  </div>
                </div>
                @empty
                <p class="text-muted mb-0">No recent sales.</p>
                @endforelse
              </div>
            </div>
          </div>
          @endcanany
          <!-- /Recent Sales -->
        </div>
        <div class="row">
          <!-- Sales Statics -->
          @canany(['view_sales', 'add_sales'])
          <div class="col-xl-6 col-sm-12 col-12 d-flex">
            <div class="card flex-fill">
              <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-inline-flex align-items-center">
                  <span class="title-icon bg-soft-danger fs-16 me-2"><i class="ti ti-alert-triangle"></i></span>
                  <h5 class="card-title mb-0">Sales Statics</h5>
                </div>
                <div class="dropdown">
                  <a href="javascript:void(0);" class="dropdown-toggle btn btn-sm btn-white" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ti ti-calendar me-1"></i>2025
                  </a>
                  <ul class="dropdown-menu p-3">
                    <li>
                      <a href="javascript:void(0);" class="dropdown-item">2025</a>
                    </li>
                    <li>
                      <a href="javascript:void(0);" class="dropdown-item">2022</a>
                    </li>
                    <li>
                      <a href="javascript:void(0);" class="dropdown-item">2021</a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="card-body pb-0">
                <div class="d-flex align-items-center flex-wrap gap-2">
                  <div class="border p-2 br-8">
                    <h5 class="d-inline-flex align-items-center text-teal">{{ number_format($totalSales ?? 0, 0) }}</h5>
                    <p>Revenue</p>
                  </div>
                  <div class="border p-2 br-8">
                    <h5 class="d-inline-flex align-items-center text-orange">{{ number_format($totalPurchase ?? 0, 0) }}</h5>
                    <p>Expense</p>
                  </div>
                </div>
                <div id="sales-statistics"></div>
              </div>
            </div>
          </div>
          @endcanany
          <!-- Recent Transactions -->
          @canany(['view_sales', 'view_purchases', 'add_sales', 'add_purchases'])
          <div class="col-xl-6 col-sm-12 col-12 d-flex">
            <div class="card flex-fill">
              <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-inline-flex align-items-center">
                  <span class="title-icon bg-soft-orange fs-16 me-2"><i class="ti ti-flag"></i></span>
                  <h5 class="card-title mb-0">Recent Transactions</h5>
                </div>
                <a href="{{ route('all_sales') }}" class="fs-13 fw-medium text-decoration-underline">View All</a>
              </div>
              <div class="card-body p-0">
                <ul class="nav nav-tabs nav-justified transaction-tab">
                  <li class="nav-item"><a class="nav-link active" href="#sale" data-bs-toggle="tab">Sale</a></li>
                  <li class="nav-item"><a class="nav-link" href="#purchase-transaction" data-bs-toggle="tab">Purchase</a></li>
                  <li class="nav-item"><a class="nav-link" href="#quotation" data-bs-toggle="tab">Quotation</a></li>
                  <li class="nav-item"><a class="nav-link" href="#expenses" data-bs-toggle="tab">Expenses</a></li>
                  <li class="nav-item"><a class="nav-link" href="#invoices" data-bs-toggle="tab">Invoices</a></li>
                </ul>
                <div class="tab-content">
                  <div class="tab-pane show active" id="sale">
                    <div class="table-responsive">
                      <table class="table table-borderless custom-table">
                        <thead class="thead-light">
                          <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Total</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse($recentSales ?? [] as $sale)
                          <tr>
                            <td>{{ $sale->sale_date ? $sale->sale_date->format('d M Y') : '-' }}</td>
                            <td>
                              <div class="d-flex align-items-center file-name-icon">
                                <div class="avatar avatar-md bg-light d-flex align-items-center justify-content-center"><i class="ti ti-user text-muted"></i></div>
                                <div class="ms-2">
                                  <h6><a href="{{ route('all_sales') }}" class="fw-bold">{{ optional($sale->customer)->company ?? 'Customer #'.$sale->id }}</a></h6>
                                  <span class="fs-13 text-orange">#{{ $sale->id }}</span>
                                </div>
                              </div>
                            </td>
                            <td><span class="badge badge-{{ $sale->status === 'completed' ? 'success' : ($sale->status === 'pending' ? 'warning' : 'secondary') }} badge-xs d-inline-flex align-items-center"><i class="ti ti-circle-filled fs-5 me-1"></i>{{ ucfirst($sale->status ?? '-') }}</span></td>
                            <td class="fs-16 fw-bold text-gray-9">{{ number_format($sale->grand_total ?? 0, 0) }}</td>
                          </tr>
                          @empty
                          <tr><td colspan="4" class="text-center text-muted py-4">No sales data.</td></tr>
                          @endforelse
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="purchase-transaction">
                    <div class="table-responsive">
                      <table class="table table-borderless custom-table">
                        <thead class="thead-light">
                          <tr>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Status</th>
                            <th>Total</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse($recentPurchases ?? [] as $purchase)
                          <tr>
                            <td>{{ $purchase->purchase_date ? $purchase->purchase_date->format('d M Y') : '-' }}</td>
                            <td><a href="{{ route('all_purchases') }}" class="fw-semibold">{{ optional($purchase->supplier)->company ?? 'Supplier #'.$purchase->id }}</a></td>
                            <td><span class="badge badge-{{ $purchase->status === 'completed' ? 'success' : ($purchase->status === 'pending' ? 'warning' : 'secondary') }} badge-xs d-inline-flex align-items-center"><i class="ti ti-circle-filled fs-5 me-1"></i>{{ ucfirst($purchase->status ?? '-') }}</span></td>
                            <td class="text-gray-9">{{ number_format($purchase->grand_total ?? 0, 0) }}</td>
                          </tr>
                          @empty
                          <tr><td colspan="4" class="text-center text-muted py-4">No purchase data.</td></tr>
                          @endforelse
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <div class="tab-pane" id="quotation">
                    <div class="table-responsive">
                      <table class="table table-borderless custom-table">
                        <thead class="thead-light">
                          <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Total</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr><td colspan="4" class="text-center text-muted py-4">No data.</td></tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="expenses">
                    <div class="table-responsive">
                      <table class="table table-borderless custom-table">
                        <thead class="thead-light">
                          <tr>
                            <th>Date</th>
                            <th>Expenses</th>
                            <th>Status</th>
                            <th>Total</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr><td colspan="4" class="text-center text-muted py-4">No data.</td></tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <div class="tab-pane" id="invoices">
                    <div class="table-responsive">
                      <table class="table table-borderless custom-table">
                        <thead class="thead-light">
                          <tr>
                            <th>Customer</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Amount</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr><td colspan="4" class="text-center text-muted py-4">No data.</td></tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endcanany
          <!-- /Recent Transactions -->

        </div>

        <div class="row">
          <!-- Top Customers -->
          @can('view_customer')
          <div class="col-xxl-4 col-md-6 d-flex">
            <div class="card flex-fill">
              <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-inline-flex align-items-center">
                  <span class="title-icon bg-soft-orange fs-16 me-2"><i class="ti ti-users"></i></span>
                  <h5 class="card-title mb-0">Top Customers</h5>
                </div>
                <a href="{{ route('customers.index') }}" class="fs-13 fw-medium text-decoration-underline">View All</a>
              </div>
              <div class="card-body">
                @forelse($topCustomersList as $customer)
                <div class="d-flex align-items-center justify-content-between {{ !$loop->last ? 'border-bottom mb-3 pb-3' : '' }} flex-wrap gap-2">
                  <div class="d-flex align-items-center">
                    <a href="javascript:void(0);" class="avatar avatar-lg flex-shrink-0 bg-soft-primary rounded-circle d-flex align-items-center justify-content-center">
                      <span class="fs-16 fw-bold text-primary">{{ strtoupper(substr($customer->name ?? '?', 0, 1)) }}</span>
                    </a>
                    <div class="ms-2">
                      <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">{{ $customer->name ?? 'Customer #'.$customer->id }}</a></h6>
                      <div class="d-flex align-items-center item-list">
                        <p class="mb-0">{{ (int)($customer->order_count ?? 0) }} Orders</p>
                      </div>
                    </div>
                  </div>
                  <div class="text-end">
                    <h5>{{ number_format((float)($customer->total_amount ?? 0), 2) }}</h5>
                  </div>
                </div>
                @empty
                <p class="text-center text-muted py-3 mb-0">No customers yet.</p>
                @endforelse
              </div>
            </div>
          </div>
          @endcan
          <!-- /Top Customers -->

          <!-- Top Categories -->
          @can('view_category')
          <div class="col-xxl-4 col-md-6 d-flex">
            <div class="card flex-fill">
              <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-inline-flex align-items-center">
                  <span class="title-icon bg-soft-orange fs-16 me-2"><i class="ti ti-users"></i></span>
                  <h5 class="card-title mb-0">Top Categories</h5>
                </div>
                <div class="dropdown">
                  <a href="javascript:void(0);" class="dropdown-toggle btn btn-sm btn-white d-flex align-items-center" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="ti ti-calendar me-1"></i>Weekly
                  </a>
                  <ul class="dropdown-menu p-3">
                    <li>
                      <a href="javascript:void(0);" class="dropdown-item">Today</a>
                    </li>
                    <li>
                      <a href="javascript:void(0);" class="dropdown-item">Weekly</a>
                    </li>
                    <li>
                      <a href="javascript:void(0);" class="dropdown-item">Monthly</a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="card-body">
                <p class="text-center text-muted py-4 mb-0">No data.</p>
              </div>
            </div>
          </div>
          @endcan
          <!-- /Top Categories -->

          <!-- Order Statistics -->
          @canany(['view_sales', 'add_sales'])
          <div class="col-xxl-4 col-md-12 d-flex">
            <div class="card flex-fill">
              <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-inline-flex align-items-center">
                  <span class="title-icon bg-soft-indigo fs-16 me-2"><i class="ti ti-package"></i></span>
                  <h5 class="card-title mb-0">Order Statistics</h5>
                </div>
                <div class="dropdown">
                  <a href="javascript:void(0);" class="dropdown-toggle btn btn-sm btn-white" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ti ti-calendar me-1"></i>Weekly
                  </a>
                  <ul class="dropdown-menu p-3">
                    <li>
                      <a href="javascript:void(0);" class="dropdown-item">Today</a>
                    </li>
                    <li>
                      <a href="javascript:void(0);" class="dropdown-item">Weekly</a>
                    </li>
                    <li>
                      <a href="javascript:void(0);" class="dropdown-item">Monthly</a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="card-body pb-0">
                <div id="heat_chart"></div>
              </div>
            </div>
          </div>
          @endcanany
          <!-- /Order Statistics -->

        </div>

      </div>
@endsection
