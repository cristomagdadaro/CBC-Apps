<?php

use App\Http\Controllers\Research\ResearchPageController;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('laboratory')->group(function () {
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_EXPERIMENT_MONITORING])->get('/experiments-monitoring', function () {
        return Inertia::render('Laboratory/ExperimentsMonitoring/ExperimentsMonitoringGuest');
    })->name('laboratory.monitoring.guest');
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::middleware(['can:research.dashboard.view', 'deployment.access:' . DeploymentAccessService::MODULE_RESEARCH])->prefix('research')->group(function () {
        Route::get('/', [ResearchPageController::class, 'dashboard'])->name('research.dashboard');
        Route::get('/projects', [ResearchPageController::class, 'projectsIndex'])->middleware('can:research.projects.view')->name('research.projects.index');
        Route::get('/projects/create', [ResearchPageController::class, 'projectCreate'])->middleware('can:research.projects.create')->name('research.projects.create');
        Route::get('/projects/{project}/studies/create', [ResearchPageController::class, 'studyCreate'])->middleware(['can:research.studies.manage', 'can:update,project'])->name('research.studies.create');
        Route::get('/projects/{project}', [ResearchPageController::class, 'projectShow'])->middleware(['can:research.projects.view', 'can:view,project'])->name('research.projects.show');
        Route::get('/studies/{study}', [ResearchPageController::class, 'studyShow'])->middleware(['can:research.projects.view', 'can:view,study'])->name('research.studies.show');
        Route::get('/studies/{study}/experiments/create', [ResearchPageController::class, 'experimentCreate'])->middleware(['can:research.experiments.manage', 'can:update,study'])->name('research.experiments.create');
        Route::get('/experiments/{experiment}', [ResearchPageController::class, 'experimentShow'])->middleware(['can:research.projects.view', 'can:view,experiment'])->name('research.experiments.show');
        Route::get('/samples/inventory', [ResearchPageController::class, 'sampleInventory'])->middleware('can:research.samples.manage')->name('research.samples.inventory');
    });
});
