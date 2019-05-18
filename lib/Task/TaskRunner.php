<?php

namespace Maestro\Task;

use Amp\Promise;
use Maestro\Task\Exception\InvalidHandler;

final class TaskRunner
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

        return call_user_func($handler, $task);
    }
}
