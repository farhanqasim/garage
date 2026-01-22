@extends('layouts.app')
@section('title', __('Payment Methods List'))
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h2 class="fw-bold">Payment Methods</h2>
                </div>
            </div>
            <div class="page-btn">
                <a href="{{ route('admin.payment-methods.create') }}" class="btn btn-primary">
                    <i class="ti ti-circle-plus me-1"></i>Add Payment Method
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
                    <table id="searchableTable" class="table table-hover table-center" style="min-width: 800px;">
                        <thead class="thead-primary">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Name (Urdu)</th>
                                <th>Code</th>
                                <th>Requires Bank Account</th>
                                <th>Status</th>
                                <th>Sort Order</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($paymentMethods as $index => $method)
                                <tr>
                                    <td>{{ $index + 1 + (($paymentMethods->currentPage() - 1) * $paymentMethods->perPage()) }}</td>
                                    <td><strong>{{ $method->name }}</strong></td>
                                    <td>{{ $method->name_urdu ?? 'N/A' }}</td>
                                    <td><code>{{ $method->code }}</code></td>
                                    <td>
                                        @if($method->requires_bank_account)
                                            <span class="badge badge-success">Yes</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($method->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $method->sort_order }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('admin.payment-methods.edit', $method->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.payment-methods.destroy', $method->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this payment method?');">
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
                                    <td colspan="8" class="text-center py-4">
                                        <p class="text-muted">No payment methods found. <a href="{{ route('admin.payment-methods.create') }}">Create one</a></p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($paymentMethods->hasPages())
                <div class="card-footer p-3">
                    <div class="d-flex justify-content-center w-100" style="overflow-x: auto;">
                        {{ $paymentMethods->links('pagination::default') }}
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
