<?php

namespace Maestro\Graph;

use Amp\Promise;

interface TaskHandler
{
    public function execute(Task $task, Environment $environment): Promise;
}
