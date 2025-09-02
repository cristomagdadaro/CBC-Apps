<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRequester;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class RequesterController extends BaseController
{
    public function __construct(RequesterRepo $repository)
    {
        $this->service = $repository;
    }

    public function create(CreateRequester $request): Model
    {
        return parent::_store($request);
    }
}
