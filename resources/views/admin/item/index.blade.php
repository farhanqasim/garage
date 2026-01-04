@extends('layouts.app')
@section('title','All Items')
@section('content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h2 class="fw-bold">All Items</h2>
            </div>
        </div>
        <ul class="table-top-head">

            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf">
                    <img src="{{ asset('assets/img/icons/pdf.svg') }}" alt="img">
                </a>
            </li>

            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel">
                    <img src="{{ asset('assets/img/icons/excel.svg') }}" alt="img">
                </a>
            </li>

            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh">
                    <i class="ti ti-refresh"></i>
                </a>
            </li>

            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header">
                    <i class="ti ti-chevron-up"></i>
                </a>
            </li>

        </ul>
        <div class="page-btn">
            <a href="{{ route('all.items.create') }}" class="btn btn-primary me-2">
                <i class="ti ti-circle-plus me-1"></i>Add Items
            </a>
        </div>
    </div>
    <!-- /Product List -->
    <div class="card">
        <div class="card-header">
            <!-- Type Filter Tabs -->
            <div class="row mb-4 g-3" id="typeTabsContainer">
                <div class="col-md-3 col-6">
                    <div class="type-tab text-center p-3 cursor-pointer" data-type="all" id="tab-all">
                        <i class="ti ti-list fs-2 d-block mb-2"></i>
                        <span>All Items</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="type-tab text-center p-3 cursor-pointer" data-type="parts" id="tab-parts">
                        <i class="ti ti-tool fs-2 d-block mb-2"></i>
                        <span>Parts</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="type-tab text-center p-3 cursor-pointer" data-type="filters" id="tab-filters">
                        <i class="ti ti-filter fs-2 d-block mb-2"></i>
                        <span>Filters</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="type-tab text-center p-3 cursor-pointer" data-type="breakpad" id="tab-breakpad">
                        <i class="ti ti-disc fs-2 d-block mb-2"></i>
                        <span>Break Pad</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="type-tab text-center p-3 cursor-pointer" data-type="oil" id="tab-oil">
                        <i class="ti ti-droplet fs-2 d-block mb-2"></i>
                        <span>Oil</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="type-tab text-center p-3 cursor-pointer" data-type="battery" id="tab-battery">
                        <i class="ti ti-battery fs-2 d-block mb-2"></i>
                        <span>Battery</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="type-tab text-center p-3 cursor-pointer" data-type="scrap" id="tab-scrap">
                        <i class="ti ti-trash fs-2 d-block mb-2"></i>
                        <span>Scrap</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="type-tab text-center p-3 cursor-pointer" data-type="services" id="tab-services">
                        <i class="ti ti-tools fs-2 d-block mb-2"></i>
                        <span>Services</span>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-end flex-wrap row-gap-3">
                <div class="d-flex justify-content-end mb-3">
                    <input type="text" id="tableSearch" class="form-control w-100" placeholder="Search...">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" >
                <form id="bulkDeleteForm" method="POST" action="{{ route('all.items.bulkDelete') }}" style="height: 80vh;">
                    @csrf
                    @method('DELETE')
                    <table id="searchableTable" class="table table-hover table-center" >
                        <thead class="thead-primary">
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" class="form-check" style="width: 20px; height:20px">
                                </th>
                                <th>Product Image</th>
                                <th>Actions</th>
                                <th>Update History</th>
                                <th>Part Number</th>
                                <th>User Name</th>
                                <th>Product Name</th>
                                <th>Product Type</th>
                                <th>Bar Code</th>
                                <th>Is Active</th>
                                <th>Category</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            @forelse ($items as $item)
                            <tr data-type="{{ $item->type }}">
                                <td>
                                    <input type="checkbox" name="ids[]" value="{{ $item->id }}" style="width: 20px; height:20px"   class="item-checkbox form-check">
                                </td>
                                <td>
                                    <img src="{{ asset($item->image ?? 'assets/img/media/default.png') }}"
                                        width="70" height="70" class="rounded item-image"
                                        style="cursor:pointer;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#imageModal"
                                        data-src="{{ asset($item->image ?? 'assets/img/media/default.png') }}">
                                </td>
                                <td class="no-highlight">
                                    <div class="dropdown">
                                        <button class="btn btn-primary  dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item mt-3" href="{{ route('item.show',$item->id) }}">
                                                    <i data-feather="eye" class="me-1"></i> View
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item mt-2" href="{{ route('item.edit',$item->id) }}">
                                                    <i data-feather="edit" class="me-1"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0)"
                                                    onclick="confirmDelete('delete-form-{{ $item->id }}')"
                                                    class="dropdown-item mt-2">
                                                    <i data-feather="trash-2" class="feather-trash-2"></i>  Delete
                                                </a>
                                            </li>
                                            <hr>
                                            <li>
                                                <a class="dropdown-item text-primary" href="{{ route('item.duplicate', $item->id) }}">
                                                    <i data-feather="copy" class="me-1"></i> Duplicate
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                                <td>
                                    @if($item->updated_by_user)
                                        <div class="small">
                                            <div> {{ $item->updated_by_user->name ?? 'N/A' }}</div>
                                            @if($item->last_updated_at)
                                                <div> {{ $item->last_updated_at->format('d M Y, h:i A') }}</div>
                                            @elseif($item->updated_at)
                                                <div> {{ $item->updated_at->format('d M Y, h:i A') }}</div>
                                            @endif
                                        </div>
                                    @elseif($item->updated_at)
                                        <div class="small">
                                            <div> {{ $item->updated_at->format('d M Y, h:i A') }}</div>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $item->partnumber_item->name??'-' }}</td>
                                <td>{{ $item->item_user->name??'' }}</td>
                                <td>{{ $item->product_item->name??'' }}</td>
                                <td><span class="badge bg-info">{{ ucfirst($item->type) }}</span></td>
                                <td><span class="badge bg-secondary">{{ $item->bar_code }}</span><br> <br>
                                  @if($item->barcode_image)
                                  <img src="{{ asset($item->barcode_image)}}" alt="" />
                                  @endif
                                </td>
                                <td>
                                    <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ $item->category ? $item->category->name : 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center">No items found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </form>
                <!-- All delete forms container - will be populated dynamically -->
                <div id="deleteFormsContainer" style="display: none;">
                    @foreach ($items as $item)
                    <form id="delete-form-{{ $item->id }}"
                        action="{{ route('item.delete', $item->id) }}"
                        method="POST">
                        @csrf
                        @method('DELETE')
                    </form>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="button" id="bulkDeleteBtn" class="btn btn-danger">
                <i class="ti ti-trash me-1"></i>
            </button>
            {{-- {{ $items->links() }} --}}
            <a href="{{ route('items.recycle.bin') }}" class="btn btn-primary">
                <i class="ti ti-trash me-1"></i> Recycle Bin
            </a>
        </div>
    </div>
</div>
<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body p-0">
        <img src="" id="modalImage" class="" style="width: 100%; height:700px" alt="Item Image">
      </div>
      <div class="modal-footer p-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- Styles -->
<style>
    .type-tab {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
        background: #fff;
    }
    .type-tab:hover {
        border-color: #fe9f43;
        background: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .type-tab.active {
        border-color: #fe9f43;
        background: #fe9f43;
        color: #fff;
    }
    .type-tab.active i {
        color: #fff;
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>

<!-- Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let currentType = 'all'; // Track current selected type
        
        // Initialize: Set 'All Items' as active by default
        document.getElementById('tab-all').classList.add('active');
        
        // Type Tab Click Handler
        document.querySelectorAll('.type-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const type = this.getAttribute('data-type');
                currentType = type;
                
                // Remove active class from all tabs
                document.querySelectorAll('.type-tab').forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Load items for selected type
                loadItemsByType(type);
            });
        });
        
        // Function to load items by type
        function loadItemsByType(type) {
            const tbody = document.getElementById('itemsTableBody');
            const deleteFormsContainer = document.getElementById('deleteFormsContainer');
            
            // Show loading
            tbody.innerHTML = '<tr><td colspan="11" class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
            
            // Build URL
            let url = type === 'all' 
                ? '{{ route("all.items") }}' 
                : '{{ route("items.by.type", ":type") }}'.replace(':type', type) + '?all=true';
            
            // For 'all', reload the page or fetch all items
            if (type === 'all') {
                fetch('{{ route("all.items") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (response.redirected) {
                        window.location.href = response.url;
                        return;
                    }
                    return response.json();
                })
                .then(data => {
                    if (data) {
                        renderItems(data.items || []);
                    } else {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    location.reload();
                });
            } else {
                // Fetch items by type via AJAX
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.items) {
                        renderItems(data.items);
                    } else {
                        tbody.innerHTML = '<tr><td colspan="11" class="text-center">No items found for this type.</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error loading items:', error);
                    tbody.innerHTML = '<tr><td colspan="11" class="text-center text-danger">Error loading items. Please try again.</td></tr>';
                });
            }
        }
        
        // Function to render items in table
        function renderItems(items) {
            const tbody = document.getElementById('itemsTableBody');
            const deleteFormsContainer = document.getElementById('deleteFormsContainer');
            
            if (items.length === 0) {
                tbody.innerHTML = '<tr><td colspan="11" class="text-center">No items found.</td></tr>';
                deleteFormsContainer.innerHTML = '';
                updateCheckboxes();
                return;
            }
            
            let tbodyHtml = '';
            let deleteFormsHtml = '';
            
            items.forEach(item => {
                // Fix image path
                let imgSrc = item.image || '/assets/img/media/default.png';
                if (!imgSrc.startsWith('http') && !imgSrc.startsWith('/')) {
                    imgSrc = '/' + imgSrc;
                }
                
                tbodyHtml += `
                    <tr data-type="${item.type}">
                        <td>
                            <input type="checkbox" name="ids[]" value="${item.id}" style="width: 20px; height:20px" class="item-checkbox form-check">
                        </td>
                        <td>
                            <img src="${imgSrc}"
                                width="70" height="70" class="rounded item-image"
                                style="cursor:pointer;"
                                data-bs-toggle="modal"
                                data-bs-target="#imageModal"
                                data-src="${imgSrc}">
                        </td>
                        <td class="no-highlight">
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item mt-3" href="${item.show_url || '#'}">
                                            <i data-feather="eye" class="me-1"></i> View
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item mt-2" href="${item.edit_url || '#'}">
                                            <i data-feather="edit" class="me-1"></i> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)"
                                            onclick="confirmDelete('delete-form-${item.id}')"
                                            class="dropdown-item mt-2">
                                            <i data-feather="trash-2" class="feather-trash-2"></i> Delete
                                        </a>
                                    </li>
                                    <hr>
                                    <li>
                                        <a class="dropdown-item text-primary" href="${item.duplicate_url || '#'}">
                                            <i data-feather="copy" class="me-1"></i> Duplicate
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                        <td>
                            ${item.updated_by_user ? `
                                <div class="small">
                                    <div>${item.updated_by_user.name || 'N/A'}</div>
                                    <div>${item.last_updated_at || item.updated_at || '-'}</div>
                                </div>
                            ` : (item.updated_at ? `<div class="small"><div>${item.updated_at}</div></div>` : '<span class="text-muted">-</span>')}
                        </td>
                        <td>${item.part_number || '-'}</td>
                        <td>${item.user_name || '-'}</td>
                        <td>${item.product_name || '-'}</td>
                        <td><span class="badge bg-info">${item.type ? item.type.charAt(0).toUpperCase() + item.type.slice(1) : '-'}</span></td>
                        <td>
                            <span class="badge bg-secondary">${item.bar_code || '-'}</span><br><br>
                            ${item.barcode_image ? `<img src="/${item.barcode_image}" alt="" />` : ''}
                        </td>
                        <td>
                            <span class="badge ${item.is_active ? 'bg-success' : 'bg-danger'}">
                                ${item.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td>${item.category_name || 'N/A'}</td>
                    </tr>
                `;
                
                deleteFormsHtml += `
                    <form id="delete-form-${item.id}"
                        action="${item.delete_url || '#'}"
                        method="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="DELETE">
                    </form>
                `;
            });
            
            tbody.innerHTML = tbodyHtml;
            deleteFormsContainer.innerHTML = deleteFormsHtml;
            
            // Re-initialize feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
            
            // Re-attach image click handlers
            attachImageHandlers();
            
            // Update checkboxes
            updateCheckboxes();
        }
        
        // Function to update checkboxes after rendering
        function updateCheckboxes() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.item-checkbox');
            
            // Remove old event listeners by cloning and replacing
            const newSelectAll = selectAll.cloneNode(true);
            selectAll.parentNode.replaceChild(newSelectAll, selectAll);
            
            newSelectAll.addEventListener('change', function() {
                checkboxes.forEach(chk => chk.checked = newSelectAll.checked);
            });
        }
        
        // Function to attach image click handlers
        function attachImageHandlers() {
            document.querySelectorAll('.item-image').forEach(img => {
                img.addEventListener('click', function() {
                    const src = this.getAttribute('data-src');
                    const modalImage = document.getElementById('modalImage');
                    if (modalImage) {
                        modalImage.src = src;
                    }
                });
            });
        }
        
        // Bulk Delete with SweetAlert
        document.getElementById('bulkDeleteBtn').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            const selected = Array.from(checkboxes).filter(chk => chk.checked);
            if (selected.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Selection',
                    text: 'Please select at least one item.'
                });
                return;
            }
            
            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete ${selected.length} item(s). This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete them!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    playDeleteSound();
                    document.getElementById('bulkDeleteForm').submit();
                }
            });
        });
        
        // Initial image handlers
        attachImageHandlers();
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalImage = document.getElementById('modalImage');

    document.querySelectorAll('.item-image').forEach(img => {
        img.addEventListener('click', function() {
            const src = this.getAttribute('data-src');
            modalImage.src = src;
        });
    });

    // Optional: clear modal image on close
    const imageModal = document.getElementById('imageModal');
    imageModal.addEventListener('hidden.bs.modal', function () {
        modalImage.src = '';
    });
});
</script>




@endsection
