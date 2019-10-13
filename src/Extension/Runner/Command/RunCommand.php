<?php

namespace Maestro\Extension\Runner\Command;

use Maestro\Extension\Runner\Command\Behavior\GraphBehavior;
use Maestro\Library\Graph\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{
    /**
     * @var GraphBehavior
     */
    private $graphBehavior;

    public function __construct(
        GraphBehavior $graphBehavior
    ) {
        $this->graphBehavior = $graphBehavior;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Run the plan');
        $this->graphBehavior->configure($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        assert($output instanceof ConsoleOutputInterface);
        $section = $output->section();

        $graph = $this->graphBehavior->loadGraph($input);
        $reports = $this->graphBehavior->fetchReports($input);

        $this->graphBehavior->run($input, $output, $graph);
        $this->graphBehavior->renderReports($graph, ...$reports);


        return $graph->nodes()->byState(State::FAILED())->count();
    }
}
