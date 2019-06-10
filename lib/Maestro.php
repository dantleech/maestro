<?php

namespace Maestro;

use Maestro\Loader\GraphBuilder;
use Maestro\Task\Graph;
use Maestro\Task\GraphWalker;
use Maestro\Loader\Manifest;
use RuntimeException;

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

    public function buildGraph(Manifest $manifest, ?string $query, ?int $depth): Graph
    {
        $graph = $this->builder->build($manifest);

        if ($query) {
            $targets = $graph->nodes()->query($query);

            if ($targets->count() === 0) {
                throw new RuntimeException(sprintf(
                    'No targets found for query "%s"',
                    $query
                ));
            }

            $graph = $graph->pruneFor($targets->names());
        }

        if (null !== $depth) {
            $graph = $graph->pruneToDepth($depth);
        }

        return $graph;
    }

    public function dispatch(Graph $graph): void
    {
        $this->walker->walk($graph);
    }
}
