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
use Larapress\CRUD\Services\CRUD\ICRUDBroadcast;

// general crud permissions channel for super-user
Broadcast::channel('crud.{name}.{verb}', function (ICRUDUser $user, $name, $verb) {
    /** @var ICRUDBroadcast */
    $service = app(ICRUDBroadcast::class);
    return $service->authorizeForCRUDChannel($user, $name, $verb);
}, ['guards' => ['web', 'api']]);

// general crud permissions channel for affiliates
Broadcast::channel('crud.{name}.{verb}.${id}', function (ICRUDUser $user, $name, $verb, $uid) {
    /** @var ICRUDBroadcast */
    $service = app(ICRUDBroadcast::class);
    return $service->authorizeForCRUDSupportChannel($user, $name, $verb, $uid);
}, ['guards' => ['web', 'api']]);
