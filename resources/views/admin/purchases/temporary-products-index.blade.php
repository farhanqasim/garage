@extends('layouts.app')
@section('title', 'Temporary Products')
@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4 class="fw-bold">Temporary Products List</h4>
        </div>
        <div class="page-btn">
            <a href="{{ route('purchases.create') }}" class="btn btn-primary">
                <i class="ti ti-arrow-left me-1"></i> Back to Create Purchase
            </a>
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
            <p class="text-muted mb-4">Products added temporarily from the purchase flow appear here. You can edit them or convert to real products so they appear in the main Items list.</p>
            @if($items->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="ti ti-package-off fs-1"></i>
                    <p class="mt-2 mb-0">No temporary products yet.</p>
                    <a href="{{ route('purchases.create') }}?open_temporary=1" class="btn btn-outline-primary mt-3">Create Purchase &amp; Add Temporary Product</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Cost Price</th>
                                <th>Notes</th>
                                <th>Updated</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr>
                                <td>
                                    @if($item->image)
                                        <img src="{{ asset($item->image) }}" alt="" class="rounded temporary-product-thumb" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;" data-full-src="{{ asset($item->image) }}" title="Click to view full image" onerror="this.src='{{ asset('assets/img/icons/image.svg') }}'">
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $item->short_disc ?? $item->pro_dis ?? 'N/A' }}</td>
                                <td>Rs {{ number_format($item->packing_purchase_rate ?? 0, 2) }}</td>
                                <td class="small text-muted">{{ Str::limit($item->notes, 50) ?: '—' }}</td>
                                <td class="small">{{ $item->updated_at ? $item->updated_at->format('d M Y, H:i') : '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('purchases.temporary.edit', $item->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form action="{{ route('purchases.temporary.convert', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Convert this temporary product to a real product? It will appear in the main Items list.');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Convert to real product">
                                            <i class="ti ti-check me-1"></i>Convert to Real
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Image view modal -->
<div class="modal fade" id="temporary-product-image-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">View Image</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3">
                <img id="temporary-product-image-full" src="" alt="Product image" class="img-fluid rounded shadow-sm" style="max-height: 75vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.temporary-product-thumb').forEach(function(thumb) {
        thumb.addEventListener('click', function() {
            var src = this.getAttribute('data-full-src');
            if (src) {
                document.getElementById('temporary-product-image-full').src = src;
                var modal = new bootstrap.Modal(document.getElementById('temporary-product-image-modal'));
                modal.show();
            }
        });
    });
});
</script>
@endpush
