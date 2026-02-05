@extends('assets.headassets')
@section('title', 'Employee Login')
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
                                            <h3>Employee Login</h3>
                                            <p class="text-muted">Welcome Back!</p>
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

                                        <!-- Back to Main Login -->
                                        <div class="signinform mt-3 text-center">
                                            <h4>Admin Login? <a href="{{ route('login') }}" class="hover-a">Click Here</a></h4>
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

                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
