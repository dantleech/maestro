<?php

namespace Maestro\Task;

use Amp\Promise;
use Maestro\Task\Task;

class TaskRunner
{
    /**
     * @var TaskHandlerRegistry
     */
    private $registry;

    /**
     * @var array
     */
    private $jobMap;

    public function __construct(TaskHandlerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function run(Task $task, Artifacts $artifacts): Promise
    {
        $handler = $this->registry->getFor($task);

        return call_user_func($handler, $task);
    }
}
