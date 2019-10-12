<?php

namespace Maestro\Tests\Unit\Extension\Runner\Report;

use Maestro\Extension\Runner\Report\JsonReport;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\GraphBuilder;
use Maestro\Tests\IntegrationTestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Serializer\SerializerInterface;

class JsonReportTest extends IntegrationTestCase
{
    /**
     * @var BufferedOutput
     */
    private $output;

    /**
     * @var JsonReport
     */
    private $report;

    /**
     * @var ObjectProphecy
     */
    private $serializer;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->output = $this->prophesize(LoggerInterface::class);
        $this->serializer = $this->prophesize(SerializerInterface::class);
        $this->report = new JsonReport(
            $this->serializer->reveal(),
            $this->workspace()->path('/'),
            $this->output->reveal()
        );
    }

    public function testWritesJsonReport()
    {
        $graph = GraphBuilder::create()
            ->build();

        $this->output->notice(Argument::containingString('Writing JSON report to'))->shouldBeCalled();
        $this->serializer->serialize($graph, 'json')->willReturn('foo');

        $this->render($graph);

        $this->assertFileExists($this->workspace()->path('graph-report.json'));
    }

    private function render(Graph $graph)
    {
        $this->report->render($graph);
    }
}
