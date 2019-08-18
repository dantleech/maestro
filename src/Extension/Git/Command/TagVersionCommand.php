<?php

namespace Maestro\Extension\Git\Command;

use Maestro\Extension\Git\Task\TagVersionTask;
use Maestro\Extension\Maestro\Command\Behavior\GraphBehavior;
use Maestro\Graph\Edge;
use Maestro\Graph\Graph;
use Maestro\Graph\Node;
use Maestro\Graph\SystemTags;
use Maestro\Graph\TaskResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TagVersionCommand extends Command
{
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
        $this->graphBehavior->configure($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $graph = $this->graphBehavior->buildGraph($input);
        $graph = $graph->pruneForTags(SystemTags::TAG_INITIALIZE);

        foreach ($graph->leafs() as $leaf) {
            $scriptNodeId = sprintf($leaf->id() . '/git tag');
            $nodes = $graph->nodes()->add(Node::create($scriptNodeId, [
                'label' => 'git tag',
                'task' => new TagVersionTask()
            ]));
            $edges = $graph->edges()->add(Edge::create($scriptNodeId, $leaf->id()));
            $graph = new Graph($nodes, $edges);
        }

        $this->graphBehavior->run($input, $output, $graph);

        return $graph->nodes()->byTaskResult(TaskResult::FAILURE())->count();
    }
}
