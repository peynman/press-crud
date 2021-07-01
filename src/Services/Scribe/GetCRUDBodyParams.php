<?php

namespace Larapress\CRUD\Services\Scribe;

use Carbon\Carbon;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Request;
use Knuckles\Scribe\Extracting\Strategies\BodyParameters\GetFromFormRequest;
use Larapress\CRUD\Services\CRUD\CRUDController;
use Larapress\CRUD\Services\CRUD\ICRUDProvider;
use Mpociot\Reflection\DocBlock;
use ReflectionClass;
use ReflectionFunctionAbstract;

class GetCRUDBodyParams extends GetFromFormRequest
{
    public function __invoke(Route $route, ReflectionClass $controller, ReflectionFunctionAbstract $method, array $routeRules, array $alreadyExtractedData = []): array
    {
        $parameters = parent::__invoke($route, $controller, $method, $routeRules, $alreadyExtractedData);

        if ($controller->isSubclassOf(CRUDController::class)) {
            $providerClass = $route->getAction('provider');
            if (!is_null($providerClass)) {
                /** @var ICRUDProvider */
                $provider = new $providerClass();
                $modelClass = class_basename($provider->getModelClass());
                switch ($method->getName()) {
                    case 'show':
                        break;
                    case 'query':
                        $parameters = array_merge($parameters, $this->getFilterParams($provider));
                        $sortable = implode(', ', array_map(function ($item) {
                            return '<small class="badge badge-grey">'.$item.'</small>';
                        }, $provider->getValidSortColumns()));
                        $parameters[] = [
                            'name' => 'sort',
                            'type' => 'object[]',
                            'value' => '',
                            'description' => 'Available sort columns: '.$sortable,
                        ];

                        $attachable = implode(', ', array_map(function ($item) {
                            return '<small class="badge badge-grey">'.$item.'</small>';
                        }, $provider->getValidRelations()));
                        $parameters[] = [
                            'name' => 'with',
                            'type' => 'object[]',
                            'value' => '',
                            'description' => 'Available relation names: '.$attachable,
                        ];
                        break;
                    case 'store':
                        $parameters = array_merge($parameters, $this->getBodyParametersFromCRUDVerb(
                            $provider,
                            $provider->getCreateRules(Request::instance()),
                            'getCreateRules',
                            'createValidations'
                        ));
                        break;
                    case 'update':
                        $parameters = array_merge($parameters, $this->getBodyParametersFromCRUDVerb(
                            $provider,
                            $provider->getUpdateRules(Request::instance()),
                            'getUpdateRules',
                            'updateValidations'
                        ));
                        break;
                    case 'reports':
                        break;
                    case 'export':
                        break;
                }
            }
        }

        return $parameters;
    }

    protected function getBodyParametersFromCRUDVerb(ICRUDProvider $provider, $rules, $method, $property)
    {
        $parameters = [];
        $provReflection = new ReflectionClass($provider);
        $auto = $this->getBodyParametersFromValidationRules($rules);
        $reflected = $provReflection->getMethod($method);
        $comment = $reflected->getDocComment();
        $block = new DocBlock($comment);
        $docblock = $this->getBodyParametersFromDocBlock($block->getTags());

        if ($provReflection->hasProperty($property)) {
            $reflected = $provReflection->getProperty($property);
            $comment = $reflected->getDocComment();
            $block = new DocBlock($comment);
            $docblock = array_merge($docblock, $this->getBodyParametersFromDocBlock($block->getTags()));
        }
        $parameters = array_merge($parameters, $auto);
        if (!is_null($docblock)) {
            $parameters = array_merge($parameters, $docblock);
        }

        return $parameters;
    }

    protected function getFilterParams(ICRUDProvider $provider)
    {
        $parameters = [];
        $avFilters = $provider->getFilterFields();
        foreach ($avFilters as $filter => $filterRule) {
            if ($filter === 'relations' || !is_string($filterRule)) {
                continue;
            }

            $rules = explode(":", $filterRule);
            $type = 'string';
            $desc = '';
            $value = self::$MISSING_VALUE;
            switch ($rules[0]) {
                case 'after':
                case 'before':
                    $type = 'datetime';
                    $value = Carbon::now()->format(config('larapress.crud.datetime-format'));
                    $desc = 'DateTime string with format ' . config('larapress.crud.datetime-format') . '.';
                    break;
                case 'has':
                case 'has-has':
                case 'hasnot-has':
                    $type = 'object[]';
                    $desc = 'List of Object to apply filter rule ' . $rules[0] . '.';
                    $parameters['filters.' . $filter . '[].id'] = [
                        'name' => 'filters.' . $filter . '[].id',
                        'required' => true,
                        'type' => 'int',
                        'value' => 1,
                        'description' => 'The id of the object to apply filter rule ' . $rules[0] . '.',
                    ];
                    break;
                case 'bitwise':
                    $value = '1';
                    $type = 'int';
                    $desc = 'An integer to check as bitwise operator &';
                    break;
                case 'not-null':
                    $desc = 'check if value is not null';
                    break;
                case 'null':
                    $desc = 'check if value is null';
                    break;
                case 'equals':
                    $value = 'something';
                    $desc = 'The string or integer is equal to filter';
                    break;
                case 'like':
                    $value = 'something';
                    $desc = 'The string or integer is like (%SOMETHING%)';
                    break;
                case '>=':
                case '>':
                case '<':
                case '<=':
                    $value = '300';
                    break;
                case 'in':
                    $type = 'string[]';
                    break;
            }

            $providerReflect = new ReflectionClass($provider);
            if ($providerReflect->hasProperty('filterFields')) {
                $filterComments = new DocBlock($providerReflect->getProperty('filterFields')->getDocComment());
                $tags = $filterComments->getTags();
                foreach ($tags as $tag) {
                    if ($tag->getName() === 'bodyParam') {
                        $content = explode(' ', $tag->getContent());
                        if ($content[0] === $filter) {
                            $type = $content[1];
                            $desc = implode(' ', array_splice($content, 2)).'</br>'.$desc;
                        }
                    }
                }
            }


            $parameters['filters.' . $filter] = [
                'name' => $filter,
                'required' => false,
                'type' => $type,
                'value' => $value,
                'description' => $desc,
            ];
        }
        return $parameters;
    }

    public function getBodyParametersFromDocBlock($tags)
    {
        $parameters = [];

        foreach ($tags as $tag) {
            if ($tag->getName() !== 'bodyParam') continue;

            $tagContent = trim($tag->getContent());
            // Format:
            // @bodyParam <name> <type> <"required" (optional)> <description>
            // Examples:
            // @bodyParam text string required The text.
            // @bodyParam user_id integer The ID of the user.
            preg_match('/(.+?)\s+(.+?)\s+(required\s+)?([\s\S]*)/', $tagContent, $content);
            if (empty($content)) {
                // this means only name and type were supplied
                [$name, $type] = preg_split('/\s+/', $tagContent);
                $required = false;
                $description = '';
            } else {
                [$_, $name, $type, $required, $description] = $content;
                $description = trim(str_replace(['No-example.', 'No-example'], '', $description));
                if ($description == 'required') {
                    $required = $description;
                    $description = '';
                }
                $required = trim($required) === 'required';
            }

            $type = $this->normalizeTypeName($type);
            [$description, $example] = $this->parseExampleFromParamDescription($description, $type);
            $value = is_null($example) && !$this->shouldExcludeExample($tagContent)
                ? $this->generateDummyValue($type)
                : $example;

            $parameters[$name] = compact('name', 'type', 'description', 'required', 'value');
        }

        return $parameters;
    }
}
