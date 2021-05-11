<?php

namespace Larapress\CRUD\Services\Scribe;

use Knuckles\Scribe\Extracting\ParamHelpers;
use Illuminate\Routing\Route;
use Knuckles\Scribe\Extracting\Strategies\Metadata\GetFromDocBlocks;
use Larapress\CRUD\Services\CRUD\BaseCRUDController;
use Larapress\CRUD\Services\CRUD\ICRUDProvider;
use Larapress\CRUD\Services\RBAC\IPermissionsMetadata;
use Mpociot\Reflection\DocBlock;
use ReflectionClass;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionUnionType;
use Illuminate\Support\Str;

class GetCRUDMetaData extends GetFromDocBlocks
{
    public function __invoke(Route $route, ReflectionClass $controller, ReflectionFunctionAbstract $method, array $routeRules, array $alreadyExtractedData = []): array
    {
        $result = parent::__invoke($route, $controller, $method, $routeRules, $alreadyExtractedData);

        if ($controller->isSubclassOf(BaseCRUDController::class)) {
            $providerClass = $route->getAction('provider');
            if (!is_null($providerClass)) {
                /** @var ICRUDProvider|IPermissionsMetadata */
                $provider = new $providerClass();
                $modelClass = class_basename($provider->getModelClass());

                switch ($method->getName()) {
                    case 'show':
                        $result['description'] .= 'Show details about specific ' . $modelClass . ' resource.';
                        break;
                    case 'query':
                        $result['description'] .= 'Search/Filter results in ' . $modelClass . ' table.';
                        break;
                    case 'store':
                        $result['description'] .= 'Store a new ' . $modelClass . '.';
                        break;
                    case 'update':
                        $result['description'] .= 'Update existing ' . $modelClass . ' based on id.';
                        break;
                    case 'reports':
                        $result['description'] .= 'Get reports about ' . $modelClass . '.';
                        break;
                    case 'destroy':
                        $result['description'] .= 'Soft delete a ' . $modelClass . ' record.';
                        break;
                    case 'export':
                        $result['description'] .= 'Download results on ' . $modelClass . ' query.';
                        break;
                }

                $providerReflect = new ReflectionClass($provider);
                if ($providerReflect->hasProperty('verbs')) {
                    $verbsComments = new DocBlock($providerReflect->getProperty('verbs')->getDocComment());
                    $tags = $verbsComments->getTags();

                    if (isset($route->action['as'])) {
                        $parts = explode(".", $route->action['as']);
                        $verbName = implode(".", array_splice($parts, 1));
                        foreach ($tags as $tag) {
                            if (str_replace('-', '.', $tag->getName()) === $verbName) {
                                $result['description'] .= '</br>' . $tag->getDescription();
                            }
                        }

                        if (!Str::endsWith($verbName, 'any')) {
                            $verbPermission = in_array($verbName, ['index', 'show', 'query', 'view', 'export']) ? 'view' : $verbName;
                            $result['description'] .= '<aside class="notic">requires permission: ' . $provider->getPermissionObjectName() . '.' . $verbPermission . '</aside>';
                        }
                    }
                }

                $crudDesc = (new DocBlock($providerReflect->getDocComment()));
                $result['groupDescription'] = $crudDesc->getText();
            }
        }

        return $result;
    }
}
