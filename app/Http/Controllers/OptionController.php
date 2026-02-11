<?php

namespace App\Http\Controllers;

use App\Http\Requests\Generic\GetRequest;
use App\Http\Requests\CreateOptionRequest;
use App\Http\Requests\DeleteRequest;
use App\Http\Requests\UpdateOptionRequest;
use App\Repositories\OptionRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class OptionController extends BaseController
{
    public function __construct(OptionRepo $repository)
    {
        $this->service = $repository;
    }

    /**
     * List all options with optional filtering
     */
    public function index(GetRequest $request)
    {
        $options = $this->service->search(new \Illuminate\Support\Collection($request->validated()));
        return $options;
    }

    /**
     * Create a new option
     */
    public function store(CreateOptionRequest $request)
    {
        return parent::_store($request);
    }

    /**
     * Update an option
     */
    public function update(UpdateOptionRequest $request, string $id)
    {
        return parent::_update($id, $request);
    }

    /**
     * Delete an option
     */
    public function destroy(DeleteRequest $request, string $id)
    {
        return parent::_destroy($id);
    }

    /**
     * Get options by group
     */
    public function getByGroup(Request $request, $group)
    {
        $options = $this->service->getByGroup($group);
        return new Collection($options);
    }

    /**
     * Get options by key
     */
    public function getByKey(Request $request, $key)
    {
        $value = $this->service->getByKey($key);
        return new Collection([
            'key' => $key,
            'value' => $value
        ]);
    }

    /**
     * Get all options grouped by group
     */
    public function getAllGrouped(Request $request)
    {
        $options = $this->service->getAllGrouped();
        return new Collection($options);
    }

    /**
     * Get options for dropdown with optional group filter
     */
    public function getForDropdown(Request $request)
    {
        $group = $request->query('group');
        $options = $this->service->getForDropdown($group);
        return new Collection($options);
    }

    /**
     * Get options with metadata
     */
    public function getWithMetadata(Request $request)
    {
        $group = $request->query('group');
        $options = $this->service->getWithMetadata($group);
        return new Collection($options);
    }
}
