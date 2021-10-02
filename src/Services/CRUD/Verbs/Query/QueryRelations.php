<?php

namespace Larapress\CRUD\Services\CRUD\Verbs\Query;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Larapress\CRUD\Extend\Helpers;
use Larapress\CRUD\ICRUDUser;
use Larapress\CRUD\Services\CRUD\ICRUDProvider;
use Larapress\CRUD\Services\CRUD\ICRUDService;
use Larapress\CRUD\Services\CRUD\ICRUDVerb;
use Illuminate\Support\Str;
use Larapress\Profiles\IProfileUser;

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
        $this->recursiveRelationsQuery(
            $user,
            $request->getProvider(),
            $query,
            $qFilter,
            $request->getRelationsToLoad(),
            $request->getFilters(),
        );
    }

    protected function recursiveRelationsQuery(
        ICRUDUser $user,
        ICRUDProvider $provider,
        Builder $query,
        QueryFilter $qFilter,
        $relationsToLoad,
        array $requestFilters
    ) {
        $validRelations = $provider->getValidRelations();
        $validRelationNames = $this->getValidRelationNames($provider);

        foreach ($relationsToLoad as $relation) {
            if (!isset($relation['name']) || !isset($relation['columns'])) {
                continue;
            }

            $relationName = $relation['name'];
            $relationColumns = $relation['columns'];

            if (Str::contains($relationName, '.')) {
                $nestedRelationParts = explode('.', $relationName);
                $nestedProvider = null;
                $nestedValidRelationNames = $validRelationNames;
                $nestedValidRelations = $validRelations;
                $nestedRelationPartsCount = count($nestedRelationParts);
                for ($i = 0; $i < $nestedRelationPartsCount - 1; $i++) {
                    $nestedRelationNamePart = $nestedRelationParts[$i];
                    if (
                        !in_array($nestedRelationNamePart, $nestedValidRelationNames) ||
                        !is_string($nestedValidRelations[$nestedRelationNamePart]) ||
                        !class_exists($nestedValidRelations[$nestedRelationNamePart])
                    ) {
                        $nestedProvider = null;
                        break;
                    }

                    $nestedProvider = $this->crudService->makeCompositeProvider($nestedValidRelations[$nestedRelationNamePart]);
                    if (is_null($nestedProvider)) {
                        break;
                    }

                    $nestedValidRelations = $nestedProvider->getValidRelations();
                    $nestedValidRelationNames = $this->getValidRelationNames($nestedProvider);
                    if (!$user->hasPermission($nestedProvider->getPermissionObjectName() . '.' . ICRUDVerb::VIEW)) {
                        $nestedProvider = null;
                        break;
                    }
                }

                if (!is_null($nestedProvider) && in_array($nestedRelationParts[count($nestedRelationParts) - 1], $nestedValidRelationNames)) {
                    $this->applyValidatedRelation(
                        $user,
                        $query,
                        $qFilter,
                        $nestedRelationParts[count($nestedRelationParts) - 1],
                        $relationColumns,
                        $nestedValidRelations,
                        $requestFilters,
                        $relationName,
                    );
                }
            } else {
                if (in_array($relationName, $validRelationNames)) {
                    $this->applyValidatedRelation(
                        $user,
                        $query,
                        $qFilter,
                        $relationName,
                        $relationColumns,
                        $validRelations,
                        $requestFilters,
                        $relationName,
                    );
                }
            }
        }
    }

    protected function applyValidatedRelation(
        IProfileUser $user,
        Builder $query,
        QueryFilter $qFilter,
        $relationName,
        $relationColumns,
        $validRelations,
        array $requestFilters,
        string $relationNameFull
    ) {
        if (is_string($relationColumns)) {
            $relationColumns = array_map(function ($item) {
                return trim($item);
            }, explode(',', $relationColumns));
        }
        $shouldInclude = true;
        /** @var ICRUDProvider */
        $relationProvider = null;
        if (isset($validRelations[$relationName])) {
            if (is_callable($validRelations[$relationName])) {
                $shouldInclude = $validRelations[$relationName]($user);
            } else if (is_string($validRelations[$relationName])) {
                if (class_exists($validRelations[$relationName])) {
                    $relationProvider = $this->crudService->makeCompositeProvider($validRelations[$relationName]);
                    $shouldInclude = $user->hasPermission($relationProvider->getPermissionObjectName() . '.' . ICRUDVerb::VIEW);
                }
            }
        }

        $relationFilters = [];
        if (!is_null($relationFilters) && !is_null($relationProvider)) {
            $relationFilters = $relationProvider->getFilterFields();
        }

        if ($shouldInclude) {
            if (Str::endsWith($relationNameFull, '_count')) {
                $realRelationName = Str::substr($relationNameFull, 0, Str::length($relationNameFull) - Str::length('_count'));
                $query->withCount([
                    $realRelationName => function (Builder $relationQuery)
                    use (
                        $user,
                        $relationColumns,
                        $relationNameFull,
                        $qFilter,
                        $relationProvider,
                        $requestFilters
                    ) {
                        if (count($relationColumns) > 0 && $relationColumns[0] !== "*") {
                            $relationQuery->select($relationColumns);
                        }
                        if (isset($relationFilters[$relationNameFull])) {
                            $qFilter->applyFilters($user, $relationQuery, $relationFilters[$relationNameFull], $requestFilters);
                        }
                        if (!is_null($relationProvider)) {
                            $relationProvider->onBeforeQuery($relationQuery);
                        }
                    }
                ]);
            } else {
                $query->with([
                    $relationNameFull => function (Relation $relationQuery)
                    use (
                        $user,
                        $relationColumns,
                        $relationNameFull,
                        $qFilter,
                        $relationProvider,
                        $requestFilters
                    ) {
                        if (count($relationColumns) > 0 && $relationColumns[0] !== "*") {
                            $relationQuery->select($relationColumns);
                        }
                        if (isset($relationFilters[$relationNameFull])) {
                            $qFilter->applyFilters($user, $relationQuery->getQuery(), $relationFilters[$relationNameFull], $requestFilters);
                        }
                        if (!is_null($relationProvider)) {
                            $relationProvider->onBeforeQuery($relationQuery->getQuery());
                        }
                    }
                ]);
            }
        }
    }

    protected function getValidRelationNames(ICRUDProvider $provider)
    {
        $validRelationNames = $provider->getValidRelations();
        if (Helpers::isAssocArray($validRelationNames)) {
            $validRelationNames = array_keys($validRelationNames);
        }

        $counts = [];
        foreach ($validRelationNames as $name) {
            $counts[] = $name.'_count';
        }

        return array_merge($validRelationNames, $counts);
    }
}
