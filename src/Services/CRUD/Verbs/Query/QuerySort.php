<?php

namespace Larapress\CRUD\Services\CRUD\Verbs\Query;

use Illuminate\Database\Eloquent\Builder;
use Larapress\CRUD\ICRUDUser;

class QuerySort
{
    /**
     * Undocumented function
     *
     * @param ICRUDUser $user
     * @param Builder $query
     * @param array $orders
     *
     * @return QueryFilterResult
     */
    public function applyOrders(ICRUDUser $user, QueryRequest $request, Builder $query)
    {
        $validSorts = $request->getProvider()->getValidSortColumns();
        $validSortNames = $request->getValidSortColumnNames();
        $orders = $request->getOrders();

        if (!is_null($validSortNames) && count($validSortNames) > 0) {
            foreach ($orders as $sort) {
                if (isset($sort['column']) && isset($sort['direction'])) {
                    if (in_array($sort['column'], $validSortNames)) {
                        $dir = $sort['direction'] === 'asc' ? 'ASC' : 'DESC';
                        if (isset($validSorts[$sort['column']]) && is_callable($validSorts[$sort['column']])) {
                            $validSorts[$sort['column']]($user, $query, $dir);
                        } else {
                            $query->orderBy($sort['column'], $dir);
                        }
                    }
                }
            }
        }
    }
}
