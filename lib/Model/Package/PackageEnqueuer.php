<?php

namespace Maestro\Model\Package;

use Maestro\Model\Job\Job;
use Maestro\Model\Job\Queue;
use Maestro\Model\Job\Queues;
use Maestro\Model\Package\PackageDefinition;

class PackageEnqueuer
{
    public function enqueue(Queues $queues, PackageDefinition $package, Manifest $manifest)
    {
        $queue = $queues->get($package->syncId());

        foreach ($manifest as $name => $item) {
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
