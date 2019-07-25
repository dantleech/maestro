<?php

namespace Maestro\Tests\Unit\Graph\Scheduler;

use Maestro\Graph\Node;
use Maestro\Graph\Scheduler\RepeatSchedule;
use Maestro\Graph\Scheduler\RepeatScheduler;
use Maestro\Graph\Timer\StaticTimer;
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
