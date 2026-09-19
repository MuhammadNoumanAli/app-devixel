@extends('layouts.master')

@section('title', 'Permissions Matrix')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Permissions Matrix</h4>
    <p class="text-muted mb-0">Configure granular, module-wise permission assignments for each role</p>
  </div>
  <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to Roles
  </a>
</div>



<form method="POST" action="{{ route('roles.updateRole') }}">
  @csrf

  <div class="nav-align-top mb-4">
    <ul class="nav nav-pills mb-3 gap-2" role="tablist">
      @foreach($roles as $key => $role)
        <li class="nav-item">
          <button
            type="button"
            class="nav-link {{ $loop->first ? 'active' : '' }} d-flex align-items-center"
            role="tab"
            data-bs-toggle="tab"
            data-bs-target="#tab-role-{{ $role->id }}"
            aria-controls="tab-role-{{ $role->id }}"
            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
          >
            <i class="ti {{ $role->name === 'Admin' ? 'ti-shield-lock' : 'ti-user-check' }} me-2"></i>
            <span>{{ $role->name }}</span>
            @if($role->name === 'Admin')
              <span class="badge bg-label-primary ms-2">Super</span>
            @else
              <span class="badge bg-label-secondary ms-2 role-count-badge-{{ $role->id }}">{{ $role->permissions->count() }}</span>
            @endif
          </button>
        </li>
      @endforeach
    </ul>

    <div class="tab-content border-0 p-0 bg-transparent">
      @foreach($roles as $role)
        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-role-{{ $role->id }}" role="tabpanel">
          @if($role->name === 'Admin')
            <div class="card shadow-sm border-start border-primary border-4">
              <div class="card-body p-4 text-center">
                <div class="avatar avatar-lg bg-label-primary mx-auto mb-3">
                  <i class="ti ti-shield-lock fs-1"></i>
                </div>
                <h4 class="fw-bold mb-1 text-primary">Super Administrator</h4>
                <p class="text-muted mb-0 max-w-500 mx-auto">
                  The Admin role automatically has unrestricted bypass access to every single module, resource, and action across the entire portal. Permissions cannot be removed from this role.
                </p>
              </div>
            </div>
          @else
            <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-3 rounded shadow-sm">
              <div class="d-flex align-items-center">
                <span class="badge bg-label-info fs-6 me-2">{{ $role->name }}</span>
                <span class="text-muted small">Select module permissions below:</span>
              </div>
              <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-primary" onclick="toggleAllRolePerms('{{ $role->id }}', true)">
                  <i class="ti ti-checks me-1"></i> Grant All
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="toggleAllRolePerms('{{ $role->id }}', false)">
                  <i class="ti ti-x me-1"></i> Revoke All
                </button>
              </div>
            </div>

            <!-- Module Wise Grid -->
            <div class="row g-4">
              @php
                $rolePermNames = $role->permissions->pluck('name')->toArray();
              @endphp

              @foreach($groupedPermissions as $moduleName => $moduleData)
                @php
                  $modPerms = $moduleData['permissions'];
                  $modPermKeys = array_keys($modPerms);
                  $assignedCount = count(array_intersect($modPermKeys, $rolePermNames));
                  $allChecked = count($modPermKeys) > 0 && $assignedCount === count($modPermKeys);
                  $slug = \Illuminate\Support\Str::slug($moduleName);
                @endphp

                <div class="col-xl-6 col-lg-12">
                  <div class="card h-100 shadow-sm border">
                    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                      <div class="d-flex align-items-center">
                        <i class="{{ $moduleData['icon'] }} fs-4 me-2 text-primary"></i>
                        <div>
                          <h6 class="mb-0 fw-bold">{{ $moduleName }}</h6>
                          <small class="text-muted d-none d-sm-inline">{{ $moduleData['description'] }}</small>
                        </div>
                      </div>
                      <div class="form-check form-switch mb-0">
                        <input
                          class="form-check-input mod-toggle"
                          type="checkbox"
                          id="toggle_{{ $role->id }}_{{ $slug }}"
                          onchange="toggleModulePerms(this, '{{ $role->id }}', '{{ $slug }}')"
                          {{ $allChecked ? 'checked' : '' }}
                          title="Toggle all in this module"
                        />
                        <label class="form-check-label small fw-semibold" for="toggle_{{ $role->id }}_{{ $slug }}">All</label>
                      </div>
                    </div>
                    <div class="card-body p-3">
                      <div class="row g-2">
                        @foreach($modPerms as $permKey => $permLabel)
                          @php
                            $isChecked = in_array($permKey, $rolePermNames);
                          @endphp
                          <div class="col-md-6 col-12">
                            <div class="p-2 border rounded bg-white hover-shadow-sm h-100 d-flex align-items-center">
                              <div class="form-check mb-0 w-100">
                                <input
                                  type="checkbox"
                                  name="permissions[{{ $role->name }}][]"
                                  value="{{ $permKey }}"
                                  id="perm_{{ $role->id }}_{{ $permKey }}"
                                  class="form-check-input role-perm-checkbox perm-group-{{ $role->id }}-{{ $slug }}"
                                  data-role-id="{{ $role->id }}"
                                  data-module="{{ $slug }}"
                                  {{ $isChecked ? 'checked' : '' }}
                                  onchange="updateModuleCounter('{{ $role->id }}', '{{ $slug }}')"
                                />
                                <label class="form-check-label w-100 cursor-pointer text-dark" for="perm_{{ $role->id }}_{{ $permKey }}">
                                  <div class="fw-semibold small">{{ $permLabel }}</div>
                                  <div class="text-muted font-monospace" style="font-size: 0.72rem;">{{ $permKey }}</div>
                                </label>
                              </div>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>
      @endforeach
    </div>
  </div>

  @can('edit-permission')
    <div class="card shadow-sm border-0 sticky-bottom py-3 px-4 bg-white mt-4 d-flex flex-row justify-content-between align-items-center" style="box-shadow: 0 -4px 16px rgba(0,0,0,0.06) !important; z-index: 10;">
      <span class="text-muted small">
        <i class="ti ti-info-circle me-1 text-primary"></i> Changes apply immediately to all active users with the respective roles.
      </span>
      <button type="submit" class="btn btn-primary px-4">
        <i class="ti ti-device-floppy me-1"></i> Save All Permissions
      </button>
    </div>
  @endcan
</form>

<script>
function toggleModulePerms(masterSwitch, roleId, moduleSlug) {
  const checkboxes = document.querySelectorAll('.perm-group-' + roleId + '-' + moduleSlug);
  checkboxes.forEach(cb => {
    cb.checked = masterSwitch.checked;
  });
  updateRoleCountBadge(roleId);
}

function updateModuleCounter(roleId, moduleSlug) {
  const checkboxes = document.querySelectorAll('.perm-group-' + roleId + '-' + moduleSlug);
  const masterSwitch = document.getElementById('toggle_' + roleId + '_' + moduleSlug);
  if (masterSwitch && checkboxes.length > 0) {
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    masterSwitch.checked = allChecked;
  }
  updateRoleCountBadge(roleId);
}

function toggleAllRolePerms(roleId, shouldCheck) {
  const checkboxes = document.querySelectorAll('#tab-role-' + roleId + ' .role-perm-checkbox');
  checkboxes.forEach(cb => {
    cb.checked = shouldCheck;
  });
  const moduleToggles = document.querySelectorAll('#tab-role-' + roleId + ' .mod-toggle');
  moduleToggles.forEach(sw => {
    sw.checked = shouldCheck;
  });
  updateRoleCountBadge(roleId);
}

function updateRoleCountBadge(roleId) {
  const checked = document.querySelectorAll('#tab-role-' + roleId + ' .role-perm-checkbox:checked').length;
  const badge = document.querySelector('.role-count-badge-' + roleId);
  if (badge) {
    badge.textContent = checked;
  }
}
</script>
@endsection
