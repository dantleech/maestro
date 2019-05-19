<?php

namespace Maestro\Task\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Task\Task;
use Maestro\Task\TaskHandler;

class NullHandler implements TaskHandler
{
    /**
     * @var bool
     */
    private $invoked = false;

    public function __invoke(Task $task): Promise
    {
        $this->invoked = true;
        return new Success();
    }

    public function wasInvoked(): bool
    {
        return $this->invoked;
    }
}
