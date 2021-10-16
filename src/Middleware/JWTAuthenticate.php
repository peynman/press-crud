<?php

namespace Larapress\CRUD\Middleware;

use Tymon\JWTAuth\Http\Middleware\Authenticate;
use Closure;
use Exception;
use Illuminate\Support\Facades\Auth;

class JWTAuthenticate extends Authenticate
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if (!is_null($request->query('jwttoken'))) {
                $request->headers->set('Authorization', 'Bearer '.$request->query('jwttoken'), false);
            }

            $this->authenticate($request);
            if (!is_null($request->query('jwttoken'))) {
                if (Auth::check()) {
                    if (!$request->wantsJson()) {
                        Auth::login(Auth::user());
                    }
                }
            }
        } catch (Exception $e) {}

        return $next($request);
    }
}
