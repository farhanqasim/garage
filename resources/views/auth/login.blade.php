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

                                        <!-- Branch Selection (Only show if branches exist) -->
                                        @if(isset($branches) && $branches->count() > 0)
                                        <div class="mb-3" id="branchSelectionDiv">
                                            <label class="form-label">Branch 
                                                <small class="text-muted">(Auto-detected from your email)</small>
                                            </label>
                                            <div class="input-group">
                                                <select name="branch_id" class="form-control border-end-0 @error('branch_id') is-invalid @enderror" id="branchSelect">
                                                    <option value="">-- Select Branch (Optional) --</option>
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
                                                Enter your email above to auto-detect your branch
                                            </small>
                                            @error('branch_id')
                                                <span class="text-danger small">{{ $message }}</span>
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
                                const branchInfoMsg = document.getElementById('branchInfoMsg');
                                const branchAutoDetectMsg = document.getElementById('branchAutoDetectMsg');
                                
                                let debounceTimer;
                                
                                if (emailInput && branchSelect) {
                                    // Auto-detect branch when email is entered
                                    emailInput.addEventListener('blur', function() {
                                        const email = this.value.trim();
                                        
                                        if (!email || !email.includes('@')) {
                                            return;
                                        }
                                        
                                        // Show loading message
                                        branchAutoDetectMsg.style.display = 'block';
                                        branchInfoMsg.style.display = 'none';
                                        
                                        // Clear previous timer
                                        clearTimeout(debounceTimer);
                                        
                                        // Debounce API call
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
                                                
                                                if (data.success) {
                                                    if (data.branch_id) {
                                                        // User has a branch - auto-select it
                                                        branchSelect.value = data.branch_id;
                                                        branchInfoMsg.innerHTML = '<i class="ti ti-check text-success"></i> Branch auto-selected: <strong>' + data.branch_name + '</strong>';
                                                        branchInfoMsg.style.display = 'block';
                                                        branchInfoMsg.classList.remove('text-danger');
                                                        branchInfoMsg.classList.add('text-success');
                                                    } else if (data.is_admin && data.branches) {
                                                        // Admin user - show all branches
                                                        branchInfoMsg.innerHTML = '<i class="ti ti-info-circle text-info"></i> Admin user - You can select any branch';
                                                        branchInfoMsg.style.display = 'block';
                                                        branchInfoMsg.classList.remove('text-danger', 'text-success');
                                                        branchInfoMsg.classList.add('text-info');
                                                    } else {
                                                        branchInfoMsg.innerHTML = '<i class="ti ti-info-circle"></i> No branch found for this email';
                                                        branchInfoMsg.style.display = 'block';
                                                        branchInfoMsg.classList.remove('text-success', 'text-info');
                                                    }
                                                } else {
                                                    branchInfoMsg.innerHTML = '<i class="ti ti-info-circle"></i> ' + (data.message || 'Could not detect branch');
                                                    branchInfoMsg.style.display = 'block';
                                                    branchInfoMsg.classList.remove('text-success', 'text-info');
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error:', error);
                                                branchAutoDetectMsg.style.display = 'none';
                                                branchInfoMsg.innerHTML = '<i class="ti ti-alert-circle text-danger"></i> Error detecting branch';
                                                branchInfoMsg.style.display = 'block';
                                                branchInfoMsg.classList.add('text-danger');
                                            });
                                        }, 500); // 500ms debounce
                                    });
                                    
                                    // If email is pre-filled (from old() helper), auto-detect on page load
                                    @if(old('email'))
                                        emailInput.dispatchEvent(new Event('blur'));
                                    @endif
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
