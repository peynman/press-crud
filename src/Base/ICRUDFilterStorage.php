<?php

namespace Larapress\CRUD\Base;

interface ICRUDFilterStorage
{
    /**
     * @param string     $key
     * @param array|null $value
     * @param string     $userId
     */
    public function putFilters(string $key, array $value, string $userId);

    /**
     * @param string $key
     * @param array  $defaultValue
     * @param string $userId
     *
     * @return array|null
     */
    public function getFilters(string $key, array $defaultValue, string $userId);
}
