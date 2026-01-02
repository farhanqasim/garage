@extends('assets.headassets')
@section('title', 'Reset')
@section('authentication')
    <div class="main-wrapper">
        <div class="account-content">
            <div class="login-wrapper login-new">
                <div class="row w-100">
                    <div class="col-lg-5 mx-auto">
                        <div class="login-content user-login">

                            <form method="POST" action="{{ route('password.update') }}">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">

                                <div class="card">
                                    <div class="card-body p-5">

                                        <!-- Logo -->
                                        <div class="login-logo text-center mb-4">
                                            <img style="width: 60px;" src="{{ setting_value('logo', asset('assets/img/logo.svg')) }}"  alt="Img">
                                        </div>

                                        <!-- Heading -->
                                        <div class="login-userheading mb-4 text-center">
                                            <h3>Reset Your Password</h3>
                                            <h4>Enter your new password below</h4>
                                        </div>

                                        <!-- Email -->
                                        <div class="mb-3">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input id="email" type="email" name="email" class="form-control border-end-0 @error('email') is-invalid @enderror"
                                                       value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
                                                <span class="input-group-text border-start-0"><i class="ti ti-mail"></i></span>
                                            </div>
                                            @error('email')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- New Password -->
                                        <div class="mb-3">
                                            <label class="form-label">New Password <span class="text-danger">*</span></label>
                                            <div class="pass-group">
                                                <input id="password" type="password" name="password"
                                                       class="pass-input form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                                                <span class="ti toggle-password ti-eye-off text-gray-9"></span>
                                            </div>
                                            @error('password')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Confirm Password -->
                                        <div class="mb-3">
                                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                            <div class="pass-group">
                                                <input id="password-confirm" type="password" name="password_confirmation" class="pass-inputs form-control" required autocomplete="new-password">
                                                <span class="ti toggle-passwords ti-eye-off text-gray-9"></span>
                                            </div>
                                        </div>

                                        <!-- Submit -->
                                        <div class="form-login mb-3">
                                            <button type="submit" class="btn btn-login w-100">Reset Password</button>
                                        </div>

                                        <!-- Back to Login -->
                                        <div class="signinform mt-3 text-center">
                                            <h4>Remember your password?
                                                <a href="{{ route('login') }}" class="hover-a">Sign In</a>
                                            </h4>
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
