<?php

namespace Maestro;

use Maestro\Loader\GraphBuilder;
use Maestro\Loader\TaskMap;
use Maestro\Task\ArtifactsResolver;
use Maestro\Task\ArtifactsResolver\AggregatingArtifactsResolver;
use Maestro\Task\HandlerRegistry\EagerHandlerRegistry;
use Maestro\Task\GraphWalker;
use Maestro\Task\NodeVisitor\TaskRunningVisitor;
use Maestro\Task\TaskHandler;
use Maestro\Task\TaskHandlerRegistry;
use Maestro\Task\TaskRunner;
use Maestro\Task\TaskRunner\HandlingTaskRunner;

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
            $this->buildGraphWalker()
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

    private function buildGraphWalker(): GraphWalker
    {
        $visitors = [
            new TaskRunningVisitor($this->buildTaskRunner(), $this->buildArtifactsResolver()),
        ];
        return new GraphWalker($visitors);
    }

    private function buildArtifactsResolver(): ArtifactsResolver
    {
        return new AggregatingArtifactsResolver();
    }
}
