<?php

namespace Maestro\Tests\Unit\Extension\Dot\Report;

use Maestro\Extension\Dot\Report\DotReport;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\GraphBuilder;
use Maestro\Library\Graph\Node;
use Maestro\Tests\IntegrationTestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class DotReportTest extends IntegrationTestCase
{
    /**
     * @var DotReport
     */
    private $report;
    /**
     * @var BufferedOutput
     */
    private $output;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->output = new BufferedOutput();
        $this->report = new DotReport($this->workspace()->path('/'), $this->output);
    }

    public function testWritesDotFileToFile()
    {
        $graph = GraphBuilder::create()
            ->addNode(Node::create('foo1'))
            ->addNode(Node::create('foo2'))
            ->addNode(Node::create('foo3'))
            ->build();

        $this->assertStringContainsString('Writing dot file to', $this->render($graph));
        $this->assertFileExists($this->workspace()->path('maestro.dot'));
    }

    private function render(Graph $graph)
    {
        $this->report->render($graph);
        return $this->output->fetch();
    }
}
