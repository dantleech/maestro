<?php

namespace Maestro\Extension\Maestro\Command;

use Amp\Loop;
use Maestro\Dumper\DotDumper;
use Maestro\Dumper\GraphRenderer;
use Maestro\Dumper\TargetDumper;
use Maestro\Extension\Maestro\Task\ScriptTask;
use Maestro\Loader\Loader;
use Maestro\Maestro;
use Maestro\MaestroBuilder;
use Maestro\Task\Edge;
use Maestro\Task\Graph;
use Maestro\Task\Node;
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
            foreach ($graph->leafs() as $leaf) {
                $scriptNodeId = sprintf($leaf->id() . '/script');
                $nodes = $graph->nodes()->add(Node::create($scriptNodeId, [
                    'task' => new ScriptTask($script)
                ]));
                $edges = $graph->edges()->add(Edge::create($scriptNodeId, $leaf->id()));
                $graph = new Graph($nodes, $edges);
            }
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
                $section->overwrite((new GraphRenderer())->render($graph));
            });
        }

        Loop::run();

        if ($input->getOption(self::OPT_PROGRESS)) {
            $section->overwrite(
                (new GraphRenderer())->render($graph)
            );
        }

        return $graph->nodes()->byState(State::FAILED())->count();
    }

    private function buildRunner(InputInterface $input): Maestro
    {
        $builder = $this->builder;
        $builder->withMaxConcurrency(Cast::toInt(
            $input->getOption(self::OPT_CONCURRENCY)
        ));
        $runner = $builder->build();
        return $runner;
    }
}
