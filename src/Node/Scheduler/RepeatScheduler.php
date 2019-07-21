<?php

namespace Maestro\Node\Scheduler;

use Maestro\Node\Node;
use Maestro\Node\Schedule;
use Maestro\Node\Scheduler;
use Maestro\Node\Timer;
use Maestro\Node\Timer\ClockTimer;

class RepeatScheduler implements Scheduler
{
    /**
     * @var Timer
     */
    private $timer;

    private $hasRun = false;

    public function __construct(?Timer $timer = null)
    {
        $this->timer = $timer ?: new ClockTimer();
    }

    public function shouldRun(Schedule $schedule, Node $node): bool
    {
        assert($schedule instanceof RepeatSchedule);

        if (false === $this->hasRun) {
            $this->timer->reset();
            $this->hasRun = true;
            return true;
        }

        $shouldRun = $this->timer->elapsed() >= $schedule->delay();

        if ($shouldRun) {
            $this->timer->reset();
        }

        return $shouldRun;
    }

    public function shouldReschedule(Schedule $scheduler, Node $node): bool
    {
        return true;
    }
}
