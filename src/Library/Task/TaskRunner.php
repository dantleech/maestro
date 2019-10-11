<?php

namespace Maestro\Library\Task;

use Amp\Promise;
use Maestro\Library\Artifact\Artifacts;

interface TaskRunner
{
    public function run(Task $task, Artifacts $artifacts): Promise;
}
