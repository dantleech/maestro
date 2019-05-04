<?php

namespace Maestro\Console\Progress;

use Maestro\Model\Job\Queues;

class SilentProgress implements Progress
{
    public function render(Queues $queus): ?string
    {
        return null;
    }
}
