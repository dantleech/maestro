<?php

namespace Maestro\Library\Task;

use Amp\Promise;

class TaskRunner
{
    /**
     * @var TaskHandlerRegistry
     */
    private $registry;

    public function __construct(TaskHandlerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function run(Task $task): Promise
    {
        return $this->registry->getHandlerFor($task)->handle($task);
    }
}
