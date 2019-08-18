<?php

namespace Maestro\Extension\Maestro\Command;

use Amp\Loop;
use Maestro\Console\DumperRegistry;
use Maestro\Extension\Maestro\Command\Behavior\GraphBehavior;
use Maestro\Extension\Maestro\Dumper\TargetDumper;
use Maestro\Extension\Maestro\Dumper\OverviewRenderer;
use Maestro\Extension\Maestro\Graph\ExecScriptOnLeafNodesModifier;
use Maestro\Maestro;
use Maestro\MaestroBuilder;
use Maestro\Graph\TaskResult;
use Maestro\Util\Cast;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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
    private const OPT_REPORT = 'report';

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

    public function __construct(GraphBehavior $graphBehavior, DumperRegistry $dumper)
    {
        $this->dumper = $dumper;
        $this->graphBehavior = $graphBehavior;

        parent::__construct();
    }

    protected function configure()
    {
        $this->graphBehavior->configure($this);

        $this->addOption(self::OPT_DUMP, null, InputOption::VALUE_REQUIRED, 'Dump a representation of the task graph to a file');
        $this->addOption(self::OPT_REPORT, 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Show report when finished');
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

        $reportDumpers = array_map(function (string $dumperName) {
            return $this->dumper->get($dumperName);
        }, (array) $input->getOption(self::OPT_REPORT));

        $this->graphBehavior->run($input, $output, $graph);

        foreach ($reportDumpers as $reportDumper) {
            $output->writeln($reportDumper->dump($graph));
        }

        return $graph->nodes()->byTaskResult(TaskResult::FAILURE())->count();
    }
}
