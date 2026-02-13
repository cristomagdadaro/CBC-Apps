<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateItemRequest;
use App\Http\Requests\DeleteItemRequest;
use App\Http\Requests\GetItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Repositories\ItemRepo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class ItemController extends BaseController
{
    public function __construct(ItemRepo $repository)
    {
        $this->service = $repository;
    }

    public function index(GetItemRequest $request, $item_id = null): Collection
    {
        return parent::_index($request);
    }

    public function create(CreateItemRequest $request) : Model
    {
        return parent::_store($request);
    }

    public function update(UpdateItemRequest $request, string $id): Model
    {
        return parent::_update($id, $request);
    }

    public function destroy(DeleteItemRequest $request, string $id): Model
    {
        return parent::_destroy($request->validated('id'));
    }

    public function indexOptions(GetItemRequest $request): JsonResponse
    {
        $data = $this->service->search(new Collection($request->validated()));

        $data->getCollection()->transform(function ($item) {
            return [
                'value' => $item->id,
                'label' => $item->name . (
                    ($item->description || $item->brand)
                        ? ' (' . trim(
                            ($item->description ?? '') .
                            ($item->description && $item->brand ? ', ' : '') .
                            ($item->brand ?? '')
                        ) . ')'
                        : ''
                ),
            ];
        });

        return response()->json($data);
    }
}
