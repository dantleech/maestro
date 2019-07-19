<?php

namespace Maestro\Node;

use Amp\Promise;

interface TaskRunner
{
    public function run(Task $task, Environment $environment): Promise;
}
