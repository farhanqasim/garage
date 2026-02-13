<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Elite Car Wash</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- React and ReactDOM -->
    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="{{asset('assets/plugins/tabler-icons/tabler-icons.min.css')}}">
    
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
                'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue',
                sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>
</head>
<body>
    <div id="root"></div>
    
    <script type="text/babel">
        const { useState, useEffect, useRef } = React;
        
        // Routes
        const ROUTES = {
            carWashHome: '{{ route("employee.home") }}',
            profile: '{{ route("employee.profile", auth()->user()->id) }}',
            profileUpdate: '{{ route("employee.profile.update", auth()->user()->id) }}',
            passwordVerify: '{{ route("employee.password.verify") }}',
            savePattern: '{{ route("save.pattern") }}',
            saveFingerprint: '{{ route("save.fingerprint") }}',
        };
        
        // User and Branch Info
        const branchName = @json($branchName ?? 'No Branch');
        const userName = @json($userName ?? 'Guest');
        const user = @json($user);
        const assetUrl = '{{ asset("") }}';
        
        // Icon Components
        const createIcon = (iconClass, props) => {
            const iconSize = props.size || 20;
            const className = props.className || "";
            const styleObj = {};
            styleObj.fontSize = iconSize + 'px';
            return React.createElement('i', { 
                className: iconClass + ' ' + className, 
                style: styleObj
            });
        };
        
        const UserCircle = (props) => createIcon('ti ti-user-circle', props);
        const ArrowLeft = (props) => createIcon('ti ti-arrow-left', props);
        const Camera = (props) => createIcon('ti ti-camera', props);
        const Lock = (props) => createIcon('ti ti-lock', props);
        const Grid3X3 = (props) => createIcon('ti ti-grid-3x3', props);
        const Fingerprint = (props) => createIcon('ti ti-fingerprint', props);
        
        const App = () => {
            const [formData, setFormData] = useState({
                name: user.name || '',
                email: user.email || '',
                phone: user.phone || '',
                profile_img: null,
                old_password: '',
                new_password: '',
                new_password_confirmation: '',
            });
            
            const [showPasswordFields, setShowPasswordFields] = useState(false);
            const [passwordVerified, setPasswordVerified] = useState(false);
            const [patternDots, setPatternDots] = useState([]);
            const [isDrawing, setIsDrawing] = useState(false);
            const [firstPattern, setFirstPattern] = useState(null);
            const [confirmedPattern, setConfirmedPattern] = useState(null);
            const [patternError, setPatternError] = useState('');
            const [patternSuccess, setPatternSuccess] = useState('');
            const [patternInstruction, setPatternInstruction] = useState('Draw your pattern (minimum 3 dots)');
            const patternDotsRef = useRef([]);
            const [fingerprintProgress, setFingerprintProgress] = useState(0);
            const [fingerprintStatus, setFingerprintStatus] = useState('Hold to Scan Finger');
            const [fingerprintSuccess, setFingerprintSuccess] = useState('');
            const [loading, setLoading] = useState(false);
            const [successMessage, setSuccessMessage] = useState('');
            
            // Show success message if redirected with success
            useEffect(() => {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('success')) {
                    setSuccessMessage('Profile updated successfully!');
                    setTimeout(() => setSuccessMessage(''), 3000);
                }
            }, []);
            
            const handleInputChange = (e) => {
                const { name, value, files } = e.target;
                if (files && files[0]) {
                    setFormData(prev => ({ ...prev, [name]: files[0] }));
                } else {
                    setFormData(prev => ({ ...prev, [name]: value }));
                }
            };
            
            const handleVerifyPassword = async () => {
                try {
                    const response = await fetch(ROUTES.passwordVerify, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ old_password: formData.old_password })
                    });
                    const data = await response.json();
                    if (data.success) {
                        setPasswordVerified(true);
                        setShowPasswordFields(true);
                    } else {
                        alert(data.message || 'Invalid old password');
                    }
                } catch (error) {
                    alert('Error verifying password');
                }
            };
            
            const handleSubmit = async (e) => {
                e.preventDefault();
                setLoading(true);
                
                const formDataToSend = new FormData();
                formDataToSend.append('name', formData.name);
                formDataToSend.append('email', formData.email);
                formDataToSend.append('phone', formData.phone);
                if (formData.profile_img) {
                    formDataToSend.append('profile_img', formData.profile_img);
                }
                if (formData.new_password && passwordVerified) {
                    formDataToSend.append('new_password', formData.new_password);
                    formDataToSend.append('new_password_confirmation', formData.new_password_confirmation);
                }
                formDataToSend.append('_method', 'PUT');
                formDataToSend.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                
                try {
                    const response = await fetch(ROUTES.profileUpdate, {
                        method: 'POST',
                        body: formDataToSend
                    });
                    
                    if (response.ok) {
                        setSuccessMessage('Profile updated successfully!');
                        setTimeout(() => {
                            window.location.href = ROUTES.profile + '?success=1';
                        }, 1500);
                    } else {
                        alert('Failed to update profile');
                    }
                } catch (error) {
                    alert('Error updating profile');
                } finally {
                    setLoading(false);
                }
            };
            
            // Pattern Lock Functions (use ref for touch to avoid stale state)
            const handlePatternDotClick = (index) => {
                if (!isDrawing) {
                    setIsDrawing(true);
                    patternDotsRef.current = [index];
                    setPatternDots([index]);
                }
            };
            
            const handlePatternDotEnter = (index) => {
                if (isDrawing && !patternDotsRef.current.includes(index)) {
                    patternDotsRef.current = [...patternDotsRef.current, index];
                    setPatternDots(patternDotsRef.current);
                }
            };
            
            const handlePatternMouseUp = () => {
                if (!isDrawing) return;
                setIsDrawing(false);
                const dots = patternDotsRef.current;
                if (dots.length < 3) {
                    setPatternError('Pattern must have at least 3 dots');
                    return;
                }
                const currentPattern = dots.join(',');
                if (!firstPattern) {
                    setFirstPattern(currentPattern);
                    setPatternError('');
                    setPatternInstruction('Draw your pattern again to confirm');
                    patternDotsRef.current = [];
                    setPatternDots([]);
                } else {
                    if (currentPattern === firstPattern) {
                        setConfirmedPattern(firstPattern);
                        setPatternError('');
                        setPatternInstruction('Pattern confirmed! Click Save Pattern to save.');
                        patternDotsRef.current = [];
                        setPatternDots([]);
                    } else {
                        setPatternError('Patterns do not match. Please draw again from the beginning.');
                        setFirstPattern(null);
                        setConfirmedPattern(null);
                        patternDotsRef.current = [];
                        setPatternDots([]);
                        setPatternInstruction('Draw your pattern (minimum 3 dots)');
                        setTimeout(() => setPatternError(''), 3000);
                    }
                }
            };
            
            const handleSavePattern = async () => {
                const pattern = confirmedPattern || (patternDots.length >= 3 ? patternDots.join(',') : null);
                if (!pattern || pattern.split(',').length < 3) {
                    setPatternError('Please draw your pattern twice to confirm, then click Save Pattern.');
                    return;
                }
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const response = await fetch(ROUTES.savePattern, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ pattern, _token: csrfToken })
                    });
                    
                    const text = await response.text();
                    let data = {};
                    try {
                        data = (text && text.trim()) ? JSON.parse(text) : {};
                    } catch (e) {
                        if (!response.ok) {
                            throw new Error('Server error: ' + response.status);
                        }
                        throw new Error('Invalid response. Please refresh and try again.');
                    }
                    if (response.status === 302 || response.status === 301) {
                        throw new Error('Session expired. Please login again.');
                    }
                    if (data.success) {
                        setPatternSuccess('Pattern saved successfully!');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        setPatternError(data.message || 'Failed to save pattern');
                    }
                } catch (error) {
                    setPatternError('Error saving pattern');
                }
            };
            
            // Fingerprint Functions
            const handleFingerprintStart = () => {
                setFingerprintStatus('Scanning...');
                let progress = 0;
                const interval = setInterval(() => {
                    progress += 5;
                    setFingerprintProgress(progress);
                    if (progress >= 100) {
                        clearInterval(interval);
                        handleSaveFingerprint();
                    }
                }, 50);
            };
            
            const handleFingerprintStop = () => {
                if (fingerprintProgress < 100) {
                    setFingerprintProgress(0);
                    setFingerprintStatus('Hold to Scan Finger');
                }
            };
            
            const handleSaveFingerprint = async () => {
                const fingerprintData = 'fingerprint_' + user.email + '_' + Date.now();
                try {
                    const response = await fetch(ROUTES.saveFingerprint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ fingerprint_data: fingerprintData })
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        setFingerprintSuccess('Fingerprint saved successfully!');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        setFingerprintStatus(data.message || 'Failed to save fingerprint');
                    }
                } catch (error) {
                    setFingerprintStatus('Error saving fingerprint');
                }
            };
            
            return React.createElement('div', { className: 'min-h-screen bg-slate-50' },
                // Header
                React.createElement('header', { className: 'bg-white border-b sticky top-0 z-50' },
                    React.createElement('div', { className: 'max-w-4xl mx-auto px-4 py-4 flex items-center justify-between' },
                        React.createElement('a', { 
                            href: ROUTES.carWashHome,
                            className: 'flex items-center space-x-2 text-blue-600 hover:text-blue-700'
                        },
                            React.createElement(ArrowLeft, { size: 20 }),
                            React.createElement('span', { className: 'font-bold' }, 'Back to Dashboard')
                        ),
                        React.createElement('h1', { className: 'text-xl font-black text-slate-800' }, 'My Profile')
                    )
                ),
                
                // Main Content
                React.createElement('main', { className: 'max-w-4xl mx-auto px-4 py-6' },
                    // Success Message
                    successMessage && React.createElement('div', { 
                        className: 'mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800'
                    }, successMessage),
                    
                    // Profile Form
                    React.createElement('div', { className: 'bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6' },
                        React.createElement('h2', { className: 'text-lg font-bold text-slate-800 mb-6' }, 'Update Profile'),
                        React.createElement('form', { onSubmit: handleSubmit },
                            // Name and Phone
                            React.createElement('div', { className: 'grid grid-cols-1 md:grid-cols-2 gap-4 mb-4' },
                                React.createElement('div', {},
                                    React.createElement('label', { className: 'block text-sm font-bold text-slate-700 mb-2' }, 'Name'),
                                    React.createElement('input', {
                                        type: 'text',
                                        name: 'name',
                                        value: formData.name,
                                        onChange: handleInputChange,
                                        className: 'w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none',
                                        required: true
                                    })
                                ),
                                React.createElement('div', {},
                                    React.createElement('label', { className: 'block text-sm font-bold text-slate-700 mb-2' }, 'Phone'),
                                    React.createElement('input', {
                                        type: 'text',
                                        name: 'phone',
                                        value: formData.phone,
                                        onChange: handleInputChange,
                                        className: 'w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none',
                                        required: true
                                    })
                                )
                            ),
                            
                            // Email
                            React.createElement('div', { className: 'mb-4' },
                                React.createElement('label', { className: 'block text-sm font-bold text-slate-700 mb-2' }, 'Email'),
                                React.createElement('input', {
                                    type: 'email',
                                    name: 'email',
                                    value: formData.email,
                                    onChange: handleInputChange,
                                    className: 'w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none',
                                    required: true
                                })
                            ),
                            
                            // Profile Image
                            React.createElement('div', { className: 'mb-4' },
                                React.createElement('label', { className: 'block text-sm font-bold text-slate-700 mb-2' }, 'Profile Image'),
                                React.createElement('div', { className: 'flex items-center space-x-4' },
                                    user.profile_img && React.createElement('img', {
                                        src: assetUrl + user.profile_img,
                                        alt: 'Profile',
                                        className: 'w-20 h-20 rounded-full object-cover border-2 border-slate-200'
                                    }),
                                    React.createElement('input', {
                                        type: 'file',
                                        name: 'profile_img',
                                        onChange: handleInputChange,
                                        accept: 'image/*',
                                        className: 'px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none'
                                    })
                                )
                            ),
                            
                            // Password Section
                            React.createElement('div', { className: 'border-t pt-6 mt-6' },
                                React.createElement('h3', { className: 'text-md font-bold text-slate-800 mb-4' }, 'Change Password'),
                                React.createElement('div', { className: 'mb-4' },
                                    React.createElement('label', { className: 'block text-sm font-bold text-slate-700 mb-2' }, 'Old Password'),
                                    React.createElement('div', { className: 'flex space-x-2' },
                                        React.createElement('input', {
                                            type: 'password',
                                            name: 'old_password',
                                            value: formData.old_password,
                                            onChange: handleInputChange,
                                            className: 'flex-1 px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none'
                                        }),
                                        React.createElement('button', {
                                            type: 'button',
                                            onClick: handleVerifyPassword,
                                            className: 'px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-bold'
                                        }, 'Verify')
                                    )
                                ),
                                showPasswordFields && React.createElement('div', { className: 'grid grid-cols-1 md:grid-cols-2 gap-4' },
                                    React.createElement('div', {},
                                        React.createElement('label', { className: 'block text-sm font-bold text-slate-700 mb-2' }, 'New Password'),
                                        React.createElement('input', {
                                            type: 'password',
                                            name: 'new_password',
                                            value: formData.new_password,
                                            onChange: handleInputChange,
                                            className: 'w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none'
                                        })
                                    ),
                                    React.createElement('div', {},
                                        React.createElement('label', { className: 'block text-sm font-bold text-slate-700 mb-2' }, 'Confirm Password'),
                                        React.createElement('input', {
                                            type: 'password',
                                            name: 'new_password_confirmation',
                                            value: formData.new_password_confirmation,
                                            onChange: handleInputChange,
                                            className: 'w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none'
                                        })
                                    )
                                )
                            ),
                            
                            // Submit Button
                            React.createElement('div', { className: 'mt-6 flex justify-end' },
                                React.createElement('button', {
                                    type: 'submit',
                                    disabled: loading,
                                    className: 'px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-bold disabled:opacity-50'
                                }, loading ? 'Updating...' : 'Update Profile')
                            )
                        )
                    ),
                    
                    // Security Settings
                    React.createElement('div', { className: 'bg-white rounded-2xl shadow-sm border border-slate-200 p-6' },
                        React.createElement('h2', { className: 'text-lg font-bold text-slate-800 mb-6' }, 'Login Security Settings'),
                        
                        // Pattern Lock
                        React.createElement('div', { className: 'mb-6 pb-6 border-b' },
                            React.createElement('div', { className: 'flex items-center justify-between mb-4' },
                                React.createElement('h3', { className: 'text-md font-bold text-slate-800 flex items-center' },
                                    React.createElement(Grid3X3, { size: 18, className: 'mr-2' }),
                                    'Pattern Lock'
                                ),
                                user.pattern_lock ? 
                                    React.createElement('span', { className: 'px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold' }, 'Set') :
                                    React.createElement('span', { className: 'px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold' }, 'Not Set')
                            ),
                            React.createElement('div', { className: 'text-center' },
                                React.createElement('p', { className: 'text-sm text-slate-600 mb-4' }, patternInstruction),
                                React.createElement('div', {
                                    className: 'inline-grid grid-cols-3 gap-4 p-6 bg-slate-50 rounded-2xl',
                                    onMouseUp: handlePatternMouseUp,
                                    onTouchStart: (e) => {
                                        const touch = e.touches[0];
                                        const el = document.elementFromPoint(touch.clientX, touch.clientY);
                                        if (el && el.getAttribute('data-dot-index') !== null) {
                                            const i = parseInt(el.getAttribute('data-dot-index'));
                                            handlePatternDotClick(i);
                                        }
                                    },
                                    onTouchMove: (e) => {
                                        if (!isDrawing) return;
                                        const touch = e.touches[0];
                                        const el = document.elementFromPoint(touch.clientX, touch.clientY);
                                        if (el && el.getAttribute('data-dot-index') !== null) {
                                            const i = parseInt(el.getAttribute('data-dot-index'));
                                            handlePatternDotEnter(i);
                                        }
                                    },
                                    onTouchEnd: (e) => { e.preventDefault(); handlePatternMouseUp(); }
                                },
                                    Array.from({ length: 9 }, (_, i) =>
                                        React.createElement('button', {
                                            key: i,
                                            type: 'button',
                                            'data-dot-index': i,
                                            onMouseDown: () => handlePatternDotClick(i),
                                            onMouseEnter: () => handlePatternDotEnter(i),
                                            className: `w-12 h-12 rounded-full border-2 transition-all touch-none ${
                                                patternDots.includes(i) 
                                                    ? 'bg-blue-600 border-blue-600 scale-110' 
                                                    : 'bg-white border-slate-300'
                                            }`
                                        })
                                    )
                                ),
                                patternError && React.createElement('p', { className: 'text-red-600 text-sm mt-2' }, patternError),
                                patternSuccess && React.createElement('p', { className: 'text-green-600 text-sm mt-2' }, patternSuccess),
                                confirmedPattern && React.createElement('button', {
                                    onClick: handleSavePattern,
                                    className: 'mt-4 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-bold'
                                }, 'Save Pattern')
                            )
                        ),
                        
                        // Fingerprint
                        React.createElement('div', {},
                            React.createElement('div', { className: 'flex items-center justify-between mb-4' },
                                React.createElement('h3', { className: 'text-md font-bold text-slate-800 flex items-center' },
                                    React.createElement(Fingerprint, { size: 18, className: 'mr-2' }),
                                    'Fingerprint / Biometric'
                                ),
                                user.fingerprint_data ? 
                                    React.createElement('span', { className: 'px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold' }, 'Set') :
                                    React.createElement('span', { className: 'px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold' }, 'Not Set')
                            ),
                            React.createElement('div', { className: 'text-center' },
                                React.createElement('button', {
                                    type: 'button',
                                    onMouseDown: handleFingerprintStart,
                                    onMouseUp: handleFingerprintStop,
                                    onTouchStart: (e) => { e.preventDefault(); handleFingerprintStart(); },
                                    onTouchEnd: (e) => { e.preventDefault(); handleFingerprintStop(); },
                                    className: 'w-32 h-32 mx-auto rounded-full bg-slate-100 flex items-center justify-center hover:bg-blue-100 transition-colors'
                                },
                                    React.createElement(Fingerprint, { size: 48, className: 'text-blue-600' })
                                ),
                                fingerprintProgress > 0 && React.createElement('div', { className: 'mt-4 max-w-xs mx-auto' },
                                    React.createElement('div', { className: 'w-full bg-slate-200 rounded-full h-2' },
                                        React.createElement('div', {
                                            className: 'bg-blue-600 h-2 rounded-full transition-all',
                                            style: { width: `${fingerprintProgress}%` }
                                        })
                                    )
                                ),
                                React.createElement('p', { className: 'text-sm text-slate-600 mt-4' }, fingerprintStatus),
                                fingerprintSuccess && React.createElement('p', { className: 'text-green-600 text-sm mt-2' }, fingerprintSuccess)
                            )
                        )
                    )
                )
            );
        };
        
        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(React.createElement(App));
    </script>
</body>
</html>
