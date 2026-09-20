@extends('layouts.master')

@section('title', 'Staff Loans & Advances')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Staff Loans & Salary Advances</h4>
    <p class="text-muted mb-0">Manage loan approvals, monthly installment schedules, and automatic payroll recovery</p>
  </div>
  @can('hr-loans-manage')
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createLoanModal">
      <i class="ti ti-cash me-1"></i> Issue Loan / Advance
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

<!-- Loans Table -->
<div class="card shadow-sm">
  <div class="table-responsive text-nowrap">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Employee</th>
          <th>Principal Amount</th>
          <th>Monthly Deduction</th>
          <th>Remaining Balance</th>
          <th>Purpose</th>
          <th class="text-center">Status</th>
          <th class="text-center" style="width: 160px;">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($loans as $loan)
          @php
            $statusClasses = [
              'pending' => 'bg-label-warning',
              'active' => 'bg-label-primary',
              'repaid' => 'bg-label-success',
              'cancelled' => 'bg-label-secondary',
            ];
          @endphp
          <tr>
            <td>
              <div class="fw-semibold text-heading">{{ $loan->user->name }}</div>
              <small class="text-muted">{{ $loan->user->employeeProfile?->designation ?? 'Staff' }}</small>
            </td>
            <td>
              <span class="font-monospace fw-bold">Rs. {{ number_format($loan->principal_amount, 2) }}</span>
            </td>
            <td>
              <span class="font-monospace text-danger fw-semibold">-Rs. {{ number_format($loan->monthly_installment, 2) }} / mo</span>
            </td>
            <td>
              <span class="font-monospace fw-bold text-dark">Rs. {{ number_format($loan->remaining_balance, 2) }}</span>
            </td>
            <td>
              <span class="text-truncate d-inline-block" style="max-width: 200px;">{{ $loan->purpose ?? '--' }}</span>
            </td>
            <td class="text-center">
              <span class="badge {{ $statusClasses[$loan->status] ?? 'bg-label-secondary' }}">
                {{ ucfirst($loan->status) }}
              </span>
            </td>
            <td class="text-center">
              <div class="btn-group">
                <!-- View Installments Modal Button -->
                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#installmentsModal{{ $loan->id }}">
                  <i class="ti ti-list me-1"></i> Schedule
                </button>

                @can('hr-loans-manage')
                  @if($loan->status === 'pending')
                    <form action="{{ route('hr.loans.approve', $loan->id) }}" method="POST" class="d-inline">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-success" title="Approve Loan">
                        <i class="ti ti-check"></i>
                      </button>
                    </form>
                    <form action="{{ route('hr.loans.cancel', $loan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this loan?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger" title="Cancel">
                        <i class="ti ti-x"></i>
                      </button>
                    </form>
                  @endif
                @endcan
              </div>

              <!-- Installments Modal -->
              <div class="modal fade" id="installmentsModal{{ $loan->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered text-start">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Installment Schedule: {{ $loan->user->name }}</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                      <div class="p-3 bg-light border-bottom d-flex justify-content-between">
                        <div>Principal: <strong>Rs. {{ number_format($loan->principal_amount, 2) }}</strong></div>
                        <div>Remaining: <strong class="text-danger">Rs. {{ number_format($loan->remaining_balance, 2) }}</strong></div>
                      </div>
                      <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                          <thead class="table-light">
                            <tr>
                              <th>#</th>
                              <th>Due Date</th>
                              <th>Amount</th>
                              <th>Status</th>
                              <th>Paid Date</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse($loan->installments as $inst)
                              <tr>
                                <td>{{ $inst->installment_number }}</td>
                                <td class="font-monospace">{{ \Carbon\Carbon::parse($inst->due_date)->format('M d, Y') }}</td>
                                <td class="font-monospace font-weight-bold">Rs. {{ number_format($inst->amount, 2) }}</td>
                                <td>
                                  <span class="badge {{ $inst->status === 'deducted' ? 'bg-label-success' : 'bg-label-warning' }}">
                                    {{ ucfirst($inst->status) }}
                                  </span>
                                </td>
                                <td>{{ $inst->paid_date ? \Carbon\Carbon::parse($inst->paid_date)->format('M d, Y') : '--' }}</td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="5" class="text-center py-3 text-muted">No installments scheduled yet (loan is pending approval).</td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center py-4 text-muted">No staff loans or advances recorded.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($loans->hasPages())
    <div class="card-footer border-top py-3 d-flex justify-content-end">
      {{ $loans->links() }}
    </div>
  @endif
</div>

<!-- Create Loan Modal -->
<div class="modal fade" id="createLoanModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('hr.loans.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Issue Staff Loan / Salary Advance</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Employee</label>
            <select name="user_id" class="form-select" required>
              <option value="">-- Select Employee --</option>
              @foreach($employees as $emp)
                <option value="{{ $emp->id }}">
                  {{ $emp->name }} (Base: Rs. {{ number_format($emp->employeeProfile?->base_salary ?? 0, 2) }})
                </option>
              @endforeach
            </select>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label">Principal Amount (Rs. PKR)</label>
              <div class="input-group">
                <span class="input-group-text font-monospace">Rs.</span>
                <input type="number" step="0.01" name="principal_amount" class="form-control font-monospace" placeholder="e.g. 50000" min="1" required>
              </div>
            </div>
            <div class="col-6">
              <label class="form-label">Monthly Installment (Rs. PKR)</label>
              <div class="input-group">
                <span class="input-group-text font-monospace">Rs.</span>
                <input type="number" step="0.01" name="monthly_installment" class="form-control font-monospace" placeholder="e.g. 10000" min="1" required>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Purpose / Notes</label>
            <textarea name="purpose" class="form-control" rows="2" placeholder="e.g. Medical emergency advance, educational loan"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create Loan Request</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
