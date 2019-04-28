<?php

namespace Maestro\Service;

use Maestro\Adapter\Amp\Job\InitializePackage;
use Maestro\Adapter\Twig\Job\ApplyTemplate;
use Maestro\Model\Job\QueueDispatcher;
use Maestro\Model\Job\QueueStatuses;
use Maestro\Model\Job\Queues;
use Maestro\Model\Package\PackageDefinition;
use Maestro\Model\Package\PackageDefinitions;
use Maestro\Model\Package\Workspace;

class Applicator
{
    /**
     * @var PackageDefinitions
     */
    private $definitions;
    /**
     * @var QueueDispatcher
     */
    private $queueDispatcher;
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

    public function apply(bool $reset, string $query): QueueStatuses
    {
        $queues = Queues::create();

        foreach ($this->definitions->query($query) as $package) {
            assert($package instanceof PackageDefinition);

            $workingDirectory = $this->workspace->package($package)->path();

            $queue = $queues->get($package->syncId());

            $queue->enqueue(
                new InitializePackage($queue, $package, $reset)
            );

            foreach ($package->manifest() as $name => $fileDefinition) {
                $queue->enqueue(
                    new ApplyTemplate($package, $name, $fileDefinition['dest'] ?? $name)
                );
            }
        }

        return $this->queueDispatcher->dispatch($queues);
    }
}
