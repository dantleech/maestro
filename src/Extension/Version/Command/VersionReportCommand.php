<?php

namespace Maestro\Extension\Version\Command;

use Maestro\Extension\Maestro\Command\Behavior\GraphBehavior;
use Maestro\Extension\Survey\Console\VersionReport;
use Maestro\Extension\Survey\Task\SurveyTask;
use Maestro\Graph\Edge;
use Maestro\Graph\Node;
use Maestro\Graph\SystemTags;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VersionReportCommand extends Command
{
    /**
     * @var GraphBehavior
     */
    private $graphBehavior;

    /**
     * @var VersionReport
     */
    private $report;

    public function __construct(GraphBehavior $graphBehavior, VersionReport $report)
    {
        $this->graphBehavior = $graphBehavior;
        $this->report = $report;
        parent::__construct();
    }

    protected function configure()
    {
        $this->graphBehavior->configure($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $graph = $this->graphBehavior->buildGraph($input)
                      ->pruneForTags(SystemTags::TAG_INITIALIZE);
        $builder = $graph->builder();

        foreach ($graph->leafs() as $leaf) {
            $builder->addNode(Node::create($leaf->id(). '/survey', [
                'label' => 'survey',
                'task' => new SurveyTask(),
                'tags' => ['survey'],
            ]));
            $builder->addEdge(Edge::create($leaf->id(). '/survey', $leaf->id()));
        }

        $graph = $builder->build();

        $this->graphBehavior->run($input, $output, $graph);
        $this->report->render($output, $graph);
    }
}
