<?php

namespace Larapress\CRUD\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Larapress\CRUD\Services\CRUD\ICRUDProvider;
use Larapress\CRUD\Services\RBAC\IPermissionsMetadata;
use Larapress\CRUD\ICRUDUser;
use Larapress\CRUD\Services\CRUD\ICRUDService;

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
    public function __construct($user, $model, string $providerClass, Carbon $timestamp, string $verb)
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
        /** @var IPermissionsMetadata */
        $provider = $this->getProvider();
        return new PrivateChannel('crud.'.$provider->getPermissionObjectName().'.'.$this->verb);
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
     * @return ICRUDUser
     */
    public function getUser()
    {
        if (is_null($this->userId)) {
            return null;
        }

        return call_user_func([config('larapress.crud.user.model'), "find"], $this->userId);
    }

    /**
     * @return \Larapress\CRUD\Services\CRUD\ICRUDProvider|IPermissionsMetadata
     */
    public function getProvider(): ICRUDProvider
    {
        /** @var ICRUDService */
        $crudService = app(ICRUDService::class);
        $providerClass = new $this->providerClass;
        $provider = new $providerClass;
        $crudService->useProvider($provider);
        return $crudService->getCompositeProvider();
    }
}
