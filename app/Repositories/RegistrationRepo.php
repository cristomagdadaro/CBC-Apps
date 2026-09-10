<?php

namespace App\Repositories;

use App\Models\Registration;

class RegistrationRepo extends AbstractRepoService
{
    public function __construct(Registration $model)
    {
        parent::__construct($model);
    }

    public function findWithLock(string $id): ?Registration
    {
        return $this->model->where('id', $id)->lockForUpdate()->first();
    }
}
