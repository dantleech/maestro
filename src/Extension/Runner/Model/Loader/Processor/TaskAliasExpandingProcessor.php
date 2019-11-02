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

    public function process(array $node): array
    {
        if (isset($node['type'])) {
            $node['type'] = $this->map->getDefinitionByAlias($node['type'])->taskClass();
        }

        foreach ($node['nodes'] ?? [] as $childName => $childNode) {
            $node['nodes'][$childName] = $this->process($childNode);
        }

        return $node;
    }
}
