<?php

namespace Larapress\CRUD\Tests;

use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Hash;
use Larapress\CRUD\ICRUDUser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\StatefulGuard;

abstract class PackageTestApplication extends TestCase
{
    use DatabaseMigrations;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../../../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Undocumented function
     *
     * @return Builder
     */
    public function getUserQuery(): Builder
    {
        $class = config('larapress.crud.user.class');
        return call_user_func([$class, 'query']);
    }

    /**
     * Undocumented function
     *
     * @return ICRUDUser
     */
    public function getRootUser()
    {
        return $this->getUserQuery()->find(1);
    }
    public function updateUser($username, $password): ICRUDUser
    {
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
    public function setupRolePermissions()
    {
        $this->artisan('lp:crud', [
            '--action' => 'update:permissions',
        ]);
        $this->artisan('lp:crud', [
            '--action' => 'create:super-user',
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

    protected $userTokens = [];
    protected function getAuthorizationToken($user)
    {
        if (!isset($this->userTokens[$user->id])) {
            $guards = config('auth.guards');
            foreach ($guards as $guardName => $guardParams) {
                if ($guardName === 'api') {
                    /** @var StatefulGuard $guard */
                    $guard = Auth::guard($guardName);
                    $token = $guard->login($user, true);
                    if ($token !== false) {
                        if (!is_null($token)) {
                        $this->userTokens[$user->id] = $token;
                        }
                    }
                }
            }
        }

        return $this->userTokens[$user->id];
    }
}
