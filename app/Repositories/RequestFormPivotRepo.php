<?php

namespace App\Repositories;

use App\Models\Requester;
use App\Models\RequestFormPivot;
use App\Models\UseRequestForm;
use Illuminate\Database\Eloquent\Model;

class RequestFormPivotRepo extends AbstractRepoService
{
    public function __construct(RequestFormPivot $model)
    {
        parent::__construct($model);
    }

    public function createRequestPivot(array $data): Model
    {
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

        $requestTypes = $data['request_type'];
        if (!is_array($requestTypes)) {
            $requestTypes = array_filter([$requestTypes]);
        }

        $formData = [
            'request_type' => array_values(array_unique($requestTypes)),
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

    public function getGuestFormById(?string $requestId): ?RequestFormPivot
    {
        if (!$requestId) {
            return null;
        }

        return $this->model
            ->newQuery()
            ->where('id', $requestId)
            ->with(['requester','request_form'])
            ->first();
    }

    public function getForPdf(string $id): RequestFormPivot
    {
        /** @var RequestFormPivot $record */
        $record = $this->model
            ->newQuery()
            ->with(['requester', 'request_form'])
            ->findOrFail($id);

        return $record;
    }
}
