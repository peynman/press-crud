<?php

namespace Larapress\CRUD\Services\CRUD\Verbs\Query;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class QueryFilterResult
{
    /** @var Builder */
    public $query;
    /** @var int */
    public $total;
    /** @var null|Collection */
    public $items;
    /** @var int */
    public $currentPage;
    /** @var int */
    public $perPage;

    public function __construct(Builder $query, int $total, int $currentPage, int $perPage)
    {
        $this->query = $query;
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
    }

    /**
     * Undocumented function
     *
     * @return Collection
     */
    public function getItems()
    {
        if (is_null($this->items)) {
            $this->items = $this->query->get();
        }

        return $this->items;
    }

    /**
     * Undocumented function
     *
     * @return int
     */
    public function getQueryTotal()
    {
        if ($this->total === -1) {
            $this->total = $this->items->count();
        }

        return $this->total;
    }
}
