<?php

namespace Maestro\Console\Report;

use Maestro\Model\Job\QueueStatuses;

interface QueueReport
{
    public function render(QueueStatuses $statuses);
}
