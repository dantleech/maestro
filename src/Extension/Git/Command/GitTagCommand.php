<?php

namespace Maestro\Extension\Git\Command;

use Maestro\Extension\Maestro\Command\Behavior\GraphBehavior;
use Maestro\Graph\SystemTags;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GitTagCommand extends Command
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
        $this->graphBehavior->run($input, $output, $graph);
    }
}
