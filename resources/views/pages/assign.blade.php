@extends('layouts.apps')

@section('content')
<div class="container-fluid pt-4 px-4">
    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        @each('partials.summary-card', [
            ['title' => 'Cases in Department', 'count' => $departmentCasesCount, 'icon' => 'building'],
            ['title' => 'Assigned Cases', 'count' => $assignedCasesCount, 'icon' => 'user-check'],
            ['title' => 'Evidence Uploaded', 'count' => $evidenceUploadedCount, 'icon' => 'file-upload']
        ], 'card')
    </div>

    <div class="row g-4">
        <!-- Left: Case Management Accordion -->
        <div class="col-lg-8">
            <div class="card shadow rounded bg-white" style="min-height: 60vh;">
                <div class="card-header bg-primary text-white py-3">
                    <!-- Optional header content -->
                </div>

                <div class="accordion accordion-flush" id="caseAccordion">
                    @foreach([
                        [
                            'id' => 'unassignedSection',
                            'title' => 'Unassigned Cases',
                            'icon' => 'folder-open',
                            'content' => 'partials.card-unassigned'
                        ],
                        [
                            'id' => 'assignedSection',
                            'title' => 'Recently Assigned Cases',
                            'icon' => 'user-check',
                            'content' => 'partials.card-assigned'
                        ],
                        [
                            'id' => 'evidenceSection',
                            'title' => 'Recent Evidence Cases',
                            'icon' => 'file-upload',
                            'content' => 'partials.card-evidence'
                        ]
                    ] as $section)
                    <div class="accordion-item border-bottom bg-white">
                        <h2 class="accordion-header" id="{{ $section['id'] }}Heading">
                            <button class="accordion-button collapsed py-3 px-4" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#{{ $section['id'] }}"
                                    style="background-color: #ffffff; color: #333;">
                                <i class="fas fa-{{ $section['icon'] }} text-primary me-2"></i>
                                {{ $section['title'] }}
                            </button>
                        </h2>
                        <div id="{{ $section['id'] }}"
                             class="accordion-collapse collapse"
                             data-bs-parent="#caseAccordion">
                            <div class="accordion-body py-4 px-4 bg-white">
                                @include($section['content'])
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right: Department Metrics -->
        <div class="col-lg-4">
            <div class="sticky-top top-4">
                <div class="card border-0 shadow-sm rounded-lg bg-white">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0 text-white">📊 Department Metrics</h5>
                    </div>
                    <div class="card-body">
                        @each('partials.metrics-card', [
                            ['label' => 'Available Investigators', 'value' => count($investigators)],
                            ['label' => 'Cases with Evidence', 'value' => $evidenceUploadedCount]
                        ], 'metric')
                    </div>
                </div>

                <!-- Tip Box -->
                <div class="mt-4">
                    <div class="card bg-white shadow-sm rounded-lg">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <i class="fas fa-lightbulb text-warning fa-lg"></i>
                                </div>
                                <div>
                                    <strong class="text-muted">Tip</strong>
                                    <p class="mb-0 small">Balance assignments by checking investigator availability and past workloads.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
