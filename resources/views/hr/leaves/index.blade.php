@extends('layouts.master')

@section('title', 'Leaves & Holidays Calendar')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Leaves, Holidays & Roster Swaps</h4>
    <p class="text-muted mb-0">Manage leave requests, public holidays, and working weekends</p>
  </div>
  <div class="d-flex gap-2">
    @can('hr-leaves-approve')
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#adminApplyLeaveModal">
        <i class="ti ti-user-plus me-1"></i> Add Employee Leave
      </button>
    @endcan
    @can('hr-leaves-apply')
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
        <i class="ti ti-calendar-plus me-1"></i> Apply for Leave
      </button>
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

<!-- My Quotas Cards -->
@if(count($myQuotas) > 0)
<div class="row mb-4">
  @foreach($myQuotas as $quota)
    <div class="col-md-3 col-sm-6 mb-3">
      <div class="card shadow-sm border-start border-4 border-primary h-100">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <small class="text-muted text-uppercase fw-semibold">{{ $quota->leaveType->name }}</small>
              <h4 class="mb-0 fw-bold mt-1">{{ $quota->remaining_days }} <span class="fs-7 fw-normal text-muted">/ {{ $quota->allocated_days }} left</span></h4>
            </div>
            <div class="avatar bg-label-primary rounded">
              <i class="ti ti-calendar-check fs-3"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endforeach
  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card shadow-sm border-start border-4 border-info h-100">
      <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <small class="text-muted text-uppercase fw-semibold">Monthly Policy Cap</small>
            <h4 class="mb-0 fw-bold mt-1">Max {{ $settings->max_paid_leaves_per_month }} <span class="fs-7 fw-normal text-muted">paid / mo</span></h4>
          </div>
          <div class="avatar bg-label-info rounded">
            <i class="ti ti-info-circle fs-3"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

<!-- Tabs: Leave Requests, Public Holidays, Roster & Working Weekend Exceptions -->
<div class="nav-align-top mb-4">
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
      <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#tab-leaves">
        <i class="ti ti-file-text me-1"></i> Leave Applications
      </button>
    </li>
    <li class="nav-item">
      <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-holidays">
        <i class="ti ti-calendar-star me-1"></i> Public Holidays ({{ \Carbon\Carbon::now()->year }})
      </button>
    </li>
    <li class="nav-item">
      <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-roster">
        <i class="ti ti-calendar-stats me-1"></i> Working Weekends & Compensatory Off
      </button>
    </li>
  </ul>

  <div class="tab-content bg-transparent p-0 pt-3">
    <!-- Tab 1: Leave Applications -->
    <div class="tab-pane fade show active" id="tab-leaves" role="tabpanel">
      <div class="card shadow-sm">
        <div class="table-responsive text-nowrap">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Employee</th>
                <th>Leave Type</th>
                <th>Dates</th>
                <th>Days Count</th>
                <th>Nature</th>
                <th>Reason</th>
                <th class="text-center">Status</th>
                @can('hr-leaves-approve')
                <th class="text-center" style="width: 140px;">Action</th>
                @endcan
              </tr>
            </thead>
            <tbody>
              @forelse($leaves as $leave)
                @php
                  $statusClasses = [
                    'pending' => 'bg-label-warning',
                    'approved' => 'bg-label-success',
                    'rejected' => 'bg-label-danger',
                  ];
                @endphp
                <tr>
                  <td>
                    <div class="fw-semibold text-heading">{{ $leave->user->name }}</div>
                    <small class="text-muted">{{ $leave->user->employeeProfile?->designation ?? 'Staff' }}</small>
                  </td>
                  <td>
                    <span class="badge bg-label-info">{{ $leave->leaveType->name }}</span>
                  </td>
                  <td>
                    <span class="font-monospace">
                      {{ \Carbon\Carbon::parse($leave->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}
                    </span>
                  </td>
                  <td>
                    <strong>{{ $leave->days_count }}</strong> day(s)
                  </td>
                  <td>
                    @if($leave->is_paid)
                      <span class="badge bg-label-success">Paid Leave</span>
                    @else
                      <span class="badge bg-label-secondary">Unpaid Leave</span>
                    @endif
                  </td>
                  <td>
                    <small class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $leave->reason }}">
                      {{ $leave->reason ?? '--' }}
                    </small>
                  </td>
                  <td class="text-center">
                    <span class="badge {{ $statusClasses[$leave->status] ?? 'bg-label-secondary' }}">
                      {{ ucfirst($leave->status) }}
                    </span>
                  </td>
                  @can('hr-leaves-approve')
                  <td class="text-center">
                    @if($leave->status === 'pending')
                      <form action="{{ route('hr.leaves.approve', $leave->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-icon btn-success rounded-pill" title="Approve">
                          <i class="ti ti-check"></i>
                        </button>
                      </form>
                      <form action="{{ route('hr.leaves.reject', $leave->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-icon btn-danger rounded-pill" title="Reject">
                          <i class="ti ti-x"></i>
                        </button>
                      </form>
                    @else
                      <small class="text-muted">By {{ $leave->approver?->name ?? 'Admin' }}</small>
                    @endif
                  </td>
                  @endcan
                </tr>
              @empty
                <tr>
                  <td colspan="{{ auth()->user()->can('hr-leaves-approve') ? 8 : 7 }}" class="text-center py-4 text-muted">
                    No leave requests found.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if($leaves->hasPages())
          <div class="card-footer border-top py-3 d-flex justify-content-end">
            {{ $leaves->links() }}
          </div>
        @endif
      </div>
    </div>

    <!-- Tab 2: Public Holidays -->
    <div class="tab-pane fade" id="tab-holidays" role="tabpanel">
      <div class="card shadow-sm">
        <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Public & Gazetted Holidays</h5>
          @can('hr-holidays-manage')
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
              <i class="ti ti-plus me-1"></i> Add Holiday
            </button>
          @endcan
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Holiday Name</th>
                <th>Date</th>
                <th>Day</th>
                <th>Description</th>
                @can('hr-holidays-manage')
                <th class="text-center" style="width: 80px;">Action</th>
                @endcan
              </tr>
            </thead>
            <tbody>
              @forelse($holidays as $holiday)
                <tr>
                  <td class="fw-semibold text-heading">{{ $holiday->name }}</td>
                  <td class="font-monospace">{{ \Carbon\Carbon::parse($holiday->holiday_date)->format('M d, Y') }}</td>
                  <td>{{ \Carbon\Carbon::parse($holiday->holiday_date)->format('l') }}</td>
                  <td>{{ $holiday->description ?? '--' }}</td>
                  @can('hr-holidays-manage')
                  <td class="text-center">
                    <form action="{{ route('hr.holidays.destroy', $holiday->id) }}" method="POST" onsubmit="return confirm('Remove this holiday?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-icon btn-text-danger rounded-pill">
                        <i class="ti ti-trash"></i>
                      </button>
                    </form>
                  </td>
                  @endcan
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-4 text-muted">No public holidays registered for this year.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Tab 3: Roster & Working Weekend Exceptions -->
    <div class="tab-pane fade" id="tab-roster" role="tabpanel">
      <div class="card shadow-sm">
        <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
          <div>
            <h5 class="card-title mb-0">Working Weekend & Compensatory Off Swaps</h5>
            <small class="text-muted">e.g. Schedule Saturday as Working Day in exchange for Friday Holiday</small>
          </div>
          @can('hr-holidays-manage')
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addRosterExceptionModal">
              <i class="ti ti-plus me-1"></i> Add Calendar Exception
            </button>
          @endcan
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Date</th>
                <th>Exception Type</th>
                <th>Assigned Shift</th>
                <th>Applies To</th>
                <th>Notes / Reason</th>
                @can('hr-holidays-manage')
                <th class="text-center" style="width: 80px;">Action</th>
                @endcan
              </tr>
            </thead>
            <tbody>
              @forelse($rosterExceptions as $exc)
                @php
                  $typeBadges = [
                    'working_day' => 'bg-label-primary',
                    'compensatory_off' => 'bg-label-success',
                    'holiday' => 'bg-label-warning',
                  ];
                @endphp
                <tr>
                  <td>
                    <span class="font-monospace fw-semibold">
                      {{ \Carbon\Carbon::parse($exc->exception_date)->format('M d, Y') }}
                    </span>
                    <div class="text-muted fs-8">{{ \Carbon\Carbon::parse($exc->exception_date)->format('l') }}</div>
                  </td>
                  <td>
                    <span class="badge {{ $typeBadges[$exc->type] ?? 'bg-label-secondary' }}">
                      {{ ucfirst(str_replace('_', ' ', $exc->type)) }}
                    </span>
                  </td>
                  <td>{{ $exc->shift?->name ?? 'Default Shift' }}</td>
                  <td>{{ $exc->user?->name ?? 'All Staff Members' }}</td>
                  <td>{{ $exc->notes ?? '--' }}</td>
                  @can('hr-holidays-manage')
                  <td class="text-center">
                    <form action="{{ route('hr.roster-exceptions.destroy', $exc->id) }}" method="POST" onsubmit="return confirm('Remove this exception?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-icon btn-text-danger rounded-pill">
                        <i class="ti ti-trash"></i>
                      </button>
                    </form>
                  </td>
                  @endcan
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center py-4 text-muted">No compensatory off or working weekend exceptions scheduled.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Apply Leave Modal -->
<div class="modal fade" id="applyLeaveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('hr.leaves.apply') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Apply for Leave</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info py-2 fs-8 mb-3">
            <i class="ti ti-info-circle me-1"></i> Note: You are allowed up to <strong>{{ $settings->max_paid_leaves_per_month }} paid leaves</strong> in a single calendar month. Any leave exceeding this limit converts to salary deduction.
          </div>
          <div class="mb-3">
            <label class="form-label">Leave Category</label>
            <select name="leave_type_id" class="form-select" required>
              @foreach($leaveTypes as $lt)
                <option value="{{ $lt->id }}">{{ $lt->name }} ({{ $lt->is_paid ? 'Paid' : 'Unpaid' }})</option>
              @endforeach
            </select>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label">Start Date</label>
              <input type="date" name="start_date" class="form-control" value="{{ \Carbon\Carbon::tomorrow()->toDateString() }}" required>
            </div>
            <div class="col-6">
              <label class="form-label">End Date</label>
              <input type="date" name="end_date" class="form-control" value="{{ \Carbon\Carbon::tomorrow()->toDateString() }}" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Reason</label>
            <textarea name="reason" class="form-control" rows="3" placeholder="State reason for absence..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Application</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Admin Add Employee Leave Modal -->
<div class="modal fade" id="adminApplyLeaveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('hr.leaves.adminApply') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="ti ti-user-plus me-1 text-primary"></i> Add Employee Leave</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Select Employee <span class="text-danger">*</span></label>
            <select name="user_id" class="form-select" required>
              <option value="">-- Choose Employee --</option>
              @foreach($employees as $emp)
                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->email }})</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Leave Category <span class="text-danger">*</span></label>
            <select name="leave_type_id" class="form-select" required>
              @foreach($leaveTypes as $lt)
                <option value="{{ $lt->id }}">{{ $lt->name }} ({{ $lt->is_paid ? 'Paid' : 'Unpaid' }})</option>
              @endforeach
            </select>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
              <input type="date" name="start_date" class="form-control" value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
              <input type="date" name="end_date" class="form-control" value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Reason</label>
            <textarea name="reason" class="form-control" rows="2" placeholder="Reason or administrative note..."></textarea>
          </div>
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="auto_approve" value="1" id="autoApproveSwitch" checked>
            <label class="form-check-label fw-semibold" for="autoApproveSwitch">Approve immediately (mark as Approved)</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Save Leave</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Holiday Modal -->
<div class="modal fade" id="addHolidayModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('hr.holidays.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Add Public / Gazetted Holiday</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Holiday Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Independence Day" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Holiday Date</label>
            <input type="date" name="holiday_date" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description (Optional)</label>
            <input type="text" name="description" class="form-control" placeholder="Optional notes...">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Holiday</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Roster Exception Modal -->
<div class="modal fade" id="addRosterExceptionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('hr.roster-exceptions.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Add Working Weekend / Compensatory Off</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Exception Date</label>
            <input type="date" name="exception_date" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Exception Type</label>
            <select name="type" class="form-select" required>
              <option value="working_day">Working Day (Working Weekend e.g. Saturday/Sunday active)</option>
              <option value="compensatory_off">Compensatory Off (e.g. Friday Day Off with Full Pay)</option>
              <option value="holiday">Special Holiday</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Assigned Shift</label>
            <select name="shift_id" class="form-select">
              <option value="">-- Shift Default --</option>
              @foreach($shifts as $sh)
                <option value="{{ $sh->id }}">{{ $sh->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Applies To Specific Employee (Leave blank for All Staff)</label>
            <select name="user_id" class="form-select">
              <option value="">-- All Staff Members --</option>
              @foreach($employees as $emp)
                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Notes</label>
            <input type="text" name="notes" class="form-control" placeholder="e.g. Working Saturday in exchange for Friday festival off">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Calendar Exception</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
