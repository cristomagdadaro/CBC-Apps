<?php

namespace App\Repositories;

use App\Models\SuppEquipReport;

class SuppEquipReportRepo extends AbstractRepoService
{
    public function __construct(SuppEquipReport $model)
    {
        parent::__construct($model);

        $this->appendWith = ['transaction.item', 'transaction.user', 'transaction.personnel', 'item', 'user'];
    }
}
