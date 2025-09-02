<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLabRequest;
use App\Http\Requests\CreateRequestFormPivot;
use App\Http\Requests\GetLabRequest;
use App\Http\Requests\UpdateRequestFormPivot;
use App\Models\Requester;
use App\Models\RequestFormPivot;
use App\Models\UseRequestForm;
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
        $requester = Requester::create($request->validated());
        $requestForm = UseRequestForm::create($request->validated());

        $pivotData = array_merge($request->validated(), [
            'requester_id' => $requester->id,
            'form_id' => $requestForm->id,
        ]);

        return RequestFormPivot::create($pivotData);
    }

    public function update(UpdateRequestFormPivot $request, $request_pivot_id = null): Model
    {
        return parent::_update($request_pivot_id, $request);
    }
}
