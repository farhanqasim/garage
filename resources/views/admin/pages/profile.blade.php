@extends('layouts.app')
@section('title', __('Profile'))
@section('content')
    <div class="content">
        <section class="section">
            <div class="section-header mb-4">
                <h1>Update Profile</h1>
            </div>

            <div class="section-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ti ti-check me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        @foreach($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <div class="row">
                    <div class="col-12 col-md-8 col-lg-12">
                        <div class="card">
                            <form action="{{ route('user.profile.update', auth()->user()->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="card-header">
                                    <h4>Update Profile</h4>
                                </div>

                                <div class="card-body">
                                    <!-- First Name & Last Name -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Name</label>
                                            <input type="text" class="form-control" name="name" value="{{ auth()->user()->name }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Phone</label>
                                            <input type="text" class="form-control" name="phone" value="{{ auth()->user()->phone }}" required>
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" value="{{ auth()->user()->email }}" required>
                                        </div>
                                    </div>

                                    <!-- Profile Image -->
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Profile Image</label>
                                            <input type="file" class="form-control" name="profile_img">
                                            @if(auth()->user()->profile_img)
                                                <img src="{{ asset(auth()->user()->profile_img) }}" alt="Profile Image" class="mt-2" width="100">
                                            @endif
                                        </div>
                                    </div>

                                    <hr>

                                    <!-- Change Password Section -->
                                    <h5>Change Password</h5>

                                    <!-- Old Password -->
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Old Password</label>
                                            <input type="password" class="form-control" name="old_password" id="old_password">
                                            <button type="button" class="btn btn-primary mt-2" id="verify_password">Verify</button>
                                            @error('old_password')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- New Password & Confirm Password (Initially Hidden) -->
                                    <div id="new-password-fields" style="display: none;">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">New Password</label>
                                                <input type="password" class="form-control" name="new_password">
                                                @error('new_password')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Confirm Password</label>
                                                <input type="password" class="form-control" name="new_password_confirmation">
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </div>
                            </form>
                        </div>

                        <!-- Pattern & Fingerprint Setup Card -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h4>Login Security Settings</h4>
                                <p class="text-muted mb-0">Set up Pattern Lock and Fingerprint for quick login</p>
                            </div>

                            <div class="card-body">
                                <!-- Pattern Lock Setup -->
                                <div class="mb-4">
                                    <h5 class="mb-3">
                                        <i class="ti ti-grid-3x3 me-2"></i>Pattern Lock
                                        @if(auth()->user()->pattern_lock)
                                            <span class="badge bg-success ms-2">Set</span>
                                        @else
                                            <span class="badge bg-warning ms-2">Not Set</span>
                                        @endif
                                    </h5>
                                    
                                    @if(auth()->user()->pattern_lock)
                                        <div class="alert alert-info">
                                            <i class="ti ti-info-circle me-2"></i>Pattern is already set. Draw a new pattern below to update it.
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="ti ti-alert-triangle me-2"></i>Pattern is not set. Draw a pattern below to enable pattern login.
                                        </div>
                                    @endif

                                    <div class="text-center mb-3">
                                        <p class="text-muted small mb-3" id="patternSetupInstruction">Draw your pattern (minimum 3 dots)</p>
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="pattern-lock-container" style="background: #f8f9fa; padding: 2rem; border-radius: 1rem; display: inline-block;">
                                                <div class="pattern-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                                                    @for($i = 0; $i < 9; $i++)
                                                        <button type="button" class="pattern-dot-setup" data-index="{{ $i }}" style="width: 50px; height: 50px; border-radius: 50%; border: 2px solid #dee2e6; background: #fff; cursor: pointer; transition: all 0.2s;">
                                                        </button>
                                                    @endfor
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <form id="savePatternForm" action="{{ route('save.pattern') }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <input type="hidden" name="pattern" id="savePatternInput" value="">
                                                    <button type="submit" class="btn btn-primary" id="savePatternBtn" disabled style="display: none;">
                                                        <i class="ti ti-device-floppy me-2" id="savePatternIcon"></i><span id="savePatternText">Save Pattern</span>
                                                    </button>
                                                </form>
                                                @if(auth()->user()->pattern_lock)
                                                    <button type="button" class="btn btn-danger ms-2" id="clearPatternBtn">
                                                        <i class="ti ti-trash me-2"></i>Clear Pattern
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-danger small mt-3" id="patternSetupError" style="display: none;"></p>
                                        <p class="text-success small mt-3" id="patternSetupSuccess" style="display: none;"></p>
                                    </div>
                                </div>

                                <hr>

                                <!-- Fingerprint Setup -->
                                <div class="mb-4">
                                    <h5 class="mb-3">
                                        <i class="ti ti-fingerprint me-2"></i>Fingerprint / Biometric
                                        @if(auth()->user()->fingerprint_data)
                                            <span class="badge bg-success ms-2">Set</span>
                                        @else
                                            <span class="badge bg-warning ms-2">Not Set</span>
                                        @endif
                                    </h5>
                                    
                                    @if(auth()->user()->fingerprint_data)
                                        <div class="alert alert-info">
                                            <i class="ti ti-info-circle me-2"></i>Fingerprint is already set. Scan again to update it.
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="ti ti-alert-triangle me-2"></i>Fingerprint is not set. Scan your fingerprint below to enable fingerprint login.
                                        </div>
                                    @endif

                                    <div class="text-center mb-3">
                                        <form id="saveFingerprintForm" action="{{ route('save.fingerprint') }}" method="POST" style="display: none;">
                                            @csrf
                                            <input type="hidden" name="fingerprint_data" id="fingerprintDataInput" value="">
                                        </form>
                                        <div class="fingerprint-container mb-3">
                                            <button type="button" id="fingerprintSetupBtn" class="btn btn-link p-0" style="border: none; background: none;">
                                                <div class="fingerprint-icon-setup" style="width: 120px; height: 120px; margin: 0 auto; border-radius: 50%; background: #f8f9fa; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                                                    <i class="ti ti-fingerprint" style="font-size: 64px; color: #3b82f6;"></i>
                                                </div>
                                            </button>
                                            <div class="progress mt-3" id="fingerprintSetupProgress" style="display: none; max-width: 200px; margin: 0 auto;">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <p class="text-muted small mb-2" id="fingerprintSetupStatus">Hold to Scan Finger</p>
                                        <p class="text-success small mt-3" id="fingerprintSetupSuccess" style="display: none;"></p>
                                        @if(auth()->user()->fingerprint_data)
                                            <form id="clearFingerprintForm" action="{{ route('save.fingerprint') }}" method="POST" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="fingerprint_data" value="">
                                                <button type="submit" class="btn btn-danger mt-3" id="clearFingerprintBtn" onclick="return confirm('Are you sure you want to clear your fingerprint? You will not be able to login using fingerprint until you set a new one.');">
                                                    <i class="ti ti-trash me-2"></i>Clear Fingerprint
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
<script>
        document.getElementById('verify_password').addEventListener('click', function () {
            let oldPassword = document.getElementById('old_password').value;

            fetch("{{ route('user.password.verify') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ old_password: oldPassword })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('new-password-fields').style.display = 'block';
                } else {
                    alert(data.message);
                }
            });
        });

        // Pattern Lock Setup - Double confirm flow (draw twice, both must match)
        let patternDotsSetup = [];
        let isDrawingSetup = false;
        let firstPattern = null;      // First draw (to confirm)
        let confirmedPattern = null;  // When both draws match
        const patternDotsSetupElements = document.querySelectorAll('.pattern-dot-setup');
        const savePatternBtn = document.getElementById('savePatternBtn');
        const patternSetupError = document.getElementById('patternSetupError');
        const patternSetupSuccess = document.getElementById('patternSetupSuccess');
        const patternSetupInstruction = document.getElementById('patternSetupInstruction');
        const clearPatternBtn = document.getElementById('clearPatternBtn');

        function resetPatternSetup() {
            firstPattern = null;
            confirmedPattern = null;
            patternDotsSetup = [];
            updatePatternSetupDisplay();
            if (savePatternBtn) {
                savePatternBtn.disabled = true;
                savePatternBtn.style.display = 'none';
            }
            if (patternSetupInstruction) patternSetupInstruction.textContent = 'Draw your pattern (minimum 3 dots)';
            if (patternSetupSuccess) { patternSetupSuccess.style.display = 'none'; patternSetupSuccess.textContent = ''; }
        }

        function handlePatternDrawComplete() {
            if (patternDotsSetup.length < 3) {
                patternSetupError.textContent = 'Pattern must have at least 3 dots';
                patternSetupError.style.display = 'block';
                if (savePatternBtn) savePatternBtn.disabled = true;
                return;
            }
            const currentPattern = patternDotsSetup.join(',');
            
            if (!firstPattern) {
                // First draw - store and ask for confirm
                firstPattern = currentPattern;
                patternSetupError.style.display = 'none';
                patternSetupInstruction.textContent = 'Draw your pattern again to confirm';
                patternSetupInstruction.classList.remove('text-muted');
                patternSetupInstruction.classList.add('text-info');
                patternDotsSetup = [];
                updatePatternSetupDisplay();
                if (savePatternBtn) savePatternBtn.disabled = true;
            } else {
                // Second draw - compare
                if (currentPattern === firstPattern) {
                    confirmedPattern = firstPattern;
                    patternSetupError.style.display = 'none';
                    patternSetupInstruction.textContent = 'Pattern confirmed! Click Save Pattern to save.';
                    patternSetupInstruction.classList.remove('text-muted', 'text-info');
                    patternSetupInstruction.classList.add('text-success');
                    if (savePatternBtn) {
                        savePatternBtn.disabled = false;
                        savePatternBtn.style.display = 'inline-block';
                    }
                } else {
                    patternSetupError.textContent = 'Patterns do not match. Please draw again from the beginning.';
                    patternSetupError.style.display = 'block';
                    firstPattern = null;
                    confirmedPattern = null;
                    patternDotsSetup = [];
                    updatePatternSetupDisplay();
                    patternSetupInstruction.textContent = 'Draw your pattern (minimum 3 dots)';
                    patternSetupInstruction.classList.remove('text-info', 'text-success');
                    patternSetupInstruction.classList.add('text-muted');
                    if (savePatternBtn) {
                        savePatternBtn.disabled = true;
                        savePatternBtn.style.display = 'none';
                    }
                    setTimeout(function() {
                        patternSetupError.style.display = 'none';
                    }, 3000);
                }
            }
        }

        if (patternDotsSetupElements.length > 0) {
            patternDotsSetupElements.forEach((dot, index) => {
                dot.addEventListener('mousedown', function() {
                    isDrawingSetup = true;
                    patternDotsSetup = [index];
                    updatePatternSetupDisplay();
                });
                
                dot.addEventListener('mouseenter', function() {
                    if (isDrawingSetup && !patternDotsSetup.includes(index)) {
                        patternDotsSetup.push(index);
                        updatePatternSetupDisplay();
                    }
                });
                
                dot.addEventListener('mouseup', function() {
                    if (isDrawingSetup) {
                        isDrawingSetup = false;
                        handlePatternDrawComplete();
                    }
                });
            });

            // Touch events for mobile
            patternDotsSetupElements.forEach((dot, index) => {
                dot.addEventListener('touchstart', function(e) {
                    e.preventDefault();
                    isDrawingSetup = true;
                    patternDotsSetup = [index];
                    updatePatternSetupDisplay();
                });
                
                dot.addEventListener('touchmove', function(e) {
                    e.preventDefault();
                    const touch = e.touches[0];
                    const element = document.elementFromPoint(touch.clientX, touch.clientY);
                    if (element && element.classList.contains('pattern-dot-setup')) {
                        const dotIndex = parseInt(element.getAttribute('data-index'));
                        if (isDrawingSetup && !patternDotsSetup.includes(dotIndex)) {
                            patternDotsSetup.push(dotIndex);
                            updatePatternSetupDisplay();
                        }
                    }
                });
                
                dot.addEventListener('touchend', function(e) {
                    e.preventDefault();
                    if (isDrawingSetup) {
                        isDrawingSetup = false;
                        handlePatternDrawComplete();
                    }
                });
            });
        }

        function updatePatternSetupDisplay() {
            patternDotsSetupElements.forEach((dot, index) => {
                if (patternDotsSetup.includes(index)) {
                    dot.style.background = '#3b82f6';
                    dot.style.borderColor = '#3b82f6';
                    dot.style.transform = 'scale(1.1)';
                } else {
                    dot.style.background = '#fff';
                    dot.style.borderColor = '#dee2e6';
                    dot.style.transform = 'scale(1)';
                }
            });
        }

        // Save Pattern - use form submit (ensures session cookie is sent)
        const savePatternForm = document.getElementById('savePatternForm');
        const savePatternInput = document.getElementById('savePatternInput');
        if (savePatternForm && savePatternBtn) {
            savePatternForm.addEventListener('submit', function(e) {
                const pattern = confirmedPattern || (patternDotsSetup.length >= 3 ? patternDotsSetup.join(',') : null);
                if (!pattern || pattern.split(',').length < 3) {
                    e.preventDefault();
                    patternSetupError.textContent = 'Please draw your pattern twice to confirm, then click Save Pattern.';
                    patternSetupError.style.display = 'block';
                    return false;
                }
                if (savePatternInput) {
                    savePatternInput.value = pattern;
                }
                savePatternBtn.disabled = true;
                const savePatternText = document.getElementById('savePatternText');
                if (savePatternText) {
                    savePatternText.textContent = 'Saving...';
                }
            });
        }

        // Clear Pattern
        if (clearPatternBtn) {
            clearPatternBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to clear your pattern? You will not be able to login using pattern until you set a new one.')) {
                    fetch('{{ route("save.pattern") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ pattern: '' })
                    })
                    .then(response => {
                        // Check if response is OK
                        if (!response.ok) {
                            return response.text().then(text => {
                                try {
                                    const jsonData = JSON.parse(text);
                                    throw new Error(jsonData.message || 'Server returned error: ' + response.status);
                                } catch (e) {
                                    throw new Error('Server error (Status: ' + response.status + '). Please try again.');
                                }
                            });
                        }
                        
                        // Check content type
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            return response.json();
                        } else {
                            return response.text().then(text => {
                                if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')) {
                                    throw new Error('Server returned HTML error page. Please try again.');
                                }
                                try {
                                    return JSON.parse(text);
                                } catch (e) {
                                    throw new Error('Invalid JSON response from server.');
                                }
                            });
                        }
                    })
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message || 'Failed to clear pattern');
                        }
                    })
                    .catch(error => {
                        console.error('Pattern clear error:', error);
                        alert(error.message || 'Failed to clear pattern');
                    });
                }
            });
        }

        // Fingerprint Setup
        const fingerprintSetupBtn = document.getElementById('fingerprintSetupBtn');
        const fingerprintSetupProgress = document.getElementById('fingerprintSetupProgress');
        const fingerprintSetupStatus = document.getElementById('fingerprintSetupStatus');
        const fingerprintSetupSuccess = document.getElementById('fingerprintSetupSuccess');
        const clearFingerprintBtn = document.getElementById('clearFingerprintBtn');
        let scanIntervalSetup = null;
        let scanProgressSetup = 0;

        if (fingerprintSetupBtn) {
            fingerprintSetupBtn.addEventListener('mousedown', startFingerprintSetupScan);
            fingerprintSetupBtn.addEventListener('mouseup', stopFingerprintSetupScan);
            fingerprintSetupBtn.addEventListener('mouseleave', stopFingerprintSetupScan);
            
            fingerprintSetupBtn.addEventListener('touchstart', function(e) {
                e.preventDefault();
                startFingerprintSetupScan();
            });
            
            fingerprintSetupBtn.addEventListener('touchend', function(e) {
                e.preventDefault();
                stopFingerprintSetupScan();
            });
        }

        function startFingerprintSetupScan() {
            scanProgressSetup = 0;
            fingerprintSetupProgress.style.display = 'block';
            fingerprintSetupStatus.textContent = 'Scanning...';
            fingerprintSetupBtn.querySelector('.fingerprint-icon-setup').style.background = '#3b82f6';
            fingerprintSetupBtn.querySelector('.fingerprint-icon-setup i').style.color = '#fff';
            
            scanIntervalSetup = setInterval(function() {
                scanProgressSetup += 5;
                fingerprintSetupProgress.querySelector('.progress-bar').style.width = scanProgressSetup + '%';
                
                if (scanProgressSetup >= 100) {
                    stopFingerprintSetupScan();
                    saveFingerprint();
                }
            }, 50);
        }

        function stopFingerprintSetupScan() {
            if (scanIntervalSetup) {
                clearInterval(scanIntervalSetup);
                scanIntervalSetup = null;
            }
            
            if (scanProgressSetup < 100) {
                fingerprintSetupProgress.style.display = 'none';
                fingerprintSetupStatus.textContent = 'Hold to Scan Finger';
                fingerprintSetupBtn.querySelector('.fingerprint-icon-setup').style.background = '#f8f9fa';
                fingerprintSetupBtn.querySelector('.fingerprint-icon-setup i').style.color = '#3b82f6';
                fingerprintSetupProgress.querySelector('.progress-bar').style.width = '0%';
                scanProgressSetup = 0;
            }
        }

        function saveFingerprint() {
            const fingerprintData = 'fingerprint_' + '{{ auth()->user()->email }}' + '_' + Date.now();
            const form = document.getElementById('saveFingerprintForm');
            const input = document.getElementById('fingerprintDataInput');
            if (form && input) {
                input.value = fingerprintData;
                fingerprintSetupStatus.textContent = 'Saving...';
                form.submit();
                return;
            }
            fetch('{{ route("save.fingerprint") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ fingerprint_data: fingerprintData })
            })
            .then(response => {
                // Check if response is OK
                if (!response.ok) {
                    return response.text().then(text => {
                        // Try to parse as JSON first
                        try {
                            const jsonData = JSON.parse(text);
                            throw new Error(jsonData.message || 'Server returned error: ' + response.status);
                        } catch (e) {
                            // If not JSON, it's probably an HTML error page
                            throw new Error('Server error (Status: ' + response.status + '). Please try again.');
                        }
                    });
                }
                
                // Check content type
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    // If not JSON, get text and try to parse
                    return response.text().then(text => {
                        // Check if it's HTML (error page)
                        if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')) {
                            throw new Error('Server returned HTML error page. Please try again.');
                        }
                        
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            throw new Error('Invalid JSON response from server.');
                        }
                    });
                }
            })
            .then(data => {
                if (data.success) {
                    fingerprintSetupSuccess.textContent = 'Fingerprint saved successfully! You can now use it to login.';
                    fingerprintSetupSuccess.style.display = 'block';
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    fingerprintSetupStatus.textContent = data.message || 'Failed to save fingerprint';
                    fingerprintSetupStatus.style.color = '#dc3545';
                }
            })
            .catch(error => {
                console.error('Fingerprint save error:', error);
                fingerprintSetupStatus.textContent = error.message || 'An error occurred while saving fingerprint';
                fingerprintSetupStatus.style.color = '#dc3545';
            });
        }
    </script>
@endsection
