<?php

namespace App\Repositories;

use App\Models\Registration;

class RegistrationRepo extends AbstractRepoService
{
    public function __construct(Registration $model)
    {
        parent::__construct($model);
    }
}
