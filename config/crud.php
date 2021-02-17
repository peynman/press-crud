<?php

return [
    /**
     * User model definition
     * User CRUD provider and a master password if you need one
     */
    'user' => [
        'class' => App\Models\User::class,
        'crud-provider' => \Larapress\Profiles\CRUD\UserCRUDProvider::class,
        'master_customer_password' => env('MASTER_CUSTOMER_PASSWORD', null),
    ],

    /**
     * All CRUDProviders to be loaded
     * this dictates the permissions available
     */
    'permissions' => [
        \Larapress\CRUD\CRUD\RoleCRUDProvider::class,
        'larapress.reports.permissions',
        'larapress.notifications.permissions',
        'larapress.profiles.permissions',
        'larapress.ecommerce.permissions',
        'larapress.pages.permissions',
    ],

    /**
     * All CRUD Controllers to register
     */
    'controllers' => [
        \Larapress\CRUD\CRUDControllers\RoleController::class,
        'larapress.reports.controllers',
        'larapress.notifications.controllers',
        'larapress.profiles.controllers',
        'larapress.ecommerce.controllers',
        'larapress.pages.controllers',
    ],

    /**
     * Middlewares for CRUD routes
     */
    'middlewares' => [
        'api',
        'auth:api',
        \Larapress\CRUD\Middleware\CRUDAuthorizeRequest::class,
    ],

    /**
     * Middlewares for CRUD Broadcast routes
     */
    'broadcast-middlewares' => [
        'api',
        'auth:api',
    ],

    /**
     * CRUD Session management
     */
    'session' => [
        'connection' => 'default'
    ],

    /**
     * Customize Larapress-CRUD routes
     */
    'routes' => [
        'roles' => [
            'name' => 'roles',
        ],
    ],

    /**
     * Add prefix to all CRUD routes
     */
    'prefix' => 'api',

    /**
     * Queue CRUD related jobs (Events/Listeners) on a specific queue
     */
    'queue' => 'jobs',

    /**
     * DateTime format used to parse or present Dates
     */
    'datetime-format' => 'Y-m-d\TH:i:sO',

    /**
     * Available languages for CRUD API
     */
    'languages' => [
        'en' => \Larapress\CRUD\Translation\Lang\Roman::class,
        'fa' => \Larapress\CRUD\Translation\Lang\Persian::class,
    ],
];
