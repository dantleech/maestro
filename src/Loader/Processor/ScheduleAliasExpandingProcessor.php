<?php

namespace Maestro\Loader\Processor;

use Maestro\Loader\Processor;
use Maestro\Loader\AliasToClassMap;

class ScheduleAliasExpandingProcessor implements Processor
{
    /**
     * @var AliasToClassMap
     */
    private $scheduleMap;

    public function __construct(AliasToClassMap $scheduleMap)
    {
        $this->scheduleMap = $scheduleMap;
    }

    public function process(array $manifest): array
    {
        foreach ($manifest['packages'] ?? [] as $packageName => $package) {
            foreach ($package['tasks'] ?? [] as $taskName => $task) {
                if (!isset($task['schedule'])) {
                    continue;
                }

                if (!isset($task['schedule']['type'])) {
                    continue;
                }

                $manifest['packages'][$packageName]['tasks'][$taskName]['schedule']['type'] = $this->scheduleMap->classNameFor($task['schedule']['type']);
            }
        }

        return $manifest;
    }
}
