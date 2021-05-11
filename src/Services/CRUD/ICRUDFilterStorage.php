<?php

namespace Larapress\CRUD\Services\CRUD;

interface ICRUDFilterStorage
{
    /**
     * @param string     $key
     * @param array|null $value
     * @param string     $userId
     */
    public function putFilters(string $key, $value, string $userId);

    /**
     * @param string $key
     * @param array  $defaultValue
     * @param string $userId
     *
     * @return array|null|string
     */
    public function getFilters(string $key, $defaultValue, string $userId);

    /**
     * @param string $sessionId
     * @param string $providerClass
     * @return string
     */
    public function getFilterKey(string $sessionId, string $providerClass);

    /**
     * @param string $sessionId
     * @param ICRUDProvider $provider
     * @return string[]|array|null
     */
    public function getFilterValues(string $sessionId, ICRUDProvider $provider);
}
