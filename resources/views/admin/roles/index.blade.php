@extends('layouts.app')

@section('title', 'Roles & Permissions')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Roles & Permissions</h4>
            <h6>Manage user roles and their permissions</h6>
        </div>
        <div class="page-btn">
            @can('create-roles')
            <a href="{{ route('roles.create') }}" class="btn btn-primary">
                <i class="ti ti-circle-plus me-1"></i> Add Role
            </a>
            @endcan
            <button type="button" class="btn btn-info" onclick="createAllPermissions()">
                <i class="ti ti-key me-1"></i> Create All Permissions
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="rolesTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Role Name</th>
                            <th>Permissions Count</th>
                            <th>Users Count</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $index => $role)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $role->name }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $role->permissions->count() }} Permissions</span>
                            </td>
                            <td>
                                <span class="badge bg-success">{{ \App\Models\User::role($role->name)->count() }} Users</span>
                            </td>
                            <td>{{ $role->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    @can('view-roles')
                                    <a href="{{ route('roles.show', $role->id) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    @endcan
                                    @can('edit-roles')
                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete-roles')
                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No roles found. <a href="{{ route('roles.create') }}">Create one</a></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function createAllPermissions() {
        if (!confirm('This will create all permissions. Continue?')) {
            return;
        }

        fetch('{{ route("roles.create-permissions") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('All permissions created successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while creating permissions.');
        });
    }
</script>
@endpush
@endsection
