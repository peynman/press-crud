<?php

namespace Larapress\CRUD\Events;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Larapress\CRUD\Services\CRUD\ICRUDVerb;

/**
 * Class CreatedEvent.
 */
class CRUDCreated extends CRUDVerbEvent
{
    public function __construct($user, Model $model, string $providerClass, Carbon $timestamp)
    {
        parent::__construct($user, $model, $providerClass, $timestamp, ICRUDVerb::CREATE);
    }
}
