<?php

return [
    'user' => [
        'class' => App\Models\User::class,
        'crud-provider' => \Larapress\Profiles\CRUD\UserCRUDProvider::class,
    ],

    'permissions' => [
        \Larapress\CRUD\CRUD\RoleCRUDProvider::class,
    ],
    'controllers' => [
        \Larapress\CRUD\CRUDControllers\RoleController::class,
    ],
    'middlewares' => [
        'api',
        'auth:api',
        \Larapress\CRUD\Middleware\CRUDAuthorizeRequest::class,
    ],
    'broadcast-middlewares' => [
        'api',
        'auth:api',
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
