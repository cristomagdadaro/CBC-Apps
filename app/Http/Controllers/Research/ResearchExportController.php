<?php

namespace App\Http\Controllers\Research;

use App\Http\Controllers\BaseController;
use App\Models\Research\ResearchExperiment;
use App\Repositories\ResearchExportRepo;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResearchExportController extends BaseController
{
    public function __construct(private readonly ResearchExportRepo $exportRepo)
    {
    }

    public function experimentSamplesCsv(ResearchExperiment $experiment): StreamedResponse
    {
        $experiment = $this->exportRepo->hydrateExperimentForSampleExport($experiment);

        $filename = sprintf(
            '%s-sample-monitoring-%s.csv',
            strtolower($experiment->code),
            now()->format('Ymd-His')
        );

        return response()->streamDownload(function () use ($experiment) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'project_code',
                'study_code',
                'experiment_code',
                'sample_uid',
                'sample_name',
                'commodity',
                'sample_type',
                'pr_code',
                'line_label',
                'plant_label',
                'replication_number',
                'status',
                'stage',
                'recorded_on',
                'selected_for_export',
                'parameter_set',
                'notes',
            ]);

            foreach ($experiment->samples as $sample) {
                if ($sample->monitoringRecords->isEmpty()) {
                    fputcsv($handle, [
                        $experiment->study?->project?->code,
                        $experiment->study?->code,
                        $experiment->code,
                        $sample->uid,
                        $sample->accession_name,
                        $sample->commodity,
                        $sample->sample_type,
                        $sample->pr_code,
                        $sample->line_label,
                        $sample->plant_label,
                        $sample->replication_number,
                        $sample->current_status,
                        '',
                        '',
                        '',
                        '',
                        '',
                    ]);

                    continue;
                }

                foreach ($sample->monitoringRecords as $record) {
                    fputcsv($handle, [
                        $experiment->study?->project?->code,
                        $experiment->study?->code,
                        $experiment->code,
                        $sample->uid,
                        $sample->accession_name,
                        $sample->commodity,
                        $sample->sample_type,
                        $sample->pr_code,
                        $sample->line_label,
                        $sample->plant_label,
                        $sample->replication_number,
                        $sample->current_status,
                        $record->stage,
                        optional($record->recorded_on)->format('Y-m-d'),
                        $record->selected_for_export ? 'yes' : 'no',
                        json_encode($record->parameter_set ?? [], JSON_UNESCAPED_UNICODE),
                        $record->notes,
                    ]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
