<?php

use ;

return [
    /**
     * User model class
     * User CRUD provider
     */
    'user' => [
        'model' => App\Models\User::class,
        'provider' => \Larapress\Profiles\CRUD\UserCRUDProvider::class,
        'compositions' => [
            // \Larapress\Auth\Compositions\UserAuthComposition::class,
        ],
    ],

    // named permissions used for system wide services
    'app_permissions' => [
        'horizon',
        'log-viewer',
        'telescope',
    ],

    // available verbs for crud resources
    'verbs' => [
        \Larapress\CRUD\Services\CRUD\ICRUDVerb::VIEW => \Larapress\CRUD\Services\CRUD\Verbs\Query\Query::class,
        \Larapress\CRUD\Services\CRUD\ICRUDVerb::EDIT => \Larapress\CRUD\Services\CRUD\Verbs\Update::class,
        \Larapress\CRUD\Services\CRUD\ICRUDVerb::CREATE => \Larapress\CRUD\Services\CRUD\Verbs\Store::class,
        \Larapress\CRUD\Services\CRUD\ICRUDVerb::SHOW => \Larapress\CRUD\Services\CRUD\Verbs\Show::class,
        \Larapress\CRUD\Services\CRUD\ICRUDVerb::EXPORT => \Larapress\CRUD\Services\CRUD\Verbs\Export::class,
        \Larapress\CRUD\Services\CRUD\ICRUDVerb::DELETE => \Larapress\CRUD\Services\CRUD\Verbs\Destroy::class,
        \Larapress\CRUD\Services\CRUD\ICRUDVerb::REPORTS => \Larapress\CRUD\Services\CRUD\Verbs\Reports::class,
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


    /**
     * crud resources of the package
     */
    'routes' => [
        'roles' => [
            'name' => 'roles',
            'model' => \Larapress\CRUD\Models\Role::class,
            'provider' => \Larapress\CRUD\CRUD\RoleCRUDProvider::class,
        ],
    ],

    /**
     * All CRUD Controllers to register
     */
    'controllers' => [
        \Larapress\CRUD\Controllers\RoleController::class,
        // 'include::larapress.reports.controllers',
        // 'include::larapress.notifications.controllers',
        // 'include::larapress.profiles.controllers',
        // 'include::larapress.ecommerce.controllers',
        // 'include::larapress.pages.controllers',
    ],

    /**
     * All CRUDProviders to be loaded
     * this dictates the permissions available
     */
    'permissions' => [
        \Larapress\CRUD\CRUD\RoleCRUDProvider::class,
        \Larapress\CRUD\CRUD\SystemAppPermissions::class,
        // 'include::larapress.reports.permissions',
        // 'include::larapress.notifications.permissions',
        // 'include::larapress.profiles.permissions',
        // 'include::larapress.ecommerce.permissions',
        // 'include::larapress.pages.permissions',
    ],

];
