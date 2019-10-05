<?php

namespace Maestro\Tests\Unit\Extension\Report\Model;

use Maestro\Extension\Report\Model\ConsoleReport;
use Maestro\Extension\Report\Model\ConsoleReportRegistry;
use Maestro\Extension\Report\Model\Exception\ReportNotFound;
use PHPUnit\Framework\TestCase;

class ConsoleReportRegistryTest extends TestCase
{
    public function testGetsReport()
    {
        $report = $this->prophesize(ConsoleReport::class);
        $registry = new ConsoleReportRegistry(['name' => $report->reveal()]);
        $this->assertEquals($report->reveal(), $registry->get('name'));
    }

    public function testThrowsExceptionIfReportIsUnknown()
    {
        $this->expectException(ReportNotFound::class);

        $report = $this->prophesize(ConsoleReport::class);
        $registry = new ConsoleReportRegistry(['name' => $report->reveal()]);
        $this->assertEquals($report->reveal(), $registry->get('foo'));
    }
}
