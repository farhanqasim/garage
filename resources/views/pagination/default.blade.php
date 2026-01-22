<style>
    /* Pagination Custom Styles */
    .pagination-custom {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }
    
    .pagination-custom .page-item {
        margin: 0;
    }
    
    .pagination-custom .page-link {
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        transition: all 0.2s ease;
        min-width: 2.5rem;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .pagination-custom .page-link-circle {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50% !important;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        background-color: #fff;
        border: 1px solid #dee2e6;
        color: #6c757d;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .pagination-custom .page-link-circle:hover:not(.disabled) {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
        box-shadow: 0 2px 6px rgba(13, 110, 253, 0.3);
        transform: translateY(-1px);
    }
    
    .pagination-custom .page-link-circle:active:not(.disabled) {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(13, 110, 253, 0.2);
    }
    
    .pagination-custom .page-item.disabled .page-link-circle {
        opacity: 0.4;
        cursor: not-allowed;
        background-color: #f8f9fa;
        border-color: #e9ecef;
        color: #adb5bd;
        box-shadow: none;
    }
    
    .pagination-custom .page-item.disabled .page-link-circle:hover {
        background-color: #f8f9fa;
        border-color: #e9ecef;
        color: #adb5bd;
        transform: none;
        box-shadow: none;
    }
    
    .pagination-custom .page-item.active .page-link {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
        color: #fff !important;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(13, 110, 253, 0.2);
    }
    
    .pagination-custom .page-link:hover:not(.disabled):not(.active):not(.page-link-circle) {
        background-color: #e9ecef;
        border-color: #dee2e6;
        color: #0d6efd;
    }
    
    .pagination-custom .page-item.disabled .page-link:not(.page-link-circle) {
        opacity: 0.5;
        cursor: not-allowed;
        background-color: #f8f9fa;
    }
    
    /* Responsive Pagination Styles */
    @media (max-width: 767.98px) {
        .pagination {
            font-size: 0.875rem;
        }
        .pagination-custom .page-link {
            padding: 0.375rem 0.5rem;
            font-size: 0.875rem;
            min-width: 2rem;
        }
        .pagination-custom .page-link-circle {
            width: 2rem;
            height: 2rem;
            min-width: 2rem;
            font-size: 0.875rem;
        }
        .card-footer {
            padding: 0.75rem !important;
        }
    }
    
    @media (max-width: 575.98px) {
        .pagination {
            font-size: 0.75rem;
        }
        .pagination-custom .page-link {
            padding: 0.25rem 0.375rem;
            font-size: 0.75rem;
            min-width: 1.75rem;
        }
        .pagination-custom .page-link-circle {
            width: 1.75rem;
            height: 1.75rem;
            min-width: 1.75rem;
            font-size: 0.75rem;
        }
    }
    
    /* Table Responsive */
    .table-responsive {
        -webkit-overflow-scrolling: touch;
    }
    
    @media (max-width: 767.98px) {
        .table {
            font-size: 0.875rem;
        }
        .table th,
        .table td {
            padding: 0.5rem 0.375rem;
            white-space: nowrap;
        }
    }
</style>
@if ($paginator->hasPages())
    <nav aria-label="Pagination Navigation">
        {{-- Mobile View: Simple Previous/Next with Page Info --}}
        <div class="d-flex justify-content-between d-md-none w-100">
            <ul class="pagination mb-0 w-100 justify-content-between" style="flex-wrap: nowrap;">
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">« Prev</span>
                    </li>
                @else
                    <li class="page-item">
                        <!-- <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">« Prev</a> -->
                    </li>
                @endif

                <li class="page-item">
                    <span class="page-link bg-transparent border-0 px-2">
                        <small class="text-muted">{{ $paginator->currentPage() }}/{{ $paginator->lastPage() }}</small>
                    </span>
                </li>

                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next »</a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">Next »</span>
                    </li>
                @endif
            </ul>
        </div>

        {{-- Desktop View: Full Pagination --}}
        <div class="d-none d-md-block">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="text-muted small">
                    Showing
                    <span class="fw-semibold">{{ $paginator->firstItem() }}</span>
                    to
                    <span class="fw-semibold">{{ $paginator->lastItem() }}</span>
                    of
                    <span class="fw-semibold">{{ $paginator->total() }}</span>
                    results
                </div>

                <ul class="pagination mb-0 pagination-custom">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true" aria-label="Previous">
                            <span class="page-link page-link-circle" aria-hidden="true">
                                <i class="ti ti-chevron-left"></i>
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link page-link-circle" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">
                                <i class="ti ti-chevron-left"></i>
                            </a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true">
                                <span class="page-link">{{ $element }}</span>
                            </li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" aria-current="page">
                                        <span class="page-link bg-primary text-white border-primary">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link page-link-circle" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">
                                <i class="ti ti-chevron-right"></i>
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true" aria-label="Next">
                            <span class="page-link page-link-circle" aria-hidden="true">
                                <i class="ti ti-chevron-right"></i>
                            </span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif


