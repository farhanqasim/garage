{{-- resources/views/admin/branches/create.blade.php --}}
@extends('layouts.app')
@section('title', __('Add Branch'))
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h2 class="fw-bold">Add Branch</h2>
                </div>
            </div>
            <div class="page-btn">
                <a href="{{ route('all.branches') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Back</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                @if (session('info'))
                    <div class="alert alert-info">{{ session('info') }}</div>
                @endif
                
            </div>
        </div>
    </div>
@endsection
