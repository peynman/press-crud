<?php

return [
    'user' => [
        'class' => App\Models\User::class,
    ],
    'events' => [
        'channel' => 'larapress-crud',
    ],

    'permissions' => [
        \Larapress\Profiles\MetaData\RoleMetaData::class,
    ],
];
