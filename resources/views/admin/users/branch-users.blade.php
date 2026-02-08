@extends('layouts.app')
@section('title','Branch Users')
@section('content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h2 class="fw-bold">Users - {{ $branch->branch_name }}</h2>
                <p class="text-muted">Branch Code: {{ $branch->branch_code }}</p>
            </div>
        </div>
        <div class="page-btn">
            <a href="{{ route('all.branches') }}" class="btn btn-secondary me-2">
                <i class="ti ti-arrow-left me-1"></i>Back to All Branches
            </a>
        </div>
    </div>
    <!-- /Product List -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto; width: 100%;">
                <table id="searchableTable" class="table table-hover table-center" style="min-width: 1200px;">
                    <thead class="thead-primary">
                        <tr>
                            <th>#</th>
                            <th>Employee Name</th>
                            <th>Profile Image</th>
                            <th>Role</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
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
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                            <td>
                                @if ($user->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No users found for this branch</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrapper mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Table search functionality
    var searchInput = document.getElementById('tableSearch');
    if (searchInput) {
        var table = document.getElementById('searchableTable');
        searchInput.addEventListener('keyup', function() {
            var term = this.value.toLowerCase();
            var rows = table.querySelectorAll('tbody tr');
            rows.forEach(function(row) {
                var text = (row.textContent || '').toLowerCase();
                row.style.display = term === '' || text.indexOf(term) !== -1 ? '' : 'none';
            });
        });
    }
    
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>
@endsection
