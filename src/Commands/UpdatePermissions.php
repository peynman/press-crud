<?php

namespace Larapress\CRUD\Commands;

use Illuminate\Console\Command;
use Larapress\CRUD\Models\Permission;
use Larapress\CRUD\Services\RBAC\IPermissionsService;

class UpdatePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lp:crud:update-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update CRUD permissions in database.';

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
        /** @var IPermissionsService */
        $service = app(IPermissionsService::class);
        $service->forEachRegisteredProviderClass(function ($meta_data_class) {
            /** @var IPermissionsMetadata $instance */
            $instance = new $meta_data_class();
            $all_verbs = $instance->getPermissionVerbs();
            foreach ($all_verbs as $verb_name) {
                $this->info($instance->getPermissionObjectName().' -> '.$verb_name.' ['.class_basename($instance).']');
                if (is_null($instance->getPermissionObjectName())) {
                    dd($instance);
                }
                /* @var Permission $model */
                Permission::firstOrCreate([
                    'name' => $instance->getPermissionObjectName(),
                    'verb' => $verb_name,
                ]);
            }
        });
        $service->updateSuperRole();
        $this->info('Super-Role updated with latest permissions, all users with super-role are updated too.');

        return 0;
    }
}
