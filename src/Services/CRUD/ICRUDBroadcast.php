<?php

namespace Larapress\CRUD\Services\CRUD;

use Illuminate\Http\Request;
use Larapress\CRUD\ICRUDUser;

interface ICRUDBroadcast
{
    /**
     * Undocumented function
     *
     * @param Request $request
     * @return mixed
     */
    public function authenticateRequest(Request $request);

    /**
     * Undocumented function
     *
     * @param ICRUDUser $user
     * @param string $name
     * @param string $verb
     * @return boolean
     */
    public function authorizeForCRUDChannel(ICRUDUser $user, $name, $verb);

    /**
     * Undocumented function
     *
     * @param ICRUDUser $user
     * @param string $name
     * @param string $verb
     * @param string $id
     * @return boolean
     */
    public function authorizeForCRUDSupportChannel(ICRUDUser $user, $name, $verb, $id);
}
