@extends('layouts.app')

@section('title', 'View Role')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Role Details</h4>
            <h6>View role and assigned permissions</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back
            </a>
            @can('edit-roles')
            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary">
                <i class="ti ti-edit me-1"></i> Edit
            </a>
            @endcan
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">Role Information</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">Role Name:</th>
                            <td><strong>{{ $role->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Total Permissions:</th>
                            <td><span class="badge bg-info">{{ $role->permissions->count() }}</span></td>
                        </tr>
                        <tr>
                            <th>Users with this Role:</th>
                            <td><span class="badge bg-success">{{ \App\Models\User::role($role->name)->count() }}</span></td>
                        </tr>
                        <tr>
                            <th>Created At:</th>
                            <td>{{ $role->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Updated At:</th>
                            <td>{{ $role->updated_at->format('d M Y, h:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Assigned Permissions</h5>
                </div>
                <div class="card-body">
                    @if($role->permissions->count() > 0)
                        @foreach($permission_groups as $group => $permissions)
                            @php
                                $groupPermissions = $role->permissions->filter(function($p) use ($permissions) {
                                    return in_array($p->name, $permissions);
                                });
                            @endphp
                            @if($groupPermissions->count() > 0)
                            <div class="mb-4">
                                <h6 class="text-primary mb-2">{{ $group }}</h6>
                                <div class="row">
                                    @foreach($groupPermissions as $permission)
                                    <div class="col-md-4 mb-2">
                                        <span class="badge bg-success">
                                            <i class="ti ti-check me-1"></i>
                                            {{ str_replace('-', ' ', ucwords($permission->name, '-')) }}
                                        </span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @endforeach
                    @else
                        <p class="text-muted">No permissions assigned to this role.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

