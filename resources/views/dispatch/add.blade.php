@extends('layouts.master')

@section('title', 'Add Dispatch Load')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Add Dispatch Load</h4>
    <p class="text-muted mb-0">Record load routing, driver assignment, broker rate confirmation, and financials</p>
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

<form method="POST" action="{{ route('dispatchers.store') }}" enctype="multipart/form-data">
  @csrf

  <!-- Section 1: Carrier & Load Identification -->
  <div class="card shadow-sm mb-4">
    <div class="card-header border-bottom py-3 d-flex align-items-center">
      <i class="ti ti-truck-delivery me-2 text-primary fs-4"></i>
      <h5 class="card-title mb-0">Load & Carrier Identification</h5>
    </div>
    <div class="card-body pt-4">
      <div class="row g-3">
        <div class="col-md-3">
          <label for="mc_number_class" class="form-label">Carrier MC Number <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-hash"></i></span>
            <input
              id="mc_number_class"
              type="text"
              class="form-control @error('mc_number') is-invalid @enderror"
              name="mc_number"
              value="{{ old('mc_number') }}"
              placeholder="e.g. 123456"
              required
              autofocus
            />
          </div>
          @error('mc_number')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-3">
          <label for="owner_name" class="form-label">Carrier / Owner Name <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-user"></i></span>
            <input
              id="owner_name"
              type="text"
              class="form-control @error('owner_name') is-invalid @enderror"
              name="owner_name"
              value="{{ old('owner_name') }}"
              placeholder="Carrier Name"
              required
            />
          </div>
          @error('owner_name')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-3">
          <label for="load_number" class="form-label">Load Reference # <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-barcode"></i></span>
            <input
              id="load_number"
              type="text"
              class="form-control @error('load_number') is-invalid @enderror"
              name="load_number"
              value="{{ old('load_number') }}"
              placeholder="e.g. LD-8890"
              required
            />
          </div>
          @error('load_number')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-3">
          <label for="supervisor_id" class="form-label">Assign Supervisor <span class="text-muted small">(Optional)</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-user-check"></i></span>
            <select
              id="supervisor_id"
              name="supervisor_id"
              class="form-select @error('supervisor_id') is-invalid @enderror"
            >
              <option value="">Default ({{ Auth::user()->first_name }} {{ Auth::user()->last_name }})</option>
              @if(isset($supervisors))
                @foreach($supervisors as $supervisor)
                  <option value="{{ $supervisor->id }}" {{ old('supervisor_id') == $supervisor->id ? 'selected' : '' }}>
                    {{ $supervisor->first_name }} {{ $supervisor->last_name }}
                  </option>
                @endforeach
              @endif
            </select>
          </div>
          @error('supervisor_id')
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
              value="{{ old('pick_location') }}"
              placeholder="City, ST"
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
              value="{{ old('delivery_location') }}"
              placeholder="City, ST"
              required
            />
          </div>
          @error('delivery_location')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-4">
          <label for="load_date" class="form-label">Load Date <span class="text-danger">*</span></label>
          <input
            id="load_date"
            type="date"
            class="form-control @error('load_date') is-invalid @enderror"
            name="load_date"
            value="{{ old('load_date', date('Y-m-d')) }}"
            @cannot('add-previous-load-date') min="{{ date('Y-m-d') }}" @endcannot
            required
          />
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
            value="{{ old('pick_date') }}"
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
            value="{{ old('delivery_date') }}"
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
            value="{{ old('driver_name') }}"
            placeholder="Driver Full Name"
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
            value="{{ old('driver_number') }}"
            placeholder="(888) 000-0000"
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
            value="{{ old('truck_number') }}"
            placeholder="e.g. TRK-101"
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
            value="{{ old('trailer_number') }}"
            placeholder="e.g. TRL-505"
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
              value="{{ old('total_miles') }}"
              placeholder="e.g. 750"
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
              value="{{ old('rate') }}"
              placeholder="e.g. 2400.00"
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
              value="{{ old('percentage') }}"
              placeholder="e.g. 10"
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
              value="{{ old('receivable') }}"
              placeholder="e.g. 240.00"
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
            value="{{ old('broker_company_name') }}"
            placeholder="Freight Brokerage Inc."
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
            value="{{ old('broker_mc') }}"
            placeholder="e.g. MC-654321"
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
            value="{{ old('broker_number') }}"
            placeholder="(800) 555-1234"
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
            value="{{ old('broker_email') }}"
            placeholder="broker@freight.com"
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
            value="{{ old('broker_rep_name') }}"
            placeholder="Agent Full Name"
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
          <label for="rate_confirmation" class="form-label">Rate Confirmation</label>
          <input id="rate_confirmation" type="file" class="form-control @error('rate_confirmation') is-invalid @enderror" name="rate_confirmation">
          @error('rate_confirmation')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <small class="text-muted">PDF or Image</small>
        </div>

        <div class="col-md-4">
          <label for="bol_pod" class="form-label">BOL / POD (Bill of Lading)</label>
          <input id="bol_pod" type="file" class="form-control @error('bol_pod') is-invalid @enderror" name="bol_pod">
          @error('bol_pod')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <small class="text-muted">PDF or Image</small>
        </div>

        <div class="col-md-4">
          <label for="additional_doc" class="form-label">Additional Documents</label>
          <input id="additional_doc" type="file" class="form-control @error('additional_doc') is-invalid @enderror" name="additional_doc">
          @error('additional_doc')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <small class="text-muted">Optional</small>
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
        placeholder="Add any load notes, check-in instructions, or delivery specifications..."
      >{{ old('comment') }}</textarea>
      @error('comment')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <!-- Action buttons -->
  <div class="d-flex justify-content-end gap-2 mb-4">
    <a href="{{ route('dispatchers.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">
      <i class="ti ti-device-floppy me-1"></i> Save Dispatch Load
    </button>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const rateInput = document.getElementById('rate');
  const percentageInput = document.getElementById('percentage');
  const receivableInput = document.getElementById('receivable');
  const mcInput = document.getElementById('mc_number_class');
  const ownerInput = document.getElementById('owner_name');
  const hidePercentage = document.getElementById('hide_percentage');

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

  if (mcInput) {
    mcInput.addEventListener('blur', function() {
      const mc = mcInput.value.trim();
      if (mc.length > 0) {
        fetch('/mc-details?mc_number=' + encodeURIComponent(mc))
          .then(res => res.json())
          .then(data => {
            if (data.status) {
              if (ownerInput && !ownerInput.value) {
                ownerInput.value = data.company_name || data.name;
              }
              if (data.percent_flat === 'flat_rate') {
                if (hidePercentage) hidePercentage.style.display = 'none';
                if (receivableInput && data.charge_type) receivableInput.value = data.charge_type;
              } else {
                if (hidePercentage) hidePercentage.style.display = 'block';
                if (percentageInput && data.charge_type) {
                  percentageInput.value = data.charge_type.replace('%', '');
                  calculateReceivable();
                }
              }
            }
          })
          .catch(err => console.log('MC lookup error:', err));
      }
    });
  }
});
</script>
@endsection
