<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetRequest;
use App\Http\Requests\CreateRequest;
use App\Http\Requests\UpdateRequest;
use App\Http\Requests\DeleteRequest;
use App\Repositories\AbstractRepoService;
use GuzzleHttp\Promise\Create;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class BaseController extends Controller
{
    protected AbstractRepoService $service;

    protected function _index($request): Collection
    {
        $data = $this->service->search(new Collection($request->validated()));
        return new Collection($data);
    }

    protected function _store($request): Model
    {
        return $this->service->create($request->validated());
    }

    protected function _update(string $id, $request): Model
    {
        return $this->service->update($id, $request->validated());
    }

    protected function _destroy(string $id): Model
    {
        return $this->service->delete($id);
    }

    public function _multiDestroy($request): JsonResponse
    {
        $ids = $request->input('ids', []);
        $deletedItems = [];

        foreach ($ids as $id) {
            $deletedItems[] = $this->service->delete($id);
        }

        return response()->json([
            'data' => $deletedItems
        ]);
    }
}
