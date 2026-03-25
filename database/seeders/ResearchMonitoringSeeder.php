<?php

namespace Database\Seeders;

use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchMonitoringRecord;
use App\Models\Research\ResearchProject;
use App\Models\Research\ResearchSample;
use App\Models\Research\ResearchSampleInventoryLog;
use App\Models\Research\ResearchStudy;
use Illuminate\Database\Seeder;

class ResearchMonitoringSeeder extends Seeder
{
    public function run(): void
    {
        ResearchProject::factory()
            ->count(3)
            ->create()
            ->each(function (ResearchProject $project) {
                ResearchStudy::factory()
                    ->count(2)
                    ->for($project, 'project')
                    ->create()
                    ->each(function (ResearchStudy $study) {
                        ResearchExperiment::factory()
                            ->count(2)
                            ->for($study, 'study')
                            ->create()
                            ->each(function (ResearchExperiment $experiment) {
                                ResearchSample::factory()
                                    ->count(8)
                                    ->for($experiment, 'experiment')
                                    ->create()
                                    ->each(function (ResearchSample $sample) {
                                        ResearchMonitoringRecord::factory()
                                            ->count(3)
                                            ->for($sample, 'sample')
                                            ->create();

                                        ResearchSampleInventoryLog::query()->create([
                                            'sample_id' => $sample->id,
                                            'action' => 'seed_init',
                                            'barcode_value' => $sample->uid,
                                            'qr_payload' => 'sample:' . $sample->uid,
                                            'context' => ['source' => 'ResearchMonitoringSeeder'],
                                        ]);
                                    });
                            });
                    });
            });
    }
}
