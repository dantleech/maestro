<?php

namespace Maestro\Node\TaskRunner;

use Amp\Promise;
use Maestro\Node\Exception\InvalidHandler;
use Maestro\Node\Exception\InvalidHandlerResponse;
use Maestro\Node\Task;
use Maestro\Node\TaskContext;
use Maestro\Node\TaskHandlerRegistry;
use Maestro\Node\TaskRunner;

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

    public function run(Task $task, TaskContext $context): Promise
    {
        $handler = $this->registry->getFor($task);

        if (!is_callable($handler)) {
            throw new InvalidHandler(sprintf(
                'Handler "%s" is not __invoke-able',
                get_class($handler)
            ));
        }

        $promise = call_user_func($handler, $task, $context);

        if (!$promise instanceof Promise) {
            throw new InvalidHandlerResponse(sprintf(
                'Handler for task "%s" must return an a `Promise`, got "%s"',
                get_class($task),
                is_object($promise) ? get_class($promise) : gettype($promise)
            ));
        }

        return $promise;
    }
}
