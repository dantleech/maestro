<?php

namespace Maestro\Node;

use Amp\Promise;

interface TaskHandler
{
    public function execute(Task $task, Artifacts $artifacts): Promise;
}
