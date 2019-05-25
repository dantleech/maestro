<?php

namespace Maestro;

use Maestro\Loader\GraphBuilder;
use Maestro\Task\Node;
use Maestro\Task\Scheduler;
use Maestro\Task\Dispatcher;
use Maestro\Loader\Manifest;
use Maestro\Task\Queue;

class Maestro
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

    public function buildGraph(Manifest $manifest): Node
    {
        return $this->builder->build($manifest);
    }

    public function dispatch(Node $graph): void
    {
        $queue = $this->scheduler->schedule($graph, new Queue());
        $this->dispatcher->dispatch($queue);
    }
}
