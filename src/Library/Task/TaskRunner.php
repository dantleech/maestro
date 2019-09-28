<?php

namespace Maestro\Library\Task;

use Amp\Promise;

interface TaskRunner
{
    public function run(Task $task, Artifacts $artifacts): Promise;
}
