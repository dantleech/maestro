<?php

namespace Maestro\Extension\Vcs\Task;

use Maestro\Library\Task\Task;

class TagVersionTask implements Task
{
    public function description(): string
    {
        return 'applying tag';
    }
}
