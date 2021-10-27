<?php

namespace Larapress\CRUD\Commands;

use Illuminate\Console\Command;
use Larapress\CRUD\Services\CRUD\ICRUDProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Larapress\CRUD\Services\CRUD\ICRUDService;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

class CreateCRUDJSON extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lp:crud:json {crud} {--depth=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create JSON description for CRUD resource.';

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
        $providerClass = $this->argument('crud');

        $maxDepth = $this->option('depth');
        if (is_null($maxDepth)) {
            $maxDepth = 3;
        }

        if ($providerClass === 'repos') {
            $this->writeRepositoriesJson();
        } else {
            $this->writeModelProviderClassJson($providerClass, $maxDepth);
        }

        return 0;
    }

    protected function writeRepositoriesJson()
    {
        $repos = config('larapress.crud.safe-sources');

        $json = [];
        foreach ($repos as $repo) {
            $interface = new ReflectionClass($repo);
            $json[$interface->getShortName()] = [
                'methods' => array_map(function (ReflectionMethod $m) {
                    return [
                        'name' => $m->getName(),
                        'params' => array_map(function (ReflectionParameter $p) {
                            return [
                                'name' => $p->getName(),
                                'type' => $p->getType(),
                            ];
                        }, $m->getParameters()),
                    ];
                }, $interface->getMethods()),
                'class' => $repo,
            ];
        }

        $handle = fopen(storage_path('json/crud/repos.json'), 'w');
        fwrite($handle, json_encode($json, JSON_PRETTY_PRINT));
        fclose($handle);

        $this->info('done.');
    }

    protected function writeModelProviderClassJson(string $providerClass, int $maxDepth)
    {
        if (!class_exists($providerClass)) {
            $this->warn('Provider class ' . $providerClass . ' not found');
            return 1;
        }

        /** @var ICRUDService */
        $crudService = app(ICRUDService::class);

        /** @var ICRUDProvider */
        $provider = $crudService->makeCompositeProvider($providerClass);

        if (is_null($provider)) {
            $this->warn('Provider class ' . $providerClass . ' could not be created');
            return 1;
        }

        $providerNames = [];

        $handle = fopen(storage_path('json/crud/' . class_basename($provider->getModelClass()) . '.json'), 'w');
        fwrite($handle, json_encode($this->getProviderJSON($crudService, $provider, true, 1, $maxDepth, $providerNames), JSON_PRETTY_PRINT));
        fclose($handle);

        $this->info('done.');
    }

    public function getProviderJSON(ICRUDService $service, ICRUDProvider $provider, $detailed, $depth, $maxDepth, &$providerNames)
    {
        $providerNames[] = $provider->getPermissionObjectName();

        $modelClass = $provider->getModelClass();
        /** @var Model */
        $model = new $modelClass();

        $relations = [];
        $columns = [];

        $columnNames = Schema::getColumnListing($model->getTable());
        $sortableColumns = $provider->getValidSortColumns();
        $hiddenColumns = $model->getHidden();
        $avRelations = $provider->getValidRelations();

        if ($depth <= $maxDepth) {
            foreach ($avRelations as $relation => $providerClass) {
                if (is_string($providerClass) && is_string($relation)) {
                    $relationProvider = $service->makeCompositeProvider($providerClass);
                    if (in_array($relationProvider->getPermissionObjectName(), $providerNames)) {
                        $relations[] = [
                            'name' => $relation,
                            'provider' => $relationProvider->getPermissionObjectName(),
                        ];
                    } else {
                        $relatedData = $this->getProviderJSON($service, $relationProvider, false, $depth + 1, $maxDepth, $providerNames);
                        $relatedData['name'] = $relation;
                        $relations[] = $relatedData;
                    }
                } else if (is_numeric($relation) && is_string($providerClass)) {
                    $relations[] = [
                        'name' => $providerClass
                    ];
                } else if (is_string($relation) && is_callable(($providerClass))) {
                    $relations[] = [
                        'name' => $relation
                    ];
                }
            }
        }

        foreach ($columnNames as $column) {
            $columns[] = [
                'name' => $column,
                'sortable' => in_array($column, $sortableColumns),
                'hidden' => in_array($column, $hiddenColumns),
            ];
        }

        $data = [
            'name' => $provider->getPermissionObjectName(),
            'relations' => $relations,
            'columns' => $columns,
        ];

        if ($detailed) {
            $data = array_merge($data, [
                'verbs' => $provider->getPermissionVerbs(),
                'createRules' => $provider->getCreateRules(Request::create('')),
                'updateRules' => $provider->getUpdateRules(Request::create('')),
                'filters' => $provider->getFilterFields(),
            ]);
        }

        return $data;
    }
}
