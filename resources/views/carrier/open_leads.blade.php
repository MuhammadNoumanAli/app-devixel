@extends('layouts.master')

@section('title', 'Open Leads Management')

@push('page-style')
<style>
  .lead-timeline {
    position: relative;
    padding-left: 28px;
  }
  .lead-timeline::before {
    content: '';
    position: absolute;
    top: 10px;
    bottom: 10px;
    left: 10px;
    width: 2px;
    background: #e9ecef;
  }
  .lead-note-item {
    position: relative;
    margin-bottom: 20px;
  }
  .lead-note-dot {
    position: absolute;
    left: -28px;
    top: 4px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    background: #7367f0;
    color: #fff;
    box-shadow: 0 0 0 3px #fff;
  }
  .lead-note-box {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 12px 16px;
    border: 1px solid #eef0f3;
  }
  .lead-note-box.agent-note {
    background: #f0f4ff;
    border-color: #d8e2fd;
  }
  .lead-note-box.dispatcher-note {
    background: #f3fdf8;
    border-color: #c9f0d8;
  }
  .lead-row-done {
    transition: all 0.5s ease-out;
    opacity: 0;
    transform: translateX(40px);
  }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h4 class="fw-bold mb-1">Open Leads Workflow</h4>
    <p class="text-muted mb-0">Active collaboration between Dispatchers & Sales Agents on assigned carriers, driver follow-ups, and document submissions</p>
  </div>
  <div class="d-flex gap-2">
    @can('assigned-carriers-list')
      <a href="{{ route('carriers.assigned') }}" class="btn btn-outline-secondary">
        <i class="ti ti-list me-1"></i> Assigned Carriers
      </a>
    @endcan
    @can('carriers-create')
      <a href="{{ route('carriers.create') }}" class="btn btn-primary">
        <i class="ti ti-plus me-1"></i> Add Carrier Lead
      </a>
    @endcan
  </div>
</div>

<!-- Navigation Tabs for Status Filtering -->
<div class="nav-align-top mb-4">
  <ul class="nav nav-pills flex-wrap gap-2" role="tablist">
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'all_open' ? 'active' : '' }}" href="{{ route('carriers.openLeads', array_merge(request()->query(), ['tab' => 'all_open'])) }}">
        <i class="ti ti-inbox me-1"></i> All Active Leads
        <span class="badge rounded-pill bg-primary ms-2">{{ $counts['all_open'] ?? 0 }}</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'pending' ? 'active' : '' }}" href="{{ route('carriers.openLeads', array_merge(request()->query(), ['tab' => 'pending'])) }}">
        <i class="ti ti-clock me-1"></i> New / Pending
        <span class="badge rounded-pill bg-warning text-dark ms-2">{{ $counts['pending'] ?? 0 }}</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'in_progress' ? 'active' : '' }}" href="{{ route('carriers.openLeads', array_merge(request()->query(), ['tab' => 'in_progress'])) }}">
        <i class="ti ti-loader me-1"></i> In Progress
        <span class="badge rounded-pill bg-info ms-2">{{ $counts['in_progress'] ?? 0 }}</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'not_responding' ? 'active' : '' }}" href="{{ route('carriers.openLeads', array_merge(request()->query(), ['tab' => 'not_responding'])) }}">
        <i class="ti ti-phone-off me-1"></i> Not Responding
        <span class="badge rounded-pill bg-danger ms-2">{{ $counts['not_responding'] ?? 0 }}</span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'documents_required' ? 'active' : '' }}" href="{{ route('carriers.openLeads', array_merge(request()->query(), ['tab' => 'documents_required'])) }}">
        <i class="ti ti-file-alert me-1"></i> Docs Needed
        <span class="badge rounded-pill bg-dark ms-2">{{ $counts['documents_required'] ?? 0 }}</span>
      </a>
    </li>
    <li class="nav-item ms-auto">
      <a class="nav-link {{ $tab === 'done' ? 'active' : '' }} text-success" href="{{ route('carriers.openLeads', array_merge(request()->query(), ['tab' => 'done'])) }}">
        <i class="ti ti-circle-check me-1"></i> Completed Leads
        <span class="badge rounded-pill bg-success ms-2">{{ $counts['done'] ?? 0 }}</span>
      </a>
    </li>
  </ul>
</div>

<!-- Filters Bar -->
<div class="card shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" action="{{ route('carriers.openLeads') }}" class="row g-3 align-items-end">
      <input type="hidden" name="tab" value="{{ $tab }}">
      <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
      <div class="col-md-4 col-sm-6">
        <label class="form-label small fw-semibold">Search MC, DOT, Carrier or Company</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="ti ti-search"></i></span>
          <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
        </div>
      </div>

      @if($isAdmin)
      <div class="col-md-3 col-sm-6">
        <label class="form-label small fw-semibold">Assigned Dispatcher</label>
        <select name="dispatcher_id" class="form-select form-select-sm">
          <option value="">All Dispatchers</option>
          @foreach($dispatchers as $d)
            <option value="{{ $d->id }}" @selected(request('dispatcher_id') == $d->id)>{{ $d->full_name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3 col-sm-6">
        <label class="form-label small fw-semibold">Sales Agent</label>
        <select name="agent_id" class="form-select form-select-sm">
          <option value="">All Sales Agents</option>
          @foreach($agents as $a)
            <option value="{{ $a->id }}" @selected(request('agent_id') == $a->id)>{{ $a->full_name }}</option>
          @endforeach
        </select>
      </div>
      @endif

      <div class="col-md-2 col-sm-6 d-flex gap-2">
        <button type="submit" class="btn btn-sm btn-primary w-100">
          <i class="ti ti-filter me-1"></i> Filter
        </button>
        <a href="{{ route('carriers.openLeads', ['tab' => $tab]) }}" class="btn btn-sm btn-outline-secondary">
          <i class="ti ti-rotate"></i>
        </a>
      </div>
    </form>
  </div>
</div>

<!-- Alert notice for Sales Agent vs Dispatcher workflow -->
@if(!$canChangeStatus)
  <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
    <i class="ti ti-info-circle fs-4 me-2"></i>
    <div>
      <strong>Sales Agent View:</strong> You can review messages from dispatchers regarding carriers you submitted, write follow-up notes, and attach requested compliance files. Lead statuses and completion are managed by the assigned dispatcher.
    </div>
  </div>
@else
  <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
    <i class="ti ti-bell-ringing fs-4 me-2"></i>
    <div>
      <strong>Dispatcher / Supervisor View:</strong> You have full control over lead status. Flag issues like <em>Not Responding</em> or <em>Documents Needed</em> to alert the agent. Once resolved, mark the status as <strong>Done</strong> to complete and archive the lead.
    </div>
  </div>
@endif

<!-- Open Leads Table -->
<div class="card shadow-sm">
  <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Active Leads ({{ $leads->total() }})</h5>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table table-hover align-middle" id="open-leads-table">
      <thead class="table-light">
        <tr>
          <th style="width: 50px;">#</th>
          <th>MC / Lead Info</th>
          <th>Status</th>
          <th>Assigned Dispatcher</th>
          <th>Sales Agent</th>
          <th>Latest Note</th>
          <th>Last Activity</th>
          <th style="width: 140px;" class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($leads as $key => $lead)
          @php
            $statusMap = [
              'pending' => ['label' => 'Pending (New)', 'class' => 'bg-label-warning', 'icon' => 'ti-clock'],
              'in_progress' => ['label' => 'In Progress', 'class' => 'bg-label-info', 'icon' => 'ti-loader'],
              'not_responding' => ['label' => 'Not Responding', 'class' => 'bg-label-danger', 'icon' => 'ti-phone-off'],
              'documents_required' => ['label' => 'Docs Needed', 'class' => 'bg-label-dark', 'icon' => 'ti-file-alert'],
              'done' => ['label' => 'Completed', 'class' => 'bg-label-success', 'icon' => 'ti-circle-check'],
            ];
            $st = $statusMap[$lead->assignment_status ?? 'pending'] ?? ['label' => ucfirst($lead->assignment_status ?? 'Pending'), 'class' => 'bg-label-secondary', 'icon' => 'ti-help'];
            $latestNote = $lead->notes->last();
          @endphp
          <tr id="lead-row-{{ $lead->id }}">
            <td>{{ ($leads->currentPage() - 1) * $leads->perPage() + $key + 1 }}</td>
            <td>
              <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                  <span class="badge bg-label-primary font-monospace fw-semibold">MC-{{ $lead->mc_number }}</span>
                  <span class="fw-semibold text-heading">{{ $lead->name }}</span>
                </div>
                <div class="d-flex align-items-center text-muted small text-nowrap gap-2">
                  @if($lead->company_name)
                    <span><i class="ti ti-building ti-xs me-1 text-secondary"></i>{{ $lead->company_name }}</span>
                  @endif
                  @if($lead->company_name && $lead->number)
                    <span class="text-secondary">&bull;</span>
                  @endif
                  @if($lead->number)
                    <span><i class="ti ti-phone ti-xs me-1 text-secondary"></i>{{ $lead->number }}</span>
                  @endif
                </div>
              </div>
            </td>
            <td>
              <span class="badge {{ $st['class'] }} rounded-pill px-2.5 py-1 text-nowrap lead-status-badge-{{ $lead->id }}">
                <i class="ti {{ $st['icon'] }} ti-xs me-1"></i> <span class="badge-text">{{ $st['label'] }}</span>
              </span>
            </td>
            <td>
              <div class="d-flex align-items-center text-nowrap">
                <div class="avatar avatar-xs me-2">
                  <span class="avatar-initial rounded-circle bg-label-info fw-bold">
                    {{ strtoupper(substr($lead->assignedTo->first_name ?? 'D', 0, 1)) }}
                  </span>
                </div>
                <div>
                  <span class="fw-semibold d-block text-heading">{{ $lead->assignedTo->full_name ?? 'Unassigned' }}</span>
                </div>
              </div>
            </td>
            <td>
              <div class="d-flex align-items-center text-nowrap">
                <div class="avatar avatar-xs me-2">
                  <span class="avatar-initial rounded-circle bg-label-secondary fw-bold">
                    {{ strtoupper(substr($lead->user->first_name ?? 'A', 0, 1)) }}
                  </span>
                </div>
                <span class="fw-medium text-heading">{{ $lead->user ? $lead->user->full_name : 'System' }}</span>
              </div>
            </td>
            <td>
              @if($latestNote)
                <div class="small fw-semibold text-truncate" style="max-width: 220px;" title="{{ $latestNote->message }}">
                  {{ Str::limit($latestNote->message, 45) }}
                </div>
                <small class="text-muted d-block">
                  <i class="ti ti-user me-1"></i>{{ $latestNote->user?->name ?? 'User' }} &bull; {{ $latestNote->created_at->diffForHumans() }}
                  @if($latestNote->attachment)
                    <i class="ti ti-paperclip ms-1 text-primary" title="Attachment attached"></i>
                  @endif
                </small>
              @else
                <span class="text-muted small fst-italic">No notes recorded yet</span>
              @endif
            </td>
            <td>
              <small class="text-muted">{{ $lead->updated_at->diffForHumans() }}</small>
            </td>
            <td class="text-center">
              <button
                type="button"
                class="btn btn-sm btn-primary rounded-pill px-3"
                onclick="openLeadModal({{ $lead->id }})"
              >
                <i class="ti ti-messages me-1"></i> Open Lead
              </button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-5">
              <div class="mb-2"><i class="ti ti-inbox-off fs-1 text-secondary"></i></div>
              <h6 class="text-muted fw-semibold">No open leads found for this view</h6>
              <p class="text-muted small mb-0">When new carriers are assigned to dispatchers, they will appear here automatically.</p>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @include('layouts.pagination', ['paginator' => $leads, 'name' => 'leads'])
</div>

<!-- Modal: Communication Trail & Document Upload (Route 3) -->
<div class="modal fade" id="leadNotesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-bottom py-3 bg-light">
        <div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary font-monospace fs-6" id="modal-mc-number">MC-000000</span>
            <h5 class="modal-title mb-0 fw-bold" id="modal-carrier-name">Carrier Details</h5>
            <span class="badge rounded-pill px-3 py-1" id="modal-status-badge">Status</span>
          </div>
          <small class="text-muted d-block mt-1" id="modal-carrier-subtitle">Company &bull; Contact Info</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <!-- Lead Meta Banner -->
        <div class="row g-2 mb-4 p-3 bg-lighter rounded border text-muted small">
          <div class="col-sm-6">
            <i class="ti ti-user-check text-primary me-1"></i> <strong>Assigned Dispatcher:</strong> <span id="modal-assigned-to">--</span>
          </div>
          <div class="col-sm-6">
            <i class="ti ti-user text-secondary me-1"></i> <strong>Sales Agent:</strong> <span id="modal-created-by">--</span>
          </div>
          <div class="col-sm-6">
            <i class="ti ti-truck text-info me-1"></i> <strong>Equipment:</strong> <span id="modal-equipment">--</span>
          </div>
          <div class="col-sm-6">
            <i class="ti ti-phone text-success me-1"></i> <strong>Phone:</strong> <span id="modal-phone">--</span>
          </div>
        </div>

        <!-- Notes & Document Timeline -->
        <h6 class="fw-bold mb-3 d-flex align-items-center">
          <i class="ti ti-history me-2 text-primary"></i> Communication History & Attachments
        </h6>
        
        <div id="modal-timeline-container" class="lead-timeline mb-4">
          <!-- Populated via AJAX -->
          <div class="text-center py-4" id="modal-timeline-loading">
            <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
            <span class="ms-2 text-muted small">Loading discussion history...</span>
          </div>
        </div>

        <!-- Compose New Note / Upload Form -->
        <div class="card border border-primary-subtle shadow-sm bg-white mt-4">
          <div class="card-header py-2 px-3 bg-light border-bottom d-flex align-items-center justify-content-between">
            <span class="fw-semibold text-primary small">
              <i class="ti ti-edit me-1"></i> Post Note / Share Documents
            </span>
            @if(!$canChangeStatus)
              <span class="badge bg-label-secondary small"><i class="ti ti-lock me-1"></i> Status Managed by Dispatcher</span>
            @endif
          </div>
          <div class="card-body p-3">
            <form id="leadNoteForm" enctype="multipart/form-data">
              @csrf
              <input type="hidden" id="current_carrier_id" name="carrier_id" value="">

              @if($canChangeStatus)
                <div class="mb-3">
                  <label for="note_status" class="form-label small fw-semibold text-dark">
                    <i class="ti ti-flag text-warning me-1"></i> Update Lead Status
                  </label>
                  <select id="note_status" name="status" class="form-select form-select-sm">
                    <option value="pending">Pending (New)</option>
                    <option value="in_progress">In Progress (Working with Driver)</option>
                    <option value="not_responding">Driver / Carrier Not Responding (Requires Agent Follow-up)</option>
                    <option value="documents_required">Documents Needed (Missing COI / MC / W9 / etc.)</option>
                    <option value="done">Done (Completed - Hide/Remove from Active Open Leads)</option>
                  </select>
                  <small class="text-muted d-block mt-1">
                    <i class="ti ti-info-circle me-1"></i> Selecting <strong>Done</strong> marks the lead resolved and automatically removes it from the active open leads list.
                  </small>
                </div>
              @endif

              <div class="mb-3">
                <label for="note_message" class="form-label small fw-semibold text-dark">
                  Message / Notes <span class="text-danger">*</span>
                </label>
                <textarea
                  id="note_message"
                  name="message"
                  rows="3"
                  class="form-control"
                  placeholder="{{ $canChangeStatus ? 'Provide feedback, driver response, or missing documents needed...' : 'Enter your follow-up note, driver confirmation, or resolution response...' }}"
                  required
                ></textarea>
              </div>

              <div class="mb-3">
                <label for="note_attachment" class="form-label small fw-semibold text-dark">
                  <i class="ti ti-paperclip text-primary me-1"></i> Attach Document / File <span class="text-muted">(Optional - PDF, Word, Image, ZIP max 10MB)</span>
                </label>
                <input type="file" id="note_attachment" name="attachment" class="form-control form-control-sm" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.webp,.zip">
              </div>

              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" id="btnSubmitNote" class="btn btn-sm btn-primary">
                  <span class="spinner-border spinner-border-sm d-none me-1" id="submitNoteSpinner"></span>
                  <i class="ti ti-send me-1"></i> Post Note & Update
                </button>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

@push('page-script')
<script>
  const statusConfig = {
    'pending': { label: 'Pending (New)', class: 'bg-label-warning', icon: 'ti-clock' },
    'in_progress': { label: 'In Progress', class: 'bg-label-info', icon: 'ti-loader' },
    'not_responding': { label: 'Not Responding', class: 'bg-label-danger', icon: 'ti-phone-off' },
    'documents_required': { label: 'Docs Needed', class: 'bg-label-dark', icon: 'ti-file-alert' },
    'done': { label: 'Completed', class: 'bg-label-success', icon: 'ti-circle-check' }
  };

  let activeCarrierId = null;

  window.openLeadModal = function(carrierId) {
    activeCarrierId = carrierId;
    document.getElementById('current_carrier_id').value = carrierId;
    document.getElementById('note_message').value = '';
    document.getElementById('note_attachment').value = '';

    const container = document.getElementById('modal-timeline-container');
    container.innerHTML = `
      <div class="text-center py-4">
        <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
        <span class="ms-2 text-muted small">Loading discussion history...</span>
      </div>
    `;

    const modalEl = document.getElementById('leadNotesModal');
    if (window.bootstrap && bootstrap.Modal) {
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    } else if (window.$) {
      $('#leadNotesModal').modal('show');
    }

    fetch(`/carrier-leads/${carrierId}/notes`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(response => {
      if (!response.ok) throw new Error('Network error loading notes');
      return response.json();
    })
    .then(data => {
      if (!data.success) throw new Error(data.message || 'Error fetching lead details');

      const carrier = data.carrier;
      document.getElementById('modal-mc-number').textContent = `MC-${carrier.mc_number}`;
      document.getElementById('modal-carrier-name').textContent = carrier.name;
      document.getElementById('modal-carrier-subtitle').innerHTML = `<strong>${carrier.company_name}</strong> &bull; DOT: ${carrier.dot || 'N/A'}`;
      document.getElementById('modal-assigned-to').textContent = carrier.assigned_to;
      document.getElementById('modal-created-by').textContent = carrier.created_by;
      document.getElementById('modal-equipment').textContent = `${carrier.truck_type} (${carrier.truck_size})`;
      document.getElementById('modal-phone').textContent = carrier.number;

      // Status Badge
      const st = statusConfig[carrier.assignment_status] || statusConfig['pending'];
      const badgeEl = document.getElementById('modal-status-badge');
      badgeEl.className = `badge rounded-pill px-3 py-1 ${st.class}`;
      badgeEl.innerHTML = `<i class="ti ${st.icon} me-1"></i> ${st.label}`;

      // Set dropdown status if user can change status
      const statusSelect = document.getElementById('note_status');
      if (statusSelect) {
        statusSelect.value = carrier.assignment_status || 'pending';
      }

      // Render Notes
      renderNotes(data.notes);
    })
    .catch(err => {
      console.error(err);
      container.innerHTML = `
        <div class="alert alert-danger py-2 small">
          <i class="ti ti-alert-triangle me-1"></i> Could not load notes: ${err.message}
        </div>
      `;
    });
  }

  function renderNotes(notes) {
    const container = document.getElementById('modal-timeline-container');
    if (!notes || notes.length === 0) {
      container.innerHTML = `
        <div class="text-center py-4 text-muted small">
          <i class="ti ti-message-dots fs-3 d-block mb-1"></i>
          No notes or documents recorded for this lead yet. Start the conversation below!
        </div>
      `;
      return;
    }

    let html = '';
    notes.forEach(note => {
      const isAgent = note.user_role === 'Sales Agent';
      const isDispatcher = note.user_role === 'Dispatcher';
      const roleBadgeClass = isAgent ? 'bg-label-primary' : (isDispatcher ? 'bg-label-success' : 'bg-label-secondary');
      const boxClass = isAgent ? 'agent-note' : (isDispatcher ? 'dispatcher-note' : '');
      const dotIcon = isAgent ? 'ti-user' : (isDispatcher ? 'ti-headset' : 'ti-info-circle');

      let attachmentHtml = '';
      if (note.attachment) {
        if (note.attachment_is_image) {
          attachmentHtml = `
            <div class="mt-2 p-2 bg-white rounded border d-inline-block">
              <a href="${note.attachment}" target="_blank" class="d-block mb-1">
                <img src="${note.attachment}" alt="Attachment" style="max-height: 120px; border-radius: 4px;" class="img-fluid">
              </a>
              <a href="${note.attachment}" target="_blank" download class="small text-primary">
                <i class="ti ti-download me-1"></i> ${note.attachment_original_name || 'Download Image'}
              </a>
            </div>
          `;
        } else {
          attachmentHtml = `
            <div class="mt-2">
              <a href="${note.attachment}" target="_blank" download class="btn btn-sm btn-outline-primary py-1 px-2">
                <i class="ti ti-file-download me-1"></i> ${note.attachment_original_name || 'Download Attached File'}
              </a>
            </div>
          `;
        }
      }

      let statusPill = '';
      if (note.status) {
        const sInfo = statusConfig[note.status] || { label: note.status, class: 'bg-label-secondary' };
        statusPill = `<span class="badge ${sInfo.class} rounded-pill ms-2 small" style="font-size: 10px;">${sInfo.label}</span>`;
      }

      html += `
        <div class="lead-note-item">
          <div class="lead-note-dot">
            <i class="ti ${dotIcon}"></i>
          </div>
          <div class="lead-note-box ${boxClass}">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <div>
                <strong class="text-dark">${note.user_name}</strong>
                <span class="badge ${roleBadgeClass} rounded-pill ms-1" style="font-size: 10px;">${note.user_role}</span>
                ${statusPill}
              </div>
              <small class="text-muted" title="${note.created_at_formatted}">
                <i class="ti ti-clock me-1"></i>${note.created_at_human}
              </small>
            </div>
            <div class="text-body small" style="white-space: pre-wrap;">${escapeHtml(note.message)}</div>
            ${attachmentHtml}
          </div>
        </div>
      `;
    });

    container.innerHTML = html;
  }

  function escapeHtml(text) {
    if (!text) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
  }

  // Handle Form Submission
  document.getElementById('leadNoteForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!activeCarrierId) return;

    const btn = document.getElementById('btnSubmitNote');
    const spinner = document.getElementById('submitNoteSpinner');
    btn.disabled = true;
    spinner.classList.remove('d-none');

    const formData = new FormData(this);

    fetch(`/carrier-leads/${activeCarrierId}/notes`, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      }
    })
    .then(res => {
      if (!res.ok) {
        return res.json().then(errData => { throw new Error(errData.message || 'Error posting note'); });
      }
      return res.json();
    })
    .then(data => {
      btn.disabled = false;
      spinner.classList.add('d-none');

      if (!data.success) {
        alert(data.message || 'Error updating lead');
        return;
      }

      // Append note to timeline
      document.getElementById('note_message').value = '';
      document.getElementById('note_attachment').value = '';

      // Reload notes
      openLeadModal(activeCarrierId);

      // Check if lead status changed to done
      if (data.is_done) {
        // If current tab is not 'done', hide/remove row from the active list
        const currentTab = '{{ $tab }}';
        if (currentTab !== 'done') {
          const row = document.getElementById(`lead-row-${activeCarrierId}`);
          if (row) {
            row.classList.add('lead-row-done');
            setTimeout(() => {
              row.remove();
            }, 500);
          }
        }
      } else {
        // Update badge on the table row
        const st = statusConfig[data.assignment_status] || statusConfig['pending'];
        const badgeEl = document.querySelector(`.lead-status-badge-${activeCarrierId}`);
        if (badgeEl) {
          badgeEl.className = `badge ${st.class} rounded-pill px-3 py-2 lead-status-badge-${activeCarrierId}`;
          badgeEl.innerHTML = `<i class="ti ${st.icon} me-1"></i> <span class="badge-text">${st.label}</span>`;
        }
      }
    })
    .catch(err => {
      btn.disabled = false;
      spinner.classList.add('d-none');
      alert(err.message || 'An error occurred while posting note');
    });
  });
</script>
@endpush
