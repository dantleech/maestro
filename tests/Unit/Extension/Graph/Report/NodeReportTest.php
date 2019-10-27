<?php

namespace Maestro\Tests\Unit\Extension\Graph\Report;

use Maestro\Extension\Graph\Report\NodeReport;
use Maestro\Extension\Survey\Report\SurveyReport;
use Maestro\Extension\Survey\Task\SurveyTask;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Graph\GraphBuilder;
use Maestro\Library\Graph\Node;
use Maestro\Library\Artifact\Artifact;
use Maestro\Tests\IntegrationTestCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Serializer\SerializerInterface;

class NodeReportTest extends IntegrationTestCase
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
        $this->report = new NodeReport(
            $this->output,
            $this->container()->get(SerializerInterface::class)
        );
    }

    public function testRendersNodeInformation()
    {
        $graph = GraphBuilder::create()
            ->addNode(Node::create('foo1'))
            ->addNode(Node::create('foo2'))
            ->addNode(Node::create('foo3'))
            ->build();

        $this->assertStringContainsString($this->output->fetch(), 'foo1');
    }
}
