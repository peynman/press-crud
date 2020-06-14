<?php

namespace Larapress\CRUD\Commands;

use Illuminate\Console\Command;

abstract class ActionCommandBase extends Command
{
    protected $actions = [];

    /**
     * ActionCommandBase constructor.
     *
     * @param array $actions
     */
    public function __construct($actions = [])
    {
        parent::__construct();
        $this->actions = $actions;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->option('action');
        foreach ($this->actions as $act => $callback) {
            if ($action == $act) {
                $callback();

                return;
            }
        }

        $this->error('action '.$action.'not found!');
    }
}
