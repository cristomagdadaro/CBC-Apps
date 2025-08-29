<?php

namespace App\Repositories;

use App\Models\UseRequestForm;

class LabRequestFormRepo extends AbstractRepoService
{
    public function __construct(UseRequestForm $model)
    {
        parent::__construct($model);
    }
}
