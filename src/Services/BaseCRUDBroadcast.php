<?php

namespace Larapress\CRUD\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Larapress\CRUD\Exceptions\AppException;
use Larapress\CRUD\ICRUDUser;

class BaseCRUDBroadcast implements IBaseCRUDBroadcast
{
    /**
     * Undocumented function
     *
     * @param Request $request
     * @return mixed
     */
    public function authenticateRequest(Request $request)
    {
        $user = Auth::user();
        if ($request->get('channel_name', null) === 'presence-website' || !is_null($user)) {
            if (Auth::check()) {
                return [
                    'channel_data' => [
                        'user_id' => $user->id,
                        'user_info' => $user->name,
                    ],
                ];
            }

            return true;
        }

        throw new AppException(AppException::ERR_ACCESS_DENIED);
    }

    /**
     * Undocumented function
     *
     * @param ICRUDUser $user
     * @param string $name
     * @param string $verb
     * @return boolean
     */
    public function authorizeForCRUDChannel(ICRUDUser $user, $name, $verb)
    {
        if (
            !is_null($user) &&
            $user->hasRole(config('larapress.profiles.security.roles.super-role')) &&
            $user->hasPermission([$name . '.' . $verb])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Undocumented function
     *
     * @param ICRUDUser $user
     * @param string $name
     * @param string $verb
     * @param string $id
     * @return boolean
     */
    public function authorizeForCRUDSupportChannel(ICRUDUser $user, $name, $verb, $id)
    {
        if (
            !is_null($user) &&
            $user->hasRole(config('larapress.profiles.security.roles.affiliate')) &&
            $user->hasPermission([$name . '.' . $verb]) &&
            $user->id == $id
        ) {
            return true;
        }

        return false;
    }
}
