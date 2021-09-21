<?php

namespace Larapress\CRUD\Services\RepoSources;

use Larapress\CRUD\ICRUDUser;
use Illuminate\Http\Request;
use Symfony\Component\Routing\Route;

interface IRepositorySources {
    /**
     * Undocumented function
     *
     * @param ICRUDUser|null $user
     * @param array $sources
     * @param Request|null $request
     * @param Route|null $route
     *
     * @return \Illuminate\Http\Response
     */
    public function fetchRepositorySources($user, $sources, $request, $route);
}
