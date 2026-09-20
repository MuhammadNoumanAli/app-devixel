@extends('layouts.master')

@section('title', 'User Login Logs & Location Tracking')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">User Login Logs & Security Audit</h4>
    <p class="text-muted mb-0">Track employee login timestamps, IP addresses, geographical locations (city/country), and devices</p>
  </div>
  <a href="{{ route('hr.attendance.index') }}" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to Attendance Ledger
  </a>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4">
  <div class="card-body py-3">
    <form action="{{ route('hr.attendance.loginLogs') }}" method="GET" class="row g-3 align-items-center">
      <div class="col-md-3">
        <label class="form-label fs-8 fw-semibold text-muted mb-1">SEARCH IP / CITY / DEVICE</label>
        <div class="input-group">
          <span class="input-group-text"><i class="ti ti-search"></i></span>
          <input type="text" name="search" class="form-control" placeholder="Search IP, City, User..." value="{{ request('search') }}">
        </div>
      </div>
      @can('hr-attendance-regularize')
      <div class="col-md-3">
        <label class="form-label fs-8 fw-semibold text-muted mb-1">EMPLOYEE</label>
        <select name="user_id" class="form-select">
          <option value="">All Staff Members</option>
          @foreach($employees as $emp)
            <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>
              {{ $emp->name }}
            </option>
          @endforeach
        </select>
      </div>
      @endcan
      <div class="col-md-2">
        <label class="form-label fs-8 fw-semibold text-muted mb-1">DATE</label>
        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
      </div>
      <div class="col-md-2">
        <label class="form-label fs-8 fw-semibold text-muted mb-1">MONTH</label>
        <input type="month" name="month" class="form-control" value="{{ request('month') }}">
      </div>
      <div class="col-md-2 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-primary w-100" style="margin-top: 20px;"><i class="ti ti-filter me-1"></i> Filter</button>
        <a href="{{ route('hr.attendance.loginLogs') }}" class="btn btn-outline-secondary" style="margin-top: 20px;">Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- Login Logs Table -->
<div class="card shadow-sm">
  <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Login Session History & IP Geolocation Audit</h5>
    <span class="badge bg-label-primary font-monospace">{{ $logs->total() }} Total Logins Recorded</span>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Login Time</th>
          <th>Employee</th>
          <th>IP Address</th>
          <th>Location (City, Country)</th>
          <th>Device & OS</th>
          <th>Browser</th>
          <th class="text-center">Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($logs as $log)
          @php
            $profile = $log->user?->employeeProfile;
          @endphp
          <tr>
            <td>
              <div class="fw-semibold font-monospace text-heading">
                {{ $log->login_at ? $log->login_at->format('M d, Y') : '--' }}
              </div>
              <small class="text-muted font-monospace">
                {{ $log->login_at ? $log->login_at->format('h:i:s A') : '--' }}
              </small>
            </td>
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar avatar-xs me-2">
                  <span class="avatar-initial rounded-circle bg-label-primary fs-8 fw-bold">
                    {{ strtoupper(substr($log->user?->name ?? 'U', 0, 1)) }}
                  </span>
                </div>
                <div>
                  <div class="fw-medium text-heading">{{ $log->user?->name }}</div>
                  <small class="text-muted">{{ $profile?->designation ?? $profile?->department ?? 'Staff' }}</small>
                </div>
              </div>
            </td>
            <td>
              <span class="badge bg-label-dark font-monospace">
                <i class="ti ti-network me-1"></i>{{ $log->ip_address }}
              </span>
            </td>
            <td>
              <div class="d-flex align-items-center">
                <i class="ti ti-map-pin text-danger me-1 fs-5"></i>
                <div>
                  <div class="fw-medium text-body">
                    {{ $log->city ? $log->city . ', ' . $log->country : $log->location_summary }}
                  </div>
                  @if($log->region)
                    <small class="text-muted">{{ $log->region }}</small>
                  @endif
                </div>
              </div>
            </td>
            <td>
              <span class="badge bg-label-info font-monospace">
                <i class="ti ti-device-desktop me-1"></i>{{ $log->platform }} ({{ $log->device }})
              </span>
            </td>
            <td>
              <span class="text-body font-monospace">
                <i class="ti ti-browser me-1"></i>{{ $log->browser }}
              </span>
            </td>
            <td class="text-center">
              <span class="badge {{ $log->status === 'success' ? 'bg-label-success' : 'bg-label-danger' }}">
                {{ ucfirst($log->status) }}
              </span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center py-4 text-muted">
              No login logs recorded matching criteria.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($logs->hasPages())
    <div class="card-footer border-top py-3 d-flex justify-content-end">
      {{ $logs->links() }}
    </div>
  @endif
</div>
@endsection
