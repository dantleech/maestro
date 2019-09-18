<?php

namespace Maestro\Extension\Runner\Task;

use Amp\Success;
use Maestro\Library\Support\Environment\Environment;
use Maestro\Library\Support\Variables\Variables;

class InitHandler
{
    public function __invoke(InitTask $task)
    {
        return new Success([
            new Environment($task->manifest()->env()),
            new Variables($task->manifest()->vars()),
            $task->manifest()
        ]);
    }
}
