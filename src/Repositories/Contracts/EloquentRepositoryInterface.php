<?php

namespace Nijat\LaravelCrud\Repositories\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Expression;

interface EloquentRepositoryInterface
{
    /**
     * Get all models.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function all(
        array $columns = ['*'],
        array $relations = []
    ): Collection;

    /**
     * getAllPaginated.
     * paginate models and return as Collection
     *
     * @param  array  $columns
     * @param  array  $conditions
     * @param  array  $relations
     * @param  array  $params
     * @param  string|null  $sorting
     * @param  int|null  $perPage
     * @param  array|null  $customConditions
     * @param  string|null  $customSorting
     * @param  array|null  $customRelations
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(
        array $columns = ['*'],
        array $conditions = [],
        array $relations = [],
        array $params = [],
        ?string $sorting = null,
        ?int $perPage = 20,
        ?array $customConditions = [],
        ?string $customSorting = null,
        ?array $customRelations = [],
    ): LengthAwarePaginator;

    /**
     * Get all trashed models.
     *
     * @return Collection
     */
    public function allTrashed(): Collection;

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
        array $columns = ['*'],
        array $relations = [],
        array $customRelations = [],
        array $appends = []
    ): Model;

    /**
     * Find trashed model by id.
     *
     * @param int $modelId
     * @return Model
     */
    public function findTrashedById(int $modelId): Model;

    /**
     * Find only trashed model by id.
     *
     * @param int $modelId
     * @return Model
     */
    public function findOnlyTrashedById(int $modelId): Model;

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
        string|null $key = null,
        array $conditions = []
    ): array;

    /**
     * Create a model.
     *
     * @param  array  $payload
     * @return Model
     */
    public function create(array $payload): Model;

    /**
     * Create or update a record matching the attributes, and fill it with values.
     *
     * @param  array  $attributes
     * @param  array  $payload
     * @return Builder|Model
     */
    public function updateOrCreate(array $attributes, array $payload = []): Builder|Model;

    /**
     * Update existing model.
     *
     * @param  int|Model  $modelOrModelId
     * @param  array  $payload
     * @return Model
     */
    public function update(int|Model $modelOrModelId, array $payload): Model;

    /**
     * Delete model by id.
     *
     * @param int|Model $modelOrModelId,
     * @return bool
     */
    public function deleteById(int|Model $modelOrModelId): bool;

    /**
     * Restore model by id.
     *
     * @param int $modelId
     * @return bool
     */
    public function restoreById(int $modelId): bool;

    /**
     * Permanently delete model by id.
     *
     * @param int $modelId
     * @return bool
     */
    public function permanentlyDeleteById(int $modelId): bool;
}
