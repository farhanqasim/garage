<div class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo active">
        <a href="{{ route('home') }}" class="logo logo-normal d-flex align-items-center">
            <img style="width: 60px;" src="{{ setting_value('logo', asset('assets/img/logo.svg')) }}" alt="Img">
            <h3>{{ setting_value('logo_text', 'SOFT') }}</h3>
        </a>
        <a href="{{ route('home') }}" class="logo logo-white">
            <img style="width: 60px;" src="{{ setting_value('logo', asset('assets/img/logo.svg')) }}" alt="Img">

        </a>
        <a href="{{ route('home') }}" class="logo-small">
            <img style="width: 60px;" src="{{ setting_value('logo', asset('assets/img/logo.svg')) }}" alt="Img">

        </a>
        <a id="toggle_btn" href="javascript:void(0);">
            <i data-feather="chevrons-left" class="feather-16"></i>
        </a>
    </div>
    <!-- /Logo -->
    <div class="modern-profile p-3 pb-0">
        <div class="text-center rounded bg-light p-3 mb-4 user-profile">
            <div class="avatar avatar-lg online mb-3">
                <img src="{{asset('assets/img/customer/customer15.jpg')}}" alt="Img" class="img-fluid rounded-circle">
            </div>
            <h6 class="fs-14 fw-bold mb-1">Adrian Herman</h6>
            <p class="fs-12 mb-0">System Admin</p>
        </div>
        <div class="sidebar-nav mb-3">
            <ul class="nav nav-tabs nav-tabs-solid nav-tabs-rounded nav-justified bg-transparent" role="tablist">
                <li class="nav-item"><a class="nav-link active border-0" href="#">Menu</a></li>
                <li class="nav-item"><a class="nav-link border-0" href="chat.html">Chats</a></li>
                <li class="nav-item"><a class="nav-link border-0" href="email.html">Inbox</a></li>
            </ul>
        </div>
    </div>
    <div class="sidebar-header p-3 pb-0 pt-2">
        <div class="text-center rounded bg-light p-2 mb-4 sidebar-profile d-flex align-items-center">
            <div class="avatar avatar-md onlin">
                <img src="{{asset('assets/img/customer/customer15.jpg')}}" alt="Img" class="img-fluid rounded-circle">
            </div>
            <div class="text-start sidebar-profile-info ms-2">
                <h6 class="fs-14 fw-bold mb-1">Adrian Herman</h6>
                <p class="fs-12">System Admin</p>
            </div>
        </div>
        <div class="d-flex align-items-center justify-content-between menu-item mb-3">
            <div>
                <a href="index.html" class="btn btn-sm btn-icon bg-light">
                    <i class="ti ti-layout-grid-remove"></i>
                </a>
            </div>
            <div>
                <a href="chat.html" class="btn btn-sm btn-icon bg-light">
                    <i class="ti ti-brand-hipchat"></i>
                </a>
            </div>
            <div>
                <a href="email.html" class="btn btn-sm btn-icon bg-light position-relative">
                    <i class="ti ti-message"></i>
                </a>
            </div>
            <div class="notification-item">
                <a href="activities.html" class="btn btn-sm btn-icon bg-light position-relative">
                    <i class="ti ti-bell"></i>
                    <span class="notification-status-dot"></span>
                </a>
            </div>
            <div class="me-0">
                <a href="general-settings.html" class="btn btn-sm btn-icon bg-light">
                    <i class="ti ti-settings"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="sidebar-inner slimscroll scrollbar-w-14" style="height:100vh; overflow:auto;">

        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                {{-- Dashboard - visible to all authenticated users --}}
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Main</h6>
                    <ul>
                        <li class="submenu">
                            <a href="{{ route('home') }}" class="subdrop active"><i
                                    class="ti ti-layout-grid fs-16 me-2"></i><span>Dashboard</span><span
                                    class="menu-arrow"></span></a>
                        </li>
                    </ul>
                </li>

                {{-- Inventory (view_items/add_items + item type permissions) --}}
                @canany(['view_items', 'add_items', 'view_category', 'view_parts', 'view_filters', 'view_break_pad', 'view_oil', 'view_battery', 'view_scrap', 'view_services', 'add_parts', 'add_filters', 'add_break_pad', 'add_oil', 'add_battery', 'add_scrap', 'add_services'])
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Inventory</h6>
                    <ul>
                        @canany(['view_items', 'view_parts', 'view_filters', 'view_break_pad', 'view_oil', 'view_battery', 'view_scrap', 'view_services'])
                        <li><a href="{{ route('all.items') }}"><i data-feather="box"></i><span>Items</span></a></li>
                        @endcanany
                        @canany(['add_items', 'add_parts', 'add_filters', 'add_break_pad', 'add_oil', 'add_battery', 'add_scrap', 'add_services'])
                        <li><a href="{{ route('all.items.create') }}"><i
                                    class="ti ti-table-plus fs-16 me-2"></i><span>Create Item</span></a></li>
                        @endcanany
                        @can('view_category')
                        <li><a href="{{ route('all.category') }}"><i
                                    class="ti ti-list-details fs-16 me-2"></i><span>Category</span></a></li>
                        <li><a href="{{ route('all.sub.category') }}"><i
                                    class="ti ti-carousel-vertical fs-16 me-2"></i><span>Sub Category</span></a></li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Items Parts & Role Permissions --}}
                @canany(['view_car_wash_services', 'view_role'])
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Items Parts</h6>
                    <ul>
                        @can('view_car_wash_services')
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-layout-grid fs-16 me-2"></i><span>Item
                                    Parts</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="{{ route('car.wash.services') }}">Services</a></li>
                            </ul>
                        </li>
                        @endcan
                        @can('view_role')
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-device-laptop fs-16 me-2"></i><span>Role &
                                    Permissions</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="{{ route('roles.index') }}">Group Permissions</a></li>
                            </ul>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Sales --}}
                @canany(['view_sales', 'add_sales'])
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Sales</h6>
                    <ul>
                        @can('view_sales')
                        <li class="submenu">
                            <a href="javascript:void(0);"><i
                                    class="ti ti-layout-grid fs-16 me-2"></i><span>Sales</span><span
                                    class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="{{ route('all_sales') }}">Sales</a></li>
                                <li><a href="">POS Orders</a></li>
                            </ul>
                        </li>
                        @endcan
                        <li><a href=""><i class="ti ti-file-invoice fs-16 me-2"></i><span>Invoices</span></a></li>
                        <li><a href=""><i class="ti ti-receipt-refund fs-16 me-2"></i><span>Sales Return</span></a></li>
                    </ul>
                </li>
                @endcanany

                {{-- Peoples (Customers & Suppliers) --}}
                @canany(['view_customer', 'view_supplier'])
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Peoples</h6>
                    <ul>
                        @can('view_customer')
                        <li><a href="{{ route('customers.index') }}"><i class="ti ti-users-group fs-16 me-2"></i><span>Customers</span></a></li>
                        @endcan
                        @can('view_supplier')
                        <li><a href="{{ route('suppliers.index') }}"><i class="ti ti-user-dollar fs-16 me-2"></i><span>Suppliers</span></a></li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Branches --}}
                @canany(['view_branch', 'view_user', 'add_user'])
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Branches</h6>
                    <ul>
                        @can('view_branch')
                        <li><a href="{{ route('all.branches') }}">
                                <i class="ti ti-stack-3 fs-16 me-2"></i>
                                <span>Branches</span></a>
                        </li>
                        @endcan
                        @can('view_user')
                        <li><a href="{{ route('all.users') }}">
                                <i class="ti ti-users fs-16 me-2"></i>
                                <span>Employees</span></a>
                        </li>
                        @endcan
                        @can('add_user')
                        <li><a href="{{ route('all.users') }}?add=1">
                                <i class="ti ti-user-plus fs-16 me-2"></i>
                                <span>Add New Employee</span></a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Warehouses --}}
                @canany(['view_warehouse', 'add_warehouse'])
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Warehouses</h6>
                    <ul>
                        @can('view_warehouse')
                        <li><a href="{{ route('warehouses.index') }}">
                                <i class="ti ti-archive fs-16 me-2"></i>
                                <span>All Warehouses</span></a>
                        </li>
                        @endcan
                        @can('add_warehouse')
                        <li><a href="{{ route('warehouses.create') }}">
                                <i class="ti ti-plus fs-16 me-2"></i>
                                <span>Add New Warehouse</span></a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- Bank Management --}}
                @canany(['view_banks', 'view_cash_accounts', 'view_bank_transactions', 'view_bank_accounts'])
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Bank Management</h6>
                    <ul>
                        @can('view_banks')
                        <li><a href="{{ route('admin.banks.index') }}"><i class="ti ti-building-bank fs-16 me-2"></i><span>Banks</span></a></li>
                        @endcan
                        @can('view_cash_accounts')
                        <li><a href="{{ route('admin.cash-accounts.index') }}">
                                <i class="ti ti-wallet fs-16 me-2"></i>
                                <span>Cash Accounts (Wallets)</span></a>
                        </li>
                        <li><a href="{{ route('admin.cash-transactions.index') }}">
                                <i class="ti ti-file-text fs-16 me-2"></i>
                                <span>Cash Transactions History</span></a>
                        </li>
                        @endcan
                        <li><a href="{{ route('admin.payments.index') }}">
                                <i class="ti ti-wallet fs-16 me-2"></i>
                                <span>Payments</span></a>
                        </li>
                        <li><a href="{{ route('admin.payment-methods.index') }}">
                                <i class="ti ti-credit-card-pay fs-16 me-2"></i>
                                <span>Payment Methods</span></a>
                        </li>
                        @can('view_bank_transactions')
                        <li><a href="{{ route('admin.bank-transactions.index') }}">
                                <i class="ti ti-exchange fs-16 me-2"></i>
                                <span>Bank Transactions</span></a>
                        </li>
                        @endcan
                        <li><a href="{{ route('car.wash.worker-bank-accounts') }}">
                                <i class="ti ti-credit-card fs-16 me-2"></i>
                                <span>Worker Bank Accounts</span></a>
                        </li>
                        <li><a href="{{ route('car.wash.worker-cash-accounts') }}">
                                <i class="ti ti-wallet fs-16 me-2"></i>
                                <span>Worker Cash Accounts</span></a>
                        </li>
                    </ul>
                </li>
                @endcanany

                {{-- Purchases --}}
                @canany(['view_purchases', 'add_purchases'])
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Purchases</h6>
                    <ul>
                        @can('view_purchases')
                        <li><a href="{{ route('all_purchases') }}"><i class="ti ti-shopping-bag fs-16 me-2"></i><span>Purchases</span></a></li>
                        @endcan
                        <li><a href=""><i class="ti ti-file-unknown fs-16 me-2"></i><span>Purchase Order</span></a></li>
                        <li><a href=""><i class="ti ti-file-upload fs-16 me-2"></i><span>Purchase Return</span></a></li>
                    </ul>
                </li>
                @endcanany
                {{-- <li class="submenu-open"> --}}
                    {{-- <h6 class="submenu-hdr">Peoples</h6>
                    <ul>
                        <li><a href="customers.html"><i
                                    class="ti ti-users-group fs-16 me-2"></i><span>Customers</span></a></li>
                        <li><a href="billers.html"><i class="ti ti-user-up fs-16 me-2"></i><span>Billers</span></a></li>
                        <li><a href="{{ route('all_suppliers') }}"><i
                                    class="ti ti-user-dollar fs-16 me-2"></i><span>Suppliers</span></a></li>
                        <li><a href="store-list.html"><i class="ti ti-home-bolt fs-16 me-2"></i><span>Stores</span></a>
                        </li>
                        <li><a href="warehouse.html"><i class="ti ti-archive fs-16 me-2"></i><span>Warehouses</span></a>
                        </li>
                    </ul>
                </li> --}}

                <li class="submenu-open">
                    <ul>
                        <li><a href="{{route('user.profile',auth()->user()->id)}}"><i
                                    class="ti ti-user-circle fs-16 me-2"></i><span>Profile</span></a></li>
                        <li>
                            <a href="{{ route('logout') }}" onclick="event.preventDefault();
                document.getElementById('logout-form').submit();"><i
                                    class="ti ti-logout fs-16 me-2"></i><span>Logout</span> </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
