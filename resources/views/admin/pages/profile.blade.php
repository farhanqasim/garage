@extends('layouts.app')
@section('title', __('Profile'))
@section('content')
    <div class="content">
        <section class="section">
            <div class="section-header mb-4">
                <h1>Update Profile</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-8 col-lg-12">
                        <div class="card">
                            <form action="{{ route('user.profile.update', auth()->user()->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="card-header">
                                    <h4>Update Profile</h4>
                                </div>

                                <div class="card-body">
                                    <!-- First Name & Last Name -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Name</label>
                                            <input type="text" class="form-control" name="name" value="{{ auth()->user()->name }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Phone</label>
                                            <input type="text" class="form-control" name="phone" value="{{ auth()->user()->phone }}" required>
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" value="{{ auth()->user()->email }}" required>
                                        </div>
                                    </div>

                                    <!-- Profile Image -->
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Profile Image</label>
                                            <input type="file" class="form-control" name="profile_img">
                                            @if(auth()->user()->profile_img)
                                                <img src="{{ asset(auth()->user()->profile_img) }}" alt="Profile Image" class="mt-2" width="100">
                                            @endif
                                        </div>
                                    </div>

                                    <hr>

                                    <!-- Change Password Section -->
                                    <h5>Change Password</h5>

                                    <!-- Old Password -->
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Old Password</label>
                                            <input type="password" class="form-control" name="old_password" id="old_password">
                                            <button type="button" class="btn btn-primary mt-2" id="verify_password">Verify</button>
                                            @error('old_password')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- New Password & Confirm Password (Initially Hidden) -->
                                    <div id="new-password-fields" style="display: none;">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">New Password</label>
                                                <input type="password" class="form-control" name="new_password">
                                                @error('new_password')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Confirm Password</label>
                                                <input type="password" class="form-control" name="new_password_confirmation">
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
<script>
        document.getElementById('verify_password').addEventListener('click', function () {
            let oldPassword = document.getElementById('old_password').value;

            fetch("{{ route('user.password.verify') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ old_password: oldPassword })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('new-password-fields').style.display = 'block';
                } else {
                    alert(data.message);
                }
            });
        });
    </script>
@endsection
