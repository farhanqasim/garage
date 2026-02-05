@extends('layouts.app')
@section('title', 'Attendance History')

@push('styles')
<style>
    body {
        background-color: #f8fafc;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 font-weight-bold">
                            <i class="ti ti-clock me-2"></i>Attendance History
                        </h4>
                        <a href="{{ route('attendance') }}" class="btn btn-light btn-sm">
                            <i class="ti ti-arrow-left me-1"></i>Back to Attendance
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Filters Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="ti ti-filter me-2"></i>Filters</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('attendance.history.page') }}">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label fw-bold">Date From</label>
                                        <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label fw-bold">Date To</label>
                                        <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label fw-bold">Worker</label>
                                        <select name="worker_id" class="form-select">
                                            <option value="">All Workers</option>
                                            @foreach($workers as $worker)
                                                <option value="{{ $worker->id }}" {{ $filters['worker_id'] == $worker->id ? 'selected' : '' }}>
                                                    {{ $worker->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label fw-bold">User</label>
                                        <select name="user_id" class="form-select">
                                            <option value="">All Users</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ $filters['user_id'] == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="ti ti-search me-1"></i>Apply Filters
                                        </button>
                                        <a href="{{ route('attendance.history.page') }}" class="btn btn-secondary">
                                            <i class="ti ti-x me-1"></i>Clear Filters
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Attendance Table -->
                    <div class="card">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="ti ti-check-circle me-2 text-success"></i>
                                Completed Attendance ({{ count($completed) }} {{ count($completed) == 1 ? 'Pair' : 'Pairs' }})
                            </h5>
                            <a href="{{ route('attendance.history.page', request()->query()) }}" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-refresh me-1"></i>Refresh
                            </a>
                        </div>
                        <div class="card-body">
                            @if(count($completed) > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="fw-bold">Employee</th>
                                                <th class="fw-bold">Date</th>
                                                <th class="fw-bold">IN Time</th>
                                                <th class="fw-bold">OUT Time</th>
                                                <th class="fw-bold">Hours</th>
                                                <th class="fw-bold">IN Location</th>
                                                <th class="fw-bold">OUT Location</th>
                                                <th class="fw-bold">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($completed as $item)
                                                <tr>
                                                    <td class="fw-bold">{{ $item['employeeName'] }}</td>
                                                    <td>{{ $item['inTime']->format('M d, Y') }}</td>
                                                    <td>{{ $item['inTime']->format('h:i A') }}</td>
                                                    <td class="{{ $item['outTime'] ? '' : 'text-warning fw-bold' }}">
                                                        {{ $item['outTime'] ? $item['outTime']->format('h:i A') : 'Pending' }}
                                                    </td>
                                                    <td class="fw-bold {{ $item['hours'] ? 'text-primary' : 'text-warning' }}">
                                                        {{ $item['hours'] ? number_format($item['hours'], 2) . ' hrs' : 'N/A' }}
                                                    </td>
                                                    <td>
                                                        @if($item['inLocation'])
                                                            <a href="https://www.google.com/maps?q={{ $item['inLocation']['lat'] }},{{ $item['inLocation']['lng'] }}" 
                                                               target="_blank" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i class="ti ti-map-pin me-1"></i>View Map
                                                            </a>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item['outLocation'])
                                                            <a href="https://www.google.com/maps?q={{ $item['outLocation']['lat'] }},{{ $item['outLocation']['lng'] }}" 
                                                               target="_blank" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i class="ti ti-map-pin me-1"></i>View Map
                                                            </a>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-info" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#attendanceDetailModal"
                                                                onclick="showAttendanceDetail({{ $loop->index }})">
                                                            <i class="ti ti-eye me-1"></i>View Details
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="ti ti-check-circle" style="font-size: 48px; color: #cbd5e1;"></i>
                                    <p class="mt-3 text-muted">
                                        @if($filters['date_from'] || $filters['date_to'] || $filters['worker_id'] || $filters['user_id'])
                                            No attendance records found. Try adjusting your filters.
                                        @else
                                            No attendance data available.
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Detail Modal -->
<div class="modal fade" id="attendanceDetailModal" tabindex="-1" aria-labelledby="attendanceDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="attendanceDetailModalLabel">
                    <i class="ti ti-user me-2"></i>Attendance Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="attendanceDetailContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const attendanceData = @json($completed);
    const googleMapsApiKey = @json($googleMapsApiKey);

    function showAttendanceDetail(index) {
        const item = attendanceData[index];
        const inAtt = item.inAttendance;
        const outAtt = item.outAttendance;
        
        let content = `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="ti ti-login me-2"></i>Check-In Details</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>Employee:</strong> ${item.employeeName}</p>
                            <p><strong>Date & Time:</strong> ${new Date(item.inTime).toLocaleString()}</p>
                            ${inAtt.address ? `<p><strong>Address:</strong> ${inAtt.address}</p>` : ''}
                            ${inAtt.lat && inAtt.lng ? `<p><strong>Coordinates:</strong> ${inAtt.lat}, ${inAtt.lng}</p>` : ''}
                            ${inAtt.captured_photo ? `
                                <div class="mt-3">
                                    <strong>Photo:</strong><br>
                                    <img src="${inAtt.captured_photo.startsWith('http') ? inAtt.captured_photo : '{{ url("/") }}/' + inAtt.captured_photo}" 
                                         class="img-thumbnail mt-2" 
                                         style="max-width: 200px; max-height: 200px;" 
                                         alt="Check-in Photo">
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="ti ti-logout me-2"></i>Check-Out Details</h6>
                        </div>
                        <div class="card-body">
                            ${outAtt ? `
                                <p><strong>Date & Time:</strong> ${new Date(item.outTime).toLocaleString()}</p>
                                ${outAtt.address ? `<p><strong>Address:</strong> ${outAtt.address}</p>` : ''}
                                ${outAtt.lat && outAtt.lng ? `<p><strong>Coordinates:</strong> ${outAtt.lat}, ${outAtt.lng}</p>` : ''}
                                ${outAtt.captured_photo ? `
                                    <div class="mt-3">
                                        <strong>Photo:</strong><br>
                                        <img src="${outAtt.captured_photo.startsWith('http') ? outAtt.captured_photo : '{{ url("/") }}/' + outAtt.captured_photo}" 
                                             class="img-thumbnail mt-2" 
                                             style="max-width: 200px; max-height: 200px;" 
                                             alt="Check-out Photo">
                                    </div>
                                ` : ''}
                            ` : '<p class="text-muted">Check-out not recorded yet</p>'}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="ti ti-map me-2"></i>Location Map</h6>
                        </div>
                        <div class="card-body p-0">
                            <div id="attendanceMap" style="height: 400px; width: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.getElementById('attendanceDetailContent').innerHTML = content;
        
        // Load Google Maps
        if (typeof google === 'undefined') {
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${googleMapsApiKey}&callback=initMap`;
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
            window.initMap = function() {
                initializeMap(item);
            };
        } else {
            initializeMap(item);
        }
    }
    
    function initializeMap(item) {
        const mapDiv = document.getElementById('attendanceMap');
        if (!mapDiv) return;
        
        let center = { lat: 31.4255, lng: 74.3019 }; // Default location
        let markers = [];
        
        if (item.inLocation) {
            center = { lat: parseFloat(item.inLocation.lat), lng: parseFloat(item.inLocation.lng) };
            markers.push({
                position: center,
                title: 'Check-In Location',
                icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
            });
        }
        
        if (item.outLocation) {
            const outPos = { lat: parseFloat(item.outLocation.lat), lng: parseFloat(item.outLocation.lng) };
            markers.push({
                position: outPos,
                title: 'Check-Out Location',
                icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
            });
            // Center map between both locations if both exist
            if (item.inLocation) {
                center = {
                    lat: (parseFloat(item.inLocation.lat) + parseFloat(item.outLocation.lat)) / 2,
                    lng: (parseFloat(item.inLocation.lng) + parseFloat(item.outLocation.lng)) / 2
                };
            } else {
                center = outPos;
            }
        }
        
        const map = new google.maps.Map(mapDiv, {
            zoom: markers.length > 1 ? 12 : 15,
            center: center,
            mapTypeId: 'roadmap'
        });
        
        markers.forEach(markerData => {
            const marker = new google.maps.Marker({
                position: markerData.position,
                map: map,
                title: markerData.title,
                icon: markerData.icon
            });
            
            const infoWindow = new google.maps.InfoWindow({
                content: `<strong>${markerData.title}</strong><br>${markerData.position.lat}, ${markerData.position.lng}`
            });
            
            marker.addListener('click', () => {
                infoWindow.open(map, marker);
            });
        });
        
        // Fit bounds if multiple markers
        if (markers.length > 1) {
            const bounds = new google.maps.LatLngBounds();
            markers.forEach(m => bounds.extend(m.position));
            map.fitBounds(bounds);
        }
    }
</script>
@endpush
@endsection
