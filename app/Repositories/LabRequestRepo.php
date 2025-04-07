<?php

namespace App\Repositories;

use App\Models\LabRequest;

class LabRequestRepo extends AbstractRepoService
{
    public function __construct(LabRequest $model)
    {
        parent::__construct($model);
    }
}
