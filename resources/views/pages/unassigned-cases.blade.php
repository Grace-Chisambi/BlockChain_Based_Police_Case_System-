@extends('layouts.apps')

@section('content')
<div class="container py-5">
    <div class="card system-card">
        <div class="card-body">
            <h4 class="mb-4 text-primary text-center fw-bold">Assign Investigator to Unassigned Case</h4>

            <div id="alertContainer"></div>

            @if($cases->isEmpty())
                <p>No unassigned cases found.</p>
            @else
                <form action="{{ route('page.assign.investigator') }}" method="POST" id="assignForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Select Case</label>
                        <select name="case_id" class="form-control" required>
                            <option value="">Choose a case</option>
                            @foreach($cases as $case)
                                <option value="{{ $case->case_id }}">
                                    #{{ $case->case_number }} - {{ $case->case_type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select Investigator</label>
                        <select name="investigator_id" class="form-control" required>
                            <option value="">Choose investigator</option>
                            @foreach($investigators as $investigator)
                                <option value="{{ $investigator->staff_id }}">
                                    {{ $investigator->fname }} {{ $investigator->sname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="assignBtn">
                            <i class="fa fa-paper-plane me-2"></i> Assign
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

<script>
document.getElementById('assignForm').addEventListener('submit', async function (e) {
  e.preventDefault();

  const btn = document.getElementById('assignBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> Assigning...';

  const formData = new FormData(this);

  function showAlert(type, msg) {
    document.getElementById('alertContainer').innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
  }

  try {
    const res = await fetch("{{ route('page.assign.investigator') }}", {
      method: "POST",
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      },
      body: formData
    });
    const data = await res.json();

    if (data.success) {
      const txHash = data.transaction_hash ?? 'N/A';
      showAlert('success', `Investigator assigned successfully`);
      setTimeout(() => location.reload(), 1500);
    } else {
      showAlert('danger', data.message || 'Assignment failed.');
    }
  } catch (err) {
    console.error(err);
    showAlert('danger', 'An unexpected error occurred.');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa fa-paper-plane me-2"></i> Assign';
  }
});
</script>

@endsection
