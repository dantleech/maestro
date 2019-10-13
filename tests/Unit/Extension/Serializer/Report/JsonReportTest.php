<?php

namespace Maestro\Tests\Unit\Extension\Serializer\Report;

use Maestro\Extension\Serializer\Report\JsonReport;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\GraphBuilder;
use Maestro\Tests\IntegrationTestCase;
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
        $this->output = new BufferedOutput();
        $this->serializer = $this->prophesize(SerializerInterface::class);
        $this->report = new JsonReport($this->serializer->reveal(), $this->output);
    }

    public function testWritesJsonReport()
    {
        $graph = GraphBuilder::create()
            ->build();

        $this->serializer->serialize($graph, 'json')->willReturn('hello');
        $this->assertStringContainsString('hello', $this->render($graph));
    }

    private function render(Graph $graph)
    {
        $this->report->render($graph);
        return $this->output->fetch();
    }
}
