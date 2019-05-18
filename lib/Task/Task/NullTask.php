<?php

namespace Maestro\Task\Task;

use Maestro\Task\Task;

class NullTask implements Task
{
    public function handler(): string
    {
        return NullHandler::class;
    }
}
