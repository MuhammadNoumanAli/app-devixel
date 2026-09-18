@extends('layouts.master')

@section('title', 'Dispatch Loads')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Dispatch & Load Management</h4>
    <p class="text-muted mb-0">Track active loads, rate confirmations, pick/delivery dates, and broker info</p>
  </div>
  @can('dispatchers-create')
    <a href="{{ route('dispatchers.create') }}" class="btn btn-primary">
      <i class="ti ti-plus me-1"></i> Add Load
    </a>
  @endcan
</div>

<div class="card shadow-sm">
  <div class="card-header border-bottom py-3">
    <h5 class="card-title mb-0">Dispatch Loads List</h5>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th style="width: 50px;">#</th>
          <th>Dispatcher</th>
          <th>MC #</th>
          <th>Route (Pick → Delivery)</th>
          <th>Load Date</th>
          <th>Carrier / Owner</th>
          @can('dispatch-view-rate')
            <th>Rate</th>
          @endcan
          <th style="width: 90px;" class="text-center">Status</th>
          <th style="width: 140px;" class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($dispatchers as $key => $dispatch)
          <tr>
            <td>{{ ($dispatchers->currentPage() - 1) * $dispatchers->perPage() + $key + 1 }}</td>
            <td>
              <span class="fw-semibold">{{ $dispatch->user ? $dispatch->user->full_name : 'System' }}</span>
            </td>
            <td>
              <span class="badge bg-label-primary font-monospace">{{ $dispatch->mc_number }}</span>
            </td>
            <td>
              <div class="small">
                <span class="text-dark fw-semibold">{{ $dispatch->pick_location }}</span>
                <span class="text-muted mx-1">→</span>
                <span class="text-dark fw-semibold">{{ $dispatch->delivery_location }}</span>
              </div>
            </td>
            <td>
              {{ $dispatch->load_date ? \Carbon\Carbon::parse($dispatch->load_date)->format('M d, Y') : 'N/A' }}
            </td>
            <td>{{ $dispatch->owner_name }}</td>
            @can('dispatch-view-rate')
              <td class="text-success fw-bold">${{ number_format($dispatch->rate) }}</td>
            @endcan
            <td class="text-center">
              <button
                type="button"
                class="btn btn-sm btn-icon rounded-pill is_cancel {{ $dispatch->is_cancel ? 'btn-danger cancel-dot' : 'btn-label-success' }}"
                data-dispatch-id="{{ $dispatch->id }}"
                data-dispatch-status="{{ $dispatch->is_cancel ? '1' : '0' }}"
                onclick="cancelLoad({{ $dispatch->id }})"
                title="{{ $dispatch->is_cancel ? 'Cancelled Load' : 'Active Load' }}"
              >
                <i class="ti {{ $dispatch->is_cancel ? 'ti-x' : 'ti-check' }}"></i>
              </button>
            </td>
            <td class="text-center">
              <div class="d-flex justify-content-center align-items-center">
                @can('dispatchers-view')
                  <a
                    href="{{ route('dispatchers.show', $dispatch->id) }}"
                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill"
                    title="View Details"
                  >
                    <i class="ti ti-eye"></i>
                  </a>
                @endcan

                @can('dispatchers-edit')
                  <a
                    href="{{ route('dispatchers.edit', $dispatch->id) }}"
                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill"
                    title="Edit Dispatch"
                  >
                    <i class="ti ti-edit"></i>
                  </a>
                @endcan

                <a
                  href="{{ route('dispatchers.editAttachDocument', $dispatch->id) }}"
                  class="btn btn-sm btn-icon btn-text-secondary rounded-pill"
                  title="Attach Documents"
                >
                  <i class="ti ti-paperclip"></i>
                </a>

                @can('dispatchers-delete')
                  <a
                    href="javascript:void(0);"
                    onclick="return confirmAndSubmit({{ $dispatch->id }})"
                    class="btn btn-sm btn-icon btn-text-danger rounded-pill"
                    title="Delete Load"
                  >
                    <i class="ti ti-trash"></i>
                  </a>
                  <form id="delete-record-{{ $dispatch->id }}" action="{{ route('dispatchers.destroy', $dispatch->id) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                  </form>
                @endcan
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center text-muted py-4">No dispatch loads found</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer d-flex justify-content-end">
    {{ $dispatchers->links() }}
  </div>
</div>
@endsection
