<?php

namespace Maestro\Extension\Git\Task;

use Maestro\Graph\Task;

class GitTagTask implements Task
{
    public function description(): string
    {
        return 'applying tag';
    }
}
