<?php

namespace App\Repositories;

use App\Models\Research\ResearchMonitoringRecord;

class ResearchMonitoringRecordRepo extends AbstractRepoService
{
    public function __construct(ResearchMonitoringRecord $model)
    {
        parent::__construct($model);
    }
}
