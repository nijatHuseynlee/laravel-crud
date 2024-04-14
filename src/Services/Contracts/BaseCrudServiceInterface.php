<?php

namespace Nijat\LaravelCrud\Services\Contracts;

use Nijat\LaravelCrud\Requests\RequestParser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

interface BaseCrudServiceInterface
{
    /**
     * getAll
     *
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function getAll(
        array $columns = ['*'],
        array $relations = []
    ): Collection;

    /**
     * getAllPaginated
     *
     * @param  RequestParser $requestParser
     * @param  array $column
     * @param  array $conditions
     * @param  array $relations
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(
        RequestParser $requestParser,
        array $columns = ['*'],
        array $conditions = [],
        array $relations = [],
    ): LengthAwarePaginator;

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
     * create
     *
     * @param  array $payload
     * @return Model
     */
    public function create(array $payload): Model;

    /**
     * findById
     *
     * @param int|Model $modelOrModelId,
     * @param RequestParser|null $requestParser
     * @param array $relations
     * @param array $appends
     * @return Model
     */
    public function findById(
        int|Model $modelOrModelId,
        ?RequestParser $requestParser = null,
        ?array $columns = ['*'],
        ?array $relations = [],
        ?array $appends = []
    ): Model;

    /**
     * update
     *
     * @param  int|Model $modelOrModelId
     * @param  mixed $payload
     * @return Model|null
     */
    public function update(int|Model $modelOrModelId, array $payload): Model;

    /**
     * delete
     *
     * @param  int|Model $modelOrModelId,
     * @return bool
     */
    public function delete(int|Model $modelOrModelId): bool;
}
