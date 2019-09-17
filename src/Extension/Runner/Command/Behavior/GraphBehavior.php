<?php

namespace Maestro\Extension\Runner\Command\Behavior;

use Amp\Loop;
use Maestro\Extension\Runner\Loader\GraphConstructor;
use Maestro\Extension\Runner\Loader\ManifestLoader;
use Maestro\Library\GraphTask\GraphTaskScheduler;
use Maestro\Library\Graph\Graph;
use Maestro\Library\Task\Worker;
use Maestro\Library\Util\Cast;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GraphBehavior
{
    private const ARG_MANIFEST = 'manifest';

    private const POLL_TIME_DISPATCH = 10;
    private const POLL_TIME_RENDER = 100;

    /**
     * @var ManifestLoader
     */
    private $loader;

    /**
     * @var GraphConstructor
     */
    private $constructor;

    /**
     * @var GraphTaskScheduler
     */
    private $scheduler;

    /**
     * @var Worker
     */
    private $worker;

    public function __construct(ManifestLoader $loader, GraphConstructor $constructor, GraphTaskScheduler $scheduler, Worker $worker)
    {
        $this->loader = $loader;
        $this->constructor = $constructor;
        $this->scheduler = $scheduler;
        $this->worker = $worker;
    }

    public function configure(Command $command): void
    {
        $command->addArgument(self::ARG_MANIFEST, InputArgument::REQUIRED, 'Path to the plan to execute');
    }

    public function loadGraph(InputInterface $input): Graph
    {
        return $this->constructor->construct(
            $this->loader->load(
                Cast::toString($input->getArgument(self::ARG_MANIFEST))
            )
        );
    }

    public function run(InputInterface $input, OutputInterface $output, Graph $graph)
    {
        assert($output instanceof ConsoleOutputInterface);
        $section = $output->section();

        Loop::repeat(self::POLL_TIME_DISPATCH, function () use ($graph) {
            $this->scheduler->run($graph);
        });

        Loop::repeat(self::POLL_TIME_DISPATCH + 1, function () {
            $this->worker->start();
        });

        Loop::run();
    }
}
