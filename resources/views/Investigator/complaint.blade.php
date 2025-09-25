@extends('layouts.investigator')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-sm-12 col-xl-6">
            <div class="bg-light rounded h-100 p-4">
                <h6 class="mb-4">Create a Complaint</h6>

                    <!-- Complaint Name -->
                    <form action="{{ route('complaint.store') }}" method="POST">
                        @csrf

                        <!-- Complainant Name -->
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="name" placeholder="Complainant's Name" required>
                            <label for="name">Complainant's Name</label>
                        </div>

                        <!-- Age -->
                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" name="age" placeholder="Age" required>
                            <label for="age">Age</label>
                        </div>

                        <!-- Village -->
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="village" placeholder="Village" required>
                            <label for="village">Village</label>
                        </div>

                        <!-- Job -->
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="job" placeholder="Job" required>
                            <label for="job">Job</label>
                        </div>

                        <!-- Phone Number -->
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="phone_number" placeholder="Phone Number">
                            <label for="phone_number">Phone Number</label>
                        </div>

                        <!-- Statement -->
                        <div class="form-floating mb-3">
                            <textarea class="form-control" name="statement" placeholder="Complaint Statement" style="height: 150px;"></textarea>
                            <label for="statement">Statement</label>
                        </div>

                        <button type="submit" class="btn btn-primary">Submit Complaint</button>
                    </form>
            </div>
        </div>
    </div>
</div>
@endsection
