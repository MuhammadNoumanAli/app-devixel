@extends('layouts.master')

@section('title', 'HR Settings & Shifts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">HR & Workforce Settings</h4>
    <p class="text-muted mb-0">Configure grace period, late deduction percentages, sales lead bonus triggers, and shifts</p>
  </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="ti ti-check me-2"></i>{{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

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

<div class="row">
  <!-- General HR & Payroll Policy -->
  <div class="col-xl-7 col-lg-6 mb-4">
    <div class="card shadow-sm h-100">
      <div class="card-header border-bottom py-3 d-flex align-items-center">
        <i class="ti ti-settings text-primary me-2 fs-4"></i>
        <h5 class="card-title mb-0">Operational Policies & Deduction Rules</h5>
      </div>
      <div class="card-body pt-4">
        <form action="{{ route('hr.settings.update') }}" method="POST">
          @csrf

          <!-- Section 1: Attendance & Penalties -->
          <div class="mb-4">
            <h6 class="text-uppercase text-muted fs-7 fw-semibold mb-3">
              <i class="ti ti-clock me-1"></i> Attendance & Grace Period
            </h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-medium">Grace Period (Minutes)</label>
                <div class="input-group">
                  <input type="number" name="grace_period_minutes" class="form-control" value="{{ old('grace_period_minutes', $settings->grace_period_minutes) }}" min="0" max="120" required>
                  <span class="input-group-text">mins</span>
                </div>
                <small class="text-muted">Grace allowance after shift start before late penalty triggers.</small>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-medium">Late Arrival Deduction (%)</label>
                <div class="input-group">
                  <input type="number" step="0.01" name="late_deduction_percent" class="form-control" value="{{ old('late_deduction_percent', $settings->late_deduction_percent) }}" min="0" max="100" required>
                  <span class="input-group-text">%</span>
                </div>
                <small class="text-muted">% of that day's salary deducted when late (e.g. 20% or 25%).</small>
              </div>
            </div>
          </div>

          <hr class="my-4">

          <!-- Section 2: Sales Agent Lead Bonus -->
          <div class="mb-4">
            <h6 class="text-uppercase text-muted fs-7 fw-semibold mb-3">
              <i class="ti ti-trophy me-1"></i> Sales Agent Lead Conversion Bonus
            </h6>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label fw-medium">Min Load Payable ($ USD)</label>
                <div class="input-group">
                  <span class="input-group-text font-monospace">$</span>
                  <input type="number" step="0.01" name="qualifying_lead_load_min_amount" class="form-control font-monospace" value="{{ old('qualifying_lead_load_min_amount', $settings->qualifying_lead_load_min_amount) }}" min="0" required>
                </div>
                <small class="text-muted">Qualifying load amount threshold in USD (e.g. $200).</small>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-medium">Days Window</label>
                <div class="input-group">
                  <input type="number" name="qualifying_lead_max_days" class="form-control font-monospace" value="{{ old('qualifying_lead_max_days', $settings->qualifying_lead_max_days) }}" min="1" max="365" required>
                  <span class="input-group-text">days</span>
                </div>
                <small class="text-muted">Max days from assignment to load booking.</small>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-medium">Lead Bonus (Rs. PKR)</label>
                <div class="input-group">
                  <span class="input-group-text font-monospace">Rs.</span>
                  <input type="number" step="0.01" name="sales_agent_lead_bonus_amount" class="form-control font-monospace fw-bold text-success" value="{{ old('sales_agent_lead_bonus_amount', $settings->sales_agent_lead_bonus_amount) }}" min="0" required>
                </div>
                <small class="text-muted">Direct PKR bonus credited to sales agent per qualified lead.</small>
              </div>
            </div>
          </div>

          <hr class="my-4">

          <!-- Section 3: Paid Leaves Policy -->
          <div class="mb-4">
            <h6 class="text-uppercase text-muted fs-7 fw-semibold mb-3">
              <i class="ti ti-calendar me-1"></i> Paid Leaves Quota & Caps
            </h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-medium">Yearly Paid Leaves Quota</label>
                <div class="input-group">
                  <input type="number" name="yearly_paid_leaves_quota" class="form-control" value="{{ old('yearly_paid_leaves_quota', $settings->yearly_paid_leaves_quota) }}" min="0" max="365" required>
                  <span class="input-group-text">days / year</span>
                </div>
                <small class="text-muted">Annual paid leave quota per employee (e.g. 14 days).</small>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-medium">Max Paid Leaves per Month</label>
                <div class="input-group">
                  <input type="number" name="max_paid_leaves_per_month" class="form-control" value="{{ old('max_paid_leaves_per_month', $settings->max_paid_leaves_per_month) }}" min="0" max="31" required>
                  <span class="input-group-text">days / mo</span>
                </div>
                <small class="text-muted">Max allowed paid leaves in one month (e.g. 2). Excess is unpaid.</small>
              </div>
            </div>
          </div>

          <hr class="my-4">

          <!-- Section 4: Calendar & Rest Days -->
          <div class="mb-4">
            <h6 class="text-uppercase text-muted fs-7 fw-semibold mb-3">
              <i class="ti ti-calendar-stats me-1"></i> Weekend Rest Days & Calendar Mode
            </h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-medium">Official Weekend Rest Days</label>
                @php
                  $weekends = $settings->weekend_days ?? ['Saturday', 'Sunday'];
                  $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                @endphp
                <div class="d-flex flex-wrap gap-2 pt-1">
                  @foreach($days as $day)
                    <div class="form-check me-2">
                      <input class="form-check-input" type="checkbox" name="weekend_days[]" value="{{ $day }}" id="weekend_{{ $day }}" {{ in_array($day, $weekends) ? 'checked' : '' }}>
                      <label class="form-check-label" for="weekend_{{ $day }}">{{ $day }}</label>
                    </div>
                  @endforeach
                </div>
                <small class="text-muted">Select official days off (e.g. Saturday & Sunday, or Friday depending on business needs).</small>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-medium">Daily Salary Divisor Mode</label>
                <select name="days_in_month_mode" class="form-select">
                  <option value="30_days" {{ $settings->days_in_month_mode == '30_days' ? 'selected' : '' }}>Standard 30 Days (Salary / 30)</option>
                  <option value="actual_days" {{ $settings->days_in_month_mode == 'actual_days' ? 'selected' : '' }}>Actual Days in Month (28-31)</option>
                  <option value="working_days" {{ $settings->days_in_month_mode == 'working_days' ? 'selected' : '' }}>Actual Working Days (Excl. Weekends)</option>
                </select>
                <small class="text-muted">Used to calculate daily salary rate for late/leave deductions.</small>
              </div>
            </div>
          </div>

          <div class="text-end pt-3">
            <button type="submit" class="btn btn-primary px-4">
              <i class="ti ti-device-floppy me-1"></i> Save HR Settings
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Shifts Management -->
  <div class="col-xl-5 col-lg-6 mb-4">
    <div class="card shadow-sm h-100">
      <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <i class="ti ti-calendar-time text-primary me-2 fs-4"></i>
          <h5 class="card-title mb-0">Work Shifts</h5>
        </div>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createShiftModal">
          <i class="ti ti-plus me-1"></i> Add Shift
        </button>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Shift Name</th>
                <th>Timing</th>
                <th>Grace</th>
                <th class="text-center">Status</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($shifts as $shift)
                <tr>
                  <td>
                    <div class="fw-semibold text-heading">{{ $shift->name }}</div>
                    @if($shift->is_night_shift)
                      <span class="badge bg-label-dark fs-8"><i class="ti ti-moon me-1"></i> Night Shift</span>
                    @endif
                  </td>
                  <td>
                    <small class="font-monospace">
                      {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}
                    </small>
                  </td>
                  <td>{{ $shift->grace_minutes }}m</td>
                  <td class="text-center">
                    <span class="badge {{ $shift->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">
                      {{ $shift->is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="text-center">
                    <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#editShiftModal{{ $shift->id }}">
                      <i class="ti ti-edit"></i>
                    </button>
                    <form action="{{ route('hr.shifts.destroy', $shift->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this shift?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-icon btn-text-danger rounded-pill">
                        <i class="ti ti-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>

                <!-- Edit Shift Modal -->
                <div class="modal fade" id="editShiftModal{{ $shift->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <form action="{{ route('hr.shifts.update', $shift->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                          <h5 class="modal-title">Edit Shift: {{ $shift->name }}</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <div class="mb-3">
                            <label class="form-label">Shift Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $shift->name }}" required>
                          </div>
                          <div class="row g-3 mb-3">
                            <div class="col-6">
                              <label class="form-label">Start Time</label>
                              <input type="time" name="start_time" class="form-control" value="{{ substr($shift->start_time, 0, 5) }}" required>
                            </div>
                            <div class="col-6">
                              <label class="form-label">End Time</label>
                              <input type="time" name="end_time" class="form-control" value="{{ substr($shift->end_time, 0, 5) }}" required>
                            </div>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Grace Minutes</label>
                            <input type="number" name="grace_minutes" class="form-control" value="{{ $shift->grace_minutes }}" min="0" max="120" required>
                          </div>
                          <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_night_shift" value="1" id="night_{{ $shift->id }}" {{ $shift->is_night_shift ? 'checked' : '' }}>
                            <label class="form-check-label" for="night_{{ $shift->id }}">Night Shift (Crosses Midnight)</label>
                          </div>
                          <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active_{{ $shift->id }}" {{ $shift->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="active_{{ $shift->id }}">Shift Active</label>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-primary">Update Shift</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-4 text-muted">No shifts defined yet.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Create Shift Modal -->
<div class="modal fade" id="createShiftModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('hr.shifts.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Create New Shift</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Shift Name (e.g. Morning, Evening, Night US)</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Night US Operations" required>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label">Start Time</label>
              <input type="time" name="start_time" class="form-control" value="08:00" required>
            </div>
            <div class="col-6">
              <label class="form-label">End Time</label>
              <input type="time" name="end_time" class="form-control" value="17:00" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Grace Minutes</label>
            <input type="number" name="grace_minutes" class="form-control" value="15" min="0" max="120" required>
            <small class="text-muted">Grace allowance for this specific shift.</small>
          </div>
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="is_night_shift" value="1" id="create_night_shift">
            <label class="form-check-label" for="create_night_shift">Night Shift (Crosses Midnight)</label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="create_shift_active" checked>
            <label class="form-check-label" for="create_shift_active">Active</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create Shift</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
