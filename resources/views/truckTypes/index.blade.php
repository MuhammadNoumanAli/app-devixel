@extends('layouts.master')

@section('title', 'Truck Types')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Truck Types</h4>
    <p class="text-muted mb-0">Manage vehicle categories and configurations</p>
  </div>
  <a href="{{ route('truck-types.create') }}" class="btn btn-primary">
    <i class="ti ti-plus me-1"></i> Add Truck Type
  </a>
</div>

<div class="card shadow-sm">
  <div class="card-header border-bottom py-3">
    <h5 class="card-title mb-0">Truck Types List</h5>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th style="width: 70px;">#</th>
          <th>Name</th>
          <th>Created Date</th>
          <th style="width: 120px;" class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($truckTypes as $key => $type)
          <tr>
            <td>{{ ($truckTypes->currentPage() - 1) * $truckTypes->perPage() + $key + 1 }}</td>
            <td class="fw-semibold">
              <span class="badge bg-label-primary fs-6">{{ $type->name }}</span>
            </td>
            <td>{{ $type->created_at->format('M d, Y') }}</td>
            <td class="text-center">
              <div class="d-flex justify-content-center align-items-center">
                <a
                  href="{{ route('truck-types.edit', $type->id) }}"
                  class="btn btn-sm btn-icon btn-text-secondary rounded-pill me-1"
                  title="Edit"
                >
                  <i class="ti ti-edit"></i>
                </a>
                <a
                  href="javascript:void(0);"
                  onclick="return confirmAndSubmit({{ $type->id }})"
                  class="btn btn-sm btn-icon btn-text-danger rounded-pill"
                  title="Delete"
                >
                  <i class="ti ti-trash"></i>
                </a>
                <form id="delete-record-{{ $type->id }}" action="{{ route('truck-types.destroy', $type->id) }}" method="POST" class="d-none">
                  @csrf
                  @method('DELETE')
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="text-center text-muted py-4">No truck types found</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @include('layouts.pagination', ['paginator' => $truckTypes, 'name' => 'truck types'])
</div>
@endsection
