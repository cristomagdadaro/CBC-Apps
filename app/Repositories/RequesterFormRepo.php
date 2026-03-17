<?php

namespace App\Repositories;

use App\Models\UseRequestForm;

class RequesterFormRepo extends AbstractRepoService
{
    public function __construct(UseRequestForm $model)
    {
        parent::__construct($model);
    }
}
