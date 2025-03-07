<?php

namespace App\Repositories;

use App\Models\Participant;

class ParticipantRepo extends AbstractRepoService
{
    public function __construct(Participant $model)
    {
        parent::__construct($model);
    }
}
