<?php

namespace Maestro\Task;

interface TaskHandlerRegistry
{
    public function getFor(Task $task): TaskHandler;
}
