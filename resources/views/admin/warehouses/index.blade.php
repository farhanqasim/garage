@extends('layouts.app')

@section('title', 'Warehouses')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Warehouses</h4>
            <h6>Manage branch warehouses</h6>
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
                        @forelse($warehouses as $index => $warehouse)
                        <tr>
                            <td>{{ $index + 1 }}</td>
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
                                <div class="d-flex gap-2">
                                    <a href="{{ route('warehouses.show', $warehouse->id) }}" class="btn btn-sm btn-primary" title="View">
                                        <i class="ti ti-eye"></i> View
                                    </a>
                                    <a href="{{ route('warehouses.low-stock', $warehouse->id) }}" class="btn btn-sm btn-warning" title="Low Stock">
                                        <i class="ti ti-alert-triangle"></i> Low Stock
                                    </a>
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
@endsection

