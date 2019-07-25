<?php

namespace Maestro\Graph\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Graph\Environment;
use Maestro\Graph\Task;
use Maestro\Graph\TaskHandler;

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
