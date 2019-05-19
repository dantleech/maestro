<?php

namespace Maestro\Loader;

use Maestro\Loader\Exception\GraphContainsCircularReference;
use Maestro\Task\Node;
use RuntimeException;

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
    ) {
        $root = Node::createRoot();
        $this->walkPackages($root, $manifest);

        return $root;
    }

    private function walkPackages(Node $root, Manifest $manifest)
    {
        foreach ($manifest->packages() as $package) {
            $packageNode = $root->addChild(
                Node::create(
                    $package->name(),
                    Instantiator::create()->instantiate(
                        $this->taskMap->classNameFor('package'),
                        [
                            'name' => $package->name()
                        ]
                    )
                )
            );

            $prototype = $package->prototype();
            $prototype = $prototype ? $manifest->prototype($prototype) : null;

            $this->walkPackage($packageNode, $package, $prototype);
        }
    }

    private function walkPackage(Node $packageNode, Package $package, ?Prototype $prototype)
    {
        $tasks = array_merge($prototype ? $prototype->tasks() : [], $package->tasks());
        $this->walkTasks($packageNode, $tasks);
    }

    private function walkTasks(Node $node, array $tasks)
    {
        $resolved = [];
        foreach ($tasks as $taskName => $task) {
            $task = $this->walkTask($node, $taskName, $task, $tasks, $resolved);
            $resolved[$taskName] = $task;
        }
    }

    private function walkTask(Node $node, string $taskName, Task $task, array $tasks, array $resolved, $seen = []): Node
    {
        if (isset($resolved[$taskName])) {
            return $resolved[$taskName];
        }

        if (in_array($taskName, $seen)) {
            throw new GraphContainsCircularReference(sprintf(
                'Graph contains circular reference: "%s -> %s"',
                implode(' -> ', array_reverse($seen)),
                $taskName
            ));
        }
        $seen[] = $taskName;

        if ($task->depends()) {
            $depName = $task->depends();
            if (!isset($tasks[$taskName])) {
                throw new RuntimeException(sprintf(
                    'Task depends on unknown task "%s", known tasks: "%s"',
                    $taskName,
                    implode('", "', array_keys($tasks))
                ));
            }

            if (isset($resolved[$depName])) {
                $node = $resolved[$depName];
            }

            $node = $resolved[$depName] = $this->walkTask(
                $node,
                $depName,
                $tasks[$taskName],
                $tasks,
                $resolved,
                $seen
            );
        }

        return $node->addChild(
            Node::create(
                $taskName,
                Instantiator::create()->instantiate(
                    $this->taskMap->classNameFor($task->type()),
                    $task->parameters()
                )
            )
        );
    }
}
