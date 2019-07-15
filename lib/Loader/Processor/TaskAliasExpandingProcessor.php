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
        return $manifest;
    }
}
