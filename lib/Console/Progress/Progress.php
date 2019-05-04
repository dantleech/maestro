<?php

namespace Maestro\Console\Progress;

use Maestro\Model\Job\Queues;

interface Progress
{
    public function render(Queues $queues): ?string;
}
