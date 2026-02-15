@extends('assets.headassets')
@section('title', 'Login')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
@endpush
@push('scripts')
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
@endpush
@section('authentication')
    <div class="main-wrapper">
        <div class="account-content">
            <div class="login-wrapper login-new">
                <div class="row w-100">
                    <div class="col-lg-5 mx-auto">
                        <div class="login-content user-login">
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="card">
                                    <div class="card-body p-5">

                                        <div class="login-logo text-center mb-4">
                                            <img style="width: 60px;" src="{{ setting_value('logo', asset('assets/img/logo.svg')) }}"  alt="Img">
                                        </div>

                                        <!-- Login Method Tabs -->
                                        <ul class="nav nav-pills nav-justified mb-4" id="loginMethodTabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active" id="pin-tab" data-bs-toggle="pill" data-bs-target="#pin-pane" type="button" role="tab" aria-controls="pin-pane" aria-selected="true">
                                                    <i class="ti ti-key me-1"></i> PIN
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="pattern-tab" data-bs-toggle="pill" data-bs-target="#pattern-pane" type="button" role="tab" aria-controls="pattern-pane" aria-selected="false">
                                                    <i class="ti ti-grid-3x3 me-1"></i> Pattern
                                                </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="fingerprint-tab" data-bs-toggle="pill" data-bs-target="#fingerprint-pane" type="button" role="tab" aria-controls="fingerprint-pane" aria-selected="false">
                                                    <i class="ti ti-fingerprint me-1"></i> Bio
                                                </button>
                                            </li>
                                        </ul>

                                        <!-- Tab Content -->
                                        <div class="tab-content" id="loginMethodTabContent">
                                            <!-- PIN Tab -->
                                            <div class="tab-pane fade show active" id="pin-pane" role="tabpanel" aria-labelledby="pin-tab">
                                                <!-- Username (dropdown) -->
                                        <div class="mb-3">
                                            <label class="form-label">Username <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <select name="email" id="emailInput" class="form-control border-end-0 @error('email') is-invalid @enderror" required autofocus>
                                                    <option value="">-- Select User --</option>
                                                    @isset($users)
                                                        @foreach($users as $u)
                                                            <option value="{{ $u->email }}" {{ old('email') == $u->email ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                                                        @endforeach
                                                    @endisset
                                                </select>
                                                <span class="input-group-text border-start-0"><i class="ti ti-user"></i></span>
                                            </div>
                                            <small class="text-muted" id="branchAutoDetectMsg" style="display: none;">
                                                <i class="ti ti-loader spinner-border spinner-border-sm"></i> Detecting your branch...
                                            </small>
                                            @error('email')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Branch Selection (Hidden by default, shown based on user role) -->
                                        @if(isset($branches) && $branches->count() > 0)
                                        <div class="mb-3" id="branchSelectionDiv" style="display: none;">
                                            <label class="form-label">Branch 
                                                <span class="text-danger" id="branchRequiredStar" style="display: none;">*</span>
                                                <small class="text-muted" id="branchOptionalText">(Auto-detected from your account)</small>
                                            </label>
                                            <div class="input-group">
                                                <select name="branch_id" class="form-control border-end-0 @error('branch_id') is-invalid @enderror" id="branchSelect">
                                                    <option value="">-- Select Branch --</option>
                                                    @foreach($branches as $branch)
                                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                                            {{ $branch->branch_name }}
                                                            @if($branch->branch_code)
                                                                ({{ $branch->branch_code }})
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="input-group-text border-start-0"><i class="ti ti-building"></i></span>
                                            </div>
                                            <!-- Hidden input to ensure branch_id is submitted even when select is disabled -->
                                            <input type="hidden" name="branch_id_hidden" id="branchIdHidden" value="">
                                            <small class="text-muted" id="branchInfoMsg">
                                                Select your username above to auto-detect your branch
                                            </small>
                                            @error('branch_id')
                                                <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        @endif

                                        <!-- Password -->
                                        <div class="mb-3">
                                            <label class="form-label">Password <span class="text-danger">*</span></label>
                                            <div class="pass-group">
                                                <input type="password" name="password" class="pass-input form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                                                <span class="ti toggle-password ti-eye-off text-gray-9"></span>
                                            </div>
                                            @error('password')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>

                                                <!-- Submit -->
                                                <div class="form-login mb-3">
                                                    <button type="submit" class="btn btn-login w-100">Sign In</button>
                                                </div>
                                            </div>

                                            <!-- Pattern Tab -->
                                            <div class="tab-pane fade" id="pattern-pane" role="tabpanel" aria-labelledby="pattern-tab">
                                                <div class="text-center mb-4">
                                                    <p class="text-muted small mb-3">Draw Pattern (Top Row: 0, 1, 2)</p>
                                                    <div class="d-flex justify-content-center">
                                                        <div class="pattern-lock-container" style="background: #f8f9fa; padding: 2rem; border-radius: 1rem; display: inline-block;">
                                                            <div class="pattern-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                                                                @for($i = 0; $i < 9; $i++)
                                                                    <button type="button" class="pattern-dot" data-index="{{ $i }}" style="width: 50px; height: 50px; border-radius: 50%; border: 2px solid #dee2e6; background: #fff; cursor: pointer; transition: all 0.2s;">
                                                                    </button>
                                                                @endfor
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <p class="text-danger small mt-3" id="patternError" style="display: none;"></p>
                                                    @error('pattern')
                                                        <p class="text-danger small mt-2">{{ $message }}</p>
                                                    @enderror
                                                    <input type="hidden" name="pattern" id="patternInput" value="">
                                                </div>
                                            </div>

                                            <!-- Fingerprint Tab -->
                                            <div class="tab-pane fade" id="fingerprint-pane" role="tabpanel" aria-labelledby="fingerprint-tab">
                                                <div class="text-center mb-4">
                                                    <div class="fingerprint-container mb-3">
                                                        <button type="button" id="fingerprintBtn" class="btn btn-link p-0" style="border: none; background: none;">
                                                            <div class="fingerprint-icon" style="width: 120px; height: 120px; margin: 0 auto; border-radius: 50%; background: #f8f9fa; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                                                                <i class="ti ti-fingerprint" style="font-size: 64px; color: #3b82f6;"></i>
                                                            </div>
                                                        </button>
                                                        <div class="progress mt-3" id="fingerprintProgress" style="display: none; max-width: 200px; margin: 0 auto;">
                                                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                                        </div>
                                                    </div>
                                                    <p class="text-muted small mb-2" id="fingerprintStatus">Hold to Scan Finger</p>
                                                    <p class="text-muted" style="font-size: 0.75rem;">Biometric Identity Verification</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Forgot Password -->


                                        <!-- Register Link - Hidden for Employee Login -->
                                        {{-- <div class="signinform mt-3 text-center">
                                            <h4>Don't have an account? <a href="{{ route('register') }}" class="hover-a">Sign Up</a></h4>
                                        </div> --}}

                                        <!-- OR -->
                                        <div class="form-setlogin or-text d-none">
                                            <h4>OR</h4>
                                        </div>
                                        <!-- Social Logins -->
                                        <div class="d-none align-items-center justify-content-center mt-2 flex-wrap">
                                            <div class="text-center me-2 flex-fill">
                                                <a href="javascript:void(0);" class="br-10 p-2 btn btn-info d-flex align-items-center justify-content-center">
                                                    <img class="img-fluid m-1" src="{{ asset('assets/img/icons/facebook-logo.svg') }}" alt="Facebook">
                                                </a>
                                            </div>
                                            <div class="text-center me-2 flex-fill">
                                                <a href="javascript:void(0);" class="btn btn-white br-10 p-2 border d-flex align-items-center justify-content-center">
                                                    <img class="img-fluid m-1" src="{{ asset('assets/img/icons/google-logo.svg') }}" alt="Google">
                                                </a>
                                            </div>
                                            {{-- <div class="text-center flex-fill">
                                                <a href="javascript:void(0);" class="bg-dark br-10 p-2 btn btn-dark d-flex align-items-center justify-content-center">
                                                    <img class="img-fluid m-1" src="{{ asset('assets/img/icons/apple-logo.svg') }}" alt="Apple">
                                                </a>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- JavaScript for Login Methods -->
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                // If pattern error on redirect back, switch to pattern tab
                                @if($errors->has('pattern'))
                                    var patternTab = document.getElementById('pattern-tab');
                                    if (patternTab) {
                                        patternTab.click();
                                    }
                                @endif
                                // Pattern Lock Logic
                                let patternDots = [];
                                let isDrawing = false;
                                let userPattern = null; // Will be loaded from database
                                
                                const patternDotsElements = document.querySelectorAll('.pattern-dot');
                                const patternInput = document.getElementById('patternInput');
                                const patternError = document.getElementById('patternError');
                                const patternPane = document.getElementById('pattern-pane');
                                
                                if (patternDotsElements.length > 0) {
                                    patternDotsElements.forEach((dot, index) => {
                                        dot.addEventListener('mousedown', function() {
                                            isDrawing = true;
                                            patternDots = [index];
                                            updatePatternDisplay();
                                        });
                                        
                                        dot.addEventListener('mouseenter', function() {
                                            if (isDrawing && !patternDots.includes(index)) {
                                                patternDots.push(index);
                                                updatePatternDisplay();
                                            }
                                        });
                                        
                                        dot.addEventListener('mouseup', function() {
                                            if (isDrawing) {
                                                isDrawing = false;
                                                checkPattern();
                                            }
                                        });
                                    });
                                    
                                    // Touch events for mobile
                                    patternDotsElements.forEach((dot, index) => {
                                        dot.addEventListener('touchstart', function(e) {
                                            e.preventDefault();
                                            isDrawing = true;
                                            patternDots = [index];
                                            updatePatternDisplay();
                                        });
                                        
                                        dot.addEventListener('touchmove', function(e) {
                                            e.preventDefault();
                                            const touch = e.touches[0];
                                            const element = document.elementFromPoint(touch.clientX, touch.clientY);
                                            if (element && element.classList.contains('pattern-dot')) {
                                                const dotIndex = parseInt(element.getAttribute('data-index'));
                                                if (isDrawing && !patternDots.includes(dotIndex)) {
                                                    patternDots.push(dotIndex);
                                                    updatePatternDisplay();
                                                }
                                            }
                                        });
                                        
                                        dot.addEventListener('touchend', function(e) {
                                            e.preventDefault();
                                            if (isDrawing) {
                                                isDrawing = false;
                                                checkPattern();
                                            }
                                        });
                                    });
                                }
                                
                                function updatePatternDisplay() {
                                    patternDotsElements.forEach((dot, index) => {
                                        if (patternDots.includes(index)) {
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
                                
                                function checkPattern() {
                                    if (!userPattern) {
                                        patternError.textContent = 'Pattern not set. Please set your pattern first.';
                                        patternError.style.display = 'block';
                                        setTimeout(function() {
                                            patternDots = [];
                                            updatePatternDisplay();
                                            patternError.style.display = 'none';
                                        }, 2000);
                                        return;
                                    }
                                    // Submit to server - verification happens securely on backend (encrypted storage)
                                    patternInput.value = patternDots.join(',');
                                    submitPatternLogin();
                                }

                                function submitPatternLogin() {
                                    const email = document.getElementById('emailInput').value;
                                    const pattern = patternDots.join(',');
                                    const branchId = document.getElementById('branchSelect') ? document.getElementById('branchSelect').value : '';
                                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                                    // Create form and submit
                                    const form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action = '{{ route("login.pattern") }}';
                                    
                                    const csrfInput = document.createElement('input');
                                    csrfInput.type = 'hidden';
                                    csrfInput.name = '_token';
                                    csrfInput.value = csrfToken;
                                    form.appendChild(csrfInput);

                                    const emailInput = document.createElement('input');
                                    emailInput.type = 'hidden';
                                    emailInput.name = 'email';
                                    emailInput.value = email;
                                    form.appendChild(emailInput);

                                    const patternInput = document.createElement('input');
                                    patternInput.type = 'hidden';
                                    patternInput.name = 'pattern';
                                    patternInput.value = pattern;
                                    form.appendChild(patternInput);

                                    if (branchId) {
                                        const branchInput = document.createElement('input');
                                        branchInput.type = 'hidden';
                                        branchInput.name = 'branch_id';
                                        branchInput.value = branchId;
                                        form.appendChild(branchInput);
                                    }

                                    document.body.appendChild(form);
                                    form.submit();
                                }
                                
                                // Fingerprint Logic
                                const fingerprintBtn = document.getElementById('fingerprintBtn');
                                const fingerprintProgress = document.getElementById('fingerprintProgress');
                                const fingerprintStatus = document.getElementById('fingerprintStatus');
                                let scanInterval = null;
                                let scanProgress = 0;
                                
                                if (fingerprintBtn) {
                                    fingerprintBtn.addEventListener('mousedown', startFingerprintScan);
                                    fingerprintBtn.addEventListener('mouseup', stopFingerprintScan);
                                    fingerprintBtn.addEventListener('mouseleave', stopFingerprintScan);
                                    
                                    // Touch events
                                    fingerprintBtn.addEventListener('touchstart', function(e) {
                                        e.preventDefault();
                                        startFingerprintScan();
                                    });
                                    
                                    fingerprintBtn.addEventListener('touchend', function(e) {
                                        e.preventDefault();
                                        stopFingerprintScan();
                                    });
                                }
                                
                                let userHasFingerprint = false;

                                function startFingerprintScan() {
                                    if (!userHasFingerprint) {
                                        fingerprintStatus.textContent = 'Fingerprint not set. Please set your fingerprint first.';
                                        fingerprintStatus.style.color = '#dc3545';
                                        setTimeout(function() {
                                            fingerprintStatus.textContent = 'Hold to Scan Finger';
                                            fingerprintStatus.style.color = '';
                                        }, 2000);
                                        return;
                                    }

                                    scanProgress = 0;
                                    fingerprintProgress.style.display = 'block';
                                    fingerprintStatus.textContent = 'Scanning...';
                                    fingerprintBtn.querySelector('.fingerprint-icon').style.background = '#3b82f6';
                                    fingerprintBtn.querySelector('.fingerprint-icon i').style.color = '#fff';
                                    
                                    scanInterval = setInterval(function() {
                                        scanProgress += 5;
                                        fingerprintProgress.querySelector('.progress-bar').style.width = scanProgress + '%';
                                        
                                        if (scanProgress >= 100) {
                                            stopFingerprintScan();
                                            // Submit fingerprint login form
                                            submitFingerprintLogin();
                                        }
                                    }, 50);
                                }

                                function submitFingerprintLogin() {
                                    const email = document.getElementById('emailInput').value;
                                    const fingerprintData = 'fingerprint_' + email + '_' + Date.now(); // Simulated fingerprint data
                                    const branchId = document.getElementById('branchSelect') ? document.getElementById('branchSelect').value : '';
                                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                                    // Create form and submit
                                    const form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action = '{{ route("login.fingerprint") }}';
                                    
                                    const csrfInput = document.createElement('input');
                                    csrfInput.type = 'hidden';
                                    csrfInput.name = '_token';
                                    csrfInput.value = csrfToken;
                                    form.appendChild(csrfInput);

                                    const emailInput = document.createElement('input');
                                    emailInput.type = 'hidden';
                                    emailInput.name = 'email';
                                    emailInput.value = email;
                                    form.appendChild(emailInput);

                                    const fingerprintInput = document.createElement('input');
                                    fingerprintInput.type = 'hidden';
                                    fingerprintInput.name = 'fingerprint_data';
                                    fingerprintInput.value = fingerprintData;
                                    form.appendChild(fingerprintInput);

                                    if (branchId) {
                                        const branchInput = document.createElement('input');
                                        branchInput.type = 'hidden';
                                        branchInput.name = 'branch_id';
                                        branchInput.value = branchId;
                                        form.appendChild(branchInput);
                                    }

                                    document.body.appendChild(form);
                                    form.submit();
                                }
                                
                                function stopFingerprintScan() {
                                    if (scanInterval) {
                                        clearInterval(scanInterval);
                                        scanInterval = null;
                                    }
                                    
                                    if (scanProgress < 100) {
                                        fingerprintProgress.style.display = 'none';
                                        fingerprintStatus.textContent = 'Hold to Scan Finger';
                                        fingerprintBtn.querySelector('.fingerprint-icon').style.background = '#f8f9fa';
                                        fingerprintBtn.querySelector('.fingerprint-icon i').style.color = '#3b82f6';
                                        fingerprintProgress.querySelector('.progress-bar').style.width = '0%';
                                        scanProgress = 0;
                                    }
                                }
                                
                                // Auto Branch Detection
                                const emailInput = document.getElementById('emailInput');
                                const branchSelect = document.getElementById('branchSelect');
                                const branchSelectionDiv = document.getElementById('branchSelectionDiv');
                                const branchInfoMsg = document.getElementById('branchInfoMsg');
                                const branchAutoDetectMsg = document.getElementById('branchAutoDetectMsg');
                                const branchRequiredStar = document.getElementById('branchRequiredStar');
                                const branchOptionalText = document.getElementById('branchOptionalText');
                                const loginForm = document.querySelector('form[method="POST"]');
                                
                                let debounceTimer;
                                let isUserRole = false;
                                
                                // Check pattern and fingerprint status when email is entered
                                function checkPatternFingerprintStatus(email) {
                                    if (!email || !email.includes('@')) {
                                        userPattern = null;
                                        userHasFingerprint = false;
                                        if (patternPane) {
                                            const patternMsg = patternPane.querySelector('.pattern-status-msg');
                                            if (patternMsg) patternMsg.remove();
                                        }
                                        return;
                                    }

                                    // Check pattern status
                                    fetch('{{ route("get.user.pattern.status") }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify({ email: email })
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.has_pattern) {
                                            userPattern = true; // User has pattern set (never send actual pattern to client)
                                            if (patternPane) {
                                                let patternMsg = patternPane.querySelector('.pattern-status-msg');
                                                if (!patternMsg) {
                                                    patternMsg = document.createElement('p');
                                                    patternMsg.className = 'text-success small mt-2 pattern-status-msg';
                                                    patternPane.querySelector('.text-center').appendChild(patternMsg);
                                                }
                                                patternMsg.textContent = 'Pattern is set. Draw your pattern to login.';
                                            }
                                        } else {
                                            userPattern = null;
                                            if (patternPane) {
                                                let patternMsg = patternPane.querySelector('.pattern-status-msg');
                                                if (!patternMsg) {
                                                    patternMsg = document.createElement('p');
                                                    patternMsg.className = 'text-warning small mt-2 pattern-status-msg';
                                                    patternPane.querySelector('.text-center').appendChild(patternMsg);
                                                }
                                                patternMsg.textContent = 'Pattern not set. Please set your pattern first in settings.';
                                            }
                                        }
                                    });

                                    // Check fingerprint status
                                    fetch('{{ route("get.user.fingerprint.status") }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify({ email: email })
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        userHasFingerprint = data.has_fingerprint;
                                        if (!userHasFingerprint) {
                                            const fingerprintStatusEl = document.getElementById('fingerprintStatus');
                                            if (fingerprintStatusEl) {
                                                fingerprintStatusEl.textContent = 'Fingerprint not set. Please set your fingerprint first.';
                                                fingerprintStatusEl.style.color = '#dc3545';
                                            }
                                        } else {
                                            const fingerprintStatusEl = document.getElementById('fingerprintStatus');
                                            if (fingerprintStatusEl) {
                                                fingerprintStatusEl.textContent = 'Hold to Scan Finger';
                                                fingerprintStatusEl.style.color = '';
                                            }
                                        }
                                    });
                                }

                                if (emailInput && branchSelect && branchSelectionDiv) {
                                    function focusAndOpenBranchDropdown() {
                                        setTimeout(function() {
                                            if (branchSelect && !branchSelect.disabled && !branchSelect.hasAttribute('readonly')) {
                                                branchSelect.focus();
                                                if (typeof branchSelect.showPicker === 'function') {
                                                    branchSelect.showPicker();
                                                } else {
                                                    branchSelect.click();
                                                }
                                            }
                                        }, 150);
                                    }
                                    function handleEmailOrUserChange() {
                                        const email = (emailInput.tagName === 'SELECT' ? emailInput.value : emailInput.value.trim());
                                        
                                        // Check pattern and fingerprint status
                                        checkPatternFingerprintStatus(email);
                                        
                                        if (!email || !email.includes('@')) {
                                            branchSelectionDiv.style.display = 'none';
                                            branchSelect.disabled = false;
                                            branchSelect.value = '';
                                            return;
                                        }
                                        
                                        // Show branch section immediately below username
                                        branchAutoDetectMsg.style.display = 'block';
                                        branchInfoMsg.style.display = 'none';
                                        branchSelectionDiv.style.display = 'block';
                                        branchSelect.disabled = true; // Disable while loading
                                        
                                        function doBranchFetch() {
                                            // Make AJAX request to get user's branch
                                            fetch('{{ route("get.user.branch") }}', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                },
                                                body: JSON.stringify({
                                                    email: email
                                                })
                                            })
                                            .then(response => response.json())
                                            .then(data => {
                                                branchAutoDetectMsg.style.display = 'none';
                                                branchSelectionDiv.style.display = 'block';
                                                
                                                if (data.success) {
                                                    if (data.is_admin) {
                                                        // Admin user - branch is OPTIONAL and ENABLED
                                                        isUserRole = false;
                                                        branchRequiredStar.style.display = 'none';
                                                        branchSelect.removeAttribute('required');
                                                        branchSelect.disabled = false; // Enable for admin
                                                        branchOptionalText.textContent = '(Optional for Admin)';
                                                        branchInfoMsg.innerHTML = '<i class="ti ti-info-circle text-info"></i> Admin user - Branch selection is optional';
                                                        branchInfoMsg.style.display = 'block';
                                                        branchInfoMsg.classList.remove('text-danger', 'text-success');
                                                        branchInfoMsg.classList.add('text-info');
                                                        
                                                        // Auto-select if branch_id provided; open dropdown only if user ne abhi select nahi kiya
                                                        if (data.branch_id) {
                                                            branchSelect.value = data.branch_id;
                                                        } else if (!branchSelect.value) {
                                                            focusAndOpenBranchDropdown();
                                                        }
                                                    } else if (data.branch_id && data.branch_required) {
                                                        // Normal user - branch is REQUIRED and AUTO-SELECTED
                                                        isUserRole = true;
                                                        branchRequiredStar.style.display = 'inline';
                                                        branchSelect.value = data.branch_id;
                                                        
                                                        // Keep select enabled but prevent changes (so value submits)
                                                        branchSelect.disabled = false; // Keep enabled so value submits
                                                        branchSelect.style.backgroundColor = '#e9ecef';
                                                        branchSelect.style.cursor = 'not-allowed';
                                                        branchSelect.setAttribute('readonly', 'readonly'); // Visual indicator
                                                        
                                                        // Prevent user from changing the value
                                                        branchSelect.addEventListener('mousedown', function(e) {
                                                            e.preventDefault();
                                                            return false;
                                                        });
                                                        branchSelect.addEventListener('keydown', function(e) {
                                                            if (e.key !== 'Tab' && e.key !== 'Enter') {
                                                                e.preventDefault();
                                                                return false;
                                                            }
                                                        });
                                                        
                                                        // Set hidden input value as backup
                                                        const branchIdHidden = document.getElementById('branchIdHidden');
                                                        if (branchIdHidden) {
                                                            branchIdHidden.value = data.branch_id;
                                                        }
                                                        
                                                        branchOptionalText.textContent = '(Auto-selected - Required)';
                                                        branchInfoMsg.innerHTML = '<i class="ti ti-check text-success"></i> Branch auto-selected: <strong>' + data.branch_name + '</strong> - Ready to login';
                                                        branchInfoMsg.style.display = 'block';
                                                        branchInfoMsg.classList.remove('text-danger', 'text-info');
                                                        branchInfoMsg.classList.add('text-success');
                                                        
                                                        // Remove any error messages
                                                        branchSelect.classList.remove('is-invalid');
                                                        
                                                        // Focus on password field
                                                        setTimeout(function() {
                                                            const passwordInput = document.querySelector('input[name="password"]');
                                                            if (passwordInput) {
                                                                passwordInput.focus();
                                                            }
                                                        }, 100);
                                                    } else {
                                                        // User has no branch
                                                        isUserRole = true;
                                                        branchRequiredStar.style.display = 'inline';
                                                        branchSelect.setAttribute('required', 'required');
                                                        branchSelect.disabled = false; // Enable to allow manual selection if needed
                                                        branchSelect.style.backgroundColor = '';
                                                        branchSelect.style.cursor = '';
                                                        branchSelect.value = '';
                                                        branchOptionalText.textContent = '(Required)';
                                                        branchInfoMsg.innerHTML = '<i class="ti ti-alert-circle text-danger"></i> ' + (data.message || 'No active branch found. Please contact administrator.');
                                                        branchInfoMsg.style.display = 'block';
                                                        branchInfoMsg.classList.remove('text-success', 'text-info');
                                                        branchInfoMsg.classList.add('text-danger');
                                                        focusAndOpenBranchDropdown();
                                                    }
                                                } else {
                                                    // Error or user not found
                                                    branchSelectionDiv.style.display = 'none';
                                                    branchSelect.disabled = false;
                                                    branchSelect.value = '';
                                                    branchInfoMsg.innerHTML = '<i class="ti ti-info-circle"></i> ' + (data.message || 'Could not detect branch');
                                                    branchInfoMsg.style.display = 'block';
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error:', error);
                                                branchAutoDetectMsg.style.display = 'none';
                                                branchSelect.disabled = false;
                                                branchInfoMsg.innerHTML = '<i class="ti ti-alert-circle text-danger"></i> Error detecting branch';
                                                branchInfoMsg.style.display = 'block';
                                                branchInfoMsg.classList.add('text-danger');
                                            });
                                        }
                                        
                                        if (emailInput.tagName === 'SELECT') {
                                            doBranchFetch();
                                        } else {
                                            clearTimeout(debounceTimer);
                                            debounceTimer = setTimeout(doBranchFetch, 800);
                                        }
                                    }
                                    // Auto-detect branch when email/user is selected (on change for select, input for text)
                                    if (emailInput.tagName === 'SELECT') {
                                        emailInput.addEventListener('change', handleEmailOrUserChange);
                                    } else {
                                        emailInput.addEventListener('input', function() {
                                            clearTimeout(debounceTimer);
                                            debounceTimer = setTimeout(handleEmailOrUserChange, 800);
                                        });
                                    }
                                    // Also trigger on blur (when user leaves email field) - for text input only
                                    if (emailInput.tagName !== 'SELECT') {
                                        emailInput.addEventListener('blur', function() {
                                            if (this.value.trim() && this.value.includes('@')) {
                                                this.dispatchEvent(new Event('input'));
                                            }
                                        });
                                    }
                                    
                                    // When branch is selected, auto-focus password field
                                    branchSelect.addEventListener('change', function() {
                                        if (branchSelect.value) {
                                            setTimeout(function() {
                                                const passwordInput = document.querySelector('input[name="password"]');
                                                if (passwordInput) {
                                                    passwordInput.focus();
                                                }
                                            }, 100);
                                        }
                                    });

                                    // Form validation - prevent login if user role and no branch selected
                                    if (loginForm) {
                                        loginForm.addEventListener('submit', function(e) {
                                            // Get branch value from select (it's enabled so value will be there)
                                            const branchValue = branchSelect.value;
                                            
                                            if (isUserRole && !branchValue) {
                                                e.preventDefault();
                                                branchSelect.classList.add('is-invalid');
                                                branchInfoMsg.innerHTML = '<i class="ti ti-alert-circle text-danger"></i> Branch selection is required for user login!';
                                                branchInfoMsg.style.display = 'block';
                                                branchInfoMsg.classList.remove('text-success', 'text-info');
                                                branchInfoMsg.classList.add('text-danger');
                                                branchSelect.style.backgroundColor = '';
                                                branchSelect.style.cursor = '';
                                                branchSelect.removeAttribute('readonly');
                                                branchSelect.focus();
                                                return false;
                                            }
                                            
                                            // Ensure branch value is set (should already be set)
                                            if (isUserRole && branchValue) {
                                                // Value is already in select field, it will submit automatically
                                                // Remove readonly attribute if present (doesn't affect submission)
                                                branchSelect.removeAttribute('readonly');
                                            }
                                        });
                                    }
                                    
                                    // If email is pre-filled (from old() helper), auto-detect on page load
                                    @if(old('email'))
                                        setTimeout(function() {
                                            emailInput.dispatchEvent(new Event(emailInput.tagName === 'SELECT' ? 'change' : 'input'));
                                        }, 500);
                                    @endif
                                }
                                
                                // Searchable username dropdown (Select2)
                                if (typeof $ !== 'undefined' && $.fn.select2 && document.getElementById('emailInput')) {
                                    $('#emailInput').select2({
                                        placeholder: '-- Select User --',
                                        allowClear: false,
                                        width: '100%'
                                    });
                                    // Ensure branch section shows when user selects from dropdown (Select2 uses select2:select)
                                    $('#emailInput').on('select2:select', function() {
                                        var sel = document.getElementById('emailInput');
                                        if (sel) sel.dispatchEvent(new Event('change'));
                                    });
                                }

                            });
                        </script>

                        {{-- <div class="my-4 d-flex justify-content-center align-items-center copyright-text">
                            <p>Copyright &copy; 2025 DreamsPOS</p>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
