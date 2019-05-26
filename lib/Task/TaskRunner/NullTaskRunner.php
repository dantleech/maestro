<?php

namespace Maestro\Task\TaskRunner;

use Amp\Promise;
use Amp\Success;
use Maestro\Task\Artifacts;
use Maestro\Task\Task;
use Maestro\Task\TaskRunner;

class NullTaskRunner implements TaskRunner
{
    public function run(Task $task, Artifacts $artifacts): Promise
    {
        return new Success();
    }
}
