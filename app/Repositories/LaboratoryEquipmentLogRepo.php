<?php

namespace App\Repositories;

use App\Models\LaboratoryEquipmentLog;

class LaboratoryEquipmentLogRepo extends AbstractRepoService
{
    public array $appendWith = ['equipment', 'personnel'];

    public function __construct(LaboratoryEquipmentLog $model)
    {
        parent::__construct($model);
    }
}
