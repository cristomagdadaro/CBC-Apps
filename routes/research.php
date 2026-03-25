<?php

use App\Http\Controllers\Research\ResearchExperimentController;
use App\Http\Controllers\Research\ResearchExportController;
use App\Http\Controllers\Research\ResearchMonitoringRecordController;
use App\Http\Controllers\Research\ResearchProjectController;
use App\Http\Controllers\Research\ResearchSampleController;
use App\Http\Controllers\Research\ResearchStudyController;
use Illuminate\Support\Facades\Route;

Route::prefix('research')->group(function () {
    Route::middleware(['can:research.projects.create'])->post('/projects', [ResearchProjectController::class, 'store'])->name('api.research.projects.store');
    Route::middleware(['can:research.projects.update'])->put('/projects/{project}', [ResearchProjectController::class, 'update'])->name('api.research.projects.update');
    Route::middleware(['can:research.projects.delete'])->delete('/projects/{project}', [ResearchProjectController::class, 'destroy'])->name('api.research.projects.destroy');

    Route::middleware(['can:research.studies.manage'])->post('/studies', [ResearchStudyController::class, 'store'])->name('api.research.studies.store');
    Route::middleware(['can:research.studies.manage'])->put('/studies/{study}', [ResearchStudyController::class, 'update'])->name('api.research.studies.update');
    Route::middleware(['can:research.studies.manage'])->delete('/studies/{study}', [ResearchStudyController::class, 'destroy'])->name('api.research.studies.destroy');

    Route::middleware(['can:research.experiments.manage'])->post('/experiments', [ResearchExperimentController::class, 'store'])->name('api.research.experiments.store');
    Route::middleware(['can:research.experiments.manage'])->put('/experiments/{experiment}', [ResearchExperimentController::class, 'update'])->name('api.research.experiments.update');
    Route::middleware(['can:research.experiments.manage'])->delete('/experiments/{experiment}', [ResearchExperimentController::class, 'destroy'])->name('api.research.experiments.destroy');

    Route::middleware(['can:research.samples.manage'])->post('/samples', [ResearchSampleController::class, 'store'])->name('api.research.samples.store');
    Route::middleware(['can:research.samples.manage'])->put('/samples/{sample}', [ResearchSampleController::class, 'update'])->name('api.research.samples.update');
    Route::middleware(['can:research.samples.manage'])->delete('/samples/{sample}', [ResearchSampleController::class, 'destroy'])->name('api.research.samples.destroy');

    Route::middleware(['can:research.monitoring.manage'])->post('/records', [ResearchMonitoringRecordController::class, 'store'])->name('api.research.records.store');
    Route::middleware(['can:research.monitoring.manage'])->put('/records/{record}', [ResearchMonitoringRecordController::class, 'update'])->name('api.research.records.update');
    Route::middleware(['can:research.monitoring.manage'])->delete('/records/{record}', [ResearchMonitoringRecordController::class, 'destroy'])->name('api.research.records.destroy');

    Route::middleware(['can:research.exports.manage'])->get('/experiments/{experiment}/export/samples', [ResearchExportController::class, 'experimentSamplesCsv'])->name('api.research.experiments.export.samples');
});
