<?php

namespace Larapress\CRUD\Middleware;

use Closure;

class CORSMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, HEADER');
        $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, User-Agent, If-Modified-Since, Cache-Control, Range');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        return $response;
    }
}
