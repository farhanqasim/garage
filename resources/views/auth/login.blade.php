@extends('assets.headassets')
@section('title', 'Login')
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

                                        <div class="login-userheading mb-4 text-center">
                                            <h3>Welcome Back!</h3>

                                        </div>

                                        <!-- Email -->
                                        <div class="mb-3">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="email" name="email" id="emailInput" class="form-control border-end-0 @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                                                <span class="input-group-text border-start-0"><i class="ti ti-mail"></i></span>
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
                                                <small class="text-muted" id="branchOptionalText">(Auto-detected from your email)</small>
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
                                                Enter your email above to auto-detect your branch
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

                                        <!-- Remember Me -->
                                        <div class="d-flex justify-content-between">
                                            <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="remember">Remember Me</label>
                                        </div>
                                        @if (Route::has('password.request'))
                                            <div class="text-center mb-3">
                                                <a href="{{ route('password.request') }}" class="text-primary">Forgot Your Password?</a>
                                            </div>
                                        @endif
                                        </div>
                                        <!-- Submit -->
                                        <div class="form-login mb-3">
                                            <button type="submit" class="btn btn-login w-100">Sign In</button>
                                        </div>

                                        <!-- Sign in with Fingerprint — pehle save, phir login -->
                                        <div class="mb-3" id="webauthnSection">
                                            <div class="text-center text-muted small mb-2">— or —</div>
                                            <div class="alert alert-info py-2 px-3 small mb-2" role="alert">
                                                <strong>Fingerprint login:</strong> Pehli baar fingerprint <strong>save</strong> karein (neeche steps), uske baad agli dafa finger se hi login ho sakta hai.
                                            </div>
                                            <button type="button" id="btnFingerprintLogin" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2" title="Pehle email + password daalein, phir click karein. Pehli baar finger lagayenge to save hogi.">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 11c0 2.21-.9 4-2 4s-2-1.79-2-4 .9-4 2-4 2 1.79 2 4z"/><path d="M12 18c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="M4.93 4.93l1.41 1.41"/><path d="M17.66 17.66l1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="M6.34 17.66l-1.41 1.41"/><path d="M19.07 4.93l-1.41 1.41"/></svg>
                                                <span>Sign in with Fingerprint</span>
                                            </button>
                                            <small class="text-muted d-block mt-2 text-center">
                                                <strong>Step 1 (pehli baar):</strong> Email &amp; password upar daalein → "Sign in with Fingerprint" click karein → jab browser/device puche to <strong>pehle finger sensor par lagayein</strong> (ya Windows Hello / device passcode) — fingerprint save ho jayegi.<br>
                                                <strong>Step 2 (agli baar):</strong> Email &amp; password daalein, phir isi button par click karein aur finger lagayein — login ho jayega.
                                            </small>
                                        </div>

                                        <!-- Forgot Password -->


                                        <!-- Register Link -->
                                        <div class="signinform mt-3 text-center">
                                            <h4>Don’t have an account? <a href="{{ route('register') }}" class="hover-a">Sign Up</a></h4>
                                        </div>

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

                        <!-- JavaScript for Auto Branch Detection -->
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
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
                                
                                if (emailInput && branchSelect && branchSelectionDiv) {
                                    // Auto-detect branch when email is entered (on input change)
                                    emailInput.addEventListener('input', function() {
                                        const email = this.value.trim();
                                        
                                        if (!email || !email.includes('@')) {
                                            branchSelectionDiv.style.display = 'none';
                                            branchSelect.disabled = false;
                                            branchSelect.value = '';
                                            return;
                                        }
                                        
                                        // Show loading message immediately
                                        branchAutoDetectMsg.style.display = 'block';
                                        branchInfoMsg.style.display = 'none';
                                        branchSelectionDiv.style.display = 'block';
                                        branchSelect.disabled = true; // Disable while loading
                                        
                                        // Clear previous timer
                                        clearTimeout(debounceTimer);
                                        
                                        // Fast debounce - 800ms (less than 1 second)
                                        debounceTimer = setTimeout(function() {
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
                                                        
                                                        // Auto-select if branch_id provided
                                                        if (data.branch_id) {
                                                            branchSelect.value = data.branch_id;
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
                                        }, 800); // 800ms debounce (less than 1 second for faster response)
                                    });
                                    
                                    // Also trigger on blur (when user leaves email field)
                                    emailInput.addEventListener('blur', function() {
                                        if (this.value.trim() && this.value.includes('@')) {
                                            // Trigger the same logic
                                            this.dispatchEvent(new Event('input'));
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
                                            emailInput.dispatchEvent(new Event('input'));
                                        }, 500);
                                    @endif
                                }

                                // Fingerprint / WebAuthn login
                                (function() {
                                    var btnFingerprint = document.getElementById('btnFingerprintLogin');
                                    if (!btnFingerprint) return;
                                    if (typeof PublicKeyCredential === 'undefined') {
                                        btnFingerprint.disabled = true;
                                        btnFingerprint.title = 'Fingerprint login is not supported in this browser. Use Chrome, Edge, or Safari.';
                                        return;
                                    }
                                    function base64urlToBuffer(base64url) {
                                        var base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
                                        var pad = base64.length % 4;
                                        if (pad) base64 += new Array(5 - pad).join('=');
                                        var binary = atob(base64);
                                        var bytes = new Uint8Array(binary.length);
                                        for (var i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
                                        return bytes.buffer;
                                    }
                                    function bufferToBase64url(buffer) {
                                        var bytes = new Uint8Array(buffer);
                                        var binary = '';
                                        for (var i = 0; i < bytes.length; i++) binary += String.fromCharCode(bytes[i]);
                                        return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
                                    }
                                    btnFingerprint.addEventListener('click', function() {
                                        var emailEl = document.getElementById('emailInput');
                                        var passwordEl = document.querySelector('input[name="password"]');
                                        var email = emailEl && emailEl.value ? emailEl.value.trim() : '';
                                        var password = passwordEl ? passwordEl.value : '';
                                        btnFingerprint.disabled = true;
                                        btnFingerprint.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Use fingerprint...';

                                        // Step 1: Try fingerprint-only (conditional UI) – no password needed if user already has passkey
                                        function tryConditionalLogin() {
                                            return fetch('{{ route("webauthn.login.conditional.options") }}', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json'
                                                },
                                                body: JSON.stringify({})
                                            })
                                            .then(function(r) { return r.json(); })
                                            .then(function(data) {
                                                if (!data.success || !data.options) return null;
                                                var opt = data.options;
                                                var challenge = base64urlToBuffer(opt.challenge);
                                                var getOpt = {
                                                    challenge: challenge,
                                                    timeout: opt.timeout || 60000,
                                                    rpId: opt.rpId,
                                                    allowCredentials: [],
                                                    userVerification: opt.userVerification || 'preferred'
                                                };
                                                return navigator.credentials.get({ publicKey: getOpt, mediation: 'conditional' });
                                            })
                                            .then(function(cred) {
                                                if (!cred) return null;
                                                var response = cred.response;
                                                return fetch('{{ route("webauthn.login.verify") }}', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                        'Accept': 'application/json'
                                                    },
                                                    body: JSON.stringify({
                                                        credential: {
                                                            id: cred.id,
                                                            response: {
                                                                authenticatorData: bufferToBase64url(response.authenticatorData),
                                                                clientDataJSON: bufferToBase64url(response.clientDataJSON),
                                                                signature: bufferToBase64url(response.signature),
                                                                userHandle: response.userHandle ? bufferToBase64url(response.userHandle) : null
                                                            }
                                                        },
                                                        remember: document.getElementById('remember') && document.getElementById('remember').checked
                                                    })
                                                });
                                            });
                                        }

                                        tryConditionalLogin()
                                        .then(function(res) {
                                            if (res && res.ok) return res.json();
                                            if (res && res.status === 404) return null; // no passkey selected
                                            return null;
                                        })
                                        .then(function(data) {
                                            if (data && data.success && data.redirect) {
                                                window.location.href = data.redirect;
                                                return true;
                                            }
                                            return false;
                                        })
                                        .then(function(didLogin) {
                                            if (didLogin) return;
                                            // Step 2: No passkey or user cancelled – require email + password and either login or register fingerprint
                                            if (!email || !password) {
                                                btnFingerprint.disabled = false;
                                                btnFingerprint.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 11c0 2.21-.9 4-2 4s-2-1.79-2-4 .9-4 2-4 2 1.79 2 4z"/><path d="M12 18c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4"/></svg><span>Sign in with Fingerprint</span>';
                                                alert('Pehle email aur password daalein, phir is button ko click karein.\n\nPehli baar: fingerprint save hogi (finger lagayein jab puche).\nAgli dafa: finger se hi login ho sakta hai.');
                                                if (emailEl) emailEl.focus();
                                                return;
                                            }
                                            btnFingerprint.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Checking...';
                                            return fetch('{{ route("webauthn.login.options") }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({ email: email, password: password })
                                        })
                                        .then(function(r) { return r.json(); })
                                        .then(function(data) {
                                            if (!data.success) {
                                                btnFingerprint.disabled = false;
                                                btnFingerprint.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 11c0 2.21-.9 4-2 4s-2-1.79-2-4 .9-4 2-4 2 1.79 2 4z"/><path d="M12 18c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4"/></svg><span>Sign in with Fingerprint</span>';
                                                alert(data.message || 'Email ya password galat. Sahi daal kar phir "Sign in with Fingerprint" try karein.');
                                                return;
                                            }
                                            if (data.mode === 'register') {
                                                btnFingerprint.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Register fingerprint...';
                                                var opt = data.options;
                                                var challenge = base64urlToBuffer(opt.challenge);
                                                var pubKey = {
                                                    challenge: challenge,
                                                    rp: opt.rp,
                                                    user: {
                                                        id: base64urlToBuffer(opt.user.id),
                                                        name: opt.user.name,
                                                        displayName: opt.user.displayName || opt.user.name
                                                    },
                                                    pubKeyCredParams: opt.pubKeyCredParams || [{ type: 'public-key', alg: -7 }, { type: 'public-key', alg: -257 }],
                                                    timeout: opt.timeout || 60000,
                                                    attestation: opt.attestation || 'none',
                                                    authenticatorSelection: opt.authenticatorSelection || { userVerification: 'preferred' }
                                                };
                                                return navigator.credentials.create({ publicKey: pubKey })
                                                    .then(function(cred) {
                                                        if (!cred) throw new Error('No credential returned');
                                                        var response = cred.response;
                                                        var attestationObj = response.attestationObject;
                                                        var clientDataJSON = response.clientDataJSON;
                                                        return fetch('{{ route("webauthn.register.verify") }}', {
                                                            method: 'POST',
                                                            headers: {
                                                                'Content-Type': 'application/json',
                                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                                'Accept': 'application/json'
                                                            },
                                                            body: JSON.stringify({
                                                                credential: {
                                                                    id: cred.id,
                                                                    response: {
                                                                        attestationObject: bufferToBase64url(attestationObj),
                                                                        clientDataJSON: bufferToBase64url(clientDataJSON)
                                                                    }
                                                                },
                                                                device_name: 'Fingerprint / Biometric',
                                                                remember: document.getElementById('remember') && document.getElementById('remember').checked
                                                            })
                                                        });
                                                    });
                                            }
                                            btnFingerprint.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Use your fingerprint...';
                                            var opt = data.options;
                                            var challenge = base64urlToBuffer(opt.challenge);
                                            var allowCredentials = (opt.allowCredentials || []).map(function(c) {
                                                return { type: 'public-key', id: base64urlToBuffer(c.id) };
                                            });
                                            var getOpt = {
                                                challenge: challenge,
                                                timeout: opt.timeout || 60000,
                                                rpId: opt.rpId,
                                                allowCredentials: allowCredentials.length ? allowCredentials : undefined,
                                                userVerification: opt.userVerification || 'preferred'
                                            };
                                            return navigator.credentials.get({ publicKey: getOpt })
                                                .then(function(cred) {
                                                    if (!cred) throw new Error('No credential returned');
                                                    var response = cred.response;
                                                    return fetch('{{ route("webauthn.login.verify") }}', {
                                                        method: 'POST',
                                                        headers: {
                                                            'Content-Type': 'application/json',
                                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                            'Accept': 'application/json'
                                                        },
                                                        body: JSON.stringify({
                                                            credential: {
                                                                id: cred.id,
                                                                response: {
                                                                    authenticatorData: bufferToBase64url(response.authenticatorData),
                                                                    clientDataJSON: bufferToBase64url(response.clientDataJSON),
                                                                    signature: bufferToBase64url(response.signature),
                                                                    userHandle: response.userHandle ? bufferToBase64url(response.userHandle) : null
                                                                }
                                                            },
                                                            remember: document.getElementById('remember') && document.getElementById('remember').checked
                                                        })
                                                    });
                                                });
                                        })
                                        .then(function(res) {
                                            if (!res) return;
                                            if (res.ok) return res.json();
                                            return res.json().then(function(j) { throw new Error(j.message || 'Verification failed'); });
                                        })
                                        .then(function(data) {
                                            if (!data) return;
                                            if (data.redirect) {
                                                window.location.href = data.redirect;
                                                return;
                                            }
                                            if (data.success) {
                                                window.location.href = (data.redirect || '/home');
                                                return;
                                            }
                                            throw new Error(data.message || 'Login failed');
                                        })
                                        .catch(function(err) {
                                            btnFingerprint.disabled = false;
                                            btnFingerprint.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 11c0 2.21-.9 4-2 4s-2-1.79-2-4 .9-4 2-4 2 1.79 2 4z"/><path d="M12 18c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4"/></svg><span>Sign in with Fingerprint</span>';
                                            alert(err.message || 'Fingerprint cancel ya fail. Login ke liye upar "Sign In" button use karein (password se), ya phir dubara try karein: pehle fingerprint save karein (email + password daal kar is button par click, phir finger lagayein).');
                                        });
                                        })
                                        .catch(function() {
                                            btnFingerprint.disabled = false;
                                            btnFingerprint.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 11c0 2.21-.9 4-2 4s-2-1.79-2-4 .9-4 2-4 2 1.79 2 4z"/><path d="M12 18c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4"/></svg><span>Sign in with Fingerprint</span>';
                                        });
                                    });
                                })();
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
