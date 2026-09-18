@extends('layouts.master')

@section('title', 'Add Carrier')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Add New Carrier</h4>
    <p class="text-muted mb-0">Register a new carrier profile, equipment specifications, and compliance documents</p>
  </div>
  <a href="{{ route('carriers.index') }}" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to Carriers
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

<form method="POST" action="{{ route('carriers.store') }}" enctype="multipart/form-data">
  @csrf

  <!-- Section 1: Basic Information -->
  <div class="card shadow-sm mb-4">
    <div class="card-header border-bottom py-3 d-flex align-items-center">
      <i class="ti ti-truck me-2 text-primary fs-4"></i>
      <h5 class="card-title mb-0">Carrier Information</h5>
    </div>
    <div class="card-body pt-4">
      <div class="row g-3">
        @role('Admin')
        <div class="col-md-12">
          <label for="agent_id" class="form-label">Sales Agent <span class="text-danger">*</span></label>
          <select id="agent_id" class="form-select @error('agent_id') is-invalid @enderror" name="agent_id" required>
            <option value="">Choose Sales Agent...</option>
            @foreach($salesAgnets as $saleAgent)
              <option value="{{ $saleAgent->id }}" @selected(old('agent_id') == $saleAgent->id)>{{ $saleAgent->first_name }} {{ $saleAgent->last_name }}</option>
            @endforeach
          </select>
          @error('agent_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        @endrole

        <div class="col-md-4">
          <label for="mc_number" class="form-label">MC Number <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-hash"></i></span>
            <input
              id="mc_number"
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

        <div class="col-md-4">
          <label for="dot" class="form-label">DOT Number</label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-id"></i></span>
            <input
              id="dot"
              type="text"
              class="form-control @error('dot') is-invalid @enderror"
              name="dot"
              value="{{ old('dot') }}"
              placeholder="e.g. 987654"
            />
          </div>
          @error('dot')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-4">
          <label for="name" class="form-label">Carrier Contact Name <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-user"></i></span>
            <input
              id="name"
              type="text"
              class="form-control @error('name') is-invalid @enderror"
              name="name"
              value="{{ old('name') }}"
              placeholder="Contact Person Name"
              required
            />
          </div>
          @error('name')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-4">
          <label for="email" class="form-label">Carrier Email <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-mail"></i></span>
            <input
              id="email"
              type="email"
              class="form-control @error('email') is-invalid @enderror"
              name="email"
              value="{{ old('email') }}"
              placeholder="carrier@example.com"
              required
            />
          </div>
          @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-4">
          <label for="number" class="form-label">Contact Number <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-phone"></i></span>
            <input
              id="number"
              type="text"
              class="form-control @error('number') is-invalid @enderror"
              name="number"
              value="{{ old('number') }}"
              placeholder="(888) 123-4567"
              required
            />
          </div>
          @error('number')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-4">
          <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-building"></i></span>
            <input
              id="company_name"
              type="text"
              class="form-control @error('company_name') is-invalid @enderror"
              name="company_name"
              value="{{ old('company_name') }}"
              placeholder="Logistics / Trucking Co."
              required
            />
          </div>
          @error('company_name')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>
  </div>

  <!-- Section 2: Equipment & Pricing -->
  <div class="card shadow-sm mb-4">
    <div class="card-header border-bottom py-3 d-flex align-items-center">
      <i class="ti ti-settings me-2 text-primary fs-4"></i>
      <h5 class="card-title mb-0">Equipment & Rates</h5>
    </div>
    <div class="card-body pt-4">
      <div class="row g-3">
        <div class="col-md-4">
          <label for="truck_type" class="form-label">Truck Type <span class="text-danger">*</span></label>
          <select id="truck_type" class="form-select @error('truck_type') is-invalid @enderror" name="truck_type" required>
            <option value="">Choose Truck Type...</option>
            @foreach($truckTypes as $truckType)
              <option value="{{ $truckType->id }}" @selected(old('truck_type') == $truckType->id)>{{ $truckType->name }}</option>
            @endforeach
          </select>
          @error('truck_type')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-4">
          <label for="truck_size" class="form-label">Truck Size <span class="text-danger">*</span></label>
          <select id="truck_size" class="form-select @error('truck_size') is-invalid @enderror" name="truck_size" required>
            <option value="">Choose Truck Size...</option>
            @foreach($truckSizes as $truckSize)
              <option value="{{ $truckSize->id }}" @selected(old('truck_size') == $truckSize->id)>{{ $truckSize->name }}</option>
            @endforeach
          </select>
          @error('truck_size')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-4">
          <label for="maximum_weight" class="form-label">Maximum Weight (lbs) <span class="text-danger">*</span></label>
          <div class="input-group">
            <input
              id="maximum_weight"
              type="number"
              class="form-control @error('maximum_weight') is-invalid @enderror"
              name="maximum_weight"
              value="{{ old('maximum_weight') }}"
              placeholder="e.g. 45000"
              required
            />
            <span class="input-group-text">lbs</span>
          </div>
          @error('maximum_weight')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-3">
          <label for="payment_type" class="form-label">Payment Type <span class="text-danger">*</span></label>
          <select id="payment_type" class="form-select @error('payment_type') is-invalid @enderror" name="payment_type" required>
            <option value="">Choose Payment Type...</option>
            @foreach($paymentTypes as $paymentType)
              <option value="{{ $paymentType->id }}" @selected(old('payment_type') == $paymentType->id)>{{ $paymentType->name }}</option>
            @endforeach
          </select>
          @error('payment_type')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-3">
          <label for="percent_flat" class="form-label">Percentage / Flat Rate <span class="text-danger">*</span></label>
          <select id="percent_flat" class="form-select @error('percent_flat') is-invalid @enderror" name="percent_flat" required>
            <option value="">Select Rate Type...</option>
            <option value="percentage" @selected(old('percent_flat') === 'percentage')>Percentage (%)</option>
            <option value="flat_rate" @selected(old('percent_flat') === 'flat_rate')>Flat Rate ($)</option>
          </select>
          @error('percent_flat')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-3">
          <label for="charge_type" class="form-label">Charge Rate / Value <span class="text-danger">*</span></label>
          <input
            id="charge_type"
            type="text"
            class="form-control @error('charge_type') is-invalid @enderror"
            name="charge_type"
            value="{{ old('charge_type') }}"
            placeholder="e.g. 10% or 150"
            required
          />
          @error('charge_type')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-3">
          <label for="rpm" class="form-label">Expected RPM ($/mile) <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input
              id="rpm"
              type="text"
              class="form-control @error('rpm') is-invalid @enderror"
              name="rpm"
              value="{{ old('rpm') }}"
              placeholder="e.g. 2.50"
              required
            />
          </div>
          @error('rpm')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>
  </div>

  <!-- Section 3: Operating Zones -->
  <div class="card shadow-sm mb-4">
    <div class="card-header border-bottom py-3 d-flex align-items-center">
      <i class="ti ti-map-pins me-2 text-primary fs-4"></i>
      <h5 class="card-title mb-0">Preferred Operating Zones</h5>
    </div>
    <div class="card-body pt-4">
      <div class="mb-3">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="all_zones" name="all_zones" @checked(old('all_zones'))>
          <label class="form-check-label fw-bold" for="all_zones">Select All Zones (Nationwide)</label>
        </div>
      </div>
      <div class="row g-3">
        @for($i = 0; $i <= 9; $i++)
          <div class="col-6 col-sm-4 col-md-2">
            <div class="form-check form-switch">
              <input
                class="form-check-input zone-checkbox"
                type="checkbox"
                id="z{{ $i }}"
                name="z{{ $i }}"
                @checked(old('z'.$i))
              >
              <label class="form-check-label" for="z{{ $i }}">Zone {{ $i }}</label>
            </div>
          </div>
        @endfor
      </div>
    </div>
  </div>

  <!-- Section 4: Address Details -->
  <div class="card shadow-sm mb-4">
    <div class="card-header border-bottom py-3 d-flex align-items-center">
      <i class="ti ti-map-pin me-2 text-primary fs-4"></i>
      <h5 class="card-title mb-0">Address Information</h5>
    </div>
    <div class="card-body pt-4">
      <div class="row g-3">
        <div class="col-md-8">
          <label for="street_address" class="form-label">Street Address <span class="text-danger">*</span></label>
          <input
            id="street_address"
            type="text"
            class="form-control @error('street_address') is-invalid @enderror"
            name="street_address"
            value="{{ old('street_address') }}"
            placeholder="1234 Main St, Suite 100"
            required
          />
          @error('street_address')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-4">
          <label for="city_name" class="form-label">City <span class="text-danger">*</span></label>
          <input
            id="city_name"
            type="text"
            class="form-control @error('city_name') is-invalid @enderror"
            name="city_name"
            value="{{ old('city_name') }}"
            placeholder="City"
            required
          />
          @error('city_name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-6">
          <label for="state_name" class="form-label">State <span class="text-danger">*</span></label>
          <select class="form-select @error('state_name') is-invalid @enderror" name="state_name" id="state_name" required>
            <option value="">Choose State...</option>
            @foreach($states as $state)
              <option value="{{ $state->id }}" @selected(old('state_name') == $state->id)>{{ $state->name }}</option>
            @endforeach
          </select>
          @error('state_name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-6">
          <label for="zip_code" class="form-label">Zip Code <span class="text-danger">*</span></label>
          <input
            id="zip_code"
            type="text"
            class="form-control @error('zip_code') is-invalid @enderror"
            name="zip_code"
            value="{{ old('zip_code') }}"
            placeholder="e.g. 75001"
            required
          />
          @error('zip_code')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>
  </div>

  <!-- Section 5: Documents & Compliance -->
  <div class="card shadow-sm mb-4">
    <div class="card-header border-bottom py-3 d-flex align-items-center">
      <i class="ti ti-file-text me-2 text-primary fs-4"></i>
      <h5 class="card-title mb-0">Documents & Compliance</h5>
    </div>
    <div class="card-body pt-4">
      <div class="row g-3">
        <div class="col-md-4">
          <label for="mc_letter" class="form-label">MC Authority Letter</label>
          <input id="mc_letter" type="file" class="form-control @error('mc_letter') is-invalid @enderror" name="mc_letter">
          @error('mc_letter')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <small class="text-muted">PDF, PNG, JPG accepted</small>
        </div>

        <div class="col-md-4">
          <label for="w_form" class="form-label">W-9 Form (Tax ID Proof)</label>
          <input id="w_form" type="file" class="form-control @error('w_form') is-invalid @enderror" name="w_form">
          @error('w_form')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <small class="text-muted">PDF, PNG, JPG accepted</small>
        </div>

        <div class="col-md-4">
          <label for="coi" class="form-label">COI (Certificate of Insurance)</label>
          <input id="coi" type="file" class="form-control @error('coi') is-invalid @enderror" name="coi">
          @error('coi')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <small class="text-muted">PDF, PNG, JPG accepted</small>
        </div>

        <div class="col-md-4">
          <label for="noa" class="form-label">NOA (Notice of Assignment)</label>
          <input id="noa" type="file" class="form-control @error('noa') is-invalid @enderror" name="noa">
          @error('noa')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <small class="text-muted">Optional</small>
        </div>

        <div class="col-md-4">
          <label for="void_cheque" class="form-label">VOID Cheque</label>
          <input id="void_cheque" type="file" class="form-control @error('void_cheque') is-invalid @enderror" name="void_cheque">
          @error('void_cheque')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <small class="text-muted">Optional</small>
        </div>

        <div class="col-md-4">
          <label for="extra_document" class="form-label">Extra Document</label>
          <input id="extra_document" type="file" class="form-control @error('extra_document') is-invalid @enderror" name="extra_document">
          @error('extra_document')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <small class="text-muted">Optional</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Section 6: Comments & Notes -->
  <div class="card shadow-sm mb-4">
    <div class="card-header border-bottom py-3 d-flex align-items-center">
      <i class="ti ti-notes me-2 text-primary fs-4"></i>
      <h5 class="card-title mb-0">Internal Notes & Remarks</h5>
    </div>
    <div class="card-body pt-4">
      <div class="row">
        <div class="col-md-12">
          <label for="comment" class="form-label">Comments / Dispatch Instructions</label>
          <textarea
            id="comment"
            rows="3"
            class="form-control @error('comment') is-invalid @enderror"
            name="comment"
            placeholder="Add any specific driver preferences, notes, or contact instructions..."
          >{{ old('comment') }}</textarea>
          @error('comment')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>
  </div>

  <!-- Action buttons -->
  <div class="d-flex justify-content-end gap-2 mb-4">
    <a href="{{ route('carriers.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">
      <i class="ti ti-device-floppy me-1"></i> Save Carrier
    </button>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const allZonesCheckbox = document.getElementById('all_zones');
  const zoneCheckboxes = document.querySelectorAll('.zone-checkbox');

  if (allZonesCheckbox) {
    allZonesCheckbox.addEventListener('change', function() {
      zoneCheckboxes.forEach(cb => {
        cb.checked = allZonesCheckbox.checked;
      });
    });
  }
});
</script>
@endsection
