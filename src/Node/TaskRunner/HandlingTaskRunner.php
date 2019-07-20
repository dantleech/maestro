<?php

namespace Maestro\Node\TaskRunner;

use Amp\Promise;
use Maestro\Node\Environment;
use Maestro\Node\Task;
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

    public function run(Task $task, Environment $environment): Promise
    {
        $handler = $this->registry->getFor($task);
        $promise = $handler->execute($task, $environment);

        return $promise;
    }
}
