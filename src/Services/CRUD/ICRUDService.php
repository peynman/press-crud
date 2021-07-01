<?php

namespace Larapress\CRUD\Services\CRUD;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
     * Undocumented function
     *
     * @param ICRUDVerb $verb
     * @return void
     */
    public function addCRUDVerb(ICRUDVerb $verb);

    /**
     * Undocumented function
     *
     * @return ICRUDProvider
     */
    public function getCompositeProvider(): ICRUDProvider;

    /**
     * Undocumented function
     *
     * @return ICRUDStorage
     */
    public function getStorage(): ICRUDStorage;

    /**
     * Undocumented function
     *
     * @return ICRUDExporter
     */
    public function getExporter(): ICRUDExporter;

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getCRUDVerbs();

    /**
     * Undocumented function
     *
     * @param string|ICRUDProvider $provider
     * @return ICRUDProvider
     */
    public function makeCompositeProvider($provider): ICRUDProvider;

    /**
     * Undocumented function
     *
     * @param string $verb
     * @param Request $request
     * @param array $args
     *
     * @return Response
     */
    public function handle(string $verb, Request $request, ...$args);
}
