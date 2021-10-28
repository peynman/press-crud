<?php

namespace Larapress\Profiles\Commands;

use Illuminate\Console\Command;
use Larapress\CRUD\Models\Role;

class ExportRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lp:crud:export-roles {path?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export roles.';

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
            if (!file_exists(storage_path('json'))) {
                mkdir(storage_path('json'));
            }
            $filepath = storage_path('/json/roles.json');
        }

        file_put_contents($filepath, json_encode(Role::with('permissions')->all(), JSON_PRETTY_PRINT));
        $this->info('Roles exported to path: '.$filepath.'.');

        return 0;
    }
}
