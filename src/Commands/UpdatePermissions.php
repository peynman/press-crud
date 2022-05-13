<?php

namespace Larapress\CRUD\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Larapress\CRUD\Models\Permission;
use Larapress\CRUD\Services\RBAC\IPermissionsService;
use Illuminate\Support\Str;

/* It iterates over all registered providers and creates a permission for each verb */
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
            foreach ($all_verbs as $verb => $meta) {
                if (is_string($meta)) {
                    $verb_name = $meta;
                } else {
                    $verb_name = $verb;
                }

                if (Str::contains($verb_name, '.')) {
                    continue;
                }

                $this->info($instance->getPermissionObjectName().' -> '.$verb_name.' ['.class_basename($instance).']');
                if (is_null($instance->getPermissionObjectName())) {
                    throw new Exception("Provider doeas not have valid name: ". $meta_data_class);
                }
                /* @var Permission $model */
                Permission::firstOrCreate([
                    'name' => $instance->getPermissionObjectName(),
                    'verb' => $verb_name,
                ]);
            }
        });
        $service->updateSuperRole();
        Artisan::call('cache:clear');
        $this->info('Super-Role updated with latest permissions, all users with super-role are updated too.');

        return 0;
    }
}
