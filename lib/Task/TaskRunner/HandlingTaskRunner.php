<?php

namespace Maestro\Task\TaskRunner;

use Amp\Promise;
use Maestro\Task\Artifacts;
use Maestro\Task\Exception\InvalidHandler;
use Maestro\Task\Task;
use Maestro\Task\TaskHandlerRegistry;
use Maestro\Task\TaskRunner;

final class HandlingTaskRunner implements TaskRunner
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

        if (!is_callable($handler)) {
            throw new InvalidHandler(sprintf(
                'Handler "%s" is not __invoke-able',
                get_class($handler)
            ));
        }

        return call_user_func($handler, $task, $artifacts);
    }
}
