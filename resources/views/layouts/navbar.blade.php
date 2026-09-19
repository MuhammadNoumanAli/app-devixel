<nav
  class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
  id="layout-navbar"
>
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
      <i class="ti ti-menu-2 ti-sm"></i>
    </a>
  </div>

  <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <!-- Search -->
    <div class="navbar-nav align-items-center">
      <div class="nav-item navbar-search-wrapper mb-0">
        <a class="nav-item nav-link search-toggler d-flex align-items-center px-0" href="javascript:void(0);">
          <i class="ti ti-search ti-md me-2"></i>
          <span class="d-none d-md-inline-block text-muted">Search (Carriers, MC, Loads...)</span>
        </a>
      </div>
    </div>
    <!-- /Search -->

    <ul class="navbar-nav flex-row align-items-center ms-auto">
      <!-- Real-Time Chat Messages Dropdown (Protected by 'chat' permission) -->
      @can('chat')
      <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
        <a
          class="nav-link dropdown-toggle hide-arrow"
          href="javascript:void(0);"
          data-bs-toggle="dropdown"
          data-bs-auto-close="outside"
          aria-expanded="false"
        >
          <i class="ti ti-messages ti-md"></i>
          <span class="badge bg-danger rounded-pill badge-notifications total_chat_count" style="display: none;">0</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end py-0">
          <li class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
              <h5 class="text-body mb-0 me-auto">Messages</h5>
              <a href="{{ route('chat.index') }}" class="dropdown-notifications-all text-body" title="Open Chat">
                <i class="ti ti-mail-opened fs-4"></i>
              </a>
            </div>
          </li>
          <li class="dropdown-notifications-list scrollable-container">
            <ul class="list-group list-group-flush messages_html">
              <li class="list-group-item text-center text-muted py-3">No new messages</li>
            </ul>
          </li>
          <li class="dropdown-menu-footer border-top">
            <a
              href="{{ route('chat.index') }}"
              class="dropdown-item d-flex justify-content-center text-primary p-2 h-px-40 mb-1 align-items-center"
            >
              Open Full Chat
            </a>
          </li>
        </ul>
      </li>
      @endcan

      <!-- Notification Dropdown -->
      <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
        <a
          class="nav-link dropdown-toggle hide-arrow"
          href="javascript:void(0);"
          data-bs-toggle="dropdown"
          data-bs-auto-close="outside"
          aria-expanded="false"
        >
          <i class="ti ti-bell ti-md"></i>
          <span class="badge bg-primary rounded-pill badge-notifications" id="total_count" style="display: none;">0</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end py-0" style="width: 360px;">
          <li class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
              <h5 class="text-body mb-0 me-auto">Notifications</h5>
            </div>
          </li>
          <li class="dropdown-notifications-list scrollable-container">
            <ul class="list-group list-group-flush" id="notification_html">
              <li class="list-group-item text-center text-muted py-3">No new notifications</li>
            </ul>
          </li>
        </ul>
      </li>

      <!-- User Profile Dropdown -->
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
          <div class="avatar avatar-online">
            @if(Auth::user()->avatar && file_exists(public_path(Auth::user()->avatar)))
              <img src="{{ asset(Auth::user()->avatar) }}" alt="{{ Auth::user()->full_name }}" class="rounded-circle" style="object-fit: cover;">
            @else
              <span class="avatar-initial rounded-circle bg-label-primary font-weight-bold">
                {{ strtoupper(substr(Auth::user()->first_name ?? 'U', 0, 1)) }}
              </span>
            @endif
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item" href="{{ route('users.edit', Auth::id()) }}">
              <div class="d-flex">
                <div class="flex-shrink-0 me-3">
                  <div class="avatar avatar-online">
                    @if(Auth::user()->avatar && file_exists(public_path(Auth::user()->avatar)))
                      <img src="{{ asset(Auth::user()->avatar) }}" alt="{{ Auth::user()->full_name }}" class="rounded-circle" style="object-fit: cover;">
                    @else
                      <span class="avatar-initial rounded-circle bg-label-primary font-weight-bold">
                        {{ strtoupper(substr(Auth::user()->first_name ?? 'U', 0, 1)) }}
                      </span>
                    @endif
                  </div>
                </div>
                <div class="flex-grow-1">
                  <span class="fw-semibold d-block">{{ Auth::user()->full_name }}</span>
                  <small class="text-muted">{{ Auth::user()->roles->pluck('name')->first() ?? 'User' }}</small>
                </div>
              </div>
            </a>
          </li>
          <li>
            <div class="dropdown-divider"></div>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('users.edit', Auth::id()) }}">
              <i class="ti ti-user-circle me-2 ti-sm"></i>
              <span class="align-middle">Edit Profile & Avatar</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('chat.index') }}">
              <i class="ti ti-messages me-2 ti-sm"></i>
              <span class="align-middle">Messenger</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('users.changePasswordForm', Auth::id()) }}">
              <i class="ti ti-key me-2 ti-sm"></i>
              <span class="align-middle">Change Password</span>
            </a>
          </li>
          <li>
            <div class="dropdown-divider"></div>
          </li>
          <li>
            <a
              class="dropdown-item"
              href="javascript:void(0);"
              onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
            >
              <i class="ti ti-logout me-2 ti-sm"></i>
              <span class="align-middle">Sign Out</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
              @csrf
            </form>
          </li>
        </ul>
      </li>
      <!--/ User Profile Dropdown -->
    </ul>
  </div>
</nav>
