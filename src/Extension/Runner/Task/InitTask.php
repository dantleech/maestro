<?php

namespace Maestro\Extension\Runner\Task;

use Maestro\Library\Task\Task;

class InitTask implements Task
{
    public function description(): string
    {
        return 'initializing';
    }
}
