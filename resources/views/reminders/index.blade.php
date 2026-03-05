@extends('layouts.app')
@section('title', 'Reminders')
@push('styles')
<style>
  .reminder-card-link:hover { background-color: rgba(0,0,0,.03); }
</style>
@endpush
@section('content')
<div class="content">
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div class="page-title">
            <h4 class="fw-bold mb-1">Reminders</h4>
            <p class="text-muted mb-0 small">Invoices pending, payments recovery, car wash jobs & task reminders.</p>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="row mb-4 g-3">
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-0 overflow-hidden" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                <div class="card-body d-flex align-items-center text-white">
                    <span class="bg-white bg-opacity-25 rounded-2 p-3 me-3"><i class="ti ti-file-invoice fs-24"></i></span>
                    <div>
                        <p class="text-white-50 mb-0 small text-uppercase fw-semibold">Sales Pending</p>
                        <h4 class="text-white mb-0 fw-bold">{{ $salesPending->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-0 overflow-hidden" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <div class="card-body d-flex align-items-center text-white">
                    <span class="bg-white bg-opacity-25 rounded-2 p-3 me-3"><i class="ti ti-shopping-bag fs-24"></i></span>
                    <div>
                        <p class="text-white-50 mb-0 small text-uppercase fw-semibold">Purchases Pending</p>
                        <h4 class="text-white mb-0 fw-bold">{{ $purchasesPending->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-0 overflow-hidden" style="background: linear-gradient(135deg, #0d9488 0%, #059669 100%);">
                <div class="card-body d-flex align-items-center text-white">
                    <span class="bg-white bg-opacity-25 rounded-2 p-3 me-3"><i class="ti ti-car fs-24"></i></span>
                    <div>
                        <p class="text-white-50 mb-0 small text-uppercase fw-semibold">Car Wash Pending</p>
                        <h4 class="text-white mb-0 fw-bold">{{ $carWashPending->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card border-0 overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body d-flex align-items-center text-white">
                    <span class="bg-white bg-opacity-25 rounded-2 p-3 me-3"><i class="ti ti-bell fs-24"></i></span>
                    <div>
                        <p class="text-white-50 mb-0 small text-uppercase fw-semibold">Task Reminders</p>
                        <h4 class="text-white mb-0 fw-bold">{{ $taskReminders->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- 1) Sales invoices pending --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 bg-transparent d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold"><i class="ti ti-file-invoice me-2 text-primary"></i>Sales Invoices Pending</h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ $salesPending->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($salesPending->isEmpty())
                        <div class="text-center py-4 text-muted small">No pending sales invoices.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Reference</th>
                                        <th>Customer</th>
                                        <th class="text-end">Remaining</th>
                                        <th>Date</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salesPending as $s)
                                    <tr class="reminder-card-link">
                                        <td>{{ $s->reference }}</td>
                                        <td>{{ $s->customer }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($s->remaining, 2) }}</td>
                                        <td>{{ $s->sale_date ? \Carbon\Carbon::parse($s->sale_date)->format('d M Y') : '—' }}</td>
                                        <td class="text-end">
                                            <a href="{{ $s->link }}" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 2) Purchases pending (payment recovery) --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 bg-transparent d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold"><i class="ti ti-shopping-bag me-2 text-warning"></i>Purchases / Payment Recovery</h5>
                    <span class="badge bg-warning bg-opacity-10 text-dark">{{ $purchasesPending->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($purchasesPending->isEmpty())
                        <div class="text-center py-4 text-muted small">No pending purchases.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Reference</th>
                                        <th>Supplier</th>
                                        <th class="text-end">Remaining</th>
                                        <th>Date</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchasesPending as $p)
                                    <tr class="reminder-card-link">
                                        <td>{{ $p->reference }}</td>
                                        <td>{{ $p->supplier }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($p->remaining, 2) }}</td>
                                        <td>{{ $p->purchase_date ? \Carbon\Carbon::parse($p->purchase_date)->format('d M Y') : '—' }}</td>
                                        <td class="text-end">
                                            <a href="{{ $p->link }}" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 3) Car wash pending --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 bg-transparent d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold"><i class="ti ti-car me-2 text-success"></i>Elite Car Wash – Jobs / Payment Pending</h5>
                    <span class="badge bg-success bg-opacity-10 text-success">{{ $carWashPending->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($carWashPending->isEmpty())
                        <div class="text-center py-4 text-muted small">No car wash jobs pending payment.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Service</th>
                                        <th>Customer / Vehicle</th>
                                        <th class="text-end">Price</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($carWashPending as $j)
                                    <tr class="reminder-card-link">
                                        <td>{{ $j->service_name }}</td>
                                        <td>{{ $j->customer_name }} <span class="text-muted">({{ $j->vehicle_no }})</span></td>
                                        <td class="text-end">{{ number_format($j->price ?? 0, 2) }}</td>
                                        <td><span class="badge bg-{{ $j->status === 'active' ? 'info' : 'secondary' }} bg-opacity-10 text-{{ $j->status === 'active' ? 'info' : 'secondary' }}">{{ $j->status }}</span></td>
                                        <td>{{ $j->start_time ? \Carbon\Carbon::parse($j->start_time)->format('d M H:i') : '—' }}</td>
                                        <td class="text-end">
                                            <a href="{{ $j->link }}" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 4) Task reminders --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 bg-transparent d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold"><i class="ti ti-bell me-2 text-secondary"></i>Task Reminders</h5>
                    <div>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary me-1">{{ $taskReminders->count() }}</span>
                        <a href="{{ route('task.reminder') }}" class="btn btn-sm btn-outline-secondary">Open Task Reminder</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($taskReminders->isEmpty())
                        <div class="text-center py-4 text-muted small">No pending task reminders.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Assignee</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($taskReminders as $t)
                                    <tr class="reminder-card-link">
                                        <td>{{ $t->title ?? '—' }}</td>
                                        <td><span class="badge bg-opacity-10 text-uppercase small">{{ $t->priority ?? '—' }}</span></td>
                                        <td><span class="badge bg-info bg-opacity-10 text-info">{{ $t->status ?? '—' }}</span></td>
                                        <td>{{ $t->assignee ?? '—' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('task.reminder') }}" class="btn btn-sm btn-outline-primary">Open</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
