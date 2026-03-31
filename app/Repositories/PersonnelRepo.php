<?php

namespace App\Repositories;

use App\Models\Personnel;
use App\Services\Personnel\PersonnelIdService;

class PersonnelRepo extends AbstractRepoService
{
    public function __construct(Personnel $model, private readonly PersonnelIdService $personnelIdService)
    {
        parent::__construct($model);
    }

    public function create(array $data)
    {
        $payload = $this->normalizePersonnelPayload($data, true);
        $model = parent::create($payload);

        if ($model) {
            $model->forceFill(['updated_at' => null])->saveQuietly();
        }

        return $model;
    }

    public function update(int|string $id, array $data): Personnel
    {
        return parent::update($id, $this->normalizePersonnelPayload($data, false));
    }

    /**
     * Get all personnel formatted for select fields
     */
    public function getOptions()
    {
        return $this->model
            ->newQuery()
            ->selectRaw('id as name, CONCAT(fname, " ", COALESCE(mname, ""), " ", lname, " ", COALESCE(suffix, "")) as label')
            ->get();
    }

    /**
     * Get all personnel formatted for inventory form, only including those with transactions and matching the specified category IDs
     * Default category IDs are 1, 2, 3, 5, and 6 (consumables, non-consumables, chemicals, PPEs, and office supplies
    */
    public function getAllForInventoryForm($categoryIds = [1, 2, 3, 5, 6])
    {
        return $this->model->newQuery()->get();
    }

    public function previewNextExternalEmployeeId(): string
    {
        return $this->personnelIdService->previewNextExternalEmployeeId();
    }

    private function normalizePersonnelPayload(array $data, bool $isCreate): array
    {
        $isPhilRiceEmployee = filter_var($data['is_philrice_employee'] ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $isPhilRiceEmployee = $isPhilRiceEmployee ?? true;

        unset($data['is_philrice_employee']);

        if ($isCreate && ! $isPhilRiceEmployee) {
            $data['employee_id'] = $this->personnelIdService->consumeNextExternalEmployeeId();
        }

        return $data;
    }
}
