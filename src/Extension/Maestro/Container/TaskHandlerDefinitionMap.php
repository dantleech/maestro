<?php

namespace Maestro\Extension\Maestro\Container;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

class TaskHandlerDefinitionMap implements IteratorAggregate
{
    private $definitions;

    public function __construct(array $definitions)
    {
        foreach ($definitions as $definition) {
            $this->add($definition);
        }
    }

    private function add(TaskHandlerDefinition $definition): void
    {
        $this->definitions[] = $definition;
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->definitions);
    }
}
