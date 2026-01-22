@extends('layouts.app')
@section('title', __('Banks List'))
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h2 class="fw-bold">Banks</h2>
                </div>
            </div>
            <div class="page-btn">
                <a href="{{ route('admin.banks.create') }}" class="btn btn-primary">
                    <i class="ti ti-circle-plus me-1"></i>Add Bank
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <div class="d-flex justify-content-end mb-3">
                    <input type="text" id="tableSearch" class="form-control w-100" placeholder="Search...">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <table id="searchableTable" class="table table-hover table-center" style="min-width: 600px;">
                        <thead class="thead-primary">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Short Name</th>
                                <th>API Enabled</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($banks as $index => $bank)
                                <tr>
                                    <td>{{ $index + 1 + (($banks->currentPage() - 1) * $banks->perPage()) }}</td>
                                    <td><strong>{{ $bank->name }}</strong></td>
                                    <td>{{ $bank->short_name ?? 'N/A' }}</td>
                                    <td>
                                        @if($bank->api_enabled)
                                            <span class="badge badge-success">Yes</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($bank->status)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <form action="{{ route('admin.banks.toggle-status', $bank->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-{{ $bank->status ? 'warning' : 'success' }}" title="{{ $bank->status ? 'Deactivate' : 'Activate' }}">
                                                    <i class="ti ti-{{ $bank->status ? 'toggle-left' : 'toggle-right' }}"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('admin.banks.edit', $bank->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.banks.destroy', $bank->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this bank?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <p class="text-muted">No banks found. <a href="{{ route('admin.banks.create') }}">Create one</a></p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($banks->hasPages())
                <div class="card-footer p-3">
                    <div class=" w-100" style="overflow-x: auto;">
                        {{ $banks->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Simple table search
        document.getElementById('tableSearch')?.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('searchableTable');
            const rows = table?.getElementsByTagName('tbody')[0]?.getElementsByTagName('tr') || [];

            for (let row of rows) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            }
        });
    </script>
@endsection
