<?php

namespace Maestro\Service;

use Maestro\Model\Job\JobFactory;
use Maestro\Model\Job\QueueDispatcher;
use Maestro\Model\Job\QueueStatuses;
use Maestro\Model\Job\Queues;
use Maestro\Model\Package\ManifestItem;
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

    /**
     * @var JobFactory
     */
    private $jobFactory;

    public function __construct(
        PackageDefinitions $definitions,
        QueueDispatcher $queueDispatcher,
        Workspace $workspace,
        JobFactory $jobFactory
    ) {
        $this->definitions = $definitions;
        $this->queueDispatcher = $queueDispatcher;
        $this->workspace = $workspace;
        $this->jobFactory = $jobFactory;
    }

    public function apply(string $query, ?string $target = null): QueueStatuses
    {
        $queues = Queues::create();

        foreach ($this->definitions->query($query) as $package) {
            assert($package instanceof PackageDefinition);

            $workingDirectory = $this->workspace->package($package)->path();
            $queue = $queues->get($package->syncId());

            foreach ($package->manifest()->forTarget($target) as $item) {
                assert($item instanceof ManifestItem);
                $queue->enqueue(
                    $this->jobFactory->create($item->type(), $item->parameters(), [
                        'packageDefinition' => $package,
                        'queue' => $queue
                    ])
                );
            }
        }

        return $this->queueDispatcher->dispatch($queues);
    }
}
