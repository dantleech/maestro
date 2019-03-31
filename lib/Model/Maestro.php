<?php

namespace Phpactor\Extension\Maestro\Model;

use Amp\Loop;
use Amp\Promise;
use Phpactor\Extension\Maestro\Model\UnitExecutor;

class Maestro
{
    private $executor;
    private $queueRegistry;

    public function __construct(UnitExecutor $executor, QueueRegistry $queueRegistry)
    {
        $this->executor = $executor;
        $this->queueRegistry = $queueRegistry;
    }

    public function run(array $config): Promise
    {
        return \Amp\call(function () use ($config) {
            $this->executor->execute($config);

            $promises = [];
            foreach ($this->queueRegistry->all() as $queue) {
                $promises[] = $queue->run();
            }

            yield $promises;
        });
    }
}
