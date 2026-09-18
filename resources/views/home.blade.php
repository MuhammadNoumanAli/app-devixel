@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
<div class="row g-4 mb-4">
  <!-- Carrier Documents Received Card -->
  @can('documents-received')
    <div class="col-sm-6 col-xl-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div class="content-left">
              <span class="text-heading fw-semibold">Carriers Added</span>
              <div class="d-flex align-items-center my-2">
                <h3 class="mb-0 me-2">{{ $carrierToday }}</h3>
                <span class="badge bg-label-success small">Today</span>
              </div>
              @can('documents-received-30-days')
                <small class="text-muted">Month: <strong>{{ $carrierLast30Days }}</strong></small>
              @endcan
            </div>
            <div class="avatar">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti ti-truck-delivery ti-sm"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endcan

  <!-- Loads Dispatched Card -->
  <div class="col-sm-6 col-xl-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading fw-semibold">Loads Dispatched</span>
            <div class="d-flex align-items-center my-2">
              <h3 class="mb-0 me-2">{{ $dispatchToday }}</h3>
              <span class="badge bg-label-info small">Today</span>
            </div>
            <small class="text-muted">Month: <strong>{{ $dispatchLast30Days }}</strong></small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-info">
              <i class="ti ti-box ti-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Total Revenue Card -->
  <div class="col-sm-6 col-xl-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading fw-semibold">Gross Revenue</span>
            <div class="d-flex align-items-center my-2">
              <h3 class="mb-0 me-2">${{ number_format($revenueToday) }}</h3>
              <span class="badge bg-label-warning small">Today</span>
            </div>
            <small class="text-muted">Month: <strong>${{ number_format($revenueLast30Days) }}</strong></small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="ti ti-currency-dollar ti-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Commission Card -->
  <div class="col-sm-6 col-xl-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading fw-semibold">Commission</span>
            <div class="d-flex align-items-center my-2">
              <h3 class="mb-0 me-2">${{ number_format($commissionToday) }}</h3>
              <span class="badge bg-label-danger small">Today</span>
            </div>
            <small class="text-muted">Month: <strong>${{ number_format($commissionLast30Days) }}</strong></small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="ti ti-receipt ti-sm"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
  <!-- Truck Types Distribution Donut Chart -->
  <div class="col-12 col-xl-5 col-md-6">
    <div class="card shadow-sm h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Carriers by Truck Type</h5>
        <span class="badge bg-label-primary">Live</span>
      </div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <div id="truckTypePieChart" style="min-height: 290px; width: 100%;"></div>
      </div>
    </div>
  </div>

  <!-- Dispatches Revenue Bar Chart -->
  <div class="col-12 col-xl-7 col-md-6">
    <div class="card shadow-sm h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Dispatch Revenue Trend</h5>
        <span class="badge bg-label-success">Monthly</span>
      </div>
      <div class="card-body">
        <div id="dispatchRevenueChart" style="min-height: 290px; width: 100%;"></div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Tables Row -->
<div class="row g-4">
  <!-- Recent Carriers -->
  <div class="col-12 col-xl-6">
    <div class="card shadow-sm h-100">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center py-3">
        <h5 class="card-title mb-0">Recent Carriers</h5>
        <a href="{{ route('carriers.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th>MC #</th>
              <th>Name</th>
              <th>Truck Type</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            @forelse($recentSales as $carrier)
              <tr>
                <td><span class="badge bg-label-primary">{{ $carrier->mc_number }}</span></td>
                <td class="fw-semibold text-truncate" style="max-width: 140px;">{{ $carrier->name ?? 'N/A' }}</td>
                <td>{{ $carrier->truckType->name ?? 'N/A' }}</td>
                <td>{{ $carrier->created_at->format('M d') }}</td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted py-3">No carriers added yet</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Recent Dispatches -->
  <div class="col-12 col-xl-6">
    <div class="card shadow-sm h-100">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center py-3">
        <h5 class="card-title mb-0">Recent Loads</h5>
        <a href="{{ route('dispatchers.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th>Load #</th>
              <th>Pick / Delivery</th>
              <th>Rate</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($recentDispatch as $disp)
              <tr>
                <td><span class="badge bg-label-info">{{ $disp->load_number }}</span></td>
                <td class="small text-truncate" style="max-width: 150px;">
                  {{ $disp->pick_location }} → {{ $disp->delivery_location }}
                </td>
                <td class="fw-semibold text-success">${{ number_format($disp->rate) }}</td>
                <td>
                  <span class="badge bg-label-{{ $disp->invoice_status === 'Paid' ? 'success' : ($disp->is_cancel ? 'danger' : 'warning') }}">
                    {{ $disp->is_cancel ? 'Cancelled' : ($disp->invoice_status ?? 'Pending') }}
                  </span>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted py-3">No dispatches found</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('page-script')
<script>
  $(function() {
    // 1. Truck Type Donut Chart
    $.ajax({
      url: "{{ route('TruckType.PieChartData') }}",
      type: "GET",
      success: function(response) {
        var labels = [];
        var series = [];
        if (response && response.length > 0) {
          response.forEach(function(item) {
            labels.push(item.name);
            series.push(parseInt(item.count));
          });
        }

        if (series.length === 0) {
          labels = ['Dry Van', 'Reefer', 'Flatbed'];
          series = [45, 25, 30];
        }

        var pieOptions = {
          chart: {
            type: 'donut',
            height: 290
          },
          labels: labels,
          series: series,
          colors: ['#7367f0', '#28c76f', '#ff9f43', '#00cfe8', '#ea5455', '#4b465c'],
          legend: {
            position: 'bottom'
          },
          dataLabels: {
            enabled: true,
            formatter: function (val) {
              return parseInt(val) + "%";
            }
          },
          plotOptions: {
            pie: {
              donut: {
                size: '65%'
              }
            }
          }
        };

        var chart = new ApexCharts(document.querySelector("#truckTypePieChart"), pieOptions);
        chart.render();
      }
    });

    // 2. Dispatch Revenue Trend Chart
    $.ajax({
      url: "{{ route('dispatchers.chartRevenueType') }}",
      type: "GET",
      success: function(response) {
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var revenueData = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

        if (response && response.length > 0) {
          response.forEach(function(item) {
            if (item.month && item.month <= 12) {
              revenueData[item.month - 1] = parseFloat(item.total_revenue || item.revenue || 0);
            }
          });
        }

        var barOptions = {
          chart: {
            type: 'bar',
            height: 290,
            toolbar: { show: false }
          },
          series: [{
            name: 'Gross Revenue ($)',
            data: revenueData
          }],
          xaxis: {
            categories: months
          },
          colors: ['#7367f0'],
          plotOptions: {
            bar: {
              borderRadius: 6,
              columnWidth: '45%'
            }
          },
          dataLabels: { enabled: false },
          yaxis: {
            labels: {
              formatter: function(val) {
                return '$' + val.toLocaleString();
              }
            }
          }
        };

        var barChart = new ApexCharts(document.querySelector("#dispatchRevenueChart"), barOptions);
        barChart.render();
      }
    });
  });
</script>
@endpush
