<?php

namespace Larapress\CRUD\Services\CRUD\Verbs\Query;

use Illuminate\Database\Eloquent\Builder;
use Larapress\CRUD\ICRUDUser;
use Larapress\CRUD\Services\CRUD\ICRUDProvider;

class QueryFilter
{
    /**
     * Undocumented function
     *
     * @param Builder $query
     * @param array $filters
     * @param array $avFilters
     *
     * @return boolean
     */
    public function applyFilters(ICRUDUser $user, Builder $query, array $availableFilters, array $filters)
    {
        $hasFilters = false;

        foreach ($availableFilters as $field => $options) {
            if (isset($filters[$field]) && !is_null($filters[$field]) && (!is_array($filters[$field]) || count(array_keys($filters[$field])) > 0)) {
                $hasFilters = true;
                if (is_callable($options)) {
                    if (!empty($filters[$field])) {
                        $options($query, $filters[$field]);
                    }
                    continue;
                }
                if ($field === 'flags' && $filters[$field] == 0) {
                    continue;
                }

                $parts = explode(':', $options);
                switch ($parts[0]) {
                    case 'after':
                        $query->whereDate($parts[1], '>=', $filters[$field]);
                        break;
                    case 'before':
                        $query->whereDate($parts[1], '<=', $filters[$field]);
                        break;
                    case 'has':
                        $values = $filters[$field];
                        if (is_array($values) && isset($values[0][isset($parts[3]) ? $parts[3] : 'id'])) {
                            $values = collect($values)->pluck('id')->toArray();
                        } else {
                            if (is_array($values)) {
                                $values = array_keys($values);
                            } else {
                                $values = [$values];
                            }
                        }
                        if (count($values) > 0) {
                            $query->whereHas(
                                $parts[1],
                                function (Builder $q) use ($values, $parts) {
                                    $q->whereIn(isset($parts[2]) ? $parts[2] : 'id', $values);
                                }
                            );
                        }
                        break;
                    case 'has-has':
                        $values = $filters[$field];
                        if (is_array($values) && isset($values[0][isset($parts[3]) ? $parts[3] : 'id'])) {
                            $values = collect($values)->pluck('id')->toArray();
                        } else {
                            if (is_array($values)) {
                                $values = array_values($values);
                            } else {
                                $values = [$values];
                            }
                        }
                        if (count($values) > 0) {
                            $query->whereHas(
                                $parts[1],
                                function (Builder $q) use ($values, $parts) {
                                    $q->whereHas($parts[2], function ($q) use ($parts, $values) {
                                        $q->whereIn($parts[3], $values);
                                    });
                                }
                            );
                        }
                        break;
                    case 'hasnot-has':
                        $values = $filters[$field];
                        if (is_array($values) && isset($values[0][isset($parts[3]) ? $parts[3] : 'id'])) {
                            $values = collect($values)->pluck('id')->toArray();
                        } else {
                            if (is_array($values)) {
                                $values = array_values($values);
                            } else {
                                $values = [$values];
                            }
                        }
                        if (count($values) > 0) {
                            $query->whereDoesntHave(
                                $parts[1],
                                function (Builder $q) use ($values, $parts) {
                                    $q->whereHas($parts[2], function ($q) use ($parts, $values) {
                                        $q->whereIn($parts[3], $values);
                                    });
                                }
                            );
                        }
                        break;
                    case 'equals':
                        $query->where($parts[1], '=', $filters[$field]);
                        break;
                    case 'bitwise':
                        $query->where($parts[1], '&', $filters[$field]);
                        break;
                    case 'like':
                        if (strlen($filters[$field]) > 3) {
                            $query->where($parts[1], 'LIKE', $filters[$field]);
                        }
                        break;
                    case 'in':
                        if (is_array($filters[$field]) && count($filters[$field]) > 0) {
                            $ins = array_keys($filters[$field]);
                            $query->whereIn($parts[1], $ins);
                        }
                        break;
                    case 'has-count':
                        if (is_numeric($filters[$field])) {
                            $query->withCount($parts[1])->having($parts[1] . '_count', $parts[2], $filters[$field]);
                        }
                        break;
                    case '>':
                    case '<':
                    case '>=':
                    case '>=':
                        if (is_numeric($filters[$field])) {
                            $query->where($parts[1], $parts[0], $filters[$field]);
                        }
                        break;
                    case 'not-null':
                        $query->whereNotNull($parts[1]);
                        break;
                    case 'null':
                        $query->whereIsNull($parts[1]);
                        break;
                }
            }
        }

        return $hasFilters;
    }
}
