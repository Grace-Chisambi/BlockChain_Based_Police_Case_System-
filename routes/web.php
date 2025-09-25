<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\SuspectsController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\CaseClosureController;
use App\Http\Controllers\InvestigatorController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProsecutorController;


Route::get('/', function () {
    return view('auth.login');
});

// Static Page Routes
Route::get('/admin', [PagesController::class, 'admin'])->name('admin.page');
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
Route::get('/user_management', [PagesController::class, 'user_management'])->name('user_management.page');
Route::get('/index', [PagesController::class, 'index'])->name('index.page');
Route::get('/assignment', [PagesController::class, 'assignment'])->name('assignment.page');
Route::get('/register_case', [PagesController::class, 'register_case'])->name('register_case.page');
Route::get('/user_create', [PagesController::class, 'user_create'])->name('user_create.page');

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/admin', [DashboardController::class, 'index'])->name('admin');
Route::get('investigator/cases/{caseId}/report', [DashboardController::class, 'showCaseReport'])->name('investigator.cases.report');
Route::get('investigator/cases/{caseId}/report/pdf', [DashboardController::class, 'showCaseReportPdf'])->name('investigator.cases.report.pdf');
Route::get('/progress', [DashboardController::class, 'showAssignedCases'])->name('progress.list');

Route::get('/progress', [SupervisorController::class, 'viewProgress'])->name('progress.index');
// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



// Supervisor Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/assign', [SupervisorController::class, 'supervisorAssignPage'])->name('page.assign');
    Route::post('/assign', [SupervisorController::class, 'assignInvestigator'])->name('page.assign.investigator');
});


Route::get('/unassigned-cases', [SupervisorController::class, 'unassignedCases'])->name('cases.unassigned');
// Supervisor reviewing suspects
Route::get('/supervisor/suspects/pending', [SupervisorController::class, 'pendingSuspectReviews'])->name('supervisor.suspects.pending');
Route::get('/supervisor/suspect/{id}/review', [SupervisorController::class, 'reviewSuspect'])->name('supervisor.suspect.review');
Route::patch('/supervisor/suspect/{id}/review', [SupervisorController::class, 'submitReview'])->name('supervisor.suspect.review.submit');
Route::post('/assign-investigator', [SupervisorController::class, 'assignInvestigator'])->name('assign.investigator');

//Progress Routes
Route::get('/progress', [ProgressController::class, 'viewProgress'])->name('progress.index');
Route::post('/progress/approve', [ProgressController::class, 'approve'])->name('progress.approve');
Route::get('progress/{case_id?}', [ProgressController::class, 'index'])->name('progress.index');
Route::get('/investigator/progress_form', [ProgressController::class, 'logForm'])->name('investigator.progress.form');
Route::get('/progress/case/{case_id}', [ProgressController::class, 'caseProgress'])->name('progress.case');

//case Closure
Route::prefix('investigator')->name('investigator.')->middleware(['auth'])->group(function () {
    Route::get('/case_closures', [CaseClosureController::class, 'create'])->name('case_closures.create');
    Route::post('/case_closures', [CaseClosureController::class, 'store'])->name('case_closures.store');
});

// Case Routes
Route::post('/cases', [CaseController::class, 'store'])->name('cases.store');
Route::get('/cases/create', [CaseController::class, 'create'])->name('cases.create');
Route::post('/cases', [CaseController::class, 'store'])->name('cases.store');
Route::get('/complaints/{id}/convert', [CaseController::class, 'convert'])->name('complaints.convert');
Route::get('/complaints/{id}/convert', [CaseController::class, 'convert'])->name('pages.convert_case');
Route::post('/complaints/{complaint}/convert', [ComplaintController::class, 'convert'])->name('complaints.convert');

Route::get('/cases', [CaseController::class, 'index'])->name('cases.index');
Route::get('/cases/{id}', [CaseController::class, 'show'])->name('cases.show');
Route::get('/cases/{id}/edit', [CaseController::class, 'edit'])->name('cases.edit');
Route::put('/cases/{id}', [CaseController::class, 'update'])->name('cases.update');
Route::delete('/cases/{id}', [CaseController::class, 'destroy'])->name('cases.destroy');
Route::get('/cases/export/pdf', [CaseController::class, 'exportPdf'])->name('cases.export.pdf');
Route::get('/all_cases', [CaseController::class, 'index'])->name('cases.all');
Route::post('/complaints/{id}/convert-auto', [CaseController::class, 'autoConvert'])->name('complaints.autoConvert');
Route::get('/report.case_report', [CaseController::class, 'report'])->name('cases.report');
Route::get('/cases/report/export', [CaseController::class, 'exportReport'])->name('cases.report.export');
Route::post('/cases/bulk-action', [CaseController::class, 'bulkAction'])->name('cases.bulkAction');
Route::post('/all_cases/bulk-action', [CaseController::class, 'bulkAction'])->name('all_cases.bulkAction');

//complaints
Route::post('/complaint/store', [ComplaintController::class, 'store'])->name('complaint.store');
Route::post('/complaint/store', [ComplaintController::class, 'store'])->name('complaint.store');

Route::get('/complaint/{id}', [ComplaintController::class, 'show'])->name('complaint.show');
Route::delete('/complaint/{id}', [ComplaintController::class, 'destroy'])->name('complaint.destroy');
Route::get('/complaint', [ComplaintController::class, 'create'])->name('complaint.create');


// LOGS
Route::get('/logs', [LogsController::class, 'index'])->name('logs.index');

// Admin dashboard route
Route::middleware(['auth', 'can:admin-access'])->group(function () {
    Route::get('/admin/admin_dash', [AdminController::class, 'index'])->name('admin.admin_dash');
});



// Evidence
Route::middleware(['auth'])->group(function () {
    Route::get('/cases/{case}/evidence/create', [EvidenceController::class, 'create'])->name('evidence.create');
    Route::post('/evidence/store', [EvidenceController::class, 'store'])->name('evidence.store');
    Route::post('/cases/bulk-action', [EvidenceController::class, 'handleBulkAction'])->name('cases.bulk.action');
    Route::post('/evidence/store', [InvestigatorController::class, 'storeEvidence'])->name('evidence.store');

});
// Show review page for a specific case
Route::get('/cases/{case_id}/review_evidence', [EvidenceController::class, 'showReviewPage'])->name('review.evidence.page');
// Handle evidence review submission
Route::post('/evidence/{evidence_id}/review', [EvidenceController::class, 'submitReview'])->name('evidence.review.submit');
Route::get('/pending_cases', [EvidenceController::class, 'casesWithPendingEvidence'])->name('cases.pending.review');
Route::get('/evidence/export/{case_id}', [EvidenceController::class, 'exportReviewedEvidence'])->name('evidence.export');

//Notifications

Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
});


//Investigator Routes
Route::middleware(['auth'])->prefix('investigator')->name('investigator.')->group(function () {
    Route::get('/dash', [InvestigatorController::class, 'dashboard'])->name('dash');
    Route::get('/assign', [InvestigatorController::class, 'assign'])->name('assign');
Route::get('/complaint', [InvestigatorController::class, 'complaints'])->name('investigator.complaint');

    Route::get('/cases/{id}', [InvestigatorController::class, 'show'])->name('cases.show');
    Route::post('/evidence/store', [InvestigatorController::class, 'storeEvidence'])->name('evidence.store');
});

// Store the form
Route::post('/investigator/progress_form', [InvestigatorController::class, 'store'])
    ->name('investigator.progress.store');
    // Investigator reports group
Route::prefix('investigator')->name('investigator.')->group(function () {

    // Assignment report page (GET)
    Route::get('reports/assignment', [InvestigatorController::class, 'assignmentReport'])
        ->name('assigned_cases');

    // Export PDF for assignment report (GET)
    Route::get('reports/assignment/export', [InvestigatorController::class, 'exportAssignmentReport'])
        ->name('assigned_cases.export');
});
// Assigned cases listing & search
Route::get('/investigator/assigned-cases', [InvestigatorController::class, 'assignedCases'])->name('investigator.assigned_cases');

// Report page route
Route::get('/investigator/assigned-cases/report', [InvestigatorController::class, 'assignmentReport'])->name('investigator.assignment_report');
Route::get('/investigator/reports/assignment', [InvestigatorController::class, 'assignmentReport'])
    ->name('investigator.reports.assignment');

    // Investigator Case Detailed Report
Route::get('investigator/cases/{caseId}/report', [InvestigatorController::class, 'showCaseReport'])
    ->name('investigator.cases.report');
Route::get('investigator/cases/{caseId}/report/pdf', [InvestigatorController::class, 'showCaseReportPdf'])
     ->name('investigator.cases.report.pdf');

// Assignments
Route::get('/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');

// Users
Route::resource('users', UserController::class)->middleware('auth');
Route::get('/user_management', [UserController::class, 'index'])->name('user_management.page');
Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');
Route::post('/users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulkAction');

//prosecutor
Route::middleware(['auth'])->prefix('prosecutor')->group(function () {
    Route::get('/cases', [ProsecutorController::class, 'index'])->name('prosecutor.cases');
    Route::get('/cases/{case_id}', [ProsecutorController::class, 'show'])->name('prosecutor.cases.show');
    Route::post('/evidence/{evidence_id}/review', [ProsecutorController::class, 'reviewEvidence'])->name('prosecutor.evidence.review');
    Route::post('/cases/{case_id}/close', [ProsecutorController::class, 'closeCase'])->name('prosecutor.case.close');
});
 Route::get('/prosecutor/reports', [ProsecutorController::class, 'reports'])->name('prosecutor.reports');
 Route::get('/prosecutor/reports/pdf', [ProsecutorController::class, 'exportPdf'])->name('prosecutor.reports.pdf');
Route::get('/prosecutor/appearances', [ProsecutorController::class, 'upcomingAppearances'])->name('prosecutor.appearances');

Route::prefix('prosecutor')->name('prosecutor.')->middleware(['auth'])->group(function () {
    Route::get('/case_closures', [ProsecutorController::class, 'view'])->name('case_closures.create');
    Route::post('/case_closures', [ProsecutorController::class, 'closecase'])->name('case_closures.store');
});
Route::middleware(['auth'])->group(function () {
    Route::get('/prosecutor/dashboard', [ProsecutorController::class, 'dashboard'])->name('prosecutor.dashboard');
});
Route::post('prosecutor/export-selected-pdf', [ProsecutorController::class, 'exportSelectedPdf'])->name('prosecutor.exportSelectedPdf');



// Complaints
Route::get('/page/review', [ComplaintController::class, 'review'])->name('page.review');
Route::get('/complaints/review', [ComplaintController::class, 'review'])->name('complaints.review');
Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
Route::get('/complaints/create', [ComplaintController::class, 'create'])->name('complaints.create');
Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
Route::get('/complaints/{id}', [ComplaintController::class, 'show'])->name('complaints.show');
Route::delete('/complaints/{id}', [ComplaintController::class, 'destroy'])->name('complaints.destroy');

// Suspects Routes

Route::middleware(['auth', 'role:investigator'])->group(function () {
    Route::get('/investigator/suspects', [SuspectsController::class, 'investigatorIndex'])->name('suspects.index');
    Route::get('/investigator/suspect/create', [SuspectsController::class, 'create'])->name('suspect.create');
    Route::post('/investigator/suspect', [SuspectsController::class, 'store'])->name('suspect.store');
    Route::get('/investigator/suspect/{id}/edit', [SuspectsController::class, 'edit'])->name('suspect.edit');
    Route::put('/investigator/suspect/{id}', [SuspectsController::class, 'update'])->name('suspect.update');
});

Route::get('/suspects/show/{id}', [SuspectsController::class, 'show'])->name('suspects.show');
Route::delete('/suspects/{id}', [SuspectsController::class, 'destroy'])->name('suspects.destroy');
Route::get('/suspects/create', [SuspectsController::class, 'create'])->name('suspect.create');
Route::get('/investigator/suspect', [SuspectsController::class, 'investigatorIndex'])->name('investigator.suspect');
Route::get('/suspects', [SuspectsController::class, 'index'])->name('suspect.index');
Route::get('/suspects/create', [SuspectsController::class, 'create'])->name('suspect.create');
Route::post('/suspects/store', [SuspectsController::class, 'store'])->name('suspect.store');
Route::get('/suspects/show/{id}', [SuspectsController::class, 'show'])->name('suspect.show');
Route::get('/suspects/{id}', [SuspectsController::class, 'show'])->name('suspects.show');


require __DIR__.'/auth.php';
