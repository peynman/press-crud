<?php

namespace Larapress\CRUD\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Larapress\CRUD\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Larapress\CRUD\Models\Role;
use Larapress\CRUD\Services\RBAC\IPermissionsService;
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
        ];
        if (is_null($form['name']) || is_null($form['password'])) {
            $form = $this->fillForm($form);
        }

        /** @var IPermissionsService */
        $service = app(IPermissionsService::class);
        $service->createSuperUser($form['name'], $form['password']);

        $this->info('Account created/updated.');

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
