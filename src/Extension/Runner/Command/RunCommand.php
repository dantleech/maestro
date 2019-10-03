<?php

namespace Maestro\Extension\Runner\Command;

use Maestro\Extension\Report\Model\ConsoleReport;
use Maestro\Extension\Report\Model\ConsoleReportRegistry;
use Maestro\Extension\Runner\Command\Behavior\GraphBehavior;
use Maestro\Extension\Runner\Report\RunReport;
use Maestro\Library\Graph\State;
use Maestro\Library\Util\Cast;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RunCommand extends Command
{
    const OPTION_REPORT = 'report';

    /**
     * @var GraphBehavior
     */
    private $graphBehavior;

    /**
     * @var ConsoleReportRegistry
     */
    private $reportRegistry;

    public function __construct(
        GraphBehavior $graphBehavior,
        ConsoleReportRegistry $reportRegistry
    ) {
        $this->graphBehavior = $graphBehavior;
        $this->reportRegistry = $reportRegistry;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Run the plan');
        $this->addOption(
            self::OPTION_REPORT,
            'r',
            InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY,
            'Reports to render',
            ['run']
        );
        $this->graphBehavior->configure($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        assert($output instanceof ConsoleOutputInterface);
        $section = $output->section();

        $reports = $this->fetchReports($input->getOption(self::OPTION_REPORT), $output);

        $graph = $this->graphBehavior->loadGraph($input);

        $this->graphBehavior->run($input, $output, $graph);
        $style = new SymfonyStyle($input, $output);

        foreach ($reports as $report) {
            $style->title($report->title());
            $style->block($report->description());
            $report->render($output, $graph);
        }


        return $graph->nodes()->byState(State::FAILED())->count();
    }

    /**
     * @return ConsoleReport[]
     */
    private function fetchReports(array $reports, ConsoleOutputInterface $output): array
    {
        return array_map(function (string $reportName) {
            return $this->reportRegistry->get($reportName);
        }, $reports);
    }
}
