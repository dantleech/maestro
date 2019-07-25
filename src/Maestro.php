<?php

namespace Maestro;

use Maestro\Loader\GraphConstructor;
use Maestro\Loader\ManifestLoader;
use Maestro\Graph\Graph;
use Maestro\Graph\GraphWalker;
use Maestro\Loader\Manifest;
use RuntimeException;

class Maestro
{
    /**
     * @var GraphConstructor
     */
    private $constructor;

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
        GraphConstructor $constructor,
        GraphWalker $walker
    ) {
        $this->constructor = $constructor;
        $this->walker = $walker;
        $this->loader = $loader;
    }

    public function loadManifest(string $path): Manifest
    {
        return $this->loader->load($path);
    }

    public function buildGraph(Manifest $manifest, ?string $query, ?int $depth): Graph
    {
        $graph = $this->constructor->construct($manifest);

        if ($query) {
            $targets = $graph->nodes()->query($query);

            if ($targets->count() === 0) {
                throw new RuntimeException(sprintf(
                    'No targets found for query "%s"',
                    $query
                ));
            }

            $graph = $graph->pruneFor($targets->ids());
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
