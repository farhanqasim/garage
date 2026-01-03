@extends('layouts.app')
@section('title','All Items')
@section('content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h2 class="fw-bold">All Items</h2>
            </div>
        </div>
        <ul class="table-top-head">

            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf">
                    <img src="{{ asset('assets/img/icons/pdf.svg') }}" alt="img">
                </a>
            </li>

            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel">
                    <img src="{{ asset('assets/img/icons/excel.svg') }}" alt="img">
                </a>
            </li>

            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh">
                    <i class="ti ti-refresh"></i>
                </a>
            </li>

            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header">
                    <i class="ti ti-chevron-up"></i>
                </a>
            </li>

        </ul>
        <div class="page-btn">
            <a href="{{ route('all.items.create') }}" class="btn btn-primary me-2">
                <i class="ti ti-circle-plus me-1"></i>Add Items
            </a>
        </div>
    </div>
    <!-- /Product List -->
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-end flex-wrap row-gap-3">
            {{-- <div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                <div class="dropdown">
                    <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                        Status
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end p-3">
                        <li><a href="javascript:void(0);" class="dropdown-item rounded-1">Active</a></li>
                        <li><a href="javascript:void(0);" class="dropdown-item rounded-1">Inactive</a></li>
                    </ul>
                </div>
            </div> --}}
            <div class="d-flex justify-content-end mb-3">
                <input type="text" id="tableSearch" class="form-control w-100" placeholder="Search...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" >
                <form id="bulkDeleteForm" method="POST" action="{{ route('all.items.bulkDelete') }}" style="height: 80vh;">
                    @csrf
                    @method('DELETE')
                    <table id="searchableTable" class="table table-hover table-center" >
                        <thead class="thead-primary">
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" class="form-check" style="width: 20px; height:20px">
                                </th>
                                <th>Product Image</th>
                                <th>Actions</th>
                                <th>Update History</th>
                                <th>Part Number</th>
                                <th>User Name</th>
                                <th>Product Name</th>
                                <th>Product Type</th>
                                <th>Bar Code</th>
                                <th>Is Active</th>
                                <th>Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                            <tr>
                                <td>
                                    <input type="checkbox" name="ids[]" value="{{ $item->id }}" style="width: 20px; height:20px"   class="item-checkbox form-check">
                                </td>
                                <td>
                                    <img src="{{ asset($item->image ?? 'assets/img/media/default.png') }}"
                                        width="70" height="70" class="rounded item-image"
                                        style="cursor:pointer;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#imageModal"
                                        data-src="{{ asset($item->image ?? 'assets/img/media/default.png') }}">
                                </td>
                                <td class="no-highlight">
                                    <div class="dropdown">
                                        <button class="btn btn-primary  dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            {{-- <li>
                                                <a class="dropdown-item" href="">
                                                     <i data-feather="tag" class="me-1"></i> Lable
                                                </a>
                                            </li> --}}
                                            <li>
                                                <a class="dropdown-item mt-3" href="{{ route('item.show',$item->id) }}">
                                                    <i data-feather="eye" class="me-1"></i> View
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item mt-2" href="{{ route('item.edit',$item->id) }}">
                                                    <i data-feather="edit" class="me-1"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0)"
                                                    onclick="confirmDelete('delete-form-{{ $item->id }}')"
                                                    class="dropdown-item mt-2">
                                                    <i data-feather="trash-2" class="feather-trash-2"></i>  Delete
                                                </a>
                                            </li>
                                            <hr>
                                            <li>
                                                <a class="dropdown-item text-primary" href="{{ route('item.duplicate', $item->id) }}">
                                                    <i data-feather="copy" class="me-1"></i> Duplicate
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <!-- Hidden delete form - moved outside dropdown -->
                                    <form id="delete-form-{{ $item->id }}"
                                        action="{{ route('item.delete', $item->id) }}"
                                        method="POST"
                                        style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                                <td>
                                    @if($item->updated_by_user)
                                        <div class="small">
                                            <div> {{ $item->updated_by_user->name ?? 'N/A' }}</div>
                                            @if($item->last_updated_at)
                                                <div> {{ $item->last_updated_at->format('d M Y, h:i A') }}</div>
                                            @elseif($item->updated_at)
                                                <div> {{ $item->updated_at->format('d M Y, h:i A') }}</div>
                                            @endif
                                        </div>
                                    @elseif($item->updated_at)
                                        <div class="small">
                                            <div> {{ $item->updated_at->format('d M Y, h:i A') }}</div>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $item->partnumber_item->name??'-' }}</td>
                                <td>{{ $item->item_user->name??'' }}</td>
                                <td>{{ $item->product_item->name??'' }}</td>
                                <td>{{ $item->type }}</td>
                                <td><span class="badge bg-secondary">{{ $item->bar_code }}</span><br> <br>
                                  <img src="{{ asset($item->barcode_image)}}" alt="" />
                                </td>
                                <td>
                                    <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ $item->category ? $item->category->name : 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center">No items found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
        <div class="card-footer">
            <button type="button" id="bulkDeleteBtn" class="btn btn-danger">
                <i class="ti ti-trash me-1"></i>
            </button>
            {{-- {{ $items->links() }} --}}
            <a href="{{ route('items.recycle.bin') }}" class="btn btn-primary">
                <i class="ti ti-trash me-1"></i> Recycle Bin
            </a>
        </div>
    </div>
</div>
<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body p-0">
        <img src="" id="modalImage" class="" style="width: 100%; height:700px" alt="Item Image">
      </div>
      <div class="modal-footer p-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Select all checkboxes
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.item-checkbox');
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(chk => chk.checked = selectAll.checked);
        });
        // Bulk Delete
        document.getElementById('bulkDeleteBtn').addEventListener('click', function() {
            const selected = Array.from(checkboxes).filter(chk => chk.checked);
            if (selected.length === 0) return alert('Please select at least one item.');
            if (!confirm('Are you sure you want to delete selected items?')) return;
            document.getElementById('bulkDeleteForm').submit();
        });
        // Single Delete
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!confirm('Are you sure you want to delete this item?')) return;
                const id = this.dataset.id;
                fetch(`/items/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).then(res => res.json())
                .then(data => {
                    if (data.success) location.reload();
                    else alert('Failed to delete');
                });
            });
        });
        // Duplicate
        document.querySelectorAll('.duplicate-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.dataset.id;
                if (!confirm('Duplicate this item?')) return;
                fetch(`/items/${id}/duplicate`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Item duplicated successfully.');
                        location.reload();
                    } else {
                        alert('Failed to duplicate item.');
                    }
                });
            });
        });
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalImage = document.getElementById('modalImage');

    document.querySelectorAll('.item-image').forEach(img => {
        img.addEventListener('click', function() {
            const src = this.getAttribute('data-src');
            modalImage.src = src;
        });
    });

    // Optional: clear modal image on close
    const imageModal = document.getElementById('imageModal');
    imageModal.addEventListener('hidden.bs.modal', function () {
        modalImage.src = '';
    });
});
</script>




@endsection
