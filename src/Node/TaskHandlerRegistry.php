<?php

namespace Maestro\Node;

interface TaskHandlerRegistry
{
    public function getFor(Task $task): TaskHandler;
}
