@extends('layouts.investigator')

@section('content')
<style>
  .breadcrumb-bar { margin-top: -20px; margin-left: 2rem; margin-bottom: 1.5rem; }
  .system-card { background: #fff; border-radius: .75rem; box-shadow: 0 0 30px rgba(0,0,0,0.05); border: none; }
  .system-card .card-body { padding: 2rem; }
  .system-input { background: #fff !important; border-radius: .5rem; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1); }
  .system-input:focus { border-color: #0ea5e9; box-shadow: 0 0 0 .2rem rgba(14,165,233,0.25); }

  .timeline { list-style: none; padding: 0; margin: 0; }
  .timeline-entry { border-left: 3px solid #0ea5e9; padding-left: 1rem; margin-bottom: 1rem; }
  .badge-new { background: #0ea5e9; color: #fff; margin-left: .5rem; padding: .15em .5em; font-size: .7rem; font-weight: 600; border-radius: .25rem; vertical-align: middle; }

  .chart-card canvas {
    max-width: 100%;
  }
</style>

<div class="container py-5">
  <!-- Breadcrumb -->
  <div class="breadcrumb-bar">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('investigator/dash') }}"><i class="fas fa-home"></i></a></li>
        <li class="breadcrumb-item active">Log Progress</li>
      </ol>
    </nav>
  </div>

  <div class="row">
    <!-- Left Column -->
    <div class="col-md-6 mb-4">
      {{-- Log Form --}}
      @if ($errors->any())
        <div class="alert alert-danger system-card shadow-sm mb-4">
          <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <div class="card system-card mb-4">
        <div class="card-body">
          @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          @endif

          <h4 class="text-primary text-center fw-bold mb-4">Case Progress</h4>
          <form method="POST" action="{{ route('investigator.progress.store') }}">
            @csrf
            <div class="mb-4">
              <label class="form-label" for="case_id">Select Assigned Case *</label>
              <select name="case_id" id="case_id" class="form-select system-input" required>
                <option value="">-- Choose Case --</option>
                @foreach($assignedCases as $case)
                  <option value="{{ $case->case_id }}" {{ old('case_id') == $case->case_id ? 'selected' : '' }}>
                    {{ $case->case_number }} - {{ $case->case_type }}
                  </option>
                @endforeach
              </select>
            </div>

            <div id="notesSection" style="display: {{ old('case_id') ? 'block' : 'none' }};">
              <div class="mb-3">
                <label class="form-label" for="date">Progress Date *</label>
                <input type="date" name="date" class="form-control system-input" value="{{ old('date') }}" required>
              </div>
              <div class="mb-4">
                <label class="form-label" for="notes">Progress Notes *</label>
                <textarea name="notes" rows="5" class="form-control system-input" required>{{ old('notes') }}</textarea>
              </div>
              <div class="d-grid gap-3">
                <button type="submit" class="btn btn-primary rounded-pill py-3 fs-5">
                  <i class="fas fa-paper-plane me-2"></i>Submit Progress
                </button>
                <a href="{{ route('investigator.assign') }}" class="btn btn-secondary rounded-pill py-3 fs-5">Cancel</a>
              </div>
            </div>
          </form>
        </div>
      </div>

      {{-- Chart Card --}}
      <div class="card system-card chart-card">
        <div class="card-body">
          <h5 class="text-center text-primary fw-bold mb-3">Progress Overview Chart</h5>
          <div style="height: 400px;">
            <canvas id="progressChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Timeline -->
    <div class="col-md-6">
      <div class="card system-card">
        <div class="card-body">
          <h4 class="text-success text-center fw-bold mb-4">Previous Progress Updates</h4>
          <div class="mb-3">
            <label for="filterCase" class="form-label">Filter by Case</label>
            <select id="filterCase" class="form-select system-input">
              <option value="">-- All Cases --</option>
              @foreach($assignedCases as $case)
                <option value="case-{{ $case->case_id }}">#{{ $case->case_number }} - {{ $case->case_type }}</option>
              @endforeach
            </select>
          </div>

          @if($progressEntries->isNotEmpty())
            <ul class="timeline">
              @php $cutoff = now()->subDays(7); @endphp
              @foreach($progressEntries as $entry)
                @php $isNew = \Carbon\Carbon::parse($entry->date)->gte($cutoff); @endphp
                <li id="case-{{ $entry->case_id }}">
                  <div class="timeline-entry {{ $isNew ? 'new' : '' }}">
                    <div class="d-flex justify-content-between">
                      <div>
                        <i class="fas fa-user text-primary me-2"></i>{{ $entry->fname }} {{ $entry->sname }}
                        @if($isNew)<span class="badge-new">New</span>@endif
                        <span class="badge bg-{{ $entry->action == 'approve' ? 'success' : ($entry->action == 'reject' ? 'danger' : 'secondary') }} ms-2">
                          {{ ucfirst($entry->action ?? 'pending') }}
                        </span>
                      </div>
                      <small>{{ \Carbon\Carbon::parse($entry->date)->format('M d, Y') }}</small>
                    </div>
                    <p class="mt-2 mb-1"><strong>Notes:</strong> {{ $entry->notes }}</p>
                    @if($entry->recommendations)
                      <p class="text-info mb-1"><strong>Supervisor:</strong> {{ $entry->supervisor_fname }} {{ $entry->supervisor_sname }} – {{ $entry->recommendations }}</p>
                    @endif
                  </div>
                </li>
              @endforeach
            </ul>
            <div class="d-flex justify-content-center mt-3">{{ $progressEntries->links() }}</div>
          @else
            <p class="text-center text-muted">No progress entries submitted yet.</p>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Toggle notes section visibility based on case selection
  document.getElementById('case_id').addEventListener('change', function(e) {
    document.getElementById('notesSection').style.display = e.target.value ? 'block' : 'none';
  });

  // Filter timeline entries by case
  document.getElementById('filterCase').addEventListener('change', function(e) {
    document.querySelectorAll('.timeline li').forEach(li => {
      li.style.display = !e.target.value || li.id === e.target.value ? 'block' : 'none';
    });
  });

  // Progress Overview Chart with Chart.js
  const ctx = document.getElementById('progressChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: [
        @foreach($assignedCases as $case)"{{ $case->case_number }}",@endforeach
      ],
      datasets: [
        {
          label: 'Approved',
          data: [@foreach($assignedCases as $case){{ $progressCounts[$case->case_id]['approve'] ?? 0 }},@endforeach],
          backgroundColor: '#a5d8ff'
        },
        {
          label: 'Pending',
          data: [@foreach($assignedCases as $case){{ $progressCounts[$case->case_id]['pending'] ?? 0 }},@endforeach],
          backgroundColor: '#bae6fd'
        },
        {
          label: 'Rejected',
          data: [@foreach($assignedCases as $case){{ $progressCounts[$case->case_id]['reject'] ?? 0 }},@endforeach],
          backgroundColor: '#fca5a5'
        }
      ]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'top' },
        tooltip: { mode: 'index', intersect: false }
      },
      scales: {
        x: {
          stacked: true,
          ticks: { precision: 0, stepSize: 1 },
          title: { display: true, text: 'Number of Entries' }
        },
        y: {
          stacked: true,
          title: { display: true, text: 'Case Number' }
        }
      }
    }
  });
</script>
@endsection
