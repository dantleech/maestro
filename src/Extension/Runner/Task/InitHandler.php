<?php

namespace Maestro\Extension\Runner\Task;

use Amp\Success;
use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Support\Variables\Variables;
use Maestro\Library\Task\ProvidingTaskHandler;

class InitHandler implements ProvidingTaskHandler
{
    public function provides(): array
    {
        return [
            Environment::class,
            Variables::class,
        ];
    }

    public function __invoke(InitTask $task)
    {
        return new Success([
            new Environment($task->environment()),
            new Variables($task->variables()),
        ]);
    }
}
