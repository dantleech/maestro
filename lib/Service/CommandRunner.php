<?php

namespace Maestro\Service;

use Amp\Promise;
use Maestro\Adapter\Amp\Job\Process;
use Maestro\Model\Job\QueueDispatcher;
use Maestro\Model\Job\Queues;
use Maestro\Model\Package\PackageDefinition;
use Maestro\Model\Package\PackageDefinitions;
use Maestro\Model\Package\PackageRepository;

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

    public function __construct(PackageDefinitions $definitions, QueueDispatcher $queueDispatcher)
    {
        $this->definitions = $definitions;
        $this->queueDispatcher = $queueDispatcher;
    }

    public function run(string $command): void
    {
        $queues = Queues::create();

        foreach ($this->definitions as $package) {
            assert($package instanceof PackageDefinition);

            $queues->get($package->syncId())->enqueue(
                new Process($package, $command)
            );
        }

        $this->queueDispatcher->dispatch($queues);
    }
}
