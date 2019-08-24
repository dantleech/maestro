<?php

namespace Maestro\Extension\Git\Command;

use Maestro\Extension\Version\Console\VersionReport;
use Maestro\Extension\Git\Task\TagVersionTask;
use Maestro\Extension\Maestro\Command\Behavior\GraphBehavior;
use Maestro\Extension\Survey\Task\SurveyTask;
use Maestro\Graph\Edge;
use Maestro\Graph\Node;
use Maestro\Graph\SystemTags;
use Maestro\Graph\TaskResult;
use Maestro\Util\Cast;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TagVersionCommand extends Command
{
    const OPT_DRY_RUN = 'dry-run';

    /**
     * @var GraphBehavior
     */
    private $graphBehavior;

    /**
     * @var VersionReport
     */
    private $versionReport;

    public function __construct(GraphBehavior $graphBehavior, VersionReport $versionReport)
    {
        $this->graphBehavior = $graphBehavior;
        parent::__construct();
        $this->versionReport = $versionReport;
    }

    protected function configure()
    {
        $this->setDescription('Tag configured version and show report (or just show report with dry-run)');
        $this->graphBehavior->configure($this);
        $this->addOption(self::OPT_DRY_RUN, null, InputOption::VALUE_NONE, 'Do not perform the tag, just show the report');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $graph = $this->graphBehavior->buildGraph($input)
                      ->pruneForTags(SystemTags::TAG_INITIALIZE);
        $builder = $graph->builder();

        foreach ($graph->leafs() as $leaf) {
            $parentId = $leaf->id();

            if (false === Cast::toBool($input->getOption(self::OPT_DRY_RUN))) {
                $scriptNodeId = sprintf($leaf->id() . '/git tag');
                $builder->addNode(Node::create($scriptNodeId, [
                    'label' => 'git tag',
                    'task' => new TagVersionTask()
                ]));
                $builder->addEdge(Edge::create($scriptNodeId, $leaf->id()));
                $parentId = $scriptNodeId;
            }

            $builder->addNode(Node::create($parentId. '/info', [
                'label' => 'git version info',
                'task' => new SurveyTask(),
                'tags' => ['survey'],
            ]));
            $builder->addEdge(Edge::create($parentId. '/info', $parentId));
        }

        $graph = $builder->build();

        $this->graphBehavior->run($input, $output, $graph);
        $this->versionReport->render($output, $graph);

        return $graph->nodes()->byTaskResult(TaskResult::FAILURE())->count();
    }
}
