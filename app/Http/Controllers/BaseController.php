<?php

namespace App\Http\Controllers;

use App\Repositories\AbstractRepoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BaseController extends Controller
{
    protected AbstractRepoService $service;

    public function _index($request): Collection
    {
        $data = $this->service->search(new Collection($request->validated()));
        return new Collection($data);
    }

    public function _store($request): JsonResponse
    {
        return $this->service->create($request->validated());
    }
}
