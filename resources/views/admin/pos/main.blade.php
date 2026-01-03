
<!DOCTYPE html>
<html lang="en" data-layout-mode="light_mode">
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

  <title>@yield('title') | {{env('APP_NAME')}}</title>
   <link rel="icon" href="{{ setting_value('favicon', asset('assets/img/favicon.png')) }}" type="image/x-icon"/>
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

  </style>

  @stack('styles')
</head>

	<body class="pos-page">

		<!-- Main Wrapper -->
		<div class="main-wrapper pos-five">

	      @include('admin.pos.includes.header')

		    @include('admin.pos.includes.sidebar')

			<div class="page-wrapper pos-pg-wrapper ms-0">
                <div class="content pos-design p-0">
                @yield('pos_conent')
                @include('admin.pos.includes.footer')
                </div>

			</div>

		</div>
		<!-- /Main Wrapper -->

		<!-- Payment Completed -->
		<div class="modal fade modal-default" id="payment-completed" aria-labelledby="payment-completed">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-body p-0">
						<div class="success-wrap text-center">
							<form action="https://dreamspos.dreamstechnologies.com/html/template/pos.html">
								<div class="icon-success bg-success text-white mb-2">
									<i class="ti ti-check"></i>
								</div>
								<h3 class="mb-2">Payment Completed</h3>
								<p class="mb-3">Do you want to Print Receipt for the Completed Order</p>
								<div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
									<button type="button" class="btn btn-md btn-secondary" data-bs-toggle="modal" data-bs-target="#print-receipt">Print Receipt<i class="feather-arrow-right-circle icon-me-5"></i></button>
									<button type="submit" class="btn btn-md btn-primary">Next Order</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Payment Completed -->

		<!-- Print Receipt -->
		<div class="modal fade modal-default" id="print-receipt" aria-labelledby="print-receipt">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-body">
						<div class="icon-head text-center">
							<a href="javascript:void(0);">
								<img src="assets/img/logo.svg" width="100" height="30" alt="Receipt Logo">
							</a>
						</div>
						<div class="text-center info text-center">
							<h6>Dreamguys Technologies Pvt Ltd.,</h6>
							<p class="mb-0">Phone Number: +1 5656665656</p>
							<p class="mb-0">Email: <a href="https://dreamspos.dreamstechnologies.com/cdn-cgi/l/email-protection#94f1ecf5f9e4f8f1d4f3f9f5fdf8baf7fbf9"><span class="__cf_email__" data-cfemail="a7c2dfc6cad7cbc2e7c0cac6cecb89c4c8ca">[email&#160;protected]</span></a></p>
						</div>
						<div class="tax-invoice">
							<h6 class="text-center">Tax Invoice</h6>
							<div class="row">
								<div class="col-sm-12 col-md-6">
									<div class="invoice-user-name"><span>Name: </span>John Doe</div>
									<div class="invoice-user-name"><span>Invoice No: </span>CS132453</div>
								</div>
								<div class="col-sm-12 col-md-6">
									<div class="invoice-user-name"><span>Customer Id: </span>#LL93784</div>
									<div class="invoice-user-name"><span>Date: </span>01.07.2022</div>
								</div>
							</div>
						</div>
						<table class="table-borderless w-100 table-fit">
							<thead>
								<tr>
									<th># Item</th>
									<th>Price</th>
									<th>Qty</th>
									<th class="text-end">Total</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>1. Red Nike Laser</td>
									<td>$50</td>
									<td>3</td>
									<td class="text-end">$150</td>
								</tr>
								<tr>
									<td>2. Iphone 14</td>
									<td>$50</td>
									<td>2</td>
									<td class="text-end">$100</td>
								</tr>
								<tr>
									<td>3. Apple Series 8</td>
									<td>$50</td>
									<td>3</td>
									<td class="text-end">$150</td>
								</tr>
								<tr>
									<td colspan="4">
										<table class="table-borderless w-100 table-fit">
											<tr>
												<td class="fw-bold">Sub Total :</td>
												<td class="text-end">$700.00</td>
											</tr>
											<tr>
												<td class="fw-bold">Discount :</td>
												<td class="text-end">-$50.00</td>
											</tr>
											<tr>
												<td class="fw-bold">Shipping :</td>
												<td class="text-end">0.00</td>
											</tr>
											<tr>
												<td class="fw-bold">Tax (5%) :</td>
												<td class="text-end">$5.00</td>
											</tr>
											<tr>
												<td class="fw-bold">Total Bill :</td>
												<td class="text-end">$655.00</td>
											</tr>
											<tr>
												<td class="fw-bold">Due :</td>
												<td class="text-end">$0.00</td>
											</tr>
											<tr>
												<td class="fw-bold">Total Payable :</td>
												<td class="text-end">$655.00</td>
											</tr>
										</table>
									</td>
								</tr>
							</tbody>
						</table>
						<div class="text-center invoice-bar">
							<div class="border-bottom border-dashed">
								<p>**VAT against this challan is payable through central registration. Thank you for your business!</p>
							</div>
							<a href="javascript:void(0);">
								<img src="assets/img/barcode/barcode-03.jpg" alt="Barcode">
							</a>
							<p class="text-dark fw-bold">Sale 31</p>
							<p>Thank You For Shopping With Us. Please Come Again</p>
							<a href="javascript:void(0);" class="btn btn-md btn-primary">Print Receipt</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Print Receipt -->

		<!-- Products -->
		<div class="modal fade modal-default pos-modal" id="products" aria-labelledby="products">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header d-flex align-items-center justify-content-between">
						<div class="d-flex align-items-center">
							<h5 class="me-4">Products</h5>
						</div>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="card bg-light mb-3">
							<div class="card-body">
								<div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
									<span class="badge bg-dark fs-12">Order ID : #45698</span>
									<p class="fs-16">Number of Products : 02</p>
								</div>
								<div class="product-wrap h-auto">
									<div class="product-list bg-white align-items-center justify-content-between">
										<div class="d-flex align-items-center product-info" data-bs-toggle="modal" data-bs-target="#products">
											<a href="javascript:void(0);" class="pro-img">
												<img src="assets/img/products/pos-product-16.png" alt="Products">
											</a>
											<div class="info">
												<h6><a href="javascript:void(0);">Red Nike Laser</a></h6>
												<p>Quantity : 04</p>
											</div>
										</div>
										<p class="text-teal fw-bold">$2000</p>
									</div>
									<div class="product-list bg-white align-items-center justify-content-between">
										<div class="d-flex align-items-center product-info" data-bs-toggle="modal" data-bs-target="#products">
											<a href="javascript:void(0);" class="pro-img">
												<img src="assets/img/products/pos-product-17.png" alt="Products">
											</a>
											<div class="info">
												<h6><a href="javascript:void(0);">Iphone 11S</a></h6>
												<p>Quantity : 04</p>
											</div>
										</div>
										<p class="text-teal fw-bold">$3000</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Products -->

		<div class="modal fade" id="create" tabindex="-1" aria-labelledby="create"  aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" >Create</h5>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<form action="https://dreamspos.dreamstechnologies.com/html/template/pos.html">
						<div class="modal-body pb-1">
							<div class="row">
								<div class="col-lg-6 col-sm-12 col-12">
									<div class="mb-3">
										<label class="form-label">Customer Name <span class="text-danger">*</span></label>
										<input type="text" class="form-control">
									</div>
								</div>
								<div class="col-lg-6 col-sm-12 col-12">
									<div class="mb-3">
										<label class="form-label">Phone <span class="text-danger">*</span></label>
										<input type="text" class="form-control">
									</div>
								</div>
								<div class="col-lg-12">
									<div class="mb-3">
										<label class="form-label">Email</label>
										<input type="email" class="form-control">
									</div>
								</div>
								<div class="col-lg-12">
									<div class="mb-3">
										<label class="form-label">Address</label>
										<input type="text" class="form-control">
									</div>
								</div>
								<div class="col-lg-6 col-sm-12 col-12">
									<div class="mb-3">
										<label class="form-label">City</label>
										<input type="text" class="form-control">
									</div>
								</div>
								<div class="col-lg-6 col-sm-12 col-12">
									<div class="mb-3">
										<label class="form-label">Country</label>
										<input type="text" class="form-control">
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer d-flex justify-content-end gap-2 flex-wrap">
							<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-md btn-primary">Submit</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<!-- Hold -->
		<div class="modal fade modal-default pos-modal" id="hold-order" aria-labelledby="hold-order">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Hold order</h5>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<form action="https://dreamspos.dreamstechnologies.com/html/template/pos.html">
						<div class="modal-body">
							<div class="bg-light br-10 p-4 text-center mb-3">
								<h2 class="display-1">4500.00</h2>
							</div>
							<div class="mb-3">
								<label class="form-label">Order Reference <span class="text-danger">*</span></label>
								<input class="form-control" type="text" value="" placeholder="">
							</div>
							<p>The current order will be set on hold. You can retreive this order from the pending order button. Providing a reference to it might help you to identify the order more quickly.</p>
						</div>
						<div class="modal-footer d-flex justify-content-end gap-2">
							<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-md btn-primary">Confirm</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<!-- /Hold -->

		<!-- Edit Product -->
		<div class="modal fade modal-default pos-modal" id="edit-product" aria-labelledby="edit-product">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Edit Product</h5>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<form action="https://dreamspos.dreamstechnologies.com/html/template/pos.html">
						<div class="modal-body pb-1">
							<div class="row">
								<div class="col-lg-12">
									<div class="mb-3">
										<label class="form-label">Product Name <span class="text-danger">*</span></label>
										<input type="text" class="form-control" value="Red Nike Laser Show" disabled>
									</div>
								</div>
								<div class="col-lg-6 col-sm-12 col-12">
									<div class="mb-3">
										<label class="form-label">Product Price <span class="text-danger">*</span></label>
										<div class="input-icon-start position-relative">
											<span class="input-icon-addon text-gray-9">
												<i class="ti ti-currency-dollar"></i>
											</span>
											<input type="text" class="form-control" value="1800">
										</div>
									</div>
								</div>
								<div class="col-lg-6 col-sm-12 col-12">
									<div class="mb-3">
										<label class="form-label">Tax Type <span class="text-danger">*</span></label>
										<select class="select">
											<option>Exclusive</option>
											<option>Inclusive</option>
										</select>
									</div>
								</div>
								<div class="col-lg-6 col-sm-12 col-12">
									<div class="mb-3">
										<label class="form-label">Tax <span class="text-danger">*</span></label>
										<div class="input-icon-start position-relative">
											<span class="input-icon-addon text-gray-9">
												<i class="ti ti-percentage"></i>
											</span>
											<input type="text" class="form-control" value="15">
										</div>
									</div>
								</div>
								<div class="col-lg-6 col-sm-12 col-12">
									<div class="mb-3">
										<label class="form-label">Discount Type <span class="text-danger">*</span></label>
										<select class="select">
											<option>Percentage</option>
											<option>Early payment discounts</option>
										</select>
									</div>
								</div>
								<div class="col-lg-6 col-sm-12 col-12">
									<div class="mb-3">
										<label class="form-label">Discount <span class="text-danger">*</span></label>
										<input type="text" class="form-control" value="15">
									</div>
								</div>
								<div class="col-lg-6 col-sm-12 col-12">
									<div class="mb-3">
										<label class="form-label">Sale Unit <span class="text-danger">*</span></label>
										<select class="select">
											<option>Kilogram</option>
											<option>Grams</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer d-flex justify-content-end flex-wrap gap-2">
							<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">Cancel</button>
						   <button type="submit" class="btn btn-md btn-primary">Submit</button>
					   </div>
					</form>
				</div>
			</div>
		</div>
		<!-- /Edit Product -->

		<!-- Delete Product -->
		<div class="modal fade modal-default" id="delete" aria-labelledby="payment-completed">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-body p-0">
						<div class="success-wrap text-center">
							<form action="https://dreamspos.dreamstechnologies.com/html/template/pos.html">
								<div class="icon-success bg-danger-transparent text-danger mb-2">
									<i class="ti ti-trash"></i>
								</div>
								<h3 class="mb-2">Are you Sure!</h3>
								<p class="fs-16 mb-3">The current order will be deleted as no payment has been made so far.
								</p>
								<div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
									<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">No, Cancel</button>
									<button type="submit" class="btn btn-md btn-primary">Yes, Delete</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Delete Product -->

		<!-- Reset -->
		<div class="modal fade modal-default" id="reset" aria-labelledby="payment-completed">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-body p-0">
						<div class="success-wrap text-center">
							<form action="https://dreamspos.dreamstechnologies.com/html/template/pos.html">
								<div class="icon-success bg-purple-transparent text-purple mb-2">
									<i class="ti ti-transition-top"></i>
								</div>
								<h3 class="mb-2">Confirm Your Action</h3>
								<p class="fs-16 mb-3">The current order will be cleared. But not deleted if it's persistent. Would you like to proceed ?</p>
								<div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
									<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">No, Cancel</button>
									<button type="submit" class="btn btn-md btn-primary">Yes, Proceed</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Reset -->

		<!-- Recent Transactions -->
		<div class="modal fade pos-modal" id="recents" tabindex="-1"    aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						 <h5 class="modal-title" >Recent Transactions</h5>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="tabs-sets">
							<ul class="nav nav-tabs" id="myTab" role="tablist">
								<li class="nav-item" role="presentation">
								  <button class="nav-link active" id="purchase-tab" data-bs-toggle="tab" data-bs-target="#purchase" type="button"   aria-controls="purchase" aria-selected="true" role="tab">Purchase</button>
								</li>
								<li class="nav-item" role="presentation">
								  <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button"   aria-controls="payment" aria-selected="false" role="tab">Payment</button>
								</li>
								<li class="nav-item" role="presentation">
								  <button class="nav-link" id="return-tab" data-bs-toggle="tab" data-bs-target="#return" type="button"   aria-controls="return" aria-selected="false" role="tab">Return</button>
								</li>
							  </ul>
							  <div class="tab-content" >
								<div class="tab-pane fade show active" id="purchase" role="tabpanel" aria-labelledby="purchase-tab">
									<div class="card mb-0">
										<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
											<div class="search-set">
												<div class="search-input">
													<span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
												</div>
											</div>
											<ul class="table-top-head">
												<li>
													<a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="assets/img/icons/pdf.svg" alt="img"></a>
												</li>
												<li>
													<a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="assets/img/icons/excel.svg" alt="img"></a>
												</li>
												<li>
													<a data-bs-toggle="tooltip" data-bs-placement="top" title="Print"><i class="ti ti-printer"></i></a>
												</li>
											</ul>
										</div>
										<div class="card-body">
											<div class="table-responsive">
												<table class="table datatable border">
													<thead class="thead-light">
														<tr>
															<th class="no-sort">
																<label class="checkboxs">
																	<input type="checkbox" class="select-all">
																	<span class="checkmarks"></span>
																</label>
															</th>
															<th>Customer</th>
															<th>Reference</th>
															<th>Date</th>
															<th>Amount	</th>
															<th class="no-sort">Action</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-27.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Carl Evans</a>
																</div>
															</td>
															<td>INV/SL0101</td>
															<td>24 Dec 2024</td>
															<td>$1000</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-02.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Minerva Rameriz</a>
																</div>
															</td>
															<td>INV/SL0102</td>
															<td>10 Dec 2024</td>
															<td>$1500</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-05.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Robert Lamon</a>
																</div>
															</td>
															<td>INV/SL0103</td>
															<td>27 Nov 2024</td>
															<td>$1500</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-22.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Patricia Lewis</a>
																</div>
															</td>
															<td>INV/SL0104</td>
															<td>18 Nov 2024</td>
															<td>$2000</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-03.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Mark Joslyn</a>
																</div>
															</td>
															<td>INV/SL0105</td>
															<td>06 Nov 2024</td>
															<td>$800</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-12.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Marsha Betts</a>
																</div>
															</td>
															<td>INV/SL0106</td>
															<td>25 Oct 2024</td>
															<td>$750</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-06.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Daniel Jude</a>
																</div>
															</td>
															<td>INV/SL0107</td>
															<td>14 Oct 2024</td>
															<td>$1300</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
								<div class="tab-pane fade" id="payment" role="tabpanel" >
									<div class="card mb-0">
										<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
											<div class="search-set">
												<div class="search-input">
													<span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
												</div>
											</div>
											<ul class="table-top-head">
												<li>
													<a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="assets/img/icons/pdf.svg" alt="img"></a>
												</li>
												<li>
													<a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="assets/img/icons/excel.svg" alt="img"></a>
												</li>
												<li>
													<a data-bs-toggle="tooltip" data-bs-placement="top" title="Print"><i class="ti ti-printer"></i></a>
												</li>
											</ul>
										</div>
										<div class="card-body">
											<div class="table-responsive">
												<table class="table datatable border">
													<thead class="thead-light">
														<tr>
															<th class="no-sort">
																<label class="checkboxs">
																	<input type="checkbox" class="select-all">
																	<span class="checkmarks"></span>
																</label>
															</th>
															<th>Customer</th>
															<th>Reference</th>
															<th>Date</th>
															<th>Amount	</th>
															<th class="no-sort">Action</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-27.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Carl Evans</a>
																</div>
															</td>
															<td>INV/SL0101</td>
															<td>24 Dec 2024</td>
															<td>$1000</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-02.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Minerva Rameriz</a>
																</div>
															</td>
															<td>INV/SL0102</td>
															<td>10 Dec 2024</td>
															<td>$1500</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-05.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Robert Lamon</a>
																</div>
															</td>
															<td>INV/SL0103</td>
															<td>27 Nov 2024</td>
															<td>$1500</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-22.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Patricia Lewis</a>
																</div>
															</td>
															<td>INV/SL0104</td>
															<td>18 Nov 2024</td>
															<td>$2000</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-03.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Mark Joslyn</a>
																</div>
															</td>
															<td>INV/SL0105</td>
															<td>06 Nov 2024</td>
															<td>$800</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-12.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Marsha Betts</a>
																</div>
															</td>
															<td>INV/SL0106</td>
															<td>25 Oct 2024</td>
															<td>$750</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-06.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Daniel Jude</a>
																</div>
															</td>
															<td>INV/SL0107</td>
															<td>14 Oct 2024</td>
															<td>$1300</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
								<div class="tab-pane fade" id="return" role="tabpanel" >
									<div class="card mb-0">
										<div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
											<div class="search-set">
												<div class="search-input">
													<span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
												</div>
											</div>
											<ul class="table-top-head">
												<li>
													<a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="assets/img/icons/pdf.svg" alt="img"></a>
												</li>
												<li>
													<a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="assets/img/icons/excel.svg" alt="img"></a>
												</li>
												<li>
													<a data-bs-toggle="tooltip" data-bs-placement="top" title="Print"><i class="ti ti-printer"></i></a>
												</li>
											</ul>
										</div>
										<div class="card-body">
											<div class="table-responsive">
												<table class="table datatable border">
													<thead class="thead-light">
														<tr>
															<th class="no-sort">
																<label class="checkboxs">
																	<input type="checkbox" class="select-all">
																	<span class="checkmarks"></span>
																</label>
															</th>
															<th>Customer</th>
															<th>Reference</th>
															<th>Date</th>
															<th>Amount	</th>
															<th class="no-sort">Action</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-27.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Carl Evans</a>
																</div>
															</td>
															<td>INV/SL0101</td>
															<td>24 Dec 2024</td>
															<td>$1000</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-02.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Minerva Rameriz</a>
																</div>
															</td>
															<td>INV/SL0102</td>
															<td>10 Dec 2024</td>
															<td>$1500</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-05.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Robert Lamon</a>
																</div>
															</td>
															<td>INV/SL0103</td>
															<td>27 Nov 2024</td>
															<td>$1500</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-22.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Patricia Lewis</a>
																</div>
															</td>
															<td>INV/SL0104</td>
															<td>18 Nov 2024</td>
															<td>$2000</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-03.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Mark Joslyn</a>
																</div>
															</td>
															<td>INV/SL0105</td>
															<td>06 Nov 2024</td>
															<td>$800</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-12.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Marsha Betts</a>
																</div>
															</td>
															<td>INV/SL0106</td>
															<td>25 Oct 2024</td>
															<td>$750</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
														<tr>
															<td>
																<label class="checkboxs">
																	<input type="checkbox">
																	<span class="checkmarks"></span>
																</label>
															</td>
															<td>
																<div class="d-flex align-items-center">
																	<a href="javascript:void(0);" class="avatar avatar-md me-2">
																		<img src="assets/img/users/user-06.jpg" alt="product">
																	</a>
																	<a href="javascript:void(0);">Daniel Jude</a>
																</div>
															</td>
															<td>INV/SL0107</td>
															<td>14 Oct 2024</td>
															<td>$1300</td>
															<td class="action-table-data">
																<div class="edit-delete-action">
																	<a class="me-2 edit-icon p-2" href="javascript:void(0);"><i data-feather="eye" class="feather-eye"></i></a>
																	<a class="me-2 p-2" href="javascript:void(0);"><i data-feather="edit" class="feather-edit"></i></a>
																	<a class="p-2" href="javascript:void(0);"><i data-feather="trash-2" class="feather-trash-2"></i></a>
																</div>
															</td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Recent Transactions -->

		<!-- Orders -->
		<div class="modal fade pos-modal" id="orders" tabindex="-1"  aria-hidden="true">
			<div class="modal-dialog modal-md modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						 <h5 class="modal-title" >Orders</h5>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="tabs-sets">
							<ul class="nav nav-tabs" id="myTabs" role="tablist">
								<li class="nav-item" role="presentation">
								  <button class="nav-link active" id="onhold-tab" data-bs-toggle="tab" data-bs-target="#onhold" type="button"   aria-controls="onhold" aria-selected="true" role="tab">Onhold</button>
								</li>
								<li class="nav-item" role="presentation">
								  <button class="nav-link" id="unpaid-tab" data-bs-toggle="tab" data-bs-target="#unpaid" type="button"   aria-controls="unpaid" aria-selected="false" role="tab">Unpaid</button>
								</li>
								<li class="nav-item" role="presentation">
								  <button class="nav-link" id="paid-tab" data-bs-toggle="tab" data-bs-target="#paid" type="button"   aria-controls="paid" aria-selected="false" role="tab">Paid</button>
								</li>
							  </ul>
							  <div class="tab-content" >
								<div class="tab-pane fade show active" id="onhold" role="tabpanel" aria-labelledby="onhold-tab">
									<div class="input-icon-start pos-search position-relative mb-3">
										<span class="input-icon-addon">
											<i class="ti ti-search"></i>
										</span>
										<input type="text" class="form-control" placeholder="Search Product">
									</div>
									<div class="order-body">
										<div class="card bg-light mb-3">
											<div class="card-body">
												<span class="badge bg-dark fs-12 mb-2">Order ID : #45698</span>
												<div class="row g-3">
													<div class="col-md-6">
														<p class="fs-15 mb-1"><span class="fs-14 fw-bold text-gray-9">Cashier :</span> admin</p>
														<p class="fs-15"><span class="fs-14 fw-bold text-gray-9">Total :</span> $900</p>
													</div>
													<div class="col-md-6">
														<p class="fs-15 mb-1"><span class="fs-14 fw-bold text-gray-9">Customer :</span>  Botsford</p>
														<p class="fs-15"><span class="fs-14 fw-bold text-gray-9">Date :</span> 24 Dec 2024 13:39:11</p>
													</div>
												</div>
												<div class="bg-info-transparent p-1 rounded text-center my-3">
													<p class="text-info fw-medium">Customer need to recheck the product once</p>
												</div>
												<div class="d-flex align-items-center justify-content-center flex-wrap gap-2">
													<a href="javascript:void(0);" class="btn btn-md btn-orange">Open Order</a>
													<a href="javascript:void(0);" class="btn btn-md btn-teal" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#products">View Products</a>
													<a href="javascript:void(0);" class="btn btn-md btn-indigo">Print</a>
												</div>
											</div>
										</div>
										<div class="card bg-light mb-0">
											<div class="card-body">
												<span class="badge bg-dark fs-12 mb-2">Order ID : #666659</span>
												<div class="mb-3">
													<div class="row g-3">
														<div class="col-md-6">
															<p class="fs-15 mb-1"><span class="fs-14 fw-bold text-gray-9">Cashier :</span> admin</p>
															<p class="fs-15"><span class="fs-14 fw-bold text-gray-9">Total :</span> $900</p>
														</div>
														<div class="col-md-6">
															<p class="fs-15 mb-1"><span class="fs-14 fw-bold text-gray-9">Customer :</span>  Botsford</p>
															<p class="fs-15"><span class="fs-14 fw-bold text-gray-9">Date :</span> 24 Dec 2024 13:39:11</p>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="tab-pane fade" id="unpaid" role="tabpanel" >
									<div class="input-icon-start pos-search position-relative mb-3">
										<span class="input-icon-addon">
											<i class="ti ti-search"></i>
										</span>
										<input type="text" class="form-control" placeholder="Search Product">
									</div>
									<div class="order-body">
										<div class="card bg-light mb-3">
											<div class="card-body">
												<span class="badge bg-dark fs-12 mb-2">Order ID : #45698</span>
												<div class="row g-3">
													<div class="col-md-6">
														<p class="fs-15 mb-1"><span class="fs-14 fw-bold text-gray-9">Cashier :</span> admin</p>
														<p class="fs-15"><span class="fs-14 fw-bold text-gray-9">Total :</span> $900</p>
													</div>
													<div class="col-md-6">
														<p class="fs-15 mb-1"><span class="fs-14 fw-bold text-gray-9">Customer :</span>  Anastasia</p>
														<p class="fs-15"><span class="fs-14 fw-bold text-gray-9">Date :</span> 24 Dec 2024 13:39:11</p>
													</div>
												</div>
												<div class="bg-info-transparent p-1 rounded text-center my-3">
													<p class="text-info fw-medium">Customer need to recheck the product once</p>
												</div>
												<div class="d-flex align-items-center justify-content-center flex-wrap gap-2">
													<a href="javascript:void(0);" class="btn btn-md btn-orange">Open Order</a>
													<a href="javascript:void(0);" class="btn btn-md btn-teal" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#products">View Products</a>
													<a href="javascript:void(0);" class="btn btn-md btn-indigo">Print</a>
												</div>
											</div>
										</div>
										<div class="card bg-light mb-0">
											<div class="card-body">
												<span class="badge bg-dark fs-12 mb-2">Order ID : #666659</span>
												<div class="row g-3">
													<div class="col-md-6">
														<p class="fs-15 mb-1"><span class="fs-14 fw-bold text-gray-9">Cashier :</span> admin</p>
														<p class="fs-15"><span class="fs-14 fw-bold text-gray-9">Total :</span> $900</p>
													</div>
													<div class="col-md-6">
														<p class="fs-15 mb-1"><span class="fs-14 fw-bold text-gray-9">Customer :</span>  Lucia</p>
														<p class="fs-15"><span class="fs-14 fw-bold text-gray-9">Date :</span> 24 Dec 2024 13:39:11</p>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="tab-pane fade" id="paid" role="tabpanel" >
									<div class="input-icon-start pos-search position-relative mb-3">
										<span class="input-icon-addon">
											<i class="ti ti-search"></i>
										</span>
										<input type="text" class="form-control" placeholder="Search Product">
									</div>
									<div class="order-body">
										<div class="card bg-light mb-3">
											<div class="card-body">
												<span class="badge bg-dark fs-12 mb-2">Order ID : #45698</span>
												<div class="row g-3">
													<div class="col-md-6">
														<p class="fs-15 mb-1"><span class="fs-14 fw-bold text-gray-9">Cashier :</span> admin</p>
														<p class="fs-15"><span class="fs-14 fw-bold text-gray-9">Total :</span> $1000</p>
													</div>
													<div class="col-md-6">
														<p class="fs-15 mb-1"><span class="fs-14 fw-bold text-gray-9">Customer :</span>  Hugo</p>
														<p class="fs-15"><span class="fs-14 fw-bold text-gray-9">Date :</span> 24 Dec 2024 13:39:11</p>
													</div>
												</div>
												<div class="bg-info-transparent p-1 rounded text-center my-3">
													<p class="text-info fw-medium">Customer need to recheck the product once</p>
												</div>
												<div class="d-flex align-items-center justify-content-center flex-wrap gap-2">
													<a href="javascript:void(0);" class="btn btn-md btn-orange">Open Order</a>
													<a href="javascript:void(0);" class="btn btn-md btn-teal" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#products">View Products</a>
													<a href="javascript:void(0);" class="btn btn-md btn-indigo">Print</a>
												</div>
											</div>
										</div>
										<div class="card bg-light mb-0">
											<div class="card-body">
												<span class="badge bg-dark fs-12 mb-2">Order ID : #666659</span>
												<div class="row g-3">
													<div class="col-md-6">
														<p class="fs-15 mb-1"><span class="fs-14 fw-bold text-gray-9">Cashier :</span> admin</p>
														<p class="fs-15"><span class="fs-14 fw-bold text-gray-9">Total :</span> $9100</p>
													</div>
													<div class="col-md-6">
														<p class="fs-15 mb-1"><span class="fs-14 fw-bold text-gray-9">Customer :</span>  Antonio</p>
														<p class="fs-15"><span class="fs-14 fw-bold text-gray-9">Date :</span> 23 Dec 2024 13:39:11</p>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Orders -->

		<!-- Scan -->
		<div class="modal fade modal-default" id="scan-payment">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-body p-0">
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
						<div class="success-wrap scan-wrap text-center">
							<h5><span class="text-gray-6">Amount to Pay :</span> $150</h5>
							<div class="scan-img">
								<img src="assets/img/icons/scan-img.svg" alt="img">
							</div>
							<p class="mb-3">Scan your Phone or UPI App to Complete the payment</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Scan -->



		<!-- Order Tax -->
		<div class="modal fade modal-default" id="order-tax">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Order Tax</h5>
					   <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
						   <span aria-hidden="true">×</span>
					   </button>
				   </div>
				   <form action="https://dreamspos.dreamstechnologies.com/html/template/pos.html">
						<div class="modal-body pb-1">
							<div class="mb-3">
								<label class="form-label">Order Tax <span class="text-danger">*</span></label>
								<select class="select">
									<option>Select</option>
									<option>No Tax</option>
									<option>@10</option>
									<option>@15</option>
									<option>VAT</option>
									<option>SLTAX</option>
								</select>
							</div>
						</div>
						<div class="modal-footer d-flex justify-content-end flex-wrap gap-2">
							<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-md btn-primary">Submit</button>
						</div>
				   </form>
				</div>
			</div>
		</div>
		<!-- /Order Tax -->

		<!-- Shipping Cost -->
		<div class="modal fade modal-default" id="shipping-cost">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Shipping Cost</h5>
					   <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
						   <span aria-hidden="true">×</span>
					   </button>
				   </div>
				   <form action="https://dreamspos.dreamstechnologies.com/html/template/pos.html">
						<div class="modal-body pb-1">
							<div class="mb-3">
								<label class="form-label">Shipping Cost <span class="text-danger">*</span></label>
								<input type="text" class="form-control">
							</div>
						</div>
						<div class="modal-footer d-flex justify-content-end flex-wrap gap-2">
							<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-md btn-primary">Submit</button>
						</div>
				   </form>
				</div>
			</div>
		</div>
		<!-- /Shipping Cost -->

		<!-- Coupon Code -->
		<div class="modal fade modal-default" id="coupon-code">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Coupon Code</h5>
					   <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
						   <span aria-hidden="true">×</span>
					   </button>
				   </div>
				   <form action="https://dreamspos.dreamstechnologies.com/html/template/pos.html">
						<div class="modal-body pb-1">
							<div class="mb-3">
								<label class="form-label">Coupon Code <span class="text-danger">*</span></label>
								<select class="select">
									<option>Select</option>
									<option>NEWYEAR30</option>
									<option>CHRISTMAS100</option>
									<option>HALLOWEEN20</option>
									<option>BLACKFRIDAY50</option>
								</select>
							</div>
						</div>
						<div class="modal-footer d-flex justify-content-end flex-wrap gap-2">
							<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-md btn-primary">Submit</button>
						</div>
				   </form>
				</div>
			</div>
		</div>
		<!-- /Coupon Code -->

		<!-- Discount -->
		<div class="modal fade modal-default" id="discount">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Discount </h5>
					   <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
						   <span aria-hidden="true">×</span>
					   </button>
				   </div>
				   <form action="https://dreamspos.dreamstechnologies.com/html/template/pos.html">
						<div class="modal-body pb-1">
							<div class="mb-3">
								<label class="form-label">Order Discount Type <span class="text-danger">*</span></label>
								<select class="select">
									<option>Select</option>
									<option>Flat</option>
									<option>Percentage</option>
								</select>
							</div>
							<div class="mb-3">
								<label class="form-label">Value <span class="text-danger">*</span></label>
								<input type="text" class="form-control">
							</div>
						</div>
						<div class="modal-footer d-flex justify-content-end flex-wrap gap-2">
							<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-md btn-primary">Submit</button>
						</div>
				   </form>
				</div>
			</div>
		</div>
		<!-- /Discount -->

		<!-- Cash Payment -->
		<div class="modal fade modal-default" id="cash-payment" aria-labelledby="payment-completed">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-body p-0">
						<div class="success-wrap">
							<div class="text-center">
								<div class="icon-success bg-success text-white mb-2">
									<i class="ti ti-check"></i>
								</div>
								<h3 class="mb-2">Congratulations, Sale Completed</h3>
								<div class="p-2 d-flex align-items-center justify-content-center gap-2 flex-wrap mb-3">
									<p class="fs-13 fw-medium pe-2 border-end mb-0">Bill Amount : <span class="text-gray-9">$150</span></p>
									<p class="fs-13 fw-medium pe-2 border-end mb-0">Total Paid : <span class="text-gray-9">$200</span></p>
									<p class="fs-13 fw-medium mb-0">Change : <span class="text-gray-9">$50</span></p>
								</div>
							</div>
							<div class="bg-success-transparent p-2 mb-3 br-5 border-start border-success d-flex align-items-center">
								<span class="avatar avatar-sm bg-success rounded-circle flex-shrink-0 fs-16 me-2">
									<i class="ti ti-mail-opened"></i>
								</span>
								<p class="fs-13 fw-medium text-gray-9">A receipt of this transaction will be sent to the registered info@<a href="https://dreamspos.dreamstechnologies.com/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="c7a4b2b4b3a8aaa2b587a2bfa6aab7aba2e9a4a8aa">[email&#160;protected]</a></p>
							</div>
							<div class="resend-form mb-3">
								<input type="text" class="form-control" value="infocustomer@example.com">
								<button type="submit" class="btn btn-primary btn-xs">Resend Email</button>
							</div>
							<div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
								<button type="button" class="btn btn-md btn-light flex-fill" data-bs-toggle="modal" data-bs-target="#print-receipt"><i class="ti ti-printer me-1"></i>Print Receipt</button>
								<button type="button" class="btn btn-md btn-teal flex-fill"><i class="ti ti-gift me-1"></i>Gift Receipt</button>
								<a href="#" class="btn btn-md btn-dark flex-fill"><i class="ti ti-shopping-cart me-1"></i>Next Order</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Cash Payment -->

		<!-- Card Payment -->
		<div class="modal fade modal-default" id="card-payment">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-body p-0">
						<div class="success-wrap">
							<div class="text-center">
								<div class="icon-success bg-success text-white mb-2">
									<i class="ti ti-check"></i>
								</div>
								<h3 class="mb-2">Congratulations, Sale Completed</h3>
								<div class="p-2 text-center mb-3">
									<p class="fs-13 fw-medium">Bill Amount : <span class="text-gray-9">$150</span></p>
								</div>
							</div>
							<div class="bg-success-transparent p-2 mb-3 br-5 border-start border-success d-flex align-items-center">
								<span class="avatar avatar-sm bg-success rounded-circle flex-shrink-0 fs-16 me-2">
									<i class="ti ti-mail-opened"></i>
								</span>
								<p class="fs-13 fw-medium text-gray-9">A receipt of this transaction will be sent to the registered info@<a href="https://dreamspos.dreamstechnologies.com/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="caa9bfb9bea5a7afb88aafb2aba7baa6afe4a9a5a7">[email&#160;protected]</a></p>
							</div>
							<div class="resend-form mb-3">
								<input type="text" class="form-control" value="infocustomer@example.com">
								<button type="submit" class="btn btn-primary btn-xs">Resend Email</button>
							</div>
							<div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
								<button type="button" class="btn btn-md btn-light flex-fill" data-bs-toggle="modal" data-bs-target="#print-receipt"><i class="ti ti-printer me-1"></i>Print Receipt</button>
								<button type="button" class="btn btn-md btn-teal flex-fill"><i class="ti ti-gift me-1"></i>Gift Receipt</button>
								<a href="#" class="btn btn-md btn-dark flex-fill"><i class="ti ti-shopping-cart me-1"></i>Next Order</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Card Payment -->

		<!-- Active Gift Card -->
		<div class="modal fade pos-modal" id="gift-payment" tabindex="-1"  aria-hidden="true">
			<div class="modal-dialog modal-md modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						 <h5 class="modal-title">Pay By Gift Card</h5>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<div class="modal-body pb-1">
						<div class="mb-3">
							<img src="assets/img/icons/gift-card.svg" alt="img">
						</div>
						<div class="resend-form mb-3">
							<input type="text" class="form-control" placeholder="Scan Barcode / Enter Number">
							<button type="submit" class="btn btn-primary btn-xs" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#redeem-value">Check</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Active Gift Card -->

		<!-- Redeem Value -->
		<div class="modal fade pos-modal" id="redeem-value" tabindex="-1"  aria-hidden="true">
			<div class="modal-dialog modal-md modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						 <h5 class="modal-title">Redeem Value</h5>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<form action="https://dreamspos.dreamstechnologies.com/html/template/pos.html">
						<div class="modal-body">
							<div class="bg-info-transparent p-2 br-8 mb-3">
								<p class="text-info">Balance isn’t enough to pay, you can still make a partial payment</p>
							</div>
							<div class="card bg-light shadow-none text-center">
								<div class="card-body">
									<p class="text-gray-5 mb-1">Gift Card Number</p>
									<h2 class="display-1">5698</h2>
								</div>
							</div>
							<div class="bg-danger-transparent p-2 mb-3 br-5 border-start border-danger d-flex align-items-center">
								<span class="avatar avatar-sm bg-danger rounded-circle fs-16 me-2">
									<i class="ti ti-gift"></i>
								</span>
								<p class="fs-16 text-gray-9">Available gift card balance : $45.56</p>
							</div>
							<div>
								<input type="text" class="form-control" placeholder="Enter Bill Amount">
							</div>
						</div>
						<div class="modal-footer d-flex justify-content-end gap-2 flex-wrap">
							<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-md btn-primary">Make Partial Payment</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<!-- /Redeem Value -->

		<!-- Redeem Value -->
		<div class="modal fade pos-modal" id="redeem-fullpayment" tabindex="-1"  aria-hidden="true">
			<div class="modal-dialog modal-md modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						 <h5 class="modal-title">Redeem Value</h5>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<form action="https://dreamspos.dreamstechnologies.com/html/template/pos.html">
						<div class="modal-body">
							<div class="card bg-light shadow-none text-center">
								<div class="card-body">
									<p class="text-gray-5 mb-1">Gift Card Number</p>
									<h2 class="display-1">5698</h2>
								</div>
							</div>
							<div class="bg-success-transparent p-2 mb-3 br-5 border-start border-success">
								<span class="avatar avatar-sm bg-success rounded-circle fs-16 me-2">
									<i class="ti ti-gift"></i>
								</span>
								<p class="fs-16 text-gray-9">Available gift card balance : $45.56</p>
							</div>
							<div>
								<input type="text" class="form-control" placeholder="Enter Bill Amount">
							</div>
						</div>
						<div class="modal-footer d-flex justify-content-end gap-2 flex-wrap">
							<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-md btn-primary">Make  Payment</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<!-- /Redeem Value -->

		<!-- Barcode -->
		<div class="modal fade pos-modal" id="barcode" tabindex="-1"  aria-hidden="true">
			<div class="modal-dialog modal-md modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						 <h5 class="modal-title">Barcode</h5>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<form action="https://dreamspos.dreamstechnologies.com/html/template/pos.html">
						<div class="modal-body">
							<div class="mb-3">
								<input type="text" class="form-control" placeholder="Enter Barcode of the Product">
							</div>
							<div class="card bg-light shadow-none border-0 br-0 mb-0">
								<div class="card-body d-flex align-items-center justify-content-between">
									<div>
										<p class="fs-13 mb-1">Tablet 1.02 inch</p>
										<h6 class="fs-13 fw-semibold">$3000</h6>
									</div>
									<div class="qty-item m-0">
										<a href="javascript:void(0);" class="dec d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="minus"><i class="ti ti-minus"></i></a>
										<input type="text" class="form-control text-center" name="qty" value="4">
										<a href="javascript:void(0);" class="inc d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="plus"><i class="ti ti-plus"></i></a>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer d-flex justify-content-end gap-2 flex-wrap">
							<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-md btn-primary">Add Item</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<!-- /Barcode -->

		<!-- Split Payment -->
		<div class="modal fade pos-modal" id="split-payment" tabindex="-1"  aria-hidden="true">
			<div class="modal-dialog modal-md modal-dialog-centered modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						 <h5 class="modal-title">Split Payment</h5>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<form action="https://dreamspos.dreamstechnologies.com/html/template/pos.html">
						<div class="modal-body">
							<div class="additem-info">
								<div class="bg-light p-3 add-info">
									<div class="row align-items-center g-2">
										<div class="col-lg-2">
											<h6 class="fs-14 fw-semibold">Payment 2</h6>
										</div>
										<div class="col-lg-4">
											<select class="select">
												<option>Cash</option>
												<option>Card</option>
											</select>
										</div>
										<div class="col-lg-4">
											<input type="text" class="form-control" placeholder="Enter Amount">
										</div>
										<div class="col-lg-2">
											<button class="btn btn-dark w-100">Charge</button>
										</div>
									</div>
								</div>
								<div class="bg-light p-3 add-info">
									<div class="row align-items-center g-2">
										<div class="col-lg-2">
											<h6 class="fs-14 fw-semibold">Payment 2</h6>
										</div>
										<div class="col-lg-4">
											<select class="select">
												<option>Cash</option>
												<option>Card</option>
											</select>
										</div>
										<div class="col-lg-4">
											<input type="text" class="form-control" placeholder="Enter Amount">
										</div>
										<div class="col-lg-2">
											<button class="btn btn-dark w-100">Charge</button>
										</div>
									</div>
								</div>
							</div>
							<div class="text-end">
								<a href="#" class="btn btn-primary btn-md add-item">Add More</a>
							</div>
						</div>
						<div class="modal-footer d-flex justify-content-end gap-2 flex-wrap">
							<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">Cancel</button>
							<a href="#" class="btn btn-md btn-primary" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#cash-payment">Complete Sale</a>
						</div>
					</form>
				</div>
			</div>
		</div>
		<!-- /Split Payment -->

		<!-- Payment Cash -->
		<div class="modal fade modal-default" id="payment-cash">
			<div class="modal-dialog modal-dialog-centered modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Finalize Sale</h5>
					   <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
						   <span aria-hidden="true">×</span>
					   </button>
				   </div>
				   <form action="https://dreamspos.dreamstechnologies.com/html/template/pos-4.html">
						<div class="modal-body pb-1">
							<div class="row">
								<div class="col-md-4">
									<div class="mb-3">
										<label class="form-label">Received Amount <span class="text-danger">*</span></label>
										<div class="input-icon-start position-relative">
											<span class="input-icon-addon text-gray-9">
												<i class="ti ti-currency-dollar"></i>
											</span>
											<input type="text" class="form-control" value="1800">
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="mb-3">
										<label class="form-label">Paying Amount <span class="text-danger">*</span></label>
										<div class="input-icon-start position-relative">
											<span class="input-icon-addon text-gray-9">
												<i class="ti ti-currency-dollar"></i>
											</span>
											<input type="text" class="form-control" value="1800">
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="change-item mb-3">
										<label class="form-label">Change</label>
										<div class="input-icon-start position-relative">
											<span class="input-icon-addon text-gray-9">
												<i class="ti ti-currency-dollar"></i>
											</span>
											<input type="text" class="form-control" value="0.00">
										</div>
									</div>
									<div class="point-item mb-3">
										<label class="form-label">Balance Point</label>
										<input type="text" class="form-control" value="200">
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Payment Type <span class="text-danger">*</span></label>
										<select class="select select-payment">
											<option value="credit">Credit Card</option>
											<option value="cash" selected>Cash</option>
											<option value="cheque">Cheque</option>
											<option value="deposit">Deposit</option>
											<option value="points">Points</option>
										</select>
									</div>
									<div class="quick-cash payment-content bg-light  mb-3">
										<div class="d-flex align-items-center flex-wra gap-4">
											<h5 class="text-nowrap">Quick Cash</h5>
											<div class="d-flex align-items-center flex-wrap gap-3">
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash1" checked>
													<label class="btn btn-white" for="cash1">10</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash2">
													<label class="btn btn-white" for="cash2">20</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash3">
													<label class="btn btn-white" for="cash3">50</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash4">
													<label class="btn btn-white" for="cash4">100</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash5">
													<label class="btn btn-white" for="cash5">500</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash6">
													<label class="btn btn-white" for="cash6">1000</label>
												</div>
											</div>
										</div>
									</div>
									<div class="point-wrap payment-content mb-3">
										<div class=" bg-success-transparent d-flex align-items-center justify-content-between flex-wrap p-2 gap-2 br-5">
											<h6 class="fs-14 fw-bold text-success">You have 2000 Points to Use</h6>
											<a href="javascript:void(0);" class="btn btn-dark btn-md">Use for this Purchase</a>
										</div>
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Payment Receiver</label>
										<input type="text" class="form-control">
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Payment Note</label>
										<textarea class="form-control" rows="3" placeholder="Type your message"></textarea>
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Sale Note</label>
										<textarea class="form-control" rows="3" placeholder="Type your message"></textarea>
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Staff Note</label>
										<textarea class="form-control" rows="3" placeholder="Type your message"></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer d-flex justify-content-end flex-wrap gap-2">
							<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-md btn-primary">Submit</button>
						</div>
				   </form>
				</div>
			</div>
		</div>
		<!-- /Payment Cash  -->

		<!-- Payment Card  -->
		<div class="modal fade modal-default" id="payment-card">
			<div class="modal-dialog modal-dialog-centered modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Finalize Sale</h5>
					   <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
						   <span aria-hidden="true">×</span>
					   </button>
				   </div>
				   <form action="https://dreamspos.dreamstechnologies.com/html/template/pos-4.html">
						<div class="modal-body pb-1">
							<div class="row">
								<div class="col-md-4">
									<div class="mb-3">
										<label class="form-label">Received Amount <span class="text-danger">*</span></label>
										<div class="input-icon-start position-relative">
											<span class="input-icon-addon text-gray-9">
												<i class="ti ti-currency-dollar"></i>
											</span>
											<input type="text" class="form-control" value="1800">
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="mb-3">
										<label class="form-label">Paying Amount <span class="text-danger">*</span></label>
										<div class="input-icon-start position-relative">
											<span class="input-icon-addon text-gray-9">
												<i class="ti ti-currency-dollar"></i>
											</span>
											<input type="text" class="form-control" value="1800">
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="change-item mb-3">
										<label class="form-label">Change</label>
										<div class="input-icon-start position-relative">
											<span class="input-icon-addon text-gray-9">
												<i class="ti ti-currency-dollar"></i>
											</span>
											<input type="text" class="form-control" value="0.00">
										</div>
									</div>
									<div class="point-item mb-3">
										<label class="form-label">Balance Point</label>
										<input type="text" class="form-control" value="200">
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Payment Type <span class="text-danger">*</span></label>
										<select class="select select-payment">
											<option value="credit" selected>Credit Card</option>
											<option value="cash">Cash</option>
											<option value="cheque">Cheque</option>
											<option value="deposit">Deposit</option>
											<option value="points">Points</option>
										</select>
									</div>
									<div class="quick-cash payment-content bg-light  mb-3">
										<div class="d-flex align-items-center flex-wra gap-4">
											<h5 class="text-nowrap">Quick Cash</h5>
											<div class="d-flex align-items-center flex-wrap gap-3">
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash11" checked>
													<label class="btn btn-white" for="cash11">10</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash12">
													<label class="btn btn-white" for="cash12">20</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash13">
													<label class="btn btn-white" for="cash13">50</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash14">
													<label class="btn btn-white" for="cash14">100</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash15">
													<label class="btn btn-white" for="cash15">500</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash16">
													<label class="btn btn-white" for="cash16">1000</label>
												</div>
											</div>
										</div>
									</div>
									<div class="point-wrap payment-content mb-3">
										<div class=" bg-success-transparent d-flex align-items-center justify-content-between flex-wrap p-2 gap-2 br-5">
											<h6 class="fs-14 fw-bold text-success">You have 2000 Points to Use</h6>
											<a href="javascript:void(0);" class="btn btn-dark btn-md">Use for this Purchase</a>
										</div>
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Payment Receiver</label>
										<input type="text" class="form-control">
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Payment Note</label>
										<textarea class="form-control" rows="3" placeholder="Type your message"></textarea>
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Sale Note</label>
										<textarea class="form-control" rows="3" placeholder="Type your message"></textarea>
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Staff Note</label>
										<textarea class="form-control" rows="3" placeholder="Type your message"></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer d-flex justify-content-end flex-wrap gap-2">
							<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-md btn-primary">Submit</button>
						</div>
				   </form>
				</div>
			</div>
		</div>
		<!-- /Payment Card  -->

		<!-- Payment Cheque -->
		<div class="modal fade modal-default" id="payment-cheque">
			<div class="modal-dialog modal-dialog-centered modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Finalize Sale</h5>
					   <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
						   <span aria-hidden="true">×</span>
					   </button>
				   </div>
				   <form action="https://dreamspos.dreamstechnologies.com/html/template/pos-4.html">
						<div class="modal-body pb-1">
							<div class="row">
								<div class="col-md-4">
									<div class="mb-3">
										<label class="form-label">Received Amount <span class="text-danger">*</span></label>
										<div class="input-icon-start position-relative">
											<span class="input-icon-addon text-gray-9">
												<i class="ti ti-currency-dollar"></i>
											</span>
											<input type="text" class="form-control" value="1800">
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="mb-3">
										<label class="form-label">Paying Amount <span class="text-danger">*</span></label>
										<div class="input-icon-start position-relative">
											<span class="input-icon-addon text-gray-9">
												<i class="ti ti-currency-dollar"></i>
											</span>
											<input type="text" class="form-control" value="1800">
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="change-item mb-3">
										<label class="form-label">Change</label>
										<div class="input-icon-start position-relative">
											<span class="input-icon-addon text-gray-9">
												<i class="ti ti-currency-dollar"></i>
											</span>
											<input type="text" class="form-control" value="0.00">
										</div>
									</div>
									<div class="point-item mb-3">
										<label class="form-label">Balance Point</label>
										<input type="text" class="form-control" value="200">
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Payment Type <span class="text-danger">*</span></label>
										<select class="select select-payment">
											<option value="credit">Credit Card</option>
											<option value="cash">Cash</option>
											<option value="cheque" selected>Cheque</option>
											<option value="deposit">Deposit</option>
											<option value="points">Points</option>
										</select>
									</div>
									<div class="quick-cash payment-content bg-light  mb-3">
										<div class="d-flex align-items-center flex-wra gap-4">
											<h5 class="text-nowrap">Quick Cash</h5>
											<div class="d-flex align-items-center flex-wrap gap-3">
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash21" checked>
													<label class="btn btn-white" for="cash21">10</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash22">
													<label class="btn btn-white" for="cash22">20</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash23">
													<label class="btn btn-white" for="cash23">50</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash24">
													<label class="btn btn-white" for="cash24">100</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash25">
													<label class="btn btn-white" for="cash25">500</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash26">
													<label class="btn btn-white" for="cash26">1000</label>
												</div>
											</div>
										</div>
									</div>
									<div class="point-wrap payment-content mb-3">
										<div class=" bg-success-transparent d-flex align-items-center justify-content-between flex-wrap p-2 gap-2 br-5">
											<h6 class="fs-14 fw-bold text-success">You have 2000 Points to Use</h6>
											<a href="javascript:void(0);" class="btn btn-dark btn-md">Use for this Purchase</a>
										</div>
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Payment Receiver</label>
										<input type="text" class="form-control">
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Payment Note</label>
										<textarea class="form-control" rows="3" placeholder="Type your message"></textarea>
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Sale Note</label>
										<textarea class="form-control" rows="3" placeholder="Type your message"></textarea>
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Staff Note</label>
										<textarea class="form-control" rows="3" placeholder="Type your message"></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer d-flex justify-content-end flex-wrap gap-2">
							<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-md btn-primary">Submit</button>
						</div>
				   </form>
				</div>
			</div>
		</div>
		<!-- /Payment Cheque -->

		<!--  Payment Deposit -->
		<div class="modal fade modal-default" id="payment-deposit">
			<div class="modal-dialog modal-dialog-centered modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Finalize Sale</h5>
					   <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
						   <span aria-hidden="true">×</span>
					   </button>
				   </div>
				   <form action="https://dreamspos.dreamstechnologies.com/html/template/pos-4.html">
						<div class="modal-body pb-1">
							<div class="row">
								<div class="col-md-4">
									<div class="mb-3">
										<label class="form-label">Received Amount <span class="text-danger">*</span></label>
										<div class="input-icon-start position-relative">
											<span class="input-icon-addon text-gray-9">
												<i class="ti ti-currency-dollar"></i>
											</span>
											<input type="text" class="form-control" value="1800">
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="mb-3">
										<label class="form-label">Paying Amount <span class="text-danger">*</span></label>
										<div class="input-icon-start position-relative">
											<span class="input-icon-addon text-gray-9">
												<i class="ti ti-currency-dollar"></i>
											</span>
											<input type="text" class="form-control" value="1800">
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="change-item mb-3">
										<label class="form-label">Change</label>
										<div class="input-icon-start position-relative">
											<span class="input-icon-addon text-gray-9">
												<i class="ti ti-currency-dollar"></i>
											</span>
											<input type="text" class="form-control" value="0.00">
										</div>
									</div>
									<div class="point-item mb-3">
										<label class="form-label">Balance Point</label>
										<input type="text" class="form-control" value="200">
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Payment Type <span class="text-danger">*</span></label>
										<select class="select select-payment">
											<option value="credit">Credit Card</option>
											<option value="cash">Cash</option>
											<option value="cheque">Cheque</option>
											<option value="deposit" selected>Deposit</option>
											<option value="points">Points</option>
										</select>
									</div>
									<div class="quick-cash payment-content bg-light  mb-3">
										<div class="d-flex align-items-center flex-wra gap-4">
											<h5 class="text-nowrap">Quick Cash</h5>
											<div class="d-flex align-items-center flex-wrap gap-3">
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash31" checked>
													<label class="btn btn-white" for="cash31">10</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash32">
													<label class="btn btn-white" for="cash32">20</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash33">
													<label class="btn btn-white" for="cash33">50</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash34">
													<label class="btn btn-white" for="cash34">100</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash35">
													<label class="btn btn-white" for="cash35">500</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash36">
													<label class="btn btn-white" for="cash36">1000</label>
												</div>
											</div>
										</div>
									</div>
									<div class="point-wrap payment-content mb-3">
										<div class=" bg-success-transparent d-flex align-items-center justify-content-between flex-wrap p-2 gap-2 br-5">
											<h6 class="fs-14 fw-bold text-success">You have 2000 Points to Use</h6>
											<a href="javascript:void(0);" class="btn btn-dark btn-md">Use for this Purchase</a>
										</div>
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Payment Receiver</label>
										<input type="text" class="form-control">
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Payment Note</label>
										<textarea class="form-control" rows="3" placeholder="Type your message"></textarea>
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Sale Note</label>
										<textarea class="form-control" rows="3" placeholder="Type your message"></textarea>
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Staff Note</label>
										<textarea class="form-control" rows="3" placeholder="Type your message"></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer d-flex justify-content-end flex-wrap gap-2">
							<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-md btn-primary">Submit</button>
						</div>
				   </form>
				</div>
			</div>
		</div>
		<!-- /Payment Deposit -->

		<!-- Payment Point -->
		<div class="modal fade modal-default" id="payment-points">
			<div class="modal-dialog modal-dialog-centered modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Finalize Sale</h5>
					   <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
						   <span aria-hidden="true">×</span>
					   </button>
				   </div>
				   <form action="https://dreamspos.dreamstechnologies.com/html/template/pos-4.html">
						<div class="modal-body pb-1">
							<div class="row">
								<div class="col-md-4">
									<div class="mb-3">
										<label class="form-label">Received Amount <span class="text-danger">*</span></label>
										<div class="input-icon-start position-relative">
											<span class="input-icon-addon text-gray-9">
												<i class="ti ti-currency-dollar"></i>
											</span>
											<input type="text" class="form-control" value="1800">
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="mb-3">
										<label class="form-label">Paying Amount <span class="text-danger">*</span></label>
										<div class="input-icon-start position-relative">
											<span class="input-icon-addon text-gray-9">
												<i class="ti ti-currency-dollar"></i>
											</span>
											<input type="text" class="form-control" value="1800">
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="change-item mb-3">
										<label class="form-label">Change</label>
										<div class="input-icon-start position-relative">
											<span class="input-icon-addon text-gray-9">
												<i class="ti ti-currency-dollar"></i>
											</span>
											<input type="text" class="form-control" value="0.00">
										</div>
									</div>
									<div class="point-item mb-3">
										<label class="form-label">Balance Point</label>
										<input type="text" class="form-control" value="200">
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Payment Type <span class="text-danger">*</span></label>
										<select class="select select-payment">
											<option value="credit">Credit Card</option>
											<option value="cash">Cash</option>
											<option value="cheque">Cheque</option>
											<option value="deposit">Deposit</option>
											<option value="points" selected>Points</option>
										</select>
									</div>
									<div class="quick-cash payment-content bg-light  mb-3">
										<div class="d-flex align-items-center flex-wra gap-4">
											<h5 class="text-nowrap">Quick Cash</h5>
											<div class="d-flex align-items-center flex-wrap gap-3">
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash41" checked>
													<label class="btn btn-white" for="cash41">10</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash42">
													<label class="btn btn-white" for="cash42">20</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash43">
													<label class="btn btn-white" for="cash43">50</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash44">
													<label class="btn btn-white" for="cash44">100</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash45">
													<label class="btn btn-white" for="cash45">500</label>
												</div>
												<div class="form-check">
													<input type="radio" class="btn-check" name="cash" id="cash46">
													<label class="btn btn-white" for="cash46">1000</label>
												</div>
											</div>
										</div>
									</div>
									<div class="point-wrap payment-content mb-3">
										<div class=" bg-success-transparent d-flex align-items-center justify-content-between flex-wrap p-2 gap-2 br-5">
											<h6 class="fs-14 fw-bold text-success">You have 2000 Points to Use</h6>
											<a href="javascript:void(0);" class="btn btn-dark btn-md">Use for this Purchase</a>
										</div>
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Payment Receiver</label>
										<input type="text" class="form-control">
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Payment Note</label>
										<textarea class="form-control" rows="3" placeholder="Type your message"></textarea>
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Sale Note</label>
										<textarea class="form-control" rows="3" placeholder="Type your message"></textarea>
									</div>
								</div>
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">Staff Note</label>
										<textarea class="form-control" rows="3" placeholder="Type your message"></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer d-flex justify-content-end flex-wrap gap-2">
							<button type="button" class="btn btn-md btn-secondary" data-bs-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-md btn-primary">Submit</button>
						</div>
				   </form>
				</div>
			</div>
		</div>
		<!-- /Payment Point -->

		 <!-- Calculator -->
		<div class="modal fade pos-modal" id="calculator" tabindex="-1"  aria-hidden="true">
			<div class="modal-dialog modal-md modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-body p-0">
						<div class="calculator-wrap">
							<div class="p-3">
								<div class="d-flex align-items-center">
									<h3>Calculator</h3>
									<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">×</span>
									</button>
								</div>
								<div>
									<input class="input" type="text" placeholder="0" readonly>
								</div>
							</div>
							<div class="calculator-body d-flex justify-content-between">
								<div class="text-center">
									<button class="btn btn-clear" onclick="if (!window.__cfRLUnblockHandlers) return false; clr()" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">C</button>
									<button class="btn btn-number" onclick="if (!window.__cfRLUnblockHandlers) return false; dis('7')" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">7</button>
									<button class="btn btn-number" onclick="if (!window.__cfRLUnblockHandlers) return false; dis('4')" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">4</button>
									<button class="btn btn-number" onclick="if (!window.__cfRLUnblockHandlers) return false; dis('1')" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">1</button>
									<button class="btn btn-number" onclick="if (!window.__cfRLUnblockHandlers) return false; dis(',')" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">,</button>
								</div>
								<div class="text-center">
									<button class="btn btn-expression" onclick="if (!window.__cfRLUnblockHandlers) return false; dis('https://dreamspos.dreamstechnologies.com/')" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">÷</button>
									<button class="btn btn-number" onclick="if (!window.__cfRLUnblockHandlers) return false; dis('8')" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">8</button>
									<button class="btn btn-number" onclick="if (!window.__cfRLUnblockHandlers) return false; dis('5')" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">5</button>
									<button class="btn btn-number" onclick="if (!window.__cfRLUnblockHandlers) return false; dis('2')" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">2</button>
									<button class="btn btn-number" onclick="if (!window.__cfRLUnblockHandlers) return false; dis('00')" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">00</button>
								</div>
								<div class="text-center">
									<button class="btn btn-expression" onclick="if (!window.__cfRLUnblockHandlers) return false; dis('%')" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">%</button>
									<button class="btn btn-number" onclick="if (!window.__cfRLUnblockHandlers) return false; dis('9')" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">9</button>
									<button class="btn btn-number" onclick="if (!window.__cfRLUnblockHandlers) return false; dis('6')" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">6</button>
									<button class="btn btn-number" onclick="if (!window.__cfRLUnblockHandlers) return false; dis('3')" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">3</button>
									<button class="btn btn-number" onclick="if (!window.__cfRLUnblockHandlers) return false; dis('.')" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">.</button>
								</div>
								<div class="text-center">
									<button class="btn btn-clear" onclick="if (!window.__cfRLUnblockHandlers) return false; back()" data-cf-modified-1208fc3c2b1f20a3615e2fba-=""><i class="ti ti-backspace"></i></button>
									<button class="btn btn-expression" onclick="if (!window.__cfRLUnblockHandlers) return false; dis('*')" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">x</button>
									<button class="btn btn-expression" onclick="if (!window.__cfRLUnblockHandlers) return false; dis('-')" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">-</button>
									<button class="btn btn-expression" onclick="if (!window.__cfRLUnblockHandlers) return false; dis('+')" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">+</button>
									<button class="btn btn-clear" onclick="if (!window.__cfRLUnblockHandlers) return false; solve()" data-cf-modified-1208fc3c2b1f20a3615e2fba-="">=</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Calculator -->

		<!-- Cash Register Details -->
		<div class="modal fade pos-modal" id="cash-register" tabindex="-1"  aria-hidden="true">
			<div class="modal-dialog modal-md modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						 <h5 class="modal-title">Cash Register Details</h5>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="table-responsive">
							<table class="table table-striped border">
								<tr>
									<td>Cash in Hand</td>
									<td class="text-gray-9 fw-medium text-end">$45689</td>
								</tr>
								<tr>
									<td>Total Sale Amount</td>
									<td class="text-gray-9 fw-medium text-end">$565597.88</td>
								</tr>
								<tr>
									<td>Total Payment</td>
									<td class="text-gray-9 fw-medium text-end">$566867.97</td>
								</tr>
								<tr>
									<td>Cash Payment</td>
									<td class="text-gray-9 fw-medium text-end">$3355.84</td>
								</tr>
								<tr>
									<td>Total Sale Return</td>
									<td class="text-gray-9 fw-medium text-end">$1959</td>
								</tr>
								<tr>
									<td>Total Expense</td>
									<td class="text-gray-9 fw-medium text-end">$0</td>
								</tr>
								<tr>
									<td class="text-gray-9 fw-bold bg-secondary-transparent">Total Cash</td>
									<td class="text-gray-9 fw-bold text-end bg-secondary-transparent">$587130.97</td>
								</tr>
							</table>
						</div>
					</div>
					<div class="modal-footer d-flex justify-content-end gap-2 flex-wrap">
						<button type="button" class="btn btn-md btn-primary" data-bs-dismiss="modal">Cancel</button>
					</div>
				</div>
			</div>
		</div>
		<!-- /Cash Register Details -->

		<!-- Today's Sale -->
		<div class="modal fade pos-modal" id="today-sale" tabindex="-1"  aria-hidden="true">
			<div class="modal-dialog modal-md modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						 <h5 class="modal-title">Today's Sale</h5>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="table-responsive">
							<table class="table table-striped border">
								<tr>
									<td>Total Sale Amount</td>
									<td class="text-gray-9 fw-medium text-end">$565597.88</td>
								</tr>
								<tr>
									<td>Cash Payment</td>
									<td class="text-gray-9 fw-medium text-end">$3355.84</td>
								</tr>
								<tr>
									<td>Credit Card Payment</td>
									<td class="text-gray-9 fw-medium text-end">$1959</td>
								</tr>
								<tr>
									<td>Cheque Payment:</td>
									<td class="text-gray-9 fw-medium text-end">$0</td>
								</tr>
								<tr>
									<td>Deposit Payment</td>
									<td class="text-gray-9 fw-medium text-end">$565597.88</td>
								</tr>
								<tr>
									<td>Points Payment</td>
									<td class="text-gray-9 fw-medium text-end">$3355.84</td>
								</tr>
								<tr>
									<td>Gift Card Payment</td>
									<td class="text-gray-9 fw-medium text-end">$565597.88</td>
								</tr>
								<tr>
									<td>Scan & Pay</td>
									<td class="text-gray-9 fw-medium text-end">$3355.84</td>
								</tr>
								<tr>
									<td>Pay Later</td>
									<td class="text-gray-9 fw-medium text-end">$3355.84</td>
								</tr>
								<tr>
									<td>Total Payment</td>
									<td class="text-gray-9 fw-medium text-end">$565597.88</td>
								</tr>
								<tr>
									<td>Total Sale Return</td>
									<td class="text-gray-9 fw-medium text-end">$565597.88</td>
								</tr>
								<tr>
									<td>Total Expense:</td>
									<td class="text-gray-9 fw-medium text-end">$565597.88</td>
								</tr>
								<tr>
									<td class="text-gray-9 fw-bold bg-secondary-transparent">Total Cash</td>
									<td class="text-gray-9 fw-bold text-end bg-secondary-transparent">$587130.97</td>
								</tr>
							</table>
						</div>
					</div>
					<div class="modal-footer d-flex justify-content-end gap-2 flex-wrap">
						<button type="button" class="btn btn-md btn-primary" data-bs-dismiss="modal">Cancel</button>
					</div>
				</div>
			</div>
		</div>
		<!-- /Today's Sale -->

		<!-- Today's Profit -->
		<div class="modal fade pos-modal" id="today-profit" tabindex="-1"  aria-hidden="true">
			<div class="modal-dialog modal-md modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						 <h5 class="modal-title">Today's Profit</h5>
						<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="row justify-content-center g-3 mb-3">
							<div class="col-lg-4 col-md-6 d-flex">
								<div class="border border-success bg-success-transparent br-8 p-3 flex-fill">
									<p class="fs-16 text-gray-9 mb-1">Total Sale</p>
									<h3 class="text-success">$89954</h3>
								</div>
							</div>
							<div class="col-lg-4 col-md-6 d-flex">
								<div class="border border-danger bg-danger-transparent br-8 p-3 flex-fill">
									<p class="fs-16 text-gray-9 mb-1">Expense</p>
									<h3 class="text-danger">$89954</h3>
								</div>
							</div>
							<div class="col-lg-4 col-md-6 d-flex">
								<div class="border border-info bg-info-transparent br-8 p-3 flex-fill">
									<p class="fs-16 text-gray-9 mb-1">Total Profit	</p>
									<h3 class="text-info">$2145</h3>
								</div>
							</div>
						</div>
						<div class="table-responsive">
							<table class="table table-striped border">
								<tr>
									<td>Product Revenue</td>
									<td class="text-gray-9 fw-medium text-end">$565597.88</td>
								</tr>
								<tr>
									<td>Product Cost</td>
									<td class="text-gray-9 fw-medium text-end">$3355.84</td>
								</tr>
								<tr>
									<td>Expense</td>
									<td class="text-gray-9 fw-medium text-end">$1959</td>
								</tr>
								<tr>
									<td>Total Stock Adjustment</td>
									<td class="text-gray-9 fw-medium text-end">$0</td>
								</tr>
								<tr>
									<td>Deposit Payment</td>
									<td class="text-gray-9 fw-medium text-end">$565597.88</td>
								</tr>
								<tr>
									<td>Total Purchase Shipping Cost</td>
									<td class="text-gray-9 fw-medium text-end">$3355.84</td>
								</tr>
								<tr>
									<td>Total Sell Discount</td>
									<td class="text-gray-9 fw-medium text-end">$565597.88</td>
								</tr>
								<tr>
									<td>Total Sell Return</td>
									<td class="text-gray-9 fw-medium text-end">$3355.84</td>
								</tr>
								<tr>
									<td>Closing Stock</td>
									<td class="text-gray-9 fw-medium text-end">$3355.84</td>
								</tr>
								<tr>
									<td>Total Sales</td>
									<td class="text-gray-9 fw-medium text-end">$565597.88</td>
								</tr>
								<tr>
									<td>Total Sale Return</td>
									<td class="text-gray-9 fw-medium text-end">$565597.88</td>
								</tr>
								<tr>
									<td>Total Expense</td>
									<td class="text-gray-9 fw-medium text-end">$565597.88</td>
								</tr>
								<tr>
									<td class="text-gray-9 fw-bold bg-secondary-transparent">Total Cash</td>
									<td class="text-gray-9 fw-bold text-end bg-secondary-transparent">$587130.97</td>
								</tr>
							</table>
						</div>
					</div>
					<div class="modal-footer d-flex justify-content-end gap-2 flex-wrap">
						<button type="button" class="btn btn-md btn-primary" data-bs-dismiss="modal">Cancel</button>
					</div>
				</div>
			</div>
		</div>
		<!-- /Today's Profit -->

{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
    <script src="https://unpkg.com/feather-icons"></script>
  <!-- jQuery -->
  <script src="{{asset('assets/js/jquery-3.7.1.min.js')}}" ></script>

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
<script src="{{ asset('assets/js/custom-select2.js') }}" ></script>
  <!-- Custom JS -->
  <script src="{{asset('assets/js/theme-colorpicker.js')}}" type="f89f8e290dd47aa8bc06c7c9-text/javascript"></script>
  <script src="{{asset('assets/js/script.js')}}" type="f89f8e290dd47aa8bc06c7c9-text/javascript"></script>

  <script src="{{asset('assets/cdn-cgi/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js')}}" data-cf-settings="f89f8e290dd47aa8bc06c7c9-|49" defer>
  </script>

 	<script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015" integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ==" data-cf-beacon='{"version":"2024.11.0","token":"3ca157e612a14eccbb30cf6db6691c29","server_timing":{"name":{"cfCacheStatus":true,"cfEdge":true,"cfExtPri":true,"cfL4":true,"cfOrigin":true,"cfSpeedBrain":true},"location_startswith":null}}' crossorigin="anonymous"></script>
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
function confirmDelete(formId, customMessage = null) {
    const message = customMessage;
    Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const deleteForm = document.getElementById(formId);
            if (deleteForm) {
                deleteForm.submit();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Delete form not found.'
                });
            }
        }
    });
}
</script>

@stack('scripts')
<script>

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

  <!-- ✅ Laravel Flash Message Integration -->
  <script>
  @if (session('success'))
      toastr.success("{{ session('success') }}");
  @endif

  @if (session('error'))
      toastr.error("{{ session('error') }}");
  @endif

  @if (session('warning'))
      toastr.warning("{{ session('warning') }}");
  @endif

  @if (session('info'))
      toastr.info("{{ session('info') }}");
  @endif



  </script>
  <script>
    $(document).ajaxSuccess(function(event, xhr) {
        try {
            let response = xhr.responseJSON;
            if (!response) return;

            if (response.success === true) {
                toastr.success(response.message ?? "Saved successfully!");
            } else if (response.success === false) {
                toastr.error(response.message ?? "Something went wrong!");
            }
        } catch (e) {
            console.log("Not JSON response");
        }
    });

    $(document).ajaxError(function(event, xhr) {
        toastr.error("Server Error! Please try again.");
    });
</script>


  <!-- ✅ Example: Auto Toast on Form Submit -->
  <script>
        $('.searchable-select').select2({
    width: '100%',
    placeholder: 'Please Select',
    allowClear: true
});
  $(document).ready(function() {
      $('#stockForm').on('submit', function(e) {
          e.preventDefault(); // prevent real submission for demo
          $('#add-stock').modal('hide'); // close modal
          toastr.success("Stock added successfully!", "Success");
      });
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

        // Excel Export
        document.querySelector('.export-excel').addEventListener('click', function() {
            let table = document.getElementById('searchableTable');
            let wb = XLSX.utils.table_to_book(table, {sheet:"Units"});
            XLSX.writeFile(wb, "units.xlsx");
        });
        // PDF Export
        document.querySelector('.export-pdf').addEventListener('click', function() {
            let table = document.getElementById('searchableTable');

            let opt = {
                margin: 0.5,
                filename: 'units.pdf',
                image: { type: 'jpeg', quality: 0.95 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'A4', orientation: 'portrait' }
            };

            html2pdf().from(table).set(opt).save();
        });

</script>


</body>

</html>
