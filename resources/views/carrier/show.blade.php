@extends('layouts.master')

@section('title', 'Carrier Details - ' . $carrier->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Carrier Profile: {{ $carrier->name }}</h4>
    <p class="text-muted mb-0">Complete carrier dossier, equipment capabilities, and compliance records</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('carriers.index') }}" class="btn btn-outline-secondary">
      <i class="ti ti-arrow-left me-1"></i> Back to Carriers
    </a>
    @can('carriers-edit')
      <a href="{{ route('carriers.edit', $carrier->id) }}" class="btn btn-primary">
        <i class="ti ti-edit me-1"></i> Edit Carrier
      </a>
    @endcan
  </div>
</div>

<div class="row">
  <!-- Left Column: Carrier Identity & Overview -->
  <div class="col-xl-4 col-lg-5 col-md-5">
    <!-- Carrier Card -->
    <div class="card shadow-sm mb-4">
      <div class="card-body text-center pt-4">
        <div class="avatar avatar-xl mx-auto mb-3">
          <span class="avatar-initial rounded-circle bg-label-primary fs-3 fw-bold">
            {{ strtoupper(substr($carrier->name, 0, 2)) }}
          </span>
        </div>
        <h5 class="mb-1 fw-bold">{{ $carrier->name }}</h5>
        <p class="text-muted mb-3">{{ $carrier->company_name }}</p>
        <div class="d-flex justify-content-center gap-2 mb-3">
          <span class="badge bg-label-primary font-monospace">MC # {{ $carrier->mc_number }}</span>
          @if($carrier->dot)
            <span class="badge bg-label-secondary font-monospace">DOT # {{ $carrier->dot }}</span>
          @endif
        </div>
      </div>
      <div class="card-body border-top">
        <small class="text-muted text-uppercase fw-bold">Contact Details</small>
        <ul class="list-unstyled mb-0 mt-3">
          <li class="d-flex align-items-center mb-3">
            <i class="ti ti-mail text-muted me-2"></i>
            <span class="fw-semibold me-2">Email:</span>
            <a href="mailto:{{ $carrier->email }}">{{ $carrier->email }}</a>
          </li>
          <li class="d-flex align-items-center mb-3">
            <i class="ti ti-phone text-muted me-2"></i>
            <span class="fw-semibold me-2">Phone:</span>
            <a href="tel:{{ $carrier->number }}">{{ $carrier->number }}</a>
          </li>
          <li class="d-flex align-items-center mb-3">
            <i class="ti ti-user-check text-muted me-2"></i>
            <span class="fw-semibold me-2">Sales Agent:</span>
            <span>{{ $carrier->user ? $carrier->user->full_name : 'System' }}</span>
          </li>
          <li class="d-flex align-items-center">
            <i class="ti ti-user-star text-muted me-2"></i>
            <span class="fw-semibold me-2">Assigned To:</span>
            <span>{{ $carrier->assignedTo ? $carrier->assignedTo->full_name : 'Unassigned' }}</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Operating Address Card -->
    <div class="card shadow-sm mb-4">
      <div class="card-header border-bottom py-3">
        <h6 class="card-title mb-0"><i class="ti ti-map-pin me-2 text-primary"></i> Operating Address</h6>
      </div>
      <div class="card-body pt-3">
        <p class="mb-1 fw-semibold">{{ $carrier->street_address }}</p>
        <p class="text-muted mb-0">
          {{ $carrier->city_name }}, {{ $carrier->state->name ?? 'N/A' }} {{ $carrier->zip_code }}
        </p>
      </div>
    </div>
  </div>

  <!-- Right Column: Equipment, Zones, Documents, and Notes -->
  <div class="col-xl-8 col-lg-7 col-md-7">
    <!-- Equipment & Rates -->
    <div class="card shadow-sm mb-4">
      <div class="card-header border-bottom py-3">
        <h5 class="card-title mb-0"><i class="ti ti-truck me-2 text-primary"></i> Equipment & Pricing</h5>
      </div>
      <div class="card-body pt-3">
        <div class="row g-3">
          <div class="col-md-6 col-sm-6">
            <div class="d-flex align-items-center">
              <div class="badge bg-label-info p-2 rounded me-3"><i class="ti ti-truck fs-5"></i></div>
              <div>
                <small class="text-muted d-block">Truck Type</small>
                <span class="fw-bold">{{ $carrier->truckType->name ?? 'N/A' }}</span>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-sm-6">
            <div class="d-flex align-items-center">
              <div class="badge bg-label-info p-2 rounded me-3"><i class="ti ti-dimensions fs-5"></i></div>
              <div>
                <small class="text-muted d-block">Truck Size</small>
                <span class="fw-bold">{{ $carrier->truckSize->name ?? 'N/A' }}</span>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-sm-6">
            <div class="d-flex align-items-center">
              <div class="badge bg-label-warning p-2 rounded me-3"><i class="ti ti-weight fs-5"></i></div>
              <div>
                <small class="text-muted d-block">Maximum Weight</small>
                <span class="fw-bold">{{ number_format($carrier->maximum_weight) }} lbs</span>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-sm-6">
            <div class="d-flex align-items-center">
              <div class="badge bg-label-success p-2 rounded me-3"><i class="ti ti-coin fs-5"></i></div>
              <div>
                <small class="text-muted d-block">Expected RPM</small>
                <span class="fw-bold text-success">${{ number_format($carrier->rpm, 2) }} / mile</span>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-sm-6">
            <div class="d-flex align-items-center">
              <div class="badge bg-label-primary p-2 rounded me-3"><i class="ti ti-credit-card fs-5"></i></div>
              <div>
                <small class="text-muted d-block">Payment Type</small>
                <span class="fw-bold">{{ $carrier->paymentType->name ?? 'N/A' }}</span>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-sm-6">
            <div class="d-flex align-items-center">
              <div class="badge bg-label-secondary p-2 rounded me-3"><i class="ti ti-receipt-2 fs-5"></i></div>
              <div>
                <small class="text-muted d-block">Charge Type / Rate</small>
                <span class="fw-bold">{{ ucfirst($carrier->percent_flat) }}: {{ $carrier->charge_type }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Preferred Zones -->
    <div class="card shadow-sm mb-4">
      <div class="card-header border-bottom py-3">
        <h5 class="card-title mb-0"><i class="ti ti-map-pins me-2 text-primary"></i> Operating Zones</h5>
      </div>
      <div class="card-body pt-3">
        @if($carrier->all_zones)
          <span class="badge bg-success me-2 mb-2 p-2"><i class="ti ti-world me-1"></i> All Zones (Nationwide)</span>
        @endif

        <div class="d-flex flex-wrap gap-2 mt-2">
          @for($i = 0; $i <= 9; $i++)
            @php $z = 'z' . $i; @endphp
            <span class="badge {{ $carrier->$z ? 'bg-primary' : 'bg-label-secondary' }} p-2">
              <i class="ti {{ $carrier->$z ? 'ti-check' : 'ti-x' }} me-1"></i> Zone {{ $i }}
            </span>
          @endfor
        </div>
      </div>
    </div>

    <!-- Documents & Files -->
    <div class="card shadow-sm mb-4">
      <div class="card-header border-bottom py-3">
        <h5 class="card-title mb-0"><i class="ti ti-file-certificate me-2 text-primary"></i> Compliance Documents</h5>
      </div>
      <div class="card-body pt-3">
        <div class="row g-3">
          @php
            $documents = [
              ['title' => 'MC Authority Letter', 'field' => $carrier->mc_letter, 'icon' => 'ti-certificate'],
              ['title' => 'W-9 Form (Tax Proof)', 'field' => $carrier->w_form, 'icon' => 'ti-file-text'],
              ['title' => 'Certificate of Insurance (COI)', 'field' => $carrier->coi, 'icon' => 'ti-shield-check'],
              ['title' => 'Notice of Assignment (NOA)', 'field' => $carrier->noa, 'icon' => 'ti-file-invoice'],
              ['title' => 'VOID Cheque', 'field' => $carrier->void_cheque, 'icon' => 'ti-credit-card'],
              ['title' => 'Extra Document', 'field' => $carrier->extra_document, 'icon' => 'ti-paperclip'],
            ];
          @endphp

          @foreach($documents as $doc)
            <div class="col-md-6">
              <div class="border rounded p-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <i class="ti {{ $doc['icon'] }} fs-3 text-primary me-2"></i>
                  <div>
                    <span class="fw-semibold d-block">{{ $doc['title'] }}</span>
                    <small class="text-muted">{{ $doc['field'] ? 'Uploaded' : 'Not Provided' }}</small>
                  </div>
                </div>
                @if($doc['field'])
                  <a
                    href="{{ asset('carrier_img/' . $doc['field']) }}"
                    target="_blank"
                    download
                    class="btn btn-sm btn-outline-primary"
                  >
                    <i class="ti ti-download me-1"></i> Download
                  </a>
                @else
                  <span class="badge bg-label-secondary">None</span>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <!-- Notes & Comments -->
    <div class="card shadow-sm mb-4">
      <div class="card-header border-bottom py-3">
        <h5 class="card-title mb-0"><i class="ti ti-notes me-2 text-primary"></i> Dispatch Notes & Comments</h5>
      </div>
      <div class="card-body pt-3">
        <p class="mb-0 {{ empty($carrier->comment) ? 'text-muted fst-italic' : '' }}">
          {{ $carrier->comment ?: 'No special dispatch comments or instructions recorded for this carrier.' }}
        </p>
      </div>
    </div>
  </div>
</div>
@endsection
