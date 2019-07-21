<?php

namespace Maestro\Node\Schedule;

use Maestro\Node\Node;
use Maestro\Node\Schedule;
use Maestro\Node\Timer;
use Maestro\Node\Timer\ClockTimer;

class RepeatSchedule implements Schedule
{
    /**
     * @var int
     */
    private $delaySeconds;

    /**
     * @var Timer
     */
    private $timer;

    private $hasRun = false;

    public function __construct(int $time, ?Timer $timer = null)
    {
        $this->delaySeconds = $time;
        $this->timer = $timer ?: new ClockTimer();
    }

    public function shouldRun(Node $node): bool
    {
        if (false === $this->hasRun) {
            $this->timer->reset();
            $this->hasRun = true;
            return true;
        }

        $shouldRun = $this->timer->elapsed() >= $this->delaySeconds;

        if ($shouldRun) {
            $this->timer->reset();
        }

        return $shouldRun;
    }

    public function shouldReschedule(Node $node): bool
    {
        return true;
    }
}
