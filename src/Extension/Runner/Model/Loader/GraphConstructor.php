<?php

namespace Maestro\Extension\Runner\Model\Loader;

use Maestro\Library\Graph\Edge;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\GraphBuilder;
use Maestro\Library\Graph\Node;
use Maestro\Library\Instantiator\Instantiator;
use Webmozart\PathUtil\Path;

class GraphConstructor
{
    const NODE_ROOT = 'root';

    /**
     * @var bool|null
     */
    private $purge;

    /**
     * @var ManifestNode
     */
    private $manifest;

    /**
     * @var PathExpander
     */
    private $pathExpander;

    public function __construct(PathExpander $pathExpander, ManifestNode $manifest, ?bool $purge = null)
    {
        $this->purge = $purge;
        $this->manifest = $manifest;
        $this->pathExpander = $pathExpander;
    }

    public function construct(): Graph
    {
        $builder = GraphBuilder::create();
        $this->buildNode($builder, $this->manifest, '');

        return $builder->build();
    }

    private function buildNode(GraphBuilder $builder, ManifestNode $node, ?string $parentPath): void
    {
        $path = '/'.$node->name();
        if ($parentPath) {
            $path = Path::join([$parentPath, $node->name()]);
        }

        $builder->addNode(Node::create($path, [
            'label' => $node->name(),
            'task' => Instantiator::instantiate($node->type(), $node->args()),
            'tags' => $node->tags(),
        ]));

        foreach ($this->pathExpander->expand($this->nodeDependencies($node, $parentPath), $parentPath) as $dependencyPath) {
            $builder->addEdge(Edge::create($path, $dependencyPath));
        }

        foreach ($node->nodes() as $name => $childNode) {
            $this->buildNode($builder, $childNode, $path);
        }
    }

    private function nodeDependencies(ManifestNode $node, ?string $parentPath): array
    {
        $depends = $node->depends();

        if ($parentPath) {
            $depends[] = $parentPath;
        }

        return $depends;
    }
}
