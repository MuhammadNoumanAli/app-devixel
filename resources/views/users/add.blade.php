@extends('layouts.master')

@section('title', 'Add User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Create User Account</h4>
    <p class="text-muted mb-0">Add a new team member and assign roles and permissions</p>
  </div>
  <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to Users
  </a>
</div>

<div class="card shadow-sm">
  <div class="card-header border-bottom py-3">
    <h5 class="card-title mb-0">User Information</h5>
  </div>
  <div class="card-body pt-4">
    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
      @csrf

      <!-- Avatar Upload Section -->
      <div class="d-flex align-items-center gap-4 mb-4 pb-3 border-bottom">
        <div class="position-relative">
          <img
            id="avatar_preview"
            src="https://ui-avatars.com/api/?name=New+User&background=7367F0&color=fff&size=100"
            alt="user avatar"
            class="d-block rounded-circle"
            height="100"
            width="100"
            style="object-fit: cover; border: 3px solid #7367f0;"
          />
        </div>
        <div class="button-wrapper">
          <label for="upload_avatar" class="btn btn-primary btn-sm me-2 mb-1" tabindex="0">
            <i class="ti ti-upload me-1"></i> Upload Photo
            <input
              type="file"
              id="upload_avatar"
              name="avatar"
              class="d-none"
              accept="image/png, image/jpeg, image/jpg, image/webp, image/gif"
              onchange="previewAvatar(this)"
            />
          </label>
          <button type="button" class="btn btn-outline-secondary btn-sm mb-1" onclick="resetAvatar()">
            <i class="ti ti-refresh me-1"></i> Reset
          </button>
          <div class="text-muted small mt-1">Allowed JPG, PNG, GIF or WEBP. Max size 2MB.</div>
          @error('avatar')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
          <input
            id="first_name"
            type="text"
            class="form-control @error('first_name') is-invalid @enderror"
            name="first_name"
            value="{{ old('first_name') }}"
            placeholder="John"
            required
            autofocus
          />
          @error('first_name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-6">
          <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
          <input
            id="last_name"
            type="text"
            class="form-control @error('last_name') is-invalid @enderror"
            name="last_name"
            value="{{ old('last_name') }}"
            placeholder="Doe"
            required
          />
          @error('last_name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-6">
          <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
          <input
            id="email"
            type="email"
            class="form-control @error('email') is-invalid @enderror"
            name="email"
            value="{{ old('email') }}"
            placeholder="john.doe@example.com"
            required
          />
          @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-6">
          <label for="user_type" class="form-label">Role <span class="text-danger">*</span></label>
          <select
            id="user_type"
            class="form-select @error('user_type') is-invalid @enderror"
            name="user_type"
            onchange="getLoadCommission()"
            required
          >
            <option value="">-- Select Role --</option>
            <option value="Admin">Admin</option>
            <option value="Dispatcher">Dispatcher</option>
            <option value="Sales Agent">Sales Agent</option>
            <option value="Manager">Manager</option>
            <option value="Dispatch Supervisor">Dispatch Supervisor</option>
          </select>
          @error('user_type')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-6 div_load_commission" style="display: none;">
          <label for="load_commission" class="form-label">Load Commission (%)</label>
          <div class="input-group">
            <input
              id="load_commission"
              type="number"
              step="0.01"
              class="form-control @error('load_commission') is-invalid @enderror"
              name="load_commission"
              value="{{ old('load_commission') }}"
              placeholder="e.g. 10.00"
            />
            <span class="input-group-text">%</span>
          </div>
          @error('load_commission')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-6">
          <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
          <input
            id="password"
            type="password"
            class="form-control @error('password') is-invalid @enderror"
            name="password"
            placeholder="Minimum 8 characters"
            required
          />
          @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-6">
          <label for="password-confirm" class="form-label">Confirm Password <span class="text-danger">*</span></label>
          <input
            id="password-confirm"
            type="password"
            class="form-control"
            name="password_confirmation"
            placeholder="Confirm password"
            required
          />
        </div>
      </div>

      <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-primary">
          <i class="ti ti-check me-1"></i> Create User
        </button>
      </div>
    </form>
  </div>
</div>

<script>
const originalAvatarSrc = document.getElementById('avatar_preview').src;

function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('avatar_preview').src = e.target.result;
    }
    reader.readAsDataURL(input.files[0]);
  }
}

function resetAvatar() {
  document.getElementById('upload_avatar').value = '';
  document.getElementById('avatar_preview').src = originalAvatarSrc;
}

function getLoadCommission() {
  const roleSelect = document.getElementById('user_type');
  const commDiv = document.querySelector('.div_load_commission');
  if (roleSelect && commDiv) {
    if (roleSelect.value === 'Dispatcher') {
      commDiv.style.display = 'block';
    } else {
      commDiv.style.display = 'none';
    }
  }
}
</script>
@endsection
