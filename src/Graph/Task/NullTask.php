<?php

namespace Maestro\Graph\Task;

use Maestro\Graph\Task;

class NullTask implements Task
{
    public function description(): string
    {
        return 'being a taskless node';
    }
}
