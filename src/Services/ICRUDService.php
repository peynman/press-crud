<?php

namespace Larapress\CRUD\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Larapress\Core\Exceptions\AppException;
use Larapress\Core\Exceptions\ValidationException;

interface ICRUDService
{
    /**
     * @param ICRUDProvider $provider
     */
    public function useProvider(ICRUDProvider $provider);

    /**
     * @param ICRUDStorage $storage
     */
    public function useCRUDStorage(ICRUDStorage $storage);

    /**
     * @param ICRUDExporter $exporter
     */
    public function useCRUDExporter(ICRUDExporter $exporter);

    /**
     * Search the in the resources.
     *
     * @param Request $request
     *
     * @return LengthAwarePaginator
     * @throws AppException
     * @throws \Exception
     */
    public function query(Request $request);


    /**
     * Undocumented function
     *
     * @param Request $request
     * @return [Builder, int]
     * @throws AppException
     * @throws \Exception
     */
    public function buildQueryForRequest(Request $request, $onBeforeQuery = null);

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Database\Eloquent\Model|Response
     * @throws ValidationException
     * @throws AppException
     * @throws \Exception
     */
    public function store(Request $request);

    /**
     * Display this specified resource.
     *
     * @param Request $request
     * @param string  $id
     *
     * @return \Illuminate\Database\Eloquent\Model|Response
     */
    public function show(Request $request, $id);

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response|\Illuminate\Database\Eloquent\Model
     * @throws ValidationException
     * @throws AppException
     * @throws \Exception
     */
    public function update(Request $request, $id);

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     * @return Response
     */
    public function destroy(Request $request, $id);

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function reports(Request $request);

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function export(Request $request);
}
