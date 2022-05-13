<?php

namespace Larapress\CRUD\Extend;

class ChainOfResponsibility
{
    private $head;

    public function __construct(private array $queue)
    {
        $this->head = 0;
    }

    /**
     * Undocumented function
     *
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     */
    public function handle(string $method, ...$args)
    {
        $task = $this->dequeue();
        if (is_string($task)) {
            return null;
        }

        return $task->{$method}(function (...$args) use ($method) {
            $this->handle($method, ...$args);
        }, ...$args);
}

    /**
     * Undocumented function
     *
     * @param string $method
     * @param callable $aggregator
     * @param mixed ...$args
     *
     * @return mixed
     */
    public function aggregate(string $method, callable $aggregator, ...$args)
    {
        $task = $this->dequeue();
        if (is_null($task)) {
            return $aggregator(...$args);
        }

        return $task->{$method}(function (...$args) use ($method, $aggregator) {
            return $this->aggregate($method, $aggregator, ...$args);
        }, ...$args);
    }

    private function dequeue()
    {
        if ($this->head < count($this->queue)) {
            $this->head++;
            return $this->queue[$this->head - 1];
        }

        return null;
    }
}
