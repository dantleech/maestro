<?php

namespace Maestro\Task\Test;

use Maestro\Loader\Instantiator;
use Maestro\Task\Artifacts;
use Maestro\Task\TaskHandler;

final class HandlerTester
{
    /**
     * @var TaskHandler
     */
    private $handler;

    private function __construct(TaskHandler $handler)
    {
        $this->handler = $handler;
    }

    public static function create(TaskHandler $handler): self
    {
        return new self($handler);
    }

    public function handle(string $taskFqn, array $parameters, array $artifacts): ?Artifacts
    {
        $task = Instantiator::create()->instantiate($taskFqn, $parameters);
        return \Amp\Promise\wait(call_user_func($this->handler, $task, Artifacts::create($artifacts)));
    }
}
