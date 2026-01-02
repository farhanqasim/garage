@extends('layouts.app')
@section('title', __('Vehical List'))
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h2 class="fw-bold">Vehical</h2>
                </div>
            </div>
            <div class="page-btn">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-category"><i
                        class="ti ti-circle-plus me-1"></i>Add</a>
            </div>
        </div>
        <!-- /product list -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <div class="d-flex justify-content-end mb-3">
                    <input type="text" id="tableSearch" class="form-control w-100" placeholder="Search...">
                </div>
                <div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                    <div class="dropdown">
                        <a href="javascript:void(0);"
                            class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center"
                            data-bs-toggle="dropdown">
                            Status
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Active</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Inactive</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="searchableTable" class="table table-hover table-center " id="categoryTable">
                        <thead class="thead-primary">
                            <tr>
                                <th>#</th>
                                <th>Model</th>
                                <th>Country</th>
                                <th>Manufactured</th>
                                <th>Year</th>
                                <th>Engine CC</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($all_vehicals as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->car_model_name }}</td>
                                    <td>{{ $item->car_manufactured_country }}</td>
                                    <td>{{ $item->car_manufacturer }}</td>
                                    <td>{{ $item->carmanufactured_year }}</td>
                                    <td>{{ $item->engine_cc }}</td>
                                    <td>
                                        @if ($item->status === 'active')
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif

                                    </td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="#" data-bs-toggle="modal"
                                                data-bs-target="#edit-category{{ $item->id }}">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="p-2" href="{{ route('delete.scales', $item->id) }}">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="">
                    {{ $all_vehicals->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

@endsection
