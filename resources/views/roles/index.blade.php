@extends('layouts.master')

@section('title', 'Roles List')

@section('content')
<h4 class="fw-bold mb-2">Roles & Access Control</h4>
<p class="mb-4">
  A role provides access to predefined menus and features so that users only have access to what their job requires.
</p>

<!-- Role Cards Grid -->
<div class="row g-4 mb-4">
  @foreach($roles as $role)
    <div class="col-xl-4 col-lg-6 col-md-6">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted">Total {{ $role->users_count ?? $role->users->count() }} users</span>
            <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
              <li class="avatar avatar-sm">
                <span class="avatar-initial rounded-circle bg-label-primary font-weight-bold">
                  {{ strtoupper(substr($role->name, 0, 1)) }}
                </span>
              </li>
            </ul>
          </div>
          <div class="d-flex justify-content-between align-items-end">
            <div class="role-heading">
              <h4 class="mb-1 text-body">{{ $role->name }}</h4>
              <span class="badge bg-label-secondary mb-2">{{ $role->name === 'Admin' ? 'Super Access' : $role->permissions->count() . ' Permissions' }}</span>
              <br/>
              @can('edit-role')
                @if($role->name !== 'Admin')
                  <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}" class="role-edit-modal small">
                    <span>Edit Role</span>
                  </a>
                @else
                  <small class="text-muted">Built-in Administrator</small>
                @endif
              @endcan
            </div>
            <a href="{{ route('roles.permissionIndex') }}" class="text-muted"><i class="ti ti-shield fs-4"></i></a>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Role Modal -->
    @if($role->name !== 'Admin')
      <div class="modal fade" id="editRoleModal{{ $role->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content p-3 p-md-5">
            <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-body">
              <div class="text-center mb-4">
                <h3 class="role-title mb-2">Edit Role: {{ $role->name }}</h3>
                <p class="text-muted">Set or modify permissions for this role</p>
              </div>
              <form action="{{ route('roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="col-12 mb-4">
                  <label class="form-label" for="modalRoleName{{ $role->id }}">Role Name</label>
                  <input
                    type="text"
                    id="modalRoleName{{ $role->id }}"
                    name="role_name"
                    class="form-control"
                    value="{{ $role->name }}"
                    required
                  />
                </div>
                <div class="col-12">
                  <h5>Role Permissions</h5>
                  <div class="table-responsive">
                    <table class="table table-flush-spacing">
                      <tbody>
                        @foreach($permissions->chunk(3) as $permChunk)
                          <tr>
                            @foreach($permChunk as $p)
                              <td>
                                <div class="form-check">
                                  <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $p->name }}"
                                    id="perm_{{ $role->id }}_{{ $p->id }}"
                                    {{ $role->hasPermissionTo($p->name) ? 'checked' : '' }}
                                  />
                                  <label class="form-check-label" for="perm_{{ $role->id }}_{{ $p->id }}">
                                    {{ ucfirst(str_replace('-', ' ', $p->name)) }}
                                  </label>
                                </div>
                              </td>
                            @endforeach
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="col-12 text-center mt-4">
                  <button type="submit" class="btn btn-primary me-sm-3 me-1">Update Role</button>
                  <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    @endif
  @endforeach

  <!-- Add New Role Card -->
  @can('add-role')
    <div class="col-xl-4 col-lg-6 col-md-6">
      <div class="card h-100 shadow-sm border-dashed">
        <div class="row g-0 h-100 align-items-center">
          <div class="col-12">
            <div class="card-body text-center">
              <button
                data-bs-target="#addRoleModal"
                data-bs-toggle="modal"
                class="btn btn-primary mb-2 text-nowrap add-new-role"
              >
                <i class="ti ti-plus me-1"></i> Add New Role
              </button>
              <p class="mb-0 text-muted small">Create custom role with granular permissions</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endcan
</div>

<!-- Users with Roles Table -->
<div class="card shadow-sm">
  <div class="card-header border-bottom d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Total Users with Roles</h5>
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
      <i class="ti ti-user-plus me-1"></i> Add User
    </a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover">
      <thead class="table-light">
        <tr>
          <th>User</th>
          <th>Role</th>
          <th>Status</th>
          <th>Commission</th>
          <th>Joined</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($users as $user)
          <tr>
            <td>
              <div class="d-flex align-items-center">
                <div class="avatar avatar-sm me-3">
                  <span class="avatar-initial rounded-circle bg-label-primary font-weight-bold">
                    {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
                  </span>
                </div>
                <div>
                  <h6 class="mb-0 text-body">{{ $user->full_name }}</h6>
                  <small class="text-muted">{{ $user->email }}</small>
                </div>
              </div>
            </td>
            <td>
              @forelse($user->roles as $userRole)
                <span class="badge bg-label-{{ $userRole->name === 'Admin' ? 'primary' : 'info' }} me-1">
                  {{ $userRole->name }}
                </span>
              @empty
                <span class="badge bg-label-secondary">No Role</span>
              @endforelse
            </td>
            <td>
              <span class="badge bg-label-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                {{ ucfirst($user->status) }}
              </span>
            </td>
            <td>
              {{ $user->load_commission ? $user->load_commission . '%' : '0%' }}
            </td>
            <td>{{ $user->created_at->format('M d, Y') }}</td>
            <td>
              <div class="d-flex align-items-center">
                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-icon btn-text-secondary rounded-pill me-1" title="Edit">
                  <i class="ti ti-edit"></i>
                </a>
                <a href="{{ route('chat.index', $user->id) }}" class="btn btn-sm btn-icon btn-text-secondary rounded-pill me-1" title="Message">
                  <i class="ti ti-messages"></i>
                </a>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="card-footer d-flex justify-content-end">
    {{ $users->links() }}
  </div>
</div>

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="modal-body">
        <div class="text-center mb-4">
          <h3 class="role-title mb-2">Add New Role</h3>
          <p class="text-muted">Set role permissions</p>
        </div>
        <form action="{{ route('roles.store') }}" method="POST">
          @csrf
          <div class="col-12 mb-4">
            <label class="form-label" for="roleNameInput">Role Name</label>
            <input
              type="text"
              id="roleNameInput"
              name="role_name"
              class="form-control"
              placeholder="e.g. Dispatch Supervisor"
              required
            />
          </div>
          <div class="col-12">
            <h5>Role Permissions</h5>
            <div class="table-responsive">
              <table class="table table-flush-spacing">
                <tbody>
                  @foreach($permissions->chunk(3) as $permChunk)
                    <tr>
                      @foreach($permChunk as $p)
                        <td>
                          <div class="form-check">
                            <input
                              class="form-check-input"
                              type="checkbox"
                              name="permissions[]"
                              value="{{ $p->name }}"
                              id="add_perm_{{ $p->id }}"
                            />
                            <label class="form-check-label" for="add_perm_{{ $p->id }}">
                              {{ ucfirst(str_replace('-', ' ', $p->name)) }}
                            </label>
                          </div>
                        </td>
                      @endforeach
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
          <div class="col-12 text-center mt-4">
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Create Role</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
