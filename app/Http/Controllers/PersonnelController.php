<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePersonnelRequest;
use App\Http\Requests\DeletePersonnelRequest;
use App\Http\Requests\GetPersonnelRequest;
use App\Http\Requests\UpdatePersonnelRequest;
use App\Repositories\PersonnelRepo;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PersonnelController extends BaseController
{

    public function __construct(PersonnelRepo $repository)
    {
        $this->service = $repository;
    }

    public function index(GetPersonnelRequest $request)
    {
        $response = $this->service->search(new Collection($request->validated()));

        $filterOutAdmin = function ($item) {
            return $item->id !== 1;
        };

        if ($response instanceof LengthAwarePaginator) {
            $filtered = $response->getCollection()->filter($filterOutAdmin)->values();
            $response->setCollection($filtered);
            return $response;
        }

        if (is_array($response) && array_key_exists('data', $response)) {
            $response['data'] = collect($response['data'])->filter($filterOutAdmin)->values();
            return $response;
        }

        if ($response instanceof Collection) {
            return $response->filter($filterOutAdmin)->values();
        }

        return $response;
    }

    public function create(CreatePersonnelRequest $request): Model
    {
        return parent::_store($request);
    }

    public function update(UpdatePersonnelRequest $request, string $id): Model
    {
        return parent::_update($id, $request);
    }

    public function destroy(DeletePersonnelRequest $request, string $id): Model | JsonResponse
    {
        return parent::_destroy($id);
    }
}
