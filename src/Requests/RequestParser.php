<?php

namespace Nijat\LaravelCrud\Requests;


class RequestParser
{    
    /**
     * autoFilters
     *
     * @var array
     */
    protected array $autoFilters;

    /**
     * customFilters
     *
     * @var array
     */
    protected array $customFilters;
    
    /**
     * autoSorting
     *
     * @var array
     */
    protected ?string $autoSorting;
    
    /**
     * customSorting
     *
     * @var array
     */
    protected ?string $customSorting;
    
    /**
     * expandable
     *
     * @var array
     */
    protected array $expandable;

    /**
     * customExpandable
     *
     * @var array
     */
    protected array $customExpandable;
    
    /**
     * perPage
     *
     * @var array
     */
    protected int $perPage;
    
    /**
     * __construct
     *
     * @return void
     */
    public function __construct(
        ?array $autoFilters = [],
        ?array $customFilters = [],
        ?string $autoSorting = null,
        ?string $customSorting = null,
        ?array $expandable= [],
        ?array $customExpandable = [],
        ?int $perPage = 20,
    )
    {
        $this->autoFilters = $autoFilters;
        $this->customFilters = $customFilters;

        $this->autoSorting = $autoSorting;
        $this->customSorting = $customSorting;

        $this->expandable = $expandable;       
        $this->customExpandable = $customExpandable;   

        $this->perPage = $perPage;
    }

    /**
     * Get the value of autoFilters
     */ 
    public function getAutoFilters()
    {
        return $this->autoFilters;
    }

    /**
     * Get the value of customFilters
     */ 
    public function getCustomFilters()
    {
        return $this->customFilters;
    }

    /**
     * Get the value of autoSorting
     */ 
    public function getAutoSorting()
    {
        return $this->autoSorting;
    }

    /**
     * Get the value of customSorting
     */ 
    public function getCustomSorting()
    {
        return $this->customSorting;
    }

    /**
     * Get the value of expandable
     */ 
    public function getExpandable()
    {
        return $this->expandable;
    } 

    /**
     * Get the value of customExpandable
     */ 
    public function getCustomExpandable()
    {
        return $this->customExpandable;
    }

    /**
     * Get the value of perPage
     */ 
    public function getPerPage()
    {
        return $this->perPage;
    }
}