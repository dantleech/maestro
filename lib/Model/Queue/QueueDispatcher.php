<?php


namespace Phpactor\Extension\Maestro\Model;

use Amp\Promise;
use Phpactor\Extension\Maestro\Model\Queue;

class QueueDispatcher
{
    private $handlerRegistry;

    public function __construct(JobHandlerRegistry $handlerRegistry)
    {
        $this->handlerRegistry = $handlerRegistry;
    }

    public function dispatch(Queue $queue): Promise
    {
        return \Amp\call(function () use ($queue) {
            while ($job = $queue->dequeue()) {
               $handler = $this->handlerRegistry->get($job->handler());
               yield $handler->__invoke($job);
            }
        });
    }
}
