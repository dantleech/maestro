<?php

namespace Maestro\Tests\Unit\Library\Report;

use Maestro\Library\Report\Report;
use Maestro\Library\Report\ReportRegistry;
use Maestro\Library\Report\Exception\ReportNotFound;
use PHPUnit\Framework\TestCase;

class ReportRegistryTest extends TestCase
{
    public function testGetsReport()
    {
        $report = $this->prophesize(Report::class);
        $registry = new ReportRegistry(['name' => $report->reveal()]);
        $this->assertEquals($report->reveal(), $registry->get('name'));
    }

    public function testThrowsExceptionIfReportIsUnknown()
    {
        $this->expectException(ReportNotFound::class);

        $report = $this->prophesize(Report::class);
        $registry = new ReportRegistry(['name' => $report->reveal()]);
        $this->assertEquals($report->reveal(), $registry->get('foo'));
    }
}
