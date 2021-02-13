<?php

namespace Larapress\CRUD\Tests\Feature;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Hash;
use Larapress\CRUD\ICRUDUser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

abstract class CRUDTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../../../../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Undocumented function
     *
     * @return Builder
     */
    public function getUserQuery() : Builder {
        $class = config('larapress.crud.user.class');
        return call_user_func([$class, 'query']);
    }

    public function getRootUser(): ICRUDUser {
        return $this->getUserQuery()->find(1);
    }
    public function updateUser($username, $password): ICRUDUser {
        $user = $this->getUserQuery()->where('name', $username)->first();
        if (is_null($user)) {
            $class = config('larapress.crud.user.class');
            $user = call_user_func([$class, 'create'], [
                'name' => $username,
                'password' => Hash::make($password),
            ]);
        }
        return $user;
    }
    public function setupRolePermissions() {
        $this->artisan('larapress:crud', [
            '--action' => 'update:permissions',
        ]);
        $this->artisan('larapress:crud', [
            '--action' => 'create:super-role',
            '--password' => 'root',
            '--name' => 'root'
        ]);
    }

    /**
     * Setup migrations & other bootstrap stuff.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setupRolePermissions();
    }
}
