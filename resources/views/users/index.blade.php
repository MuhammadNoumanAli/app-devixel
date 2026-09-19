@extends('layouts.master')

@section('title', 'Users')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">User Management</h4>
    <p class="text-muted mb-0">Manage system users, roles, active status, and load commission rates</p>
  </div>
  @can('create-users')
    <a href="{{ route('users.create') }}" class="btn btn-primary">
      <i class="ti ti-user-plus me-1"></i> Add User
    </a>
  @endcan
</div>

<div class="card shadow-sm">
  <div class="card-header border-bottom py-3">
    <h5 class="card-title mb-0">Users List</h5>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th style="width: 50px;">#</th>
          <th>User</th>
          <th>Email</th>
          <th class="text-center">Commission</th>
          <th>Role</th>
          <th class="text-center" style="width: 110px;">Status</th>
          <th class="text-center" style="width: 120px;">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $key => $user)
          <tr>
            <td>{{ method_exists($users, 'currentPage') ? ($users->currentPage() - 1) * $users->perPage() + $key + 1 : $key + 1 }}</td>
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-2">
                  @if($user->avatar && file_exists(public_path($user->avatar)))
                    <img src="{{ asset($user->avatar) }}" alt="{{ $user->full_name }}" class="rounded-circle" style="object-fit: cover; width: 38px; height: 38px;">
                  @else
                    <span class="avatar-initial rounded-circle bg-label-primary font-weight-bold">
                      {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
                    </span>
                  @endif
                </div>
                <div>
                  <h6 class="mb-0 text-body">{{ $user->full_name }}</h6>
                </div>
              </div>
            </td>
            <td>{{ $user->email }}</td>
            <td class="text-center">
              <span class="badge bg-label-secondary font-monospace">
                {{ $user->load_commission ? $user->load_commission . '%' : '0%' }}
              </span>
            </td>
            <td>
              @forelse($user->roles as $role)
                <span class="badge bg-label-{{ $role->name === 'Admin' ? 'primary' : 'info' }}">
                  {{ $role->name }}
                </span>
              @empty
                <span class="badge bg-label-secondary">No Role</span>
              @endforelse
            </td>
            <td class="text-center">
              @if($user->id == 1)
                <span
                  class="badge bg-label-success opacity-75 d-inline-flex align-items-center"
                  style="cursor: not-allowed;"
                  title="Cannot be changed"
                  data-bs-toggle="tooltip"
                >
                  <i class="ti ti-lock me-1"></i>Active
                </span>
              @elseif(auth()->check() && auth()->id() == $user->id)
                <span
                  class="badge bg-label-{{ $user->status === 'active' ? 'success' : 'danger' }} opacity-75 d-inline-flex align-items-center"
                  style="cursor: not-allowed;"
                  title="You cannot change your own status"
                  data-bs-toggle="tooltip"
                >
                  <i class="ti ti-lock me-1"></i>{{ ucfirst($user->status) }}
                </span>
              @else
                <a
                  href="javascript:void(0);"
                  class="badge bg-label-{{ $user->status === 'active' ? 'success' : 'danger' }} text-decoration-none user-status-btn d-inline-flex align-items-center"
                  data-user-id="{{ $user->id }}"
                  data-status="{{ $user->status }}"
                  data-url="{{ route('users.changeStatus', $user->id) }}"
                  role="button"
                  title="Click to toggle status"
                  data-bs-toggle="tooltip"
                >
                  <i class="ti ti-power me-1 status-icon"></i><span class="status-label">{{ ucfirst($user->status) }}</span>
                </a>
              @endif
            </td>
            <td class="text-center">
              <div class="d-flex justify-content-center align-items-center">
                <a
                  href="{{ route('chat.index', $user->id) }}"
                  class="btn btn-sm btn-icon btn-text-secondary rounded-pill me-1"
                  title="Direct Message"
                >
                  <i class="ti ti-messages"></i>
                </a>

                @can('edit-users')
                  <a
                    href="{{ route('users.edit', $user->id) }}"
                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill me-1"
                    title="Edit User"
                  >
                    <i class="ti ti-edit"></i>
                  </a>
                @endcan

                <a
                  href="{{ route('users.changePasswordForm', $user->id) }}"
                  class="btn btn-sm btn-icon btn-text-secondary rounded-pill me-1"
                  title="Change Password"
                >
                  <i class="ti ti-key"></i>
                </a>

                @can('delete-users')
                  @if($user->id != 1)
                    <a
                      href="javascript:void(0);"
                      onclick="return confirmAndSubmit({{ $user->id }})"
                      class="btn btn-sm btn-icon btn-text-danger rounded-pill"
                      title="Delete User"
                    >
                      <i class="ti ti-trash"></i>
                    </a>
                    <form id="delete-record-{{ $user->id }}" action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-none">
                      @csrf
                      @method('DELETE')
                    </form>
                  @else
                    <span class="btn btn-sm btn-icon text-muted rounded-pill opacity-50" title="Super Admin cannot be deleted" style="cursor: not-allowed;">
                      <i class="ti ti-lock"></i>
                    </span>
                  @endif
                @endcan
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center text-muted py-4">No users found</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @include('layouts.pagination', ['paginator' => $users, 'name' => 'users'])
</div>
@endsection

@push('page-script')
<script>
$(document).ready(function () {
  // Setup AJAX CSRF header
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // SweetAlert2 Toast configuration - Compact, modern toast
  const StatusToast = typeof Swal !== 'undefined' ? Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    showCloseButton: true,
    timer: 2800,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer);
      toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
  }) : null;

  // Initialize tooltips if Bootstrap tooltip is available
  if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }

  $(document).on('click', '.user-status-btn', function (e) {
    e.preventDefault();
    const $btn = $(this);
    if ($btn.hasClass('disabled') || $btn.data('processing')) {
      return;
    }

    const userId = $btn.data('user-id');
    const currentStatus = $btn.data('status');
    const targetStatus = (currentStatus === 'active') ? 'inactive' : 'active';
    const url = $btn.data('url') || ('/users/' + userId + '/change-status');
    const originalHtml = $btn.html();

    // Set loading state
    $btn.data('processing', true).addClass('disabled opacity-75');
    $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span><span class="status-label">Saving...</span>');

    $.ajax({
      url: url,
      type: 'POST',
      data: {
        status: targetStatus
      },
      dataType: 'json'
    }).done(function (response) {
      if (response && response.status) {
        const newStatus = response.new_status || targetStatus;
        const newLabel = response.status_label || (newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
        const badgeClass = response.badge_class || (newStatus === 'active' ? 'bg-label-success' : 'bg-label-danger');

        $btn.removeClass('bg-label-success bg-label-danger')
            .addClass(badgeClass)
            .data('status', newStatus)
            .attr('data-status', newStatus);

        $btn.html('<i class="ti ti-power me-1 status-icon"></i><span class="status-label">' + newLabel + '</span>');

        if (typeof window.showToast === 'function') {
          window.showToast(response.message || ('Status updated to ' + newLabel + ' successfully!'), 'success');
        } else if (StatusToast) {
          StatusToast.fire({
            icon: 'success',
            title: response.message || ('Status updated to ' + newLabel + ' successfully!')
          });
        }
      } else {
        $btn.html(originalHtml);
        const errorMsg = (response && response.message) ? response.message : 'Failed to update status.';
        if (typeof window.showToast === 'function') {
          window.showToast(errorMsg, 'error');
        } else if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'error',
            title: 'Action Failed',
            text: errorMsg
          });
        } else {
          alert(errorMsg);
        }
      }
    }).fail(function (xhr) {
      $btn.html(originalHtml);
      let errorMsg = 'Failed to update status. Please try again.';
      if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMsg = xhr.responseJSON.message;
      }
      if (typeof window.showToast === 'function') {
        window.showToast(errorMsg, 'error');
      } else if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: errorMsg
        });
      } else {
        alert(errorMsg);
      }
    }).always(function () {
      $btn.data('processing', false).removeClass('disabled opacity-75');
    });
  });
});
</script>
@endpush
