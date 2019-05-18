<?php

namespace Maestro\Task;

use Amp\Promise;
use Amp\Success;

/**
 * The node represents a task in the task graph.
 *
 * It is responsible for:
 *
 * - Running the task and managing it's state.
 * - Aggregating task artifacts.
 */
class Node
{
    private $parent;
    private $children;
    private $state;
    private $task;
    private $artifacts;

    public function __construct(?Node $parent, Nodes $children, Task $task)
    {
        $this->parent = $parent;
        $this->children = $children;
        $this->state = State::WAITING();
        $this->task = $task;
    }

    public function parent(): Node
    {
        return $this->parent;
    }

    public function children(): Nodes
    {
        return $this->children;
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

    private function mergedArtifacts(): array
    {
        if (null === $this->parent) {
            return [];
        }

        return array_merge($this->parent->artifacts(), $this->artifacts);
    }
}
