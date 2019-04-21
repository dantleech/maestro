<?php

namespace Maestro\Model\Job\QueueDispatcher;

use Maestro\Model\Job\QueueDispatcher;
use Maestro\Model\Job\QueueModifier;
use Maestro\Model\Job\Queues;

class PreDispatcherDecorator implements QueueDispatcher
{
    /**
     * @var QueueDispatcher
     */
    private $innerDispatcher;

    /**
     * @var QueueModifier[]
     */
    private $queueModifiers = [];

    public function __construct(QueueDispatcher $innerDispatcher, array $queueModifiers)
    {
        $this->innerDispatcher = $innerDispatcher;
        foreach ($queueModifiers as $queueModifier) {
            $this->add($queueModifier);
        }
    }

    public function dispatch(Queues $queues): void
    {
        foreach ($queues as $queue) {
            foreach ($this->queueModifiers as $queueModifier) {
                $queueModifier->modify($queue);
            }
        }

        $this->innerDispatcher->dispatch($queues);
    }

    private function add(QueueModifier $queueModifier): void
    {
        $this->queueModifiers[] = $queueModifier;
    }
}
