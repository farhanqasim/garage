@extends('layouts.app')
@section('title','All Employees')
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h2 class="fw-bold">All Employees</h2>
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
                                  @php
                                    $roleLabels = ['employee' => 'Employee', 'worker' => 'Worker', 'user' => 'User', 'manager' => 'Manager', 'salesman' => 'Sales man', 'purchaser' => 'Purchaser'];
                                    $roleBadges = ['employee' => 'bg-success', 'worker' => 'bg-dark', 'user' => 'bg-info', 'manager' => 'bg-primary', 'salesman' => 'bg-secondary', 'purchaser' => 'bg-warning'];
                                    $r = $user->role ?? '';
                                    $label = $roleLabels[$r] ?? ucfirst($r);
                                    $badge = $roleBadges[$r] ?? 'bg-secondary';
                                  @endphp
                                  @if ($r)
                                    <span class="badge {{ $badge }}">{{ $label }}</span>
                                  @else
                                    <span class="badge bg-info">—</span>
                                  @endif
                                </td>
                                <td>{{ $user->branch->branch_name ?? 'N/A' }}</td>
                                <td>{{ $user->branch->branch_code ?? 'N/A' }}</td>
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
                                <td>
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
                    <h4>Edit Employee</h4>
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
                        <!-- Full Name -->
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" name="name" value="{{ $item->name }}" class="form-control" required>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" value="{{ $item->email }}" class="form-control" required>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" name="phone" value="{{ $item->phone }}" class="form-control">
                        </div>

                        <!-- Password -->
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password (Leave blank to keep same)</label>
                            <input type="password" name="password" class="form-control">
                        </div>

                        <!-- Role -->
                        <div class="col-md-4">
                            <label for="role" class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="">Select Role</option>
                                <option value="user" {{ $item->role == 'user' ? 'selected' : '' }}>User</option>
                                <option value="employee" {{ $item->role == 'employee' ? 'selected' : '' }}>Employee</option>
                                <option value="manager" {{ $item->role == 'manager' ? 'selected' : '' }}>Manager</option>
                                <option value="salesman" {{ $item->role == 'salesman' ? 'selected' : '' }}>Sales man</option>
                                <option value="purchaser" {{ $item->role == 'purchaser' ? 'selected' : '' }}>Purchaser</option>
                                <option value="worker" {{ $item->role == 'worker' ? 'selected' : '' }}>Worker</option>
                            </select>
                        </div>

                        <!-- Branch -->
                        <div class="col-md-4 branch-section">
                            <label for="branch_id" class="form-label">Branch</label>
                            <select name="branch_id" class="form-select">
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $item->branch_id == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->branch_name }} ({{ $branch->branch_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Commission % (Worker) -->
                        <div class="col-md-4 commission-edit-wrap" style="display: {{ $item->role === 'worker' ? 'block' : 'none' }};">
                            <label for="commission_edit_{{ $item->id }}" class="form-label">Commission % (Worker)</label>
                            <input type="number" name="commission" id="commission_edit_{{ $item->id }}" class="form-control" min="0" max="100" value="{{ old('commission', $item->commission ?? 0) }}" placeholder="0-100">
                        </div>

                        <!-- Profile Image -->
                        <div class="col-md-4">
                            <label for="profile_img" class="form-label">Profile Image</label>
                            <input type="file" name="profile_img" class="form-control">
                            @if($item->profile_img)
                                <img src="{{ asset($item->profile_img) }}" width="60" class="mt-2 rounded">
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
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
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div class="page-title">
                    <h4>Add User</h4>
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

                        <div class="col-md-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="Enter full name">
                        </div>

                        <div class="col-md-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required placeholder="Enter email">
                        </div>

                        <div class="col-md-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="Enter phone number">
                        </div>

                        <div class="col-md-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="Enter password">
                        </div>

                        <div class="col-md-4">
                            <label for="role" class="form-label">Role</label>
                            <select name="role" id="roleSelect" class="form-select" required>
                                <option value="">Select Role</option>
                                <option value="user">User</option>
                                <option value="employee">Employee</option>
                                <option value="manager">Manager</option>
                                <option value="salesman">Sales man</option>
                                <option value="purchaser">Purchaser</option>
                                <option value="worker">Worker</option>
                            </select>
                        </div>
                        <div class="col-md-4 branch-section" >
                            <label for="branch_id" class="form-label">Branch</label>
                            <select name="branch_id" class="form-select">
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->branch_name }} ({{ $branch->branch_code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4" id="commissionWrapAdd" style="display: none;">
                            <label for="commission" class="form-label">Commission % (Worker)</label>
                            <input type="number" name="commission" id="commissionAdd" class="form-control" min="0" max="100" value="0" placeholder="0-100">
                        </div>

                        <div class="col-md-12">
                            <label for="spatie_role" class="form-label">Permission Role</label>
                            <select name="spatie_role" id="spatie_role" class="form-select">
                                <option value="">None (no permissions)</option>
                                @foreach($spatieRoles ?? [] as $r)
                                    <option value="{{ $r->name }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Assigns permissions. Create roles in Admin → Roles.</small>
                        </div>

                        <div class="col-md-4">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('roleSelect');
    const branchSections = document.querySelectorAll('.branch-section');
    const commissionWrapAdd = document.getElementById('commissionWrapAdd');

    function toggleAddForm() {
        const selectedRole = roleSelect ? roleSelect.value : '';
        branchSections.forEach(function(el) {
            el.style.display = (selectedRole === 'employee' || selectedRole === 'customer' || selectedRole === 'worker') ? 'block' : 'none';
        });
        if (commissionWrapAdd) {
            commissionWrapAdd.style.display = selectedRole === 'worker' ? 'block' : 'none';
        }
    }
    if (roleSelect) {
        roleSelect.addEventListener('change', toggleAddForm);
        toggleAddForm();
    }
    // Edit modals: show Commission % when Role = Worker
    document.querySelectorAll('.modal select[name="role"]').forEach(function(roleSel) {
        roleSel.addEventListener('change', function() {
            var wrap = this.closest('.modal').querySelector('.commission-edit-wrap');
            if (wrap) wrap.style.display = this.value === 'worker' ? 'block' : 'none';
        });
    });
});
</script>

@endsection
