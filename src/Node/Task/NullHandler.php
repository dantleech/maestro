<?php

namespace Maestro\Node\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Node\Environment;
use Maestro\Node\Task;
use Maestro\Node\TaskHandler;

class NullHandler implements TaskHandler
{
    /**
     * @var bool
     */
    private $invoked = false;

    public function execute(Task $task, Environment $environment): Promise
    {
        $this->invoked = true;
        return new Success($environment);
    }

    public function wasInvoked(): bool
    {
        return $this->invoked;
    }
}
