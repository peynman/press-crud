<?php

namespace Larapress\CRUD\Services\CRUD\Verbs\Query;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\CRUD\Services\CRUD\ICRUDService;
use Larapress\CRUD\Services\CRUD\ICRUDVerb;
use Larapress\CRUD\ICRUDUser;
use Illuminate\Pagination\LengthAwarePaginator;
use Larapress\CRUD\Services\Pagination\PaginatedResponse;

class Query implements ICRUDVerb
{
    /**
     * Undocumented function
     *
     * @return string
     */
    public function getVerbName(): string
    {
        return ICRUDVerb::VIEW;
    }

    /**
     * Undocumented function
     *
     * @param ICRUDService $service
     * @param Request $request
     * @param ...$args
     *
     * @return PaginatedResponse
     */
    public function handle(ICRUDService $service, Request $request, ...$args)
    {
        $qRequest = QueryRequest::createFromBase($request);
        $qRequest->useProvider($service->getCompositeProvider());

        /** @var ICRUDUser */
        $user = Auth::user();

        $qResult = $this->getQueryFromRequest($user, $service, $qRequest);

        return new PaginatedResponse(
            new LengthAwarePaginator(
                $qResult->getItems(),
                $qResult->getQueryTotal(),
                $qResult->perPage,
                $qResult->currentPage,
            ),
            $request->get('refId', 1)
        );
    }

    /**
     * @param QueryRequest $request
     * @param closure $onBeforeQuery
     *
     * @return QueryFilterResult
     *
     * @throws AppException
     */
    protected function getQueryFromRequest(ICRUDUser $user, ICRUDService $crudService, QueryRequest $request, $onBeforeQuery = null)
    {
        $crudProvider = $request->getProvider();

        /*** @var Builder $query */
        $query = $crudProvider->onBeforeQuery(call_user_func([$crudProvider->getModelClass(), 'query']));
        if (!is_null($onBeforeQuery)) {
            $onBeforeQuery($query);
        }

        $qFilter = new QueryFilter();

        if ($request->hasRelations()) {
            $qRelations = new QueryRelations($crudService);
            $qRelations->loadRelations($user, $request, $query, $qFilter);
        }

        if ($request->isSearchQuery()) {
            $qSearch = new QuerySearch();
            $qSearch->applySearch($user, $request, $query);
        } else {
            $qFilter->applyFilters($user, $query, $crudProvider->getFilterFields(), $request->getFilters());
        }

        if ($request->shouldSort()) {
            $qSort = new QuerySort();
            $qSort->applyOrders($user, $request, $query);
        }

        return $this->getPaginatedResultForQuery($user, $request, $query);
    }

        /**
     * Undocumented function
     *
     * @param Builder $query
     * @param array $filters
     * @param array $avFilters
     *
     * @return QueryFilterResult
     */
    public function getPaginatedResultForQuery(ICRUDUser $user, QueryRequest $request, Builder $query)
    {
        // clone request for calculating total records and offset
        $cq = clone $query;
        // dont include relations in clone
        $cq->setEagerLoads([]);

        // find query total count if this is not a search
        if (!$request->isSearchQuery()) {
            $total = $cq->count();
        } else {
            // on search return total as number of matched records
            $total = -1;
        }

        // apply pagination
        $paginate_from = max(0, intval($request->get('page', 1) - 1));
        $limit = $request->get('limit', 10);

        // for records more than 100, find current page id and filter with that
        if ($total > 100) {
            $offset = $cq->skip($paginate_from * $limit)->first();
            if (!is_null($offset)) {
                $query->where('id', '>=', $offset->id);
            }
            $query->take($limit);
        } else {
            // for records less than 100, just use skip function
            $query->skip($paginate_from * $limit)->take($limit);
        }

        return new QueryFilterResult(
            $query,
            $total,
            $paginate_from + 1,
            $limit,
        );
    }
}
