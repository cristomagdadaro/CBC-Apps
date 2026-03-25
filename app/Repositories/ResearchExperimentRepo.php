<?php

namespace App\Repositories;

use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchMonitoringRecord;
use App\Models\Research\ResearchSample;
use Illuminate\Support\Facades\DB;

class ResearchExperimentRepo extends AbstractRepoService
{
    public function __construct(ResearchExperiment $model)
    {
        parent::__construct($model);
    }

    public function deleteCascade(ResearchExperiment $experiment): void
    {
        DB::transaction(function () use ($experiment) {
            $sampleIds = ResearchSample::query()->where('experiment_id', $experiment->id)->pluck('id');

            ResearchMonitoringRecord::query()->whereIn('sample_id', $sampleIds)->delete();
            ResearchSample::query()->whereIn('id', $sampleIds)->delete();
            $experiment->delete();
        });
    }
}
