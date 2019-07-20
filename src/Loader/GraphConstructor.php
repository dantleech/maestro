<?php

namespace Maestro\Loader;

use Maestro\Extension\Maestro\Task\ManifestTask;
use Maestro\Extension\Maestro\Task\PackageTask;
use Maestro\Node\Edge;
use Maestro\Node\Graph;
use Maestro\Node\GraphBuilder;
use Maestro\Node\Node;

class GraphConstructor
{
    const NODE_ROOT = 'root';

    /**
     * @var bool|null
     */
    private $purge;

    public function __construct(?bool $purge = null)
    {
        $this->purge = $purge;
    }

    public function construct(
        Manifest $manifest
    ): Graph {
        $builder = GraphBuilder::create();
        $builder->addNode(
            Node::create(self::NODE_ROOT, [
                'task' => new ManifestTask($manifest->path(), $manifest->vars(), $manifest->env())
            ])
        );
        $this->walkPackages($manifest, $builder);

        return $builder->build();
    }

    private function walkPackages(Manifest $manifest, GraphBuilder $builder)
    {
        foreach ($manifest->packages() as $package) {
            $builder->addNode($packageNode = Node::create(
                $package->name(),
                [
                    'label' => $package->name(),
                    'task' => Instantiator::create()->instantiate(
                        PackageTask::class,
                        [
                            'name' => $package->name(),
                            'purgeWorkspace' => $this->purge ?? $package->purgeWorkspace(),
                            'environment' => $package->environment()
                        ]
                    ),
                ]
            ));

            $builder->addEdge(Edge::create($package->name(), self::NODE_ROOT));
            $this->walkPackage($packageNode, $package, $builder);
        }
    }

    private function walkPackage(Node $packageNode, Package $package, GraphBuilder $builder)
    {
        /** @var Task $task */
        foreach ($package->tasks() as $taskName => $task) {
            $nodeId = $this->namespacedTaskName($package, $taskName);

            $builder->addNode(Node::create(
                $nodeId,
                [
                    'label' => $taskName,
                    'task' => Instantiator::create()->instantiate(
                        $task->type(),
                        $task->args()
                    )
                ]
            ));

            if (empty($task->depends())) {
                $builder->addEdge(Edge::create($nodeId, $package->name()));
            }

            foreach ($task->depends() as $dependency) {
                $builder->addEdge(Edge::create(
                    $nodeId,
                    $this->namespacedTaskName($package, $dependency)
                ));
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
