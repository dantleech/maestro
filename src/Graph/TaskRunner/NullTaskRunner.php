<?php

namespace Maestro\Graph\TaskRunner;

use Amp\Promise;
use Amp\Success;
use Maestro\Graph\Environment;
use Maestro\Graph\Task;
use Maestro\Graph\TaskRunner;

class NullTaskRunner implements TaskRunner
{
    public function run(Task $task, Environment $environment): Promise
    {
        return new Success($environment);
    }
}
