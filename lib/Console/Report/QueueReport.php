<?php

namespace Maestro\Console\Report;

use Maestro\Model\Job\QueueStatuses;
use Symfony\Component\Console\Output\OutputInterface;

interface QueueReport
{
    public function render(OutputInterface $output, QueueStatuses $statuses);
}
