@extends('layouts.master')

@section('title', 'Carriers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Carriers Directory</h4>
    <p class="text-muted mb-0">Manage registered carrier profiles, documents, and dispatcher assignments</p>
  </div>
  @can('carriers-create')
    <a href="{{ route('carriers.create') }}" class="btn btn-primary">
      <i class="ti ti-plus me-1"></i> Add Carrier
    </a>
  @endcan
</div>

<div class="card shadow-sm">
  <div class="card-header border-bottom py-3">
    <h5 class="card-title mb-0">Carriers List</h5>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th style="width: 60px;">#</th>
          <th>Agent</th>
          <th>MC #</th>
          <th>Carrier Name</th>
          <th>Contact Number</th>
          <th>Truck Type</th>
          <th style="width: 180px;">Assign To</th>
          <th style="width: 130px;" class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($carriers as $key => $carrier)
          <tr>
            <td>{{ ($carriers->currentPage() - 1) * $carriers->perPage() + $key + 1 }}</td>
            <td>
              <div class="d-flex align-items-center text-nowrap">
                <div class="avatar avatar-xs me-2">
                  <span class="avatar-initial rounded-circle bg-label-secondary fw-bold">
                    {{ strtoupper(substr($carrier->user->first_name ?? 'A', 0, 1)) }}
                  </span>
                </div>
                <span class="fw-medium text-heading">{{ $carrier->user ? $carrier->user->full_name : 'System' }}</span>
              </div>
            </td>
            <td>
              <span class="badge bg-label-primary font-monospace fw-semibold">MC-{{ $carrier->mc_number }}</span>
            </td>
            <td class="fw-semibold text-heading">{{ $carrier->name }}</td>
            <td class="text-nowrap">
              @if($carrier->number)
                <i class="ti ti-phone ti-xs me-1 text-secondary"></i>{{ $carrier->number }}
              @else
                <span class="text-muted">--</span>
              @endif
            </td>
            <td>
              <span class="badge bg-label-info">{{ $carrier->truckType->name ?? 'N/A' }}</span>
            </td>
            <td>
              @can('assign-carrier')
                <select
                  onchange="getDispatchId(this, {{ $carrier->id }})"
                  class="form-select form-select-sm"
                  style="min-width: 170px;"
                >
                  <option value="">-- Unassigned --</option>
                  @foreach($dispatchers as $dispatcher)
                    <option value="{{ $dispatcher->id }}" {{ $dispatcher->id == $carrier->assign_to ? 'selected' : '' }}>
                      {{ $dispatcher->full_name }}
                    </option>
                  @endforeach
                </select>
              @else
                <span class="text-muted text-nowrap">{{ $carrier->assignedTo->full_name ?? 'N/A' }}</span>
              @endcan
            </td>
            <td class="text-center">
              <div class="d-flex justify-content-center align-items-center">
                @can('carriers-view')
                  <a
                    href="{{ route('carriers.show', $carrier->id) }}"
                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill"
                    title="View Carrier"
                  >
                    <i class="ti ti-eye"></i>
                  </a>
                @endcan

                @can('carriers-edit')
                  <a
                    href="{{ route('carriers.edit', $carrier->id) }}"
                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill"
                    title="Edit Carrier"
                  >
                    <i class="ti ti-edit"></i>
                  </a>
                @endcan

                @can('send-email')
                  <a
                    href="{{ route('carriers.email', $carrier->id) }}"
                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill"
                    title="Send Email"
                  >
                    <i class="ti ti-mail-forward"></i>
                  </a>
                @endcan

                @can('carriers-delete')
                  <a
                    href="javascript:void(0);"
                    onclick="return confirmAndSubmit({{ $carrier->id }})"
                    class="btn btn-sm btn-icon btn-text-danger rounded-pill"
                    title="Delete Carrier"
                  >
                    <i class="ti ti-trash"></i>
                  </a>
                  <form id="delete-record-{{ $carrier->id }}" action="{{ route('carriers.destroy', $carrier->id) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                  </form>
                @endcan
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-4">No carriers found</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @include('layouts.pagination', ['paginator' => $carriers, 'name' => 'carriers'])
</div>
@endsection
