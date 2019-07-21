<?php

namespace Maestro\Node\Schedule;

use Maestro\Node\Node;
use Maestro\Node\Schedule;
use Maestro\Node\Timer;
use Maestro\Node\Timer\ClockTimer;

class DeferredSchedule implements Schedule
{
    private $timer;

    private $delaySeconds;

    public function __construct(int $delaySeconds, ?Timer $timer = null)
    {
        $this->timer = $timer ?: new ClockTimer();
        $this->delaySeconds = $delaySeconds;
    }

    public function shouldRun(Node $node): bool
    {
        return $this->timer->elapsed() >= $this->delaySeconds;
    }

    public function shouldReschedule(Node $node): bool
    {
        return false;
    }
}
