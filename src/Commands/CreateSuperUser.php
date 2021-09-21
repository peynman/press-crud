<?php

namespace Larapress\CRUD\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Larapress\CRUD\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Larapress\CRUD\Models\Role;
use Larapress\Profiles\Models\Domain;

class CreateSuperUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lp:crud:create-super-user {--name=} {--password=} {--domain=}';

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
            'domain' => $this->option('domain', null)
        ];
        if (is_null($form['name']) || is_null($form['password'])) {
            $form = $this->fillForm($form);
        }
        self::updateSuperUserWithData($form);
        $this->info('Account updated with super-role.');

        return 0;
    }

    protected function fillForm($form) {
        $vals = [];
        foreach ($form as $key => $value) {
            $vals[$key] = $this->ask('Please enter '.$key.':');
        }

        return $vals;
    }

    public static function updateSuperUserWithData($form)
    {
        /** @var Builder $user_quer */
        $user_quer = call_user_func([config('larapress.crud.user.model'), 'query']);
        /** @var \Larapress\CRUD\ICRUDUser $user */
        $user = $user_quer->where('name', $form['name'])->first();

        if (is_null($user)) {
            $user = call_user_func([config('larapress.crud.user.model'), 'create'], [
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
                'priority' => 4294967295,
            ]);
        }
        if (! is_null($super_role)) {
            $user->roles()->sync($super_role);
        }

        if (is_numeric($form['domain'])) {
            $user->domains()->attach($form['domain']);
        } else if (is_string($form['domain'])) {
            $user->domains()->attach(Domain::where('name', $form['domain'])->first()->id);
        }

        /** @var int[] $permission_ids */
        $permission_ids = Permission::query()->select('id')->pluck('id');
        $super_role->permissions()->sync($permission_ids);
        $user->forgetPermissionsCache();
    }
}
