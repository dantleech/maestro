<?php

namespace Maestro\Node;

use Amp\Promise;

interface TaskRunner
{
    public function run(Task $task, Artifacts $artifacts): Promise;
}
