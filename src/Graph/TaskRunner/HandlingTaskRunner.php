<?php

namespace Maestro\Graph\TaskRunner;

use Amp\Promise;
use Maestro\Graph\Environment;
use Maestro\Graph\Task;
use Maestro\Graph\TaskHandlerRegistry;
use Maestro\Graph\TaskRunner;

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
