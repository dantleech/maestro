<?php

namespace Maestro\Extension\Maestro\Command;

use Maestro\Console\DumperRegistry;
use Maestro\Console\Report\RunReport;
use Maestro\Extension\Maestro\Command\Behavior\GraphBehavior;
use Maestro\Extension\Maestro\Dumper\TargetDumper;
use Maestro\Extension\Maestro\Graph\ExecScriptOnLeafNodesModifier;
use Maestro\Maestro;
use Maestro\MaestroBuilder;
use Maestro\Graph\TaskResult;
use Maestro\Util\Cast;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{
    private const OPT_DUMP = 'dump';
    private const OPT_LIST_TARGETS = 'targets';
    private const OPT_EXEC_SCRIPT = 'exec';
    private const OPT_ENVIRONMENT = 'environment';

    /**
     * @var MaestroBuilder
     */
    private $builder;

    /**
     * @var DumperRegistry
     */
    private $dumper;

    /**
     * @var GraphBehavior
     */
    private $graphBehavior;

    /**
     * @var RunReport
     */
    private $report;

    public function __construct(
        GraphBehavior $graphBehavior,
        DumperRegistry $dumper,
        RunReport $report
    )
    {
        $this->dumper = $dumper;
        $this->graphBehavior = $graphBehavior;
        $this->report = $report;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Run the plan');
        $this->graphBehavior->configure($this);

        $this->addOption(self::OPT_DUMP, null, InputOption::VALUE_REQUIRED, 'Dump a representation of the task graph to a file');
        $this->addOption(self::OPT_LIST_TARGETS, null, InputOption::VALUE_NONE, 'Display targets');
        $this->addOption(self::OPT_EXEC_SCRIPT, null, InputOption::VALUE_REQUIRED, 'Execute command on targets');
        $this->addOption(self::OPT_ENVIRONMENT, null, InputOption::VALUE_NONE, 'Report environment for leaf nodes after execution');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        assert($output instanceof ConsoleOutputInterface);
        $section = $output->section();

        $graph = $this->graphBehavior->buildGraph($input);

        if ($script = $input->getOption(self::OPT_EXEC_SCRIPT)) {
            $graph = (new ExecScriptOnLeafNodesModifier(Cast::toString($script)))($graph);
        }

        if ($input->getOption(self::OPT_LIST_TARGETS)) {
            $output->writeln((new TargetDumper())->dump($graph));
            return 0;
        }

        if ($dumper = $input->getOption(self::OPT_DUMP)) {
            $output->writeln($this->dumper->get(Cast::toString($dumper))->dump($graph));
            return 0;
        }

        $this->graphBehavior->run($input, $output, $graph);
        $this->report->render($output, $graph);

        return $graph->nodes()->byTaskResult(TaskResult::FAILURE())->count();
    }
}
