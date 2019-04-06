<?php

namespace Phpactor\Extension\Maestro\Model;

use Amp\Loop;
use Amp\Promise;
use Phpactor\Extension\Maestro\Model\Unit\UnitExecutor;
use Phpactor\Extension\Maestro\Model\Queue\QueueDispatcher;
use Phpactor\Extension\Maestro\Model\Queue\QueueRegistry;

class Maestro
{
    private $executor;
    private $queueRegistry;
    private $dispatcher;

    public function __construct(
        UnitExecutor $executor,
        QueueRegistry $queueRegistry,
        QueueDispatcher $dispatcher
    )
    {
        $this->executor = $executor;
        $this->queueRegistry = $queueRegistry;
        $this->dispatcher = $dispatcher;
    }

    public function run(array $config): Promise
    {
        return \Amp\call(function () use ($config) {

            $this->executor->execute($config);

            $promises = [];
            foreach ($this->queueRegistry->all() as $queue) {
                $promises[] = $this->dispatcher->dispatch($queue);
            }

            yield $promises;
        });
    }
}
