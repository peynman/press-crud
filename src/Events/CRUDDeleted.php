<?php

namespace Larapress\CRUD\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * Class CRUDDeleted
 *
 * @package Larapress\CRUD\Events
 */
class CRUDDeleted
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
     * Create a new event instance.
     *
     * @param Model  $model
     * @param Carbon $timestamp
     */
    public function __construct(Model $model, $timestamp)
    {
        //
        $this->model = $model;
        $this->timestamp = $timestamp;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel(config('larapress.curd.events.channel'));
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
    public function getTimestamp(): Carbon
    {
        return $this->timestamp;
    }
}
