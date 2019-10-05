<?php

namespace Maestro\Library\Task\Task;

use Amp\Promise;
use Amp\Success;

class NullHandler
{
    public function __invoke(NullTask $task): Promise
    {
        return new Success([]);
    }
}
