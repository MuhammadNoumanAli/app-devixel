@extends('layouts.master')

@section('title', 'Attach BOL/POD - ' . $dispatcher->load_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Attach BOL / POD: {{ $dispatcher->load_number }}</h4>
    <p class="text-muted mb-0">Upload Bill of Lading or Proof of Delivery for load confirmation</p>
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

<div class="row justify-content-center">
  <div class="col-lg-6 col-md-8">
    <div class="card shadow-sm">
      <div class="card-header border-bottom py-3 d-flex align-items-center">
        <i class="ti ti-upload me-2 text-primary fs-4"></i>
        <h5 class="card-title mb-0">Upload Document</h5>
      </div>
      <div class="card-body pt-4">
        <div class="mb-3 p-3 bg-label-secondary rounded">
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Load Number:</span>
            <span class="fw-bold">{{ $dispatcher->load_number }}</span>
          </div>
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Carrier:</span>
            <span class="fw-semibold">{{ $dispatcher->owner_name }} (MC # {{ $dispatcher->mc_number }})</span>
          </div>
          <div class="d-flex justify-content-between">
            <span class="text-muted">Current BOL/POD:</span>
            <span>
              @if($dispatcher->bol_pod)
                <a href="{{ asset('dispatcher_img/' . $dispatcher->bol_pod) }}" target="_blank" class="badge bg-success">
                  <i class="ti ti-check me-1"></i> Attached
                </a>
              @else
                <span class="badge bg-label-warning">Not Uploaded</span>
              @endif
            </span>
          </div>
        </div>

        <form method="POST" action="{{ route('dispatchers.updateAttachDocument', $dispatcher->id) }}" enctype="multipart/form-data">
          @csrf
          @method('PATCH')

          <div class="mb-4">
            <label for="bol_pod" class="form-label">Select BOL / POD File <span class="text-danger">*</span></label>
            <input
              id="bol_pod"
              type="file"
              class="form-control @error('bol_pod') is-invalid @enderror"
              name="bol_pod"
              required
            />
            @error('bol_pod')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-muted">Accepted formats: PDF, JPG, PNG. Maximum size 10MB.</small>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('dispatchers.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
              <i class="ti ti-upload me-1"></i> Upload Document
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
