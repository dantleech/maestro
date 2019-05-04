<?php

namespace Maestro\Console\Progress;

use Maestro\Model\Job\QueueStatuses;
use Maestro\Model\Job\Queues;

interface Progress
{
    public function update(Queues $queus, ConsoleOutputInterface $output);
}
