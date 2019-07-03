<?php

namespace Maestro\Node;

use Maestro\Node\Task;
use Maestro\Node\TaskHandler;

interface TaskHandlerRegistry
{
    public function getFor(Task $task): TaskHandler;
}
