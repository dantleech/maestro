<?php

namespace Maestro\Extension\Git\Task;

use Maestro\Graph\Task;

class VersionInfoTask implements Task
{
    public function description(): string
    {
        return 'gathering version info';
    }
}
