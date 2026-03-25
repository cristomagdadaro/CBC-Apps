<?php

namespace App\Repositories;

use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchMonitoringRecord;
use App\Models\Research\ResearchProject;
use App\Models\Research\ResearchSample;
use App\Models\Research\ResearchStudy;
use Illuminate\Support\Facades\DB;

class ResearchProjectRepo extends AbstractRepoService
{
    public function __construct(ResearchProject $model)
    {
        parent::__construct($model);
    }

    public function deleteCascade(ResearchProject $project): void
    {
        DB::transaction(function () use ($project) {
            $studyIds = ResearchStudy::query()->where('project_id', $project->id)->pluck('id');
            $experimentIds = ResearchExperiment::query()->whereIn('study_id', $studyIds)->pluck('id');
            $sampleIds = ResearchSample::query()->whereIn('experiment_id', $experimentIds)->pluck('id');

            ResearchMonitoringRecord::query()->whereIn('sample_id', $sampleIds)->delete();
            ResearchSample::query()->whereIn('id', $sampleIds)->delete();
            ResearchExperiment::query()->whereIn('id', $experimentIds)->delete();
            ResearchStudy::query()->whereIn('id', $studyIds)->delete();
            $project->delete();
        });
    }
}
