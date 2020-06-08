<?php

namespace Larapress\CRUD\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Larapress\CRUD\Base\ICRUDProvider;

class CRUDUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Model
     */
    private $model;

    /**
     * @var Carbon
     */
    private $timestamp;

    /**
     * @var string
     */
    private $providerClass;

    /**
     * Create a new event instance.
     *
     * @param Model $model
     * @param string $providerClass
     * @param Carbon $timestamp
     */
    public function __construct(Model $model, string $providerClass, Carbon $timestamp)
    {
        //
        $this->model = $model;
        $this->timestamp = $timestamp;
        $this->providerClass = $providerClass;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('crud.'.$this->providerClass.'.updated');
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @return Carbon
     */
    public function getTimestamp() : Carbon
    {
        return $this->timestamp;
    }

    /**
     * @return \Larapress\CRUD\Base\ICRUDProvider
     */
    public function getProvider(): ICRUDProvider
    {
        return new $this->providerClass;
    }
}
