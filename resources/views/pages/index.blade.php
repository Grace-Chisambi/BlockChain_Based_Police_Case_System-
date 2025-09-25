@extends('layouts.apps')

@section('content')
<div class="row">
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <!-- Metrics Cards -->
            @foreach ([
                ['icon' => 'folder-open', 'title' => 'Total Cases', 'count' => $casesCount, 'color' => 'success', 'change' => '+5% from last month'],
                ['icon' => 'exclamation-circle', 'title' => 'Total Complaints', 'count' => $complaintsCount, 'color' => 'warning', 'change' => '+8% new complaints'],
                ['icon' => 'users', 'title' => 'Police Staff', 'count' => $usersCount, 'color' => 'info', 'change' => '+2% staff growth']
            ] as $item)
                <div class="col-sm-6 col-xl-4">
                    <div class="bg-white shadow-sm rounded d-flex align-items-center justify-content-between p-4">
                        <i class="fa fa-{{ $item['icon'] }} fa-3x text-primary"></i>
                        <div class="ms-3 text-end">
                            <p class="mb-2 fw-semibold text-secondary">{{ $item['title'] }}</p>
                            <h5 class="mb-0">{{ $item['count'] }}</h5>
                            <small class="text-{{ $item['color'] }}">{{ $item['change'] }}</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Crime Map and Logs Section -->
    <div class="row mt-4">
        <!-- Crime Occurrence Map -->
        <div class="col-md-8 grid-margin stretch-card">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title text-primary mb-3">Case Occurrence</h4>
                    <div id="crimeMap" style="height: 450px; width: 100%; border-radius: 8px;"></div>
                </div>
            </div>
        </div>

        <!-- Recent Activity (Logs) -->
        <div class="col-md-4 grid-margin stretch-card">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title text-primary mb-3">Recent Activity</h4>
                    <div class="position-relative mb-4">
                        <canvas id="transaction-history"></canvas>
                        <div class="custom-value mt-2 text-center">
                            <strong>{{ $totalLogs }}</strong> <span class="text-muted">Total Logs</span>
                        </div>
                    </div>

                    <div class="transaction-history">
                        @if($latestLogs->isEmpty())
                            <div class="log-entry bg-light d-flex justify-content-between align-items-center p-3 rounded">
                                <div>
                                    <h6 class="mb-1">No recent activity found.</h6>
                                    <p class="text-muted mb-0">--</p>
                                </div>
                            </div>
                        @else
                            @foreach($latestLogs as $log)
                                <div class="log-entry bg-white border d-flex justify-content-between align-items-center p-3 rounded mb-2">
                                    <div>
                                        <h6 class="mb-1">{{ $log->action }}</h6>
                                        <p class="text-muted mb-0">{{ $log->description }}</p>
                                        <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet Map + Chart.js Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css">

<script>
  var map = L.map('crimeMap').setView([-13.9626, 33.7741], 6); // Malawi center

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
  }).addTo(map);

  // Markers
  @foreach($crimeLocations as $location)
    L.marker([{{ $location->latitude }}, {{ $location->longitude }}]).addTo(map)
      .bindPopup("<b>{{ $location->case_title }}</b><br>{{ $location->description }}");
  @endforeach

  // OPTIONAL: Shaded polygons (if crime areas are passed from backend)
  @if(isset($crimeAreas) && count($crimeAreas))
    @foreach($crimeAreas as $area)
      L.polygon([
        @foreach($area['coordinates'] as $coord)
          [{{ $coord[0] }}, {{ $coord[1] }}],
        @endforeach
      ], {
        color: 'red',
        fillColor: '#f03',
        fillOpacity: 0.2
      }).addTo(map).bindPopup("{{ $area['name'] }}");
    @endforeach
  @endif
</script>

<!-- Chart.js Doughnut -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('transaction-history');
  if (ctx) {
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Cases', 'Complaints', 'Users'],
        datasets: [{
          data: [{{ $casesCount }}, {{ $complaintsCount }}, {{ $usersCount }}],
          backgroundColor: ['#4e73df', '#f6c23e', '#1cc88a'],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        cutout: '70%',
        plugins: {
          legend: {
            display: true,
            position: 'bottom'
          }
        }
      }
    });
  }
</script>
@endsection
