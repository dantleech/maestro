<?php

namespace Maestro\Graph;

use ArrayAccess;
use ArrayIterator;
use BadMethodCallException;
use Closure;
use Countable;
use IteratorAggregate;
use RuntimeException;

final class Nodes implements IteratorAggregate, Countable, ArrayAccess
{
    /**
     * @var Node[]
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
                implode('", "', $this->ids())
            ));
        }

        return $this->nodes[$key];
    }

    /**
     * @return string[]
     */
    public function ids(): array
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

    /**
     * @return Nodes<Node>
     */
    public function byState(State ...$states): Nodes
    {
        return Nodes::fromNodes(array_filter($this->nodes, function (Node $node) use ($states) {
            return $node->state()->in(...$states);
        }));
    }

    public function byTaskResult(TaskResult $taskResult): Nodes
    {
        return Nodes::fromNodes(array_filter($this->nodes, function (Node $node) use ($taskResult) {
            return $node->taskResult()->is($taskResult);
        }));
    }

    private function addNode(Node $node): void
    {
        $this->nodes[$node->id()] = $node;
    }

    public function byStates(State ...$states): Nodes
    {
        return $this->filter(function (Node $node) use ($states) {
            return $node->state()->in(...$states);
        });
    }

    public function byIds(array $ids): Nodes
    {
        return $this->filter(function (Node $node) use ($ids) {
            return in_array($node->id(), $ids, true);
        });
    }

    public function filter(Closure $predicate): Nodes
    {
        return Nodes::fromNodes(array_filter($this->nodes, $predicate));
    }

    public function containsId(string $id): bool
    {
        return array_key_exists($id, $this->nodes);
    }

    public function query(string $query): Nodes
    {
        $nodes = [];
        if (empty($query)) {
            return Nodes::empty();
        }
        $pattern = sprintf('{^%s$}', str_replace('\*', '.*', preg_quote($query)));

        foreach ($this->nodes as $id => $node) {
            if (!preg_match($pattern, $id)) {
                continue;
            }

            $nodes[$id] = $node;
        }

        return Nodes::fromNodes($nodes);
    }

    public function allDone(): bool
    {
        foreach ($this->nodes as $node) {
            if ($node->state()->isScheduled() || $node->state()->isBusy() || $node->state()->isWaiting()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return Nodes<Node>
     */
    public function reverse(): Nodes
    {
        return new self(array_reverse($this->nodes));
    }
}
