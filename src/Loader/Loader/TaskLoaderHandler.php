<?php

namespace Maestro\Loader\Loader;

use Maestro\Loader\Instantiator;
use Maestro\Loader\Loader;
use Maestro\Loader\LoaderHandler;
use Maestro\Loader\Task;
use Maestro\Loader\AliasToClassMap;
use Maestro\Node\Edge;
use Maestro\Node\GraphBuilder;
use Maestro\Node\Node;

class TaskLoaderHandler implements LoaderHandler
{
    /**
     * @var AliasToClassMap
     */
    private $taskMap;

    public function __construct(AliasToClassMap $taskMap)
    {
        $this->taskMap = $taskMap;
    }

    public function load(string $parentId, GraphBuilder $builder, Loader $loader): void
    {
        assert($loader instanceof TaskLoader);

        /** @var Task $task */
        foreach ($loader->tasks() as $taskName => $task) {
            $this->processTask($parentId, $builder, $taskName, $task);
        }
    }

    private function processTask(string $parentId, GraphBuilder $builder, string $taskName, Task $task)
    {
        $nodeId = $this->namespacedTaskName($parentId, $taskName);
        $builder->addNode(Node::create(
            $nodeId,
            [
                'label' => $taskName,
                'task' => Instantiator::create()->instantiate(
                    $this->taskMap->classNameFor($task->type()),
                    $task->parameters()
                )
            ]
        ));
        
        if (empty($task->depends())) {
            $builder->addEdge(Edge::create($nodeId, $parentId));
        }
        
        foreach ($task->depends() as $dependency) {
            $builder->addEdge(Edge::create(
                $nodeId,
                $this->namespacedTaskName($parentId, $dependency)
            ));
        }
    }

    private function namespacedTaskName(string $parentId, $taskName): string
    {
        return sprintf(
            '%s%s%s',
            $parentId,
            Node::NAMEPSPACE_SEPARATOR,
            $taskName
        );
    }
}
