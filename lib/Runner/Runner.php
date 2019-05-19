<?php

namespace Maestro\Runner;

use Amp\Loop;
use Maestro\Loader\GraphBuilder;
use Maestro\Task\Node;
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

    public function run(Manifest $manifest): Node
    {
        $graph = $this->builder->build($manifest);

        Loop::repeat(1000, function () use ($graph) {
            $queue = $this->scheduler->schedule($graph, new Queue());
            $this->dispatcher->dispatch($queue);
        });

        return $graph;
    }
}
