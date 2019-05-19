<?php

namespace Maestro\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Task\Task\NullTask;
use RuntimeException;

/**
 * The node represents a task in the task graph.
 *
 * It is responsible for:
 *
 * - Running the task and managing it's state.
 * - Aggregating task artifacts.
 */
final class Node
{
    private $parent;
    private $children = [];
    private $state;
    private $task;
    private $artifacts;
    private $name;

    public function __construct(string $name, ?Task $task = null)
    {
        $this->state = State::WAITING();
        $this->task = $task ?: new NullTask();
        $this->children = [];
        $this->name = $name;
    }

    public static function createRoot(): self
    {
        return new self('root');
    }

    public static function create(string $name, ?Task $task = null): self
    {
        return new self($name, $task);
    }

    public function addChild(Node $node): Node
    {
        $node = clone($node);
        $node->parent = $this;
        $this->children[$node->name()] = $node;

        return $node;
    }

    public function child(string $name): Node
    {
        if (!isset($this->children[$name])) {
            throw new RuntimeException(sprintf(
                'Child "%s" not found, known children: "%s"',
                $name,
                implode('", "', array_keys($this->children))
            ));
        }

        return $this->children[$name];
    }

    public function parent(): ?Node
    {
        return $this->parent;
    }

    public function children(): Nodes
    {
        return Nodes::fromNodes(array_values($this->children));
    }

    public function state(): State
    {
        return $this->state;
    }

    public function run(TaskRunner $taskRunner): Promise
    {
        return \Amp\call(function () use ($taskRunner) {
            $this->state = State::BUSY();
            $this->artifacts = yield $taskRunner->run(
                $this->task,
                $this->mergedArtifacts()
            );
            $this->state = State::IDLE();
            return new Success();
        });
    }

    public function root(): Node
    {
        if (null === $this->parent) {
            return $this;
        }

        return $this->root();
    }

    private function mergedArtifacts(): Artifacts
    {
        if (null === $this->parent) {
            return Artifacts::create([]);
        }

        return $this->parent->artifacts()->merge($this->artifacts);
    }

    private function clone(): self
    {
        $clone = clone $this;
        if ($clone->parent) {
            $clone->parent = $clone->parent->clone();
        }

        foreach ($clone->children as $index => $child) {
            $clone->children[$index] = clone $child;
        }

        return $clone;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function task(): Task
    {
        return $this->task;
    }
}
