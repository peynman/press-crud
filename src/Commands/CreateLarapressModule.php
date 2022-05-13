<?php

namespace Larapress\CRUD\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateLarapressModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lp:crud:create-module {--name=} {--path=} {--migrations=} {--routes=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new Larapress module.';

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
            'name:package name:' => $this->option('name', null),
            'path:relative path:' => $this->option('path', null),
            'migrations:has migrations?(1,0):' => $this->option('migrations', null),
            'routes:has routes?(1,0):' => $this->option('routes', null),
        ];
        $form = $this->fillForm($form);
        $camelName = Str::camel($form['name']);
        $kebabName = Str::kebab($form['name']);
        $fullName = ucwords($form['name']);

        $emptyPHPObject = "<?php\n\nreturn [\n];\n";
        $emptyPHP = "<?php\n\n";
        $emptyServiceProvider = function($name) use($fullName) {
            return "<?php

namespace Larapress\\$fullName\Providers;

use Illuminate\Support\ServiceProvider;

class PackageServiceProvider extends ServiceProvider {
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        \$this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'larapress');
    }
}
";
        };


        $structure = [
            'config' => [
                "$camelName.php" => $emptyPHPObject,
            ],
            'migrations' => $form['migrations'] ? [] : null,
            'resources' => [
                'lang' => [
                    'fa' => [
                        "$camelName.php" => $emptyPHPObject,
                    ],
                    'en' => [
                        "$camelName.php" => $emptyPHPObject,
                    ],
                ],
            ],
            'routes' => $form['routes'] ? [
                'api.php' => $emptyPHP,
            ] : null,
            'src' => [
                'Services' => [],
                'Providers' => [
                    'PackageServiceProvider.php' => $emptyServiceProvider($form['name']),
                ],
            ],
            'tests' => [
                'Feature' => [
                    "${fullName}ServiceTest.php" => '',
                ],
            ],
            'CHANGELOG.md' => '',
            '.gitignore' => function () {
                return file_get_contents('./packages/larapress-crud/.gitignore');
            },
            'README.md' => function () {
                return file_get_contents('./packages/larapress-crud/README.md');
            },
            'LICENSE' => function () {
                return file_get_contents('./packages/larapress-crud/LICENSE');
            },
            'composer.json' => function () {
                return file_get_contents('./packages/larapress-crud/composer.json');
            },
        ];

        $this->makeStructure($form['path'], $structure);

        return 0;
    }

    protected function makeStructure ($path, $structure) {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        foreach ($structure as $key => $value) {
            $fullpath = $path.DIRECTORY_SEPARATOR.$key;
            if (is_string($value)) {
                $this->info('Create file: '.$fullpath);
                file_put_contents($fullpath, $value);
            } else if (is_array($value)) {
                $this->info('Create directory: '.$fullpath);
                $this->makeStructure($fullpath, $value);
            }
        }
    }

    protected function fillForm($form) {
        $vals = [];
        foreach ($form as $k => $value) {
            $keyM = explode(':', $k);
            $key = $keyM[0];
            if (is_null($value)) {
                $vals[$key] = $this->ask(count($keyM) === 1 ? 'Please enter '.$key.':' : $keyM[1]) ?? 0;
            } else {
                if (is_callable($value)) {
                    $vals[$key] = $value($vals);
                } else {
                    $vals[$key] = $value;
                }
            }
        }

        return $vals;
    }
}
