<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLabRequest;
use App\Http\Requests\GetLabRequest;
use App\Repositories\LabRequestRepo;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Illuminate\Database\Eloquent\Model;

class LabRequestController extends BaseController
{
    public function __construct(LabRequestRepo $repository)
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
