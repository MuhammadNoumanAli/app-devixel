@extends('layouts.master')

@section('title', 'Change Password - ' . $user->full_name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Change Password: {{ $user->full_name }}</h4>
    <p class="text-muted mb-0">Set a new secure password for this user account</p>
  </div>
  <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to Users
  </a>
</div>



@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <div class="fw-semibold mb-1"><i class="ti ti-alert-triangle me-1"></i> Please correct the following errors:</div>
    <ul class="mb-0 ps-3">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<div class="row justify-content-center">
  <div class="col-lg-6 col-md-8">
    <div class="card shadow-sm">
      <div class="card-header border-bottom py-3 d-flex align-items-center">
        <i class="ti ti-lock me-2 text-primary fs-4"></i>
        <h5 class="card-title mb-0">Security Credentials</h5>
      </div>
      <div class="card-body pt-4">
        <form method="POST" action="{{ route('users.updatePassword', $user->id) }}">
          @csrf
          @method('PATCH')

          <div class="mb-3">
            <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="ti ti-key"></i></span>
              <input
                id="password"
                type="password"
                class="form-control @error('password') is-invalid @enderror"
                name="password"
                placeholder="Enter minimum 8 characters"
                required
                autocomplete="new-password"
              />
            </div>
            @error('password')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-4">
            <label for="password-confirm" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="ti ti-check"></i></span>
              <input
                id="password-confirm"
                type="password"
                class="form-control"
                name="password_confirmation"
                placeholder="Re-type new password"
                required
                autocomplete="new-password"
              />
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
              <i class="ti ti-device-floppy me-1"></i> Update Password
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
