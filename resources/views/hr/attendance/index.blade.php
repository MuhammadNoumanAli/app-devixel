@extends('layouts.master')

@section('title', 'Attendance & Biometric Punches')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Attendance Ledger</h4>
    <p class="text-muted mb-0">Biometric fingerprint punches, late deductions (25%), grace period tracking, and supervisor regularization</p>
  </div>
  <div>
    <!-- Web Kiosk Punch Button (Clock In / Clock Out) -->
    @can('hr-attendance-punch')
      <form action="{{ route('hr.attendance.punch') }}" method="POST" class="d-inline">
        @csrf
        @if(!$myTodayAttendance || !$myTodayAttendance->check_in)
          <button type="submit" class="btn btn-success btn-lg shadow-sm">
            <i class="ti ti-fingerprint me-2"></i> Clock In / Thumb In
          </button>
        @elseif(!$myTodayAttendance->check_out)
          <button type="submit" class="btn btn-warning btn-lg shadow-sm">
            <i class="ti ti-fingerprint me-2"></i> Clock Out / Thumb Out
          </button>
        @else
          <button type="button" class="btn btn-outline-success btn-lg" disabled>
            <i class="ti ti-circle-check me-2"></i> Shift Completed ({{ \Carbon\Carbon::parse($myTodayAttendance->check_out)->format('h:i A') }})
          </button>
        @endif
      </form>
    @endcan
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

<!-- My Punch Status Banner if today's check-in exists -->
@if($myTodayAttendance)
<div class="card shadow-sm mb-4 border-start border-4 border-{{ $myTodayAttendance->status == 'late' ? 'warning' : 'success' }}">
  <div class="card-body py-3 d-flex flex-wrap justify-content-between align-items-center">
    <div class="d-flex align-items-center">
      <div class="avatar avatar-md me-3 bg-label-{{ $myTodayAttendance->status == 'late' ? 'warning' : 'success' }} rounded d-flex align-items-center justify-content-center">
        <i class="ti ti-fingerprint fs-2"></i>
      </div>
      <div>
        <h6 class="mb-0 fw-bold">Your Today's Punch ({{ \Carbon\Carbon::today()->format('M d, Y') }})</h6>
        <div class="text-muted">
          Check-in: <strong class="text-body">{{ $myTodayAttendance->check_in ? \Carbon\Carbon::parse($myTodayAttendance->check_in)->format('h:i:s A') : '--' }}</strong> |
          Check-out: <strong class="text-body">{{ $myTodayAttendance->check_out ? \Carbon\Carbon::parse($myTodayAttendance->check_out)->format('h:i:s A') : '--' }}</strong> |
          Status: <span class="badge {{ $myTodayAttendance->status == 'late' ? 'bg-label-warning' : 'bg-label-success' }}">{{ ucfirst($myTodayAttendance->status) }}</span>
        </div>
      </div>
    </div>
    @if($myTodayAttendance->late_deduction_amount > 0)
      <div>
        <span class="badge bg-label-danger fs-7 px-3 py-2">
          <i class="ti ti-alert-triangle me-1"></i> Late by {{ $myTodayAttendance->late_minutes }}m (Penalty: -Rs. {{ number_format($myTodayAttendance->late_deduction_amount, 2) }})
        </span>
      </div>
    @endif
  </div>
</div>
@endif

<!-- Filter Bar -->
<div class="card shadow-sm mb-4">
  <div class="card-body py-3">
    <form action="{{ route('hr.attendance.index') }}" method="GET" class="row g-3 align-items-center">
      <div class="col-md-2">
        <label class="form-label fs-8 fw-semibold text-muted mb-1">SPECIFIC DATE</label>
        <input type="date" name="date" class="form-control" value="{{ request('date', $date) }}">
      </div>
      <div class="col-md-2">
        <label class="form-label fs-8 fw-semibold text-muted mb-1">OR MONTH</label>
        <input type="month" name="month" class="form-control" value="{{ request('month', $month) }}">
      </div>
      @can('hr-attendance-regularize')
      <div class="col-md-3">
        <label class="form-label fs-8 fw-semibold text-muted mb-1">EMPLOYEE</label>
        <select name="user_id" class="form-select">
          <option value="">All Employees</option>
          @foreach($employees as $emp)
            <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>
              {{ $emp->name }}
            </option>
          @endforeach
        </select>
      </div>
      @endcan
      <div class="col-md-2">
        <label class="form-label fs-8 fw-semibold text-muted mb-1">STATUS</label>
        <select name="status" class="form-select">
          <option value="">All Statuses</option>
          <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
          <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
          <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
          <option value="on_leave" {{ request('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
          <option value="compensatory_off" {{ request('status') == 'compensatory_off' ? 'selected' : '' }}>Compensatory Off</option>
        </select>
      </div>
      <div class="col-md-3 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-primary" style="margin-top: 20px;"><i class="ti ti-search me-1"></i> Filter</button>
        <a href="{{ route('hr.attendance.index') }}" class="btn btn-outline-secondary" style="margin-top: 20px;">Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- Attendance Ledger Table -->
<div class="card shadow-sm">
  <div class="table-responsive text-nowrap">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Date</th>
          <th>Employee</th>
          <th>Shift</th>
          <th>Check-In</th>
          <th>Check-Out</th>
          <th>Late Arrival</th>
          <th class="text-center">Status</th>
          <th>Portal Login (Location & IP)</th>
          <th>Late Deduction</th>
          @can('hr-attendance-regularize')
          <th class="text-center" style="width: 110px;">Regularize</th>
          @endcan
        </tr>
      </thead>
      <tbody>
        @forelse($attendances as $att)
          @php
            $profile = $att->user?->employeeProfile;
            $statusClasses = [
              'present' => 'bg-label-success',
              'late' => 'bg-label-warning',
              'half_day' => 'bg-label-info',
              'absent' => 'bg-label-danger',
              'on_leave' => 'bg-label-primary',
              'compensatory_off' => 'bg-label-secondary',
              'holiday' => 'bg-label-dark',
            ];
          @endphp
          <tr>
            <td>
              <span class="fw-semibold text-heading font-monospace">
                {{ \Carbon\Carbon::parse($att->work_date)->format('M d, Y') }}
              </span>
              <div class="text-muted fs-8">{{ \Carbon\Carbon::parse($att->work_date)->format('l') }}</div>
            </td>
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar avatar-xs me-2">
                  <span class="avatar-initial rounded-circle bg-label-primary fs-8 fw-bold">
                    {{ strtoupper(substr($att->user?->name ?? 'U', 0, 1)) }}
                  </span>
                </div>
                <div>
                  <div class="fw-medium text-heading">{{ $att->user?->name }}</div>
                  <small class="text-muted font-monospace">{{ $profile?->employee_code ?? '' }}</small>
                </div>
              </div>
            </td>
            <td>
              <small class="text-body">{{ $att->shift?->name ?? 'General' }}</small>
            </td>
            <td>
              @if($att->check_in)
                <span class="font-monospace fw-medium text-heading">
                  {{ \Carbon\Carbon::parse($att->check_in)->format('h:i:s A') }}
                </span>
              @else
                <span class="text-muted">--:--</span>
              @endif
            </td>
            <td>
              @if($att->check_out)
                <span class="font-monospace fw-medium text-heading">
                  {{ \Carbon\Carbon::parse($att->check_out)->format('h:i:s A') }}
                </span>
              @else
                <span class="badge bg-label-secondary fs-8">Missing Out-Punch</span>
              @endif
            </td>
            <td>
              @if($att->late_minutes > 0)
                <span class="badge bg-label-warning font-monospace">
                  <i class="ti ti-clock-alert me-1"></i> {{ $att->late_minutes }} mins
                </span>
              @else
                <span class="text-muted">On Time</span>
              @endif
            </td>
            <td class="text-center">
              <span class="badge {{ $statusClasses[$att->status] ?? 'bg-label-secondary' }}">
                {{ ucfirst(str_replace('_', ' ', $att->status)) }}
              </span>
              @if($att->is_regularized)
                <div class="fs-8 text-info mt-1" title="Regularized by {{ $att->regularizer?->name }}">
                  <i class="ti ti-check-double"></i> Regularized
                </div>
              @endif
            </td>
            <td>
              @php
                $dateKey = is_string($att->work_date) ? substr($att->work_date, 0, 10) : $att->work_date->toDateString();
                $portalLogin = isset($loginLogsByEmployeeDate[$att->user_id . '_' . $dateKey])
                    ? $loginLogsByEmployeeDate[$att->user_id . '_' . $dateKey]->first()
                    : ($att->user?->latestLoginLog ?? null);
              @endphp
              @if($portalLogin)
                <div>
                  <span class="badge bg-label-info font-monospace fs-8" title="Browser: {{ $portalLogin->browser }} on {{ $portalLogin->platform }} ({{ $portalLogin->device }}) | Time: {{ $portalLogin->login_at?->format('h:i A') }}">
                    <i class="ti ti-map-pin me-1"></i>{{ $portalLogin->city ?? 'Local' }}, {{ $portalLogin->country_code ?? 'PK' }}
                  </span>
                </div>
                <small class="text-muted font-monospace fs-8" title="{{ $portalLogin->browser }} ({{ $portalLogin->platform }})">{{ $portalLogin->ip_address }}</small>
              @else
                <span class="badge bg-label-light text-muted fs-8">
                  <i class="ti ti-device-laptop me-1"></i> No Web Login
                </span>
              @endif
            </td>
            <td>
              @if($att->late_deduction_amount > 0)
                <span class="text-danger fw-bold font-monospace">
                  -Rs. {{ number_format($att->late_deduction_amount, 2) }}
                </span>
              @else
                <span class="text-muted">Rs. 0.00</span>
              @endif
            </td>
            @can('hr-attendance-regularize')
            <td class="text-center">
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#regularizeModal{{ $att->id }}">
                <i class="ti ti-adjustments me-1"></i> Adjust
              </button>

              <!-- Regularization Modal -->
              <div class="modal fade" id="regularizeModal{{ $att->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content text-start">
                    <form action="{{ route('hr.attendance.regularize', $att->id) }}" method="POST">
                      @csrf
                      <div class="modal-header">
                        <h5 class="modal-title">Regularize Attendance: {{ $att->user?->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <div class="alert alert-info py-2 mb-3 fs-8">
                          Date: <strong>{{ $att->work_date }}</strong> | Daily Rate: <strong>Rs. {{ number_format($att->daily_salary_rate, 2) }}</strong>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Attendance Status</label>
                          <select name="status" class="form-select" required>
                            <option value="present" {{ $att->status == 'present' ? 'selected' : '' }}>Present</option>
                            <option value="late" {{ $att->status == 'late' ? 'selected' : '' }}>Late (Subject to Deduction)</option>
                            <option value="compensatory_off" {{ $att->status == 'compensatory_off' ? 'selected' : '' }}>Compensatory Off</option>
                            <option value="half_day" {{ $att->status == 'half_day' ? 'selected' : '' }}>Half Day</option>
                            <option value="absent" {{ $att->status == 'absent' ? 'selected' : '' }}>Absent</option>
                          </select>
                        </div>
                        <div class="row g-3 mb-3">
                          <div class="col-6">
                            <label class="form-label">Adjust Check-In (HH:MM)</label>
                            <input type="time" name="check_in" class="form-control" value="{{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('H:i') : '08:00' }}">
                          </div>
                          <div class="col-6">
                            <label class="form-label">Adjust Check-Out (HH:MM)</label>
                            <input type="time" name="check_out" class="form-control" value="{{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('H:i') : '17:00' }}">
                          </div>
                        </div>
                        <div class="form-check form-switch mt-2">
                          <input class="form-check-input" type="checkbox" name="waive_penalty" value="1" id="waive_{{ $att->id }}" checked>
                          <label class="form-check-label text-success fw-medium" for="waive_{{ $att->id }}">
                            Waive Late Deduction Penalty ($0.00 deduction)
                          </label>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Regularization</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </td>
            @endcan
          </tr>
        @empty
          <tr>
            <td colspan="{{ auth()->user()->can('hr-attendance-regularize') ? 9 : 8 }}" class="text-center py-4 text-muted">
              No attendance records found for this period.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($attendances->hasPages())
    <div class="card-footer border-top py-3 d-flex justify-content-end">
      {{ $attendances->links() }}
    </div>
  @endif
</div>
@endsection
