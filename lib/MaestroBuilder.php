<?php

namespace Maestro;

use Maestro\Loader\GraphBuilder;
use Maestro\Loader\TaskMap;
use Maestro\Task\ArtifactsResolver;
use Maestro\Task\ArtifactsResolver\AggregatingArtifactsResolver;
use Maestro\Task\HandlerRegistry\EagerHandlerRegistry;
use Maestro\Task\GraphWalker;
use Maestro\Task\NodeStateMachine;
use Maestro\Task\NodeVisitor\ConcurrencyLimitingVisitor;
use Maestro\Task\NodeVisitor\TaskRunningVisitor;
use Maestro\Task\StateObservers;
use Maestro\Task\TaskHandler;
use Maestro\Task\TaskHandlerRegistry;
use Maestro\Task\TaskRunner;
use Maestro\Task\TaskRunner\HandlingTaskRunner;

final class MaestroBuilder
{
    private $taskMap = [];
    private $handlers = [];
    private $maxConcurrency = 10;

    /**
     * @var bool|null
     */
    private $purge;

    public static function create(): self
    {
        return new self();
    }

    public function build(): Maestro
    {
        return new Maestro(
            new GraphBuilder(
                new TaskMap($this->taskMap),
                $this->purge
            ),
            $this->buildGraphWalker()
        );
    }

    public function addJobHandler(string $alias, string $jobClass, TaskHandler $handler): self
    {
        $this->taskMap[$alias] = $jobClass;
        $this->handlers[$jobClass] = $handler;
        return $this;
    }

    public function withMaxConcurrency(int $maxConcurrency)
    {
        $this->maxConcurrency = $maxConcurrency;
        return $this;
    }

    public function withPurge(?bool $purge): self
    {
        $this->purge = $purge;

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
            new ConcurrencyLimitingVisitor($this->maxConcurrency),
            new TaskRunningVisitor($this->buildTaskRunner(), $this->buildArtifactsResolver()),
        ];
        return new GraphWalker(
            $this->buildNodeStateMachine(),
            $visitors
        );
    }

    private function buildArtifactsResolver(): ArtifactsResolver
    {
        return new AggregatingArtifactsResolver();
    }

    private function buildNodeStateMachine(): NodeStateMachine
    {
        return new NodeStateMachine($this->buildStateObservers());
    }

    private function buildStateObservers()
    {
        return new StateObservers();
    }
}
