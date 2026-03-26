<?php

namespace App\Repositories;

use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchMonitoringRecord;
use App\Models\Research\ResearchSample;
use App\Models\Research\ResearchStudy;
use App\Services\Research\ResearchAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ResearchStudyRepo extends AbstractRepoService
{
    public function __construct(ResearchStudy $model, private readonly ResearchAccessService $accessService)
    {
        parent::__construct($model);

        $this->appendWith = ['project'];
        $this->appendCount = ['experiments'];
    }

    protected function buildSearchQuery(Collection $parameters, bool $withPagination, bool $isTrashed)
    {
        $builder = $this->model->newQuery()
            ->whereIn('project_id', $this->accessService->visibleProjectIdsQuery(request()->user()));

        $this->applyProjectFilter($builder, $parameters);
        $this->applyAppends($builder, $parameters);
        $this->applySearchFilters($builder, $parameters);
        $this->applyGroupBy($builder, $parameters);
        $this->applySorting($builder, $parameters);

        if ($withPagination) {
            return $this->applyPagination($builder, $parameters);
        }

        return $builder->get();
    }

    protected function applyProjectFilter(Builder $query, Collection $parameters): void
    {
        $projectId = $parameters->get('project_id');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }
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
