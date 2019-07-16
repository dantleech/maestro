<?php

namespace Maestro\Node\TaskRunner;

use Amp\Promise;
use Amp\Success;
use Maestro\Node\Task;
use Maestro\Node\TaskContext;
use Maestro\Node\TaskRunner;

class NullTaskRunner implements TaskRunner
{
    public function run(Task $task, TaskContext $context): Promise
    {
        return new Success();
    }
}
