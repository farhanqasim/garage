@extends('layouts.app')
@section('title', __('Scales List'))
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h2 class="fw-bold">Scales</h2>
                </div>
            </div>
            <ul class="table-top-head">
                <li>
                 <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img
                            src="{{ asset('assets/img/icons/pdf.svg') }}" alt="img"></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img
                            src="{{ asset('assets/img/icons/excel.svg') }}" alt="img"></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i
                            class="ti ti-chevron-up"></i></a>
                </li>
            </ul>
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
                                <th>Name</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($scales as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>
                                        @if ($item->status === 'active')
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                        <br>
                                        <div class="form-group mt-2">
                                            <form action="{{ route('update.scales.status', $item->id) }}" method="POST"
                                                class="status-form">
                                                @csrf
                                                <input type="hidden" name="status" value="{{ $item->status }}">

                                                <div
                                                    class="status-toggle modal-status d-flex justify-content-between align-items-center">
                                                    {{-- <span class="status-label">Status</span> --}}
                                                    <input type="checkbox" id="status-{{ $item->id }}"
                                                        class="check status-checkbox"
                                                        {{ $item->status == 'active' ? 'checked' : '' }}>
                                                    <label for="status-{{ $item->id }}" class="checktoggle"></label>
                                                </div>
                                            </form>
                                        </div>
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
                                <div class="modal fade" id="edit-category{{ $item->id }}">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <div class="page-title">
                                                    <h4>Edit scales</h4>
                                                </div>
                                                <button type="button" class="close bg-danger text-white fs-16"
                                                    data-bs-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('update.scales', $item->id) }}" method="post"
                                                enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="categoryname" class="col-form-label">Name:</label>
                                                        <input type="text" class="form-control" name="name"
                                                            value="{{ $item->name }}" id="categoryname">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary me-2"
                                                        data-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="">
                    {{ $scales->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="add-category">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="page-title">
                        <h4>Add scales</h4>
                    </div>
                    <button type="button" class="close bg-danger text-white fs-16" data-bs-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('post.scales') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="categoryname" class="col-form-label">Name:</label>
                            <input type="text" class="form-control" name="name" id="categoryname">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary me-2" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
