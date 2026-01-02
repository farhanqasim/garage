@extends('assets.headassets')
@section('title', 'Email')
@section('authentication')
        <div class="main-wrapper">
        <div class="account-content">
            <div class="login-wrapper login-new">
                <div class="row w-100">
                    <div class="col-lg-5 mx-auto">
                        <div class="login-content user-login">
                            <form method="POST" action="{{ route('password.email') }}">
                                @csrf
                                <div class="card">
                                    <div class="card-body p-5">

                                        <!-- Logo -->
                                        <div class="login-logo text-center mb-4">
                                            <img style="width: 60px;" src="{{ setting_value('logo', asset('assets/img/logo.svg')) }}"  alt="Img">
                                        </div>

                                        <!-- Heading -->
                                        <div class="login-userheading mb-4 text-center">
                                            <h3>Forgot Password?</h3>
                                            <h4>We'll send you a link to reset your password</h4>
                                        </div>

                                        <!-- Status Message -->
                                        @if (session('status'))
                                            <div class="alert alert-success" role="alert">
                                                {{ session('status') }}
                                            </div>
                                        @endif

                                        <!-- Email Field -->
                                        <div class="mb-3">
                                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input id="email" type="email" name="email" class="form-control border-end-0 @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                                                <span class="input-group-text border-start-0"><i class="ti ti-mail"></i></span>
                                            </div>
                                            @error('email')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="form-login mb-3">
                                            <button type="submit" class="btn btn-login w-100">
                                                Send Password Reset Link
                                            </button>
                                        </div>

                                        <!-- Back to Login -->
                                        <div class="signinform mt-3 text-center">
                                            <h4>Remember your password?
                                                <a href="{{ route('login') }}" class="hover-a">Back to Login</a>
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
