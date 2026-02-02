<?php
namespace App\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Throwable;

abstract class AbstractRepoService {

    public Model $model;

    public array $appendWith = [];

    public array $appendCount = [];


    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function create(array $data)
    {
        try {
            $model = $this->model->create($data);
            $model->save();

            return $model;
        } catch (Exception $error) {
            $this->sendError($error);
        }
    }

    public function update(int|string $id, array $data): Model
    {
        try {
            $model = $this->model->findOrFail($id);
            $model->fill($data);
            $model->save();

            return $model;
        } catch (Exception $error) {
            $this->sendError($error);
        }
    }

    public function delete(int|string $id): Model
    {
        try {
            $model = $this->model->findOrFail($id);
            
            $deletedData = $model->getAttributes();
            
            $model->delete();
            
            $model->setRawAttributes($deletedData);
            
            return $model;
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }


    public function search(Collection $parameters, bool $withPagination = true, bool $isTrashed = false)
    {
        try {
            return $this->buildSearchQuery($parameters, $withPagination, $isTrashed);
        } catch (Exception $error) {
            $this->sendError($error);
        }
    }

    protected function buildSearchQuery(Collection $parameters, bool $withPagination, bool $isTrashed)
    {
        $builder = $this->model->newQuery();
        $this->applyAppends($builder, $parameters);
        $this->applyParentFilter($builder, $parameters);
        $this->applySearchFilters($builder, $parameters);
        $this->applyGroupBy($builder, $parameters);
        $this->applySorting($builder, $parameters);
        return $this->applyPagination($builder, $parameters);
    }

    public function applySearchFilters(Builder &$query, Collection $parameters): void
    {
        $isExact = $parameters->get('is_exact', false);
        $filter = $parameters->get('filter', null);
        $searchTerm = $parameters->get('search', '');

        if (is_string($isExact)) {
            $isExact = $isExact === 'true';
        }

        if (empty($searchTerm)) return;

        // Apply search on the main model
        $this->applySearch($query, $searchTerm, $filter, $isExact);
    }

    public function applyParentFilter(Builder &$query, Collection $parameters): void
    {
        $filterByParentColumn = $parameters->get('filter_by_parent_column');
        $filterByParentId = $parameters->get('filter_by_parent_id');

        if (!empty($filterByParentColumn) && !empty($filterByParentId)) {
            $query = $query->where($filterByParentColumn, $filterByParentId);
        }
    }

    protected function applySearch(Builder $query, string $search, ?string $filter, bool $is_exact): void
    {
        if (empty($search)) {
            return;
        }

        // Apply search to a specific column if filter is provided
        if ($filter) {
            if (str_contains($filter, '.')) {
                $filter = explode('.', $filter)[1]; // Extract the column name if filter contains a relation
            }
            $query->where($filter, 'like', "%{$search}%");
            return;
        }

        // Retrieve searchable columns
        $columns = collect($query->getModel()->getSearchable());
        if ($columns->isEmpty()) {
            return;
        }

        // Handle full name search
        if ($columns->contains('fname') && $columns->contains('lname')) {
            $query->orWhereRaw("CONCAT_WS(' ', fname, mname, lname, suffix) LIKE ?", ["%{$search}%"]);
            return;
        }

        // Handle specific "name" column search
        if ($filter === 'name') {
            $query->where('name', 'like', "%{$search}%");
            return;
        }

        // Apply search to all searchable columns
        $query->where(function ($subQuery) use ($columns, $search, $is_exact) {
            foreach ($columns as $column) {
                $operator = $is_exact ? '=' : 'like';
                $value = $is_exact ? $search : "%{$search}%";
                $subQuery->orWhere($column, $operator, $value);
            }
        });
    }

    public function applyAppends(Builder &$model, Collection $parameters): void
    {
        $with = $parameters->get('with', $this->appendWith);;
        $count = $parameters->get('count', $this->appendCount);

        if ($with) {
            $with = is_array($with) ? $with : explode(',', $with);
            $model = $model->with($with);
        }

        if ($count) {
            $count = is_array($count) ? $count : explode(',', $count);
            $model = $model->withCount($count);
        }
    }

    public function applyGroupBy(Builder &$query, Collection $parameters): void
    {
        $group_by = $parameters->get('group_by', null);

        if ($group_by) {
            $query->groupBy($group_by);
        }
    }

    public function applySorting(Builder &$query, Collection $parameters): void
    {
        $sortColumn = $parameters->get('sort', null);
        $order = strtoupper($parameters->get('order', 'desc'));

        if (!$sortColumn) return;

        // Validate the sort column exists to prevent SQL errors
        $table = $query->getModel()->getTable();
        if (!Schema::hasColumn($table, $sortColumn)) {
            $selectedColumns = $query->getQuery()->getColumns() ? $query->getQuery()->getColumns()[0] : ''; // Get selected columns from query

            if (str_contains($selectedColumns, $sortColumn)) {
                // If sort column exists in the query, use it
                $sortColumn = $selectedColumns;
            } elseif (Schema::hasColumn($query->getModel()->getTable(), 'id')) {
                // Default to table ID if it exists
                $sortColumn = $table.'.id';
            } else {
                // Default to UUID if no valid column is found
                $sortColumn = 'uuid';
            }
        }

        if (in_array($order, ['ASC', 'DESC'])) {
            $query->orderBy($sortColumn, $order);
        } else {
            $query->orderBy($sortColumn, 'desc'); // Fallback to descending order
        }
    }

    protected function applyPagination(Builder $query, Collection $parameters)
    {
        $perPage = $parameters->get('per_page', 10);
        $page = $parameters->get('page', 1);

        if ($perPage === '*' || $perPage === 'all') {
            return [ 'data' => $query->get() ];
        }


        return $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
    }

    /**
     * @throws Throwable
     */
    public function sendError(Throwable $error): never
    {
        Log::error('Error occurred: ' . $error->getMessage(), ['exception' => $error]);
        // Re-throw the original exception to preserve context and stack trace
        throw $error;
    }
}
