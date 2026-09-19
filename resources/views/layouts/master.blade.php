<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-navbar-fixed layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="{{ asset('assets') }}/"
  data-template="vertical-menu-template"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
    <title>@yield('title', 'Carrier Management System') | Devixel</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/tabler-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-chat.css') }}" />

    @stack('vendor-style')
    @stack('page-style')

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>

    <style>
      .cancel-dot {
        background-color: #ea5455 !important;
        border-color: #ea5455 !important;
      }
      .badge-number {
        font-size: 0.65rem;
        padding: 0.2rem 0.4rem;
      }

      /* Global SweetAlert2 Toast Overrides */
      body.swal2-toast-shown .swal2-container {
        box-sizing: border-box !important;
        width: auto !important;
        max-width: 420px !important;
        padding: 1rem 1.25rem !important;
        background-color: transparent !important;
        pointer-events: none !important;
        z-index: 10999 !important;
      }
      body.swal2-toast-shown .swal2-container.swal2-top-end,
      body.swal2-toast-shown .swal2-container.swal2-top-right {
        top: 75px !important;
        right: 1.25rem !important;
      }
      .light-style .swal2-popup.swal2-toast,
      .dark-style .swal2-popup.swal2-toast,
      .swal2-popup.swal2-toast {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        width: auto !important;
        min-width: 280px !important;
        max-width: 380px !important;
        padding: 0.75rem 1rem !important;
        border-radius: 0.5rem !important;
        background: #ffffff !important;
        border: 1px solid rgba(75, 70, 92, 0.08) !important;
        box-shadow: 0 0.5rem 1.25rem rgba(75, 70, 92, 0.15), 0 0.125rem 0.35rem rgba(75, 70, 92, 0.06) !important;
        pointer-events: auto !important;
        overflow: hidden !important;
        gap: 0.6rem !important;
      }
      .light-style .swal2-popup.swal2-toast .swal2-title,
      .dark-style .swal2-popup.swal2-toast .swal2-title,
      .swal2-popup.swal2-toast .swal2-title {
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        color: #383544 !important;
        line-height: 1.4 !important;
        margin: 0 !important;
        padding: 0 !important;
        text-align: left !important;
        flex: 1 1 auto !important;
        white-space: normal !important;
      }
      .light-style .swal2-popup.swal2-toast .swal2-icon,
      .dark-style .swal2-popup.swal2-toast .swal2-icon,
      .swal2-popup.swal2-toast .swal2-icon {
        width: 1.5rem !important;
        height: 1.5rem !important;
        min-width: 1.5rem !important;
        margin: 0 !important;
        border-width: 2px !important;
        flex-shrink: 0 !important;
      }
      .swal2-popup.swal2-toast .swal2-icon.swal2-success {
        border-color: #28c76f !important;
      }
      .swal2-popup.swal2-toast .swal2-icon.swal2-success .swal2-success-ring {
        width: 1.5rem !important;
        height: 1.5rem !important;
        border-width: 2px !important;
        border-color: rgba(40, 199, 111, 0.25) !important;
      }
      .swal2-popup.swal2-toast .swal2-icon.swal2-success [class^=swal2-success-line] {
        height: 2px !important;
        background-color: #28c76f !important;
      }
      .swal2-popup.swal2-toast .swal2-icon.swal2-success .swal2-success-line-tip {
        width: 0.45rem !important;
        left: 0.18rem !important;
        top: 0.72rem !important;
      }
      .swal2-popup.swal2-toast .swal2-icon.swal2-success .swal2-success-line-long {
        width: 0.75rem !important;
        right: 0.18rem !important;
        top: 0.65rem !important;
      }
      .swal2-popup.swal2-toast .swal2-close {
        width: 1.25rem !important;
        height: 1.25rem !important;
        font-size: 1.25rem !important;
        line-height: 1 !important;
        color: #a5a3ae !important;
        margin: 0 !important;
        padding: 0 !important;
        background: transparent !important;
        border: none !important;
        cursor: pointer !important;
        align-self: center !important;
      }
      .swal2-popup.swal2-toast .swal2-timer-progress-bar {
        height: 3px !important;
        background: #28c76f !important;
      }
      .table-responsive::-webkit-scrollbar {
        height: 5px;
      }
      .table-responsive::-webkit-scrollbar-track {
        background: transparent;
      }
      .table-responsive::-webkit-scrollbar-thumb {
        background: rgba(75, 70, 92, 0.15);
        border-radius: 4px;
      }
    </style>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->
        @include('layouts.sidebar')
        <!-- / Menu -->

        <!-- Layout page -->
        <div class="layout-page">
          <!-- Navbar -->
          @include('layouts.navbar')
          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">


              @if (isset($errors) && $errors->any())
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

              @yield('content')
            </div>
            <!-- / Content -->

            <!-- Footer -->
            @include('layouts.footer')
            <!-- / Footer -->

            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>

      <!-- Drag Target Area To SlideIn Menu On Small Screens -->
      <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core Modals -->
    <div class="modal fade" id="carrierModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Carrier Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="carrierDetails">
            <div class="text-center py-4">
              <div class="spinner-border text-primary" role="status"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="dispatcherModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Dispatch Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="dispatcherDetails">
            <div class="text-center py-4">
              <div class="spinner-border text-primary" role="status"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-responsive/datatables.responsive.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script>
      window.currentRoute = "{{ Route::currentRouteName() }}";
    </script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script>
      // Global Toast Notification Helper
      window.showToast = function(message, type = 'success', duration = 3200) {
        if (typeof Swal !== 'undefined') {
          const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            showCloseButton: true,
            timer: duration,
            timerProgressBar: true,
            didOpen: (toast) => {
              toast.addEventListener('mouseenter', Swal.stopTimer);
              toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
          });
          Toast.fire({
            icon: type,
            title: message
          });
        } else {
          alert(message);
        }
      };

      // Automatically display Toaster for Add, Edit, Delete and Status flash messages
      function triggerSessionToasts() {
        @if (session('status'))
          window.showToast(@json(session('status')), 'success');
        @endif

        @if (session('success'))
          window.showToast(@json(session('success')), 'success');
        @endif

        @if (session('error'))
          window.showToast(@json(session('error')), 'error');
        @endif

        @if (session('warning'))
          window.showToast(@json(session('warning')), 'warning');
        @endif

        @if (session('info'))
          window.showToast(@json(session('info')), 'info');
        @endif

        @if (isset($errors) && $errors->any())
          window.showToast('Please correct the validation errors in the form.', 'error');
        @endif
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', triggerSessionToasts);
      } else {
        triggerSessionToasts();
      }
    </script>

    @stack('vendor-script')
    @stack('page-script')
    @stack('scripts')
  </body>
</html>
