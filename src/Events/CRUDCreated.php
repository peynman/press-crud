<?php

namespace Larapress\CRUD\Events;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Larapress\CRUD\Services\RBAC\IPermissionsMetadata;

/**
 * Class CreatedEvent.
 */
class CRUDCreated extends CRUDVerbEvent
{
    public function __construct($user, Model $model, string $providerClass, Carbon $timestamp)
    {
        parent::__construct($user, $model, $providerClass, $timestamp, IPermissionsMetadata::CREATE);
    }
}
