<?php

namespace Maestro\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Task\Exception\TaskFailed;
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
        $this->artifacts = Artifacts::create([]);
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
        $node = clone $node;
        $node->parent = $this;

        $this->children[] = $node;

        return $node;
    }

    public function child(string $name): Node
    {
        foreach ($this->children as $child) {
            if ($name === $child->name()) {
                return $child;
            }
        }

        throw new RuntimeException(sprintf(
            'Child "%s" not found, known children: "%s"',
            $name,
            implode('", "', array_keys($this->children))
        ));
    }

    public function parent(): ?Node
    {
        return $this->parent;
    }

    /**
     * @return Nodes<Node>
     */
    public function children(): Nodes
    {
        return Nodes::fromNodes(array_values($this->children));
    }

    /**
     * @return Nodes<Node>
     */
    public function selfAndAncestors(): Nodes
    {
        $nodes = [ $this ];
        $current = $this;

        while ($parent = $current->parent()) {
            $nodes[] = $parent;
            $current = $parent;
        }

        return Nodes::fromNodes($nodes);
    }

    public function state(): State
    {
        return $this->state;
    }

    public function run(TaskRunner $taskRunner): Promise
    {
        return \Amp\call(function () use ($taskRunner) {
            $this->state = State::BUSY();

            try {
                $this->setArtifacts(yield $taskRunner->run(
                    $this->task,
                    $this->mergedArtifacts()
                ));
                $this->state = State::IDLE();
            } catch (TaskFailed $failed) {
                $this->state = State::FAILED();
                $this->setArtifacts($failed->artifacts());
            }
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

    public function name(): string
    {
        return $this->name;
    }

    public function task(): Task
    {
        return $this->task;
    }

    private function mergedArtifacts(): Artifacts
    {
        if (null === $this->parent) {
            return Artifacts::create([]);
        }

        return $this->parent->mergedArtifacts()->merge($this->artifacts);
    }

    private function setArtifacts(?Artifacts $artifacts): void
    {
        if (null === $artifacts) {
            return;
        }
        $this->artifacts = $artifacts;
    }

    public function artifacts(): Artifacts
    {
        return $this->artifacts;
    }
}
