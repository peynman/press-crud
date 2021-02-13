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
use Larapress\Profiles\IProfileUser;

/**
 * Class CreatedEvent.
 */
class CRUDVerbEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var int
     */
    public $modelId;

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

    /** @var int */
    public $userId;

    /**
     * Create a new event instance.
     *
     * @param Model $model
     * @param string $providerClass
     * @param Carbon $timestamp
     */
    public function __construct($user, $model, string $providerClass, Carbon $timestamp, $verb)
    {
        if (is_null($user)) {
            $this->userId = null;
        } else {
            $this->userId = is_numeric($user) ? $user : $user->id;
        }
        $this->modelId = $model->id;
        $this->timestamp = $timestamp;
        $this->providerClass = $providerClass;
        $this->verb = $verb;

        $model->setAppends([]);
        $snapshot = $model->toArray();
        $this->data = [
            'model' => $snapshot,
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
        return call_user_func([$this->getProvider()->getModelClass(), "find"], $this->modelId);
    }

    /**
     * Undocumented function
     *
     * @return IProfileUser
     */
    public function getUser()
    {
        if (is_null($this->userId)) {
            return null;
        }

        return call_user_func([config('larapress.crud.user.class'), "find"], $this->userId);
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
