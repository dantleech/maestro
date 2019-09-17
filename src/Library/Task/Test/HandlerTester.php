<?php

namespace Maestro\Library\Task\Test;

use Maestro\Library\GraphTask\Artifacts;
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

    public function handle(string $taskFqn, array $args, array $artifacts = []): Artifacts
    {
        $task = Instantiator::instantiate($taskFqn, $args);
        $artifacts = \Amp\Promise\wait(Instantiator::call(
            $this->taskHandler,
            '__invoke',
            array_merge([
                $task,
            ], array_values($artifacts)),
            Instantiator::MODE_TYPE
        ));
        return new Artifacts($artifacts);
    }
}
