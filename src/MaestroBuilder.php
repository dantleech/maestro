<?php

namespace Maestro;

use Maestro\Loader\GraphConstructor;
use Maestro\Loader\ManifestLoader;
use Maestro\Loader\Processor\PrototypeExpandingProcessor;
use Maestro\Loader\Processor\ScheduleAliasExpandingProcessor;
use Maestro\Loader\Processor\TaskAliasExpandingProcessor;
use Maestro\Loader\AliasToClassMap;
use Maestro\Graph\EnvironmentResolver;
use Maestro\Graph\EnvironmentResolver\AggregatingEnvironmentResolver;
use Maestro\Graph\TaskHandlerRegistry\EagerHandlerRegistry;
use Maestro\Graph\GraphWalker;
use Maestro\Graph\NodeDecider\ScheduleDecider;
use Maestro\Graph\NodeStateMachine;
use Maestro\Graph\NodeDecider\ConcurrencyLimitingDecider;
use Maestro\Graph\NodeDecider\TaskRunningDecider;
use Maestro\Graph\Scheduler;
use Maestro\Graph\SchedulerRegistry;
use Maestro\Graph\SchedulerRegistry\EagerSchedulerRegistry;
use Maestro\Graph\StateObserver;
use Maestro\Graph\StateObservers;
use Maestro\Graph\TaskHandler;
use Maestro\Graph\TaskHandlerRegistry;
use Maestro\Graph\TaskRunner;
use Maestro\Graph\TaskRunner\HandlingTaskRunner;
use Maestro\Util\Cast;

final class MaestroBuilder
{
    private $taskMap = [];
    private $handlers = [];
    private $scheduleMap = [];
    private $schedulers = [];
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

    public function addSchedule(string $alias, string $scheduleClass, Scheduler $scheduler): self
    {
        $this->scheduleMap[$alias] = $scheduleClass;
        $this->schedulers[$scheduleClass] = $scheduler;
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

    private function buildSchedulerRegistry(): SchedulerRegistry
    {
        return new EagerSchedulerRegistry($this->schedulers);
    }

    private function buildGraphWalker(): GraphWalker
    {
        $visitors = [
            new ConcurrencyLimitingDecider($this->maxConcurrency),
            new ScheduleDecider($this->buildSchedulerRegistry()),
            new TaskRunningDecider(
                $this->buildTaskRunner(),
                $this->buildSchedulerRegistry(),
                $this->buildArtifactsResolver()
            ),
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
