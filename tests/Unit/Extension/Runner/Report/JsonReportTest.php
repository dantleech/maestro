<?php

namespace Maestro\Tests\Unit\Extension\Runner\Report;

use Maestro\Extension\Runner\Report\JsonReport;
use Maestro\Extension\Task\Extension\TaskHandlerDefinition;
use Maestro\Extension\Task\Extension\TaskHandlerDefinitionMap;
use Maestro\Library\Graph\Edge;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\GraphBuilder;
use Maestro\Library\Graph\Node;
use Maestro\Library\Task\Task\NullTask;
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
     * @var TaskHandlerDefinitionMap
     */
    private $definitionMap;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->output = new BufferedOutput();
        $this->definitionMap = new TaskHandlerDefinitionMap([
            new TaskHandlerDefinition('foo', 'null', NullTask::class),
        ]);
        $this->report = new JsonReport($this->output, $this->definitionMap, $this->workspace()->path('/'));
    }

    public function testWritesJsonReport()
    {
        $graph = GraphBuilder::create()
            ->addEdge(Edge::create('foo3', 'foo2'))
            ->addNode(Node::create('foo1'))
            ->addNode(Node::create('foo2'))
            ->addNode(Node::create('foo3'))
            ->build();

        $this->assertStringContainsString('Writing JSON report to', $this->render($graph));
        $this->assertFileExists($this->workspace()->path('graph-report.json'));
    }

    private function render(Graph $graph)
    {
        $this->report->render($graph);
        return $this->output->fetch();
    }
}
