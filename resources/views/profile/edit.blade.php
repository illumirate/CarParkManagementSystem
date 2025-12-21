{{-- Author: Leo Chia Chuen --}}
@extends('layout')

@section('title', 'Edit Profile - TARUMT Car Park')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-edit me-2"></i>Edit Profile
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" value="{{ $user->email }}" disabled>
                            <div class="form-text">Email cannot be changed</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                   placeholder="e.g., 012-3456789">
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">User Type</label>
                            <input type="text" class="form-control" value="{{ ucfirst($user->user_type ?? 'User') }}" disabled>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="faculty" class="form-label">Faculty</label>
                            <input type="text" class="form-control @error('faculty') is-invalid @enderror"
                                   id="faculty" name="faculty" value="{{ old('faculty', $user->faculty) }}"
                                   placeholder="e.g., Faculty of Computing and Information Technology">
                            @error('faculty')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            @if($user->isStudent())
                            <label for="course" class="form-label">Course</label>
                            <input type="text" class="form-control @error('course') is-invalid @enderror"
                                   id="course" name="course" value="{{ old('course', $user->course) }}"
                                   placeholder="e.g., Bachelor of Computer Science">
                            @error('course')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @else
                            <label for="department" class="form-label">Department</label>
                            <input type="text" class="form-control @error('department') is-invalid @enderror"
                                   id="department" name="department" value="{{ old('department', $user->department) }}"
                                   placeholder="e.g., IT Department">
                            @error('department')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @endif
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
