<?php

namespace App\Repositories;

use App\Models\LabRequestForm;

class LabRequestFormRepo extends AbstractRepoService
{
    public function __construct(LabRequestForm $model)
    {
        parent::__construct($model);
    }
}
