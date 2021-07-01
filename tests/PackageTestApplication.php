<?php

namespace Larapress\CRUD\Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Hash;
use Larapress\CRUD\ICRUDUser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\StatefulGuard;
use \Illuminate\Contracts\Auth\Authenticatable;

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

        /**
         * Override the validation captcha extension to always return true.
         */
        $app['validator']->extend('captcha_api', function () {
            return true;
        });
        return $app;
    }

    /**
     * Undocumented function
     *
     * @return Builder
     */
    public function getUserQuery(): Builder
    {
        $class = config('larapress.crud.user.model');
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
    public function beRootUser()
    {
        /** @var Authenticatable */
        $user = $this->getRootUser();
        $this->be($user);
    }
    public function updateUser($username, $password): ICRUDUser
    {
        $user = $this->getUserQuery()->where('name', $username)->first();
        if (is_null($user)) {
            $class = config('larapress.crud.user.model');
            $user = call_user_func([$class, 'create'], [
                'name' => $username,
                'password' => Hash::make($password),
            ]);
        }
        return $user;
    }
    public function setupRolePermissions()
    {
        $this->artisan('lp:crud:update-permissions');
        $this->artisan('lp:crud:create-super-user', [
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
    /**
     * Undocumented function
     *
     * @param Model|int $user
     * @return string|null
     */
    protected function getAuthorizationToken($user)
    {
        if (is_numeric($user)) {
            $user = call_user_func([config('larapress.crud.user.model'), 'find'], $user);
        }
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

    /**
     * Undocumented function
     *
     * @param integer $user
     * @param array $headers
     * @return array
     */
    protected function getAuthorizationHeader($user = 1, $headers = [])
    {
        return array_merge($headers, [
            'Authorization: Bearer ' . $this->getAuthorizationToken($user),
        ]);
    }
}
