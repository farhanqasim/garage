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
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Inventory</h6>
                    <ul>
                        <li><a href="{{ route('all.items') }}"><i data-feather="box"></i><span>Items</span></a></li>
                        <li><a href="{{ route('all.items.create') }}"><i
                                    class="ti ti-table-plus fs-16 me-2"></i><span>Create Item</span></a></li>
                        <li><a href="{{ route('all.category') }}"><i
                                    class="ti ti-list-details fs-16 me-2"></i><span>Category</span></a></li>
                        <li><a href="{{ route('all.sub.category') }}"><i
                                    class="ti ti-carousel-vertical fs-16 me-2"></i><span>Sub Category</span></a></li>
                    </ul>
                </li>
                    <li class="submenu-open">
                    <h6 class="submenu-hdr">Items Parts</h6>
                    <ul>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-layout-grid fs-16 me-2"></i><span>Item
                                    Parts</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="">Parts</a></li>
                                <li><a href="">Battery</a></li>
                                <li><a href="">Oil</a></li>
                                <li><a href="">Scrap</a></li>
                                <li><a href="">Services</a></li>
                                {{-- <li><a href="{{ route('all.vehical') }}">Vehicals</a></li>
                                <li><a href="{{ route('all.brands') }}">Brands</a></li>
                                <li><a href="{{ route('all.platos') }}">Platos</a></li>
                                <li><a href="{{ route('all.amphors') }}">Amphors</a></li>
                                <li><a href="{{ route('all.lineitems') }}">Line Item</a></li>
                                <li><a href="{{ route('all.companies') }}">Company</a></li>
                                <li><a href="{{ route('all.packings') }}">Packing</a></li>
                                <li><a href="{{ route('all.scales') }}">Scale</a></li>
                                <li><a href="{{ route('all.units') }}">Units</a></li> --}}
                            </ul>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i class="ti ti-device-laptop fs-16 me-2"></i><span>Role &
                                    Permissions</span><span class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="{{ route('roles.index') }}">Group Permissions</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Sales</h6>
                    <ul>
                        <li class="submenu">
                            <a href="javascript:void(0);"><i
                                    class="ti ti-layout-grid fs-16 me-2"></i><span>Sales</span><span
                                    class="menu-arrow"></span></a>
                            <ul>
                                <li><a href="{{ route('all_sales') }}">Sales</a></li>
                                <li><a href="">POS Orders</a></li>
                            </ul>
                        </li>
                        <li><a href=""><i
                                    class="ti ti-file-invoice fs-16 me-2"></i><span>Invoices</span></a></li>
                        <li><a href=""><i class="ti ti-receipt-refund fs-16 me-2"></i><span>Sales
                                    Return</span></a></li>
                    </ul>
                </li>
                <li class="submenu-open">
				  <h6 class="submenu-hdr">Peoples</h6>
					<ul>
					 <li><a href="{{ route('customers.index') }}"><i class="ti ti-users-group fs-16 me-2"></i><span>Customers</span></a></li>
					 <li><a href="{{ route('suppliers.index') }}"><i class="ti ti-user-dollar fs-16 me-2"></i><span>Suppliers</span></a></li>
					</ul>
				</li>
                <li class="submenu-open">
                    <h6 class="submenu-hdr">Branches</h6>
                    <ul>
                        <li><a href="{{ route('all.branches') }}">
                                <i class="ti ti-stack-3 fs-16 me-2"></i>
                                <span>Branches</span></a>
                        </li>
                    </ul>
                </li>
                {{-- <li class="submenu-open">
                    <h6 class="submenu-hdr">System Users</h6>
                    <ul>
                        <li>
                            <a href="{{ route('all.users') }}"><i
                                    class="ti ti-stack-pop fs-16 me-2"></i><span>Users</span></a>
                        </li>
                        <li>
                            <a href="{{ route('all.employees') }}"><i
                                    class="ti ti-stack-pop fs-16 me-2"></i><span>Employees</span></a>
                        </li>
                    </ul>
                </li> --}}

                <li class="submenu-open">
                    <h6 class="submenu-hdr">Purchases</h6>
                    <ul>
                        <li><a href="{{ route('all_purchases') }}"><i class="ti ti-shopping-bag fs-16 me-2"></i><span>Purchases</span></a></li>
                        <li><a href=""><i class="ti ti-file-unknown fs-16 me-2"></i><span>Purchase Order</span></a></li>
                        <li><a href=""><i class="ti ti-file-upload fs-16 me-2"></i><span>Purchase Return</span></a></li>
                    </ul>
                </li>
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
