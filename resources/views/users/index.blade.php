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
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th style="width: 60px;">#</th>
          <th>User</th>
          <th>Email</th>
          <th>Commission</th>
          <th>Role</th>
          <th style="width: 100px;">Status</th>
          <th style="width: 140px;" class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $key => $user)
          <tr>
            <td>{{ ($users->currentPage() - 1) * $users->perPage() + $key + 1 }}</td>
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-2">
                  <span class="avatar-initial rounded-circle bg-label-primary font-weight-bold">
                    {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
                  </span>
                </div>
                <div>
                  <h6 class="mb-0 text-body">{{ $user->full_name }}</h6>
                </div>
              </div>
            </td>
            <td>{{ $user->email }}</td>
            <td>
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
            <td>
              <a
                href="{{ route('users.changeStatus', $user->id) }}"
                class="badge bg-label-{{ $user->status === 'active' ? 'success' : 'danger' }} text-decoration-none"
                title="Click to toggle status"
              >
                <i class="ti ti-power me-1"></i>{{ ucfirst($user->status) }}
              </a>
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
  <div class="card-footer d-flex justify-content-end">
    {{ $users->links() }}
  </div>
</div>
@endsection
