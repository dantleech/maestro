<?php

namespace Maestro\Task;

class Dispatcher
{
    /**
     * @var TaskRunner
     */
    private $runner;
    /**
     * @var int
     */
    private $concurrency;

    public function __construct(TaskRunner $runner, int $concurrency)
    {
        $this->runner = $runner;
        $this->concurrency = $concurrency;
    }

    public function dispatch(Queue $queue): void
    {
        while ($node = $queue->dequeue()) {
            assert($node instanceof Node);
            \Amp\call(function () use ($node) {
                yield $node->run($this->runner);
            });
        }
    }
}
