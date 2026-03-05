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
                                            <div id="selectedUserBranchDisplay" class="mt-2 py-2 px-3 rounded small d-flex align-items-center" style="display: none; background: #e8f4fd; border: 1px solid #0d6efd;">
                                                <i class="ti ti-building me-2 text-primary"></i>
                                                <span class="text-muted me-1">Branch:</span>
                                                <strong id="selectedUserBranchName" class="text-primary"></strong>
                                            </div>
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
                                                    <div class="fingerprint-container mb-3" id="fingerprintContainer" role="button" tabindex="0" style="cursor: pointer;" title="فنگر پرنٹ اسکین کریں">
                                                        <button type="button" id="fingerprintBtn" class="btn btn-link p-0" style="border: none; background: none;">
                                                            <div class="fingerprint-icon" style="width: 120px; height: 120px; margin: 0 auto; border-radius: 50%; background: #f8f9fa; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">
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
                                
                                // Fingerprint / WebAuthn: real biometric login (conditional UI)
                                const fingerprintBtn = document.getElementById('fingerprintBtn');
                                const fingerprintStatus = document.getElementById('fingerprintStatus');
                                const fingerprintError = document.getElementById('fingerprintError');
                                let userHasFingerprint = false;

                                function bufferToBase64url(buffer) {
                                    const bytes = new Uint8Array(buffer);
                                    let binary = '';
                                    for (let i = 0; i < bytes.length; i++) binary += String.fromCharCode(bytes[i]);
                                    return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
                                }
                                function base64urlToBuffer(str) {
                                    var s = str.replace(/-/g,'+').replace(/_/g,'/');
                                    var pad = s.length % 4;
                                    if (pad) s += '='.repeat(4 - pad);
                                    var binary = atob(s);
                                    var bytes = new Uint8Array(binary.length);
                                    for (var i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
                                    return bytes.buffer;
                                }

                                function setFingerprintUI(state) {
                                    var icon = document.querySelector('.fingerprint-icon');
                                    var iconI = document.querySelector('.fingerprint-icon i');
                                    if (icon) icon.style.background = (state === 'loading' || state === 'active') ? '#3b82f6' : '#f8f9fa';
                                    if (iconI) iconI.style.color = (state === 'loading' || state === 'active') ? '#fff' : '#3b82f6';
                                    if (fingerprintStatus) fingerprintStatus.textContent = state === 'loading' ? 'لوڈ ہو رہا ہے...' : (state === 'active' ? 'فنگر پرنٹ اسکین کریں...' : 'Bio ٹیب پر آتے ہی یا نیچے آئیکن پر کلک کر کے فنگر پرنٹ اسکین کریں');
                                }
                                function loginWithFingerprint() {
                                    if (!window.PublicKeyCredential || !navigator.credentials || !navigator.credentials.get) {
                                        if (fingerprintError) {
                                            fingerprintError.textContent = 'اس براؤزر میں فنگر پرنٹ لاگ ان سپورٹ نہیں۔ Chrome یا Edge (HTTPS یا localhost) استعمال کریں۔';
                                            fingerprintError.style.display = 'block';
                                        }
                                        return;
                                    }
                                    if (fingerprintError) { fingerprintError.style.display = 'none'; fingerprintError.textContent = ''; }
                                    setFingerprintUI('loading');

                                    fetch('{{ route("webauthn.login.conditional.options") }}', {
                                        method: 'POST',
                                        credentials: 'same-origin',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'X-Requested-With': 'XMLHttpRequest'
                                        },
                                        body: JSON.stringify({})
                                    })
                                    .then(function(r) {
                                        if (!r.ok) return r.text().then(function(t) { throw new Error(t || 'سرور نے غلطی واپس کی'); });
                                        return r.json();
                                    })
                                    .then(function(data) {
                                        if (!data.success || !data.options) throw new Error(data.message || 'Options نہیں ملے');
                                        setFingerprintUI('active');
                                        const opt = data.options;
                                        const publicKey = {
                                            challenge: base64urlToBuffer(opt.challenge),
                                            timeout: opt.timeout || 60000,
                                            rpId: opt.rpId || window.location.hostname.replace(/:\d+$/, ''),
                                            allowCredentials: (opt.allowCredentials || []).map(function(c) {
                                                return { id: base64urlToBuffer(c.id), type: c.type || 'public-key' };
                                            }),
                                            userVerification: opt.userVerification || 'preferred'
                                        };
                                        return navigator.credentials.get({ publicKey: publicKey, mediation: 'optional' });
                                    })
                                    .then(credential => {
                                        if (!credential) throw new Error('تصدیق منسوخ');
                                        const id = credential.id;
                                        const authenticatorData = bufferToBase64url(credential.response.authenticatorData);
                                        const clientDataJSON = bufferToBase64url(credential.response.clientDataJSON);
                                        const signature = bufferToBase64url(credential.response.signature);
                                        const userHandle = credential.response.userHandle ? bufferToBase64url(credential.response.userHandle) : null;
                                        return fetch('{{ route("webauthn.login.verify") }}', {
                                            method: 'POST',
                                            credentials: 'same-origin',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'X-Requested-With': 'XMLHttpRequest'
                                            },
                                            body: JSON.stringify({
                                                credential: {
                                                    id: id,
                                                    response: {
                                                        authenticatorData: authenticatorData,
                                                        clientDataJSON: clientDataJSON,
                                                        signature: signature,
                                                        userHandle: userHandle
                                                    }
                                                }
                                            })
                                        });
                                    })
                                    .then(function(r) {
                                        if (!r.ok) return r.json().then(function(d) { return { success: false, message: d.message || 'لاگ ان ناکام' }; }).catch(function() { return { success: false, message: 'سرور غلطی' }; });
                                        return r.json();
                                    })
                                    .then(function(data) {
                                        setFingerprintUI('idle');
                                        if (data.success && data.redirect) {
                                            window.location.href = data.redirect;
                                        } else {
                                            if (fingerprintError) {
                                                fingerprintError.textContent = data.message || 'لاگ ان ناکام';
                                                fingerprintError.style.display = 'block';
                                            }
                                        }
                                    })
                                    .catch(function(err) {
                                        setFingerprintUI('idle');
                                        if (fingerprintError) {
                                            fingerprintError.textContent = err.message || 'پہلے پروفائل میں فنگر پرنٹ رجسٹر کریں یا تصدیق دوبارہ کریں۔';
                                            fingerprintError.style.display = 'block';
                                        }
                                    });
                                }

                                var fingerprintContainer = document.getElementById('fingerprintContainer');
                                if (fingerprintContainer) {
                                    fingerprintContainer.addEventListener('click', function(e) { e.preventDefault(); loginWithFingerprint(); });
                                    fingerprintContainer.addEventListener('keydown', function(e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); loginWithFingerprint(); } });
                                }
                                if (fingerprintBtn) {
                                    fingerprintBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); loginWithFingerprint(); });
                                }
                                var fingerprintTab = document.getElementById('fingerprint-tab');
                                if (fingerprintTab) {
                                    fingerprintTab.addEventListener('shown.bs.tab', function() {
                                        loginWithFingerprint();
                                    });
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

                                function updateBranchDisplay(data) {
                                    var branchDisplay = document.getElementById('selectedUserBranchDisplay');
                                    var branchNameEl = document.getElementById('selectedUserBranchName');
                                    if (!branchDisplay || !branchNameEl) return;
                                    if (data.success && data.branch_name) {
                                        branchNameEl.textContent = data.branch_name + (data.branch_code ? ' (' + data.branch_code + ')' : '');
                                        branchDisplay.style.display = 'block';
                                    } else if (data.success && data.is_admin) {
                                        branchNameEl.textContent = 'Select branch below';
                                        branchDisplay.style.display = 'block';
                                    } else if (data.success && data.branch_required && !data.branch_id) {
                                        branchNameEl.textContent = '—';
                                        branchDisplay.style.display = 'block';
                                    } else if (!data.success || !data.branch_name) {
                                        branchDisplay.style.display = 'none';
                                    }
                                }
                                if (emailInput) {
                                    function focusAndOpenBranchDropdown() {
                                        if (!branchSelect || !branchSelectionDiv) return;
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
                                        
                                        checkPatternFingerprintStatus(email);
                                        
                                        var branchDisplay = document.getElementById('selectedUserBranchDisplay');
                                        if (!email || !email.includes('@')) {
                                            if (branchDisplay) branchDisplay.style.display = 'none';
                                            if (branchSelectionDiv) branchSelectionDiv.style.display = 'none';
                                            if (branchSelect) { branchSelect.disabled = false; branchSelect.value = ''; }
                                            return;
                                        }
                                        
                                        if (branchAutoDetectMsg) branchAutoDetectMsg.style.display = 'block';
                                        if (branchInfoMsg) branchInfoMsg.style.display = 'none';
                                        if (branchSelectionDiv) branchSelectionDiv.style.display = 'block';
                                        if (branchSelect) branchSelect.disabled = true;
                                        
                                        function doBranchFetch() {
                                            fetch('{{ route("get.user.branch") }}', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                },
                                                body: JSON.stringify({ email: email })
                                            })
                                            .then(response => response.json())
                                            .then(data => {
                                                if (branchAutoDetectMsg) branchAutoDetectMsg.style.display = 'none';
                                                updateBranchDisplay(data);
                                                if (branchSelectionDiv) branchSelectionDiv.style.display = 'block';
                                                
                                                if (!branchSelect || !branchSelectionDiv) return;
                                                
                                                if (data.success) {
                                                    if (data.is_admin) {
                                                        isUserRole = false;
                                                        if (branchRequiredStar) branchRequiredStar.style.display = 'none';
                                                        if (branchSelect) { branchSelect.removeAttribute('required'); branchSelect.disabled = false; }
                                                        if (branchOptionalText) branchOptionalText.textContent = '(Optional for Admin)';
                                                        if (branchInfoMsg) {
                                                            branchInfoMsg.innerHTML = '<i class="ti ti-info-circle text-info"></i> Admin user - Branch selection is optional';
                                                            branchInfoMsg.style.display = 'block';
                                                            branchInfoMsg.classList.remove('text-danger', 'text-success');
                                                            branchInfoMsg.classList.add('text-info');
                                                        }
                                                        if (data.branch_id && branchSelect) branchSelect.value = data.branch_id;
                                                        else if (branchSelect && !branchSelect.value) focusAndOpenBranchDropdown();
                                                    } else if (data.branch_id && data.branch_required) {
                                                        isUserRole = true;
                                                        if (branchRequiredStar) branchRequiredStar.style.display = 'inline';
                                                        if (branchSelect) {
                                                            branchSelect.value = data.branch_id;
                                                            branchSelect.disabled = false;
                                                            branchSelect.style.backgroundColor = '#e9ecef';
                                                            branchSelect.style.cursor = 'not-allowed';
                                                            branchSelect.setAttribute('readonly', 'readonly');
                                                            branchSelect.addEventListener('mousedown', function(e) { e.preventDefault(); return false; });
                                                            branchSelect.addEventListener('keydown', function(e) {
                                                                if (e.key !== 'Tab' && e.key !== 'Enter') { e.preventDefault(); return false; }
                                                            });
                                                            branchSelect.classList.remove('is-invalid');
                                                        }
                                                        var branchIdHidden = document.getElementById('branchIdHidden');
                                                        if (branchIdHidden) branchIdHidden.value = data.branch_id;
                                                        if (branchOptionalText) branchOptionalText.textContent = '(Auto-selected - Required)';
                                                        if (branchInfoMsg) {
                                                            branchInfoMsg.innerHTML = '<i class="ti ti-check text-success"></i> Branch auto-selected: <strong>' + data.branch_name + '</strong> - Ready to login';
                                                            branchInfoMsg.style.display = 'block';
                                                            branchInfoMsg.classList.remove('text-danger', 'text-info');
                                                            branchInfoMsg.classList.add('text-success');
                                                        }
                                                        setTimeout(function() {
                                                            var passwordInput = document.querySelector('input[name="password"]');
                                                            if (passwordInput) passwordInput.focus();
                                                        }, 100);
                                                    } else {
                                                        isUserRole = true;
                                                        if (branchRequiredStar) branchRequiredStar.style.display = 'inline';
                                                        if (branchSelect) {
                                                            branchSelect.setAttribute('required', 'required');
                                                            branchSelect.disabled = false;
                                                            branchSelect.style.backgroundColor = '';
                                                            branchSelect.style.cursor = '';
                                                            branchSelect.value = '';
                                                        }
                                                        if (branchOptionalText) branchOptionalText.textContent = '(Required)';
                                                        if (branchInfoMsg) {
                                                            branchInfoMsg.innerHTML = '<i class="ti ti-alert-circle text-danger"></i> ' + (data.message || 'No active branch found. Please contact administrator.');
                                                            branchInfoMsg.style.display = 'block';
                                                            branchInfoMsg.classList.remove('text-success', 'text-info');
                                                            branchInfoMsg.classList.add('text-danger');
                                                        }
                                                        focusAndOpenBranchDropdown();
                                                    }
                                                } else {
                                                    if (branchSelectionDiv) branchSelectionDiv.style.display = 'none';
                                                    if (branchSelect) { branchSelect.disabled = false; branchSelect.value = ''; }
                                                    if (branchInfoMsg) {
                                                        branchInfoMsg.innerHTML = '<i class="ti ti-info-circle"></i> ' + (data.message || 'Could not detect branch');
                                                        branchInfoMsg.style.display = 'block';
                                                    }
                                                    updateBranchDisplay(data);
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error:', error);
                                                if (branchAutoDetectMsg) branchAutoDetectMsg.style.display = 'none';
                                                if (branchSelect) branchSelect.disabled = false;
                                                if (branchInfoMsg) {
                                                    branchInfoMsg.innerHTML = '<i class="ti ti-alert-circle text-danger"></i> Error detecting branch';
                                                    branchInfoMsg.style.display = 'block';
                                                    branchInfoMsg.classList.add('text-danger');
                                                }
                                                updateBranchDisplay({ success: false });
                                            });
                                        }
                                        
                                        if (emailInput.tagName === 'SELECT') {
                                            doBranchFetch();
                                        } else {
                                            clearTimeout(debounceTimer);
                                            debounceTimer = setTimeout(doBranchFetch, 800);
                                        }
                                    }
                                    if (emailInput.tagName === 'SELECT') {
                                        emailInput.addEventListener('change', handleEmailOrUserChange);
                                    } else {
                                        emailInput.addEventListener('input', function() {
                                            clearTimeout(debounceTimer);
                                            debounceTimer = setTimeout(handleEmailOrUserChange, 800);
                                        });
                                    }
                                    if (emailInput.tagName !== 'SELECT') {
                                        emailInput.addEventListener('blur', function() {
                                            if (this.value.trim() && this.value.includes('@')) {
                                                this.dispatchEvent(new Event('input'));
                                            }
                                        });
                                    }
                                    if (branchSelect) {
                                        branchSelect.addEventListener('change', function() {
                                            if (branchSelect.value) {
                                                setTimeout(function() {
                                                    var passwordInput = document.querySelector('input[name="password"]');
                                                    if (passwordInput) passwordInput.focus();
                                                }, 100);
                                            }
                                        });
                                    }
                                    if (loginForm && branchSelect) {
                                        loginForm.addEventListener('submit', function(e) {
                                            var branchValue = branchSelect.value;
                                            if (isUserRole && !branchValue) {
                                                e.preventDefault();
                                                branchSelect.classList.add('is-invalid');
                                                if (branchInfoMsg) {
                                                    branchInfoMsg.innerHTML = '<i class="ti ti-alert-circle text-danger"></i> Branch selection is required for user login!';
                                                    branchInfoMsg.style.display = 'block';
                                                    branchInfoMsg.classList.remove('text-success', 'text-info');
                                                    branchInfoMsg.classList.add('text-danger');
                                                }
                                                branchSelect.style.backgroundColor = '';
                                                branchSelect.style.cursor = '';
                                                branchSelect.removeAttribute('readonly');
                                                branchSelect.focus();
                                                return false;
                                            }
                                            if (isUserRole && branchValue) branchSelect.removeAttribute('readonly');
                                        });
                                    }
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
