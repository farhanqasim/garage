{{-- resources/views/admin/branches/index.blade.php --}}
@extends('layouts.app')
@section('title', __('Branches List'))
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h2 class="fw-bold">Branches</h2>
                </div>
            </div>

         
            <div class="page-btn">
                <a href="" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-category"><i
                        class="ti ti-circle-plus me-1"></i>Add</a>
            </div>
        </div>
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- /product list -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <div class="d-flex justify-content-end mb-3 flex-grow-1 me-2">
                    <input type="text" id="tableSearch" class="form-control" placeholder="Search by name, branch, code, manager, email, phone..." style="max-width: 320px;">
                </div>
                <div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                    <div class="dropdown">
                        <a href="javascript:void(0);"
                            class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center"
                            data-bs-toggle="dropdown">
                            Status
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Active</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Inactive</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="searchableTable" class="table table-hover table-center " id="branchTable">
                        <thead class="thead-primary">
                            <tr>
                                <th>#</th>
                                <th>Branch Name</th>
                                <th>Branch Code</th>
                                <th>Manager Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($branches as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 + (($branches->currentPage() - 1) * $branches->perPage()) }}</td>
                                    <td>{{ $item->branch_name }}</td>
                                    <td>{{ $item->branch_code }}</td>
                                    <td>{{ $item->manager_name ?? 'N/A' }}</td>
                                    <td>{{ $item->email ?? 'N/A' }}</td>
                                    <td>{{ $item->phone ?? 'N/A' }}</td>
                                    <td>
                                       @if ($item->status === 'active')
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                        <br>
                                        <div class="form-group mt-2">

                                            <form action="{{ route('update.branch.status', $item->id) }}" method="POST"
                                                class="status-form">
                                                @csrf
                                                <input type="hidden" name="status" value="{{ $item->status }}">
                                                <div
                                                    class="status-toggle modal-status d-flex justify-content-between align-items-center">
                                                    {{-- <span class="status-label">Status</span> --}}
                                                    <input type="checkbox" id="status-{{ $item->id }}"
                                                        class="check status-checkbox"
                                                        {{ $item->status == 'active' ? 'checked' : '' }}>
                                                    <label for="status-{{ $item->id }}" class="checktoggle"></label>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2 text-primary" href="{{ route('branch.users', $item->id) }}" title="View Branch Users">
                                                <i data-feather="users" class="feather-users"></i>
                                            </a>
                                            <a class="me-2 p-2" href="#" data-bs-toggle="modal"
                                                data-bs-target="#edit-category{{ $item->id }}" title="Edit Branch">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a href="javascript:void(0)"
                                                onclick="confirmDelete('delete-form-{{ $item->id }}')"
                                                class="p-2 text-danger" title="Delete">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                            <!-- Hidden delete form -->
                                            <form id="delete-form-{{ $item->id }}"
                                                action="{{ route('branch.delete', $item->id) }}"
                                                method="POST"
                                                style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No branches found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="">
                    {{ $branches->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modals: Generate one for each branch outside the loop --}}
    @foreach($branches as $item)
    <div class="modal fade" id="edit-category{{ $item->id }}">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="page-title">
                        <h4>Edit Branch</h4>
                    </div>
                    <button type="button" class="close bg-danger text-white fs-16"
                        data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('branch.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row ">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="branch_name" class="col-form-label">Branch Name <span class="text-danger">*</span>:</label>
                                    <input type="text" class="form-control @error('branch_name') is-invalid @enderror" name="branch_name" id="branch_name" value="{{ old('branch_name', $item->branch_name) }}" required style="text-transform: uppercase" oninput="this.value = this.value.toUpperCase()">
                                    @error('branch_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="branch_code" class="col-form-label">Branch Code <span class="text-danger">*</span>:</label>
                                    <input type="text" class="form-control @error('branch_code') is-invalid @enderror" name="branch_code" id="branch_code" value="{{ old('branch_code', $item->branch_code) }}" required>
                                    @error('branch_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="manager_name" class="col-form-label">Manager Name:</label>
                                    <input type="text" class="form-control @error('manager_name') is-invalid @enderror" name="manager_name" id="manager_name" value="{{ old('manager_name', $item->manager_name) }}">
                                    @error('manager_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="email" class="col-form-label">Email:</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" value="{{ old('email', $item->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="phone" class="col-form-label">Phone:</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone" value="{{ old('phone', $item->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="address" class="col-form-label">Address:</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror" name="address" id="address" value="{{ old('address', $item->address) }}">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="city" class="col-form-label">City:</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" id="city" value="{{ old('city', $item->city) }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="state" class="col-form-label">State:</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" name="state" id="state" value="{{ old('state', $item->state) }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country" class="col-form-label">Country:</label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror" name="country" id="country" value="{{ old('country', $item->country ?? 'Pakistan') }}">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location" class="col-form-label">Location:</label>
                                    <input type="text" class="form-control @error('location') is-invalid @enderror" name="location" id="location" value="{{ old('location', $item->location) }}">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach


    <div class="modal fade" id="add-category">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="page-title">
                        <h4>Add Branch</h4>
                    </div>
                    <button type="button" class="close bg-danger text-white fs-16" data-bs-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('branch.store') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row ">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="branch_name" class="col-form-label">Branch Name <span class="text-danger">*</span>:</label>
                                    <input type="text" class="form-control @error('branch_name') is-invalid @enderror" name="branch_name" id="branch_name" value="{{ old('branch_name') }}" required style="text-transform: uppercase" oninput="this.value = this.value.toUpperCase()">
                                    @error('branch_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="branch_code" class="col-form-label">Branch Code <span class="text-danger">*</span>:</label>
                                    <input type="text" class="form-control @error('branch_code') is-invalid @enderror" name="branch_code" id="branch_code" value="{{ old('branch_code') }}" required>
                                    @error('branch_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="email" class="col-form-label">Email:</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="phone" class="col-form-label">Phone:</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="address" class="col-form-label">Address:</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror" name="address" id="address" value="{{ old('address') }}">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country" class="col-form-label">Country:</label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror" name="country" id="country" value="{{ old('country', 'Pakistan') }}">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_branch_location" class="col-form-label">Location:</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('location') is-invalid @enderror" name="location" id="add_branch_location" value="{{ old('location') }}" placeholder="Address or use current location">
                                        <button type="button" class="btn btn-outline-primary" id="add_branch_location_btn" title="Use current location">
                                            <i data-feather="map-pin" class="feather-14"></i> Current
                                        </button>
                                    </div>
                                    @error('location')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('tableSearch');
        var table = document.getElementById('searchableTable');
        if (searchInput && table) {
            searchInput.addEventListener('input', function() {
                var term = (this.value || '').trim().toLowerCase();
                var rows = table.querySelectorAll('tbody tr');
                rows.forEach(function(row) {
                    var text = (row.textContent || '').toLowerCase();
                    row.style.display = term === '' || text.indexOf(term) !== -1 ? '' : 'none';
                });
            });
        }
    });
    (function() {
        var btn = document.getElementById('add_branch_location_btn');
        var input = document.getElementById('add_branch_location');
        if (!btn || !input) return;
        btn.addEventListener('click', function() {
            btn.disabled = true;
            btn.textContent = 'Getting...';
            if (typeof feather !== 'undefined') feather.replace();
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                btn.disabled = false;
                btn.innerHTML = '<i data-feather="map-pin" class="feather-14"></i> Current';
                if (typeof feather !== 'undefined') feather.replace();
                return;
            }
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    var lat = pos.coords.latitude;
                    var lng = pos.coords.longitude;
                    fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json', {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        input.value = data.display_name || (lat + ', ' + lng);
                        btn.disabled = false;
                        btn.innerHTML = '<i data-feather="map-pin" class="feather-14"></i> Current';
                        if (typeof feather !== 'undefined') feather.replace();
                    })
                    .catch(function() {
                        input.value = lat + ', ' + lng;
                        btn.disabled = false;
                        btn.innerHTML = '<i data-feather="map-pin" class="feather-14"></i> Current';
                        if (typeof feather !== 'undefined') feather.replace();
                    });
                },
                function(err) {
                    alert('Could not get location: ' + (err.message || 'Permission denied or unavailable.'));
                    btn.disabled = false;
                    btn.innerHTML = '<i data-feather="map-pin" class="feather-14"></i> Current';
                    if (typeof feather !== 'undefined') feather.replace();
                }
            );
        });
    })();
    </script>
@endsection
