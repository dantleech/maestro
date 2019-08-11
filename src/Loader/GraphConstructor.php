<?php

namespace Maestro\Loader;

use Maestro\Extension\Maestro\Task\GitTask;
use Maestro\Extension\Maestro\Task\ManifestTask;
use Maestro\Extension\Maestro\Task\PackageTask;
use Maestro\Graph\Edge;
use Maestro\Graph\Graph;
use Maestro\Graph\GraphBuilder;
use Maestro\Graph\Node;
use Maestro\Graph\SystemTags;

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
                            'vars' => $package->vars(),
                            'env' => $package->env()
                        ]
                    ),
                    'tags' => [ SystemTags::TAG_INITIALIZE ],
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
                    'task' => Instantiator::create()->instantiate(
                        $task->type(),
                        $task->args()
                    ),
                    'schedule' => Instantiator::create()->instantiate(
                        $task->schedule()->type(),
                        $task->schedule()->args()
                    ),
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
                'label' => sprintf('%s VCS checkout', $package->name()),
                'task' => Instantiator::create()->instantiate(
                    GitTask::class,
                    [
                        'url' => $package->url(),
                    ]
                ),
                'tags' => [ SystemTags::TAG_INITIALIZE ]
            ]
        );
        $builder->addNode($vcsNode);
        $builder->addEdge(Edge::create($vcsNode->id(), $parentId));
        $parentId = $vcsNode->id();
        return $parentId;
    }
}
