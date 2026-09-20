@extends('layouts.master')

@section('title', 'Edit Employee Profile - ' . $user->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Edit Employee Profile</h4>
    <p class="text-muted mb-0">Configure base salary, biometric thumb ID, work shift, and dispatcher target bonus for {{ $user->name }}</p>
  </div>
  <a href="{{ route('hr.employees.index') }}" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to Directory
  </a>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <ul class="mb-0 ps-3">
    @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
    @endforeach
  </ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<form action="{{ route('hr.employees.update', $user->id) }}" method="POST">
  @csrf
  @method('PUT')

  <div class="row">
    <!-- Basic Information & Salary -->
    <div class="col-lg-8">
      <!-- HR Core Details -->
      <div class="card shadow-sm mb-4">
        <div class="card-header border-bottom py-3">
          <h5 class="card-title mb-0"><i class="ti ti-id me-2 text-primary"></i> Employment & Salary Information</h5>
        </div>
        <div class="card-body pt-4">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-medium">Employee Code</label>
              <input type="text" name="employee_code" class="form-control" value="{{ old('employee_code', $profile->employee_code ?? 'EMP-' . str_pad($user->id, 4, '0', STR_PAD_LEFT)) }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Designation / Role Title</label>
              <input type="text" name="designation" class="form-control" placeholder="e.g. Senior Freight Dispatcher, Sales Executive" value="{{ old('designation', $profile->designation) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Department</label>
              <input type="text" name="department" class="form-control" placeholder="e.g. Dispatch & Operations, Sales / Lead Gen, Accounts, HR" value="{{ old('department', $profile->department) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Assigned Shift</label>
              <select name="shift_id" class="form-select">
                <option value="">-- Select Shift --</option>
                @foreach($shifts as $shift)
                  <option value="{{ $shift->id }}" {{ old('shift_id', $profile->shift_id) == $shift->id ? 'selected' : '' }}>
                    {{ $shift->name }} ({{ substr($shift->start_time, 0, 5) }} - {{ substr($shift->end_time, 0, 5) }}, Grace: {{ $shift->grace_minutes }}m)
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Joining Date</label>
              <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', $profile->joining_date?->toDateString()) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Monthly Base Salary (Rs. PKR)</label>
              <div class="input-group">
                <span class="input-group-text font-monospace">Rs.</span>
                <input type="number" step="0.01" name="base_salary" class="form-control font-monospace fw-bold text-success" value="{{ old('base_salary', $profile->base_salary ?? 0) }}" min="0" required>
              </div>
              <small class="text-muted">Daily salary rate is automatically derived as (Salary / 30).</small>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Employment Status</label>
              <select name="status" class="form-select">
                <option value="active" {{ old('status', $profile->status) == 'active' ? 'selected' : '' }}>Active</option>
                <option value="probation" {{ old('status', $profile->status) == 'probation' ? 'selected' : '' }}>Probation</option>
                <option value="resigned" {{ old('status', $profile->status) == 'resigned' ? 'selected' : '' }}>Resigned</option>
                <option value="terminated" {{ old('status', $profile->status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Per-Dispatcher Target Bonus Configuration -->
      <div class="card shadow-sm mb-4">
        <div class="card-header border-bottom py-3 d-flex align-items-center">
          <i class="ti ti-chart-arrows-vertical text-warning me-2 fs-4"></i>
          <div>
            <h5 class="card-title mb-0">Dynamic Dispatcher Load Volume Milestone Target</h5>
            <small class="text-muted">Configure monthly volume appreciation bonuses (e.g. loads > $2,000 / $4,000 USD booked in month)</small>
          </div>
        </div>
        <div class="card-body pt-4">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-medium">Monthly Load Volume Target ($ USD)</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" step="0.01" name="monthly_load_target_amount" class="form-control font-monospace" value="{{ old('monthly_load_target_amount', $profile->monthly_load_target_amount) }}" placeholder="e.g. 2000.00 or 4000.00">
              </div>
              <small class="text-muted">Threshold of monthly booked load volume (USD) to qualify for bonus.</small>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Bonus Calculation Mode</label>
              <select name="target_bonus_type" class="form-select" id="target_bonus_type">
                <option value="fixed" {{ old('target_bonus_type', $profile->target_bonus_type) == 'fixed' ? 'selected' : '' }}>Fixed Amount (Rs. PKR)</option>
                <option value="percentage" {{ old('target_bonus_type', $profile->target_bonus_type) == 'percentage' ? 'selected' : '' }}>Percentage of Volume (e.g. 3.5%)</option>
                <option value="both" {{ old('target_bonus_type', $profile->target_bonus_type) == 'both' ? 'selected' : '' }}>Both (Fixed Rs. + %)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Fixed Bonus Amount (Rs. PKR)</label>
              <div class="input-group">
                <span class="input-group-text font-monospace">Rs.</span>
                <input type="number" step="0.01" name="target_bonus_fixed" class="form-control font-monospace" value="{{ old('target_bonus_fixed', $profile->target_bonus_fixed ?? 0) }}" min="0">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-medium">Percentage Bonus (%)</label>
              <div class="input-group">
                <input type="number" step="0.01" name="target_bonus_percentage" class="form-control font-monospace" value="{{ old('target_bonus_percentage', $profile->target_bonus_percentage ?? 0) }}" min="0" max="100">
                <span class="input-group-text">%</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Banking Details -->
      <div class="card shadow-sm mb-4">
        <div class="card-header border-bottom py-3">
          <h5 class="card-title mb-0"><i class="ti ti-building-bank me-2 text-info"></i> Bank Disbursement Information (Pakistani Banks)</h5>
        </div>
        <div class="card-body pt-4">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-medium">Bank Name</label>
              <input type="text" name="bank_name" class="form-control" placeholder="e.g. HBL, Meezan, Faysal, MCB" value="{{ old('bank_name', $profile->bank_name) }}">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-medium">Account Number</label>
              <input type="text" name="bank_account_number" class="form-control" placeholder="Account Number" value="{{ old('bank_account_number', $profile->bank_account_number) }}">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-medium">IBAN Number</label>
              <input type="text" name="iban" class="form-control" placeholder="PK00..." value="{{ old('iban', $profile->iban) }}">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Biometrics & Emergency Info -->
    <div class="col-lg-4">
      <!-- Biometric Device Enrollment -->
      <div class="card shadow-sm mb-4 border-primary">
        <div class="card-header border-bottom py-3 bg-label-primary">
          <h5 class="card-title mb-0 text-primary"><i class="ti ti-fingerprint me-2"></i> Biometric Thumb Device</h5>
        </div>
        <div class="card-body pt-4">
          <div class="mb-3">
            <label class="form-label fw-medium">Hardware Biometric User ID / PIN</label>
            <input type="text" name="biometric_thumb_id" class="form-control font-monospace" placeholder="e.g. 1001 or BIO-04" value="{{ old('biometric_thumb_id', $profile->biometric_thumb_id) }}">
            <small class="text-muted">Unique fingerprint scan ID mapped from the physical biometric thumb clock.</small>
          </div>
        </div>
      </div>

      <!-- Direct Commissions -->
      <div class="card shadow-sm mb-4">
        <div class="card-header border-bottom py-3">
          <h5 class="card-title mb-0"><i class="ti ti-percentage me-2 text-secondary"></i> Direct Commissions</h5>
        </div>
        <div class="card-body pt-4">
          <div class="mb-3">
            <label class="form-label fw-medium">Flat Commission Per Load (Rs. PKR)</label>
            <div class="input-group">
              <span class="input-group-text font-monospace">Rs.</span>
              <input type="number" step="0.01" name="load_commission" class="form-control font-monospace" value="{{ old('load_commission', $user->load_commission ?? 0) }}">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-medium">Commission Percentage (%)</label>
            <div class="input-group">
              <input type="number" step="0.01" name="commission_percent" class="form-control font-monospace" value="{{ old('commission_percent', $user->commission_percent ?? 0) }}">
              <span class="input-group-text">%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Emergency Contact -->
      <div class="card shadow-sm mb-4">
        <div class="card-header border-bottom py-3">
          <h5 class="card-title mb-0"><i class="ti ti-phone-call me-2 text-danger"></i> Emergency Contact</h5>
        </div>
        <div class="card-body pt-4">
          <div class="mb-3">
            <label class="form-label fw-medium">Contact Person</label>
            <input type="text" name="emergency_contact_name" class="form-control" placeholder="Name" value="{{ old('emergency_contact_name', $profile->emergency_contact_name) }}">
          </div>
          <div class="mb-3">
            <label class="form-label fw-medium">Contact Phone</label>
            <input type="text" name="emergency_contact_phone" class="form-control" placeholder="+1..." value="{{ old('emergency_contact_phone', $profile->emergency_contact_phone) }}">
          </div>
        </div>
      </div>

      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary btn-lg">
          <i class="ti ti-device-floppy me-1"></i> Save Profile
        </button>
      </div>
    </div>
  </div>
</form>
@endsection
