<?php

namespace Maestro;

use Maestro\Loader\GraphBuilder;
use Maestro\Loader\ManifestLoader;
use Maestro\Node\Graph;
use Maestro\Node\GraphWalker;
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

    /**
     * @var ManifestLoader
     */
    private $loader;

    public function __construct(
        ManifestLoader $loader,
        GraphBuilder $builder,
        GraphWalker $walker
    )
    {
        $this->builder = $builder;
        $this->walker = $walker;
        $this->loader = $loader;
    }

    public function loadManifest(string $path): Manifest
    {
        return $this->loader->load($path);
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
