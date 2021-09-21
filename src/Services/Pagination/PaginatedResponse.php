<?php

namespace Larapress\CRUD\Services\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;
use stdClass;

class PaginatedResponse extends stdClass {

    public $items;
    public $total;
    public $from;
    public $to;
    public $currPage;
    public $perPage;
    public $refId;

    /**
     * Undocumented function
     *
     * @param LengthAwarePaginator $paginate
     * @param null|string|int $refId
     */
    public function __construct(LengthAwarePaginator $paginate, $refId = null) {
        $this->items = $paginate->items();
        $this->from = $paginate->firstItem();
        $this->to = $paginate->lastItem();
        $this->currPage = $paginate->currentPage();
        $this->perPage = $paginate->perPage();
        $this->total = $paginate->total();
        $this->refId = $refId;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function toArray() {
        return [
            'items' => $this->items,
            'total' => $this->total,
            'from' => $this->from,
            'to' => $this->to,
            'currPage' => $this->currPage,
            'perPage' => $this->perPage,
            'refId' => $this->refId,
        ];
    }

    /**
     * Undocumented function
     *
     * @param int|null $limit
     *
     * @return int
     */
    public static function safeLimit($limit = null) {
        if (is_null($limit)) {
            return config('larapress.crud.repository.limit');
        }

        return min(config('larapress.crud.repository.max_limit'), max(config('larapress.crud.repository.min_limit'), $limit));
    }
}
