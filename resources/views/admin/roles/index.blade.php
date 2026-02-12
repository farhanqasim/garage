@extends('layouts.app')
@section('title', 'Roles & Permissions')
@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Roles</h4>
            <h6>Manage roles and permissions</h6>
        </div>
        <div class="page-btn">
            @can('add_role')
            <a href="{{ route('roles.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Create New Role
            </a>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Sr.#</th>
                            <th>Name</th>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $key => $role)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $role->name }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach ($role->permissions as $perm)
                                    <span class="badge bg-primary text-capitalize">
                                        {{ str_replace('_', ' ', $perm->name) }}
                                    </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @can('update_role')
                                <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-success btn-sm">
                                    <i class="ti ti-edit"></i> Edit
                                </a>
                                @endcan
                                @can('delete_role')
                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                        <i class="ti ti-trash"></i> Delete
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No roles found. Create one to get started.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
