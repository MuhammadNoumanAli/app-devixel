@extends('layouts.master')

@section('title', 'Invoices & Payments Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-1">Invoices & Payment Tracking</h4>
    <p class="text-muted mb-0">Track carrier invoices, record partial payments, monitor balances, and export statements</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('invoices.viewDispatcherPDFView') }}" class="btn btn-primary">
      <i class="ti ti-file-plus me-1"></i> Generate New Invoice
    </a>
  </div>
</div>



<!-- Summary KPI Cards -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card shadow-sm h-100 border-0">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-semibold text-uppercase">Total Invoiced (Payable)</span>
          <div class="avatar avatar-sm bg-label-primary rounded p-2">
            <i class="ti ti-file-invoice fs-5 text-primary"></i>
          </div>
        </div>
        <h4 class="fw-bold mb-1">${{ number_format($metrics['all_total'], 2) }}</h4>
        <small class="text-muted">{{ $metrics['all_count'] }} invoices generated</small>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card shadow-sm h-100 border-0">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-semibold text-uppercase">Outstanding Due</span>
          <div class="avatar avatar-sm bg-label-danger rounded p-2">
            <i class="ti ti-alert-circle fs-5 text-danger"></i>
          </div>
        </div>
        <h4 class="fw-bold mb-1 text-danger">${{ number_format($metrics['due_total'], 2) }}</h4>
        <small class="text-muted">{{ $metrics['due_count'] }} invoices completely unpaid</small>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card shadow-sm h-100 border-0">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-semibold text-uppercase">Partially Paid</span>
          <div class="avatar avatar-sm bg-label-info rounded p-2">
            <i class="ti ti-coin fs-5 text-info"></i>
          </div>
        </div>
        <h4 class="fw-bold mb-1 text-info">${{ number_format($metrics['partial_total'], 2) }}</h4>
        <small class="text-muted">{{ $metrics['partial_count'] }} invoices with balance left</small>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card shadow-sm h-100 border-0">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-semibold text-uppercase">Cleared / Paid</span>
          <div class="avatar avatar-sm bg-label-success rounded p-2">
            <i class="ti ti-circle-check fs-5 text-success"></i>
          </div>
        </div>
        <h4 class="fw-bold mb-1 text-success">${{ number_format($metrics['paid_total'], 2) }}</h4>
        <small class="text-muted">{{ $metrics['paid_count'] }} invoices fully cleared</small>
      </div>
    </div>
  </div>
</div>

<!-- Main Card with Filters and Status Tabs -->
<div class="card shadow-sm border-0">
  <!-- Filter Toolbar -->
  <div class="card-header border-bottom py-3">
    <form method="GET" action="{{ route('invoices.index') }}" id="filterForm">
      <input type="hidden" name="status" id="tabStatusInput" value="{{ $statusTab }}">
      <div class="row g-2 align-items-center">
        <div class="col-md-3 col-sm-6">
          <label class="form-label small text-muted mb-1">Filter by Carrier (MC #)</label>
          <select name="mc_number" id="select_mc_number" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
            <option value="">-- All MC Numbers --</option>
            @foreach($mc_numbers as $c)
              <option value="{{ $c->mc_number }}" @selected($mcFilter == $c->mc_number)>
                {{ $c->mc_number }} - {{ $c->company_name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3 col-sm-6">
          <label class="form-label small text-muted mb-1">Search Keyword</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="ti ti-search"></i></span>
            <input
              type="text"
              name="search"
              class="form-control"
              placeholder="Invoice #, Carrier, Load #"
              value="{{ $search }}"
            />
          </div>
        </div>

        <div class="col-md-4 col-sm-6">
          <label class="form-label small text-muted mb-1">Date Range</label>
          <input type="hidden" id="start_date_invoices" name="start_date" value="{{ $startDate }}" />
          <input type="hidden" id="end_date_invoices" name="end_date" value="{{ $endDate }}" />
          <div id="report_range_invoices" class="form-control form-control-sm bg-white d-flex align-items-center justify-content-between" style="cursor: pointer;">
            <span class="small text-truncate"></span>
            <i class="ti ti-calendar ti-xs text-muted ms-1"></i>
          </div>
        </div>

        <div class="col-md-2 col-sm-12 d-flex gap-1 align-items-end pt-3">
          <button type="submit" class="btn btn-sm btn-primary flex-fill">
            <i class="ti ti-filter me-1"></i> Apply
          </button>
          @if($mcFilter || $search || $startDate || $endDate)
            <a href="{{ route('invoices.index', ['status' => $statusTab]) }}" class="btn btn-sm btn-outline-secondary" title="Clear Filters">
              <i class="ti ti-refresh"></i>
            </a>
          @endif
        </div>
      </div>
    </form>

    @if(!empty($mcFilter))
      <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
        <span class="small text-muted">
          Showing invoices filtered for MC: <strong>{{ $mcFilter }}</strong>
        </span>
        <a href="{{ route('invoices.downloadMcAllInvoicesPdf', $mcFilter) }}" class="btn btn-sm btn-outline-danger">
          <i class="ti ti-download me-1"></i> Download All Invoices for MC #{{ $mcFilter }}
        </a>
      </div>
    @endif
  </div>

  <!-- Status Tabs -->
  <div class="card-header border-bottom py-2 bg-light-subtle">
    <ul class="nav nav-pills card-header-pills">
      <li class="nav-item">
        <a
          class="nav-link {{ $statusTab === 'all' ? 'active' : '' }}"
          href="{{ route('invoices.index', array_merge(request()->query(), ['status' => 'all'])) }}"
        >
          <i class="ti ti-list me-1"></i> All Invoices
          <span class="badge bg-secondary ms-1 rounded-pill">{{ $metrics['all_count'] }}</span>
        </a>
      </li>
      <li class="nav-item">
        <a
          class="nav-link {{ $statusTab === 'due' ? 'active' : '' }}"
          href="{{ route('invoices.index', array_merge(request()->query(), ['status' => 'due'])) }}"
        >
          <i class="ti ti-alert-circle me-1 text-danger"></i> Due / Unpaid
          <span class="badge bg-danger ms-1 rounded-pill">{{ $metrics['due_count'] }}</span>
        </a>
      </li>
      <li class="nav-item">
        <a
          class="nav-link {{ $statusTab === 'partial' ? 'active' : '' }}"
          href="{{ route('invoices.index', array_merge(request()->query(), ['status' => 'partial'])) }}"
        >
          <i class="ti ti-coin me-1 text-info"></i> Partial Paid
          <span class="badge bg-info ms-1 rounded-pill">{{ $metrics['partial_count'] }}</span>
        </a>
      </li>
      <li class="nav-item">
        <a
          class="nav-link {{ $statusTab === 'paid' ? 'active' : '' }}"
          href="{{ route('invoices.index', array_merge(request()->query(), ['status' => 'paid'])) }}"
        >
          <i class="ti ti-circle-check me-1 text-success"></i> Paid / Cleared
          <span class="badge bg-success ms-1 rounded-pill">{{ $metrics['paid_count'] }}</span>
        </a>
      </li>
    </ul>
  </div>

  <!-- Invoices Table -->
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Invoice #</th>
          <th>MC # & Carrier</th>
          <th>Invoice Date</th>
          <th>Loads</th>
          <th class="text-end">Payable Amount</th>
          <th class="text-end">Paid Amount</th>
          <th class="text-end">Due Balance</th>
          <th class="text-center">Status</th>
          <th class="text-center" style="width: 170px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($invoices as $inv)
          <tr id="invoice-row-{{ $inv->id }}">
            <td>
              <span class="fw-bold font-monospace text-primary">{{ $inv->invoice_no }}</span>
            </td>
            <td>
              <div class="fw-semibold text-dark">{{ $inv->carrier_name ?: 'N/A' }}</div>
              <span class="badge bg-label-secondary font-monospace small">MC: {{ $inv->mc_number }}</span>
            </td>
            <td class="small text-muted">
              {{ $inv->invoice_date ? $inv->invoice_date->format('M d, Y') : 'N/A' }}
            </td>
            <td>
              <div class="d-flex flex-wrap gap-1" style="max-width: 160px;">
                @foreach($inv->invoiceDispatches as $item)
                  <span class="badge bg-label-info font-monospace small" title="Dispatcher: {{ $item->dispatcher ? $item->dispatcher->first_name : 'N/A' }}">
                    #{{ $item->load_number }}
                  </span>
                @endforeach
              </div>
              <small class="text-muted">{{ $inv->invoiceDispatches->count() }} load(s)</small>
            </td>
            <td class="text-end fw-bold">
              ${{ number_format($inv->total_amount, 2) }}
            </td>
            <td class="text-end fw-semibold text-success" id="paid-val-{{ $inv->id }}">
              ${{ number_format($inv->paid_amount, 2) }}
            </td>
            <td class="text-end fw-bold" id="due-val-{{ $inv->id }}">
              <span class="{{ $inv->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                ${{ number_format($inv->due_amount, 2) }}
              </span>
            </td>
            <td class="text-center" id="status-val-{{ $inv->id }}">
              @if($inv->status === 'paid')
                <span class="badge bg-label-success">
                  <i class="ti ti-circle-check me-1"></i> Cleared
                </span>
              @elseif($inv->status === 'partial')
                <span class="badge bg-label-info">
                  <i class="ti ti-coin me-1"></i> Partial
                </span>
              @else
                <span class="badge bg-label-danger">
                  <i class="ti ti-clock me-1"></i> Due
                </span>
              @endif
            </td>
            <td class="text-center">
              <div class="d-inline-flex gap-1 align-items-center">
                <!-- Add Payment Button -->
                @if($inv->status !== 'paid')
                  <button
                    type="button"
                    class="btn btn-sm btn-icon btn-text-success rounded-pill"
                    title="Record Payment"
                    onclick="openPaymentModal({{ $inv->id }}, '{{ $inv->invoice_no }}', '{{ $inv->mc_number }}', {{ $inv->total_amount }}, {{ $inv->paid_amount }}, {{ $inv->due_amount }})"
                  >
                    <i class="ti ti-cash fs-5"></i>
                  </button>
                @else
                  <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill opacity-25" disabled title="Already fully paid">
                    <i class="ti ti-check fs-5"></i>
                  </button>
                @endif

                <!-- Payment History & Details Button -->
                <button
                  type="button"
                  class="btn btn-sm btn-icon btn-text-info rounded-pill"
                  title="Payment History & Loads"
                  onclick="openHistoryModal({{ $inv->id }})"
                >
                  <i class="ti ti-history fs-5"></i>
                </button>

                <!-- Download PDF Button -->
                <a
                  href="{{ route('invoices.downloadInvoicePdf', $inv->id) }}"
                  class="btn btn-sm btn-icon btn-text-primary rounded-pill"
                  title="Download Invoice PDF"
                >
                  <i class="ti ti-download fs-5"></i>
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center text-muted py-5">
              <div class="mb-2"><i class="ti ti-file-invoice fs-1 text-secondary"></i></div>
              <h6 class="text-muted fw-semibold">No invoices found</h6>
              <p class="small text-muted mb-3">No invoice records match your current status tab or filters.</p>
              <a href="{{ route('invoices.viewDispatcherPDFView') }}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus me-1"></i> Generate Invoice from Dispatcher Report
              </a>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($invoices->hasPages())
    <div class="card-footer border-top py-3">
      {{ $invoices->links() }}
    </div>
  @endif
</div>

<!-- Modal 1: Record Partial Payment -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title text-white mb-0" id="paymentModalLabel">
          <i class="ti ti-cash me-2"></i> Record Partial Payment
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="recordPaymentForm" method="POST">
        @csrf
        <div class="modal-body p-4">
          <!-- Summary Banner -->
          <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3 py-2">
            <div>
              <span class="small text-muted d-block">Invoice / MC</span>
              <strong id="modal_inv_label" class="font-monospace text-primary"></strong>
            </div>
            <div class="text-end">
              <span class="small text-muted d-block">Remaining Balance</span>
              <strong id="modal_due_label" class="text-danger fs-6"></strong>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label for="pay_amount" class="form-label">Payment Amount ($) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input
                  type="number"
                  step="0.01"
                  min="0.01"
                  class="form-control"
                  id="pay_amount"
                  name="amount"
                  required
                />
              </div>
            </div>

            <div class="col-md-6">
              <label for="pay_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
              <input
                type="date"
                class="form-control"
                id="pay_date"
                name="payment_date"
                value="{{ date('Y-m-d') }}"
                required
              />
            </div>

            <div class="col-md-6">
              <label for="pay_method" class="form-label">Payment Method</label>
              <select class="form-select" id="pay_method" name="payment_method">
                <option value="Zelle">Zelle</option>
                <option value="ACH / Bank Transfer">ACH / Bank Transfer</option>
                <option value="Check">Check</option>
                <option value="Wire Transfer">Wire Transfer</option>
                <option value="Credit / Debit Card">Credit / Debit Card</option>
                <option value="Cash">Cash</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <div class="col-md-6">
              <label for="pay_ref" class="form-label">Reference # / Trans ID</label>
              <input
                type="text"
                class="form-control"
                id="pay_ref"
                name="reference_no"
                placeholder="e.g. Check #1042, Zelle ID"
              />
            </div>

            <div class="col-12">
              <label for="pay_note" class="form-label">Note / Remarks (Optional)</label>
              <textarea
                class="form-control"
                id="pay_note"
                name="note"
                rows="2"
                placeholder="Notes about partial payment installment..."
              ></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light py-2">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success" id="btnSubmitPayment">
            <i class="ti ti-check me-1"></i> Save Payment
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal 2: Payment History & Loads Detail -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white py-3">
        <h5 class="modal-title text-white mb-0" id="historyModalLabel">
          <i class="ti ti-history me-2"></i> Payment History & Loads Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <!-- Summary Cards -->
        <div class="row g-2 mb-3 text-center">
          <div class="col-4">
            <div class="p-2 border rounded bg-light">
              <small class="text-muted d-block">Total Payable</small>
              <span class="fw-bold fs-6 text-dark" id="hist_total">$0.00</span>
            </div>
          </div>
          <div class="col-4">
            <div class="p-2 border rounded bg-light">
              <small class="text-muted d-block">Paid to Date</small>
              <span class="fw-bold fs-6 text-success" id="hist_paid">$0.00</span>
            </div>
          </div>
          <div class="col-4">
            <div class="p-2 border rounded bg-light">
              <small class="text-muted d-block">Remaining Balance</small>
              <span class="fw-bold fs-6" id="hist_due">$0.00</span>
            </div>
          </div>
        </div>

        <ul class="nav nav-tabs mb-3" role="tablist">
          <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-payments" type="button">
              <i class="ti ti-cash me-1"></i> Payments Recorded (<span id="hist_payments_count">0</span>)
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-loads" type="button">
              <i class="ti ti-truck me-1"></i> Attached Loads (<span id="hist_loads_count">0</span>)
            </button>
          </li>
        </ul>

        <div class="tab-content">
          <!-- Tab 1: Payments List -->
          <div class="tab-pane fade show active" id="tab-payments">
            <div class="table-responsive">
              <table class="table table-sm table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th class="text-end">Amount</th>
                    <th>Recorded By</th>
                    <th>Note</th>
                    <th class="text-center">Action</th>
                  </tr>
                </thead>
                <tbody id="hist_payments_tbody">
                  <tr>
                    <td colspan="8" class="text-center text-muted py-3">Loading payment history...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Tab 2: Loads List -->
          <div class="tab-pane fade" id="tab-loads">
            <div class="table-responsive">
              <table class="table table-sm table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Load #</th>
                    <th>Dispatcher</th>
                    <th class="text-end">Load Rate</th>
                    <th class="text-end">Payable Amount</th>
                  </tr>
                </thead>
                <tbody id="hist_loads_tbody">
                  <tr>
                    <td colspan="4" class="text-center text-muted py-3">Loading attached loads...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light py-2">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

@push('page-script')
<script>
  let activeInvoiceId = null;

  $(function() {
    var hasStart = '{{ $startDate }}';
    var hasEnd = '{{ $endDate }}';

    var start = hasStart ? moment('{{ $startDate }}') : moment().subtract(6, 'days');
    var end = hasEnd ? moment('{{ $endDate }}') : moment();

    function cbInvoiceDate(start, end, isInit) {
      if (!hasStart && !hasEnd && isInit) {
        $('#report_range_invoices span').html('All Dates (Click to filter)');
        $('#start_date_invoices').val('');
        $('#end_date_invoices').val('');
      } else {
        $('#report_range_invoices span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        $('#start_date_invoices').val(start.format('YYYY-MM-DD'));
        $('#end_date_invoices').val(end.format('YYYY-MM-DD'));
      }
    }

    $('#report_range_invoices').daterangepicker({
      startDate: start,
      endDate: end,
      ranges: {
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
      }
    }, function(selectedStart, selectedEnd) {
      hasStart = true;
      hasEnd = true;
      cbInvoiceDate(selectedStart, selectedEnd, false);
      document.getElementById('filterForm').submit();
    });

    cbInvoiceDate(start, end, true);

    $('#report_range_invoices').on('cancel.daterangepicker', function(ev, picker) {
      $('#start_date_invoices').val('');
      $('#end_date_invoices').val('');
      $('#report_range_invoices span').html('All Dates (Click to filter)');
      document.getElementById('filterForm').submit();
    });
  });

  window.openPaymentModal = function(invId, invNo, mcNo, total, paid, due) {
    activeInvoiceId = invId;
    document.getElementById('modal_inv_label').innerText = `${invNo} (MC: ${mcNo})`;
    document.getElementById('modal_due_label').innerText = `$${parseFloat(due).toFixed(2)}`;
    document.getElementById('pay_amount').value = parseFloat(due).toFixed(2);
    document.getElementById('pay_amount').max = parseFloat(due).toFixed(2);
    document.getElementById('pay_ref').value = '';
    document.getElementById('pay_note').value = '';

    const form = document.getElementById('recordPaymentForm');
    form.action = `/invoices/${invId}/payments`;

    const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    paymentModal.show();
  };

  // Handle Ajax Payment Form Submission
  $(function() {
    const paymentForm = document.getElementById('recordPaymentForm');
    if (paymentForm) {
      paymentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('btnSubmitPayment');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        const form = e.target;
        const formData = new FormData(form);

        fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''
          }
        })
        .then(res => res.json())
        .then(data => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="ti ti-check me-1"></i> Save Payment';

          if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
            window.location.reload();
          } else {
            alert(data.message || 'Error recording payment.');
          }
        })
        .catch(err => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="ti ti-check me-1"></i> Save Payment';
          console.error(err);
          alert('Failed to save payment. Please try again.');
        });
      });
    }
  });

  window.openHistoryModal = function(invId) {
    const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
    historyModal.show();

    const payTbody = document.getElementById('hist_payments_tbody');
    const loadsTbody = document.getElementById('hist_loads_tbody');
    payTbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm me-1"></span> Loading payments...</td></tr>';
    loadsTbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm me-1"></span> Loading loads...</td></tr>';

    fetch(`/invoices/${invId}/payments`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
      document.getElementById('hist_total').innerText = `$${data.total_amount}`;
      document.getElementById('hist_paid').innerText = `$${data.paid_amount}`;

      const dueElem = document.getElementById('hist_due');
      dueElem.innerText = `$${data.due_amount}`;
      dueElem.className = parseFloat(data.due_amount) > 0 ? 'fw-bold fs-6 text-danger' : 'fw-bold fs-6 text-success';

      document.getElementById('hist_payments_count').innerText = data.payments.length;
      document.getElementById('hist_loads_count').innerText = data.loads.length;

      // Render Payments
      if (data.payments.length === 0) {
        payTbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">No payments recorded yet for this invoice.</td></tr>';
      } else {
        let payRows = '';
        data.payments.forEach((p, idx) => {
          payRows += `
            <tr>
              <td><strong>#${idx + 1}</strong></td>
              <td>${p.payment_date}</td>
              <td><span class="badge bg-label-secondary">${p.payment_method}</span></td>
              <td>${p.reference_no}</td>
              <td class="text-end fw-bold text-success">$${p.amount}</td>
              <td><small>${p.receiver}</small></td>
              <td><small class="text-muted">${p.note}</small></td>
              <td class="text-center">
                <button type="button" class="btn btn-sm btn-icon btn-text-danger rounded-pill" title="Delete Payment" onclick="deletePayment('${p.delete_url}')">
                  <i class="ti ti-trash"></i>
                </button>
              </td>
            </tr>
          `;
        });
        payTbody.innerHTML = payRows;
      }

      // Render Loads
      if (data.loads.length === 0) {
        loadsTbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No loads associated.</td></tr>';
      } else {
        let loadRows = '';
        data.loads.forEach(l => {
          loadRows += `
            <tr>
              <td><span class="badge bg-label-info font-monospace">#${l.load_number}</span></td>
              <td>${l.dispatcher}</td>
              <td class="text-end">$${l.rate}</td>
              <td class="text-end fw-bold text-primary">$${l.receivable}</td>
            </tr>
          `;
        });
        loadsTbody.innerHTML = loadRows;
      }
    })
    .catch(err => {
      console.error(err);
      payTbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-3">Failed to load payment history.</td></tr>';
    });
  }

  window.deletePayment = function(deleteUrl) {
    if (!confirm('Are you sure you want to delete this payment installment? The remaining balance will be recalculated.')) {
      return;
    }

    fetch(deleteUrl, {
      method: 'DELETE',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''
      }
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        window.location.reload();
      } else {
        alert(data.message || 'Error deleting payment.');
      }
    })
    .catch(err => {
      console.error(err);
      alert('Failed to delete payment.');
    });
  }
</script>
@endpush
@endsection
