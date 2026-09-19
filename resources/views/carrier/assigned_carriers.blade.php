@extends('layouts.master')

@section('title', 'Assigned Carriers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-1">Assigned Carriers</h4>
    <p class="text-muted mb-0">Overview of all carriers assigned to dispatchers with real-time assignment tracking</p>
  </div>
  <div class="d-flex gap-2">
    @can('open-leads-list')
      <a href="{{ route('carriers.openLeads') }}" class="btn btn-outline-primary">
        <i class="ti ti-messages me-1"></i> Open Leads Dashboard
      </a>
    @endcan
    @can('carriers-create')
      <a href="{{ route('carriers.create') }}" class="btn btn-primary">
        <i class="ti ti-plus me-1"></i> Add Carrier
      </a>
    @endcan
  </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-body d-flex align-items-center">
        <div class="avatar avatar-md me-3 bg-label-primary rounded p-2">
          <i class="ti ti-truck-delivery fs-3"></i>
        </div>
        <div>
          <span class="text-muted small">Total Assigned</span>
          <h4 class="fw-bold mb-0 text-primary">{{ number_format($stats['total_assigned'] ?? 0) }}</h4>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-body d-flex align-items-center">
        <div class="avatar avatar-md me-3 bg-label-warning rounded p-2">
          <i class="ti ti-clock-pause fs-3"></i>
        </div>
        <div>
          <span class="text-muted small">Pending / New</span>
          <h4 class="fw-bold mb-0 text-warning">{{ number_format($stats['pending'] ?? 0) }}</h4>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-body d-flex align-items-center">
        <div class="avatar avatar-md me-3 bg-label-info rounded p-2">
          <i class="ti ti-progress fs-3"></i>
        </div>
        <div>
          <span class="text-muted small">In Progress</span>
          <h4 class="fw-bold mb-0 text-info">{{ number_format($stats['in_progress'] ?? 0) }}</h4>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-body d-flex align-items-center">
        <div class="avatar avatar-md me-3 bg-label-success rounded p-2">
          <i class="ti ti-circle-check fs-3"></i>
        </div>
        <div>
          <span class="text-muted small">Completed (Done)</span>
          <h4 class="fw-bold mb-0 text-success">{{ number_format($stats['done'] ?? 0) }}</h4>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filters Card -->
<div class="card shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('carriers.assigned') }}" id="assignedFilterForm" class="row g-3 align-items-end">
      <div class="{{ $isAdmin ? 'col-md-3' : 'col-md-4' }} col-sm-6">
        <label class="form-label small fw-semibold">Search Carrier</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="ti ti-search"></i></span>
          <input type="text" name="search" class="form-control form-control-sm" placeholder="MC#, DOT, Name, Company..." value="{{ request('search') }}">
        </div>
      </div>

      @if($isAdmin)
      <div class="col-md-2 col-sm-6">
        <label class="form-label small fw-semibold">Dispatcher</label>
        <select name="dispatcher_id" class="form-select form-select-sm">
          <option value="">All Dispatchers</option>
          @foreach($dispatchers as $dispatcher)
            <option value="{{ $dispatcher->id }}" @selected(request('dispatcher_id') == $dispatcher->id)>
              {{ $dispatcher->full_name }}
            </option>
          @endforeach
        </select>
      </div>
      @endif

      <div class="col-md-2 col-sm-6">
        <label class="form-label small fw-semibold">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">All Statuses</option>
          <option value="pending" @selected(request('status') == 'pending')>Pending (New)</option>
          <option value="in_progress" @selected(request('status') == 'in_progress')>In Progress</option>
          <option value="not_responding" @selected(request('status') == 'not_responding')>Not Responding</option>
          <option value="documents_required" @selected(request('status') == 'documents_required')>Documents Needed</option>
          <option value="done" @selected(request('status') == 'done')>Done</option>
        </select>
      </div>

      <div class="{{ $isAdmin ? 'col-md-3' : 'col-md-4' }} col-sm-6">
        <label class="form-label small fw-semibold">Date Range</label>
        <input type="hidden" id="start_date_assigned" name="start_date" value="{{ request('start_date') }}" />
        <input type="hidden" id="end_date_assigned" name="end_date" value="{{ request('end_date') }}" />
        <div id="reportrange_assigned" class="form-control form-control-sm bg-white d-flex align-items-center justify-content-between" style="cursor: pointer;">
          <span class="small text-truncate"></span>
          <i class="ti ti-calendar ti-xs text-muted ms-1"></i>
        </div>
      </div>

      <div class="col-md-2 col-sm-12 d-flex gap-2">
        <button type="submit" class="btn btn-sm btn-primary flex-fill">
          <i class="ti ti-filter me-1"></i> Filter
        </button>
        <a href="{{ route('carriers.assigned') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
          <i class="ti ti-rotate"></i>
        </a>
      </div>
    </form>
  </div>
</div>

<!-- Carriers List Table -->
<div class="card shadow-sm">
  <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Assigned Carriers ({{ $carriers->total() }})</h5>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th style="width: 50px;">#</th>
          <th>MC & DOT</th>
          <th>Carrier & Company</th>
          <th>Assigned Dispatcher</th>
          <th>Sales Agent</th>
          <th>Assigned At</th>
          <th>Status</th>
          <th style="width: 140px;" class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($carriers as $key => $carrier)
          <tr>
            <td>{{ ($carriers->currentPage() - 1) * $carriers->perPage() + $key + 1 }}</td>
            <td>
              <div>
                <span class="badge bg-label-primary font-monospace fw-bold">MC-{{ $carrier->mc_number }}</span>
              </div>
              @if($carrier->dot)
                <small class="text-muted">DOT: {{ $carrier->dot }}</small>
              @endif
            </td>
            <td>
              <div class="fw-semibold">{{ $carrier->name }}</div>
              <small class="text-muted"><i class="ti ti-building me-1"></i>{{ $carrier->company_name }}</small>
              <div class="small text-muted"><i class="ti ti-phone me-1"></i>{{ $carrier->number }}</div>
            </td>
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar avatar-xs me-2">
                  <span class="avatar-initial rounded-circle bg-label-info font-weight-bold">
                    {{ strtoupper(substr($carrier->assignedTo->first_name ?? 'D', 0, 1)) }}
                  </span>
                </div>
                <div>
                  <span class="fw-semibold">{{ $carrier->assignedTo->full_name ?? 'Unassigned' }}</span>
                  <small class="d-block text-muted">{{ $carrier->assignedTo->email ?? '' }}</small>
                </div>
              </div>
            </td>
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar avatar-xs me-2">
                  <span class="avatar-initial rounded-circle bg-label-secondary font-weight-bold">
                    {{ strtoupper(substr($carrier->user->first_name ?? 'A', 0, 1)) }}
                  </span>
                </div>
                <span>{{ $carrier->user ? $carrier->user->full_name : 'System' }}</span>
              </div>
            </td>
            <td>
              @if($carrier->assigned_at)
                <div>{{ \Carbon\Carbon::parse($carrier->assigned_at)->format('M d, Y') }}</div>
                <small class="text-muted">{{ \Carbon\Carbon::parse($carrier->assigned_at)->format('h:i A') }}</small>
              @else
                <span class="text-muted">--</span>
              @endif
            </td>
            <td>
              @php
                $statusMap = [
                  'pending' => ['label' => 'Pending', 'class' => 'bg-label-warning', 'icon' => 'ti-clock'],
                  'in_progress' => ['label' => 'In Progress', 'class' => 'bg-label-info', 'icon' => 'ti-loader'],
                  'not_responding' => ['label' => 'Not Responding', 'class' => 'bg-label-danger', 'icon' => 'ti-phone-off'],
                  'documents_required' => ['label' => 'Docs Needed', 'class' => 'bg-label-dark', 'icon' => 'ti-file-alert'],
                  'done' => ['label' => 'Done', 'class' => 'bg-label-success', 'icon' => 'ti-check'],
                ];
                $st = $statusMap[$carrier->assignment_status ?? 'pending'] ?? ['label' => ucfirst($carrier->assignment_status ?? 'Pending'), 'class' => 'bg-label-secondary', 'icon' => 'ti-help'];
              @endphp
              <span class="badge {{ $st['class'] }} rounded-pill px-3 py-2">
                <i class="ti {{ $st['icon'] }} me-1"></i> {{ $st['label'] }}
              </span>
            </td>
            <td class="text-center">
              <div class="d-flex justify-content-center align-items-center gap-1">
                @can('open-leads-list')
                  <a
                    href="{{ route('carriers.openLeads', ['search' => $carrier->mc_number]) }}"
                    class="btn btn-sm btn-icon btn-text-primary rounded-pill"
                    title="Open Lead Discussion"
                  >
                    <i class="ti ti-messages"></i>
                  </a>
                @endcan

                @can('carriers-view')
                  <a
                    href="{{ route('carriers.show', $carrier->id) }}"
                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill"
                    title="View Carrier Profile"
                  >
                    <i class="ti ti-eye"></i>
                  </a>
                @endcan

                @can('send-email')
                  <a
                    href="{{ route('carriers.email', $carrier->id) }}"
                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill"
                    title="Send Email to Dispatcher"
                  >
                    <i class="ti ti-mail-forward"></i>
                  </a>
                @endcan
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-5">
              <div class="mb-2"><i class="ti ti-folder-off fs-1 text-secondary"></i></div>
              <h6 class="text-muted fw-semibold">No assigned carriers found matching your filters</h6>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer d-flex justify-content-between align-items-center py-3">
    <div class="text-muted small">Showing {{ $carriers->firstItem() ?? 0 }} to {{ $carriers->lastItem() ?? 0 }} of {{ $carriers->total() }} assigned carriers</div>
    <div>{{ $carriers->links() }}</div>
  </div>
</div>
@endsection

@push('page-script')
<script>
  $(function() {
    var hasStart = '{{ request('start_date') }}';
    var hasEnd = '{{ request('end_date') }}';

    var start = hasStart ? moment('{{ request('start_date') }}') : moment().subtract(29, 'days');
    var end = hasEnd ? moment('{{ request('end_date') }}') : moment();

    function cbAssignedDate(start, end, isInit) {
      if (!hasStart && !hasEnd && isInit) {
        $('#reportrange_assigned span').html('All Dates (Click to filter)');
        $('#start_date_assigned').val('');
        $('#end_date_assigned').val('');
      } else {
        $('#reportrange_assigned span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        $('#start_date_assigned').val(start.format('YYYY-MM-DD'));
        $('#end_date_assigned').val(end.format('YYYY-MM-DD'));
      }
    }

    $('#reportrange_assigned').daterangepicker({
      startDate: start,
      endDate: end,
      ranges: {
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
      }
    }, function(selectedStart, selectedEnd) {
      hasStart = true;
      hasEnd = true;
      cbAssignedDate(selectedStart, selectedEnd, false);
      document.getElementById('assignedFilterForm').submit();
    });

    cbAssignedDate(start, end, true);

    $('#reportrange_assigned').on('cancel.daterangepicker', function(ev, picker) {
      $('#start_date_assigned').val('');
      $('#end_date_assigned').val('');
      $('#reportrange_assigned span').html('All Dates (Click to filter)');
      document.getElementById('assignedFilterForm').submit();
    });
  });
</script>
@endpush
