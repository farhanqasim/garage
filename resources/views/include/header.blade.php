<div class="header">
      <div class="main-header">
        <!-- Logo -->
        <div class="header-left active">
          <a href="{{ route('home') }}" class="logo logo-normal">
            <img style="width: 60px;" src="{{ setting_value('logo', asset('assets/img/elite-car-wash-logo.png')) }}" alt="Elite Car Wash">
            <h3>{{ setting_value('logo_text', 'Elite Car Wash') }}</h3>
          </a>
          <a href="{{ route('home') }}" class="logo logo-white">
            <img style="width: 60px;" src="{{ setting_value('logo', asset('assets/img/elite-car-wash-logo.png')) }}" alt="Elite Car Wash">
            <h3>{{ setting_value('logo_text', 'Elite Car Wash') }}</h3>
          </a>
          <a href="{{ route('home') }}" class="logo-small">
            <img style="width: 60px;" src="{{ setting_value('logo', asset('assets/img/elite-car-wash-logo.png')) }}" alt="Elite Car Wash">
            <h3>{{ setting_value('logo_text', 'Elite Car Wash') }}</h3>
          </a>
        </div>
        <!-- /Logo -->
        <a id="mobile_btn" class="mobile_btn" href="#sidebar">
          <span class="bar-icon">
            <span></span>
            <span></span>
            <span></span>
          </span>
        </a>

        <!-- Header Menu -->
        <ul class="nav user-menu">

          <!-- Search -->
          <li class="nav-item nav-searchinputs">
            <div class="top-nav-search">
              <a href="javascript:void(0);" class="responsive-search">
                <i class="fa fa-search"></i>
              </a>
              <form action="#" class="dropdown">
                <div class="searchinputs input-group dropdown-toggle" id="dropdownMenuClickable" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                  <input type="text" placeholder="Search">
                  <div class="search-addon">
                    <span><i class="ti ti-search"></i></span>
                  </div>
                  <span class="input-group-text">
                    <kbd class="d-flex align-items-center"><img src="{{asset('assets/img/icons/command.svg')}}" alt="img" class="me-1">K</kbd>
                  </span>
                </div>
                <div class="dropdown-menu search-dropdown" aria-labelledby="dropdownMenuClickable">
                  <div class="search-info">
                    <h6><span><i data-feather="search" class="feather-16"></i></span>Recent Searches
                    </h6>
                    <ul class="search-tags">
                      <li><a href="javascript:void(0);">Products</a></li>
                      <li><a href="javascript:void(0);">Sales</a></li>
                      <li><a href="javascript:void(0);">Applications</a></li>
                    </ul>
                  </div>
                  <div class="search-info">
                    <h6><span><i data-feather="help-circle" class="feather-16"></i></span>Help</h6>
                    <p>How to Change Product Volume from 0 to 200 on Inventory management</p>
                    <p>Change Product Name</p>
                  </div>
                  <div class="search-info">
                    <h6><span><i data-feather="user" class="feather-16"></i></span>Customers</h6>
                    <ul class="customers">
                      <li><a href="javascript:void(0);">Aron Varu<img src="{{asset('assets/img/profiles/avator1.jpg')}}" alt="Img" class="img-fluid"></a></li>
                      <li><a href="javascript:void(0);">Jonita<img src="{{asset('assets/img/profiles/avatar-01.jpg')}}" alt="Img" class="img-fluid"></a></li>
                      <li><a href="javascript:void(0);">Aaron<img src="{{asset('assets/img/profiles/avatar-10.jpg')}}" alt="Img" class="img-fluid"></a></li>
                    </ul>
                  </div>
                </div>
              </form>
            </div>
          </li>
          <!-- /Search -->

          <!-- Select Store -->
          {{-- <li class="nav-item dropdown has-arrow main-drop select-store-dropdown">
            <a href="javascript:void(0);" class="dropdown-toggle nav-link select-store" data-bs-toggle="dropdown">
              <span class="user-info">
                <span class="user-letter">
                  <img src="{{asset('assets/img/store/store-01.png')}}" alt="Store Logo" class="img-fluid">
                </span>
                <span class="user-detail">
                  <span class="user-name">Freshmart</span>
                </span>
              </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
              <a href="javascript:void(0);" class="dropdown-item">
                <img src="{{asset('assets/img/store/store-01.png')}}" alt="Store Logo" class="img-fluid">Freshmart
              </a>
              <a href="javascript:void(0);" class="dropdown-item">
                <img src="{{asset('assets/img/store/store-02.png')}}" alt="Store Logo" class="img-fluid">Grocery Apex
              </a>
              <a href="javascript:void(0);" class="dropdown-item">
                <img src="{{asset('assets/img/store/store-03.png')}}" alt="Store Logo" class="img-fluid">Grocery Bevy
              </a>
              <a href="javascript:void(0);" class="dropdown-item">
                <img src="{{asset('assets/img/store/store-04.png')}}" alt="Store Logo" class="img-fluid">Grocery Eden
              </a>
            </div>
          </li> --}}
          <!-- /Select Store -->

          {{-- <li class="nav-item dropdown link-nav">
            <a href="javascript:void(0);" class="btn btn-primary btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
              <i class="ti ti-circle-plus me-1"></i>Add New
            </a>
            <div class="dropdown-menu dropdown-xl dropdown-menu-center">
              <div class="row g-2">
                <div class="col-md-2">
                  <a href="category-list.html" class="link-item">
                    <span class="link-icon">
                      <i class="ti ti-brand-codepen"></i>
                    </span>
                    <p>Category</p>
                  </a>
                </div>
                <div class="col-md-2">
                  <a href="add-product.html" class="link-item">
                    <span class="link-icon">
                      <i class="ti ti-square-plus"></i>
                    </span>
                    <p>Product</p>
                  </a>
                </div>
                <div class="col-md-2">
                  <a href="category-list.html" class="link-item">
                    <span class="link-icon">
                      <i class="ti ti-shopping-bag"></i>
                    </span>
                    <p>Purchase</p>
                  </a>
                </div>
                <div class="col-md-2">
                  <a href="online-orders.html" class="link-item">
                    <span class="link-icon">
                      <i class="ti ti-shopping-cart"></i>
                    </span>
                    <p>Sale</p>
                  </a>
                </div>
                <div class="col-md-2">
                  <a href="expense-list.html" class="link-item">
                    <span class="link-icon">
                      <i class="ti ti-file-text"></i>
                    </span>
                    <p>Expense</p>
                  </a>
                </div>
                <div class="col-md-2">
                  <a href="quotation-list.html" class="link-item">
                    <span class="link-icon">
                      <i class="ti ti-device-floppy"></i>
                    </span>
                    <p>Quotation</p>
                  </a>
                </div>
                <div class="col-md-2">
                  <a href="sales-returns.html" class="link-item">
                    <span class="link-icon">
                      <i class="ti ti-copy"></i>
                    </span>
                    <p>Return</p>
                  </a>
                </div>
                <div class="col-md-2">
                  <a href="users.html" class="link-item">
                    <span class="link-icon">
                      <i class="ti ti-user"></i>
                    </span>
                    <p>User</p>
                  </a>
                </div>
                <div class="col-md-2">
                  <a href="customers.html" class="link-item">
                    <span class="link-icon">
                      <i class="ti ti-users"></i>
                    </span>
                    <p>Customer</p>
                  </a>
                </div>
                <div class="col-md-2">
                  <a href="sales-report.html" class="link-item">
                    <span class="link-icon">
                      <i class="ti ti-shield"></i>
                    </span>
                    <p>Biller</p>
                  </a>
                </div>
                <div class="col-md-2">
                  <a href="suppliers.html" class="link-item">
                    <span class="link-icon">
                      <i class="ti ti-user-check"></i>
                    </span>
                    <p>Supplier</p>
                  </a>
                </div>
                <div class="col-md-2">
                  <a href="stock-transfer.html" class="link-item">
                    <span class="link-icon">
                      <i class="ti ti-truck"></i>
                    </span>
                    <p>Transfer</p>
                  </a>
                </div>
              </div>
            </div>
          </li> --}}

          <li class="nav-item pos-nav">
            <a href="{{ route('point_of_sale') }}" class="btn btn-dark btn-md d-inline-flex align-items-center">
              <i class="ti ti-device-laptop me-1"></i>POS
            </a>
          </li>

          <!-- Branch Switcher (Admin Only) -->
          @auth
            @if(auth()->user()->role === 'admin')
              @php
                $allBranches = \App\Models\Branch::where('status', 'active')->orderBy('branch_name', 'asc')->get();
                $currentBranchId = session('selected_branch_id');
                $currentBranch = $currentBranchId ? \App\Models\Branch::find($currentBranchId) : null;
              @endphp
              <li class="nav-item dropdown has-arrow main-drop branch-switcher-dropdown">
                <a href="javascript:void(0);" class="dropdown-toggle nav-link" data-bs-toggle="dropdown" aria-expanded="false">
                  <span class="user-info">
                    <span class="user-letter">
                      <i class="ti ti-building-store"></i>
                    </span>
                    <span class="user-detail">
                      <span class="user-name">
                        @if($currentBranch)
                          {{ $currentBranch->branch_name }}
                          @if($currentBranch->branch_code)
                            ({{ $currentBranch->branch_code }})
                          @endif
                        @else
                          All Branches
                        @endif
                      </span>
                    </span>
                  </span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                  <div class="dropdown-header">
                    <h6 class="mb-0">Switch Branch</h6>
                    <p class="text-muted small mb-0">Select a branch to work with</p>
                  </div>
                  <div class="dropdown-divider"></div>
                  <a href="javascript:void(0);" class="dropdown-item branch-switch-item {{ !$currentBranchId ? 'active' : '' }}" 
                     onclick="switchBranch(null, 'All Branches')">
                    <i class="ti ti-world me-2"></i>
                    <span>All Branches</span>
                    @if(!$currentBranchId)
                      <i class="ti ti-check ms-auto text-success"></i>
                    @endif
                  </a>
                  @foreach($allBranches as $branch)
                    <a href="javascript:void(0);" class="dropdown-item branch-switch-item {{ $currentBranchId == $branch->id ? 'active' : '' }}" 
                       onclick="switchBranch({{ $branch->id }}, '{{ $branch->branch_name }}')">
                      <i class="ti ti-building-store me-2"></i>
                      <span>{{ $branch->branch_name }}</span>
                      @if($branch->branch_code)
                        <small class="text-muted ms-2">({{ $branch->branch_code }})</small>
                      @endif
                      @if($currentBranchId == $branch->id)
                        <i class="ti ti-check ms-auto text-success"></i>
                      @endif
                    </a>
                  @endforeach
                </div>
              </li>
            @endif
          @endauth
          <!-- /Branch Switcher -->

          <!-- Flag -->
          {{-- <li class="nav-item dropdown has-arrow flag-nav nav-item-box">
            <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="javascript:void(0);" role="button">
              <img src="{{asset('assets/img/flags/us-flag.svg')}}" alt="Language" class="img-fluid">
            </a>
            <div class="dropdown-menu dropdown-menu-right">
              <a href="javascript:void(0);" class="dropdown-item">
                <img src="{{asset('assets/img/flags/english.svg')}}" alt="Img" height="16">English
              </a>
              <a href="javascript:void(0);" class="dropdown-item">
                <img src="{{asset('assets/img/flags/arabic.svg')}}" alt="Img" height="16">Arabic
              </a>
            </div>
          </li> --}}
          <!-- /Flag -->

          <li class="nav-item nav-item-box">
            <a href="javascript:void(0);" id="btnFullscreen">
              <i class="ti ti-maximize"></i>
            </a>
          </li>
          {{-- <li class="nav-item nav-item-box">
            <a href="email.html">
              <i class="ti ti-mail"></i>
              <span class="badge rounded-pill">1</span>
            </a>
          </li> --}}
          <!-- Notifications -->
          <li class="nav-item dropdown nav-item-box">
            <a href="javascript:void(0);" class="dropdown-toggle nav-link" data-bs-toggle="dropdown">
              <i class="ti ti-bell"></i>
            </a>
            <div class="dropdown-menu notifications">
              <div class="topnav-dropdown-header">
                <h5 class="notification-title">Notifications</h5>
                <a href="javascript:void(0)" class="clear-noti">Mark all as read</a>
              </div>
              <div class="noti-content">
                <ul class="notification-list">
                  <li class="notification-message">
                    <a href="activities.html">
                      <div class="media d-flex">
                        <span class="avatar flex-shrink-0">
                          <img alt="Img" src="{{asset('assets/img/profiles/avatar-13.jpg')}}">
                        </span>
                        <div class="flex-grow-1">
                          <p class="noti-details"><span class="noti-title">James Kirwin</span> confirmed his order. Order No: #78901.Estimated delivery: 2 days
                          </p>
                          <p class="noti-time">4 mins ago</p>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="notification-message">
                    <a href="activities.html">
                      <div class="media d-flex">
                        <span class="avatar flex-shrink-0">
                          <img alt="Img" src="{{asset('assets/img/profiles/avatar-03.jpg')}}">
                        </span>
                        <div class="flex-grow-1">
                          <p class="noti-details"><span class="noti-title">Leo Kelly</span> cancelled his order scheduled for 17 Jan 2025</p>
                          <p class="noti-time">10 mins ago</p>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="notification-message">
                    <a href="activities.html" class="recent-msg">
                      <div class="media d-flex">
                        <span class="avatar flex-shrink-0">
                          <img alt="Img" src="{{asset('assets/img/profiles/avatar-17.jpg')}}">
                        </span>
                        <div class="flex-grow-1">
                          <p class="noti-details">Payment of $50 received for Order #67890 from <span class="noti-title">Antonio Engle</span></p>
                          <p class="noti-time">05 mins ago</p>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="notification-message">
                    <a href="activities.html" class="recent-msg">
                      <div class="media d-flex">
                        <span class="avatar flex-shrink-0">
                          <img alt="Img" src="{{asset('assets/img/profiles/avatar-02.jpg')}}">
                        </span>
                        <div class="flex-grow-1">
                          <p class="noti-details"><span class="noti-title">Andrea</span> confirmed his order. Order No: #73401.Estimated delivery: 3 days</p>
                          <p class="noti-time">4 mins ago</p>
                        </div>
                      </div>
                    </a>
                  </li>
                </ul>
              </div>
              <div class="topnav-dropdown-footer d-flex align-items-center gap-3">
                <a href="#" class="btn btn-secondary btn-md w-100">Cancel</a>
                <a href="activities.html" class="btn btn-primary btn-md w-100">View all</a>
              </div>
            </div>
          </li>
          <!-- /Notifications -->

          <li class="nav-item nav-item-box">
            <a href="{{ route('admin.setting') }}"><i class="ti ti-settings"></i></a>
          </li>
          <li class="nav-item dropdown has-arrow main-drop profile-nav">
            <a href="javascript:void(0);" class="nav-link userset" data-bs-toggle="dropdown">
              <span class="user-info p-0">
                <span class="user-letter">
                  <img src="{{asset(auth()->user()->profile_img??'assets/img/profiles/avator1.jpg')}}" alt="Img" class="img-fluid">
                </span>
              </span>
            </a>
            <div class="dropdown-menu menu-drop-user">
              <div class="profileset d-flex align-items-center">
                <span class="user-img me-2">
                  <img src="{{asset(auth()->user()->profile_img)}}" alt="Img">
                </span>
                <div>
                  <h6 class="fw-medium">{{ auth()->user()->name?? "Guest" }}</h6>
                  <p>{{ auth()->user()->role??"guest" }}</p>
                </div>
              </div>
              <a class="dropdown-item" href="{{route('user.profile',auth()->user()->id)}}"><i class="ti ti-user-circle me-2"></i>MyProfile</a>
              {{-- <a class="dropdown-item" href="sales-report.html"><i class="ti ti-file-text me-2"></i>Reports</a> --}}
              <a class="dropdown-item" href="{{ route('admin.setting') }}"><i class="ti ti-settings-2 me-2"></i>Settings</a>
              <hr class="my-2">
              <a class="dropdown-item logout pb-0" href="{{ route('logout') }}" onclick="event.preventDefault();
                document.getElementById('logout-form').submit();">
                <i class="ti ti-logout me-2"></i>Logout</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
          </li>
        </ul>
        <!-- /Header Menu -->

        <!-- Mobile Menu -->
        <div class="dropdown mobile-user-menu">
          <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
          <div class="dropdown-menu dropdown-menu-right">
            <a class="dropdown-item" href="{{route('user.profile',auth()->user()->id)}}">My Profile</a>
            <a class="dropdown-item" href="{{ route('admin.setting') }}">Settings</a>
            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                document.getElementById('logout-form').submit();">Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
          </div>
        </div>
        <!-- /Mobile Menu -->
      </div>
    </div>

    @auth
      @if(auth()->user()->role === 'admin')
        <script>
          function switchBranch(branchId, branchName) {
            console.log('=== Branch Switch Started ===');
            console.log('Branch ID:', branchId);
            console.log('Branch Name:', branchName);
            
            // Show loading
            const dropdown = document.querySelector('.branch-switcher-dropdown .dropdown-toggle');
            const originalContent = dropdown ? dropdown.innerHTML : '';

            // Get CSRF token from meta tag or form
            let csrfToken = '{{ csrf_token() }}';
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            if (metaToken) {
              csrfToken = metaToken.getAttribute('content');
            }
            console.log('CSRF Token found:', csrfToken ? 'Yes' : 'No');

            // Get route URL
            const routeUrl = '{{ route("branch.switch") }}';
            console.log('Route URL:', routeUrl);

            // Prepare data - use simple form data
            const formData = new FormData();
            if (branchId !== null && branchId !== 'null' && branchId !== '') {
              formData.append('branch_id', branchId);
            }
            formData.append('_token', csrfToken);
            
            console.log('FormData prepared, branch_id:', branchId);

            // Make AJAX request
            fetch(routeUrl, {
              method: 'POST',
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
              },
              body: formData,
              credentials: 'same-origin'
            })
            .then(response => {
              console.log('Response received, status:', response.status);
              console.log('Response Content-Type:', response.headers.get('content-type'));
              
              // Check if response is OK
              if (!response.ok) {
                console.error('Response not OK, status:', response.status);
                return response.text().then(text => {
                  console.error('Error response text:', text.substring(0, 500));
                  // Try to parse as JSON first
                  try {
                    const jsonData = JSON.parse(text);
                    throw new Error(jsonData.message || 'Server returned error: ' + response.status);
                  } catch (e) {
                    // If not JSON, it's probably an HTML error page
                    throw new Error('Server error (Status: ' + response.status + '). Check Laravel logs for details.');
                  }
                });
              }
              
              // Try to get response text first
              return response.text().then(text => {
                console.log('Response text length:', text.length);
                console.log('Response text preview:', text.substring(0, 200));
                
                // Check if it's HTML (error page)
                if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')) {
                  console.error('Received HTML instead of JSON - likely an error page');
                  throw new Error('Server returned HTML error page. Check Laravel logs.');
                }
                
                try {
                  const jsonData = JSON.parse(text);
                  return jsonData;
                } catch (e) {
                  console.error('Failed to parse JSON:', e);
                  console.error('Response text was:', text.substring(0, 500));
                  throw new Error('Invalid JSON response. Server may have returned an error page.');
                }
              });
            })
            .then(data => {
              console.log('Parsed response data:', data);
              
              if (data.success) {
                console.log('Branch switch successful');
                
                // Update UI
                const userDetail = document.querySelector('.branch-switcher-dropdown .user-name');
                if (userDetail) {
                  userDetail.textContent = branchName;
                }

                // Update active state
                document.querySelectorAll('.branch-switcher-dropdown .branch-switch-item').forEach(item => {
                  item.classList.remove('active');
                  const checkIcon = item.querySelector('.ti-check');
                  if (checkIcon) {
                    checkIcon.remove();
                  }
                });

                // Mark selected item as active
                let selectedItem;
                if (branchId === null || branchId === 'null' || !branchId) {
                  selectedItem = document.querySelector('.branch-switcher-dropdown .branch-switch-item:first-child');
                } else {
                  selectedItem = document.querySelector(`.branch-switcher-dropdown .branch-switch-item[onclick*="${branchId}"]`);
                }
                
                if (selectedItem) {
                  selectedItem.classList.add('active');
                  const checkIcon = document.createElement('i');
                  checkIcon.className = 'ti ti-check ms-auto text-success';
                  selectedItem.appendChild(checkIcon);
                }

                // Show success message
                if (typeof Swal !== 'undefined') {
                  Swal.fire({
                    icon: 'success',
                    title: 'Branch Switched',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                  });
                } else {
                  alert(data.message);
                }

                // Reload page after a short delay to reflect changes
                setTimeout(() => {
                  window.location.reload();
                }, 500);
              } else {
                const errorMsg = data.message || 'Failed to switch branch';
                console.error('Switch failed:', errorMsg, data);
                if (typeof Swal !== 'undefined') {
                  Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                  });
                } else {
                  alert(errorMsg);
                }
              }
            })
            .catch(error => {
              console.error('=== Error Details ===');
              console.error('Error name:', error.name);
              console.error('Error message:', error.message);
              console.error('Error stack:', error.stack);
              
              const errorMsg = error.message || 'Failed to switch branch. Please try again.';
              console.error('Final error message:', errorMsg);
              
              if (typeof Swal !== 'undefined') {
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: errorMsg,
                  footer: 'Check browser console (F12) for details'
                });
              } else {
                alert(errorMsg + '\n\nCheck browser console (F12) for details');
              }
            });
          }
        </script>
      @endif
    @endauth
