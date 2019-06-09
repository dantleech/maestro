<?php

namespace Maestro\Loader;

use Maestro\Extension\Maestro\Task\ManifestTask;
use Maestro\Task\Edge;
use Maestro\Task\Graph;
use Maestro\Task\Node;

class GraphBuilder
{
    const NODE_ROOT = 'root';

    /**
     * @var TaskMap
     */
    private $taskMap;

    public function __construct(TaskMap $taskMap)
    {
        $this->taskMap = $taskMap;
    }

    public function build(
        Manifest $manifest
    ): Graph {
        $nodes = [
            Node::create(self::NODE_ROOT, [
                'task' => new ManifestTask($manifest->path())
            ])
        ];
        $edges = [];
        $this->walkPackages($manifest, $nodes, $edges);

        return Graph::create($nodes, $edges);
    }

    private function walkPackages(Manifest $manifest, array &$nodes, array &$edges)
    {
        foreach ($manifest->packages() as $package) {
            $nodes[] = $packageNode = Node::create(
                $package->name(),
                [
                    'label' => $package->name(),
                    'task' => Instantiator::create()->instantiate(
                        $this->taskMap->classNameFor('package'),
                        [
                            'name' => $package->name(),
                            'purgeWorkspace' => $package->purgeWorkspace(),
                        ]
                    ),
                ]
            );

            $edges[] = Edge::create($package->name(), self::NODE_ROOT);
            $this->walkPackage($packageNode, $package, $nodes, $edges);
        }
    }

    private function walkPackage(Node $packageNode, Package $package, array &$nodes, &$edges)
    {
        /** @var Task $task */
        foreach ($package->tasks() as $taskName => $task) {
            $nodeId = $this->namespace($package, $taskName);

            $nodes[] = Node::create(
                $nodeId,
                [
                    'label' => $taskName,
                    'task' => Instantiator::create()->instantiate(
                        $this->taskMap->classNameFor($task->type()),
                        $task->parameters()
                    )
                ]
            );

            if (empty($task->depends())) {
                $edges[] = Edge::create($nodeId, $package->name());
            }

            foreach ($task->depends() as $dependency) {
                $edges[] = Edge::create($nodeId, $this->namespace($package, $dependency));
            }
        }
    }

    private function namespace(Package $package, $taskName): string
    {
        return sprintf(
            '%s%s%s',
            $package->name(),
            Node::NAMEPSPACE_SEPARATOR,
            $taskName
        );
    }
}
