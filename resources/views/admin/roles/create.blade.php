@extends('layouts.app')
@section('title', 'Create Role')
@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Create Role</h4>
            <h6>Add new role with permissions</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf

                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Manager">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <h5 class="mb-3">Permissions</h5>
                        <div class="d-flex gap-2 mb-3">
                            <button type="button" class="btn btn-sm btn-primary" id="checkAll">
                                <i class="ti ti-check me-1"></i> Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary" id="uncheckAll">
                                <i class="ti ti-x me-1"></i> Deselect All
                            </button>
                        </div>

                        @foreach($permission_groups as $groupName => $permissions)
                        <div class="card mb-3">
                            <div class="card-header bg-light d-flex align-items-center">
                                <input type="checkbox" class="form-check-input me-2 group-checkbox"
                                       id="group_{{ Str::slug($groupName) }}"
                                       data-group="{{ $groupName }}">
                                <label class="form-check-label mb-0 fw-bold text-capitalize" for="group_{{ Str::slug($groupName) }}">
                                    {{ str_replace('_', ' ', $groupName) }}
                                </label>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($permissions as $perm)
                                    <div class="col-md-4 col-lg-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input permission-checkbox"
                                                   type="checkbox" name="permissions[]"
                                                   value="{{ $perm->id }}"
                                                   id="perm_{{ $perm->id }}"
                                                   data-group="{{ $groupName }}">
                                            <label class="form-check-label" for="perm_{{ $perm->id }}">
                                                {{ str_replace('_', ' ', $perm->name) }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i> Create Role
                    </button>
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('checkAll').addEventListener('click', function() {
        document.querySelectorAll('.permission-checkbox, .group-checkbox').forEach(cb => cb.checked = true);
    });
    document.getElementById('uncheckAll').addEventListener('click', function() {
        document.querySelectorAll('.permission-checkbox, .group-checkbox').forEach(cb => cb.checked = false);
    });

    document.querySelectorAll('.group-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const group = this.getAttribute('data-group');
            document.querySelectorAll('.permission-checkbox[data-group="' + group + '"]').forEach(p => {
                p.checked = this.checked;
            });
        });
    });

    document.querySelectorAll('.permission-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const group = this.getAttribute('data-group');
            const groupPerms = document.querySelectorAll('.permission-checkbox[data-group="' + group + '"]');
            const checked = Array.from(groupPerms).filter(p => p.checked).length;
            const groupCb = document.querySelector('.group-checkbox[data-group="' + group + '"]');
            if (groupCb) groupCb.checked = checked === groupPerms.length;
        });
    });
});
</script>
@endpush
@endsection
