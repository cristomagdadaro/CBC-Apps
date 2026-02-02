<?php

namespace App\Repositories;

use App\Models\EventSubform;

class EventSubformRepo extends AbstractRepoService
{
    public function __construct(EventSubform $model)
    {
        parent::__construct($model);
    }
}
