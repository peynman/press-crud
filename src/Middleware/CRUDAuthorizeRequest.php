<?php

namespace Larapress\CRUD\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\CRUD\ICRUDUser;

class CRUDAuthorizeRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {
        $name = $request->route()->getName();
        if (is_null($name)) {
            throw new \Exception('Route with CRUD Authorize has no name!', 500);
        }

        $name_parts = explode('.', $name);
        if (count($name_parts) < 2) {
            throw new \Exception('Route with CRUD Authorize has invalid name!', 500);
        }

        /** @var ICRUDUser $user */
        $user = Auth::user();
        if (is_null($user)) {
            throw new \Exception('Route with CRUD Authorize has to authenticate with user credentials first.', 500);
        }

        $required_permissions = self::getCRUDVerbPermissions($name_parts[0], $name_parts[1]);
        if (! is_null($required_permissions)) {
            if (! $user->hasPermission($required_permissions)) {
                throw new AppException(AppException::ERR_ACCESS_DENIED);
            }
        }

        return $next($request);
    }

    /**
     * @param $name
     * @param $verb
     * @return array|null
     */
    public static function getCRUDVerbPermissions($name, $verb)
    {
        if ($verb === 'any') {
            return null;
        }

        return [$name.'.'.$verb];
    }
}
