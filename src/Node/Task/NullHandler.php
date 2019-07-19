<?php

namespace Maestro\Node\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Node\Artifacts;
use Maestro\Node\Task;
use Maestro\Node\TaskHandler;

class NullHandler implements TaskHandler
{
    /**
     * @var bool
     */
    private $invoked = false;

    public function execute(Task $task, Artifacts $artifacts): Promise
    {
        $this->invoked = true;
        return new Success();
    }

    public function wasInvoked(): bool
    {
        return $this->invoked;
    }
}
