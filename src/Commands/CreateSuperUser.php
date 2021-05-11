<?php

namespace Larapress\CRUD\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Larapress\CRUD\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Larapress\CRUD\Models\Role;

class CreateSuperUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lp:crud:create-super-user {--name=} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Super User.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $form = [
            'name' => $this->option('name', null),
            'password' => $this->option('password', null),
        ];
        if (is_null($form['name']) || is_null($form['password'])) {
            $form = $this->fillForm($form);
        }
        self::updateSuperUserWithData($form);
        $this->info('Account updated with super-role.');

        return 0;
    }


    public static function updateSuperUserWithData($form)
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
