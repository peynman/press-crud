<?php

namespace Larapress\CRUD\Commands;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Larapress\Core\Commands\ActionCommandBase;
use Larapress\CRUD\Base\IPermissionsMetaData;
use Larapress\CRUD\ICRUDUser;
use Larapress\CRUD\Models\Permission;
use Larapress\CRUD\Models\Role;
use Larapress\CRUDRender\CRUD\ICRUDPermissionView;

class AccountManager extends ActionCommandBase
{
    const SUPER_ROLE_PRIORITY = PHP_INT_MAX;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:accounts {--action=}';

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
                'number' => null,
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
            $meta_data_classes = config('larapress.crud.permissions');
            $process_class_names = function ($meta_data_classes, $callback) {
                foreach ($meta_data_classes as $meta_data_class) {
                    if (Str::startsWith($meta_data_class, 'include::')) {
                        $callback(config(Str::substr($meta_data_class, Str::length('include::'))), $callback);
                    } else {
                        /** @var IPermissionsMetaData|ICRUDPermissionView $instance */
                        $instance = call_user_func([$meta_data_class, 'instance']);
                        $all_verbs = $instance->getPermissionVerbs();
                        foreach ($all_verbs as $verb_name) {
                            $name = $instance->getPermissionObjectName().'.'.$verb_name;
                            $title = $instance->getTitleByVerbName($verb_name);
                            $this->info($name);
                            /** @var Permission $model */
                            $model = Permission::where('name', $name)->first();
                            if (is_null($model)) {
                                Permission::create([
                                    'name' => $name,
                                    'title' => $title,
                                    'group_name' => $instance->getPermissionObjectName(),
                                    'group_title' => $instance->plural(),
                                ]);
                            } else {
                                $model->update([
                                    'title' => $title,
                                    'group_name' => $instance->getPermissionObjectName(),
                                    'group_title' => $instance->plural(),
                                ]);
                            }
                        }
                    }
                }
            };
            $process_class_names($meta_data_classes, $process_class_names);
        };
    }

    private function updateSuperRole()
    {
        return function () {
            /** @var Role $super_role */
            $super_role = Role::where('name', 'super-role')->first();
            if (is_null($super_role)) {
                $super_role = Role::create([
                    'name' => 'super-role',
                    'title' => 'Super Role',
                    'priority' => self::SUPER_ROLE_PRIORITY,
                ]);
            }
            /** @var int[] $permission_ids */
            $permission_ids = Permission::query()->select('id')->pluck('id');
            $super_role->permissions()->sync($permission_ids);

            /** @var Builder $user_query */
            $user_query = call_user_func([config('larapress.crud.user.class'), 'query']);
            /** @var ICRUDUser[] $super_users */
            $super_users = $user_query->whereHas(
                'roles',
                function (/* @var Builder $q */$q) {
                    $q->where('name', 'super-role');
                }
            )->get();

            foreach ($super_users as $super_user) {
                $this->info('Permissions cache cleared for user: '.$super_user->name);
                $super_user->forgetPermissionsCache();
            }

            $this->info('Super-Role updated with latest permissions, all users with super-role are updated too.');
        };
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
            $user->update([
                'password' => Hash::make($form['password']),
            ]);
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

    private function fillForm($form)
    {
        $data = [];
        foreach ($form as $key => $val) {
            $data[$key] = $this->ask($key, $val);
        }

        return $data;
    }
}
