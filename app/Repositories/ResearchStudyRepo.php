<?php

namespace App\Repositories;

use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchMonitoringRecord;
use App\Models\Research\ResearchSample;
use App\Models\Research\ResearchStudy;
use Illuminate\Support\Facades\DB;

class ResearchStudyRepo extends AbstractRepoService
{
    public function __construct(ResearchStudy $model)
    {
        parent::__construct($model);
    }

    public function deleteCascade(ResearchStudy $study): void
    {
        DB::transaction(function () use ($study) {
            $experimentIds = ResearchExperiment::query()->where('study_id', $study->id)->pluck('id');
            $sampleIds = ResearchSample::query()->whereIn('experiment_id', $experimentIds)->pluck('id');

            ResearchMonitoringRecord::query()->whereIn('sample_id', $sampleIds)->delete();
            ResearchSample::query()->whereIn('id', $sampleIds)->delete();
            ResearchExperiment::query()->whereIn('id', $experimentIds)->delete();
            $study->delete();
        });
    }
}
