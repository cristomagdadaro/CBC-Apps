<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLabRequest;
use App\Http\Requests\GetLabRequest;
use App\Repositories\RequestFormPivotRepo;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class RequestFormPivotController extends BaseController
{
    public function __construct(RequestFormPivotRepo $repository)
    {
        $this->service = $repository;
    }

    public function index(GetLabRequest $request, $request_id = null): Collection
    {
        return parent::_index($request);
    }

    public function create(CreateLabRequest $request, $request_id = null): Model
    {
        return parent::_store($request);
    }
}
