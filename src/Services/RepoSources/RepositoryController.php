<?php

namespace Larapress\CRUD\Services\RepoSources;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class RepositoryController extends Controller
{
    public static function registerPublicApiRoutes()
    {
        Route::post('/repos',  '\\' . self::class . '@getSources')
            ->name('any.any.sources');
    }

    /**
     * Render Repositories
     *
     * @param Request $request
     * @param String $slug
     *
     * @return Response
     */
    public function getSources(IRepositorySources $service, RepositoryRequest $request)
    {
        return $service->fetchRepositorySources(Auth::user(), $request->getSources(), $request, null);
    }
}
