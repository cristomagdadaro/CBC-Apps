<?php

namespace App\Repositories;

use App\Models\Item;
use App\Repositories\AbstractRepoService;
use Illuminate\Database\Eloquent\Builder;

class ItemRepo extends AbstractRepoService
{
    public function __construct(Item $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all items formatted for select fields
     */
    public function getOptions()
    {
        return $this->model
            ->newQuery()
            ->selectRaw('id as name, CONCAT(name, " (", COALESCE(brand, ""), IF(description != "", CONCAT(" - ", description), ""), ")") as label')
            ->get();
    }
}
