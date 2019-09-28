<?php

namespace Maestro\Library\Task\TaskRunner;

use Amp\Promise;
use Maestro\Library\Instantiator\Instantiator;
use Maestro\Library\Task\Artifacts;
use Maestro\Library\Task\Task;
use Maestro\Library\Task\TaskHandlerRegistry;
use Maestro\Library\Task\TaskRunner;

class InvokingTaskRunner implements TaskRunner
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
