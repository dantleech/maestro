<?php

namespace Maestro\Extension\Maestro\Command;

use Amp\Loop;
use Maestro\Dumper\DotDumper;
use Maestro\Dumper\GraphRenderer;
use Maestro\Loader\Loader;
use Maestro\Maestro;
use Maestro\MaestroBuilder;
use Maestro\Util\Cast;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\PathUtil\Path;

class RunCommand extends Command
{
    const ARG_PLAN = 'plan';

    const POLL_TIME_DISPATCH = 100;
    const POLL_TIME_RENDER = 100;

    const OPTION_DOT = 'dot';
    const OPTION_CONCURRENCY = 'concurrency';
    const OPTION_PROGRESS = 'progress';

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
        $this->addOption(self::OPTION_DOT, null, InputOption::VALUE_NONE, 'Dump the task graph to a dot file');
        $this->addOption(self::OPTION_CONCURRENCY, null, InputOption::VALUE_REQUIRED, 'Limit the number of concurrent tasks', 10);
        $this->addOption(self::OPTION_PROGRESS, 'p', InputOption::VALUE_NONE, 'Show progress');
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
            )
        );

        if ($input->getOption(self::OPTION_DOT)) {
            return $output->writeln((new DotDumper())->dump($graph));
        }

        Loop::repeat(self::POLL_TIME_DISPATCH, function () use ($runner, $graph) {
            $runner->dispatch($graph);

            if ($graph->allDone()) {
                Loop::stop();
            }
        });

        if ($input->getOption(self::OPTION_PROGRESS)) {
            Loop::repeat(self::POLL_TIME_RENDER, function () use ($graph, $section) {
                $section->overwrite((new GraphRenderer())->render($graph));
            });
        }

        Loop::run();

        $section->overwrite(
            (new GraphRenderer())->render($graph)
        );
    }

    private function loadManifestArray(string $planPath)
    {
        $path = $this->resolvePath($planPath);

        if (!file_exists($path)) {
            throw new RuntimeException(sprintf(
                'Plan file "%s" does not exist',
                $path
            ));
        }

        $array = json_decode(Cast::toString(file_get_contents($planPath)), true);

        if (false === $array) {
            throw new RuntimeException(sprintf(
                'Could not decode JSON: "%s"',
                json_last_error_msg()
            ));
        }

        return $array;
    }

    private function resolvePath(string $planPath)
    {
        if (Path::isAbsolute($planPath)) {
            return $planPath;
        }

        return Path::join(Cast::toString(getcwd()), $planPath);
    }

    private function buildRunner(InputInterface $input): Maestro
    {
        $builder = $this->builder;
        $builder->withMaxConcurrency(Cast::toInt(
            $input->getOption(self::OPTION_CONCURRENCY)
        ));
        $runner = $builder->build();
        return $runner;
    }
}
