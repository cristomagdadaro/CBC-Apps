<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormScanRequest;
use App\Repositories\FormScanRepo;
use Illuminate\Http\JsonResponse;

class FormScanController extends BaseController
{
    protected function repo(): FormScanRepo
    {
        return $this->service;
    }

    public function __construct(FormScanRepo $repository)
    {
        $this->service = $repository;
    }

    public function scan(FormScanRequest $request, string $event_id): JsonResponse
    {
        return $this->repo()->processScan($event_id, $request->validated());
    }
}
