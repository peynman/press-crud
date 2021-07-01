<?php

namespace Larapress\CRUD\Services\CRUD\Verbs\Query;

use Illuminate\Database\Eloquent\Builder;
use Larapress\CRUD\ICRUDUser;
use Larapress\CRUD\Services\CRUD\ICRUDProvider;
use Illuminate\Support\Str;

class QuerySearch
{
    /**
     * Undocumented function
     *
     * @param Builder $query
     * @param array $filters
     * @param array $avFilters
     *
     * @return QueryFilterResult
     */
    public function applySearch(ICRUDUser $user, QueryRequest $request, Builder $query)
    {
        $searchTerm = $request->getSearchTerm();
        $searchableColumns = $request->getProvider()->getSearchableColumns();

        if (Str::startsWith($searchTerm, '#')) {
            $query->where('id', substr($searchTerm, 1))->withTrashed();
        } else {
            $searchIndexer = 0;
            ini_set('memory_limit', '256M');
            if (count($searchableColumns) > 0) {
                foreach ($searchableColumns as $column) {
                    $parts = explode(':', $column);
                    // dont clone and union the query for last search column
                    if ($searchIndexer === count($searchableColumns) - 1) {
                        $search = $query;
                    } else {
                        $search = clone $query;
                    }
                    $searchIndexer++;
                    if (count($parts) == 1) {
                        $search->where($column, 'LIKE', '%' . $searchTerm . '%');
                    } else {
                        switch ($parts[0]) {
                            case 'has':
                                $has = explode(',', $parts[1]);
                                if (count($has) == 2) {
                                    $search->whereHas(
                                        $has[0],
                                        function (Builder $q) use ($searchTerm, $has) {
                                            $q->where($has[1], 'LIKE', '%' . $searchTerm . '%');
                                        }
                                    );
                                }
                                break;
                            case 'has_exact':
                                $has = explode(',', $parts[1]);
                                if (count($has) == 2) {
                                    $search->whereHas(
                                        $has[0],
                                        function (Builder $q) use ($searchTerm, $has) {
                                            $q->where($has[1], $searchTerm);
                                        }
                                    );
                                }
                                break;
                            case 'equals':
                                $search->where($parts[1], '=', $searchTerm);
                                break;
                        }
                    }
                    if ($search !== $query) {
                        $query->union($search);
                    }
                }
            }
        }
    }
}
