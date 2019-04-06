<?php

namespace Maestro\Model;

use Amp\Loop;
use Amp\Promise;
use Maestro\Model\Unit\UnitExecutor;

class Maestro
{
    private $executor;
    private $queueRegistry;
    private $dispatcher;

    public function __construct(
        UnitExecutor $executor
    )
    {
        $this->executor = $executor;
    }

    public function run(array $config): Promise
    {
        return \Amp\call(function () use ($config) {

            $this->executor->execute($config);

        });
    }
}
