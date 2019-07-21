<?php

namespace Maestro;

use Maestro\Loader\GraphConstructor;
use Maestro\Loader\ManifestLoader;
use Maestro\Loader\Processor\PrototypeExpandingProcessor;
use Maestro\Loader\Processor\ScheduleAliasExpandingProcessor;
use Maestro\Loader\Processor\TaskAliasExpandingProcessor;
use Maestro\Loader\AliasToClassMap;
use Maestro\Node\EnvironmentResolver;
use Maestro\Node\EnvironmentResolver\AggregatingEnvironmentResolver;
use Maestro\Node\HandlerRegistry\EagerHandlerRegistry;
use Maestro\Node\GraphWalker;
use Maestro\Node\NodeDecider\ScheduleDecider;
use Maestro\Node\NodeStateMachine;
use Maestro\Node\NodeDecider\ConcurrencyLimitingDecider;
use Maestro\Node\NodeDecider\TaskRunningDecider;
use Maestro\Node\Schedule;
use Maestro\Node\StateObserver;
use Maestro\Node\StateObservers;
use Maestro\Node\TaskHandler;
use Maestro\Node\TaskHandlerRegistry;
use Maestro\Node\TaskRunner;
use Maestro\Node\TaskRunner\HandlingTaskRunner;
use Maestro\Util\Cast;

final class MaestroBuilder
{
    private $taskMap = [];
    private $scheduleMap = [];
    private $handlers = [];
    private $maxConcurrency = 10;

    /**
     * @var bool|null
     */
    private $purge;

    /**
     * @var array
     */
    private $stateObservers = [];

    /**
     * @var string
     */
    private $workingDirectory;

    public function __construct(?string $workingDirectory = null)
    {
        $this->workingDirectory = $workingDirectory ?: Cast::toString(getcwd());
    }

    public static function create(): self
    {
        return new self();
    }

    public function build(): Maestro
    {
        return new Maestro(
            $this->buildLoader(),
            new GraphConstructor(
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

    public function addSchedule(string $alias, Schedule $schedule): self
    {
        $this->scheduleMap[$alias] = get_class($schedule);
        return $this;
    }

    public function addStateObserver(StateObserver $stateObserver): self
    {
        $this->stateObservers[] = $stateObserver;
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
            new ConcurrencyLimitingDecider($this->maxConcurrency),
            new ScheduleDecider(),
            new TaskRunningDecider($this->buildTaskRunner(), $this->buildArtifactsResolver()),
        ];
        return new GraphWalker(
            $this->buildNodeStateMachine(),
            $visitors
        );
    }

    private function buildArtifactsResolver(): EnvironmentResolver
    {
        return new AggregatingEnvironmentResolver();
    }

    private function buildNodeStateMachine(): NodeStateMachine
    {
        return new NodeStateMachine($this->buildStateObservers());
    }

    private function buildStateObservers()
    {
        return new StateObservers($this->stateObservers);
    }

    private function buildLoader(): ManifestLoader
    {
        return new ManifestLoader($this->workingDirectory, [
            new PrototypeExpandingProcessor(),
            new TaskAliasExpandingProcessor(new AliasToClassMap('task', $this->taskMap)),
            new ScheduleAliasExpandingProcessor(new AliasToClassMap('schedule', $this->scheduleMap))
        ]);
    }
}
