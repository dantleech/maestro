<?php

namespace Maestro\Task;

use Amp\Promise;

interface TaskRunner
{
    public function run(Task $task, Artifacts $artifacts): Promise;
}
