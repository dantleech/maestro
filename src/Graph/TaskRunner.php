<?php

namespace Maestro\Graph;

use Amp\Promise;

interface TaskRunner
{
    public function run(Task $task, Environment $environment): Promise;
}
