<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepo extends AbstractRepoService
{
    protected array $searchable = [
        'name',
        'description',
    ];

    public function __construct(Category $model)
    {
        parent::__construct($model);
    }
}
