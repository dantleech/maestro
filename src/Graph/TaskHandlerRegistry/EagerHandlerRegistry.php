<?php

namespace Maestro\Graph\TaskHandlerRegistry;

use Maestro\Graph\Exception\HandlerNotFound;
use Maestro\Graph\Task;
use Maestro\Graph\TaskHandler;
use Maestro\Graph\TaskHandlerRegistry;

class EagerHandlerRegistry implements TaskHandlerRegistry
{
    /**
     * @var array
     */
    private $handlers = [];

    public function __construct(array $handlers)
    {
        foreach ($handlers as $taskFqn => $handler) {
            $this->add($taskFqn, $handler);
        }
    }

    public function getFor(Task $task): TaskHandler
    {
        $taskFqn = get_class($task);
        if (!isset($this->handlers[$taskFqn])) {
            throw new HandlerNotFound(sprintf(
                'Handler for "%s" not registered, handlers are registered for: "%s"',
                $taskFqn,
                implode('", "', array_keys($this->handlers))
            ));
        }

        return $this->handlers[$taskFqn];
    }

    private function add(string $taskFqn, TaskHandler $handler)
    {
        $this->handlers[$taskFqn] = $handler;
    }
}
