<?php

namespace Larapress\CRUD\Events;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

/**
 * Class CreatedEvent
 *
 * @package Larapress\CRUD\Events
 */
class CRUDCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Model $model
     */
    protected $model;

    /**
     * @var Carbon $model
     */
    private $timestamp;

    /**
     * Create a new event instance.
     *
     * @param Model  $model
     * @param Carbon $timestamp
     */
    public function __construct(Model $model, Carbon $timestamp)
    {
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
     * @return Carbon
     */
    public function getTimestamp() : Carbon
    {
        return $this->timestamp;
    }


    public function getModel(): Model
    {
        return $this->model;
    }
}
