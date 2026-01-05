@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Edit Role</h4>
            <h6>Update role and permissions</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $role->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <h5 class="mb-3">Permissions</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <button type="button" class="btn btn-sm btn-primary" onclick="selectAllPermissions()">
                                <i class="ti ti-check me-1"></i> Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="deselectAllPermissions()">
                                <i class="ti ti-x me-1"></i> Deselect All
                            </button>
                        </div>

                        @foreach($permission_groups as $group => $permissions)
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <input type="checkbox" class="form-check-input group-checkbox" 
                                           data-group="{{ $group }}" 
                                           onchange="toggleGroupPermissions('{{ $group }}', this.checked)"
                                           id="group_{{ str_replace(' ', '_', $group) }}">
                                    <strong>{{ $group }}</strong>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($permissions as $permission)
                                    @php
                                        $permissionModel = $all_permissions->firstWhere('name', $permission);
                                    @endphp
                                    @if($permissionModel)
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input permission-checkbox" 
                                                   type="checkbox" 
                                                   name="permissions[]" 
                                                   value="{{ $permissionModel->id }}"
                                                   id="permission_{{ $permissionModel->id }}"
                                                   data-group="{{ $group }}"
                                                   {{ in_array($permissionModel->id, $rolePermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="permission_{{ $permissionModel->id }}">
                                                {{ str_replace('-', ' ', ucwords($permission, '-')) }}
                                            </label>
                                        </div>
                                    </div>
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i> Update Role
                        </button>
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function selectAllPermissions() {
        document.querySelectorAll('.permission-checkbox').forEach(cb => {
            cb.checked = true;
        });
        document.querySelectorAll('.group-checkbox').forEach(cb => {
            cb.checked = true;
        });
    }

    function deselectAllPermissions() {
        document.querySelectorAll('.permission-checkbox').forEach(cb => {
            cb.checked = false;
        });
        document.querySelectorAll('.group-checkbox').forEach(cb => {
            cb.checked = false;
        });
    }

    function toggleGroupPermissions(group, checked) {
        document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`).forEach(cb => {
            cb.checked = checked;
        });
    }

    // Update group checkbox when individual permissions change
    document.querySelectorAll('.permission-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const group = this.getAttribute('data-group');
            const groupCheckboxes = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
            const checkedCount = Array.from(groupCheckboxes).filter(c => c.checked).length;
            const groupCheckbox = document.querySelector(`.group-checkbox[data-group="${group}"]`);
            if (groupCheckbox) {
                groupCheckbox.checked = checkedCount === groupCheckboxes.length;
            }
        });
    });

    // Initialize group checkboxes on page load
    document.addEventListener('DOMContentLoaded', function() {
        @foreach($permission_groups as $group => $permissions)
        const group{{ str_replace(' ', '', $group) }}Checkboxes = document.querySelectorAll(`.permission-checkbox[data-group="{{ $group }}"]`);
        const checked{{ str_replace(' ', '', $group) }} = Array.from(group{{ str_replace(' ', '', $group) }}Checkboxes).filter(c => c.checked).length;
        const group{{ str_replace(' ', '', $group) }}Checkbox = document.querySelector(`.group-checkbox[data-group="{{ $group }}"]`);
        if (group{{ str_replace(' ', '', $group) }}Checkbox) {
            group{{ str_replace(' ', '', $group) }}Checkbox.checked = checked{{ str_replace(' ', '', $group) }} === group{{ str_replace(' ', '', $group) }}Checkboxes.length;
        }
        @endforeach
    });
</script>
@endpush
@endsection

