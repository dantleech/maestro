<?php

namespace Maestro;

use Maestro\Loader\GraphBuilder;
use Maestro\Loader\TaskMap;
use Maestro\Task\Dispatcher;
use Maestro\Task\HandlerRegistry\EagerHandlerRegistry;
use Maestro\Task\Scheduler\DepthFirstScheduler;
use Maestro\Task\TaskHandler;
use Maestro\Task\TaskHandlerRegistry;
use Maestro\Task\TaskRunner;
use Maestro\Task\TaskRunner\HandlingTaskRunner;
use Maestro\Maestro;

final class MaestroBuilder
{
    private $taskMap = [];
    private $handlers = [];

    public static function create(): self
    {
        return new self();
    }

    public function build(): Maestro
    {
        return new Maestro(
            new GraphBuilder(new TaskMap($this->taskMap)),
            new DepthFirstScheduler(),
            new Dispatcher($this->buildTaskRunner())
        );
    }

    public function addJobHandler(string $alias, string $jobClass, TaskHandler $handler): self
    {
        $this->taskMap[$alias] = $jobClass;
        $this->handlers[$jobClass] = $handler;
        return $this;
    }

    private function buildTaskRunner(): TaskRunner
    {
        return new HandlingTaskRunner(
            $this->buildHandlerRegistry()
        );
    }

    private function buildHandlerRegistry(): TaskHandlerRegistry
    {
        return new EagerHandlerRegistry($this->handlers);
    }
}
