<?php

namespace App\Http\Controllers\Research;

use App\Http\Controllers\Controller;
use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchProject;
use App\Models\Research\ResearchSample;
use App\Models\Research\ResearchStudy;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ResearchPageController extends Controller
{
    public function dashboard(): Response
    {
        $recentProjects = ResearchProject::query()
            ->withCount('studies')
            ->latest('updated_at')
            ->limit(5)
            ->get();

        return Inertia::render('Research/ResearchDashboard', [
            'stats' => [
                'projects' => ResearchProject::query()->count(),
                'studies' => ResearchStudy::query()->count(),
                'experiments' => ResearchExperiment::query()->count(),
                'samples' => ResearchSample::query()->count(),
            ],
            'recentProjects' => $recentProjects,
            'catalog' => $this->catalog(),
            'permissionMatrix' => config('research.permission_matrix', []),
            'sampleIdentifierExample' => 'RI00010001Q',
            'currentUser' => Auth::user()?->only(['id', 'name', 'email']),
        ]);
    }

    public function projectsIndex(): Response
    {
        $projects = ResearchProject::query()
            ->withCount('studies')
            ->with([
                'studies' => fn ($query) => $query
                    ->withCount('experiments')
                    ->latest('updated_at'),
            ])
            ->latest('updated_at')
            ->get();

        return Inertia::render('Research/Projects/ResearchProjectIndex', [
            'projects' => $projects,
            'catalog' => $this->catalog(),
        ]);
    }

    public function projectCreate(): Response
    {
        return Inertia::render('Research/Projects/ResearchProjectCreate', [
            'catalog' => $this->catalog(),
        ]);
    }

    public function projectShow(ResearchProject $project): Response
    {
        $project->load([
            'studies' => fn ($query) => $query
                ->withCount('experiments')
                ->with([
                    'experiments' => fn ($experimentQuery) => $experimentQuery
                        ->withCount('samples')
                        ->latest('updated_at'),
                ])
                ->latest('updated_at'),
        ]);

        return Inertia::render('Research/Projects/ResearchProjectShow', [
            'project' => $project,
            'catalog' => $this->catalog(),
        ]);
    }

    public function experimentShow(ResearchExperiment $experiment): Response
    {
        $experiment->load([
            'study.project',
            'samples' => fn ($query) => $query
                ->with([
                    'monitoringRecords' => fn ($recordQuery) => $recordQuery->latest('recorded_on')->latest('id'),
                ])
                ->latest('updated_at'),
        ]);

        return Inertia::render('Research/Experiments/ResearchExperimentShow', [
            'experiment' => $experiment,
            'catalog' => $this->catalog(),
        ]);
    }

    protected function catalog(): array
    {
        return [
            'commodities' => config('research.commodities', []),
            'sample_types' => config('research.sample_types', []),
            'seasons' => config('research.seasons', []),
            'stages' => config('research.stages', []),
        ];
    }
}
