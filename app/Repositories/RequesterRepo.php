<?php

namespace App\Repositories;

use App\Models\Requester;

class RequesterRepo extends AbstractRepoService
{
    public function __construct(Requester $model)
    {
        parent::__construct($model);
    }
}
