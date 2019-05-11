<?php

namespace Maestro\Tests\Unit\Console\Report;

use Maestro\Console\Report\Exception\ReportNotFound;
use Maestro\Console\Report\QueueReport;
use Maestro\Console\Report\ReportRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class ReportRegistryTest extends TestCase
{
    /**
     * @var ObjectProphecy|QueueReport
     */
    private $report1;

    protected function setUp(): void
    {
        $this->report1 = $this->prophesize(QueueReport::class);
    }

    public function testThrowsExceptionIfReportNotFound()
    {
        $this->expectException(ReportNotFound::class);

        $this->create([
            'one' => $this->report1->reveal()
        ])->get('foobar');
    }

    public function testReturnsReport()
    {
        $report = $this->create([
            'one' => $this->report1->reveal()
        ])->get('one');

        $this->assertSame($this->report1->reveal(), $report);
    }

    private function create(array $map): ReportRegistry
    {
        return new ReportRegistry($map);
    }
}
