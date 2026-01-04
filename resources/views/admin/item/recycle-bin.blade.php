@extends('layouts.app')
@section('title','Recycle Bin Items')

@section('content')
<div class="content">
    <div class="page-header">
        <h2 class="fw-bold">Recycle Bin</h2>

        <a href="{{ route('all.items') }}" class="btn btn-primary">
            <i class="ti ti-arrow-left"></i> Back to Items
        </a>
    </div>

    <div class="card mt-3">
        <div class="card-body table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Deleted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>

                        <td>
                            <img src="{{ asset($item->image ?? 'assets/img/media/default.png') }}" width="70" class="rounded">
                        </td>

                        <td>{{ $item->product_item->name ?? '' }}</td>

                        <td>{{ $item->deleted_at->format('Y-m-d h:i A') }}</td>

                        <td>
                            <form action="{{ route('items.restore', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-success btn-sm">
                                    Restore
                                </button>
                            </form>

                            <a href="javascript:void(0)"
                                onclick="confirmDelete('delete-form-{{ $item->id }}', 'Are you sure you want to permanently delete this item? This action cannot be undone!')"
                                class="btn btn-danger btn-sm">
                                Delete Forever
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No deleted items found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Hidden Delete Forms Container -->
<div id="deleteFormsContainer" style="display: none;">
    @foreach ($items as $item)
    <form id="delete-form-{{ $item->id }}"
        action="{{ route('items.forceDelete', $item->id) }}"
        method="POST">
        @csrf
        @method('DELETE')
    </form>
    @endforeach
</div>
@endsection
