@extends('assets.headassets')
@section('title', 'Select Branch')
@section('authentication')
    <div class="main-wrapper">
        <div class="account-content">
            <div class="login-wrapper login-new">
                <div class="row w-100">
                    <div class="col-lg-5 mx-auto">
                        <div class="login-content user-login">
                            <form method="POST" action="{{ route('branch.select.complete') }}">
                                @csrf
                                <div class="card">
                                    <div class="card-body p-5">
                                        <div class="login-logo text-center mb-4">
                                            <img style="width: 60px;" src="{{ setting_value('logo', asset('assets/img/logo.svg')) }}" alt="Img">
                                        </div>

                                        <div class="login-userheading mb-4 text-center">
                                            <h3>Select Branch</h3>
                                            <p class="text-muted">Please select a branch to continue</p>
                                        </div>
                                        @php
                                            $branches = session('pending_branches', []);
                                            // Convert array back to collection if needed
                                            if (!empty($branches) && is_array($branches)) {
                                                $branches = collect($branches)->map(function($branch) {
                                                    return (object) $branch;
                                                });
                                            }
                                        @endphp
                                        
                                        @if($branches && count($branches) > 0)
                                            <div class="mb-3">
                                                <label class="form-label">Select Branch <span class="text-danger">*</span></label>
                                                <select name="branch_id" class="form-control @error('branch_id') is-invalid @enderror"  autofocus>
                                                    <option value="">-- Select Branch --</option>
                                                    @foreach($branches as $branch)
                                                        @php
                                                            $branchObj = is_array($branch) ? (object) $branch : $branch;
                                                        @endphp
                                                        <option value="{{ $branchObj->id }}" {{ old('branch_id') == $branchObj->id ? 'selected' : '' }}>
                                                            {{ $branchObj->branch_name }} 
                                                            @if(isset($branchObj->branch_code))
                                                                ({{ $branchObj->branch_code }})
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('branch_id')
                                                    <span class="text-danger small">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-login mb-3">
                                                <button type="submit" class="btn btn-login w-100">Continue</button>
                                            </div>
                                        @else
                                            <div class="alert alert-warning">
                                                <p>No active branches found for your account. Please contact administrator.</p>
                                            </div>
                                            <div class="form-login mb-3">
                                                <a href="{{ route('login') }}" class="btn btn-secondary w-100">Back to Login</a>
                                            </div>
                                        @endif

                                        <div class="signinform mt-3 text-center">
                                            <a href="{{ route('login') }}" class="hover-a">Back to Login</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

