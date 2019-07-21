<?php

namespace Maestro\Tests\Unit\Node\Scheduler;

use Maestro\Node\Node;
use Maestro\Node\Scheduler\RepeatSchedule;
use Maestro\Node\Scheduler\RepeatScheduler;
use Maestro\Node\Timer\StaticTimer;
use PHPUnit\Framework\TestCase;

class RepeatSchedulerTest extends TestCase
{
    public function testRunsImmediatelyOnFirstIteration()
    {
        $schedule = new RepeatScheduler(StaticTimer::hasElapsed(5));
        $this->assertTrue($schedule->shouldRun(new RepeatSchedule(10), Node::create('1')));
    }

    public function testDoesNotRunIfElaspedTimeLessThanDelayTime()
    {
        $schedule = new RepeatScheduler(StaticTimer::hasElapsed(5));
        $this->assertTrue($schedule->shouldRun(new RepeatSchedule(10), Node::create('1')));
        $this->assertFalse($schedule->shouldRun(new RepeatSchedule(10), Node::create('1')));
    }

    public function testRunsIfElapsedTimeMoreThanDelayTime()
    {
        $schedule = new RepeatScheduler(StaticTimer::hasElapsed(10));
        $this->assertTrue($schedule->shouldRun(new RepeatSchedule(8), Node::create('1')));
    }

    public function testRunsIfElapsedTimeSameAsDelayTime()
    {
        $schedule = new RepeatScheduler(StaticTimer::hasElapsed(10));
        $this->assertTrue($schedule->shouldRun(new RepeatSchedule(10), Node::create('1')));
    }
}
