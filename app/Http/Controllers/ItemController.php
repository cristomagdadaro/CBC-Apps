<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateItemRequest;
use App\Http\Requests\DeleteItemRequest;
use App\Http\Requests\GetItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Repositories\ItemRepo;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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

    public function store(CreateItemRequest $request) : Model
    {
        return parent::_store($request);
    }

    public function update(UpdateItemRequest $request, string $id): Model
    {
        return parent::_update($request, $id);
    }

    public function destroy(DeleteItemRequest $request, string $id): Model
    {
        return parent::_destroy($id);
    }
}
