<?php

namespace Maestro\Extension\Maestro\Command\Behavior;

use Amp\Loop;
use Maestro\Extension\Maestro\Dumper\OverviewRenderer;
use Maestro\Graph\Graph;
use Maestro\Graph\GraphWalker;
use Maestro\Maestro;
use Maestro\MaestroBuilder;
use Maestro\Util\Cast;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GraphBehavior
{
    private const ARG_PLAN = 'plan';
    private const ARG_QUERY = 'target';

    private const OPT_DEPTH = 'depth';
    private const OPT_CONCURRENCY = 'concurrency';
    private const OPT_PURGE = 'purge';
    private const OPT_PROGRESS = 'progress';

    private const POLL_TIME_DISPATCH = 10;
    private const POLL_TIME_RENDER = 100;

    /**
     * @var MaestroBuilder
     */
    private $builder;

    public function __construct(MaestroBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function configure(Command $command): void
    {
        $command->addArgument(self::ARG_PLAN, InputArgument::REQUIRED, 'Path to the plan to execute');
        $command->addArgument(self::ARG_QUERY, InputArgument::OPTIONAL, 'Limit execution to dependencies of matching targets');
        $command->addOption(self::OPT_CONCURRENCY, null, InputOption::VALUE_REQUIRED, 'Limit the number of concurrent tasks', 10);
        $command->addOption(self::OPT_DEPTH, null, InputOption::VALUE_REQUIRED, 'Limit depth of graph');
        $command->addOption(self::OPT_PURGE, null, InputOption::VALUE_NONE, 'Purge package workspaces before build');
        $command->addOption(self::OPT_PROGRESS, 'p', InputOption::VALUE_NONE, 'Show progress');
    }

    public function buildGraph(InputInterface $input): Graph
    {
        $maestro = $this->buildRunner($input);

        return $maestro->buildGraph(
            $maestro->loadManifest(
                Cast::toString(
                    $input->getArgument(self::ARG_PLAN)
                )
            ),
            Cast::toStringOrNull($input->getArgument(self::ARG_QUERY)),
            Cast::toIntOrNull($input->getOption(self::OPT_DEPTH))
        );
    }

    public function run(InputInterface $input, OutputInterface $output, Graph $graph)
    {
        $maestro = $this->buildRunner($input);

        assert($output instanceof ConsoleOutputInterface);
        $section = $output->section();

        Loop::repeat(self::POLL_TIME_DISPATCH, function () use ($maestro, $graph) {
            if ($graph->nodes()->allDone()) {
                Loop::stop();
            }
            $maestro->dispatch($graph);
        });

        if ($input->getOption(self::OPT_PROGRESS)) {
            Loop::repeat(self::POLL_TIME_RENDER, function () use ($graph, $section) {
                $section->overwrite((new OverviewRenderer())->dump($graph));
            });
        }

        Loop::run();
        $section->clear();
    }

    private function buildRunner(InputInterface $input): Maestro
    {
        $builder = $this->builder;
        $builder->withMaxConcurrency(Cast::toInt(
            $input->getOption(self::OPT_CONCURRENCY)
        ));
        $builder->withPurge(Cast::toBool($input->getOption(self::OPT_PURGE)));

        return $builder->build();
    }
}
