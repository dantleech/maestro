<?php

namespace Maestro\Service;

use Amp\Promise;
use Maestro\Adapter\Amp\Job\InitializePackage;
use Maestro\Adapter\Amp\Job\Process;
use Maestro\Model\Job\QueueDispatcher;
use Maestro\Model\Job\QueueStatuses;
use Maestro\Model\Job\Queues;
use Maestro\Model\Package\PackageDefinition;
use Maestro\Model\Package\PackageDefinitions;
use Maestro\Model\Package\PackageRepository;
use Maestro\Model\Package\Workspace;

final class CommandRunner
{
    /**
     * @var QueueDispatcher
     */
    private $queueDispatcher;

    /**
     * @var PackageDefinitions
     */
    private $definitions;

    /**
     * @var Workspace
     */
    private $workspace;

    public function __construct(PackageDefinitions $definitions, QueueDispatcher $queueDispatcher, Workspace $workspace)
    {
        $this->definitions = $definitions;
        $this->queueDispatcher = $queueDispatcher;
        $this->workspace = $workspace;
    }

    public function run(string $command, bool $reset): QueueStatuses
    {
        $queues = Queues::create();

        foreach ($this->definitions as $package) {
            assert($package instanceof PackageDefinition);

            $workingDirectory = $this->workspace->package($package)->path();

            $queue = $queues->get($package->syncId());

            $queue->enqueue(
                new InitializePackage($queue, $package, $reset)
            );

            $queue->enqueue(
                new Process($workingDirectory, $command, $package->consoleId())
            );
        }

        return $this->queueDispatcher->dispatch($queues);
    }
}
