@extends('layouts.app')
@section('title', 'Purchases')
@section('content')
<div class="content">
    <div class="page-header transfer">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4 class="fw-bold">Purchase</h4>
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
        <div class="d-flex purchase-pg-btn">
            <div class="page-btn">
                <a href="{{ route('purchases.create') }}" class="btn btn-primary"><i
                        data-feather="plus-circle" class="me-1"></i>Add Purchase</a>
            </div>
            <div class="page-btn import">
                <a href="#" class="btn btn-secondary color" data-bs-toggle="modal" data-bs-target="#view-notes"><i
                        data-feather="download" class="me-2"></i>Import Purchase</a>
            </div>
        </div>

    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
            <div class="search-set">
              <div class="d-flex justify-content-end mb-3">
                <input type="text" id="tableSearch" class="form-control w-100" placeholder="Search...">
                </div>
            </div>
            <div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                <div class="dropdown">
                    <a href="javascript:void(0);"
                        class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center"
                        data-bs-toggle="dropdown">
                        Payment Status
                    </a>
                    <ul class="dropdown-menu  dropdown-menu-end p-3">
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Paid</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Unpaid</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item rounded-1">Overdue</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="searchableTable" class="table table-hover table-center" >
                    <thead class="thead-light">
                        <tr>
                            <th>Supplier Name</th>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Payment Status</th>
                            <th class="no-sort">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases ?? [] as $purchase)
                        <tr>
                            <td>{{ $purchase->supplier->names[0] ?? 'N/A' }}</td>
                            <td>{{ $purchase->reference ?? '-' }}</td>
                            <td>{{ $purchase->purchase_date->format('d M Y') }}</td>
                            <td>
                                <span class="badges status-badge fs-10 p-1 px-2 rounded-1 
                                    {{ $purchase->status == 'received' ? 'bg-success' : ($purchase->status == 'pending' ? 'bg-warning' : 'bg-info') }}">
                                    {{ ucfirst($purchase->status) }}
                                </span>
                            </td>
                            <td>Rs {{ number_format($purchase->grand_total, 2) }}</td>
                            <td>Rs {{ number_format($purchase->grand_total, 2) }}</td>
                            <td>Rs 0.00</td>
                            <td><span class="p-1 pe-2 rounded-1 text-success bg-success-transparent fs-10"><i
                                        class="ti ti-point-filled me-1 fs-11"></i>Paid</span></td>
                            <td class="action-table-data no-highlight">
                                <div class="edit-delete-action">
                                    <a class="me-2 p-2" href="javascript:void(0);" title="View">
                                        <i data-feather="eye" class="action-eye"></i>
                                    </a>
                                    <a class="me-2 p-2" href="javascript:void(0);" title="Edit">
                                        <i data-feather="edit" class="feather-edit"></i>
                                    </a>
                                    <a href="javascript:void(0);" class="p-2 text-danger" title="Delete">
                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No purchases found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(isset($purchases) && $purchases->hasPages())
        <div class="card-footer">
            {{ $purchases->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

	<!-- Add Purchase -->
	<div class="modal fade" id="add-purchase">
		<div class="modal-dialog purchase modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<div class="page-title">
						<h4>Add Purchase</h4>
					</div>
					<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form action="">
					<div class="modal-body">
						<div class="row">
							<div class="col-lg-4 col-md-6 col-sm-12">
								<div class="mb-3 add-product">
									<label class="form-label">Supplier Name<span class="text-danger ms-1">*</span></label>
									<div class="row">
										<div class="col-lg-10 col-sm-10 col-10">
											<select class="select form-control">
												<option>Select</option>
												<option>Apex Computers</option>
												<option>Dazzle Shoes</option>
												<option>Best Accessories</option>
											</select>
										</div>
										<div class="col-lg-2 col-sm-2 col-2 ps-0">
											<div class="add-icon tab">
												<a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#add_customer"><i data-feather="plus-circle" class="feather-plus-circles"></i></a>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-4 col-md-6 col-sm-12">
								<div class="mb-3">
									<label class="form-label">Date<span class="text-danger ms-1">*</span></label>

									<div class="input-groupicon calender-input">
										<i data-feather="calendar" class="info-img"></i>
										<input type="text" class="datetimepicker form-control p-2" placeholder="dd/mm/yyyy">
									</div>
								</div>
							</div>
							<div class="col-lg-4 col-sm-12">
								<div class="mb-3">
									<label class="form-label">Reference<span class="text-danger ms-1">*</span></label>
									<input type="text" class="form-control">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-12">
								<div class="mb-3">
									<label class="form-label">Product<span class="text-danger ms-1">*</span></label>
									<input type="text" class="form-control" placeholder="Search Product">
								</div>
							</div>
							<div class="col-lg-12">
								<div class="modal-body-table mt-3">
									<div class="table-responsive">
										<table class="table datatable rounded-1">
											<thead>
												<tr>
													<th class="bg-secondary-transparent p-3">Product</th>
													<th class="bg-secondary-transparent p-3">Qty</th>
													<th class="bg-secondary-transparent p-3">Purchase Price($)</th>
													<th class="bg-secondary-transparent p-3">Discount($)</th>
													<th class="bg-secondary-transparent p-3">Tax(%)</th>
													<th class="bg-secondary-transparent p-3">Tax Amount($)</th>
													<th class="bg-secondary-transparent p-3">Unit Cost($)</th>
													<th class="bg-secondary-transparent p-3">Total Cost(%)</th>
												</tr>
											</thead>

											<tbody>
												<tr>
													<td class="p-0"></td>
													<td class="p-0"></td>
													<td class="p-0"></td>
													<td class="p-0"></td>
													<td class="p-0"></td>
													<td class="p-0"></td>
													<td class="p-0"></td>
													<td class="p-0"></td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>

							</div>
							<div class="row">
								<div class="col-lg-3 col-md-6 col-sm-12">
									<div class="mb-3">
										<label class="form-label">Order Tax<span class="text-danger ms-1">*</span></label>
										<input type="text" class="form-control">
									</div>
								</div>
								<div class="col-lg-3 col-md-6 col-sm-12">
									<div class="mb-3">
										<label class="form-label">Discount<span class="text-danger ms-1">*</span></label>
										<input type="text" class="form-control">
									</div>
								</div>
								<div class="col-lg-3 col-md-6 col-sm-12">
									<div class="mb-3">
										<label class="form-label">Shipping<span class="text-danger ms-1">*</span></label>
										<input type="text" class="form-control">
									</div>
								</div>
								<div class="col-lg-3 col-md-6 col-sm-12">
									<div class="mb-3">
										<label class="form-label">Status<span class="text-danger ms-1">*</span></label>
										<select class="select form-control">
											<option>Select</option>
											<option>Received</option>
											<option>Pending</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-12 mt-3">
							<div class="mb-3 summer-description-box">
								<label class="form-label">Description</label>
								<div class="editor pages-editor"></div>
								<p class="mt-1">Maximum 60 Words</p>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn me-2 btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary">Submit</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!-- /Add Purchase -->

	<!-- Add Supplier -->
	<div class="modal fade" id="add_customer">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<div class="page-title">
						<h4>Add Supplier</h4>
					</div>
					<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form action="">
					<div class="modal-body">
						<div>
							<label class="form-label">Supplier<span class="text-danger">*</span></label>
							<input type="text" class="form-control">
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn me-2 btn-secondary fs-13 fw-medium p-2 px-3 shadow-none" data-bs-dismiss="modal">Cancel</button>
						<button  type="submit" class="btn btn-primary fs-13 fw-medium p-2 px-3">Add Supplier</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!-- /Add Supplier -->

	<!-- Edit Purchase -->
	<div class="modal fade" id="edit-purchase">
		<div class="modal-dialog purchase modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<div class="page-title">
						<h4>Edit Purchase</h4>
					</div>
					<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form action="">
					<div class="modal-body">
						<div class="row">
							<div class="col-lg-4 col-md-6 col-sm-12">
								<div class="mb-3 add-product">
									<label class="form-label">Supplier Name<span class="text-danger ms-1">*</span></label>
									<div class="row">
										<div class="col-lg-10 col-sm-10 col-10">
											<select class="select form-control">
												<option>Select</option>
												<option>Apex Computers</option>
												<option>Dazzle Shoes</option>
												<option>Best Accessories</option>
											</select>
										</div>
										<div class="col-lg-2 col-sm-2 col-2 ps-0">
											<div class="add-icon tab">
												<a href="javascript:void(0);"><i data-feather="plus-circle" class="feather-plus-circles"></i></a>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-4 col-md-6 col-sm-12">
								<div class="mb-3">
									<label class="form-label">Date<span class="text-danger ms-1">*</span></label>

									<div class="input-groupicon calender-input">
										<i data-feather="calendar" class="info-img"></i>
										<input type="text" class="datetimepicker form-control p-2" placeholder="24 Dec 2024">
									</div>
								</div>
							</div>
							<div class="col-lg-4 col-sm-12">
								<div class="mb-3">
									<label class="form-label">Supplier<span class="text-danger ms-1">*</span></label>
									<input type="text" class="form-control" value="Elite Retail">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-12">
								<div class="mb-3">
									<label class="form-label">Product<span class="text-danger ms-1">*</span></label>
									<input type="text" class="form-control" placeholder="Search Product">
								</div>
							</div>
							<div class="col-lg-12">
							<div class="modal-body-table">
								<div class="table-responsive">
									<table class="table">
										<thead>
											<tr>
												<th class="bg-secondary-transparent p-3">Product Name</th>
												<th class="bg-secondary-transparent p-3">QTY</th>
												<th class="bg-secondary-transparent p-3">Purchase Price($) </th>
												<th class="bg-secondary-transparent p-3">Discount($) </th>
												<th class="bg-secondary-transparent p-3">Tax %</th>
												<th class="bg-secondary-transparent p-3">Tax Amount($)</th>
												<th class="text-end bg-secondary-transparent p-3">Unit Cost($)</th>
												<th class="text-end bg-secondary-transparent p-3">Total Cost ($) </th>

											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="p-4">
													<div class="d-flex align-items-center">
														<a href="javascript:void(0);" class="avatar avatar-md me-2">
															<img src="{{ asset('assets/img/products/stock-img-02.png') }}" alt="product">
														</a>
														<a href="javascript:void(0);">Nike Jordan</a>
													</div>
												</td>
												<td class="p-4"><div class="product-quantity">
													<span class="quantity-btn">+<i data-feather="plus-circle" class="plus-circle"></i></span>
													<input type="text" class="quntity-input" value="10">
													<span class="quantity-btn"><i data-feather="minus-circle" class="feather-search"></i></span>
												</div></td>
												<td class="p-4">300</td>
												<td class="p-4">50</td>
												<td class="p-4">0</td>
												<td class="p-4">0.00</td>
												<td class="p-4">300</td>
												<td class="p-4">600</td>

											</tr>
										</tbody>
									</table>
								</div>
							</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-12 float-md-right">
								<div class="total-order m-2 mb-3 ms-auto">
									<ul class="border-1 rounded-1">
										<li class="border-0 border-bottom">
											<h4 class="border-0">Order Tax</h4>
											<h5>$ 0.00</h5>
										</li>
										<li class="border-0 border-bottom">
											<h4 class="border-0">Discount</h4>
											<h5>$ 0.00</h5>
										</li>
										<li class="border-0 border-bottom">
											<h4 class="border-0">Shipping</h4>
											<h5>$ 0.00</h5>
										</li>
										<li class="total border-0">
											<h4 class="border-0">Grand Total</h4>
											<h5>$1800.00</h5>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-3 col-md-6 col-sm-12">
								<div class="mb-3">
									<label class="form-label">Order Tax<span class="text-danger ms-1">*</span></label>
									<input type="text" class="form-control" value="0">
								</div>
							</div>
							<div class="col-lg-3 col-md-6 col-sm-12">
								<div class="mb-3">
									<label class="form-label">Discount<span class="text-danger ms-1">*</span></label>
									<input type="text" class="form-control" value="0">
								</div>
							</div>
							<div class="col-lg-3 col-md-6 col-sm-12">
								<div class="mb-3">
									<label class="form-label">Shipping<span class="text-danger ms-1">*</span></label>
									<input type="text" class="form-control" value="0">
								</div>
							</div>
							<div class="col-lg-3 col-md-6 col-sm-12">
								<div class="mb-3">
									<label class="form-label">Status<span class="text-danger ms-1">*</span></label>
									<select class="select">
										<option>Select</option>
										<option>Received</option>
										<option>Pending</option>
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-12">
								<div class="mb-3 summer-description-box">
									<label class="form-label">Description</label>
									<div class="editor pages-editor">
										<p class="mt-1">Maximum 60 Words</p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn me-2 btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary">Save Changes </button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!-- /Edit Purchase -->

	<!-- Import Purchase -->
	<div class="modal fade" id="view-notes">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="page-wrapper-new p-0">
					<div class="content">
						<div class="modal-header">
							<div class="page-title">
								<h4>Import Purchase</h4>
							</div>
							<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<form action="">
							<div class="modal-body">
								<div class="row">
									<div class="col-lg-6 col-sm-6 col-12">
										<div class="mb-3">
											<label class="form-label">Supplier Name<span class="text-danger ms-1">*</span></label>
											<div class="row">
												<div class="col-lg-10 col-sm-10 col-10">
													<select class="select form-control">
														<option>Select</option>
														<option>Apex Computers</option>
														<option>Apex Computers</option>
													</select>
												</div>
												<div class="col-lg-2 col-sm-2 col-2 ps-0">
													<div class="add-icon tab">
														<a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#add_customer"><i data-feather="plus-circle" class="feather-plus-circles"></i></a>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-lg-6 col-sm-6 col-12">
										<div class="mb-3">
											<label class="form-label"> Status<span class="text-danger ms-1">*</span></label>
											<select class="select form-control">
												<option>Select</option>
												<option>Received</option>
												<option>Ordered</option>
												<option>Pending</option>
											</select>
										</div>
									</div>
									<div class="col-lg-12 col-12">
										<div class="row">
											<div >
												<div class="modal-footer-btn download-file">
													<a href="javascript:void(0)" class="btn btn-submit fs-13 fw-medium">Download Sample File</a>
												</div>
											</div>
										</div>
									</div>
									<div class="col-lg-12">
										<div class="mb-3 image-upload-down">
											<label class="form-label">	Upload CSV File</label>
											<div class="image-upload download">
												<input type="file">
												<div class="image-uploads">
													<img src="assets/img/download-img.png" alt="img">
													<h4>Drag and drop a <span>file to upload</span></h4>
												</div>
											</div>
										</div>
									</div>
									<div class="col-lg-4 col-sm-6 col-12">
										<div class="mb-3">
											<label class="form-label">Order Tax<span class="text-danger ms-1">*</span></label>
											<input type="text" class="form-control">
										</div>
									</div>
									<div class="col-lg-4 col-sm-6 col-12">
										<div class="mb-3">
											<label class="form-label">Discount<span class="text-danger ms-1">*</span></label>
											<input type="text" class="form-control">
										</div>
									</div>
									<div class="col-lg-4 col-sm-6 col-12">
										<div class="mb-3">
											<label class="form-label">Shipping<span class="text-danger ms-1">*</span></label>
											<input type="text" class="form-control">
										</div>
									</div>
									<div class="mb-3 summer-description-box transfer">
										<label class="form-label">Description</label>
											<div class="editor pages-editor">
											<p>Maximum 60 Characters</p>
										</div>
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn me-2 btn-secondary" data-bs-dismiss="modal">Cancel</button>
								<button type="submit" class="btn btn-primary">Submit</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /Import Purchase -->
@endsection
