@extends('layouts.master')

@section('title', 'Dispatcher PDF & Invoice Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Invoice Generation & Dispatcher Reports</h4>
    <p class="text-muted mb-0">Select loads to generate bulk PDF invoices or export Excel reports</p>
  </div>
</div>

@if (session('status'))
  <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <i class="ti ti-check me-2"></i> {{ session('status') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@elseif (session('error'))
  <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <i class="ti ti-alert-circle me-2"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<div class="card shadow-sm">
  <div class="card-header border-bottom py-3">
    <div class="row g-3 align-items-center">
      <div class="col-md-4 col-sm-6">
        <label class="form-label small text-muted">Date Range</label>
        <input type="hidden" id="start_date1" />
        <input type="hidden" id="end_date1" />
        <div id="report_range_dispatch_pdf" class="form-control form-control-sm bg-white d-flex align-items-center justify-content-between" style="cursor: pointer;">
          <span class="small" data-url="{{ route('reports.dispatchers') }}"></span>
          <i class="ti ti-calendar ti-xs text-muted"></i>
        </div>
      </div>

      <div class="col-md-4 col-sm-6">
        <label class="form-label small text-muted">Filter by MC #</label>
        <select id="select_mc_numbers" class="form-select form-select-sm">
          <option value="">-- All MC Numbers --</option>
        </select>
      </div>

      <div class="col-md-4 col-sm-12 ms-auto text-md-end pt-md-3">
        <input type="hidden" id="selected_invoices" name="selected_invoices">
        <div class="d-inline-flex gap-2">
          <div class="download_pdf" style="display: none;">
            <a id="pdf_dispatch_url" href="" class="btn btn-primary btn-sm">
              <i class="ti ti-file-type-pdf me-1"></i> Download PDF
            </a>
          </div>
          <div class="download_xlx">
            <a id="xlx_dispatch_url" href="" class="btn btn-outline-primary btn-sm">
              <i class="ti ti-file-spreadsheet me-1"></i> Export XLS
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th style="width: 50px;">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="invoice_all" value="all">
            </div>
          </th>
          <th>MC #</th>
          <th>Load #</th>
          <th>Dispatcher</th>
          <th>Origin</th>
          <th>Destination</th>
          <th>Delivery Date</th>
          <th>Carrier</th>
          <th>Rate</th>
          <th>Invoice Status</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody id="pdf_table">
        @foreach($dispatchers as $key => $dispatch)
          <tr>
            <td>
              @if($dispatch->invoice_generate != 1)
                <div class="form-check">
                  <input class="form-check-input chcktbl" type="checkbox" name="invoice" value="{{ $dispatch->id }}">
                </div>
              @endif
            </td>
            <td><span class="badge bg-label-primary font-monospace">{{ $dispatch->mc_number }}</span></td>
            <td><span class="badge bg-label-info">{{ $dispatch->load_number }}</span></td>
            <td>{{ $dispatch->user ? $dispatch->user->full_name : 'System' }}</td>
            <td class="small">{{ $dispatch->pick_location }}</td>
            <td class="small">{{ $dispatch->delivery_location }}</td>
            <td class="small">{{ $dispatch->delivery_date ? \Carbon\Carbon::parse($dispatch->delivery_date)->format('M d, Y') : 'N/A' }}</td>
            <td class="fw-semibold">{{ $dispatch->owner_name }}</td>
            <td class="fw-bold text-success">${{ number_format($dispatch->rate) }}</td>
            <td>
              <select onchange="changeInvoiceStatus(this, {{ $dispatch->id }})" class="form-select form-select-sm" style="min-width: 130px;">
                <option value="" @selected(empty($dispatch->invoice_status))>-- Select --</option>
                <option value="1" @selected($dispatch->invoice_status == 1)>Invoice Sent</option>
                <option value="2" @selected($dispatch->invoice_status == 2)>Paid</option>
              </select>
            </td>
            <td class="text-center">
              <a
                href="{{ route('dispatchers.show', $dispatch->id) }}"
                class="btn btn-sm btn-icon btn-text-secondary rounded-pill"
                title="View Dispatch"
              >
                <i class="ti ti-eye"></i>
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
