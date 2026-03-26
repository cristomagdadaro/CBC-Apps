<?php

namespace App\Repositories;

use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchProject;
use App\Models\Research\ResearchSample;
use App\Models\Research\ResearchStudy;
use App\Models\User;
use App\Services\Research\ResearchAccessService;

class ResearchPageRepo
{
    public function __construct(private readonly ResearchAccessService $accessService)
    {
    }

    public function dashboardPayload(?User $user): array
    {
        $visibleProjectIds = $this->accessService->visibleProjectIdsQuery($user);

        $recentProjects = $this->accessService->visibleProjectsQuery($user)
            ->withCount('studies')
            ->latest('updated_at')
            ->limit(5)
            ->get();

        return [
            'stats' => [
                'projects' => $this->accessService->visibleProjectsQuery($user)->count(),
                'studies' => ResearchStudy::query()->whereIn('project_id', $visibleProjectIds)->count(),
                'experiments' => ResearchExperiment::query()
                    ->whereIn('study_id', ResearchStudy::query()->select('id')->whereIn('project_id', $visibleProjectIds))
                    ->count(),
                'samples' => ResearchSample::query()
                    ->whereIn('experiment_id', ResearchExperiment::query()
                        ->select('id')
                        ->whereIn('study_id', ResearchStudy::query()->select('id')->whereIn('project_id', $visibleProjectIds)))
                    ->count(),
            ],
            'recentProjects' => $recentProjects,
        ];
    }

    public function projectsIndexData(?User $user)
    {
        return $this->accessService->visibleProjectsQuery($user)
            ->withCount('studies')
            ->with([
                'studies' => fn ($query) => $query
                    ->withCount('experiments')
                    ->latest('updated_at'),
            ])
            ->latest('updated_at')
            ->get();
    }

    public function hydrateProject(ResearchProject $project): ResearchProject
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

        return $project;
    }

    public function hydrateExperiment(ResearchExperiment $experiment): ResearchExperiment
    {
        $experiment->load([
            'study.project',
            'samples' => fn ($query) => $query
                ->with([
                    'monitoringRecords' => fn ($recordQuery) => $recordQuery->latest('recorded_on')->latest('id'),
                ])
                ->latest('updated_at'),
        ]);

        return $experiment;
    }
}
