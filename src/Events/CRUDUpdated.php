<?php

namespace Larapress\CRUD\Events;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Larapress\CRUD\Services\IPermissionsMetadata;

/**
 * Class CreatedEvent.
 */
class CRUDUpdated extends CRUDVerbEvent
{
    public function __construct(Model $model, string $providerClass, Carbon $timestamp)
    {
        parent::__construct($model, $providerClass, $timestamp, IPermissionsMetadata::EDIT);
    }
}
