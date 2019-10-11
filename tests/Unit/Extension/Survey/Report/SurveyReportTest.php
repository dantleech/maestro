<?php

namespace Maestro\Tests\Unit\Extension\Survey\Report;

use Maestro\Extension\Survey\Report\SurveyReport;
use Maestro\Extension\Survey\Task\SurveyTask;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\GraphBuilder;
use Maestro\Library\Graph\Node;
use Maestro\Library\Survey\Survey;
use Maestro\Library\Survey\SurveyResult;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class SurveyReportTest extends TestCase
{
    /**
     * @var SurveyReport
     */
    private $report;
    /**
     * @var BufferedOutput
     */
    private $output;

    protected function setUp(): void
    {
        $this->output = new BufferedOutput();
        $this->report = new SurveyReport($this->output);
    }

    public function testRendersNothingIfNoNodesHaveSurveyTask()
    {
        $graph = GraphBuilder::create()
            ->addNode(Node::create('foo1'))
            ->addNode(Node::create('foo2'))
            ->addNode(Node::create('foo3'))
            ->build();

        $this->assertEmpty($this->render($graph));
    }

    public function testIgnoresNodesWithNoSurveyResult()
    {
        $surveyResult = $this->prophesize(SurveyResult::class);
        $graph = GraphBuilder::create()
            ->addNode(Node::create('foo1', [
                'task' => new SurveyTask()
            ]))
            ->addNode(Node::create('foo2', [
                'task' => new SurveyTask(),
                'artifacts' => [
                    new Survey([$surveyResult->reveal()])
                ]
            ]))
            ->addNode(Node::create('foo3'))
            ->build();

        $output = $this->render($graph);
        $this->assertStringNotContainsString('foo1', $output);
        $this->assertStringContainsString('foo2', $output);
    }

    private function render(Graph $graph)
    {
        $this->report->render($graph);
        return $this->output->fetch();
    }
}
