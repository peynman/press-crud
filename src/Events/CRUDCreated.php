<?php

namespace Larapress\CRUD\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class CreatedEvent.
 */
class CRUDCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Model
     */
    protected $model;

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
