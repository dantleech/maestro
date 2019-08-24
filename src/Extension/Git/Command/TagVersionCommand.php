<?php

namespace Maestro\Extension\Git\Command;

use Maestro\Extension\Git\Model\VersionReport;
use Maestro\Extension\Git\Task\TagVersionTask;
use Maestro\Extension\Maestro\Command\Behavior\GraphBehavior;
use Maestro\Extension\Survey\Task\SurveyTask;
use Maestro\Graph\Edge;
use Maestro\Graph\Graph;
use Maestro\Graph\Node;
use Maestro\Graph\SystemTags;
use Maestro\Graph\TaskResult;
use Maestro\Util\Cast;
use Maestro\Util\StringUtil;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
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

    public function __construct(GraphBehavior $graphBehavior)
    {
        $this->graphBehavior = $graphBehavior;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Tag configured version and show report (or just show report with dry-run)');
        $this->graphBehavior->configure($this);
        $this->addOption(self::OPT_DRY_RUN, null, InputOption::VALUE_NONE, 'Do not perform the tag, just show the report');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $graph = $this->graphBehavior->buildGraph($input);
        $graph = $graph->pruneForTags(SystemTags::TAG_INITIALIZE);

        foreach ($graph->leafs() as $leaf) {
            $parentId = $leaf->id();
            $edges = $graph->edges();
            $nodes = $graph->nodes();

            if (false === Cast::toBool($input->getOption(self::OPT_DRY_RUN))) {
                $scriptNodeId = sprintf($leaf->id() . '/git tag');
                $nodes = $graph->nodes()->add(Node::create($scriptNodeId, [
                    'label' => 'git tag',
                    'task' => new TagVersionTask()
                ]));
                $edges = $graph->edges()->add(Edge::create($scriptNodeId, $leaf->id()));
                $parentId = $scriptNodeId;
            }

            $nodes = $nodes->add(Node::create($parentId. '/info', [
                'label' => 'git version info',
                'task' => new SurveyTask(),
                'tags' => ['version_info'],
            ]));
            $edges = $edges->add(Edge::create($parentId. '/info', $parentId));
            $graph = new Graph($nodes, $edges);
        }

        $this->graphBehavior->run($input, $output, $graph);

        $this->renderReport($output, $graph);

        return $graph->nodes()->byTaskResult(TaskResult::FAILURE())->count();
    }

    private function renderReport(OutputInterface $output, Graph $graph)
    {
        $table = new Table($output);
        $table->setHeaders([
            'package',
            'conf',
            'tag',
            'tag-id',
            'head-id',
            'message',
        ]);
        
        foreach ($graph->nodes()->byTaskResult(TaskResult::SUCCESS())->byTags('version_info') as $node) {
            $versionReport = $node->environment()->vars()->get('survey')->get(VersionReport::class);
            assert($versionReport instanceof VersionReport);
            $table->addRow([
                $versionReport->packageName(),
                $this->formatConfiguredVersion($versionReport),
                $versionReport->taggedVersion(),
                substr($versionReport->taggedCommit() ?? '', 0, 10),
                $this->formatHeadCommit($versionReport),
                StringUtil::firstLine($versionReport->headMessage()),
            ]);
        }
        $table->render();
    }

    private function formatConfiguredVersion(VersionReport $versionReport)
    {
        if ($versionReport->willBeTagged()) {
            return sprintf('<bg=black;fg=yellow>%s</>', $versionReport->configuredVersion());
        }

        return $versionReport->configuredVersion();
    }

    private function formatHeadCommit(VersionReport $versionReport)
    {
        if ($versionReport->divergence() > 0) {
            return sprintf(
                '%s <fg=yellow;bg=black>+%s</>',
                substr($versionReport->headCommit(), 0, 10),
                $versionReport->divergence()
            );
        }

        return substr($versionReport->headCommit(), 0, 10);
    }
}
