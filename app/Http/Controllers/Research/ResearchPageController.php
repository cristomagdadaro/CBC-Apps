<?php

namespace App\Http\Controllers\Research;

use App\Http\Controllers\BaseController;
use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchProject;
use App\Repositories\ResearchPageRepo;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ResearchPageController extends BaseController
{
    public function __construct(private readonly ResearchPageRepo $pageRepo)
    {
    }

    public function dashboard(): Response
    {
        $payload = $this->pageRepo->dashboardPayload();

        return Inertia::render('Research/ResearchDashboard', [
            'stats' => $payload['stats'],
            'recentProjects' => $payload['recentProjects'],
            'catalog' => $this->catalog(),
            'permissionMatrix' => config('research.permission_matrix', []),
            'sampleIdentifierExample' => 'RI00010001Q',
            'currentUser' => Auth::user()?->only(['id', 'name', 'email']),
        ]);
    }

    public function projectsIndex(): Response
    {
        $projects = $this->pageRepo->projectsIndexData();

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
        $project = $this->pageRepo->hydrateProject($project);

        return Inertia::render('Research/Projects/ResearchProjectShow', [
            'project' => $project,
            'catalog' => $this->catalog(),
        ]);
    }

    public function experimentShow(ResearchExperiment $experiment): Response
    {
        $experiment = $this->pageRepo->hydrateExperiment($experiment);

        return Inertia::render('Research/Experiments/ResearchExperimentShow', [
            'experiment' => $experiment,
            'catalog' => $this->catalog(),
        ]);
    }

    public function sampleInventory(): Response
    {
        return Inertia::render('Research/Samples/ResearchSampleInventory', [
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
