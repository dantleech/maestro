<?php

namespace Maestro\Console\Progress;

use Maestro\Model\Job\Queues;
use Symfony\Component\Console\Output\OutputInterface;

interface Progress
{
    public function render(Queues $queues): ?string;
}
