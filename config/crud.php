<?php

use Larapress\CRUD\Middleware\CRUDAuthorizeRequest;

return [
    'user' => [
        'class' => App\Models\User::class,
    ],
    'events' => [
        'channel' => 'larapress-crud',
    ],

    'permissions' => [
        \Larapress\CRUD\MetaData\RoleMetaData::class,
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
];
