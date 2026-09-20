@extends('layouts.master')

@section('title', 'Employee Profiles & Salaries')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">HR Employee Directory</h4>
    <p class="text-muted mb-0">Manage employee salaries, biometric thumb IDs, work shifts, and dynamic dispatcher milestone targets</p>
  </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="ti ti-check me-2"></i>{{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Filters & Search -->
<div class="card shadow-sm mb-4">
  <div class="card-body py-3">
    <form action="{{ route('hr.employees.index') }}" method="GET" class="row g-3 align-items-center">
      <div class="col-md-5">
        <div class="input-group">
          <span class="input-group-text"><i class="ti ti-search"></i></span>
          <input type="text" name="search" class="form-control" placeholder="Search by name, email, code, designation, or thumb ID..." value="{{ request('search') }}">
        </div>
      </div>
      <div class="col-md-3">
        <select name="status" class="form-select">
          <option value="">All Employment Statuses</option>
          <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
          <option value="probation" {{ request('status') == 'probation' ? 'selected' : '' }}>Probation</option>
          <option value="resigned" {{ request('status') == 'resigned' ? 'selected' : '' }}>Resigned</option>
          <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
        </select>
      </div>
      <div class="col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="ti ti-filter me-1"></i> Filter</button>
        <a href="{{ route('hr.employees.index') }}" class="btn btn-outline-secondary">Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- Employee List -->
<div class="card shadow-sm">
  <div class="table-responsive text-nowrap">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Emp Code</th>
          <th>Employee</th>
          <th>Designation / Dept</th>
          <th>Base Salary (PKR)</th>
          <th>Target Bonus (Dispatcher)</th>
          <th>Biometric ID</th>
          <th class="text-center">Status</th>
          <th class="text-center" style="width: 100px;">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($employees as $user)
          @php
            $profile = $user->employeeProfile;
          @endphp
          <tr>
            <td>
              <span class="badge bg-label-dark font-monospace">
                {{ $profile?->employee_code ?? 'EMP-' . str_pad($user->id, 4, '0', STR_PAD_LEFT) }}
              </span>
            </td>
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-2">
                  @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle" style="width: 38px; height: 38px; object-fit: cover;">
                  @else
                    <span class="avatar-initial rounded-circle bg-label-primary fw-bold">
                      {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                  @endif
                </div>
                <div>
                  <h6 class="mb-0 text-heading fw-semibold">{{ $user->name }}</h6>
                  <small class="text-muted">{{ $user->email }}</small>
                </div>
              </div>
            </td>
            <td>
              <div class="fw-medium text-body">{{ $profile?->designation ?? 'Staff' }}</div>
              @if($profile?->department)
                <span class="badge bg-label-primary fs-8 me-1">{{ $profile->department }}</span>
              @endif
              <small class="text-muted d-block mt-1">
                <i class="ti ti-clock-hour-4 me-1"></i>{{ $profile?->shift?->name ?? 'Default Shift' }}
              </small>
            </td>
            <td>
              <span class="fw-bold text-success font-monospace">
                Rs. {{ number_format($profile?->base_salary ?? 0, 2) }}
              </span>
              <div class="text-muted fs-8">Rs. {{ number_format(($profile?->base_salary ?? 0) / 30, 2) }} / day</div>
            </td>
            <td>
              @if($profile && $profile->monthly_load_target_amount > 0)
                <div class="fw-medium text-primary">
                  Target: ${{ number_format($profile->monthly_load_target_amount, 2) }} USD
                </div>
                <small class="text-muted">
                  @if($profile->target_bonus_type == 'fixed')
                    Bonus: Rs. {{ number_format($profile->target_bonus_fixed, 2) }}
                  @elseif($profile->target_bonus_type == 'percentage')
                    Bonus: {{ $profile->target_bonus_percentage }}%
                  @else
                    Bonus: Rs. {{ number_format($profile->target_bonus_fixed, 2) }} + {{ $profile->target_bonus_percentage }}%
                  @endif
                </small>
              @else
                <span class="text-muted fs-8">Not configured</span>
              @endif
            </td>
            <td>
              @if($profile?->biometric_thumb_id)
                <span class="badge bg-label-info font-monospace">
                  <i class="ti ti-fingerprint me-1"></i>{{ $profile->biometric_thumb_id }}
                </span>
              @else
                <span class="badge bg-label-secondary fs-8">No Thumb ID</span>
              @endif
            </td>
            <td class="text-center">
              @php
                $st = $profile?->status ?? 'active';
                $statusBadges = [
                  'active' => 'bg-label-success',
                  'probation' => 'bg-label-warning',
                  'resigned' => 'bg-label-secondary',
                  'terminated' => 'bg-label-danger',
                ];
              @endphp
              <span class="badge {{ $statusBadges[$st] ?? 'bg-label-secondary' }}">
                {{ ucfirst($st) }}
              </span>
            </td>
            <td class="text-center">
              <a href="{{ route('hr.employees.edit', $user->id) }}" class="btn btn-sm btn-primary">
                <i class="ti ti-edit me-1"></i> Edit
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center py-4 text-muted">No employees found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($employees->hasPages())
    <div class="card-footer border-top py-3 d-flex justify-content-end">
      {{ $employees->links() }}
    </div>
  @endif
</div>
@endsection
