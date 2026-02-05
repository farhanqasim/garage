<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Pro - Employee Attendance System</title>
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
                root.innerHTML = '<div style="padding: 2rem; text-align: center;"><div style="width: 48px; height: 48px; border: 4px solid #3b82f6; border-top: 4px solid transparent; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem;"></div><p style="color: #64748b; font-weight: 600;">Loading Attendance System...</p></div>';
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
    </style>
</head>
<body>
    <div id="root"></div>
    
    <script type="text/babel" data-presets="react">
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
            }, paths.map(p => React.createElement(p.tag, p.attrs || {})));
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

        const EMPLOYEES = [
            { id: 1, name: 'Waseem', rate: 500 },
            { id: 2, name: 'Qamar', rate: 500 },
            { id: 3, name: 'Salman', rate: 500 },
            { id: 4, name: 'Zia', rate: 500 },
            { id: 5, name: 'Junaid', rate: 500 },
        ];

        const App = () => {
            const [view, setView] = useState('home');
            const [selectedEmployee, setSelectedEmployee] = useState(null);
            const [attendanceMode, setAttendanceMode] = useState(null);
            const [logs, setLogs] = useState([]);
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

            useEffect(() => {
                const savedLogs = localStorage.getItem('attendance_logs');
                if (savedLogs) setLogs(JSON.parse(savedLogs));
            }, []);

            useEffect(() => {
                localStorage.setItem('attendance_logs', JSON.stringify(logs));
            }, [logs]);

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
                        const coords = { lat: pos.coords.latitude, lng: pos.coords.longitude };
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
                        enableHighAccuracy: true, 
                        timeout: 15000,
                        maximumAge: 60000 // Accept cached location up to 1 minute old
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

            const saveAttendance = (photo) => {
                const newEntry = {
                    id: Date.now(),
                    employeeId: selectedEmployee.id,
                    employeeName: selectedEmployee.name,
                    type: attendanceMode,
                    time: new Date().toISOString(),
                    location: location,
                    address: address,
                    photo: photo
                };
                setLogs([newEntry, ...logs]);
                setStatusMessage({ text: "Attendance Recorded!", type: "success" });
                setTimeout(() => {
                    setView('home');
                    setCapturedImage(null);
                    setSelectedEmployee(null);
                    setLocation(null);
                    setStatusMessage(null);
                }, 2000);
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
                return (totalHours * EMPLOYEES.find(e => e.id === empId).rate).toLocaleString(undefined, { minimumFractionDigits: 2 });
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
                        maxWidth: '28rem', 
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
                            }, `${EMPLOYEES.length} Total`)
                        ),

                        EMPLOYEES.map((emp) =>
                            React.createElement('div', { 
                                key: emp.id, 
                                style: { 
                                    backgroundColor: 'white', 
                                    padding: '1.25rem', 
                                    borderRadius: '1.875rem', 
                                    boxShadow: '0 1px 2px 0 rgba(0, 0, 0, 0.05)', 
                                    border: '1px solid #f1f5f9', 
                                    display: 'flex', 
                                    justifyContent: 'space-between', 
                                    alignItems: 'center'
                                } 
                            },
                                React.createElement('div', { style: { display: 'flex', alignItems: 'center', gap: '1rem' } },
                                    React.createElement('div', { 
                                        style: { 
                                            width: '2.5rem', 
                                            height: '2.5rem', 
                                            borderRadius: '50%', 
                                            backgroundColor: '#f1f5f9', 
                                            display: 'flex', 
                                            alignItems: 'center', 
                                            justifyContent: 'center', 
                                            fontWeight: 900, 
                                            color: '#94a3b8'
                                        } 
                                    }, emp.name.charAt(0)),
                                    React.createElement('span', { 
                                        style: { 
                                            fontSize: '1.125rem', 
                                            fontWeight: 700, 
                                            color: '#1e293b', 
                                            letterSpacing: '-0.025em'
                                        } 
                                    }, emp.name)
                                ),
                                React.createElement('div', { style: { display: 'flex', gap: '0.5rem' } },
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
                                backgroundColor: '#fef2f2', 
                                color: '#dc2626', 
                                padding: '1rem', 
                                borderRadius: '1rem', 
                                fontSize: '0.75rem', 
                                fontWeight: 700, 
                                display: 'flex', 
                                alignItems: 'center', 
                                justifyContent: 'space-between', 
                                border: '1px solid #fecaca'
                            } 
                        },
                            React.createElement('span', { style: { flex: 1, textAlign: 'left' } }, locationError),
                            React.createElement('button', {
                                onClick: getGPSLocation,
                                style: {
                                    backgroundColor: '#dc2626',
                                    color: 'white',
                                    padding: '0.5rem',
                                    borderRadius: '0.75rem',
                                    boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
                                    border: 'none',
                                    cursor: 'pointer'
                                }
                            },
                                React.createElement(RefreshCw, { size: 18, style: { animation: isLocating ? 'spin 1s linear infinite' : 'none' } })
                            )
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
                                    letterSpacing: '-0.05em'
                                } 
                            }, 'Dashboard'),
                            React.createElement('button', {
                                onClick: () => { if(window.confirm('Delete all data logs?')) setLogs([]) },
                                style: {
                                    color: '#ef4444',
                                    fontSize: '0.625rem',
                                    fontWeight: 900,
                                    textTransform: 'uppercase',
                                    letterSpacing: '0.1em',
                                    background: 'none',
                                    border: 'none',
                                    cursor: 'pointer'
                                }
                            }, 'Clear Logs')
                        ),

                        React.createElement('div', { 
                            style: { 
                                display: 'grid', 
                                gridTemplateColumns: '1fr', 
                                gap: '1rem'
                            } 
                        },
                            EMPLOYEES.map(emp =>
                                React.createElement('div', { 
                                    key: emp.id, 
                                    style: { 
                                        backgroundColor: 'white', 
                                        padding: '1.25rem', 
                                        borderRadius: '2rem', 
                                        display: 'flex', 
                                        justifyContent: 'space-between', 
                                        alignItems: 'center', 
                                        boxShadow: '0 1px 2px 0 rgba(0, 0, 0, 0.05)', 
                                        border: '1px solid #f1f5f9'
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
                                            React.createElement(DollarSign, { size: 20 })
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
                                        React.createElement('a', {
                                            href: `https://www.google.com/maps?q=${log.location.lat},${log.location.lng}`,
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
