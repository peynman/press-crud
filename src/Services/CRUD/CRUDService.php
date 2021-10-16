<?php

namespace Larapress\CRUD\Services\CRUD;

use Illuminate\Http\Request;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\CRUD\Services\CRUD\RDBStorage;

/**
 * Class CRUDService.
 */
class CRUDService implements ICRUDService
{
    /**
     * @var ICRUDProvider
     */
    public $crudProvider;
    /**
     * @var ICRUDExporter
     */
    public $crudExporter;
    /**
     * @var ICRUDStorage
     */
    public $crudStorage;

    /** @var ICRUDVerb[] */
    public $crudVerbs;

    public function __construct()
    {
        $this->crudStorage = new RDBStorage();
        $this->crudVerbs = config('larapress.crud.verbs');
    }

    /**
     * @param ICRUDProvider $provider
     */
    public function useProvider(ICRUDProvider $provider)
    {
        $this->crudProvider = $this->makeCompositeProvider($provider);
    }

    /**
     * Undocumented function
     *
     * @param string|ICRUDProvider $provider
     * @return ICRUDProvider
     */
    public function makeCompositeProvider($provider): ICRUDProvider
    {
        if (is_string($provider)) {
            $provider = new $provider();
        }

        $compositProvider = $provider;
        $compositions = $provider->getCompositionClasses();
        if (!is_null($compositions) && count($compositions) > 0) {
            $compositProvider = array_reduce($compositions, function (ICRUDProvider $carry, string $compositClass) {
                return new $compositClass($carry);
            }, $provider);
        }

        return $compositProvider;
    }

    /**
     * @param ICRUDExporter $exporter
     */
    public function useCRUDExporter(ICRUDExporter $exporter)
    {
        $this->crudExporter = $exporter;
    }

    /**
     * @param ICRUDStorage $storage
     */
    public function useCRUDStorage(ICRUDStorage $storage)
    {
        $this->crudStorage = $storage;
    }

    /**
     * Undocumented function
     *
     * @return ICRUDProvider
     */
    public function getCompositeProvider(): ICRUDProvider
    {
        return $this->crudProvider;
    }

    /**
     * Undocumented function
     *
     * @return ICRUDStorage
     */
    public function getStorage(): ICRUDStorage
    {
        return $this->crudStorage;
    }

    /**
     * Undocumented function
     *
     * @return ICRUDExporter
     */
    public function getExporter(): ICRUDExporter
    {
        return $this->crudExporter;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getCRUDVerbs()
    {
        return $this->crudVerbs;
    }

    /**
     * Undocumented function
     *
     * @param ICRUDVerb $verb
     *
     * @return void
     */
    public function addCRUDVerb(ICRUDVerb $verb)
    {
        $this->crudVerbs[$verb->getVerbName()] = $verb;
    }

    /**
     * Undocumented function
     *
     * @param string $verb
     * @param Request $request
     * @param array $args
     *
     * @return Response
     */
    public function handle(string $verb, Request $request, ...$args)
    {
        if (!isset($this->crudVerbs[$verb])) {
            throw new AppException(AppException::ERR_INVALID_VERB);
        }

        /** @var ICRUDVerb */
        $verb = $this->crudVerbs[$verb];
        if (is_string($verb) && class_exists($verb)) {
            app()->bind(ICRUDVerb::class, $verb);
            $verb = app(ICRUDVerb::class);
        }

        return $verb->handle($this, $request, ...$args);
    }
}
