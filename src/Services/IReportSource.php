<?php

namespace Larapress\CRUD\Services;

interface IReportSource {

    /**
     * Undocumented function
     *
     * @param [type] $user
     * @return array
     */
    public function getReportNames($user);

    /**
     * Undocumented function
     *
     * @param [type] $user
     * @param string $name
     * @param array $options
     * @return array
     */
    public function getReport($user, string $name, array $options = []);
}
