<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

use Illuminate\Support\Facades\Broadcast;
use Larapress\CRUD\ICRUDUser;
use Larapress\CRUD\Services\IBaseCRUDBroadcast;

// general crud permissions
Broadcast::channel('crud.{name}.{verb}', function (ICRUDUser $user, $name, $verb) {
    /** @var IBaseCRUDBroadcast */
    $service = app(IBaseCRUDBroadcast::class);
    return $service->authorizeForCRUDChannel($user, $name, $verb);
}, ['guards' => ['web', 'api']]);

// general crud permissions or
Broadcast::channel('crud.{name}.{verb}.${id}', function (ICRUDUser $user, $name, $verb, $uid) {
    /** @var IBaseCRUDBroadcast */
    $service = app(IBaseCRUDBroadcast::class);
    return $service->authorizeForCRUDSupportChannel($user, $name, $verb, $uid);
}, ['guards' => ['web', 'api']]);
