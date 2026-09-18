@extends('layouts.master')

@section('title', 'Permissions Matrix')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-1">Permissions Matrix</h4>
    <p class="text-muted mb-0">Configure granular permission assignments for each role</p>
  </div>
  <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-left me-1"></i> Back to Roles
  </a>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="POST" action="{{ route('roles.updateRole') }}">
      @csrf
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th style="width: 25%;">Role</th>
              <th>Permissions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($roles as $role)
              <tr>
                <td class="fw-semibold">
                  <span class="badge bg-label-{{ $role->name === 'Admin' ? 'primary' : 'info' }} fs-6">
                    {{ $role->name }}
                  </span>
                </td>
                <td>
                  @if($role->name === 'Admin')
                    <span class="text-success fw-bold"><i class="ti ti-check me-1"></i> All Permissions Granted (Super Administrator)</span>
                  @else
                    <div class="row g-2">
                      @foreach($permissions as $permission)
                        <div class="col-md-4 col-sm-6">
                          <div class="form-check">
                            <input
                              type="checkbox"
                              name="permissions[{{ $role->name }}][]"
                              value="{{ $permission->name }}"
                              id="matrix_{{ $role->id }}_{{ $permission->id }}"
                              {{ in_array($permission->name, $role->permissions->pluck('name')->toArray()) ? 'checked' : '' }}
                              class="form-check-input"
                            />
                            <label class="form-check-label small" for="matrix_{{ $role->id }}_{{ $permission->id }}">
                              {{ ucfirst(str_replace('-', ' ', $permission->name)) }}
                            </label>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @can('edit-permission')
        <div class="d-flex justify-content-end mt-4">
          <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy me-1"></i> Save Permissions
          </button>
        </div>
      @endcan
    </form>
  </div>
</div>
@endsection
