<?php

namespace App\Repositories;

use App\Models\Form;

class FormRepo extends AbstractRepoService
{
    public function __construct(Form $model)
    {
        parent::__construct($model);
    }
}
