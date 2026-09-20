<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payslip (Slip 1) - {{ $payslip->user->name }} - {{ $payslip->cycle->cycle_code }}</title>
  <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/core.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/tabler-icons.css') }}" />
  <style>
    body {
      background-color: #f8f9fa;
      color: #333;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    .payslip-card {
      max-width: 850px;
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
      background: #eef2ff;
      color: #4f46e5;
      font-weight: 700;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 1px;
      border-radius: 4px;
    }
    .section-title {
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #6b7280;
      font-weight: 700;
      border-bottom: 2px solid #f3f4f6;
      padding-bottom: 8px;
      margin-bottom: 15px;
    }
    .table-slip th {
      background: #f9fafb;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #4b5563;
    }
    .net-salary-banner {
      background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
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
      <i class="ti ti-printer me-1"></i> Print / Download PDF
    </button>
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
        <p class="text-muted mb-0 small">Official Salary Compensation Statement</p>
      </div>
      <div class="text-end">
        <span class="watermark-badge mb-2">Slip 1: Salary & Commissions</span>
        <h4 class="fw-bold mb-0 mt-2">{{ \Carbon\Carbon::parse($payslip->cycle->start_date)->format('F Y') }}</h4>
        <small class="text-muted">Cycle: {{ $payslip->cycle->cycle_code }}</small>
      </div>
    </div>

    <!-- Employee & Disbursement Info -->
    <div class="row g-4 mb-4">
      <div class="col-6">
        <div class="section-title">Employee Details</div>
        <table class="table table-sm table-borderless mb-0">
          <tr>
            <td class="text-muted ps-0" style="width: 130px;">Name:</td>
            <td class="fw-bold text-dark">{{ $payslip->user->name }}</td>
          </tr>
          <tr>
            <td class="text-muted ps-0">Employee Code:</td>
            <td class="font-monospace fw-semibold">{{ $payslip->user->employeeProfile?->employee_code ?? 'EMP-' . $payslip->user->id }}</td>
          </tr>
          <tr>
            <td class="text-muted ps-0">Designation:</td>
            <td>
              <span class="fw-semibold">{{ $payslip->user->employeeProfile?->designation ?? 'Staff Member' }}</span>
              @if($payslip->user->employeeProfile?->department)
                <span class="badge bg-label-primary ms-1">{{ $payslip->user->employeeProfile->department }}</span>
              @endif
            </td>
          </tr>
          <tr>
            <td class="text-muted ps-0">Shift:</td>
            <td>{{ $payslip->user->employeeProfile?->shift?->name ?? 'Standard Shift' }}</td>
          </tr>
        </table>
      </div>
      <div class="col-6">
        <div class="section-title">Payment & Banking Details</div>
        <table class="table table-sm table-borderless mb-0">
          <tr>
            <td class="text-muted ps-0" style="width: 130px;">Bank Name:</td>
            <td class="fw-semibold">{{ $payslip->user->employeeProfile?->bank_name ?? 'Corporate Bank' }}</td>
          </tr>
          <tr>
            <td class="text-muted ps-0">Account No:</td>
            <td class="font-monospace">{{ $payslip->user->employeeProfile?->bank_account_number ?? '--------' }}</td>
          </tr>
          <tr>
            <td class="text-muted ps-0">IBAN:</td>
            <td class="font-monospace">{{ $payslip->user->employeeProfile?->iban ?? '--------' }}</td>
          </tr>
          <tr>
            <td class="text-muted ps-0">Disbursement:</td>
            <td><span class="badge bg-label-success">{{ ucfirst($payslip->payment_method) }}</span></td>
          </tr>
        </table>
      </div>
    </div>

    <!-- Earnings and Deductions Two Column Layout -->
    <div class="row g-4 mb-4">
      <!-- Earnings (With Commissions Prominently Displayed) -->
      <div class="col-6">
        <div class="section-title text-success"><i class="ti ti-circle-plus me-1"></i> Gross Earnings (PKR)</div>
        <table class="table table-slip table-bordered mb-0">
          <thead>
            <tr>
              <th>Description</th>
              <th class="text-end" style="width: 140px;">Amount (Rs.)</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Base Salary Earned</td>
              <td class="text-end font-monospace">Rs. {{ number_format($payslip->base_salary_earned, 2) }}</td>
            </tr>
            <tr>
              <td>
                <div>Sales Agent Lead Bonus</div>
                <small class="text-muted">Qualified carrier conversions (> $200 USD)</small>
              </td>
              <td class="text-end font-monospace text-success fw-semibold">
                Rs. {{ number_format($payslip->sales_lead_bonus_earned, 2) }}
              </td>
            </tr>
            <tr>
              <td>
                <div>Dispatcher Milestone Bonus</div>
                <small class="text-muted">Appreciation for load volume targets</small>
              </td>
              <td class="text-end font-monospace text-success fw-semibold">
                Rs. {{ number_format($payslip->dispatcher_target_bonus_earned, 2) }}
              </td>
            </tr>
            <tr>
              <td>
                <div>Dispatcher Load Commissions</div>
                <small class="text-muted">Direct per-load % commission</small>
              </td>
              <td class="text-end font-monospace text-success fw-semibold">
                Rs. {{ number_format($payslip->dispatcher_load_commission_earned, 2) }}
              </td>
            </tr>
            <tr class="table-light fw-bold">
              <td>Total Gross Earnings</td>
              <td class="text-end font-monospace text-success">Rs. {{ number_format($payslip->total_earnings, 2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Deductions -->
      <div class="col-6">
        <div class="section-title text-danger"><i class="ti ti-circle-minus me-1"></i> Deductions (PKR)</div>
        <table class="table table-slip table-bordered mb-0">
          <thead>
            <tr>
              <th>Description</th>
              <th class="text-end" style="width: 140px;">Amount (Rs.)</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <div>Late Arrival Penalties</div>
                <small class="text-muted">{{ $payslip->late_count }} late punch(es)</small>
              </td>
              <td class="text-end font-monospace text-danger">
                -Rs. {{ number_format($payslip->late_deductions_total, 2) }}
              </td>
            </tr>
            <tr>
              <td>
                <div>Leave Deductions</div>
                <small class="text-muted">Unpaid or exceeding 2 paid leaves/mo</small>
              </td>
              <td class="text-end font-monospace text-danger">
                -Rs. {{ number_format($payslip->unpaid_leave_deductions_total, 2) }}
              </td>
            </tr>
            <tr>
              <td>
                <div>Loan / Advance Recovery</div>
                <small class="text-muted">Scheduled monthly installment</small>
              </td>
              <td class="text-end font-monospace text-danger">
                -Rs. {{ number_format($payslip->loan_deductions_total, 2) }}
              </td>
            </tr>
            <tr class="table-light fw-bold">
              <td>Total Deductions</td>
              <td class="text-end font-monospace text-danger">-Rs. {{ number_format($payslip->total_deductions, 2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Net Salary Highlight Banner -->
    <div class="net-salary-banner d-flex justify-content-between align-items-center mb-5">
      <div>
        <h5 class="mb-0 text-white fw-bold">NET PAYABLE SALARY</h5>
        <small class="text-white-50">(Gross Earnings minus Total Deductions in PKR)</small>
      </div>
      <div class="text-end">
        <h2 class="mb-0 text-white fw-bolder font-monospace">Rs. {{ number_format($payslip->net_salary, 2) }}</h2>
        <small class="text-white-50">Status: {{ strtoupper($payslip->payment_status) }}</small>
      </div>
    </div>

    <!-- Signatures Block -->
    <div class="row pt-5 mt-5 border-top">
      <div class="col-4 text-center">
        <div style="border-bottom: 1px dashed #999; height: 40px; margin-bottom: 8px;"></div>
        <small class="text-muted fw-semibold">Employee Signature</small>
      </div>
      <div class="col-4 text-center">
        <div style="border-bottom: 1px dashed #999; height: 40px; margin-bottom: 8px;"></div>
        <small class="text-muted fw-semibold">HR & Accounts Officer</small>
      </div>
      <div class="col-4 text-center">
        <div style="border-bottom: 1px dashed #999; height: 40px; margin-bottom: 8px;"></div>
        <small class="text-muted fw-semibold">Managing Director / CEO</small>
      </div>
    </div>
  </div>
</div>

</body>
</html>
