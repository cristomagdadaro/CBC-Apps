<?php

namespace App\Repositories;

use App\Models\EventSubformResponse;

class EventSubformRepo extends AbstractRepoService
{
    public function __construct(EventSubformResponse $model)
    {
        parent::__construct($model);
    }
}
