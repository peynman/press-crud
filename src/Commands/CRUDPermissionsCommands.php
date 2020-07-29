<?php

namespace Larapress\CRUD\Commands;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Larapress\CRUD\Services\IPermissionsService;
use Larapress\CRUD\Commands\ActionCommandBase;
use Larapress\CRUD\Services\IPermissionsMetadata;
use Larapress\CRUD\Extend\Helpers;
use Larapress\CRUD\ICRUDUser;
use Larapress\CRUD\Models\Permission;
use Larapress\CRUD\Models\Role;
use Larapress\Pages\Repository\IPageRepository;

class CRUDPermissionsCommands extends ActionCommandBase
{
    const SUPER_ROLE_PRIORITY = 4294967295;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larapress:crud {--action=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create super users and assign roles';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct([
            'create:super-user' => $this->updateSuperUser(),
            'update:permissions' => $this->updatePermissions(),
            'update:super-role' => $this->updateSuperRole(),
        ]);
    }

    private function updateSuperUser()
    {
        return function () {
            $form = [
                'name' => null,
                'password' => null,
            ];
            $form = $this->fillForm($form);
            $this->updateSuperUserWithData($form);
            $this->info('Account updated with super-role.');
        };
    }

    private function updatePermissions()
    {
        return function () {
            /** @var IPermissionsService */
            $service = app(IPermissionsService::class);
            $service->forEachRegisteredProviderClass(function($meta_data_class) {
                /** @var IPermissionsMetadata $instance */
                $instance = new $meta_data_class();
                $all_verbs = $instance->getPermissionVerbs();
                foreach ($all_verbs as $verb_name) {
                    $this->info($instance->getPermissionObjectName().' -> '.$verb_name.' ['.class_basename($instance).']');
                    /* @var Permission $model */
                    Permission::firstOrCreate([
                        'name' => $instance->getPermissionObjectName(),
                        'verb' => $verb_name,
                    ]);
                }
            });
            $service->updateSuperRole();
            $this->info('Super-Role updated with latest permissions, all users with super-role are updated too.');
        };
    }

    private function updateSuperRole()
    {
        return function () {
            /** @var IPermissionsService */
            $service = app(IPermissionsService::class);
            $service->updateSuperRole();
            $this->info('Super-Role updated with latest permissions, all users with super-role are updated too.');
        };
    }


    private function fillForm($form)
    {
        $data = [];
        foreach ($form as $key => $val) {
            $data[$key] = $this->ask($key, $val);
        }

        return $data;
    }

    private function updateSuperUserWithData($form)
    {
        /** @var Builder $user_quer */
        $user_quer = call_user_func([config('larapress.crud.user.class'), 'query']);
        /** @var \Larapress\CRUD\ICRUDUser $user */
        $user = $user_quer->where('name', $form['name'])->first();

        if (is_null($user)) {
            $user = call_user_func([config('larapress.crud.user.class'), 'create'], [
                'name' => $form['name'],
                'password' => Hash::make($form['password']),
            ]);
        } else {
            if ($form['password']) {
                $user->update([
                    'password' => Hash::make($form['password']),
                ]);
            }
        }

        /** @var Role $super_role */
        $super_role = Role::where('name', 'super-role')->first();
        if (is_null($super_role)) {
            $super_role = Role::create([
                'name' => 'super-role',
                'title' => 'Super Role',
            ]);
        }
        if (! is_null($super_role)) {
            $user->roles()->sync($super_role);
        }

        /** @var int[] $permission_ids */
        $permission_ids = Permission::query()->select('id')->pluck('id');
        $super_role->permissions()->sync($permission_ids);
        $user->forgetPermissionsCache();
    }
}
