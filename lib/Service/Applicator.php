<?php

namespace Maestro\Service;

use Maestro\Model\Job\QueueDispatcher;
use Maestro\Model\Job\QueueStatuses;
use Maestro\Model\Job\Queues;
use Maestro\Model\Instantiator;
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

    public function apply(string $query): QueueStatuses
    {
        $queues = Queues::create();

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
                    Instantiator::create()->instantiate(
                        $this->jobClassMap[$item->type()],
                        $item->parameters(),
                        [
                            'queue' => $queue,
                            'packageDefinition' => $package,
                        ]
                    )
                );
            }
        }

        return $this->queueDispatcher->dispatch($queues);
    }
}
