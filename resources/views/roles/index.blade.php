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
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Role Permissions (Module-Wise)</h5>
                    <div class="btn-group btn-group-sm">
                      <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleAllModalPerms('editRoleModal{{ $role->id }}', true)">Select All</button>
                      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleAllModalPerms('editRoleModal{{ $role->id }}', false)">Deselect All</button>
                    </div>
                  </div>
                  <div class="accordion" id="accordionEditRole{{ $role->id }}" style="max-height: 380px; overflow-y: auto;">
                    @foreach($groupedPermissions as $modName => $modInfo)
                      @php
                        $modSlug = \Illuminate\Support\Str::slug($modName);
                        $modPerms = $modInfo['permissions'];
                        $rolePermNames = $role->permissions->pluck('name')->toArray();
                        $hasActive = count(array_intersect(array_keys($modPerms), $rolePermNames)) > 0;
                      @endphp
                      <div class="accordion-item border mb-2 rounded shadow-none">
                        <h2 class="accordion-header" id="headingEdit_{{ $role->id }}_{{ $modSlug }}">
                          <button class="accordion-button py-2 px-3 {{ $hasActive ? '' : 'collapsed' }} bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEdit_{{ $role->id }}_{{ $modSlug }}" aria-expanded="{{ $hasActive ? 'true' : 'false' }}">
                            <i class="{{ $modInfo['icon'] }} me-2 text-primary"></i>
                            <span class="fw-semibold me-auto">{{ $modName }}</span>
                          </button>
                        </h2>
                        <div id="collapseEdit_{{ $role->id }}_{{ $modSlug }}" class="accordion-collapse collapse {{ $hasActive ? 'show' : '' }}">
                          <div class="accordion-body p-3 bg-white">
                            <div class="row g-2">
                              @foreach($modPerms as $pKey => $pLabel)
                                <div class="col-md-6 col-12">
                                  <div class="form-check">
                                    <input
                                      class="form-check-input modal-perm-check"
                                      type="checkbox"
                                      name="permissions[]"
                                      value="{{ $pKey }}"
                                      id="perm_edit_{{ $role->id }}_{{ $pKey }}"
                                      {{ in_array($pKey, $rolePermNames) ? 'checked' : '' }}
                                    />
                                    <label class="form-check-label small cursor-pointer" for="perm_edit_{{ $role->id }}_{{ $pKey }}">
                                      <span class="fw-semibold d-block text-dark">{{ $pLabel }}</span>
                                      <span class="text-muted font-monospace" style="font-size: 0.7rem;">{{ $pKey }}</span>
                                    </label>
                                  </div>
                                </div>
                              @endforeach
                            </div>
                          </div>
                        </div>
                      </div>
                    @endforeach
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
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h5 class="mb-0">Role Permissions (Module-Wise)</h5>
              <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleAllModalPerms('addRoleModal', true)">Select All</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleAllModalPerms('addRoleModal', false)">Deselect All</button>
              </div>
            </div>
            <div class="accordion" id="accordionAddRole" style="max-height: 380px; overflow-y: auto;">
              @foreach($groupedPermissions as $modName => $modInfo)
                @php
                  $modSlug = \Illuminate\Support\Str::slug($modName);
                  $modPerms = $modInfo['permissions'];
                @endphp
                <div class="accordion-item border mb-2 rounded shadow-none">
                  <h2 class="accordion-header" id="headingAdd_{{ $modSlug }}">
                    <button class="accordion-button py-2 px-3 {{ $loop->first ? '' : 'collapsed' }} bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdd_{{ $modSlug }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                      <i class="{{ $modInfo['icon'] }} me-2 text-primary"></i>
                      <span class="fw-semibold me-auto">{{ $modName }}</span>
                    </button>
                  </h2>
                  <div id="collapseAdd_{{ $modSlug }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}">
                    <div class="accordion-body p-3 bg-white">
                      <div class="row g-2">
                        @foreach($modPerms as $pKey => $pLabel)
                          <div class="col-md-6 col-12">
                            <div class="form-check">
                              <input
                                class="form-check-input modal-perm-check"
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $pKey }}"
                                id="perm_add_{{ $pKey }}"
                              />
                              <label class="form-check-label small cursor-pointer" for="perm_add_{{ $pKey }}">
                                <span class="fw-semibold d-block text-dark">{{ $pLabel }}</span>
                                <span class="text-muted font-monospace" style="font-size: 0.7rem;">{{ $pKey }}</span>
                              </label>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
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

<script>
function toggleAllModalPerms(modalId, check) {
  document.querySelectorAll('#' + modalId + ' .modal-perm-check').forEach(el => el.checked = check);
}
</script>
@endsection
