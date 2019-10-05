<?php

namespace Maestro\Extension\Runner\Task;

use Maestro\Extension\Runner\Model\Loader\Manifest;
use Maestro\Library\Task\Task;

class InitTask implements Task
{
    public function description(): string
    {
        return 'initializing';
    }
}
