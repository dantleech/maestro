<?php

namespace Maestro\Graph\Test;

use Maestro\Loader\Instantiator;
use Maestro\Graph\Environment;
use Maestro\Graph\TaskHandler;
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

    public function handle(string $taskFqn, array $args, array $environment): Environment
    {
        $task = Instantiator::create()->instantiate($taskFqn, $args);

        $environment = \Amp\Promise\wait($this->handler->execute($task, Environment::create($environment)));

        if (!$environment) {
            throw new RuntimeException(sprintf('Promise from handler %s did not resolve to an Environment', get_class($this->handler)));
        }

        return $environment;
    }
}
