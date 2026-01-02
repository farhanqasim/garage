@extends('layouts.app')
@section('title', 'Sales')
@section('content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Create Sales</h4>
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
            <a href="{{ route('all_sales') }}" class="btn btn-primary"><i class="ti ti-circle-plus me-1"></i>All
                Sales</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form action="">
                <div class="card border-0">
                    <div class="card-body pb-0">
                        <div class="table-responsive no-pagination mb-3">
                            <table class="table datanew">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Purchase Price($)</th>
                                        <th>Discount($)</th>
                                        <th>Tax(%)</th>
                                        <th>Tax Amount($)</th>
                                        <th>Unit Cost($)</th>
                                        <th>Total Cost($)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="sales-items-body">
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No items added yet</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Customer Name<span
                                            class="text-danger ms-1">*</span></label>
                                    <div class="row">
                                        <div class="col-lg-10 col-sm-10 col-10">
                                            <select class="select form-control">
                                                <option>Select</option>
                                                <option>Carl Evans</option>
                                                <option>Minerva Rameriz</option>
                                                <option>Robert Lamon</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-2 col-sm-2 col-2 ps-0">
                                            <div class="add-icon">
                                                <a href="#" class="bg-dark text-white p-2 rounded"
                                                    data-bs-toggle="modal" data-bs-target="#add_customer"><i
                                                        data-feather="plus-circle" class="plus"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Date<span class="text-danger ms-1">*</span></label>
                                    <div class="input-group">
                                        <input type="date" class=" form-control" placeholder="Choose">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Supplier<span class="text-danger ms-1">*</span></label>
                                    <select class="select form-control">
                                        <option>Select</option>
                                        <option>Apex Computers</option>
                                        <option>Beats Headphones</option>
                                        <option>Dazzle Shoes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-12 col-12">
                                <div class="mb-3 position-relative">
                                    <label class="form-label">
                                        Product <span class="text-danger ms-1">*</span>
                                    </label>

                                    <div class="input-group">
                                        <input type="text" id="product-search" class="form-control"
                                            placeholder="Type barcode / part number / vehicle / model / year">

                                        <a class="btn btn-primary"   data-bs-toggle="modal" data-bs-target="#add-sales-new">
                                            <i class="fas fa-search"></i> Search
                                        </a>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-lg-6 ms-auto">
                                <div class="total-order w-100 max-widthauto m-auto mb-4">
                                    <ul class="border-1 rounded-2">
                                        <li class="border-bottom">
                                            <h4 class="border-end">Order Tax</h4>
                                            <h5>$ 0.00</h5>
                                        </li>
                                        <li class="border-bottom">
                                            <h4 class="border-end">Discount</h4>
                                            <h5>$ 0.00</h5>
                                        </li>
                                        <li class="border-bottom">
                                            <h4 class="border-end">Shipping</h4>
                                            <h5>$ 0.00</h5>
                                        </li>
                                        <li class="border-bottom">
                                            <h4 class="border-end">Grand Total</h4>
                                            <h5>$ 0.00</h5>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Order Tax<span class="text-danger ms-1">*</span></label>
                                    <div class="input-group">
                                        <input type="text" value="0" class="form-control p-2">
                                    </div>

                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Discount<span class="text-danger ms-1">*</span></label>
                                    <div class="input-group">
                                        <input type="text" value="0" class="form-control p-2">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Shipping<span class="text-danger ms-1">*</span></label>
                                    <div class="input-group">
                                        <input type="text" value="0" class="form-control p-2">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="mb-3 mb-5">
                                    <label class="form-label">Status<span class="text-danger ms-1">*</span></label>
                                    <select class="select form-control">
                                        <option>Select</option>
                                        <option>Completed</option>
                                        <option>Inprogress</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary add-cancel me-3"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary add-sale">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

	<!-- YouTube-Style Search & Filter Modal -->
	<div class="modal fade" id="add-sales-new" tabindex="-1" aria-labelledby="addSalesModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content" style="border-radius: 12px; overflow: hidden;">
				<div class="modal-header border-bottom" style="background: #f8f9fa;">
						<div class="page-title">
						<h4 class="mb-0">Search & Filter Items</h4>
						<small class="text-muted">Find items using advanced filters</small>
					</div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body p-0">
					<!-- Search Bar -->
					<div class="p-4 border-bottom" style="background: #fff;">
						<div class="position-relative">
							<input type="text" id="item-search-input" class="form-control form-control-lg ps-5" 
								placeholder="Search by barcode, part number, vehicle, model, year..." 
								style="border-radius: 24px; border: 2px solid #e0e0e0;">
							<i class="fas fa-search position-absolute" style="left: 20px; top: 50%; transform: translateY(-50%); color: #999;"></i>
							<button type="button" id="clear-search" class="btn btn-link position-absolute d-none" 
								style="right: 10px; top: 50%; transform: translateY(-50%); padding: 0; color: #999;">
								<i class="fas fa-times"></i>
							</button>
						</div>
					</div>

					<!-- Filter Chips (YouTube-style) -->
					<div class="px-4 py-3 border-bottom" style="background: #f8f9fa; overflow-x: auto;">
						<div class="d-flex gap-2 flex-wrap align-items-center">
							<span class="text-muted small fw-bold me-2">Filters:</span>
							<button type="button" class="btn btn-sm filter-chip" data-filter="in_stock" data-value="yes" style="border-radius: 16px; white-space: nowrap;">
								<i class="fas fa-check-circle me-1"></i> In Stock
							</button>
							<button type="button" class="btn btn-sm filter-chip" data-filter="is_active" data-value="1" style="border-radius: 16px; white-space: nowrap;">
								<i class="fas fa-toggle-on me-1"></i> Active
							</button>
							<button type="button" class="btn btn-sm" id="advanced-filters-toggle" style="border-radius: 16px; white-space: nowrap;">
								<i class="fas fa-filter me-1"></i> More Filters
							</button>
							<button type="button" class="btn btn-sm btn-outline-danger d-none" id="clear-all-filters" style="border-radius: 16px; white-space: nowrap;">
								<i class="fas fa-times me-1"></i> Clear All
						</button>
						</div>
					</div>

					<!-- Advanced Filters Panel (Collapsible) -->
					<div class="collapse" id="advancedFiltersPanel">
						<div class="p-4 border-bottom" style="background: #fff;">
							<div class="row g-3">
								<div class="col-md-3">
									<label class="form-label small fw-bold text-muted">Category</label>
									<select class="form-select form-select-sm" id="filter-category">
										<option value="">All Categories</option>
									</select>
								</div>
								<div class="col-md-3">
									<label class="form-label small fw-bold text-muted">Manufacturer</label>
									<select class="form-select form-select-sm" id="filter-manufacturer">
										<option value="">All Manufacturers</option>
									</select>
								</div>
								<div class="col-md-3">
									<label class="form-label small fw-bold text-muted">Part Number</label>
									<select class="form-select form-select-sm" id="filter-part-number">
										<option value="">All Part Numbers</option>
									</select>
								</div>
								<div class="col-md-3">
									<label class="form-label small fw-bold text-muted">Technology</label>
									<select class="form-select form-select-sm" id="filter-technology">
										<option value="">All Technologies</option>
									</select>
								</div>
								<div class="col-md-3">
									<label class="form-label small fw-bold text-muted">Grade</label>
									<select class="form-select form-select-sm" id="filter-grade">
										<option value="">All Grades</option>
									</select>
								</div>
								<div class="col-md-3">
									<label class="form-label small fw-bold text-muted">Volt</label>
									<select class="form-select form-select-sm" id="filter-volt">
										<option value="">All Volts</option>
									</select>
								</div>
								<div class="col-md-3">
									<label class="form-label small fw-bold text-muted">CCA</label>
									<select class="form-select form-select-sm" id="filter-cca">
										<option value="">All CCAs</option>
									</select>
								</div>
								<div class="col-md-3">
									<label class="form-label small fw-bold text-muted">Supplier</label>
									<select class="form-select form-select-sm" id="filter-supplier">
										<option value="">All Suppliers</option>
									</select>
								</div>
								<div class="col-md-3">
									<label class="form-label small fw-bold text-muted">Rack</label>
									<select class="form-select form-select-sm" id="filter-rack">
										<option value="">All Racks</option>
									</select>
								</div>
								<div class="col-md-3">
									<label class="form-label small fw-bold text-muted">Min Price</label>
									<input type="number" class="form-control form-control-sm" id="filter-min-price" placeholder="0.00" step="0.01">
								</div>
								<div class="col-md-3">
									<label class="form-label small fw-bold text-muted">Max Price</label>
									<input type="number" class="form-control form-control-sm" id="filter-max-price" placeholder="0.00" step="0.01">
								</div>
							</div>
						</div>
					</div>

					<!-- Stock Info -->
					<div class="row g-2 px-4 py-3 border-bottom" style="background: #f8f9fa;">
          <div class="col-6">
            <div class="p-2 rounded" style="background-color: #f0fff4; border: 1px solid #d1fae5;">
              <small class="text-success fw-bold d-block mb-1" style="font-size: 0.7rem;">WAREHOUSE</small>
								<div class="fw-bold text-success" id="warehouse-stock">0 Units</div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-2 rounded" style="background-color: #fffaf0; border: 1px solid #feebc8;">
              <small class="text-warning fw-bold d-block mb-1" style="font-size: 0.7rem; color: #c05621 !important;">SHOP</small>
								<div class="fw-bold" style="color: #c05621;" id="shop-stock">0 Units</div>
            </div>
          </div>
        </div>

					<!-- Results Container -->
					<div class="p-4" style="max-height: 400px; overflow-y: auto;">
						<div id="search-results-container">
							<div class="text-center text-muted py-5">
								<i class="fas fa-search fa-3x mb-3" style="opacity: 0.3;"></i>
								<p>Start typing to search items or use filters above</p>
            </div>
          </div>
						<div id="no-results" class="text-center text-muted py-5 d-none">
							<i class="fas fa-inbox fa-3x mb-3" style="opacity: 0.3;"></i>
							<p>No items found. Try adjusting your search or filters.</p>
              </div>
						<div id="loading-results" class="text-center py-5 d-none">
							<div class="spinner-border text-primary" role="status">
								<span class="visually-hidden">Loading...</span>
							</div>
							<p class="mt-2 text-muted">Searching...</p>
						</div>
					</div>
						</div>
				<div class="modal-footer border-top">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				</div>
				</div>
			</div>
		</div>
@endsection
@push('styles')
<style>
    .filter-chip {
        background: #fff;
        border: 1px solid #ddd;
        color: #333;
        transition: all 0.2s;
    }
    .filter-chip:hover {
        background: #f0f0f0;
        border-color: #999;
    }
    .filter-chip.active {
        background: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }
    .item-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: all 0.2s;
        background: #fff;
    }
    .item-card:hover {
        border-color: #0d6efd;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .item-card.selected {
        border-color: #0d6efd;
        background: #e7f1ff;
    }
    mark {
        background: #ffeb3b;
        padding: 2px 4px;
        border-radius: 3px;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
    // Elements
    const searchInput = $('#item-search-input');
    const clearSearchBtn = $('#clear-search');
    const modal = $('#add-sales-new');
    const resultsContainer = $('#search-results-container');
        const noResults = $('#no-results');
    const loadingResults = $('#loading-results');
    const advancedFiltersToggle = $('#advanced-filters-toggle');
    const clearAllFiltersBtn = $('#clear-all-filters');
    
    // Filter state
    let activeFilters = {};
    let filterOptions = {};
    let searchTimeout = null;
    
    // Initialize: Load filter options and open modal
    $('#product-search').on('click', function() {
        modal.modal('show');
        if (Object.keys(filterOptions).length === 0) {
            loadFilterOptions();
        }
    });
    
    // Load filter options
    function loadFilterOptions() {
        $.ajax({
            url: "{{ route('sales.filter.options') }}",
            success: function(data) {
                filterOptions = data;
                populateFilterDropdowns(data);
            },
            error: function(xhr) {
                console.error('Error loading filter options:', xhr);
            }
        });
    }
    
    // Populate filter dropdowns
    function populateFilterDropdowns(data) {
        // Categories
        if (data.categories) {
            data.categories.forEach(cat => {
                $('#filter-category').append(`<option value="${cat.id}">${cat.name}</option>`);
            });
        }
        
        // Manufacturers
        if (data.manufacturers) {
            data.manufacturers.forEach(man => {
                $('#filter-manufacturer').append(`<option value="${man.id}">${man.name}</option>`);
            });
        }
        
        // Part Numbers
        if (data.part_numbers) {
            data.part_numbers.forEach(pn => {
                $('#filter-part-number').append(`<option value="${pn.id}">${pn.name}</option>`);
            });
        }
        
        // Technologies
        if (data.technologies) {
            data.technologies.forEach(tech => {
                $('#filter-technology').append(`<option value="${tech.id}">${tech.name}</option>`);
            });
        }
        
        // Grades
        if (data.grades) {
            data.grades.forEach(grade => {
                $('#filter-grade').append(`<option value="${grade.id}">${grade.name}</option>`);
            });
        }
        
        // Volts
        if (data.volts) {
            data.volts.forEach(volt => {
                $('#filter-volt').append(`<option value="${volt.id}">${volt.name}</option>`);
            });
        }
        
        // CCAs
        if (data.ccas) {
            data.ccas.forEach(cca => {
                $('#filter-cca').append(`<option value="${cca.id}">${cca.name}</option>`);
            });
        }
        
        // Suppliers
        if (data.suppliers) {
            data.suppliers.forEach(supplier => {
                $('#filter-supplier').append(`<option value="${supplier}">${supplier}</option>`);
            });
        }
        
        // Racks
        if (data.racks) {
            data.racks.forEach(rack => {
                $('#filter-rack').append(`<option value="${rack}">${rack}</option>`);
            });
        }
    }
    
    // Live search with debounce
    searchInput.on('input', function() {
        const query = $(this).val().trim();
        clearSearchBtn.toggleClass('d-none', !query);
        
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            if (query.length >= 2 || Object.keys(activeFilters).length > 0) {
                performSearch();
            } else {
                resultsContainer.html(`
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-search fa-3x mb-3" style="opacity: 0.3;"></i>
                        <p>Start typing to search items or use filters above</p>
                    </div>
                `);
            }
        }, 500);
    });
    
    // Clear search
    clearSearchBtn.on('click', function() {
        searchInput.val('');
        $(this).addClass('d-none');
        performSearch();
    });
    
    // Filter chip clicks
    $('.filter-chip').on('click', function() {
        const filter = $(this).data('filter');
        const value = $(this).data('value');
        
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            delete activeFilters[filter];
        } else {
            $('.filter-chip[data-filter="' + filter + '"]').removeClass('active');
            $(this).addClass('active');
            activeFilters[filter] = value;
        }
        
        updateClearAllButton();
        performSearch();
    });
    
    // Advanced filter changes
    $('#filter-category, #filter-manufacturer, #filter-part-number, #filter-technology, #filter-grade, #filter-volt, #filter-cca, #filter-supplier, #filter-rack, #filter-min-price, #filter-max-price').on('change input', function() {
        const filterId = $(this).attr('id').replace('filter-', '').replace('-', '_');
        const value = $(this).val();
        
        if (value) {
            activeFilters[filterId] = value;
        } else {
            delete activeFilters[filterId];
        }
        
        updateClearAllButton();
        performSearch();
    });
    
    // Toggle advanced filters
    advancedFiltersToggle.on('click', function() {
        $('#advancedFiltersPanel').collapse('toggle');
    });
    
    // Clear all filters
    clearAllFiltersBtn.on('click', function() {
        activeFilters = {};
        $('.filter-chip').removeClass('active');
        $('#filter-category, #filter-manufacturer, #filter-part-number, #filter-technology, #filter-grade, #filter-volt, #filter-cca, #filter-supplier, #filter-rack').val('');
        $('#filter-min-price, #filter-max-price').val('');
        updateClearAllButton();
        performSearch();
    });
    
    // Update clear all button visibility
    function updateClearAllButton() {
        const hasFilters = Object.keys(activeFilters).length > 0 || searchInput.val().trim().length > 0;
        clearAllFiltersBtn.toggleClass('d-none', !hasFilters);
    }
    
    // Perform search
    function performSearch() {
        const query = searchInput.val().trim();
        
        // Build search params
        const params = {
            q: query,
            limit: 50
        };
        
        // Add active filters
        Object.keys(activeFilters).forEach(key => {
            params[key] = activeFilters[key];
        });
        
        // Show loading
        resultsContainer.hide();
            noResults.hide();
        loadingResults.show();
        
        // Perform AJAX search
            $.ajax({
                url: "{{ route('sales.items.ajax.search') }}",
            data: params,
                success: function(items) {
                loadingResults.hide();
                
                    if (items.length === 0) {
                        noResults.show();
                    resultsContainer.hide();
                        return;
                }
                
                noResults.hide();
                resultsContainer.show();
                
                let html = '';
                const searchTerm = query.toLowerCase();
                const regex = searchTerm ? new RegExp(searchTerm.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&'), 'gi') : null;
                
                items.forEach(item => {
                    const partNumber = item.partnumber_item?.name || 'N/A';
                    const manufacturer = item.vehical_item?.manutacturer_vehical?.name || '';
                    const model = item.vehical_item?.model_vehical?.name || '';
                    const year = item.vehical_item?.carmanufactured_year || '';
                    const price = item.sale_price || 0;
                    const stock = item.on_hand || 0;
                    const barCode = item.bar_code || '';
                    const serialNumber = item.serial_number || '';
                    
                    // Highlight search term
                    let displayPartNumber = partNumber;
                    let displayManufacturer = manufacturer;
                    let displayModel = model;
                    let displayYear = String(year);
                    
                    if (regex) {
                        displayPartNumber = partNumber.replace(regex, match => `<mark>${match}</mark>`);
                        displayManufacturer = manufacturer.replace(regex, match => `<mark>${match}</mark>`);
                        displayModel = model.replace(regex, match => `<mark>${match}</mark>`);
                        displayYear = String(year).replace(regex, match => `<mark>${match}</mark>`);
                    }
                    
                        html += `
                        <div class="item-card" data-id="${item.id}" 
                             data-name="${partNumber.replace(/"/g, '&quot;')}"
                             data-price="${price}"
                             data-stock="${stock}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">${displayPartNumber}</h6>
                                    <div class="small text-muted mb-2">
                                        ${displayManufacturer ? displayManufacturer + ' ' : ''}${displayModel}${displayYear ? ' (' + displayYear + ')' : ''}
                                    </div>
                                    <div class="d-flex gap-3 small">
                                        ${barCode ? `<span><i class="fas fa-barcode me-1"></i>${barCode}</span>` : ''}
                                        ${serialNumber ? `<span><i class="fas fa-hashtag me-1"></i>${serialNumber}</span>` : ''}
                                        <span class="text-${stock > 0 ? 'success' : 'danger'}">
                                            <i class="fas fa-box me-1"></i>Stock: ${stock}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-primary mb-1">$${parseFloat(price).toFixed(2)}</div>
                                    <button class="btn btn-sm btn-primary add-item-btn">
                                        <i class="fas fa-plus me-1"></i>Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    });
                
                    resultsContainer.html(html);
                
                // Update stock info
                updateStockInfo(items);
                },
                error: function(xhr) {
                loadingResults.hide();
                console.error('Search error:', xhr);
                resultsContainer.html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading items. Please try again.
                    </div>
                `);
            }
        });
    }
    
    // Update stock info
    function updateStockInfo(items) {
        let warehouseStock = 0;
        let shopStock = 0;
        
        items.forEach(item => {
            const stock = item.on_hand || 0;
            // You can customize this logic based on your business rules
            warehouseStock += stock * 0.7; // Example: 70% in warehouse
            shopStock += stock * 0.3; // Example: 30% in shop
        });
        
        $('#warehouse-stock').text(Math.round(warehouseStock) + ' Units');
        $('#shop-stock').text(Math.round(shopStock) + ' Units');
    }
    
    // Add item to sales table
    $(document).on('click', '.item-card, .add-item-btn', function(e) {
        e.stopPropagation();
        const card = $(this).closest('.item-card');
        const itemId = card.data('id');
        const itemName = card.data('name');
        const itemPrice = card.data('price');
        const itemStock = card.data('stock');
        
        // Check if item already exists in table
        let exists = false;
        $('#sales-items-body tr').each(function() {
            if ($(this).find('td:first').text().trim() === itemName) {
                exists = true;
                return false;
            }
        });
        
        if (exists) {
            alert('Item already added to the list!');
            return;
        }
        
        // Add to table
        const newRow = `
            <tr>
                <td>${itemName}</td>
                <td><input type="number" value="1" min="1" max="${itemStock}" class="form-control qty" style="width: 80px;"></td>
                <td>$${parseFloat(itemPrice).toFixed(2)}</td>
                <td><input type="number" value="0" class="form-control discount" style="width: 80px;"></td>
                <td><input type="number" value="0" class="form-control tax" style="width: 80px;"></td>
                <td class="tax-amount">$0.00</td>
                <td class="unit-cost">$${parseFloat(itemPrice).toFixed(2)}</td>
                <td class="total-cost">$${parseFloat(itemPrice).toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        // Remove empty row if exists
        $('#sales-items-body tr:first').remove();
            $('#sales-items-body').prepend(newRow);
        
        // Close modal
        modal.modal('hide');
        
        // Clear search
            searchInput.val('');
        clearSearchBtn.addClass('d-none');
    });
    
    // Remove item from table
    $(document).on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        
        // Add empty row if table is empty
        if ($('#sales-items-body tr').length === 0) {
            $('#sales-items-body').html('<tr><td colspan="9" class="text-center text-muted">No items added yet</td></tr>');
        }
    });
    
    // Calculate totals when quantity/discount/tax changes
    $(document).on('input', '.qty, .discount, .tax', function() {
        const row = $(this).closest('tr');
        const qty = parseFloat(row.find('.qty').val()) || 0;
        const price = parseFloat(row.find('td').eq(2).text().replace('$', '')) || 0;
        const discount = parseFloat(row.find('.discount').val()) || 0;
        const taxPercent = parseFloat(row.find('.tax').val()) || 0;
        
        const subtotal = (price * qty) - discount;
        const taxAmount = (subtotal * taxPercent) / 100;
        const total = subtotal + taxAmount;
        
        row.find('.tax-amount').text('$' + taxAmount.toFixed(2));
        row.find('.unit-cost').text('$' + (subtotal / qty).toFixed(2));
        row.find('.total-cost').text('$' + total.toFixed(2));
        });
    });
</script>
@endpush
