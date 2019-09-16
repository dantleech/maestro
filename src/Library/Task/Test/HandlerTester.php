<?php

namespace Maestro\Library\Task\Test;

use Maestro\Library\GraphTaskRunner\ArtifactContainer;
use Maestro\Library\Instantiator\Instantiator;

class HandlerTester
{
    private $taskHandler;

    private function __construct($handler)
    {
        $this->taskHandler = $handler;
    }

    public static function create($handler)
    {
        return new self($handler);
    }

    public function handle(string $taskFqn, array $args): ArtifactContainer
    {
        $task = Instantiator::create($taskFqn, $args);
        $artifacts = \Amp\Promise\wait(call_user_func_array($this->taskHandler, [$task]));
        return new ArtifactContainer($args);
    }
}
