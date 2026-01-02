@extends('assets.headassets')
@section('title', 'Confirm')
@section('authentication')
   <div class="main-wrapper">
        <div class="account-content">
            <div class="login-wrapper login-new">
                <div class="row w-100">
                    <div class="col-lg-5 mx-auto">
                        <div class="login-content user-login">
                            <form method="POST" action="{{ route('password.confirm') }}">
                                @csrf
                                <div class="card">
                                    <div class="card-body p-5">

                                        <!-- Logo -->
                                        <div class="login-logo text-center mb-4">
                                            <img style="width: 60px;" src="{{ setting_value('logo', asset('assets/img/logo.svg')) }}"  alt="Img">
                                        </div>

                                        <!-- Heading -->
                                        <div class="login-userheading mb-4 text-center">
                                            <h3>Confirm Your Password</h3>
                                            <h4>Please confirm your password before continuing</h4>
                                        </div>

                                        <!-- Password Field -->
                                        <div class="mb-3">
                                            <label class="form-label">Password <span class="text-danger">*</span></label>
                                            <div class="pass-group">
                                                <input id="password" type="password" name="password" class="form-control pass-input @error('password') is-invalid @enderror" required autocomplete="current-password">
                                                <span class="ti toggle-password ti-eye-off text-gray-9"></span>
                                            </div>
                                            @error('password')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="form-login mb-3">
                                            <button type="submit" class="btn btn-login w-100">
                                                Confirm Password
                                            </button>
                                        </div>

                                        <!-- Forgot Password -->
                                        @if (Route::has('password.request'))
                                            <div class="text-center mb-3">
                                                <a href="{{ route('password.request') }}" class="text-primary">
                                                    Forgot Your Password?
                                                </a>
                                            </div>
                                        @endif

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
