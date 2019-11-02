<?php

namespace Maestro\Extension\Runner\Model\Loader;

use Exception;
use Maestro\Library\Graph\Edge;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\GraphBuilder;
use Maestro\Library\Graph\Node;
use Maestro\Library\Instantiator\Instantiator;
use Maestro\Library\Support\NodeMeta;
use Maestro\Library\Support\Variables\Variables;
use RuntimeException;
use Webmozart\PathUtil\Path;

class GraphConstructor
{
    const NODE_ROOT = 'root';

    /**
     * @var ManifestNode
     */
    private $manifest;

    /**
     * @var PathExpander
     */
    private $pathExpander;

    public function __construct(PathExpander $pathExpander, ManifestNode $manifest)
    {
        $this->manifest = $manifest;
        $this->pathExpander = $pathExpander;
    }

    public function construct(): Graph
    {
        $builder = GraphBuilder::create();
        $this->buildNode($builder, $this->manifest, new Variables(), '');

        return $builder->build();
    }

    private function buildNode(GraphBuilder $builder, ManifestNode $node, Variables $variables, ?string $parentPath): void
    {
        $path = '/'.$node->name();
        if ($parentPath) {
            $path = Path::join([$parentPath, $node->name()]);
        }

        $variables = $variables->merge(new Variables($node->vars()));

        $builder->addNode(Node::create($path, [
            'label' => $node->name(),
            'task' => $this->createTask($node),
            'tags' => $node->tags(),
            'artifacts' => [
                new NodeMeta($node->name(), $path),
                $variables,
            ]
        ]));

        foreach ($this->pathExpander->expand($this->nodeDependencies($node, $parentPath), $parentPath) as $dependencyPath) {
            $builder->addEdge(Edge::create($path, $dependencyPath));
        }

        foreach ($node->nodes() as $name => $childNode) {
            $this->buildNode($builder, $childNode, $variables, $path);
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

    private function createTask(ManifestNode $node)
    {
        try {
            return Instantiator::instantiate($node->type(), $node->args());
        } catch (Exception $e) {
            throw new RuntimeException(sprintf(
                'Could not instantiate node "%s"',
                $node->name()
            ), 0, $e);
        }
    }
}
