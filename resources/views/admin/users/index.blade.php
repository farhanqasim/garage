@extends('layouts.app')
@section('title','All Users')
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h2 class="fw-bold">All Users</h2>
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
                <a href="" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-category"><i
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
                    <table id="searchableTable" class="table table-hover table-center " id="branchTable">
                        <thead class="thead-primary">
                            <tr>
                                <th>#</th>
                                <th>User Name</th>
                                <th>Profile Image</th>
                                <th>Role</th>
                                <th>Branche Name</th>
                                <th>Branche Code</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($users as $key => $user)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $user->name }}</td>
                                <td>
                                    @if ($user->profile_img)
                                    <img src={{ asset($user->profile_img) }} class="rounded" width='50px' height="50px" alt="">
                                    @else
                                       <img src={{ asset('assets/img/profiles/avator1.jpg') }}  class="rounded" width='50px' height="50px" alt="">
                                    @endif
                                </td>
                                <td>
                                  @if ($user->role)
                                    <span class="badge bg-primary">User</span>
                                    @else
                                    <span class="badge bg-info">No role Have</span>
                                  @endif
                                </td>
                                    <td>{{ $user->branches->branch_name ?? 'N/A' }}</td>
                                    <td>{{ $user->branches->branch_code ?? 'N/A' }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->phone ?? 'N/A' }}</td>
                                <td>
                                    @if ($user->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                        <div class="form-group mt-2">

                                            <form action="{{ route('update.employees.status', $user->id) }}" method="POST"
                                                class="status-form">
                                                @csrf
                                                <input type="hidden" name="status" value="{{ $user->status }}">
                                                <div
                                                    class="status-toggle modal-status d-flex justify-content-between align-items-center">
                                                    {{-- <span class="status-label">Status</span> --}}
                                                    <input type="checkbox" id="status-{{ $user->id }}"
                                                        class="check status-checkbox"
                                                        {{ $user->status == 'active' ? 'checked' : '' }}>
                                                    <label for="status-{{ $user->id }}" class="checktoggle"></label>
                                                </div>
                                            </form>
                                        </div>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                     <a class="me-2 p-2" href="#" data-bs-toggle="modal"
                                                data-bs-target="#edit-category{{ $user->id }}">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a href="javascript:void(0)"
                                                onclick="confirmDelete('delete-form-{{ $user->id }}')"
                                                class="p-2 text-danger">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                            <!-- Hidden delete form -->
                                            <form id="delete-form-{{ $user->id }}"
                                                action="{{ route('delete.user', $user->id) }}"
                                                method="POST"
                                                style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">No employees found</td>
                            </tr>
                        @endforelse
                    </tbody>

                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="">
                 {{ $users->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@foreach($users as $item)
    <div class="modal fade" id="edit-category{{ $item->id }}">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="page-title">
                        <h4>Edit User</h4>
                    </div>
                    <button type="button" class="close bg-danger text-white fs-16"
                        data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
              <form action="{{ route('update.user', $item->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" name="name" value="{{ $item->name }}" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" value="{{ $item->email }}" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" name="phone" value="{{ $item->phone }}" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password (Leave blank to keep same)</label>
                                    <input type="password" name="password" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label for="role" class="form-label">Role</label>
                                    <select name="role" class="form-select" required>
                                        <option value="">Select Role</option>
                                        <option value="user" {{ $item->role == 'user' ? 'selected' : '' }}>User</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="profile_img" class="form-label">Profile Image</label>
                                    <input type="file" name="profile_img" class="form-control">
                                    @if($item->profile_img)
                                        <img src="{{ asset($item->profile_img) }}" width="60" class="mt-2 rounded">
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>

            </div>
        </div>
    </div>
    @endforeach


    <div class="modal fade" id="add-category">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="page-title">
                    <h4>Add Employee</h4>
                </div>
                <button type="button" class="close bg-danger text-white fs-16" data-bs-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="{{ route('post.employees') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="Enter full name">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required placeholder="Enter email">
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="Enter phone number">
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="Enter password">
                        </div>

                        <div class="col-md-6">
                            <label for="role" class="form-label">Role</label>
                            <select name="role" id="roleSelect" class="form-select" required>
                                <option value="">Select Role</option>
                                {{-- <option value="user">User</option> --}}
                                <option value="user">User</option>
                                {{-- <option value="customer">Customer</option> --}}
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="profile_img" class="form-label">Profile Image</label>
                            <input type="file" name="profile_img" class="form-control">
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>



@endsection
