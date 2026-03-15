<?php

namespace App\Http\Controllers;

use App\Repositories\AbstractRepoService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

abstract class BaseController extends Controller
{
    protected ?AbstractRepoService $service = null;

    protected function requireService(): AbstractRepoService
    {
        if (! $this->service) {
            throw new \BadMethodCallException(static::class . ' does not define a primary repository service.');
        }

        return $this->service;
    }

    protected function _index($request): Collection
    {
        $data = $this->requireService()->search(new Collection($request->validated()));
        return new Collection($data);
    }

    protected function _store($request): Model
    {
        return $this->requireService()->create($request->validated());
    }

    protected function _update(string $id, $request): Model
    {
        return $this->requireService()->update($id, $request->validated());
    }

    protected function _destroy(string $id): Model
    {
        return $this->requireService()->delete($id);
    }

    public function _multiDestroy($request): JsonResponse
    {
        $ids = $request->input('ids', []);
        $deletedItems = [];

        foreach ($ids as $id) {
            $deletedItems[] = $this->requireService()->delete($id);
        }

        return response()->json([
            'data' => $deletedItems
        ]);
    }

    /**
     * Load audit logs for a model instance.
     * Used to display audit information in edit/update pages.
     */
    protected function loadAuditLogs(Model $model)
    {
        if (method_exists($model, 'auditLogs')) {
            return $model->auditLogs()->latest('created_at')->get();
        }
        return [];
    }
}

