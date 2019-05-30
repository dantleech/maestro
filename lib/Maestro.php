<?php

namespace Maestro;

use Maestro\Loader\GraphBuilder;
use Maestro\Task\Graph;
use Maestro\Task\GraphWalker;
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
     * @var GraphWalker
     */
    private $walker;

    public function __construct(GraphBuilder $builder, GraphWalker $walker)
    {
        $this->builder = $builder;
        $this->walker = $walker;
    }

    public function buildGraph(Manifest $manifest): Graph
    {
        return $this->builder->build($manifest);
    }

    public function dispatch(Graph $graph): void
    {
        $this->walker->walk($graph);
    }
}
