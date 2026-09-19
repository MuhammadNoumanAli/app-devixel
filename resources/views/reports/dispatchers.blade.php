@extends('layouts.master')

@section('title', 'Dispatcher Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Dispatcher Load Reports</h4>
    <p class="text-muted mb-0">Filtered load analytics, dispatcher metrics, and Excel data export</p>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header border-bottom py-3">
    <div class="row g-3 align-items-center">
      @role('Admin')
        <div class="col-md-3 col-sm-6">
          <label class="form-label small text-muted">Filter by Dispatcher</label>
          <select id="select_dispatcher" class="form-select form-select-sm">
            <option value="all">All Dispatchers</option>
            @if(isset($dispatch_users))
              @foreach($dispatch_users as $user)
                <option value="{{ $user->id }}">{{ $user->full_name }}</option>
              @endforeach
            @endif
          </select>
        </div>
      @endrole

      <div class="col-md-4 col-sm-6">
        <label class="form-label small text-muted">Date Range</label>
        <input type="hidden" id="start_date_d" />
        <input type="hidden" id="end_date_d" />
        <div id="reportrange_dispatch" class="form-control form-control-sm bg-white d-flex align-items-center justify-content-between" style="cursor: pointer;">
          <span class="small" data-url="{{ route('reports.dispatchers') }}"></span>
          <i class="ti ti-calendar ti-xs text-muted"></i>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 ms-auto text-md-end pt-md-3">
        <a id="excel_url_dispatch" href="" class="btn btn-primary btn-sm">
          <i class="ti ti-file-spreadsheet me-1"></i> Export Excel
        </a>
      </div>
    </div>
  </div>

  <div class="card-body p-0">
    <table id="dispatch_table" class="table table-hover align-middle mb-0 w-100">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Dispatcher</th>
          <th>MC #</th>
          <th>Pick Location</th>
          <th>Delivery Location</th>
          <th>Load Date</th>
          <th>Carrier Name</th>
          <th>Rate</th>
          <th>Broker</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>
@endsection
