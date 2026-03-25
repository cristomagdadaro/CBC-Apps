<?php

namespace App\Repositories;

use App\Models\Research\ResearchMonitoringRecord;
use App\Models\Research\ResearchSample;
use Illuminate\Support\Facades\DB;

class ResearchSampleRepo extends AbstractRepoService
{
    public function __construct(ResearchSample $model)
    {
        parent::__construct($model);
    }

    public function deleteCascade(ResearchSample $sample): void
    {
        DB::transaction(function () use ($sample) {
            ResearchMonitoringRecord::query()->where('sample_id', $sample->id)->delete();
            $sample->delete();
        });
    }
}
