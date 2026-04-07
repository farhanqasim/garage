@extends('assets.headassets')
@section('title', 'Login')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<style>
.email-dropdown-list {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1050;
    max-height: 220px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    list-style: none;
    margin: 0;
    padding: 0;
}
.email-dropdown-list .email-dropdown-item {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    font-size: 0.9rem;
}
.email-dropdown-list .email-dropdown-item:hover,
.email-dropdown-list .email-dropdown-item.active {
    background: #e7f1ff;
}
.email-dropdown-list .email-dropdown-item:last-child { border-bottom: none; }
.email-dropdown-list .email-dropdown-item .highlight {
    background: rgba(13, 110, 253, 0.2);
    font-weight: 600;
    padding: 0 1px;
    border-radius: 2px;
}
.email-dropdown-list:empty { display: none !important; }
</style>
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
                                        </ul>

                                        <!-- Tab Content -->
                                        <div class="tab-content" id="loginMethodTabContent">
                                            <!-- PIN Tab -->
                                            <div class="tab-pane fade show active" id="pin-pane" role="tabpanel" aria-labelledby="pin-tab">
                                                <!-- Email (searchable dropdown + manual type) -->
                                        <div class="mb-3">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <div class="input-group position-relative">
                                                <input type="email" name="email" id="emailInput" class="form-control border-end-0 @error('email') is-invalid @enderror" placeholder="Search or type email" value="{{ old('email', $rememberedEmail ?? '') }}" required autofocus autocomplete="off" role="combobox" aria-expanded="false" aria-autocomplete="list" aria-controls="emailDropdownList">
                                                <span class="input-group-text border-start-0"><i class="ti ti-user"></i></span>
                                                <div id="emailDropdownList" class="email-dropdown-list" role="listbox" style="display: none;"></div>
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

                                        <div class="form-check mb-3">
                                            <input type="checkbox" name="remember" id="remember" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
                                            <label for="remember" class="form-check-label">Remember Me</label>
                                        </div>

                                                <!-- Submit -->
                                                <div class="form-login mb-3">
                                                    <button type="submit" class="btn btn-login w-100">Sign In</button>
                                                </div>
                                            </div>

                                            <!-- Fingerprint Tab -->
                                            <div class="tab-pane fade" id="fingerprint-pane" role="tabpanel" aria-labelledby="fingerprint-tab">
                                                <div class="text-center mb-4">
                                                    <div class="fingerprint-container mb-3" id="fingerprintContainer" role="button" tabindex="0" style="cursor: pointer;" title="فنگر پرنٹ اسکین کریں">
                                                        <button type="button" id="bio-fingerprint-btn" class="btn btn-link p-0" style="border: none; background: none;">
                                                            <div class="fingerprint-icon" style="width: 120px; height: 120px; margin: 0 auto; border-radius: 50%; background: #f8f9fa; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">
                                                                <i class="ti ti-fingerprint" style="font-size: 64px; color: #3b82f6;"></i>
                                                            </div>
                                                        </button>
                                                        <div class="progress mt-3" id="fingerprintProgress" style="display: none; max-width: 200px; margin: 0 auto;">
                                                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                                        </div>
                                                    </div>
                                                    <p class="text-muted small mb-2" id="bio-status-text">Hold to Scan Finger</p>
                                                    <p class="text-muted small mb-2" id="bio-message-text" style="display: none;"></p>
                                                    <div id="bio-register-section" style="display: none;" class="mt-2">
                                                        <small class="text-muted">Login with PIN first, then go to Profile to register fingerprint on this device.</small>
                                                    </div>
                                                    <div id="bio-not-supported" style="display: none;" class="mt-3 text-center">
                                                        <div class="alert alert-warning py-2 px-3 d-inline-block text-start" style="max-width: 400px;">
                                                            <strong>⚠️ Biometric not available</strong>
                                                            <p class="mb-1 small" id="bio-unsupported-reason"></p>
                                                            <hr class="my-1">
                                                            <p class="mb-0 small text-muted">
                                                                <strong>Solutions:</strong><br>
                                                                • On PC: Use <code>http://localhost</code><br>
                                                                • On Phone: Deploy to HTTPS domain<br>
                                                                • Use PIN or Pattern tab instead
                                                            </p>
                                                        </div>
                                                    </div>
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
                                var userEmails = @json($userEmails ?? []);

                                (function initEmailAutocomplete() {
                                    var emailInput = document.getElementById('emailInput');
                                    var dropdown = document.getElementById('emailDropdownList');
                                    if (!emailInput || !dropdown) return;

                                    var selectedIndex = -1;

                                    function filterEmails(query) {
                                        var q = (query || '').trim().toLowerCase();
                                        if (!q) return userEmails.slice();
                                        return userEmails.filter(function(email) {
                                            return email.toLowerCase().indexOf(q) !== -1;
                                        });
                                    }

                                    function escapeHtml(s) {
                                        if (!s) return '';
                                        return String(s)
                                            .replace(/&/g, '&amp;')
                                            .replace(/</g, '&lt;')
                                            .replace(/>/g, '&gt;')
                                            .replace(/"/g, '&quot;');
                                    }
                                    function escapeRegex(s) {
                                        return String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                                    }
                                    function highlightMatch(email, query) {
                                        if (!query || !query.trim()) return escapeHtml(email);
                                        var q = query.trim();
                                        var re = new RegExp(escapeRegex(q), 'gi');
                                        var result = '';
                                        var lastIndex = 0;
                                        var match;
                                        while ((match = re.exec(email)) !== null) {
                                            result += escapeHtml(email.substring(lastIndex, match.index)) + '<span class="highlight">' + escapeHtml(match[0]) + '</span>';
                                            lastIndex = match.index + match[0].length;
                                        }
                                        result += escapeHtml(email.substring(lastIndex));
                                        return result || escapeHtml(email);
                                    }

                                    function renderList(emails) {
                                        var query = (emailInput.value || '').trim();
                                        dropdown.innerHTML = '';
                                        dropdown.style.display = emails.length ? 'block' : 'none';
                                        selectedIndex = -1;
                                        emails.forEach(function(email, i) {
                                            var div = document.createElement('div');
                                            div.className = 'email-dropdown-item';
                                            div.setAttribute('role', 'option');
                                            div.setAttribute('data-index', i);
                                            div.setAttribute('data-email', email);
                                            div.innerHTML = highlightMatch(email, query);
                                            div.addEventListener('click', function() {
                                                emailInput.value = this.getAttribute('data-email');
                                                dropdown.style.display = 'none';
                                                dropdown.innerHTML = '';
                                                emailInput.dispatchEvent(new Event('input', { bubbles: true }));
                                            });
                                            dropdown.appendChild(div);
                                        });
                                    }

                                    function openDropdown() {
                                        var emails = filterEmails(emailInput.value);
                                        renderList(emails);
                                    }

                                    emailInput.addEventListener('focus', function() {
                                        openDropdown();
                                    });

                                    emailInput.addEventListener('input', function() {
                                        openDropdown();
                                    });

                                    emailInput.addEventListener('keydown', function(e) {
                                        var items = dropdown.querySelectorAll('.email-dropdown-item');
                                        if (e.key === 'ArrowDown') {
                                            e.preventDefault();
                                            selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                                            if (selectedIndex >= 0 && items[selectedIndex]) {
                                                items.forEach(function(el, i) { el.classList.toggle('active', i === selectedIndex); });
                                                items[selectedIndex].scrollIntoView({ block: 'nearest' });
                                            }
                                            return;
                                        }
                                        if (e.key === 'ArrowUp') {
                                            e.preventDefault();
                                            selectedIndex = Math.max(selectedIndex - 1, -1);
                                            items.forEach(function(el, i) { el.classList.toggle('active', i === selectedIndex); });
                                            if (selectedIndex >= 0 && items[selectedIndex]) items[selectedIndex].scrollIntoView({ block: 'nearest' });
                                            return;
                                        }
                                        if (e.key === 'Enter' && selectedIndex >= 0 && items[selectedIndex]) {
                                            e.preventDefault();
                                            emailInput.value = items[selectedIndex].getAttribute('data-email') || items[selectedIndex].textContent;
                                            dropdown.style.display = 'none';
                                            dropdown.innerHTML = '';
                                            emailInput.dispatchEvent(new Event('input', { bubbles: true }));
                                            return;
                                        }
                                        if (e.key === 'Escape') {
                                            dropdown.style.display = 'none';
                                            dropdown.innerHTML = '';
                                            selectedIndex = -1;
                                        }
                                    });

                                    document.addEventListener('click', function(e) {
                                        if (!emailInput.contains(e.target) && !dropdown.contains(e.target)) {
                                            dropdown.style.display = 'none';
                                            dropdown.innerHTML = '';
                                        }
                                    });
                                })();

                                // Fingerprint / WebAuthn: real biometric login (conditional UI)
                                const fingerprintBtn = document.getElementById('bio-fingerprint-btn');
                                const fingerprintStatus = document.getElementById('bio-status-text');
                                const fingerprintError = document.getElementById('fingerprintError');
                                let userHasFingerprint = false;

                                function getDeviceType() {
                                    var ua = navigator.userAgent || '';
                                    if (/iPhone|iPad|iPod/i.test(ua)) return 'iPhone';
                                    if (/Android/i.test(ua)) return 'Android';
                                    if (/Windows/i.test(ua)) return 'Windows PC';
                                    if (/Mac/i.test(ua)) return 'Mac';
                                    return 'this device';
                                }
                                function isSecureContext() {
                                    return window.isSecureContext === true;
                                }
                                function isLocalhost() {
                                    var host = window.location.hostname;
                                    return host === 'localhost' || host === '127.0.0.1' || host === '::1';
                                }
                                function showBioUnsupported(message) {
                                    if (fingerprintBtn) fingerprintBtn.style.display = 'none';
                                    var container = document.getElementById('fingerprintContainer');
                                    if (container) container.style.display = 'none';
                                    var reasonEl = document.getElementById('bio-unsupported-reason');
                                    if (reasonEl) reasonEl.textContent = message || '';
                                    var notSupportedDiv = document.getElementById('bio-not-supported');
                                    if (notSupportedDiv) notSupportedDiv.style.display = 'block';
                                }
                                function showNoPasskeyState() {
                                    var statusEl = document.getElementById('bio-status-text') || document.getElementById('bio-message-text');
                                    if (statusEl) {
                                        statusEl.innerHTML = '<span class="text-warning">' +
                                            '⚠️ اس ڈیوائس پر کوئی فنگر پرنٹ رجسٹرڈ نہیں۔' +
                                            '<br><small class="text-muted">No fingerprint registered on this device.</small>' +
                                            '</span>';
                                    }
                                    var regSection = document.getElementById('bio-register-section');
                                    if (regSection) regSection.style.display = 'block';
                                }

                                if (!window.PublicKeyCredential) {
                                    showBioUnsupported('Biometric login is not supported in this browser. Use Chrome or Edge.');
                                } else if (!isSecureContext()) {
                                    showBioUnsupported(
                                        'Biometric login requires HTTPS or localhost. ' +
                                        'You are accessing via ' + window.location.protocol + '//' + window.location.hostname + ' which is not secure. ' +
                                        'On ' + getDeviceType() + ', use HTTPS.'
                                    );
                                } else if (!isLocalhost() && window.location.protocol !== 'https:') {
                                    showBioUnsupported(
                                        'آپ کا ' + getDeviceType() + ' اس سائٹ کو IP ایڈریس سے کھول رہا ہے۔ فنگر پرنٹ کے لیے HTTPS ضروری ہے۔ Biometric requires HTTPS on mobile devices.'
                                    );
                                }

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
                                        if (!credential) {
                                            setFingerprintUI('idle');
                                            showNoPasskeyState();
                                            throw new Error('No passkey on this device');
                                        }
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
                                        var msg = (err && err.message) ? err.message : String(err);
                                        var isNotAllowed = (err && err.name === 'NotAllowedError') || /not allowed|user cancelled|canceled/i.test(msg);
                                        var isNotFound = /not found|no passkey|no credential/i.test(msg);
                                        if (isNotAllowed || isNotFound) {
                                            showNoPasskeyState();
                                        } else {
                                            if (fingerprintError) {
                                                fingerprintError.textContent = msg || 'پہلے پروفائل میں فنگر پرنٹ رجسٹر کریں یا تصدیق دوبارہ کریں۔';
                                                fingerprintError.style.display = 'block';
                                            }
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
                                
                                // Check fingerprint status when email is entered
                                function checkFingerprintStatus(email) {
                                    if (!email || !email.includes('@')) {
                                        userHasFingerprint = false;
                                        return;
                                    }

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
                                            const fingerprintStatusEl = document.getElementById('bio-status-text');
                                            if (fingerprintStatusEl) {
                                                fingerprintStatusEl.textContent = 'Fingerprint not set. Please set your fingerprint first.';
                                                fingerprintStatusEl.style.color = '#dc3545';
                                            }
                                        } else {
                                            const fingerprintStatusEl = document.getElementById('bio-status-text');
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
                                        
                                        checkFingerprintStatus(email);
                                        
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
                                        
                                        function setBranchOptions(branches) {
                                            if (!branchSelect || !branches || !branches.length) return;
                                            branchSelect.innerHTML = '<option value="">-- Select Branch --</option>';
                                            branches.forEach(function(b) {
                                                var opt = document.createElement('option');
                                                opt.value = b.id;
                                                opt.textContent = b.name + (b.code ? ' (' + b.code + ')' : '');
                                                branchSelect.appendChild(opt);
                                            });
                                        }

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
                                                    var branches = data.branches;
                                                    if (!branches && data.branch_id) {
                                                        branches = [{ id: data.branch_id, name: data.branch_name || '', code: data.branch_code || '' }];
                                                    }
                                                    if (branches && branches.length) {
                                                        setBranchOptions(branches);
                                                        if (branches.length === 1) branchSelect.value = branches[0].id;
                                                    }
                                                    branchSelect.disabled = false;
                                                    branchSelect.removeAttribute('readonly');
                                                    branchSelect.style.backgroundColor = '';
                                                    branchSelect.style.cursor = '';

                                                    if (data.is_admin) {
                                                        isUserRole = false;
                                                        if (branchRequiredStar) branchRequiredStar.style.display = 'none';
                                                        branchSelect.removeAttribute('required');
                                                        if (branchOptionalText) branchOptionalText.textContent = '(Optional for Admin)';
                                                        if (branchInfoMsg) {
                                                            branchInfoMsg.innerHTML = '<i class="ti ti-info-circle text-info"></i> Admin user - Select branch below if needed';
                                                            branchInfoMsg.style.display = 'block';
                                                            branchInfoMsg.classList.remove('text-danger', 'text-success');
                                                            branchInfoMsg.classList.add('text-info');
                                                        }
                                                    } else {
                                                        isUserRole = true;
                                                        if (branchRequiredStar) branchRequiredStar.style.display = 'inline';
                                                        branchSelect.setAttribute('required', 'required');
                                                        if (branchOptionalText) branchOptionalText.textContent = '(Required - select your branch)';
                                                        if (branchInfoMsg) {
                                                            branchInfoMsg.innerHTML = '<i class="ti ti-info-circle text-info"></i> Select your branch from the list below';
                                                            branchInfoMsg.style.display = 'block';
                                                            branchInfoMsg.classList.remove('text-danger', 'text-success');
                                                            branchInfoMsg.classList.add('text-info');
                                                        }
                                                    }
                                                    focusAndOpenBranchDropdown();
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
                                                if (branchSelect) { branchSelect.disabled = false; branchSelect.value = ''; }
                                                isUserRole = false;
                                                if (branchInfoMsg) {
                                                    branchInfoMsg.innerHTML = '<i class="ti ti-info-circle text-info"></i> Could not load branches. Sign in anyway – we\'ll use your default branch.';
                                                    branchInfoMsg.style.display = 'block';
                                                    branchInfoMsg.classList.remove('text-danger', 'text-success');
                                                    branchInfoMsg.classList.add('text-info');
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
                                                branchSelect.focus();
                                                return false;
                                            }
                                            // Ensure branch select is enabled so its value is submitted (disabled fields are not sent)
                                            branchSelect.disabled = false;
                                        });
                                    }
                                    @if(old('email'))
                                        setTimeout(function() {
                                            emailInput.dispatchEvent(new Event(emailInput.tagName === 'SELECT' ? 'change' : 'input'));
                                        }, 500);
                                    @endif
                                }
                                
                                // Email is a plain input; branch is fetched on blur/input via getUserBranchByEmail
                            });
                        </script>

                        <script>
                        (function () {
                            var STORAGE_KEY = 'remembered_email';

                            document.addEventListener('DOMContentLoaded', function () {
                                var emailInputs = document.querySelectorAll('input[name="email"], input[type="email"], #emailInput');
                                var rememberCheckbox = document.getElementById('remember');

                                if (emailInputs.length === 0) return;

                                var savedEmail = localStorage.getItem(STORAGE_KEY);
                                if (savedEmail && savedEmail.trim() !== '') {
                                    emailInputs.forEach(function (input) {
                                        if (!input.value || input.value.trim() === '') {
                                            input.value = savedEmail;
                                        }
                                    });
                                    if (rememberCheckbox) rememberCheckbox.checked = true;
                                }

                                emailInputs.forEach(function (input) {
                                    input.addEventListener('input', function () {
                                        var val = this.value;
                                        emailInputs.forEach(function (other) {
                                            if (other !== input) other.value = val;
                                        });
                                    });
                                    input.addEventListener('blur', function () {
                                        if (rememberCheckbox && rememberCheckbox.checked && this.value.trim() !== '') {
                                            localStorage.setItem(STORAGE_KEY, this.value.trim());
                                        }
                                    });
                                });

                                var forms = document.querySelectorAll('form');
                                forms.forEach(function (form) {
                                    form.addEventListener('submit', function () {
                                        var emailVal = '';
                                        emailInputs.forEach(function (input) {
                                            if (input.value.trim() !== '') emailVal = input.value.trim();
                                        });
                                        if (rememberCheckbox && rememberCheckbox.checked && emailVal) {
                                            localStorage.setItem(STORAGE_KEY, emailVal);
                                        } else if (rememberCheckbox && !rememberCheckbox.checked) {
                                            localStorage.removeItem(STORAGE_KEY);
                                        }
                                    });
                                });

                                if (rememberCheckbox) {
                                    rememberCheckbox.addEventListener('change', function () {
                                        if (!this.checked) {
                                            localStorage.removeItem(STORAGE_KEY);
                                        }
                                    });
                                }
                            });
                        })();
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
