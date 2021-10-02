<?php

namespace Larapress\CRUD\Services\RepoSources;

use Larapress\CRUD\ICRUDUser;
use Larapress\CRUD\Services\CRUD\ICRUDService;
use Larapress\CRUD\Services\CRUD\Verbs\Show;
use Mews\Captcha\Facades\Captcha;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Larapress\CRUD\Exceptions\AppException;

class RepositorySources implements IRepositorySources
{
    /** @var ICRUDService */
    protected $crudService;

    public function __construct(ICRUDService $crudService)
    {
        $this->crudService = $crudService;
    }

    /**
     * @param ICRUDUser|null $user
     * @param array $sources
     * @param Request|null $request
     * @param Route|null $route
     *
     * @return \Illuminate\Http\Response
     */
    public function fetchRepositorySources($user, $sources, $request, $route)
    {
        $repos = [];
        foreach ($sources as $source) {
            if (!isset($source['resource']) || !isset($source['class'])) {
                throw new AppException(AppException::ERR_INVALID_QUERY);
            }

            $res = [];
            switch ($source['resource']) {
                case 'object':
                    switch ($source['class']) {
                        case 'captcha':
                            $res = Captcha::create('default', true);
                            break;
                        default:
                            $provider = new $source['class'];
                            $this->crudService->useProvider($provider);
                            $showVerb = new Show();
                            $res = $showVerb->handle($this->crudService, $request, is_null($route) ? null : $route->parameter($source['param']));
                            break;
                    }
                    break;
                case 'repository':
                    $repo = $source['class'];
                    if (Str::startsWith($repo, '\\')) {
                        $repo = Str::substr($repo, 1);
                    }
                    $safeRepos = config('larapress.crud.safe-sources');
                    if (in_array($repo, $safeRepos)) {
                        $args =  isset($source['args']) ? $source['args'] : [];
                        $repoRef = app()->make($repo);

                        $methodArgs = [];
                        usort($args, function ($a, $b) {
                            $aIndex = isset($a['index']) ? $a['index'] : 0;
                            $bIndex = isset($b['index']) ? $b['index'] : 0;
                            return $aIndex <=> $bIndex;
                        });
                        foreach ($args as $arg) {
                            if (isset($arg['type']) && $arg['type'] !== 'json') {
                                switch ($arg['type']) {
                                    case 'string':
                                        if (isset($arg['value'])) {
                                            $methodArgs[] = $arg['value'];
                                        } else {
                                            $methodArgs[] = null;
                                        }
                                        break;
                                    case 'request':
                                        $methodArgs[] = $request;
                                        break;
                                    case 'route':
                                        $methodArgs[] = $route;
                                        break;
                                    case 'param':
                                        if (isset($args['value'])) {
                                            $methodArgs[] = $route->parameter($arg['value']);
                                        } else {
                                            throw new AppException(AppException::ERR_INVALID_QUERY);
                                        }
                                        break;
                                }
                            } else {
                                if (isset($arg['value'])) {
                                    $methodArgs[] = is_string($arg['value']) ? json_decode($arg['value']) : $arg['value'];
                                } else {
                                    $methodArgs[] = null;
                                }
                            }
                        }
                        $res = call_user_func([$repoRef,  $source['method']], $user, ...$methodArgs);
                    } else {
                        throw new AppException(AppException::ERR_INVALID_QUERY);
                    }
                    break;
            }

            $repos[$source['path']] = $res;
        }
        return $repos;
    }
}
