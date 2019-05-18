<?php

namespace Maestro\Task\HandlerRegistry;

use Maestro\Task\Exception\HandlerNotFound;
use Maestro\Task\Task;
use Maestro\Task\TaskHandler;
use Maestro\Task\TaskHandlerRegistry;

class EagerHandlerRegistry implements TaskHandlerRegistry
{
    /**
     * @var array
     */
    private $handlers = [];

    public function __construct(array $handlers)
    {
        foreach ($handlers as $handler) {
            $this->add($handler);
        }
    }

    public function getFor(Task $task): TaskHandler
    {
        if (!isset($this->handlers[$task->handler()])) {
            throw new HandlerNotFound(sprintf(
                'Handler "%s" not known, known handlers: "%s"',
                $task->handler(),
                implode('", "', array_keys($this->handlers))
            ));
        }

        return $this->handlers[$task->handler()];
    }

    private function add(TaskHandler $handler)
    {
        $this->handlers[get_class($handler)] = $handler;
    }
}
