<?php

namespace Larapress\CRUD\Services\CRUD;
use Larapress\CRUD\ICRUDUser;

interface ICRUDReportSource
{

    /**
     * Undocumented function
     *
     * @param ICRUDUser $user
     * @return array
     */
    public function getReportNames(ICRUDUser $user);

    /**
     * Undocumented function
     *
     * @param ICRUDUser $user
     * @param string $name
     * @param array $options
     *
     * @return array
     */
    public function getReport(ICRUDUser $user, string $name, array $options = []);
}
