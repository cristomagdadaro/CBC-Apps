<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRequestForm;
use App\Repositories\RequesterFormRepo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class RequestFormController extends BaseController
{
    public function __construct(RequesterFormRepo $repository){
        $this->service = $repository;
    }

    public function create(CreateRequestForm $request): Model
    {
        return parent::_store($request);
    }
}
