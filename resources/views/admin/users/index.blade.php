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
                <div class="d-flex justify-content-end mb-3 flex-grow-1 me-2">
                    <input type="text" id="tableSearch" class="form-control" placeholder="Search by name, email, phone, branch, role..." style="max-width: 320px;">
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
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto; width: 100%;">
                    <table id="searchableTable" class="table table-hover table-center " id="branchTable" style="min-width: 1200px;">
                        <thead class="thead-primary">
                            <tr>
                                <th>#</th>
                                <th>Employee Name</th>
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
                                    @php
                                        $mainRoleBadges = ['user' => 'bg-info', 'manager' => 'bg-primary', 'salesman' => 'bg-success', 'purchaser' => 'bg-warning'];
                                        $mainRoleLabels = ['user' => 'User', 'manager' => 'Manager', 'salesman' => 'Sales man', 'purchaser' => 'Purchaser'];
                                        $mainRoleClass = $mainRoleBadges[$user->role] ?? 'bg-secondary';
                                        $mainRoleLabel = $mainRoleLabels[$user->role] ?? ucfirst($user->role);
                                    @endphp
                                    <span class="badge {{ $mainRoleClass }}">{{ $mainRoleLabel }}</span>
                                  @else
                                    <span class="badge bg-info">No role Have</span>
                                  @endif
                                </td>
                                <td>
                                    @if($user->branch_id)
                                        @php
                                            $branch = \App\Models\Branch::find($user->branch_id);
                                        @endphp
                                        @if($branch)
                                            <strong>{{ $branch->branch_name }}</strong>
                                        @else
                                            <span class="text-muted">No Branch</span>
                                        @endif
                                    @elseif($user->assignedBranches->count() > 0)
                                        <strong>{{ $user->assignedBranches->first()->branch_name }}</strong>
                                    @else
                                        <span class="text-muted">No Branch</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->branch_id)
                                        @php
                                            $branch = \App\Models\Branch::find($user->branch_id);
                                        @endphp
                                        @if($branch)
                                            <span>{{ $branch->branch_code }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    @elseif($user->assignedBranches->count() > 0)
                                        <span>{{ $user->assignedBranches->first()->branch_code }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
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
                                <div class="col-12">
                                    <label class="form-label">Profile Image</label>
                                    <div class="profile-preview-wrap position-relative w-100 border rounded overflow-hidden bg-light" style="min-height:200px;">
                                        <img id="profile_preview_{{ $item->id }}" src="{{ $item->profile_img ? asset($item->profile_img) : '' }}" class="w-100 h-100 rounded" style="object-fit:cover;min-height:200px;{{ $item->profile_img ? '' : 'display:none' }}" alt="Profile">
                                        <div id="profile_placeholder_{{ $item->id }}" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-muted" style="min-height:200px;{{ $item->profile_img ? 'display:none' : '' }}">No photo</div>
                                        <input type="file" name="profile_img" id="profile_input_{{ $item->id }}" accept="image/*" class="d-none">
                                        <button type="button" id="profile_clear_btn_{{ $item->id }}" class="btn btn-danger btn-sm position-absolute rounded-circle p-0 profile-clear-btn" style="width:28px;height:28px;top:6px;right:6px;line-height:26px;font-size:18px;display:none;z-index:2" title="Remove & choose again" data-preview-id="profile_preview_{{ $item->id }}" data-placeholder-id="profile_placeholder_{{ $item->id }}" data-input-id="profile_input_{{ $item->id }}" data-fallback-src="{{ $item->profile_img ? asset($item->profile_img) : '' }}">&times;</button>
                                    </div>
                                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2 profile-choose-btn" data-input-id="profile_input_{{ $item->id }}" data-preview-id="profile_preview_{{ $item->id }}" data-placeholder-id="profile_placeholder_{{ $item->id }}" data-clear-id="profile_clear_btn_{{ $item->id }}">Choose photo</button>
                                </div>

                                <div class="col-12">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" name="name" value="{{ $item->name }}" class="form-control" required>
                                </div>

                                <div class="col-12">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" value="{{ $item->email }}" class="form-control" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Phone</label>
                                    <p class="text-muted small mb-2">Pehla box: Name (khud likhein). Doosra box: Mobile number. Add New se aur numbers.</p>
                                    <div class="phone-container">
                                        @php
                                            $phoneRows = [];
                                            if ($item->phone) {
                                                foreach (array_filter(array_map('trim', explode(',', $item->phone))) as $part) {
                                                    if (strpos($part, '|') !== false) {
                                                        $phoneRows[] = explode('|', $part, 2);
                                                    } else {
                                                        $phoneRows[] = ['', $part];
                                                    }
                                                }
                                            }
                                            if (empty($phoneRows)) { $phoneRows = [['', '']]; }
                                        @endphp
                                        @foreach($phoneRows as $idx => $pair)
                                        <div class="row g-2 mb-2 align-items-center phone-row">
                                            <div class="col">
                                                <input type="text" name="phone_name[]" value="{{ $pair[0] ?? '' }}" class="form-control" placeholder="Name">
                                            </div>
                                            <div class="col">
                                                <input type="text" name="phone[]" value="{{ $pair[1] ?? '' }}" class="form-control" placeholder="Mobile number">
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="btn btn-outline-danger remove-phone" title="Remove" @if(count($phoneRows) <= 1) style="display:none" @endif>&times;</button>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm mt-1 add-phone-btn">Add New</button>
                                </div>

                                <div class="col-12">
                                    <label for="password" class="form-label">Password (Leave blank to keep same)</label>
                                    <input type="password" name="password" class="form-control">
                                </div>

                                <div class="col-12">
                                    <label for="role" class="form-label">Role</label>
                                    <select name="role" class="form-select" required>
                                        <option value="">Select Role</option>
                                        <option value="user" {{ $item->role == 'user' ? 'selected' : '' }}>User</option>
                                        <option value="employee" {{ $item->role == 'employee' ? 'selected' : '' }}>Employee</option>
                                        <option value="manager" {{ $item->role == 'manager' ? 'selected' : '' }}>Manager</option>
                                        <option value="salesman" {{ $item->role == 'salesman' ? 'selected' : '' }}>Sales man</option>
                                        <option value="purchaser" {{ $item->role == 'purchaser' ? 'selected' : '' }}>Purchaser</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="branch_id" class="form-label">Branch</label>
                                    <select name="branch_id" class="form-select">
                                        <option value="">Select Branch</option>
                                        @foreach($branches ?? [] as $branch)
                                            <option value="{{ $branch->id }}" {{ (isset($item->branch_id) && $item->branch_id == $branch->id) ? 'selected' : '' }}>{{ $branch->branch_name }} ({{ $branch->branch_code ?? '' }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 border-top pt-3 mt-2">
                                    <label class="form-label fw-bold">User ID Card</label>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label small">Front</label>
                                            <input type="file" name="user_id_card_front" accept="image/*" class="form-control form-control-sm edit-user-file-input" data-preview-id="preview_user_id_front_{{ $item->id }}" data-current-link-id="link_user_id_front_{{ $item->id }}">
                                            @if($item->user_id_card_front)
                                                <a href="{{ asset($item->user_id_card_front) }}" target="_blank" class="small d-block mt-1" id="link_user_id_front_{{ $item->id }}">View current</a>
                                            @else
                                                <span id="link_user_id_front_{{ $item->id }}"></span>
                                            @endif
                                            <img id="preview_user_id_front_{{ $item->id }}" src="" class="mt-2 rounded border" style="max-width:100%;max-height:120px;object-fit:contain;display:none" alt="Preview">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Back</label>
                                            <input type="file" name="user_id_card_back" accept="image/*" class="form-control form-control-sm edit-user-file-input" data-preview-id="preview_user_id_back_{{ $item->id }}" data-current-link-id="link_user_id_back_{{ $item->id }}">
                                            @if($item->user_id_card_back)
                                                <a href="{{ asset($item->user_id_card_back) }}" target="_blank" class="small d-block mt-1" id="link_user_id_back_{{ $item->id }}">View current</a>
                                            @else
                                                <span id="link_user_id_back_{{ $item->id }}"></span>
                                            @endif
                                            <img id="preview_user_id_back_{{ $item->id }}" src="" class="mt-2 rounded border" style="max-width:100%;max-height:120px;object-fit:contain;display:none" alt="Preview">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Father Name ID Card</label>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label small">Front</label>
                                            <input type="file" name="father_id_card_front" accept="image/*" class="form-control form-control-sm edit-user-file-input" data-preview-id="preview_father_id_front_{{ $item->id }}" data-current-link-id="link_father_id_front_{{ $item->id }}">
                                            @if(!empty($item->father_id_card_front))
                                                <a href="{{ asset($item->father_id_card_front) }}" target="_blank" class="small d-block mt-1" id="link_father_id_front_{{ $item->id }}">View current</a>
                                            @else
                                                <span id="link_father_id_front_{{ $item->id }}"></span>
                                            @endif
                                            <img id="preview_father_id_front_{{ $item->id }}" src="" class="mt-2 rounded border" style="max-width:100%;max-height:120px;object-fit:contain;display:none" alt="Preview">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Back</label>
                                            <input type="file" name="father_id_card_back" accept="image/*" class="form-control form-control-sm edit-user-file-input" data-preview-id="preview_father_id_back_{{ $item->id }}" data-current-link-id="link_father_id_back_{{ $item->id }}">
                                            @if(!empty($item->father_id_card_back))
                                                <a href="{{ asset($item->father_id_card_back) }}" target="_blank" class="small d-block mt-1" id="link_father_id_back_{{ $item->id }}">View current</a>
                                            @else
                                                <span id="link_father_id_back_{{ $item->id }}"></span>
                                            @endif
                                            <img id="preview_father_id_back_{{ $item->id }}" src="" class="mt-2 rounded border" style="max-width:100%;max-height:120px;object-fit:contain;display:none" alt="Preview">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Google Map Current Location</label>
                                    <div class="input-group">
                                        <input type="text" name="current_location" id="edit_location_{{ $item->id }}" class="form-control" value="{{ old('current_location', $item->current_location ?? '') }}" placeholder="Address or use current location">
                                        <button type="button" class="btn btn-outline-primary btn-edit-location" data-input-id="edit_location_{{ $item->id }}">Current</button>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">House Photo (Front Side View)</label>
                                    <input type="file" name="house_photo_front" accept="image/*" class="form-control edit-user-file-input" data-preview-id="preview_house_photo_{{ $item->id }}" data-current-link-id="link_house_photo_{{ $item->id }}">
                                    @if(!empty($item->house_photo_front))
                                        <a href="{{ asset($item->house_photo_front) }}" target="_blank" class="small d-block mt-1" id="link_house_photo_{{ $item->id }}">View current</a>
                                    @else
                                        <span id="link_house_photo_{{ $item->id }}"></span>
                                    @endif
                                    <img id="preview_house_photo_{{ $item->id }}" src="" class="mt-2 rounded border" style="max-width:100%;max-height:150px;object-fit:contain;display:none" alt="Preview">
                                </div>

                                <div class="col-12">
                                    <label for="credit_limit_{{ $item->id }}" class="form-label fw-bold">Credit Limit</label>
                                    <select name="credit_limit" id="credit_limit_{{ $item->id }}" class="form-select">
                                        <option value="">Select Credit Limit</option>
                                        <option value="0" {{ (isset($item->credit_limit) && (float)$item->credit_limit == 0) ? 'selected' : '' }}>0</option>
                                        <option value="5000" {{ (isset($item->credit_limit) && (float)$item->credit_limit == 5000) ? 'selected' : '' }}>5,000</option>
                                        <option value="10000" {{ (isset($item->credit_limit) && (float)$item->credit_limit == 10000) ? 'selected' : '' }}>10,000</option>
                                        <option value="25000" {{ (isset($item->credit_limit) && (float)$item->credit_limit == 25000) ? 'selected' : '' }}>25,000</option>
                                        <option value="50000" {{ (isset($item->credit_limit) && (float)$item->credit_limit == 50000) ? 'selected' : '' }}>50,000</option>
                                        <option value="100000" {{ (isset($item->credit_limit) && (float)$item->credit_limit == 100000) ? 'selected' : '' }}>1,00,000</option>
                                        <option value="250000" {{ (isset($item->credit_limit) && (float)$item->credit_limit == 250000) ? 'selected' : '' }}>2,50,000</option>
                                        <option value="500000" {{ (isset($item->credit_limit) && (float)$item->credit_limit == 500000) ? 'selected' : '' }}>5,00,000</option>
                                    </select>
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

                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="Enter full name">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required placeholder="Enter email">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Phone</label>
                            <p class="text-muted small mb-2">Pehla box: Name (khud likhein). Doosra box: Mobile number. Add New se aur numbers.</p>
                            <div class="phone-container">
                                <div class="row g-2 mb-2 align-items-center phone-row">
                                    <div class="col">
                                        <input type="text" name="phone_name[]" class="form-control" placeholder="Name">
                                    </div>
                                    <div class="col">
                                        <input type="text" name="phone[]" class="form-control" placeholder="Mobile number">
                                    </div>
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-outline-danger remove-phone" title="Remove" style="display:none">&times;</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-1 add-phone-btn">Add New</button>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="Enter password">
                        </div>

                        <div class="col-md-6">
                            <label for="role" class="form-label">Role</label>
                            <select name="role" id="roleSelect" class="form-select" required>
                                <option value="">Select Role</option>
                                <option value="user">User</option>
                                <option value="manager">Manager</option>
                                <option value="salesman">Sales man</option>
                                <option value="purchaser">Purchaser</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="branch_id" class="form-label">Branch</label>
                            <select name="branch_id" class="form-select">
                                <option value="">Select Branch</option>
                                @foreach($branches ?? [] as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->branch_name }} ({{ $branch->branch_code ?? '' }})</option>
                                @endforeach
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Table search: filter rows by Employee Name, email, phone, branch, role, status
        var searchInput = document.getElementById('tableSearch');
        var table = document.getElementById('searchableTable');
        if (searchInput && table) {
            searchInput.addEventListener('input', function() {
                var term = (this.value || '').trim().toLowerCase();
                var rows = table.querySelectorAll('tbody tr');
                rows.forEach(function(row) {
                    var text = (row.textContent || '').toLowerCase();
                    row.style.display = term === '' || text.indexOf(term) !== -1 ? '' : 'none';
                });
            });
        }

        // Auto-open add modal if URL parameter is set
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('add') === '1') {
            const addModal = new bootstrap.Modal(document.getElementById('add-category'));
            addModal.show();
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Edit User: Profile image – choose photo button
        document.querySelectorAll('.profile-choose-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var inputId = this.getAttribute('data-input-id');
                if (inputId) document.getElementById(inputId).click();
            });
        });

        // Edit User: Profile image – file selected: show only photo + cross button
        document.querySelectorAll('input[id^="profile_input_"]').forEach(function(input) {
            input.addEventListener('change', function() {
                var id = this.id.replace('profile_input_', '');
                var preview = document.getElementById('profile_preview_' + id);
                var placeholder = document.getElementById('profile_placeholder_' + id);
                var clearBtn = document.getElementById('profile_clear_btn_' + id);
                if (!this.files || !this.files[0]) return;
                var file = this.files[0];
                if (!file.type.startsWith('image/')) return;
                var reader = new FileReader();
                reader.onload = function(e) {
                    if (preview) { preview.src = e.target.result; preview.style.display = ''; }
                    if (placeholder) placeholder.style.display = 'none';
                    if (clearBtn) clearBtn.style.display = '';
                };
                reader.readAsDataURL(file);
            });
        });

        // Edit User: Profile image – cross button: clear selection, show current/placeholder again
        document.querySelectorAll('.profile-clear-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var input = document.getElementById(this.getAttribute('data-input-id'));
                var preview = document.getElementById(this.getAttribute('data-preview-id'));
                var placeholder = document.getElementById(this.getAttribute('data-placeholder-id'));
                var fallback = this.getAttribute('data-fallback-src') || '';
                if (input) input.value = '';
                if (preview) {
                    preview.src = fallback;
                    preview.style.display = fallback ? '' : 'none';
                }
                if (placeholder) placeholder.style.display = fallback ? 'none' : '';
                this.style.display = 'none';
            });
        });

        // Edit User: Current location button (Google map current location)
        document.querySelectorAll('.btn-edit-location').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var inputId = this.getAttribute('data-input-id');
                var input = document.getElementById(inputId);
                if (!input) return;
                this.disabled = true;
                this.textContent = 'Getting...';
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by your browser.');
                    this.disabled = false;
                    this.textContent = 'Current';
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        var lat = pos.coords.latitude;
                        var lng = pos.coords.longitude;
                        fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json', { headers: { 'Accept': 'application/json' } })
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                input.value = data.display_name || (lat + ', ' + lng);
                                btn.disabled = false;
                                btn.textContent = 'Current';
                            })
                            .catch(function() {
                                input.value = lat + ', ' + lng;
                                btn.disabled = false;
                                btn.textContent = 'Current';
                            });
                    },
                    function(err) {
                        alert('Could not get location: ' + (err.message || 'Permission denied or unavailable.'));
                        btn.disabled = false;
                        btn.textContent = 'Current';
                    }
                );
            });
        });

        // Phone: Add New (Name + Mobile per row)
        document.querySelectorAll('.add-phone-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var modal = this.closest('.modal');
                var container = modal ? modal.querySelector('.phone-container') : null;
                if (!container) return;
                var rows = container.querySelectorAll('.phone-row');
                var lastRow = rows[rows.length - 1];
                if (!lastRow) return;
                var clone = lastRow.cloneNode(true);
                clone.querySelectorAll('input').forEach(function(inp) { inp.value = ''; });
                container.querySelectorAll('.remove-phone').forEach(function(r) { r.style.display = ''; });
                clone.querySelector('.remove-phone').style.display = '';
                container.appendChild(clone);
            });
        });
        document.querySelectorAll('.phone-container').forEach(function(container) {
            container.addEventListener('click', function(e) {
                var removeBtn = e.target.closest('.remove-phone');
                if (!removeBtn) return;
                var row = removeBtn.closest('.phone-row');
                var rows = container.querySelectorAll('.phone-row');
                if (rows.length <= 1) return;
                row.remove();
                rows = container.querySelectorAll('.phone-row');
                if (rows.length === 1) rows[0].querySelector('.remove-phone').style.display = 'none';
            });
        });
    });
</script>
@endpush

@endsection
