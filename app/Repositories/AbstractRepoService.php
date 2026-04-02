<?php
namespace App\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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

        if($withPagination)
            return $this->applyPagination($builder, $parameters);
        
        return $builder->get();
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
        $search = trim($search);

        if ($search === '') {
            return;
        }

        $operator = $is_exact ? '=' : 'like';
        $value = $is_exact ? $search : "%{$search}%";

        if ($filter && $this->applyFilteredSearch($query, $filter, $operator, $value)) {
            return;
        }

        $columns = $this->getSearchableColumns($query->getModel());

        if ($columns->isEmpty()) {
            return;
        }

        $query->where(function (Builder $subQuery) use ($columns, $query, $operator, $value) {
            $this->applyModelSearchColumns($subQuery, $query->getModel(), $columns, $operator, $value);
        });
    }

    protected function applyFilteredSearch(Builder $query, string $filter, string $operator, string $value): bool
    {
        if (str_contains($filter, '.')) {
            return $this->applyRelationColumnSearch($query, $filter, $operator, $value, 'and');
        }

        if ($this->shouldApplyFullNameSearch($query->getModel(), $filter)) {
            $this->applyFullNameSearch($query, $query->getModel(), $operator, $value);

            return true;
        }

        $relationName = $this->resolveRelationNameForFilter($query->getModel(), $filter);
        if ($relationName) {
            return $this->applyRelationModelSearch($query, $query->getModel(), $relationName, $operator, $value, 'and');
        }

        if ($this->hasColumn($query->getModel(), $filter)) {
            $query->where($filter, $operator, $value);

            return true;
        }

        return false;
    }

    protected function isRelationColumn(string $column): bool
    {
        return str_contains($column, '.');
    }

    protected function applyRelationColumnSearch(Builder $query, string $relationColumn, string $operator, string $value, string $boolean = 'or'): bool
    {
        [$relationPath, $columnName] = $this->splitRelationColumn($relationColumn);

        if (!$relationPath || !$columnName) {
            return false;
        }

        $method = $boolean === 'and' ? 'whereHas' : 'orWhereHas';

        $query->{$method}($relationPath, function (Builder $relatedQuery) use ($columnName, $operator, $value) {
            $relatedModel = $relatedQuery->getModel();

            if ($this->shouldApplyFullNameSearch($relatedModel, $columnName)) {
                $this->applyFullNameSearch($relatedQuery, $relatedModel, $operator, $value);

                return;
            }

            if ($this->hasColumn($relatedModel, $columnName)) {
                $relatedQuery->where($columnName, $operator, $value);

                return;
            }

            $relationName = $this->resolveRelationNameForFilter($relatedModel, $columnName);
            if ($relationName) {
                $this->applyRelationModelSearch($relatedQuery, $relatedModel, $relationName, $operator, $value, 'and');

                return;
            }

            $columns = $this->getSearchableColumns($relatedModel);
            if ($columns->isEmpty()) {
                return;
            }

            $relatedQuery->where(function (Builder $nestedQuery) use ($relatedModel, $columns, $operator, $value) {
                $this->applyModelSearchColumns($nestedQuery, $relatedModel, $columns, $operator, $value);
            });
        });

        return true;
    }

    protected function splitRelationColumn(string $relationColumn): array
    {
        $segments = explode('.', $relationColumn);

        if (count($segments) < 2) {
            return [null, null];
        }

        $columnName = array_pop($segments);

        return [implode('.', $segments), $columnName];
    }

    protected function applyModelSearchColumns(Builder $query, Model $model, Collection $columns, string $operator, string $value): void
    {
        $hasCondition = false;

        if ($this->supportsFullNameSearch($model)) {
            $this->applyFullNameSearch($query, $model, $operator, $value);
            $hasCondition = true;
        }

        foreach ($columns as $column) {
            $applied = $this->applySearchableColumn(
                $query,
                $model,
                $column,
                $operator,
                $value,
                $hasCondition ? 'or' : 'and',
            );

            $hasCondition = $hasCondition || $applied;
        }
    }

    protected function applySearchableColumn(
        Builder $query,
        Model $model,
        string $column,
        string $operator,
        string $value,
        string $boolean = 'or',
    ): bool {
        if ($this->isRelationColumn($column)) {
            return $this->applyRelationColumnSearch($query, $column, $operator, $value, $boolean);
        }

        $applied = false;

        if ($this->hasColumn($model, $column)) {
            $method = $boolean === 'and' ? 'where' : 'orWhere';
            $query->{$method}($column, $operator, $value);
            $applied = true;
        }

        $relationName = $this->resolveRelationNameForFilter($model, $column);
        if ($relationName) {
            $this->applyRelationModelSearch($query, $model, $relationName, $operator, $value, $applied ? 'or' : $boolean);

            return true;
        }

        return $applied;
    }

    protected function applyRelationModelSearch(
        Builder $query,
        Model $model,
        string $relationName,
        string $operator,
        string $value,
        string $boolean = 'or',
    ): bool {
        $relation = $this->resolveRelationInstance($model, $relationName);

        if (!$relation) {
            return false;
        }

        $method = $boolean === 'and' ? 'whereHas' : 'orWhereHas';

        $query->{$method}($relationName, function (Builder $relatedQuery) use ($relation, $operator, $value) {
            $relatedModel = $relation->getRelated();
            $columns = $this->getSearchableColumns($relatedModel);

            if ($columns->isEmpty() && !$this->supportsFullNameSearch($relatedModel)) {
                return;
            }

            $relatedQuery->where(function (Builder $nestedQuery) use ($relatedModel, $columns, $operator, $value) {
                $this->applyModelSearchColumns($nestedQuery, $relatedModel, $columns, $operator, $value);
            });
        });

        return true;
    }

    protected function getSearchableColumns(Model $model): Collection
    {
        return collect($model->getSearchable())
            ->filter(fn ($column) => is_string($column) && $column !== '')
            ->unique()
            ->values();
    }

    protected function resolveRelationNameForFilter(Model $model, string $filter): ?string
    {
        $candidates = [$filter];

        if (Str::endsWith($filter, '_id')) {
            $candidates[] = Str::beforeLast($filter, '_id');
        }

        foreach (array_unique($candidates) as $candidate) {
            if ($this->resolveRelationInstance($model, $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    protected function resolveRelationInstance(Model $model, string $relationName): ?Relation
    {
        if (!method_exists($model, $relationName)) {
            return null;
        }

        try {
            $relation = $model->{$relationName}();
        } catch (Throwable) {
            return null;
        }

        return $relation instanceof Relation ? $relation : null;
    }

    protected function hasColumn(Model $model, string $column): bool
    {
        return Schema::connection($model->getConnectionName())
            ->hasColumn($model->getTable(), $column);
    }

    protected function supportsFullNameSearch(Model $model): bool
    {
        $columns = $this->getSearchableColumns($model);

        return $columns->contains('fname') && $columns->contains('lname');
    }

    protected function shouldApplyFullNameSearch(Model $model, ?string $column = null): bool
    {
        if (!$this->supportsFullNameSearch($model)) {
            return false;
        }

        if ($column === null) {
            return true;
        }

        return in_array($column, ['name', 'full_name', 'fullName'], true);
    }

    protected function applyFullNameSearch(Builder $query, Model $model, string $operator, string $value): void
    {
        $expression = $this->fullNameExpression($model);

        $query->whereRaw("{$expression} {$operator} ?", [$value]);
    }

    protected function fullNameExpression(Model $model): string
    {
        $driver = $model->getConnection()->getDriverName();

        return match ($driver) {
            'sqlite' => "TRIM(REPLACE(REPLACE(COALESCE(fname, '') || ' ' || COALESCE(mname, '') || ' ' || COALESCE(lname, '') || ' ' || COALESCE(suffix, ''), '  ', ' '), '  ', ' '))",
            default => "TRIM(CONCAT_WS(' ', fname, mname, lname, suffix))",
        };
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

        $table = $query->getModel()->getTable();
        if (!Schema::hasColumn($table, $sortColumn)) {
            $selectedColumns = $query->getQuery()->getColumns() ? $query->getQuery()->getColumns()[0] : '';

            if (str_contains($selectedColumns, $sortColumn)) {
                $sortColumn = $selectedColumns;
            } elseif (Schema::hasColumn($query->getModel()->getTable(), 'id')) {
                $sortColumn = $table.'.id';
            } else {
                $sortColumn = 'uuid';
            }
        }

        if (in_array($order, ['ASC', 'DESC'])) {
            $query->orderBy($sortColumn, $order);
        } else {
            $query->orderBy($sortColumn, 'desc');
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
        throw $error;
    }
}
