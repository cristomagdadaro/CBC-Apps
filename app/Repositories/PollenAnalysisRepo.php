<?php

namespace App\Repositories;

use App\Models\PollenAnalysis;

class PollenAnalysisRepo extends AbstractRepoService
{
    public function __construct(PollenAnalysis $model)
    {
        parent::__construct($model);
    }

    public function getModelClass(): string
    {
        return PollenAnalysis::class;
    }
}
