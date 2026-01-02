@extends('assets.headassets')
@section('title', 'Register')
@section('authentication')
<div class="main-wrapper">
        <div class="account-content">
            <div class="login-wrapper login-new">
                <div class="row w-100">
                    <div class="col-lg-5 mx-auto">
                        <div class="login-content user-login">


                            <form method="POST" action="{{ route('register') }}">
                                @csrf
                                <div class="card">
                                    <div class="card-body p-5">
                                        <div class="login-userheading">
                                                <div class="login-logo">
                                                <img style="width: 60px;" src="{{ setting_value('logo', asset('assets/img/logo.svg')) }}"  alt="Img">
                                            </div>
                                        </div>

                                        <!-- Name -->
                                        <div class="mb-3">
                                            <label class="form-label">Name <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="text" name="name" class="form-control border-end-0 @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
                                                <span class="input-group-text border-start-0"><i class="ti ti-user"></i></span>
                                            </div>
                                            @error('name')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Email -->
                                        <div class="mb-3">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="email" name="email" class="form-control border-end-0 @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
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
                                                <input type="password" name="password" class="pass-input form-control @error('password') is-invalid @enderror" required>
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
                                                <input type="password" name="password_confirmation" class="pass-inputs form-control" required>
                                                <span class="ti toggle-passwords ti-eye-off text-gray-9"></span>
                                            </div>
                                        </div>

                                        <!-- Terms Checkbox -->
                                        <div class="form-login authentication-check mb-3">
                                            <label class="checkboxs ps-4 mb-0 pb-0 line-height-1">
                                                <input type="checkbox" required>
                                                <span class="checkmarks"></span> I agree to the <a href="#" class="text-primary">Terms & Privacy</a>
                                            </label>
                                        </div>

                                        <!-- Submit -->
                                        <div class="form-login">
                                            <button type="submit" class="btn btn-login w-100">Sign Up</button>
                                        </div>

                                        <!-- Sign In Link -->
                                        <div class="signinform mt-3 text-center">
                                            <h4>Already have an account? <a href="{{ route('login') }}" class="hover-a">Sign In</a></h4>
                                        </div>

                                        <!-- Social Logins -->
                                        <div class="form-setlogin or-text d-none">
                                            <h4>OR</h4>
                                        </div>
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
