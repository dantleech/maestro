<?php

namespace Maestro;

use Amp\Loop;
use Maestro\Loader\GraphBuilder;
use Maestro\Task\Scheduler;
use Maestro\Task\Dispatcher;
use Maestro\Loader\Manifest;
use Maestro\Task\Queue;

class Runner
{
    /**
     * @var GraphBuilder
     */
    private $builder;
    /**
     * @var Scheduler
     */
    private $scheduler;
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    public function __construct(GraphBuilder $builder, Scheduler $scheduler, Dispatcher $dispatcher)
    {
        $this->builder = $builder;
        $this->scheduler = $scheduler;
        $this->dispatcher = $dispatcher;
    }

    public function run(Manifest $manifest)
    {
        $graph = $this->builder->build($manifest);
        $queue = $this->scheduler->schedule($graph, new Queue());
        $this->dispatcher->dispatch($queue);
        Loop::run();
    }
}
