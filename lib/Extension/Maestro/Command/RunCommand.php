<?php

namespace Maestro\Extension\Maestro\Command;

use Amp\Loop;
use Maestro\Extension\Maestro\Dumper\DotDumper;
use Maestro\Extension\Maestro\Dumper\TargetDumper;
use Maestro\Extension\Maestro\Dumper\GraphRenderer;
use Maestro\Extension\Maestro\Dumper\LeafArtifactsDumper;
use Maestro\Extension\Maestro\Graph\ExecScriptOnLeafNodesModifier;
use Maestro\Loader\Loader;
use Maestro\Maestro;
use Maestro\MaestroBuilder;
use Maestro\Task\State;
use Maestro\Util\Cast;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{
    private const POLL_TIME_DISPATCH = 100;
    private const POLL_TIME_RENDER = 100;

    private const ARG_PLAN = 'plan';
    private const ARG_QUERY = 'target';

    private const OPT_DOT = 'dot';
    private const OPT_CONCURRENCY = 'concurrency';
    private const OPT_PROGRESS = 'progress';
    private const OPT_LIST_TARGETS = 'targets';
    private const OPT_DEPTH = 'depth';
    private const OPT_EXEC_SCRIPT = 'exec';
    private const OPT_ARTIFACTS = 'artifacts';
    private const OPT_PURGE = 'purge';

    /**
     * @var MaestroBuilder
     */
    private $builder;

    /**
     * @var Loader
     */
    private $loader;

    public function __construct(MaestroBuilder $builder, Loader $loader)
    {
        parent::__construct();
        $this->builder = $builder;
        $this->loader = $loader;
    }

    protected function configure()
    {
        $this->addArgument(self::ARG_PLAN, InputArgument::REQUIRED, 'Path to the plan to execute');
        $this->addArgument(self::ARG_QUERY, InputArgument::OPTIONAL, 'Limit execution to dependencies of matching targets');
        $this->addOption(self::OPT_DOT, null, InputOption::VALUE_NONE, 'Dump the task graph to a dot file');
        $this->addOption(self::OPT_CONCURRENCY, null, InputOption::VALUE_REQUIRED, 'Limit the number of concurrent tasks', 10);
        $this->addOption(self::OPT_PROGRESS, 'p', InputOption::VALUE_NONE, 'Show progress');
        $this->addOption(self::OPT_LIST_TARGETS, null, InputOption::VALUE_NONE, 'Display targets');
        $this->addOption(self::OPT_DEPTH, null, InputOption::VALUE_REQUIRED, 'Limit depth of graph');
        $this->addOption(self::OPT_EXEC_SCRIPT, null, InputOption::VALUE_REQUIRED, 'Execute command on targets');
        $this->addOption(self::OPT_ARTIFACTS, null, InputOption::VALUE_NONE, 'Report artifacts for leaf nodes after execution');
        $this->addOption(self::OPT_PURGE, null, InputOption::VALUE_NONE, 'Purge package workspaces before build');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        assert($output instanceof ConsoleOutputInterface);
        $section = $output->section();

        $runner = $this->buildRunner($input);

        $graph = $runner->buildGraph(
            $this->loader->load(
                Cast::toString(
                    $input->getArgument(self::ARG_PLAN)
                )
            ),
            Cast::toStringOrNull($input->getArgument(self::ARG_QUERY)),
            Cast::toIntOrNull($input->getOption(self::OPT_DEPTH))
        );

        if ($script = $input->getOption(self::OPT_EXEC_SCRIPT)) {
            $graph = (new ExecScriptOnLeafNodesModifier(Cast::toString($script)))($graph);
        }

        if ($input->getOption(self::OPT_LIST_TARGETS)) {
            $output->writeln((new TargetDumper())->dump($graph));
            return 0;
        }

        if ($input->getOption(self::OPT_DOT)) {
            return $output->writeln((new DotDumper())->dump($graph));
        }

        Loop::repeat(self::POLL_TIME_DISPATCH, function () use ($runner, $graph) {
            $runner->dispatch($graph);

            if ($graph->nodes()->allDone()) {
                Loop::stop();
            }
        });

        if ($input->getOption(self::OPT_PROGRESS)) {
            Loop::repeat(self::POLL_TIME_RENDER, function () use ($graph, $section) {
                $section->overwrite((new GraphRenderer())->dump($graph));
            });
        }

        Loop::run();

        if ($input->getOption(self::OPT_PROGRESS)) {
            $section->overwrite(
                (new GraphRenderer())->dump($graph)
            );
        }

        if ($input->getOption(self::OPT_ARTIFACTS)) {
            $output->writeln((new LeafArtifactsDumper())->dump($graph));
        }

        return $graph->nodes()->byState(State::FAILED())->count();
    }

    private function buildRunner(InputInterface $input): Maestro
    {
        $builder = $this->builder;
        $builder->withMaxConcurrency(Cast::toInt(
            $input->getOption(self::OPT_CONCURRENCY)
        ));
        $builder->withPurge(Cast::toBool($input->getOption(self::OPT_PURGE)));
        $runner = $builder->build();
        return $runner;
    }
}
