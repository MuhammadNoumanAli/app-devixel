@extends('layouts.master')

@section('title', 'Unpaid Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Unpaid Carrier Invoices</h4>
    <p class="text-muted mb-0">Track and update invoices pending payment</p>
  </div>
  <a href="{{ route('invoices.viewDispatcherPDFView') }}" class="btn btn-primary">
    <i class="ti ti-file-analytics me-1"></i> Dispatcher Reports
  </a>
</div>

<div class="card shadow-sm">
  <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Unpaid Invoices List</h5>
    <div style="width: 250px;">
      <select id="select_mc_numbers_not_paid" class="form-select form-select-sm">
        <option value="">-- Filter by MC Number --</option>
        @foreach($mc_numbers as $row)
          <option value="{{ $row->mc_number }}">{{ $row->mc_number }}</option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>MC #</th>
          <th>Carrier</th>
          <th>Load #</th>
          <th>Dispatcher</th>
          <th>Route</th>
          <th>Delivery Date</th>
          <th>Rate</th>
          <th>Status</th>
          <th style="width: 80px;" class="text-center">Action</th>
        </tr>
      </thead>
      <tbody id="carrier_not_paid">
        @forelse($dispatchers as $dispatch)
          <tr>
            <td><span class="badge bg-label-primary font-monospace">{{ $dispatch->mc_number }}</span></td>
            <td class="fw-semibold">{{ $dispatch->owner_name }}</td>
            <td><span class="badge bg-label-info">{{ $dispatch->load_number }}</span></td>
            <td>{{ $dispatch->user ? $dispatch->user->full_name : 'System' }}</td>
            <td class="small">
              {{ $dispatch->pick_location }} → {{ $dispatch->delivery_location }}
            </td>
            <td>
              {{ $dispatch->delivery_date ? \Carbon\Carbon::parse($dispatch->delivery_date)->format('M d, Y') : 'N/A' }}
            </td>
            <td class="fw-bold text-success">${{ number_format($dispatch->rate) }}</td>
            <td>
              <span class="badge bg-label-warning">
                {{ $dispatch->invoice_status == 1 ? 'Invoice Sent' : ($dispatch->invoice_status ?? 'Pending') }}
              </span>
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
        @empty
          <tr>
            <td colspan="9" class="text-center text-muted py-4">No unpaid invoices found</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
