<?php

namespace Maestro\Node\TaskRunner;

use Amp\Promise;
use Amp\Success;
use Maestro\Node\Environment;
use Maestro\Node\Task;
use Maestro\Node\TaskRunner;

class NullTaskRunner implements TaskRunner
{
    public function run(Task $task, Environment $environment): Promise
    {
        return new Success($environment);
    }
}
