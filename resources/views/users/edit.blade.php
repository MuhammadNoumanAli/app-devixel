@extends('layouts.master')

@section('title', 'Edit User - ' . $user->full_name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Edit User: {{ $user->full_name }}</h4>
    <p class="text-muted mb-0">Update user profile information, role assignment, and commission rate</p>
  </div>
  <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to Users
  </a>
</div>

@if (session('status'))
  <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <i class="ti ti-check me-2"></i> {{ session('status') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@elseif (session('error'))
  <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <i class="ti ti-alert-circle me-2"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

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

<div class="card shadow-sm">
  <div class="card-header border-bottom py-3">
    <h5 class="card-title mb-0">User Information</h5>
  </div>
  <div class="card-body pt-4">
    <form method="POST" action="{{ route('users.update', $user->id) }}">
      @csrf
      @method('PATCH')

      <div class="row g-3">
        <div class="col-md-6">
          <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
          <input
            id="first_name"
            type="text"
            class="form-control @error('first_name') is-invalid @enderror"
            name="first_name"
            value="{{ old('first_name', $user->first_name) }}"
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
            value="{{ old('last_name', $user->last_name) }}"
            required
          />
          @error('last_name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-6">
          <label for="email" class="form-label">Email Address</label>
          <input
            id="email"
            type="email"
            class="form-control bg-light"
            name="email"
            value="{{ $user->email }}"
            disabled
          />
          <small class="text-muted">Email address cannot be modified</small>
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
            @foreach($roles as $role)
              <option value="{{ $role->name }}" @selected(in_array($role->id, $user_roles))>
                {{ $role->name }}
              </option>
            @endforeach
          </select>
          @error('user_type')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-6 div_load_commission" style="display: {{ $user->hasRole('Dispatcher') ? 'block' : 'none' }};">
          <label for="load_commission" class="form-label">Load Commission (%)</label>
          <div class="input-group">
            <input
              id="load_commission"
              type="number"
              step="0.01"
              class="form-control @error('load_commission') is-invalid @enderror"
              name="load_commission"
              value="{{ old('load_commission', $user->load_commission) }}"
              placeholder="e.g. 5"
            />
            <span class="input-group-text">%</span>
          </div>
          @error('load_commission')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <i class="ti ti-device-floppy me-1"></i> Update User
        </button>
      </div>
    </form>
  </div>
</div>

<script>
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
