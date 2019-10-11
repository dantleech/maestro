<?php

namespace Maestro\Extension\Task\Extension;

use ArrayIterator;
use Iterator;
use IteratorAggregate;
use RuntimeException;

class TaskHandlerDefinitionMap implements IteratorAggregate
{
    /**
     * @var array
     */
    private $definitions = [];

    /**
     * @var array
     */
    private $definitionsByClass = [];


    public function __construct(array $definitions)
    {
        foreach ($definitions as $definition) {
            $this->add($definition);
        }
    }

    public function getDefinitionByAlias(string $alias): TaskHandlerDefinition
    {
        if (!isset($this->definitions[$alias])) {
            throw new RuntimeException(sprintf(
                'Unknown task definitinon "%s", known definitions: "%s"',
                $alias,
                implode('", "', array_map(function (TaskHandlerDefinition $definition) {
                    return $definition->alias();
                }, $this->definitions))
            ));
        }

        return $this->definitions[$alias];
    }

    public function getDefinitionByClass(string $taskClass): TaskHandlerDefinition
    {
        if (!isset($this->definitionsByClass[$taskClass])) {
            throw new RuntimeException(sprintf(
                'Unknown task definitinon "%s", known definitions: "%s"',
                $taskClass,
                implode('", "', array_map(function (TaskHandlerDefinition $definition) {
                    return $definition->taskClass();
                }, $this->definitionsByClass))
            ));
        }

        return $this->definitionsByClass[$taskClass];
    }

    private function add(TaskHandlerDefinition $definition): void
    {
        $this->definitions[$definition->alias()] = $definition;
        $this->definitionsByClass[$definition->taskClass()] = $definition;
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->definitions);
    }

    public function sorted(): self
    {
        $definitions = $this->definitions;
        usort($definitions, function ($one, $two) {
            return $one->alias() <=> $two->alias();
        });
        return new self($definitions);
    }

    public function aliases(): array
    {
        return array_values(array_map(function (TaskHandlerDefinition $definition) {
            return $definition->alias();
        }, $this->definitions));
    }
}
