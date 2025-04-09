<?php

namespace App\Repositories;

use App\Models\Personnel;

class PersonnelRepo extends AbstractRepoService
{
    protected array $searchable = [
        'id',
        'fname',
        'mname',
        'lname',
        'suffix',
        'position',
        'phone',
        'address',
        'email',
    ];

    public function __construct(Personnel $model)
    {
        parent::__construct($model);
    }
}
