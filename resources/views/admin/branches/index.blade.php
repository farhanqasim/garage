{{-- resources/views/admin/branches/index.blade.php --}}
@extends('layouts.app')
@section('title', __('Branches List'))
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h2 class="fw-bold">Branches</h2>
                </div>
            </div>

         
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
                                <th>Branch Name</th>
                                <th>Branch Code</th>
                                <th>Manager Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($branches as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 + (($branches->currentPage() - 1) * $branches->perPage()) }}</td>
                                    <td>
                                        <div>
                                            <strong>{{ $item->user->name ?? 'N/A' }}</strong>
                                            <span class="badge badge-primary ms-1">Owner</span>
                                        </div>
                                        @if($item->users->count() > 0)
                                            <div class="mt-2">
                                                @foreach($item->users->take(3) as $assignedUser)
                                                    @php
                                                        $userRole = $assignedUser->pivot->role ?? 'staff';
                                                        $roleBadges = [
                                                            'manager' => 'badge-primary',
                                                            'staff' => 'badge-info',
                                                            'worker' => 'badge-warning',
                                                            'other' => 'badge-secondary'
                                                        ];
                                                        $roleColor = $roleBadges[$userRole] ?? 'badge-secondary';
                                                    @endphp
                                                    <div class="small mb-1">
                                                        {{ $assignedUser->name }}
                                                        <span class="badge {{ $roleColor }}">{{ ucfirst($userRole) }}</span>
                                                    </div>
                                                @endforeach
                                                @if($item->users->count() > 3)
                                                    <small class="text-muted">+{{ $item->users->count() - 3 }} more</small>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $item->branch_name }}</td>
                                    <td>{{ $item->branch_code }}</td>
                                    <td>{{ $item->manager_name ?? 'N/A' }}</td>
                                    <td>{{ $item->email ?? 'N/A' }}</td>
                                    <td>{{ $item->phone ?? 'N/A' }}</td>
                                    <td>
                                       @if ($item->status === 'active')
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                        <br>
                                        <div class="form-group mt-2">

                                            <form action="{{ route('update.branch.status', $item->id) }}" method="POST"
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
                                                data-bs-target="#edit-category{{ $item->id }}" title="Edit Branch">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            @if(auth()->user()->role === 'admin')
                                            <a class="me-2 p-2 text-primary" href="#" data-bs-toggle="modal"
                                                data-bs-target="#assign-users-modal{{ $item->id }}" title="Assign Users">
                                                <i data-feather="users" class="feather-users"></i>
                                            </a>
                                            @endif
                                            <a href="javascript:void(0)"
                                                onclick="confirmDelete('delete-form-{{ $item->id }}')"
                                                class="p-2 text-danger" title="Delete">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                            <!-- Hidden delete form -->
                                            <form id="delete-form-{{ $item->id }}"
                                                action="{{ route('branch.delete', $item->id) }}"
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
                                    <td colspan="9" class="text-center">No branches found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="">
                    {{ $branches->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modals: Generate one for each branch outside the loop --}}
    @foreach($branches as $item)
    <div class="modal fade" id="edit-category{{ $item->id }}">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="page-title">
                        <h4>Edit Branch</h4>
                    </div>
                    <button type="button" class="close bg-danger text-white fs-16"
                        data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('branch.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row ">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="user_id" class="col-form-label">Select User <span class="text-danger">*</span>:</label>
                                    <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                        @if(auth()->user()->role === 'admin')
                                            <option value="">-- Select User --</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ old('user_id', $item->user_id) == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} ({{ $user->email }})
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="{{ auth()->user()->id }}" {{ auth()->user()->id == $item->user_id ? 'selected' : '' }}>
                                                {{ auth()->user()->name }} ({{ auth()->user()->email }})
                                            </option>
                                        @endif
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="branch_name" class="col-form-label">Branch Name <span class="text-danger">*</span>:</label>
                                    <input type="text" class="form-control @error('branch_name') is-invalid @enderror" name="branch_name" id="branch_name" value="{{ old('branch_name', $item->branch_name) }}" required>
                                    @error('branch_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="branch_code" class="col-form-label">Branch Code <span class="text-danger">*</span>:</label>
                                    <input type="text" class="form-control @error('branch_code') is-invalid @enderror" name="branch_code" id="branch_code" value="{{ old('branch_code', $item->branch_code) }}" required>
                                    @error('branch_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="manager_name" class="col-form-label">Manager Name:</label>
                                    <input type="text" class="form-control @error('manager_name') is-invalid @enderror" name="manager_name" id="manager_name" value="{{ old('manager_name', $item->manager_name) }}">
                                    @error('manager_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="email" class="col-form-label">Email:</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" value="{{ old('email', $item->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="phone" class="col-form-label">Phone:</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone" value="{{ old('phone', $item->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="address" class="col-form-label">Address:</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror" name="address" id="address" value="{{ old('address', $item->address) }}">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="city" class="col-form-label">City:</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" id="city" value="{{ old('city', $item->city) }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="state" class="col-form-label">State:</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" name="state" id="state" value="{{ old('state', $item->state) }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country" class="col-form-label">Country:</label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror" name="country" id="country" value="{{ old('country', $item->country ?? 'Pakistan') }}">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location" class="col-form-label">Location:</label>
                                    <input type="text" class="form-control @error('location') is-invalid @enderror" name="location" id="location" value="{{ old('location', $item->location) }}">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @if(auth()->user()->role === 'admin')
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="col-form-label fw-bold">Assign Additional Users to this Branch:</label>
                                    <div class="p-2 bg-light rounded mb-2">
                                        <small class="text-muted">
                                            <i class="ti ti-info-circle me-1"></i>
                                            Owner: <strong>{{ $item->user->name ?? 'N/A' }}</strong>
                                            @if($item->users->count() > 0)
                                                | Currently assigned: <strong>{{ $item->users->count() }} user(s)</strong>
                                            @endif
                                        </small>
                                    </div>
                                    <select name="assigned_user_ids[]" id="assigned_user_ids{{ $item->id }}" class="form-control @error('assigned_user_ids') is-invalid @enderror" multiple size="5" onchange="updateEditRoleSelects{{ $item->id }}()">
                                        @foreach($users as $user)
                                            @if($user->id != $item->user_id)
                                                <option value="{{ $user->id }}" 
                                                    {{ $item->users->contains($user->id) ? 'selected' : '' }}>
                                                    {{ $user->name }} ({{ $user->email }})
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple users. These users will be able to access this branch.</small>
                                    @error('assigned_user_ids')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="mt-3" id="edit-role-selects-container{{ $item->id }}">
                                        <label class="form-label fw-bold small">Assign Roles:</label>
                                        <div id="edit-role-selects{{ $item->id }}"></div>
                                    </div>
                                    <script>
                                        function updateEditRoleSelects{{ $item->id }}() {
                                            const select = document.getElementById('assigned_user_ids{{ $item->id }}');
                                            const roleContainer = document.getElementById('edit-role-selects{{ $item->id }}');
                                            const selectedOptions = Array.from(select.selectedOptions);
                                            
                                            roleContainer.innerHTML = '';
                                            
                                            selectedOptions.forEach((option, index) => {
                                                const userId = option.value;
                                                const userName = option.text.split('(')[0].trim();
                                                const currentRole = @json($item->users->pluck('pivot.role', 'id')->toArray())[userId] || 'staff';
                                                
                                                const div = document.createElement('div');
                                                div.className = 'mb-2';
                                                div.innerHTML = `
                                                    <label class="form-label small">${userName}:</label>
                                                    <select name="assigned_user_roles[]" class="form-control form-control-sm">
                                                        <option value="manager" ${currentRole === 'manager' ? 'selected' : ''}>Manager</option>
                                                        <option value="staff" ${currentRole === 'staff' ? 'selected' : ''}>Staff</option>
                                                        <option value="worker" ${currentRole === 'worker' ? 'selected' : ''}>Worker</option>
                                                        <option value="other" ${currentRole === 'other' ? 'selected' : ''}>Other</option>
                                                    </select>
                                                `;
                                                roleContainer.appendChild(div);
                                            });
                                        }
                                        
                                        // Initialize on page load
                                        document.addEventListener('DOMContentLoaded', function() {
                                            updateEditRoleSelects{{ $item->id }}();
                                        });
                                    </script>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Assign Users Modal for each branch --}}
    @foreach($branches as $item)
    @if(auth()->user()->role === 'admin')
    <div class="modal fade" id="assign-users-modal{{ $item->id }}">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="page-title">
                        <h4>Assign Users to Branch: {{ $item->branch_name }}</h4>
                    </div>
                    <button type="button" class="close bg-danger text-white fs-16" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('branch.assign.users', $item->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Owner:</label>
                            <div class="p-2 bg-light rounded">
                                <i class="ti ti-user me-2"></i>{{ $item->user->name ?? 'N/A' }} <small class="text-muted">({{ $item->user->email ?? 'N/A' }})</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Currently Assigned Users:</label>
                            @if($item->users->count() > 0)
                                <div class="list-group">
                                    @foreach($item->users as $assignedUser)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="ti ti-user me-2"></i>{{ $assignedUser->name }}
                                                <small class="text-muted">({{ $assignedUser->email }})</small>
                                                @php
                                                    $userRole = $assignedUser->pivot->role ?? 'staff';
                                                    $roleBadges = [
                                                        'manager' => 'badge-primary',
                                                        'staff' => 'badge-info',
                                                        'worker' => 'badge-warning',
                                                        'other' => 'badge-secondary'
                                                    ];
                                                    $roleColors = $roleBadges[$userRole] ?? 'badge-secondary';
                                                @endphp
                                                <span class="badge {{ $roleColors }} ms-2">{{ ucfirst($userRole) }}</span>
                                            </div>
                                            <a href="{{ route('branch.remove.user', ['branchId' => $item->id, 'userId' => $assignedUser->id]) }}" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to remove this user from the branch?')">
                                                <i data-feather="x" class="feather-sm"></i> Remove
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">No users assigned yet.</p>
                            @endif
                        </div>

                        <div class="form-group mb-3">
                            <label for="user_ids{{ $item->id }}" class="form-label fw-bold">Select Users to Assign:</label>
                            <select name="user_ids[]" id="user_ids{{ $item->id }}" class="form-control @error('user_ids') is-invalid @enderror" multiple size="6" onchange="updateRoleSelects{{ $item->id }}()" required>
                                @foreach($users as $user)
                                    @if($user->id != $item->user_id)
                                        <option value="{{ $user->id }}" 
                                            {{ $item->users->contains($user->id) ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple users.</small>
                            @error('user_ids')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group" id="role-selects-container{{ $item->id }}">
                            <label class="form-label fw-bold">Assign Roles:</label>
                            <div id="role-selects{{ $item->id }}"></div>
                            <small class="form-text text-muted">Select a role for each user. Default role is Staff.</small>
                            @error('user_roles')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <script>
                            function updateRoleSelects{{ $item->id }}() {
                                const select = document.getElementById('user_ids{{ $item->id }}');
                                const roleContainer = document.getElementById('role-selects{{ $item->id }}');
                                const selectedOptions = Array.from(select.selectedOptions);
                                
                                roleContainer.innerHTML = '';
                                
                                selectedOptions.forEach((option, index) => {
                                    const userId = option.value;
                                    const userName = option.text.split('(')[0].trim();
                                    const currentRole = @json($item->users->pluck('pivot.role', 'id')->toArray())[userId] || 'staff';
                                    
                                    const div = document.createElement('div');
                                    div.className = 'mb-2';
                                    div.innerHTML = `
                                        <label class="form-label small">${userName}:</label>
                                        <select name="user_roles[]" class="form-control form-control-sm">
                                            <option value="manager" ${currentRole === 'manager' ? 'selected' : ''}>Manager</option>
                                            <option value="staff" ${currentRole === 'staff' ? 'selected' : ''}>Staff</option>
                                            <option value="worker" ${currentRole === 'worker' ? 'selected' : ''}>Worker</option>
                                            <option value="other" ${currentRole === 'other' ? 'selected' : ''}>Other</option>
                                        </select>
                                    `;
                                    roleContainer.appendChild(div);
                                });
                            }
                            
                            // Initialize on page load
                            document.addEventListener('DOMContentLoaded', function() {
                                updateRoleSelects{{ $item->id }}();
                            });
                        </script>
                    </div>
                    <div class="modal-footer">
                        <a href="" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</a>
                        <button type="submit" class="btn btn-primary">Assign Users</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endforeach

    <div class="modal fade" id="add-category">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="page-title">
                        <h4>Add Branch</h4>
                    </div>
                    <button type="button" class="close bg-danger text-white fs-16" data-bs-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('branch.store') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row ">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="user_id" class="col-form-label">Select User <span class="text-danger">*</span>:</label>
                                    <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                        @if(auth()->user()->role === 'admin')
                                            <option value="">-- Select User --</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} ({{ $user->email }})
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="{{ auth()->user()->id }}" selected>
                                                {{ auth()->user()->name }} ({{ auth()->user()->email }})
                                            </option>
                                        @endif
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="branch_name" class="col-form-label">Branch Name <span class="text-danger">*</span>:</label>
                                    <input type="text" class="form-control @error('branch_name') is-invalid @enderror" name="branch_name" id="branch_name" value="{{ old('branch_name') }}" required>
                                    @error('branch_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="branch_code" class="col-form-label">Branch Code <span class="text-danger">*</span>:</label>
                                    <input type="text" class="form-control @error('branch_code') is-invalid @enderror" name="branch_code" id="branch_code" value="{{ old('branch_code') }}" required>
                                    @error('branch_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="manager_name" class="col-form-label">Manager Name:</label>
                                    <input type="text" class="form-control @error('manager_name') is-invalid @enderror" name="manager_name" id="manager_name" value="{{ old('manager_name') }}">
                                    @error('manager_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="email" class="col-form-label">Email:</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="phone" class="col-form-label">Phone:</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="address" class="col-form-label">Address:</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror" name="address" id="address" value="{{ old('address') }}">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="city" class="col-form-label">City:</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" id="city" value="{{ old('city') }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="state" class="col-form-label">State:</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" name="state" id="state" value="{{ old('state') }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country" class="col-form-label">Country:</label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror" name="country" id="country" value="{{ old('country', 'Pakistan') }}">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location" class="col-form-label">Location:</label>
                                    <input type="text" class="form-control @error('location') is-invalid @enderror" name="location" id="location" value="{{ old('location') }}">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
