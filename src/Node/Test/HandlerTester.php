<?php

namespace Maestro\Node\Test;

use Maestro\Loader\Instantiator;
use Maestro\Node\Environment;
use Maestro\Node\TaskHandler;

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

    public function handle(string $taskFqn, array $parameters, array $environment): ?Environment
    {
        $task = Instantiator::create()->instantiate($taskFqn, $parameters);

        return \Amp\Promise\wait($this->handler->execute($task, Environment::create($environment)));
    }
}
