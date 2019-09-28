<?php

namespace Maestro\Library\Task;

use Amp\Promise;
use Maestro\Library\Task\Artifacts;
use Maestro\Library\Instantiator\Instantiator;

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
        return Instantiator::call(
            $this->registry->getHandlerFor($task),
            '__invoke',
            array_merge([
                $task
            ], $artifacts->toArray()),
            Instantiator::MODE_TYPE
        );
    }
}
