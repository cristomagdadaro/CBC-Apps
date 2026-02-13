<?php

namespace App\Repositories;

use App\Models\EventSubform;

class EventSubformRepo extends AbstractRepoService
{
    public function __construct(EventSubform $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all event subforms formatted for select fields
     */
    public function getOptions()
    {
        return $this->model
            ->newQuery()
            ->select('id as name', 'form_type as label')
            ->get();
    }
}
