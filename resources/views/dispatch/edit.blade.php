@extends('layouts.master')

@section('title', 'Edit Dispatch Load - ' . $dispatcher->load_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Edit Dispatch Load: {{ $dispatcher->load_number }}</h4>
    <p class="text-muted mb-0">Update dispatch schedule, driver assignment, rate info, and associated documents</p>
  </div>
  <a href="{{ route('dispatchers.index') }}" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to Dispatches
  </a>
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

@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <div class="fw-semibold mb-1"><i class="ti ti-alert-triangle me-1"></i> Please correct the following errors:</div>
    <ul class="mb-0 ps-3">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<form method="POST" action="{{ route('dispatchers.update', $dispatcher->id) }}" enctype="multipart/form-data">
  @csrf
  @method('PATCH')

  <!-- Section 1: Carrier & Load Identification -->
  <div class="card shadow-sm mb-4">
    <div class="card-header border-bottom py-3 d-flex align-items-center">
      <i class="ti ti-truck-delivery me-2 text-primary fs-4"></i>
      <h5 class="card-title mb-0">Load & Carrier Identification</h5>
    </div>
    <div class="card-body pt-4">
      <div class="row g-3">
        <div class="col-md-4">
          <label for="mc_number" class="form-label">Carrier MC Number <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-hash"></i></span>
            <input
              id="mc_number"
              type="text"
              class="form-control @error('mc_number') is-invalid @enderror"
              name="mc_number"
              value="{{ old('mc_number', $dispatcher->mc_number) }}"
              required
            />
          </div>
          @error('mc_number')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-4">
          <label for="owner_name" class="form-label">Carrier / Owner Name <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-user"></i></span>
            <input
              id="owner_name"
              type="text"
              class="form-control @error('owner_name') is-invalid @enderror"
              name="owner_name"
              value="{{ old('owner_name', $dispatcher->owner_name) }}"
              required
            />
          </div>
          @error('owner_name')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-4">
          <label for="load_number" class="form-label">Load Reference # <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-barcode"></i></span>
            <input
              id="load_number"
              type="text"
              class="form-control @error('load_number') is-invalid @enderror"
              name="load_number"
              value="{{ old('load_number', $dispatcher->load_number) }}"
              required
            />
          </div>
          @error('load_number')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>
  </div>

  <!-- Section 2: Routing & Schedule -->
  <div class="card shadow-sm mb-4">
    <div class="card-header border-bottom py-3 d-flex align-items-center">
      <i class="ti ti-route me-2 text-primary fs-4"></i>
      <h5 class="card-title mb-0">Route & Schedule</h5>
    </div>
    <div class="card-body pt-4">
      <div class="row g-3">
        <div class="col-md-6">
          <label for="pick_location" class="form-label">Origin / Pickup Location <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-map-pin"></i></span>
            <input
              id="pick_location"
              type="text"
              class="form-control @error('pick_location') is-invalid @enderror"
              name="pick_location"
              value="{{ old('pick_location', $dispatcher->pick_location) }}"
              required
            />
          </div>
          @error('pick_location')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-6">
          <label for="delivery_location" class="form-label">Destination / Delivery Location <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-map-pin-check"></i></span>
            <input
              id="delivery_location"
              type="text"
              class="form-control @error('delivery_location') is-invalid @enderror"
              name="delivery_location"
              value="{{ old('delivery_location', $dispatcher->delivery_location) }}"
              required
            />
          </div>
          @error('delivery_location')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-4">
          <label for="load_date" class="form-label">Load Date <span class="text-danger">*</span></label>
          @can('edit-load-date')
            <input
              id="load_date"
              type="date"
              class="form-control @error('load_date') is-invalid @enderror"
              name="load_date"
              value="{{ old('load_date', $dispatcher->load_date) }}"
              required
            />
          @else
            <input
              id="load_date"
              type="date"
              class="form-control bg-light"
              name="load_date"
              value="{{ $dispatcher->load_date }}"
              readonly
            />
          @endcan
          @error('load_date')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-4">
          <label for="pick_date" class="form-label">Pick Date <span class="text-danger">*</span></label>
          <input
            id="pick_date"
            type="date"
            class="form-control @error('pick_date') is-invalid @enderror"
            name="pick_date"
            value="{{ old('pick_date', $dispatcher->pick_date) }}"
            required
          />
          @error('pick_date')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-4">
          <label for="delivery_date" class="form-label">Delivery Date</label>
          <input
            id="delivery_date"
            type="date"
            class="form-control @error('delivery_date') is-invalid @enderror"
            name="delivery_date"
            value="{{ old('delivery_date', $dispatcher->delivery_date) }}"
          />
          @error('delivery_date')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>
  </div>

  <!-- Section 3: Driver & Equipment -->
  <div class="card shadow-sm mb-4">
    <div class="card-header border-bottom py-3 d-flex align-items-center">
      <i class="ti ti-steering-wheel me-2 text-primary fs-4"></i>
      <h5 class="card-title mb-0">Driver & Assigned Equipment</h5>
    </div>
    <div class="card-body pt-4">
      <div class="row g-3">
        <div class="col-md-3">
          <label for="driver_name" class="form-label">Driver Name <span class="text-danger">*</span></label>
          <input
            id="driver_name"
            type="text"
            class="form-control @error('driver_name') is-invalid @enderror"
            name="driver_name"
            value="{{ old('driver_name', $dispatcher->driver_name) }}"
            required
          />
          @error('driver_name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-3">
          <label for="driver_number" class="form-label">Driver Phone <span class="text-danger">*</span></label>
          <input
            id="driver_number"
            type="text"
            class="form-control @error('driver_number') is-invalid @enderror"
            name="driver_number"
            value="{{ old('driver_number', $dispatcher->driver_number) }}"
            required
          />
          @error('driver_number')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-3">
          <label for="truck_number" class="form-label">Truck Number <span class="text-danger">*</span></label>
          <input
            id="truck_number"
            type="text"
            class="form-control @error('truck_number') is-invalid @enderror"
            name="truck_number"
            value="{{ old('truck_number', $dispatcher->truck_number) }}"
            required
          />
          @error('truck_number')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-3">
          <label for="trailer_number" class="form-label">Trailer Number</label>
          <input
            id="trailer_number"
            type="text"
            class="form-control @error('trailer_number') is-invalid @enderror"
            name="trailer_number"
            value="{{ old('trailer_number', $dispatcher->trailer_number) }}"
          />
          @error('trailer_number')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>
  </div>

  <!-- Section 4: Financials & Mileage -->
  <div class="card shadow-sm mb-4">
    <div class="card-header border-bottom py-3 d-flex align-items-center">
      <i class="ti ti-calculator me-2 text-primary fs-4"></i>
      <h5 class="card-title mb-0">Financials & Commission</h5>
    </div>
    <div class="card-body pt-4">
      <div class="row g-3">
        <div class="col-md-3">
          <label for="total_miles" class="form-label">Total Miles <span class="text-danger">*</span></label>
          <div class="input-group">
            <input
              id="total_miles"
              type="number"
              step="any"
              class="form-control @error('total_miles') is-invalid @enderror"
              name="total_miles"
              value="{{ old('total_miles', $dispatcher->total_miles) }}"
              required
            />
            <span class="input-group-text">mi</span>
          </div>
          @error('total_miles')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-3">
          <label for="rate" class="form-label">Gross Rate ($) <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input
              id="rate"
              type="number"
              step="0.01"
              class="form-control @error('rate') is-invalid @enderror"
              name="rate"
              value="{{ old('rate', $dispatcher->rate) }}"
              required
            />
          </div>
          @error('rate')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-3" id="hide_percentage">
          <label for="percentage" class="form-label">Commission Percentage (%)</label>
          <div class="input-group">
            <input
              id="percentage"
              type="number"
              step="0.01"
              class="form-control @error('percentage') is-invalid @enderror"
              name="percentage"
              value="{{ old('percentage', $dispatcher->percentage) }}"
            />
            <span class="input-group-text">%</span>
          </div>
          @error('percentage')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-3">
          <label for="receivable" class="form-label">Receivable Amount ($)</label>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input
              id="receivable"
              type="number"
              step="0.01"
              class="form-control @error('receivable') is-invalid @enderror"
              name="receivable"
              value="{{ old('receivable', $dispatcher->receivable) }}"
            />
          </div>
          @error('receivable')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>
  </div>

  <!-- Section 5: Broker Details -->
  <div class="card shadow-sm mb-4">
    <div class="card-header border-bottom py-3 d-flex align-items-center">
      <i class="ti ti-briefcase me-2 text-primary fs-4"></i>
      <h5 class="card-title mb-0">Broker Information</h5>
    </div>
    <div class="card-body pt-4">
      <div class="row g-3">
        <div class="col-md-4">
          <label for="broker_company_name" class="form-label">Broker Company <span class="text-danger">*</span></label>
          <input
            id="broker_company_name"
            type="text"
            class="form-control @error('broker_company_name') is-invalid @enderror"
            name="broker_company_name"
            value="{{ old('broker_company_name', $dispatcher->broker_company_name) }}"
            required
          />
          @error('broker_company_name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-4">
          <label for="broker_mc" class="form-label">Broker MC # <span class="text-danger">*</span></label>
          <input
            id="broker_mc"
            type="text"
            class="form-control @error('broker_mc') is-invalid @enderror"
            name="broker_mc"
            value="{{ old('broker_mc', $dispatcher->broker_mc) }}"
            required
          />
          @error('broker_mc')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-4">
          <label for="broker_number" class="form-label">Broker Phone <span class="text-danger">*</span></label>
          <input
            id="broker_number"
            type="text"
            class="form-control @error('broker_number') is-invalid @enderror"
            name="broker_number"
            value="{{ old('broker_number', $dispatcher->broker_number) }}"
            required
          />
          @error('broker_number')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-6">
          <label for="broker_email" class="form-label">Broker Email <span class="text-danger">*</span></label>
          <input
            id="broker_email"
            type="email"
            class="form-control @error('broker_email') is-invalid @enderror"
            name="broker_email"
            value="{{ old('broker_email', $dispatcher->broker_email) }}"
            required
          />
          @error('broker_email')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-6">
          <label for="broker_rep_name" class="form-label">Representative / Agent Name <span class="text-danger">*</span></label>
          <input
            id="broker_rep_name"
            type="text"
            class="form-control @error('broker_rep_name') is-invalid @enderror"
            name="broker_rep_name"
            value="{{ old('broker_rep_name', $dispatcher->broker_rep_name) }}"
            required
          />
          @error('broker_rep_name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>
  </div>

  <!-- Section 6: Documents -->
  <div class="card shadow-sm mb-4">
    <div class="card-header border-bottom py-3 d-flex align-items-center">
      <i class="ti ti-file-text me-2 text-primary fs-4"></i>
      <h5 class="card-title mb-0">Load Documentation</h5>
    </div>
    <div class="card-body pt-4">
      <div class="row g-3">
        <div class="col-md-4">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="rate_confirmation" class="form-label mb-0">Rate Confirmation</label>
            @if($dispatcher->rate_confirmation)
              <a href="{{ asset('dispatcher_img/' . $dispatcher->rate_confirmation) }}" target="_blank" class="badge bg-label-info text-decoration-none">
                <i class="ti ti-paperclip me-1"></i> Current File
              </a>
            @endif
          </div>
          <input id="rate_confirmation" type="file" class="form-control @error('rate_confirmation') is-invalid @enderror" name="rate_confirmation">
          @error('rate_confirmation')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <small class="text-muted">Choose new file to replace</small>
        </div>

        <div class="col-md-4">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="bol_pod" class="form-label mb-0">BOL / POD (Bill of Lading)</label>
            @if($dispatcher->bol_pod)
              <a href="{{ asset('dispatcher_img/' . $dispatcher->bol_pod) }}" target="_blank" class="badge bg-label-info text-decoration-none">
                <i class="ti ti-paperclip me-1"></i> Current File
              </a>
            @endif
          </div>
          <input id="bol_pod" type="file" class="form-control @error('bol_pod') is-invalid @enderror" name="bol_pod">
          @error('bol_pod')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <small class="text-muted">Choose new file to replace</small>
        </div>

        <div class="col-md-4">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="additional_doc" class="form-label mb-0">Additional Documents</label>
            @if($dispatcher->additional_doc)
              <a href="{{ asset('dispatcher_img/' . $dispatcher->additional_doc) }}" target="_blank" class="badge bg-label-info text-decoration-none">
                <i class="ti ti-paperclip me-1"></i> Current File
              </a>
            @endif
          </div>
          <input id="additional_doc" type="file" class="form-control @error('additional_doc') is-invalid @enderror" name="additional_doc">
          @error('additional_doc')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <small class="text-muted">Choose new file to replace</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Section 7: Notes -->
  <div class="card shadow-sm mb-4">
    <div class="card-header border-bottom py-3 d-flex align-items-center">
      <i class="ti ti-notes me-2 text-primary fs-4"></i>
      <h5 class="card-title mb-0">Special Instructions & Comments</h5>
    </div>
    <div class="card-body pt-4">
      <textarea
        id="comment"
        rows="3"
        class="form-control @error('comment') is-invalid @enderror"
        name="comment"
      >{{ old('comment', $dispatcher->comment) }}</textarea>
      @error('comment')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <!-- Action buttons -->
  <div class="d-flex justify-content-end gap-2 mb-4">
    <a href="{{ route('dispatchers.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">
      <i class="ti ti-device-floppy me-1"></i> Update Dispatch Load
    </button>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const rateInput = document.getElementById('rate');
  const percentageInput = document.getElementById('percentage');
  const receivableInput = document.getElementById('receivable');

  function calculateReceivable() {
    const rate = parseFloat(rateInput.value) || 0;
    const pct = parseFloat(percentageInput.value) || 0;
    if (rate > 0 && pct > 0) {
      receivableInput.value = ((rate * pct) / 100).toFixed(2);
    }
  }

  if (rateInput && percentageInput && receivableInput) {
    rateInput.addEventListener('input', calculateReceivable);
    percentageInput.addEventListener('input', calculateReceivable);
  }
});
</script>
@endsection
