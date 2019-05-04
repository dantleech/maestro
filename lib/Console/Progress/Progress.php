<?php

namespace Maestro\Console\Progress;

use Maestro\Model\Job\Queues;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

interface Progress
{
    public function update(Queues $queus, ConsoleOutputInterface $output);
}
