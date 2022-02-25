<?php

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
            // \Larapress\ECommerce\Compositions\UserBalanceComposition::class,
            // \Larapress\LCMS\Services\SupportGroup\Compositions\UserComposition::class,
        ],
    ],

    // named permissions used for system wide services
    'app_permissions' => [
        // 'horizon',
        // 'log-viewer',
        // 'telescope',
    ],

    // available verbs for crud resources
    'verbs' => [
        \Larapress\CRUD\Services\CRUD\ICRUDVerb::VIEW => \Larapress\CRUD\Services\CRUD\Verbs\Query\Query::class,
        \Larapress\CRUD\Services\CRUD\ICRUDVerb::EDIT => \Larapress\CRUD\Services\CRUD\Verbs\Update::class,
        \Larapress\CRUD\Services\CRUD\ICRUDVerb::CREATE => \Larapress\CRUD\Services\CRUD\Verbs\Store::class,
        \Larapress\CRUD\Services\CRUD\ICRUDVerb::SHOW => \Larapress\CRUD\Services\CRUD\Verbs\Show::class,
        \Larapress\CRUD\Services\CRUD\ICRUDVerb::EXPORT => \Larapress\CRUD\Services\CRUD\Verbs\Export::class,
        \Larapress\CRUD\Services\CRUD\ICRUDVerb::DELETE => \Larapress\CRUD\Services\CRUD\Verbs\Destroy::class,
        // \Larapress\Reports\Services\Reports\ReportsVerb::REPORTS => \Larapress\Reports\Services\Reports\ReportsVerb::class,
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
     * Middlewares for protected web routes
     */
    'web-middlewares' => [
        'web',
        'auth:web',
        \Larapress\CRUD\Middleware\CRUDAuthorizeRequest::class,
    ],

    /**
     * Middlewares for public CRUD routes
     */
    'public-middlewares' => [
        'api',
        'web',
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
    'datetime-format' => 'Y-m-d\TH:i:sP',

    // default repository retrieve limits
    'repository' => [
        'limit' => 50,
        'max_limit' => 200,
        'min_limit' => 5,
    ],

    /**
     * Available languages for CRUD API
     */
    'languages' => [
        'en' => \Larapress\CRUD\Translation\Lang\Roman::class,
        // 'fa' => \Larapress\CRUD\Translation\Lang\Persian::class,
    ],

    // default repository retrieve limits
    'repository' => [
        'limit' => 50,
        'max_limit' => 200,
        'min_limit' => 5,
    ],

    /** safe sources for client to ask with "ServerSources" page property */
    'safe-sources' => [
        \Larapress\CRUD\Repository\IPermissionsRepository::class,
        \Larapress\CRUD\Repository\IRoleRepository::class,
        // \Larapress\Profiles\Repository\PhoneNumber\IPhoneNumberRepository::class,
        // \Larapress\Profiles\Repository\Domain\IDomainRepository::class,
        // \Larapress\Profiles\Repository\Form\IFormRepository::class,
        // \Larapress\Profiles\Repository\Filter\IFilterRepository::class,
        // \Larapress\ECommerce\Services\Banking\IBankGatewayRepository::class,
        // \Larapress\ECommerce\Services\Product\IProductRepository::class,
        // \Larapress\ECommerce\Services\Cart\ICartRepository::class,
        // \Larapress\Pages\Repository\IPageRepository::class,
        // \Larapress\Notifications\Services\SMSService\ISMSGatewayRepository::class,
        // \Larapress\LCMS\Services\CourseSession\ICourseSessionRepository::class,
        // \Larapress\ECommerce\Services\Wallet\IWalletTransactionRepository::class,
        // \Larapress\Notifications\Services\Notifications\INotificationsRepository::class,
        // \Larapress\Chat\Services\Chat\IChatRepository::class,
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
        // 'include::larapress.fileshare.permissions',
        // 'include::larapress.chat.permissions',
    ],

];
