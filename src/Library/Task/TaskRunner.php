<?php

namespace Maestro\Library\Task;

use Amp\Promise;
use Maestro\Library\GraphTask\Artifacts;

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

    public function run(Task $task, Artifacts $artifacts): Promise
    {
        return $this->registry->getHandlerFor($task)->handle($task);
    }
}
