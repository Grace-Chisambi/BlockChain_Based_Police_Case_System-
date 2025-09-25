@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- Add alert container for AJAX alerts --}}
<div id="alertContainer"></div>

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('page.assign.investigator') }}" method="POST" id="assignForm">
            @csrf

            <div class="mb-3">
                <label class="form-label">Select Case</label>
                <select name="case_id" class="form-control" required>
                    <option value="">-- Choose Case --</option>
                    @foreach($cases as $case)
                        <option value="{{ $case->case_id }}">
                            #{{ $case->case_number }} - {{ $case->case_type }} ({{ $case->case_status }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Select Investigator</label>
                <select name="investigator_id" class="form-control" required>
                    <option value="">-- Choose Available Investigator --</option>
                    @foreach($investigators as $investigator)
                        <option value="{{ $investigator->staff_id }}">
                            {{ $investigator->fname }} {{ $investigator->sname }}
                        </option>
                    @endforeach
                </select>
                @error('investigator_id')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-success" id="assignBtn">
                    <i class="fa fa-paper-plane me-2"></i> Assign
                </button>
            </div>
        </form>
    </div>
</div>

@if(!$cases->isEmpty())
    @foreach($cases as $case)
        <div class="card shadow-sm mb-4 rounded-3">
            <div class="card-header bg-light">
                <strong>Case Number:</strong> {{ $case->case_number }}<br>
                <strong>Type:</strong> {{ $case->case_type }}<br>
                <strong>Status:</strong> {{ $case->case_status }}
            </div>
        </div>
    @endforeach
@else
    <div class="alert alert-info text-center">No unassigned cases found in your department.</div>
@endif

<p class="px-3 pt-3 text-muted">
    View all unassigned cases in your department on the dedicated page.
</p>
<div class="px-3 pb-3">
    <a href="{{ route('cases.unassigned') }}" class="btn btn-sm btn-outline-primary">
        View All
    </a>
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

