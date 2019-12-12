<?php

namespace Larapress\CRUD\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CRUDQueried
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var array|Model[]
     */
    private $models;

    /**
     * Create a new event instance.
     *
     * @param array|Model[] $models
     * @param Carbon        $timestamp
     */
    public function __construct(array $models, Carbon $timestamp)
    {
        $this->models = $models;
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
     * @return array|Model[]
     */
    public function getModels(): array
    {
        return $this->models;
    }
}
