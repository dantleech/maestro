<?php

namespace Maestro\Loader;

use Maestro\Loader\Manifest;
use Maestro\Loader\Package;
use Maestro\Task\Node;

class GraphBuilder
{
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
    )
    {
        $root = Node::createRoot();
        $this->walkPackages($root, $manifest);

        return $root;
    }

    private function walkPackages(Node $root, Manifest $manifest)
    {
        foreach ($manifest->packages() as $package) {
            $packageNode = $root->addChild(
                Node::create(
                    'package-init',
                    Instantiator::create()->instantiate(
                        $this->taskMap->classNameFor('package'),
                        [
                            'name' => $package->name()
                        ]
                    )
                )
            );

            $prototype = $package->prototype() ? $manifest->prototype($package->prototype()) : null;

            $this->walkPackage($packageNode, $package, $prototype);
        }
    }

    private function walkPackage(Node $packageNode, Package $package, ?Prototype $prototype)
    {
        $tasks = array_merge($prototype ? $prototype->tasks() : [], $package->tasks());

        foreach ($tasks as $name => $task) {
            $packageNode->addChild(
                Node::create(
                    $name,
                    Instantiator::create()->instantiate(
                        $this->taskMap->classNameFor($task->type()),
                        []
                    )
                )
            );
        }
    }
}
