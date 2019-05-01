<?php

namespace Maestro\Model\Package;

use Maestro\Model\Job\Queue;

class QueueFactory
{
    /**
     * @var array
     */
    private $jobNameToClassMap;

    public function __construct(array $jobNameToClassMap)
    {
        $this->jobNameToClassMap = $jobNameToClassMap;
    }

    public function create(Manifest $manifest): Queue
    {
        $queue = new Queue();

        foreach ($manifest->getIterator() as $manifestItem) {
            $jobClass = $this->jobNameToClassMap[$manifestItem->name()];
            $job = Instantiator::create()->instantiate($jobClass, $manifestItem->parameters());
            $queue->enqueue($job);
        }

        return $queue;
    }
}
