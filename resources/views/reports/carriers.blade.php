@extends('layouts.master')

@section('title', 'Carrier Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Carrier Reports</h4>
    <p class="text-muted mb-0">Filtered analytics and Excel data exports for registered carriers</p>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header border-bottom py-3">
    <div class="row g-3 align-items-center">
      @role('Admin')
        <div class="col-md-3 col-sm-6">
          <label class="form-label small text-muted">Filter by Agent</label>
          <select id="select_agent" class="form-select form-select-sm">
            <option value="all">All Agents</option>
            @if(isset($salesAgnets))
              @foreach($salesAgnets as $salesAgnet)
                <option value="{{ $salesAgnet->id }}">{{ $salesAgnet->full_name }}</option>
              @endforeach
            @endif
          </select>
        </div>
      @endrole

      <div class="col-md-4 col-sm-6">
        <label class="form-label small text-muted">Date Range</label>
        <input type="hidden" id="start_date" />
        <input type="hidden" id="end_date" />
        <div id="reportrange" class="form-control form-control-sm bg-white d-flex align-items-center justify-content-between" style="cursor: pointer;">
          <span class="small" data-url="{{ route('reports.carriers') }}"></span>
          <i class="ti ti-calendar ti-xs text-muted"></i>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 ms-auto text-md-end pt-md-3">
        <a id="excel_url" href="" class="btn btn-primary btn-sm">
          <i class="ti ti-file-spreadsheet me-1"></i> Export Excel
        </a>
      </div>
    </div>
  </div>

  <div class="card-body pt-3">
    <div class="table-responsive">
      <table id="carrier_table" class="table table-hover align-middle w-100">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Agent Name</th>
            <th>MC #</th>
            <th>Name</th>
            <th>Number</th>
            <th>Truck Type</th>
            <th>Assign To</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
