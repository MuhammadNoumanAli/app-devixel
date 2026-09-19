@extends('layouts.master')

@section('title', 'Truck Type Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Truck Type Reports</h4>
    <p class="text-muted mb-0">Equipment performance analytics, volume metrics, and revenue breakdown</p>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header border-bottom py-3">
    <div class="row g-3 align-items-center">
      @role('Admin')
        <div class="col-md-3 col-sm-6">
          <label class="form-label small text-muted">Filter by Equipment</label>
          <select id="select_truckTypes" class="form-select form-select-sm">
            <option value="all">All Equipment Types</option>
            @if(isset($truckTypes))
              @foreach($truckTypes as $truckType)
                <option value="{{ $truckType->id }}">{{ $truckType->name }}</option>
              @endforeach
            @endif
          </select>
        </div>
      @endrole

      <div class="col-md-4 col-sm-6">
        <label class="form-label small text-muted">Date Range</label>
        <input type="hidden" id="start_date_truckType" />
        <input type="hidden" id="end_date_truckType" />
        <div id="reportrange_truckType" class="form-control form-control-sm bg-white d-flex align-items-center justify-content-between" style="cursor: pointer;">
          <span class="small" data-url="{{ route('reports.truckTypesReport') }}"></span>
          <i class="ti ti-calendar ti-xs text-muted"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="card-body p-0">
    <table id="truckTypes_table" class="table table-hover align-middle mb-0 w-100">
      <thead class="table-light">
        <tr>
          <th>Sr. #</th>
          <th>Agent Name</th>
          <th>MC #</th>
          <th>Truck Name</th>
          <th>Rate</th>
          <th>%</th>
          <th>Receivable</th>
          <th>Load Date</th>
          <th>Created At</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>
@endsection
