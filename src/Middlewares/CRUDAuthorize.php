<?php

namespace Larapress\CRUD\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Larapress\Core\Exceptions\AppException;
use Larapress\CRUD\Base\IPermissionsMetaData;
use Larapress\CRUD\ICRUDUser;

class CRUDAuthorize
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
            throw new \Exception("Route with CRUD Authorize has no name!", 500);
        }

        $name_parts = explode('.', $name);
        if (count($name_parts) < 2) {
            throw new \Exception("Route with CRUD Authorize has invalid name!", 500);
        }

        /** @var ICRUDUser $user */
        $user = Auth::user();
        if (is_null($user)) {
            throw new \Exception("Route with CRUD Authorize has to authenticate with user credentials first.", 500);
        }

        $required_permissions = [];
        switch ($name_parts[1]) {
            case 'index':
            case 'show':
            case 'query':
            case 'view':
                $required_permissions[] = $name_parts[0].'.'.IPermissionsMetaData::VIEW;
                break;
            case 'store':
            case 'create':
            case 'links-store':
                $required_permissions[] = $name_parts[0].'.'.IPermissionsMetaData::CREATE;
                break;
            case 'edit':
            case 'update':
                $required_permissions[] = $name_parts[0].'.'.IPermissionsMetaData::EDIT;
                break;
            case 'delete':
            case 'destroy':
                $required_permissions[] = $name_parts[0].'.'.IPermissionsMetaData::DELETE;
                break;
            case 'any':
                $required_permissions = null;
                break;
            case 'custom':
                $required_permissions = null;
                break;
            default:
                $name_parts[1] = str_replace('index', 'view', $name_parts[1]);
                $name_parts[1] = str_replace('show', 'view', $name_parts[1]);
                $name_parts[1] = str_replace('query', 'view', $name_parts[1]);
                $name_parts[1] = str_replace('view', 'view', $name_parts[1]);
                $required_permissions[] = $name_parts[0].'.'.$name_parts[1];
        }

        if (!is_null($required_permissions)) {
            if (!$user->hasPermission($required_permissions)) {
                throw new AppException(AppException::ERR_ACCESS_DENIED);
            }
        }

        return $next($request);
    }
}
