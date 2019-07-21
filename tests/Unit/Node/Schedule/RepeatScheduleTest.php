<?php

namespace Maestro\Tests\Unit\Node\Schedule;

use Maestro\Node\Node;
use Maestro\Node\Schedule\RepeatSchedule;
use Maestro\Node\Timer\StaticTimer;
use PHPUnit\Framework\TestCase;

class RepeatScheduleTest extends TestCase
{
    public function testRunsImmediatelyOnFirstIteration()
    {
        $schedule = new RepeatSchedule(10, StaticTimer::hasElapsed(5));
        $this->assertTrue($schedule->shouldRun(Node::create('1')));
    }

    public function testDoesNotRunIfElaspedTimeLessThanDelayTime()
    {
        $schedule = new RepeatSchedule(10, StaticTimer::hasElapsed(5));
        $this->assertTrue($schedule->shouldRun(Node::create('1')));
        $this->assertFalse($schedule->shouldRun(Node::create('1')));
    }

    public function testRunsIfElapsedTimeMoreThanDelayTime()
    {
        $schedule = new RepeatSchedule(8, StaticTimer::hasElapsed(10));
        $this->assertTrue($schedule->shouldRun(Node::create('1')));
    }

    public function testRunsIfElapsedTimeSameAsDelayTime()
    {
        $schedule = new RepeatSchedule(10, StaticTimer::hasElapsed(10));
        $this->assertTrue($schedule->shouldRun(Node::create('1')));
    }
}
