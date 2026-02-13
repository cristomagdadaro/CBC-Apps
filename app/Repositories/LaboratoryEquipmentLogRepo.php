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

    /**
     * Get all laboratory equipment logs formatted for select fields
     */
    public function getOptions()
    {
        return $this->model
            ->newQuery()
            ->join('items', 'laboratory_equipment_logs.item_id', '=', 'items.id')
            ->select('laboratory_equipment_logs.id as name', 'items.name as label')
            ->distinct()
            ->get();
    }
}
