<?php

namespace Larapress\CRUD\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Larapress\CRUD\Models\Permission;
use Larapress\CRUD\Services\RBAC\IPermissionsService;
use Illuminate\Support\Str;

class UpdateUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lp:crud:update-password {--id=} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user password in database.';

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
            'id' => $this->option('id', null),
            'password' => $this->option('password', null),
        ];
        if (is_null($form['id']) || is_null($form['password'])) {
            $form = $this->fillForm($form);
        }

        /** @var Builder $user_quer */
        $user_quer = call_user_func([config('larapress.crud.user.model'), 'query']);
        /** @var \Larapress\CRUD\ICRUDUser $user */
        $user = $user_quer->find($form['id'])->first();
        if (!is_null($user)) {
            $user->update([
                'password' => Hash::make($form['password']),
            ]);
            $this->info("User {$user->id} password updated.");
        }

        return 0;
    }


    protected function fillForm($form) {
        $vals = [];
        foreach ($form as $key => $value) {
            $vals[$key] = $this->ask('Please enter '.$key.':');
        }

        return $vals;
    }
}
