<?php

namespace Nijat\LaravelCrud\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class AppFormRequest extends FormRequest
{
    const SCENATIO_DEFAULT = 0;
    const SCENARIO_INSERT = 1;
    const SCENARIO_UPDATE = 2;
    const SCENARIO_DELETE = 3;

    /**
     * scenario
     * keeps request method
     *
     * DEFAULT = GET,
     * INSERT = POST,
     * UPDATE = PATCH | PUT,
     * DELETE = DELETE,
     *
     * @var int
     */
    protected int $scenario = self::SCENATIO_DEFAULT;

    /**
     * sortBy
     * keeps default sort attribute
     * add - to attribute if you want sort DESC
     * user can change sort attr as ?sort=-name(name DESC)
     *
     * @var string
     */
    protected ?string $sortBy = null;

    /**
     * customFilters
     * keeps custom filters which are not generate conditions automaticly
     *
     * @var array
     */
    protected array $customFilters = [];

    /**
     * expandable
     * keeps fields which allow to join relations and return their resource
     *
     * @var array
     */
    protected array $expandable = [];

    /**
     * customExpandable
     * keeps custom fields which you want to join relation with custom queries
     *
     * @var array
     */
    protected array $customExpandable = [];

    /**
     * perPage
     * keeps default item count in a page
     * if you want to use different default item count (perPage) for every FormRequest
     * then change this value
     * otherwise default perPage set from .env file
     * @var int
     */
    protected int $perPage = 0;

    /**
     * getScenario
     *
     * @return int
     */
    public function getScenario(): int
    {
        return $this->scenario;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * parser
     *
     * return parsed request query
     *
     * @return RequestParser
     */
    final public function parser(): RequestParser
    {
        return new RequestParser(
            autoFilters: $this->generateAutoFilters(),
            customFilters: $this->customFilters(),

            autoSorting: $this->autoSorting(),
            customSorting: $this->customSorting(),

            expandable: $this->expandable(),
            customExpandable: $this->customExpandable(),

            perPage: $this->perPage(),
        );
    }

    /**
     * lightParser
     *
     * parsed only extandable and customExtandable query
     *
     * @return RequestParser
     */
    public function lightParser()
    {
        return new RequestParser(
            expandable: $this->expandable(),
            customExpandable: $this->customExpandable()
        );
    }

    /**
     * return sortable attributes
     * 
     * @return array<string, string>
     */
    protected function sortable()
    {
        return [];
    }

    /**
     * return custom sortable attributes
     * 
     * @return array<string, string>
     */
    protected function customSortable()
    {
        return [];
    }

    /**
     * Before getting the validator instance set scenario
     * Get the validator instance for the request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    final protected function getValidatorInstance()
    {
        switch ($this->method()){
            case self::METHOD_POST:
                $this->scenario = self::SCENARIO_INSERT;
                break;
            case self::METHOD_PUT:
            case self::METHOD_PATCH:
                $this->scenario = self::SCENARIO_UPDATE;
                break;
            case self::METHOD_DELETE:
                $this->scenario = self::SCENARIO_DELETE;
                break;
            default:
                $this->scenario = self::SCENATIO_DEFAULT;
                break;
        }

        return parent::getValidatorInstance();
    }

    /**
     * generateAutoFilters
     *
     * generate auto filters from rules automaticly
     *
     * @return array
     */
    final protected function generateAutoFilters(): array
    {
        $rules = collect($this->rules())
                    ->forget($this->customFilters)
                    ->toArray();

        $filters = $this->validated();

        $autoFilters = [];

        foreach ($rules as $attr => $rule)
        {
            if(!is_array($rule) && !is_string($rule)){
                continue;
            }

            $rule = is_string($rule) ? explode('|', $rule) : $rule;

            if(in_array('string', $rule) && !empty($filters[$attr]))
            {
                $autoFilters[] = [$attr, 'LIKE', "%{$filters[$attr]}%"];
            }
            elseif(isset($filters[$attr]) && $filters[$attr] != null)
            {
                $autoFilters[] = [$attr, '=', $filters[$attr]];
            }
        }

        return $autoFilters;
    }

    /**
     * customFilters
     *
     * return only custom filter attributes from request
     *
     * @return array
     */
    final protected function customFilters(): array
    {
        return $this->only($this->customFilters);
    }

    /**
     * perPage
     *
     * get item count per page from request
     *
     * @return int
     */
    final protected function perPage(): ?int
    {
        $per_page = (int) $this->get('per-page');

        if($per_page < 1) {
            return $this->perPage ?: config("crud.items_count_per_page");
        }

        return $per_page > config("crud.max_items_count_per_page") ? config("crud.items_count_per_page") : $per_page;
    }

    /**
     * autoSorting
     *
     * get sorting field from request
     *
     * @return string
     */
    final protected function autoSorting(): ?string
    {
        $sorting = $this->get('sort');

        if(in_array($sorting, $this->customSortable())) {
            return null;
        }

        if(!in_array($sorting, $this->sortable())) {
            return $this->sortBy;
        }

        $direction = strtolower($this->get('direction'));

        $attr = $direction == 'desc' ?  '-'.$sorting : $sorting; 

        return $attr;
    }

    /**
     * customSorting
     *
     * get custom sorting field from request
     * @return string
     */
    final protected function customSorting(): ?string
    {
        $sorting = $this->get('sort');

        if(!in_array($sorting, $this->customSortable())) {
            return null;
        }

        $direction = strtolower($this->get('direction'));

        $attr = $direction == 'desc' ?  '-'.$sorting : $sorting; 

        return $attr;
    }

    /**
     * expandable
     * 
     * return expandable fields which filter from request
     *
     * @return array
     */
    final protected function expandable(): array
    {
        $expand = $this->get('expand');

        return collect(explode(',', $expand))
                ->map( fn($e) => trim($e))
                ->diff($this->customExpandable)
                ->intersect($this->expandable)
                ->toArray();
    }

    /**
     * customExpandable
     *
     * return custom expandable fields
     *
     * @return array
     */
    final protected function customExpandable(): array
    {
        $expand = $this->get('expand');

        return collect(explode(',', $expand))
                ->map( fn($e) => trim($e))
                ->intersect($this->customExpandable)
                ->toArray();
    }

}
