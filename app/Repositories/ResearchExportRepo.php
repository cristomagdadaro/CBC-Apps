<?php

namespace App\Repositories;

use App\Models\Research\ResearchExperiment;

class ResearchExportRepo
{
    public function hydrateExperimentForSampleExport(ResearchExperiment $experiment): ResearchExperiment
    {
        $experiment->load([
            'study.project',
            'samples.monitoringRecords',
        ]);

        return $experiment;
    }
}
