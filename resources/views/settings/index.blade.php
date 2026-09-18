@extends('layouts.master')

@section('title', 'System Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">System Settings</h4>
    <p class="text-muted mb-0">Configure notification recipients and system defaults</p>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header border-bottom py-3">
    <h5 class="card-title mb-0">Notification Settings</h5>
  </div>
  <div class="card-body pt-4">
    <form method="POST" action="{{ route('settings.storeOrUpdate') }}">
      @csrf
      @if(isset($setting) && $setting)
        <input type="hidden" name="id" value="{{ $setting->id ?? $setting['id'] }}">
      @endif

      <div class="row g-3">
        <div class="col-md-6">
          <label for="email" class="form-label">Notification Email Address <span class="text-danger">*</span></label>
          <input
            id="email"
            type="email"
            class="form-control @error('email') is-invalid @enderror"
            name="email"
            value="{{ isset($setting) ? ($setting->email ?? $setting['email'] ?? '') : old('email') }}"
            placeholder="notifications@example.com"
            required
          />
          <small class="text-muted">System dispatch reports and alerts will be delivered to this address.</small>
          @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="d-flex justify-content-start mt-4">
        <button type="submit" class="btn btn-primary">
          <i class="ti ti-device-floppy me-1"></i> Save Settings
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
