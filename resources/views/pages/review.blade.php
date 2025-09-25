@extends('layouts.investigator')

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
  <div class="breadcrumb-bar">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
          <a href="{{ url('investigator/dash') }}"><i class="fas fa-home"></i></a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Complaint Review</li>
      </ol>
    </nav>
  </div>

  {{-- Flash messages --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {!! session('success') !!}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {!! session('error') !!}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- Dynamic AJAX alert container --}}
  <div id="dynamic-alert-container"></div>

  <div class="card system-card">
    <div class="card-header bg-light d-flex justify-content-between">
      <a href="{{ url('register_case') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Add Complaint
      </a>
    </div>
    <div class="card-body">
      <table class="table table-bordered table-hover align-middle system-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Statement</th>
            <th>Reported On</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="complaints-table-body">
          @forelse ($complaints as $complaint)
          <tr data-id="{{ $complaint->complaint_id }}">
            <td>{{ $loop->iteration + ($complaints->currentPage() - 1) * $complaints->perPage() }}</td>
            <td>{{ $complaint->sname }}</td>
            <td>{!! Str::limit(strip_tags($complaint->statement), 50, '...') !!}</td>
            <td>{{ $complaint->created_at?->format('d M Y') }}</td>
            <td class="text-center">
              <a href="{{ route('complaints.show', $complaint->complaint_id) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-eye me-1"></i> View
              </a>
              <button class="btn btn-sm btn-success convert-btn" data-id="{{ $complaint->complaint_id }}">
                <i class="fas fa-magic me-1"></i> Auto Convert
              </button>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center text-muted">No complaints found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>

      <div class="mt-4 d-flex justify-content-center">
        {{ $complaints->links() }}
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.convert-btn').forEach(button => {
  button.addEventListener('click', async function () {
    const complaintId = this.dataset.id;
    const btn = this;
    btn.disabled = true;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Converting...';

    const alertContainer = document.getElementById('dynamic-alert-container');
    alertContainer.innerHTML = '';

    try {
      const response = await fetch(`/complaints/${complaintId}/convert-auto`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
        },
      });

      const result = await response.json();
      btn.disabled = false;
      btn.innerHTML = originalHtml;

      const alertType = result.success ? 'success' : 'danger';
      const message = result.message || (result.success ? 'Converted successfully.' : 'Conversion failed.');

      alertContainer.innerHTML = `
        <div class="alert alert-${alertType} alert-dismissible fade show mt-3" role="alert">
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      `;

      if (result.success) {
        const row = document.querySelector(`tr[data-id='${complaintId}']`);
        if (row) row.remove();

        // Optional: Reload page after short delay
        // setTimeout(() => location.reload(), 1000);
      }

    setTimeout(() => location.reload(), 1000);

    } catch (error) {
      console.error(error);
      btn.disabled = false;
      btn.innerHTML = originalHtml;

      alertContainer.innerHTML = `
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
          An unexpected error occurred.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      `;
    }
  });
});
</script>
@endsection
