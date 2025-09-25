@extends('layouts.apps')

@section('content')
<style>
  .breadcrumb-bar {
      margin-top: -20px;
      margin-left: 2rem;
      margin-bottom: 1rem;
  }
  .system-card {
      width: 100%;
      max-width: 100%;
      margin: auto;
      border: none;
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.05);
      border-radius: 0.75rem;
      background-color: #ffffff;
  }
  .system-card .card-body {
      padding: 2rem;
  }
  .system-table th {
      background-color: #f8f9fa;
      color: #0d6efd;
  }
</style>

<div class="container py-5">

  <!-- Breadcrumb -->
  <div class="breadcrumb-bar">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fas fa-home"></i></a></li>
        <li class="breadcrumb-item active" aria-current="page">All Cases Overview</li>
      </ol>
    </nav>
  </div>

  <!-- Filters and Report -->
  <form method="GET" action="{{ route('cases.index') }}" class="row g-3 mb-4">
    <div class="col-md-3">
      <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by case number">
    </div>
    <div class="col-md-2">
      <select name="status" class="form-select">
        <option value="">Status</option>
        <option value="Open" {{ request('status') == 'Open' ? 'selected' : '' }}>Open</option>
        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
        <option value="Closed" {{ request('status') == 'Closed' ? 'selected' : '' }}>Closed</option>
      </select>
    </div>
    <div class="col-md-2">
      <select name="date_range" class="form-select">
        <option value="">Date Range</option>
        <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>Last Week</option>
        <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}>Last Month</option>
      </select>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100">Filter</button>
    </div>
    <div class="col-md-3 text-end">
      <a href="{{ route('cases.report') }}" class="btn btn-outline-dark w-100">Generate Report</a>
    </div>
  </form>

  <!-- Bulk Actions -->
  <form id="bulkActionForm" method="POST" action="{{ route('all_cases.bulkAction') }}">
    @csrf
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <select id="bulkActionSelect" name="action" class="form-select" style="width: 200px;" required>
          <option value="" disabled selected>Select bulk action</option>
          <option value="assign">Assign to Staff</option>
          <option value="close">Close Cases</option>
          <option value="export">Export Selected</option>
          <!-- Add other bulk actions as needed -->
        </select>
      </div>
      <button type="submit" class="btn btn-primary" id="bulkActionBtn" disabled>
        <i class="fas fa-check-circle me-1"></i> Apply
      </button>
    </div>

    <!-- Table -->
    <div class="card system-card">
      <div class="card-body">
        <table class="table table-bordered table-hover align-middle system-table">
          <thead>
            <tr>
              <th><input type="checkbox" id="selectAll"></th>
              <th>Case #</th>
              <th>Status</th>
              <th>Type</th>
              <th>Priority</th>
              <th>Department</th>
              <th>Assigned Staff</th>
              <th>Created</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($cases as $case)
            <tr>
              <td>
                <input type="checkbox" name="case_ids[]" value="{{ $case->case_id }}" class="case-checkbox">
              </td>
              <td class="text-primary fw-bold">{{ $case->case_number }}</td>
              <td>
                <span class="badge bg-{{ $case->case_status == 'Open' ? 'success' : ($case->case_status == 'Pending' ? 'warning' : 'secondary') }}">
                  {{ $case->case_status }}
                </span>
              </td>
              <td>{{ $case->case_type }}</td>
              <td>
                <span class="badge bg-info text-dark">
                  {{ ucfirst($case->priority) }}
                </span>
              </td>
              <td>{{ $case->department->name ?? 'N/A' }}</td>
              <td>
                @foreach($case->assignments as $assignment)
                <div class="mb-1">
                  <span class="badge bg-light text-dark">
                    {{ $assignment->user->sname ?? 'N/A' }} -
                    <small class="text-muted">{{ ucfirst($assignment->user->role ?? '') }}</small>
                  </span>
                </div>
                @endforeach
              </td>
              <td>{{ $case->created_at->format('d M Y') }}</td>
              <td class="text-center">
                <a href="{{ route('cases.show', $case->case_id) }}" class="btn btn-sm btn-outline-primary" title="View">
                  <i class="bi bi-eye"></i>
                </a>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="text-center text-muted">No cases found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>

        <div class="mt-4 d-flex justify-content-center">
          {!! $cases->withQueryString()->links('pagination::bootstrap-5') !!}
        </div>
      </div>
    </div>
  </form>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const selectAllCheckbox = document.getElementById('selectAll');
    const caseCheckboxes = document.querySelectorAll('.case-checkbox');
    const bulkActionBtn = document.getElementById('bulkActionBtn');

    function updateBulkButton() {
      const anyChecked = [...caseCheckboxes].some(chk => chk.checked);
      bulkActionBtn.disabled = !anyChecked;
    }

    selectAllCheckbox.addEventListener('change', (e) => {
      caseCheckboxes.forEach(chk => chk.checked = e.target.checked);
      updateBulkButton();
    });

    caseCheckboxes.forEach(chk => {
      chk.addEventListener('change', () => {
        if (!chk.checked) {
          selectAllCheckbox.checked = false;
        } else if ([...caseCheckboxes].every(chk => chk.checked)) {
          selectAllCheckbox.checked = true;
        }
        updateBulkButton();
      });
    });
  });
</script>
@endsection
