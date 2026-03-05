@extends('layouts.app')
@section('title', 'User Transactions Report')
@section('content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex align-items-center">
            <div class="page-title">
                <h2 class="fw-bold mb-0 d-flex align-items-center">
                    <span class="title-icon bg-soft-primary rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;"><i class="ti ti-report text-primary"></i></span>
                    User Transactions Report
                </h2>
                <p class="text-muted small mb-0 mt-1">{{ $user->name }} ({{ $user->email }}) – tamam transactions ki list</p>
            </div>
        </div>
        <div class="page-btn">
            <a href="{{ route('all.users') }}" class="btn btn-outline-secondary d-inline-flex align-items-center">
                <i class="ti ti-arrow-left me-1"></i>Back to Users
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-center mb-0">
                    <thead class="thead-primary">
                        <tr>
                            <th>#</th>
                            <th>Tareekh</th>
                            <th>Raqam</th>
                            <th>Qisam</th>
                            <th>Mutaliqa Item / Detail</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $index => $r)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $r['date']->format('d-m-Y') }}<br><small class="text-muted">{{ $r['date']->format('h:i A') }}</small></td>
                                <td>{{ $r['ref'] }}</td>
                                <td><span class="badge bg-soft-primary">{{ $r['type'] }}</span></td>
                                <td>{{ $r['detail'] ?: '–' }}</td>
                                <td class="text-end">{{ isset($r['amount']) ? number_format($r['amount'], 2) : '–' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Is user ki koi transaction nahi mili.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
