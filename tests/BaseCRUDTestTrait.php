<?php

namespace Larapress\CRUD\Tests;

use Larapress\CRUD\Services\CRUD\ICRUDProvider;
use Larapress\CRUD\Services\RBAC\IPermissionsMetadata;

trait BaseCRUDTestTrait
{
    /**
     * Undocumented function
     *
     * @param ICRUDProvider|IPermissionsMetadata $provider
     * @return \Illuminate\Testing\TestResponse
     */
    public function doCRUDCreateTest($provider, $data = [], $user = 1)
    {
        return $this->post(
            implode('/', [config('larapress.crud.prefix'), $provider->getPermissionObjectName()]),
            $data,
            $this->getAuthorizationHeader($user, [
                'Accept' => 'application/json'
            ])
        );
    }


    /**
     * Undocumented function
     *
     * @param int $id
     * @param ICRUDProvider|IPermissionsMetadata $provider
     * @return \Illuminate\Testing\TestResponse
     */
    public function doCRUDUpdateTest($provider, $id, $data = [], $user = 1)
    {
        return $this->put(
            implode('/', [config('larapress.crud.prefix'), $provider->getPermissionObjectName(), $id]),
            $data,
            $this->getAuthorizationHeader($user, [
                'Accept' => 'application/json'
            ])
        );
    }
}
