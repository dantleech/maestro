<?php

namespace Maestro\Tests\Unit\Extension\Runner\Report;

use Maestro\Extension\Runner\Report\JsonReport;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\GraphBuilder;
use Maestro\Library\Report\GraphSerializer;
use Maestro\Tests\IntegrationTestCase;
use Symfony\Component\Console\Output\BufferedOutput;

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
        $this->output = new BufferedOutput();
        $this->serializer = $this->prophesize(GraphSerializer::class);
        $this->report = new JsonReport($this->output, $this->serializer->reveal(), $this->workspace()->path('/'));
    }

    public function testWritesJsonReport()
    {
        $graph = GraphBuilder::create()
            ->build();

        $this->serializer->serialize($graph)->willReturn([]);
        $this->assertStringContainsString('Writing JSON report to', $this->render($graph));
        $this->assertFileExists($this->workspace()->path('graph-report.json'));
    }

    private function render(Graph $graph)
    {
        $this->report->render($graph);
        return $this->output->fetch();
    }
}
