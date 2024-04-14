<?php
namespace Nijat\LaravelCrud\Services;

use Nijat\LaravelCrud\Requests\RequestParser;
use Nijat\LaravelCrud\Services\Contracts\BaseCrudServiceInterface;
use Nijat\LaravelCrud\Repositories\Contracts\EloquentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

abstract class BaseCrudService implements BaseCrudServiceInterface
{
    /**
     * repository
     *
     * @var EloquentRepositoryInterface
     */
    protected $repository;

    /**
     * __construct.
     *
     * @param  EloquentRepositoryInterface $repository
     * @return void
     */
    public function __construct(EloquentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

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
    ): Collection
    {
        return $this->repository->all($columns, $relations);
    }

    /**
     * getAllPaginated
     *
     * @param  RequestParser $requestParser
     * @param  array $columns
     * @param  array $conditions
     * @param  array $relations
     * @param  array $params
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(
        RequestParser $requestParser,
        array $columns = ['*'],
        array $conditions = [],
        array $relations = [],
        array $params = [],
    ): LengthAwarePaginator
    {
        $options = $this->getQueryOptions($requestParser);

        $relations = array_merge($options['autoRelations'], $relations);

        $conditions = array_merge($options['conditions'], $conditions);

        return $this->repository->getAllPaginated(
            columns: $columns,
            conditions: $conditions,
            relations: $relations,
            params: $params,
            sorting: $options['sorting'],
            perPage: $options['perPage'],
            customConditions: $options['customConditions'],
            customSorting: $options['customSorting'],
            customRelations: $options['customRelations'],
        );
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
        string|null $key = null,
        array $conditions = []
    ): array
    {
        return $this->repository->pluck($column, $key, $conditions);
    }

    /**
     * create
     *
     * @param  array $payload
     * @return Model
     */
    public function create(array $payload): Model
    {
        $model = $this->repository->create($payload);

        return $model;
    }

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
    ): Model
    {
        if($requestParser)
        {
            $options = $this->getQueryOptions($requestParser);

            $relations = array_merge($options['autoRelations'], $relations);
        }

        return $this->repository->findById(
            $modelOrModelId,
            $columns,
            $relations,
            $options['customRelations'] ?? [],
            $appends
        );
    }

    /**
     * update
     *
     * @param  int|Model $modelOrModelId
     * @param  mixed $payload
     * @return Model|null
     */
    public function update(int|Model $modelOrModelId, array $payload): Model
    {
        return $this->repository->update($modelOrModelId, $payload);
    }

    /**
     * delete
     *
     * @param  int|Model $modelOrModelId
     * @return bool
     */
    public function delete(int|Model $modelOrModelId): bool
    {
        return $this->repository->deleteById($modelOrModelId);
    }


    /**
     * getQueryOptions
     *
     * @param  RequestParser $requestParser
     * @return array
     */
    protected function getQueryOptions(RequestParser $requestParser): array
    {
        return [
            'conditions' => $requestParser->getAutoFilters(),
            'customConditions' => $requestParser->getCustomFilters(),
            'sorting' => $requestParser->getAutoSorting(),
            'customSorting' => $requestParser->getCustomSorting(),
            'autoRelations' => $requestParser->getExpandable(),
            'customRelations' => $requestParser->getCustomExpandable(),
            'perPage' => $requestParser->getPerPage(),
        ];
    }
}
