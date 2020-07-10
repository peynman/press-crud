<?php

namespace Larapress\CRUD\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\CRUD\Base\IPermissionsMetadata;
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
    public static function getCRUDVerbPermissions($name, $verb) {
        $required_permissions = [];

        switch ($verb) {
            case 'index':
            case 'show':
            case 'query':
            case 'view':
                $required_permissions[] = $name.'.'.IPermissionsMetadata::VIEW;
                break;
            case 'store':
            case 'create':
                $required_permissions[] = $name.'.'.IPermissionsMetadata::CREATE;
                break;
            case 'edit':
            case 'update':
                $required_permissions[] = $name.'.'.IPermissionsMetadata::EDIT;
                break;
            case 'delete':
            case 'destroy':
                $required_permissions[] = $name.'.'.IPermissionsMetadata::DELETE;
                break;
            case 'reports':
                $required_permissions[] = $name.'.'.IPermissionsMetadata::REPORTS;
                break;
            case 'any':
            case 'custom':
                    $required_permissions = null;
                break;
            default:
                $verb = str_replace('index', 'view', $verb);
                $verb = str_replace('show', 'view', $verb);
                $verb = str_replace('query', 'view', $verb);
                $verb = str_replace('view', 'view', $verb);
                $required_permissions[] = $name.'.'.$verb;
                break;
        }

        return $required_permissions;
    }
}
