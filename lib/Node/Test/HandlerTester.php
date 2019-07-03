<?php

namespace Maestro\Node\Test;

use Maestro\Loader\Instantiator;
use Maestro\Node\Artifacts;
use Maestro\Node\TaskHandler;
use RuntimeException;

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

        if (!is_callable($this->handler)) {
            throw new RuntimeException(sprintf('Handler "%s" must be callable', get_class($this->handler)));
        }
        return \Amp\Promise\wait(call_user_func($this->handler, $task, Artifacts::create($artifacts)));
    }
}
