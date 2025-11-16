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
        $data = $request->validated();

        $requesterData = [
            'name' => $data['name'],
            'affiliation' => $data['affiliation'],
            'email' => $data['email'],
            'position' => $data['position'] ?? null,
            'phone' => $data['phone'],
        ];

        $requester = isset($data['requester_id'])
            ? Requester::findOrFail($data['requester_id'])
            : Requester::create($requesterData);

        $formData = [
            'request_type' => $data['request_type'],
            'request_details' => $data['request_details'] ?? null,
            'request_purpose' => $data['request_purpose'],
            'project_title' => $data['project_title'] ?? null,
            'date_of_use' => $data['date_of_use'],
            'time_of_use' => $data['time_of_use'],
            'labs_to_use' => $data['labs_to_use'] ?? [],
            'equipments_to_use' => $data['equipments_to_use'] ?? [],
            'consumables_to_use' => $data['consumables_to_use'] ?? [],
        ];

        $requestForm = isset($data['form_id'])
            ? UseRequestForm::findOrFail($data['form_id'])
            : UseRequestForm::create($formData);

        $pivotData = [
            'requester_id' => $requester->id,
            'form_id' => $requestForm->id,
            'request_status' => $data['request_status'] ?? 'pending',
            'agreed_clause_1' => $data['agreed_clause_1'] ?? false,
            'agreed_clause_2' => $data['agreed_clause_2'] ?? false,
            'agreed_clause_3' => $data['agreed_clause_3'] ?? false,
            'disapproved_remarks' => $data['disapproved_remarks'] ?? null,
            'approval_constraint' => $data['approval_constraint'] ?? null,
            'approved_by' => $data['approved_by'] ?? null,
        ];

        return RequestFormPivot::create($pivotData);
    }

    public function update(UpdateRequestFormPivot $request, $request_pivot_id = null): Model
    {
        return parent::_update($request_pivot_id, $request);
    }
}
