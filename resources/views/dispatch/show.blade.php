@extends('layouts.master')

@section('title', 'Dispatch Details - ' . $dispatcher->load_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Dispatch Load: {{ $dispatcher->load_number }}</h4>
    <p class="text-muted mb-0">Complete shipment dossier, transit route, financials, and proof documents</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('dispatchers.index') }}" class="btn btn-outline-secondary">
      <i class="ti ti-arrow-left me-1"></i> Back to Dispatches
    </a>
    @can('dispatchers-edit')
      <a href="{{ route('dispatchers.edit', $dispatcher->id) }}" class="btn btn-primary">
        <i class="ti ti-edit me-1"></i> Edit Dispatch
      </a>
    @endcan
  </div>
</div>

<div class="row">
  <!-- Left Column: Load Summary & Route Card -->
  <div class="col-xl-4 col-lg-5 col-md-5">
    <div class="card shadow-sm mb-4">
      <div class="card-body text-center pt-4">
        <div class="avatar avatar-xl mx-auto mb-3">
          <span class="avatar-initial rounded-circle bg-label-info fs-3 fw-bold">
            <i class="ti ti-truck-delivery fs-2"></i>
          </span>
        </div>
        <h5 class="mb-1 fw-bold">{{ $dispatcher->load_number }}</h5>
        <p class="text-muted mb-3">{{ $dispatcher->owner_name }}</p>
        <span class="badge bg-label-primary font-monospace fs-6">MC # {{ $dispatcher->mc_number }}</span>
      </div>
      <div class="card-body border-top">
        <small class="text-muted text-uppercase fw-bold">Route Overview</small>
        <div class="mt-3">
          <div class="d-flex mb-3">
            <div class="me-3 text-center">
              <i class="ti ti-circle-filled text-success fs-5"></i>
              <div class="border-start border-2 h-100 mx-auto" style="min-height: 30px;"></div>
            </div>
            <div>
              <small class="text-muted d-block">Origin / Pickup</small>
              <span class="fw-bold">{{ $dispatcher->pick_location }}</span>
              <small class="text-muted d-block">{{ date('M d, Y', strtotime($dispatcher->pick_date)) }}</small>
            </div>
          </div>
          <div class="d-flex">
            <div class="me-3 text-center">
              <i class="ti ti-map-pin text-danger fs-5"></i>
            </div>
            <div>
              <small class="text-muted d-block">Destination / Delivery</small>
              <span class="fw-bold">{{ $dispatcher->delivery_location }}</span>
              <small class="text-muted d-block">
                {{ $dispatcher->delivery_date ? date('M d, Y', strtotime($dispatcher->delivery_date)) : 'TBD' }}
              </small>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body border-top">
        <small class="text-muted text-uppercase fw-bold">Driver & Equipment</small>
        <ul class="list-unstyled mb-0 mt-3">
          <li class="d-flex align-items-center mb-2">
            <i class="ti ti-user text-muted me-2"></i>
            <span class="fw-semibold me-2">Driver:</span>
            <span>{{ $dispatcher->driver_name }}</span>
          </li>
          <li class="d-flex align-items-center mb-2">
            <i class="ti ti-phone text-muted me-2"></i>
            <span class="fw-semibold me-2">Phone:</span>
            <a href="tel:{{ $dispatcher->driver_number }}">{{ $dispatcher->driver_number }}</a>
          </li>
          <li class="d-flex align-items-center mb-2">
            <i class="ti ti-truck text-muted me-2"></i>
            <span class="fw-semibold me-2">Truck #:</span>
            <span>{{ $dispatcher->truck_number }}</span>
          </li>
          <li class="d-flex align-items-center">
            <i class="ti ti-trailer text-muted me-2"></i>
            <span class="fw-semibold me-2">Trailer #:</span>
            <span>{{ $dispatcher->trailer_number ?: 'N/A' }}</span>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Right Column: Financials, Broker, Documents, and Notes -->
  <div class="col-xl-8 col-lg-7 col-md-7">
    <!-- Financial Breakdown Card -->
    <div class="card shadow-sm mb-4">
      <div class="card-header border-bottom py-3">
        <h5 class="card-title mb-0"><i class="ti ti-calculator me-2 text-primary"></i> Financials & Mileage</h5>
      </div>
      <div class="card-body pt-3">
        <div class="row g-3">
          <div class="col-md-3 col-6">
            <div class="p-3 bg-label-secondary rounded text-center">
              <small class="text-muted d-block">Total Distance</small>
              <h5 class="mb-0 fw-bold">{{ number_format($dispatcher->total_miles) }} mi</h5>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="p-3 bg-label-primary rounded text-center">
              <small class="text-muted d-block">Gross Rate</small>
              <h5 class="mb-0 fw-bold text-primary">${{ number_format($dispatcher->rate, 2) }}</h5>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="p-3 bg-label-info rounded text-center">
              <small class="text-muted d-block">Commission %</small>
              <h5 class="mb-0 fw-bold text-info">{{ $dispatcher->percentage }}%</h5>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="p-3 bg-label-success rounded text-center">
              <small class="text-muted d-block">Receivable</small>
              <h5 class="mb-0 fw-bold text-success">${{ number_format($dispatcher->receivable, 2) }}</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Broker Information Card -->
    <div class="card shadow-sm mb-4">
      <div class="card-header border-bottom py-3">
        <h5 class="card-title mb-0"><i class="ti ti-briefcase me-2 text-primary"></i> Broker Details</h5>
      </div>
      <div class="card-body pt-3">
        <div class="row g-3">
          <div class="col-md-6">
            <small class="text-muted d-block">Brokerage Company</small>
            <span class="fw-bold">{{ $dispatcher->broker_company_name }}</span>
          </div>
          <div class="col-md-6">
            <small class="text-muted d-block">Broker MC #</small>
            <span class="badge bg-label-secondary font-monospace">{{ $dispatcher->broker_mc }}</span>
          </div>
          <div class="col-md-6">
            <small class="text-muted d-block">Representative Name</small>
            <span class="fw-semibold">{{ $dispatcher->broker_rep_name }}</span>
          </div>
          <div class="col-md-6">
            <small class="text-muted d-block">Broker Contact Phone</small>
            <a href="tel:{{ $dispatcher->broker_number }}">{{ $dispatcher->broker_number }}</a>
          </div>
          <div class="col-md-12">
            <small class="text-muted d-block">Broker Email</small>
            <a href="mailto:{{ $dispatcher->broker_email }}">{{ $dispatcher->broker_email }}</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Documents & Files -->
    <div class="card shadow-sm mb-4">
      <div class="card-header border-bottom py-3">
        <h5 class="card-title mb-0"><i class="ti ti-file-text me-2 text-primary"></i> Load Documents</h5>
      </div>
      <div class="card-body pt-3">
        <div class="row g-3">
          @php
            $docs = [
              ['title' => 'Rate Confirmation', 'file' => $dispatcher->rate_confirmation, 'icon' => 'ti-file-invoice'],
              ['title' => 'BOL / POD (Proof of Delivery)', 'file' => $dispatcher->bol_pod, 'icon' => 'ti-file-certificate'],
              ['title' => 'Additional Document', 'file' => $dispatcher->additional_doc, 'icon' => 'ti-paperclip'],
            ];
          @endphp

          @foreach($docs as $doc)
            <div class="col-md-4">
              <div class="border rounded p-3 text-center">
                <i class="ti {{ $doc['icon'] }} fs-2 text-primary mb-2"></i>
                <h6 class="fw-semibold mb-1 fs-6">{{ $doc['title'] }}</h6>
                @if($doc['file'])
                  <a
                    href="{{ asset('dispatcher_img/' . $doc['file']) }}"
                    target="_blank"
                    download
                    class="btn btn-sm btn-outline-primary mt-2"
                  >
                    <i class="ti ti-download me-1"></i> Download
                  </a>
                @else
                  <span class="badge bg-label-secondary mt-2">Not Uploaded</span>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <!-- Notes & Instructions -->
    <div class="card shadow-sm mb-4">
      <div class="card-header border-bottom py-3">
        <h5 class="card-title mb-0"><i class="ti ti-notes me-2 text-primary"></i> Dispatch Notes</h5>
      </div>
      <div class="card-body pt-3">
        <p class="mb-0 {{ empty($dispatcher->comment) ? 'text-muted fst-italic' : '' }}">
          {{ $dispatcher->comment ?: 'No additional notes or special instructions for this load.' }}
        </p>
      </div>
    </div>
  </div>
</div>
@endsection
