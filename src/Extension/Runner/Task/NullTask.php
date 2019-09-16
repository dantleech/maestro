<?php

namespace Maestro\Extension\Runner\Task;

use Maestro\Library\Task\Task;

class NullTask implements Task
{
    public function description(): string
    {
        return 'doing nothing';
    }
}
