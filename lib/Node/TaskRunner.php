<?php

namespace Maestro\Node;

use Amp\Promise;
use Maestro\Node\Artifacts;
use Maestro\Node\Task;

interface TaskRunner
{
    public function run(Task $task, Artifacts $artifacts): Promise;
}
