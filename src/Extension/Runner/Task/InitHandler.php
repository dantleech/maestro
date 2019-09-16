<?php

namespace Maestro\Extension\Runner\Task;

use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Instantiator\Instantiator;
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
        return [
            Instantiator::instantiate(Environment::class, $task->environment()),
            Instantiator::instantiate(Variables::class, $task->variables()),
        ];
    }
}
