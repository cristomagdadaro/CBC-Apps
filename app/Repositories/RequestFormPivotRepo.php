<?php

namespace App\Repositories;

use App\Models\RequestFormPivot;

class RequestFormPivotRepo extends AbstractRepoService
{
    public function __construct(RequestFormPivot $model)
    {
        parent::__construct($model);
    }
}
