<?php

namespace Larapress\CRUD\Services\CRUD\Verbs\Query;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Larapress\CRUD\ICRUDUser;
use Larapress\CRUD\Services\CRUD\ICRUDProvider;
use Larapress\CRUD\Services\CRUD\ICRUDService;
use Larapress\CRUD\Services\CRUD\ICRUDVerb;

class QueryRelations
{
    /** @var QueryRequest */
    protected $request;

    /** @var ICRUDProvider */
    protected $provider;

    /** @var ICRUDService */
    protected $crudService;

    public function __construct(ICRUDService $service)
    {
        $this->crudService = $service;
    }

    /**
     * Undocumented function
     *
     * @param Builder $query
     * @param QueryRequest $request
     *
     * @return QueryFilterResult
     */
    public function loadRelations(ICRUDUser $user, QueryRequest $request, Builder $query, QueryFilter $qFilter)
    {
        $provider = $request->getProvider();
        $validRelations = $provider->getValidRelations();
        $validRelationNames = $request->getValidRelationNames();
        $relations = $request->getRelationsToLoad();

        $availableFilters = $provider->getFilterFields();
        $relationFilters = isset($availableFilters['relations']) ? $availableFilters['relations'] : [];

        foreach ($relations as $relation) {
            if (isset($relation['name']) && isset($relation['columns'])) {
                $name = $relation['name'];
                $relationColumns = $relation['columns'];

                if (in_array($name, $validRelationNames)) {
                    if (is_string($relationColumns)) {
                        $relationColumns = array_map(function ($item) {
                            return trim($item);
                        }, explode(',', $relationColumns));
                    }
                    $shouldInclude = true;
                    /** @var ICRUDProvider */
                    $relationProvider = null;
                    if (isset($validRelations[$name])) {
                        if (is_callable($validRelations[$name])) {
                            $shouldInclude = $validRelations[$name]($user);
                        } else if (is_string($validRelations[$name])) {
                            if (class_exists($validRelations[$name])) {
                                $relationProvider = $this->crudService->makeCompositeProvider($validRelations[$name]);
                                $shouldInclude = $user->hasPermission($relationProvider->getPermissionObjectName().'.'.ICRUDVerb::VIEW);
                            }
                        }
                    }
                    if ($shouldInclude) {
                        $query->with([
                            $name => function (Relation $relationQuery)
                            use (
                                $user,
                                $relationColumns,
                                $name,
                                $qFilter,
                                $relationFilters,
                                $relationProvider,
                                $request,
                            ) {
                                if (count($relationColumns) > 0 && $relationColumns[0] !== "*") {
                                    $relationQuery->select($relationColumns);
                                }
                                if (isset($relationFilters[$name])) {
                                    $qFilter->applyFilters($user, $relationQuery->getQuery(), $relationFilters[$name], $request->getFilters());
                                }
                                if (!is_null($relationProvider)) {
                                    $relationProvider->onBeforeQuery($relationQuery->getQuery());
                                }
                            }
                        ]);
                    }
                }
            }
        }
    }
}
