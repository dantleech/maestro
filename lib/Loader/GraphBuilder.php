<?php

namespace Maestro\Loader;

use Maestro\Loader\Exception\GraphContainsCircularReference;
use Maestro\Task\Edge;
use Maestro\Task\Graph;
use Maestro\Task\Node;
use Maestro\Loader\Task;
use RuntimeException;

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
        $nodes = [ Node::create(self::NODE_ROOT) ];
        $edges = [];
        $this->walkPackages($manifest, $nodes, $edges);

        return new Graph($nodes, $edges);
    }

    private function walkPackages(Manifest $manifest, array &$nodes, array &$edges)
    {
        foreach ($manifest->packages() as $package) {
            $nodes[] = $packageNode = Node::create(
                $package->name(),
                Instantiator::create()->instantiate(
                    $this->taskMap->classNameFor('package'),
                    [
                        'name' => $package->name()
                    ]
                )
            );
            $edges[] = Edge::create($package->name(), self::NODE_ROOT);

            $prototype = $package->prototype();
            $prototype = $prototype ? $manifest->prototype($prototype) : null;

            $this->walkPackage($packageNode, $package, $nodes, $edges, $prototype);
        }
    }

    private function walkPackage(Node $packageNode, Package $package, array &$nodes, &$edges, ?Prototype $prototype)
    {
        $tasks = array_merge($prototype ? $prototype->tasks() : [], $package->tasks());

        /** @var Task $task */
        foreach ($tasks as $taskName => $task) {
            $taskName = $this->namespace($package, $taskName);

            $nodes[] = Node::create(
                $taskName,
                Instantiator::create()->instantiate(
                    $this->taskMap->classNameFor($task->type()),
                    $task->parameters()
                )
            );
            $edges[] = Edge::create($taskName, $package->name());

            foreach ($task->depends() as $dependency) {
                $edges[] = Edge::create($taskName, $this->namespace($package, $dependency));
            }
        }
    }

    private function namespace(Package $package, $taskName): string
    {
        return sprintf('%s#%s', $package->name(), $taskName);
    }
}
