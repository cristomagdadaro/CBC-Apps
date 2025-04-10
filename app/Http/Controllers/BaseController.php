<?php

namespace App\Http\Controllers;

use App\Repositories\AbstractRepoService;
use Illuminate\Database\Eloquent\Model;
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

    public function _store($request): Model
    {
        return $this->service->create($request->validated());
    }

    public function _update(string $id, $request): Model
    {
        return $this->service->update($id, $request->validated());
    }

    public function _destroy(string $id): Model
    {
        return $this->service->delete($id);
    }
}
