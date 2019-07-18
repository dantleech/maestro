<?php

namespace Maestro\Loader\Processor;

use Maestro\Loader\Processor;
use Maestro\Loader\TaskMap;

class TaskAliasExpandingProcessor implements Processor
{
    /**
     * @var TaskMap
     */
    private $taskMap;

    public function __construct(TaskMap $taskMap)
    {
        $this->taskMap = $taskMap;
    }

    public function process(array $manifest): array
    {
        foreach ($manifest['packages'] ?? [] as $packageName => &$package) {
            foreach ($package['tasks'] ?? [] as $taskName => &$task) {
                $manifest['packages'][$packageName]['tasks'][$taskName]['type'] = $this->taskMap->classNameFor($task['type']);
            }
        }
        return $manifest;
    }
}
