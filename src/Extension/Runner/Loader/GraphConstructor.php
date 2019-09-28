<?php

namespace Maestro\Extension\Runner\Loader;

use Maestro\Extension\Runner\Task\InitTask;
use Maestro\Extension\Runner\Task\PackageInitTask;
use Maestro\Extension\Vcs\Task\CheckoutTask;
use Maestro\Library\Graph\Edge;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\GraphBuilder;
use Maestro\Library\Graph\Node;
use Maestro\Library\Instantiator\Instantiator;

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
                'label' => 'root',
                'task' => Instantiator::instantiate(InitTask::class, [
                    'manifest' => $manifest
                ]),
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
                    'task' => Instantiator::instantiate(PackageInitTask::class, [
                        'name' => $package->name(),
                        'purgeWorkspace' => $this->purge ?: false,
                        'env' => $package->env(),
                        'vars' => $package->vars(),
                        'version' => $package->version()
                    ]),
                    'tags' => $package->tags()
                ]
            ));

            $builder->addEdge(Edge::create($package->name(), self::NODE_ROOT));
            $parentId = $package->name();

            if ($package->url()) {
                $parentId = $this->addVcsNode($package, $builder, $parentId);
            }

            $this->walkPackage($parentId, $package, $builder);
        }
    }

    private function walkPackage(string $parentId, Package $package, GraphBuilder $builder)
    {
        /** @var Task $task */
        foreach ($package->tasks() as $taskName => $task) {
            $nodeId = $this->namespacedTaskName($package, $taskName);

            $builder->addNode(Node::create(
                $nodeId,
                [
                    'label' => $taskName,
                    'task' => Instantiator::instantiate(
                        $task->type(),
                        $task->args()
                    ),
                    'tags' => $task->tags(),
                ]
            ));

            if (empty($task->depends())) {
                $builder->addEdge(Edge::create($nodeId, $parentId));
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

    private function addVcsNode(Package $package, GraphBuilder $builder, string $parentId): string
    {
        $vcsNode = Node::create(
            $package->name() . '/vcs',
            [
                'label' => 'checkout',
                'task' => new CheckoutTask($package->url()),
            ]
        );
        $builder->addNode($vcsNode);
        $builder->addEdge(Edge::create($vcsNode->id(), $parentId));
        $parentId = $vcsNode->id();
        return $parentId;
    }
}
