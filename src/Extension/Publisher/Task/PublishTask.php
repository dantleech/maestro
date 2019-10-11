<?php

namespace Maestro\Extension\Publisher\Task;

use Maestro\Library\Task\Task;

class PublishTask implements Task
{
    public function description(): string
    {
        return 'publishing';
    }
}
