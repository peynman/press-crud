<?php

use Larapress\CRUD\Middleware\CRUDAuthorizeRequest;

return [
    'user' => [
        'class' => App\Models\User::class,
    ],

    'permissions' => [
        \Larapress\CRUD\CRUD\RoleCRUDProvider::class,
    ],
    'controllers' => [
        \Larapress\CRUD\CRUDControllers\RoleController::class,
    ],
    'middlewares' => [
        'auth:api',
        CRUDAuthorizeRequest::class,
    ],

    'session' => [
        'connection' => 'default'
    ],

    'routes' => [
        'roles' => [
            'name' => 'roles',
        ],
    ],

    'prefix' => 'api',

    'queue' => 'jobs',

    'datetime-format' => 'Y-m-d\TH:i:sO',

    'languages' => [
        'en' => \Larapress\CRUD\Translation\Lang\Roman::class,
        'fa' => \Larapress\CRUD\Translation\Lang\Persian::class,
    ],
];
