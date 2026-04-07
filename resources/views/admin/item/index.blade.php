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
            @canany(['add_items', 'add_parts', 'add_filters', 'add_break_pad', 'add_oil', 'add_battery', 'add_scrap', 'add_services'])
            <a href="{{ route('all.items.create.new') }}" class="btn btn-primary me-2">
                <i class="ti ti-circle-plus me-1"></i>Add Items
            </a>
            @endcanany
        </div>
    </div>
    <!-- /Product List -->
    <div class="card">
        <div class="card-header">
            <!-- Type Filter Dropdown -->
            <div class="row mb-4 g-3 align-items-end">
                <div class="col-md-3">
                    <label for="typeFilterDropdown" class="form-label fw-bold mb-2">Filter by Type:</label>
                    <select id="typeFilterDropdown" class="form-control form-select">
                        <option value="all">All Items</option>
                        @canany(['view_items', 'view_parts'])<option value="parts">Parts</option>@endcanany
                        @canany(['view_items', 'view_filters'])<option value="filters">Filters</option>@endcanany
                        @canany(['view_items', 'view_break_pad'])<option value="breakpad">Break Pad</option>@endcanany
                        @canany(['view_items', 'view_oil'])<option value="oil">Oil</option>@endcanany
                        @canany(['view_items', 'view_battery'])<option value="battery">Battery</option>@endcanany
                        @canany(['view_items', 'view_scrap'])<option value="scrap">Scrap</option>@endcanany
                        @canany(['view_items', 'view_services'])<option value="services">Services</option>@endcanany
                    </select>
                </div>
                <div class="col-md-3 col-auto">
                    <a href="{{ route('items.price.list') }}" id="priceListBtn" class="btn btn-primary" target="_blank" rel="noopener">
                        <i class="ti ti-list me-1"></i>Price List
                    </a>
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
                                    @canany(['delete_items', 'delete_parts', 'delete_filters', 'delete_break_pad', 'delete_oil', 'delete_battery', 'delete_scrap', 'delete_services'])
                                    <input type="checkbox" id="selectAll" class="form-check" style="width: 20px; height:20px">
                                    @endcanany
                                </th>
                                <th>Product Image</th>
                                <th>Item Details</th>
                                <th>View</th>
                                <th>Actions</th>
                                <th>Serial Number</th>
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
                            @php
                                $t = $item->type ?? '';
                                $permMap = ['parts'=>'view_parts','filters'=>'view_filters','breakpad'=>'view_break_pad','oil'=>'view_oil','battery'=>'view_battery','scrap'=>'view_scrap','services'=>'view_services'];
                                $viewPerm = $permMap[$t] ?? 'view_items';
                                $permMapU = ['parts'=>'update_parts','filters'=>'update_filters','breakpad'=>'update_break_pad','oil'=>'update_oil','battery'=>'update_battery','scrap'=>'update_scrap','services'=>'update_services'];
                                $updatePerm = $permMapU[$t] ?? 'update_items';
                                $permMapD = ['parts'=>'delete_parts','filters'=>'delete_filters','breakpad'=>'delete_break_pad','oil'=>'delete_oil','battery'=>'delete_battery','scrap'=>'delete_scrap','services'=>'delete_services'];
                                $deletePerm = $permMapD[$t] ?? 'delete_items';
                                $permMapA = ['parts'=>'add_parts','filters'=>'add_filters','breakpad'=>'add_break_pad','oil'=>'add_oil','battery'=>'add_battery','scrap'=>'add_scrap','services'=>'add_services'];
                                $addPerm = $permMapA[$t] ?? 'add_items';
                            @endphp
                            <tr data-item-id="{{ $item->id }}" data-type="{{ $item->type }}">
                                <td>
                                    @can($deletePerm)
                                    <input type="checkbox" name="ids[]" value="{{ $item->id }}" style="width: 20px; height:20px"   class="item-checkbox form-check">
                                    @endcan
                                </td>
                                <td>
                                    @php
                                        $rawImg = $item->image ?? 'assets/img/media/default.png';
                                        $imgUrl = (str_starts_with($rawImg, 'http://') || str_starts_with($rawImg, 'https://')) ? $rawImg : asset($rawImg);
                                        $imgFallback = asset('assets/img/media/default.png');
                                    @endphp
                                    <img src="{{ $imgUrl }}"
                                        width="70" height="70" class="rounded item-image"
                                        style="cursor:pointer;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#imageModal"
                                        data-src="{{ $imgUrl }}"
                                        onerror="this.onerror=null;this.src='{{ $imgFallback }}';"
                                        data-fallback="{{ $imgFallback }}">
                                </td>
                                <td>
                                    <div class="small">
                                        @if(($item->type ?? '') === 'battery')
                                            @php
                                                $v = $item->volt_item ? trim((string)($item->volt_item->name ?? '')) : '';
                                                $voltDisplay = $v !== '' ? (preg_match('/\d*\s*V$/i', $v) ? $v : $v . 'V') : '-';
                                                $p = $item->plate_item ? trim((string)($item->plate_item->name ?? '')) : '';
                                                $plateDisplay = $p !== '' ? (preg_match('/\d*\s*PL$/i', $p) ? $p : $p . 'PL') : '-';
                                                $a = $item->amphors_item ? trim((string)($item->amphors_item->name ?? '')) : '';
                                                $ampDisplay = $a !== '' ? (preg_match('/\d*\s*AH$/i', $a) ? $a : $a . 'AH') : '-';
                                                $c = $item->cca_item ? trim((string)($item->cca_item->name ?? '')) : '';
                                                $ccaDisplay = $c !== '' ? (preg_match('/\d*\s*CCA$/i', $c) ? $c : $c . 'CCA') : '-';
                                            @endphp
                                            <div>{{ $item->company_item->name ?? '-' }}</div>
                                            <div class="item-list-primary-title">{{ $item->product_item->name ?? $item->partnumber_item->name ?? '-' }}</div>
                                            <div>{{ $voltDisplay }} {{ $plateDisplay }} {{ $ampDisplay }} {{ $ccaDisplay }}</div>
                                        @elseif(($item->type ?? '') === 'parts')
                                            <div class="item-list-primary-title">{{ $item->product_item->name ?? $item->partnumber_item->name ?? '-' }}</div>
                                            @if(trim((string)($item->product_item->name ?? '')) !== '')
                                            <div class="text-muted">{{ $item->product_item->name }}</div>
                                            @endif
                                            <div>{{ $item->category->name ?? '-' }}</div>
                                            <div>{{ $item->company_item->name ?? '-' }}</div>
                                            <div>{{ $item->quality_item->name ?? '-' }}</div>
                                        @elseif(in_array($item->type ?? '', ['filters', 'breakpad']))
                                            <div class="item-list-primary-title">{{ $item->product_item->name ?? $item->partnumber_item->name ?? '-' }}</div>
                                            @if(trim((string)($item->product_item->name ?? '')) !== '')
                                            <div class="text-muted">{{ $item->product_item->name }}</div>
                                            @endif
                                            <div>{{ $item->category->name ?? '-' }}</div>
                                            <div>{{ $item->company_item->name ?? '-' }}</div>
                                            <div>{{ $item->quality_item->name ?? '-' }}</div>
                                        @elseif(($item->type ?? '') === 'oil')
                                            <div class="item-list-primary-title">{{ $item->product_item->name ?? '-' }}</div>
                                            <div>{{ $item->category->name ?? '-' }}</div>
                                            <div>{{ $item->company_item->name ?? '-' }}</div>
                                            <div>{{ $item->quality_item->name ?? '-' }}</div>
                                        @elseif(($item->type ?? '') === 'scrap')
                                            <div class="item-list-primary-title">{{ $item->product_item->name ?? '-' }}</div>
                                            <div>{{ $item->category->name ?? '-' }}</div>
                                            <div>{{ $item->company_item->name ?? '-' }}</div>
                                            <div>{{ $item->level_item->name ?? '-' }}</div>
                                        @elseif(($item->type ?? '') === 'services')
                                            <div class="item-list-primary-title">{{ $item->product_item->name ?? '-' }}</div>
                                            <div>{{ $item->services_item->name ?? '-' }}</div>
                                        @else
                                            <div class="item-list-primary-title">{{ $item->product_item->name ?? $item->partnumber_item->name ?? '-' }}</div>
                                            @if(trim((string)($item->product_item->name ?? '')) !== '')
                                            <div class="text-muted">{{ $item->product_item->name }}</div>
                                            @endif
                                            <div>{{ $item->category->name ?? '-' }}</div>
                                            <div>{{ $item->company_item->name ?? '-' }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @can($viewPerm)
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('item.show',$item->id) }}">
                                        <i data-feather="eye" class="me-1"></i> View
                                    </a>
                                    @endcan
                                </td>
                                <td class="no-highlight">
                                    <div class="dropdown">
                                        <button class="btn btn-primary  dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            @can($updatePerm)
                                            <li>
                                                <a class="dropdown-item mt-2" href="{{ route('item.edit',$item->id) }}">
                                                    <i data-feather="edit" class="me-1"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0)" class="dropdown-item mt-2 js-item-toggle-active" data-item-id="{{ $item->id }}" data-is-active="{{ $item->is_active ? 1 : 0 }}">
                                                    @if($item->is_active)
                                                        <i data-feather="toggle-right" class="me-1"></i> Deactivate
                                                    @else
                                                        <i data-feather="toggle-left" class="me-1"></i> Activate
                                                    @endif
                                                </a>
                                            </li>
                                            @endcan
                                            @can($deletePerm)
                                            <li>
                                                <a href="javascript:void(0)"
                                                    onclick="confirmItemDelete('delete-form-{{ $item->id }}', {{ $item->id }})"
                                                    class="dropdown-item mt-2">
                                                    <i data-feather="trash-2" class="feather-trash-2"></i>  Delete
                                                </a>
                                            </li>
                                            @endcan
                                    @if($item->vehical_item)
                                    <hr>
                                    <li>
                                        <a class="dropdown-item text-success" href="javascript:void(0)" onclick="openVehicleServiceHistory({{ $item->id }})">
                                            <i data-feather="car" class="me-1"></i> Vehicle Service History
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                                <td>
                                    @if($item->serial_number && $item->serial_number != $item->id)
                                        <span class="badge bg-primary">{{ $item->serial_number }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
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
                                  <img src="{{ asset($item->barcode_image)}}" alt="" onerror="this.onerror=null; this.src='{{ asset('assets/img/barcode/barcode1.png') }}';" />
                                  @endif
                                </td>
                                <td class="js-item-status-cell">
                                    <span class="badge js-item-status-badge {{ $item->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ $item->category ? $item->category->name : 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="14" class="text-center">No items found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </form>
                <div class="d-flex justify-content-center mt-3">
                    {{ $items->appends(request()->query())->links() }}
                </div>
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
            @canany(['update_items', 'update_parts', 'update_filters', 'update_break_pad', 'update_oil', 'update_battery', 'update_scrap', 'update_services'])
            <button type="button" id="bulkEditBtn" class="btn btn-outline-primary me-2" style="display: none;">
                <i class="ti ti-edit me-1"></i> Bulk Edit
            </button>
            @endcanany
            @can('delete_items')
            <button type="button" id="bulkDeleteBtn" class="btn btn-danger" style="display: none;">
                <i class="ti ti-trash me-1"></i> Delete Selected
            </button>
            @endcan
            <button type="button" id="shareWhatsAppBtn" class="btn btn-success" style="display: none;">
                <i class="ti ti-brand-whatsapp me-1"></i> Share on WhatsApp
            </button>
            {{-- {{ $items->links() }} --}}
            <a href="{{ route('items.recycle.bin') }}" class="btn btn-primary">
                <i class="ti ti-trash me-1"></i> Recycle Bin
            </a>
        </div>
    </div>
</div>

<!-- WhatsApp Share Modal -->
<div class="modal fade" id="whatsappShareModal" tabindex="-1" aria-labelledby="whatsappShareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="whatsappShareModalLabel">
                    <i class="ti ti-brand-whatsapp me-2"></i>Share on WhatsApp
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="whatsappShareForm">
                    <div class="mb-3">
                        <label for="phoneNumber" class="form-label">Phone Number (with Country Code) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">+</span>
                            <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" 
                                placeholder="923001234567" required pattern="[0-9]{10,15}">
                        </div>
                        <small class="text-muted">Example: 923001234567 (without + sign)</small>
                    </div>
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Selected Items:</strong> <span id="selectedItemsCount">0</span> item(s) will be shared
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="generateAndShareBtn">
                    <i class="ti ti-brand-whatsapp me-1"></i> Generate PDF & Share
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Edit Modal -->
<div class="modal fade" id="bulkEditModal" tabindex="-1" aria-labelledby="bulkEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkEditModalLabel">
                    <i class="ti ti-edit me-2"></i>Bulk Edit Items
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkEditForm">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3">Only filled fields will be applied to <strong><span id="bulkEditSelectedCount">0</span></strong> selected item(s). Leave blank to keep current value.</p>
                    <div class="mb-3">
                        <label for="bulk_retail_price" class="form-label">Retail Price (Rs.)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="bulk_retail_price" name="retail_price" placeholder="Leave blank to keep">
                    </div>
                    <div class="mb-3">
                        <label for="bulk_total_price" class="form-label">Cost / Total Price (Rs.)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="bulk_total_price" name="total_price" placeholder="Leave blank to keep">
                    </div>
                    <div class="mb-3">
                        <label for="bulk_sale_price" class="form-label">Sale Price (Rs.)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="bulk_sale_price" name="sale_price" placeholder="Leave blank to keep">
                    </div>
                    <div class="mb-3">
                        <label for="bulk_category_id" class="form-label">Category</label>
                        <select class="form-select" id="bulk_category_id" name="category_id">
                            <option value="">— No change —</option>
                            @isset($categories)
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name ?? 'Category' }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bulk_is_active" class="form-label">Status</label>
                        <select class="form-select" id="bulk_is_active" name="is_active">
                            <option value="">— No change —</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="bulkEditSubmitBtn">
                        <i class="ti ti-check me-1"></i> Apply to Selected
                    </button>
                </div>
            </form>
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

<!-- Vehicle Details - Service History Tracker Modal -->
<div class="modal fade" id="vehicleServiceHistoryModal" tabindex="-1" aria-labelledby="vehicleServiceHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="vehicleServiceHistoryModalLabel">
                    <i class="ti ti-car me-2"></i>Vehicle Details - Service History Tracker
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Vehicle Details Section -->
                <div id="vehicleDetailsSection" class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ti ti-info-circle me-2"></i>Vehicle Information
                    </h6>
                    <div class="row" id="vehicleDetailsContent">
                        <div class="col-md-12 text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading vehicle details...</p>
                        </div>
                    </div>
                </div>
                
                <!-- AI Service History Section -->
                <div id="serviceHistorySection" class="border-top pt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-success mb-0">
                            <i class="ti ti-brain me-2"></i>AI-Generated Service History
                        </h6>
                        <button type="button" id="refreshServiceHistoryBtn" class="btn btn-sm btn-outline-primary" onclick="generateServiceHistory()">
                            <i class="ti ti-refresh me-1"></i>Refresh
                        </button>
                    </div>
                    <div id="serviceHistoryContent">
                        <div class="text-center py-5">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Generating service history with AI...</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Styles -->
<style>
    /* Type filter dropdown styles */
    #typeFilterDropdown {
        cursor: pointer;
    }
    /* Item Details column: main title (part # / product) — bolder + slightly larger */
    #searchableTable .item-list-primary-title {
        font-size: 1.075rem;
        font-weight: 700;
        line-height: 1.35;
        letter-spacing: 0.02em;
        color: var(--bs-heading-color, #1e293b);
    }
</style>

<!-- Scripts -->
<script>
    async function confirmItemDelete(formId, itemId) {
        try {
            const url = '{{ route("items.can_delete", ":id") }}'.replace(':id', itemId);
            const res = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) {
                throw new Error('Server returned HTTP ' + res.status);
            }

            const data = await res.json();
            if (data && data.can_delete === false) {
                const usedIn = data.used_in && typeof data.used_in === 'object' ? data.used_in : {};
                const usedInBits = Object.entries(usedIn).filter(([_, v]) => (parseInt(v, 10) || 0) > 0)
                    .map(([k, v]) => k.replace(/_/g, ' ') + ': ' + v)
                    .slice(0, 6);
                const usedInLine = usedInBits.length ? '<div class="small text-muted mt-2">' + usedInBits.join(' | ') + '</div>' : '';
                Swal.fire({
                    icon: 'warning',
                    title: 'Cannot delete',
                    html: '<div>' + (data.message || 'This item cannot be deleted because it is already used in transactions.') + '</div>' + usedInLine
                });
                return;
            }
        } catch (e) {
            // If we cannot verify usage safely, block deletion for safety.
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Unable to verify item usage. Deletion has been blocked for safety.'
            });
            return;
        }
        confirmDelete(formId);
    }

    // Safety net: block delete on server-checked usage, even if user bypasses UI.
    document.addEventListener('submit', async function(e) {
        const form = e.target;
        if (!form || !(form instanceof HTMLFormElement)) return;
        if (!form.id || !form.id.startsWith('delete-form-')) return;
        if (form.dataset.allowUsageDelete === '1') return;

        const itemIdStr = form.id.replace('delete-form-', '');
        const itemId = parseInt(itemIdStr, 10);
        if (!itemId) return;

        e.preventDefault();
        try {
            const url = '{{ route("items.can_delete", ":id") }}'.replace(':id', itemId);
            const res = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) {
                throw new Error('HTTP ' + res.status);
            }
            const data = await res.json();
            if (data && data.can_delete === false) {
                const usedIn = data.used_in && typeof data.used_in === 'object' ? data.used_in : {};
                const usedInBits = Object.entries(usedIn).filter(([_, v]) => (parseInt(v, 10) || 0) > 0)
                    .map(([k, v]) => k.replace(/_/g, ' ') + ': ' + v)
                    .slice(0, 6);
                const usedInLine = usedInBits.length ? '<div class="small text-muted mt-2">' + usedInBits.join(' | ') + '</div>' : '';

                Swal.fire({
                    icon: 'warning',
                    title: 'Cannot delete',
                    html: '<div>' + (data.message || 'This item cannot be deleted because it is already used in transactions.') + '</div>' + usedInLine
                });
                return;
            }

            // Allowed -> allow submission
            form.dataset.allowUsageDelete = '1';
            form.submit();
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Unable to verify item usage. Deletion has been blocked for safety.'
            });
        }
    }, true);

    document.addEventListener('DOMContentLoaded', function () {
        let currentType = 'all'; // Track current selected type
        
        // Type Dropdown Change Handler
        const typeFilterDropdown = document.getElementById('typeFilterDropdown');
        const priceListBtn = document.getElementById('priceListBtn');
        function updatePriceListHref() {
            if (priceListBtn) {
                const type = typeFilterDropdown ? typeFilterDropdown.value : 'all';
                const base = '{{ route("items.price.list") }}';
                priceListBtn.href = type && type !== 'all' ? (base + '?type=' + encodeURIComponent(type)) : base;
            }
        }
        if (typeFilterDropdown) {
            updatePriceListHref();
            typeFilterDropdown.addEventListener('change', function() {
                const type = this.value;
                currentType = type;
                updatePriceListHref();
                loadItemsByType(type);
            });
        }
        
        // Function to load items by type
        function loadItemsByType(type) {
            const tbody = document.getElementById('itemsTableBody');
            const deleteFormsContainer = document.getElementById('deleteFormsContainer');
            
            // Show loading
            tbody.innerHTML = '<tr><td colspan="14" class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
            
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
                        tbody.innerHTML = '<tr><td colspan="14" class="text-center">No items found for this type.</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error loading items:', error);
                    tbody.innerHTML = '<tr><td colspan="14" class="text-center text-danger">Error loading items. Please try again.</td></tr>';
                });
            }
        }
        
        // Function to render items in table
        function renderItems(items) {
            const tbody = document.getElementById('itemsTableBody');
            const deleteFormsContainer = document.getElementById('deleteFormsContainer');
            
            if (items.length === 0) {
                tbody.innerHTML = '<tr><td colspan="14" class="text-center">No items found.</td></tr>';
                deleteFormsContainer.innerHTML = '';
                updateCheckboxes();
                return;
            }
            
            let tbodyHtml = '';
            let deleteFormsHtml = '';
            
            const baseUrl = window.location.origin;
            const defaultImgUrl = baseUrl + '/assets/img/media/default.png';
            items.forEach(item => {
                // Fix image path: use full URL, avoid double prefix
                let imgSrc = (item.image && String(item.image).trim()) ? item.image : '';
                if (!imgSrc) {
                    imgSrc = defaultImgUrl;
                } else if (!imgSrc.startsWith('http://') && !imgSrc.startsWith('https://')) {
                    imgSrc = baseUrl + (imgSrc.startsWith('/') ? imgSrc : '/' + imgSrc);
                }
                // Item details by type
                let itemDetailsHtml = '';
                if ((item.type || '') === 'battery') {
                    itemDetailsHtml = `<div class="small"><div>${item.company_name || '-'}</div><div class="item-list-primary-title">${item.product_name || item.part_number || '-'}</div><div>${item.volt_name || '-'} ${item.plate_name || '-'} ${item.amphors_name || '-'} ${item.cca_name || '-'}</div></div>`;
                } else if ((item.type || '') === 'parts') {
                    const mutedLineP = (item.product_name && String(item.product_name).trim()) ? `<div class="text-muted">${item.product_name}</div>` : '';
                    itemDetailsHtml = `<div class="small"><div class="item-list-primary-title">${item.product_name || item.part_number || '-'}</div>${mutedLineP}<div>${item.category_name || '-'}</div><div>${item.company_name || '-'}</div><div>${item.quality_name || '-'}</div></div>`;
                } else if ((item.type || '') === 'filters' || (item.type || '') === 'breakpad') {
                    const mutedLineFb = (item.product_name && String(item.product_name).trim()) ? `<div class="text-muted">${item.product_name}</div>` : '';
                    itemDetailsHtml = `<div class="small"><div class="item-list-primary-title">${item.product_name || item.part_number || '-'}</div>${mutedLineFb}<div>${item.category_name || '-'}</div><div>${item.company_name || '-'}</div><div>${item.quality_name || '-'}</div></div>`;
                } else if ((item.type || '') === 'oil') {
                    itemDetailsHtml = `<div class="small"><div class="item-list-primary-title">${item.product_name || '-'}</div><div>${item.category_name || '-'}</div><div>${item.company_name || '-'}</div><div>${item.quality_name || '-'}</div></div>`;
                } else if ((item.type || '') === 'scrap') {
                    itemDetailsHtml = `<div class="small"><div class="item-list-primary-title">${item.product_name || '-'}</div><div>${item.category_name || '-'}</div><div>${item.company_name || '-'}</div><div>${item.level_name || '-'}</div></div>`;
                } else if ((item.type || '') === 'services') {
                    itemDetailsHtml = `<div class="small"><div class="item-list-primary-title">${item.product_name || '-'}</div><div>${item.services_name || '-'}</div></div>`;
                } else {
                    const mutedLineEl = (item.product_name && String(item.product_name).trim()) ? `<div class="text-muted">${item.product_name}</div>` : '';
                    itemDetailsHtml = `<div class="small"><div class="item-list-primary-title">${item.product_name || item.part_number || '-'}</div>${mutedLineEl}<div>${item.category_name || '-'}</div><div>${item.company_name || '-'}</div></div>`;
                }
                tbodyHtml += `
                    <tr data-item-id="${item.id}" data-type="${item.type}">
                        <td>
                            <input type="checkbox" name="ids[]" value="${item.id}" style="width: 20px; height:20px" class="item-checkbox form-check">
                        </td>
                        <td>
                            <img src="${imgSrc}"
                                width="70" height="70" class="rounded item-image"
                                style="cursor:pointer;"
                                data-bs-toggle="modal"
                                data-bs-target="#imageModal"
                                data-src="${imgSrc}"
                                data-fallback="${defaultImgUrl}"
                                onerror="this.onerror=null;this.src=this.getAttribute('data-fallback')||'${defaultImgUrl.replace(/'/g, "\\'")}';">
                        </td>
                        <td>${itemDetailsHtml}</td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="${item.show_url || '#'}">
                                <i data-feather="eye" class="me-1"></i> View
                            </a>
                        </td>
                        <td class="no-highlight">
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item mt-2" href="${item.edit_url || '#'}">
                                            <i data-feather="edit" class="me-1"></i> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" class="dropdown-item mt-2 js-item-toggle-active" data-item-id="${item.id}" data-is-active="${item.is_active ? 1 : 0}">
                                            ${item.is_active ? '<i data-feather="toggle-right" class="me-1"></i> Deactivate' : '<i data-feather="toggle-left" class="me-1"></i> Activate'}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)"
                                            onclick="confirmItemDelete('delete-form-${item.id}', ${item.id})"
                                            class="dropdown-item mt-2">
                                            <i data-feather="trash-2" class="feather-trash-2"></i> Delete
                                        </a>
                                    </li>
                                    ${item.has_vehicle ? `
                                    <hr>
                                    <li>
                                        <a class="dropdown-item text-success" href="javascript:void(0)" onclick="openVehicleServiceHistory(${item.id})">
                                            <i data-feather="car" class="me-1"></i> Vehicle Service History
                                        </a>
                                    </li>
                                    ` : ''}
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
                        <td class="js-item-status-cell">
                            <span class="badge js-item-status-badge ${item.is_active ? 'bg-success' : 'bg-secondary'}">
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
                updateActionButtons();
            });
            
            // Use event delegation for checkboxes (works for dynamically added items)
            $(document).off('change', '.item-checkbox').on('change', '.item-checkbox', function() {
                updateActionButtons();
                updateSelectAllState();
            });
            
            // Update button states
            updateActionButtons();
            updateSelectAllState();
        }
        
        // Function to show/hide action buttons based on selection
        function updateActionButtons() {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            const selected = Array.from(checkboxes).filter(chk => chk.checked);
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            const shareWhatsAppBtn = document.getElementById('shareWhatsAppBtn');
            
            const bulkEditBtn = document.getElementById('bulkEditBtn');
            if (selected.length > 0) {
                if (bulkDeleteBtn) bulkDeleteBtn.style.display = 'inline-block';
                if (shareWhatsAppBtn) shareWhatsAppBtn.style.display = 'inline-block';
                if (bulkEditBtn) bulkEditBtn.style.display = 'inline-block';
            } else {
                if (bulkDeleteBtn) bulkDeleteBtn.style.display = 'none';
                if (shareWhatsAppBtn) shareWhatsAppBtn.style.display = 'none';
                if (bulkEditBtn) bulkEditBtn.style.display = 'none';
            }
        }
        
        // Function to update select all checkbox state
        function updateSelectAllState() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.item-checkbox');
            const checkedCount = Array.from(checkboxes).filter(chk => chk.checked).length;
            
            if (selectAll && checkboxes.length > 0) {
                if (checkedCount === 0) {
                    selectAll.checked = false;
                    selectAll.indeterminate = false;
                } else if (checkedCount === checkboxes.length) {
                    selectAll.checked = true;
                    selectAll.indeterminate = false;
                } else {
                    selectAll.checked = false;
                    selectAll.indeterminate = true;
                }
            }
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
        
        // WhatsApp Share Button Click - Use event delegation to handle dynamically
        $(document).on('click', '#shareWhatsAppBtn', function() {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            const selected = Array.from(checkboxes).filter(chk => chk.checked);
            
            if (selected.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Selection',
                    text: 'Please select at least one item to share.'
                });
                return;
            }
            
            // Update selected items count in modal
            document.getElementById('selectedItemsCount').textContent = selected.length;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('whatsappShareModal'));
            modal.show();
        });

        // Bulk Edit: show modal with selected count
        $(document).on('click', '#bulkEditBtn', function() {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            const selected = Array.from(checkboxes).filter(chk => chk.checked);
            if (selected.length === 0) {
                Swal.fire({ icon: 'warning', title: 'No Selection', text: 'Please select at least one item to edit.' });
                return;
            }
            document.getElementById('bulkEditSelectedCount').textContent = selected.length;
            document.getElementById('bulkEditForm').reset();
            document.getElementById('bulk_category_id').value = '';
            document.getElementById('bulk_is_active').value = '';
            const modal = new bootstrap.Modal(document.getElementById('bulkEditModal'));
            modal.show();
        });

        // Bulk Edit form submit
        $('#bulkEditForm').on('submit', function(e) {
            e.preventDefault();
            const checkboxes = document.querySelectorAll('.item-checkbox');
            const selectedIds = Array.from(checkboxes).filter(chk => chk.checked).map(chk => chk.value);
            if (selectedIds.length === 0) {
                Swal.fire({ icon: 'warning', title: 'No Selection', text: 'Please select at least one item.' });
                return;
            }
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            selectedIds.forEach(function(id) { formData.append('ids[]', id); });
            const retail = $('#bulk_retail_price').val();
            const total = $('#bulk_total_price').val();
            const sale = $('#bulk_sale_price').val();
            const cat = $('#bulk_category_id').val();
            const active = $('#bulk_is_active').val();
            if (retail !== '') formData.append('retail_price', retail);
            if (total !== '') formData.append('total_price', total);
            if (sale !== '') formData.append('sale_price', sale);
            if (cat !== '') formData.append('category_id', cat);
            if (active !== '') formData.append('is_active', active);
            const allEmpty = !retail && !total && !sale && !cat && active === '';
            if (allEmpty) {
                Swal.fire({ icon: 'info', title: 'No Changes', text: 'Please fill at least one field to update.' });
                return;
            }
            $('#bulkEditSubmitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Applying...');
            $.ajax({
                url: '{{ route("all.items.bulkUpdate") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    bootstrap.Modal.getInstance(document.getElementById('bulkEditModal')).hide();
                    Swal.fire({ icon: 'success', title: 'Done', text: res.message || res.updated + ' item(s) updated.' });
                    document.querySelectorAll('.item-checkbox').forEach(function(chk) { chk.checked = false; });
                    if (document.getElementById('selectAll')) document.getElementById('selectAll').checked = false;
                    updateActionButtons();
                    updateSelectAllState();
                    location.reload();
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : (xhr.responseJSON && xhr.responseJSON.errors ? Object.values(xhr.responseJSON.errors).flat().join(' ') : 'Update failed.');
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                },
                complete: function() {
                    $('#bulkEditSubmitBtn').prop('disabled', false).html('<i class="ti ti-check me-1"></i> Apply to Selected');
                }
            });
        });
        
        // Generate PDF and Share on WhatsApp - Use event delegation
        $(document).on('click', '#generateAndShareBtn', function() {
            const phoneNumber = document.getElementById('phoneNumber').value.trim();
            
            if (!phoneNumber) {
                Swal.fire({
                    icon: 'error',
                    title: 'Phone Number Required',
                    text: 'Please enter a phone number with country code.'
                });
                return;
            }
            
            // Validate phone number format
            if (!/^[0-9]{10,15}$/.test(phoneNumber)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Phone Number',
                    text: 'Please enter a valid phone number (10-15 digits).'
                });
                return;
            }
            
            // Get selected item IDs
            const checkboxes = document.querySelectorAll('.item-checkbox');
            const selectedIds = Array.from(checkboxes)
                .filter(chk => chk.checked)
                .map(chk => chk.value);
            
            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Selection',
                    text: 'Please select at least one item.'
                });
                return;
            }
            
            // Show loading
            const btn = this;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader me-1"></i> Generating PDF...';
            
            // Generate PDF and get share URL
            fetch('{{ route("items.generate.whatsapp.pdf") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    item_ids: selectedIds,
                    phone_number: phoneNumber
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Open WhatsApp with PDF
                    const whatsappUrl = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(data.message)}`;
                    window.open(whatsappUrl, '_blank');
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('whatsappShareModal'));
                    modal.hide();
                    
                    // Reset form
                    document.getElementById('whatsappShareForm').reset();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Shared!',
                        text: 'PDF has been shared on WhatsApp.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to generate PDF. Please try again.'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.'
                });
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });
        
        // Bulk Delete with SweetAlert - Use event delegation
        $(document).on('click', '#bulkDeleteBtn', function() {
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

            const selectedIds = selected.map(chk => chk.value);
            const checkUrlForId = (itemId) => '{{ route("items.can_delete", ":id") }}'.replace(':id', itemId);

            // Verify deletion eligibility before showing final confirmation.
            Promise.all(selectedIds.map(id =>
                fetch(checkUrlForId(id), {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.ok ? r.json() : Promise.reject(new Error('HTTP ' + r.status)))
            )).then(results => {
                const blocked = results
                    .map((r, idx) => ({ id: selectedIds[idx], can_delete: r.can_delete, message: r.message, used_in: r.used_in }))
                    .filter(x => x.can_delete === false);

                if (blocked.length > 0) {
                    const blockedLines = blocked.slice(0, 6).map(b => {
                        return `<div class="small text-muted">#${b.id}: ${b.message || 'This item cannot be deleted because it is already used in transactions.'}</div>`;
                    }).join('');

                    Swal.fire({
                        icon: 'warning',
                        title: 'Cannot delete used items',
                        html: blockedLines + (blocked.length > 6 ? `<div class="small text-muted mt-2">... and ${blocked.length - 6} more</div>` : '')
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
                        const bulkDeleteUrl = '{{ route("all.items.bulkDelete") }}';
                        const csrfTokenEl = document.querySelector('meta[name="csrf-token"]');
                        const csrfToken = csrfTokenEl ? csrfTokenEl.getAttribute('content') : '';

                        fetch(bulkDeleteUrl, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ ids: selectedIds })
                        })
                        .then(async (res) => {
                            if (!res.ok) {
                                // Try to parse message; otherwise fall back.
                                const txt = await res.text().catch(() => '');
                                throw new Error(txt || ('HTTP ' + res.status));
                            }
                            return res.json();
                        })
                        .then((data) => {
                            const blockedItems = Array.isArray(data.blocked_items) ? data.blocked_items : [];
                            if (blockedItems.length > 0) {
                                const blockedLines = blockedItems.slice(0, 10).map(b => {
                                    const usedInBits = b.used_in && typeof b.used_in === 'object'
                                        ? Object.entries(b.used_in)
                                            .filter(([_, v]) => (parseInt(v, 10) || 0) > 0)
                                            .map(([k, v]) => k.replace(/_/g, ' ') + ': ' + v)
                                            .slice(0, 3)
                                            .join(', ')
                                        : '';
                                    return `<div class="small text-muted">#${b.id}: ${b.message || 'Used in transactions.'}${usedInBits ? ' (' + usedInBits + ')' : ''}</div>`;
                                }).join('');

                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Cannot delete used items',
                                    html: blockedLines + (blockedItems.length > 10 ? `<div class="small text-muted mt-2">... and ${blockedItems.length - 10} more</div>` : '')
                                });
                            } else {
                                Swal.fire({
                                    icon: data.success ? 'success' : 'warning',
                                    title: data.success ? 'Deleted' : 'Not deleted',
                                    text: data.message || 'Operation completed.'
                                });
                            }

                            // Refresh to reflect updated item status.
                            setTimeout(function() { location.reload(); }, 500);
                        })
                        .catch((err) => {
                            console.error('Bulk delete failed:', err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Unable to verify item usage. Deletion has been blocked for safety.'
                            });
                        });
                    }
                });
            }).catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Unable to verify item usage. Deletion has been blocked for safety.'
                });
            });
        });
        
        // Initial image handlers
        attachImageHandlers();
        
        // Initial button state
        setTimeout(function() {
            updateActionButtons();
            updateSelectAllState();
        }, 100);
        
        // Initial button state
        setTimeout(function() {
            updateActionButtons();
            updateSelectAllState();
        }, 100);
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalImage = document.getElementById('modalImage');
    
    if (!modalImage) return;

    document.querySelectorAll('.item-image').forEach(img => {
        img.addEventListener('click', function() {
            const src = this.getAttribute('data-src');
            if (modalImage) modalImage.src = src;
        });
    });

    // Optional: clear modal image on close
    const imageModal = document.getElementById('imageModal');
    if (imageModal) {
        imageModal.addEventListener('hidden.bs.modal', function () {
            if (modalImage) modalImage.src = '';
        });
    }
    
    // Initial button state on page load
    setTimeout(function() {
        if (typeof updateActionButtons === 'function') {
            updateActionButtons();
        }
    }, 500);
});

// Global variable to store current item ID
let currentVehicleItemId = null;

// Function to open Vehicle Service History modal
function openVehicleServiceHistory(itemId) {
    currentVehicleItemId = itemId;
    const modal = new bootstrap.Modal(document.getElementById('vehicleServiceHistoryModal'));
    modal.show();
    
    // Load vehicle details and service history
    loadVehicleDetails(itemId);
    generateServiceHistory(itemId);
}

// Function to load vehicle details
function loadVehicleDetails(itemId) {
    const vehicleDetailsContent = document.getElementById('vehicleDetailsContent');
    vehicleDetailsContent.innerHTML = '<div class="col-md-12 text-center py-3"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading vehicle details...</p></div>';
    
    fetch(`/items/${itemId}/vehicle-details`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.vehicle) {
            const vehicle = data.vehicle;
            let html = '';
            
            if (vehicle.manufacturer || vehicle.model || vehicle.engine || vehicle.country || vehicle.year_ranges) {
                html = '<div class="row g-3">';
                if (vehicle.manufacturer) {
                    html += `<div class="col-md-6"><div class="card border-primary"><div class="card-body"><h6 class="text-primary mb-1"><i class="ti ti-building me-2"></i>Manufacturer</h6><p class="mb-0 fw-bold">${vehicle.manufacturer}</p></div></div></div>`;
                }
                if (vehicle.model) {
                    html += `<div class="col-md-6"><div class="card border-info"><div class="card-body"><h6 class="text-info mb-1"><i class="ti ti-car me-2"></i>Model</h6><p class="mb-0 fw-bold">${vehicle.model}</p></div></div></div>`;
                }
                if (vehicle.engine) {
                    html += `<div class="col-md-6"><div class="card border-warning"><div class="card-body"><h6 class="text-warning mb-1"><i class="ti ti-engine me-2"></i>Engine CC</h6><p class="mb-0 fw-bold">${vehicle.engine} CC</p></div></div></div>`;
                }
                if (vehicle.country) {
                    html += `<div class="col-md-6"><div class="card border-success"><div class="card-body"><h6 class="text-success mb-1"><i class="ti ti-world me-2"></i>Country</h6><p class="mb-0 fw-bold">${vehicle.country}</p></div></div></div>`;
                }
                if (vehicle.year_ranges && vehicle.year_ranges.length > 0) {
                    const yearRanges = vehicle.year_ranges.join(', ');
                    html += `<div class="col-md-12"><div class="card border-secondary"><div class="card-body"><h6 class="text-secondary mb-1"><i class="ti ti-calendar me-2"></i>Year Ranges</h6><p class="mb-0 fw-bold">${yearRanges}</p></div></div></div>`;
                }
                if (vehicle.part_number) {
                    html += `<div class="col-md-12"><div class="card border-dark"><div class="card-body"><h6 class="text-dark mb-1"><i class="ti ti-tag me-2"></i>Part Number</h6><p class="mb-0 fw-bold">${vehicle.part_number}</p></div></div></div>`;
                }
                html += '</div>';
            } else {
                html = '<div class="alert alert-warning"><i class="ti ti-alert-circle me-2"></i>No vehicle details available for this item.</div>';
            }
            
            vehicleDetailsContent.innerHTML = html;
        } else {
            vehicleDetailsContent.innerHTML = '<div class="alert alert-warning"><i class="ti ti-alert-circle me-2"></i>No vehicle details found.</div>';
        }
    })
    .catch(error => {
        console.error('Error loading vehicle details:', error);
        vehicleDetailsContent.innerHTML = '<div class="alert alert-danger"><i class="ti ti-alert-circle me-2"></i>Error loading vehicle details. Please try again.</div>';
    });
}

// Function to generate service history using Google AI (Gemini)
function generateServiceHistory(itemId) {
    if (!itemId) itemId = currentVehicleItemId;
    if (!itemId) return;
    
    const serviceHistoryContent = document.getElementById('serviceHistoryContent');
    serviceHistoryContent.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-success" role="status"></div><p class="mt-2 text-muted">Generating service history with Google AI...</p></div>';
    
    fetch(`/items/${itemId}/service-history-ai`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ item_id: itemId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.service_history) {
            serviceHistoryContent.innerHTML = `
                <div class="card border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="ti ti-brain text-success me-2 fs-4"></i>
                            <h6 class="mb-0 text-success">AI-Generated Service History</h6>
                        </div>
                        <div class="service-history-text" style="white-space: pre-wrap; line-height: 1.8; color: #333;">
                            ${data.service_history}
                        </div>
                    </div>
                </div>
            `;
        } else {
            serviceHistoryContent.innerHTML = `
                <div class="alert alert-warning">
                    <i class="ti ti-alert-circle me-2"></i>
                    ${data.message || 'Unable to generate service history. Please try again.'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error generating service history:', error);
        serviceHistoryContent.innerHTML = `
            <div class="alert alert-danger">
                <i class="ti ti-alert-circle me-2"></i>
                Error generating service history. Please check your Google AI API key configuration or try again later.
            </div>
        `;
    });
}
</script>

<script>
// Suppress 404 errors for missing images and SVGs
(function() {
    // Suppress image 404 errors
    const originalError = window.onerror;
    window.onerror = function(msg, url, line, col, error) {
        // Suppress 404 errors for images and SVGs
        if (msg && (msg.includes('404') || msg.includes('Failed to load resource'))) {
            if (url && (url.includes('.png') || url.includes('.jpg') || url.includes('.jpeg') || url.includes('.svg') || url.includes('barcodes'))) {
                return true; // Suppress error
            }
        }
        // Suppress addEventListener null errors
        if (msg && msg.includes('addEventListener') && msg.includes('null')) {
            return true; // Suppress error
        }
        // Call original error handler for other errors
        if (originalError) {
            return originalError.apply(this, arguments);
        }
        return false;
    };
    
    // Add error handlers to all images
    document.addEventListener('DOMContentLoaded', function() {
        // Handle image load errors
        document.querySelectorAll('img').forEach(img => {
            if (!img.onerror) {
                img.addEventListener('error', function() {
                    // Set default image if not already set
                    if (!this.src.includes('default.png') && !this.src.includes('barcode1.png')) {
                        if (this.src.includes('barcodes') || this.src.includes('barcode')) {
                            this.src = '/assets/img/barcode/barcode1.png';
                        } else {
                            this.src = '/assets/img/media/default.png';
                        }
                    }
                }, { once: true });
            }
        });
    });
    
    // Suppress console errors for 404s
    const originalConsoleError = console.error;
    console.error = function(...args) {
        const message = args[0] || '';
        if (typeof message === 'string') {
            // Suppress 404 errors
            if (message.includes('404') || message.includes('Failed to load resource')) {
                if (message.includes('.png') || message.includes('.jpg') || message.includes('.jpeg') || message.includes('.svg') || message.includes('barcodes')) {
                    return; // Suppress
                }
            }
            // Suppress addEventListener null errors
            if (message.includes('addEventListener') && message.includes('null')) {
                return; // Suppress
            }
        }
        originalConsoleError.apply(console, args);
    };
})();
</script>

@if (session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Cannot delete',
                    text: @json(session('error'))
                });
            }
        });
    </script>
@endif

@include('admin.item.partials.item-active-toggle-script')

@endsection
