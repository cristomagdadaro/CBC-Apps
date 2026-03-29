<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLabRequest;
use App\Http\Requests\CreateRequestFormPivot;
use App\Http\Requests\GetLabRequest;
use App\Http\Requests\UpdateRequestFormPivot;
use App\Repositories\RequestFormPivotRepo;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use App\Models\RequestFormPivot;

class RequestFormPivotController extends BaseController
{
    public function __construct(RequestFormPivotRepo $repository)
    {
        $this->service = $repository;
    }

    protected function repo(): RequestFormPivotRepo
    {
        return $this->service;
    }

    public function index(GetLabRequest $request, $request_id = null): Collection
    {
        return parent::_index($request);
    }

    public function create(CreateRequestFormPivot $request): JsonResponse
    {
        return response()->json(
            $this->repo()->createRequestPivot($request->validated()),
            201
        );
    }

    public function update(UpdateRequestFormPivot $request, $request_pivot_id = null): Model
    {
        $pivot = RequestFormPivot::query()->findOrFail($request_pivot_id);
        $this->authorize('update', $pivot);

        return $this->repo()->updateApprovalWithPipeline($request_pivot_id, $request->validated());
    }
}
