{{-- Author: Leo Chia Chuen --}}

@extends('layout')

@section('title', 'Register - TARUMT Car Park')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Create Account</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}"
                                   placeholder="Enter your full name" required>
                        </div>
                        @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">TARUMT Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}"
                                   placeholder="your.email@student.tarc.edu.my" required>
                        </div>
                        <div class="form-text">Use your TARUMT email (@student.tarc.edu.my or @tarc.edu.my)</div>
                        @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">I am a <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="user_type" id="student"
                                       value="student" {{ old('user_type') == 'student' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="student">
                                    <i class="fas fa-graduation-cap me-1"></i>Student
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="user_type" id="staff"
                                       value="staff" {{ old('user_type') == 'staff' ? 'checked' : '' }}>
                                <label class="form-check-label" for="staff">
                                    <i class="fas fa-briefcase me-1"></i>Staff
                                </label>
                            </div>
                        </div>
                        @error('user_type')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3" id="student_id_field" style="display: none;">
                        <label for="student_id" class="form-label">Student ID <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            <input type="text" class="form-control @error('student_id') is-invalid @enderror"
                                   id="student_id" name="student_id" value="{{ old('student_id') }}"
                                   placeholder="e.g., 2301234">
                        </div>
                        @error('student_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3" id="staff_id_field" style="display: none;">
                        <label for="staff_id" class="form-label">Staff ID <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                            <input type="text" class="form-control @error('staff_id') is-invalid @enderror"
                                   id="staff_id" name="staff_id" value="{{ old('staff_id') }}"
                                   placeholder="e.g., S12345">
                        </div>
                        @error('staff_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone') }}"
                                   placeholder="e.g., 012-3456789">
                        </div>
                        @error('phone')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" placeholder="Create a strong password" required>
                        </div>
                        <div class="form-text">
                            <small>Password must contain:
                                <ul class="mb-0 ps-3">
                                    <li>At least 8 characters</li>
                                    <li>Uppercase and lowercase letters</li>
                                    <li>At least one number</li>
                                    <li>At least one special character (!@#$%^&*)</li>
                                </ul>
                            </small>
                        </div>
                        @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control"
                                   id="password_confirmation" name="password_confirmation"
                                   placeholder="Re-enter your password" required>
                        </div>
                    </div>

                    <div class="d-grid gap-1">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <div class="text-center">
                    <span class="text-muted">Already have an account?</span>
                    <a href="{{ route('login') }}" class="text-decoration-none fw-bold">Login here</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const studentRadio = document.getElementById('student');
    const staffRadio = document.getElementById('staff');
    const studentIdField = document.getElementById('student_id_field');
    const staffIdField = document.getElementById('staff_id_field');

    function toggleFields() {
        if (studentRadio.checked) {
            studentIdField.style.display = 'block';
            staffIdField.style.display = 'none';
        } else if (staffRadio.checked) {
            studentIdField.style.display = 'none';
            staffIdField.style.display = 'block';
        }
    }

    studentRadio.addEventListener('change', toggleFields);
    staffRadio.addEventListener('change', toggleFields);

    // Initialize on page load
    toggleFields();
});
</script>
@endpush
