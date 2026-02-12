<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Attendance Pro - Employee Attendance System</title>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places&loading=async" async defer></script>
    <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script>
        // Load lucide-react only after React is ready
        (function() {
            function loadLucideReact() {
                if (typeof React === 'undefined' || typeof ReactDOM === 'undefined') {
                    setTimeout(loadLucideReact, 100);
                    return;
                }
                
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/lucide-react@latest/dist/umd/lucide-react.js';
                script.onload = function() {
                    console.log('Lucide React loaded successfully');
                    // Set a flag so Babel script knows it's ready
                    window.lucideReactReady = true;
                };
                script.onerror = function() {
                    console.warn('Failed to load Lucide React, using fallback icons');
                    window.lucideReactReady = false;
                };
                document.head.appendChild(script);
            }
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', loadLucideReact);
            } else {
                loadLucideReact();
            }
        })();
    </script>
    <script>
        // Show loading message while scripts load
        window.addEventListener('load', function() {
            const root = document.getElementById('root');
            if (root && root.innerHTML.trim() === '') {
                // Don't show loading message, just wait for React to load
                root.innerHTML = '';
            }
        });
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f8fafc;
        }
        #root {
            width: 100%;
            min-height: 100vh;
        }
        @media print {
            body, #root { background: #fff; margin: 0; padding: 0; }
            body * { visibility: hidden; }
            .attendance-detail-print,
            .attendance-detail-print * { visibility: visible; }
            .attendance-detail-print {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 210mm !important;
                max-width: 210mm !important;
                margin: 0 !important;
                padding: 12mm !important;
                box-shadow: none !important;
                border: 1px solid #e2e8f0 !important;
            }
            .no-print { display: none !important; }
            .attendance-detail-print table { font-size: 10pt; }
            .attendance-detail-print th,
            .attendance-detail-print td { padding: 6pt 8pt; }
            .print-only { display: block !important; }
            body.print-preview-open .attendance-detail-print,
            body.print-preview-open .attendance-detail-print * { visibility: hidden !important; }
            .print-preview-content,
            .print-preview-content * { visibility: visible !important; }
            .print-preview-content {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 210mm !important;
                max-width: 210mm !important;
                margin: 0 !important;
                padding: 12mm !important;
                box-shadow: none !important;
                background: #fff !important;
            }
        }
        .print-only { display: none; }
        @page { size: A4; margin: 12mm; }
    </style>
</head>
<body>
    <div id="root"></div>
    
    <script type="text/babel" data-presets="react">
        // Default branch = branch user is logged in to (session or user's branch)
        const DEFAULT_BRANCH_ID = {{ json_encode(session('selected_branch_id') ?? auth()->user()->branch_id ?? 'all') }};
        
        // Wait for React to load
        if (typeof React === 'undefined' || typeof ReactDOM === 'undefined') {
            document.getElementById('root').innerHTML = '<div style="padding: 2rem; text-align: center;"><h3>Loading React...</h3></div>';
        }
        
        const { useState, useRef, useEffect } = React;
        
        // Create simple icon components (fallback if lucide-react fails)
        const createSVGIcon = (paths, size = 24) => {
            return (props) => React.createElement('svg', {
                width: props.size || size,
                height: props.size || size,
                viewBox: '0 0 24 24',
                fill: 'none',
                stroke: 'currentColor',
                strokeWidth: 2,
                strokeLinecap: 'round',
                strokeLinejoin: 'round',
                ...props
            }, paths.map(p => React.createElement(p.tag || 'path', p.attrs || {})));
        };
        
        // Icon definitions
        const Camera = typeof lucideReact !== 'undefined' && lucideReact.Camera ? lucideReact.Camera : createSVGIcon([
            { tag: 'path', attrs: { d: 'M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z' } },
            { tag: 'circle', attrs: { cx: 12, cy: 13, r: 4 } }
        ]);
        
        const MapPin = typeof lucideReact !== 'undefined' && lucideReact.MapPin ? lucideReact.MapPin : createSVGIcon([
            { tag: 'path', attrs: { d: 'M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z' } },
            { tag: 'circle', attrs: { cx: 12, cy: 10, r: 3 } }
        ]);
        
        const UserCheck = typeof lucideReact !== 'undefined' && lucideReact.UserCheck ? lucideReact.UserCheck : createSVGIcon([
            { tag: 'path', attrs: { d: 'M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2' } },
            { tag: 'circle', attrs: { cx: 8.5, cy: 7, r: 4 } },
            { tag: 'polyline', attrs: { points: '17 11 19 13 23 9' } }
        ]);
        
        const UserX = typeof lucideReact !== 'undefined' && lucideReact.UserX ? lucideReact.UserX : createSVGIcon([
            { tag: 'path', attrs: { d: 'M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2' } },
            { tag: 'circle', attrs: { cx: 8.5, cy: 7, r: 4 } },
            { tag: 'line', attrs: { x1: 18, y1: 8, x2: 23, y2: 13 } },
            { tag: 'line', attrs: { x1: 23, y1: 8, x2: 18, y2: 13 } }
        ]);
        
        const History = typeof lucideReact !== 'undefined' && lucideReact.History ? lucideReact.History : createSVGIcon([
            { tag: 'circle', attrs: { cx: 12, cy: 12, r: 10 } },
            { tag: 'polyline', attrs: { points: '12 6 12 12 16 14' } }
        ]);
        
        const DollarSign = typeof lucideReact !== 'undefined' && lucideReact.DollarSign ? lucideReact.DollarSign : createSVGIcon([
            { tag: 'line', attrs: { x1: 12, y1: 1, x2: 12, y2: 23 } },
            { tag: 'path', attrs: { d: 'M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6' } }
        ]);
        
        const Users = typeof lucideReact !== 'undefined' && lucideReact.Users ? lucideReact.Users : createSVGIcon([
            { tag: 'path', attrs: { d: 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2' } },
            { tag: 'circle', attrs: { cx: 9, cy: 7, r: 4 } },
            { tag: 'path', attrs: { d: 'M23 21v-2a4 4 0 0 0-3-3.87' } },
            { tag: 'path', attrs: { d: 'M16 3.13a4 4 0 0 1 0 7.75' } }
        ]);
        
        const CheckCircle2 = typeof lucideReact !== 'undefined' && lucideReact.CheckCircle2 ? lucideReact.CheckCircle2 : createSVGIcon([
            { tag: 'path', attrs: { d: 'M22 11.08V12a10 10 0 1 1-5.93-9.14' } },
            { tag: 'polyline', attrs: { points: '22 4 12 14.01 9 11.01' } }
        ]);
        
        const XCircle = typeof lucideReact !== 'undefined' && lucideReact.XCircle ? lucideReact.XCircle : createSVGIcon([
            { tag: 'circle', attrs: { cx: 12, cy: 12, r: 10 } },
            { tag: 'line', attrs: { x1: 15, y1: 9, x2: 9, y2: 15 } },
            { tag: 'line', attrs: { x1: 9, y1: 9, x2: 15, y2: 15 } }
        ]);
        
        const RefreshCw = typeof lucideReact !== 'undefined' && lucideReact.RefreshCw ? lucideReact.RefreshCw : createSVGIcon([
            { tag: 'polyline', attrs: { points: '23 4 23 10 17 10' } },
            { tag: 'polyline', attrs: { points: '1 20 1 14 7 14' } },
            { tag: 'path', attrs: { d: 'M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15' } }
        ]);
        
        const ExternalLink = typeof lucideReact !== 'undefined' && lucideReact.ExternalLink ? lucideReact.ExternalLink : createSVGIcon([
            { tag: 'path', attrs: { d: 'M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6' } },
            { tag: 'polyline', attrs: { points: '15 3 21 3 21 9' } },
            { tag: 'line', attrs: { x1: 10, y1: 14, x2: 21, y2: 3 } }
        ]);
        
        const Info = typeof lucideReact !== 'undefined' && lucideReact.Info ? lucideReact.Info : createSVGIcon([
            { tag: 'circle', attrs: { cx: 12, cy: 12, r: 10 } },
            { tag: 'line', attrs: { x1: 12, y1: 16, x2: 12, y2: 12 } },
            { tag: 'line', attrs: { x1: 12, y1: 8, x2: 12.01, y2: 8 } }
        ]);
        
        const ShieldCheck = typeof lucideReact !== 'undefined' && lucideReact.ShieldCheck ? lucideReact.ShieldCheck : createSVGIcon([
            { tag: 'path', attrs: { d: 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z' } },
            { tag: 'polyline', attrs: { points: '9 12 11 14 15 10' } }
        ]);
        
        const LayoutDashboard = typeof lucideReact !== 'undefined' && lucideReact.LayoutDashboard ? lucideReact.LayoutDashboard : createSVGIcon([
            { tag: 'rect', attrs: { x: 3, y: 3, width: 7, height: 9 } },
            { tag: 'rect', attrs: { x: 14, y: 3, width: 7, height: 5 } },
            { tag: 'rect', attrs: { x: 14, y: 12, width: 7, height: 9 } },
            { tag: 'rect', attrs: { x: 3, y: 16, width: 7, height: 5 } }
        ]);
        
        // Safe Calendar icon - wait for lucideReact or use fallback
        const Calendar = typeof lucideReact !== 'undefined' && lucideReact.Calendar ? lucideReact.Calendar : createSVGIcon([
            { tag: 'rect', attrs: { x: 3, y: 4, width: 18, height: 18, rx: 2, ry: 2 } },
            { tag: 'line', attrs: { x1: 16, y1: 2, x2: 16, y2: 6 } },
            { tag: 'line', attrs: { x1: 8, y1: 2, x2: 8, y2: 6 } },
            { tag: 'line', attrs: { x1: 3, y1: 10, x2: 21, y2: 10 } }
        ], 16);

        const API_BASE = '{{ url("/car-wash") }}';
        const API_ROUTES = {
            branches: API_BASE + '/attendance/branches',
            employees: API_BASE + '/attendance/employees',
            store: API_BASE + '/attendance',
            history: API_BASE + '/attendance/history',
            completed: API_BASE + '/attendance/completed'
        };
        const ROUTES = {
            staff: '{{ route("car.wash.staff") }}',
            userProfile: '{{ url("user/profile") }}',
            employeeProfile: '{{ url("employee/profile") }}'
        };

        // Allowed locations for attendance - Multiple locations supported
        // Location 1: Havoline Oil Change/Barki Xpress Lube
        // Google Maps Link: https://maps.app.goo.gl/8HoMrBSNfK5hzDMe9
        // Location 2: New Location
        // Google Maps Link: https://maps.app.goo.gl/mjVkzMFUofj5LABNA
        // Location 3: GFF6+7XH Lahore, Pakistan
        // Location 4: GFF7+724 Lahore, Pakistan
        const ALLOWED_LOCATIONS = [
            {
                lat: 31.4255, // Havoline Oil Change/Barki Xpress Lube
                lng: 74.3019,
                radius: 10, // 10 meters radius
                name: 'Havoline Oil Change/Barki Xpress Lube'
            },
            {
                lat: 31.4200, // New Location - Update with exact coordinates from Google Maps link
                lng: 74.3000, // TODO: Update with exact coordinates from https://maps.app.goo.gl/mjVkzMFUofj5LABNA
                radius: 10, // 10 meters radius
                name: 'Location 2'
            },
            {
                lat: 31.5204, // GFF6+7XH Lahore, Pakistan - UPDATE: Get exact coordinates from Google Maps
                lng: 74.3587, // Plus Code: GFF6+7XH Lahore, Pakistan - Search "GFF6+7XH Lahore" in Google Maps to get exact coordinates
                radius: 20, // 20 meters radius
                name: 'GFF6+7XH Lahore, Pakistan'
            },
            {
                lat: 31.5200, // GFF7+724 Lahore, Pakistan - UPDATE: Get exact coordinates from Google Maps
                lng: 74.3590, // Plus Code: GFF7+724 Lahore, Pakistan - Search "GFF7+724 Lahore" in Google Maps to get exact coordinates
                radius: 20, // 20 meters radius
                name: 'GFF7+724 Lahore, Pakistan'
            }
        ];

        // Calculate distance between two coordinates in meters (Haversine formula)
        const calculateDistance = (lat1, lng1, lat2, lng2) => {
            const R = 6371000; // Earth radius in meters
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLng = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLng / 2) * Math.sin(dLng / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c; // Distance in meters
        };

        const App = () => {
            const [view, setView] = useState('home');
            const [selectedEmployee, setSelectedEmployee] = useState(null);
            const [attendanceMode, setAttendanceMode] = useState(null);
            const [logs, setLogs] = useState([]);
            const [employees, setEmployees] = useState([]);
            const [branches, setBranches] = useState([]);
            const [selectedBranchId, setSelectedBranchId] = useState(DEFAULT_BRANCH_ID && String(DEFAULT_BRANCH_ID) !== '' ? String(DEFAULT_BRANCH_ID) : 'all');
            const [selectedDate, setSelectedDate] = useState(() => new Date().toISOString().slice(0, 10));
            const [detailEmployee, setDetailEmployee] = useState(null);
            const [detailDateFrom, setDetailDateFrom] = useState(() => {
                const d = new Date();
                d.setDate(1);
                return d.toISOString().slice(0, 10);
            });
            const [detailDateTo, setDetailDateTo] = useState(() => new Date().toISOString().slice(0, 10));
            const [detailLogs, setDetailLogs] = useState([]);
            const [loadingDetailLogs, setLoadingDetailLogs] = useState(false);
            const [showPrintPreview, setShowPrintPreview] = useState(false);
            const [loadingEmployees, setLoadingEmployees] = useState(false);
            const [location, setLocation] = useState(null);
            const [address, setAddress] = useState("");
            const [locationError, setLocationError] = useState(null);
            const [capturedImage, setCapturedImage] = useState(null);
            const [statusMessage, setStatusMessage] = useState(null);
            const [isLocating, setIsLocating] = useState(false);
            const [cameraError, setCameraError] = useState(null);
            
            const videoRef = useRef(null);
            const canvasRef = useRef(null);
            
            // Initialize canvas on mount
            useEffect(() => {
                if (!canvasRef.current) {
                    const canvas = document.createElement('canvas');
                    canvas.id = 'capture-canvas';
                    canvas.style.display = 'none';
                    document.body.appendChild(canvas);
                    canvasRef.current = canvas;
                }
            }, []);

            // Fetch branches for dropdown
            useEffect(() => {
                fetch(API_ROUTES.branches, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && Array.isArray(data.branches)) {
                        setBranches(data.branches);
                    }
                })
                .catch(err => console.error('Error fetching branches:', err));
            }, []);

            // Fetch employees from API (when branch or date range changes)
            useEffect(() => {
                setLoadingEmployees(true);
                const params = new URLSearchParams();
                if (selectedBranchId && selectedBranchId !== 'all') params.set('branch_id', selectedBranchId);
                if (selectedDate) {
                    params.set('date_from', selectedDate);
                    params.set('date_to', selectedDate);
                }
                const url = params.toString() ? API_ROUTES.employees + '?' + params.toString() : API_ROUTES.employees;
                fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && Array.isArray(data.employees)) {
                        setEmployees(data.employees);
                    } else {
                        setEmployees([]);
                    }
                    setLoadingEmployees(false);
                })
                .catch(err => {
                    console.error('Error fetching employees:', err);
                    setLoadingEmployees(false);
                });
            }, [selectedBranchId, selectedDate]);


            // Load attendance history when employee is selected
            useEffect(() => {
                if (selectedEmployee && selectedEmployee.id) {
                    const [type, id] = selectedEmployee.id.split('_');
                    fetch(`${API_ROUTES.history}?employeeId=${id}&type=${type}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && Array.isArray(data.attendances)) {
                            const formattedLogs = data.attendances.map(att => ({
                                id: att.id,
                                employeeId: selectedEmployee.id,
                                employeeName: selectedEmployee.name,
                                type: att.type,
                                time: att.time,
                                location: att.location,
                                address: att.address,
                                photo: att.photo
                            }));
                            setLogs(formattedLogs);
                            
                            // Initialize Google Maps after logs are loaded and rendered
                            const initMaps = () => {
                                formattedLogs.forEach(log => {
                                    if (log.location && log.location.lat && log.location.lng) {
                                        const mapElement = document.getElementById(`map-${log.id}`);
                                        if (mapElement && typeof google !== 'undefined' && google.maps) {
                                            // Clear loading text if exists
                                            if (mapElement.children.length > 0 && mapElement.children[0].tagName === 'DIV') {
                                                mapElement.innerHTML = '';
                                            }
                                            
                                            try {
                                                const map = new google.maps.Map(mapElement, {
                                                    center: { lat: parseFloat(log.location.lat), lng: parseFloat(log.location.lng) },
                                                    zoom: 15,
                                                    mapTypeControl: false,
                                                    streetViewControl: true,
                                                    fullscreenControl: true,
                                                    zoomControl: true,
                                                    styles: [
                                                        {
                                                            featureType: 'poi',
                                                            elementType: 'labels',
                                                            stylers: [{ visibility: 'off' }]
                                                        }
                                                    ]
                                                });
                                                
                                                new google.maps.Marker({
                                                    position: { lat: parseFloat(log.location.lat), lng: parseFloat(log.location.lng) },
                                                    map: map,
                                                    title: log.address || 'Attendance Location',
                                                    animation: google.maps.Animation.DROP,
                                                    icon: {
                                                        url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
                                                        scaledSize: new google.maps.Size(40, 40)
                                                    }
                                                });
                                            } catch (err) {
                                                console.error('Error initializing map for log', log.id, ':', err);
                                                mapElement.innerHTML = '<div style="padding: 1rem; text-align: center; color: #ef4444; font-size: 0.75rem;">Map loading error</div>';
                                            }
                                        }
                                    }
                                });
                            };
                            
                            // Try multiple times in case React hasn't rendered yet
                            setTimeout(initMaps, 300);
                            setTimeout(initMaps, 800);
                            setTimeout(initMaps, 1500);
                        }
                    })
                    .catch(err => console.error('Error fetching history:', err));
                }
            }, [selectedEmployee]);

            useEffect(function() {
                if (showPrintPreview) document.body.classList.add('print-preview-open');
                else document.body.classList.remove('print-preview-open');
                return function() { document.body.classList.remove('print-preview-open'); };
            }, [showPrintPreview]);

            // Load IN/OUT detail when detail view is open (with date range)
            useEffect(() => {
                if (!detailEmployee || !detailEmployee.id) {
                    setDetailLogs([]);
                    return;
                }
                setLoadingDetailLogs(true);
                const [type, id] = detailEmployee.id.split('_');
                const params = new URLSearchParams({ employeeId: id, type: type });
                if (detailDateFrom) params.set('date_from', detailDateFrom);
                if (detailDateTo) params.set('date_to', detailDateTo);
                fetch(API_ROUTES.history + '?' + params.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && Array.isArray(data.attendances)) {
                        setDetailLogs(data.attendances.map(att => ({
                            id: att.id,
                            type: att.type,
                            time: att.time,
                            location: att.location,
                            address: att.address,
                            photo: att.photo
                        })));
                    } else {
                        setDetailLogs([]);
                    }
                    setLoadingDetailLogs(false);
                })
                .catch(() => { setDetailLogs([]); setLoadingDetailLogs(false); });
            }, [detailEmployee, detailDateFrom, detailDateTo]);

            useEffect(() => {
                let stream = null;
                const startCamera = async () => {
                    if (view === 'camera' && !capturedImage) {
                        setCameraError(null);
                        try {
                            stream = await navigator.mediaDevices.getUserMedia({ 
                                video: { facingMode: 'user' } 
                            });
                            if (videoRef.current) videoRef.current.srcObject = stream;
                            // Start GPS location after camera is ready
                            setTimeout(() => getGPSLocation(), 500);
                        } catch (err) {
                            console.error('Camera error:', err);
                            let errorMsg = "Camera access blocked. Please enable camera permissions in your browser.";
                            if (err.name === 'NotAllowedError') {
                                errorMsg = "Camera permission denied. Please allow camera access in browser settings and refresh the page.";
                            } else if (err.name === 'NotFoundError') {
                                errorMsg = "No camera found. Please connect a camera device.";
                            } else if (err.name === 'NotReadableError') {
                                errorMsg = "Camera is being used by another application. Please close other apps using the camera.";
                            }
                            setCameraError(errorMsg);
                            // Still try to get GPS even if camera fails
                            getGPSLocation();
                        }
                    }
                };
                startCamera();
                return () => { 
                    if (stream) {
                        stream.getTracks().forEach(t => t.stop());
                        stream = null;
                    }
                };
            }, [view]);

            const getAddress = async (lat, lng) => {
                try {
                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                    const data = await response.json();
                    setAddress(data.display_name || "Location Address Found");
                } catch (error) {
                    setAddress("Coordinates fixed (Address service unavailable)");
                }
            };

            const getGPSLocation = () => {
                if (!navigator.geolocation) {
                    setLocationError("GPS is not supported by this browser.");
                    setIsLocating(false);
                    return;
                }

                setIsLocating(true);
                setLocationError(null);
                setLocation(null);
                
                // Set timeout to prevent infinite loading
                let timeoutId = setTimeout(() => {
                    setIsLocating(false);
                    setLocationError("GPS timeout. Please check your location settings or try again.");
                }, 20000); // 20 second timeout

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        clearTimeout(timeoutId);
                        // Get exact current location with accuracy info
                        const coords = { 
                            lat: pos.coords.latitude, 
                            lng: pos.coords.longitude,
                            accuracy: pos.coords.accuracy // Accuracy in meters
                        };
                        
                        // Check if location is within any allowed location's radius
                        let nearestLocation = null;
                        let minDistance = Infinity;
                        
                        ALLOWED_LOCATIONS.forEach(loc => {
                            const distance = calculateDistance(
                                coords.lat, 
                                coords.lng, 
                                loc.lat, 
                                loc.lng
                            );
                            if (distance < minDistance) {
                                minDistance = distance;
                                nearestLocation = { ...loc, distance: distance };
                            }
                        });
                        
                        console.log('GPS Location captured:', {
                            lat: coords.lat,
                            lng: coords.lng,
                            accuracy: coords.accuracy + ' meters',
                            nearestLocation: nearestLocation ? nearestLocation.name : 'None',
                            distance: nearestLocation ? nearestLocation.distance.toFixed(2) + ' meters' : 'N/A',
                            withinRange: nearestLocation ? nearestLocation.distance <= nearestLocation.radius : false,
                            timestamp: new Date().toISOString()
                        });
                        
                        // Check if within any allowed location's radius (optional - allow any location)
                        if (nearestLocation && nearestLocation.distance <= nearestLocation.radius) {
                            // Within allowed location - no error
                            setLocationError(null);
                        } else if (nearestLocation) {
                            // Outside allowed locations but still allow (show warning)
                            const locationNames = ALLOWED_LOCATIONS.map(l => l.name).join(' or ');
                            setLocationError(`Warning: You are ${nearestLocation.distance.toFixed(0)} meters away from nearest allowed location (${locationNames}). Attendance will still be recorded.`);
                        } else {
                            // No nearest location found - still allow
                            setLocationError(null);
                        }
                        
                        setLocation(coords);
                        getAddress(coords.lat, coords.lng);
                        setIsLocating(false);
                    },
                    (err) => {
                        clearTimeout(timeoutId);
                        setIsLocating(false);
                        let errorMsg = "Unable to get location.";
                        if (err.code === 1) {
                            errorMsg = "Location access denied. Please enable location/GPS permissions in your browser settings.";
                        } else if (err.code === 2) {
                            errorMsg = "Location unavailable. Please check your GPS settings.";
                        } else if (err.code === 3) {
                            errorMsg = "Location request timed out. Please try again.";
                        } else {
                            errorMsg = "GPS error: " + (err.message || "Please enable location services.");
                        }
                        setLocationError(errorMsg);
                    },
                    { 
                        enableHighAccuracy: true, // Use GPS for highest accuracy
                        timeout: 20000, // 20 second timeout
                        maximumAge: 0 // Always get fresh location - no cache, exact current location where user is sitting/standing
                    }
                );
            };

            const capturePhoto = () => {
                if (videoRef.current && canvasRef.current) {
                    const context = canvasRef.current.getContext('2d');
                    canvasRef.current.width = videoRef.current.videoWidth;
                    canvasRef.current.height = videoRef.current.videoHeight;
                    context.drawImage(videoRef.current, 0, 0);
                    const imageData = canvasRef.current.toDataURL('image/png');
                    setCapturedImage(imageData);
                    saveAttendance(imageData);
                }
            };

            const saveAttendance = async (photo) => {
                if (!selectedEmployee || !location || !photo) {
                    setStatusMessage({ text: "Missing required data!", type: "error" });
                    return;
                }

                // Double check location is within allowed locations range before saving
                // Check against all allowed locations
                let nearestLoc = null;
                let minDist = Infinity;
                ALLOWED_LOCATIONS.forEach(loc => {
                    const dist = calculateDistance(location.lat, location.lng, loc.lat, loc.lng);
                    if (dist < minDist) {
                        minDist = dist;
                        nearestLoc = { ...loc, distance: dist };
                    }
                });

                // Location check is now optional - allow attendance from any location
                // If within allowed location, show success message, otherwise just proceed
                if (nearestLoc && nearestLoc.distance <= nearestLoc.radius) {
                    // Within allowed location - optional success message
                    console.log(`Within allowed location: ${nearestLoc.name} (${nearestLoc.distance.toFixed(0)}m)`);
                } else if (nearestLoc) {
                    // Outside but still allow - just log warning
                    console.log(`Warning: Outside allowed locations. Distance: ${nearestLoc.distance.toFixed(0)}m from ${nearestLoc.name}`);
                }
                // Continue with attendance marking regardless of location

                try {
                    // Convert base64 to blob
                    const base64Data = photo.split(',')[1] || photo;
                    const byteCharacters = atob(base64Data);
                    const byteNumbers = new Array(byteCharacters.length);
                    for (let i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    const byteArray = new Uint8Array(byteNumbers);
                    const blob = new Blob([byteArray], { type: 'image/png' });
                    const file = new File([blob], 'attendance_photo.png', { type: 'image/png' });

                    // Prepare form data
                    const formData = new FormData();
                    const [empType, empId] = selectedEmployee.id.split('_');
                    formData.append('type', empType);
                    formData.append('employeeId', empId);
                    formData.append('attendanceType', attendanceMode);
                    formData.append('photo', file);
                    formData.append('lat', location.lat);
                    formData.append('lng', location.lng);
                    formData.append('accuracy', location.accuracy || 0); // GPS accuracy in meters - exact current location
                    formData.append('address', address || '');
                    // Send current time - backend will convert to Pakistan timezone
                    formData.append('capturedAt', new Date().toISOString());
                    // Send deviceInfo as JSON string - backend will parse it
                    const deviceInfoObj = {
                        userAgent: navigator.userAgent,
                        platform: navigator.platform,
                        language: navigator.language,
                        screenWidth: window.screen.width,
                        screenHeight: window.screen.height
                    };
                    formData.append('deviceInfo', JSON.stringify(deviceInfoObj));

                    // Send to backend
                    const response = await fetch(API_ROUTES.store, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: formData
                    });

                    // Check if response is ok
                    if (!response.ok) {
                        const errorData = await response.json().catch(() => ({ message: 'Network error' }));
                        console.error('API Error Response:', errorData);
                        setStatusMessage({ text: errorData.message || `Error: ${response.status} ${response.statusText}`, type: "error" });
                        return;
                    }

                    const data = await response.json();
                    console.log('API Response:', data);

                    if (data.success) {
                        // Update employee's last_in / last_out in list so time shows without page refresh
                        const capturedAt = data.attendance && data.attendance.capturedAt ? data.attendance.capturedAt : new Date().toISOString();
                        const attType = (data.attendance && data.attendance.attendanceType) || attendanceMode;
                        setEmployees(prev => prev.map(emp => {
                            if (emp.id !== selectedEmployee.id) return emp;
                            const updated = { ...emp };
                            if (attType === 'in') updated.last_in = capturedAt;
                            else updated.last_out = capturedAt;
                            return updated;
                        }));

                        // Reload history from backend to get latest data
                        const [empType, empId] = selectedEmployee.id.split('_');
                        fetch(`${API_ROUTES.history}?employeeId=${empId}&type=${empType}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.json())
                        .then(historyData => {
                            if (historyData.success && Array.isArray(historyData.attendances)) {
                                const formattedLogs = historyData.attendances.map(att => ({
                                    id: att.id,
                                    employeeId: selectedEmployee.id,
                                    employeeName: selectedEmployee.name,
                                    type: att.type,
                                    time: att.time,
                                    location: att.location,
                                    address: att.address,
                                    photo: att.photo
                                }));
                                setLogs(formattedLogs);
                            }
                        })
                        .catch(err => console.error('Error reloading history:', err));
                        
                        setStatusMessage({ text: "Attendance Recorded!", type: "success" });
                        setTimeout(() => {
                            setView('home');
                            setCapturedImage(null);
                            setSelectedEmployee(null);
                            setLocation(null);
                            setStatusMessage(null);
                        }, 2000);
                    } else {
                        setStatusMessage({ text: data.message || "Failed to save attendance", type: "error" });
                    }
                } catch (error) {
                    console.error('Error saving attendance:', error);
                    setStatusMessage({ text: "Error saving attendance. Please try again.", type: "error" });
                }
            };

            const calculateSalary = (empId) => {
                const empLogs = logs.filter(l => l.employeeId === empId).sort((a, b) => new Date(a.time) - new Date(b.time));
                let totalHours = 0;
                for (let i = 0; i < empLogs.length; i++) {
                    if (empLogs[i].type === 'in') {
                        const nextOut = empLogs.slice(i + 1).find(l => l.type === 'out');
                        if (nextOut) totalHours += (new Date(nextOut.time) - new Date(empLogs[i].time)) / (1000 * 60 * 60);
                    }
                }
                // Default rate if not available
                const rate = 500; // You can fetch this from employee data if needed
                return (totalHours * rate).toLocaleString(undefined, { minimumFractionDigits: 2 });
            };

            const getTotalWorkTimeFormatted = (logList) => {
                if (!logList || logList.length === 0) return { hours: 0, minutes: 0, formatted: '0h 0m' };
                const sorted = [...logList].sort((a, b) => new Date(a.time || 0) - new Date(b.time || 0));
                let totalMs = 0;
                for (let i = 0; i < sorted.length; i++) {
                    if (sorted[i].type === 'in') {
                        const nextOut = sorted.slice(i + 1).find(l => l.type === 'out');
                        if (nextOut && sorted[i].time && nextOut.time) {
                            totalMs += new Date(nextOut.time) - new Date(sorted[i].time);
                        }
                    }
                }
                const totalMins = Math.round(totalMs / (1000 * 60));
                const h = Math.floor(totalMins / 60);
                const m = totalMins % 60;
                return { hours: h, minutes: m, formatted: h + 'h ' + m + 'm' };
            };

            const openPrintPreview = () => {
                if (!detailEmployee) return;
                const totalWork = getTotalWorkTimeFormatted(detailLogs);
                const sorted = [...detailLogs].sort((a, b) => new Date(a.time || 0) - new Date(b.time || 0));
                const dateRangeText = detailDateFrom && detailDateTo ? (detailDateFrom === detailDateTo ? 'Date: ' + detailDateFrom : 'Date range: ' + detailDateFrom + ' – ' + detailDateTo) : '';
                let rows = '';
                if (sorted.length === 0) {
                    rows = '<tr><td colspan="4" style="padding:12pt;text-align:center;color:#64748b">No IN/OUT records for this date range.</td></tr>';
                } else {
                    sorted.forEach(function(log, idx) {
                        var durationStr = '-';
                        if (log.type === 'in') {
                            var nextOut = sorted.slice(idx + 1).find(function(l) { return l.type === 'out'; });
                            if (nextOut && log.time && nextOut.time) {
                                var mins = Math.round((new Date(nextOut.time) - new Date(log.time)) / (1000 * 60));
                                durationStr = Math.floor(mins / 60) + 'h ' + (mins % 60) + 'm';
                            }
                        }
                        var timeStr = log.time ? new Date(log.time).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' }) : '-';
                        var typeLabel = log.type === 'in' ? 'IN' : 'OUT';
                        var addr = (log.address || '-').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                        rows += '<tr><td style="padding:6pt 8pt;color:#1e293b">' + timeStr + '</td><td style="padding:6pt 8pt">' + typeLabel + '</td><td style="padding:6pt 8pt;font-weight:600">' + durationStr + '</td><td style="padding:6pt 8pt;color:#64748b;max-width:180px;overflow:hidden;text-overflow:ellipsis">' + addr + '</td></tr>';
                    });
                }
                const empName = (detailEmployee.name || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                const html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>IN/OUT Detail - ' + empName + '</title><style>@page{size:A4;margin:12mm}body{font-family:system-ui,sans-serif;margin:0;padding:12mm;max-width:210mm;box-sizing:border-box}table{width:100%;border-collapse:collapse;font-size:10pt}th,td{padding:6pt 8pt;text-align:left}th{background:#f8fafc;border-bottom:2px solid #e2e8f0;font-weight:800;color:#475569}.toolbar{margin-bottom:16px;display:flex;gap:8px}</style></head><body>' +
                    '<div class="toolbar"><button onclick="window.print()" style="padding:8px 16px;background:#2563eb;color:#fff;border:none;border-radius:6px;font-weight:700;cursor:pointer">Print</button><button onclick="window.close()" style="padding:8px 16px;background:#64748b;color:#fff;border:none;border-radius:6px;font-weight:700;cursor:pointer">Close</button></div>' +
                    '<h2 style="font-size:1.25rem;font-weight:800;color:#1e293b;margin:0 0 8px 0">' + empName + ' – IN/OUT Detail</h2>' +
                    (dateRangeText ? '<p style="font-size:0.875rem;color:#64748b;margin:0 0 12px 0">' + dateRangeText + '</p>' : '') +
                    '<p style="margin:0 0 12px 0;padding:8px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-weight:700;color:#166534">Total work time: ' + totalWork.formatted + '</p>' +
                    '<table><thead><tr><th>Date &amp; Time</th><th>Type</th><th>Duration</th><th>Address</th></tr></thead><tbody>' + rows + '</tbody></table>' +
                    '</body></html>';
                const w = window.open('', '_blank', 'width=210mm,height=297mm,scrollbars=yes,resizable=yes');
                if (w) {
                    w.document.write(html);
                    w.document.close();
                }
            };

            return React.createElement('div', { 
                style: { 
                    minHeight: '100vh', 
                    backgroundColor: '#f8fafc', 
                    fontFamily: 'system-ui, sans-serif',
                    color: '#0f172a',
                    paddingBottom: '2.5rem'
                } 
            },
                // Header
                React.createElement('header', { 
                    style: { 
                        backgroundColor: '#0f172a', 
                        color: 'white', 
                        padding: '1.5rem', 
                        boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
                        borderBottomLeftRadius: '2.5rem',
                        borderBottomRightRadius: '2.5rem',
                        display: 'flex',
                        justifyContent: 'space-between',
                        alignItems: 'center',
                        position: 'sticky',
                        top: 0,
                        zIndex: 50
                    } 
                },
                    React.createElement('div', { style: { display: 'flex', alignItems: 'center', gap: '0.75rem' } },
                        React.createElement('div', { 
                            style: { 
                                backgroundColor: '#6366f1', 
                                padding: '0.5rem', 
                                borderRadius: '0.75rem',
                                boxShadow: '0 4px 6px -1px rgba(99, 102, 241, 0.3)'
                            } 
                        },
                            React.createElement(ShieldCheck, { size: 24 })
                        ),
                        React.createElement('div', null,
                            React.createElement('h1', { 
                                style: { 
                                    fontSize: '1.25rem', 
                                    fontWeight: 900, 
                                    letterSpacing: '-0.025em',
                                    textTransform: 'uppercase'
                                } 
                            }, 'Attendance Pro'),
                            React.createElement('p', { 
                                style: { 
                                    fontSize: '0.625rem', 
                                    fontWeight: 700, 
                                    color: '#94a3b8',
                                    letterSpacing: '0.2em'
                                } 
                            }, 'STABLE v2.0')
                        )
                    ),
                    React.createElement('button', {
                        onClick: () => setView(view === 'admin' ? 'home' : 'admin'),
                        style: {
                            padding: '0.75rem',
                            borderRadius: '1rem',
                            transition: 'all 0.3s',
                            backgroundColor: view === 'admin' ? '#4f46e5' : '#1e293b',
                            color: view === 'admin' ? 'white' : '#94a3b8',
                            border: 'none',
                            cursor: 'pointer'
                        }
                    },
                        view === 'admin' ? React.createElement(Users, { size: 22 }) : React.createElement(LayoutDashboard, { size: 22 })
                    )
                ),

                React.createElement('main', { 
                    style: { 
                        maxWidth: '56rem', 
                        margin: '0 auto', 
                        padding: '1rem',
                        marginTop: '1.5rem'
                    } 
                },
                    statusMessage && React.createElement('div', { 
                        style: { 
                            marginBottom: '1rem', 
                            padding: '1rem', 
                            backgroundColor: '#10b981', 
                            color: 'white', 
                            borderRadius: '1rem', 
                            display: 'flex', 
                            alignItems: 'center', 
                            gap: '0.75rem', 
                            fontWeight: 700,
                            boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)'
                        } 
                    },
                        React.createElement(CheckCircle2, { size: 24 }),
                        statusMessage.text
                    ),

                    view === 'home' && React.createElement('div', { style: { display: 'flex', flexDirection: 'column', gap: '1rem' } },
                        React.createElement('div', { 
                            style: { 
                                backgroundColor: '#eef2ff', 
                                padding: '1.25rem', 
                                borderRadius: '1.875rem', 
                                border: '1px solid #c7d2fe', 
                                display: 'flex', 
                                gap: '1rem', 
                                alignItems: 'center'
                            } 
                        },
                            React.createElement('div', { 
                                style: { 
                                    backgroundColor: '#e0e7ff', 
                                    padding: '0.5rem', 
                                    borderRadius: '50%', 
                                    color: '#4f46e5'
                                } 
                            },
                                React.createElement(Info, { size: 20 })
                            ),
                            React.createElement('p', { 
                                style: { 
                                    fontSize: '0.75rem', 
                                    color: '#4338ca', 
                                    lineHeight: '1.5', 
                                    fontWeight: 600
                                } 
                            },
                                'Please allow ',
                                React.createElement('b', null, 'Camera'),
                                ' and ',
                                React.createElement('b', null, 'Location'),
                                ' access when prompted for secure verification.'
                            )
                        ),

                        branches.length > 0 ? React.createElement('div', { 
                            style: { 
                                display: 'flex', 
                                alignItems: 'center', 
                                gap: '0.75rem', 
                                padding: '0.5rem 0',
                                flexWrap: 'wrap'
                            } 
                        },
                            React.createElement('label', { 
                                style: { 
                                    fontSize: '0.75rem', 
                                    fontWeight: 700, 
                                    color: '#64748b', 
                                    textTransform: 'uppercase', 
                                    letterSpacing: '0.05em'
                                } 
                            }, 'Branch'),
                            React.createElement('select', {
                                value: selectedBranchId,
                                onChange: (e) => setSelectedBranchId(e.target.value),
                                style: {
                                    flex: '1',
                                    minWidth: '140px',
                                    maxWidth: '280px',
                                    padding: '0.5rem 0.75rem',
                                    borderRadius: '0.75rem',
                                    border: '1px solid #e2e8f0',
                                    backgroundColor: 'white',
                                    fontSize: '0.875rem',
                                    fontWeight: 600,
                                    color: '#1e293b',
                                    cursor: 'pointer'
                                }
                            },
                                branches.length > 1 ? React.createElement('option', { value: 'all' }, 'All Branches') : null,
                                branches.map(b => React.createElement('option', { key: b.id, value: String(b.id) }, b.name))
                            )
                        ) : null,

                        React.createElement('div', { 
                            style: { 
                                display: 'flex', 
                                alignItems: 'center', 
                                gap: '0.75rem', 
                                padding: '0.5rem 0',
                                flexWrap: 'wrap'
                            } 
                        },
                            React.createElement('label', { 
                                style: { 
                                    fontSize: '0.75rem', 
                                    fontWeight: 700, 
                                    color: '#64748b', 
                                    textTransform: 'uppercase', 
                                    letterSpacing: '0.05em'
                                } 
                            }, 'Date'),
                            React.createElement('input', {
                                type: 'date',
                                value: selectedDate,
                                onChange: (e) => setSelectedDate(e.target.value),
                                style: {
                                    padding: '0.5rem 0.75rem',
                                    borderRadius: '0.75rem',
                                    border: '1px solid #e2e8f0',
                                    fontSize: '0.875rem',
                                    fontWeight: 600,
                                    color: '#1e293b'
                                }
                            })
                        ),

                        React.createElement('div', { 
                            style: { 
                                display: 'flex', 
                                alignItems: 'center', 
                                justifyContent: 'space-between', 
                                padding: '0 0.5rem', 
                                paddingTop: '0.5rem'
                            } 
                        },
                            React.createElement('h2', { 
                                style: { 
                                    fontSize: '0.875rem', 
                                    fontWeight: 900, 
                                    color: '#94a3b8', 
                                    textTransform: 'uppercase', 
                                    letterSpacing: '0.1em'
                                } 
                            }, 'Active Employees'),
                            React.createElement('span', { 
                                style: { 
                                    fontSize: '0.75rem', 
                                    fontWeight: 700, 
                                    color: '#4f46e5', 
                                    backgroundColor: '#eef2ff', 
                                    padding: '0.25rem 0.75rem', 
                                    borderRadius: '9999px'
                                } 
                            }, `${employees.length} Total`)
                        ),

                        employees.length === 0 ? React.createElement('div', { 
                            style: { 
                                padding: '2rem', 
                                textAlign: 'center', 
                                color: '#64748b' 
                            } 
                        }, 'No employees found') : employees.map((emp) =>
                            React.createElement('div', { 
                                key: emp.id,
                                title: 'Double-click to open for edit',
                                onDoubleClick: function() {
                                    const parts = (emp.id || '').split('_');
                                    const type = parts[0];
                                    const id = parts[1];
                                    if (!id) return;
                                    if (type === 'worker') {
                                        window.location.href = ROUTES.staff + '?openWorker=' + encodeURIComponent(id);
                                    } else if (type === 'user') {
                                        window.location.href = ROUTES.userProfile + '/' + encodeURIComponent(id);
                                    }
                                },
                                style: { 
                                    backgroundColor: 'white', 
                                    padding: '1.25rem', 
                                    borderRadius: '1.875rem', 
                                    boxShadow: '0 1px 2px 0 rgba(0, 0, 0, 0.05)', 
                                    border: '1px solid #f1f5f9', 
                                    display: 'flex', 
                                    justifyContent: 'space-between', 
                                    alignItems: 'center',
                                    cursor: 'pointer'
                                } 
                            },
                                React.createElement('div', { style: { display: 'flex', alignItems: 'center', gap: '1rem' } },
                                    emp.profile_img
                                        ? React.createElement('img', {
                                            src: emp.profile_img,
                                            alt: emp.name,
                                            style: {
                                                width: '2.5rem',
                                                height: '2.5rem',
                                                borderRadius: '50%',
                                                objectFit: 'cover',
                                                border: '2px solid #f1f5f9',
                                                flexShrink: 0
                                            }
                                        })
                                        : React.createElement('div', { 
                                            style: { 
                                                width: '2.5rem', 
                                                height: '2.5rem', 
                                                borderRadius: '50%', 
                                                backgroundColor: '#f1f5f9', 
                                                display: 'flex', 
                                                alignItems: 'center', 
                                                justifyContent: 'center', 
                                                fontWeight: 900, 
                                                color: '#94a3b8',
                                                flexShrink: 0
                                            } 
                                        }, emp.name.charAt(0)),
                                    React.createElement('div', { style: { display: 'flex', flexDirection: 'column', gap: '0.125rem' } },
                                        React.createElement('span', { 
                                            style: { 
                                                fontSize: '1.125rem', 
                                                fontWeight: 700, 
                                                color: '#1e293b', 
                                                letterSpacing: '-0.025em'
                                            } 
                                        }, emp.name),
                                        emp.role ? React.createElement('span', { 
                                            style: { 
                                                fontSize: '0.6875rem', 
                                                fontWeight: 600, 
                                                color: '#64748b', 
                                                textTransform: 'uppercase', 
                                                letterSpacing: '0.05em'
                                            } 
                                        }, emp.role) : null
                                    )
                                ),
                                React.createElement('div', { style: { display: 'flex', flexDirection: 'column', alignItems: 'flex-end', gap: '0.5rem' } },
                                    React.createElement('div', { style: { display: 'flex', flexDirection: 'column', gap: '0.5rem', alignItems: 'stretch' } },
                                        React.createElement('button', {
                                            onClick: () => { setSelectedEmployee(emp); setAttendanceMode('in'); setView('camera'); },
                                            style: {
                                                backgroundColor: '#059669',
                                                color: 'white',
                                                padding: '0.625rem 1.25rem',
                                                borderRadius: '1rem',
                                                fontWeight: 900,
                                                fontSize: '0.75rem',
                                                boxShadow: '0 10px 15px -3px rgba(5, 150, 105, 0.2)',
                                                border: 'none',
                                                cursor: 'pointer',
                                                transition: 'all 0.2s'
                                            },
                                            onMouseOver: (e) => e.target.style.backgroundColor = '#047857',
                                            onMouseOut: (e) => e.target.style.backgroundColor = '#059669'
                                        }, 'IN'),
                                        React.createElement('button', {
                                            onClick: () => { setSelectedEmployee(emp); setAttendanceMode('out'); setView('camera'); },
                                            style: {
                                                backgroundColor: '#ea580c',
                                                color: 'white',
                                                padding: '0.625rem 1.25rem',
                                                borderRadius: '1rem',
                                                fontWeight: 900,
                                                fontSize: '0.75rem',
                                                boxShadow: '0 10px 15px -3px rgba(234, 88, 12, 0.2)',
                                                border: 'none',
                                                cursor: 'pointer',
                                                transition: 'all 0.2s'
                                            },
                                            onMouseOver: (e) => e.target.style.backgroundColor = '#c2410c',
                                            onMouseOut: (e) => e.target.style.backgroundColor = '#ea580c'
                                        }, 'OUT')
                                    ),
                                    (emp.last_in || emp.last_out) ? React.createElement('div', { 
                                        style: { 
                                            fontSize: '0.6875rem', 
                                            fontWeight: 600, 
                                            textAlign: 'right',
                                            lineHeight: 1.4,
                                            width: '100%',
                                            alignSelf: 'stretch'
                                        } 
                                    },
                                        emp.last_in ? React.createElement('div', { style: { color: '#059669', width: '100%' } }, 'IN: ', new Date(emp.last_in).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' })) : null,
                                        emp.last_out ? React.createElement('div', { style: { color: '#ea580c', width: '100%' } }, 'OUT: ', new Date(emp.last_out).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' })) : null
                                    ) : null,
                                    React.createElement('div', { style: { marginTop: '0.25rem', textAlign: 'right' } },
                                        React.createElement('span', {
                                            onClick: function(e) { e.stopPropagation(); e.preventDefault(); setDetailEmployee(emp); setView('detail'); setDetailDateFrom(selectedDate); setDetailDateTo(selectedDate); },
                                            style: { fontSize: '0.6875rem', color: '#2563eb', textDecoration: 'underline', cursor: 'pointer', fontWeight: 600 },
                                            role: 'button'
                                        }, 'View IN/OUT')
                                    )
                                )
                            )
                        )
                    ),

                    view === 'detail' && detailEmployee && React.createElement('div', {
                        className: 'attendance-detail-print',
                        style: {
                            width: '100%',
                            maxWidth: '900px',
                            margin: '0 auto',
                            backgroundColor: 'white',
                            padding: '1.5rem',
                            borderRadius: '1.875rem',
                            boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
                            border: '1px solid #f1f5f9'
                        }
                    },
                        React.createElement('div', { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1.25rem', flexWrap: 'wrap', gap: '0.75rem' } },
                            React.createElement('div', { className: 'no-print', style: { display: 'flex', gap: '0.5rem', alignItems: 'center' } },
                                React.createElement('button', {
                                    onClick: () => { setView('home'); setDetailEmployee(null); },
                                    style: {
                                        padding: '0.5rem 1rem',
                                        borderRadius: '0.75rem',
                                        border: '1px solid #e2e8f0',
                                        backgroundColor: '#f8fafc',
                                        fontWeight: 700,
                                        fontSize: '0.875rem',
                                        color: '#475569',
                                        cursor: 'pointer'
                                    }
                                }, '\u2190 Back'),
                                React.createElement('button', {
                                    onClick: () => setShowPrintPreview(true),
                                    style: {
                                        padding: '0.5rem 1rem',
                                        borderRadius: '0.75rem',
                                        border: '1px solid #2563eb',
                                        backgroundColor: '#2563eb',
                                        color: 'white',
                                        fontWeight: 700,
                                        fontSize: '0.875rem',
                                        cursor: 'pointer'
                                    }
                                }, 'Print A4')
                            ),
                            React.createElement('div', { style: { display: 'flex', alignItems: 'center', gap: '0.75rem' } },
                                detailEmployee.profile_img
                                    ? React.createElement('img', {
                                        src: detailEmployee.profile_img,
                                        alt: detailEmployee.name,
                                        style: {
                                            width: '3rem',
                                            height: '3rem',
                                            borderRadius: '50%',
                                            objectFit: 'cover',
                                            border: '2px solid #e2e8f0',
                                            flexShrink: 0
                                        }
                                    })
                                    : React.createElement('div', {
                                        style: {
                                            width: '3rem',
                                            height: '3rem',
                                            borderRadius: '50%',
                                            backgroundColor: '#e2e8f0',
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'center',
                                            fontWeight: 800,
                                            fontSize: '1.25rem',
                                            color: '#64748b',
                                            flexShrink: 0
                                        }
                                    }, detailEmployee.name ? detailEmployee.name.charAt(0) : '?'),
                                React.createElement('h2', { style: { fontSize: '1.25rem', fontWeight: 800, color: '#1e293b', margin: 0 } }, detailEmployee.name + ' – IN/OUT Detail')
                            )
                        ),
                        React.createElement('div', { className: 'no-print', style: { display: 'flex', alignItems: 'center', gap: '1rem', marginBottom: '1.25rem', flexWrap: 'wrap' } },
                            React.createElement('label', { style: { fontSize: '0.75rem', fontWeight: 700, color: '#64748b', textTransform: 'uppercase' } }, 'Date range'),
                            React.createElement('input', {
                                type: 'date',
                                value: detailDateFrom,
                                onChange: (e) => setDetailDateFrom(e.target.value),
                                style: { padding: '0.5rem 0.75rem', borderRadius: '0.5rem', border: '1px solid #e2e8f0', fontSize: '0.875rem', fontWeight: 600 }
                            }),
                            React.createElement('span', { style: { color: '#94a3b8', fontWeight: 700 } }, '–'),
                            React.createElement('input', {
                                type: 'date',
                                value: detailDateTo,
                                onChange: (e) => setDetailDateTo(e.target.value),
                                style: { padding: '0.5rem 0.75rem', borderRadius: '0.5rem', border: '1px solid #e2e8f0', fontSize: '0.875rem', fontWeight: 600 }
                            })
                        ),
                        React.createElement('div', { className: 'print-only', style: { display: 'none', marginBottom: '0.5rem', fontSize: '0.875rem', color: '#64748b' } }, detailDateFrom && detailDateTo ? (detailDateFrom === detailDateTo ? 'Date: ' + detailDateFrom : 'Date range: ' + detailDateFrom + ' – ' + detailDateTo) : ''),
                        loadingDetailLogs ? React.createElement('div', { style: { textAlign: 'center', padding: '2rem', color: '#64748b' } }, 'Loading...') :
                        detailLogs.length === 0 ? React.createElement('div', { style: { textAlign: 'center', padding: '2rem', color: '#64748b' } }, 'No IN/OUT records for this date range.') :
                        React.createElement(React.Fragment, null,
                            (function() {
                                const totalWork = getTotalWorkTimeFormatted(detailLogs);
                                return React.createElement('div', {
                                    style: {
                                        marginBottom: '1rem',
                                        padding: '0.75rem 1rem',
                                        backgroundColor: (totalWork.hours > 0 || totalWork.minutes > 0) ? '#f0fdf4' : '#f8fafc',
                                        border: '1px solid ' + ((totalWork.hours > 0 || totalWork.minutes > 0) ? '#bbf7d0' : '#e2e8f0'),
                                        borderRadius: '0.75rem',
                                        fontWeight: 700,
                                        fontSize: '0.9375rem',
                                        color: (totalWork.hours > 0 || totalWork.minutes > 0) ? '#166534' : '#475569'
                                    }
                                }, 'Total work time: ', totalWork.formatted);
                            })(),
                        React.createElement('div', { style: { overflowX: 'auto' } },
                            React.createElement('table', { style: { width: '100%', borderCollapse: 'collapse', fontSize: '0.875rem' } },
                                React.createElement('thead', null,
                                    React.createElement('tr', { style: { borderBottom: '2px solid #e2e8f0', backgroundColor: '#f8fafc' } },
                                        React.createElement('th', { style: { textAlign: 'left', padding: '0.75rem', fontWeight: 800, color: '#475569' } }, 'Date & Time'),
                                        React.createElement('th', { style: { textAlign: 'left', padding: '0.75rem', fontWeight: 800, color: '#475569' } }, 'Type'),
                                        React.createElement('th', { style: { textAlign: 'left', padding: '0.75rem', fontWeight: 800, color: '#475569' } }, 'Duration'),
                                        React.createElement('th', { style: { textAlign: 'left', padding: '0.75rem', fontWeight: 800, color: '#475569' } }, 'Address')
                                    )
                                ),
                                React.createElement('tbody', null,
                                    (function() {
                                        const sorted = [...detailLogs].sort((a, b) => new Date(a.time || 0) - new Date(b.time || 0));
                                        return sorted.map(function(log, idx) {
                                            var durationStr = '-';
                                            if (log.type === 'in') {
                                                const nextOut = sorted.slice(idx + 1).find(function(l) { return l.type === 'out'; });
                                                if (nextOut && log.time && nextOut.time) {
                                                    var mins = Math.round((new Date(nextOut.time) - new Date(log.time)) / (1000 * 60));
                                                    var h = Math.floor(mins / 60);
                                                    var m = mins % 60;
                                                    durationStr = h + 'h ' + m + 'm';
                                                }
                                            }
                                            return React.createElement('tr', { key: log.id, style: { borderBottom: '1px solid #f1f5f9' } },
                                                React.createElement('td', { style: { padding: '0.75rem', color: '#1e293b' } }, log.time ? new Date(log.time).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' }) : '-'),
                                                React.createElement('td', { style: { padding: '0.75rem' } },
                                                    React.createElement('span', {
                                                        style: {
                                                            padding: '0.25rem 0.5rem',
                                                            borderRadius: '0.5rem',
                                                            fontWeight: 700,
                                                            fontSize: '0.75rem',
                                                            backgroundColor: log.type === 'in' ? '#d1fae5' : '#ffedd5',
                                                            color: log.type === 'in' ? '#059669' : '#ea580c'
                                                        }
                                                    }, log.type === 'in' ? 'IN' : 'OUT')
                                                ),
                                                React.createElement('td', { style: { padding: '0.75rem', fontWeight: 600, color: log.type === 'in' ? '#166534' : '#94a3b8' } }, durationStr),
                                                React.createElement('td', { style: { padding: '0.75rem', maxWidth: '200px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' } },
                                                    (function() {
                                                        var mapsUrl = null;
                                                        if (log.location && log.location.lat != null && log.location.lng != null) {
                                                            mapsUrl = 'https://www.google.com/maps?q=' + encodeURIComponent(log.location.lat) + ',' + encodeURIComponent(log.location.lng);
                                                        } else if (log.address) {
                                                            mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(log.address);
                                                        }
                                                        if (mapsUrl) {
                                                            return React.createElement('a', {
                                                                href: mapsUrl,
                                                                target: '_blank',
                                                                rel: 'noopener noreferrer',
                                                                style: { color: '#2563eb', textDecoration: 'underline', cursor: 'pointer', fontWeight: 600 }
                                                            }, log.address || 'View on map');
                                                        }
                                                        return log.address || '-';
                                                    })()
                                                )
                                            );
                                        });
                                    })()
                                )
                            )
                        )
                        )
                    ),

                    showPrintPreview && detailEmployee && React.createElement('div', {
                        style: {
                            position: 'fixed',
                            left: 0,
                            top: 0,
                            right: 0,
                            bottom: 0,
                            backgroundColor: 'rgba(0,0,0,0.5)',
                            zIndex: 9999,
                            display: 'flex',
                            flexDirection: 'column',
                            alignItems: 'center',
                            padding: '1.5rem',
                            overflow: 'auto'
                        }
                    },
                        React.createElement('div', { className: 'no-print', style: { display: 'flex', gap: '0.5rem', marginBottom: '1rem' } },
                            React.createElement('button', {
                                onClick: () => window.print(),
                                style: { padding: '0.5rem 1.25rem', borderRadius: '0.75rem', backgroundColor: '#2563eb', color: '#fff', border: 'none', fontWeight: 700, cursor: 'pointer' }
                            }, 'Print'),
                            React.createElement('button', {
                                onClick: () => setShowPrintPreview(false),
                                style: { padding: '0.5rem 1.25rem', borderRadius: '0.75rem', backgroundColor: '#64748b', color: '#fff', border: 'none', fontWeight: 700, cursor: 'pointer' }
                            }, 'Close')
                        ),
                        React.createElement('div', {
                            className: 'print-preview-content',
                            style: {
                                backgroundColor: 'white',
                                padding: '1.5rem',
                                borderRadius: '1.875rem',
                                boxShadow: '0 4px 6px -1px rgba(0,0,0,0.1)',
                                border: '1px solid #f1f5f9',
                                maxWidth: '210mm',
                                width: '100%'
                            }
                        },
                            React.createElement('div', { style: { display: 'flex', alignItems: 'center', gap: '0.75rem', marginBottom: '1rem' } },
                                detailEmployee.profile_img ? React.createElement('img', { src: detailEmployee.profile_img, alt: detailEmployee.name, style: { width: '3rem', height: '3rem', borderRadius: '50%', objectFit: 'cover', border: '2px solid #e2e8f0' } }) : React.createElement('div', { style: { width: '3rem', height: '3rem', borderRadius: '50%', backgroundColor: '#e2e8f0', display: 'flex', alignItems: 'center', justifyContent: 'center', fontWeight: 800, fontSize: '1.25rem', color: '#64748b' } }, detailEmployee.name ? detailEmployee.name.charAt(0) : '?'),
                                React.createElement('h2', { style: { fontSize: '1.25rem', fontWeight: 800, color: '#1e293b', margin: 0 } }, detailEmployee.name + ' – IN/OUT Detail')
                            ),
                            React.createElement('div', { style: { marginBottom: '1rem', fontSize: '0.875rem', color: '#64748b' } }, detailDateFrom && detailDateTo ? (detailDateFrom === detailDateTo ? 'Date: ' + detailDateFrom : 'Date range: ' + detailDateFrom + ' – ' + detailDateTo) : ''),
                            (function() {
                                const totalWork = getTotalWorkTimeFormatted(detailLogs);
                                return React.createElement('div', { style: { marginBottom: '1rem', padding: '0.75rem 1rem', backgroundColor: (totalWork.hours > 0 || totalWork.minutes > 0) ? '#f0fdf4' : '#f8fafc', border: '1px solid ' + ((totalWork.hours > 0 || totalWork.minutes > 0) ? '#bbf7d0' : '#e2e8f0'), borderRadius: '0.75rem', fontWeight: 700, fontSize: '0.9375rem', color: (totalWork.hours > 0 || totalWork.minutes > 0) ? '#166534' : '#475569' } }, 'Total work time: ', totalWork.formatted);
                            })(),
                            detailLogs.length === 0 ? React.createElement('div', { style: { textAlign: 'center', padding: '2rem', color: '#64748b' } }, 'No IN/OUT records for this date range.') :
                            React.createElement('div', { style: { overflowX: 'auto' } },
                                React.createElement('table', { style: { width: '100%', borderCollapse: 'collapse', fontSize: '0.875rem' } },
                                    React.createElement('thead', null,
                                        React.createElement('tr', { style: { borderBottom: '2px solid #e2e8f0', backgroundColor: '#f8fafc' } },
                                            React.createElement('th', { style: { textAlign: 'left', padding: '0.75rem', fontWeight: 800, color: '#475569' } }, 'Date & Time'),
                                            React.createElement('th', { style: { textAlign: 'left', padding: '0.75rem', fontWeight: 800, color: '#475569' } }, 'Type'),
                                            React.createElement('th', { style: { textAlign: 'left', padding: '0.75rem', fontWeight: 800, color: '#475569' } }, 'Duration'),
                                            React.createElement('th', { style: { textAlign: 'left', padding: '0.75rem', fontWeight: 800, color: '#475569' } }, 'Address')
                                        )
                                    ),
                                    React.createElement('tbody', null,
                                        (function() {
                                            const sorted = [...detailLogs].sort((a, b) => new Date(a.time || 0) - new Date(b.time || 0));
                                            return sorted.map(function(log, idx) {
                                                var durationStr = '-';
                                                if (log.type === 'in') {
                                                    const nextOut = sorted.slice(idx + 1).find(function(l) { return l.type === 'out'; });
                                                    if (nextOut && log.time && nextOut.time) {
                                                        var mins = Math.round((new Date(nextOut.time) - new Date(log.time)) / (1000 * 60));
                                                        durationStr = Math.floor(mins / 60) + 'h ' + (mins % 60) + 'm';
                                                    }
                                                }
                                                return React.createElement('tr', { key: log.id, style: { borderBottom: '1px solid #f1f5f9' } },
                                                    React.createElement('td', { style: { padding: '0.75rem', color: '#1e293b' } }, log.time ? new Date(log.time).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' }) : '-'),
                                                    React.createElement('td', { style: { padding: '0.75rem' } }, React.createElement('span', { style: { padding: '0.25rem 0.5rem', borderRadius: '0.5rem', fontWeight: 700, fontSize: '0.75rem', backgroundColor: log.type === 'in' ? '#d1fae5' : '#ffedd5', color: log.type === 'in' ? '#059669' : '#ea580c' } }, log.type === 'in' ? 'IN' : 'OUT')),
                                                    React.createElement('td', { style: { padding: '0.75rem', fontWeight: 600, color: log.type === 'in' ? '#166534' : '#94a3b8' } }, durationStr),
                                                    React.createElement('td', { style: { padding: '0.75rem', color: '#64748b', maxWidth: '200px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' } }, log.address || '-')
                                                );
                                            });
                                        })()
                                    )
                                )
                            )
                        )
                    ),

                    view === 'camera' && React.createElement('div', { 
                        style: { 
                            backgroundColor: 'white', 
                            padding: '1.5rem', 
                            borderRadius: '2.5rem', 
                            boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.25)', 
                            border: '1px solid #f1f5f9', 
                            display: 'flex',
                            flexDirection: 'column',
                            gap: '1.5rem',
                            textAlign: 'center'
                        } 
                    },
                        React.createElement('div', { 
                            style: { 
                                display: 'flex', 
                                justifyContent: 'space-between', 
                                alignItems: 'center', 
                                borderBottom: '1px solid #e2e8f0',
                                paddingBottom: '1rem'
                            } 
                        },
                            React.createElement('div', { style: { textAlign: 'left' } },
                                React.createElement('p', { 
                                    style: { 
                                        fontSize: '0.625rem', 
                                        fontWeight: 900, 
                                        color: '#94a3b8', 
                                        textTransform: 'uppercase', 
                                        letterSpacing: '0.1em'
                                    } 
                                }, 'Employee Verification'),
                                React.createElement('h2', { 
                                    style: { 
                                        fontSize: '1.25rem', 
                                        fontWeight: 900, 
                                        color: '#0f172a'
                                    } 
                                }, selectedEmployee.name)
                            ),
                            React.createElement('button', {
                                onClick: () => setView('home'),
                                style: {
                                    color: '#94a3b8',
                                    backgroundColor: '#f1f5f9',
                                    padding: '0.5rem',
                                    borderRadius: '0.75rem',
                                    border: 'none',
                                    cursor: 'pointer'
                                }
                            },
                                React.createElement(XCircle, { size: 24 })
                            )
                        ),
                        
                        React.createElement('div', { 
                            style: { 
                                position: 'relative',
                                overflow: 'hidden',
                                borderRadius: '2rem',
                                backgroundColor: '#0f172a',
                                aspectRatio: '16/9',
                                border: '8px solid #f8fafc',
                                boxShadow: 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.06)'
                            } 
                        },
                            cameraError ? React.createElement('div', { 
                                style: { 
                                    padding: '2rem', 
                                    color: 'white', 
                                    height: '100%', 
                                    display: 'flex', 
                                    flexDirection: 'column', 
                                    alignItems: 'center', 
                                    justifyContent: 'center'
                                } 
                            },
                                React.createElement(XCircle, { size: 48, style: { color: '#ef4444', marginBottom: '1rem' } }),
                                React.createElement('p', { 
                                    style: { 
                                        fontSize: '0.875rem', 
                                        fontWeight: 700, 
                                        lineHeight: '1.75',
                                        marginBottom: '1rem',
                                        textAlign: 'center'
                                    } 
                                }, cameraError),
                                React.createElement('button', {
                                    onClick: async () => {
                                        setCameraError(null);
                                        try {
                                            const newStream = await navigator.mediaDevices.getUserMedia({ 
                                                video: { facingMode: 'user' } 
                                            });
                                            if (stream) stream.getTracks().forEach(t => t.stop());
                                            stream = newStream;
                                            if (videoRef.current) videoRef.current.srcObject = stream;
                                        } catch (err) {
                                            let errorMsg = "Camera access blocked. Please enable camera permissions in your browser.";
                                            if (err.name === 'NotAllowedError') {
                                                errorMsg = "Camera permission denied. Please allow camera access in browser settings and refresh the page.";
                                            } else if (err.name === 'NotFoundError') {
                                                errorMsg = "No camera found. Please connect a camera device.";
                                            } else if (err.name === 'NotReadableError') {
                                                errorMsg = "Camera is being used by another application. Please close other apps using the camera.";
                                            }
                                            setCameraError(errorMsg);
                                        }
                                    },
                                    style: {
                                        backgroundColor: '#3b82f6',
                                        color: 'white',
                                        padding: '0.75rem 1.5rem',
                                        borderRadius: '0.75rem',
                                        border: 'none',
                                        fontWeight: 700,
                                        fontSize: '0.875rem',
                                        cursor: 'pointer',
                                        boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)'
                                    }
                                }, 'Retry Camera Access')
                            ) : !capturedImage ? React.createElement('video', {
                                ref: videoRef,
                                autoPlay: true,
                                playsInline: true,
                                muted: true,
                                style: { width: '100%', height: '100%', objectFit: 'cover' }
                            }) : React.createElement('img', {
                                src: capturedImage,
                                style: { width: '100%', height: '100%', objectFit: 'cover' },
                                alt: 'Verification'
                            }),

                            React.createElement('div', { 
                                style: { 
                                    position: 'absolute',
                                    bottom: '1rem',
                                    left: '1rem',
                                    right: '1rem',
                                    padding: '0.75rem',
                                    borderRadius: '1rem',
                                    color: 'white',
                                    display: 'flex',
                                    flexDirection: 'column',
                                    gap: '0.25rem',
                                    textAlign: 'left',
                                    backdropFilter: 'blur(16px)',
                                    border: '1px solid rgba(255, 255, 255, 0.1)',
                                    backgroundColor: location ? 'rgba(16, 185, 129, 0.8)' : 'rgba(15, 23, 42, 0.6)'
                                } 
                            },
                                React.createElement('div', { 
                                    style: { 
                                        fontWeight: 900, 
                                        textTransform: 'uppercase', 
                                        letterSpacing: '0.15em',
                                        fontSize: '0.625rem',
                                        display: 'flex',
                                        alignItems: 'center',
                                        gap: '0.5rem'
                                    } 
                                },
                                    React.createElement(MapPin, { size: 12, style: { animation: isLocating ? 'bounce 1s infinite' : 'none' } }),
                                    isLocating ? 'Verifying Coordinates...' : location ? 'Location Verified' : 'Searching GPS Signal...'
                                ),
                                React.createElement('p', { 
                                    style: { 
                                        overflow: 'hidden',
                                        textOverflow: 'ellipsis',
                                        whiteSpace: 'nowrap',
                                        opacity: 0.8,
                                        fontWeight: 700,
                                        fontSize: '0.75rem'
                                    } 
                                }, location ? address : 'Determining physical address...')
                            )
                        ),

                        locationError && React.createElement('div', { 
                            style: { 
                                backgroundColor: locationError.includes('Warning') ? '#fef3c7' : '#fef2f2', 
                                color: locationError.includes('Warning') ? '#92400e' : '#dc2626', 
                                padding: '1rem', 
                                borderRadius: '1rem', 
                                fontSize: '0.75rem', 
                                fontWeight: 700, 
                                display: 'flex', 
                                alignItems: 'center', 
                                justifyContent: 'space-between', 
                                border: locationError.includes('Warning') ? '1px solid #fbbf24' : '1px solid #fecaca',
                                boxShadow: locationError.includes('Warning') ? 'none' : '0 4px 6px -1px rgba(239, 68, 68, 0.3)'
                            } 
                        },
                            React.createElement('div', { style: { flex: 1, textAlign: 'left', display: 'flex', alignItems: 'center', gap: '0.5rem' } },
                                locationError.includes('Warning') ? React.createElement(Info, { size: 20, style: { flexShrink: 0 } }) : (locationError.includes('Out of Range') ? React.createElement(XCircle, { size: 20, style: { flexShrink: 0 } }) : null),
                                React.createElement('span', null, locationError)
                            ),
                            React.createElement('button', {
                                onClick: getGPSLocation,
                                style: {
                                    backgroundColor: '#dc2626',
                                    color: 'white',
                                    padding: '0.5rem',
                                    borderRadius: '0.75rem',
                                    boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
                                    border: 'none',
                                    cursor: 'pointer',
                                    marginLeft: '0.5rem'
                                }
                            },
                                React.createElement(RefreshCw, { size: 18, style: { animation: isLocating ? 'spin 1s linear infinite' : 'none' } })
                            )
                        ),
                        
                        location && !locationError && React.createElement('div', {
                            style: {
                                backgroundColor: '#f0fdf4',
                                color: '#166534',
                                padding: '0.75rem',
                                borderRadius: '1rem',
                                fontSize: '0.75rem',
                                fontWeight: 700,
                                display: 'flex',
                                alignItems: 'center',
                                gap: '0.5rem',
                                border: '1px solid #86efac',
                                marginTop: '0.5rem'
                            }
                        },
                            React.createElement(CheckCircle2, { size: 16, style: { color: '#16a34a' } }),
                            (() => {
                                let nearestLoc = null;
                                let minDist = Infinity;
                                ALLOWED_LOCATIONS.forEach(loc => {
                                    const dist = calculateDistance(location.lat, location.lng, loc.lat, loc.lng);
                                    if (dist < minDist) {
                                        minDist = dist;
                                        nearestLoc = { ...loc, distance: dist };
                                    }
                                });
                                return nearestLoc && nearestLoc.distance <= nearestLoc.radius 
                                    ? `Within Range (${nearestLoc.distance.toFixed(0)}m from ${nearestLoc.name})` 
                                    : 'Location verified - Ready to mark attendance';
                            })()
                        ),

                        React.createElement('button', {
                            onClick: capturePhoto,
                            disabled: !location || isLocating || cameraError || !!capturedImage,
                            style: {
                                width: '100%',
                                padding: '1.25rem',
                                borderRadius: '1.875rem',
                                fontSize: '1.125rem',
                                fontWeight: 900,
                                letterSpacing: '-0.025em',
                                transition: 'all 0.2s',
                                boxShadow: '0 20px 25px -5px rgba(0, 0, 0, 0.1)',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                gap: '0.75rem',
                                border: 'none',
                                cursor: (!location || isLocating || cameraError || !!capturedImage) ? 'not-allowed' : 'pointer',
                                backgroundColor: (!location || isLocating || cameraError) ? '#e2e8f0' : '#4f46e5',
                                color: (!location || isLocating || cameraError) ? '#94a3b8' : 'white'
                            }
                        },
                            React.createElement(Camera, { size: 24 }),
                            capturedImage ? 'Verified!' : !location ? 'Connecting to GPS...' : `Confirm ${attendanceMode?.toUpperCase()}`
                        )
                    ),

                    view === 'admin' && React.createElement('div', { 
                        style: { 
                            display: 'flex',
                            flexDirection: 'column',
                            gap: '1.5rem',
                            paddingBottom: '3rem'
                        } 
                    },
                        React.createElement('div', { 
                            style: { 
                                display: 'flex', 
                                justifyContent: 'space-between', 
                                alignItems: 'center'
                            } 
                        },
                            React.createElement('h2', { 
                                style: { 
                                    fontSize: '1.5rem', 
                                    fontWeight: 900, 
                                    color: '#0f172a', 
                                    letterSpacing: '-0.05em',
                                    margin: 0
                                } 
                            }, 'Dashboard'),
                            React.createElement('a', {
                                href: '{{ route("attendance.history.page") }}',
                                style: {
                                    backgroundColor: '#10b981',
                                    color: 'white',
                                    padding: '0.5rem 1rem',
                                    borderRadius: '0.5rem',
                                    textDecoration: 'none',
                                    fontWeight: 700,
                                    fontSize: '0.875rem',
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: '0.5rem',
                                    transition: 'background-color 0.2s'
                                },
                                onMouseEnter: (e) => e.target.style.backgroundColor = '#059669',
                                onMouseLeave: (e) => e.target.style.backgroundColor = '#10b981'
                            },
                                React.createElement(Calendar, { size: 16 }),
                                'View History'
                            )
                        ),

                        React.createElement('div', { 
                            style: { 
                                display: 'grid', 
                                gridTemplateColumns: '1fr', 
                                gap: '1rem'
                            } 
                        },
                            employees.map(emp =>
                                React.createElement('div', { 
                                    key: emp.id,
                                    title: 'Double-click to open for edit',
                                    onDoubleClick: function() {
                                        const parts = (emp.id || '').split('_');
                                        const type = parts[0];
                                        const id = parts[1];
                                        if (!id) return;
                                        if (type === 'worker') {
                                            window.location.href = ROUTES.staff + '?openWorker=' + encodeURIComponent(id);
                                        } else if (type === 'user') {
                                            window.location.href = ROUTES.userProfile + '/' + encodeURIComponent(id);
                                        }
                                    },
                                    style: { 
                                        backgroundColor: 'white', 
                                        padding: '1.25rem', 
                                        borderRadius: '2rem', 
                                        display: 'flex', 
                                        justifyContent: 'space-between', 
                                        alignItems: 'center', 
                                        boxShadow: '0 1px 2px 0 rgba(0, 0, 0, 0.05)', 
                                        border: '1px solid #f1f5f9',
                                        cursor: 'pointer'
                                    } 
                                },
                                    React.createElement('div', { style: { display: 'flex', alignItems: 'center', gap: '1rem' } },
                                        React.createElement('div', { 
                                            style: { 
                                                backgroundColor: '#4f46e5', 
                                                padding: '0.75rem', 
                                                borderRadius: '1rem', 
                                                color: 'white', 
                                                boxShadow: '0 10px 15px -3px rgba(79, 70, 229, 0.2)'
                                            } 
                                        },
                                            React.createElement('span', { style: { fontSize: '1rem', fontWeight: 700, letterSpacing: '0.05em' } }, 'RS')
                                        ),
                                        React.createElement('div', null,
                                            React.createElement('p', { 
                                                style: { 
                                                    fontSize: '0.625rem', 
                                                    color: '#94a3b8', 
                                                    fontWeight: 900, 
                                                    textTransform: 'uppercase', 
                                                    letterSpacing: '0.1em'
                                                } 
                                            }, 'Employee'),
                                            React.createElement('p', { 
                                                style: { 
                                                    fontWeight: 900, 
                                                    color: '#1e293b'
                                                } 
                                            }, emp.name)
                                        )
                                    ),
                                    React.createElement('div', { style: { textAlign: 'right' } },
                                        React.createElement('p', { 
                                            style: { 
                                                fontSize: '0.625rem', 
                                                color: '#94a3b8', 
                                                fontWeight: 900, 
                                                textTransform: 'uppercase', 
                                                letterSpacing: '0.1em'
                                            } 
                                        }, 'Accrued Salary'),
                                        React.createElement('p', { 
                                            style: { 
                                                fontWeight: 900, 
                                                fontSize: '1.25rem', 
                                                color: '#4338ca'
                                            } 
                                        }, `Rs. ${calculateSalary(emp.id)}`)
                                    )
                                )
                            )
                        ),

                        React.createElement('div', { style: { paddingTop: '1rem' } },
                            React.createElement('h3', { 
                                style: { 
                                    fontWeight: 900, 
                                    color: '#0f172a', 
                                    fontSize: '1.125rem', 
                                    marginBottom: '1rem', 
                                    display: 'flex', 
                                    alignItems: 'center', 
                                    gap: '0.5rem',
                                    letterSpacing: '-0.025em'
                                } 
                            },
                                React.createElement(History, { style: { color: '#4f46e5' }, size: 24 }),
                                'Recent Verification Feed'
                            ),
                            React.createElement('div', { style: { display: 'flex', flexDirection: 'column', gap: '1rem' } },
                                logs.length === 0 ? React.createElement('div', { 
                                    style: { 
                                        textAlign: 'center', 
                                        padding: '4rem 0', 
                                        backgroundColor: 'white', 
                                        borderRadius: '2rem', 
                                        border: '1px dashed #e2e8f0', 
                                        color: '#94a3b8', 
                                        fontWeight: 700, 
                                        textTransform: 'uppercase', 
                                        fontSize: '0.75rem', 
                                        letterSpacing: '0.1em'
                                    } 
                                }, 'No activity found') : logs.map(log =>
                                    React.createElement('div', { 
                                        key: log.id, 
                                        style: { 
                                            backgroundColor: 'white', 
                                            padding: '1.25rem', 
                                            borderRadius: '2rem', 
                                            boxShadow: '0 1px 2px 0 rgba(0, 0, 0, 0.05)', 
                                            border: '1px solid #f8fafc', 
                                            display: 'flex',
                                            flexDirection: 'column',
                                            gap: '1rem'
                                        } 
                                    },
                                        React.createElement('div', { style: { display: 'flex', gap: '1.25rem', alignItems: 'center' } },
                                            React.createElement('div', { style: { position: 'relative' } },
                                                React.createElement('img', {
                                                    src: log.photo,
                                                    style: { width: '5rem', height: '5rem', borderRadius: '1rem', objectFit: 'cover', border: '4px solid #f8fafc', boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)' },
                                                    alt: 'log'
                                                }),
                                                React.createElement('div', { 
                                                    style: { 
                                                        position: 'absolute',
                                                        top: '-0.5rem',
                                                        right: '-0.5rem',
                                                        padding: '0.25rem 0.5rem',
                                                        borderRadius: '0.5rem',
                                                        fontSize: '0.5rem',
                                                        fontWeight: 900,
                                                        textTransform: 'uppercase',
                                                        letterSpacing: '-0.025em',
                                                        boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
                                                        backgroundColor: log.type === 'in' ? '#10b981' : '#ea580c',
                                                        color: 'white'
                                                    } 
                                                }, log.type)
                                            ),
                                            React.createElement('div', { style: { flex: 1, minWidth: 0 } },
                                                React.createElement('p', { 
                                                    style: { 
                                                        fontWeight: 900, 
                                                        color: '#1e293b', 
                                                        fontSize: '1.125rem', 
                                                        lineHeight: '1.25'
                                                    } 
                                                }, log.employeeName),
                                                React.createElement('p', { 
                                                    style: { 
                                                        fontSize: '0.625rem', 
                                                        color: '#94a3b8', 
                                                        fontWeight: 700, 
                                                        marginTop: '0.25rem', 
                                                        textTransform: 'uppercase', 
                                                        letterSpacing: '0.05em'
                                                    } 
                                                }, new Date(log.time).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' })),
                                                React.createElement('div', { 
                                                    style: { 
                                                        marginTop: '0.5rem', 
                                                        display: 'flex', 
                                                        alignItems: 'flex-start', 
                                                        gap: '0.25rem', 
                                                        color: '#4f46e5'
                                                    } 
                                                },
                                                    React.createElement(MapPin, { size: 12, style: { marginTop: '0.125rem', flexShrink: 0 } }),
                                                    React.createElement('p', { 
                                                        style: { 
                                                            fontSize: '0.625rem', 
                                                            fontWeight: 700, 
                                                            lineHeight: '1.75',
                                                            display: '-webkit-box',
                                                            WebkitLineClamp: 2,
                                                            WebkitBoxOrient: 'vertical',
                                                            overflow: 'hidden'
                                                        } 
                                                    }, log.address)
                                                )
                                            )
                                        ),
                                        log.location && log.location.lat && log.location.lng ? React.createElement('div', { style: { display: 'flex', flexDirection: 'column', gap: '0.75rem' } },
                                            React.createElement('div', {
                                                id: `map-${log.id}`,
                                                style: {
                                                    width: '100%',
                                                    height: '200px',
                                                    borderRadius: '1rem',
                                                    overflow: 'hidden',
                                                    border: '2px solid #e2e8f0',
                                                    boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
                                                    backgroundColor: '#f1f5f9',
                                                    position: 'relative'
                                                }
                                            },
                                                React.createElement('div', {
                                                    style: {
                                                        position: 'absolute',
                                                        top: '50%',
                                                        left: '50%',
                                                        transform: 'translate(-50%, -50%)',
                                                        color: '#94a3b8',
                                                        fontSize: '0.75rem',
                                                        fontWeight: 600
                                                    }
                                                }, 'Loading map...')
                                            ),
                                            React.createElement('a', {
                                                href: `https://www.google.com/maps?q=${log.location.lat},${log.location.lng}`,
                                                target: '_blank',
                                                rel: 'noreferrer',
                                                style: {
                                                    width: '100%',
                                                    backgroundColor: '#3b82f6',
                                                    color: 'white',
                                                    padding: '0.75rem',
                                                    borderRadius: '1rem',
                                                    display: 'flex',
                                                    alignItems: 'center',
                                                    justifyContent: 'center',
                                                    gap: '0.5rem',
                                                    textDecoration: 'none',
                                                    fontWeight: 700,
                                                    fontSize: '0.875rem',
                                                    boxShadow: '0 4px 6px -1px rgba(59, 130, 246, 0.3)',
                                                    transition: 'all 0.2s'
                                                },
                                                onMouseOver: (e) => { e.target.style.backgroundColor = '#2563eb'; },
                                                onMouseOut: (e) => { e.target.style.backgroundColor = '#3b82f6'; }
                                            },
                                                React.createElement(MapPin, { size: 18 }),
                                                'Open in Google Maps'
                                            )
                                        ) : React.createElement('a', {
                                            href: log.location ? `https://www.google.com/maps?q=${log.location.lat},${log.location.lng}` : '#',
                                            target: '_blank',
                                            rel: 'noreferrer',
                                            style: {
                                                width: '100%',
                                                backgroundColor: '#f1f5f9',
                                                padding: '0.75rem',
                                                borderRadius: '1rem',
                                                display: 'flex',
                                                alignItems: 'center',
                                                justifyContent: 'center',
                                                gap: '0.5rem',
                                                fontSize: '0.625rem',
                                                fontWeight: 900,
                                                color: '#475569',
                                                textTransform: 'uppercase',
                                                letterSpacing: '0.2em',
                                                transition: 'all 0.2s',
                                                textDecoration: 'none',
                                                border: '1px solid #e2e8f0'
                                            },
                                            onMouseOver: (e) => {
                                                e.target.style.backgroundColor = '#0f172a';
                                                e.target.style.color = 'white';
                                            },
                                            onMouseOut: (e) => {
                                                e.target.style.backgroundColor = '#f1f5f9';
                                                e.target.style.color = '#475569';
                                            }
                                        },
                                            React.createElement(ExternalLink, { size: 12 }),
                                            'View On Google Maps'
                                        )
                                    )
                                )
                            )
                        )
                    )
                ),

                React.createElement('footer', { 
                    style: { 
                        marginTop: '3rem', 
                        textAlign: 'center', 
                        paddingBottom: '2rem', 
                        padding: '0 1.5rem'
                    } 
                },
                    React.createElement('div', { 
                        style: { 
                            backgroundColor: '#cbd5e1', 
                            height: '1px', 
                            width: '6rem', 
                            margin: '0 auto 1rem',
                            opacity: 0.5
                        } 
                    }),
                    React.createElement('p', { 
                        style: { 
                            color: '#94a3b8', 
                            fontSize: '0.5625rem', 
                            textTransform: 'uppercase', 
                            fontWeight: 900, 
                            letterSpacing: '0.3em'
                        } 
                    }, 'Encrypted Attendance Protocol')
                )
            );
        };

        // Wait for Babel to process the script
        function tryRender() {
            try {
                const rootElement = document.getElementById('root');
                if (!rootElement) {
                    console.error('Root element not found');
                    setTimeout(tryRender, 200);
                    return;
                }
                
                // Check if React is loaded
                if (typeof React === 'undefined' || typeof ReactDOM === 'undefined') {
                    console.error('React or ReactDOM not loaded');
                    rootElement.innerHTML = '<div style="padding: 2rem; text-align: center; color: #ef4444;"><h3>Loading React...</h3><p>Please wait...</p></div>';
                    setTimeout(tryRender, 200);
                    return;
                }
                
                // Check if App is defined (Babel processed)
                if (typeof App === 'undefined') {
                    console.log('Waiting for Babel to process...');
                    setTimeout(tryRender, 200);
                    return;
                }
                
                console.log('Initializing React app...');
                const root = ReactDOM.createRoot(rootElement);
                root.render(React.createElement(App));
                console.log('React app rendered successfully');
            } catch (error) {
                console.error('React rendering error:', error);
                const rootElement = document.getElementById('root');
                if (rootElement) {
                    rootElement.innerHTML = `
                        <div style="padding: 2rem; text-align: center; color: #ef4444;">
                            <h3>Error Loading Attendance System</h3>
                            <p>Please check console for details.</p>
                            <p style="font-size: 0.875rem; color: #64748b; margin-top: 1rem;">${error.message}</p>
                            <pre style="text-align: left; margin-top: 1rem; font-size: 0.75rem; background: #f1f5f9; padding: 1rem; border-radius: 0.5rem; overflow: auto;">${error.stack || error.toString()}</pre>
                        </div>
                    `;
                }
            }
        }
        
        // Start trying to render after scripts load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(tryRender, 500);
            });
        } else {
            setTimeout(tryRender, 500);
        }
    </script>
    <style>
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</body>
</html>
