<?php

namespace Maestro\Loader;

use Maestro\Extension\Maestro\Task\ManifestTask;
use Maestro\Node\Edge;
use Maestro\Node\Graph;
use Maestro\Node\Node;

class GraphBuilder
{
    const NODE_ROOT = 'root';

    /**
     * @var TaskMap
     */
    private $taskMap;

    /**
     * @var bool|null
     */
    private $purge;

    public function __construct(TaskMap $taskMap, ?bool $purge = null)
    {
        $this->taskMap = $taskMap;
        $this->purge = $purge;
    }

    public function build(
        Manifest $manifest
    ): Graph {
        $nodes = [
            Node::create(self::NODE_ROOT, [
                'task' => new ManifestTask($manifest->path(), $manifest->artifacts())
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
                            'purgeWorkspace' => $this->purge ?? $package->purgeWorkspace(),
                            'artifacts' => $package->artifacts()
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
            $nodeId = $this->namespacedTaskName($package, $taskName);

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
                $edges[] = Edge::create($nodeId, $this->namespacedTaskName($package, $dependency));
            }
        }
    }

    private function namespacedTaskName(Package $package, $taskName): string
    {
        return sprintf(
            '%s%s%s',
            $package->name(),
            Node::NAMEPSPACE_SEPARATOR,
            $taskName
        );
    }
}
