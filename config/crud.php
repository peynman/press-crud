<?php

use Larapress\CRUD\Middleware\CRUDAuthorizeRequest;

return [
    'user' => [
        'class' => App\Models\User::class,
    ],
    'permissions' => [
        \Larapress\CRUD\Metadata\RoleMetadata::class,
    ],
    'controllers' => [
        \Larapress\CRUD\CRUDControllers\RoleController::class,
    ],

    'JSONCRUDRenderOnJsonContentType' => [
        'auth:api',
        CRUDAuthorizeRequest::class,
    ],

    'routes' => [
        'roles' => [
            'name' => 'roles',
        ],
    ],

    'prefix' => 'api',

    'languages' => [
        'en' => \Larapress\CRUD\Translation\Lang\Roman::class,
        'fa' => \Larapress\CRUD\Translation\Lang\Persian::class,
    ],
];
