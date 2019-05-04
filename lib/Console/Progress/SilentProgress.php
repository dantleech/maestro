<?php

namespace Maestro\Console\Progress;

use Maestro\Model\Job\Queues;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class SilentProgress implements Progress
{
    public function update(Queues $queus, ConsoleOutputInterface $output)
    {
    }
}
