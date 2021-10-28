<?php

namespace Larapress\CRUD\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Larapress\CRUD\Models\Role;

class ImportRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lp:crud:import-roles {path?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import forms.';

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
        $filepath = $this->argument('path');
        if (is_null($filepath)) {
            $filepath = storage_path('/json/roles.json');
        }

        $types = json_decode(file_get_contents($filepath), true);

        foreach ($types as $type) {
            $role = Role::updateOrCreate([
                'id' => $type['id'],
                'name' => $type['name'],
            ], [
                'author_id' => $type['author_id'],
                'title' => $type['title'],
                'priority' => $type['priority'],
                'created_at' => $type['created_at'],
                'updated_at' => $type['updated_at'],
                'deleted_at' => $type['deleted_at'],
            ]);

            $permissions = Collection::make($type['permissions']);
            $role->permissions()->sync($permissions->pluck('id')->toArray());

            $this->info('Role added with name: '.$type['name'].'.');
        }

        $this->info('Roles imported.');

        return 0;
    }
}
