<?php

namespace Larapress\CRUD\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Larapress\CRUD\Services\ICRUDProvider;
use Larapress\CRUD\Services\IPermissionsMetadata;

/**
 * Class CreatedEvent.
 */
class CRUDVerbEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Model
     */
    public $model;

    /**
     * @var Carbon
     */
    public $timestamp;

    /**
     * @var string
     */
    public $providerClass;

    /** @var array */
    public $data;

    /** @var string */
    public $verb;

    /** @var mixed */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param Model $model
     * @param string $providerClass
     * @param Carbon $timestamp
     */
    public function __construct($user, Model $model, string $providerClass, Carbon $timestamp, $verb)
    {
        $this->user = $user;
        $this->model = $model;
        $this->timestamp = $timestamp;
        $this->providerClass = $providerClass;
        $this->verb = $verb;
        $this->data = [
            'model' => $model->toArray(),
            'timestamp' => $timestamp,
            'provider' => $providerClass,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('crud.'.$this->getProvider()->getPermissionObjectName().'.'.$this->verb);
    }

    /**
     * @return Carbon
     */
    public function getTimestamp() : Carbon
    {
        return $this->timestamp;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @return \Larapress\CRUD\Services\ICRUDProvider|IPermissionsMetadata
     */
    public function getProvider(): ICRUDProvider
    {
        $class = $this->data['provider'];
        return new $class;
    }
}
