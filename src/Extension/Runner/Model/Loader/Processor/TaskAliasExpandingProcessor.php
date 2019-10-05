<?php

namespace Maestro\Extension\Runner\Model\Loader\Processor;

use Maestro\Extension\Task\Extension\TaskHandlerDefinitionMap;
use Maestro\Extension\Runner\Model\Loader\Processor;

class TaskAliasExpandingProcessor implements Processor
{
    /**
     * @var TaskHandlerDefinitionMap
     */
    private $map;

    public function __construct(TaskHandlerDefinitionMap $map)
    {
        $this->map = $map;
    }

    public function process(array $manifest): array
    {
        foreach ($manifest['packages'] ?? [] as $packageName => &$package) {
            foreach ($package['tasks'] ?? [] as $taskName => &$task) {
                $manifest['packages'][$packageName]['tasks'][$taskName]['type'] = $this->map->getDefinitionByAlias($task['type'])->taskClass();
            }
        }
        return $manifest;
    }
}
