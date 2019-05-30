<?php

namespace Maestro\Task;

use ArrayAccess;
use ArrayIterator;
use BadMethodCallException;
use Countable;
use IteratorAggregate;
use RuntimeException;

final class Nodes implements IteratorAggregate, Countable, ArrayAccess
{
    /**
     * @var array
     */
    private $nodes = [];

    private function __construct(array $nodes)
    {
        $this->nodes = $nodes;
    }

    public static function fromNodes(array $nodes): self
    {
        return new self($nodes);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function add(Node $node): Nodes
    {
        return new self(array_merge($this->nodes, [$node]));
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->nodes);
    }

    public function get(int $offset): Node
    {
        if (!isset($this->nodes[$offset])) {
            throw new RuntimeException(sprintf(
                'No node exists at offset "%s" for nodes with size "%s"',
                $offset, count($this->nodes)
            ));
        }
        return $this->nodes[$offset];
    }

    /**
     * @return string[]
     */
    public function names(): array
    {
        return array_map(function (Node $node) {
            return $node->name();
        }, $this->nodes);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->nodes);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->nodes[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return $this->nodes[$offset];
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        throw new BadMethodCallException();
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        throw new BadMethodCallException();
    }
}
