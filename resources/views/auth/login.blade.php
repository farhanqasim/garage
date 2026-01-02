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

                                        <!-- Branch Selection (Only show if branches exist) -->
                                        @if(isset($branches) && $branches->count() > 0)
                                        <div class="mb-3">
                                            <label class="form-label">Branch</label>
                                            <div class="input-group">
                                                <select name="branch_id" class="form-control border-end-0 @error('branch_id') is-invalid @enderror">
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
                                            @error('branch_id')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        @endif

                                        <!-- Email -->
                                        <div class="mb-3">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="email" name="email" class="form-control border-end-0 @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                                                <span class="input-group-text border-start-0"><i class="ti ti-mail"></i></span>
                                            </div>
                                            @error('email')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>

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

                        {{-- <div class="my-4 d-flex justify-content-center align-items-center copyright-text">
                            <p>Copyright &copy; 2025 DreamsPOS</p>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
