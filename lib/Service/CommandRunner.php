<?php

namespace Maestro\Service;

use Amp\Promise;
use Maestro\Adapter\Amp\Job\Process;
use Maestro\Model\Package\PackageDefinition;
use Maestro\Model\Package\PackageDefinitions;
use Maestro\Model\Job\QueueManager;
use Maestro\Model\Package\PackageRepository;

final class CommandRunner
{
    /**
     * @var QueueManager
     */
    private $queueManager;

    /**
     * @var PackageDefinitions
     */
    private $definitions;

    public function __construct(PackageDefinitions $definitions, QueueManager $queueManager)
    {
        $this->definitions = $definitions;
        $this->queueManager = $queueManager;
    }

    public function run(string $command): void
    {
        foreach ($this->definitions as $package) {
            assert($package instanceof PackageDefinition);

            $this->queueManager->getOrCreate($package->syncId())->enqueue(
                new Process($package, $command)
            );
        }

        $this->queueManager->dispatch();
    }
}
