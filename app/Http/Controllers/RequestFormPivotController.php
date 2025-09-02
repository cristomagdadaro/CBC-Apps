<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLabRequest;
use App\Http\Requests\CreateRequestFormPivot;
use App\Http\Requests\GetLabRequest;
use App\Http\Requests\UpdateRequestFormPivot;
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

    public function create(CreateRequestFormPivot $request): Model
    {
        return parent::_store($request);
    }

    public function update(UpdateRequestFormPivot $request, $request_pivot_id = null): Model
    {
        return parent::_update($request_pivot_id, $request);
    }
}
