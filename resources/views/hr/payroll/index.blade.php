@extends('layouts.master')

@section('title', 'Payroll & Payslips')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Payroll & Compensation Engine</h4>
    <p class="text-muted mb-0">Generate monthly payroll, calculate sales lead bonuses, dispatcher targets, late deductions, and export bank sheets</p>
  </div>
  @can('hr-payroll-generate')
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generatePayrollModal">
      <i class="ti ti-calculator me-1"></i> Run Payroll Batch
    </button>
  @endcan
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

<!-- Payroll Cycle Selection & Metrics -->
<div class="card shadow-sm mb-4">
  <div class="card-body py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <form action="{{ route('hr.payroll.index') }}" method="GET" class="d-flex align-items-center gap-2">
        <label class="fw-semibold text-muted text-nowrap">Payroll Cycle:</label>
        <select name="cycle_id" class="form-select w-auto" onchange="this.form.submit()">
          @forelse($cycles as $c)
            <option value="{{ $c->id }}" {{ $selectedCycle?->id == $c->id ? 'selected' : '' }}>
              {{ $c->cycle_code }} ({{ ucfirst($c->status) }})
            </option>
          @empty
            <option value="">No payroll cycles yet</option>
          @endforelse
        </select>
      </form>

      @if($selectedCycle)
        <div class="d-flex align-items-center gap-2">
          @can('hr-payroll-export')
            <a href="{{ route('hr.payroll.exportExcel', $selectedCycle->id) }}" class="btn btn-outline-success">
              <i class="ti ti-file-spreadsheet me-1"></i> Export Bank Sheet (Excel)
            </a>
          @endcan

          @can('hr-payroll-lock')
            @if($selectedCycle->status !== 'locked')
              <form action="{{ route('hr.payroll.lock', $selectedCycle->id) }}" method="POST" onsubmit="return confirm('Lock this payroll cycle? This will lock all amounts and amortize scheduled loan installments.');">
                @csrf
                <button type="submit" class="btn btn-warning">
                  <i class="ti ti-lock me-1"></i> Lock & Approve Cycle
                </button>
              </form>
            @else
              <span class="badge bg-label-success fs-7 px-3 py-2">
                <i class="ti ti-lock-check me-1"></i> Cycle Locked & Approved
              </span>
            @endif
          @endcan
        </div>
      @endif
    </div>
  </div>
</div>

@if($selectedCycle)
<!-- Cycle Summary Cards -->
<div class="row mb-4">
  <div class="col-md-4 mb-3">
    <div class="card shadow-sm border-start border-4 border-primary h-100">
      <div class="card-body py-3">
        <small class="text-muted text-uppercase fw-semibold">Total Gross Earnings</small>
        <h3 class="mb-0 fw-bold text-primary mt-1">Rs. {{ number_format($selectedCycle->total_gross, 2) }}</h3>
        <small class="text-muted">Base salary + lead bonuses + targets</small>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-3">
    <div class="card shadow-sm border-start border-4 border-success h-100">
      <div class="card-body py-3">
        <small class="text-muted text-uppercase fw-semibold">Net Salary Disbursement</small>
        <h3 class="mb-0 fw-bold text-success mt-1">Rs. {{ number_format($selectedCycle->total_net, 2) }}</h3>
        <small class="text-muted">Total PKR payable to employees</small>
      </div>
    </div>
  </div>
  <div class="col-md-4 mb-3">
    <div class="card shadow-sm border-start border-4 border-info h-100">
      <div class="card-body py-3">
        <small class="text-muted text-uppercase fw-semibold">Payroll Status</small>
        <h3 class="mb-0 fw-bold text-info mt-1">{{ ucfirst($selectedCycle->status) }}</h3>
        <small class="text-muted">Processed by {{ $selectedCycle->processor?->name ?? 'HR Admin' }}</small>
      </div>
    </div>
  </div>
</div>

<!-- Payslips Table -->
<div class="card shadow-sm">
  <div class="card-header border-bottom py-3">
    <h5 class="card-title mb-0">Employee Payslips for Cycle: {{ $selectedCycle->cycle_code }}</h5>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Employee</th>
          <th>Base Salary</th>
          <th>Lead Bonuses</th>
          <th>Dispatcher Targets</th>
          <th>Deductions</th>
          <th>Net Payable (PKR)</th>
          <th class="text-center">Status</th>
          <th class="text-center" style="width: 220px;">Payslips</th>
        </tr>
      </thead>
      <tbody>
        @forelse($payslips as $ps)
          @php
            $profile = $ps->user?->employeeProfile;
          @endphp
          <tr>
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-2">
                  <span class="avatar-initial rounded-circle bg-label-primary fs-7 fw-bold">
                    {{ strtoupper(substr($ps->user?->name ?? 'U', 0, 1)) }}
                  </span>
                </div>
                <div>
                  <h6 class="mb-0 fw-semibold text-heading">{{ $ps->user?->name }}</h6>
                  <small class="text-muted">{{ $profile?->designation ?? $profile?->department ?? 'Staff' }}</small>
                </div>
              </div>
            </td>
            <td>
              <span class="font-monospace">Rs. {{ number_format($ps->base_salary_earned, 2) }}</span>
            </td>
            <td>
              @if($ps->sales_lead_bonus_earned > 0)
                <span class="badge bg-label-success font-monospace">
                  +Rs. {{ number_format($ps->sales_lead_bonus_earned, 2) }}
                </span>
              @else
                <span class="text-muted">Rs. 0.00</span>
              @endif
            </td>
            <td>
              @php
                $targetPlusComm = $ps->dispatcher_target_bonus_earned + $ps->dispatcher_load_commission_earned;
              @endphp
              @if($targetPlusComm > 0)
                <span class="badge bg-label-primary font-monospace">
                  +Rs. {{ number_format($targetPlusComm, 2) }}
                </span>
              @else
                <span class="text-muted">Rs. 0.00</span>
              @endif
            </td>
            <td>
              @if($ps->total_deductions > 0)
                <span class="badge bg-label-danger font-monospace">
                  -Rs. {{ number_format($ps->total_deductions, 2) }}
                </span>
                <div class="fs-8 text-muted">Late: -Rs. {{ number_format($ps->late_deductions_total, 2) }}</div>
              @else
                <span class="text-muted">Rs. 0.00</span>
              @endif
            </td>
            <td>
              <span class="fw-bold fs-6 text-success font-monospace">
                Rs. {{ number_format($ps->net_salary, 2) }}
              </span>
            </td>
            <td class="text-center">
              <span class="badge {{ $ps->payment_status == 'paid' ? 'bg-label-success' : 'bg-label-warning' }}">
                {{ ucfirst($ps->payment_status) }}
              </span>
            </td>
            <td class="text-center">
              <div class="btn-group">
                <!-- Slip 1: Normal Payslip with commissions -->
                <a href="{{ route('hr.payslip.normal', $ps->id) }}" class="btn btn-sm btn-outline-primary" target="_blank" title="Slip 1: Normal Payslip with Commissions">
                  <i class="ti ti-file-invoice me-1"></i> Slip 1
                </a>
                <!-- Slip 2: Detailed Payslip with itemized logs -->
                <a href="{{ route('hr.payslip.detailed', $ps->id) }}" class="btn btn-sm btn-outline-info" target="_blank" title="Slip 2: Detailed Operational Audit">
                  <i class="ti ti-file-analytics me-1"></i> Slip 2
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center py-4 text-muted">No payslips found in this cycle.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($payslips->hasPages())
    <div class="card-footer border-top py-3 d-flex justify-content-end">
      {{ $payslips->links() }}
    </div>
  @endif
</div>
@endif

<!-- Generate Payroll Modal -->
<div class="modal fade" id="generatePayrollModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('hr.payroll.generate') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Run Monthly Payroll Batch</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-medium">Payroll Month / Cycle (YYYY-MM)</label>
            <input type="month" name="cycle_code" class="form-control font-monospace" value="{{ \Carbon\Carbon::now()->format('Y-m') }}" required>
            <small class="text-muted">Calculates base salary, 25% late deductions, qualifying lead bonuses, and dynamic dispatcher milestone targets.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Calculate Payroll Batch</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
