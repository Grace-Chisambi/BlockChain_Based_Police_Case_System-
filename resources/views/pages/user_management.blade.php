@extends("layouts.admin")

@section("content")
<style>
    body {
        background-color: #e0f7fa; /* light sky blue */
        color: #007acc; /* sky blue text */
    }

    .breadcrumb-bar {
        margin-top: -10px;
        margin-left: 2rem;
        margin-bottom: 1rem;
    }

    .breadcrumb-bar a, 
    .breadcrumb-bar .breadcrumb-item.active {
        color: #007acc;
    }

    .card {
        background-color: #ffffff; /* white card */
    }

    .btn-primary {
        background-color: #007acc;
        border-color: #007acc;
    }

    .btn-primary:hover {
        background-color: #005f99;
        border-color: #005f99;
    }

    .btn-success {
        background-color: #00bfa5; /* tealish */
        border-color: #00bfa5;
    }

    .btn-success:hover {
        background-color: #009e88;
        border-color: #009e88;
    }

    .btn-warning {
        background-color: #ffb300; /* warm yellow */
        border-color: #ffb300;
        color: #000;
    }

    .btn-warning:hover {
        background-color: #cc8a00;
        border-color: #cc8a00;
    }

    .badge.bg-success {
        background-color: #007acc !important;
    }

    .badge.bg-secondary {
        background-color: #b0bec5 !important; /* light grey */
    }
</style>

<div class="container py-4">
    <!-- Breadcrumb Bar -->
    <div class="breadcrumb-bar">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ url('/') }}">
                        <i class="fas fa-home"></i>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">User Management</li>
            </ol>
        </nav>
    </div>

    <div class="card shadow rounded">
        <div class="card-header d-flex align-items-center justify-content-between bg-light">
            <h5 class="mb-0">User Management</h5>
            <a href="{{ route('users.create') }}" class="btn btn-success">
                <i class="fas fa-user-plus me-1"></i> Add User
            </a>
        </div>

        <div class="card-body">
            <!-- Search Form -->
            <form method="GET" action="{{ route('user_management.page') }}" class="mb-3">
                <div class="input-group">
                    <input type="search" name="search" class="form-control" placeholder="Search users..." value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
                </div>
            </form>

            <!-- Bulk Actions Form -->
            <form method="POST" action="{{ route('users.bulkAction') }}" id="bulkActionForm">
                @csrf
                <div class="mb-3">
                    <button type="submit" name="action" value="activate" class="btn btn-success btn-sm me-2" onclick="return confirm('Activate selected users?')">Activate Selected</button>
                    <button type="submit" name="action" value="deactivate" class="btn btn-warning btn-sm" onclick="return confirm('Deactivate selected users?')">Deactivate Selected</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col"><input type="checkbox" id="selectAll"></th>
                                <th scope="col">#</th>
                                <th scope="col">First Name</th>
                                <th scope="col">Surname</th>
                                <th scope="col">Email</th>
                                <th scope="col">Role</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td><input type="checkbox" name="user_ids[]" value="{{ $user->user_id }}"></td>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $user->fname }}</td>
                                <td>{{ $user->sname }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ ucfirst($user->role) }}</td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('users.edit', $user->user_id) }}" class="btn btn-primary btn-sm me-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>

                                    <form action="{{ route('users.toggleStatus', $user->user_id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        @if($user->is_active)
                                            <button onclick="return confirm('Deactivate this user?')" class="btn btn-warning btn-sm">
                                                <i class="fas fa-user-slash"></i> Deactivate
                                            </button>
                                        @else
                                            <button onclick="return confirm('Activate this user?')" class="btn btn-success btn-sm">
                                                <i class="fas fa-user-check"></i> Activate
                                            </button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No users found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Select/Deselect all checkboxes
    document.getElementById('selectAll').addEventListener('change', function(e) {
        let checkboxes = document.querySelectorAll('input[name="user_ids[]"]');
        checkboxes.forEach(cb => cb.checked = e.target.checked);
    });
</script>
@endsection
