<?php

namespace Maestro\Graph;

interface TaskHandlerRegistry
{
    public function getFor(Task $task): TaskHandler;
}
