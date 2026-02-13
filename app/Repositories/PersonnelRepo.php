<?php

namespace App\Repositories;

use App\Models\Personnel;

class PersonnelRepo extends AbstractRepoService
{
    public function __construct(Personnel $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all personnel formatted for select fields
     */
    public function getOptions()
    {
        return $this->model
            ->newQuery()
            ->selectRaw('id as name, CONCAT(fname, " ", COALESCE(mname, ""), " ", lname, " ", COALESCE(suffix, "")) as label')
            ->get();
    }

    public function getAllForInventoryForm()
    {
        return $this->model->newQuery()->get();
    }
}
