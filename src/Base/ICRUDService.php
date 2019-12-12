<?php

namespace Larapress\CRUD\Base;

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
     * @param ICRUDFilterStorage $storage
     */
    public function useCRUDFilterStorage(ICRUDFilterStorage $storage);

    /**
     * @param ICRUDExporter $exporter
     */
    public function useCRUDExporter(ICRUDExporter $exporter);

    /**
     * Search the in the resources.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @throws AppException
     * @throws \Exception
     */
    public function query(Request $request);

    /**
     * Filter the searching resourcess.
     *
     * @param Request $request
     *
     * @throws \Larapress\Core\Exceptions\AppException
     * @throws \Exception
     */
    public function filter(Request $request);

    /**
     * get a listing of the resources.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request);

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return Response
     * @throws ValidationException
     * @throws \Larapress\Core\Exceptions\AppException
     * @throws \Exception
     */
    public function store(Request $request);

    /**
     * Display this specified resource.
     *
     * @param Request $request
     * @param string  $id
     *
     * @return Response
     */
    public function show(Request $request, $id);

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     * @throws ValidationException
     * @throws \Larapress\Core\Exceptions\AppException
     * @throws \Exception
     */
    public function update(Request $request, $id);

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     * @throws \Larapress\Core\Exceptions\AppException
     */
    public function destroy($id);

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function export(Request $request);
}
