<?php

namespace Nijat\LaravelCrud\Repositories;

use Nijat\LaravelCrud\Repositories\Contracts\EloquentRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;

abstract class EloquentRepository implements EloquentRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * EloquentRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        $columns = $this->reformatColumns($columns);

        return $this->model->newQuery()->with($relations)->get($columns);
    }

    /**
     * getAllPaginated.
     * paginate models and return as Collection
     *
     * @param array $columns
     * @param array $conditions
     * @param array $relations
     * @param array $params
     * @param string|null $sorting
     * @param int|null $perPage
     * @param array|null $customConditions
     * @param string|null $customSorting
     * @param array|null $customRelations
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(
        array   $columns = ['*'],
        array   $conditions = [],
        array   $relations = [],
        array   $params = [],
        ?string $sorting = null,
        ?int    $perPage = 20,
        ?array  $customConditions = [],
        ?string $customSorting = null,
        ?array  $customRelations = [],
    ): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->addSelect($this->reformatColumns($columns))
            ->with($relations);

        $tableName = $this->model->getTable();

        $this->customizeQuery($query, $params);

        //add custom filters
        foreach($customConditions as $key => $condition) {
            $name = ucfirst($key);
            $method = "add".$name."Condition";
            if(method_exists($this, $method)) {
                $this->{$method}($query, $condition);
            } else {
                $query = $this->addCustomConditions($query, $customConditions);
            }
        }

        //add conditions
        foreach ($conditions as $condition) {
            $query->where($tableName . "." . $condition[0], $condition[1], $condition[2]);
        }

        //add custom sorting
        if ($customSorting) {
            $attr = ltrim($customSorting, '-');

            $direction = $customSorting == $attr ? 'ASC' : 'DESC';

            $name = ucfirst($attr);
            $method = "add".$name."Sorting";
            if(method_exists($this, $method)) {
                $this->{$method}($query, $direction);
            } else {
                $query = $this->addCustomSorting($query, $attr, $direction);
            }
        } //add sorting
        elseif ($sorting) {
            $attr = ltrim($sorting, '-');

            $direction = $sorting == $attr ? 'ASC' : 'DESC';

            $query->orderBy($tableName . "." . $attr, $direction);
        }

        //add custom relations
        if ($customRelations) {
            $query = $this->addCustomRelations($query, $customRelations);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get all trashed models.
     *
     * @return Collection
     */
    public function allTrashed(): Collection
    {
        return $this->model->onlyTrashed()->get();
    }

    /**
     * Find model by id.
     *
     * @param int|Model $modelOrModelId
     * @param array $columns
     * @param array $relations
     * @param array $customRelations
     * @param array $appends
     * @return Model
     */
    public function findById(
        int|Model $modelOrModelId,
        array     $columns = ['*'],
        array     $relations = [],
        array     $customRelations = [],
        array     $appends = []
    ): Model
    {
        $model = null;

        if (is_int($modelOrModelId)) {
            $query = $this->model->newQuery()
                ->select($this->reformatColumns($columns))
                ->with($relations);

            if ($customRelations) {
                $query = $this->addCustomRelations($query, $customRelations);
            }

            $model = $query->findOrFail($modelOrModelId);
        } else {
            $model = $modelOrModelId;
        }

        return $model->append($appends);
    }

    /**
     * Find trashed model by id.
     *
     * @param int $modelId
     * @return Model
     */
    public function findTrashedById(int $modelId): Model
    {
        return $this->model->withTrashed()->findOrFail($modelId);
    }

    /**
     * Find only trashed model by id.
     *
     * @param int $modelId
     * @return Model
     */
    public function findOnlyTrashedById(int $modelId): Model
    {
        return $this->model->onlyTrashed()->findOrFail($modelId);
    }

    /**
     * pluck
     *
     * @param string|\Illuminate\Database\Query\Expression $column
     * @param string|null $key
     * @param array $conditions
     *
     * @return array
     */
    public function pluck(
        string|Expression $column,
        string|null       $key = null,
        array             $conditions = []
    ): array
    {
        $query = $this->model->newQuery();

        $tableName = $this->model->getTable();

        //add conditions
        foreach ($conditions as $condition) {
            $query->where($tableName . "." . $condition[0], $condition[1], $condition[2]);
        }

        return $query->pluck($column, $key)->all();
    }

    /**
     * Create a model.
     *
     * @param array $payload
     * @return Model
     */
    public function create(array $payload): Model
    {
        $model = $this->model->create($payload);

        return $model->refresh();
    }

    /**
     * Update existing model.
     *
     * @param int|Model $modelOrModelId
     * @param array $payload
     * @return Model|null
     */
    public function update(int|Model $modelOrModelId, array $payload): Model
    {
        $model = is_int($modelOrModelId) ? $this->findById($modelOrModelId) : $modelOrModelId;

        $model->update($payload);

        return $model;
    }

    /**
     * Create or update a record matching the attributes, and fill it with values.
     *
     * @param array $attributes
     * @param array $payload
     * @return Builder|Model
     */
    public function updateOrCreate(array $attributes, array $payload = []): Builder|Model
    {
        return $this->model->newModelQuery()->updateOrCreate($attributes, $payload);
    }

    /**
     * Delete model by id.
     *
     * @param int|Model $modelOrModelId ,
     * @return bool
     */
    public function deleteById(int|Model $modelOrModelId): bool
    {
        $model = is_int($modelOrModelId) ? $this->findById($modelOrModelId) : $modelOrModelId;

        try {
            $model->delete();
        } catch (Exception) {
            abort(403, "Forbidden. This item has relations to other items");
        }

        return 1;
    }

    /**
     * Restore model by id.
     *
     * @param int $modelId
     * @return bool
     */
    public function restoreById(int $modelId): bool
    {
        return $this->findOnlyTrashedById($modelId)->restore();
    }

    /**
     * Permanently delete model by id.
     *
     * @param int $modelId
     * @return bool
     */
    public function permanentlyDeleteById(int $modelId): bool
    {
        return $this->findTrashedById($modelId)->forceDelete();
    }

    /**
     * Permanently delete model by id.
     *
     * @param Builder $query
     * @param array $params
     * @return bool
     */
    protected function customizeQuery(Builder $query, array $params): void
    {
        //customize query
    }

    /**
     * reformatColumns
     *
     * @param array $columns
     * @param  ?string $tableName
     * @return array
     */
    protected function reformatColumns(array $columns, ?string $tableName = null): array
    {
        $tableName = $tableName ?: $this->model->getTable();

        foreach ($columns as &$column) {
            if (!Str::contains($column, '.'))
                $column = $tableName . "." . $column;
        }

        return $columns;
    }

    /**
     * addCustomConditions
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    protected function addCustomConditions(Builder $query, array $filters): Builder
    {
        //add custom filters here
        return $query;
    }

    /**
     * addCustomSorting
     *
     * @param Builder $query
     * @param string $attr
     * @param string $direction
     * @return Builder
     */
    protected function addCustomSorting(Builder $query, string $attr, string $direction): Builder
    {
        //add custom filters here
        return $query;
    }

    /**
     * addCustomRelations
     *
     * @param Builder $query
     * @param array $relations
     * @return Builder
     */
    protected function addCustomRelations(Builder $query, array $relations): Builder
    {
        //add custom relations here
        return $query;
    }
}
