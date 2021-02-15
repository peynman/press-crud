<?php

namespace Tests\Feature;

use Larapress\CRUD\Models\Permission;
use Larapress\CRUD\Tests\PackageTestApplication;

class APITest extends PackageTestApplication
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testPageNotFound()
    {
        $response = $this->get('/');

        $response->assertStatus(404);
    }

    // /**
    //  * Undocumented function
    //  *
    //  * @return void
    //  */
    public function testRootUserExistance() {
        $user = $this->getUserQuery()->find(1);

        $this->assertNotNull($user);
        $this->assertTrue($user->name === 'root');
    }

    public function testRootPermissionsExist() {
        $permissions = Permission::all();

        $this->assertTrue($permissions->count() > 0);
    }
}
