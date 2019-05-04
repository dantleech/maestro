<?php

namespace Maestro\Console\Progress;

use Maestro\Model\Job\Queues;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SilentProgress implements Progress
{
    public function render(Queues $queus): ?string
    {
        return null;
    }
}
