<?php

namespace App\Repositories;

use App\Models\Personnel;

class PersonnelRepo extends AbstractRepoService
{
    public function __construct(Personnel $model)
    {
        parent::__construct($model);
    }
}
