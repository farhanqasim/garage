@extends('layouts.app')
@section('title', 'Item Type Data - ' . $typeLabel)
@section('content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h2 class="fw-bold">{{ $typeLabel }} - Dropdown Data</h2>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Sidebar: Item Types --}}
        <div class="col-md-3 col-lg-2 mb-4">
            <div class="card border">
                <div class="card-header py-2">
                    <h6 class="mb-0"><i class="ti ti-list me-1"></i> Item Types</h6>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($allTypes as $key => $label)
                    <a href="{{ route('item-type-data.index', $key) }}" class="list-group-item list-group-item-action {{ $type === $key ? 'active' : '' }}">
                        <i class="ti ti-{{ $key === 'parts' ? 'tool' : ($key === 'battery' ? 'battery' : ($key === 'scrap' ? 'recycle' : ($key === 'filters' ? 'filter' : ($key === 'breakpad' ? 'disc' : ($key === 'oil' ? 'droplet' : 'settings'))))) }} me-2"></i>
                        {{ $label }}
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Main: Data Tables --}}
        <div class="col-md-9 col-lg-10">
            @foreach($tables as $table)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <h6 class="mb-0">{{ $table['label'] }}</h6>
                    @if(!empty($table['routes']['post']))
                    <button type="button" class="btn btn-sm btn-primary btn-add-entity" data-key="{{ $table['key'] }}" data-label="{{ $table['label'] }}" data-route="{{ route($table['routes']['post']) }}">
                        <i class="ti ti-plus me-1"></i> New
                    </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-center mb-0">
                            <thead class="thead-primary">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($table['data'] as $idx => $row)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $row->{$table['nameCol']} ?? '-' }}</td>
                                    <td>
                                        @if(!empty($table['routes']['update']))
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit-entity" data-key="{{ $table['key'] }}" data-id="{{ $row->id }}" data-name="{{ $row->{$table['nameCol']} ?? '' }}" data-route="{{ route($table['routes']['update'], $row->id) }}">
                                            <i class="ti ti-pencil"></i>
                                        </button>
                                        @endif
                                        @if(!empty($table['routes']['delete']))
                                        <form action="{{ route($table['routes']['delete'], $row->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this item?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="entityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="entityModalTitle">Add</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="entityForm">
                @csrf
                <input type="hidden" id="entityFormMethod" name="_method" value="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="entityName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="entityName" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    var modal = new bootstrap.Modal(document.getElementById('entityModal'));
    var $form = $('#entityForm');
    var $title = $('#entityModalTitle');
    var $nameInput = $('#entityName');
    var $methodInput = $('#entityFormMethod');
    var submitUrl = '';

    $('.btn-add-entity').on('click', function() {
        var label = $(this).data('label');
        submitUrl = $(this).data('route');
        $title.text('Add ' + label);
        $nameInput.val('');
        $methodInput.val('POST');
        $form.attr('method', 'POST').attr('action', '');
        modal.show();
    });

    $('.btn-edit-entity').on('click', function() {
        var label = $(this).data('label');
        var id = $(this).data('id');
        var name = $(this).data('name');
        submitUrl = $(this).data('route');
        $title.text('Edit ' + label);
        $nameInput.val(name);
        $methodInput.val('PUT');
        $form.attr('method', 'POST').attr('action', '');
        $form.data('entity-id', id);
        modal.show();
    });

    $form.on('submit', function(e) {
        e.preventDefault();
        var method = $methodInput.val();
        var url = submitUrl;
        var data = {
            name: $nameInput.val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        if (method === 'PUT') {
            data._method = 'PUT';
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(res) {
                if (res.success || res.id) {
                    if (typeof toastr !== 'undefined') toastr.success(res.message || 'Saved successfully.');
                    else alert('Saved successfully.');
                    modal.hide();
                    location.reload();
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON?.message || 'Failed to save.';
                if (typeof toastr !== 'undefined') toastr.error(msg);
                else alert(msg);
            }
        });
    });
});
</script>
@endpush
