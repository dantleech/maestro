<?php

namespace Maestro\Loader\Processor;

use Maestro\Loader\Processor;
use Maestro\Loader\AliasToClassMap;

class TaskAliasExpandingProcessor implements Processor
{
    /**
     * @var AliasToClassMap
     */
    private $taskMap;

    public function __construct(AliasToClassMap $taskMap)
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
