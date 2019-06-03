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
        foreach ($nodes as $node) {
            $this->addNode($node);
        }
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
        return new self(array_merge($this->nodes, [
            $node->id() => $node
        ]));
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->nodes);
    }

    public function get(string $key): Node
    {
        if (!isset($this->nodes[$key])) {
            throw new RuntimeException(sprintf(
                'No node exists at offset "%s" in set "%s"',
                $key,
                implode('", "', $this->names())
            ));
        }

        return $this->nodes[$key];
    }

    /**
     * @return string[]
     */
    public function names(): array
    {
        return array_values(array_map(function (Node $node) {
            return $node->id();
        }, $this->nodes));
    }

    public function merge(Nodes $nodes): Nodes
    {
        return Nodes::fromNodes(array_merge($this->nodes, $nodes->nodes));
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

    public function byState(State ...$states): Nodes
    {
        return Nodes::fromNodes(array_filter($this->nodes, function (Node $node) use ($states) {
            return $node->state()->in(...$states);
        }));
    }

    private function addNode(Node $node): void
    {
        $this->nodes[$node->id()] = $node;
    }

    public function byStates(State ...$states): Nodes
    {
        return Nodes::fromNodes(array_filter($this->nodes, function (Node $node) use ($states) {
            return $node->state()->in(...$states);
        }));
    }

    public function containsId(string $id): bool
    {
        return array_key_exists($id, $this->nodes);
    }
}
