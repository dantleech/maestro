<?php

namespace Maestro\Node\Task;

use Maestro\Node\Task;

class NullTask implements Task
{
    public function description(): string
    {
        return 'being a taskless node';
    }
}
