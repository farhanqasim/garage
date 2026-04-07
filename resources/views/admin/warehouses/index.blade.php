@extends('layouts.app')

@section('title', 'Warehouses')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Warehouses</h4>
            <h6>Manage branch warehouses</h6>
        </div>
        <div class="page-btn">
            @can('add_warehouse')
            <a href="{{ route('warehouses.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Add New Warehouse
            </a>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Warehouse Name</th>
                            <th>Warehouse Code</th>
                            <th>Branch</th>
                            <th>Total Items</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $previousBranchDisplay = null; $rowNum = 0; @endphp
                        @forelse($warehouses as $index => $warehouse)
                        @php
                            $currentBranchDisplay = $warehouse->branch
                                ? ($warehouse->branch->branch_name . ($warehouse->branch->branch_code ? ' (' . $warehouse->branch->branch_code . ')' : ''))
                                : '—';
                        @endphp
                        @if($index === 0 || $currentBranchDisplay !== $previousBranchDisplay)
                            @if($index > 0)
                                <tr class="branch-separator"><td colspan="7" class="p-0"></td></tr>
                            @endif
                            <tr class="branch-header-row"><td colspan="7" class="branch-header-cell">{{ $currentBranchDisplay }}</td></tr>
                            @php $previousBranchDisplay = $currentBranchDisplay; @endphp
                        @endif
                        @php $rowNum++; @endphp
                        <tr>
                            <td>{{ ($warehouses->currentPage() - 1) * $warehouses->perPage() + $rowNum }}</td>
                            <td>
                                <strong>{{ $warehouse->warehouse_name }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $warehouse->warehouse_code }}</span>
                            </td>
                            <td>
                                {{ $warehouse->branch->branch_name ?? 'N/A' }}
                                @if($warehouse->branch->branch_code)
                                    <small class="text-muted">({{ $warehouse->branch->branch_code }})</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-success">{{ $warehouse->items->count() }} Items</span>
                            </td>
                            <td>
                                @if($warehouse->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    @can('view_warehouse')
                                    <a href="{{ route('warehouses.edit', $warehouse->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="ti ti-pencil"></i> Edit
                                    </a>
                                    <a href="{{ route('warehouses.show', $warehouse->id) }}" class="btn btn-sm btn-primary" title="View">
                                        <i class="ti ti-eye"></i> View
                                    </a>
                                    <a href="{{ route('warehouses.low-stock', $warehouse->id) }}" class="btn btn-sm btn-warning" title="Low Stock">
                                        <i class="ti ti-alert-triangle"></i> Low Stock
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No warehouses found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($warehouses) && $warehouses->hasPages())
            <div class="card-footer">
                {{ $warehouses->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    tr.branch-separator td {
        height: 1.25rem;
        border: none;
        background: #f8f9fa;
        vertical-align: middle;
    }
    tr.branch-header-row td.branch-header-cell {
        font-size: 1.15rem;
        font-weight: 700;
        padding: 0.5rem 0.75rem;
        background: #e9ecef;
        border-top: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
        color: #212529;
    }
</style>
@endsection

