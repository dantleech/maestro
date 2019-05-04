<?php

namespace Maestro\Service;

use Maestro\Model\Job\Job;
use Maestro\Model\Job\Queue;
use Maestro\Model\Job\QueueDispatcher;
use Maestro\Model\Job\QueueStatuses;
use Maestro\Model\Job\Queues;
use Maestro\Model\Instantiator;
use Maestro\Model\Package\ManifestItem;
use Maestro\Model\Package\PackageDefinition;
use Maestro\Model\Package\PackageDefinitions;
use Maestro\Model\Package\Workspace;
use RuntimeException;

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
     * @var array
     */
    private $jobClassMap;

    public function __construct(
        PackageDefinitions $definitions,
        QueueDispatcher $queueDispatcher,
        Workspace $workspace,
        array $jobClassMap
    ) {
        $this->definitions = $definitions;
        $this->queueDispatcher = $queueDispatcher;
        $this->workspace = $workspace;
        $this->jobClassMap = $jobClassMap;
    }

    public function apply(Queues $queues, string $query): QueueStatuses
    {
        foreach ($this->definitions->query($query) as $package) {
            assert($package instanceof PackageDefinition);

            $workingDirectory = $this->workspace->package($package)->path();
            $queue = $queues->get($package->syncId());

            foreach ($package->manifest() as $item) {
                if (!isset($this->jobClassMap[$item->type()])) {
                    throw new RuntimeException(sprintf(
                        'No job registered for type "%s"',
                        $item->type()
                    ));
                }

                $queue->enqueue(
                    $this->createJob($item, $queue, $package)
                );
            }
        }

        return $this->queueDispatcher->dispatch($queues);
    }

    private function createJob(ManifestItem $item, Queue $queue, PackageDefinition $package): Job
    {
        return Instantiator::create()->instantiate(
            $this->jobClassMap[$item->type()],
            $item->parameters(),
            [
                'queue' => $queue,
                'packageDefinition' => $package,
            ]
        );
    }
}
