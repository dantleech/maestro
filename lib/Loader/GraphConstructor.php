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

    /**
     * @var LoaderHandlerRegistry
     */
    private $registry;

    public function __construct(LoaderHandlerRegistry $registry, ?bool $purge = null)
    {
        $this->purge = $purge;
        $this->registry = $registry;
    }

    public function build(
        Manifest $manifest
    ): Graph {
        $builder = GraphBuilder::create();
        $builder->addNode(
            Node::create(self::NODE_ROOT, [
                'task' => new ManifestTask($manifest->path(), $manifest->artifacts())
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
                            'artifacts' => $package->artifacts()
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
        foreach ($package->loaders() as $loader) {
            $this->registry->getFor($loader)->load($packageNode->id(), $builder, $loader);
        }
    }
}
