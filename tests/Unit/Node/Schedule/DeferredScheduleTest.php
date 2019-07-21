<?php

namespace Maestro\Tests\Unit\Node\Schedule;

use Maestro\Node\Node;
use Maestro\Node\Schedule\DeferredSchedule;
use Maestro\Node\Timer\StaticTimer;
use PHPUnit\Framework\TestCase;

class DeferredScheduleTest extends TestCase
{
    public function testDoesNotRunIfElaspedTimeLessThanDelayTime()
    {
        $schedule = new DeferredSchedule(10, StaticTimer::hasElapsed(5));
        $this->assertFalse($schedule->shouldRun(Node::create('1')));
    }

    public function testRunsIfElapsedTimeMoreThanDelayTime()
    {
        $schedule = new DeferredSchedule(8, StaticTimer::hasElapsed(10));
        $this->assertTrue($schedule->shouldRun(Node::create('1')));
    }

    public function testRunsIfElapsedTimeSameAsDelayTime()
    {
        $schedule = new DeferredSchedule(10, StaticTimer::hasElapsed(10));
        $this->assertTrue($schedule->shouldRun(Node::create('1')));
    }
}
