<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepo extends AbstractRepoService
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }
    
    /**
     * Get categories for inventory form, only including those with items and matching the specified category IDs
    */
    public function getInventoryFormCategories($categoryIds = [])
    {
        //if empty, default to all
        if (empty($categoryIds)) {
           return $this->model->newQuery()->select('id as name', 'name as label')->has('items')->get();
        }

        return $this->model
            ->newQuery()
            ->select('id as name', 'name as label')
            ->whereIn('id', (array) $categoryIds)
            ->has('items')
            ->get();
    }

    /**
     * Get all categories formatted for select fields
     */
    public function getOptions()
    {
        return $this->model
            ->newQuery()
            ->select('id as name', 'name as label')
            ->get();
    }
}
