<?php

namespace Tests\Feature;

use Larapress\CRUD\Models\Permission;
use Larapress\CRUD\Tests\PackageTestApplication;

class RoleCRUDTest extends PackageTestApplication
{
    public function testUnAuthenticatedAPICall()
    {
        $this->json('POST', 'api/'.config('larapress.crud.routes.roles.name'), [
            'name' => 'test-role',
            'title' => 'test role',
            'priority' => 100,
            'permissions' => Permission::query()->get(['id'])->toArray(),
        ])
        // redirects to signin
        ->assertStatus(302);
    }

    public function testRoleCreate()
    {
        $token = $this->getAuthorizationToken($this->getRootUser());
        // success create
        $this->json('POST', 'api/'.config('larapress.crud.routes.roles.name'), [
            'name' => 'test-role',
            'title' => 'test role',
            'priority' => 100,
            'permissions' => Permission::query()->get(['id'])->toArray(),
        ], [
            'Authorization' => $token,
        ])->assertStatus(200);

        // invalid creates
        $this->json('POST', 'api/'.config('larapress.crud.routes.roles.name'), [
            'name' => 'test-role',
            'title' => 'test role',
            'priority' => 100,
            'permissions' => Permission::query()->get(['id'])->toArray(),
        ], [
            'Authorization' => $token,
        ])->assertStatus(400);

        // name, title required
        $this->json('POST', 'api/'.config('larapress.crud.routes.roles.name'), [
            'priority' => 100,
            'permissions' => Permission::query()->get(['id'])->toArray(),
        ], [
            'Authorization' => $token,
        ])->assertStatus(400);

        // name with invalid chars
        $this->json('POST', 'api/'.config('larapress.crud.routes.roles.name'), [
            'name' => 'test role invalid',
            'title' => 'test role invalud',
            'priority' => 100,
            'permissions' => Permission::query()->get(['id'])->toArray(),
        ], [
            'Authorization' => $token,
        ])->assertStatus(400);
    }

    public function testRoleUpdate()
    {
        $token = $this->getAuthorizationToken($this->getRootUser());
        // create test role
        $this->json('POST', 'api/'.config('larapress.crud.routes.roles.name'), [
            'name' => 'test-role',
            'title' => 'test role',
            'priority' => 100,
            'permissions' => Permission::query()->get(['id'])->toArray(),
        ], [
            'Authorization' => $token,
        ])->assertStatus(200);

        // update test role success
        $this->json('PUT', 'api/'.config('larapress.crud.routes.roles.name').'/2', [
            'name' => 'test-role',
            'title' => 'test role updated',
            'priority' => 100,
            'permissions' => Permission::query()->where('id', '<', 30)->get(['id'])->toArray(),
        ], [
            'Authorization' => $token,
        ])->assertStatus(200);

        // use already exists name for another role
        $this->json('PUT', 'api/'.config('larapress.crud.routes.roles.name').'/2', [
            'name' => 'super-role',
            'title' => 'test role updated',
            'priority' => 100,
            'permissions' => Permission::query()->where('id', '<', 30)->get(['id'])->toArray(),
        ], [
            'Authorization' => $token,
        ])->assertStatus(400);
    }
}
