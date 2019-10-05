<?php

namespace Maestro\Library\Task;

use Maestro\Library\Task\Exception\TaskHandlerNotFound;

class TaskHandlerRegistry
{
    /**
     * @var array
     */
    private $handlerMap = [];

    public function __construct(array $handlerMap)
    {
        $this->handlerMap = $handlerMap;
    }

    public function getHandlerFor(Task $task)
    {
        $fqn = get_class($task);
        if (!isset($this->handlerMap[$fqn])) {
            throw new TaskHandlerNotFound(sprintf(
                'Task handler for task "%s" not registered, we can handle the following tasks "%s"',
                $fqn,
                implode('", "', array_keys($this->handlerMap))
            ));
        }

        return $this->handlerMap[$fqn];
    }
}
