<?php

namespace App\Repositories;

use App\Models\Research\ResearchExperiment;
use App\Models\Research\ResearchMonitoringRecord;
use App\Models\Research\ResearchSample;
use App\Services\Research\ResearchAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ResearchExperimentRepo extends AbstractRepoService
{
    public function __construct(ResearchExperiment $model, private readonly ResearchAccessService $accessService)
    {
        parent::__construct($model);

        $this->appendWith = ['study', 'study.project'];
        $this->appendCount = ['samples'];
    }

    protected function buildSearchQuery(Collection $parameters, bool $withPagination, bool $isTrashed)
    {
        $builder = $this->model->newQuery()
            ->whereHas('study', function (Builder $studyQuery) {
                $studyQuery->whereIn('project_id', $this->accessService->visibleProjectIdsQuery(request()->user()));
            });

        $this->applyProjectFilter($builder, $parameters);
        $this->applyStudyFilter($builder, $parameters);
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
            $query->whereHas('study', function (Builder $studyQuery) use ($projectId) {
                $studyQuery->where('project_id', $projectId);
            });
        }
    }

    protected function applyStudyFilter(Builder $query, Collection $parameters): void
    {
        $studyId = $parameters->get('study_id');

        if ($studyId) {
            $query->where('study_id', $studyId);
        }
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
