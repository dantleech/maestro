<?php

namespace Maestro\Node\TaskRunner;

use Amp\Promise;
use Amp\Success;
use Maestro\Node\Artifacts;
use Maestro\Node\Task;
use Maestro\Node\TaskRunner;

class NullTaskRunner implements TaskRunner
{
    public function run(Task $task, Artifacts $artifacts): Promise
    {
        return new Success();
    }
}
