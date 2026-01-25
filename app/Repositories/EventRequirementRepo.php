<?php

namespace App\Repositories;

use App\Models\EventRequirement;

class EventRequirementRepo extends AbstractRepoService
{
    public function __construct(EventRequirement $model)
    {
        parent::__construct($model);
    }
}
