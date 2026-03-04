<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ConceptController;
use App\Http\Controllers\ShootController;
use App\Http\Controllers\EditingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ── Root redirect ──────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ── Public Client Routes (no auth needed) ─────────────────
Route::get('/client/concepts/{token}', [ConceptController::class, 'clientView'])
    ->name('concepts.client-view');
Route::post('/client/concepts/{token}/approve/{concept}', [ConceptController::class, 'clientApprove'])
    ->name('concepts.client-approve');
Route::post('/client/concepts/{token}/reject/{concept}', [ConceptController::class, 'clientReject'])
    ->name('concepts.client-reject');

// ── Authenticated Dashboard ────────────────────────────────
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class , 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class , 'stats'])->name('dashboard.stats');

    // ── Profile ───────────────────────────────────────────
    Route::get('/profile', [ProfileController::class , 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class , 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class , 'destroy'])->name('profile.destroy');

    // ── Agencies ──────────────────────────────────────────
    Route::resource('agencies', AgencyController::class);

    // ── Notifications ─────────────────────────────────────
    Route::group(['prefix' => 'notifications', 'as' => 'notifications.'], function () {
            Route::get('/', [NotificationController::class , 'index'])->name('index');
            Route::get('/unread-count', [NotificationController::class , 'unreadCount'])->name('unread-count');
            Route::get('/recent', [NotificationController::class , 'recent'])->name('recent');
            Route::post('/{id}/read', [NotificationController::class , 'markRead'])->name('mark-read');
            Route::post('/read-all', [NotificationController::class , 'markAllRead'])->name('read-all');
        }
        );

        // ── Employees (Admin only) ─────────────────────────────
        Route::middleware(['role:Admin'])->prefix('admin')->name('admin.')->group(function () {
            Route::resource('employees', EmployeeController::class);
            Route::post('employees/{employee}/toggle-status', [EmployeeController::class , 'toggleStatus'])->name('employees.toggle-status');
        }
        );

        // ── Leads (Admin, Manager, Sales Executive) ────────────
        Route::middleware(['role:Admin|Manager|Sales Executive'])->prefix('leads')->name('leads.')->group(function () {
            Route::get('/', [LeadController::class , 'index'])->name('index');
            Route::get('/create', [LeadController::class , 'create'])->name('create');
            Route::post('/', [LeadController::class , 'store'])->name('store');
            Route::get('/{lead}', [LeadController::class , 'show'])->name('show');
            Route::get('/{lead}/edit', [LeadController::class , 'edit'])->name('edit');
            Route::put('/{lead}', [LeadController::class , 'update'])->name('update');
            Route::delete('/{lead}', [LeadController::class , 'destroy'])->name('destroy');
            Route::get('/data/table', [LeadController::class , 'dataTable'])->name('data');
        }
        );

        // ── Projects (Admin, Manager) ──────────────────────────
        Route::middleware(['role:Admin|Manager'])->prefix('projects')->name('projects.')->group(function () {
            Route::get('/', [ProjectController::class , 'index'])->name('index');
            Route::get('/{project}', [ProjectController::class , 'show'])->name('show');
            Route::post('/from-lead/{lead}', [ProjectController::class , 'createFromLead'])->name('create-from-lead');
            Route::post('/{project}/activate', [ProjectController::class , 'activate'])->name('activate');
            Route::delete('/{project}', [ProjectController::class , 'destroy'])->name('destroy');
            Route::get('/data/table', [ProjectController::class , 'dataTable'])->name('data');
        }
        );

        // ── Concepts (Admin, Manager, Concept Writer) ──────────
        Route::middleware(['role:Admin|Manager|Concept Writer'])->prefix('concepts')->name('concepts.')->group(function () {
            Route::get('/', [ConceptController::class , 'index'])->name('index');
            Route::get('/project/{project}', [ConceptController::class , 'projectConcepts'])->name('project');
            Route::get('/assign/{project}', [ConceptController::class , 'assignForm'])->name('assign-form');
            Route::post('/assign/{project}', [ConceptController::class , 'assign'])->name('assign');
            Route::get('/task/{conceptTask}/submit', [ConceptController::class , 'submitForm'])->name('submit-form');
            Route::post('/task/{conceptTask}/submit', [ConceptController::class , 'submit'])->name('submit');
            Route::post('/{concept}/approve', [ConceptController::class , 'approve'])->name('approve');
            Route::post('/{concept}/reject', [ConceptController::class , 'reject'])->name('reject');
            Route::post('/{concept}/client-review', [ConceptController::class , 'sendToClientReview'])->name('client-review');
            Route::delete('/{concept}', [ConceptController::class , 'destroy'])->name('destroy');
            Route::get('/data/table', [ConceptController::class , 'dataTable'])->name('data');
            
            Route::post('/{conceptTask}/generate-link', [ConceptController::class, 'generateClientLink'])->name('generate-link');
        }
        );

        // ── Shoots (Admin, Manager, Shooting Person) ───────────
        Route::middleware(['role:Admin|Manager|Shooting Person'])->prefix('shoots')->name('shoots.')->group(function () {
            Route::get('/', [ShootController::class , 'index'])->name('index');
            Route::get('/create/{project}', [ShootController::class , 'create'])->name('create');
            Route::post('/create/{project}', [ShootController::class , 'store'])->name('store');
            Route::get('/{shoot}', [ShootController::class , 'show'])->name('show');
            Route::post('/{shoot}/checkin', [ShootController::class , 'checkin'])->name('checkin');
            Route::post('/{shoot}/checkout', [ShootController::class , 'checkout'])->name('checkout');
            Route::post('/{shoot}/suggest', [ShootController::class , 'suggestAdjustment'])->name('suggest');
            Route::delete('/{shoot}', [ShootController::class , 'destroy'])->name('destroy');
            Route::get('/data/table', [ShootController::class , 'dataTable'])->name('data');
            
            // Route::post('/{shoot}/suggest-adjustment', [ShootController::class, 'suggestAdjustment'])->name('suggest-adjustment');
        }
        );

        // ── Editing (Admin, Manager, Video Editor) ─────────────
        Route::middleware(['role:Admin|Manager|Video Editor'])->prefix('editing')->name('editing.')->group(function () {
            Route::get('/', [EditingController::class , 'index'])->name('index');
            Route::get('/assign/{project}', [EditingController::class , 'assignForm'])->name('assign-form');
            Route::post('/assign/{project}', [EditingController::class , 'assign'])->name('assign');
            Route::get('/{editTask}', [EditingController::class , 'show'])->name('show');
            Route::post('/{editTask}/update-count', [EditingController::class , 'updateCount'])->name('update-count');
            Route::post('/{editTask}/approve', [EditingController::class , 'approve'])->name('approve');
            Route::post('/{editTask}/revision', [EditingController::class , 'requestRevision'])->name('revision');
            Route::delete('/{editTask}', [EditingController::class , 'destroy'])->name('destroy');
            Route::get('/data/table', [EditingController::class , 'dataTable'])->name('data');
        }
        );

        // ── Reports (Admin, Manager) ───────────────────────────
        Route::middleware(['role:Admin|Manager'])->prefix('reports')->name('reports.')->group(function () {
            Route::get('/monthly', [ReportController::class , 'monthly'])->name('monthly');
            Route::get('/monthly/data', [ReportController::class , 'monthlyData'])->name('monthly-data');
        }
        );
    });

require __DIR__ . '/auth.php';
