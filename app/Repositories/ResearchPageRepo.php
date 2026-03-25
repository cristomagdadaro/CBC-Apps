<?php

namespace App\Repositories;

use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchProject;
use App\Models\Research\ResearchSample;
use App\Models\Research\ResearchStudy;

class ResearchPageRepo
{
    public function dashboardPayload(): array
    {
        $recentProjects = ResearchProject::query()
            ->withCount('studies')
            ->latest('updated_at')
            ->limit(5)
            ->get();

        return [
            'stats' => [
                'projects' => ResearchProject::query()->count(),
                'studies' => ResearchStudy::query()->count(),
                'experiments' => ResearchExperiment::query()->count(),
                'samples' => ResearchSample::query()->count(),
            ],
            'recentProjects' => $recentProjects,
        ];
    }

    public function projectsIndexData()
    {
        return ResearchProject::query()
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
