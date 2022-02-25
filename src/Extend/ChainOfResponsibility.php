<?php

namespace Larapress\CRUD\Extend;

class ChainOfResponsibility {
    private $head;

    public function __construct(private array $queue) {
        $this->head = 0;
    }

    /**
     * Undocumented function
     *
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     */
    public function handle($method, ...$args) {
        $task = $this->dequeue();
        return $task->${$method}(...$args, function (...$args) use($method) {
            $this->handle($method, ...$args);
        });
    }

    private function dequeue() {
        if ($this->head < count($this->queue)) {
            $this->head++;
            return $this->queue[$this->head - 1];
        }

        return null;
    }
}
