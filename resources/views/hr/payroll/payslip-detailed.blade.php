<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detailed Payslip (Slip 2) - {{ $payslip->user->name }} - {{ $payslip->cycle->cycle_code }}</title>
  <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/core.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/tabler-icons.css') }}" />
  <style>
    body {
      background-color: #f8f9fa;
      color: #333;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    .payslip-card {
      max-width: 950px;
      margin: 30px auto;
      background: #fff;
      padding: 40px;
      border-radius: 8px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    .header-logo {
      font-size: 26px;
      font-weight: 800;
      color: #7367f0;
      letter-spacing: -0.5px;
    }
    .watermark-badge {
      display: inline-block;
      padding: 4px 12px;
      background: #e0f2fe;
      color: #0369a1;
      font-weight: 700;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 1px;
      border-radius: 4px;
    }
    .audit-section {
      margin-bottom: 25px;
    }
    .audit-title {
      font-size: 13px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #374151;
      border-bottom: 2px solid #e5e7eb;
      padding-bottom: 6px;
      margin-bottom: 12px;
    }
    .table-audit th {
      background: #f9fafb;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #4b5563;
    }
    .net-salary-banner {
      background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
      color: #fff;
      border-radius: 8px;
      padding: 20px 25px;
    }
    @media print {
      body {
        background: #fff;
      }
      .payslip-card {
        box-shadow: none;
        margin: 0;
        padding: 0;
        max-width: 100%;
      }
      .no-print {
        display: none !important;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <!-- Print Controls -->
  <div class="text-center my-3 no-print">
    <button onclick="window.print()" class="btn btn-primary me-2">
      <i class="ti ti-printer me-1"></i> Print / Download Detailed PDF
    </button>
    <a href="{{ route('hr.payslip.normal', $payslip->id) }}" class="btn btn-outline-info me-2">
      View Slip 1 (Normal)
    </a>
    <a href="{{ route('hr.payroll.index') }}" class="btn btn-outline-secondary">
      Back to Payroll
    </a>
  </div>

  <div class="payslip-card">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
      <div>
        <div class="header-logo">DEVIXEL LOGISTICS</div>
        <p class="text-muted mb-0 small">BPO Freight Operations & Workforce Management</p>
        <p class="text-muted mb-0 small">Itemized Operational Audit & Compensation Ledger</p>
      </div>
      <div class="text-end">
        <span class="watermark-badge mb-2">Slip 2: Detailed Audit Trail</span>
        <h4 class="fw-bold mb-0 mt-2">{{ \Carbon\Carbon::parse($payslip->cycle->start_date)->format('F Y') }}</h4>
        <small class="text-muted">Cycle: {{ $payslip->cycle->cycle_code }}</small>
      </div>
    </div>

    <!-- Employee Information -->
    <div class="row g-3 mb-4 p-3 bg-light rounded">
      <div class="col-md-3">
        <small class="text-muted d-block">Employee Name</small>
        <strong class="text-dark">{{ $payslip->user->name }}</strong>
      </div>
      <div class="col-md-3">
        <small class="text-muted d-block">Employee Code</small>
        <strong class="font-monospace text-dark">{{ $payslip->user->employeeProfile?->employee_code ?? 'EMP-' . $payslip->user->id }}</strong>
      </div>
      <div class="col-md-3">
        <small class="text-muted d-block">Designation & Department</small>
        <strong class="text-dark">{{ $payslip->user->employeeProfile?->designation ?? 'Staff Member' }}</strong>
        @if($payslip->user->employeeProfile?->department)
          <span class="badge bg-label-primary ms-1 fs-8">{{ $payslip->user->employeeProfile->department }}</span>
        @endif
      </div>
      <div class="col-md-3">
        <small class="text-muted d-block">Biometric Thumb ID</small>
        <strong class="font-monospace text-dark">{{ $payslip->user->employeeProfile?->biometric_thumb_id ?? 'N/A' }}</strong>
      </div>
    </div>

    <!-- Attendance Summary Metrics -->
    <div class="audit-section">
      <div class="audit-title"><i class="ti ti-chart-bar me-1"></i> Attendance & Working Days Summary</div>
      <div class="row g-2 text-center">
        <div class="col-md-2 col-4">
          <div class="p-2 border rounded">
            <small class="text-muted d-block fs-8">Eligible Days</small>
            <strong class="fs-6 font-monospace">{{ $payslip->eligible_work_days }}</strong>
          </div>
        </div>
        <div class="col-md-2 col-4">
          <div class="p-2 border rounded">
            <small class="text-muted d-block fs-8">Present Days</small>
            <strong class="fs-6 font-monospace text-success">{{ $payslip->present_days }}</strong>
          </div>
        </div>
        <div class="col-md-2 col-4">
          <div class="p-2 border rounded">
            <small class="text-muted d-block fs-8">Paid Leaves</small>
            <strong class="fs-6 font-monospace text-info">{{ $payslip->paid_leave_days }}</strong>
          </div>
        </div>
        <div class="col-md-2 col-4">
          <div class="p-2 border rounded">
            <small class="text-muted d-block fs-8">Unpaid Leaves</small>
            <strong class="fs-6 font-monospace text-danger">{{ $payslip->unpaid_leave_days }}</strong>
          </div>
        </div>
        <div class="col-md-2 col-4">
          <div class="p-2 border rounded">
            <small class="text-muted d-block fs-8">Late Punches</small>
            <strong class="fs-6 font-monospace text-warning">{{ $payslip->late_count }}</strong>
          </div>
        </div>
        <div class="col-md-2 col-4">
          <div class="p-2 border rounded">
            <small class="text-muted d-block fs-8">Daily Rate (PKR)</small>
            <strong class="fs-6 font-monospace text-dark">Rs. {{ number_format(($payslip->user->employeeProfile?->base_salary ?? 0) / 30, 2) }}</strong>
          </div>
        </div>
      </div>
    </div>

    <!-- Itemized Earnings Audit -->
    <div class="audit-section">
      <div class="audit-title text-success"><i class="ti ti-circle-plus me-1"></i> Itemized Earnings Breakdown (PKR)</div>
      <table class="table table-audit table-bordered mb-0">
        <thead>
          <tr>
            <th style="width: 50px;">#</th>
            <th style="width: 130px;">Item Code</th>
            <th>Description & Operational Reference</th>
            <th style="width: 110px;">Date</th>
            <th class="text-end" style="width: 140px;">Amount (Rs.)</th>
          </tr>
        </thead>
        <tbody>
          @php $earningIndex = 1; @endphp
          @forelse($payslip->items->where('item_type', 'earning') as $item)
            <tr>
              <td>{{ $earningIndex++ }}</td>
              <td><span class="badge bg-label-success font-monospace">{{ $item->code }}</span></td>
              <td>{{ $item->description }}</td>
              <td>{{ $item->reference_date ? \Carbon\Carbon::parse($item->reference_date)->format('M d, Y') : '--' }}</td>
              <td class="text-end font-monospace text-success fw-semibold">Rs. {{ number_format($item->amount, 2) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-3 text-muted">No earning items recorded.</td>
            </tr>
          @endforelse
          <tr class="table-light fw-bold">
            <td colspan="4" class="text-end">Total Gross Earnings:</td>
            <td class="text-end font-monospace text-success">Rs. {{ number_format($payslip->total_earnings, 2) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Itemized Deductions Audit -->
    <div class="audit-section">
      <div class="audit-title text-danger"><i class="ti ti-circle-minus me-1"></i> Itemized Deductions & Penalties Breakdown (PKR)</div>
      <table class="table table-audit table-bordered mb-0">
        <thead>
          <tr>
            <th style="width: 50px;">#</th>
            <th style="width: 130px;">Item Code</th>
            <th>Description & Penalty Audit</th>
            <th style="width: 110px;">Date</th>
            <th class="text-end" style="width: 140px;">Amount (Rs.)</th>
          </tr>
        </thead>
        <tbody>
          @php $deductionIndex = 1; @endphp
          @forelse($payslip->items->where('item_type', 'deduction') as $item)
            <tr>
              <td>{{ $deductionIndex++ }}</td>
              <td><span class="badge bg-label-danger font-monospace">{{ $item->code }}</span></td>
              <td>{{ $item->description }}</td>
              <td>{{ $item->reference_date ? \Carbon\Carbon::parse($item->reference_date)->format('M d, Y') : '--' }}</td>
              <td class="text-end font-monospace text-danger fw-semibold">-Rs. {{ number_format($item->amount, 2) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-3 text-muted">No deductions recorded (Clean record).</td>
            </tr>
          @endforelse
          <tr class="table-light fw-bold">
            <td colspan="4" class="text-end">Total Deductions:</td>
            <td class="text-end font-monospace text-danger">-Rs. {{ number_format($payslip->total_deductions, 2) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Net Salary Highlight Banner -->
    <div class="net-salary-banner d-flex justify-content-between align-items-center mb-4">
      <div>
        <h5 class="mb-0 text-white fw-bold">NET PAYABLE COMPENSATION</h5>
        <small class="text-white-50">Audited Reconciliation: Gross Rs. {{ number_format($payslip->total_earnings, 2) }} - Deductions Rs. {{ number_format($payslip->total_deductions, 2) }}</small>
      </div>
      <div class="text-end">
        <h2 class="mb-0 text-white fw-bolder font-monospace">Rs. {{ number_format($payslip->net_salary, 2) }}</h2>
        <small class="text-white-50">Disbursement Status: {{ strtoupper($payslip->payment_status) }}</small>
      </div>
    </div>

    <!-- Audit Attestation -->
    <div class="p-3 bg-light border rounded mb-5">
      <small class="text-muted d-block">
        <strong>Audit Attestation:</strong> This itemized compensation ledger is automatically generated from the Devixel Biometric Workforce Engine, carrier dispatch logs, and company HR policy records. All sales lead bonuses, dispatcher volume milestones, and late arrival deductions are calculated strictly according to approved operational rules.
      </small>
    </div>

    <!-- Signatures -->
    <div class="row pt-4 border-top">
      <div class="col-4 text-center">
        <div style="border-bottom: 1px dashed #999; height: 35px; margin-bottom: 8px;"></div>
        <small class="text-muted fw-semibold">Audited by HR Officer</small>
      </div>
      <div class="col-4 text-center">
        <div style="border-bottom: 1px dashed #999; height: 35px; margin-bottom: 8px;"></div>
        <small class="text-muted fw-semibold">Operations Supervisor</small>
      </div>
      <div class="col-4 text-center">
        <div style="border-bottom: 1px dashed #999; height: 35px; margin-bottom: 8px;"></div>
        <small class="text-muted fw-semibold">Acknowledged by Employee</small>
      </div>
    </div>
  </div>
</div>

</body>
</html>
