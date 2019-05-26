<?php

namespace Maestro\Task;

class Dispatcher
{
    /**
     * @var TaskRunner
     */
    private $runner;

    public function __construct(TaskRunner $runner)
    {
        $this->runner = $runner;
    }

    public function dispatch(Queue $queue): void
    {
        while ($node = $queue->dequeue()) {
            assert($node instanceof Node);
            \Amp\asyncCall(function () use ($node) {
                yield $node->run($this->runner);
            });
        }
    }
}
