<?php

namespace Larapress\CRUD\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CORSMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->getMethod() === 'OPTIONS') {
            $response = new Response('', 200);
        } else {
            $response = $next($request);
        }

        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, HEADER');
        $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, User-Agent, If-Modified-Since, Cache-Control, Range');

        return $response;
    }
}
