@extends('layouts.master')

@section('title', 'Add Truck Type')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Create Truck Type</h4>
    <p class="text-muted mb-0">Define equipment categories and standard commission rates</p>
  </div>
  <a href="{{ route('truck-types.index') }}" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to Truck Types
  </a>
</div>



<div class="row justify-content-center">
  <div class="col-lg-6 col-md-8">
    <div class="card shadow-sm">
      <div class="card-header border-bottom py-3 d-flex align-items-center">
        <i class="ti ti-truck me-2 text-primary fs-4"></i>
        <h5 class="card-title mb-0">Truck Type Information</h5>
      </div>
      <div class="card-body pt-4">
        <form method="POST" action="{{ route('truck-types.store') }}">
          @csrf

          <div class="mb-3">
            <label for="name" class="form-label">Truck Type Name <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="ti ti-truck"></i></span>
              <input
                id="name"
                type="text"
                class="form-control @error('name') is-invalid @enderror"
                name="name"
                value="{{ old('name') }}"
                placeholder="e.g. Dry Van, Reefer, Flatbed"
                required
                autofocus
              />
            </div>
            @error('name')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-4">
            <label for="commission" class="form-label">Standard Commission (%) <span class="text-danger">*</span></label>
            <div class="input-group">
              <input
                id="commission"
                type="number"
                step="0.01"
                class="form-control @error('commission') is-invalid @enderror"
                name="commission"
                value="{{ old('commission') }}"
                placeholder="e.g. 5"
                required
              />
              <span class="input-group-text">%</span>
            </div>
            @error('commission')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('truck-types.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
              <i class="ti ti-plus me-1"></i> Create Truck Type
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
